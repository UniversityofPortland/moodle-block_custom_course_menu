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
 * Privacy Subsystem implementation for block_custom_course_menu.
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @author additional dev Céline Pervès <cperves@unistra.fr> for University of Strasbourg unistra.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_custom_course_menu\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();
/**
 * Privacy Subsystem for block_custom_course_menu implementing null_provider.
 *
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\core_userlist_provider,
        \core_privacy\local\request\plugin\provider
{
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table('block_custom_course_menu',
            [
                    'id'=> 'privacy:metadata:block_custom_course_menu:block_custom_course_menu:id',
                    'userid' => 'privacy:metadata:block_custom_course_menu:block_custom_course_menu:userid',
                    'categoryid' => 'privacy:metadata:block_custom_course_menu:block_custom_course_menu:categoryid',
                    'collapsed' => 'privacy:metadata:block_custom_course_menu:block_custom_course_menu:collapsed',
            ],
                'privacy:metadata:block_custom_course_menu:block_custom_course_menu'
        );
        $collection->add_database_table('block_custom_course_menu_etc',
            [
                    'id' => 'privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc:id',
                    'userid'  => 'privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc:userid',
                    'category' =>  'privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc:category',
                    'itemid' => 'privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc:itemid',
                    'hide' => 'privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc:hide',
                    'sortorder' => 'privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc:sortorder',
                    'fav' => 'privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc:fav',
            ],
            'privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc'
        );
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        //all site information so system
        $contextlist =  new contextlist();
        $contextlist->add_system_context();
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context instanceof \context_system) {
            //return all user who has a backup resore task
            $sql = 'select distinct userid from {block_custom_course_menu} union select distinct userid from {block_custom_course_menu_etc}';
            $params = [];
            $userlist->add_from_sql('userid', $sql, $params);
        }
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $systemcontexts = self::validate_contextlist_contexts($contextlist, array(CONTEXT_SYSTEM));
        if(empty($systemcontexts)){
            return;
        }
        $userid = $contextlist->get_user()->id;
        if(!empty($systemcontexts)){
            $entries = $DB->get_records('block_custom_course_menu', array('userid' => $userid));
            writer::with_context($systemcontexts[\context_system::instance()->id])->export_data([get_string('pluginname', 'block_custom_course_menu'),get_string('privacy:metadata:block_custom_course_menu:block_custom_course_menu:textcontext','block_custom_course_menu')],(object)['block_custom_course_menu'=> $entries]);
            $entries = $DB->get_records('block_custom_course_menu_etc', array('userid' => $userid));
            writer::with_context($systemcontexts[\context_system::instance()->id])->export_data([get_string('pluginname', 'block_custom_course_menu'),get_string('privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc:textcontext','block_custom_course_menu')],(object)['block_custom_course_menu_etc'=> $entries]);
        }

    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if($context instanceof \context_system){
            $DB->delete_records('block_custom_course_menu');
            $DB->delete_records('block_custom_course_menu_etc');
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {

            if (!$context instanceof \context_system) {
                continue;
            }
            \custom_course_menu_handler::user_deleted($user);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof \context_system) {
            return;
        }
        $users = $userlist->get_users();
        foreach($users as $user){
            \custom_course_menu_handler::user_deleted($user);
        }
    }

    /**
     * sanitize contextlist course and system context
     * @param approved_contextlist $contextlist
     * @return mixed
     */
    protected static function validate_contextlist_contexts(approved_contextlist $contextlist, $contextlevellist) {
        return array_reduce($contextlist->get_contexts(), function($carry, $context) use($contextlevellist) {
            if (in_array($context->contextlevel, $contextlevellist)) {
                $carry[$context->id] = $context;
            }
            return $carry;
        }, []);
    }
}
