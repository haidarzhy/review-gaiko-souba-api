<?php

namespace App\Http\Controllers;

use App\Models\Qa;
use App\Models\Qq;
use Carbon\Carbon;
use App\Models\Quotation;
use App\Models\MathSymbol;
use Illuminate\Http\Request;
use App\Models\QuotationFormula;
use App\Models\QuotationCondition;
use App\Models\QuotationFormulaCondition;

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
        $now = Carbon::now();

        // store quotation
        $quoteName = $data['qName'];
        $quote = Quotation::create([
            'q_name' => $quoteName,
            'formula_total' => $data['totalFormula']
        ]);
        if($quote) {
            // store condition
            if(isset($data['condition']) && count($data['condition']) > 0) {
                $dumpStoreQConditions = [];
                for ($i=0; $i < count($data['condition']); $i++) { 
                    $condi = $data['condition'][$i];
                    if(isset($condi['conQqID'])) {
                        if(count($condi['conQqID']) > 1) { //multiple question
                            for ($j=0; $j < count($condi['conQqID']); $j++) { 
                                $label = $condi['conAnsID']['label'];
                                $ansID = Qa::where('label', $label)->where('qq_id', $condi['conQqID'][$j])->first();
                                if($ansID) {
                                    $dumpQCondition = [
                                        'qq_id' => $condi['conQqID'][$j],
                                        'math_symbol_id' => $condi['conSymbol'],
                                        'qa_id' => $ansID->id,
                                        'quotation_id' => $quote->id,
                                        'created_at' => $now,
                                        'updated_at' => $now,
                                    ];
                                    array_push($dumpStoreQConditions, $dumpQCondition);
                                }
                            }
                        } else { //single question
                            $dumpQCondition = [
                                'qq_id' => $condi['conQqID'][0],
                                'math_symbol_id' => $condi['conSymbol'],
                                'qa_id' => $condi['conAnsID']['value'],
                                'quotation_id' => $quote->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                            array_push($dumpStoreQConditions, $dumpQCondition);
                        }
                    }
                }

                if(count($dumpStoreQConditions) > 0) {
                    QuotationCondition::insert($dumpStoreQConditions);
                }
            }

            // store formula
            if(isset($data['formula']) && count($data['formula']) > 0) {
                for ($i=0; $i < count($data['formula']); $i++) { 
                    $formula = QuotationFormula::create([
                        'formula' => $data['formula'][$i]['text'],
                        'formula_total_id' => 'F'.($i + 1),
                        'quotation_id' => $quote->id
                    ]);

                    $dumpStoreFCondition = [];
                    if($formula && isset($data['formula'][$i]['fcondition']) && count($data['formula'][$i]['fcondition']) > 0) {
                        $fcondition = $data['formula'][$i]['fcondition'];
                        for ($j=0; $j < count($fcondition); $j++) { 
                            $dumpFCondition = [
                                'math_symbol_id' => $fcondition[$j]['fconSymbol'],
                                'situation' => $fcondition[$j]['fconSituation'],
                                'result' => $fcondition[$j]['fconResult'],
                                'quotation_formula_id' => $formula->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                            array_push($dumpStoreFCondition, $dumpFCondition);
                        }
                    }

                    if(count($dumpStoreFCondition) > 0) {
                        QuotationFormulaCondition::insert($dumpStoreFCondition);
                    }
                }
            }

            return response()->json([
                'data' => $data,
                'quote' => $dumpStoreFCondition
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