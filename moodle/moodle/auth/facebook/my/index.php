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
 * This page acts as the "Canvas Page" for the facebook application.
 * eg. http://apps.facebook.com/myapp
 * Simply returns the "My" page with target="_blank" added to all links
 * to enable linking away from Facebook
 *
 * @package    facebook
 * @copyright  2011 Aaron Fulton
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
ob_start();
include($CFG->dirroot.'/my/index.php'); //fetch the "my" page
$result = ob_get_contents();
ob_end_clean();

//use regex to add a "target=_blank" to each hyperlink as we want to link out from Facebook
$pattern = "/(<\s*a\s+[^>]*href\s*=\s*[\"']?[^\"' >]+)[\"' >]/i";
$replacement = '$1" target="_blank"';
echo preg_replace($pattern, $replacement, $result);