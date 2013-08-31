<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

if (!defined('IN_PANDORA')) exit;

// We need longer execution time
set_time_limit(3600);

// Get the program's participant list
$db->escape($program_id);

// Set default for sending mail
$name = $config->ldap_fullname;
$mail = $config->ldap_mail;
$mentor_name = $lang->get('no_mentor');
$output = '';

if (isset($_POST['process']))
{
    // Get all entries from the queue and their corresponding program data
    $sql = "SELECT prg.id as program_id, " .
           "       prg.title as program_title, " .
           "       prg.dl_mentor as program_deadline, " .
           "       prg.end_time as program_complete, " .
           "       que.deadline as deadline_flag, " .
           "       que.complete as complete_flag " .
           "FROM {$db->prefix}queue que " .
           "LEFT JOIN {$db->prefix}programs prg " .
           "ON que.program_id = prg.id " .
           "WHERE (prg.dl_mentor < TIMESTAMP(CURRENT_DATE()) " .
           "OR prg.end_time < TIMESTAMP(CURRENT_DATE())) ";
           "AND prg.is_active = 1";
    $program_data = $db->query($sql);

    // Traverse through each program
    foreach ($program_data as $program)
    {
        // Set original deadline and complete flags
        $deadline = $program['deadline_flag'];
        $complete = $program['complete_flag'];

        // All projects for this program
        $sql = "SELECT id as project_id, " .
               "       title as project_title, " .
               "       is_accepted " .
               "FROM {$db->prefix}projects " .
               "WHERE program_id = {$program['program_id']}";
        $project_data = $db->query($sql);

        // Assign program name
        $email->assign('program_name', $program['program_title']);

        // Add log line
        $output .= '[' . date('r') . '] ' . $lang->get('processing_program') . "{$program['program_id']}...\n";
        $processed = false;

        // Traverse through each project
        foreach ($project_data as $project)
        {
            $project['student'] = '';
            $project['mentor'] = '';
            $project['passed'] = false;

            // Get student data
            $sql = "SELECT username, passed " .
                   "FROM {$db->prefix}participants " .
                   "WHERE program_id = {$program['program_id']} " .
                   "AND project_id = {$project['project_id']} " .
                   "AND role = 's'";
            $student_data = $db->query($sql, true);

            if ($student_data != null)
            {
                $project['student'] = $student_data['username'];
                $project['passed'] = $student_data['passed'];
            }

            // Get mentor data
            $sql = "SELECT username " .
                   "FROM {$db->prefix}participants " .
                   "WHERE program_id = {$program['program_id']} " .
                   "AND project_id = {$project['project_id']} " .
                   "AND role = 'm'";
            $mentor_data = $db->query($sql, true);

            if ($mentor_data != null)
            {
                $project['mentor'] = $mentor_data['username'];
            }

            // Get student and mentor data from LDAP
            $student_data = $user->get_details($project['student'], array($name, $mail));
            $mentor_data  = $user->get_details($project['mentor'], array($name, $mail));

            // Set student data
            if ($student_data !== false)
            {
                $student      = $project['student'];
                $student_to   = $student_data[$name][0];
                $student_name = "{$student_data[$name][0]} &lt;{$student_data[$mail][0]}&gt;";
                $student_mail = $student_data[$mail][0];
            }

            // Set mentor data
            if ($mentor_data !== false)
            {
                $mentor      = $project['mentor'];
                $mentor_to   = $mentor_data[$name][0];
                $mentor_name = "{$mentor_data[$name][0]} &lt;{$mentor_data[$mail][0]}&gt;";
                $mentor_mail = $mentor_data[$mail][0];
            }

            // Assign data needed for the email
            $email->assign(array(
                'project_name'      => $project['project_title'],
                'student_name'      => $student_name,
                'mentor_name'       => $mentor_name,
                'project_url'       => "{$config->http_host}?q=view_projects&amp;prg=" .
                                       "{$program['program_id']}&amp;p={$project['project_id']}",
            ));

            // Send out status mails on deadline
            if ($program['program_deadline'] < $core->timestamp && $project['is_accepted'] != -1 && $deadline == 0)
            {
                // Output status to console
                $output .= '[' . date('r') . '] ' . $lang->get('sending_status') . " #{$project['project_id']}... ";

                // Set the template based on the status
                $status = $project['is_accepted'] == 1 ? 'accept' : 'reject';

                // Set initial flag values
                $success_student = true;
                $success_mentor  = true;

                if ($student_data !== false && !empty($student_mail))
                {
                    $email->assign('recipient', $student_to);
                    $success_student = $email->send($student_mail, $lang->get('subject_status'), $status);
                }

                if ($mentor_data !== false && !empty($mentor_mail))
                {
                    $email->assign('recipient', $mentor_to);
                    $success_mentor = $email->send($mentor_mail, $lang->get('subject_status'), $status);
                }

                // Determine status for logging
                $log_status = $success_student && $success_mentor ? $lang->get('status_ok') : $lang->get('status_error');
                $output .= "{$log_status}\n";
                $processed = true;
            }

            // Send out result mails on program completion
            if ($program['program_complete'] < $core->timestamp && $project['passed'] != -1 && $complete == 0)
            {
                // Output status to console
                $output .= '[' . date('r') . '] ' . $lang->get('sending_result') . " #{$project['project_id']}... ";

                // Set the template based on the status
                $status = $project['passed'] == 1 ? 'pass' : 'fail';

                // Set initial flag status
                $success = true;

                if ($student_data !== false && !empty($student_mail))
                {
                    $email->assign('recipient', $student_to);
                    $success = $email->send($student_mail, $lang->get('subject_result'), $status);
                }

                // Determine status for logging
                $log_status = $success ? $lang->get('status_ok') : $lang->get('status_error');
                $output .= "{$log_status}\n";
                $processed = true;
            }
        }

        // Set new flag values
        $deadline = $program['program_deadline'] < $core->timestamp ? 1 : 0;
        $complete = $program['program_complete'] < $core->timestamp ? 1 : 0;

        // Update the queue entry if at least one flag is still unset
        if ($deadline == 0 || $complete == 0)
        {
            $sql = "UPDATE {$db->prefix}queue " .
                   "SET deadline = {$deadline}, " .
                   "    complete = {$complete} " .
                   "WHERE program_id = {$program['program_id']}";
            $db->query($sql);
        }

        // Both flags set, remove the item from queue
        else
        {
            $sql = "DELETE FROM {$db->prefix}queue " .
                   "WHERE program_id = {$program['program_id']}";
            $db->query($sql);
        }

        // No mails were processed
        if (!$processed)
        {
            $output .= '[' . date('r') . '] ' . $lang->get('program_no_mail');
        }

        $output .= "\n\n";
    }
}

