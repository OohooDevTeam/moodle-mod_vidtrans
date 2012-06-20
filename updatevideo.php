<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');


$id = optional_param('id', 0, PARAM_INT); // course_module ID, or

if ($id) {
    $cm = get_coursemodule_from_id('vidtrans', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $vidtrans = $DB->get_record('vidtrans', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

if (array_key_exists('vid', $_POST) && array_key_exists('sub', $_POST) && array_key_exists('lang', $_POST) && array_key_exists('lang_code', $_POST)) {
    $video = $_POST['vid'];
    $sub = $_POST['sub'];
    $lang = $_POST['lang'];
    $langcode = $_POST['lang_code'];

    echo "<link href='video-js.css' rel='stylesheet'>";
    echo "<script src='video.js'></script>";
    echo "<video id='my_video_1' class='video-js vjs-default-skin' controls";
    echo "    preload='auto' width='640' height='360' data-setup='{}'>";

    $browser = getBrowser();
    $basesrc = $video;
    $mainext = 'mp4'; //assumed.
    
    if ($browser == 'Opera' || $browser == 'Mozilla Firefox') {
        echo "<source src=\"$basesrc.ogg\" type='video/ogg'>";
        $mainext = 'ogg';
    }
    
    if ($browser == 'Google Chrome') {
        echo "<source src=\"$basesrc.webm\" type='video/webm'>";
        $mainext = 'webm';
    }

    //Always include mp4 source.
    echo "<source src=\"$basesrc.mp4\" type='video/mp4'>";

    echo "    <track kind='subtitles' src='$basesrc.srt$sub' srclang=$langcode label=$lang></track> ";
    echo "</video>";
    echo "<iframe id='videourl' url='$basesrc' type='video/$mainext'></iframe>";
} else if (array_key_exists('i', $_POST)) {
    $subtext = $_POST['i'];
    echo '<center>';
    if (strpos($subtext, 'Original: ') == 0 && strpos($subtext, 'Translated: ') !== false) {
        $start = strpos($subtext, 'Original: ') + strlen('Original: ');
        $end = strpos($subtext, 'Translated: ');
        $original = substr($subtext, $start, $end - $start);
        $trans = substr($subtext, $end + strlen('Translated: '));

        echo "<div> $original </div>";
        echo "-----</br>";
        echo "<div> $trans </div>";
    } else {
        echo $subtext;
    }
    echo '</center>';
}
?>