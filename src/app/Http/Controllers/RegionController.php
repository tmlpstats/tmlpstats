<?php

namespace TmlpStats\Http\Controllers;

use Illuminate\Http\Request;

use Session;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Controllers\Controller;
use TmlpStats\Region;

class RegionController extends Controller
{
    /**
     * Authenticated admins only
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:administrator');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('index', Region::class);

        $regions = Region::orderBy('name')->get();

        return view('regions.index', compact('regions'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $region = Region::findOrFail($id);

        $this->authorize('read', $region);

        return view('regions.show', compact('region'));
    }
}
