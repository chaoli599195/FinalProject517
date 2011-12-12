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
 * Settings for the Facebook plugin
 * These are accessed via the $CFG global
 *
 * @package    facebook
 * @copyright  2011 Aaron Fulton
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$settings->add(new admin_setting_configtext('Expertiza_Name', "Expertiza User Name",
                   'Please input your Expertiza User Name', ''));
                   
$settings->add(new admin_setting_configtext('Expertiza_Password', "Expertiza Password",
                   "Please input your Expertiza Password", ''));

/*$settings->add(new admin_setting_configtext('facebook_api_key', get_string('api_key', 'auth_facebook'),
                   get_string('facebook_api_key', 'auth_facebook'), ''));
                   
$settings->add(new admin_setting_configtext('facebook_secret', get_string('secret', 'auth_facebook'),
                   get_string('facebook_secret', 'auth_facebook'), ''));*/
                   
//$settings->add(new admin_setting_configcheckbox('facebook_profile_pic', get_string('profile_pic', 'auth_facebook'),
                   //get_string('facebook_profile_pic', 'auth_facebook'), 0));
