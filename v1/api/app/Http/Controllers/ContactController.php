<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use App\Models\Contact;
use App\Mail\ContactEmail;
use App\Models\MailSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contacts = Contact::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                  ->from('contacts')
                  ->groupBy('email');
        })
        ->orderBy('id', 'desc')
        ->get();
        return response()->json($contacts);

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

        // site URL
        $siteURL = $request->url();
        

        // Get the user's IP address from the request
        $ip = $request->ip();
        if($ip == null) {
            $ip = '103.135.217.240';
        }
        // Use an IP geolocation service to get the location information
        $client = new Client();
        $response = $client->get('http://ip-api.com/json/' . $ip);
        $locationData = json_decode($response->getBody(), true);

        // check user is exists or not
        $user = User::where('email', $data['email'])->first();

        $contactData = [
            'name' => $data['fullname'] != null ? $data['fullname'] : $data['fname'].' '.$data['lname'],
            'kana_name' => $data['kata_fullname'] != null ? $data['kata_fullname'] : $data['kata_fname'].' '.$data['kata_lname'],
            'company_name' => $data['company_name'],
            'tel' => $data['tel'],
            'email' => $data['email'],
            'address01' => $data['address01'],
            'address02' => $data['address02'],
            'content' => $data['content'],
            'site' => $siteURL,
            'ip' => $ip,
            'status' => 1,
            'order' => 1,
            'new' => 1
        ];

        if($user) {
            $contactData['user_id'] = $user->id;
        }

        if($locationData['status'] == 'success') {
            $contactData['lat'] = $locationData['lat'];
            $contactData['lon'] = $locationData['lon'];
            $contactData['country'] = $locationData['country'];
            $contactData['regionName'] = $locationData['regionName'];
            $contactData['city'] = $locationData['city'];
            $contactData['mobile'] = isset($locationData['mobile']) ? $locationData['mobile']:0;
        }

        $contact = Contact::create($contactData);
        if($contact) {
            // fetch mail data
            $mailSetting = MailSetting::where('mail', 'contact')->first();
            $mailData = [
                'name' => $contact->name,
                'subject' => $mailSetting->subject,
                'text' => $mailSetting->text
            ];

            try {
                $m = Mail::to($contact->email)->send(new ContactEmail($mailData));
            } catch (\Exception $e) {
                return response()->json('POS-1: '.$e->getMessage());
            }

            // $mail = new ContactEmail($mailData);
            // $mailContent = $mail->render();
            // $subject = 'ご連絡いただきありがとうございます';
            // $recipientEmail = $contact->email;

            // $headers = "MIME-Version: 1.0" . "\r\n";
            // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            // $headers .= "From: 外構相場.com <info@gaiko-souba.net>" . "\r\n";

            // try {
            //     $m = mail($recipientEmail, $subject, $mailContent, $headers);

            // } catch (\Exception $e) {
            //     return response()->json('POS-1: '.$e->getMessage());
            // }
            return response()->json(1);
        } else {
            return response()->json(0);
        }

        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $contact = Contact::find($id);
        if($contact) {
            $contacts = Contact::where('email', $contact->email)->get();
            if($contacts) {
                return response()->json($contacts);
            } else {
                return response()->json($contact);
            }
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
        $contact = Contact::find($id);
        if($contact) {
            $contact->new = 0;
            $contact->save();
            return response()->json(1);
        }
        return response()->json(0);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $contact = Contact::find($id);
        if($contact) {
            $contacts = Contact::where('email', $contact->email)->delete();
            if($contacts) {
                return response()->json(1);
            } else {
                return response()->json(0);
            }
        } else {
            return response()->json(0);
        }
    }
}
