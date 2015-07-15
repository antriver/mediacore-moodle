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
 * MediaCore's local plugin
 *
 * @package    local
 * @subpackage mediacore
 * @copyright  2012 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once $CFG->dirroot. '/mod/lti/locallib.php';


/**
 * The MediaCore Moodle Client
 * Encapsulated the client access endpoints and lti helpers
 */
class mediacore_client
{
    private $_auth;
    private $_config;
    private $_key;
    private $_secret;

    /**
     * Constructor
     */
    public function __construct() {
        $this->_config = new mediacore_config();

        $url = $this->_config->get_scheme() . '://' .
                $this->_config->get_host();
        $this->_client = new MediaCore\Http\Client($url);

        $this->_key = $this->_config->get_consumer_key();
        $this->_secret = $this->_config->get_shared_secret();

        if ($this->has_lti_config()) {
            $this->_auth = new MediaCore\Auth\Lti($this->_key, $this->_secret);
        }
    }

    /**
     * The mediacore_config object
     *
     * @return mediacore_config
     */
    public function get_config() {
        return $this->_config;
    }

    /**
     */
    public function get_auth() {
        return $this->_auth;
    }

    /**
     * Get the mediacore site host
     * w/o the port
     *
     * @return string
     */
    public function get_host() {
        return $this->_config->get_host();
    }

    /**
     * Get the mediacore site base url
     *
     * @return string
     */
    public function get_siteurl() {
        return $this->_client->getUrl();
    }

    /**
     * Get an api2 constructed path from supplied api2
     * path segments
     *
     * @param string ...
     * @return string
     */
    public function get_url() {
        $args = func_get_args();
        return $this->_client->etUrl($args);
    }

    /**
     * Urlencode the query params values
     *
     * @param string $params
     * @return array
     */
    public function get_query($params) {
        return $this->_client->getQuery($params);
    }

    /**
     * Send a GET curl request
     *
     * @param string $url
     * @param array $options
     * @param array $headers
     * @return mixed
     */
    public function get($url, $options=array(), $headers=array()) {
        return $this->_client->get($url, $headers, $options);
    }

    /**
     * Send a POST curl request
     *
     * @param string $url
     * @param array $data
     * @param array $options
     * @param array $headers
     * @return mixed
     */
    public function post($url, $data, $options=array(), $headers=array()) {
        return $this->_client->post($url, $data, $headers, $options);
    }

    /**
     * Get the cookie string from the response header of
     * the authtkt api endpoint using lti
     *
     * @param int $courseid
     * @return string
     */
    public function get_authtkt($courseid) {
        global $CFG;

        $authtkt_url = $this->_client->getUrl('api2', 'lti', 'authtkt');

        if ($this->has_lti_config()) {
            $lti_params = $this->get_lti_params($courseid);
            $this->_client->setAuth($this->_auth);
            $response = $this->_client->post($authtkt_url, $lti_params);
            $this->_client->clearAuth();
        }

        $authtkt = $response->getCookie();
        return $authtkt;
    }

    /**
     * Get the chooser js url
     *
     * @return string
     */
    public function get_chooser_js_url() {
        return $this->_client->getUrl('api', 'chooser.js');
    }

    /**
     * Get the chooser url
     *
     * @return string
     */
    public function get_chooser_url() {
        return  $this->_client->getUrl('chooser');
    }

    /**
     * Get the unsigned chooser Urlencode
     * NOTE When using trusted embeds without LTI, we
     *      append a use_trusted_embed query param here.
     *
     * @return string
     */
    public function get_unsigned_chooser_url() {
        $url = $this->get_chooser_url();
        if ($this->_config->get_use_trusted_embeds()) {
            $url .= '?use_trusted_embed=true';
        }
        return $url;
    }

    /**
     * Get the moodle webroot
     *
     * @return string
     */
    public function get_webroot() {
        return $this->_config->get_webroot();
    }

