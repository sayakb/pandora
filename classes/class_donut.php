<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

/**
* In case if you're wondering, GSoD is, in fact, the Grey Screen of Death!
*/

class donut
{
    // Global variables
    var $donut_data;
    var $slice_index;
    var $segment_open;

    // Constructor
    function __construct()
    {
        $this->donut_data = array();      
        $this->slice_index = 1;
        $this->segment_open = false;
    }

    // Load a template and return its contents
    function load($file)
    {
        global $config;

        $tpl = realpath("templates/donut/{$file}.tpl");

        if (file_exists($tpl))
        {
            return file_get_contents($tpl);
        }
        else
        {
            return false;
        }
    }
    
    // Method to create a new segment
    function create_segment($name)
    {
        $this->donut_data[] = "seg = new DonutSegment(\"{$name}\", donut);";
        $this->segment_open = true;
    }

    // Method to add a new slice
    function add_slice($name)
    {
        if ($this->segment_open)
        {
            $this->donut_data[] = "seg.addSlice(new DonutSlice(\"{$this->slice_index}\", \"{$name}\", seg));";
            $this->slice_index++;
        }
    }

    // Method to add the segment to the donut
    function add_segment()
    {
        if ($this->segment_open)
        {
            $this->donut_data[] = "donut.addSegment(seg);";
            $this->slice_index = 1;
        }

        $this->segment_open = false;
    }

    // Outputs the donut
    function output($donut_tpl)
    {
        // Set the page header
        header('Content-type: text/javascript');
        header('Content-Disposition: inline; filename="timeline.pjs"');

        // Output the template only if some data was added
        if (count($this->donut_data) > 0)
        {
            $data = implode("\n", $this->donut_data);
            
            // Load the donut template
            $tpl = $this->load($donut_tpl);

            // Was template data returned?
            if ($tpl !== false)
            {
                $tpl = str_replace("[[donut_data]]", $data, $tpl);

                // Dump it on the screen
                echo $tpl;
            }
        }

        // Terminate processing
        exit;
    }
}
?>
