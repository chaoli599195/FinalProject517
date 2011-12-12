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
 * Generic utility functions for use with all Facebook plugins
 *
 * @package    facebook
 * @copyright  2011 Aaron Fulton
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require($CFG->dirroot.'/auth/facebook/facebook-sdk/facebook.php');
require($CFG->dirroot.'/auth/facebook/spyc.php');

/**
 * Generate  the Javascript needed by Facebook Connect
 * This function ensures the javascript is not loaded twice.\
 *
 * @return string Javascript include tags
 */
function facebook_javascript() {
    global $CFG;
    static $loaded;
  
    if (isset($loaded)) {
        return;
    }
  
    $output  = '<div id="fb-root"></div>';
    $output .= '<script src="http://connect.facebook.net/en_US/all.js"></script>';
    $output .= '<script>
        FB.init({
          appId  : \''.$CFG->facebook_api_key.'\',
          status : true,
          cookie : true,
          xfbml  : true
        });
    </script>';

    $output .= '<script src="'.$CFG->wwwroot.'/auth/facebook/facebook.js" type="text/javascript"></script>';

    return $output;
}

/**
 * Creates a Facebook API object
 * This is statically cached so it can be safely called at any time.
 *
 * @return object Facebook object
 */
function facebook_object_initialize() {
    global $CFG;
    static $FB;
  
    if (isset($FB)) {
        return $FB;
    }
  
    $FB = new Facebook(array(
        'appId'  => $CFG->facebook_api_key,
        'secret' => $CFG->facebook_secret,
        'cookie' => true,
    ));

    return $FB;
}

/**
 * Retrieve the users Moodle ID given a Facebook ID
 * 
 * @param int $fb_id Facebook User ID
 * @return string Moodle User ID
 */
function facebook_get_moodle_id($fb_id) {
    global $DB;
    $data = $DB->get_record('user_info_field', array('datatype' => 'facebook'), 'id');
    $fieldid = $data->id;

    if ($fieldid) {
        $data = $DB->get_record_sql("SELECT userid FROM {user_info_data} WHERE data = ". $DB->sql_compare_text($fb_id));
        $userid = $data->userid;
    }

    return $userid;
}

/**
 * Retrieve a Facebook ID given a Moodle ID
 *
 * @param int $moodle_id Moodle User ID
 * @return string Facebook ID
 */
function facebook_get_facebook_id($moodle_id) {
    global $DB;
  
    $data = $DB->get_record('user_info_field', array('datatype' => 'facebook'), 'id');
    $fieldid = $data->id;
    if ($fieldid) {
        $data = $DB->get_record('user_info_data', array('userid' => $moodle_id, 'fieldid' => $fieldid), 'data');
        $facebook_id = $data->data;
    }
    return $facebook_id;
}

function explode_symbols($arr) {
  $result = array();
  foreach($arr as $key => $val) {
    if(is_numeric($key) && $val[0] == ":") {
      $bits = explode(":", $val, 3);
      $result[trim($bits[1])] = trim($bits[2]);
    } elseif (is_array($val)) {
      $result[$key] = explode_symbols($val);
    } else {
      $result[$key] = $val;
    }
  }
  return $result;
}
 
function deserialize_session($session_key, $secret) {
  list($session64, $hash) = explode("--", $_COOKIE[$session_key], 2);
  if(hash_hmac("SHA1", $session64, $secret) == $hash) {
    $session = base64_decode($session64);
    return explode_symbols(spyc_load($session));
  } else {
    return NULL;
    //throw new Exception("Invalid session signature");
  }
}

/**
 * List of Facebook permissions used by this package
 *
 *@return string
 */
function facebook_get_params() {
    //add these when Facebook reenables them: user_address,user_mobile_phone
    return 'user_about_me,user_online_presence,user_interests,user_website,email,publish_stream,offline_access'; //not sure if ofline_access is really necessary.
}

/**
 * Get attending courses of the user in expertiza
 * $shcema_name,$db_user,$db_password,$user_id
 * @return set of sql result
 */
function get_expertiza_attend_courses($shcema_name,$db_user,$db_password,$user_id)
{
    //add these when Facebook reenables them: user_address,user_mobile_phone
    $con = mysql_connect("localhost",$db_user,$db_password);
        if (!$con)
          {
          die('Could not connect: ' . mysql_error());
          }
          
        mysql_select_db($shcema_name, $con);

        $sql = "SELECT * FROM participants WHERE user_id = '$user_id'";
        $result = mysql_query($sql);

        mysql_close($con);
        return $result; //not sure if ofline_access is really necessary.
}

