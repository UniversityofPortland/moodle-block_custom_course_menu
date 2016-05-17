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
 * CustomCourseMenu Block Helper - Visible
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
require_once('../../config.php');

require_sesskey();
if (!isloggedin()) {
    die();
}

$userid = $USER->id;
$courseid = optional_param('courseid', null, PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_NOTAGS);

if (empty($courseid) && empty($categoryid)) {
    die();
}

if ($categoryid) {
    $itemid = $categoryid;
    $item = 'category';
} else {
    $itemid = $courseid;
    $item = 'course';
}

$params = array(
    'userid' => $userid,
    'item' => $item,
    'itemid' => $itemid,
);

$entry = $DB->get_record('block_custom_course_menu_etc', $params);

if ($entry) {
    $entry->hide = $entry->hide ? 0 : 1;
    $DB->update_record('block_custom_course_menu_etc', $entry);
} else {
    $entry = (object) $params;
    $entry->hide = 1;

    $DB->insert_record('block_custom_course_menu_etc', $entry);
}
