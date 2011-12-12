<?php
require_once('../config.php');
require_once($CFG->dirroot.'/calendar/event_form.php');
require_once($CFG->dirroot.'/calendar/lib.php');
require_once($CFG->dirroot.'/course/lib.php');

require_once($CFG->dirroot .'/auth/facebook/lib.php');

//3352 student id
$schema = "pg_test";
$user_db = "root";
$password_db = "";
$ex_user_id = 3352;

$attend_courses_set = get_expertiza_attend_courses($schema,$user_db,$password_db,$ex_user_id);

while($row = mysql_fetch_array($attend_courses_set))
{
    $due_date = get_expertiza_cur_assignment_next_due_date($schema,$user_db,$password_db,$row['parent_id']);
    $assignment_name = get_expertiza_assignment_name($schema,$user_db,$password_db,$row['parent_id']);
    
    if($due_date != null)
    {
       $time = strtotime($due_date);
       $year = date('Y',$time);
       $month = date('n',$time);
       $day = date('j',$time);
       
       //add event to calenda
       add_event_expertiza($assignment_name,$year,$month,$day);
    }
}


$eventurl = $CFG->wwwroot.'/';
redirect($eventurl);
?>
