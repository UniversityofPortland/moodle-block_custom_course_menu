<?php

require_once '../../config.php';

$PAGE->set_context(context_system::instance());

$editing = optional_param("editing", 0, PARAM_INT);

$course_icon = $OUTPUT->pix_icon('i/course', get_string('course', 'block_my_courses'));

$minus_icon = $OUTPUT->pix_icon('t/switch_minus', get_string('hide'));
$plus_icon = $OUTPUT->pix_icon('t/switch_plus', get_string('show'));

$inconspicuous_icon = $OUTPUT->pix_icon('t/show', get_string('show'));
$visible_icon = $OUTPUT->pix_icon('t/hide', get_string('hide'));

$categories = get_category_tree();

if (empty($categories)) {
    echo "You are not enrolled in any courses.";
    die();
}

$sortable_css = $editing ? ' course-sortable' : '';

$html = '<ul class="my_courses_category_list' . $sortable_css . '">';

foreach ($categories as $category) {
    if (!$editing && $category->meta->hide) {
        continue;
    }

    $params = array(
        'userid' => $USER->id,
        'categoryid' => $category->id,
    );

    $collapsed = $DB->get_field('block_my_courses', 'collapsed', $params);
    $collapsed_css = 'collapsed';

    if (!$collapsed) {
        $collapsed_css = 'not_collapsed';
    }

    $switch = $collapsed ? 'plus' : 'minus';
    $switch_icon = ${$switch . '_icon'};

    $url = new moodle_url('/blocks/my_courses/toggle.php', $params);
    $anchor = html_writer::link($url, $switch_icon, array(
        'class' => "category_switcher $switch",
    ));

    $hide = '';
    $hidden_switch = '';
    if ($editing) {
        $hidden_switch = !empty($category->meta->hide) ? 'inconspicuous' : 'visible';
        $switch_icon = ${$hidden_switch . '_icon'};

        $url = new moodle_url('/blocks/my_courses/visible.php', $params);
        $hide = html_writer::link($url, $switch_icon, array(
            'class' => "item_visibility $hidden_switch",
        ));
        $hide .= ' ';
    }

    $html .= "<li class='my_courses_category $hidden_switch'>$hide$anchor {$category->name}";
    $html .= '<ul class="my_courses_list ' . $hidden_switch . ' ' . $collapsed_css . $sortable_css . '">';
    foreach ($category->courses as $course) {
        if (!$editing && $course->meta->hide) {
            continue;
        }

        $class = !$course->visible ? 'dimmed' : '';

        $hide = '';
        $hidden_switch = '';
        if ($editing) {
            $url = new moodle_url('/blocks/my_courses/visible.php', array(
                'userid' => $USER->id,
                'courseid' => $course->id,
            ));

            $hidden_switch = !empty($course->meta->hide) ? 'inconspicuous' : 'visible';
            $switch_icon = ${$hidden_switch . '_icon'};

            $hide = html_writer::link($url, $switch_icon, array(
                'class' => "item_visibility $hidden_switch",
            ));
            $hide .= ' ';
        }

        $url = new moodle_url('/course/view.php', array('id' => $course->id));
        $anchor = html_writer::link($url, $course->fullname, array('class' => $class));

        $content = "$hide$course_icon $anchor";
        $html .= html_writer::tag('li', $content, array(
            'class' => "my_courses_course $hidden_switch",
        ));
    }

    $html .= '</ul>';
    $html .= '</li>';
}

$url = new moodle_url('/blocks/my_courses/sort.php', array(
    'userid' => $USER->id,
));

$html .= '</ul>'
      .
      html_writer::tag('span', $plus_icon, array(
          'id' => 'my_courses_plus',
          'style' => 'display: none;',
      )) .
      html_writer::tag('span', $minus_icon, array(
          'id' => 'my_courses_minus',
          'style' => 'display: none;',
      )) .
      html_writer::tag('span', $visible_icon, array(
          'id' => 'my_courses_visible',
          'style' => 'display: none;',
      )) .
      html_writer::tag('span', $inconspicuous_icon, array(
          'id' => 'my_courses_inconspicuous',
          'style' => 'display: none;',
      )) .
      html_writer::tag('span', $url->out(), array(
          'id' => 'my_courses_sort',
          'style' => 'display: none;',
      ));

echo $html;

/**
 * Helper method to pull all of the categories and courses
 *
 * @return array
 */
function get_category_tree() {
    global $DB;

    $category_meta = get_meta_for('category');
    $course_meta = get_meta_for('course');

    $courses = enrol_get_my_courses();

    $categories = array();
    foreach ($courses as $course) {
        if (!isset($categories[$course->category])) {
            $params = array('id' => $course->category);
            $category = $DB->get_record('course_categories', $params);
            $category->courses = array();

            if (isset($category_meta[$category->id])) {
                $category->meta = $category_meta[$category->id];
            } else {
                $category->meta = (object) array('hide' => 0);
            }

            $categories[$course->category] = $category;
        }

        if (isset($course_meta[$course->id])) {
            $course->meta = $course_meta[$course->id];
        } else {
            $course->meta = (object) array('hide' => 0);
        }

        $categories[$course->category]->courses[$course->id] = $course;
    }

    uasort($categories, function($cata, $catb) {
        if (isset($cata->meta->sortorder) && isset($catb->meta->sortorder)) {
            return $cata->meta->sortorder < $catb->meta->sortorder ? -1 : 1;
        } else if (isset($cata->meta->sortorder)) {
            return -1;
        } else if (isset($catb->meta->sortorder)) {
            return 1;
        } else {
            return strcmp($cata->name, $catb->name);
        }
    });

    foreach ($categories as $category) {
        uasort($category->courses, function($coursea, $courseb) {
            if (isset($coursea->meta->sortorder) && isset($courseb->meta->sortorder)) {
                return $coursea->meta->sortorder < $courseb->meta->sortorder ? -1 : 1;
            } else if (isset($coursea->meta->sortorder)) {
                return -1;
            } else if (isset($courseb->meta->sortorder)) {
                return 1;
            } else {
                return $coursea->sortorder < $courseb->sortorder ? -1 : 1;
            }
        });
    }

    return $categories;
}

/**
 * Helper function to retrieve the meta information for
 * categories and courses
 *
 * @param string $item
 * @return stdClass meta
 */
function get_meta_for($item) {
    global $DB, $USER;

    $sql = "SELECT itemid AS id, hide, sortorder FROM {block_my_courses_meta} "
         . "WHERE userid = :userid "
         . "AND item = :item";

    return $DB->get_records_sql($sql, array(
        'item' => $item,
        'userid' => $USER->id,
    ));
}