// Get the queue data
$sql = "SELECT prg.id as program_id, " .
       "       prg.title as program_title, " .
       "       que.deadline as deadline_flag, " .
       "       que.complete as complete_flag " .
       "FROM {$db->prefix}queue que " .
       "LEFT JOIN {$db->prefix}programs prg " .
       "ON que.program_id = prg.id " .
       "WHERE prg.is_active = 1";
$queue_data = $db->query($sql);

// Populate the queue
$queue_items = '';

foreach ($queue_data as $queue)
{
    $skin->assign(array(
        'program_id'                 => $queue['program_id'],
        'program_title'              => $queue['program_title'],
        'program_deadline_sent'      => $skin->visibility($queue['deadline_flag'] == 1),
        'program_deadline_pending'   => $skin->visibility($queue['deadline_flag'] == 0),
        'program_complete_sent'      => $skin->visibility($queue['complete_flag'] == 1),
        'program_complete_pending'   => $skin->visibility($queue['complete_flag'] == 0),
    ));

    $queue_items .= $skin->output('tpl_queue_item');
}

// Assign final skin data
$skin->assign(array(
    'notify_output'        => nl2br(trim($output)),
    'queue_items'          => $queue_items,
    'output_visibility'    => $skin->visibility(empty($output), true),
    'queue_visibility'     => $skin->visibility(empty($queue_items), true),
    'notice_visibility'    => $skin->visibility(empty($queue_items)),
));

// Output the module
$module_title = $lang->get('notifications');
$module_data = $skin->output('tpl_notifications');

?>
