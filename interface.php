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
 * CustomCourseMenu Block Helper - Interface
 *
 * @package    block_custom_course_menu
 * @copyright  2015 onwards University of Portland (www.up.edu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

require_sesskey();
if (!isloggedin()) {
    die();
    require_login(); // Just to pass the codechecker.
}

$PAGE->set_context(context_system::instance());

$editing = optional_param("editing", 0, PARAM_INT);

$minusicon = '<i class="fa fa-minus-square"></i>'; // Hidden course category icon.
$plusicon = '<i class="fa fa-plus-square"></i>'; // Visible course category icon.

$inconspicuousicon = '<i class="fa fa-eye-slash"></i>'; // Hidden course icon.
$visibleicon = '<i class="fa fa-eye"></i>'; // Visible course icon.

$favonicon = '<i class="fa fa-plus"></i>'; // Add to favorites icon.
$favofficon = '<i class="fa fa-minus"></i>'; // Remove from favorites icon.

$categories = array();
$configs = get_config('block_custom_course_menu');
if (!empty($configs->enablefavorites)) {
    $favorites = get_my_favorites();
    $categories = array_merge($favorites, $categories);
}
if (!empty($configs->enablelastviewed)) {
    $lastviewed = get_last_viewed();
    $categories = array_merge($lastviewed, $categories);
}

$categories = array_merge($categories, get_category_tree());

if (empty($categories)) {
    echo "You are not enrolled in any courses.";
    die();
}

$categories = sort_my_categories($categories);
$sortablecss = $editing ? ' course-sortable' : '';

$html = '<ul class="custom_course_menu_category_list' . $sortablecss . '">';

foreach ($categories as $category) {
    if (!$editing && $category->meta->hide) {
        continue;
    }

    $params = array(
        'userid' => $USER->id,
        'categoryid' => $category->id,
    );

    $collapsed = $DB->get_field('block_custom_course_menu', 'collapsed', $params);
    $collapsedcss = 'collapsed';

    if (!$collapsed) {
        $collapsedcss = 'not_collapsed';
    }

    $switch = $collapsed ? 'plus' : 'minus';
    $switchicon = ${$switch . 'icon'};

    $url = new moodle_url('/blocks/custom_course_menu/toggle.php', $params);
    $anchor = html_writer::link($url, $switchicon, array(
        'class' => "category_switcher $switch",
    ));

    $hide = $move = $hiddenswitch = '';

    if ($editing) {
        $move = html_writer::tag('span', '<i class="fa fa-arrows"></i> ', array(
            'class' => "handle",
        ));
        $hiddenswitch = !empty($category->meta->hide) ? 'inconspicuous' : 'visible';
        $switchicon = ${$hiddenswitch . 'icon'};

        $url = new moodle_url('/blocks/custom_course_menu/visible.php', $params);
        $hide = html_writer::link($url, $switchicon, array(
            'class' => "item_visibility $hiddenswitch",
        ));
        $hide .= ' ';
    }

    $html .= "<li class='custom_course_menu_category $hiddenswitch'>$move$anchor {$category->name} $hide";
    $html .= '<ul class="custom_course_menu_list ' . $hiddenswitch . ' ' . $collapsedcss . ($category->id === -1 ||
                                                     $category->id === -2 ? '' : $sortablecss) . '">';
    foreach ($category->courses as $course) {
        if (!$editing && $course->meta->hide) {
            continue;
        }

        $class = !$course->visible ? 'dimmed' : '';

        $hide = $fav = $hiddenswitch = $favswitch = '';
        if ($editing) {
            $url = new moodle_url('/blocks/custom_course_menu/visible.php', array(
                'userid' => $USER->id,
                'courseid' => $course->id,
            ));

            $hiddenswitch = !empty($course->meta->hide) ? 'inconspicuous' : 'visible';
            $switchicon = ${$hiddenswitch . 'icon'};

            $hide = html_writer::link($url, $switchicon, array(
                'class' => "item_visibility $hiddenswitch",
            ));
            $hide .= ' ';

            $url = new moodle_url('/blocks/custom_course_menu/favorite.php', array(
                'userid' => $USER->id,
                'courseid' => $course->id,
            ));

            if (!empty($configs->enablefavorites)) {
                $favswitch = empty($course->meta->fav) ? 'favon' : 'favoff';
                $switchicon = ${$favswitch . 'icon'};

                $fav = html_writer::link($url, $switchicon, array(
                    'class' => "item_favorite $favswitch",
                ));
            }

            if ($category->id === -1) {
                $hide = '';
                $hiddenswitch = 'excluded_courses';
            }

            if ($category->id === -2) {
                $hide = $fav = '';
                $hiddenswitch = 'excluded_courses';
            }
        }

        $url = new moodle_url('/course/view.php', array('id' => $course->id));
        $anchor = html_writer::link($url, $course->fullname, array('class' => $class));
        $move = $category->id === -1 || $category->id === -2 ? "" : $move;
        $content = $move.$anchor." ".$hide.$fav;
        $html .= html_writer::tag('li', $content, array(
            'class' => "custom_course_menu_course $hiddenswitch",
        ));
    }

    $html .= '</ul>';
    $html .= '</li>';
}

$url = new moodle_url('/blocks/custom_course_menu/sort.php', array(
    'userid' => $USER->id,
));

$html .= '</ul>'
      .
      html_writer::tag('span', $plusicon, array(
          'id' => 'custom_course_menu_plus',
          'style' => 'display: none;',
      )) .
      html_writer::tag('span', $minusicon, array(
          'id' => 'custom_course_menu_minus',
          'style' => 'display: none;',
      )) .
      html_writer::tag('span', $visibleicon, array(
          'id' => 'custom_course_menu_visible',
          'style' => 'display: none;',
      )) .
      html_writer::tag('span', $inconspicuousicon, array(
          'id' => 'custom_course_menu_inconspicuous',
          'style' => 'display: none;',
      )) .
      html_writer::tag('span', $url->out(), array(
          'id' => 'custom_course_menu_sort',
          'style' => 'display: none;',
      )) .
      html_writer::tag('span', $favonicon, array(
          'id' => 'custom_course_menu_favon',
          'style' => 'display: none;',
      )) .
      html_writer::tag('span', $favofficon, array(
          'id' => 'custom_course_menu_favoff',
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

    $categorymeta = get_meta_for('category');
    $coursemeta = get_meta_for('course');

    $courses = enrol_get_my_courses();

    $categories = array();
    foreach ($courses as $course) {
        if (!isset($categories[$course->category])) {
            $params = array('id' => $course->category);
            $category = $DB->get_record('course_categories', $params);
            $category->courses = array();

            if (isset($categorymeta[$category->id])) {
                $category->meta = $categorymeta[$category->id];
            } else {
                $category->meta = (object) array('hide' => 0);
            }

            $categories[$course->category] = $category;
        }

        if (isset($coursemeta[$course->id])) {
            $course->meta = $coursemeta[$course->id];
        } else {
            $course->meta = (object) array('hide' => 0, 'fav' => 0);
        }

        $categories[$course->category]->courses[$course->id] = $course;
    }

    return $categories;
}

/**
 * Sorts categories
 *
 * @param array $categories
 * @return array
 */
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
    global $CFG, $DB, $USER;
    $categorymeta = get_meta_for('category');
    $lva = get_config('block_custom_course_menu')->lastviewedamount;
    if ($CFG->version < 2014051200) { // Moodle < 2.7.
        $sql = "SELECT * FROM {log} a INNER JOIN (SELECT c.*,course, MAX(time) as time FROM {log} l JOIN {course} c ON
                c.id=l.course WHERE userid='$USER->id' AND course != 1 AND module='course' GROUP BY course) b ON
                a.course = b.course AND a.time = b.time GROUP BY a.course ORDER BY b.time DESC LIMIT $lva)";
    } else { // Moodle 2.7+.
        $sql = "SELECT a.courseid, max(a.timecreated) as date, a.userid FROM (SELECT * FROM {logstore_standard_log}
				WHERE courseid !=0 and courseid !=1) as a WHERE a.userid='$USER->id' GROUP BY a.userid,a.courseid ORDER BY date
				DESC LIMIT $lva";
    }

    $latestcourses = $DB->get_records_sql($sql);

    $categories = array();
    $order = 1;
    foreach ($latestcourses as $latest) {
        if ($course = $DB->get_record('course', array('id' => $latest->courseid))) {
            if (!isset($categories[-2])) {
                $category = new stdClass();
                $category->name = "Last $lva Viewed";
                $category->id = -2;
                $category->courses = array();

                if (isset($categorymeta[-2])) {
                    $category->meta = $categorymeta[-2];
                } else {
                    $category->meta = (object) array('hide' => 0, 'sortorder' => 1);
                }

                $categories[-2] = $category;
            }
            $course->meta = (object) array('hide' => 0, 'sortorder' => $order);
            $order++;
            $categories[-2]->courses[$course->id] = $course;
        }
    }
    return $categories;
}

