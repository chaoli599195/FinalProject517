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
 * Disconnect from Facebook confirmation form
 *
 * This file is called when a user wishes to end the link between Facebook and Moodle
 *
 * @package    facebook
 * @copyright  2011 Aaron Fulton
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require($CFG->dirroot.'/config.php');
require($CFG->dirroot.'/auth/facebook/lib.php');

$disconnect = optional_param('disconnect', null, PARAM_CLEAN);
$cancel = optional_param('cancel', null, PARAM_CLEAN);
$site = get_site();

if (!empty($cancel)) {
    redirect($CFG->wwwroot .'/user/editadvanced.php');
}

if (!empty($disconnect)) {
    $FB = facebook_object_initialize();
    $fb_user = $FB->getUser();
    $data = $DB->get_record('user_info_field', array('datatype' => 'facebook'), 'id');
    $fieldid = $data->id;
    $DB->delete_records('user_info_data', array('fieldid' => $fieldid, 'userid' => $USER->id));
    
    $ret = $FB->api(array(
        'method' => 'auth.revokeAuthorization',
        'user' => $fb_user,
    ));
      
    redirect($CFG->wwwroot .'/user/editadvanced.php');
}

$PAGE->set_title("$site->fullname");
$PAGE->set_heading("$site->fullname");

echo $OUTPUT->header();
echo $OUTPUT->box_start();
include($CFG->dirroot.'/auth/facebook/facebook_disconnect.html');
echo $OUTPUT->box_end();
echo $OUTPUT->footer();