<?php

namespace App\Http\Controllers;

use App\Models\Qq;
use App\Models\Quotation;
use App\Models\MathSymbol;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $mathSymbols = MathSymbol::get();
        $qqs = Qq::select('id')->with(['qas'])->orderBy('id', 'asc')->get();
        $qas = $qqs->pluck('qas')->flatten()->map(function ($qa) {
            return [
                'id' => $qa->id,
                'label' => $qa->label,
                'qq_id' => $qa->qq_id
            ];
        })->toArray();

        $modifiedQqs = $qqs->map(function ($item) {
            unset($item['qas']);
            return $item;
        });

        $quotations = Quotation::where('parent_id', NULL)->get();

        return response()->json([
            'symbols' => $mathSymbols,
            'qqs' => $modifiedQqs,
            'qas' => $qas,
            'quotation' => $quotations
        ]);
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

        // store quotation
        $quoteName = $data['qName'];
        $quote = Quotation::create([
            'q_name' => $quoteName
        ]);
        if($quote) {
            // store condition
            if(isset($data['condition']) && count($data['condition']) > 0) {
                $dumpStoreConditions = [];
                for ($i=0; $i < count($data['condition']); $i++) { 
                    $condi = $data['condition'][$i];
                    if(isset($condi['conQqID']) && count($condi['conQqID']) > 0) {
                        
                    }
                }
            }

            return response()->json([
                'data' => $data,
                'quote' => $quote
            ]);
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
        //
    }
}
