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
 * CustomCourseMenu Block Helper - Sort
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
require_once('../../config.php');

if (!isloggedin() || confirm_sesskey(sessid)) {
    die();
}

$type = required_param('type', PARAM_TEXT);
$userid = $USER->id;
$ids = required_param_array('ids', PARAM_INT);
$sortorder = required_param('sortorder', PARAM_TEXT);
$sortorder = explode(',', $sortorder);

// Prepare parameters for furture sql query. 
$ids = array_values($ids); 
$params = array($type, $userid); 
list($sqlidstest, $idsparams) = $DB->get_in_or_equal($ids); 
$params = array_merge($params, $idsparams); 
$sqlidstest = ' AND itemid ' .$sqlidstest;

$sql = 'SELECT itemid, id, item, sortorder, hide '
     . 'FROM {block_custom_course_menu_etc} '
     . 'WHERE item = ? '
     . ' AND userid = ? ' .$sqlidstest;

$entries = $DB->get_records_sql($sql, $params);

foreach ($ids as $index => $id) {
    if (isset($entries[$id])) {
        $entry = $entries[$id];
    } else {
        $entry = new stdClass;
        $entry->hide = 0;
        $entry->item = $type;
    }

    $entry->userid = $userid;
    $entry->itemid = $id;
    $entry->sortorder = $sortorder[$index];
		if (isset($entry->id)) {
    		$DB->update_record('block_custom_course_menu_etc', $entry);
    	} else {
    		$DB->insert_record('block_custom_course_menu_etc', $entry);
    	}
    
}
