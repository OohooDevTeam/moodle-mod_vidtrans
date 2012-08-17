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
 * Prints a particular instance of vidtrans
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/vid_form.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n = optional_param('n', 0, PARAM_INT);  // vidtrans instance ID - it should be named as the first character of the module

if ($id) {
    $cm = get_coursemodule_from_id('vidtrans', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*',
            MUST_EXIST);
    $vidtrans = $DB->get_record('vidtrans', array('id' => $cm->instance), '*',
            MUST_EXIST);
} elseif ($n) {
    $vidtrans = $DB->get_record('vidtrans', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $vidtrans->course), '*',
            MUST_EXIST);
    $cm = get_coursemodule_from_instance('vidtrans', $vidtrans->id, $course->id,
            false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'vidtrans', 'view', "view.php?id={$cm->id}",
        $vidtrans->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/vidtrans/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($vidtrans->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);


// Output starts here
echo $OUTPUT->header();

if ($vidtrans->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('vidtrans', $vidtrans, $cm->id),
            'generalbox mod_introbox', 'vidtransintro');
}

//For some reason I made everything in this plugin a form =/ It was a terrible idea
//and should probably be changed later.
$mform = new vid_form();
$mform->display();

// Finish the page
echo $OUTPUT->footer();
?>


