<?php

abstract class my_courses_handler {

    /**
     * Event handler to clean up block entries on user deletes
     *
     * @param stdClass $user
     * @return boolean
     */
    public static function user_deleted($user) {
        global $DB;
        return (
            $DB->delete_records('block_my_courses', array('userid' => $user->id)) &&
            $DB->delete_records('block_my_courses_meta', array('userid' => $user->id))
        );
    }

    /**
     * Event handler to clean up meta entries on course deletes
     *
     * @param stdClass $course
     * @return boolean
     */
    public static function course_deleted($course) {
        global $DB;
        return $DB->delete_records('block_my_courses_meta', array(
            'item' => 'course',
            'itemid' => $course->id,
        ));
    }

    /**
     * Event handler to clean up meta entries on category deletes
     *
     * @param stdClass $category
     * @return boolean
     */
    public static function course_category_deleted($category) {
        global $DB;

        return (
            $DB->delete_records('block_my_courses', array('categoryid' => $category->id)) &&
            $DB->delete_records('block_my_courses_meta', array(
                'item' => 'category',
                'itemid' => $category->id,
            ))
        );
    }
}
