<?php

namespace App\Http\Controllers;

use App\Models\Construction;
use Illuminate\Http\Request;

class ConstructionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $constructions = Construction::orderBy('id', 'asc')->get();
        return response()->json($constructions);
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
        if((isset($data['code']) && $data['code'] != '') && (isset($data['name']) && $data['name'] != '')) {
            $condi = Construction::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'order' => 1,
                'status' => 1
            ]);
            if(!$condi) {
                return response()->json([
                    'response' => 0
                ]);
            } else {
                return response()->json([
                    'response' => 1
                ]);
            }
        } else {
            return response()->json([
                'response' => 0
            ]);
        }
        return response()->json($data);
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
            $area = Construction::find($id);
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
        $data = Construction::find($id);
        if($data) {
            $data->delete();
            return response()->json(1);
        } else {
            return response()->json(0);
        }
    }
}
