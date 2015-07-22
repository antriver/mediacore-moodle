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
 * @package    mediacoreresource
 * @category   mod
 * @copyright  2015 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once realpath(dirname(__FILE__) .'/../../../moodle') . '/config.php';
defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once 'lib.php';

$courseid = required_param('id', PARAM_INT);
if (empty($courseid)) {
    print_error('invalidid', 'mediacoreresource');
    return;
}
if (!$coursemod = get_coursemodule_from_id('mediacoreresource', $courseid)) {
    //TODO i18n
    print_error('Course Module ID was incorrect');
    return;
}
if (!$course = $DB->get_record('course', array('id'=> $coursemod->course))) {
    //TODO i18n
    print_error('course is misconfigured');
    return;
}

if (!$mediacoreresource = $DB->get_record('mediacoreresource', array('id'=> $coursemod->instance))) {
    //TODO i18n
    print_error('course module is incorrect');
    return;
}

require_course_login($course->id, true, $coursemod);

$PAGE->set_url('/mod/mediacoreresource/view.php', array('id' => $courseid));
$PAGE->set_title(format_string($mediacoreresource->name));
$PAGE->set_heading($course->fullname);

$PAGE->requires->css('/mod/mediacoreresource/styles.css');
$class = 'mediacore-resource-view';
$PAGE->add_body_class($class);

$completion = new completion_info($course);
$completion->set_module_viewed($coursemod);

$renderer = $PAGE->get_renderer('mod_mediacoreresource');

echo $OUTPUT->header();

// Title
echo $renderer->display_name($mediacoreresource->name);

// iframe
echo $renderer->display_responsive_iframe($mediacoreresource, $course->id);

// Description
$description = format_module_intro('mediacoreresource', $mediacoreresource, $coursemod->id);
if (!empty($description)) {
    echo $OUTPUT->box_start('mcore-media-description');
    echo $description;
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();
