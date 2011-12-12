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
 * This is called if a user disables this application from the Facebook page
 * This removes the users Facebook ID from the Moodle database
 *
 * @package    facebook
 * @copyright  2011 Aaron Fulton
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require($CFG->dirroot.'/config.php');
require($CFG->dirroot.'/auth/facebook/lib.php');

$FB = facebook_object_initialize();
$fb_user = $FB->getSignedRequest();
$moodle_id = facebook_get_moodle_id($fb_user);

$data = $DB->get_record('user_info_field', array('datatype' => 'facebook'), 'id');
$fieldid = $data->id;

$DB->delete_records('user_info_data', array('fieldid' => $fieldid, 'userid' => $moodle_id));