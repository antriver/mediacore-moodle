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
 *       __  _____________   _______   __________  ____  ______
 *      /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
 *     / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/
 *    / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___
 *   /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/
 *
 * MediaCore's local plugin settings
 *
 * @package    local
 * @subpackage mediacore
 * @copyright  2012 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Invalid access');
global $CFG;
require_once($CFG->dirroot. '/local/mediacore/lib.php');

defined('MOODLE_INTERNAL') || die('Invalid access');

if ($hassiteconfig) {

    $settings = new admin_settingpage(MEDIACORE_PLUGIN_NAME, get_string('pluginname',
            MEDIACORE_PLUGIN_NAME));
    $ADMIN->add('localplugins', $settings);

    $setting = new admin_setting_configtext(MEDIACORE_SETTINGS_NAME . '/url',
            get_string('setting_url_label', MEDIACORE_PLUGIN_NAME),
            get_string('setting_url_desc', MEDIACORE_PLUGIN_NAME),
            'http://demo.mediacore.tv', PARAM_TEXT);
    $setting->plugin = MEDIACORE_SETTINGS_NAME;
    $settings->add($setting);

    $setting = new admin_setting_configtext(MEDIACORE_SETTINGS_NAME .'/consumer_key',
            get_string('setting_consumer_key_label', MEDIACORE_PLUGIN_NAME),
            get_string('setting_consumer_key_desc', MEDIACORE_PLUGIN_NAME),
            '', PARAM_TEXT);
    $setting->plugin = MEDIACORE_SETTINGS_NAME;
    $settings->add($setting);

    $setting = new admin_setting_configtext(MEDIACORE_SETTINGS_NAME . '/shared_secret',
            get_string('setting_shared_secret_label', MEDIACORE_PLUGIN_NAME),
            get_string('setting_shared_secret_desc', MEDIACORE_PLUGIN_NAME),
            '', PARAM_TEXT);
    $setting->plugin = MEDIACORE_SETTINGS_NAME;
    $settings->add($setting);

}
