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
    $start_month   = date('n', $program_data['start_time']);
    $student_month = date('n', $program_data['dl_student']);
    $mentor_month  = date('n', $program_data['dl_mentor']);
    $end_month     = date('n', $program_data['end_time']);

    // We will start from the project's start month
    $month = $start_month;

    // Traverse through all months
    for ($idx = 1; $idx <= 12; $idx++)
    {
        $event = false;
        
        // Get month name
        $month_name = date("F", mktime(0, 0, 0, $month));

        // Create a segment for this month
        $donut->create_segment($month_name);

        // Add program start slice
        if ($month == $start_month)
        {
            $donut->add_slice($lang->get('application_start'));
            $event = true;
        }

        // Add student deadline slice
        if ($month == $student_month)
        {
            $donut->add_slice($lang->get('student_appl_dl'));
            $event = true;
        }

        // Add mentor deadline slice
        if ($month == $mentor_month)
        {
            $donut->add_slice($lang->get('mentor_appl_dl'));
            $event = true;
        }

        // Add end of season slice
        if ($month == $end_month)
        {
            $donut->add_slice($lang->get('season_complete'));
            $event = true;
        }

        // If no events were there for the month, it is off season
        if (!$event)
        {
            $donut->add_slice($lang->get('off_season'));
        }

        // Add the segment to the donut
        $donut->add_segment();

        // Increment the month
        if (++$month > 12)
        {
            $month = 1;
        }
    }
}

// Output the donut
$donut->output('donut_timeline');
    
?>