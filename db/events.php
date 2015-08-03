<?php

$mapper = function($event) {
    return array(
        'handlerfile' => '/blocks/my_courses/handler.php',
        'handlerfunction' => array('my_courses_handler', $event),
        'schedule' => 'instant',
    );
};

$events = array(
  'course_deleted',
  'course_category_deleted',
  'user_deleted',
);

$handlers = array_combine($events, array_map($mapper, $events));
