<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
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

Route::get('api/calendarSync','CalendarSyncController@pboTeamMatches');

Route::get('agenda','CalendarSyncController@index');

Route::get('basisploegen','BasisPloegenController@index');

Route::get('basisploegen/clubPlayers','BasisPloegenController@clubPlayers');

Route::get('basisploegen/searchPlayer/{vblId}','BasisPloegenController@searchPlayer');

Route::post('basisploegen/saveTeams','BasisPloegenController@saveTeams');

Route::get('basisploegen/currentTeams','BasisPloegenController@currentTeams');

Auth::routes();
