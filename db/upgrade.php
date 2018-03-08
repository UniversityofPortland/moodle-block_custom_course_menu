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
 * Version details
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade scripts.
 *
 * @param int $oldversion current version.
 * @return bool
 */
function xmldb_block_custom_course_menu_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Changes columns to integers to be postgres approved.
    if ($oldversion < 2016051600) {
        // The categoryid field should be an integer.  To change this, we will create a new field to hold the data.
        // The preexisting field will be renamed and the contents will be copied out.
        $table = new xmldb_table('block_custom_course_menu');
        $index = new xmldb_index('usercat', XMLDB_INDEX_UNIQUE, array('userid', 'categoryid'));
        $origfield = new xmldb_field('categoryid', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null, 'userid');
        $newfield = new xmldb_field('categoryid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, 0, 'userid');

        // Remove existing index.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Rename existing categoryid field.
        if ($dbman->field_exists($table, $origfield)) {
            $dbman->rename_field($table, $origfield, 'categoryid_depr');
        }

        // Create new categoryid field.
        if (!$dbman->field_exists($table, $origfield)) {
            $dbman->add_field($table, $newfield);
        }

        // Copy data from old field to new field, checking that data is in integer form.
        $rs = $DB->get_recordset('block_custom_course_menu');
        foreach ($rs as $record) {
            if (is_numeric($record->categoryid_depr)) {
                $DB->set_field('block_custom_course_menu', 'categoryid', $record->categoryid_depr, array('id' => $record->id));
            } else if ($record->categoryid_depr === "favs") {
                $DB->set_field('block_custom_course_menu', 'categoryid', -1, array('id' => $record->id));
            } else if ($record->categoryid_depr === "lastviewed") {
                $DB->set_field('block_custom_course_menu', 'categoryid', -2, array('id' => $record->id));
            }
        }
        $rs->close(); // Don't forget to close the recordset!

        // Add back the idex using the new field.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // The itemid field should be an integer.  To change this, we will create a new field to hold the data.
        // The preexisting field will be renamed and the contents will be copied out.
        $table = new xmldb_table('block_custom_course_menu_etc');
        $index = new xmldb_index('useiteitemid', XMLDB_INDEX_UNIQUE, array('userid', 'item', 'itemid'));
        $origfield = new xmldb_field('itemid', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null, 'item');
        $newfield = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, 0, 'item');

        // Remove existing index.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Rename existing itemid field.
        if ($dbman->field_exists($table, $origfield)) {
            $dbman->rename_field($table, $origfield, 'itemid_depr');
        }

        // Create new itemid field.
        if (!$dbman->field_exists($table, $origfield)) {
            $dbman->add_field($table, $newfield);
        }

        // Copy data from old field to new field, checking that data is in integer form.
        $rs = $DB->get_recordset('block_custom_course_menu_etc');
        foreach ($rs as $record) {
            if (is_numeric($record->itemid_depr)) {
                $DB->set_field('block_custom_course_menu_etc', 'itemid', $record->itemid_depr, array('id' => $record->id));
            } else if ($record->itemid_depr === "favs") {
                $DB->set_field('block_custom_course_menu_etc', 'itemid', -1, array('id' => $record->id));
            } else if ($record->itemid_depr === "lastviewed") {
                $DB->set_field('block_custom_course_menu_etc', 'itemid', -2, array('id' => $record->id));
            }
        }
        $rs->close(); // Don't forget to close the recordset!

        // Add back the idex using the new field.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Custom_course_menu savepoint reached.
        upgrade_block_savepoint(true, 2016051600, 'custom_course_menu');
    }

    // Changes to favorite storage.
    if ($oldversion < 2018030700) {
        $rs = $DB->get_recordset('block_custom_course_menu_etc', array("fav" => 1));
        foreach ($rs as $record) {
            $params = array(
                'userid' => $record->userid,
                'item' => 'favorite',
                'itemid' => $record->itemid,
                'sortorder' => 0,
                'fav' => 1
            );

            $entry = (object) $params;
            $DB->insert_record('block_custom_course_menu_etc', $entry);
        }
        $rs->close(); // Don't forget to close the recordset!

        // Custom_course_menu savepoint reached.
        upgrade_block_savepoint(true, 2018030700, 'custom_course_menu');
    }

    return true;
}