/**
 * Get attending courses of the user in expertiza
 * $shcema_name,$db_user,$db_password,$topic_id
 * @return string
 */
function get_expertiza_assignment_name($shcema_name,$db_user,$db_password,$parent_id)
{
    //add these when Facebook reenables them: user_address,user_mobile_phone
    $con = mysql_connect("localhost",$db_user,$db_password);
        if (!$con)
          {
          die('Could not connect: ' . mysql_error());
          }
          
        mysql_select_db($shcema_name, $con);

        $sql = "SELECT * FROM assignments WHERE id = '$parent_id'";
        $result = mysql_query($sql);
        $row = mysql_fetch_array($result);
        
        if($row != null)
            $assignment_name =  $row['name'];

        mysql_close($con);
        return $assignment_name; //not sure if ofline_access is really necessary.
}

/**
 * Get attending courses of the user in expertiza
 * $shcema_name,$db_user,$db_password,$assignment_id
 *@return $current_stage
 */
function get_expertiza_cur_assignment_stage($shcema_name,$db_user,$db_password,$assignment_id)
{
    //add these when Facebook reenables them: user_address,user_mobile_phone
    $con = mysql_connect("localhost",$db_user,$db_password);
        if (!$con)
          {
          die('Could not connect: ' . mysql_error());
          }
          
        mysql_select_db($shcema_name, $con);

        $sql = "SELECT * FROM due_dates WHERE assignment_id = '$assignment_id' Order by due_at asc";
        $result = mysql_query($sql);
        $date = date("Y-m-d H:i:s");
        $flag_in_stage = 0;
        
        $result = mysql_query($sql);
        while($row = mysql_fetch_array($result))
        {
            if($row['due_at'] > $date)
            {
                $flag_in_stage = 1;
                break;
            }
            else
                $flag_in_stage = 2;
        }
        
        $current_stage = 'Unknow';
            
        if($flag_in_stage == 1)
        {
           $stage_type = $row['deadline_type_id'];
           $sql = "select * from deadline_types where id = '$stage_type'";
           $result = mysql_query($sql);
           $row = mysql_fetch_array($result);
           if($row != null)
                $current_stage = $row['name'];
        }
        else
            if($flag_in_stage == 2)
                $current_stage = 'Complete';
                else
                    if($flag_in_stage == 0)
                        $current_stage = 'Unknow';

        mysql_close($con);
        return $current_stage; //not sure if ofline_access is really necessary.
}

/**
 * Get attending courses of the user in expertiza
 * $shcema_name,$db_user,$db_password,$assignment_id
 *@return $next_due_date
 */
function get_expertiza_cur_assignment_next_due_date($shcema_name,$db_user,$db_password,$assignment_id)
{
    //add these when Facebook reenables them: user_address,user_mobile_phone
    $con = mysql_connect("localhost",$db_user,$db_password);
        if (!$con)
          {
          die('Could not connect: ' . mysql_error());
          }
          
        mysql_select_db($shcema_name, $con);

        $sql = "SELECT * FROM due_dates WHERE assignment_id = '$assignment_id' Order by due_at asc";
        $result = mysql_query($sql);
        $date = date("Y-m-d H:i:s");
        $flag_in_stage = 0;
        
        $result = mysql_query($sql);
        while($row = mysql_fetch_array($result))
        {
            if($row['due_at'] > $date)
            {
                $flag_in_stage = 1;
                break;
            }
        }
            
        if($flag_in_stage == 1)
            $next_due_date = $row['due_at'];
        else
            $next_due_date = null;
        
        mysql_close($con);
        return $next_due_date; //not sure if ofline_access is really necessary.
}

