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
 * MediaCore's tinymce plugin
 *
 * @package    local
 * @subpackage tinymce_mediacore
 * @copyright  2012 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die('Invalid access');
global $CFG;
require_once($CFG->dirroot . '/local/mediacore/lib.php');

/**
 * Plugin for MediaCore media
 *
 * @package tinymce_mediacore
 * @copyright 2012 MediaCore Technologies Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tinymce_mediacore extends editor_tinymce_plugin {

    /** @var array list of buttons defined by this plugin */
    protected $buttons = array('mediacore');

    protected function update_init_params(array &$params, context $context,
        array $options = null) {

        global $CFG, $COURSE;

        // If mediacore filter is disabled, do not add button.
        // Note: Test for "filter/mediacore" to remain compatible with Moodle < 2.5.
        $filters = filter_get_active_in_context($context);
        if (!array_key_exists('mediacore', $filters)
            && !array_key_exists('filter/mediacore', $filters)) {
            return;
        }

        $mcore_client = new mediacore_client();
        $params = $params + $mcore_client->get_tinymce_params();

        // Add button after images button.
        if ($row = $this->find_image_button($params)) {
            $this->add_button_after($params, $row, 'mediacore', 'image');
        } else {
            // If 'image' is not found, add button in the end of the last row.
            $this->add_button_after($params, $this->count_button_rows($params), 'mediacore');
        }

        // Add JS file, which uses default name.
        $this->add_js_plugin($params);
    }

    /**
     * Custom implementation of $this->find_button(array $params, $button
     *  from Moodle v2.6
     * @param array $params TinyMCE init parameters array
     * @return int the image row if exists, lower number if does not exist.
     */
    protected function find_image_button(array &$params) {
        foreach ($params as $key => $value) {
            if (preg_match('/^theme_advanced_buttons(\d+)$/', $key, $matches) &&
                    strpos(','. $value. ',', ',image,') !== false) {
                return (int)$matches[1];
            }
        }
        return false;
    }
}
