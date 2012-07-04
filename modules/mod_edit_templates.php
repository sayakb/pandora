<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

if (!defined('IN_PANDORA')) exit;

// Collect some data
$tpl_name = $core->variable('tpl_name', '');
$tpl_lang = $core->variable('tpl_lang', $config->lang_name);
$tpl_data = $core->variable('tpl_data', '');

$tpl_load = isset($_POST['tpl_load']);
$tpl_save = isset($_POST['tpl_save']);

// Generate the template and language list
$tpl_names = $skin->get_list("./templates/email/{$tpl_lang}", array('samples', 'mentor.tpl'), $tpl_name, true);
$tpl_langs = $skin->get_list("./templates/email", null, $tpl_lang);

// Was a template loaded or saved?
if ($tpl_load || $tpl_save)
{
    $tpl_path = realpath("./templates/email/{$tpl_lang}/{$tpl_name}");

    // Load template if file exists
    if (file_exists($tpl_path))
    {
        // Template was saved
        if ($tpl_save)
        {
            // Save the template to the file
            $status = @file_put_contents($tpl_path, $tpl_data);

            // Sow a success notification
            if ($status)
            {
                $show_success = true;
            }
            else
            {
                $show_error = true;
            }
        }
        
        // Template was loaded, load the data
        if ($tpl_load)
        {
            $tpl_data = file_get_contents($tpl_path);
        }
    }
}

// Process template data for display
$tpl_data = htmlspecialchars($tpl_data);
$tpl_data = str_replace("\r\n", "\n", $tpl_data);
$tpl_data = str_replace("\n", "\r\n", $tpl_data);
$tpl_data = str_replace("[", "&#91;", $tpl_data);
$tpl_data = str_replace("]", "&#93;", $tpl_data);
$tpl_data = str_replace("{", "&#123;", $tpl_data);
$tpl_data = str_replace("}", "&#125;", $tpl_data);

// Assign final skin data
$skin->assign(array(
    'tpl_names'           => $tpl_names,
    'tpl_langs'           => $tpl_langs,
    'tpl_data'            => $tpl_data,
    'notice_visibility'   => $skin->visibility(empty($tpl_name)),
    'editor_visibility'   => $skin->visibility(!empty($tpl_name)),
    'error_visibility'    => $skin->visibility(isset($show_error)),
    'success_visibility'  => $skin->visibility(isset($show_success)),
));

// Output the page
$module_title = $lang->get('edit_templates');
$module_data = $skin->output('tpl_edit_templates');

?>