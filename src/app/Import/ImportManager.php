<?php
namespace TmlpStats\Import;

use Auth;
use Cache;
use Carbon\Carbon;
use Exception;
use Log;
use Mail;
use TmlpStats\Import\Xlsx\XlsxArchiver;
use TmlpStats\GlobalReport;
use TmlpStats\Person;
use TmlpStats\Quarter;
use TmlpStats\ReportToken;
use TmlpStats\Settings\Setting;

// Required for importing multiple sheets
ini_set('max_execution_time', 240);
ini_set('memory_limit', '512M');
ini_set('max_file_uploads', '30');

/**
 * Class ImportManager
 * @package TmlpStats\Import
 *
 * ImportManager takes the list of uploaded files, has them all processed, and returns an array of results
 */
class ImportManager
{
    protected $files = [];
    protected $expectedDate = null;
    protected $enforceVersion = false;
    protected $skipEmail = false;

    protected $results = [];

    /**
     * ImportManager constructor.
     *
     * @param             $files
     * @param null|string $expectedDate
     * @param bool|true   $enforceVersion
     */
    public function __construct($files, $expectedDate = null, $enforceVersion = true)
    {
        $this->files = $files;
        if ($expectedDate) {
            $this->expectedDate = Carbon::createFromFormat('Y-m-d', $expectedDate)->startOfDay();
        }
        $this->enforceVersion = $enforceVersion;
    }

    /**
     * Specify whether or not to skip sending emails after processing a stats report
     *
     * @param bool|true $skip
     */
    public function setSkipEmail($skip = true)
    {
        $this->skipEmail = $skip;
    }

    /**
     * Get the import/validation results
     *
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Perform import. If $saveReport is provided and true, the files provided in the constructor will be imported
     * and saved.
     *
     * @param bool|false $saveReport
     */
    public function import($saveReport = false)
    {
        $successSheets = [];
        $warnSheets    = [];
        $errorSheets   = [];
        $unknownFiles  = [];

        foreach ($this->files as $file) {

            $exception = null;
            $sheetPath = null;
            $sheet     = [];

            try {
                if (!($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)) {
                    // If someone refreshes the page after submitting file, and the browser doesn't send the file contents,
                    // we end up with empty files.
                    $file = null;
                    throw new Exception("There was a problem uploading one of the files. Please try again.");
                }

                $fileName = $file->getClientOriginalName();
                if (!$file->isValid()) {
                    Log::error("Error uploading '$fileName': {$file->getError()}");
                    $file = null;
                    throw new Exception("There was a problem uploading '$fileName'. Please try again.");
                }

                try {
                    $importer = new Xlsx\XlsxImporter($file->getRealPath(), $fileName, $this->expectedDate, $this->enforceVersion);
                    $importer->import();
                    if ($saveReport) {
                        $importer->saveReport();
                    }
                    $sheet = $importer->getResults();
                } catch (Exception $e) {
                    Log::error("Error processing '$fileName': " . $e->getMessage() . "\n" . $e->getTraceAsString());

                    // Files starting with '~$' are usually special temp files created by Excel. Be nice and let the
                    // user know that's probably what went wrong.
                    if (strpos($fileName, '~$') === 0) {
                        throw new Exception("The file you uploaded, '{$fileName}', looks like a special Excel temporary file. Please look for a file that does not start with '~$'");
                    }

                    throw new Exception("There was an error processing '$fileName': " . $e->getMessage());
                }

                $user         = Auth::user()->email;
                $errorCount   = count($sheet['errors']);
                $warningCount = count($sheet['warnings']);
                Log::info("{$user} submitted sheet for {$sheet['center']} with {$errorCount} errors and {$warningCount} warnings.");

                if (isset($sheet['statsReportId'])) {

                    // Caching results so we don't have to reprocess them when the user presses submit.
                    // Throw away results if not submitted within 10 minutes
                    // If we already saved, don't bother caching.
                    if (!$saveReport) {
                        $cacheKey = "statsReport{$sheet['statsReportId']}:importdata";
                        Cache::put($cacheKey, $importer, 10);
                    }

                    if ($sheet['submittedAt']) {
                        $sheetPath = XlsxArchiver::getInstance()->archive($file, $sheet['statsReport']);
                    } else {
                        $sheetPath = XlsxArchiver::getInstance()->saveWorkingSheet($file, $sheet['statsReport']);
                    }
                }

                if ($sheet['result'] == 'error') {
                    $errorSheets[] = $sheet;
                } else if ($sheet['result'] == 'warning') {
                    $warnSheets[] = $sheet;
                } else {
                    $successSheets[] = $sheet;
                }
            } catch (Exception $e) {

                Log::error("Error processing file: " . $e->getMessage());
                $unknownFiles[] = $e->getMessage();
                $exception      = $e;
            }

            if (!$sheetPath && $file) {

                $sheetPath = XlsxArchiver::getInstance()->saveWorkingSheet($file);
            }

            if ($exception) {
                $center = isset($sheet['center'])
                    ? $sheet['center']
                    : 'unknown';

                $user = Auth::user()->email;
                $time = Carbon::now()->format('Y-m-d H:i:s');

                $body = "An exception was caught processing a sheet submitted by '{$user}' for {$center} center at {$time} UTC: '" . $exception->getMessage() . "'\n\n";
                $body .= $exception->getTraceAsString() . "\n";
                try {
                    Mail::raw($body, function ($message) use ($center, $sheetPath) {
                        $message->to(env('ADMIN_EMAIL'))->subject("Exception processing sheet for {$center} center");
                        if ($sheetPath) {
                            $message->attach($sheetPath);
                        }
                    });
                } catch (\Exception $e) {
                    Log::error("Exception caught sending error email: " . $e->getMessage());
                }
            } else if ($this->skipEmail) {
                // Don't send the email.
            } else if ($sheet['submittedAt']) {

                $result = $this->sendStatsSubmittedEmail($sheet['statsReport'], $sheet);
                if ($result !== false) {
                    $this->results['messages'] = $result;
                }
            }
        }

        $this->results['sheets']       = array_merge($successSheets, $warnSheets, $errorSheets);
        $this->results['unknownFiles'] = $unknownFiles;
    }

