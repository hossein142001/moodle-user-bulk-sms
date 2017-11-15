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
 * @package local_sms
 * @copyright  2016
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG, $PAGE;

if ($hassiteconfig) {

    $settings = new admin_settingpage( 'sms', get_string('pluginname', 'local_sms') );
    $ADMIN->add('localplugins', $settings);

    //settings
    $settings->add(new admin_setting_heading('sms_settings', '', get_string('settings')));

    $settings->add(new admin_setting_configtext('local_sms/userName', get_string('username'), get_string('username'), '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_sms/password', get_string('password'), get_string('password'), '', PARAM_RAW));


    $ADMIN->add('accounts', new admin_externalpage('send_sms', get_string('pluginname', 'local_sms'), "$CFG->wwwroot/local/sms/send_sms.php", 'local/sms:send_message'));
}