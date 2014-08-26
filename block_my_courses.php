<?php

class block_my_courses extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_my_courses');
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

        $html = '<div id="my_courses_application">'
              . '<div id="my_courses_dynamic">'
              . '<span class="interface">Loading...</span>'
              . '</div></div>';

        if (is_siteadmin($USER->id) || has_capability('moodle/cohort:manage', context_system::instance(), $USER->id)) {
            $url = new moodle_url('/course/index.php');
            $link = html_writer::link($url, get_string('fulllistofcourses') . '...');
            $this->content->footer = $link;
        }

        $courses = enrol_get_my_courses();
        $hidelink = empty($courses) && empty($CFG->block_my_courses_enablelastviewed) ? array("style" => "display:none;text-decoration:none") : array("style" => "display:inline;text-decoration:none");

        $edit_icon = $OUTPUT->pix_icon('t/edit', get_string('edit'));
        $interface = new moodle_url('/blocks/my_courses/interface.php');
        $this->content->footer .= html_writer::link($interface, html_writer::tag('span', 'Start Editing ', array("id" => "overtext", "style" => "display:none;")) . $edit_icon, array_merge(array('id' => 'my_courses_interface','onmouseover' => "$('#overtext').show()", 'onmouseout' => "$('#overtext').hide()"), $hidelink));
        $this->content->text = $html;

        return $this->content;
    }

    function has_config() {return true;}
}
?>