    /**
     * Get the base lti request params
     *
     * @param object $course
     * @return array
     */
    public function get_lti_params($courseid) {
        global $DB, $USER, $CFG;

        $course = $DB->get_record('course', array('id' => (int)$courseid), '*',
            MUST_EXIST);

        $user_given = (isset($USER->firstname)) ? $USER->firstname : '';
        $user_family = (isset($USER->lastname)) ? $USER->lastname : '';
        $user_full = trim($user_given . ' ' . $user_family);
        $user_email = (isset($USER->email)) ? $USER->email: '';

        if (strpos($CFG->release, '2.8') === false) {
            $roles = lti_get_ims_role($USER, 0, $course->id);
        } else {
            // NOTE: Moodle 2.8 adds support for specifying whether this is
            //       an LTI 2.0 launch.
            $roles = lti_get_ims_role($USER, 0, $course->id, false);
        }

        $params = array(
            'context_id' => $course->id,
            'context_label' => $course->shortname,
            'context_title' => $course->fullname,
            'ext_lms' => 'moodle-2',
            'lis_person_name_family' => $user_family,
            'lis_person_name_full' => $user_full,
            'lis_person_name_given' => $user_given,
            'lis_person_contact_email_primary' => $user_email,
            'lti_message_type' => 'basic-lti-launch-request',
            'lti_version' => 'LTI-1p0',
            'roles' => $roles,
            'tool_consumer_info_product_family_code' => 'moodle',
            'tool_consumer_info_version' => (string)$CFG->version,
            'user_id' => $USER->id,
            'custom_context_id' => $course->idnumber,
            'custom_plugin_info' => $this->_config->get_plugin_info(),
        );

        // NOTE: For LTI launches we use a custom_use_trusted_embed
        //       param. For non-LTI launches we append use_trusted_embed
        //       as a query param. See `get_signed_chooser_url`
        if ($this->_config->get_use_trusted_embeds()) {
            $params['custom_use_trusted_embed'] = 'true';
        }

        // Add debug flag for local testing.
        if ((boolean)$CFG->debugdisplay) {
            $params['debug'] = 'true';
        }
        return $params;
    }

    /**
     * Whether the config is setup for lti
     *
     * @return boolean
     */
    public function has_lti_config() {
        return $this->_config->has_lti_config();
    }

    /**
     * Get the custom atto/tinymce params
     *
     * @return array
     */
    public function get_texteditor_params() {
        global $CFG, $COURSE;

        //default non-lti urls
        $chooser_js_url = $this->get_chooser_js_url();
        $chooser_url = $this->get_unsigned_chooser_url();
        $launch_url = null;

        if ($this->has_lti_config() && isset($COURSE->id)) {
            // append the context_id to the chooser endpoint
            $chooser_url .= (strpos($chooser_url, '?') === false) ? '?' : '&';
            $chooser_url .= 'context_id=' . $COURSE->id;

            $site_url = $this->get_siteurl();
            $content_url = $CFG->wwwroot.'/local/mediacore/sign.php';
            $launch_url = str_replace($site_url, $content_url, $chooser_url);
        }
        $params['mcore_chooser_js_url'] = $chooser_js_url;
        $params['mcore_chooser_url'] = $chooser_url;
        $params['mcore_launch_url'] = $launch_url;

        return $params;
    }

    /**
     * Method for hooking into the Moodle 2.3 Tinymce plugin lib.php
     * file
     *
     * Moodle 2.4+ uses different logic -- see MediaCore plugin
     * installation instructions for details.
     *
     * @param array $filters
     * @param array $params
     * @return array
     */
    public function configure_tinymce_lib_params($filters, $params) {
        global $COURSE;

        if (!function_exists('filter_get_active_in_context')) {
            throw new Zend_Exception('This class can only be called ' .
                'from within the tinymce/lib.php file');
        }
        if (!isset($filters)) {
            $filters = filter_get_active_in_context($context);
        }
        if (array_key_exists('filter/mediacore', $filters)) {
            $params = $params + $this->get_texteditor_params();
            $params['plugins'] .= ',mediacore';
            if (isset($params['theme_advanced_buttons3_add'])) {
                $params['theme_advanced_buttons3_add'] .= ",|,mediacore";
            } else {
                $params['theme_advanced_buttons3_add'] = ",|,mediacore";
            }
        }
        return $params;
    }
}
