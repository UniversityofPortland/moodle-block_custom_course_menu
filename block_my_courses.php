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
 * Main set up of my_courses block
 *
 * @package    block-my_courses
 * @category   blocks
 * @copyright  2015 University of Porltand
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

class block_my_courses extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_my_courses');
    }

    function get_content() {
        global $THEME, $CFG, $USER, $DB, $OUTPUT, $PAGE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        if ($CFG->version < 20150500) { // If not using Moodle 2.9+
            $PAGE->requires->js('/blocks/my_courses/js/jquery-1.9.1.min.js');
        	$PAGE->requires->js('/blocks/my_courses/js/ui/js/jquery-ui-1.10.3.min.js');
            $PAGE->requires->js('/blocks/my_courses/js/courses.js');
        } else { // If using Moodle 2.9+ use the requirejs version and built in jQuery and UI
            $PAGE->requires->js('/blocks/my_courses/js/courses.js');
        }

        $this->content = new stdClass;
        $this->content->footer = '&nbsp;';

        $adminseesall = true;

        if (isset($CFG->block_course_list_adminview)) {
           if ($CFG->block_course_list_adminview == 'own') {
               $adminseesall = false;
           }
        }

        $html = '<div id="my_courses_application">'
              . '<div id="my_courses_dynamic">'
              . '<span class="interface">Loading...</span>'
              . '</div></div>';

        $footer = "";
        if (is_siteadmin($USER->id) || has_capability('moodle/cohort:manage', context_system::instance(), $USER->id)) {
            $url = new moodle_url('/course/index.php');
            $footer .= html_writer::link($url, html_writer::tag('div', get_string('fulllistofcourses') . '...', array("style" => "text-align:center;")));
        }

        if(!empty($CFG->block_my_courses_showsearch) &&
            ($CFG->block_my_courses_showsearch == 1 ||
            ($CFG->block_my_courses_showsearch == "admin" && (is_siteadmin($USER->id) || has_capability('moodle/cohort:manage', context_system::instance(), $USER->id))))) {
            $strsearchcourses= get_string("search");
            $searchurl = new moodle_url('/course/search.php');

            $footer   .= html_writer::start_tag('form', array('id' => "coursesearch", 'action' => $searchurl, 'method' => 'get'));
            $footer   .= html_writer::start_tag('fieldset', array('class' => "coursesearchbox"));
            $footer   .= html_writer::empty_tag('input', array('type' => 'text', 'name' => "search"));
            $footer   .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => $strsearchcourses));
            $footer   .= html_writer::end_tag('fieldset');
            $footer   .= html_writer::end_tag('form');
        }

        $this->content->footer = $footer;

        $courses = enrol_get_my_courses();
        $hidelink = empty($courses) && empty($CFG->block_my_courses_enablelastviewed) ? array("style" => "display:none;text-decoration:none") : array("style" => "display:inline;text-decoration:none");

        $edit_icon = $OUTPUT->pix_icon('t/edit', get_string('edit'));
        $interface = new moodle_url('/blocks/my_courses/interface.php');
        $this->content->footer .= html_writer::link($interface, $edit_icon, array_merge(array('id' => 'my_courses_interface'), $hidelink));
        $this->content->footer .= html_writer::tag('div', '', array("style" => "clear:both;"));
        $this->content->text = $html;

        return $this->content;
    }

    function has_config() {return true;}
}
?>
