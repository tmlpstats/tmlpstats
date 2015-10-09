<?php
namespace TmlpStats\Http\Controllers;

use TmlpStats\Http\Requests;
use TmlpStats\Http\Controllers\Controller;
use TmlpStats\Import\ImportManager;

use Carbon\Carbon;

use Auth;
use Request;

class ImportController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('import.index')->with([
            'submitReport'            => false, // Controls whether or not to show Submit button
            'showUploadForm'          => true,
            'showReportCheckSettings' => true,
            'expectedDate'            => ImportManager::getExpectedReportDate()->toDateString(),
        ]);
    }

    // Handle XLSX file uploads
    public function uploadSpreadsheet(Request $request)
    {
        $user = Auth::user();
        $enforceVersion = (Request::get('ignoreVersion') != 1);

        $submitReport = false;
        if (Request::has('submitReport')) {
            $submitReport = (Request::get('submitReport') == 1);
        }

        $manager = new ImportManager(Request::file('statsFiles'),
                                     Request::get('expectedReportDate'),
                                     $enforceVersion);
        $manager->import($submitReport);

        $results = $manager->getResults();

        Request::flashOnly('expectedReportDate', 'ignoreReportDate', 'ignoreVersion');

        // Controls whether or not to show Submit button
        $showReport = false;
        if ($user->getCenter()->globalRegion === 'NA' || $user->getCenter()->globalRegion === 'EME') {
            $showReport = true;
        }

        return view('import.index')->with([
            'submitReport'            => $showReport,
            'showUploadForm'          => true,
            'showReportCheckSettings' => true,
            'expectedDate'            => ImportManager::getExpectedReportDate()->toDateString(),
            'results'                 => $results,
        ]);
    }

    // Import sheets from previous quarters. No validation is done
    public function import()
    {
        return view('admin.import')->with([
            'showUploadForm'          => true,
            'showReportCheckSettings' => false,
        ]);
    }

    // Handle XLSX file uploads for import (no validation)
    public function uploadImportSpreadsheet(Request $request)
    {
        $manager = new ImportManager(Request::file('statsFiles'));
        $manager->import(false);

        $results = $manager->getResults();

        return view('admin.import')->with([
            'showUploadForm'          => true,
            'showReportCheckSettings' => false,
            'results'                 => $results,
        ]);
    }

}
