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
 * Facebook link account form.
 *
 * This file file is called when a user goes to log into moodle using thier
 * Facebook account and there is no Facebook ID stored in the system for them.
 * Either they are a new user (who should sign up) or an existing user (should add Facebook details to account)
 *
 * @package    facebook
 * @copyright  2011 Aaron Fulton
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require($CFG->dirroot.'/config.php');
require($CFG->dirroot.'/auth/facebook/lib.php');

$testsession = optional_param('testsession', 0, PARAM_INT); // test session works properly
$cancel = optional_param('cancel', 0, PARAM_BOOL);      // redirect to frontpage, needed for loginhttps

if ($cancel) {
    redirect(new moodle_url('/'));
}

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_url("$CFG->httpswwwroot/login/index.php");
$PAGE->set_context($context);
$PAGE->set_pagelayout('login');

// We don't want to allow use of this script if facebook auth isn't enabled
if (!is_enabled_auth('facebook')) {
    error("Facebook not enabled!");
}
   
/// Define variables used in page
$site = get_site();

$loginsite = get_string("loginsite");
$PAGE->navbar->add($loginsite);

/// Check if the user has actually submitted login data to us

if ($frm and isset($frm->username)) {                             // Login WITH cookies

    $frm->username = trim(moodle_strtolower($frm->username));

    if (is_enabled_auth('none') ) {
        if ($frm->username !== clean_param($frm->username, PARAM_USERNAME)) {
            $errormsg = get_string('username').': '.get_string("invalidusername");
            $errorcode = 2;
            $user = null;
        }
    }

    if ($user) {
        //user already supplied by aut plugin prelogin hook
    } else if (($frm->username == 'guest') and empty($CFG->guestloginbutton)) {
        $user = false;    /// Can't log in as guest if guest button is disabled
        $frm = false;
    } else {
        if (empty($errormsg)) {
            $user = authenticate_user_login($frm->username, $frm->password);
        }
    }

    // Intercept 'restored' users to provide them with info & reset password
    if (!$user and $frm and is_restored_user($frm->username)) {
        $PAGE->set_title(get_string('restoredaccount'));
        $PAGE->set_heading($site->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('restoredaccount'));
        echo $OUTPUT->box(get_string('restoredaccountinfo'), 'generalbox boxaligncenter');
        require_once('restored_password_form.php'); // Use our "supplanter" login_forgot_password_form. MDL-20846
        $form = new login_forgot_password_form('forgot_password.php', array('username' => $frm->username));
        $form->display();
        echo $OUTPUT->footer();
        die;
    }

    update_login_count();

    if ($user) {

        // language setup
        if (isguestuser($user)) {
            // no predefined language for guests - use existing session or default site lang
            unset($user->lang);

        } else if (!empty($user->lang)) {
            // unset previous session language - use user preference instead
            unset($SESSION->lang);
        }

        if (empty($user->confirmed)) {       // This account was never confirmed
            $PAGE->set_title(get_string("mustconfirm"));
            $PAGE->set_heading($site->fullname);
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string("mustconfirm"));
            echo $OUTPUT->box(get_string("emailconfirmsent", "", $user->email), "generalbox boxaligncenter");
            echo $OUTPUT->footer();
            die;
        }

    /// Let's get them all set up.
        add_to_log(SITEID, 'user', 'login', "view.php?id=$USER->id&course=".SITEID,
                   $user->id, 0, $user->id);
        complete_user_login($user, true); // sets the username cookie

    /// Prepare redirection
        if (user_not_fully_set_up($USER)) {
            $urltogo = $CFG->wwwroot.'/user/edit.php';
            // We don't delete $SESSION->wantsurl yet, so we get there later

        } else if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot) === 0)) {
            $urltogo = $SESSION->wantsurl;    /// Because it's an address in this site
            unset($SESSION->wantsurl);

        } else {
            // no wantsurl stored or external - go to homepage
            $urltogo = $CFG->wwwroot.'/';
            unset($SESSION->wantsurl);
        }

    /// Go to my-moodle page instead of site homepage if defaulthomepage set to homepage_my
        if (!empty($CFG->defaulthomepage) && $CFG->defaulthomepage == HOMEPAGE_MY && !is_siteadmin() && !isguestuser()) {
            if ($urltogo == $CFG->wwwroot or $urltogo == $CFG->wwwroot.'/' or $urltogo == $CFG->wwwroot.'/index.php') {
                $urltogo = $CFG->wwwroot.'/my/';
            }
        }


    /// check if user password has expired
    /// Currently supported only for ldap-authentication module
        $userauth = get_auth_plugin($USER->auth);
        if (!empty($userauth->config->expiration) and $userauth->config->expiration == 1) {
            if ($userauth->can_change_password()) {
                $passwordchangeurl = $userauth->change_password_url();
                if (!$passwordchangeurl) {
                    $passwordchangeurl = $CFG->httpswwwroot.'/login/change_password.php';
                }
            } else {
                $passwordchangeurl = $CFG->httpswwwroot.'/login/change_password.php';
            }
            $days2expire = $userauth->password_expire($USER->username);
            $PAGE->set_title("$site->fullname: $loginsite");
            $PAGE->set_heading("$site->fullname");
            if (intval($days2expire) > 0 && intval($days2expire) < intval($userauth->config->expiration_warning)) {
                echo $OUTPUT->header();
                echo $OUTPUT->confirm(get_string('auth_passwordwillexpire', 'auth', $days2expire), $passwordchangeurl, $urltogo);
                echo $OUTPUT->footer();
                exit;
            } elseif (intval($days2expire) < 0 ) {
                echo $OUTPUT->header();
                echo $OUTPUT->confirm(get_string('auth_passwordisexpired', 'auth'), $passwordchangeurl, $urltogo);
                echo $OUTPUT->footer();
                exit;
            }
        }

        reset_login_count();

        // test the session actually works by redirecting to self
        $SESSION->wantsurl = $urltogo;
        redirect(new moodle_url(get_login_url(), array('testsession'=>$USER->id)));

    } else {
        if (empty($errormsg)) {
            $errormsg = get_string("invalidlogin");
            $errorcode = 3;
        }
    }
}

/// Detect problems with timedout sessions
if ($session_has_timed_out and !data_submitted()) {
    $errormsg = get_string('sessionerroruser', 'error');
    $errorcode = 4;
}

/// First, let's remember where the user was trying to get to before they got here

if (empty($SESSION->wantsurl)) {
    $SESSION->wantsurl = (array_key_exists('HTTP_REFERER',$_SERVER) &&
                          $_SERVER["HTTP_REFERER"] != $CFG->wwwroot &&
                          $_SERVER["HTTP_REFERER"] != $CFG->wwwroot.'/' &&
                          $_SERVER["HTTP_REFERER"] != $CFG->httpswwwroot.'/login/' &&
                          $_SERVER["HTTP_REFERER"] != $CFG->httpswwwroot.'/login/index.php')
        ? $_SERVER["HTTP_REFERER"] : NULL;
}

// make sure we really are on the https page when https login required
$PAGE->verify_https_required();

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
} else {
    include("link_account.html");
}


echo $OUTPUT->footer();
?>
