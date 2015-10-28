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
 * CustomCourseMenu Block Helper - Settings
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_custom_course_menu_enablefavorites',
                       get_string('enablefavorites', 'block_custom_course_menu'),
                       get_string('configenablefavorites', 'block_custom_course_menu'), 0));
    $settings->add(new admin_setting_configcheckbox('block_custom_course_menu_enablelastviewed',
                       get_string('enablelastviewed', 'block_custom_course_menu'),
                       get_string('configenablelastviewed', 'block_custom_course_menu'), 0));
    $settings->add(new admin_setting_configtext('block_custom_course_menu_lastviewedamount',
                       get_string('lastviewedamount', 'block_custom_course_menu'),
                       get_string('configlastviewedamount', 'block_custom_course_menu'), 5, PARAM_INT));

    $options = array(
        0 => get_string('no'),
        1 => get_string('yes'),
        'admin' => get_string('adminonly', 'block_custom_course_menu')
    );
    $name = new lang_string('attemptreopenmethod', 'mod_assign');
    $description = new lang_string('attemptreopenmethod_help', 'mod_assign');
    $settings->add(new admin_setting_configselect('block_custom_course_menu_showsearch',
                                                    get_string('showsearch', 'block_custom_course_menu'),
                                                     get_string('configshowsearch', 'block_custom_course_menu'),
                                                    0,
                                                    $options));
}