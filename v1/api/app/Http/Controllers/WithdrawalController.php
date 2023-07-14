<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use App\Mail\WithdrawalEmail;
use App\Models\LeavingReason;
use Illuminate\Support\Facades\Mail;

class WithdrawalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ws = Withdrawal::orderBy('id', 'desc')->orderBy('id', 'desc')->get();
        $countedData = [];
        $foundConfirm = false;
        foreach ($ws as $withdrawal) {
            $key = 'w'.$withdrawal->user_id;

            if (!isset($countedData[$key])) {
                $countedData[$key] = [
                    'duplicated_count' => 0,
                    'inquiry_count' => 0,
                ];
                $foundConfirm = false;
            }

            if(isset($countedData[$key]) && $withdrawal->status == 0) {
                $countedData[$key]['withdrawal'] = $withdrawal;
                $foundConfirm = true;
            } 

            if($foundConfirm == false) {
                $countedData[$key]['withdrawal'] = $withdrawal;
            }

            $countedData[$key]['inquiry_count'] = count($withdrawal->user->inquiries);
            $countedData[$key]['duplicated_count']++;
        }

        $updatedWs = collect($countedData)->map(function ($data) {
            $withdrawal = $data['withdrawal'];
            $withdrawal->duplicated_count = $data['duplicated_count'];
            $withdrawal->inquiry_count = $data['inquiry_count'];
            return $withdrawal;
        });

        return response()->json($updatedWs);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $lrs = LeavingReason::orderBy('id', 'desc')->get();
        return response()->json($lrs);
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
        if(isset($data['user_id']) && $data['user_id'] != null) {
            $wData = [
                'company_name' => $data['company_name'],
                'email' => $data['email'],
                'month_to_withdrawl' => $data['month_withdraw'],
                'leaving_reason_id' => $data['reason_id'],
                'user_id' => $data['user_id'],
                'status' => 1,
                'order' => 1
            ];  
            $w = Withdrawal::create($wData);
            if($w) {

                // send mail
                $mailData = [
                    'user' => $w->user,
                    'reason' => $w->leaving_reason->name,
                    'diff_email' => '',
                    'r_email' => $w->email,
                    'subject' => '退会申請を受付ました!'
                ];


                if($w->email != $w->user->email) {
                    $mailData['diff_email'] = '退会依頼メールアドレスと登録メールアドレスが若干異なりますのでご確認ください。';
                    try {
                        $m = Mail::to($w->email)->send(new WithdrawalEmail($mailData));
                    } catch (\Exception $e) {
                        return response()->json('POS-1: '.$e->getMessage());
                    }
                }

                try {
                    $m = Mail::to($w->user->email)->send(new WithdrawalEmail($mailData));
                } catch (Exception $e) {
                    return response()->json('POS-2: '.$e->getMessage());
                }

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
        $w = Withdrawal::where('id', $id)->with(['leaving_reason'])->orderBy('id', 'desc')->first();
        if($w) {
            $otherWithdrawals = Withdrawal::where('user_id', $w->user_id)->with(['leaving_reason'])->where('id', '!=', $id)->orderBy('id', 'desc')->get();
            $w->other_withdrawals = $otherWithdrawals;
            return response()->json($w);
        } else {
            return response()->json(null);
        }
    }

    /**
     * Confirm the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function confirm($id)
    {
        $w = Withdrawal::where('id', $id)->first();
        if($w) {

            // // stop the payment
            // $cardData = [
            //     // 'aid' => 126030, // test account
            //     'aid' => 125562, // production account
            //     'gid' => 1,
            //     'rst' => '',
            //     'acid' => $data['email'],
            // ];

            // try {
            //     $response = Http::get('https://credit.j-payment.co.jp/gateway/acsgate.aspx', $cardData);
            // } catch (\Exception $e) {
            //     return response()->json('POS1 - '.$e->getMessage());
            // }
            
            return response()->json(1);
        } else {
            return response()->json(0);
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
