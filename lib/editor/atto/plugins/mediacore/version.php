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
 * Strings for component 'atto_mediacore', language 'en'.
 *
 * @package    atto_mediacore
 * @copyright  2014 MediaCore <info@mediacore.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component          = 'atto_mediacore';
$plugin->version            = 2014072900;
$plugin->requires           = 2014041100; //Moodle 2.7
$plugin->release            = '1.0';
$plugin->maturity           = MATURITY_STABLE;
$plugin->dependencies       = array(
    'local_mediacore' => 2014061800,
    'filter_mediacore' => 2014061800,
);
