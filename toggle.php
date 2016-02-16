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
 * CustomCourseMenu Block Helper - Toggle
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

if (!isloggedin()) {
    die();
}

$catid = required_param('categoryid', PARAM_NOTAGS);
$userid = $USER->id;

$params = array(
  'userid' => $userid,
  'categoryid' => $catid
);

$entry = $DB->get_record('block_custom_course_menu', $params);

if ($entry) {
    $entry->collapsed = $entry->collapsed ? 0 : 1;

    $DB->update_record('block_custom_course_menu', $entry);
} else {
    $entry = new stdClass;
    $entry->userid = $userid;
    $entry->categoryid = $catid;
    $entry->collapsed = 1;

    $DB->insert_record('block_custom_course_menu', $entry);
}
