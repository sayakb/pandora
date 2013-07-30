<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

if (!defined('IN_PANDORA')) exit;

$lang_data = array(
    /* Global keys */
    'kde_links'             => 'KDE Links',
    'not_logged_in'         => 'You are not logged in',
    'logged_in_as'          => 'Logged in as [[username]]',
    'log_in'                => 'Log in',
    'log_out'               => 'Log out',
    'administration'        => 'Administration',
    'manage_programs'       => 'Manage programs',
    'manage_bans'           => 'Manage user bans',
    'navigation'            => 'Navigation',
    'view_active_progms'    => 'View active programs',
    'view_archives'         => 'View archived projects',
    'view_rejected'         => 'View rejected projects',
    'accepted_projects'     => 'Accepted projects',
    'view_my_projects'      => 'View my projects',
    'approve_proposal'      => 'Approve proposals',
    'approve_mentors'       => 'Approve mentors',
    'edit_templates'        => 'Edit email templates',
    'mandatory_all'         => 'Please fill in all the fields',
    'err_mandatory_fields'  => 'Please fill in all the mandatory fields',
    'delete'                => 'Delete',
    'yes'                   => 'Yes',
    'no'                    => 'No',
    'program_title'         => 'Program title',
    'project_title'         => 'Project title',
    'description'           => 'Description',
    'no_programs'           => 'There are no programs to display',
    'error_occurred'        => 'An error occurred while processing your request',
    'confirm_deletion'      => 'Confirm deletion',
    'edit'                  => 'Edit',
    'save'                  => 'Save',
    'cancel'                => 'Cancel',
    'username'              => 'Username',
    'sending_status'        => 'Sending status mails for project',
    'sending_result'        => 'Sending result mails for project',
    'status_ok'             => '(Status OK)',
    'status_error'          => '(Status ERROR)',
    'available'             => 'available',
    'unavailable'           => 'unavailable',
    'debug_render'          => 'Rendered in %ss',
    'debug_queries'         => 'DB queries: %s',
    'debug_users'           => 'User(s) online: %s',
    'debug_caching'         => 'Caching is %s',
    'debug_email'           => 'Mail service is %s',

    /* Homepage */
    'welcome_homepage'      => 'Welcome to [[site_name]]',
    'home'                  => 'Home',

    /* Module: login */
    'reset'                 => 'Reset',
    'enter_user_pw'         => 'Please enter your username and password',
    'login_error'           => 'Login failed. Please notify the <a href="mailto:kde-soc-mentor-owner@kde.org">' .
                               'KDE SoC Administrators</a> if the problem persists',
    'iko_credentials'       => 'Log into [[site_name]] using your <a href="http://identity.kde.org" target="_blank">' .
                               'KDE Identity</a> credentials:',
    'password'              => 'Password',
    'create_account'        => 'Create a new account',
    'create_account_exp'    => 'Don\'t have an account? In order to log into [[site_name]], you\'ll need to create a ' .
                               'new account on <a href="http://identity.kde.org">identity.kde.org</a>',
    'register_iko'          => 'Register on KDE Identity',
    'account_banned'        => 'Your account has been banned by an administrator',

    /* Module: view_programs */
    'select_program'        => 'Select a program to continue',

    /* Module: manage_programs */
    'add_program'           => 'Add new program',
    'edit_program'          => 'Edit program',
    'active'                => 'Active',
    'start_date'            => 'Start date',
    'end_date'              => 'End date',
    'dl_student'            => 'Student deadline',
    'dl_mentor'             => 'Mentor deadline',
    'show_deadlines'        => 'Show deadlines',
    'form_notice'           => 'All fields marked with * are mandatory. All times are in [[timezone]].',
    'invalid_date'          => 'Please enter a valid start and end date',
    'invalid_deadlines'     => 'Please enter valid student and mentor application deadlines',
    'confirm_program_del'   => 'If you delete this program, all projects associated with it will also get deleted. ' .
                               'Do you want to continue?',

    /* Module: programs_home */
    'login_to_participate'  => 'Log in or sign-up to participate',
    'program_started'       => 'This program isn\'t accepting new participants',
    'apply_student'         => 'Apply as student',
    'apply_mentor'          => 'Apply to mentor',
    'view_submissions'      => 'View my submissions',
    'submit_proposal'       => 'Submit project proposal',
    'create_project'        => 'Create new project',
    'mentor_project_sel'    => 'Select project to mentor',
    'cancel_mentor'         => 'Cancel mentor application',
    'view_proposals'        => 'View project proposals',
    'resign_student'        => 'Resign from this program',
    'role_student'          => 'You are participating in this program as a student',
    'role_mentor'           => 'You are participating in this program as a mentor',
    'role_resigned'         => 'You have resigned from this program and cannot participate again',
    'role_rejected'         => 'Your mentorship application has been declined and you cannot participate in this program',
    'role_intermediate'     => 'You mentor application for this program is awaiting admin approval',
    'view_accepted'         => 'View accepted projects',
    'student_dl_info'       => 'Student application deadline: [[dl_student]]',
    'mentor_dl_info'        => 'Mentor application deadline: [[dl_mentor]]',
    'to'                    => 'to',

    /* Module: view_projects */
    'submit_proposal'       => 'Submit a proposal',
    'edit_project'          => 'Edit project',
    'proposal_submitted'    => 'Your proposal has been submitted successfully',
    'mentor_submitted'      => 'You have been successfully added as the project mentor',
    'project_updated'       => 'Project updated successfully',
    'project_home'          => 'Program home',
    'project_complete'      => 'Project complete?',
    'student_result'        => 'Student result',
    'passed'                => 'Passed',
    'failed'                => 'Failed',
    'undecided'             => 'Undecided',
    'confirm_project_del'   => 'Deletion of a project is irreversible. Are you sure you want to continue?',
    'mentor_project'        => 'Mentor this project',
    'view_project'          => 'View project details',
    'project_accepted'      => 'Project accepted?',
    'your_projects'         => 'Your projects',
    'proposed_projects'     => 'Proposed projects',
    'rejected_projects'     => 'Rejected projects',
    'student'               => 'Student',

    'mentor'                => 'Mentor',
    'no_projects'           => 'No projects were found in this category',
    'new_mentor'            => 'New mentor',
    'new_mentor_exp'        => 'Leave blank if you do not wish to change the mentor',
    'new_mentor_student'    => 'The mentor you have selected is a student for this program',
    'new_student'           => 'New student',
    'new_student_exp'       => 'Leave blank if you do not wish to change the student',
    'new_student_mentor'    => 'The student you have selected is a mentor for this program',
    'approve'               => 'Approve',
    'reject'                => 'Reject',
    'admin'                 => 'Admin',
    'subject_status'        => '[[site_name]]: Status of your submission',
    'subject_result'        => '[[site_name]]: Result of your project',
    'no_mentor'             => '<em>No mentor assigned</em>',
    'mentor_subject'        => 'New mentor request awaiting approval',
    'subscribe_student'     => '<b>Important:</b> It is essential that you subscribe to the <a href="https:' .
                               '//mail.kde.org/mailman/listinfo/kde-soc" target="_blank">KDE SoC Mailing List' .
                               '</a>, if you haven\'t done it already.',
    'subscribe_mentor'      => '<b>Important:</b> It is essential that you subscribe to the <a href="https:' .
                               '//mail.kde.org/mailman/listinfo/kde-soc-mentor" target="_blank">KDE SoC ' .
                               'Mentor Mailing List</a>, if you haven\'t done it already.',
    'confirm_resign'        => 'Confirm resignation',
    'confirm_resign_exp'    => 'Are you sure you want to resign? Your submissions will get invalidated.',

    /* Module: user_profile */
    'user_profile'          => 'User profile',
    'full_name'             => 'Full name',
    'email'                 => 'Email address',
    'previous_page'         => 'Previous page',
    'user_avatar'           => 'User avatar',
    'user_404'              => 'The requested user could not be found',
    'contact_user'          => 'Contact user',
    'full_profile'          => 'View full profile',
    'site_admin'            => 'Site admin',

    /* Module: user_bans */
    'ban_user'              => 'Ban a user',
    'banned_users'          => 'Banned users',
    'unban'                 => 'Unban',
    'unban_user'            => 'Unban this user',
    'ban'                   => 'Ban',
    'no_bans'               => 'There are no banned users at the moment',

    /* Module: approve_mentors */
    'no_pending_mentors'    => 'There are no pending mentor applications',
    'mentor_name'           => 'Mentor username',

    /* Module: timeline */
    'application_start'     => 'Start application submission',
    'student_appl_dl'       => 'Student application deadline',
    'mentor_appl_dl'        => 'Mentor application deadline',
    'application_process'   => 'Application processing',
    'season_complete'       => 'Season completion',
    'students_coding'       => 'Students coding!',
    'off_season'            => 'Off season',
    'program_timeline'      => 'Program timeline',

    /* Module: edit_templates */
    'select_tpl'            => 'Select template',
    'load_tpl'              => 'Load template',
    'language'              => 'Language',
    'no_tpl_loaded'         => 'No template loaded. Select a template file from the list above and click on ' .
                               '\'Load template\' button to edit it.',
    'tpl_saved'             => 'Template saved successfully',
    'tpl_save_error'        => 'An error occurred while saving the template file',
    'save_tpl'              => 'Save template',
    'placeholders'          => 'Placeholders',
    'recepient'             => 'Recepient\'s full name',
    'program_name'          => 'Name of the program',
    'project_name'          => 'Name of the project',
    'project_url'           => 'Permanent URL for the project',
    'student_fname'         => 'Student\'s full name and e-mail address',
    'mentor_fname'          => 'Mentor\'s full name and e-mail address',
);

?>
