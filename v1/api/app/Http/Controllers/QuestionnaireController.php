<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Qa;
use App\Models\Qq;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\QAnsInputType;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class QuestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $qs = Qq::where('status', 1)->with(['qAnsInputType'])->orderBy('id', 'desc')->get();
        return response()->json($qs);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function uIndex()
    {
        $qs = Qq::where('status', 1)->with(['qAnsInputType', 'qasWithAll'])->orderBy('id', 'asc')->get();
        if($qs && count($qs) > 0) {
            $qsWithIndex = $qs->map(function ($item, $index) {
                $item['index'] = $index + 1; // Add the index column
                return $item;
            });
            return response()->json($qsWithIndex);
        }
        return response()->json($qs);
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
        return response()->json($this->createQuestionnaire($request, 'store', null));

    }

    public function uCalculate(Request $request)
    {
        $total = 0;
        $calculatedArray = [];
        $data = $request->all();
        if(count($data) > 0) {
            for ($i = 0; $i < count($data); $i++) { 
                // $q = Qq::join('Qa', 'Qq.id', '=', 'Qa.qId')
                //     ->where('Qq.id', $data[$i]['qId'])
                //     ->where('Qa.id', $data[$i]['ansId'])
                //     ->first();
                $q = Qq::where('id', $data[$i]['qId'])->first();
                if($q && $q->qAnsInputType->input == 'text') {
                    if(isset($q->qas[0])) {
                        $dumpTextObj = null;
                        if(isset($q->qas[0]->measure)) {
                            $dumpTextObj = [
                                'summary' => $q->q,
                                'quantity' => $data[$i]['ansId'],
                                'unit_price' => 0,
                                'amount' => 0
                            ];
                            // measure input
                            if($q->qas[0]->amount != null) {
                                // amount defined
                                $dumpTextObj['unit_price'] = $q->qas[0]->amount;
                                $dumpTextObj['amount'] = $data[$i]['ansId'] * $q->qas[0]->amount;
                                $total += $dumpTextObj['amount'];
                            }
                        } else {
                            $dumpTextObj = [
                                'summary' => $data[$i]['ansId'],
                                'quantity' => 0,
                                'unit_price' => 0,
                                'amount' => 0
                            ];
                            // free input
                            if($q->qas[0]->amount != null) {
                                // amount defined
                                $total += $q->qas[0]->amount;
                            }
                        }
                        array_push($calculatedArray, $dumpTextObj);
                    }
                } else if($q && $q->qAnsInputType->input == 'select') {
                    if(isset($q->qas[0])) {
                        $dumpSelectObj = null;
                        if($q->qas[0]->amount != null) {
                            // amount defined
                            $dumpTextObj = [
                                'summary' => $q->q,
                                'quantity' => $q->qas[0]->label,
                                'unit_price' => '-',
                                'amount' => $q->qas[0]->amount
                            ];
                        }
                    }
                } else if($q && $q->qAnsInputType->input == 'radio') {

                } else if($q && $q->qAnsInputType->input == 'checkbox') {

                }
                // if(is_array($data[$i]['ansId'])) {
                //     // multiple answers
                //     for ($j = 0; $j < count($data[$i]['ansId']); $j++) { 
                //         $ans = Qa::where('id', $data[$i]['ansId'][$j])->first();
                //         if($ans) {
                //             return response()->json([
                //                 'ans' => $ans,
                //                 'q' => $ans->qq
                //             ]);
                //         }
                //     }
                // } else {
                //     // single answer
                //     $ans = Qa::where('id', $data[$i]['ansId'])->first();
                //     if($ans) {
                //         return response()->json([
                //             'ans' => $ans,
                //             'q' => $ans->qq
                //         ]);
                //     } else {
                //         // not found answer might be input text

                //     }
                // }
            }
        }
        return response()->json([
            'array' => $calculatedArray,
            'total' => $total
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uStore(Request $request)
    {
        $data = $request->all();
        if(isset($data['textInputValues']) && count($data['textInputValues']) > 0) {
            // text answers were including in answers

        } else if(isset($data['selectInputValues']) && count($data['selectInputValues']) > 0) {
            // select answers were including in answers
            foreach($data['selectInputValues'] as $sInput) {

            }
        } else if(isset($data['choiceInputValues']) && count($data['choiceInputValues']) > 0) {
            // choice answers were including in answers
            
        } else {
            return response()->json(0);
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
        $q = Qq::where('id', $id)->with(['qAnsInputType', 'qasWithAll'])->first();
        if($q) {
            return response()->json($q);
        } else {
            return response()->json(null);
        }
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
        $q = Qq::find($id);

        if($q) {
            if(isset($q->qas) && $q->qas != null) {
                // destroy old answers with no image
                $q->qas()->delete();
            }

            // create new answers
            $res = $this->createQuestionnaire($request, 'update', $id);
            return response()->json($res);
            
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
        $q = Qq::find($id);
        if($q) {
            if(isset($q->qas) && $q->qas != null) {
                foreach ($q->qas as $qa) {
                    if($qa->image != null) {
                        // Get the image path
                        $imagePath = public_path(preg_replace("/public\//", "", $qa->image, 1));
                        // Delete the image file
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                }
            }

            $q->delete();
            return response()->json(1);
        } else {
            return response()->json(0);
        }
    }

    public function createQuestionnaire($request, $mode, $QID) {

        $currentTimestamp = Carbon::now();
        $data = $request->all();

        if(isset($data['inputType'])) {
            $inputType = QAnsInputType::where('type', $data['inputType'])->first();
            $qq = false;
            if($mode == 'store') {
                // store question
                $qq = Qq::create([
                    'q' => $data['question'],
                    'prefix' => isset($data['prefix']) ? $data['prefix']:null,
                    'q_ans_input_type_id' => $inputType->id,
                    'status' => 1,
                    'order' => 1
                ]);

                if(!$qq) {
                    return 0;
                }
            } else if($mode == 'update') {
                // update question
                $qRes = Qq::where('id', $QID)->update([
                    'q' => $data['question'],
                    'prefix' => isset($data['prefix']) ? $data['prefix']:null,
                    'q_ans_input_type_id' => $inputType->id,
                ]);
                if($qRes) {
                    $qq = Qq::find($QID);
                } else {
                    return 0;
                }
            }
            
            if($qq) {
                if($inputType && $inputType->input == 'text') {
                    // input text
                    $dumpInputTextData = [
                        'qq_id' => $qq->id,
                        'amount' => isset($data['textItems']['amount']) ? $data['textItems']['amount']:null,
                        'status' => 1,
                        'order' => 1
                    ];

                    if($data['textItems']['textType'] == '自由入力') {
                        $dumpInputTextData['suffix'] = isset($data['textItems']['label']) ? $data['textItems']['label']:null;
                    } else if($data['textItems']['textType'] == '対策') {
                        if(isset($data['textItems']['measure'])) {
                            $measure = Measure::where('type', $data['textItems']['measure'])->first();
                            if($measure) {
                                $dumpInputTextData['suffix'] = $measure->type;
                                $dumpInputTextData['measure_id'] = $measure->id;
                            } else {
                                return 0;
                            }
                        }
                    } else {
                        return 0;
                    }
    
                    // store answer
                    $sa = Qa::create($dumpInputTextData);
    
                    if($sa) {
                        return 1;
                    } else {
                        return 0;
                    }
                    
                } else if($inputType && $inputType->input == 'select') {
                    // input select
                    if(isset($data['selectItems']) && count($data['selectItems']) > 0) {
                        $dumpInputSelectData = [];
                        foreach($data['selectItems'] as $si) {
                            if(isset($si['label'])) {
                                $dumpSi = [
                                    'label' => $si['label'],
                                    'amount' => isset($si['amount']) ? $si['amount']:null,
                                    'qq_id' => $qq->id,
                                    'status' => 1,
                                    'order' => 1,
                                    'created_at' => $currentTimestamp,
                                    'updated_at' => $currentTimestamp
                                ];
                                array_push($dumpInputSelectData, $dumpSi);
                            }
                        }


                        if(count($dumpInputSelectData) > 0) {
                            // store answer
                            $sa = Qa::insert($dumpInputSelectData);
                            if($sa) {
                                return 1;
                            } else {
                                return 0;
                            }
                        }

                    } else {
                        return 1;
                    }

                } else if($inputType && ($inputType->input == 'radio' || $inputType->input == 'checkbox') ) {
                    // input choice
                    if(isset($data['choiceItems']) && count($data['choiceItems']) > 0) {
                        $dumpInputChoiceData = [];
                        for ($i=0; $i < count($data['choiceItems']); $i++) { 

                            $ci = $data['choiceItems'][$i];
                            $dumpCi = [
                                'label' => isset($ci['label']) ? $ci['label']:null,
                                'amount' => isset($ci['amount']) ? $ci['amount']:null,
                                'qq_id' => $qq->id,
                                'status' => 1,
                                'order' => 1,
                                'created_at' => $currentTimestamp,
                                'updated_at' => $currentTimestamp
                            ];

                            // upload file if has file
                            $fileKey = 'choiceItems.'.$i.'.file';
                            if($request->hasFile($fileKey)) {
                                $file = $request->file($fileKey);
                                $path = 'questionnaire/ans/choices';

                                if (!Storage::exists($path)) {
                                    Storage::makeDirectory($path);
                                }

                                $currentDate = Carbon::now()->format('Ymd');
                                $fileExtension = $file->getClientOriginalExtension();
                                $newFileName = $currentDate .'-'.uniqid().'.' . $fileExtension;
                                $storedPath = Storage::disk('public')->putFileAs($path, $file, $newFileName);
                                $dumpCi['image'] = 'public/'.$storedPath;
                            } else if(isset($ci['imagePath'])) {
                                $pathImg = str_replace(url('/api').'/', '', $ci['imagePath']);
                                $dumpCi['image'] = $pathImg;
                            }

                            array_push($dumpInputChoiceData, $dumpCi);
                        }
                        
                        if(count($dumpInputChoiceData) > 0) {
                            // store answer
                            $sa = Qa::insert($dumpInputChoiceData);
                            if($sa) {
                                // delete unused images
                                $databaseImages = Qa::where('image', '!=', null)->pluck('image')->all();
                                $directory = public_path('/questionnaire/ans/choices');
                                $files = glob($directory . '/*');
                                foreach ($files as $file) {
                                    if (is_file($file) && !in_array('public'.str_replace(public_path(''), '', $file), $databaseImages)) {
                                        unlink($file); // Delete the image file
                                    }
                                }

                                // delete old records

                                return 1;
                            } else {
                                return 0;
                            }
                        } else {
                            return 1;
                        }

                    } else {
                        return 1;
                    }
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
}
