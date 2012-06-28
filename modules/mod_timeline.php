<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Get the program ID
$program_id = $core->variable('prg', 0);

// We need program ID for this module
$user->restrict($program_id > 0);

// Escape the program ID
$db->escape($program_id);

// Get program data
$sql = "SELECT * FROM {$db->prefix}programs " .
       "WHERE id = {$program_id}";
$program_data = $db->query($sql, true);

// Check if program was found
if ($program_data != null)
{
    $month   = new DateTime();
    $start   = new DateTime();
    $student = new DateTime();
    $mentor  = new DateTime();
    $end     = new DateTime();
    
    $start->setTimestamp($program_data['start_time']);
    $student->setTimestamp($program_data['dl_student']);
    $mentor->setTimestamp($program_data['dl_mentor']);
    $end->setTimestamp($program_data['end_time']);

    // We will start from the project's start month
    $month->setTimestamp($program_data['start_time']);

    // Traverse through all months
    for ($idx = 1; $idx <= 12; $idx++)
    {
        $event = false;

        // Get month number and name
        $month_num  = $month->format('n');
        $month_name = $month->format('F');

        // Create a segment for this month
        $donut->create_segment($month_name);

        // Add program start slice
        if ($month_num == $start->format('n'))
        {
            $donut->add_slice($lang->get('application_start'));
            $event = true;
        }

        // Add student deadline slice
        if ($month_num == $student->format('n'))
        {
            $donut->add_slice($lang->get('student_appl_dl'));
            $event = true;
        }

        // Add mentor deadline slice
        if ($month_num == $mentor->format('n'))
        {
            $donut->add_slice($lang->get('mentor_appl_dl'));
            $event = true;
        }

        // Add end of season slice
        // We also add a coding slice here, since well, students *are* coding!
        if ($month_num == $end->format('n'))
        {
            $donut->add_slice($lang->get('students_coding'));
            $donut->add_slice($lang->get('season_complete'));
            $event = true;
        }

        // Now we add intermediate slices for empty segments
        if (!$event)
        {
            // Application processing before mentor deadlines
            if ($month > $start && $month < $mentor)
            {
                $donut->add_slice($lang->get('application_process'));
                $event = true;
            }

            // Coding period slice
            if ($month > $mentor && $month < $end)
            {
                $donut->add_slice($lang->get('students_coding'));
                $event = true;
            }
        }

        // Still no events. This means it is off season
        if (!$event)
        {
            $donut->add_slice($lang->get('off_season'));
        }

        // Add the segment to the donut
        $donut->add_segment();

        // Increment the month
        $month->add(new DateInterval('P1M'));
    }
}

// Output the donut
$donut->output('donut_timeline');
    
?>