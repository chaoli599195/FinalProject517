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
 * Facebook login form
 *
 * This file is principally a copy of the relevant parts of the Moodle
 * /login/index.php file from the default installation.
 *
 * @author Aaron Fulton
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Facebook
 **/

require '../../config.php';
require $CFG->dirroot.'/auth/facebook/lib.php';

redirect_if_major_upgrade_required();

$testsession = optional_param('testsession', 0, PARAM_INT); // test session works properly
$cancel      = optional_param('cancel', 0, PARAM_BOOL);      // redirect to frontpage, needed for loginhttps

if ($cancel) {
    redirect(new moodle_url('/'));
}

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_url("$CFG->httpswwwroot/login/index.php");
$PAGE->set_context($context);
$PAGE->set_pagelayout('login');

/// Initialize variables
$errormsg = '';
$errorcode = 0;

// login page requested session test
if ($testsession) {
    if ($testsession == $USER->id) {
        if (isset($SESSION->wantsurl)) {
            $urltogo = $SESSION->wantsurl;
        } else {
            $urltogo = $CFG->wwwroot.'/';
        }
        unset($SESSION->wantsurl);
        redirect($urltogo);
    } else {
        // TODO: try to find out what is the exact reason why sessions do not work
        $errormsg = get_string("cookiesnotenabled");
        $errorcode = 1;
    }
}

/// Check for timed out sessions
if (!empty($SESSION->has_timed_out)) {
    $session_has_timed_out = true;
    unset($SESSION->has_timed_out);
} else {
    $session_has_timed_out = false;
}

/// auth plugins may override these - SSO anyone?
$frm  = false;
$user = false;

// We don't want to allow use of this script if facebook auth isn't enabled
if (!is_enabled_auth('facebook')) {
    error("Facebook not enabled!");
}
    
/// Define variables used in page
$site = get_site();

$loginsite = get_string("loginsite");
$PAGE->navbar->add($loginsite);

// make sure we really are on the https page when https login required
$PAGE->verify_https_required();

/// Generate the login page with forms

if (empty($frm->username)) {
    if (!empty($_GET["username"])) {
        $frm->username = $_GET["username"];
    } else {
        $frm->username = get_moodle_cookie() === 'nobody' ? '' : get_moodle_cookie();
    }

    $frm->password = "";
}

if (!empty($frm->username)) {
    $focus = "password";
} else {
    $focus = "username";
}

if (!empty($CFG->registerauth) or is_enabled_auth('none') or !empty($CFG->auth_instructions)) {
    $show_instructions = true;
} else {
    $show_instructions = false;
}

$PAGE->set_title("$site->fullname: $loginsite");
$PAGE->set_heading("$site->fullname");

echo $OUTPUT->header();

if (isloggedin() and !isguestuser()) {
    // prevent logging when already logged in, we do not want them to relogin by accident because sesskey would be changed
    echo $OUTPUT->box_start();
    $logout = new single_button(new moodle_url($CFG->httpswwwroot.'/login/logout.php', array('sesskey'=>sesskey(),'loginpage'=>1)), get_string('logout'), 'post');
    $continue = new single_button(new moodle_url($CFG->httpswwwroot.'/login/index.php', array('cancel'=>1)), get_string('cancel'), 'get');
    echo $OUTPUT->confirm(get_string('alreadyloggedin', 'error', fullname($USER)), $logout, $continue);
    echo $OUTPUT->box_end();
}
else {
    $error_code = isset($_GET['errorcode']) ? $_GET['errorcode'] : 0;
    switch ($error_code) {
      case 1:
        $errormsg = get_string("cookiesnotenabled");
        break;
      case 2:
        $errormsg = get_string('username').': '.get_string("invalidusername");
        break;
      case 3:
        $errormsg = get_string("invalidlogin");
        break;
      case 4:
        $errormsg = get_string('sessionerroruser', 'error');
        break;
    }
    include("login_form.html");
}

echo $OUTPUT->footer();