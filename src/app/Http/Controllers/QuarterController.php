<?php
namespace TmlpStats\Http\Controllers;

use TmlpStats\Quarter;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Requests\QuarterRequest;

class QuarterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $quarters = Quarter::orderBy('global_region')
            ->orderBy('start_weekend_date', 'ASC')
            ->presentAndFuture()
            ->get();
        return view('quarters.index', compact('quarters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('quarters.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(QuarterRequest $request)
    {
        if (!$request->has('cancel')) {
            Quarter::create($request->all());
        }
        return redirect('admin/quarters');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $quarter = Quarter::findOrFail($id);

        return view('quarters.show', compact('quarter'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $quarter = Quarter::findOrFail($id);

        return view('quarters.edit', compact('quarter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(QuarterRequest $request, $id)
    {
        if (!$request->has('cancel')) {
            $quarter = Quarter::findOrFail($id);
            $quarter->update($request->all());
        }

        $redirect = 'admin/quarters/' . $id;
        if ($request->has('previous_url')) {
            $redirect = $request->get('previous_url');
        }
        return redirect($redirect);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

}
