<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\OpstellingsController;
use App\Http\Controllers\DBLoadController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CalendarSyncController;
use App\Http\Controllers\BasisPloegenController;

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

Route::get('/', [WelcomeController::class ,'index']);

Route::get('home', [WelcomeController::class ,'index'])->name('home');

Route::get('opstelling', [OpstellingsController::class, 'index']);

Route::get('opstelling/clubAndTeams', [OpstellingsController::class, 'clubAndTeams']);

Route::get('opstelling/teamAndClubPlayers/{teamName}', [OpstellingsController::class, 'teamAndClubPlayers']);

Route::get('dbload/{doLoad}/{addTestClub}', [DBLoadController::class, 'dbload']);

Route::get('dbfreshness', [DBLoadController::class, 'dbfreshness']);

Route::get('/stats/opstelling/{statType}', [StatisticsController::class, 'statisticsOpstelling']);

Route::get('/stats/opstelling', [StatisticsController::class, 'opstelling']);

Route::get('/stats/verplaatsing/{statType}', [StatisticsController::class, 'statisticsVerplaatsing']);

Route::get('/stats/verplaatsing', [StatisticsController::class, 'verplaatsing']);

Route::get('/logEvent/{eventType}/{who}',[EventController::class, 'logEvent']);

Route::get('verplaatsing', [VerplaatsingsController::class, 'index']);

Route::get('verplaatsing/clubAndTeams', [VerplaatsingsController::class, 'clubAndTeams']);

Route::get('verplaatsing/meetingAndMeetingChangeRequest/{clubName}/{teamName}', [VerplaatsingsController::class, 'meetingAndMeetingChangeRequest']);

Route::post('verplaatsing/saveMeetingChangeRequest', [VerplaatsingsController::class, 'saveMeetingChangeRequest']);

Route::get('verplaatsing/testMailGun', [VerplaatsingsController::class, 'testMailGun']);

Route::get('api/calendarSync', [CalendarSyncController::class ,'pboTeamMatches']);

Route::get('agenda', [CalendarSyncController::class, 'index']);

Route::get('basisploegen', [BasisPloegenController::class, 'index']);

Route::get('basisploegen/clubPlayers', [BasisPloegenController::class, 'clubPlayers']);

Route::get('basisploegen/searchPlayer/{vblId}', [BasisPloegenController::class, 'searchPlayer']);

Route::post('basisploegen/saveTeams', [BasisPloegenController::class, 'saveTeams']);

Route::get('basisploegen/currentTeams', [BasisPloegenController::class, 'currentTeams']);

Auth::routes();