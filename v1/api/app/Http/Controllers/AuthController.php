<?php

namespace App\Http\Controllers;

use App\Models\Cc;
use App\Models\Cct;
use App\Models\Area;
use App\Models\User;
use App\Mail\RegisterEmail;
use App\Models\PaymentInfo;
use Illuminate\Support\Str;
use App\Models\Construction;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{

    /**
     * Register
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $data = $request->all();

        // check email exists or not
        $emailUser = User::where('email', $data['email'])->first();
        if($emailUser) {
            return response()->json(-2);
        }
        // example card - 4444333322221111
        $cardData = [
            // 'aid' => 126030, // test account
            'aid' => 125562, // production account
            'rt' => 1,
            'cod' => '',
            'em' => $data['email'],
            'pn' => '',
            'iid' => $data['pId'],
            'tkn' => $data['tkn']
        ];

        try {
            $response = Http::get('https://credit.j-payment.co.jp/gateway/gateway_token.aspx', $cardData);
        } catch (\Exception $e) {
            return response()->json('POS1 - '.$e->getMessage());
        }

        $responseString = (string) $response->getBody();
        $cleanedResponseString = str_replace("\r", "", $responseString);
        $arrayResponse = explode(",", $cleanedResponseString);
    
        if ( count($arrayResponse) > 0 && $arrayResponse[1] == 1 && $arrayResponse[3] == '') {
            // payment success

            // check the card type
            $cctID = '';
            try {
                $cct = Cct::whereRaw('LOWER(ccty) = ?', [Str::lower($data['card_type'])])->first();
            } catch (QueryException $e) {
                return response()->json('POS2 - '.$e->getMessage());
            }

            if($cct) {
                $cctID = $cct->id;
            }

            try {
                $masked = preg_replace('/(\d{4})(\d{4})(\d{4})(\d{4})$/', '****-****-****-$4', $data['card_number']);
            } catch (\Exception $e) {
                return response()->json('POS3 - '.$e->getMessage());
            }

            // store card info
            try {
                $cc = Cc::create([
                    'cn' => $masked,
                    'ed_month' => $data['expire_month'],
                    'ed_year' => $data['expire_year'],
                    'cct_id' => $cctID,
                    'cvv' => NULL,
                    'fn' => NULL,
                    'ln' => NULL
                ]);
            } catch (\Exception $e) {
                return response()->json('POS4 - '.$e->getMessage());
            }

            // store the payment result
            try {
                $paymentInfo = PaymentInfo::create([
                    'plan' => $data['plan'],
                    'price' => $data['price'], 
                    'gid' => isset($arrayResponse[0]) ? $arrayResponse[0]: '',
                    'rst' => isset($arrayResponse[1]) ? $arrayResponse[1]: '',
                    'ap' => isset($arrayResponse[2]) ? $arrayResponse[2]: '',
                    'ec' => isset($arrayResponse[3]) ? $arrayResponse[3]: '',
                    'god' => isset($arrayResponse[4]) ? $arrayResponse[4]: '',
                    'cod' => isset($arrayResponse[5]) ? $arrayResponse[5]: '',
                    'am' => isset($arrayResponse[6]) ? $arrayResponse[6]: '',
                    'tx' => isset($arrayResponse[7]) ? $arrayResponse[7]: '',
                    'sf' => isset($arrayResponse[8]) ? $arrayResponse[8]: '',
                    'ta' => isset($arrayResponse[9]) ? $arrayResponse[9]: '',
                    'issue_id' => isset($arrayResponse[10]) ? $arrayResponse[10]: '',
                    'ps' => isset($arrayResponse[11]) ? $arrayResponse[11]: '',
                    'acid' => isset($arrayResponse[12]) ? $arrayResponse[12]: '',
                    'product_code' => $data['pId']
                ]);
            } catch (\Exception $e) {
                return response()->json('POS5 - '.$e->getMessage());
            }

            // store user
            try {
                $userData = [
                    'name' => '',
                    'company_name' => $data['company_name'],
                    'tel' => $data['tel'],
                    'url' => $data['company_url'],
                    'address01' => $data['address01'],
                    'address02' => $data['address02'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'cc_id' => $cc->id,
                    'payment_method_id' => $this->getPaymentMethodIdByName($data['payment_method']),
                    'role_id' => 2,
                    'status' => 1,
                    'order' => 1,
                    'payment_info_id' => $paymentInfo->id
                ];

                if(isset($data['name'])) {
                    $userData['name'] = $data['name'];
                } else {
                    $userData['name'] = $data['lname'].' '.$data['fname'];
                }

                $user = User::create($userData);
            } catch (\Exception $e) {
                return response()->json('POS6 - '.$e->getMessage());
            }

            // store user_area and user_construction
            try {
                DB::table('area_user')->insert([
                    'area_id' => $this->getAreaIdByName($data['area']),
                    'user_id' => $user->id
                ]);
            } catch (\Exception $e) {
                return response()->json('POS7 - '.$e->getMessage());
            }

            if(isset($data['construction'])) {
                foreach ($data['construction'] as $con) {
                    $conId = $this->getConstructionIdByName($con);
            
                    if ($conId) {
                        try {
                            DB::table('construction_user')->insert([
                                'construction_id' => $conId,
                                'user_id' => $user->id,
                            ]);
                        } catch (\Exception $e) {
                            return response()->json('POS8 - '.$e->getMessage());
                        }
                    }
                }
            }

            // fetch mail data
            $mailSetting = MailSetting::where('mail', 'register')->first();
            $mailData = [
                'name' => $user->name,
                'password' => $data['password'],
                'plan' => $data['plan'],
                'price' => $data['price'],
                'subject' => $mailSetting->subject,
                'text' => $mailSetting->text
            ];

            try {
                $m = Mail::to($user->email)->send(new RegisterEmail($mailData));
            } catch (\Exception $e) {
                return response()->json('POS-1: '.$e->getMessage());
            }

            // $mail = new RegisterEmail($mailData);
            // $mailContent = $mail->render();
            // $subject = '登録が確認されました！';
            // $recipientEmail = $user->email;

            // $headers = "MIME-Version: 1.0" . "\r\n";
            // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            // $headers .= "From: 外構相場.com <info@gaiko-souba.net>" . "\r\n";

            // $m = mail($recipientEmail, $subject, $mailContent, $headers);

            return response()->json(1);

        } else {

            return response()->json($arrayResponse);
        }

        return response()->json(0);
    }

    /**
     * Sign in
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signin(Request $request)
    {
        $data = $request->all();
        if((isset($data['email']) && $data['email'] != '') && (isset($data['password']) && $data['password'] != '')) {
            $user = User::where('email', $data['email'])->first();
            if($user) {
                if (! $user || ! Hash::check($data['password'], $user->password)) {
                    return response()->json([
                        'error' => 'Invalid email or password'
                    ]);
                }

                if ($user->status != 1) {
                    return response()->json([
                        'error' => 'Invalid email or password'
                    ]);
                }

                $token = $user->createToken($user->name)->plainTextToken;
                return response()->json([
                    'user' => $user,
                    'token' => $token
                ]);
            } else {
                return response()->json([
                    'error' => 'Invalid email or password'
                ]);
            }
        } else {
            return response()->json([
                'response' => 0
            ]);
        }
    }

    /**
     * Sign in
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signincp(Request $request)
    {
        $data = $request->all();
        if((isset($data['email']) && $data['email'] != '') && (isset($data['password']) && $data['password'] != '')) {
            $user = User::where('email', $data['email'])->first();
            if($user) {
                if (! $user || ! Hash::check($data['password'], $user->password)) {
                    return response()->json([
                        'error' => 'Invalid email or password'
                    ]);
                }
                $token = $user->createToken($user->name)->plainTextToken;
                return response()->json([
                    'user' => $user,
                    'token' => $token
                ]);
            } else {
                return response()->json([
                    'error' => 'Invalid email or password'
                ]);
            }
        } else {
            return response()->json([
                'response' => 0
            ]);
        }
    }

    /**
     * Check Auth
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkauth(Request $request)
    {
        if(Auth::guard('sanctum')->check()) {
            $user = User::where('id', Auth::id())->with(['constructions', 'areas', 'paymentMethod', 'paymentInfo'])->first();
            return response()->json($user);
        } else {
            return response()->json(Auth::guard('sanctum')->check());
        }
        
    }

    /**
     * Sign Out
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signout(Request $request)
    {
        $data = $request->all();
        $user = User::find($data['id']);
        if($user) {
            $user->tokens()->delete();
            return response()->json(auth('sanctum')->check());
        } else {
            return response()->json(auth('sanctum')->check());
        }
        
    }

    public function getAreaIdByName($areaName)
    {
        $area = Area::where('name', $areaName)->first();

        if ($area) {
            return $area->id;
        }

        return null;
    }

    public function getConstructionIdByName($conName)
    {
        $con = Construction::where('name', $conName)->first();

        if ($con) {
            return $con->id;
        }

        return null;
    }

    public function getPaymentMethodIdByName($pmName)
    {
        $pm = PaymentMethod::where('name', $pmName)->first();

        if ($pm) {
            return $pm->id;
        }

        return null;
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
        $user = User::find($id);
        if($user) {
            // update user data
            $user->name = isset($data['name']) ? $data['name']:$user->name;
            $user->company_name = $data['company_name'];
            $user->address01 = $data['address01'];
            $user->address02 = $data['address02'];
            $user->tel = $data['tel'];
            if($data['email'] != $user->email) {
                $user->email = $data['email'];
            }
            $user->url = $data['siteurl'];            
            $user->save();

            // update user area
            $user->areas()->sync([$this->getAreaIdByName($data['area'])]);


            // Insert new constructions
            if (isset($data['construction'])) {
                // Remove existing constructions
                DB::table('construction_user')
                ->where('user_id', $user->id)
                ->delete();

                // update user constructions
                foreach ($data['construction'] as $con) {
                    $conId = $this->getConstructionIdByName($con);

                    if ($conId) {
                        try {
                            DB::table('construction_user')->insert([
                                'construction_id' => $conId,
                                'user_id' => $user->id,
                            ]);
                        } catch (\Exception $e) {
                            return response()->json(-1);
                        }
                    }
                }
            }

            return response()->json(1);
        } 
        return response()->json(0);
    }

    /**
     * get the card information
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getCard(Request $request, $id) {

        $user = User::where('id', $id)->with(['cc', 'cc.cct'])->first();
        return response()->json($user->cc);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCard(Request $request, $id)
    {
        $changeCardOnPaymentSuccess = false;
        $data = $request->all();
        $user = User::find($id);
        if($user) {
            // update card data
            if(isset($data['tkn'])) {

                // get the acid 
                $pInfo = $user->paymentInfo;
                if(isset($pInfo->acid) && $pInfo->acid != null) {
                    // example card - 4444333322221111
                    $cardData = [
                        'aid' => 126030,
                        'acid' => $pInfo->acid,
                        'cmd' => 1,
                        'tkn' => $data['tkn'],
                        'em' => $user->email,
                    ];

                    try {
                        $response = Http::get('https://credit.j-payment.co.jp/gateway/accgate_token.aspx', $cardData);
                    } catch (\Exception $e) {
                        return response()->json(-1);
                    }

                    $responseString = (string) $response->getBody();  
                    $cleanedResponseString = str_replace("\r", "", $responseString);   
                    if($cleanedResponseString == 'OK') {
                        $changeCardOnPaymentSuccess = true;
                    } else {
                        $changeCardOnPaymentSuccess = false;
                    }
                }

            } else {
                return response()->json(-1);
            }
            
            if($changeCardOnPaymentSuccess) {
                // check the card type
                $cctID = null;
                try {
                    $cct = Cct::whereRaw('LOWER(ccty) = ?', [Str::lower($data['card_type'])])->first();
                } catch (QueryException $e) {
                    return response()->json(-1);
                }

                if($cct) {
                    $cctID = $cct->id;
                }

                try {
                    $masked = preg_replace('/(\d{4})(\d{4})(\d{4})(\d{4})$/', '****-****-****-$4', $data['card_number']);
                } catch (\Exception $e) {
                    return response()->json(-1);
                }

                // store card info
                try {
                    $cc = Cc::where('id', $user->cc_id)->update([
                        'cn' => $masked,
                        'ed_month' => $data['expire_month'],
                        'ed_year' => $data['expire_year'],
                        'cct_id' => $cctID,
                        'cvv' => NULL,
                        'fn' => NULL,
                        'ln' => NULL
                    ]);
                } catch (\Exception $e) {
                    return response()->json(-1);
                }

                return response()->json(1);
            } else {
                return response()->json(-1);
            }
        } 
        return response()->json(-1);
    }
}
