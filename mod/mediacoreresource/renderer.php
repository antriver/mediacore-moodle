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

require_once realpath(dirname(__FILE__) . '/../../local/mediacore') . '/lib.php';

class mod_mediacoreresource_renderer extends plugin_renderer_base {

    /**
     */
    public function display_name($title) {
        $html = html_writer::tag('b', $title);
        $attrs = array('class' => 'mcore-media-name');
        return html_writer::tag('div', $html, $attrs);
    }

    /**
     */
    public function display_responsive_iframe($mediacoreresource, $courseid) {
        $attrs = array('class' => 'mcore-media-iframe-responsive');
        $html = $this->display_iframe($mediacoreresource, $courseid);
        return html_writer::tag('div', $html, $attrs);
    }

    /**
     */
    public function display_iframe($mediacoreresource, $courseid) {
        global $CFG;
        $client = new mediacore_client();

        $embed_url = $mediacoreresource->embed_url;
        if ($client->has_lti_config() && !is_null($courseid)) {
            $site_url = $client->get_siteurl();
            $content_url = $CFG->wwwroot . '/local/mediacore/sign.php';
            $embed_url = str_replace($site_url, $content_url, $embed_url);
        }
        $embed_url = new moodle_url($embed_url);

        $iframe_attrs = array(
            'id' => 'mcore-media-iframe',
            'src' => $embed_url->out(false),
            'width' => '560',
            'height' => '315',
            'allowfullscreen' => 'true',
            'webkitallowfullscreen' => 'true',
            'mozallowfullscreen' => 'true',
            'scrolling' => 'no',
            'frameborder' => '0',
        );
        return html_writer::tag('iframe', '', $iframe_attrs);
    }
}
