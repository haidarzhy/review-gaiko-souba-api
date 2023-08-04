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
use Illuminate\Database\QueryException;
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
        $qs = Quotation::with(['parent'])->orderBy('id', 'desc')->get();
        return response()->json($qs);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $mathSymbols = MathSymbol::get();
        $qqs = Qq::select(['id', 'qindex'])->with(['qas'])->orderBy('qindex', 'asc')->get();
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
            'condition' => isset($data['conditionString']) ? $data['conditionString']:null,
            'base_amount' => isset($data['baseAmount']) ? $data['baseAmount']:null,
            'formula_total' => isset($data['totalFormula']) ? $data['totalFormula']:null,
            'parent_id' => $data['qParent'] != '0' ? $data['qParent']:null
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
                                if($label != "どれでも") {
                                    $ansID = Qa::where('label', $label)->where('qq_id', $condi['conQqID'][$j])->first();
                                    if($ansID) {
                                        $dumpQCondition = [
                                            'qq_id' => $condi['conQqID'][$j],
                                            'math_symbol_id' => $condi['conSymbol'],
                                            'qa_id' => $ansID->id,
                                            'qa_value' => null,
                                            'qa_any' => 0,
                                            'condition_id' => 'C'.($i + 1),
                                            'quotation_id' => $quote->id,
                                            'created_at' => $now,
                                            'updated_at' => $now,
                                        ];
                                        array_push($dumpStoreQConditions, $dumpQCondition);
                                    }
                                } else {
                                    $dumpQCondition = [
                                        'qq_id' => $condi['conQqID'][$j],
                                        'math_symbol_id' => $condi['conSymbol'],
                                        'qa_id' => null,
                                        'qa_value' => null,
                                        'qa_any' => 1,
                                        'condition_id' => 'C'.($i + 1),
                                        'quotation_id' => $quote->id,
                                        'created_at' => $now,
                                        'updated_at' => $now,
                                    ];
                                    array_push($dumpStoreQConditions, $dumpQCondition);
                                }
                            }
                        } else { //single question
                            if(isset($condi['conAnsID']) && $condi['conAnsID'] != null) {
                                if($condi['conAnsID']['label'] == 'どれでも') {
                                    $dumpQCondition = [
                                        'qq_id' => $condi['conQqID'][0],
                                        'math_symbol_id' => $condi['conSymbol'],
                                        'qa_id' => null,
                                        'qa_value' => null,
                                        'qa_any' => 1,
                                        'condition_id' => 'C'.($i + 1),
                                        'quotation_id' => $quote->id,
                                        'created_at' => $now,
                                        'updated_at' => $now,
                                    ];
                                    array_push($dumpStoreQConditions, $dumpQCondition);
                                } else {
                                    $dumpQCondition = [
                                        'qq_id' => $condi['conQqID'][0],
                                        'math_symbol_id' => $condi['conSymbol'],
                                        'qa_id' => $condi['conAnsID']['value'],
                                        'qa_value' => null,
                                        'qa_any' => 0,
                                        'condition_id' => 'C'.($i + 1),
                                        'quotation_id' => $quote->id,
                                        'created_at' => $now,
                                        'updated_at' => $now,
                                    ];
                                    array_push($dumpStoreQConditions, $dumpQCondition);
                                }
                            } else if(isset($condi['conAnsValue']) && $condi['conAnsValue'] != null) {
                                $dumpQCondition = [
                                    'qq_id' => $condi['conQqID'][0],
                                    'math_symbol_id' => $condi['conSymbol'],
                                    'qa_id' => null,
                                    'qa_value' => $condi['conAnsValue'],
                                    'qa_any' => 0,
                                    'condition_id' => 'C'.($i + 1),
                                    'quotation_id' => $quote->id,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                                array_push($dumpStoreQConditions, $dumpQCondition);
                            }
                        }
                    }
                }

                if(count($dumpStoreQConditions) > 0) {
                    try {
                        QuotationCondition::insert($dumpStoreQConditions);
                    } catch (QueryException $e) {
                        return response()->json($e->getMessage());
                    }
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
        $quote = Quotation::with(['parent', 'quotationConditionsWithAll', 'quotationFormulasWithAll'])->find($id);

        if($quote) {
            return response()->json($quote);
        }
        return response()->json(null);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(0);
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

        $quotation = Quotation::find($id);
        if($quotation) {
            $data = $request->all();
            $now = Carbon::now();

            // update quotation
            $quoteName = $data['qName'];
            $quote = $quotation->update([
                'q_name' => $quoteName,
                'condition' => isset($data['conditionString']) ? $data['conditionString']: null,
                'base_amount' => isset($data['baseAmount']) ? $data['baseAmount']:null,
                'formula_total' => isset($data['totalFormula']) ? $data['totalFormula']: null,
                'parent_id' => $data['qParent'] != '0' ? $data['qParent']:null
            ]);


            if($quote) {

                $quote = $quotation;

                // update condition
                if(isset($data['condition']) && count($data['condition']) > 0) {
                    $dumpUpdateConditions = [];
                    for ($i=0; $i < count($data['condition']); $i++) { 
                        $condi = $data['condition'][$i];

                        if(isset($condi['conQqID'])) {
                            if(count($condi['conQqID']) > 1) { //multiple question
                                for ($j=0; $j < count($condi['conQqID']); $j++) { 
                                    $label = $condi['conAnsID']['label'];
                                    if($label != "どれでも") {
                                        $ansID = Qa::where('label', $label)->where('qq_id', $condi['conQqID'][$j])->first();
                                        if($ansID) {
                                            
                                            $dumpQCondition = [
                                                'id' => isset($condi['id']) && $condi['id'] != null && count($condi['id']) > $j ? $condi['id'][$j]:null,
                                                'qq_id' => $condi['conQqID'][$j],
                                                'math_symbol_id' => $condi['conSymbol'],
                                                'qa_id' => $ansID->id,
                                                'qa_value' => null,
                                                'qa_any' => 0,
                                                'condition_id' => 'C'.($i + 1),
                                                'quotation_id' => $quote->id,
                                            ];
                                            array_push($dumpUpdateConditions, $dumpQCondition);
                                        }
                                    } else {
                                        $dumpQCondition = [
                                            'id' => isset($condi['id']) && $condi['id'] != null && count($condi['id']) > $j ? $condi['id'][$j]:null,
                                            'qq_id' => $condi['conQqID'][$j],
                                            'math_symbol_id' => $condi['conSymbol'],
                                            'qa_id' => null,
                                            'qa_value' => null,
                                            'qa_any' => 1,
                                            'condition_id' => 'C'.($i + 1),
                                            'quotation_id' => $quote->id,
                                        ];
                                        array_push($dumpUpdateConditions, $dumpQCondition);
                                    }
                                }

                                if(isset($condi['id']) && $condi['id'] != null && count($condi['id']) > 0) {
                                    $ids = $condi['id'];
                                    QuotationCondition::where('quotation_id', $quote->id)
                                        ->whereNotIn('id', $condi['id'])
                                        ->delete();
                                }

                            } else { //single question
                                if(isset($condi['conAnsID']) && $condi['conAnsID'] != null) {
                                    if($condi['conAnsID']['label'] == 'どれでも') {
                                        $dumpQCondition = [
                                            'id' => isset($condi['id']) && $condi['id'] != null && count($condi['id']) > 0 ? $condi['id'][0]:null,
                                            'qq_id' => $condi['conQqID'][0],
                                            'math_symbol_id' => $condi['conSymbol'],
                                            'qa_id' => null,
                                            'qa_value' => null,
                                            'qa_any' => 1,
                                            'condition_id' => 'C'.($i + 1),
                                            'quotation_id' => $quote->id,
                                        ];

                                    } else {
                                        $dumpQCondition = [
                                            'id' => isset($condi['id']) && $condi['id'] != null && count($condi['id']) > 0 ? $condi['id'][0]:null,
                                            'qq_id' => $condi['conQqID'][0],
                                            'math_symbol_id' => $condi['conSymbol'],
                                            'qa_id' => $condi['conAnsID']['value'],
                                            'qa_value' => null,
                                            'qa_any' => 0,
                                            'condition_id' => 'C'.($i + 1),
                                            'quotation_id' => $quote->id,
                                        ];
                                    }
                                } else {
                                    $dumpQCondition = [
                                        'id' => isset($condi['id']) && $condi['id'] != null && count($condi['id']) > 0 ? $condi['id'][0]:null,
                                        'qq_id' => $condi['conQqID'][0],
                                        'math_symbol_id' => $condi['conSymbol'],
                                        'qa_id' => null,
                                        'qa_value' => $condi['conAnsValue'],
                                        'qa_any' => 0,
                                        'condition_id' => 'C'.($i + 1),
                                        'quotation_id' => $quote->id,
                                    ];
                                }

                                if(isset($condi['id']) && $condi['id'] != null && count($condi['id']) > 0) {
                                    $ids = $condi['id'];
                                    QuotationCondition::where('quotation_id', $quote->id)
                                        ->whereNotIn('id', $condi['id'])
                                        ->delete();
                                }
                                array_push($dumpUpdateConditions, $dumpQCondition);
                            }
                        }
                    }

                    if(count($dumpUpdateConditions) > 0) {
                        try {
                            QuotationCondition::upsert($dumpUpdateConditions, ['id'], ['qq_id', 'math_symbol_id', 'qa_id', 'condition_id', 'quotation_id']);
                        } catch (QueryException $e) {
                            return response()->json($e->getMessage());
                        }
                    }
                } else {
                    QuotationCondition::where('quotation_id', $quote->id)->delete();
                }


                    // update formula
                    $fIDs = [];
                    if(isset($data['formula']) && count($data['formula']) > 0) {

                        for ($i=0; $i < count($data['formula']); $i++) { 

                                if($data['formula'][$i]['id'] == null) {

                                    try {
                                        $formula = QuotationFormula::create([
                                            'formula' => $data['formula'][$i]['text'],
                                            'formula_total_id' => 'F'.($i + 1),
                                            'quotation_id' => $quote->id
                                        ]);
    
    
    
                                        array_push($fIDs, $formula->id);
                    
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
                                    } catch (\Exception $e) {
                                        return response()->json('POS IF: '.$e->getMessage());
                                    }

                                } else {
                                    try {
                                        $formula = QuotationFormula::where('id', $data['formula'][$i]['id'])->first();
                                        array_push($fIDs, $formula->id);

                                        $fCondi = $formula->update([
                                            'formula' => $data['formula'][$i]['text'],
                                            'formula_total_id' => 'F'.($i + 1),
                                            'quotation_id' => $quote->id
                                        ]);

                                        if($fCondi) {
                                            $dumpUpdateFCondition = [];

                                            if($formula && isset($data['formula'][$i]['fcondition']) && count($data['formula'][$i]['fcondition']) > 0) {
                                                $fcondition = $data['formula'][$i]['fcondition'];
                                                for ($j=0; $j < count($fcondition); $j++) { 
                                                    $dumpFCondition = [
                                                        'id' => $fcondition[$j]['id'],
                                                        'math_symbol_id' => $fcondition[$j]['fconSymbol'],
                                                        'situation' => $fcondition[$j]['fconSituation'],
                                                        'result' => $fcondition[$j]['fconResult'],
                                                        'quotation_formula_id' => $formula->id,
                                                        // 'created_at' => $now,
                                                        // 'updated_at' => $now,
                                                    ];
                                                    array_push($dumpUpdateFCondition, $dumpFCondition);
                                                }
                                            }

                                            if(count($dumpUpdateFCondition) > 0) {
                                                try {
                                                    QuotationFormulaCondition::upsert($dumpUpdateFCondition, ['id'], ['math_symbol_id', 'situation', 'result', 'quotation_formula_id']);
                                                } catch (QueryException $e) {
                                                    return response()->json($e->getMessage());
                                                }
                                            }
                                        } else {
                                            return response()->json(0);
                                        }
                                    } catch (\Exception $e) {
                                        return response()->json('POS ELSE: '.$e->getMessage());
                                    }
                            } 
                        }
                    }


                    QuotationFormula::where('quotation_id', $quote->id)
                    ->whereNotIn('id', $fIDs)
                    ->delete();


                    return response()->json(1);

            } else {
                return response()->json(0);
            }

        } else {
            return response()->json(0);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Quotation::find($id);
        if($data) {
            $data->delete();
            return response()->json(1);
        } else {
            return response()->json(0);
        }
    }

}
