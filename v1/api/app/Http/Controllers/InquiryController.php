<?php

namespace App\Http\Controllers;

use Exception;
use ParseError;
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
                        $dIqs['area'] = $iq->area;
                        $dIqs['city'] = $iq->city;
                        $dIqs['address'] = $iq->address02;
                        $dIqs['total'] = $iq->total;
                        if(isset($iq->inquiryQaAns) && $iq->inquiryQaAns != null && count($iq->inquiryQaAns) > 0) {
                            $iqas = $iq->inquiryQaAns[0];
                            if(isset($iqas->qa)) {
                                $dIqs['area'] = $iqas->qa->label;
                            }
                        }
                        if((isset($dIqs['area']) && $userArea == $dIqs['area']) || $userArea == $iq->area) {
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
        $firstQuotation = Quotation::where('q_name', '水盛遣方(みずもりやりかた）')->first();
        if($firstQuotation) {
            array_unshift($combinedQuotationIds, $firstQuotation->id);
        }

        $quotations = Quotation::whereIn('id', $combinedQuotationIds)->get();

        // global variables
        $inquiry = null;
        $quotationStoreData = [];
        $quotationTotal = 0;

        // create a inquiry dummy
        $uuid = Str::uuid()->toString();
        $inquiry = Inquiry::create([
            'uuid' => $uuid,
            'confirm' => 0,
            'status' => 1,
            'order' => 1
        ]);
        
        // check quotation is not empty
        if(count($quotations) > 0) {
            // loop the quotations which were contained data
            for ($qIndex=0; $qIndex < count($quotations); $qIndex++) { 
                
                // set variables with short names
                $quotation = $quotations[$qIndex];
                $qcs = $quotation->quotationConditions;
                $qfs = $quotation->quotationFormulas;
                $qFTotal = $quotation->formula_total;
                $qBaseAmount = $quotation->base_amount;

                // global variables
                $conditionResult = false;
                $conditionResultArray = [];
                $formulaResultArray = [];
                $formulaConditionResult = false;
                $formulaTotalResult = 0;
                $quotationCalculationResult = 0;


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
                        $conditionAnsValue = $qc->qa_value;
                        $conditionAnsAny = $qc->qa_any;
                        $conditionMathSymbol = $qc->mathSymbol;

                        if(!array_key_exists($conditionKey, $conditionResultArray)) {
                            $conditionResultArray[$conditionKey] = [];
                        }

                        // filter the data, take only inlude question index from data
                        $filteredData = array_filter($data, function ($item) use ($conditionQIndex) {
                            return $item["qIndex"] === $conditionQIndex;
                        });

                        if($filteredData != null && count($filteredData) > 0) {

                            try {
                                // loop filtered data
                                foreach($filteredData as $fD) {

                                    if($conditionAnsId != null) { // normal answer
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

                                                    try {
                                                        $result = eval("return $conditionAsString;");
                                                    } catch (ParseError $e) {
                                                        return response()->json('POS - 1.0:'.$e->getMessage());
                                                    }
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

                                                try {
                                                    $result = eval("return $conditionAsString;");
                                                } catch (ParseError $e) {
                                                    return response()->json('POS - 1.0:'.$e->getMessage());
                                                }
                                                if(isset($conditionKey) && isset($conditionResultArray[$conditionKey])) {
                                                    $conditionResultArray[$conditionKey][] = $result;
                                                }

                                            }

                                        } else {

                                            $conditionResultArray[$conditionKey][] = false;

                                        }
                                    } else if($conditionAnsValue != null) { // text input
                                        // check all required fields
                                        if($conditionQqId != null && $conditionAnsValue != null && $conditionMathSymbol != null && isset($fD['qId']) && $fD['qId'] != null && isset($fD['ans']) && $fD['ans'] != null) {

                                            $conditionAsString = $fD['ans'].' '.$conditionMathSymbol->sign.' '.$conditionAnsValue;
                                            try {
                                                $result = eval("return $conditionAsString;");
                                            } catch (ParseError $e) {
                                                return response()->json('POS - 1.0:'.$e->getMessage());
                                            }
                                            if(isset($conditionKey) && isset($conditionResultArray[$conditionKey])) {
                                                $conditionResultArray[$conditionKey][] = $result;
                                            }

                                        } else {

                                            $conditionResultArray[$conditionKey][] = false;

                                        }
                                    } else if($conditionAnsAny == 1) { // any
                                        // check all required fields
                                        if($conditionQqId != null && $conditionAnsAny != null && $conditionMathSymbol != null && isset($fD['qId']) && $fD['qId'] != null && isset($fD['ansId']) && $fD['ansId'] != null) {

                                            $conditionResultArray[$conditionKey][] = true;

                                        } else {

                                            $conditionResultArray[$conditionKey][] = false;

                                        }
                                    }

                                }
                            } catch (Exception $e) {
                                return response()->json('POS - 1:'.$e->getMessage());
                            }

                        }

                    }

                }

                // check quotation condition
                if($quotation->condition != null) {

                    try {
                        $qCString = $quotation->condition;
                        $cCount = substr_count($qCString, "C");


                        
                        if(count($conditionResultArray) == $cCount) {
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

                            
                            
    
                            try {
                                $conditionResult = eval("return $replacedFormula;");
                            } catch (ParseError $e) {
                                return response()->json('POS - 1.1:'.$e->getMessage());
                            }

                        } else {
                            $conditionResult = false;
                        }
                    } catch (Exception $e) {
                        return response()->json('POS - 2:'.$e->getMessage());
                    }

                } else {
                    // set the condition resutl to true if there's no condition
                    $conditionResult = true;

                }


                // check the condition result
                // Formula Calculation
                if($conditionResult) {

                    // check quotation have formula or not
                    if(count($qfs) > 0) {

                        // loop the quotation foumula
                        for ($qFIndex=0; $qFIndex < count($qfs); $qFIndex++) { 
                            
                            $formulaRow = $qfs[$qFIndex];
                            $formulaString = $formulaRow->formula;
                            $formulaKey = $formulaRow->formula_total_id;
                            $formulaCondition = $formulaRow->quotationFormulaConditions;

                            if($formulaString != null && $formulaString != '') {

                                try {
                                    preg_match_all('/Q\d+/', $formulaString, $matches);
                                    $formulaQNumbers = array_unique($matches[0]);

                                    $filteredData = array_filter($data, function ($item) use ($formulaQNumbers) {
                                        return in_array($item['qIndex'], $formulaQNumbers);
                                    });
                                } catch (Exception $e) {
                                    return response()->json('POS - 3:'.$e->getMessage());
                                }
                                
                                // Convert the filtered result back to an indexed array
                                $filteredData = array_values($filteredData);

                                if($filteredData != null && count($filteredData) > 0) {

                                    for($filteredDataIndex = 0; $filteredDataIndex < count($filteredData); $filteredDataIndex++) {

                                        $fD = $filteredData[$filteredDataIndex];

                                        if(isset($fD['ansId']) && $fD['ansId'] != null) { // check if ansId is not null

                                            // answer input type are radio or checkbox
                                            if(is_array($fD['ansId'])) { // check ansId is array or not

                                                try {
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
                                                } catch (Exception $e) {
                                                    return response()->json('POS - 4:'.$e->getMessage());
                                                }
                                                
                                            } else if(is_numeric($fD['ansId'])) {

                                                try {
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
                                                } catch (Exception $e) {
                                                    return response()->json('POS - 5:'.$e->getMessage());
                                                }

                                            }

                                        } else if(isset($fD['ans']) && $fD['ans'] != null) { // check if ans is not null

                                            try {
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
                                            } catch (Exception $e) {
                                                return response()->json('POS - 6:'.$e->getMessage());
                                            }

                                        }

                                    }

                                    // check the formula string, all must numbers and signs
                                    if (preg_match('/[a-zA-Z]/', $formulaString)) { 

                                        $formulaResultArray[$formulaKey] = 0;

                                    } else {

                                        try {
                                            try {
                                                $formulaResult = round(eval("return $formulaString;"));
                                            } catch (ParseError $e) {
                                                return response()->json('POS - 1.2:'.$e->getMessage());
                                            }
                                            
                                            // check if have formula conditions
                                            if($formulaCondition != null && count($formulaCondition) > 0) {
                                                
                                                for ($fCIndex=0; $fCIndex < count($formulaCondition); $fCIndex++) { 
                                                    
                                                    $formulaConditionIndex = $formulaCondition[$fCIndex];
                                                    $fCMathSymbol = $formulaConditionIndex->mathSymbol;
                                                    $fCSituation = $formulaConditionIndex->situation;
                                                    $fCResult = $formulaConditionIndex->result;

                                                    if($fCMathSymbol != null && $fCSituation != null && $formulaResult != null) {

                                                        $formulaConditionString = $formulaResult.''.$fCMathSymbol->sign.''.$fCSituation;
                                                        try {
                                                            $formulaConditionResult = eval("return $formulaConditionString;");
                                                        } catch (ParseError $e) {
                                                            return response()->json('POS - 1.3:'.$e->getMessage());
                                                        }
                                                        if($formulaConditionResult) {
                                                            $formulaResultArray[$formulaKey] = $fCResult;
                                                            break;
                                                        } else {
                                                            $formulaResultArray[$formulaKey] = 0;
                                                        }

                                                    } else { 

                                                        $formulaResultArray[$formulaKey] = 0;

                                                    }
                                                }

                                            } else {
                                                $formulaResultArray[$formulaKey] = $formulaResult;
                                            }
                                        } catch (Exception $e) {
                                            return response()->json('POS - 7:'.$e->getMessage());
                                        }

                                    }

                                } else { // quotation have formula but required values does not have in data
                                    $formulaResultArray[$formulaKey] = 0;
                                }

                            }
    
                        }
    
                    } 

                }

                // check the condition result and check the formula result
                // Total formula calculation
                if($conditionResult && count($formulaResultArray) > 0 && $qFTotal != null) {

                    // Get the F* from total formula string
                    try {

                        // Check Q and F is exist or not
                        if (strpos($qFTotal, 'F') !== false && strpos($qFTotal, 'Q') !== false) { // Q and F


                            // replace F*
                            preg_match_all('/F\d+/', $qFTotal, $matches);
                            $formulaFNumbers = array_unique($matches[0]);
                            
                            foreach ($formulaFNumbers as $ffN) {
                                        
                                if(isset($formulaResultArray[$ffN])) {
                                    $qFTotal = str_replace($ffN, $formulaResultArray[$ffN], $qFTotal);
                                } else {
                                    $qFTotal = str_replace($ffN, 0, $qFTotal);
                                }

                            }




                            // replace Q*
                            preg_match_all('/Q\d+/', $qFTotal, $matches);
                            $formulaQNumbers = array_unique($matches[0]);


                            if($formulaQNumbers != null && count($formulaQNumbers) > 0) {

                                foreach ($formulaQNumbers as $fqN) {
                                    
                                    // filter the data, take only inlude question index from data
                                    $filteredData = array_filter($data, function ($item) use ($fqN) {
                                        return $item["qIndex"] === $fqN;
                                    });

                                    if($filteredData != null && count($filteredData) > 0) {

                                        foreach ($filteredData as $fdd) {

                                            if(isset($fdd['ansId']) && $fdd['ansId'] != null) {
                                                if(is_array($fdd['ansId'])) { // check ansId is array or not

                                                    for($i = 0; $i < count($fdd['ansId']); $i++) {
                                                        if(isset($fdd['ansId'][$i])) {
                                                            $qa = Qa::where('id', )->first();
                                                            if($qa && $qa->unit_price != null) {
                                                                $qFTotal = str_replace($fqN, $qa->unit_price, $qFTotal);
                                                            }
                                                        }
                                                    }

                                                } else {

                                                    $qa = Qa::where('id', $fdd['ansId'])->first();
                                                    if($qa && $qa->unit_price != null) {
                                                        $qFTotal = str_replace($fqN, $qa->unit_price, $qFTotal);
                                                    }

                                                }
                                            } else if(isset($fdd['ans']) && $fdd['ans'] != null) {
                                                $qFTotal = str_replace($fqN, $fdd['ans'], $qFTotal);
                                            }

                                            
                                        }

                                    }

                                }

                            }

                            // calculate total
                            if (preg_match('/[a-zA-Z]/', $qFTotal)) { 

                                $formulaTotalResult = 0;

                            } else {
                                try {
                                    $formulaTotalResult = eval("return $qFTotal;");
                                } catch (ParseError $e) {
                                    return response()->json('POS - 1.2.3:'.$e->getMessage());
                                }

                            }

                        } else {
                            
                            if(strpos($qFTotal, 'Q') !== false) { // Q

                                preg_match_all('/Q\d+/', $qFTotal, $matches);
                                $formulaQNumbers = array_unique($matches[0]);

                                if($formulaQNumbers != null && count($formulaQNumbers) > 0) {

                                    foreach ($formulaQNumbers as $fqN) {
                                        
                                        // filter the data, take only inlude question index from data
                                        $filteredData = array_filter($data, function ($item) use ($fqN) {
                                            return $item["qIndex"] === $fqN;
                                        });

                                        if($filteredData != null && count($filteredData) > 0) {

                                            foreach ($filteredData as $fdd) {

                                                if(isset($fdd['ansId']) && $fdd['ansId'] != null) {
                                                    if(is_array($fdd['ansId'])) { // check ansId is array or not

                                                        for($i = 0; $i < count($fdd['ansId']); $i++) {
                                                            if(isset($fdd['ansId'][$i])) {
                                                                $qa = Qa::where('id', )->first();
                                                                if($qa && $qa->unit_price != null) {
                                                                    $qFTotal = str_replace($fqN, $qa->unit_price, $qFTotal);
                                                                }
                                                            }
                                                        }
    
                                                    } else {
    
                                                        $qa = Qa::where('id', $fdd['ansId'])->first();
                                                        if($qa && $qa->unit_price != null) {
                                                            $qFTotal = str_replace($fqN, $qa->unit_price, $qFTotal);
                                                        }
    
                                                    }
                                                } else if(isset($fdd['ans']) && $fdd['ans'] != null) {
                                                    $qFTotal = str_replace($fqN, $fdd['ans'], $qFTotal);
                                                }

                                                
                                            }

                                        }

                                    }

                                    if (preg_match('/[a-zA-Z]/', $qFTotal)) { 

                                        $formulaTotalResult = 0;

                                    } else {
                                        try {
                                            $formulaTotalResult = eval("return $qFTotal;");
                                        } catch (ParseError $e) {
                                            return response()->json('POS - 1.2.5:'.$e->getMessage());
                                        }

                                    }

                                }

                            } else if(strpos($qFTotal, 'F') !== false) { // F

                                preg_match_all('/F\d+/', $qFTotal, $matches);
                                $formulaFNumbers = array_unique($matches[0]);

                                if($formulaFNumbers != null && count($formulaFNumbers) > 0) {

                                    foreach ($formulaFNumbers as $ffN) {
                                        
                                        if(isset($formulaResultArray[$ffN])) {
                                            $qFTotal = str_replace($ffN, $formulaResultArray[$ffN], $qFTotal);
                                        } else {
                                            $qFTotal = str_replace($ffN, 0, $qFTotal);
                                        }

                                    }

                                    if (preg_match('/[a-zA-Z]/', $qFTotal)) { 

                                        $formulaTotalResult = 0;

                                    } else {
                                        try {
                                            $formulaTotalResult = eval("return $qFTotal;");
                                        } catch (ParseError $e) {
                                            return response()->json('POS - 1.3:'.$e->getMessage());
                                        }

                                    }

                                } 

                            }

                        }

                    } catch (Exception $e) {
                        return response()->json('POS - 8:'.$e->getMessage());
                    }


                } else if($conditionResult && count($formulaResultArray) > 0 && $qFTotal == null) {
                    $dumpTotalResult = 0;
                    foreach ($formulaResultArray as $value) {
                        $dumpTotalResult += $value;
                    }
                    $formulaTotalResult = $dumpTotalResult;
                }

                // Calculate quotation total amount
                if($conditionResult) {
                    try {
                        if($qBaseAmount != null && $qBaseAmount > 0 && $formulaTotalResult > 0) { // check quotation have base_price or not

                            $quotationCalculationResult = round($qBaseAmount * $formulaTotalResult);
                            $quotationTotal += $quotationCalculationResult;
        
                        } else if($qBaseAmount != null && $qBaseAmount > 0 && ($qfs == null || count($qfs) == 0) ) {
        
                            $quotationCalculationResult = round($qBaseAmount * 1);
                            $quotationTotal += $quotationCalculationResult;
                        } else {
                            $quotationCalculationResult += 0;
                            $quotationTotal += 0;
                        }
                    } catch (Exception $e) {
                        return response()->json('POS - 9:'.$e->getMessage());
                    }
                }

                // create a inquiry if condition is true
                if($conditionResult) {
                    

                    // create a dump object to store
                    if($quotation->id == 1 || $qBaseAmount > 1) { // check the quotation is area or not

                        array_push($quotationStoreData, [
                            'quotation_id' => $quotation->id,
                            'quantity' => 1,
                            'unit_price' => $quotation->base_amount,
                            'amount' => $quotation->base_amount,
                            'inquiry_id' => $inquiry->id,
                            'created_at' => $currentTimestamp,
                            'updated_at' => $currentTimestamp
                        ]);

                    } else if($formulaTotalResult > 0 && $quotationCalculationResult > 0) {

                        array_push($quotationStoreData, [
                            'quotation_id' => $quotation->id,
                            'quantity' => $formulaTotalResult,
                            'unit_price' => $quotation->base_amount,
                            'amount' => $quotationCalculationResult,
                            'inquiry_id' => $inquiry->id,
                            'created_at' => $currentTimestamp,
                            'updated_at' => $currentTimestamp
                        ]);

                    } else if($quotationCalculationResult > 0){

                        array_push($quotationStoreData, [
                            'quotation_id' => $quotation->id,
                            'quantity' => 1,
                            'unit_price' => $quotation->base_amount,
                            'amount' => $quotationCalculationResult,
                            'inquiry_id' => $inquiry->id,
                            'created_at' => $currentTimestamp,
                            'updated_at' => $currentTimestamp
                        ]);

                    }
                }

            }

        }

        // store the datas
        if($inquiry != null && count($quotationStoreData) > 0) {

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
                            $item['qa_value'] = isset($item['ans']) ? $item['ans']:null;
                        }
                    } else { // multi select
                        if(count($item['ansId']) > 0) {
                            for ($i=0; $i < count($item['ansId']); $i++) { 
                                // check the qa id
                                $c = Qa::where('id', $item['ansId'][$i])->where('qq_id', $item['qq_id'])->first();
                                if($c) {
                                    $item['qa_id'] = $item['ansId'][$i];
                                    $item['qa_value'] = null;
                                } 
                            }
                        }
                    }
    
                    unset($item['qIndex']);
                    unset($item['qId']);
                    unset($item['ansId']);
                    unset($item['ans']);
    
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
                return response()->json($e->getMessage());
            }

            try {
                $inquiryQaAns = InquiryQaAns::insert($data);
            } catch (QueryException $e) {
                return response()->json($e->getMessage());
            }

            try {
                $inquiryQuote = InquiryQuote::insert($quotationStoreData);
            } catch (QueryException $e) {
                return response()->json($e->getMessage());
            }
            
            
            // update total
            try {
                $inquiry->total = $quotationTotal;
                $inquiry->save();
            } catch (QueryException $e) {
                return response()->json($e->getMessage());
            }

            // reset the global variables
            $quotationStoreData = [];
            $quotationTotal = 0;
    
            return response()->json($inquiry->uuid);

        } else if($inquiry != null && count($data) > 0) {
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
                            $item['qa_value'] = isset($item['ans']) ? $item['ans']:null;
                        }
                    } else { // multi select
                        if(count($item['ansId']) > 0) {
                            for ($i=0; $i < count($item['ansId']); $i++) { 
                                // check the qa id
                                $c = Qa::where('id', $item['ansId'][$i])->where('qq_id', $item['qq_id'])->first();
                                if($c) {
                                    $item['qa_id'] = $item['ansId'][$i];
                                    $item['qa_value'] = null;
                                } 
                            }
                        }
                    }
    
                    unset($item['qIndex']);
                    unset($item['qId']);
                    unset($item['ansId']);
                    unset($item['ans']);
    
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
                return response()->json($e->getMessage());
            }

            try {
                $inquiryQaAns = InquiryQaAns::insert($data);
            } catch (QueryException $e) {
                return response()->json($e->getMessage());
            }

            // update total
            try {
                $inquiry->total = $quotationTotal;
                $inquiry->save();
            } catch (QueryException $e) {
                return response()->json($e->getMessage());
            }

            // reset the global variables
            $quotationStoreData = [];
            $quotationTotal = 0;
    
            return response()->json($inquiry->uuid);
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
                'area' => $data['area'],
                'city' => $data['city'],
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
