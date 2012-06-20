<?php

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/locallib.php');

class vid_form extends moodleform {

    function definition() {
        global $CFG, $DB;

        $id = optional_param('id', 0, PARAM_INT); // course_module ID, or

        if ($id) {
            $cm = get_coursemodule_from_id('vidtrans', $id, 0, false, MUST_EXIST);
            $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        } else {
            error('You must specify an id.');
        }

        $maxbytes = 536870912;

        $mform = & $this->_form;

        $mform->addElement('html', '<center>');

        $mform->addElement('html', '<script src ="jquery-1.7.1.js"></script>
            <link rel="stylesheet" type="text/css" href="view.css" />
            <script src="video.js"></script>
            <link href="video-js.css" rel="stylesheet">
            <title></title>');

        //Load the style.
        $mform->addElement('html', '<style type="text/css">view.css</style>');

        //Build the table
        $mform->addElement('html', '<table id="videotable">');
        $mform->addElement('html', '<tr><td>');
        //Load the video.



        $mform->addElement('html', '<div id ="myvidtag">
            
            <video id="my_video_1" class="video-js vjs-default-skin" controls
                   preload="auto" width="640" height="360" data-setup="{}">');


        //Determine what sources to use.
        $browser = getBrowser();
        $basesrc = '';
        $mainext = 'mp4'; //assumed.
        if ($browser == 'Opera' || $browser == 'Firefox') {
            $mform->addElement('html', "<source src=\"$basesrc.ogg\" type='video/ogg'>");
            $mainext = 'ogg';
        }
        if ($browser == 'Google Chrome') {
            $mform->addElement('html', "<source src=\"$basesrc.webm\" type='video/webm'>");
            $mainext = 'webm';
        }
        //Always include mp4 source.
        $mform->addElement('html', "<source src=\"$basesrc.mp4\" type='video/mp4'>");

        $no_support = get_string('no_support', 'vidtrans');
        $mform->addElement('html', "<font color=\"white\">$no_support<br></font>
                <track kind=\"subtitles\" src=\"$basesrc.srt\" srclang=\"lg\" label=\"Language\"></track>
            </video>
            <iframe id=\"videourl\" url='$basesrc.$mainext' type='video/$mainext'></iframe>
        </div>");

        $mform->addElement('html', '</td><td id="subtitlearea" width ="300">');
        //Load the subtitle area
        $mform->addElement('html', '<div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<div>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</td></tr></table>');


        //Load the menu
        $vid_sel = get_string('vid_sel', 'vidtrans');
        $lang_sel = get_string('lang_sel', 'vidtrans');

        $mform->addElement('html', "<div id=\"buttons\" cellspacing=\"0\" cellpadding =\"0\">
            <table>
                <tr>
                    <td class=\"icon-holder\" id=\"vid\">
                        <b>$vid_sel</b>
                    </td>
                    <td class=\"icon-holder\" id=\"lang\">
                        <b>$lang_sel</b>
                    </td>
                </tr> 
            </table>
        </div>");

        //File menu
        $mform->addElement('html', '<div id="file-list">
            <table>');




        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_vidtrans', 'vidfiles', '0');

        $vidnames = array();
        $vids = array();

        foreach ($files as $file) {
            if ($file->get_mimetype() == 'audio/ogg'
                    || $file->get_mimetype() == 'video/ogg'
                    || $file->get_mimetype() == 'video/webm'
                    || $file->get_mimetype() == 'video/mp4') {
                $filename = $file->get_filepath() . substr($file->get_filename(), 0, strrpos($file->get_filename(), '.', -0));

                if (!in_array($filename, $vidnames)) {
                    array_push($vidnames, $filename);
                    array_push($vids, $file);
                }
            }
        }

        //Add files to mform
        for ($i = 0; $i < count($vids); $i+=3) {
            $mform->addElement('html', "<tr>");

            for ($j = 0; $j < 3; $j++) {

                if ($i + $j < count($vids)) {
                    $vidfile = $vids[$i + $j];

                    $vid_name = substr($vidfile->get_filename(), 0, strrpos($vidfile->get_filename(), '.', -0));

                    $url = $CFG->wwwroot . '/pluginfile.php/' . $vidfile->get_contextid() . '/' . 'mod_vidtrans' . '/' . 'vidfiles';
                    $vidinfo = $url . $vidfile->get_filepath() . $vidfile->get_itemid() . '/' . $vid_name;

                    $type = $vidfile->get_mimetype();

                    $mform->addElement('html', "<td class = \"file-link\" id='$vidinfo::$type'><b> $vid_name </b></td>");
                } else {
                    if (has_capability('mod/vidtrans:upload_video', $context)) {
                        $add_files = get_string('add_files', 'vidtrans');
                        $mform->addElement('html', "<td class=\"file-link\" id=\"add-files\"><b>$add_files</b></td>");
                    }
                    break;
                }
            }
            $mform->addElement('html', "</tr>");
        }

        //If there wasn't a spot to insert add files butotn. Add it now.
        if (count($vids) % 3 == 0 && has_capability('mod/vidtrans:upload_video', $context)) {
            $mform->addElement('html', "<tr>");
            $add_files = get_string('add_files', 'vidtrans');
            $mform->addElement('html', "<td class=\"file-link\" id=\"add-files\"><b>$add_files</b></td>");
            $mform->addElement('html', "</tr>");
        }
        $mform->addElement('html', "</table></div>");


        //Language menu
        $english = get_string('english', 'vidtrans');
        $french = get_string('french', 'vidtrans');
        $spanish = get_string('spanish', 'vidtrans');
        $russian = get_string('russian', 'vidtrans');
        $japanese = get_string('japanese', 'vidtrans');

        $mform->addElement('html', "<div id=\"lang-list\">
            <table>
                <tr>
                    <td class=\"lang-link\" id=\"\"> <b>$english</b></td>
                    <td class=\"lang-link\" id=\"_fr\"> <b>$french</b></td>
                    <td class=\"lang-link\" id=\"_es\"> <b>$spanish</b></td>
                </tr>
                <tr>
                    <td class=\"lang-link\" id=\"_ja\"> <b>$japanese</b></td>
                    <td class=\"lang-link\" id=\"_ru\"> <b>$russian</b></td> 
                    <td></td>
                </tr>
            </table>
        </div>");

        $mform->addElement('html', '<div id="dialog"> </div>');
        $mform->addElement('html', '<div id="backgroundPopup"></div>');

        //Add the script for menus
        $mform->addElement('html', '<script type="text/javascript" src="view.js"> </script>');

        $mform->addElement('html', '</center>');
    }

}

?>
