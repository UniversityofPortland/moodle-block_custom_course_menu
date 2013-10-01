<?php

class block_my_courses_menu extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_my_courses_menu');
    }

    function get_content() {
        global $THEME, $CFG, $USER, $DB, $OUTPUT, $PAGE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $PAGE->requires->js('/blocks/my_courses/js/jquery-1.9.1.min.js');
        $PAGE->requires->js('/blocks/my_courses/js/ui/js/jquery-ui-1.10.3.min.js');
        $PAGE->requires->js('/blocks/my_courses/js/courses.js');

        $this->content = new stdClass;
        $this->content->footer = '&nbsp;';

        $adminseesall = true;

        if (isset($CFG->block_course_list_adminview)) {
           if ($CFG->block_course_list_adminview == 'own') {
               $adminseesall = false;
           }
        }

        $edit_icon = $OUTPUT->pix_icon('t/edit', get_string('edit'));
        $interface = new moodle_url('/blocks/my_courses/interface.php');
        $html = '<div id="my_courses_application">'
              . '<div id="my_courses_dynamic">'
              . '<span class="interface">Loading...</span>'
              . '</div></div>';

        if (is_siteadmin($USER->id))  {
            $url = new moodle_url('/course/index.php');

            $link = html_writer::link($url, get_string('fulllistofcourses') . '...');
            $this->content->footer = $link;
        }

        $this->content->footer .= html_writer::link($interface, $edit_icon, array(
            'id' => 'my_courses_interface',
        ));

        $this->content->text = $html;

        return $this->content;
    }
}
?>
