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

<<<<<<< HEAD
        $footer = "";
=======
>>>>>>> 440249c2dc3aaa21d202c231c2b7cf45c779303f
        if (is_siteadmin($USER->id) || has_capability('moodle/cohort:manage', context_system::instance(), $USER->id)) {
            $url = new moodle_url('/course/index.php');
<<<<<<< HEAD
            $footer .= html_writer::link($url, html_writer::tag('div', get_string('fulllistofcourses') . '...', array("style" => "text-align:center;")));
=======
            $link = html_writer::link($url, html_writer::tag('div', get_string('fulllistofcourses') . '...', array("style" => "text-align:center;")));
            $this->content->footer = $link;
>>>>>>> 9872080b5b444cefc47003921e44e13885376a8c
        }

        if(!empty($CFG->block_my_courses_showsearch)) {
            $strsearchcourses= get_string("search");
            $searchurl = new moodle_url('/course/search.php');

            $footer   .= html_writer::start_tag('form', array('id' => $formid, 'action' => $searchurl, 'method' => 'get', 'style' => 'text-align:center'));
            $footer   .= html_writer::start_tag('fieldset', array('class' => 'coursesearchbox invisiblefieldset'));
            $footer   .= html_writer::empty_tag('input', array('type' => 'text', 'id' => $inputid,
                                              'size' => $inputsize, 'name' => 'search', 'value' => s($value)));
            $footer   .= html_writer::empty_tag('input', array('type' => 'submit',
                                              'value' => $strsearchcourses));
            $footer   .= html_writer::end_tag('fieldset');
            $footer   .= html_writer::end_tag('form');
        }

        $this->content->footer = $footer;

        $courses = enrol_get_my_courses();
<<<<<<< HEAD
        $hidelink = empty($courses) && empty($CFG->block_my_courses_enablelastviewed) ? array("style" => "display:none") : array("style" => "display:inline");

        $edit_icon = $OUTPUT->pix_icon('t/edit', get_string('edit'));
        $interface = new moodle_url('/blocks/my_courses/interface.php');
<<<<<<< HEAD
        $this->content->footer .= html_writer::link($interface, $edit_icon, array_merge(array('id' => 'my_courses_interface'), $hidelink));
=======
        $hidelink = empty($courses) && empty($CFG->block_my_courses_enablelastviewed) ? array("style" => "display:none;text-decoration:none") : array("style" => "display:inline;text-decoration:none");

        $edit_icon = $OUTPUT->pix_icon('t/edit', get_string('edit'));
        $interface = new moodle_url('/blocks/my_courses/interface.php');
        $this->content->footer .= html_writer::link($interface, html_writer::tag('span', 'Start Editing ', array("id" => "overtext", "style" => "display:none;")) . $edit_icon, array_merge(array('id' => 'my_courses_interface','onmouseover' => "$('#overtext').show()", 'onmouseout' => "$('#overtext').hide()"), $hidelink));
>>>>>>> 440249c2dc3aaa21d202c231c2b7cf45c779303f
=======
        $this->content->footer .= html_writer::link($interface, html_writer::tag('span', 'Start Editing ', array("id" => "overtext", "style" => "display:none;")) . $edit_icon, array_merge(array('id' => 'my_courses_interface','onmouseover' => "$('#overtext').show()", 'onmouseout' => "$('#overtext').hide()"), $hidelink));
        $this->content->footer .= html_writer::tag('div', '', array("style" => "clear:both;"));
>>>>>>> 9872080b5b444cefc47003921e44e13885376a8c
        $this->content->text = $html;

        return $this->content;
    }

    function has_config() {return true;}
}
?>
