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
require_once $CFG->dirroot .'/course/moodleform_mod.php';
require_once $CFG->dirroot . '/local/mediacore/lib.php';


class mod_mediacoreresource_mod_form extends moodleform_mod {

    /**
     */
    public function definition() {
        global $CFG, $DB, $OUTPUT, $PAGE, $COURSE;

        $client = new mediacore_client();

        // CSS
        $PAGE->requires->css('/mod/mediacoreresource/styles.css');
        $class = 'mediacore-resource-add-update';
        $PAGE->add_body_class($class);

        // JS
        $params = $client->get_texteditor_params();
        $PAGE->requires->data_for_js('mcore_params', $params);
        $module = array(
            'name'      => 'mediacoreresource',
            'fullpath'  => '/mod/mediacoreresource/main.js',
            'requires'  => array('yui2-event'),
        );
        $PAGE->requires->js_init_call(
            'M.mod_mediacoreresource.init',
            /* args */ null,
            /* domready */ true,
            /* js specs */ $module
        );

        // Check if new or update
        $is_new = !isset($this->current->update);

        // Form
        $mform =& $this->_form;
        $mform->addElement('header', 'mcore-general',
            get_string('headertitle', 'mediacoreresource'));
        $this->add_form_fields($mform, $is_new);
        $this->add_media_btn($mform, $is_new);
        $this->add_hidden_fields($mform, $is_new);
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     */
    public function add_form_fields($mform, $is_new) {
        //
        // Name
        $mform->addElement('text', 'name',
            get_string('name', 'mediacoreresource'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        // Description
        $this->add_intro_editor(false);
    }

    /**
     */
    public function add_media_btn($mform, $is_new) {

        $iframe_html = $this->_get_preview_iframe($is_new);
        $mform->addElement(
            'static', 'mcore-media-iframe',
            get_string('mediapreview', 'mediacoreresource'),
            $iframe_html
        );

        $btngroup = array();
        $attr = array('id' => 'mcore-add-media-btn');
        $add_btn_text = ($is_new)
            ? get_string('addmedia', 'mediacoreresource')
            : get_string('replacemedia', 'mediacoreresource');
        $btngroup[] =& $mform->createElement(
            'button', 'mcore-add-media-btn', $add_btn_text,
            'mediacoreresource_add', '', $attr
        );

        $mform->addGroup($btngroup, 'media_group', '&nbsp;', '&nbsp;', false);
    }

    /**
     */
    private function _get_preview_iframe($is_new) {

        if ($is_new) {
            $url = '/mod/mediacoreresource/pix/generic-thumb.png';
        } else {
            $url = $this->current->embed_url;
        }
        $url = new moodle_url($url);

        $iframe_attrs = array(
            'id' => 'mcore-media-iframe',
            'src' => $url->out(false),
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

    /**
     */
    public function add_hidden_fields($mform) {
        $attr = array('id' => 'mcore-media-id');
        $mform->addElement('hidden', 'media_id', '', $attr);
        $mform->setType('media_id', PARAM_TEXT);

        $attr = array('id' => 'mcore-embed-url');
        $mform->addElement('hidden', 'embed_url', '', $attr);
        $mform->setType('embed_url', PARAM_URL);

        $attr = array('id' => 'mcore-thumb-url');
        $mform->addElement('hidden', 'thumb_url', '', $attr);
        $mform->setType('thumb_url', PARAM_URL);

        $attr = array('id' => 'mcore-metadata');
        $mform->addElement('hidden', 'metadata', '', $attr);
        $mform->setType('metadata', PARAM_TEXT);
    }

    /**
     * Validate the form
     *
     * @param array $data
     * @param array $files
     * @return array $errors
     */
    public function validation($data, $files) {
        $errors = array();
        if (empty($data['media_id'])) {
            $errors['name'] =
                get_string('noattachedmedia', 'mediacoreresource');
        }
        return $errors;
    }
}
