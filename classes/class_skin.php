<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

class skin
{
    // Class wide variables
    var $skin_name;
    var $skin_name_fancy;
    var $skin_path;
    var $skin_vars;
    var $skin_title;
    var $skin_file;
    var $start_time;

    // Class constructor
    function __construct()
    {
        global $core, $config;

        $this->skin_name = strtolower($config->skin_name);
        $this->skin_name_fancy = $config->skin_name;
        $this->skin_file = '';
        $this->skin_path = $core->path() . 'skins/' . strtolower($config->skin_name);
        $this->start_time = $core->get_microtime();
    }

    // Returns the name of the active skin
    function name()
    {
        return $this->skin_name;
    }

    // Function to initialize a skin file
    function init($file)
    {
        $this->skin_file = $file;
    }

    // Function to assign template variables
    function assign($data, $value = "")
    {
        // Add assigned data
        if (!is_array($data) && $value)
        {
            $this->skin_vars[$data] = $value;
        }
        else
        {
            foreach ($data as $key => $value)
            {
                $this->skin_vars[$key] = $value;
            }
        }
    }

    // Function to set the page title
    function title($value)
    {
        $this->skin_title = $value;
    }

    // Function to parse template variables
    function parse($file_name)
    {
        global $lang, $gsod, $cache;

        // Before we do anything, we set default skin data
        $this->set_defaults();

        // Build cache key
        $c_key = $this->generate_key($file_name);

        // Check if template data is cached
        $cached = $cache->get($c_key, 'skin');

        if ($cached !== false)
        {
            return $cached;
        }

        // Parse template variables
        if (!file_exists($file_name))
        {
            $title    = 'Skin parser error';
            $message  = 'Error: Skin file not found<br />';
            $message .= 'Verify that the skin selected is present in the skins/ folder';
            $gsod->trigger($title, $message);
        }

        // Get the contents of the template file
        $data = file_get_contents($file_name);

        // Parse the skin vars
        foreach($this->skin_vars as $key => $value)
        {
            $data = str_replace("[[$key]]", $value, $data);
        }

        // Remove unknown placeholders
        $data = preg_replace('/\[\[(.*?)\]\]/', '', $data);

        // Remove line breaks and tabs
        $data = preg_replace('/[\n]+/', '', $data);
        $data = preg_replace('/[ \t]+/', ' ', $data);

        // Remove hidden tags
        $data = $this->remove_hidden($data);

        // Apply localization data
        $data = $lang->parse($data);

        // Add it to cache
        $cache->put($c_key, $data, 'skin');

        // Done!
        return $data;
    }

    // Removes hidden tags
    function remove_hidden($data)
    {
        include_once('addons/simple_html_dom.php');

        $html = new simple_html_dom();
        $html->load($data);
        
        $elts = $html->find('[class*=_hidden]');

        foreach ($elts as $elt)
        {
            $elt->outertext = '';
        }

        return $html;
    }

    // Generates a cache key
    function generate_key($c_key = '')
    {
        foreach ($this->skin_vars as $key => $value)
        {
            $c_key .= "{$key}{$value}";
        }

        return crc32($c_key);
    }

    // Function to set default data to the skin vars
    function set_defaults()
    {
        global $core, $user;

        $default_data = array(
            'page_title'        => $this->skin_title,
            'skin_path'         => $this->skin_path,
            'skin_name'         => $this->skin_name_fancy,
            'username'          => $user->username,
            'guest_visibility'  => $this->visibility(!$user->is_logged_in),
            'user_visibility'   => $this->visibility($user->is_logged_in),
            'admin_visibility'  => $this->visibility($user->is_admin),
            'nav_home'          => $core->path(),
        );

        if (is_array($this->skin_vars))
        {
            $this->skin_vars = array_merge($this->skin_vars, $default_data);
        }
        else
        {
            $this->skin_vars = $default_data;
        }
    }

    // Sets HTTP headers
    function set_header($headers_ary)
    {
        if (is_array($headers_ary))
        {
            foreach ($headers_ary as $key => $value)
            {
                header("{$key}: {$value}");
            }
        }
    }

    // Function to get full path of file
    function locate($file)
    {
        return realpath("skins/{$this->skin_name}/html/{$file}.html");
    }

