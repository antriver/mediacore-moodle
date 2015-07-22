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
 * @package    mediacore
 * @category   mod
 * @copyright  2015 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die('Invalid access');

require_once realpath(dirname(__FILE__) . '/../../local/mediacore') . '/lib.php';

class mod_mediacore_renderer extends plugin_renderer_base {
    /**
     * This function displays the title of the video in bold.
     * @param string $title The title of the video.
     * @return string HTML markup.
     */
    public function display_mod_info($title) {
        $output = '';

        $attr = array('for' => 'mcore-media-iframe');
        $output .= html_writer::tag('b', $title);
        $output .= html_writer::empty_tag('br');

        return $output;
    }

    /**
     * This function displays the iframe markup.
     * @param object $mediacore A Kaltura video resrouce instance object.
     * @param int $courseid A course id.
     * @return string HTML markup.
     */
    public function display_iframe($mediacore, $courseid) {

        $client = new mediacore_client();

        $url = new moodle_url($mediacore->embed_url);
        if ($client->has_lti_config() && !$client->has_trusted_embed_config()) {
            $url = new moodle_url('/lti/mediacore/sign.php');
        }

        $attr = array(
            'id' => 'mcore-media-iframe',
            'width' => '100%',
            'src' => $url->out(false),
            'allowfullscreen' => 'true',
            'webkitallowfullscreen' => 'true',
            'mozallowfullscreen' => 'true',
            'frameborder' => '0',
            'scrolling' => 'no',
            'onload' => '
        );

        $output = html_writer::tag('iframe', '', $attr);
        $output = html_writer::tag('center', $output);
        return $output;
    }
}
