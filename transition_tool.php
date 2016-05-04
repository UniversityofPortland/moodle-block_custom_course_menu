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
 * CustomCourseMenu Transition Tool
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();

echo $OUTPUT->header();

try {
    $dataobjects1 = $DB->get_records_sql('SELECT * FROM {block_my_courses}', array());
    $dataobjects2 = $DB->get_records_sql('SELECT * FROM {block_my_courses_meta}', array());
    $DB->insert_records('block_custom_course_menu', $dataobjects1);
    $DB->insert_records('block_custom_course_menu_etc', $dataobjects2);
    $DB->set_field('block_instances', 'blockname', 'custom_course_menu', array('blockname' => 'my_courses'));
    echo $OUTPUT->container('Transition Completed');
} catch (Exception $e) {
    echo $OUTPUT->container('Transition has already occured');
}

echo $OUTPUT->footer();
