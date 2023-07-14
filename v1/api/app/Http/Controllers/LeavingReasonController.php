<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeavingReason;

class LeavingReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lrs = LeavingReason::orderBy('id', 'desc')->get();
        return response()->json($lrs);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $lr = LeavingReason::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'status' => 1,
            'order' => 1
        ]);
        if($lr) {
            return response()->json(1);
        }
        return response()->json(0);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        if((isset($data['code']) && $data['code'] != '') && (isset($data['name']) && $data['name'] != '')) {
            $area = LeavingReason::find($id);
            if($area) {
                $area->code = $data['code'];
                $area->name = $data['name'];
                $area->save();
                return response()->json(1);
            } 
        }
        return response()->json(0);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = LeavingReason::find($id);
        if($data) {
            $data->delete();
            return response()->json(1);
        } else {
            return response()->json(0);
        }
    }
}
