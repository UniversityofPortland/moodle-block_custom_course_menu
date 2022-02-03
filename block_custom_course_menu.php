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
 * block_custom_course_menu class.
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
        global $CFG, $USER, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->page->requires->string_for_js('loading', 'block_custom_course_menu');
        $this->page->requires->string_for_js('editingon', 'block_custom_course_menu');
        $this->page->requires->string_for_js('editingoff', 'block_custom_course_menu');
        $this->page->requires->jquery();
        $this->page->requires->js('/blocks/custom_course_menu/js/courses.js');

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
              . '<span class="interface">' . get_string('loading', 'block_custom_course_menu') . '</span>'
              . '</div></div>';

        $footer = "";
        if (is_siteadmin($USER->id) || has_capability('moodle/cohort:manage', context_system::instance(), $USER->id)) {
            $url = new moodle_url('/course/index.php');
            $footer .= html_writer::tag('div', html_writer::link($url, get_string('fulllistofcourses') . '...'),
                                        array('style' => 'text-align:center;'));
        }

        if (isset(get_config('block_custom_course_menu')->showsearch)) {
            $showsearch = get_config('block_custom_course_menu')->showsearch;
            if ($showsearch == 1 ||
                    ($showsearch == 'admin' &&
                        (is_siteadmin($USER->id) ||
                            has_capability('moodle/cohort:manage', context_system::instance(), $USER->id)
                        )
                    )
               ) {
                    $searchurl = new moodle_url('/course/search.php');
                    $footer   .= html_writer::start_tag('form', array('id' => 'coursesearch',
                                                                      'action' => $searchurl,
                                                                      'method' => 'get'));
                    $footer   .= html_writer::start_tag('fieldset', array('class' => 'coursesearchbox'));
                    $footer   .= html_writer::empty_tag('input', array('type' => 'text',
                                                                       'name' => 'search',
                                                                       'class' => 'searchfield'));
                    $footer   .= html_writer::link('javascript: coursesearch.submit()',
                                                   '<i class="fa fa-search"></i>',
                                                   array('id' => 'searchbutton'));
                    $footer   .= html_writer::end_tag('fieldset');
                    $footer   .= html_writer::end_tag('form');
            }
        }

        $this->content->footer = $footer;

        $courses = enrol_get_my_courses();
        $hidelink = array();
        if (empty($courses) && empty($CFG->block_custom_course_menu_enablelastviewed)) {
            $hidelink = array('class' => 'hidden');
        }

        $this->content->text = '<div class="editingmode editingoff">';
        $editicon = '<i class="fa editingicon"></i>';
        $interface = new moodle_url('/blocks/custom_course_menu/interface.php');
        $this->content->text .= html_writer::link($interface,
                                                 $editicon,
                                                 array_merge(array('id' => 'custom_course_menu_interface'), $hidelink));
        $this->content->text .= html_writer::tag('div', '', array('style' => 'clear:both;'));
        $this->content->text .= $html . '</div>';
        return $this->content;
    }
}

