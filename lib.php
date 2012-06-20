<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants for module vidtrans
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the vidtrans specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod
 * @subpackage vidtrans
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * The max number of simultaneous video processing jobs.
 */
define('MAX_VID_PROCESSING', 1);
require_once(dirname(__FILE__) . '/locallib.php');

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function vidtrans_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO: return true;
        default: return null;
    }
}

/**
 * Saves a new instance of the vidtrans into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $vidtrans An object from the form in mod_form.php
 * @param mod_vidtrans_mod_form $mform
 * @return int The id of the newly inserted vidtrans record
 */
function vidtrans_add_instance(stdClass $vidtrans, mod_vidtrans_mod_form $mform = null) {
    global $DB;

    $vidtrans->timecreated = time();

    print_object($vidtrans);

    $languages = new stdClass();

    $languages->french = property_exists($vidtrans, 'french') ? $vidtrans->french : 0;
    $languages->spanish = property_exists($vidtrans, 'spanish') ? $vidtrans->spanish : 0;
    $languages->russian = property_exists($vidtrans, 'russian') ? $vidtrans->russian : 0;
    $languages->japanese = property_exists($vidtrans, 'japanese') ? $vidtrans->japanese : 0;
    $languages->course = $vidtrans->course;
    print_object($languages);

    $DB->insert_record('vidtrans_languages', $languages);

    # You may have to add extra stuff in here #


    return $DB->insert_record('vidtrans', $vidtrans);
}

/**
 * Updates an instance of the vidtrans in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $vidtrans An object from the form in mod_form.php
 * @param mod_vidtrans_mod_form $mform
 * @return boolean Success/Fail
 */
function vidtrans_update_instance(stdClass $vidtrans, mod_vidtrans_mod_form $mform = null) {
    global $DB;

    $vidtrans->timemodified = time();
    $vidtrans->id = $vidtrans->instance;

    print_object($vidtrans);
    die();
    return $DB->update_record('vidtrans', $vidtrans);
}

/**
 * Removes an instance of the vidtrans from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function vidtrans_delete_instance($id) {
    global $DB;

    if (!$vidtrans = $DB->get_record('vidtrans', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('vidtrans', array('id' => $vidtrans->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function vidtrans_user_outline($course, $user, $mod, $vidtrans) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $vidtrans the module instance record
 * @return void, is supposed to echp directly
 */
function vidtrans_user_complete($course, $user, $mod, $vidtrans) {
    
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in vidtrans activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function vidtrans_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link vidtrans_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function vidtrans_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
    
}

/**
 * Prints single activity item prepared by {@see vidtrans_get_recent_mod_activity()}

 * @return void
 */
function vidtrans_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    
}

////////////////////////////////////////////////////////////////////////////////
// CRON Methods                                                               //
////////////////////////////////////////////////////////////////////////////////

/**
 * Runs the video processing scripts.
 *
 * @return boolean
 * */
function vidtrans_cron() {

    global $DB;

    $fs = get_file_storage();

    $file_records = cron_get_area_files('mod_vidtrans', 'vidfiles', '2');

    foreach ($file_records as $file_record) {
        if (cron_numProcessing() < MAX_VID_PROCESSING) {
            $file_record = cron_move_to_processing($file_record);
            cron_process($file_record);
        }
    }

    return true;
}

/**
 * Counts and reports the number of jobs with an item id of '1' (Being processed)
 * 
 * @return  Returns the number of jobs that are currently being processed.
 */
function cron_numProcessing() {

    $file_records = cron_get_area_files('mod_vidtrans', 'vidfiles', '1');

    $numdirs = 0;
    foreach ($file_records as $file_record) {
        if ($file_record->is_directory()) {
            $numdirs++;
        }
    }

    return count($file_records) - $numdirs;
}

/**
 * Changes the databasefile from itemid '2' (Which means requires processing) to
 * an itemid '1' (Which means it is being processed).
 * 
 * @param type $file_record     the record which needs to be processed.
 * @return void
 */
function cron_move_to_processing($file_record) {
    global $DB;

    $data = new stdClass;
    $data->id = $file_record->get_id();
    $data->itemid = 1; /* Currently Processing ID. */

    $DB->update_record('files', $data);

    $fs = get_file_storage();

    return $fs->get_file_by_id($file_record->get_id());
}

/**
 * Removes the given file (Requires an itemid of 1) from the system because it
 * has been fully processed. 
 * 
 * @param type $file_record     the record which needs to be removed. (NOT NULL)
 * @return                      returns true if successfull false otherwise.
 */
function cron_delete_from_processing($file_record) {
    if ($file_record->get_itemid() == '1') {

        $fs = get_file_storage();

        //Delete base dir
        //Get file filename -> '.'
        $file = $fs->get_file(
                $file_record->get_contextid(), $file_record->get_component(), $file_record->get_filearea(), '2', /* The base dir didnt move with the file. */ $file_record->get_filepath(), '.');

        $file_record->delete();

        // Delete file if it exists
        if ($file) {
            $file->delete();
        }

        return true;
    } else {
        return false;
    }
}

