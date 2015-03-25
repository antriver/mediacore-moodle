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
require_once $CFG->dirroot . '/local/mediacore/lib.php';
require_once 'mediacore_client.class.php';


/**
 * Plugin for MediaCore media
 *
 * This module is used to configure the MediaCore button in TinyMCE for
 * Moodle 2.4+
 *
 * Moodle 2.3 uses a different method to configure the plugin. See
 * MediaCore plugin installation documentation for details.
 *
 * @package tinymce_mediacore
 * @copyright 2012 MediaCore Technologies Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tinymce_mediacore extends editor_tinymce_plugin
{
    /** @var array list of buttons defined by this plugin */
    protected $buttons = array('mediacore');

    protected function update_init_params(array &$params, context $context,
        array $options = null) {

        // 'mediacore' is the key used in Moodle >= 2.5
        // 'filter/mediacore' is the key used in Moodle < 2.5
        $filters = filter_get_active_in_context($context);
        $enabled  = array_key_exists('mediacore', $filters)
                 || array_key_exists('filter/mediacore', $filters);

        // If mediacore filter is disabled, do not add button.
        if (!$enabled) {
            return;
        }

        $mcore_client = new mediacore_client();
        $params = $params + $mcore_client->get_texteditor_params();
        $numrows = $this->count_button_rows($params);
        $this->add_button_after($params, $numrows, '|,mediacore');

        // Add JS file, which uses default name.
        $this->add_js_plugin($params);
    }

    /**
     * Counts the number of rows in TinyMCE editor (row numbering starts with 1)
     * Re-implementation of {@link lib/editor/tinymce/classes/plugin.php} in
     * Moodle v2.6+
     *
     * @override
     * @param array $params TinyMCE init parameters array
     * @return int the maximum existing row number
     */
    protected function count_button_rows(array &$params) {
        $maxrow = 1;
        foreach ($params as $key => $value) {
            if (preg_match('/^theme_advanced_buttons(\d+)$/', $key, $matches) &&
                    (int)$matches[1] > $maxrow) {
                $maxrow = (int)$matches[1];
            }
        }
        return $maxrow;
    }
}
