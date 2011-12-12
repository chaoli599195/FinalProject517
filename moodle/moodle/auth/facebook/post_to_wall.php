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
 * Post messages to the users Facebook wall when certain events happen
 * These events come directly from the Mooodle Events system.
 *
 * @package    facebook
 * @copyright  2011 Aaron Fulton
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once($CFG->dirroot.'/auth/facebook/lib.php');

/**
 * When a student uploads an assignment, post this to their Facebook wall
 * @param $data 
 *           $data = new stdClass();
 *           $data->modulename   = 'assignment';
 *           $data->cmid         = $this->cm->id;
 *           $data->itemid       = $submission->id;
 *           $data->courseid     = $this->course->id;
 *           $data->userid       = $USER->id;
 *           $data->files        = $files;
 */
function assignment_post($data) {
    global $CFG, $SITE, $DB;
    $fb_id = facebook_get_facebook_id($data->userid);
  
    if ($fb_id) {
        $FB = facebook_object_initialize();
        $moodle_id = $data->userid;
      
        $course = $DB->get_record('course', array('id' => $data->courseid));
        $instance = $DB->get_record_sql("SELECT a.* FROM {assignment} a JOIN {course_modules} cm ON a.id = cm.instance WHERE cm.id = {$data->cmid}");
        $user = $DB->get_records_list('user', 'id', array($data->userid));
        $result = $FB->api(
            '/'. $fb_id .'/feed/',
            'post',
            array(
                'name' => get_string('assignment_post_to_wall_title', 'auth_facebook'),
                'message' => get_string('assignment_post_to_wall_message' 'auth_facebook', array('firstname' => $user[$moodle_id]->firstname, 'assignment' => $instance->name, 'course' => $course->fullname)),
                'actions' => array(
                    'name' => $SITE->fullname,
                    'link' => $CFG->wwwroot,
                ),
            ),
        );
    }

  return true;
}

/**
 * When a student finishes a quiz, post this to their Facebook wall
 * @param $data 
 *
 * $data->component  = 'mod_quiz';
 * $data->course     = $attemptobj->get_courseid();
 * $data->quiz       = $attemptobj->get_quizid();
 * $data->cm         = $attemptobj->get_cmid();
 * $data->user       = $USER;
 * $data->attempt    = $attemptobj->get_attemptid();
 */
function quiz_post($data) {
   // return true; //This does not work so return true to prevent the error
  
    global $CFG, $SITE, $DB;
    $user = $data->user;
 
    $fb_id = facebook_get_facebook_id($user->id);
  
    if ($fb_id) {
        $FB = facebook_object_initialize(); //for some reason this passes for assignement and fails for quizes
        $course = $DB->get_record('course', array('id' => $data->course));
        $instance = $DB->get_record_sql("SELECT a.* FROM {quiz} q JOIN {course_modules} cm ON q.id = cm.instance WHERE cm.id = {$data->cm}");
    
        $result = $FB->api(
            '/'. $fb_id .'/feed/',
            'post',
            array(
                'name' => get_string('quiz_post_to_wall_title'),
                'message' => get_string('quiz_post_to_wall_message', 'auth_facebook', array('firstname' => $user->firstname, 'quiz' => $instance->name, 'course' => $course->fullname)),
                'actions' => array(
                  'name' => $SITE->fullname,
                  'link' => $CFG->wwwroot,
                ),
            )
        );
    }
  
    return true;
}