/**
 *
 * Performs processing for a given video file in order to make it compatable with
 * all browsers. 
 * 
 * OGG  -> Opera and Firefox
 * MP4  -> Safari, IOS, IE9
 * WEBM -> Google Chrome (Though it is  still somewhat broken on chrome 
 * and mp4 "should" work but it doesnt. This is probably due to a processing error
 * made on my part and if someone has a way to fix it feel free to change the ffmpeg
 * commands in this method.)
 *  
 * 
 * @global type $CFG
 * @param type $vidfile     The video file which requires processing.
 */
function cron_process($vidfile) {
    global $CFG;

    //Process the subtitles
    cron_process_subtitles($vidfile);

    $fs = get_file_storage();

    //Create the temp files.
    $oggtemp = tempnam(sys_get_temp_dir(), '');
    $mp4temp = tempnam(sys_get_temp_dir(), '');
    $webtemp = tempnam(sys_get_temp_dir(), '');
    $tempfile = tempnam(sys_get_temp_dir(), '');
    $tempfile2 = tempnam(sys_get_temp_dir(), '');

    //Create a file!
    $vidfile->copy_content_to($tempfile);
    $vidfile->copy_content_to($tempfile2);

    //Tell them the file really does exist =D
    clearstatcache();

    $fileinfo = array(
        'contextid' => $vidfile->get_contextid(),
        'component' => 'mod_vidtrans',
        'filearea' => 'vidfiles',
        'itemid' => 0, /* Finished Processing item id. */
        'filepath' => $vidfile->get_filepath(),
        'filename' => '' /* Don't care at this point. */,
        'mimetype' => '' /* Don't care at this point. */);

    /* Perform processing for each required browser. */
    //Get the basae filename (without the extension)
    $filename = substr($vidfile->get_filename(), 0, strrpos($vidfile->get_filename(), '.', -0));

    //Safari & IE & IOS
    if ($vidfile->get_mimetype() != 'video/mp4') {
        //If it isn't already in mp4 format then convert it.
        exec($CFG->vidtrans_ffmpeg . " -y -i $tempfile -vcodec libx264 -f mp4 $mp4temp");
    } else {
        //Else make mp4temp the same as tempfile.
        $mp4temp = $tempfile;
    }

    //Add mp4temp to databse. (As a processed file.)
    $fileinfo['filename'] = $filename . '.mp4';
    $fileinfo['mimetype'] = 'video/mp4';
    $fs->create_file_from_pathname($fileinfo, $mp4temp);

    //Firefox & Opera
    if ($vidfile->get_mimetype() != 'audio/ogg' && $vidfile->get_mimetype() != 'video/ogg') {
        echo $CFG->vidtrans_ffmpeg . " -y -i $tempfile -vcodec libtheora -acodec libvorbis -f ogg $oggtemp<br>";
        exec($CFG->vidtrans_ffmpeg . " -y -i $tempfile -vcodec libtheora -acodec libvorbis -f ogg $oggtemp");
    } else {
        $oggtemp = $tempfile;
    }

    $fileinfo['filename'] = $filename . '.ogg';
    $fileinfo['mimetype'] = 'video/ogg';

    $fs->create_file_from_pathname($fileinfo, $oggtemp);

    //Chrome
    if ($vidfile->get_mimetype() != 'video/webm') {
        echo $CFG->vidtrans_ffmpeg . " -y -i $tempfile -vcodec libvpx -acodec libvorbis -f webm $webtemp<br>";
        exec($CFG->vidtrans_ffmpeg . " -y -i $tempfile -vcodec libvpx -acodec libvorbis -f webm $webtemp");
    } else {
        $webtemp = $tempfile;
    }

    $fileinfo['filename'] = $filename . '.webm';
    $fileinfo['mimetype'] = 'video/webm';

    $fs->create_file_from_pathname($fileinfo, $webtemp);



    //Remove from processing area.
    cron_delete_from_processing($vidfile);

    //Remove all the temporary files.
    unlink($oggtemp);
    unlink($webtemp);
    unlink($mp4temp);
    unlink($tempfile);
}

/**
 *
 * @param type $vidfile 
 */
