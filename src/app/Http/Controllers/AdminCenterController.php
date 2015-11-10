<?php
namespace TmlpStats\Http\Controllers;

use TmlpStats\Center;
use TmlpStats\Region;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Requests\CenterRequest;

class AdminCenterController extends Controller
{
    /**
     * Create a new controller instance.
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
        $this->authorize('index', Center::class);

        $centers = Center::orderBy('name', 'asc')->get();

        return view('admin.centers.index', compact('centers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', Center::class);

        return view('admin.centers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CenterRequest $request)
    {
        if (!$request->has('cancel')) {

            $this->authorize('store', Center::class);

            $input = $request->all();

            if ($request->has('global_region') || $request->has('local_region')) {

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
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $center = Center::where('abbreviation', '=', $id)->firstOrFail();

        $this->authorize($center);

        return view('admin.centers.show', compact('center'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $center = Center::where('abbreviation', '=', $id)->firstOrFail();

        $this->authorize($center);

        return view('admin.centers.edit', compact('center'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(CenterRequest $request, $id)
    {
        if (!$request->has('cancel')) {
            $center = Center::where('abbreviation', '=', $id)->firstOrFail();

            $this->authorize($center);

            $input = $request->all();

            if (!$request->has('active')) {
                $input['active'] = false;
            }

            if ($request->has('global_region') || $request->has('local_region')) {

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
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
