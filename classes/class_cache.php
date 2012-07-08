<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

/**
* In case if you're wondering, GSoD is, in fact, the Grey Screen of Death!
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
            // Set the caching options
            $options = array(
                'cacheDir'               => realpath('./cache') . '/',
                'lifeTime'               => 7200,
                'automaticSerialization' => true,
            );

            // Inistantiate the cache objects
            $this->lite = new Cache_Lite($options);
            $this->is_available = !@PEAR::isError($this->lite);
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
    function purge($group = 'default')
    {
        if ($this->is_available)
        {
            return $this->lite->clean($group);
        }
        else
        {
            return false;
        }
    }
}
?>
