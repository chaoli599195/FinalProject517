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
 * Authentication Plugin: Facebook Authentication
 *
 * Authenticates against Facebook.
 *
 * 2009-11-15  File created.
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/auth/facebook/lib.php');

/**
 * facebook authentication plugin.
 */
class auth_plugin_facebook extends auth_plugin_base {

    /**
     * Constructor.
     */
    function auth_plugin_facebook() {
        $this->authtype = 'facebook';
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }


    /**
     * Hook for overriding behavior of login page.
     * This method is called from login/index.php page for all enabled auth
     * plugins.
     *
     * We're overriding the default login behaviour when login is attempted or
     * a Facebook response is received.  We also provide our own login form if
     * an alternate login url hasn't already been defined.  This doesn't alter
     * the site's configuration value. This function performs all the login functions for Facebook.
     */
    function loginpage_hook() {
        global $CFG, $frm, $user; // Login page variables
        
        $fb_login = optional_param('fb_login', null, PARAM_INT);
        
        $participate_set = get_expertiza_attend_courses("pg_test",'root','','3352');
        
        while($row = mysql_fetch_array($participate_set))
        {
            $assignment_name = get_expertiza_assignment_name("pg_test",'root','',$row['parent_id']);
            $stage = get_expertiza_cur_assignment_stage("pg_test",'root','',$row['parent_id']);
            $next_due_date = get_expertiza_cur_assignment_next_due_date("pg_test",'root','',$row['parent_id']);
            echo $stage;
        }
        
        
        
        /*$con = mysql_connect("localhost","root","");
        if (!$con)
          {
          die('Could not connect: ' . mysql_error());
          }
          
        mysql_select_db("pg_test", $con);

        $result = mysql_query("SELECT * FROM assignments");
        
        while($row = mysql_fetch_array($result))
        {
          $new_value = $row['name'] ;
        }

        // some code

        mysql_close($con);*/
        
        if (empty($CFG->alternateloginurl)) {
            $CFG->alternateloginurl = $CFG->wwwroot .'/auth/facebook/login.php';
        }
        
       /* if ($fb_login == 1) {
          //We've received a facebook login attempt
          $FB = facebook_object_initialize();
          $fb_user = $FB->getUser();
          $moodle_id = facebook_get_moodle_id($fb_user);
          if (!empty($moodle_id)) { //user is found.  we can log them in
            $user = get_complete_user_data('id', $moodle_id);
            $frm->username = $user->username;
          }
          else { //we don't know who the user is.  Either they are a new user, or they have an existing account that is non-facebook enabled.
            redirect($CFG->wwwroot .'/auth/facebook/link_account.php');
          }
        }
        if ($fb_login == 2) {
            //user has supplied username and password to link their existing account with their facebook account
            //need to validate user here then save the facebook id against the account.
            $FB = facebook_object_initialize();
            $fb_user = $FB->getUser();

            $username = optional_param('username', null);
            $password = optional_param('password', null);
            $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
            
            if ($user && validate_internal_user_password($user, $password)) {
                $fieldid = get_field('user_info_field', 'id', 'datatype', 'facebook');

                $data = new object();
                $data->userid  = $user->id;
                $data->fieldid = $fieldid;
                $data->data    = $fb_user;

                insert_record('user_info_data', $data);

                $frm->username = $user->username;
            } else {
                if (empty($errormsg)) {
                    $errormsg = get_string("invalidlogin");
                    $errorcode = 3;
                }
                redirect($CFG->wwwroot .'/auth/facebook/link_account.php');
            }
        }*/
    }
}