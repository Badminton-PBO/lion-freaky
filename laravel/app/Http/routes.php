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

Route::get('home', 'HomeController@index');

Route::get('opstelling', 'OpstellingsController@index');

Route::get('opstelling/clubAndTeams', 'OpstellingsController@clubAndTeams');

Route::get('opstelling/teamAndClubPlayers/{teamName}', 'OpstellingsController@teamAndClubPlayers');


Route::get('dbload/{doLoad}/{addTestClub}', 'DBLoadController@dbload');

Route::get('/statistic/{statType}','StatisticsController@statistic');

Route::get('/logEvent/{eventType}/{who}','EventController@logEvent');

Route::get('verplaatsing', 'VerplaatsingsController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
