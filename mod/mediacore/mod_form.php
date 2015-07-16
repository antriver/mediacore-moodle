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

defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once $CFG->dirroot .'/course/moodleform_mod.php';
require_once $CFG->dirroot . '/local/mediacore/lib.php';


class mod_mediacore_mod_form extends moodleform_mod {

    /**
     */
    public function definition() {
        global $CFG, $DB, $OUTPUT, $PAGE, $COURSE;

        $client = new mediacore_client();

        // CSS
        $PAGE->requires->css('/mod/mediacore/styles.css');
        $class = 'mediacore-resource';
        $PAGE->add_body_class($class);

        // JS
        $variables = $client->get_texteditor_params();
        $PAGE->requires->data_for_js('mcore_vars', $variables);
        $PAGE->requires->js(new moodle_url($variables['mcore_chooser_js_url']));
        $module = array(
            'name'      => 'mod_mediacore',
            'fullpath'  => '/mod/mediacore/main.js',
            'requires'  => array('yui2-event'),
        );
        $PAGE->requires->js_init_call(
            'M.mod_mediacore.init',
            /* args */ null,
            /* domready */ true,
            /* js specs */ $module
        );

        // Form
        $mform =& $this->_form;
        $mform->addElement('header', 'mcore-general', 'General');
        $this->add_form_fields($mform);
        $this->add_media_btn($mform);
        $this->add_hidden_fields($mform);
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     */
    public function add_form_fields($mform) {
        //
        // Name
        $mform->addElement('text', 'name', 'Name:');
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
    public function add_media_btn($mform) {
        $is_new = true; //TODO

        $thumb_html = $this->_get_default_thumb($is_new);
        $iframe_html = $this->_get_preview_iframe($is_new);
        $html = '<div class="fitem fitem_ftext">' .
                '<div class="felement ftext">' .
                $thumb_html . $iframe_html .
                '</div>' .
                '</div>';
        $mform->addElement('html', $html);

        $mediagroup = array();
        $attr = array('id' => 'mcore-add-media-btn');
        $add_btn_text = ($is_new) ? 'Add Media' : 'Replace Media';
        $mediagroup[] =& $mform->createElement(
            'button', 'mcore-add-media-btn', $add_btn_text, 'mediacore', '', $attr
        );

        $mform->addGroup($mediagroup, 'media_group', '&nbsp;', '&nbsp;', false);
    }

    /**
     */
    private function _get_default_thumb($is_new) {
        $src = new moodle_url('/mod/mediacore/pix/generic-thumb.png');
        $alt    = 'Add Media';
        $title  = 'Add Media';
        $style = ($is_new) ? 'display:block' : 'display:none';
        $attr = array(
            'id' => 'mcore-media-thumb',
            'src' => $src->out(),
            'alt' => $alt,
            'title' => $title,
            'width' => '400',
            'height' => '225',
            'style' => $style,
        );
        return html_writer::empty_tag('img', $attr);
    }

    /**
     */
    private function _get_preview_iframe($is_new) {

        $src = new moodle_url(''); //TODO
        $style = (!$is_new) ? 'display:block' : 'display:none';

        $params = array(
            'id' => 'mcore-media-iframe',
            'src' => $src->out(false),
            'height' => '400',
            'width' => '225',
            'allowfullscreen' => 'true',
            'webkitallowfullscreen' => 'true',
            'mozallowfullscreen' => 'true',
            'style' => $style,
        );

        return html_writer::tag('iframe', '', $params);
    }

    /**
     * TODO
     */
    public function add_hidden_fields($mform) {
        $attr = array('id' => 'mcore-media-id');
        $mform->addElement('hidden', 'media_id', '', $attr);
        $mform->setType('media_id', PARAM_TEXT);

        $attr = array('id' => 'mcore-public-url');
        $mform->addElement('hidden', 'public_url', '', $attr);
        $mform->setType('public_url', PARAM_URL);

        $attr = array('id' => 'mcore-thumb-url');
        $mform->addElement('hidden', 'thumb_url', '', $attr);
        $mform->setType('thumb_url', PARAM_URL);

        $attr = array('id' => 'mcore-metadata');
        $mform->addElement('hidden', 'metadata', '', $attr);
        $mform->setType('metadata', PARAM_TEXT);
    }

    /**
     * Validates the form
     * TODO
     *
     * @param array $data Array of form values
     * @param array $files Array of files
     * @return array $errors Array of error messages
     */
    public function validation($data, $files) {
        $errors = array();

        //if (empty($data['src'])) {
            //$errors['add_video_thumb'] = 'No media thumb';
        //}

        return $errors;
    }
}
