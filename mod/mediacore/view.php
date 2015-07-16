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
 * MediaCore mod video resource
 *
 * @package    mod_mediacore
 * @category   mod
 * @copyright  2015 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once 'lib.php';

$id = required_param('id', PARAM_INT);    // Course Module ID


// Retrieve module instance.
if (empty($id)) {
    print_error('invalidid', 'mediacore_resource');
    return;
}

if (!$cm = get_coursemodule_from_id('mediacore_resource', $id)) {
    // NOTE this is invalid use of print_error, must be a lang string id
    print_error('Course Module ID was incorrect');
}

if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');  // NOTE As above
}

if (!$mediacore = $DB->get_record('mediacore_resource', array('id'=> $cm->instance))) {
    print_error('course module is incorrect'); // NOTE As above
}

require_course_login($course->id, true, $cm);

global $SESSION, $CFG;

$PAGE->set_url('/mod/mediacore/view.php', array('id' => $id));
$PAGE->set_title(format_string($mediacore->name));
$PAGE->set_heading($course->fullname);
$pageclass = 'mediacore-video-resource-body';
$PAGE->add_body_class($pageclass);

$context = $PAGE->context;

add_to_log($course->id, 'mediacore_resource', 'view video resource',
    'view.php?id='.$cm->id, $mediacore->id, $cm->id
);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

echo $OUTPUT->header();

$description = format_module_intro('mediacore_resource', $mediacore, $cm->id);
if (!empty($description)) {
    echo $OUTPUT->box_start('generalbox');
    echo $description;
    echo $OUTPUT->box_end();
}

$renderer = $PAGE->get_renderer('mod_mediacore');

// Require a YUI module to make the object tag be as large as possible.
//$params = array(
    //'bodyclass' => $pageclass,
    //'lastheight' => null,
    //'padding' => 15
//);
//$PAGE->requires->yui_module('moodle-local_kaltura-lticontainer', 'M.local_kaltura.init', array($params), null, true);

echo $renderer->display_iframe($mediacore, $course->id);

die('view.php');
echo $OUTPUT->footer();
