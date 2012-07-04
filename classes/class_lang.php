<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

class lang
{
    // Class wide variables
    var $lang_name;
    var $lang_vars;
    
    // Constructor
    function __construct()
    {
        global $config;

        $this->lang_name = $config->lang_name;
        $this->lang_vars = array();
    }

    // Function to parse localization data
    function parse($data)
    {
        global $core, $user, $gsod, $config;
        
        if (file_exists(realpath("lang/{$this->lang_name}.php")))
        {
            include("lang/{$this->lang_name}.php");
        }
        else
        {
            $title    = 'Language parser error';
            $message  = 'Error: Language file not found<br />';
            $message .= 'Verify that the language selected is present in the lang/ folder';
            $gsod->trigger($title, $message);
        }

        $data = $this->set_defaults($data);

        foreach ($lang_data as $key => $value)
        {
            $value = $this->parse_vars($value);           
            $data = str_replace("{{{$key}}}", $value, $data);
        }

        // Show unlocalized data as is
        $data = preg_replace('/\{\{(.*?)\}\}/', '$1', $data);

        // Done!
        return $data;
    }

    // Parses language variables
    function parse_vars($data)
    {
        global $config, $core, $user;

        // Substitute generic data
        $data = str_replace("[[host]]", $core->base_uri(), $data);
        $data = str_replace("[[site_name]]", $config->site_name, $data);
        $data = str_replace("[[username]]", $user->username, $data);
        $data = str_replace("[[timezone]]", date('T'), $data);
        
        // Replace placeholder with values
        foreach($this->lang_vars as $key => $value)
        {
            $data = str_replace("[[$key]]", $value, $data);
        }

        // Remove unknown placeholders
        $data = preg_replace('/\[\[(.*?)\]\]/', '', $data);

        // Done!
        return $data;
    }


    // Function to assign language variables
    function assign($data, $value = "")
    {
        if (!is_array($data) && $value)
        {
            $this->lang_vars[$data] = $value;
        }
        else
        {
            foreach ($data as $key => $value)
            {
                $this->lang_vars[$key] = $value;
            }
        }
    }

    // Function to return a localized phrase
    function get($key)
    {
        global $config, $core, $user;

        // Return default data
        switch($key)
        {
            case 'lang_name':
                return $this->lang_name;
            case 'site_name':
                return $config->site_name;
            case 'site_copyright':
                return $config->site_copyright;
        }

        // Get language data from lang file
        if (file_exists(realpath('lang/' . $this->lang_name . '.php')))
        {
            include('lang/' . $this->lang_name . '.php');
        }

        if (isset($lang_data[$key]))
        {
            $data = $lang_data[$key];

            // Parse placeholders
            $data = $this->parse_vars($data);

            // Return localized data
            return $data;
        }
        else
        {
            return $key;
        }
    }

    // Function to assign default variables
    function set_defaults($data)
    {
        global $config;

        $data = str_replace("{{lang_name}}", $this->lang_name, $data);
        $data = str_replace("{{site_name}}", $config->site_name, $data);
        $data = str_replace("{{site_copyright}}", $config->site_copyright, $data);

        return $data;
    }
    
    // Function to exclude a string from being treated as a key
    function escape(&$data)
    {
        $data = preg_replace('/\{\{(.*?)\}\}/', '&#123;&#123;$1&#125;&#125;', $data);
    }
}

?>