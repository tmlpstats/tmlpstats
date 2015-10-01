<?php
namespace TmlpStats\Http\Controllers;

use TmlpStats\Center;
use TmlpStats\Region;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Requests\CenterRequest;
use TmlpStats\Http\Controllers\Controller;

class CenterController extends Controller {

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $centers = Center::orderBy('name', 'asc')->get();

        return view('centers.index', compact('centers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('centers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(CenterRequest $request)
    {
        if (!$request->has('cancel')) {

            $input = $request->all();

            if($request->has('global_region') || $request->has('local_region')) {

                $region = (!$request->has('local_region') || $request->get('global_region') !== 'NA')
                    ? Region::abbreviation($request->get('global_region'))->first()
                    : Region::abbreviation($request->get('local_region'))->first();
                if ($region) {
                    $input['region_id'] = $region->id;
                }
            }

            Center::create($input);
        }

        return redirect('admin/centers');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $center = Center::where('abbreviation', '=', $id)->firstOrFail();

        return view('centers.show', compact('center'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $center = Center::where('abbreviation', '=', $id)->firstOrFail();

        return view('centers.edit', compact('center'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(CenterRequest $request, $id)
    {
        if (!$request->has('cancel')) {
            $center = Center::where('abbreviation', '=', $id)->firstOrFail();

            $input = $request->all();

            if (!$request->has('active')) {
                $input['active'] = false;
            }

            if($request->has('global_region') || $request->has('local_region')) {

                $region = (!$request->has('local_region') || $request->get('global_region') !== 'NA')
                    ? Region::abbreviation($request->get('global_region'))->first()
                    : Region::abbreviation($request->get('local_region'))->first();
                if ($region) {
                    $input['region_id'] = $region->id;
                }
            }

            $center->update($input);
        }

        $redirect = 'admin/centers/' . $id;
        if ($request->has('previous_url')) {
            $redirect = $request->get('previous_url');
        }
        return redirect($redirect);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
