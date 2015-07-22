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

defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once $CFG->dirroot . '/local/mediacore/lib.php';

/**
 * This function is passed the variables from the mod_form.php file
 * (discussed later) as an object when you first create an activity and click
 * submit. This is where you can take that data, do what you want with it and
 * then insert it into the database if you wish. This is only called once when
 * the module instance is first created, so this is where you should place the
 * logic to add the activity.
 *
 * @return boolean
 */
function mediacoreresource_add_instance($mediacore) {
    global $DB, $CFG;

    $mediacore->timemodified = time();
    $mediacore->id =  $DB->insert_record('mediacoreresource', $mediacore);

    return $mediacore->id;
}

/**
 * This function is passed the variables from the mod_form.php file as an
 * object whenever you update an activity and click submit. The id of the
 * instance you are editing is passed as the attribute instance and can be used
 * to edit any existing values in the database for that instance.
 *
 * @return boolean
 */
function mediacoreresource_update_instance($mediacore) {
    global $DB, $CFG;

    $mediacore->id = $mediacore->instance;
    $mediacore->timemodified = time();
    $updated = $DB->update_record('mediacoreresource', $mediacore);

    return $updated;
}


/**
 * This function is passed the id of your module which you can use to delete
 * the records from any database tables associated with that id. For example,
 * in the certificate module the id in the certificate table is passed, and
 * then used to delete the certificate from the database, any issues of this
 * certificate and any files associated with it on the filesystem.
 *
 * @return boolean
 */
function mediacoreresource_delete_instance($id) {
    global $DB;

    if (!$mediacore = $DB->get_record('mediacoreresource', array('id' => $id))) {
        return false;
    }
    $DB->delete_records('mediacoreresource', array('id' => $mediacore->id));

    return true;
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc.
 *
 * @return boolean
 */
function mediacoreresource_cron () {
    return false;
}

/**
 * TODO Add doc string
 *
 * @return boolean
 */
function mediacoreresource_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}
