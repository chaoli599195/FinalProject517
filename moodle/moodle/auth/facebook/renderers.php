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
 * @author Aaron Fulton
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moodle multiauth
 *
 * Replace the user profile picture with the facebook picture if its available
 *
 * 2009-11-15  File created.
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
require_once($CFG->dirroot.'/auth/facebook/lib.php');

class theme_THEMENAME_core_renderer extends core_renderer {

    /**
     * Print the specified user's avatar.
     *
     * User avatar may be obtained in two ways:
     * <pre>
     * // Option 1: (shortcut for simple cases, preferred way)
     * // $user has come from the DB and has fields id, picture, imagealt, firstname and lastname
     * $OUTPUT->user_picture($user, array('popup'=>true));
     *
     * // Option 2:
     * $userpic = new user_picture($user);
     * // Set properties of $userpic
     * $userpic->popup = true;
     * $OUTPUT->render($userpic);
     * </pre>
     *
     * @param object Object with at least fields id, picture, imagealt, firstname, lastname
     *     If any of these are missing, the database is queried. Avoid this
     *     if at all possible, particularly for reports. It is very bad for performance.
     * @param array $options associative array with user picture options, used only if not a user_picture object,
     *     options are:
     *     - courseid=$this->page->course->id (course id of user profile in link)
     *     - size=35 (size of image)
     *     - link=true (make image clickable - the link leads to user profile)
     *     - popup=false (open in popup)
     *     - alttext=true (add image alt attribute)
     *     - class = image class attribute (default 'userpicture')
     * @return string HTML fragment
     */
    public function user_picture(stdClass $user, array $options = null) {
        $FB = facebook_object_initialize();
        $fb_user = $FB->getUser();

        if ($fb_user) {
            $options = (array)$options;
            if (empty($options['size'])) {
                $size = 35;
            } else if ($options['size'] === true or $options['size'] == 1) {
                $size = 100;
            } else {
                $size = $options['size'];
            }
            
            $output = "<div><fb:profile-pic facebook-logo='true' linked='false' uid='".$fb_user."' width=".$size." height=".$size."></fb:name></div>";
            
            // then wrap it in link if needed
            if (!isset($options['link']) || !$options['link']) {
                return $output;
            }

            if (empty($options['courseid'])) {
                $courseid = $this->page->course->id;
            } else {
                $courseid = $options['courseid'];
            }

            if ($courseid == SITEID) {
                $url = new moodle_url('/user/profile.php', array('id' => $user->id));
            } else {
                $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
            }

            $attributes = array('href'=>$url);

            if ($options['popup']) {
                $id = html_writer::random_id('userpicture');
                $attributes['id'] = $id;
                $this->add_action_handler(new popup_action('click', $url), $id);
            }

            return html_writer::tag('a', $output, $attributes);
        }
        else {
          $userpicture = new user_picture($user);
          foreach ((array)$options as $key=>$value) {
              if (array_key_exists($key, $userpicture)) {
                  $userpicture->$key = $value;
              }
          }
          return $this->render($userpicture);
        }
    }
}