/**
 * Helper method to pull get the users favorite courses
 *
 * @return array
 */
function get_my_favorites() {
    global $DB, $USER;
    $categorymeta = get_meta_for('category');
    $coursemeta = get_meta_for('course');
    $sql = "SELECT * FROM {course} c WHERE c.id IN (SELECT itemid FROM {block_custom_course_menu_etc} WHERE userid = :userid
            AND fav = 1) ORDER BY c.fullname";
    $courses = $DB->get_records_sql($sql, array('userid' => $USER->id));
    $categories = array();
    foreach ($courses as $course) {
        if (!isset($categories[-1])) {
            $category = new stdClass();
            $category->name = "Favorites";
            $category->id = -1;
            $category->courses = array();

            if (isset($categorymeta[-1])) {
                $category->meta = $categorymeta[-1];
            } else {
                $category->meta = (object) array('hide' => 0, 'sortorder' => 0);
            }

            $categories[-1] = $category;
        }

        if (isset($coursemeta[$course->id])) {
            $meta = $coursemeta[$course->id];
            unset($meta->sortorder);
            $course->meta = $meta;
        } else {
            $course->meta = (object) array('hide' => 0);
        }

        $categories[-1]->courses[$course->id] = $course;
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

    $sql = "SELECT itemid AS id, hide, sortorder, fav FROM {block_custom_course_menu_etc} "
         . "WHERE userid = :userid "
         . "AND item = :item";

    return $DB->get_records_sql($sql, array(
        'item' => $item,
        'userid' => $USER->id,
    ));
}