//add assignment due date to calendar
function add_event_expertiza($assignment_name,$y,$m,$d)
{

    require_login();

    //$action = optional_param('action', 'new', PARAM_ALPHA);
    $action = "new";
    $eventid = optional_param('id', 0, PARAM_INT);
    $courseid = optional_param('courseid', SITEID, PARAM_INT);
    $courseid = optional_param('course', $courseid, PARAM_INT);

    $cal_y = $y;
    $cal_m = $m;
    $cal_d = $d;

    $url = new moodle_url('/calendar/event.php', array('action' => $action));
    if ($eventid != 0) {
        $url->param('id', $eventid);
    }
    if ($courseid != SITEID) {
        $url->param('course', $courseid);
    }
    if ($cal_y !== 0) {
        $url->param('cal_y', $cal_y);
    }
    if ($cal_m !== 0) {
        $url->param('cal_m', $cal_m);
    }
    if ($cal_d !== 0) {
        $url->param('cal_d', $cal_d);
    }
   // $PAGE->set_url($url);
   // $PAGE->set_pagelayout('standard');

    if ($courseid != SITEID && !empty($courseid)) {
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $courses = array($course->id => $course);
        $issite = false;
    } else {
        $course = get_site();
        //$courses = calendar_get_default_courses();
        $courses = array();
        $issite = true;
    }
//    require_login($course, false);

  /*  if ($action === 'delete' && $eventid > 0) {
        $deleteurl = new moodle_url('/calendar/delete.php', array('id'=>$eventid));
        if ($courseid > 0) {
            $deleteurl->param('course', $courseid);
        }
        redirect($deleteurl);
    }*/

    $calendar = new calendar_information($cal_d, $cal_m, $cal_y);
    $calendar->prepare_for_view($course, $courses);

    $formoptions = new stdClass;
    
    if ($eventid !== 0) {
        $title = get_string('editevent', 'calendar');
        $event = calendar_event::load($eventid);
        if (!calendar_edit_event_allowed($event)) {
            print_error('nopermissions');
        }
        $event->action = $action;
        $event->course = $courseid;
        $event->timedurationuntil = $event->timestart + $event->timeduration;
        $event->count_repeats();

        if (!calendar_add_event_allowed($event)) {
            print_error('nopermissions');
        }
    } else {
        $title = get_string('newevent', 'calendar');
        calendar_get_allowed_types($formoptions->eventtypes, $course);
        $event = new stdClass();
        $event->action = $action;
        $event->course = $courseid;
        $event->timeduration = 0;
        if ($formoptions->eventtypes->courses) {
            if (!$issite) {
                $event->courseid = $courseid;
                $event->eventtype = 'course';
            } else {
                unset($formoptions->eventtypes->courses);
                unset($formoptions->eventtypes->groups);
            }
        }
        if($cal_y && $cal_m && $cal_d && checkdate($cal_m, $cal_d, $cal_y)) {
            $event->timestart = make_timestamp($cal_y, $cal_m, $cal_d, 0, 0, 0);
        } else if($cal_y && $cal_m && checkdate($cal_m, 1, $cal_y)) {
            $now = usergetdate(time());
            if($cal_y == $now['year'] && $cal_m == $now['mon']) {
                $event->timestart = make_timestamp($cal_y, $cal_m, $now['mday'], 0, 0, 0);
            } else {
                $event->timestart = make_timestamp($cal_y, $cal_m, 1, 0, 0, 0);
            }
        }
        $event = new calendar_event($event);
        if (!calendar_add_event_allowed($event)) {
            print_error('nopermissions');
        }
    }

    $properties = $event->properties(true);
    $formoptions->event = $event;
    $formoptions->hasduration = ($event->timeduration > 0);
    $mform = new event_form(null, $formoptions);
    $mform->set_data($properties);
    $data = $mform->get_data();

        $name = $data->name;
        $data->name = $assignment_name ;
        if ($data->duration == 1) {
            $data->timeduration = $data->timedurationuntil- $data->timestart;
        } else if ($data->duration == 2) {
            $data->timeduration = $data->timedurationminutes * MINSECS;
        } else {
            $data->timeduration = 0;
        }

        $event->update($data);

        $params = array(
            'view' => 'day',
            'cal_d' => date('j', $event->timestart),
            'cal_m' => date('n', $event->timestart),
            'cal_y' => date('y', $event->timestart),
        );


        $eventurl = new moodle_url('../', $params);
        if (!empty($event->courseid) && $event->courseid != SITEID) {
            $eventurl->param('course', $event->courseid);
        }
        $eventurl->set_anchor('event_'.$event->id);
        //redirect($eventurl);

}


