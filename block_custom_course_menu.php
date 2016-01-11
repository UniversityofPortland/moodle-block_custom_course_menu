<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * CustomCourseMenu Block
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Main content class
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_custom_course_menu extends block_base {

    /**
     * Initialise the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_custom_course_menu');
    }

    /**
     * Initalizes settings from settings.php
     */
    private function initSettings() {
            global $DB;
            //these should automatically be created when the block is installed.
            //if they are not, they need to be configured.
            if($DB->record_exists('config', array('name' => 'block_custom_course_menu_enablefavorites'))) {
                    $this->clientid = $DB->get_record('config', array('name' => 'block_custom_course_menu_enablefavorites'))->value;
            }

            if($DB->record_exists('config', array('name' => 'block_custom_course_menu_enablelastviewed'))) {
                    $this->clientsecret = $DB->get_record('config', array('name' => 'block_custom_course_menu_enablelastviewed'))->value;
            }

            if($DB->record_exists('config', array('name' => 'block_custom_course_menu_lastviewedamount'))) {
                    $this->prefix = $DB->get_record('config', array('name' => 'block_custom_course_menu_lastviewedamount'))->value;
            }

            if($DB->record_exists('config', array('name' => 'block_custom_course_menu_showsearch'))) {
                    $this->base_url = $DB->get_record('config', array('name' => 'block_custom_course_menu_showsearch'))->value;
            }

    }

    /**
     * required for settings.php
     * @see block_base::has_config()
     */
    public function has_config() {
            return true;
    }

    /**
     * Return the content of this block.
     *
     * @return stdClass the content
     */
    public function get_content() {
        global $THEME, $CFG, $USER, $DB, $OUTPUT, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        if ($CFG->version < 2015051101) { // If not using Moodle 2.9+.
            $PAGE->requires->js('/blocks/custom_course_menu/js/jquery-1.9.1.min.js');
            $PAGE->requires->js('/blocks/custom_course_menu/js/ui/js/jquery-ui-1.10.3.min.js');
            $PAGE->requires->js('/blocks/custom_course_menu/js/courses.js');
        } else { // If using Moodle 2.9+ use the requirejs version and built in jQuery and UI.
            $PAGE->requires->js('/blocks/custom_course_menu/js/courses.js');
        }

        $this->content = new stdClass;
        $this->content->footer = '&nbsp;';

        $adminseesall = true;

        if (isset($CFG->block_course_list_adminview)) {
            if ($CFG->block_course_list_adminview == 'own') {
                $adminseesall = false;
            }
        }

        $html = '<div id="custom_course_menu_application">'
              . '<div id="custom_course_menu_dynamic">'
              . '<span class="interface">Loading...</span>'
              . '</div></div>';

        $footer = "";
        if (is_siteadmin($USER->id) || has_capability('moodle/cohort:manage', context_system::instance(), $USER->id)) {
            $url = new moodle_url('/course/index.php');
            $footer .= html_writer::link($url, html_writer::tag('div', get_string('fulllistofcourses') . '...',
                                         array("style" => "text-align:center;")));
        }

        if(isset(get_config('block_custom_course_menu')->showsearch)) {
	    $showsearch = get_config('block_custom_course_menu')->showsearch;
	    if ($showsearch == 1 ||
               ($showsearch == "admin" &&
	       (is_siteadmin($USER->id) || has_capability('moodle/cohort:manage', context_system::instance(), $USER->id)))) {
      	        $strsearchcourses = get_string("search");
	        $searchurl = new moodle_url('/course/search.php');

		$footer   .= html_writer::start_tag('form', array('id' => "coursesearch", 'action' => $searchurl, 'method' => 'get'));
        	$footer   .= html_writer::start_tag('fieldset', array('class' => "coursesearchbox"));
	        $footer   .= html_writer::empty_tag('input', array('type' => 'text', 'name' => "search"));
	        $footer   .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => $strsearchcourses));
		$footer   .= html_writer::end_tag('fieldset');
        	$footer   .= html_writer::end_tag('form');
            }
	}

        $this->content->footer = $footer;

        $courses = enrol_get_my_courses();
        $hidelink = empty($courses) && empty($CFG->block_custom_course_menu_enablelastviewed) ? array("style" =>
                                             "display:none;text-decoration:none") :
                                             array("style" => "display:inline;text-decoration:none");

        $editicon = $OUTPUT->pix_icon('t/edit', get_string('edit'));
        $interface = new moodle_url('/blocks/custom_course_menu/interface.php');
        $this->content->footer .= html_writer::link($interface,
                                                    $editicon,
                                                    array_merge(array('id' => 'custom_course_menu_interface'), $hidelink));
        $this->content->footer .= html_writer::tag('div', '', array("style" => "clear:both;"));
        $this->content->text = $html;

        return $this->content;
    }
}

