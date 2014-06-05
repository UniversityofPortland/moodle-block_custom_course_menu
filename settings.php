<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_my_courses_enablelastviewed', get_string('enablelastviewed', 'block_my_courses'),
                       get_string('configenablelastviewed', 'block_my_courses'), 0));
    $settings->add(new admin_setting_configtext('block_my_courses_lastviewedamount', get_string('lastviewedamount', 'block_my_courses'),
                       get_string('configlastviewedamount', 'block_my_courses'), 5, PARAM_INT));
}