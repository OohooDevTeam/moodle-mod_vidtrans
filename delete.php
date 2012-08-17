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
 * This file handles the deletion of video/subtitle files.
 */

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

$context = get_context_instance(CONTEXT_COURSE, $course->id);

require_login($course, true, $cm);
require_capability('mod/vidtrans:upload_video', $context);

$context = get_context_instance(CONTEXT_COURSE, $course->id);
$fs = get_file_storage();

$filename = $_POST['filename'];
$filepath = $_POST['filepath'];

$fileinfo = array(
    'component' => 'mod_vidtrans',
    'filearea' => 'vidfiles', // usually = table name
    'itemid' => 0, // usually = ID of row in table
    'contextid' => $context->id, // ID of context
    'filepath' => $filepath, // any path beginning and ending in /
    'filename' => $filename); // any filename

$extensions = array('.mp4', '.ogg', '.webm',
    '.srt', '.srt_fr', '.srt_ru', '.srt_ja', '.srt_es');

//Make sure the file isn't being processed right now
$filelist = $fs->get_area_files($fileinfo['contextid'],  $fileinfo['component'], $fileinfo['filearea'], 1);
foreach($filelist as $file) {
    if($file->get_filepath() == $fileinfo['filepath']) {
        die();
    }
}

//Cron could potentiallly execute right now. To make sure that we dont accidently 
//delte a file in the middle of it being processed we will only delete items with
//Item-id's of 2 or 0 

//Check if there are any files waiting on processing that can be deleted.
$filelist = $fs->get_area_files($fileinfo['contextid'],  $fileinfo['component'], $fileinfo['filearea'], 2);
foreach($filelist as $file) {
    if($file->get_filepath() == $fileinfo['filepath']) {
        $file->delete();
    }
    
}

//Check if there are any files that have been processed already.
$filelist = $fs->get_area_files($fileinfo['contextid'],  $fileinfo['component'], $fileinfo['filearea'], 0);
foreach($filelist as $file) {
    if($file->get_filepath() == $fileinfo['filepath']) {
        $file->delete();
    }
}


?>