//add assignment due date to calendar
function add_expertiza_courses($category_id, $course_full, $course_short)
{    
    global $DB;
    
    $id         = 0;       // course id
    $categoryid = $category_id; // course category - can be changed in edit form
    $returnto = null; // generic navigation return page switch

    //$PAGE->set_pagelayout('admin');
    //$PAGE->set_url('/course/edit.php');

    // basic access control checks
    if ($id) { // editing course
        if ($id == SITEID){
            // don't allow editing of  'site course' using this from
            print_error('cannoteditsiteform');
        }

        $course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
        //require_login($course);
        $category = $DB->get_record('course_categories', array('id'=>$course->category), '*', MUST_EXIST);
        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
        require_capability('moodle/course:update', $coursecontext);
        //$PAGE->url->param('id',$id);

    } else if ($categoryid) { // creating new course in this category
        $course = null;
        require_login();
        $category = $DB->get_record('course_categories', array('id'=>$categoryid), '*', MUST_EXIST);
        $catcontext = get_context_instance(CONTEXT_COURSECAT, $category->id);
        require_capability('moodle/course:create', $catcontext);
        //$PAGE->url->param('category',$categoryid);
        //$PAGE->set_context($catcontext);

    } else {
        require_login();
        print_error('needcoursecategroyid');
    }

    // Prepare course and the editor
    $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true);
    if (!empty($course)) {
        $allowedmods = array();
        if ($am = $DB->get_records('course_allowed_modules', array('course'=>$course->id))) {
            foreach ($am as $m) {
                $allowedmods[] = $m->module;
            }
        } else {
            // this happens in case we edit course created before enabling module restrictions or somebody disabled everything :-(
            if (empty($course->restrictmodules) and !empty($CFG->defaultallowedmodules)) {
                $allowedmods = explode(',', $CFG->defaultallowedmodules);
            }
        }
        $course->allowedmods = $allowedmods;
        //add context for editor
        $editoroptions['context'] = $coursecontext;
        $course = file_prepare_standard_editor($course, 'summary', $editoroptions, $coursecontext, 'course', 'summary', 0);

    } else {
        //editor should respect category context if course context is not set.
        $editoroptions['context'] = $catcontext;
        $course = file_prepare_standard_editor($course, 'summary', $editoroptions, null, 'course', 'summary', null);
    }

    // first create the form
    //$editform = new course_edit_form(NULL, array('course'=>$course, 'category'=>$category, 'editoroptions'=>$editoroptions, 'returnto'=>$returnto));
    
    //if (!$data = $editform->get_data()) {
        // process data if submitted
    $data->fullname = $course_full;
    $data->shortname = $course_short;
    $data->category = $categoryid;

        if (empty($course->id)) {
            // In creating the course
            $course = create_course($data, $editoroptions);

            // Get the context of the newly created course
            $context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);

            if (!empty($CFG->creatornewroleid) and !is_viewing($context, NULL, 'moodle/role:assign') and !is_enrolled($context, NULL, 'moodle/role:assign')) {
                // deal with course creators - enrol them internally with default role
                enrol_try_internal_enrol($course->id, $USER->id, $CFG->creatornewroleid);

            }
            /*if (!is_enrolled($context)) {
                // Redirect to manual enrolment page if possible
                $instances = enrol_get_instances($course->id, true);
                foreach($instances as $instance) {
                    if ($plugin = enrol_get_plugin($instance->enrol)) {
                        if ($plugin->get_manual_enrol_link($instance)) {
                            // we know that the ajax enrol UI will have an option to enrol
                            redirect(new moodle_url('/enrol/users.php', array('id'=>$course->id)));
                        }
                    }
                }
            }*/
        } else {
            // Save any changes to the files used in the editor
            update_course($data, $editoroptions);
        }

        //redirect($url);
    //}
}

function add_user_to_courses($cur_userid, $course_id, $enrol_id)
{    
    // Must have the sesskey
    global $DB,$PAGE;
    $id      = $course_id; // course id
    $action  = "enrol";

    //$PAGE->set_url(new moodle_url('/enrol/ajax.php', array('id'=>$id, 'action'=>$action)));

    $course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
    $context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);

    if ($course->id == SITEID) {
        throw new moodle_exception('invalidcourse');
    }

    //require_login($course);
    //require_capability('moodle/course:enrolreview', $context);
    //require_sesskey();

    //echo $OUTPUT->header(); // send headers

    $manager = new course_enrolment_manager($PAGE, $course);

    $outcome = new stdClass;
    $outcome->success = true;
    $outcome->response = new stdClass;
    $outcome->error = '';

    $enrolid = $enrol_id;
    $userid = $cur_userid;

    //student default
    $roleid = 5;
    $duration = optional_param('duration', 0, PARAM_INT);
    $startdate = 0;
    $recovergrades = optional_param('recovergrades', 0, PARAM_INT);

            if (empty($roleid)) {
                $roleid = null;
            }

            switch($startdate) {
                case 2:
                    $timestart = $course->startdate;
                    break;
                case 3:
                default:
                    $today = time();
                    $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
                    $timestart = $today;
                    break;
            }
            if ($duration <= 0) {
                $timeend = 0;
            } else {
                $timeend = $timestart + ($duration*24*60*60);
            }

            $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);
            $instances = $manager->get_enrolment_instances();
            $plugins = $manager->get_enrolment_plugins();
            if (!array_key_exists($enrolid, $instances)) {
                throw new enrol_ajax_exception('invalidenrolinstance');
            }
            $instance = $instances[$enrolid];
            $plugin = $plugins[$instance->enrol];
            if ($plugin->allow_enrol($instance) && has_capability('enrol/'.$plugin->get_name().':enrol', $context)) {
                $plugin->enrol_user($instance, $user->id, $roleid, $timestart, $timeend);
                if ($recovergrades) {
                    require_once($CFG->libdir.'/gradelib.php');
                    grade_recover_history_grades($user->id, $instance->courseid);
                }
            } else {
                throw new enrol_ajax_exception('enrolnotpermitted');
            }
    
}
    

