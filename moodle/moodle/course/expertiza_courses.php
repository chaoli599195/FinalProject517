<?php
require_once('../config.php');
require_once('lib.php');

require_once($CFG->dirroot.'/enrol/locallib.php');
require_once($CFG->dirroot.'/group/lib.php');
//require_once('edit_form.php');
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/grader/lib.php';

require_once($CFG->dirroot .'/auth/facebook/lib.php');
global $USER,$DB;

//3352 student id
$schema = "pg_test";
$user_db = "root";
$password_db = "";
$ex_user_id = 3352;

$coursecatory = $DB->get_record('course_categories', array('name'=>'Miscellaneous'), '*', MUST_EXIST);
$coursecatory_id = $coursecatory->id;
$course_full_name = "test";
$course_short_name = "test";

$attend_courses_set = get_expertiza_attend_courses($schema,$user_db,$password_db,$ex_user_id);

while($row = mysql_fetch_array($attend_courses_set))
{
    $assignment_name = get_expertiza_assignment_name($schema,$user_db,$password_db,$row['parent_id']);
    $course_full_name = $assignment_name;
    $course_short_name = $assignment_name;
    add_expertiza_courses($coursecatory_id,$course_full_name,$course_short_name);
    
    //enrol user
    $course = $DB->get_record('course', array('fullname'=>$course_full_name), '*', MUST_EXIST);
    $enrol = $DB->get_record('enrol', array('courseid'=>$course->id, 'status'=>0), '*', MUST_EXIST);
    add_user_to_courses($USER->id,$course->id,$enrol->id);
    
    //if complete add final grade
    $due_date = get_expertiza_cur_assignment_next_due_date($schema,$user_db,$password_db,$row['parent_id']);
    
    if($due_date == null)
    {
        add_grade_to_courses($USER->id, $course->id, $row['grade']);
    }
}

$url = $CFG->wwwroot.'/';

redirect($url);

?>
