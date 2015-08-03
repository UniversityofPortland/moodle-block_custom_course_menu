<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_my_courses_enablefavorites', get_string('enablefavorites', 'block_my_courses'),
                       get_string('configenablefavorites', 'block_my_courses'), 0));
    $settings->add(new admin_setting_configcheckbox('block_my_courses_enablelastviewed', get_string('enablelastviewed', 'block_my_courses'),
                       get_string('configenablelastviewed', 'block_my_courses'), 0));
    $settings->add(new admin_setting_configtext('block_my_courses_lastviewedamount', get_string('lastviewedamount', 'block_my_courses'),
                       get_string('configlastviewedamount', 'block_my_courses'), 5, PARAM_INT));
//    $settings->add(new admin_setting_configcheckbox('block_my_courses_showsearch', get_string('showsearch', 'block_my_courses'),
//                       get_string('configshowsearch', 'block_my_courses'), 0));
    $options = array(
        0 => get_string('no'),
        1 => get_string('yes'),
        'admin' => get_string('adminonly', 'block_my_courses')
    );
    $name = new lang_string('attemptreopenmethod', 'mod_assign');
    $description = new lang_string('attemptreopenmethod_help', 'mod_assign');
    $settings->add(new admin_setting_configselect('block_my_courses_showsearch',
                                                    get_string('showsearch', 'block_my_courses'),
                                                     get_string('configshowsearch', 'block_my_courses'),
                                                    0,
                                                    $options));
}