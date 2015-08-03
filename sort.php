<?php

require_once '../../config.php';

if (!isloggedin()) {
    die();
}

$type = required_param('type', PARAM_TEXT);
$userid = required_param('userid', PARAM_INT);
$ids = required_param('ids', PARAM_TEXT);
$sortorder = required_param('sortorder', PARAM_TEXT);

$ids = explode(',', $ids);
$sortorder = explode(',', $sortorder);

if (count($ids) != count($sortorder)) {
    die();
}

$sql = 'SELECT itemid, id, item, sortorder, hide '
     . 'FROM {block_my_courses_meta} '
     . 'WHERE item = :type '
     . 'AND userid = :userid '
     . 'AND itemid IN';

$sqlIds = array();
foreach ($ids as $index => $id) {
  $sqlIds["key$index"] = $id;
}

$sql .= '(:' . implode(',:', array_keys($sqlIds)) . ')';

$entries = $DB->get_records_sql($sql, $sqlIds + array(
    'userid' => $userid,
    'type' => $type,
));

foreach ($ids as $index => $id) {
    if (isset($entries[$id])) {
        $entry = $entries[$id];
    } else {
        $entry = new stdClass;
        $entry->hide = 0;
        $entry->item = $type;
    }

    $entry->userid = $userid;
    $entry->itemid = $id;
    $entry->sortorder = $sortorder[$index];

    if (isset($entry->id)) {
        $DB->update_record('block_my_courses_meta', $entry);
    } else {
        $DB->insert_record('block_my_courses_meta', $entry);
    }
}
