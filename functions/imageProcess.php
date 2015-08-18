<?php

// EXTRACT TEXT FROM IMAGE : only works for this project.

/*Developer: Hrushikesh Shinde
 * 
 */

//Make image readable by ocr
function refineimage($og_image_path,$ref_image_path) {

    $img = new imagick($og_image_path);
    $img -> adaptiveresizeimage(1600, 1200);
    list($width, $height) = array_values($img -> getImageGeometry());
    $img -> modulateImage(105, 0, 100);
    $img -> contrastStretchImage($width * $height * 0.90, $width * $height * 0.95);
    $img -> setimagecolorspace(Imagick::COLORSPACE_GRAY);
    $img -> blackThresholdImage("grey");
    $img -> adaptiveblurimage(0.2, 0.3);
    
    //$img->sharpenimage(1, 0.5);
    //$img -> cropimage(1026,611,0,579);
    $img -> setimageformat("png");

    file_put_contents($ref_image_path, $img);
    $img -> writeimagefile(fopen($ref_image_path, 'wb'));    
}

// Crop n save image
function cropnsaveimage($ref_image_path,$save_path,$width,$height,$x,$y){
    $img = new imagick($ref_image_path);
    $img-> cropimage($width,$height,$x,$y);

    file_put_contents($save_path, $img);
}

//perform ocr
function extracttextfromimage($img_path){
    if (file_exists($img_path)) {
    $img2 = $img_path;
    $tesseract = new TesseractOCR($img2);
    $tesseract -> setWhitelist(range('A','Z'),range('a','z'),range(0, 9), '-@.():');
    $data = $tesseract -> recognize();
    return $data;
    }
    else{
          return null;
}
}

//final array of quotes
function getvaluesforeach($og_image_path,$array,$type){
    $time ='';
    $Signal_value = '';
    $Signal_type = '';
    $trailing_sl = '';
    $sl_bracket = '';
    $current_pl = '';
    $StockCode = basename($og_image_path,'.gif');

foreach ($array as $key => $Signal) {
    switch ($key) {
        case 0:
            $Signal_type = $Signal;
            //echo $Signal_type.'<br>';
            //$Signal = null;
            break;
        case 1:
            $time = str_replace(array(' ','came','Came'), '', $Signal);
            //echo $time.'<br>';
            //$Signal = null;
            break;
        case 2: 
            $Signal_value = str_replace(array('BUY','SELL',' ','@',':'), '', $Signal);
            //echo $Signal_value.'<br>';
            //$Signal = null;
            break;
        case 3:
            $trailing_sl = str_replace(array('SL',' ',':'), '', $Signal);
            //echo $trailing_sl.'<br>';
            //$Signal = null;
            break;
        case 4:
            $sl_bracket = str_replace(array(" ","(",")"), '', $Signal);
            //echo $sl_bracket.'<br>';
            //$Signal = null;
            break;
        case 5:
            $current_pl = str_replace(array('P/L',' ','P',':','7'), '', $Signal);
            //echo $current_pl.'<br>';
            //$Signal = null;
            break;
        default:
                  
            break;

    }
       
}
    $array1 = array(
        'type'  => $type,
        'StockCode' => $StockCode,
        'SignalType'  => $Signal_type,
        'SignalValue'  => $Signal_value,
        'TimeAgo'   => $time,
        'TrailingSl' => $trailing_sl,
        'TrailingBk'=> $sl_bracket,
        'CurrentPl' => $current_pl
    );
 
    return $array1;
}

function getSignal($text){
    $text = str_replace("WL", "P/L", $text);
    $text = str_replace("PrL", "P/L", $text);
    $text = str_replace("-S", "-5", $text);
    //echo $text.'<hr>';
    
    preg_match('/BUY|SELL/',$text, $matches); // Signal type
    $temp[0] = array_shift($matches);
    preg_match('/came\s*\d{1,9}/',$text, $matches); //time ago
    $temp[1] = array_shift($matches);
    preg_match('/\s(\w\w\w|\w\w\w\w)\s*@\s*\W\s*-?[0-9]\d*(\.\d+)?/', $text, $matches); // Signal
    $temp[2] = array_shift($matches);
    preg_match('/SL\s*\W\s*-?[0-9]\d*(\.\d+)?/',$text, $matches); // tailing sl
    $temp[3] = array_shift($matches);
    preg_match('/\(([^)]+)\)/',$text,$matches); // trailing sl -> ( )
    $temp[4] = array_shift($matches);
    preg_match('/P\WL\s\W\s-?[0-9]\d*(\.\d+)?/',$text,$matches);
    $temp[5] = array_shift($matches);
    
    return $temp;
    //var_dump($temp);
}


//////****************EXTRACTION HERE*******************************************

//RefineImage
function storeImages($og_image_path,$ref_image_path,$hour_path,$fifteen_path,$five_path)
{
    refineimage($og_image_path, $ref_image_path);
    cropnsaveimage($ref_image_path, $hour_path, 515, 227, 506, 948);
    cropnsaveimage($ref_image_path, $fifteen_path, 497, 244, 0, 939);
    cropnsaveimage($ref_image_path, $five_path, 506, 252, 4, 574);
}

function getHourArray($og_image_path,$hour_path,$type)
{
    $hour_text = extracttextfromimage($hour_path);
    $hour_array = getSignal($hour_text);            //Final Array of last hour min Signal
    $hour = getvaluesforeach($og_image_path, $hour_array, $type);
    return $hour;
}

function getFifteenArray($og_image_path,$fifteen_path,$type)
{
    $fifteen_text = extracttextfromimage($fifteen_path);
    $fifteen_array = getSignal($fifteen_text);
    $fifteen = getvaluesforeach($og_image_path, $fifteen_array,$type);
    return $fifteen;
}

function getFiveArray($og_image_path,$five_path,$type)
{
    $five_text = extracttextfromimage($five_path);
    $five_array = getSignal($five_text);
    $five = getvaluesforeach($og_image_path, $five_array,$type);
    return $five;
}

//directory iterator
function AllStockCodes()
{
    $arr = array();
    $dir = new DirectoryIterator(dirname($_SERVER['DOCUMENT_ROOT'] . '/VnsInvestments/img/charts/.'));
    foreach ($dir as $fileInfo) {
        if (!$fileInfo->isDot()) {
            $arr[] = basename($fileInfo->getRealPath(), '.gif');
        }
    }
    return $arr;
}
