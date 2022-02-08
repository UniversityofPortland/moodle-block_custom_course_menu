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

namespace block_custom_course_menu\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem implementation for block_custom_course_menu.
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @author additional dev Céline Pervès <cperves@unistra.fr> for University of Strasbourg unistra.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\core_userlist_provider,
        \core_privacy\local\request\plugin\provider {

    /**
     * Get the metadata
     * @param collection $collection The initialised collection to add items to.
     * @return collection
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table('block_custom_course_menu',
            [
                    'id' => 'privacy:metadata:block_custom_course_menu:block_custom_course_menu:id',
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
                    'category' => 'privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc:category',
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
        $contextlist = new \core_privacy\local\request\contextlist();

        // The block_community data is associated at the user context level, so retrieve the user's context id.
        $sql = "SELECT ctx.id
                  FROM {block_custom_course_menu} ccm
                  JOIN {context} ctx ON ctx.instanceid = ccm.userid AND ctx.contextlevel = :contextuser
                 WHERE ccm.userid = :userid
                UNION
                SELECT ctx.id
                  FROM {block_custom_course_menu_etc} ccme
                  JOIN {context} ctx ON ctx.instanceid = ccme.userid AND ctx.contextlevel = :contextuser2
                 WHERE ccme.userid = :userid2
              GROUP BY ctx.id";

        $params = [
                'contextuser'   => CONTEXT_USER,
                'userid'        => $userid,
                'contextuser2'   => CONTEXT_USER,
                'userid2'        => $userid
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        $sql = "SELECT ccm.userid as userid FROM mdl_block_custom_course_menu ccm
                    INNER JOIN mdl_context ctx
                        ON ctx.instanceid=ccm.userid and ctx.contextlevel=:contextuser where ctx.id=:contextid
                UNION
                SELECT ccme.userid as userid from mdl_block_custom_course_menu ccme
                    INNER JOIN mdl_context ctx
                        ON ctx.instanceid=ccme.userid and ctx.contextlevel=:contextuser2 where ctx.id=:contextid
                GROUP BY userid";

        $params = [
                'contextid' => $context->id,
                'contextuser' => CONTEXT_USER,
                'contextid2' => $context->id,
                'contextuser2' => CONTEXT_USER,
        ];
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        $usercontexts = self::validate_contextlist_contexts($contextlist, array(CONTEXT_USER));
        if (empty($usercontexts)) {
            return;
        }
        if (!empty($usercontexts)) {
            $entries = $DB->get_records('block_custom_course_menu', array('userid' => $userid));
            if (!empty($entries)) {
                $usercontext = $usercontexts[\context_user::instance($userid)->id];
                $strings = [get_string('pluginname', 'block_custom_course_menu'),
                            get_string('privacy:metadata:block_custom_course_menu:block_custom_course_menu:textcontext',
                                       'block_custom_course_menu')];
                writer::with_context($usercontext)->export_data($strings, (object)['block_custom_course_menu' => $entries]);
            }
            $entries = $DB->get_records('block_custom_course_menu_etc', array('userid' => $userid));
            if (!empty($entries)) {
                $usercontext = $usercontexts[\context_user::instance($userid)->id];
                $strings = [get_string('pluginname', 'block_custom_course_menu'),
                            get_string('privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc:textcontext',
                                       'block_custom_course_menu')];
                writer::with_context($usercontext)->export_data($strings, (object)['block_custom_course_menu_etc' => $entries]);
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if (!$context instanceof \context_user) {
            return;
        }
        $userid = $context->instanceid;
        $DB->delete_records('block_custom_course_menu', array('userid' => $userid));
        $DB->delete_records('block_custom_course_menu_etc', array('userid' => $userid));
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $CFG;
        require_once($CFG->dirroot . '/blocks/custom_course_menu/handler.php');
        $contexts = $contextlist->get_contexts();
        if (count($contexts) == 0) {
            return;
        }
        $context = reset($contexts);
        if ($context->contextlevel !== CONTEXT_USER) {
            return;
        }
        $user = $contextlist->get_user();
        \custom_course_menu_handler::user_deleted($user);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $CFG;
        require_once($CFG->dirroot . '/blocks/custom_course_menu/handler.php');
        $context = $userlist->get_context();
        if (!$context instanceof \context_user) {
            return;
        }
        $users = $userlist->get_users();
        // Only user in list if concerned by the current user context.
        foreach ($users as $user) {
            if ($context->instanceid == $user->id) {
                \custom_course_menu_handler::user_deleted($user);
            }
        }
    }

    /**
     * Sanitize contextlist course and system context.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     * @param array $contextlevellist list of all context levels.
     * @return mixed of contexts.
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
