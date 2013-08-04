<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

class cache
{
    // Global vars
    var $is_available;
    var $lite;

    // Constructor
    function __construct()
    {
        @include('Cache/Lite.php');

        if (class_exists('Cache_Lite'))
        {
            $cache_path = realpath('./cache') . '/';

            // Set the caching options
            $options = array(
                'cacheDir'               => $cache_path,
                'lifeTime'               => 7200,
                'automaticSerialization' => true,
            );

            // Inistantiate the cache objects
            $this->lite = new Cache_Lite($options);
            $this->is_available = !@PEAR::isError($this->lite) && is_writable($cache_path);
        }
        else
        {
            $this->is_available = false;
        }
    }

    // Gets a value from the cache
    function get($key, $group = 'default')
    {
        if ($this->is_available)
        {
            return $this->lite->get($key, $group);
        }
        else
        {
            return false;
        }
    }

    // Saves a value to the cache
    function put($key, $data, $group = 'default')
    {
        if ($this->is_available)
        {
            return $this->lite->save($data, $key, $group);
        }
        else
        {
            return false;
        }
    }

    // Deletes data from the cache
    function remove($key, $group = 'default')
    {
        if ($this->is_available)
        {
            return $this->lite->remove($key, $group);
        }
        else
        {
            return false;
        }
    }

    // Purges the cache (for a specific group)
    function purge($groups = array('default'))
    {
        if (!is_array($groups))
        {
            $groups = array($groups);
        }

        if ($this->is_available)
        {
            $status = true;

            foreach ($groups as $group)
            {
                $status = $status && $this->lite->clean($group);
            }

            return $status;
        }
        else
        {
            return false;
        }
    }
}
?>
