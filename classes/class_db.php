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
            $message  = '<b>Pandora DB error</b><br /><br />';
            $message .= 'Database connection failed! Please check your DB settings.';
            $gsod->trigger($message);
        }
    }

    // Function to return a recordset
    function query($sql, $single = false)
    {
        try
        {
            global $gsod;
            
            $recordset = array();
            $result = $this->mysqli->query($sql);
            $sql = strtolower($sql);

            if ((strpos($sql, 'select') !== false && strpos($sql, 'select') == 0) ||
                (strpos($sql, 'show') !== false && strpos($sql, 'show') == 0))
            {
                if (!$result)
                {
                    $message  = '<b>Pandora DB error</b><br /><br />';
                    $message .= 'Error: ' . $this->mysqli->error . "<br />";
                    $message .= 'Whole query: ' . $sql;
                    $gsod->trigger($message);
                }

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

    // Validate a unique ID
    function validate($table, $column, $value)
    {
        global $core;

        if ($value > 0)
        {
            $sql = "SELECT COUNT(*) as count FROM {$this->prefix}{$table} " .
                "WHERE {$column} = '{$value}'";
            $row = $this->query($sql, true);

            if ($row['count'] == 0)
            {
                $core->redirect($core->path());
            }
        }
    }

    // Object descturtor
    function __destruct()
    {
        $this->mysqli->close();
    }
}

?>