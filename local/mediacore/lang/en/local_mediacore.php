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
 * MediaCore's local plugin language strings
 *
 * @package    local
 * @subpackage mediacore
 * @copyright  2012 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Plugin settings.
$string['pluginname'] = 'MediaCore package config';

$string['setting_url_label'] = 'MediaCore URL:';
$string['setting_url_desc'] = '*If your Moodle site is being served over "https", please make sure your MediaCore URL above begins with "https" as well.';

$string['setting_consumer_key_label'] = 'MediaCore Consumer Key';
$string['setting_consumer_key_desc'] = '*This must match an existing <a href="http://support.mediacore.com/customer/portal/articles/869178-what-is-lti-integration-and-how-do-i-set-it-up-" target="_blank">LTI consumer key</a> in your MediaCore site above.';

$string['setting_shared_secret_label'] = 'MediaCore Shared Secret';
$string['setting_shared_secret_desc'] = '*This must match the shared secret from the LTI consumer key in your MediaCore site above.';
