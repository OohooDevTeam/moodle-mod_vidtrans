<?php
/**
 * ************************************************************************
 * *                         Video Translator                            **
 * ************************************************************************
 * @package     mod                                                      **
 * @subpackage  Video Translator                                         **
 * @name        Video Translator                                         **
 * @copyright   oohoo.biz                                                **
 * @link        http://oohoo.biz                                         **
 * @author      Andrew McCann                                            **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later **
 * ************************************************************************
 * ************************************************************************ */

/**
 * Internal library of functions for module vidtrans
 *
 * All the vidtrans specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage vidtrans
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
define('__TRANSLIMIT__', 1000);
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

/**
 * Gets the browser' version's name that the user is using.
 * 
 * @return string The name of the browser being used.
 */
function getBrowser() {
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $ub = '';
    if (preg_match('/MSIE/i', $u_agent)) {
        $ub = "Internet Explorer";
    } elseif (preg_match('/Firefox/i', $u_agent)) {
        $ub = "Mozilla Firefox";
    } elseif (preg_match('/Chrome/i', $u_agent)) {
        $ub = "Google Chrome";
    } elseif (preg_match('/Safari/i', $u_agent)) {
        $ub = "Apple Safari";
    } elseif (preg_match('/Opera/i', $u_agent)) {
        $ub = "Opera";
    }else {
        $ub = "Apple Safari"; //Assume apple if it is unknown.
    }
    return $ub;
}

/**
 * Translate the subtitles for the given file and upload the translation to moodle.
 * 
 * @param String $inFile    Path to the file that needs to be uploaded
 * @param int $contextid    The module id that this file belongs to.
 * @param String $filepath  The filepath field that the file needs to be uploaded to.
 * @param String $filename  The filename that the uploaded file will have.  
 * @param String $mimetype  The mimetype that the uploaded file will have.
 * @param String $toLang    The language that you want to translate the file into. (Language Code)
 * @param String $fromLang  The language the the file is currently in. Defaults to 'en'.
 */
function translate_and_upload($inFile, $contextid, $filepath, $filename,
        $mimetype, $toLang, $fromLang = 'en') {
    
    $aFile = fopen($inFile, "r");
    $toBeTranslated = fread($aFile, filesize($inFile));

    $trans_array = to_translate_array($toBeTranslated);

    $translated_text = "";
    $authHeader = getAuthHeader();
    foreach ($trans_array as $aString) {
        $newString = translate($aString, $fromLang, $toLang, $authHeader);
        $translated_text .= $newString;
    }

    $trans_caption = merge_subtitles($translated_text, $toBeTranslated);

    $fs = get_file_storage();
    $fileinfo = array(
        'contextid' => $contextid,
        'component' => 'mod_vidtrans',
        'filearea' => 'vidfiles',
        'itemid' => 0,
        'filepath' => $filepath,
        'filename' => $filename . '_' . $toLang,
        'mimetype' => $mimetype);

    $fs->create_file_from_string($fileinfo, $trans_caption);
}

/**
 * Merges the original and translated subtitles into a single subtitle file 
 * which will be in the form
 * 
 * [timestamp]
 * [Original Text]
 * ----
 * [Translated Text]
 * 
 * @param String $trans_text    The translated subtitle text.
 * @param String $original_sub  The original subtitle text.
 * @return boolean|string       The new subtitle string. False if there was an error.
 */
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
            /* int */ $index = strpos($orig_caps[$i], "\n",
                    $first + 1 > strlen($orig_caps[$i]) ? $first : $first + 1);
            if ($index != -1) {
                $trans_subtitles .= substr($orig_caps[$i], 0, $index + 1) . substr($orig_caps[$i],
                                $index + 1) . "\n-----\n" . $trans_array[$i] . "\n\n";
            }
        }
    }

    return $trans_subtitles;
}

