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
 * CustomCourseMenu Block Helper - Handler
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * custom_course_menu_handler class.
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class custom_course_menu_handler {

    /**
     * Event handler to clean up block entries on user deletes
     *
     * @param stdClass $user
     * @return boolean
     */
    public static function user_deleted($user) {
        global $DB;
        return (
            $DB->delete_records('block_custom_course_menu', array('userid' => $user->id)) &&
            $DB->delete_records('block_custom_course_menu_etc', array('userid' => $user->id))
        );
    }

    /**
     * Event handler to clean up etc entries on course deletes
     *
     * @param stdClass $course
     * @return boolean
     */
    public static function course_deleted($course) {
        global $DB;
        return $DB->delete_records('block_custom_course_menu_etc', array(
            'item' => 'course',
            'itemid' => $course->id,
        ));
    }

    /**
     * Event handler to clean up etc entries on category deletes
     *
     * @param stdClass $category
     * @return boolean
     */
    public static function course_category_deleted($category) {
        global $DB;

        return (
            $DB->delete_records('block_custom_course_menu', array('categoryid' => $category->id)) &&
            $DB->delete_records('block_custom_course_menu_etc', array(
                'item' => 'category',
                'itemid' => $category->id,
            ))
        );
    }
}
