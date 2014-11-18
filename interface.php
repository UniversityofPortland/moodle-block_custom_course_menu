<?php

require_once '../../config.php';

$PAGE->set_context(context_system::instance());

$editing = optional_param("editing", 0, PARAM_INT);

$course_icon = $OUTPUT->pix_icon('i/course', get_string('course', 'block_my_courses'));

$minus_icon = $OUTPUT->pix_icon('t/switch_minus', get_string('hide'));
$plus_icon = $OUTPUT->pix_icon('t/switch_plus', get_string('show'));

$inconspicuous_icon = $OUTPUT->pix_icon('t/show', get_string('show'));
$visible_icon = $OUTPUT->pix_icon('t/hide', get_string('hide'));

$favon_icon = $OUTPUT->pix_icon('t/add', 'Add to favorites');
$favoff_icon = $OUTPUT->pix_icon('t/less', 'Remove from favorites');

$categories = array();
if(!empty($CFG->block_my_courses_enablefavorites)) {
    $favorites = get_my_favorites();
    $categories = array_merge($favorites,$categories);
}

if(!empty($CFG->block_my_courses_enablelastviewed)) {
    $lastviewed = get_last_viewed();
    $categories = array_merge($lastviewed,$categories);
}

$categories = array_merge($categories, get_category_tree());

if (empty($categories)) {
    echo "You are not enrolled in any courses.";
    die();
}

$categories = sort_my_categories($categories);
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

    $hide = $move = '';
    $hidden_switch = '';

    if ($editing) {
        $move = html_writer::tag('span', $OUTPUT->pix_icon('i/move_2d', 'Drag and Drop to sort') . " ", array(
            'class' => "handle",
        ));
        $hidden_switch = !empty($category->meta->hide) ? 'inconspicuous' : 'visible';
        $switch_icon = ${$hidden_switch . '_icon'};

        $url = new moodle_url('/blocks/my_courses/visible.php', $params);
        $hide = html_writer::link($url, $switch_icon, array(
            'class' => "item_visibility $hidden_switch",
        ));
        $hide .= ' ';
    }

    $html .= "<li class='my_courses_category $hidden_switch'>$move$anchor {$category->name} $hide";
    $html .= '<ul class="my_courses_list ' . $hidden_switch . ' ' . $collapsed_css . ($category->id === 'favs' || $category->id === 'lastviewed' ? '' : $sortable_css) . '">';
    foreach ($category->courses as $course) {
        if (!$editing && $course->meta->hide) {
            continue;
        }

        $class = !$course->visible ? 'dimmed' : '';

        $hide = $fav = $hidden_switch = $fav_switch = '';
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

            $url = new moodle_url('/blocks/my_courses/favorite.php', array(
                'userid' => $USER->id,
                'courseid' => $course->id,
            ));

            if (!empty($CFG->block_my_courses_enablefavorites)) {
                $fav_switch = empty($course->meta->fav) ? 'favon' : 'favoff';
                $switch_icon = ${$fav_switch . '_icon'};

                $fav = html_writer::link($url, $switch_icon, array(
                    'class' => "item_favorite $fav_switch",
                ));
            }

            if ($category->id === 'lastviewed') {
                $hide = $fav = '';
                $hidden_switch = 'excluded_courses';
            }

            if ($category->id === 'favs') {
                $hide = '';
                $hidden_switch = 'excluded_courses';
            }
        }

        $url = new moodle_url('/course/view.php', array('id' => $course->id));
        $anchor = html_writer::link($url, $course->fullname, array('class' => $class));
        $move = $category->id === "lastviewed" || $category->id === "favs" ? "" : $move;
        $content = "$move$course_icon $anchor $hide $fav";
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
      )) .
      html_writer::tag('span', $favon_icon, array(
          'id' => 'my_courses_favon',
          'style' => 'display: none;',
      )) .
      html_writer::tag('span', $favoff_icon, array(
          'id' => 'my_courses_favoff',
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
            $course->meta = (object) array('hide' => 0, 'fav' => 0);
        }

        $categories[$course->category]->courses[$course->id] = $course;
    }

    return $categories;
}