function add_grade_to_courses($cur_userid, $course_id, $grade)
{    
    global $USER,$DB,$CFG;
    
    $courseid      = $course_id;        // course id
    $page          = optional_param('page', 0, PARAM_INT);   // active page
    $perpageurl    = optional_param('perpage', 0, PARAM_INT);
    $edit          = optional_param('edit', -1, PARAM_BOOL); // sticky editting mode

    $sortitemid    = optional_param('sortitemid', 0, PARAM_ALPHANUM); // sort by which grade item
    $action        = optional_param('action', 0, PARAM_ALPHAEXT);
    $move          = optional_param('move', 0, PARAM_INT);
    $type          = optional_param('type', 0, PARAM_ALPHA);
    $target        = optional_param('target', 0, PARAM_ALPHANUM);
    $toggle        = optional_param('toggle', NULL, PARAM_INT);
    $toggle_type   = optional_param('toggle_type', 0, PARAM_ALPHANUM);

    //$PAGE->set_url(new moodle_url('/grade/report/grader/index.php', array('id'=>$courseid)));

    /// basic access checks
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        print_error('nocourseid');
    }
    require_login($course);
    $context = get_context_instance(CONTEXT_COURSE, $course->id);

    require_capability('gradereport/grader:view', $context);
    require_capability('moodle/grade:viewall', $context);

    /// return tracking object
    $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'grader', 'courseid'=>$courseid, 'page'=>$page));

    /// last selected report session tracking
    if (!isset($USER->grade_last_report)) {
        $USER->grade_last_report = array();
    }
    $USER->grade_last_report[$course->id] = 'grader';

    /// Build editing on/off buttons

    if (!isset($USER->gradeediting)) {
        $USER->gradeediting = array();
    }

    if (has_capability('moodle/grade:edit', $context)) {
        if (!isset($USER->gradeediting[$course->id])) {
            $USER->gradeediting[$course->id] = 0;
        }

        if (($edit == 1) and confirm_sesskey()) {
            $USER->gradeediting[$course->id] = 1;
        } else if (($edit == 0) and confirm_sesskey()) {
            $USER->gradeediting[$course->id] = 0;
        }

        // page params for the turn editting on
        //$options = $gpr->get_options();
        //$options['sesskey'] = sesskey();

        if ($USER->gradeediting[$course->id]) {
            $options['edit'] = 0;
            $string = get_string('turneditingoff');
        } else {
            $options['edit'] = 1;
            $string = get_string('turneditingon');
        }

    } else {
        $USER->gradeediting[$course->id] = 0;
        $buttons = '';
    }

    $gradeserror = array();

    // Handle toggle change request
    if (!is_null($toggle) && !empty($toggle_type)) {
        set_user_preferences(array('grade_report_show'.$toggle_type => $toggle));
    }

    //first make sure we have proper final grades - this must be done before constructing of the grade tree
    grade_regrade_final_grades($courseid);

    // Perform actions
    if (!empty($target) && !empty($action) && confirm_sesskey()) {
        grade_report_grader::process_action($target, $action);
    }

    $reportname = get_string('pluginname', 'gradereport_grader');

    /// Print header
    //print_grade_page_head($COURSE->id, 'report', 'grader', $reportname, false, $buttons);

    //Initialise the grader report object that produces the table
    //the class grade_report_grader_ajax was removed as part of MDL-21562
    $report = new grade_report_grader($courseid, $gpr, $context, $page, $sortitemid);
    $grade_item = $DB->get_record('grade_items', array('courseid'=>$courseid), '*', MUST_EXIST);

    // processing posted grades & feedback here
    $grade_token = "grade_".$USER->id."_".$grade_item->id;
    $data[$grade_token] = $grade;
    $warnings = $report->process_data($data);

}