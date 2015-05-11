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

        if ($CFG->version < 20150500) { // If not using Moodle 2.9+
            $PAGE->requires->js('/blocks/my_courses/js/jquery-1.9.1.min.js');
        	$PAGE->requires->js('/blocks/my_courses/js/ui/js/jquery-ui-1.10.3.min.js');
            $PAGE->requires->js('/blocks/my_courses/js/courses.js');    	
        } else { // If using Moodle 2.9+ use the requirejs version and built in jQuery and UI
            $PAGE->requires->js('/blocks/my_courses/js/courses.js');    
        }
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

    	$footer = "";
	$formid = null;
	$inputid = null;
	$inputsize = null;
	$value = null;

    	if (is_siteadmin($USER->id) || has_capability('moodle/cohort:manage', context_system::instance(), $USER->id)) {
    		$url = new moodle_url('/course/index.php');
    		$footer .= html_writer::link($url, html_writer::tag('div', get_string('fulllistofcourses') . '...', array("style" => "text-align:center;")));
    	}

    	if(!empty($CFG->block_my_courses_showsearch)) {
    		$formid = null;
	        $inputid = null;
	        $inputsize = null;
	        $value = null;

		$strsearchcourses= get_string("search");
    		$searchurl = new moodle_url('/course/search.php');

    		$footer .= html_writer::start_tag('form', array('id' => $formid, 'action' => $searchurl, 'method' => 'get', 'style' => 'text-align:center'));
    		$footer .= html_writer::start_tag('fieldset', array('class' => 'coursesearchbox invisiblefieldset'));
    		$footer .= html_writer::empty_tag('input', array('type' => 'text', 'id' => $inputid, 'size' => $inputsize, 'name' => 'search', 'value' => s($value)));

    		$footer .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => $strsearchcourses));

    		$footer .= html_writer::end_tag('fieldset');
    		$footer .= html_writer::end_tag('form');
    	}

    	$this->content->footer = $footer;

    	$courses = enrol_get_my_courses();
    	$hidelink = empty($courses) && empty($CFG->block_my_courses_enablelastviewed) ? array("style" => "display:none") : array("style" => "display: inline;");

    	$edit_icon = $OUTPUT->pix_icon('t/edit', get_string('edit'));
    	$interface = new moodle_url('/blocks/my_courses/interface.php');
    	$this->content->footer .= html_writer::link($interface, $edit_icon, array_merge(array('id' => 'my_courses_interface'), $hidelink));
    	$this->content->text = $html;

    	return $this->content;
    }

	function has_config() {return true;}
}
?>
