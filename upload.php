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
 * This page handles all file uploads (video and subtitle)
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

define('UPLOAD_LIM_MB', 500);

//The module id that the files are being uploaded to.
$id = optional_param('id', 0, PARAM_INT); // course_module ID, or


if ($id) {
    $cm = get_coursemodule_from_id('vidtrans', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*',
            MUST_EXIST);
    $vidtrans = $DB->get_record('vidtrans', array('id' => $cm->instance), '*',
            MUST_EXIST);
}

$context = get_context_instance(CONTEXT_COURSE, $course->id);

require_login($course, true, $cm);
require_capability('mod/vidtrans:upload_video', $context);

$context = get_context_instance(CONTEXT_COURSE, $course->id);
$fs = get_file_storage();

//Get a unique filepath.
$randNum = rand(0, 10000000);
$filepath = '/' . $randNum . '/';
while (filepath_exists($filepath)) {
    $randNum++;
    $filepath = '/' . $randNum . '/';
}

$no_redirect = false;

//Make sure there is a video file.
if (key_exists('vidfile', $_FILES)) {

    if (filesize($_FILES['vidfile']['tmp_name']) / (1024 * 1024) > UPLOAD_LIM_MB) {
        $no_redirect = true;
        print_upload_error(get_string('too_large', 'vidtrans') . filesize($_FILES['vidfile']['tmp_name']) / (1024 * 1024) . 'MB, ' . get_string('limit_is',
                        'vidtrans') . UPLOAD_LIM_MB . 'MB');
    }

    if (!strstr($_FILES['vidfile']['type'], 'video') && !strstr($_FILES['vidfile']['type'],
                    'audio')) {
        $no_redirect = true;
        print_upload_error(get_string('incorrect_vid', 'vidtrans'));
    }

    $file = $_FILES['vidfile'];
    $fileinfo = array(
        'contextid' => $context->id,
        'component' => 'mod_vidtrans',
        'filearea' => 'vidfiles',
        'itemid' => 2, //Processing item id
        'filepath' => $filepath,
        'filename' => $file['name'],
        'mimetype' => $file['type']);
    $fs->create_file_from_pathname($fileinfo, $_FILES['vidfile']['tmp_name']);

    $basefilename = substr($_FILES['vidfile']['name'], 0,
            strpos($_FILES['vidfile']['name'], '.', -0));
    //Make sure there is a subtitle file.
    if (key_exists('subfile', $_FILES)) {
        $fileinfo = array(
            'contextid' => $context->id,
            'component' => 'mod_vidtrans',
            'filearea' => 'vidfiles',
            'itemid' => 0,
            'filepath' => $filepath,
            'filename' => $basefilename . '.srt',
            'mimetype' => $_FILES['subfile']['type']);
        $fs->create_file_from_pathname($fileinfo, $_FILES['subfile']['tmp_name']);
    } else {
        print_upload_error(get_string('ss_sub', 'vidtrans'));
    }
} else {
    print_upload_error(get_string('ss_vid', 'vidtrans'));
}
$no_redirect = false;

header('Location: ' . dirname($_SERVER['REQUEST_URI']) . '/view.php?id=' . $_GET['id']);

/**
 * Print an upload error message to the screen.
 * 
 * @param String $err_string The upload error message.
 */
function print_upload_error($err_string) {
    $err = get_string('err_upload', 'vidtrans');
    echo <<<HTML
        <html>
            <h1>$err</h1>
            <p> \t$err_string </p>
        </html>
HTML;
    die();
}

/**
 * Check to make sure the given filepath does not already exist.
 * 
 * @param String filepath the filepath to look for.
 */
function filepath_exists($filepath) {

    global $context;
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_vidtrans', 'vidfiles');

    foreach ($files as $file) {
        if ($file->get_filepath() == $filepath) {
            return true;
        }
    }

    return false;
}

?>
