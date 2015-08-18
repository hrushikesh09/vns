<?php
ini_set('max_execution_time', 300);

require '../vendor/autoload.php';
include '../functions/imageProcess.php';

$Stocks = AllStockCodes();
$resultspath = $_SERVER['DOCUMENT_ROOT'].'/VnsInvestments/img/results.json';

$f = @fopen($resultspath, "r+");

// check if results is empty. Empty if not.. and run program for data.
if ($f !== false) {
    ftruncate($f, 0);
    fclose($f);
}

    $fp = fopen($resultspath, 'w');

    foreach($Stocks as $StockCode) {

        $og_image_path = $_SERVER['DOCUMENT_ROOT'] . '/VnsInvestments/img/charts/' . $StockCode . '.gif';
        $ref_image_path = $_SERVER['DOCUMENT_ROOT'] . '/VnsInvestments/img/charts2/' . $StockCode . '.png';
        $hour_path = $_SERVER['DOCUMENT_ROOT'] . '/VnsInvestments/img/hour/' . $StockCode . '.png';
        $fifteen_path = $_SERVER['DOCUMENT_ROOT'] . '/VnsInvestments/img/fifteen/' . $StockCode . '.png';
        $five_path = $_SERVER['DOCUMENT_ROOT'] . '/VnsInvestments/img/five/' . $StockCode . '.png';

        storeImages($og_image_path,$ref_image_path,$hour_path,$fifteen_path,$five_path);

        $hour = array();
        $fifteen = array();
        $five = array();

        $hour = getHourArray($og_image_path,$hour_path,'hour');
        $fifteen = getFifteenArray($og_image_path,$fifteen_path,'fifteen');
        $five = getFiveArray($og_image_path,$five_path,'five');

        $StocksController = array($hour,$fifteen,$five);

         foreach ($StocksController as $Stock) {
         
         fwrite($fp, json_encode($Stock, JSON_FORCE_OBJECT));
         
        }
    }

    fclose($fp);


   