<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Qa;
use App\Models\Qq;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Inquiry;
use App\Models\Quotation;
use Illuminate\Support\Str;
use App\Models\InquiryQaAns;
use App\Models\InquiryQuote;
use Illuminate\Http\Request;
use App\Models\QuotationFormula;
use App\Mail\InquiryThankYouEmail;
use App\Models\QuotationCondition;
use App\Mail\InquiryAcceptUserEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use App\Mail\InquiryAcceptContractorEmail;

class InquiryController extends Controller
{

    public function index()
    {
        // CONTRACTOR MyPage
        $inquiry = Inquiry::with(['inquiryQuotes'])->get();
        $dumpIqs = [];
        $userArea = '';

        try {
            if(Auth::guard('sanctum')->check()) {
                $user = User::where('id', Auth::id())->with(['areas'])->first();
                if($user) {
                    $userArea = $user->areas[0]->name;
                }
            }
        } catch (\Exception $e) {
            return respone()->json($e->getMessage());
        }


        
        try {
            if($inquiry && count($inquiry) > 0) {
                foreach ($inquiry as $iq) {
                    if($iq->confirm == 1) {
                        $dIqs = [];
                        $dIqs['id'] = $iq->id;
                        $dIqs['construction_schedule'] = $iq->construction_schedule;
                        $dIqs['address'] = $iq->address01.' '.$iq->address02;
                        $dIqs['total'] = $iq->total;
                        if(isset($iq->inquiryQaAns) && $iq->inquiryQaAns != null && count($iq->inquiryQaAns) > 0) {
                            $iqas = $iq->inquiryQaAns[0];
                            if(isset($iqas->qa)) {
                                $dIqs['area'] = $iqas->qa->label;
                            }
                        }
                        if(isset($dIqs['area']) && $userArea == $dIqs['area']) {
                            array_push($dumpIqs, $dIqs);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return respone()->json($e->getMessage());
        }

        return response()->json($dumpIqs);


        if($inquiry) {
            return response()->json($dumpIqs);
        } else {
            return response()->json(null);
        }
    }

    public function getAll()
    {
        // CP INDEX
        $inquiry = Inquiry::with(['inquiryQuotes', 'inquiryQaAns'])->orderBy('id', 'desc')->get();
        if($inquiry) {
            return response()->json($inquiry);
        } else {
            return response()->json(null);
        }
    }

    public function detail($id)
    {
        $inquiry = Inquiry::with(['inquiryQuotes', 'inquiryQaAns'])->where('id', $id)->first();
        if($inquiry) {
            return response()->json($inquiry);
        } else {
            return response()->json(null);
        }
    }

    public function calculate(Request $request)
    {
        $currentTimestamp = Carbon::now();
        $data = $request->all();
        // get qId array with qId key from data
        $qqIds = array_column($data, 'qId');
        // get qId array with qIndex key from data
        $qqIndexIds = array_column($data, 'qIndex');

        $quotationIds1 = QuotationCondition::whereIn('qq_id', $qqIds)
                                ->pluck('quotation_id')
                                ->toArray();
        $quotationIds2 = QuotationFormula::where(function ($query) use ($qqIndexIds) {
                                foreach ($qqIndexIds as $column) {
                                    $query->orWhere('formula', 'LIKE', '%' . $column . '%');
                                }
                            })->pluck('quotation_id')
                            ->toArray();
        $combinedQuotationIds = array_unique(array_merge($quotationIds1, $quotationIds2));
        // sort the values
        asort($combinedQuotationIds);
        // sort the index
        $combinedQuotationIds = array_values($combinedQuotationIds);

        $quotations = Quotation::whereIn('id', $combinedQuotationIds)->get();
        
        // check quotation is not empty
        if(count($quotations) > 0) {
            // loop the quotations which were contained data
            for ($qIndex=0; $qIndex < count($quotations); $qIndex++) { 
                
                // set variables with short names
                $quotation = $quotations[$qIndex];
                $qcs = $quotation->quotationConditions;
                $qfs = $quotation->quotationFormulas;

                // global variables
                $conditionResult = false;
                $conditionResultArray = [];
                $formulaResultArray = [];
                $formulaConditionResult = false;

                // check quotation has condition or not
                if(count($qcs) > 0) {

                    // loop the quotation condition
                    for ($qCIndex=0; $qCIndex < count($qcs); $qCIndex++) { 

                        // variables
                        $qc = $qcs[$qCIndex];
                        $conditionKey = $qc->condition_id;
                        $conditionQIndex = 'Q'.$qc->qq->qindex;
                        $conditionQqId = $qc->qq_id;
                        $conditionAnsId = $qc->qa_id;
                        $conditionMathSymbol = $qc->mathSymbol;

                        if(!array_key_exists($conditionKey, $conditionResultArray)) {
                            $conditionResultArray[$conditionKey] = [];
                        }

                        // filter the data, take only inlude question index from data
                        $filteredData = array_filter($data, function ($item) use ($conditionQIndex) {
                            return $item["qIndex"] === $conditionQIndex;
                        });

                        if($filteredData != null && count($filteredData) > 0) {

                            // loop filtered data
                            foreach($filteredData as $fD) {

                                // check all required fields
                                if($conditionQqId != null && $conditionAnsId != null && $conditionMathSymbol != null && isset($fD['qId']) && $fD['qId'] != null && isset($fD['ansId']) && $fD['ansId'] != null) {

                                    if(is_array($fD['ansId'])) { // check ansId is array or not

                                        foreach($fD['ansId'] as $fd) {

                                            if(is_numeric($fd)) {

                                                $conditionAsString = $conditionAnsId.' '.$conditionMathSymbol->sign.' '.$fd;
                                                
                                            } else {

                                                $replacement = "false";
                                                $conditionAsString = $conditionAnsId.' '.$conditionMathSymbol->sign.' '.$replacement;
    
                                            }

                                            $result = eval("return $conditionAsString;");
                                            if(isset($conditionKey) && isset($conditionResultArray[$conditionKey])) {
                                                $conditionResultArray[$conditionKey][] = $result;
                                            }

                                        }

                                    } else {

                                        if(is_numeric($fD['ansId'])) {

                                            $conditionAsString = $conditionAnsId.' '.$conditionMathSymbol->sign.' '.$fD['ansId'];
                                            
                                        } else {

                                            $replacement = "false";
                                            $conditionAsString = $conditionAnsId.' '.$conditionMathSymbol->sign.' '.$replacement;

                                        }

                                        $result = eval("return $conditionAsString;");
                                        if(isset($conditionKey) && isset($conditionResultArray[$conditionKey])) {
                                            $conditionResultArray[$conditionKey][] = $result;
                                        }

                                    }

                                } else {

                                    $conditionResultArray[$conditionKey][] = false;

                                }

                            }

                        }

                    }

                }

                // check quotation condition
                if($quotation->condition != null) {

                    $qCString = $quotation->condition;
                    $replacedFormula = preg_replace_callback('/\b([A-Za-z0-9_]+)\b/', function($matches) use ($conditionResultArray) {
                        $key = $matches[1];
                        if (isset($conditionResultArray[$key])) {
                            $value = $conditionResultArray[$key];
                            if (is_array($value) && empty($value)) {
                                return "false";
                            }
                            return is_array($value) ? array_reduce($value, function($carry, $item) {
                                return $carry && $item == true ? "true":"false";
                            }, true) : $value;
                        }
                        return $key;
                    }, $qCString);

                    $conditionResult = eval("return $replacedFormula;");

                } else {
                    // set the condition resutl to true if there's no condition
                    $conditionResult = true;

                }

                // check the condition result
                if($conditionResult) {

                    // check quotation has formula or not
                    if(count($qfs) > 0) {

                        // loop the quotation foumula
                        for ($qFIndex=0; $qFIndex < count($qfs); $qFIndex++) { 
                            
                            $formulaRow = $qfs[$qFIndex];
                            $formulaString = $formulaRow->formula;
                            $formulaKey = $formulaRow->formula_total_id;
                            $formulaCondition = $formulaRow->quotationFormulaConditions;

                            if($formulaString != null && $formulaString != '') {

                                preg_match_all('/Q\d+/', $formulaString, $matches);
                                $formulaQNumbers = array_unique($matches[0]);

                                $filteredData = array_filter($data, function ($item) use ($formulaQNumbers) {
                                    return in_array($item['qIndex'], $formulaQNumbers);
                                });
                                
                                // Convert the filtered result back to an indexed array
                                $filteredData = array_values($filteredData);

                                if($filteredData != null && count($filteredData) > 0) {
                                    
                                    for($filteredDataIndex = 0; $filteredDataIndex < count($filteredData); $filteredDataIndex++) {

                                        $fD = $filteredData[$filteredDataIndex];

                                        if(isset($fD['ansId']) && $fD['ansId'] != null) { // check if ansId is not null

                                            // answer input type are radio or checkbox
                                            if(is_array($fD['ansId'])) { // check ansId is array or not

                                                for($fDIndex = 0; $fDIndex < count($fD['ansId']); $fDIndex++) {

                                                    $fd = $fD['ansId'][$fDIndex];

                                                    if(is_numeric($fd)) {

                                                        if (strpos($formulaString, $fD['qIndex']) !== false) { // check again for sure 

                                                            // get the unit_price
                                                            $qa = Qa::find($fD['ansId']);
                                                            if($qa) {

                                                                $ansUnitPrice = $qa->unit_price;
                                                                if($ansUnitPrice != null) {

                                                                    $formulaString = str_replace($fD['qIndex'], $ansUnitPrice, $formulaString);

                                                                } else {

                                                                    $formulaString = str_replace($fD['qIndex'], 0, $formulaString);

                                                                }
                                                                

                                                            }

                                                        }

                                                    }

                                                }
                                                
                                            } else if(is_numeric($fD['ansId'])) {

                                                if (strpos($formulaString, $fD['qIndex']) !== false) { // check again for sure 

                                                    // get the unit_price
                                                    $qa = Qa::find($fD['ansId']);
                                                    if($qa) {

                                                        $ansUnitPrice = $qa->unit_price;
                                                        if($ansUnitPrice != null) {

                                                            $formulaString = str_replace($fD['qIndex'], $ansUnitPrice, $formulaString);

                                                        } else {

                                                            $formulaString = str_replace($fD['qIndex'], 0, $formulaString);

                                                        }
                                                        

                                                    } else {

                                                        $formulaString = str_replace($fD['qIndex'], 0, $formulaString);
    
                                                    }

                                                } else {

                                                    $formulaString = str_replace($fD['qIndex'], 0, $formulaString);

                                                }

                                            }

                                        } else if(isset($fD['ans']) && $fD['ans'] != null) { // check if ans is not null

                                            // answer input type are text
                                            if(is_numeric($fD['ans'])) {

                                                if (strpos($formulaString, $fD['qIndex']) !== false) { // check again for sure 

                                                    $formulaString = str_replace($fD['qIndex'], $fD['ans'], $formulaString);
                                                    
                                                } else {

                                                    $formulaString = str_replace($fD['qIndex'], 0, $formulaString);

                                                }

                                            } else {

                                                $formulaString = str_replace($fD['qIndex'], 0, $formulaString);

                                            }

                                        }

                                    }

                                    // check the formula string, all must numbers and signs
                                    if (preg_match('/[a-zA-Z]/', $formulaString)) { 

                                        $formulaResultArray[$formulaKey] = 0;

                                    } else {

                                        $formulaResult = round(eval("return $formulaString;"));
                                        // check if have formula conditions
                                        if($formulaCondition != null && count($formulaCondition) > 0) {

                                            for ($fCIndex=0; $fCIndex < count($formulaCondition); $fCIndex++) { 
                                                


                                            }

                                        }

                                    }

                                    return response()->json([
                                        'formulaString' => $formulaString,
                                        'formulaResult' => $formulaResultArray,
                                    ]);

                                }

                                return response()->json([
                                    'formulaQNumbers' => $formulaQNumbers,
                                    'data' => $data,
                                ]);

                            }
    
                        }
    
                    }

                }

            }

        }

        return response()->json([
            'unique' => $combinedQuotationIds,
            'id1' => $quotationIds1,
            'id2' => $quotationIds2
        ]);

        try {
            if(count($data) > 0) {
                $total = 0;
                $finalCalculatedQuotes = [];
                // get quotations
                $quotations = Quotation::get();
                
                /*--------------------  CREATE INQUIRY -----------------*/
                // create a inquiry dummy
                $uuid = Str::uuid()->toString();
                $inquiry = Inquiry::create([
                    'uuid' => $uuid,
                    'confirm' => 0,
                    'status' => 1,
                    'order' => 1
                ]);
    
                /*--------------------  LOOP QUOTATION -----------------*/
                // loop quotations
                for ($qindex = 0; $qindex < count($quotations); $qindex++) { 
                    $tFSResult = 0;
                    // set quotation
                    $quotation = $quotations[$qindex];


                    /*--------------------  CONDITION -----------------*/
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
                                    try {
                                        if(isset($qc->qa_id) && isset($item['ansId']) != '' && isset($qc->mathSymbol) && $qc->mathSymbol != null) {
                                            $conditionAsString = $qc->qa_id.' '.$qc->mathSymbol->sign.' '.$item['ansId'];
                                            $result = eval("return $conditionAsString;");
                                            if(isset($existsKey) && isset($exists[$existsKey])) {
                                                $exists[$existsKey][] = $result;
                                            }
                                        }
                                    } catch (Exception $e) {
                                        return response()->json('POS-1: '.$e->getMessage());
                                    }
                                }
                            }
                        }


                        if(count($exists) > 0) {
                            $qConditionResult = false;
                            // check quotation condition string
                            if($quotation->condition != null) {
                                $quoteConditionString = $quotation->condition;
                                try {
                                    $replacedFormula = preg_replace_callback('/\b([A-Za-z0-9_]+)\b/', function($matches) use ($exists) {
                                        $key = $matches[1];
                                        if (isset($exists[$key])) {
                                            $value = $exists[$key];
                                            if (is_array($value) && empty($value)) {
                                                return "false";
                                            }
                                            return is_array($value) ? array_reduce($value, function($carry, $item) {
                                                return $carry && $item == true ? "true":"false";
                                            }, true) : $value;
                                        }
                                        return $key;
                                    }, $quoteConditionString);

                                    // run the condition
                                    $qConditionResult = eval("return $replacedFormula;");

                                } catch (Exception $e) {
                                    return response()->json('POS-2: '.$e->getMessage());
                                }

                            } else {
                                foreach ($exists as $values) {
                                    if (in_array(false, $values, true)) {
                                        $qConditionResult = true;
                                        break;
                                    }
                                }
                            }


                            // return response()->json([
                            //     'data' => $data,
                            //     'exists' => $exists,
                            //     'condition' => $qConditionResult
                            // ]);

                            /*--------------------  FORMULA CALCULATION -----------------*/
                            // if conditions were true
                            if($qConditionResult) {
                                $calculatedFormulas = [];
                                // calculate formulas
                                $qfs = $quotation->quotationFormulas;

                                if(count($qfs) > 0) { // if have formulas
                                    for ($fIndex = 0; $fIndex < count($qfs); $fIndex++) { // loop formulas
                                        // set formula
                                        $formulaString = $qfs[$fIndex]->formula;

                                        if($formulaString != null && $formulaString != '') {
                                            try {
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
                                            } catch (Exception $e) {
                                                return response()->json('POS-3: '.$e->getMessage());
                                            }
    
                                            if($QValueFound) {
                                                // get the values with questionnaire index
                                                for ($dataIndex = 0; $dataIndex < count($data); $dataIndex++) { 
                                                    try {
                                                        if(isset($data[$dataIndex])) {
                                                            $item = $data[$dataIndex];
                                                            if(isset($item['qIndex'])) {
                                                                if (in_array($item['qIndex'], $QValues)) { // check formula values does exists in data
                                                                    $q = Qq::find($item['qId']);
                                                                    if($q) {
                                                                        if($q->qAnsInputType->input != 'text') {
                                                                            $qa = Qa::find($item['ansId']);
                                                                            if($qa) {
                                                                                $qFIDs[] = [
                                                                                    'qIndex' => $item['qIndex'],
                                                                                    'qId' => $item['qId'],
                                                                                    'ansId' => $qa->unit_price != null ? $qa->unit_price:0
                                                                                ];
                                                                            }
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
                                                    } catch (Exception $e) {
                                                        return response()->json('POS-4: '.$e->getMessage());
                                                    }
                                                }
                
                                                // if formula values exists in data
                                                if(count($qFIDs) > 0) {
                                                    // replace Q* with values
                                                    try {
                                                        array_reduce($qFIDs, function ($carry, $item) use (&$formulaString) {
                                                            if (strpos($formulaString, $item['qIndex']) !== false) {
                                                                if($item['ansId'] == null || $item['ansId'] == '') {
                                                                    $item['ansId'] = 0;
                                                                }
                                                                $formulaString = str_replace($item['qIndex'], $item['ansId'], $formulaString);
                                                            }
                                                            return $carry;
                                                        });
                    
                                                        // calculate formula
                                                        $formulaString = str_replace('x', '*', $formulaString);
                                                        $fSResult = eval("return $formulaString;");
                                                    } catch (Exception $e) {
                                                        return response()->json('POS-5: '.$e->getMessage());
                                                    }
                
                                                    // if have conditions
                                                    if(count($qfs[$fIndex]->quotationFormulaConditions) > 0) {
                                                        $qFCResult = null;
                                                        // loop quotation formula conditions
                                                        try {
                                                            for ($qfcIndex = 0; $qfcIndex < count($qfs[$fIndex]->quotationFormulaConditions); $qfcIndex++) { 
                                                                // set quotation formula condition
                                                                $qFC = $qfs[$fIndex]->quotationFormulaConditions[$qfcIndex];
                                                                if($qFC != null && $qFC->mathSymbol != null && $fSResult != null && isset($qFc->situation) && $qFC->situation != null) {
                                                                    $qFCString = $fSResult.' '.$qFC->mathSymbol->sign.' '.$qFC->situation;
                                                                    $qFCStringResult = eval("return $qFCString;");
                                                                    if($qFCStringResult) { // check the forumla result condition
                                                                        $qFCResult = $qFC->result;
                                                                        break;
                                                                    } else {
                                                                        $qFCResult = $fSResult;
                                                                    }
                                                                }
                                                            }
                                                        } catch (Exception $e) {
                                                            return response()->json('POS-6: '.$e->getMessage());
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
                                }

                                /*--------------------  TOTAL FORMULA CALCULATION -----------------*/
                                // check the formula result and total formula and calculate them
                                if(count($calculatedFormulas) > 0 && $quotation->formula_total != null) {
                                    // if total formula was defined
                                    try {
                                        $totalFormulaString = $quotation->formula_total;
                                        preg_match_all('/F\d+/', $totalFormulaString, $matches);
                                        $FValues = $matches[0];
                                        $checkValues = true;
                                        for ($keyIndex = 0; $keyIndex < count($FValues); $keyIndex++) { 
                                            if (array_key_exists($FValues[$keyIndex], $calculatedFormulas)) {
                                                // $checkValues = true;
                                            } else {
                                                // $checkValues = false;
                                                $calculatedFormulas[$FValues[$keyIndex]] = 0;
                                            }
                                        }

                                        if($checkValues) { // calculate if values and formula is same
                                            $totalFormulaString2 = strtr($totalFormulaString, $calculatedFormulas);
                                            if($totalFormulaString2 != '') {
                                                $tFSResult = round(eval("return $totalFormulaString2;"));
                                            }
                                        } else {
                                            $tFSResult = 0;
                                        }

                                    } catch (Exception $e) {
                                        return response()->json('POS-7: '.$e->getMessage());
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

                                        try {
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
                                        } catch (Exception $e) {
                                            return response()->json('POS-3: '.$e->getMessage());
                                        }

                                        if($QValueFound) {
                                            // get the values with questionnaire index
                                            for ($dataIndex = 0; $dataIndex < count($data); $dataIndex++) { 
                                                try {
                                                    if(isset($data[$dataIndex])) {
                                                        $item = $data[$dataIndex];
                                                        if(isset($item['qIndex'])) {
                                                            if (in_array($item['qIndex'], $QValues)) { // check formula values does exists in data
                                                                $q = Qq::find($item['qId']);
                                                                if($q) {
                                                                    if($q->qAnsInputType->input != 'text') {
                                                                        $qa = Qa::find($item['ansId']);
                                                                        if($qa) {
                                                                            $qFIDs[] = [
                                                                                'qIndex' => $item['qIndex'],
                                                                                'qId' => $item['qId'],
                                                                                'ansId' => $qa->unit_price != null ? $qa->unit_price:0
                                                                            ];
                                                                        }
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
                                                } catch (Exception $e) {
                                                    return response()->json('POS-4: '.$e->getMessage());
                                                }
                                            }
            
                                            // if formula values exists in data
                                            if(count($qFIDs) > 0) {
                                                // replace Q* with values
                                                try {
                                                    array_reduce($qFIDs, function ($carry, $item) use (&$formulaString) {
                                                        if (strpos($formulaString, $item['qIndex']) !== false) {
                                                            if($item['ansId'] == null || $item['ansId'] == '') {
                                                                $item['ansId'] = 0;
                                                            }
                                                            $formulaString = str_replace($item['qIndex'], $item['ansId'], $formulaString);
                                                        }
                                                        return $carry;
                                                    });
                
                                                    // calculate formula
                                                    $formulaString = str_replace('x', '*', $formulaString);
                                                    $fSResult = eval("return $formulaString;");

                                                } catch (Exception $e) {
                                                    return response()->json('POS-5: '.$e->getMessage());
                                                }


            
                                                // if have conditions
                                                if(count($qfs[$fIndex]->quotationFormulaConditions) > 0) {
                                                    $qFCResult = null;
                                                    // loop quotation formula conditions
                                                    try {
                                                        for ($qfcIndex = 0; $qfcIndex < count($qfs[$fIndex]->quotationFormulaConditions); $qfcIndex++) { 
                                                            // set quotation formula condition
                                                            $qFC = $qfs[$fIndex]->quotationFormulaConditions[$qfcIndex];
                                                            if($qFC != null && $qFC->mathSymbol != null && $fSResult != null && isset($qFc->situation) && $qFC->situation != null) {
                                                                $qFCString = $fSResult.' '.$qFC->mathSymbol->sign.' '.$qFC->situation;
                                                                $qFCStringResult = eval("return $qFCString;");
                                                                if($qFCStringResult) { // check the forumla result condition
                                                                    $qFCResult = $qFC->result;
                                                                    break;
                                                                } else {
                                                                    $qFCResult = $fSResult;
                                                                }
                                                            }
                                                        }
                                                    } catch (Exception $e) {
                                                        return response()->json('POS-6: '.$e->getMessage());
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


                                /*--------------------  TOTAL FORMULA CALCULATION -----------------*/
                                // check the formula result and total formula and calculate them
                                if(count($calculatedFormulas) > 0 && $quotation->formula_total != null) {
                                    // if total formula was defined
                                    try {
                                        $totalFormulaString = $quotation->formula_total;
                                        preg_match_all('/F\d+/', $totalFormulaString, $matches);
                                        $FValues = $matches[0];
                                        $checkValues = true;
                                        for ($keyIndex = 0; $keyIndex < count($FValues); $keyIndex++) { 
                                            if (array_key_exists($FValues[$keyIndex], $calculatedFormulas)) {
                                                // $checkValues = true;
                                            } else {
                                                // $checkValues = false;
                                                $calculatedFormulas[$FValues[$keyIndex]] = 0;
                                            }
                                        }

                                        if($checkValues) { // calculate if values and formula is same
                                            $totalFormulaString2 = strtr($totalFormulaString, $calculatedFormulas);
                                            if($totalFormulaString2 != null) {
                                                $tFSResult = round(eval("return $totalFormulaString2;"));
                                            }
                                        } else {
                                            $tFSResult = 0;
                                        }

                                    } catch (Exception $e) {
                                        return response()->json('POS-7: '.$e->getMessage());
                                    }
                                } else if(count($calculatedFormulas) > 0) {
                                    $tFSResult = array_sum($FValues);
                                }
                        
                    }

    
                    /*--------------------  FINAL CALCULATION -----------------*/
                    // final calculation
                    try {
                        if($quotation->q_name == '水盛遣方(みずもりやりかた）'){
                            array_push($finalCalculatedQuotes, [
                                'quotation_id' => $quotation->id,
                                'quantity' => 1,
                                'unit_price' => round($quotation->base_amount),
                                'amount' => round($quotation->base_amount),
                                'inquiry_id' => $inquiry->id,
                                'created_at' => $currentTimestamp,
                                'updated_at' => $currentTimestamp
                            ]);
                            $total += $quotation->base_amount;
                        } else if($tFSResult > 0 && $quotation->base_amount != null) {
                            array_push($finalCalculatedQuotes, [
                                'quotation_id' => $quotation->id,
                                'quantity' => round($tFSResult),
                                'unit_price' => $quotation->base_amount,
                                'amount' => round($quotation->base_amount * $tFSResult),
                                'inquiry_id' => $inquiry->id,
                                'created_at' => $currentTimestamp,
                                'updated_at' => $currentTimestamp
                            ]);
                            $total += round($quotation->base_amount * $tFSResult);
                        } else if($tFSResult > 0  && $quotation->base_amount == null) {
                            // array_push($finalCalculatedQuotes, [
                            //     'quotation_id' => $quotation->id,
                            //     'quantity' => round($tFSResult),
                            //     'unit_price' => 1,
                            //     'amount' => round($tFSResult),
                            //     'inquiry_id' => $inquiry->id,
                            //     'created_at' => $currentTimestamp,
                            //     'updated_at' => $currentTimestamp
                            // ]);
                            // $total += $tFSResult;
                        } else if($tFSResult > 0  && $quotation->base_amount != null) {
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
                            // array_push($finalCalculatedQuotes, [
                            //     'quotation_id' => $quotation->id,
                            //     'quantity' => 0,
                            //     'unit_price' => 0,
                            //     'amount' => 0,
                            //     'inquiry_id' => $inquiry->id,
                            //     'created_at' => $currentTimestamp,
                            //     'updated_at' => $currentTimestamp
                            // ]);
                            // $total += 0;
                        }
                
                    } catch (Exception $e) {
                        return response()->json('POS-8: '.$e->getMessage());
                    }

                }

                /*--------------------  INSERT DATA -----------------*/
                // store the request data 
                try {
                    $data = array_map(function ($item) use ($currentTimestamp, $inquiry) {
                        $item['q_index'] = $item['qIndex'];
                        $item['qq_id'] = $item['qId'];
    
                        if(!is_array($item['ansId'])) { // single select
                            // check the qa id
                            $c = Qa::where('id', $item['ansId'])->where('qq_id', $item['qq_id'])->first();
                            if($c) {
                                $item['qa_id'] = $item['ansId'];
                                $item['qa_value'] = null;
                            } else {
                                $item['qa_id'] = null;
                                $item['qa_value'] = $item['ansId'];
                            }
                        } else { // multi select
                            if(count($item['ansId']) > 0) {
                                for ($i=0; $i < count($item['ansId']); $i++) { 
                                    // check the qa id
                                    $c = Qa::where('id', $item['ansId'][$i])->where('qq_id', $item['qq_id'])->first();
                                    if($c) {
                                        $item['qa_id'] = $item['ansId'][$i];
                                        $item['qa_value'] = null;
                                    } else {
                                        $item['qa_id'] = null;
                                        $item['qa_value'] = $item['ansId'][$i];
                                    }
                                }
                            }
                        }
        
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
                } catch (Exception $e) {
                    return response()->json('POS-9: '.$e->getMessage());
                }
    
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
            $inq = Inquiry::with(['inquiryQuotes'])->where('uuid', $uuid)->first();

            // send mail
            $mailData = [
                'inquiry' => $inq,
                'subject' => '掲載完了しました！'
            ];

            try {
                $m = Mail::to($inq->email)->send(new InquiryThankYouEmail($mailData));
            } catch (\Exception $e) {
                return response()->json('POS-1: '.$e->getMessage());
            }

            // $mail = new InquiryThankYouEmail($mailData);
            // $mailContent = $mail->render();
            // $subject = '掲載完了しました！';
            // $recipientEmail = $inq->email;

            // $headers = "MIME-Version: 1.0" . "\r\n";
            // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            // $headers .= "From: 外構相場.com <info@gaiko-souba.net>" . "\r\n";

            // $m = mail($recipientEmail, $subject, $mailContent, $headers);

            return response()->json($inquiry);
        }

        return response()->json($inquiry);
    }

    public function accept(Request $request, $id)
    {
        $data = $request->all();
        $inquiry = Inquiry::find($id);
        if($inquiry) {
            $inquiry->user_id = $data['user_id'];
            $inquiry->save();
            if($inquiry->user) {

                try {
                    $mailData = [
                        'name' => $inquiry->user->name,
                        'company_name' => $inquiry->user->company_name,
                        'address01' => $inquiry->user->address01,
                        'address02' => $inquiry->user->address02,
                        'url' => $inquiry->user->url != null ? $inquiry->user->url:' ',
                        'subject' => 'お問い合わせ受付中！'
                    ];

                    try {
                        $m = Mail::to($inquiry->email)->send(new InquiryAcceptUserEmail($mailData));
                    } catch (\Exception $e) {
                        return response()->json('POS-1: '.$e->getMessage());
                    }
    
                    // send mail to user
                    // $mail = new InquiryAcceptUserEmail($mailData);
                    // $mailContent = $mail->render();
                    // $subject = 'お問い合わせ受付中！';
                    // $recipientEmail = $inquiry->email;
    
                    // $headers = "MIME-Version: 1.0" . "\r\n";
                    // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    // $headers .= "From: 外構相場.com <info@gaiko-souba.net>" . "\r\n";
    
                    // $m = mail($recipientEmail, $subject, $mailContent, $headers);
                } catch (\Exception $e) {
                    return response()->json($e->getMessage());
                }

                // send mail to contractor
                try {
                    $mailData2 = [
                        'inquiry' => $inquiry,
                        'subject' => 'お問い合わせ受付中！'
                    ];

                    try {
                        $m = Mail::to($inquiry->user->email)->send(new InquiryAcceptContractorEmail($mailData2));
                    } catch (\Exception $e) {
                        return response()->json('POS-1: '.$e->getMessage());
                    }

                    // $mail2 = new InquiryAcceptContractorEmail($mailData2);
                    // $mailContent2 = $mail2->render();
                    // $subject2 = 'お問い合わせ受付中！';
                    // $recipientEmail2 = $inquiry->user->email;
    
                    // $headers2 = "MIME-Version: 1.0" . "\r\n";
                    // $headers2 .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    // $headers2 .= "From: 外構相場.com <info@gaiko-souba.net>" . "\r\n";
    
                    // $m = mail($recipientEmail2, $subject2, $mailContent2, $headers2);
                } catch (\Exception $e) {
                    return response()->json($e->getMessage());
                }

                return response()->json(1);
            }
        }
        return response()->json(0);
    }
}
