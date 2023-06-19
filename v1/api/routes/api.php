<?php

use App\Mail\RegisterEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Middleware\AuthenticateMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['auth:sanctum'])->group(function () {

    // PM API
    Route::post('/pm/store', 'App\Http\Controllers\PaymentMethodController@store');
    Route::put('/pm/{id}/update', 'App\Http\Controllers\PaymentMethodController@update');
    Route::delete('/pm/{id}/destroy', 'App\Http\Controllers\PaymentMethodController@destroy');
    
    // AREA API
    Route::post('/areas/store', 'App\Http\Controllers\AreaController@store');
    Route::put('/areas/{id}/update', 'App\Http\Controllers\AreaController@update');
    Route::delete('/areas/{id}/destroy', 'App\Http\Controllers\AreaController@destroy');

    // CONSTRUCTION API
    Route::post('/constructions/store', 'App\Http\Controllers\ConstructionController@store');
    Route::put('/constructions/{id}/update', 'App\Http\Controllers\ConstructionController@update');
    Route::delete('/constructions/{id}/destroy', 'App\Http\Controllers\ConstructionController@destroy');

    // CONTRACTOR API
    Route::get('/contractor', 'App\Http\Controllers\ContractorController@index');
    Route::get('/contractor/{id}/detail', 'App\Http\Controllers\ContractorController@show');
    Route::delete('/contractor/{id}/deactivate', 'App\Http\Controllers\ContractorController@destroy');

    // USER API
    Route::put('/user/{id}/update', 'App\Http\Controllers\AuthController@update');
    Route::get('/user/{id}/get-card', 'App\Http\Controllers\AuthController@getCard');
    Route::put('/user/{id}/update-card', 'App\Http\Controllers\AuthController@updateCard');

    // CONTACT API
    Route::get('/contact', 'App\Http\Controllers\ContactController@index');
    Route::get('/contact/{id}/detail', 'App\Http\Controllers\ContactController@show');
    Route::delete('/contact/{id}/destroy', 'App\Http\Controllers\ContactController@destroy');
    Route::put('/contact/{id}/update-new', 'App\Http\Controllers\ContactController@update');

    // QUESTIONNAIRE API
    Route::get('/questionnaire', 'App\Http\Controllers\QuestionnaireController@index');
    Route::post('/questionnaire/store', 'App\Http\Controllers\QuestionnaireController@store');
    Route::get('/questionnaire/{id}/detail', 'App\Http\Controllers\QuestionnaireController@show');
    Route::post('/questionnaire/{id}/update', 'App\Http\Controllers\QuestionnaireController@update');
    Route::delete('/questionnaire/{id}/destroy', 'App\Http\Controllers\QuestionnaireController@destroy');

    // SETTING API
    Route::get('/settings', 'App\Http\Controllers\SettingController@index');
    Route::post('/settings/{id}/update', 'App\Http\Controllers\SettingController@update');

    // DASHBOARD API
    Route::get('/dashboard', 'App\Http\Controllers\DashboardController@index');

});

// HEAD
Route::get('/head', 'App\Http\Controllers\SettingController@getHead');

// AUTH
Route::post('/register', 'App\Http\Controllers\AuthController@register');
Route::post('/cp/sign-in', 'App\Http\Controllers\AuthController@signincp');
Route::post('/sign-in', 'App\Http\Controllers\AuthController@signin');
Route::get('/check-auth', 'App\Http\Controllers\AuthController@checkauth');
Route::post('/sign-out', 'App\Http\Controllers\AuthController@signout');

// PM INDEX
Route::get('/pm', 'App\Http\Controllers\PaymentMethodController@index');
// AREA INDEX
Route::get('/areas', 'App\Http\Controllers\AreaController@index');
// CONSTRUCTION INDEX
Route::get('/constructions', 'App\Http\Controllers\ConstructionController@index');

// CONTACT FORM API
Route::post('/contact/store', 'App\Http\Controllers\ContactController@store');

// QUESTIONNAIRE API
Route::get('/u/questionnaires', 'App\Http\Controllers\QuestionnaireController@uIndex');
Route::post('/u/questionnaires/calculate', 'App\Http\Controllers\QuestionnaireController@uCalculate');
Route::post('/u/questionnaires/store', 'App\Http\Controllers\QuestionnaireController@uStore');


// Route::get('/test', function (Request $request) {
//     return Hash::make('mYgG#85y5KV@');
// });

Route::get('/test', function (Request $request) {

    // $username = 'John Doe';
    // $mailData = ['username' => $username];

    // $mail = new RegisterEmail($username);
    // $mailContent = $mail->render();
    // $subject = 'Example Subject';
    // $recipientEmail = 'kyawthantzamt@gmail.com';

    // $headers = "MIME-Version: 1.0" . "\r\n";
    // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    // $headers .= "From: 外構相場.com <info@gaiko-souba.net>" . "\r\n";

    // $m = mail($recipientEmail, $subject, $mailContent, $headers);

    // return $m;
    return config('app.upload_folder');
    // $path = 'setting/logo';
    //             try {
    //                 if (!Storage::exists($path)) {
    //                     Storage::makeDirectory($path);
    //                 }
    //             } catch (\Exception $e) {
    //                 return response()->json($e->getMessage());
    //             }
    // return !Storage::exists($path);
});

Route::get('/clear-cache', function (Request $request) {
    $cacheCommands = array(
        'event:clear',
        'view:clear',
        'cache:clear',
        'route:clear',
        'config:clear',
        'clear-compiled',
        'optimize:clear'
    );
    foreach ($cacheCommands as $command) {
        Artisan::call($command);
    }

   return "Cache cleared successfully";
});