    // Function to output the page
    function output($file = false, $body_only = false)
    {
        global $core, $gsod;

        if ($file)
        {
            $file = $this->locate($file);

            // Return the parsed template
            return $this->parse($file);
        }
        else
        {
            $file_header = $this->locate('tpl_header');
            $file_footer = $this->locate('tpl_footer');

            if (!$this->skin_file)
            {
                $title    = 'Skin parser error';
                $message  = 'Error: Skin file not initialized<br />';
                $message .= 'Use $skin->init(\'filename\') to load a skin file';
                $gsod->trigger($title, $message);
            }

            $file_body = $this->locate($this->skin_file);

            if ($body_only)
            {
                echo $this->parse($file_body);
            }
            else
            {
                echo $this->parse($file_header);
                echo $this->parse($file_body);
                echo $this->parse($file_footer);

                // Output page load time
                $this->load_time();
            }
        }
    }

    // Outputs the page load time
    function load_time()
    {
        global $user, $core, $config, $user, $db;

        if ($user->is_admin && $config->show_debug)
        {
            // Get the load time
            $load_time = $core->get_microtime() - $this->start_time;
            $load_time = round($load_time, 3);
            $rendered  = "Page rendered in {$load_time} seconds";

            // Get the number of queries
            $hits = $db->hits - 3;
            $times = $hits == 1 ? 'time' : 'times';
            $queries = "Module queried the database {$hits} {$times}";

            // Get the online user count
            $count = $user->online();
            $users = $count == 1 ? "is {$count} user" : "are {$count} users";
            $online = "There {$users} online";

            echo '<div style="text-align:center; font-size:0.85em; margin-bottom:10px;">' .
                     "{$rendered} &bull; {$queries} &bull; {$online}" .
                 '</div>';
        }
    }

    // Function to generate pagination
    function pagination($total_items, $current_page)
    {
        global $lang, $core, $config;

        $pages = ceil($total_items / $config->per_page);
        $pagination = '';
        $url = str_replace("&pg={$current_page}", "", $core->request_uri());

        for ($idx = 1; $idx <= $pages; $idx++)
        {
            if ($pages > 10 && $idx > 3 && $idx != ($current_page - 1) &&
                $idx != ($current_page) && $idx != ($current_page + 1) &&
                $idx < ($pages - 2))
            {
                $pagination .= ' ...';

                if ($idx < ($current_page - 1))
                {
                    $idx = $current_page - 2;
                }
                else
                {
                    $idx = $pages - 3;
                }
            }
            else
            {
                if ($idx != $current_page)
                {
                    $pagination .= '<a href="' . $url . '&pg=' . $idx . '">';
                }

                $pagination .= '<span class="page_no';
                $pagination .= ($idx == $current_page ? ' page_current' : '');
                $pagination .= '">' . $idx . '</span>';

                if ($idx != $current_page)
                {
                    $pagination .= "</a>";
                }
            }
        }

        return $pagination;
    }

    // Creates a list of options from directory contents
    function get_list($relative_path, $excluded_files = "", $selected_entry = false, $insert_empty = false,
                      $pascal_case = false, $trim_extension = false)
    {
        $dir = opendir(realpath($relative_path));
        $list = '';
        $entries = array();

        if (!is_array($excluded_files))
        {
            $excluded_files = array($excluded_files);
        }

        while ($entry = readdir($dir))
        {
            if ($entry != '.' && $entry != '..' && !in_array($entry, $excluded_files))
            {
                if ($trim_extension)
                {
                    $entry = substr($entry, 0, strrpos($entry, '.'));
                }

                if ($pascal_case)
                {
                    $entries[] = strtoupper(substr($entry, 0, 1)) . substr($entry, 1, strlen($entry) - 1);
                }
                else
                {
                    $entries[] = $entry;
                }
            }
        }

        sort($entries);

        if ($insert_empty)
        {
            $list = '<option></option>';
        }

        foreach($entries as $entry)
        {
            $selected = ($selected_entry !== false && strtolower($entry) == strtolower($selected_entry));
            $list .= '<option' . ($selected ? ' selected="selected"' : '') . '>' .
                     htmlspecialchars($entry) . '</option>';
        }

        return $list;
    }

    // Function to prematurely end a session
    function kill()
    {
        global $lang;

        $this->title($lang->get('error'));
        $this->output();
        exit;
    }

    // Return visibility based on condition
    function visibility($condition)
    {
        return $condition ? '_visible' : '_hidden';
    }

    // Return checked status of checkbox/radio based on a condition
    function checked($condition)
    {
        return $condition ? 'checked="checked"' : '';
    }

    // Return disabled status of control based on a condition
    function disabled($condition)
    {
        return $condition ? 'disabled="disabled"' : '';
    }

    // Function to exclude a string from being treated as a key
    function escape(&$data)
    {
        $data = preg_replace('/\[\[(.*?)\]\]/', '&#91;&#91;$1&#93;&#93;', $data);
    }
}

?>