<?php

require_once '../../config.php';

if (!isloggedin()) {
    die();
}

$userid = required_param('userid', PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);

if (empty($courseid) && empty($categoryid)) {
    die();
}

if ($categoryid) {
    $itemid = $categoryid;
    $item = 'category';
} else {
    $itemid = $courseid;
    $item = 'course';
}

$params = array(
    'userid' => $userid,
    'item' => $item,
    'itemid' => $itemid,
);

$entry = $DB->get_record('block_my_courses_meta', $params);

if ($entry) {
  $entry->hide = $entry->hide ? 0 : 1;
  $DB->update_record('block_my_courses_meta', $entry);
} else {
  $entry = (object) $params;
  $entry->hide = 1;

  $DB->insert_record('block_my_courses_meta', $entry);
}
