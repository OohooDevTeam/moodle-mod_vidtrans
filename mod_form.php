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
 * The main vidtrans configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

/**
 * Module instance settings form
 */
class mod_vidtrans_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $DB, $COURSE;

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('vidtransname', 'vidtrans'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'vidtransname', 'vidtrans');

        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor();

        //-------------------------------------------------------------------------------
        // Adding the rest of vidtrans settings, spreeading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic

//        $mform->addElement('header', 'supported_lang', get_string('supported_lang', 'vidtrans'));
//
//
//        $record = $DB->get_record('vidtrans_languages', array('course' => $COURSE->id), '*');
//
//        $mform->addElement('checkbox', 'french', get_string('french', 'vidtrans'), '', array('checked'));
//        $mform->addElement('checkbox', 'spanish', get_string('spanish', 'vidtrans'), '', array('checked'));
//        $mform->addElement('checkbox', 'russian', get_string('russian', 'vidtrans'), '', array('checked'));
//        $mform->addElement('checkbox', 'japanese', get_string('japanese', 'vidtrans'), '', 'checked');
//
//        if ($record) {
//            $mform->setDefault('french', $record->french);
//            $mform->setDefault('spanish', $record->spanish);
//            $mform->setDefault('russian', $record->russian);
//            $mform->setDefault('japanese', $record->japanese);
//        }


        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();


        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }

}
