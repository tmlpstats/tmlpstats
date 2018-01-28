<?php
namespace TmlpStats\Http\Controllers;

use Auth;

class HelpController extends Controller
{
    /**
     * Parses file name for the video title.
     *
     * @param string $file
     * @return string
     */
    private function get_title($file)
    {
        $pathinfo = pathinfo($file);
        // Use title of file as the video title.

        return ucfirst($pathinfo['filename']);
    }

    /**
     * Shows list of videos.
     *
     * @return Response
     */
    public function index()
    {
        // Check if user is an admin to filter out admin videos later.
        $currentUser = Auth::user();
        $isAdminUser = false;
        if ($currentUser->isAdmin() ||
            $currentUser->hasRole('globalStatistician')) {
            $isAdminUser = true;
        }

        $files = [
            'accountabilities.mov',
            'managing application dates.mov',
            'where to find accountabilities report.mov',
            'entering reg per participant.mov',
            'admin/printing weekend accountability rosters.mov',
            'admin/Reconciliation Report Walkthrough.mov',
            'admin/unlocking promises.mov',
            'admin/weekend presentation thoughts.mov',
            'admin/deleting duplicates.mov',
        ];

        $videos = array();
        foreach ($files as $file) {
            $content = new \stdClass();
            $content->title = $this->get_title($file);
            $content->file = $file;

            // Do not show admin videos to non-admins.
            if ((strpos($file, 'admin') !== false || strpos($file, 'preview') !== false) && !$isAdminUser) {
                continue;
            }

            // Have folder as a tag.
            $pathinfo = pathinfo($file);
            if ($pathinfo['dirname'] != '.') {
                // If there is a directory, then list has tag.
                $content->tag = $pathinfo['dirname'];
            }

            $videos[] = $content;
        }

        return view('help.index')->with(['videos' => $videos]);
    }

    /**
     * Displays video player.
     *
     * @return Response
     */
    public function view($file)
    {
        return view('help.view')->with(['title' => $this->get_title($file),
            'file' => $file]);
    }

}
