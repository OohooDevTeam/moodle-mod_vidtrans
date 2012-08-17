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
 * Definition of log events
 *
 * NOTE: this is an example how to insert log event during installation/update.
 * It is not really essential to know about it, but these logs were created as example
 * in the previous 1.9 vidtrans.
 *
 * @package    mod
 * @subpackage vidtrans
 * @copyright  2011 Your Name <your@email.adress>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $DB;

$logs = array(
    array('module' => 'vidtrans', 'action' => 'add', 'mtable' => 'vidtrans', 'field' => 'name'),
    array('module' => 'vidtrans', 'action' => 'update', 'mtable' => 'vidtrans', 'field' => 'name'),
    array('module' => 'vidtrans', 'action' => 'view', 'mtable' => 'vidtrans', 'field' => 'name'),
    array('module' => 'vidtrans', 'action' => 'view all', 'mtable' => 'vidtrans', 'field' => 'name')
);