function cron_process_subtitles($vidfile) {

    //Get the basae filename (without the extension)
    $filename = substr($vidfile->get_filename(), 0, strrpos($vidfile->get_filename(), '.', -0));

    $fileinfo = array(
        'contextid' => $vidfile->get_contextid(),
        'component' => 'mod_vidtrans',
        'filearea' => 'vidfiles',
        'itemid' => 0,
        'filepath' => $vidfile->get_filepath(),
        'filename' => $filename . '.srt',
        'mimetype' => 'text/plain');

    $fs = get_file_storage();
    $subfile = $fs->get_file(
            $fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
    //Make sure subtitle file exists.
    if (!$subfile) {
        return;
    }

    $tempfile = tempnam(sys_get_temp_dir(), '');
    $subfile->copy_content_to($tempfile);
    //Tell them the file really does exist =D
    clearstatcache();

    $lang_array = array('es', 'fr', 'ru', 'ja');

    foreach ($lang_array as $lang) {

        translate_and_upload($tempfile, $subfile->get_contextid(), $subfile->get_filepath(), $subfile->get_filename(), $subfile->get_mimetype(), $lang);
    }

    //Delete the tempfile
    unlink($tempfile);
}

/**
 * Returns all area files (optionally limited by itemid)
 *
 * @param string $component
 * @param string $filearea
 * @param int $itemid (all files if not specified)
 * @param string $sort
 * @param bool $includedirs
 * @return array of stored_files indexed by pathanmehash
 */
function cron_get_area_files($component, $filearea, $itemid = false, $sort="sortorder, timecreated", $includedirs = false) {
    global $DB;

    $fs = get_file_storage();

    $conditions = array('component' => $component, 'filearea' => $filearea);
    if ($itemid !== false) {
        $conditions['itemid'] = $itemid;
    }

    $result = array();
    $file_records = $DB->get_records('files', $conditions, $sort);
    foreach ($file_records as $file_record) {
        if (!$includedirs and $file_record->filename === '.') {
            continue;
        }
        $result[$file_record->pathnamehash] = $fs->get_file_instance($file_record);
    }
    return $result;
}

/**
 * Returns an array of users who are participanting in this vidtrans
 *
 * Must return an array of users who are participants for a given instance
 * of vidtrans. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $vidtransid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function vidtrans_get_participants($vidtransid) {
    return false;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function vidtrans_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of vidtrans?
 *
 * This function returns if a scale is being used by one vidtrans
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $vidtransid ID of an instance of this module
 * @return bool true if the scale is used by the given vidtrans instance
 */
function vidtrans_scale_used($vidtransid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('vidtrans', array('id' => $vidtransid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of vidtrans.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any vidtrans instance
 */
function vidtrans_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('vidtrans', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give vidtrans instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $vidtrans instance object with extra cmidnumber and modname property
 * @return void
 */
function vidtrans_grade_item_update(stdClass $vidtrans) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    /** @example */
    $item = array();
    $item['itemname'] = clean_param($vidtrans->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax'] = $vidtrans->grade;
    $item['grademin'] = 0;

    grade_update('mod/vidtrans', $vidtrans->course, 'mod', 'vidtrans', $vidtrans->id, 0, null, $item);
}

/**
 * Update vidtrans grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $vidtrans instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function vidtrans_update_grades(stdClass $vidtrans, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    /** @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/vidtrans', $vidtrans->course, 'mod', 'vidtrans', $vidtrans->id, 0, $grades);
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function vidtrans_get_file_areas($course, $cm, $context) {
    return array('vidfiles' => 'Video Files');
}

/**
 * Serves the files from the vidtrans file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return void this should never return to the caller
 */
function vidtrans_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload) {
    global $DB, $CFG;

    require_login($course, true, $cm);

    $fileinfo = array(
        'component' => 'mod_vidtrans', // usually = table name
        'filearea' => $filearea, // usually = table name
        'itemid' => $args[1], // usually = ID of row in table
        'contextid' => $context->id, // ID of context
        'filepath' => '/' . $args[0] . '/', // any path beginning and ending in /
        'filename' => $args[2]); // any filename

    $fs = get_file_storage();
    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

    $filename = $fileinfo['filename'];

    $mimetype = $file->get_mimetype();

//    if ($mimetype == 'audio/ogg') {
//        $mimetype = 'video/ogg';
//    }

    if ($mimetype == 'video/ogg') {
        $tmpfile = tempnam(sys_get_temp_dir(), '');
        $file->copy_content_to($tmpfile);
        exec($CFG->vidtrans_oggzinfo . " $tmpfile", $output);
        $str_duration = substr($output[0], strlen('Content-Duration: '));
        sscanf($str_duration, '%d:%d:%lf', $HH, $MM, $SS);
        $duration = 3600 * $HH + 60 * $MM + $SS;
        header("X-Content-Duration: " . $duration);
    }

    header("Content-Type: " . $file->get_mimetype());
    header("Content-Length: " . $file->get_filesize());
//    header("Content-Disposition: attachment");
//    header("Accept-Ranges: bytes");
//    header("etag:12721n");
//    header("pragma: public");
//    header("cache-control: max-age=0");

    $file->readfile();


    die();
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding vidtrans nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the vidtrans module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function vidtrans_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
    
}

/**
 * Extends the settings navigation with the vidtrans settings
 *
 * This function is called when the context for the page is a vidtrans module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $vidtransnode {@link navigation_node}
 */
function vidtrans_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $vidtransnode=null) {
    
}

