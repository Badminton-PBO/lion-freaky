<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@index');

Route::get('home',[
    'as'=>'home',
    'uses' =>'WelcomeController@index'
]);

Route::get('opstelling', 'OpstellingsController@index');

Route::get('opstelling/clubAndTeams', 'OpstellingsController@clubAndTeams');

Route::get('opstelling/teamAndClubPlayers/{teamName}', 'OpstellingsController@teamAndClubPlayers');


Route::get('dbload/{doLoad}/{addTestClub}', 'DBLoadController@dbload');

Route::get('/stats/opstelling/{statType}','StatisticsController@statisticsOpstelling');

Route::get('/stats/opstelling','StatisticsController@opstelling');

Route::get('/stats/verplaatsing/{statType}','StatisticsController@statisticsVerplaatsing');

Route::get('/stats/verplaatsing','StatisticsController@verplaatsing');

Route::get('/logEvent/{eventType}/{who}','EventController@logEvent');

Route::get('verplaatsing', 'VerplaatsingsController@index');

Route::get('verplaatsing/clubAndTeams', 'VerplaatsingsController@clubAndTeams');

Route::get('verplaatsing/meetingAndMeetingChangeRequest/{clubName}/{teamName}', 'VerplaatsingsController@meetingAndMeetingChangeRequest');

Route::post('verplaatsing/saveMeetingChangeRequest', 'VerplaatsingsController@saveMeetingChangeRequest');

Route::get('verplaatsing/testMailGun', 'VerplaatsingsController@testMailGun');
Route::get('syncPBO', 'KalenderController@sync');


Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
