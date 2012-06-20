<?php


require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

global $CFG;

define('__TRANSLIMIT__', 1000);
define("__APPID__", $CFG->vidtrans_bingid);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inFile = $_POST['inputFile'];
    $toLang = $_POST["languageChoice"];
    $fromLang = "en";

    $aFile = fopen($inFile, "r");
    $toBeTranslated = fread($aFile, filesize($inFile));

    $trans_array = to_translate_array($toBeTranslated);

    $translated_text = "";

    foreach ($trans_array as $aString) {
        $newString = translate($aString, $fromLang, $toLang);
        $translated_text .= $newString;
    }

    $trans_caption = merge_subtitles($translated_text, $toBeTranslated);

    $fs = get_file_storage();
    $fileinfo = array(
        'contextid' => $_POST['contextid'],
        'component' => 'mod_vidtrans',
        'filearea' => 'vidfiles',
        'itemid' => 0,
        'filepath' => $_POST['filepath'],
        'filename' => $_POST['filename'] . '_' . $toLang,
        'mimetype' => $_POST['mimetype']);
    
    print_object($fileinfo);

    $fs->create_file_from_string($fileinfo, $trans_caption);

} else {
    print "You didn't post apparently";
}

function merge_subtitles(/* String */ $trans_text, /* String */ $original_sub) {
    $trans_array = explode("\n\n", $trans_text);
    $orig_caps = explode("\n\n", $original_sub);


    //Assume any extra elements are empty. (May not be a good assumption).
    if (count($trans_array) < count($orig_caps)) {
        return FALSE;
    }

    /* String */ $trans_subtitles = "";
    for (/* int */ $i = 0; $i < count($orig_caps); $i++) {
        //Find the second \n -> correspons to the begining of the text.
        /* int */ $first = strpos($orig_caps[$i], "\n");

        if ($first != -1) {
            /* int */ $index = strpos($orig_caps[$i], "\n", $first + 1 > strlen($orig_caps[$i]) ? $first : $first + 1);
            if ($index != -1) {
                $trans_subtitles .= substr($orig_caps[$i], 0, $index + 1) . substr($orig_caps[$i], $index + 1) . "\n-----\n" . $trans_array[$i] . "\n\n";
            }
        }
    }

    return $trans_subtitles;
}

function translate(/* String */ $aString, /* String */ $from, /* String */ $to) {

    //Bing likes to ignore newlines sometimes. =/ 
    $aString = str_replace("\n", "<br>", $aString);

    $url = "http://api.bing.net/json.aspx?AppId=" . __APPID__ . "&Sources=Translation&Version=2.2&Translation.SourceLanguage=$from&Translation.TargetLanguage=$to&Query=" . urlencode($aString);

    //Get translation
    $curl_handle = curl_init($url);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/4.0");

    $jsonArray = json_decode(curl_exec($curl_handle));

    /* String */ $contents = $jsonArray->SearchResponse->Translation->Results[0]->TranslatedTerm;

    //Close connection.
    curl_close($curl_handle);

    return str_replace("<br>", "\n", $contents);
}

function to_translate_array($sub) {


    //Seperate each caption. (They are of the form <NUM>\n<TIMECODE>\n<TEXT>\n\n)
    //Dealing with the case where we have "\n" (Blank line) instead of <TEXT> (SHOULD NOT HAPPEN)
    $sub = str_replace("\n\n\n", "<BLANK>\n\n", $sub);
    /* String[] */ $capArray = explode("\n\n", $sub);

    //Build the output array.
    /* String[] */ $outArray = array();
    /* String */ $max_trans_string = "";
    for (/* int */ $i = 0; $i < count($capArray); $i++) {

        //Seperate the first component. <NUM> from the rest
        /* String[] */ $seperated = substr($capArray[$i], strpos($capArray[$i], "\n") + 1);

        //Now we remove <TIMECODE>
        $seperated = substr($seperated, strpos($seperated, "\n") + 1);

        //Add string to max if it wont excede the translation limit.
        if (strlen($max_trans_string) + strlen($seperated) + 2 < __TRANSLIMIT__) {
            $max_trans_string .= $seperated . "\n\n";
        } else {
            //Add to the translation array and clear old max string.
            array_push($outArray, $max_trans_string);
            $max_trans_string = $seperated . "\n\n";
        }
    }

    //Push the last of it onto the array.
    array_push($outArray, $max_trans_string);

    return $outArray;
}

?>
