<?php
namespace TmlpStats\Http\Controllers;

use App;
use Auth;
use Illuminate\Http\Request;
use TmlpStats\Api;
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
        $this->authorize('validate', StatsReport::class);

        return view('import.index')->with([
            'submitReport' => false, // Controls whether or not to show Submit button
            'showUploadForm' => true,
            'showReportCheckSettings' => true,
            'expectedDate' => ImportManager::getExpectedReportDate()->toDateString(),
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

        $manager = new ImportManager($request->file('statsFiles'),
            $request->get('expectedReportDate'),
            $enforceVersion);
        $manager->import($submitReport);

        $results = $manager->getResults();

        $request->flashOnly('expectedReportDate', 'ignoreReportDate', 'ignoreVersion');

        return view('import.index')->with([
            'submitReport' => true,
            'showUploadForm' => true,
            'showReportCheckSettings' => true,
            'expectedDate' => ImportManager::getExpectedReportDate()->toDateString(),
            'results' => $results,
        ]);
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
