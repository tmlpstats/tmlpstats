<?php
namespace TmlpStats\Http\Controllers;

use App;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use TmlpStats\Api;
use TmlpStats\Encapsulations;
use TmlpStats\Import\ImportManager;
use TmlpStats\StatsReport;

class ImportController extends Controller
{
    /**
     * ImportController constructor.
     *
     * Requires that the user is logged in and is a statistician
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:statistician');
        $this->context = App::make(Api\Context::class);
    }

    /**
     * Show spreadsheet validation pag
     *
     * @return \Illuminate\Http\Response
     */
    public function indexValidateSheet(Request $request)
    {
        $this->context->setRegion(Auth::user()->homeRegion());
        $this->context->setCenter(Auth::user()->center);
        $this->authorize('validate', StatsReport::class);
        $expectedDate = ImportManager::getExpectedReportDate();

        return view('import.index')->with([
            'submitReport' => false, // Controls whether or not to show Submit button
            'showUploadForm' => true,
            'showReportCheckSettings' => true,
            'showAccountabilities' => $this->canShowAccountabilities($request, $expectedDate),
            'expectedDate' => $expectedDate->toDateString(),
        ]);
    }

    /**
     * Validate uploaded spreadsheet and return results
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function validateSheet(Request $request)
    {
        $this->context->setRegion(Auth::user()->homeRegion());
        $this->authorize('validate', StatsReport::class);

        $enforceVersion = ($request->get('ignoreVersion') != 1);

        $submitReport = false;
        if ($request->has('submitReport')) {
            $submitReport = ($request->get('submitReport') == 1);
        }

        $manager = new ImportManager($request->file('statsFiles'), $request->get('expectedReportDate'), $enforceVersion);
        $manager->import($submitReport);

        $results = $manager->getResults();

        $request->flashOnly('expectedReportDate', 'ignoreReportDate', 'ignoreVersion');
        $expectedDate = ImportManager::getExpectedReportDate();

        return view('import.index')->with([
            'submitReport' => isset($results['sheets'][0]['statsReportId']),
            'showUploadForm' => true,
            'showReportCheckSettings' => true,
            'expectedDate' => $expectedDate->toDateString(),
            'showAccountabilities' => $this->canShowAccountabilities($request, $expectedDate),
            'results' => $results,
        ]);
    }

    public function canShowAccountabilities(Request $request, Carbon $reportingDate)
    {
        if ($request->get('showAccountabilities', false)) {
            return true;
        } else {
            $crd = Encapsulations\CenterReportingDate::ensure(Auth::user()->center, $reportingDate);

            return $crd->canShowNextQtrAccountabilities();
        }

        return false;
    }

    /**
     * Show spreadsheet import page
     *
     * @return \Illuminate\Http\Response
     */
    public function indexImportSheet()
    {
        $this->authorize('import', StatsReport::class);

        return view('admin.import')->with([
            'submitReport' => false, // Controls whether or not to show Submit button
            'showUploadForm' => true,
            'showReportCheckSettings' => false,
        ]);
    }

    /**
     * Import uploaded spreadsheet. This does a validate and submit without sending an email.
     *
     * @return \Illuminate\Http\Response
     */
    public function importSheet(Request $request)
    {
        $this->authorize('import', StatsReport::class);

        $manager = new ImportManager($request->file('statsFiles'), null, false);
        $manager->setSkipEmail(true);
        $manager->import(true);
        $results = $manager->getResults();

        if ($request->has('json')) {
            return json_encode($results);
        }

        $request->flashOnly('expectedReportDate', 'ignoreReportDate', 'ignoreVersion');

        return view('admin.import')->with([
            'submitReport' => false, // Controls whether or not to show Submit button
            'showUploadForm' => true,
            'showReportCheckSettings' => false,
            'expectedDate' => ImportManager::getExpectedReportDate()->toDateString(),
            'results' => $results,
        ]);
    }

}