function getAuthHeader() {
    global $CFG;
    try {

        //Client ID of the application .
        $clientID = $CFG->vidtrans_client_id;
        print_object($CFG->vidtrans_client_id);
        //Client Secret key of the application.
        $clientSecret = $CFG->vidtrans_client_secret;
        print_object($CFG->vidtrans_client_secret);
        //OAuth Url.
        $authUrl = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
        //Application Scope Url
        $scopeUrl = "http://api.microsofttranslator.com";

        //Application grant type
        $grantType = "client_credentials";

        //Create the AccessTokenAuthentication object.
        $authObj = new AccessTokenAuthentication();
        //Get the Access token.

        $accessToken = $authObj->getTokens($grantType, $scopeUrl, $clientID,
                $clientSecret, $authUrl);
        //Create the authorization Header string.
        return "Authorization: Bearer " . $accessToken;
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . PHP_EOL;
    }
}

function translate(/* String */ $aString, /* String */ $from, /* String */ $to,
        $authHeader) {
    try {
        //Set the params.//
        $fromLanguage = $from;
        $toLanguage = $to;
        $inputStr = "the best machine translation \ntechnology cannot always provide translations tailored to a site or users like a human";
        $inputStr2 = str_replace("\n", "<br>", $aString);
        $params = "text=" . urlencode($inputStr2) . "&to=" . $toLanguage . "&from=" . $fromLanguage;
        $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";

        //Create the Translator Object.
        $translatorObj = new HTTPTranslator();

        //Get the curlResponse.
        $curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader);

        //Interprets a string of XML into an object.
        $xmlObj = simplexml_load_string($curlResponse);
        foreach ((array) $xmlObj[0] as $val) {
            $translatedStr = $val;
        }

        return str_replace("<br>", "\n", $translatedStr);
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . PHP_EOL;
    }
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
        /* String[] */ $seperated = substr($capArray[$i],
                strpos($capArray[$i], "\n") + 1);

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

class AccessTokenAuthentication {
    /*
     * Get the access token.
     *
     * @param string $grantType    Grant type.
     * @param string $scopeUrl     Application Scope URL.
     * @param string $clientID     Application client ID.
     * @param string $clientSecret Application client ID.
     * @param string $authUrl      Oauth Url.
     *
     * @return string.
     */

    function getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl) {
        try {
            //Initialize the Curl Session.
            $ch = curl_init();
            //Create the request Array.
            $paramArr = array(
                'grant_type' => $grantType,
                'scope' => $scopeUrl,
                'client_id' => $clientID,
                'client_secret' => $clientSecret
            );
            //Create an Http Query.//
            $paramStr = "grant_type=" . urlencode($grantType);
            $paramStr .= "&scope=" . urlencode($scopeUrl);
            $paramStr .= "&client_id=" . urlencode($clientID);
            $paramStr .= "&client_secret=" . urlencode($clientSecret);



            //Set the Curl URL.
            curl_setopt($ch, CURLOPT_URL, $authUrl);

            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);

            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramStr);

            //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

            //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            //Execute the  cURL session.
            $strResponse = curl_exec($ch);

            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
                throw new Exception($curlError);
            }
            //Close the Curl Session.
            curl_close($ch);
            //Decode the returned JSON string.
            $objResponse = json_decode($strResponse);
            if (isset($objResponse->error) && $objResponse->error) {
                throw new Exception($objResponse->error_description);
            }
            return $objResponse->access_token;
        } catch (Exception $e) {
            echo "Exception-" . $e->getMessage();
        }
    }

}

/*
 * Class:HTTPTranslator
 *
 * Processing the translator request.
 */

Class HTTPTranslator {
    /*
     * Create and execute the HTTP CURL request.
     *
     * @param string $url        HTTP Url.
     * @param string $authHeader Authorization Header string.
     * @param string $postData   Data to post.
     *
     * @return string.
     *
     */

    function curlRequest($url, $authHeader) {
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt($ch, CURLOPT_HTTPHEADER,
                array($authHeader, "Content-Type: text/xml"));
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, False);
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            throw new Exception($curlError);
        }
        //Close a cURL session.
        curl_close($ch);
        return $curlResponse;
    }

}
