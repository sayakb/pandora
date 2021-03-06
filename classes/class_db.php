<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

class db
{
    // Class wide variables
    var $mysqli;
    var $prefix;
    var $hits;

    // Constructor
    function __construct()
    {
        $this->hits = 0;
    }

    // Function to initialize a db connection
    function connect()
    {
        global $gsod, $config;

        try
        {
            $db_port_int = intval($config->db_port);
            $this->mysqli = new mysqli($config->db_host, $config->db_username,
                                       $config->db_password, $config->db_name, $db_port_int);

            if (!$this->mysqli->connect_error)
            {
                $this->prefix = $config->db_prefix;
            }
            else
            {
                throw new Exception('DB Error');
            }
        }
        catch (Exception $e)
        {
            $title   = 'Database error';
            $message = 'Database connection failed! Please check your DB settings.';
            $gsod->trigger($title, $message);
        }
    }

    // Function to return a recordset
    function query($sql, $single = false)
    {
        try
        {
            global $gsod;

            $this->hits++;
            $recordset = array();

            if (stripos($sql, 'SELECT') !== false && stripos($sql, 'SELECT') == 0)
            {
                // Append limit to single row select query
                if ($single)
                {
                    $sql .= " LIMIT 1";
                }

                // Execute the query
                $result = $this->mysqli->query($sql);

                // Some error occurred
                if (!$result)
                {
                    $title    = 'Database error';
                    $message  = "Error: {$this->mysqli->error}<br />";
                    $message .= "Whole query: {$sql}";
                    $gsod->trigger($title, $message);
                }

                // Return the data
                if (!$single)
                {
                    while ($row = $result->fetch_assoc())
                    {
                        $recordset[] = $row;
                    }

                    $result->close();
                    return $recordset;
                }
                else
                {
                    $row = $result->fetch_assoc();
                    $result->close();

                    return $row;
                }
            }
            else
            {
                $this->mysqli->query($sql);
            }

            return true;
        }
        catch (Exception $e)
        {
            return null;
        }
    }

    // Function to get the last inserted query ID
    function get_id()
    {
        return $this->mysqli->insert_id;
    }

    // Function to check affected rows
    function affected_rows()
    {
        return $this->mysqli->affected_rows;
    }

    // Function to escape a special chars string
    function escape(&$data)
    {
        $data = $this->mysqli->real_escape_string($data);
    }

    // Object descturtor
    function __destruct()
    {
        $this->mysqli->close();
    }
}

?>