function sort_my_categories($categories) {
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
 * Helper method to pull get last courses viewed
 *
 * @return array
 */
function get_last_viewed() {
    global $CFG,$DB,$USER;

    $category_meta = get_meta_for('category');
    $course_meta = get_meta_for('course');

    if ($CFG->version < 2014051200) { // Moodle < 2.7
        $sql = "SELECT * FROM {log} a INNER JOIN (SELECT c.*,course, MAX(time) as time FROM {log} l JOIN {course} c ON c.id=l.course WHERE userid='$USER->id' AND course != 1 AND module='course' GROUP BY course) b ON a.course = b.course AND a.time = b.time GROUP BY a.course ORDER BY b.time DESC LIMIT $CFG->block_my_courses_lastviewedamount";
    } else { // Moodle 2.7+
        $sql = "SELECT * FROM {logstore_standard_log} a INNER JOIN (SELECT c.*,l.courseid, MAX(l.timecreated) as time FROM {logstore_standard_log} l JOIN {course} c ON c.id=l.courseid WHERE l.userid='$USER->id' AND l.courseid != 1 AND l.target='course' GROUP BY l.courseid) b ON a.courseid = b.courseid AND a.timecreated = b.time GROUP BY a.courseid ORDER BY b.time DESC LIMIT $CFG->block_my_courses_lastviewedamount";
    }

    $courses = $DB->get_records_sql($sql);

    $categories = array();
    foreach ($courses as $course) {
        if (!isset($categories["lastviewed"])) {
            $params = array('id' => "lastviewed");
            $category =  new stdClass();
            $category->name = "Last $CFG->block_my_courses_lastviewedamount Viewed";
            $category->id = "lastviewed";
            $category->courses = array();

            if (isset($category_meta["lastviewed"])) {
                $category->meta = $category_meta["lastviewed"];
            } else {
                $category->meta = (object) array('hide' => 0, 'sortorder' => 1);
            }

            $categories["lastviewed"] = $category;
        }

        if (isset($course_meta[$course->id])) {
            $meta = $course_meta[$course->id];
            unset($meta->sortorder);
            $course->meta = $meta;
        } else {
            $course->meta = (object) array('hide' => 0);
        }

        $categories["lastviewed"]->courses[$course->id] = $course;
    }

    return $categories;
}

/**
 * Helper method to pull get the users favorite courses
 *
 * @return array
 */
function get_my_favorites() {
    global $CFG,$DB,$USER;

    $category_meta = get_meta_for('category');
    $course_meta = get_meta_for('course');
    $sql = "SELECT * FROM {course} c WHERE c.id IN (SELECT itemid FROM {block_my_courses_meta} WHERE userid = :userid AND fav = 1) ORDER BY c.fullname";
    $courses = $DB->get_records_sql($sql, array('userid' => $USER->id));

    $categories = array();
    foreach ($courses as $course) {
        if (!isset($categories["favs"])) {
            $params = array('id' => "favs");
            $category =  new stdClass();
            $category->name = "Favorites";
            $category->id = "favs";
            $category->courses = array();

            if (isset($category_meta["favs"])) {
                $category->meta = $category_meta["favs"];
            } else {
                $category->meta = (object) array('hide' => 0, 'sortorder' => 0);
            }

            $categories["favs"] = $category;
        }

        if (isset($course_meta[$course->id])) {
            $meta = $course_meta[$course->id];
            unset($meta->sortorder);
            $course->meta = $meta;
        } else {
            $course->meta = (object) array('hide' => 0);
        }

        $categories["favs"]->courses[$course->id] = $course;
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

    $sql = "SELECT itemid AS id, hide, sortorder, fav FROM {block_my_courses_meta} "
         . "WHERE userid = :userid "
         . "AND item = :item";

    return $DB->get_records_sql($sql, array(
        'item' => $item,
        'userid' => $USER->id,
    ));
}
