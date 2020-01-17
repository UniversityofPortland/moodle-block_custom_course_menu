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
 * block_custom_course_menu data generator
 *
 * @package    block_custom_course_menu
 * @category   test
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @copyright  2020 University of Strasbourg  {@link http://unistra.fr}
 * @author Céline Pervès <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


class block_custom_course_menu_generator extends testing_block_generator {

    public function set_course_visible($userid,$coursecatid, $iscourse, $isvisible){
        global $DB;
        if ($iscourse) {
            $itemid = $coursecatid;
            $item = 'course';
        } else {
            $itemid = $coursecatid;
            $item = 'category';
        }

        $params = array(
                'userid' => $userid,
                'item' => $item,
                'itemid' => $itemid,
        );

        $entry = $DB->get_record('block_custom_course_menu_etc', $params);

        if ($entry) {
            $DB->update_record('block_custom_course_menu_etc', $entry);
        } else {
            $entry = (object) $params;
            $entry->hide = !$isvisible;;
            $DB->insert_record('block_custom_course_menu_etc', $entry);
        }
    }

    public function set_collapsed_category($userid,$categoryid,$collapsed){
        global $DB;
        $params = array(
                'userid' => $userid,
                'categoryid' => $categoryid
        );

        $entry = $DB->get_record('block_custom_course_menu', $params);

        if ($entry) {
            $entry->collapsed =intval($collapsed);

            $DB->update_record('block_custom_course_menu', $entry);
        } else {
            $entry = new stdClass;
            $entry->userid = $userid;
            $entry->categoryid = $categoryid;
            $entry->collapsed =intval($collapsed);

            $DB->insert_record('block_custom_course_menu', $entry);
        }
    }

}