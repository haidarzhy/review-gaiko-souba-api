<?php

namespace App\Http\Controllers;

use App\Models\UnitPrice;
use Illuminate\Http\Request;
use App\Models\UnitPriceDetail;

class UnitPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $uPrices = UnitPrice::with(['unitPriceDetails'])->orderBy('id', 'desc')->get();
        return response()->json($uPrices);
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
        if(isset($data['up_id']) && $data['up_id'] != null) {

            $are_id = isset($data['prefecture']) && isset($data['prefecture']['value']) ? $data['prefecture']['value']:null;

            $response = UnitPriceDetail::create([
                'large_classification' => $data['large_classification'],
                'minor_classification' => isset($data['minor_classification']) ? $data['minor_classification']:null,
                'content' => isset($data['content']) ? $data['content']:null,
                'specification' => isset($data['specification']) ? $data['specification']:null,
                'area_id' => $are_id,
                'amount' => isset($data['amount']) ? $data['amount']:null,
                'unit_price_id' => isset($data['up_id']) ? $data['up_id']:null,
                'status' => 1,
                'order' => 1
            ]);

            if($response) {
                return response()->json(1);
            }

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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = UnitPriceDetail::find($id);
        if($data) {
            $data->delete();
            return response()->json(1);
        } else {
            return response()->json(0);
        }
    }
}
