<?php

require_once '../../config.php';

if (!isloggedin()) {
    die();
}

$catid = required_param('categoryid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

$params = array(
  'userid' => $userid,
  'categoryid' => $catid
);

$entry = $DB->get_record('block_my_courses', $params);

if ($entry) {
    $entry->collapsed = $entry->collapsed ? 0 : 1;

    $DB->update_record('block_my_courses', $entry);
} else {
    $entry = new stdClass;
    $entry->userid = $userid;
    $entry->categoryid = $catid;
    $entry->collapsed = 1;

    $DB->insert_record('block_my_courses', $entry);
}