    /**
     * Get the expected reported date based on day of week.
     *
     * @return Carbon datetime object
     */
    public static function getExpectedReportDate()
    {
        $expectedDate = null;
        switch (Carbon::now()->dayOfWeek) {
            case Carbon::SATURDAY:
            case Carbon::SUNDAY:
            case Carbon::MONDAY:
            case Carbon::TUESDAY:
                $expectedDate = new Carbon('last friday');
                break;
            case Carbon::WEDNESDAY:
            case Carbon::THURSDAY:
                $expectedDate = new Carbon('next friday');
                break;
            case Carbon::FRIDAY:
                $expectedDate = Carbon::now();
                break;
        }

        return $expectedDate->startOfDay();
    }

    /**
     * Send emails for the provided report to the configured accountables
     *
     * @param $statsReport
     * @param $sheet
     *
     * @return array
     * @throws Exception
     */
    public static function sendStatsSubmittedEmail($statsReport, $sheet)
    {
        if (!$statsReport || !$statsReport->submittedAt) {
            return false;
        }

        $result = [];

        $user    = ucfirst(Auth::user()->firstName);
        $quarter = $statsReport->quarter;
        $center  = $statsReport->center;
        $region  = $center->region;

        $submittedAt = $statsReport->submittedAt->copy()->setTimezone($center->timezone);

        $due               = $statsReport->due();
        $respondByDateTime = $statsReport->responseDue();

        $isLate = $submittedAt->gt($due);

        $programManager         = $center->getProgramManager($quarter);
        $classroomLeader        = $center->getClassroomLeader($quarter);
        $t1TeamLeader           = $center->getT1TeamLeader($quarter);
        $t2TeamLeader           = $center->getT2TeamLeader($quarter);
        $statistician           = $center->getStatistician($quarter);
        $statisticianApprentice = $center->getStatisticianApprentice($quarter);

        $emailMap = [
            'center'                 => $center->statsEmail,
            'regional'               => $region->email,
            'programManager'         => static::getEmail($programManager),
            'classroomLeader'        => static::getEmail($classroomLeader),
            't1TeamLeader'           => static::getEmail($t1TeamLeader),
            't2TeamLeader'           => static::getEmail($t2TeamLeader),
            'statistician'           => static::getEmail($statistician),
            'statisticianApprentice' => static::getEmail($statisticianApprentice),
        ];

        $emailMap['to'] = $emailMap['center']
            ? $emailMap['center']
            : $emailMap['statistician'];

        // If this is the first week and the report didn't validate, we also didn't import any of the
        // new accountables. Don't send the email to last quarters accountables, and instead just send it to the
        // center's stats email.
        $accountablesCopied = true;
        if (Quarter::isFirstWeek($region) && !$statsReport->isValidated()) {
            unset($emailMap['programManager']);
            unset($emailMap['classroomLeader']);
            unset($emailMap['t1TeamLeader']);
            unset($emailMap['t2TeamLeader']);
            unset($emailMap['statistician']);
            unset($emailMap['statisticianApprentice']);
            $accountablesCopied = false;
        }

        $mailingList = Setting::name('centerReportMailingList')
                              ->with($center, $quarter)
                              ->get();

        if ($mailingList) {
            $emailMap['mailingList'] = $mailingList;
        }

        $emails = [];
        foreach ($emailMap as $accountability => $email) {

            if (!$email || $accountability == 'center') {
                continue;
            }

            if (strpos($email, ',') !== false) {
                $emails = array_merge($emails, explode(',', $email));
            } else {
                $emails[] = $email;
            }
        }
        $emails = array_unique($emails);

        // Don't dump HTML into the logs
        if (env('MAIL_DRIVER') === 'log') {
            $sheet = [];
        }

        $globalReport = GlobalReport::reportingDate($statsReport->reportingDate)->first();

        $reportToken = ReportToken::get($globalReport, $center);
        $reportUrl   = url("/report/{$reportToken->token}");

        $sheetPath     = XlsxArchiver::getInstance()->getSheetPath($statsReport);
        $sheetName     = XlsxArchiver::getInstance()->getDisplayFileName($statsReport);
        $centerName    = $center->name;
        $comment       = $statsReport->submitComment;
        $reportingDate = $statsReport->reportingDate;
        try {
            Mail::send('emails.statssubmitted',
                compact('user', 'centerName', 'submittedAt', 'sheet', 'isLate', 'due', 'comment', 'respondByDateTime', 'reportUrl', 'reportingDate', 'accountablesCopied'),
                function ($message) use ($emails, $emailMap, $centerName, $sheetPath, $sheetName) {
                    // Only send email to centers in production
                    if (env('APP_ENV') === 'prod') {
                        $message->to($emailMap['to']);
                        foreach ($emails as $email) {
                            $message->cc($email);
                        }
                    } else {
                        $message->to(env('ADMIN_EMAIL'));
                    }

                    if ($emailMap['regional']) {
                        $message->replyTo($emailMap['regional']);
                    }

                    $message->subject("Team {$centerName} Statistics Submitted");

                    // Don't include the attachment when we are logging the emails instead of sending them
                    if (env('MAIL_DRIVER') !== 'log') {
                        $message->attach($sheetPath, [
                            'as' => $sheetName,
                        ]);
                    }
                }
            );
            $successMessage = "<strong>Thank you.</strong> We received your statistics and have sent a copy to the following emails"
                . "<ul><li>{$emailMap['to']}</li><li>" . implode('</li><li>', array_values($emails)) . "</li></ul>"
                . " Please reply-all to that email if there is anything you need to communicate.";

            if (env('APP_ENV') === 'prod') {
                Log::info("Sent emails to the following people with team {$centerName}'s report: " . implode(', ', $emails));
            } else {
                Log::info("Sent emails to the following people with team {$centerName}'s report: " . env('ADMIN_EMAIL'));
                $successMessage .= "<br/><br/><strong>Since this is development, we sent it to "
                    . env('ADMIN_EMAIL') . " instead.</strong>";
            }
            $result['success'][] = $successMessage;
        } catch (\Exception $e) {
            Log::error("Exception caught sending error email: " . $e->getMessage());
            $result['error'][] = "<strong>Hold up.</strong> There was a problem emailing your sheet to your team."
                . " Please email the sheet you just submitted to your Program Manager, Classroom Leader, T2 Team Leader,"
                . " and Regional Statistician ({$emailMap['regional']}) using your center stats email ({$emailMap['center']})."
                . " <span style='font-weight:bold'>We did</span> receive your statistics.";
        }

        return $result;
    }

    /**
     * Return person's email if they are not marked unsubscribed
     *
     * @param Person $person
     *
     * @return array|mixed|null
     */
    public static function getEmail(Person $person = null)
    {
        return ($person && !$person->unsubscribed)
            ? $person->email
            : null;
    }
}
