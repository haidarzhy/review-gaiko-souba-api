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

    // LEAVING REASON API
    Route::get('/leaving-reason', 'App\Http\Controllers\LeavingReasonController@index');
    Route::post('/leaving-reason/store', 'App\Http\Controllers\LeavingReasonController@store');
    Route::put('/leaving-reason/{id}/update', 'App\Http\Controllers\LeavingReasonController@update');
    Route::delete('/leaving-reason/{id}/destroy', 'App\Http\Controllers\LeavingReasonController@destroy');

    // WITHDRAWAL API
    Route::get('/withdrawal', 'App\Http\Controllers\WithdrawalController@index');
    Route::post('/withdrawal/store', 'App\Http\Controllers\WithdrawalController@store');
    Route::get('/withdrawal/create', 'App\Http\Controllers\WithdrawalController@create');
    Route::put('/withdrawal/{id}/update', 'App\Http\Controllers\WithdrawalController@update');
    Route::get('/withdrawal/{id}/detail', 'App\Http\Controllers\WithdrawalController@show');
    Route::post('/withdrawal/{id}/confirm', 'App\Http\Controllers\WithdrawalController@confirm');


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
    Route::get('/questionnaire/get-last-qindex', 'App\Http\Controllers\QuestionnaireController@getLastQindex');
    Route::post('/questionnaire/store', 'App\Http\Controllers\QuestionnaireController@store');
    Route::get('/questionnaire/{id}/detail', 'App\Http\Controllers\QuestionnaireController@show');
    Route::post('/questionnaire/{id}/update', 'App\Http\Controllers\QuestionnaireController@update');
    Route::delete('/questionnaire/{id}/destroy', 'App\Http\Controllers\QuestionnaireController@destroy');

    // QUOTATION API
    Route::get('/quotation', 'App\Http\Controllers\QuotationController@index');
    Route::get('/quotation/create', 'App\Http\Controllers\QuotationController@create');
    Route::post('/quotation/store', 'App\Http\Controllers\QuotationController@store');
    Route::get('/quotation/{id}/detail', 'App\Http\Controllers\QuotationController@show');
    Route::post('/quotation/{id}/update', 'App\Http\Controllers\QuotationController@update');
    Route::delete('/quotation/{id}/destroy', 'App\Http\Controllers\QuotationController@destroy');

    // INQUIRY API
    Route::get('inquiry', 'App\Http\Controllers\InquiryController@index');
    Route::get('inquiry/get-all', 'App\Http\Controllers\InquiryController@getAll');
    Route::get('inquiry/{id}/detail', 'App\Http\Controllers\InquiryController@detail');
    Route::post('inquiry/{id}/accept', 'App\Http\Controllers\InquiryController@accept');

    // SETTING API
    Route::get('/settings', 'App\Http\Controllers\SettingController@index');
    Route::post('/settings/{id}/update', 'App\Http\Controllers\SettingController@update');
    Route::get('/settings/clear-cache', 'App\Http\Controllers\SettingController@clearCache');

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
Route::post('/u/questionnaires/store', 'App\Http\Controllers\QuestionnaireController@uStore');

// INQUIRY API
Route::post('/u/questionnaires/calculate', 'App\Http\Controllers\InquiryController@calculate');
Route::get('/u/inquiry/{uuid}/detail', 'App\Http\Controllers\InquiryController@show');
Route::post('/u/inquiry/{uuid}/update', 'App\Http\Controllers\InquiryController@update');


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

   return response()->json("Cache cleared successfully");
});



