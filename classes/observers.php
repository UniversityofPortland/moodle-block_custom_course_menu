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
 * CustomCourseMenu Block Helper - Observer
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_custom_course_menu;
defined('MOODLE_INTERNAL') || die();

/**
 * observers class.
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observers {
    /**
     * Event handler to clean up block entries on user deletes
     *
     * @param \core\event\user_deleted $event
     * @return void
     */
    public static function user_deleted(\core\event\user_deleted $event) {
        global $DB;
        $DB->delete_records('block_custom_course_menu', array('userid' => $event->userid));
        $DB->delete_records('block_custom_course_menu_etc', array('userid' => $event->userid));
    }

    /**
     * Event handler to clean up etc entries on course deletes
     *
     * @param \core\event\course_deleted $event
     * @return void
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;
        $DB->delete_records('block_custom_course_menu_etc', array(
            'item' => 'course',
            'itemid' => $event->courseid,
        ));

        $DB->delete_records('block_custom_course_menu_etc', array(
            'item' => 'favorite',
            'itemid' => $event->courseid,
        ));
    }

    /**
     * Event handler to clean up etc entries on category deletes
     *
     * @param \core\event\course_category_deleted $event
     * @return void
     */
    public static function course_category_deleted(\core\event\course_category_deleted $event) {
        global $DB;
        $DB->delete_records('block_custom_course_menu', array('categoryid' => $event->objectid));
        $DB->delete_records('block_custom_course_menu_etc', array(
            'item' => 'category',
            'itemid' => $event->objectid,
        ));
    }
}
