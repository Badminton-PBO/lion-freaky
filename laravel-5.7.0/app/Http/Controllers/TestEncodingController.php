<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 9/17/16
 * Time: 5:59 PM
 */

namespace App\Http\Controllers;

use DB;

class TestEncodingController extends Controller {
    public function dbload() {
        $FIXED_RANKING_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/2016-2017/test_indexen_spelers.csv';

        $ch = curl_init();
        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


        //Download Fixed Rankings (15 may) CSV
        curl_setopt($ch, CURLOPT_URL, $FIXED_RANKING_CSV_URL);
        $fixedRankingCSV = curl_exec($ch);



        $parsedCsv = DBLoadController::parse_csv($fixedRankingCSV,';',true,false);

        $headers = array_flip($parsedCsv[0]);

        print_r($parsedCsv);

        DB::statement("set names utf8");//set to windows encoding

        TestEncodingController::buildAndExecQuery($parsedCsv,
            'INSERT INTO lf_tmpdbload_15mei(clubName,playerId, firstName,lastName,gender,playerLevelSingle, playerLevelDouble, playerLevelMixed) VALUES ',
            array('Club','Lidnummer','Voornaam','Achternaam','Geslacht','Klassement enkel','Klassement dubbel','Klassement gemengd')
        );

    }

    static function buildAndExecQuery($parsedCsv, $queryStart,$columnsToSelect,$qPreparedRecord = "")
    {
        //2014/12/04 For import performance reasons, its a lot faster to import using a big insert querie(s) than one by one.
        //$query = "INSERT INTO lf_tmpdbload_15mei(playerId, playerLevelSingle, playerLevelDouble, playerLevelMixed) VALUES "; //Prequery
        //$columnsToSelect = array('Lidnummer','Klassement enkel','Klassement dubbel','Klassement gemengd');

        $headers = array_flip($parsedCsv[0]);

        //Build up all prepared values (?,?,?,?,...) , (?,?,?,?,...),...
        if (empty($qPreparedRecord)) {
            $qPreparedRecord = '(' . implode(",", array_fill(0, count($columnsToSelect), "?")) . ')';
        }
        $startRecord = 1;
        $maxRecordsAtOnce = 1000;
        while ($startRecord <= count($parsedCsv) - 2) {
            $query = $queryStart;
            $endRecord = min($startRecord + $maxRecordsAtOnce - 1, count($parsedCsv) - 2);
            $numberOfRecords = min($endRecord - $startRecord + 1, $maxRecordsAtOnce);

            $qPreparedRecords = array_fill(0, $numberOfRecords, $qPreparedRecord);
            $query .= implode(",", $qPreparedRecords);

            //Build up all bind parameters
            $bindParams = array();
            for ($i = $startRecord; $i <= $endRecord; $i++) {
                for ($j = 0, $numberOfColumns = count($columnsToSelect); $j < $numberOfColumns; ++$j) {
                    $bindParams[] = $parsedCsv[$i][$headers[$columnsToSelect[$j]]];
                }
            }

            DB::insert($query, $bindParams);

            $startRecord = $endRecord + 1;
        }
    }

}