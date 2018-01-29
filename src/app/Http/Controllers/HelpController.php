<?php
namespace TmlpStats\Http\Controllers;

use Auth;
use TmlpStats as Models;

class HelpController extends Controller
{
    /**
     * Shows list of videos.
     *
     * @return Response
     */
    public function index()
    {
        $videos = [];
        foreach (Models\HelpVideo::active()->get() as $video) {
            if (!$this->context->can('watch', $video)) {
                continue;
            }

            $videos[] = [
                'id' => $video->id,
                'title' => $video->title,
                'description' => $video->description,
                'url' => $video->url,
                'tags' => collect($video->tags)->map(function($tag) {
                    return $tag->name;
                }),
            ];
        }

        return view('help.index')->with(compact('videos'));
    }

    /**
     * Displays video player.
     *
     * @return Response
     */
    public function view($id)
    {
        $video = Models\HelpVideo::findOrFail($id);
        if (!$video->active) {
            abort(404);
        }

        return view('help.view')->with(['title' => $video->title, 'url' => $video->url]);
    }
}
