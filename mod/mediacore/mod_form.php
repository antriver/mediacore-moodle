<?php
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
 * Kaltura video resource formslib class.
 *
 * @package    mod_kalvidres
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2014 Remote Learner.net Inc http://www.remote-learner.net
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

global $CFG;
require_once $CFG->dirroot . '/local/mediacore/lib.php';


class mod_mediacore_mod_form extends moodleform_mod {

    protected $add_media_btn = 'add_media';

    public function definition() {
        global $CFG, $COURSE, $PAGE;

        $PAGE->requires->css('/mod/mediacore/styles.css');
        $PAGE->requires->js('mod/mediacore/main.js');



    }
}
