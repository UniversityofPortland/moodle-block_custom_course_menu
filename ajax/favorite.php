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
 * CustomCourseMenu Helper - Favorite
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once('../../../config.php');

require_sesskey();
if (!isloggedin()) {
    die();
    require_login(); // Just to pass the codechecker.
}

$userid = $USER->id;
$courseid = optional_param('courseid', null, PARAM_INT);

if ((empty($courseid) && empty($userid))) {
    die();
}

$params = array(
    'userid' => $userid,
    'item' => 'course',
    'itemid' => $courseid,
    'fav' => 1
);

$entry = $DB->get_record('block_custom_course_menu_etc', $params);

if ($entry) {
    $entry->fav = 0;
    $DB->update_record('block_custom_course_menu_etc', $entry);

    $params["item"] = 'favorite';
    $DB->delete_records('block_custom_course_menu_etc', $params);
} else {
    $entry = (object) $params;
    $params["fav"] = 0;
    if ($newentry = $DB->get_record('block_custom_course_menu_etc', $params)) {
        $newentry->fav = 1;
        $DB->update_record('block_custom_course_menu_etc', $newentry);
    } else {
        $DB->insert_record('block_custom_course_menu_etc', $entry);
    }

    $entry->item = 'favorite';
    $entry->sortorder = 0;
    $DB->insert_record('block_custom_course_menu_etc', $entry);
}
echo json_encode(array(true));
