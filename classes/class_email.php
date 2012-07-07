<?php
/**
* Pandora v1
* license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* copyright (c) 2012 KDE. All rights reserved.
*/

/**
* In case if you're wondering, GSoD is, in fact, the Grey Screen of Death!
*/

class email
{
    // Global variables
    var $is_available;
    var $email_vars;
    var $mime;
    var $smtp;

    // Class constructor
    function __construct()
    {
        @include('Mail.php');
        @include('Mail/mime.php');

        if (class_exists('Mail_mime'))
        {
            global $config;

            // Set the SMTP server options
            $options = array(
                'host'    => $config->smtp_host,
                'port'    => $config->smtp_port,
            );

            // If SMTP authentication data is provided, add it to server options
            if (!empty($config->smtp_username) && !empty($config->smtp_password))
            {
                $options = array_merge($options, array(
                    'auth'     => true,
                    'username' => $config->smtp_username,
                    'password' => $config->smtp_password,
                ));
            }

            $this->mime = new Mail_mime("\n");
            $this->smtp = @Mail::factory('smtp', $options);
            $this->is_available = !@PEAR::isError($this->smtp);
            $this->email_vars = array();
        }
        else
        {
            $this->is_available = false;
            $this->email_vars = array();
        }
    }
    
    // Load a template and return its contents
    function load($file)
    {
        global $config;

        $tpl = realpath("templates/email/{$config->lang_name}/{$file}.tpl");

        if (file_exists($tpl))
        {
            return file_get_contents($tpl);
        }
        else
        {
            return false;
        }
    }

    // Parses an email body
    function parse($data)
    {
        // Replace placeholder with values
        foreach($this->email_vars as $key => $value)
        {
            $data = str_replace("[[$key]]", $value, $data);
        }

        // Remove unknown placeholders
        $data = preg_replace('/\[\[(.*?)\]\]/', '', $data);

        // Done!
        return $data;
    }
    
    // Sends an email message
    function send($recipient, $subject, $body_tpl)
    {
        global $config;

        if ($this->is_available)
        {
            // Set the e-mail headers
            $headers = array (
                'From'        => $config->smtp_from,
                'Return-Path' => $config->smtp_from,
                'To'          => $recipient,
                'Subject'     => $subject,
            );

            // Load the mail template
            $tpl = $this->load($body_tpl);

            if ($tpl !== false)
            {
                // Parse the template
                $body = $this->parse($tpl);

                // Setting the body of the email
                $this->mime->setTXTBody(strip_tags($body));
                $this->mime->setHTMLBody($body);

                $body = $this->mime->get();
                $headers = $this->mime->headers($headers);

                // Deliver the email
                $status = $this->smtp->send($recipient, $headers, $body);

                // Return true if no error occurred
                return !@PEAR::isError($status);
            }
        }

        // No mail function defined
        return false;
    }

    // Function to assign email variables
    function assign($data, $value = "")
    {
        if (!is_array($data) && $value)
        {
            $this->email_vars[$data] = $value;
        }
        else
        {
            foreach ($data as $key => $value)
            {
                $this->email_vars[$key] = $value;
            }
        }
    }
}
?>
