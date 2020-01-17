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
 * Unit tests for the block_custom_course_menu implementation of the privacy API.
 *
 * @package    block_custom_course_menu
 * @category   test
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @copyright  2020 unistra  {@link http://unistra.fr}
 * @author  Céline Pervès <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\request\writer;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\approved_userlist;
use \block_custom_course_menu\privacy\provider;

class block_custom_course_menu_privacy_testcase extends \core_privacy\tests\provider_testcase {

    public function setUp() {
        $this->resetAfterTest(true);
    }

    /**
     * Tets get_contexts_for_userid function.
     * Function that get the list of contexts that contain user information for the specified user.
     * @throws coding_exception
     */
    public function test_get_contexts_for_userid(){
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $admin = get_admin();
        $this->setUser($user);
        $usercontext = \context_user::instance($user->id);
        //create block
        $this->create_block_instance();
        //check datas
        // at this point no user contexts registered
        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(0, $contextlist);
        //register user as student for courses
        $this->create_coursecat_and_enrol_set_block($user);
        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(1, $contextlist);
    }

    /**
     * Test expert_user_data function.
     * Function that get the list of users who have data within a context.
     * @throws coding_exception
     */
    public function test_export_user_data(){
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $admin = get_admin();
        $this->setUser($user);
        $usercontext = \context_user::instance($user->id);
        //create block
        $this->create_block_instance();
        $this->export_context_data_for_user($user->id, $usercontext, 'block_custom_course_menu');
        $writer = \core_privacy\local\request\writer::with_context($usercontext);
        $this->assertFalse($writer->has_any_data());

        list($category1, $category2, $category3, $course1,$course2,$course3) = $this->create_coursecat_and_enrol_set_block($user);
        $this->export_context_data_for_user($user->id, $usercontext, 'block_custom_course_menu');
        $writer = \core_privacy\local\request\writer::with_context($usercontext);
        $this->assertTrue($writer->has_any_data());

        $data = $writer->get_data([get_string('pluginname', 'block_custom_course_menu'),get_string('privacy:metadata:block_custom_course_menu:block_custom_course_menu:textcontext','block_custom_course_menu')]);
        $this->assertInstanceOf('stdClass', $data);
        $this->assertTrue(property_exists($data, 'block_custom_course_menu'));
        foreach($data->block_custom_course_menu as $record){
            $this->assertEquals($user->id, $record->userid);
            $this->assertEquals($category3->id, $record->categoryid);
            $this->assertEquals("1", $record->collapsed);
        }

        $data = $writer->get_data([get_string('pluginname', 'block_custom_course_menu'),get_string('privacy:metadata:block_custom_course_menu:block_custom_course_menu_etc:textcontext','block_custom_course_menu')]);
        $this->assertInstanceOf('stdClass', $data);
        $this->assertTrue(property_exists($data, 'block_custom_course_menu_etc'));
        foreach($data->block_custom_course_menu_etc as $record){
            $this->assertEquals($user->id, $record->userid);
            $this->assertTrue($record->itemid == $category1->id || $record->itemid == $course1->id);
        }
}

    /**
     * Test delete_data_for_all_users_in_context function.
     * Function that delete all data for all users in the specified context
     * @throws coding_exception
     */
    public function test_delete_data_for_all_users_in_context() {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $admin = get_admin();
        $this->setUser($user);
        $usercontext = \context_user::instance($user->id);
        //create block
        $this->create_block_instance();
        // Delete the context.
        provider::delete_data_for_all_users_in_context($usercontext);
        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(0, $contextlist);
    }

    /**
     * Test delete_data_for_user function.
     * Function that delete all user data for the specified user, in the specified contexts.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_delete_data_for_user() {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $admin = get_admin();
        $this->setUser($user);
        $usercontext = \context_user::instance($user->id);
        //create block
        $this->create_block_instance();
        // Delete the context.
        $approvedcontextlist = new \core_privacy\tests\request\approved_contextlist(
                \core_user::get_user($user->id),
                'block_custom_course_menu',
                [$usercontext->id]
        );
        provider::delete_data_for_user($approvedcontextlist);
        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(0, $contextlist);
    }

    /**
     * Test delete_data_for_users function.
     * Function that Delete multiple users within a single context.
     * @throws coding_exception
     */
    public function test_delete_data_for_users() {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $admin = get_admin();
        $this->setUser($user);
        $usercontext = \context_user::instance($user->id);
        //create block
        $this->create_block_instance();
        // Delete the context.
        $approveduserlist = new approved_userlist($usercontext, 'block_custom_course_menu', [$user->id]);
        provider::delete_data_for_users($approveduserlist);
        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(0, $contextlist);
    }

    /**
     * Create a block instance
     * @param $SITE
     * @throws coding_exception
     */
    private function create_block_instance() {
        global $SITE;
        $blockgenerator = $this->getDataGenerator()->get_plugin_generator('block_custom_course_menu');
        $blockgenerator->create_instance(array('course' => $SITE));
    }

    /**
     * Create all the necessary course/category structure with user enrolments to test bloc custm_course_menu for a given user.
     * Then set block instance settings to test privacy for this block.
     * @param stdClass $user
     * @throws coding_exception
     */
    private function create_coursecat_and_enrol_set_block($user) {
        $category1 = $this->getDataGenerator()->create_category();
        $category2 = $this->getDataGenerator()->create_category();
        $category3 = $this->getDataGenerator()->create_category();
        $course1 = $this->getDataGenerator()->create_course(['category' => $category1->id]);
        $course2 = $this->getDataGenerator()->create_course(['category' => $category2->id]);
        $course3 = $this->getDataGenerator()->create_course(['category' => $category3->id]);
        $this->getDataGenerator()->enrol_user($user->id, $course1->id, 'student');
        $this->getDataGenerator()->enrol_user($user->id, $course2->id, 'student');
        $this->getDataGenerator()->enrol_user($user->id, $course3->id, 'student');
        $blockgenerator = $this->getDataGenerator()->get_plugin_generator('block_custom_course_menu');
        $blockgenerator->set_course_visible($user->id,$category1->id, false, false);
        $blockgenerator->set_course_visible($user->id,$course1->id, true, true);
        $blockgenerator->set_collapsed_category($user->id,$category3->id,true);
        return array($category1, $category2, $category3, $course1,$course2,$course3);
    }

}