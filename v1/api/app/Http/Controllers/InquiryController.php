<?php

namespace App\Http\Controllers;

use App\Models\Qa;
use App\Models\Qq;
use Carbon\Carbon;
use App\Models\Inquiry;
use App\Models\Quotation;
use Illuminate\Support\Str;
use App\Models\InquiryQaAns;
use App\Models\InquiryQuote;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class InquiryController extends Controller
{
    public function calculate(Request $request)
    {
        $currentTimestamp = Carbon::now();
        $data = $request->all();
        // return response()->json($data);
        try {
            if(count($data) > 0) {
                $total = 0;
                $finalCalculatedQuotes = [];
                // get quotations
                $quotations = Quotation::get();
    
                // create a inquiry dummy
                $uuid = Str::uuid()->toString();
                $inquiry = Inquiry::create([
                    'uuid' => $uuid,
                    'confirm' => 0,
                    'status' => 1,
                    'order' => 1
                ]);
    
                // loop quotations
                for ($qindex = 0; $qindex < count($quotations); $qindex++) { 
                    $tFSResult = 0;
                    // set quotation
                    $quotation = $quotations[$qindex];
                    if(count($quotation->quotationConditions) > 0) { // condition exists
                        // set conditions of a quotation
                        $qcs = $quotation->quotationConditions;
                        $exists = [];
                        // check conditions
                        for($qcIndex = 0; $qcIndex < count($qcs); $qcIndex++) {
                            // set a condition of conditions
                            $qc = $qcs[$qcIndex];
                            $existsKey = $qc->condition_id;
                            if(!array_key_exists($existsKey, $exists)) {
                                $exists[$existsKey] = [];
                            }
                            foreach ($data as $item) { // check the conditions in requested data
                                if($qc->qq_id == $item['qId']) { // exists in requested data
                                    $conditionAsString = $qc->qa_id.' '.$qc->mathSymbol->sign.' '.$item['ansId'];
                                    $result = eval("return $conditionAsString;");
                                    $exists[$existsKey][] = $result;
                                }
                            }
                        }

                        if(count($exists) > 0) {
                            $qConditionResult = false;
                            // check quotation condition string
                            if($quotation->condition != null) {
                                $quoteConditionString = $quotation->condition;
                                $replacedFormula = preg_replace_callback('/\b([A-Za-z0-9_]+)\b/', function($matches) use ($exists) {
                                    $key = $matches[1];
                                    if (isset($exists[$key])) {
                                        $value = $exists[$key];
                                        if (is_array($value) && empty($value)) {
                                            return 'false';
                                        }
                                        return is_array($value) ? array_reduce($value, function($carry, $item) {
                                            return $carry && $item;
                                        }, true) : $value;
                                    }
                                    return $key;
                                }, $quoteConditionString);
                                // run the condition
                                $qConditionResult = eval("return $replacedFormula;");
                            } else {
                                foreach ($exists as $values) {
                                    if (in_array(false, $values, true)) {
                                        $qConditionResult = true;
                                        break;
                                    }
                                }
                            }

                            // if conditions were true
                            if($qConditionResult) {
                                $calculatedFormulas = [];
                                // calculate formulas
                                $qfs = $quotation->quotationFormulas;
                                if(count($qfs) > 0) { // if have formulas
                                    for ($fIndex = 0; $fIndex < count($qfs); $fIndex++) { // loop formulas
                                        // set formula
                                        $formulaString = $qfs[$fIndex]->formula;

                                        preg_match_all('/Q\d+/', $formulaString, $matches);
                                        $QValues = $matches[0];
                                        $qFIDs = [];
                                        $QValueFound = true;

                                        foreach ($QValues as $QValue) {
                                            $found = false;
                                            foreach ($data as $item) {
                                                if ($item["qIndex"] === $QValue) {
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            if (!$found) {
                                                $QValueFound = false;
                                                break;
                                            }
                                        }

                                        if($QValueFound) {
                                            // get the values with questionnaire index
                                            for ($dataIndex = 0; $dataIndex < count($data); $dataIndex++) { 
                                                if(isset($data[$dataIndex])) {
                                                    $item = $data[$dataIndex];
                                                    if(isset($item['qIndex'])) {
                                                        if (in_array($item['qIndex'], $QValues)) { // check formula values does exists in data
                                                            $q = Qq::find($item['qId']);
                                                            if($q) {
                                                                if($q->qAnsInputType->input != 'text') {
                                                                    $qa = Qa::find($item['ansId']);
                                                                    $qFIDs[] = [
                                                                        'qIndex' => $item['qIndex'],
                                                                        'qId' => $item['qId'],
                                                                        'ansId' => $qa->unit_price != null ? $qa->unit_price:0
                                                                    ];
                                                                } else {
                                                                    $qFIDs[] = [
                                                                        'qIndex' => $item['qIndex'],
                                                                        'qId' => $item['qId'],
                                                                        'ansId' => $item['ansId']
                                                                    ];
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
            
                                            // if formula values exists in data
                                            if(count($qFIDs) > 0) {
                                                // replace Q* with values
                                                array_reduce($qFIDs, function ($carry, $item) use (&$formulaString) {
                                                    if (strpos($formulaString, $item['qIndex']) !== false) {
                                                        if($item['ansId'] == null || $item['ansId'] == '') {
                                                            $item['ansId'] = 1;
                                                        }
                                                        $formulaString = str_replace($item['qIndex'], $item['ansId'], $formulaString);
                                                    }
                                                    return $carry;
                                                });
            
                                                // calculate formula
                                                $formulaString = str_replace('x', '*', $formulaString);
                                                $fSResult = eval("return $formulaString;");
            
                                                // if have conditions
                                                if(count($qfs[$fIndex]->quotationFormulaConditions) > 0) {
                                                    $qFCResult = null;
                                                    // loop quotation formula conditions
                                                    for ($qfcIndex = 0; $qfcIndex < count($qfs[$fIndex]->quotationFormulaConditions); $qfcIndex++) { 
                                                        // set quotation formula condition
                                                        $qFC = $qfs[$fIndex]->quotationFormulaConditions[$qfcIndex];
                                                        $qFCString = $fSResult.' '.$qFC->mathSymbol->sign.' '.$qFC->situation;
                                                        $qFCStringResult = eval("return $qFCString;");
                                                        if($qFCStringResult) { // check the forumla result condition
                                                            $qFCResult = $qFC->result;
                                                            break;
                                                        } else {
                                                            $qFCResult = $fSResult;
                                                        }
                                                    }
            
                                                    if($qFCResult != null) { // set the formula result
                                                        if($qfs[$fIndex]->formula_total_id != null) {
                                                            $calculatedFormulas[$qfs[$fIndex]->formula_total_id] = $qFCResult;
                                                        } else {
                                                            $calculatedFormulas[] = $qFCResult;
                                                        }
                                                    }
                                                } else { // does not have formula conditions
                                                    if($qfs[$fIndex]->formula_total_id != null) {
                                                        $calculatedFormulas[$qfs[$fIndex]->formula_total_id] = $fSResult;
                                                    } else {
                                                        $calculatedFormulas[] = $fSResult;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                // check the formula result and total formula and calculate them
                                if(count($calculatedFormulas) > 0 && $quotation->formula_total != null) {
                                    // if total formula was defined
                                    $totalFormulaString = $quotation->formula_total;
                                    preg_match_all('/F\d+/', $totalFormulaString, $matches);
                                    $FValues = $matches[0];
                                    $checkValues = true;
                                    for ($keyIndex = 0; $keyIndex < count($FValues); $keyIndex++) { 
                                        if (array_key_exists($FValues[$keyIndex], $calculatedFormulas)) {
                                            // $checkValues = true;
                                        } else {
                                            // $checkValues = false;
                                            $calculatedFormulas[$FValues[$keyIndex]] = 1;
                                        }
                                    }
                                    if($checkValues) { // calculate if values and formula is same
                                        $totalFormulaString2 = strtr($totalFormulaString, $calculatedFormulas);
                                        $tFSResult = ceil(eval("return $totalFormulaString2;") * 10) / 10;
                                    } else {
                                        $tFSResult = 0;
                                    }
                                } else if(count($calculatedFormulas) > 0) {
                                    $tFSResult = array_sum($FValues);
                                }

                            }

                        }
                        
                    } else { // does not have condition
                        $calculatedFormulas = [];
                            // calculate formulas
                            $qfs = $quotation->quotationFormulas;
                            if(count($qfs) > 0) { // if have formulas
                                for ($fIndex = 0; $fIndex < count($qfs); $fIndex++) { // loop formulas
                                    // set formula
                                    $formulaString = $qfs[$fIndex]->formula;
                                    preg_match_all('/Q\d+/', $formulaString, $matches);
                                    $QValues = $matches[0];
                                    $qFIDs = [];
                                    $QValueFound = true;

                                        foreach ($QValues as $QValue) {
                                            $found = false;
                                            foreach ($data as $item) {
                                                if ($item["qIndex"] === $QValue) {
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            if (!$found) {
                                                $QValueFound = false;
                                                break;
                                            }
                                        }

                                        if($QValueFound) {
                                            // get the values with questionnaire index
                                            for ($dataIndex = 0; $dataIndex < count($data); $dataIndex++) { 
                                                if(isset($data[$dataIndex])) {
                                                    $item = $data[$dataIndex];
                                                    if(isset($item['qIndex'])) {
                                                        if (in_array($item['qIndex'], $QValues)) { // check formula values does exists in data
                                                            $q = Qq::find($item['qId']);
                                                            if($q) {
                                                                if($q->qAnsInputType->input != 'text') {
                                                                    $qa = Qa::find($item['ansId']);
                                                                    $qFIDs[] = [
                                                                        'qIndex' => $item['qIndex'],
                                                                        'qId' => $item['qId'],
                                                                        'ansId' => $qa->unit_price != null ? $qa->unit_price:0
                                                                    ];
                                                                } else {
                                                                    $qFIDs[] = [
                                                                        'qIndex' => $item['qIndex'],
                                                                        'qId' => $item['qId'],
                                                                        'ansId' => $item['ansId']
                                                                    ];
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
            
                                            // if formula values exists in data
                                            if(count($qFIDs) > 0) {
                                                // replace Q* with values
                                                array_reduce($qFIDs, function ($carry, $item) use (&$formulaString) {
                                                    if (strpos($formulaString, $item['qIndex']) !== false) {
                                                        if($item['ansId'] == null || $item['ansId'] == '') {
                                                            $item['ansId'] = 1;
                                                        }
                                                        $formulaString = str_replace($item['qIndex'], $item['ansId'], $formulaString);
                                                    }
                                                    return $carry;
                                                });
            
                                                // calculate formula
                                                $formulaString = str_replace('x', '*', $formulaString);
                                                $fSResult = eval("return $formulaString;");
            
                                                // if have conditions
                                                if(count($qfs[$fIndex]->quotationFormulaConditions) > 0) {
                                                    $qFCResult = null;
                                                    // loop quotation formula conditions
                                                    for ($qfcIndex = 0; $qfcIndex < count($qfs[$fIndex]->quotationFormulaConditions); $qfcIndex++) { 
                                                        // set quotation formula condition
                                                        $qFC = $qfs[$fIndex]->quotationFormulaConditions[$qfcIndex];
                                                        $qFCString = $fSResult.' '.$qFC->mathSymbol->sign.' '.$qFC->situation;
                                                        $qFCStringResult = eval("return $qFCString;");
                                                        if($qFCStringResult) { // check the forumla result condition
                                                            $qFCResult = $qFC->result;
                                                            break;
                                                        } else {
                                                            $qFCResult = $fSResult;
                                                        }
                                                    }
            
                                                    if($qFCResult != null) { // set the formula result
                                                        if($qfs[$fIndex]->formula_total_id != null) {
                                                            $calculatedFormulas[$qfs[$fIndex]->formula_total_id] = $qFCResult;
                                                        } else {
                                                            $calculatedFormulas[] = $qFCResult;
                                                        }
                                                    }
                                                } else { // does not have formula conditions
                                                    if($qfs[$fIndex]->formula_total_id != null) {
                                                        $calculatedFormulas[$qfs[$fIndex]->formula_total_id] = $fSResult;
                                                    } else {
                                                        $calculatedFormulas[] = $fSResult;
                                                    }
                                                }
                                            }
                                        }
                                }
                            } else {
                                continue;
                            }
    
                            // check the formula result and total formula and calculate them
                            if(count($calculatedFormulas) > 0 && $quotation->formula_total != null) {
                                // if total formula was defined
                                $totalFormulaString = $quotation->formula_total;
                                preg_match_all('/F\d+/', $totalFormulaString, $matches);
                                $FValues = $matches[0];
                                $checkValues = false;
                                for ($keyIndex = 0; $keyIndex < count($FValues); $keyIndex++) { 
                                    if (array_key_exists($FValues[$keyIndex], $calculatedFormulas)) {
                                        $checkValues = true;
                                    } else {
                                        $checkValues = false;
                                        break;
                                    }
                                }
                                if($checkValues) { // calculate if values and formula is same
                                    $totalFormulaString2 = strtr($totalFormulaString, $calculatedFormulas);
                                    $tFSResult = ceil(eval("return $totalFormulaString2;") * 10) / 10;
                                } else {
                                    $tFSResult = 0;
                                }
                            } else if(count($calculatedFormulas) > 0) {
                                $tFSResult = array_sum($FValues);
                            }
                    }
    
                    // final calculation
                    if($tFSResult > 0 && $quotation->base_amount != null) {
                        array_push($finalCalculatedQuotes, [
                            'quotation_id' => $quotation->id,
                            'quantity' => $tFSResult,
                            'unit_price' => $quotation->base_amount,
                            'amount' => $quotation->base_amount * $tFSResult,
                            'inquiry_id' => $inquiry->id,
                            'created_at' => $currentTimestamp,
                            'updated_at' => $currentTimestamp
                        ]);
                        $total += $quotation->base_amount * $tFSResult;
                    } else if($tFSResult > 0  && $quotation->base_amount == null) {
                        array_push($finalCalculatedQuotes, [
                            'quotation_id' => $quotation->id,
                            'quantity' => $tFSResult,
                            'unit_price' => 1,
                            'amount' => $tFSResult,
                            'inquiry_id' => $inquiry->id,
                            'created_at' => $currentTimestamp,
                            'updated_at' => $currentTimestamp
                        ]);
                        $total += $tFSResult;
                    } else if($quotation->base_amount != null) {
                        array_push($finalCalculatedQuotes, [
                            'quotation_id' => $quotation->id,
                            'quantity' => 1,
                            'unit_price' => $quotation->base_amount,
                            'amount' => $quotation->base_amount,
                            'inquiry_id' => $inquiry->id,
                            'created_at' => $currentTimestamp,
                            'updated_at' => $currentTimestamp
                        ]);
                        $total += $quotation->base_amount;
                    } else {
                        array_push($finalCalculatedQuotes, [
                            'quotation_id' => $quotation->id,
                            'quantity' => 0,
                            'unit_price' => 0,
                            'amount' => 0,
                            'inquiry_id' => $inquiry->id,
                            'created_at' => $currentTimestamp,
                            'updated_at' => $currentTimestamp
                        ]);
                        $total += 0;
                    }
                }
    
                // store the request data 
                $data = array_map(function ($item) use ($currentTimestamp, $inquiry) {
                    $item['q_index'] = $item['qIndex'];
                    $item['qq_id'] = $item['qId'];
                    $item['qa_id'] = null;
                    $item['qa_value'] = $item['ansId'];
    
                    unset($item['qIndex']);
                    unset($item['qId']);
                    unset($item['ansId']);
    
                    return array_merge(
                        [
                            'inquiry_id' => $inquiry->id,
                            'created_at' => $currentTimestamp,
                            'updated_at' => $currentTimestamp
                        ],
                        $item
                    );
                }, $data);
    
                $inquiryQaAns = InquiryQaAns::insert($data);
                
                // store the inquiry quotes
                $inquiryQuote = InquiryQuote::insert($finalCalculatedQuotes);
    
                // update total
                $inquiry->total = $total;
                $inquiry->save();
    
                return response()->json($inquiry->uuid);
    
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
        
        return response()->json(0);
    }

    public function show($uuid)
    {
        $inquiry = Inquiry::with(['inquiryQuotes'])->where('uuid', $uuid)->first();
        if($inquiry) {
            return response()->json($inquiry);
        } else {
            return response()->json(null);
        }
    }

    public function update(Request $request,$uuid)
    {
        $data = $request->all();
        try {
            $inquiry = Inquiry::where('uuid', $uuid)->update([
                'name' => $data['fullname'],
                'kata_name' => $data['kata_fullname'],
                'address01' => $data['address01'],
                'address02' => $data['address02'],
                'email' => $data['email'],
                'tel' => $data['tel'],
                'company_name' => isset($data['company_name']) ? $data['company_name']:null,
                'construction_schedule' => $data['construction_schedule'],
                'confirm' => 1
            ]);
        } catch (QueryException $e) {
            return response()->json($e->getMessage());
        }

        if($inquiry) {
            // send mail

        }

        return response()->json($inquiry);
    }
}
