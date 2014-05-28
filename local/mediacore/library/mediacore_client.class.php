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
require_once 'Zend/Uri/Http.php';
require_once 'Zend/Exception.php';
require_once 'mediacore_config.class.php';


/**
 * The MediaCore Moodle Client
 * Encapsulated the client access endpoints and lti helpers
 */
class mediacore_client
{
    private $_config;
    private $_api2_root_path = '/api2';
    private $_authtkt_path = '/api2/lti/authtkt';
    private $_chooser_js_path = '/api/chooser.js';
    private $_chooser_path = '/chooser';
    private $_uri;
    private static $_scheme = 'https';

    /**
     * Constructor
     */
    public function __construct() {
        $this->_config = new mediacore_config();

        // We have to use the fromString method because the 'host' we pass in
        // may actually contain a port (e.g. 'blah.com:8080' not just 'blah.com')
        // so we can't just pass it to Zend_Uri_Http.setHost(), like one might
        // expect
        $this->_uri = Zend_Uri_Http::fromString(
            self::$_scheme . '://' . $this->_config->get_host()
        );
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
     * Get the mediacore site url scheme
     *
     * @return string|boolean
     */
    public function get_scheme() {
        return self::$_scheme;
    }

    /**
     * Get the mediacore site host
     * w/o the port
     *
     * @return string
     */
    public function get_host() {
        return $this->_uri->getHost();
    }

    /**
     * Get the mediacore site port
     *
     * @return string|boolean
     */
    public function get_port() {
        return $this->_uri->getPort();
    }

    /**
     * Get the mediacore site url host and port
     *
     * @return string
     */
    public function get_host_and_port() {
        $val = $this->get_host();
        if ($this->get_port()) {
            $val .= ':' . $this->get_port();
        }
        return $val;
    }

    /**
     * Get the mediacore site base url
     *
     * @return string
     */
    public function get_siteurl() {
        return $this->get_scheme() .
            '://' . $this->get_host_and_port();
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
     * Get the chooser js url
     *
     * @return string
     */
    public function get_chooser_js_url() {
        return $this->get_siteurl() . $this->_chooser_js_path;
    }

    /**
     * Get the chooser url
     *
     * @return string
     */
    public function get_chooser_url() {
        return  $this->get_siteurl() . $this->_chooser_path;
    }

    /**
     * Sign and return the LTI-signed chooser endpoint
     *
     * @param string|int $courseid
     * @return string
     */
    public function get_signed_chooser_url($courseid, $lti_params) {
        $url = $this->get_chooser_url();
        return $url . '?' . $this->url_encode_query_params(
            $this->get_signed_lti_params($url, 'GET', $courseid, $lti_params));
    }

    /**
     * Get an api2 constructed path from a supplied api2 path
     * segment and optional query parameters
     *
     * @param $path string
     * @param $query_params array
     * @return string
     */
    public function get_api2_url($path='', $query_params=array()) {
        $url = $this->get_baseurl() . $this->_api2_root_path;
        if (!empty($path)) {
            $url .= $path;
        }
        if (is_array($query_params) && !empty($query_params)) {
            $url .= '?' . $this->url_encode_query_params($query_params);
        }
        return rtrim($url, '&');
    }

    /**
     * Get the url to the authtkt api endpoint
     *
     * @return string
     */
    public function get_authtkt_url() {
        return $this->get_baseurl() . $this->_authtkt_path;
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
     * Get the signed lti parameters
     * uses Oauth-1x
     *
     * @param string $endpoint
     * @param int $courseid
     * @param array $params
     * @return array
     */
    public function get_signed_lti_params($endpoint, $method='GET', $courseid,
            $params=array()) {
        global $DB;

        if (empty($courseid)) {
            throw new Zend_Exception(get_string('no_course_id',
                MEDIACORE_PLUGIN_NAME), E_USER_ERROR);
        }
        if (!$this->_config->has_lti_config()) {
            throw new Zend_Exception(get_string('no_lti_config',
                MEDIACORE_PLUGIN_NAME), E_USER_ERROR);
        }
        $course = $DB->get_record('course', array('id' => (int)$courseid), '*',
            MUST_EXIST);
        $key = $this->_config->get_consumer_key();
        $secret = $this->_config->get_shared_secret();
        $query_params = $this->get_lti_params($course);
        return lti_sign_parameters(array_merge($query_params, $params), $endpoint,
            $method, $key, $secret);
    }

    /**
     * Get the base lti request params
     *
     * @param int $courseid
     * @return array
     */
    public function get_lti_params($course) {
        global $USER, $CFG;

        $user_given = (isset($USER->firstname)) ? $USER->firstname : '';
        $user_family = (isset($USER->lastname)) ? $USER->lastname : '';
        $user_full = trim($user_given . ' ' . $user_family);
        $user_email = (isset($USER->email)) ? $USER->email: '';

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
            'roles' => lti_get_ims_role($USER, 0, $course->id),
            'tool_consumer_info_product_family_code' => 'moodle',
            'tool_consumer_info_version' => (string)$CFG->version,
            'user_id' => $USER->id,
            'custom_context_id' => $course->idnumber,
            'custom_plugin_info' => $this->_config->get_plugin_info(),
        );

        // Add debug flag for local testing.
        if ((boolean)$CFG->debugdisplay) {
            $params['debug'] = 'true';
        }
        return $params;
    }

    /**
     * Get a curl response as JSON
     *
     * @param string $url
     * @param array $params
     * @param string $opt_cookie_str
     * @return object|false
     */
    public function get_curl_response($url, $query_params,
            $opt_cookie_str) {
        global $CFG;

        $url .= (strpos($url, '?') === false ? '?' : '&') .
            $this->url_encode_query_params($query_params);

        $options = array(
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_URL => $url,
        );

        //build headers
        $headers = array();
        if (!empty($opt_cookie_str)) {
            array_push($headers, 'Cookie: ' . $opt_cookie_str);
        }
        array_push($headers, 'Content-Type: application/json');
        $options[CURLOPT_HTTPHEADER] = $headers;

        if ((boolean)$CFG->debugdisplay) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Get the cookie string from the response header of
     * the authtkt api endpoint using lti
     *
     * @param int $courseid
     * @return string
     */
    public function get_auth_cookie($courseid) {
        global $CFG;

        $authtkt_url = $this->get_authtkt_url();
        $signed_lti_params = $this->get_signed_lti_params(
            $authtkt_url, 'POST', $courseid);

        $options = array(
            CURLOPT_POST => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_URL => $authtkt_url,
            CURLOPT_POSTFIELDS =>
            $this->url_encode_query_params($signed_lti_params),
            );

        if ((boolean)$CFG->debugdisplay) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        if (!$result) { // curl failed.
            return $result;
        }
        // parse the cookie from the header
        $arr = array();
        preg_match('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        if (isset($matches[1])) {
            return rtrim($matches[1]);
        }
        return '';
    }

    /**
     * Urlencode the parameter values as a query string
     *
     * @param array $params
     * @return string
     */
    public function url_encode_query_params($params) {
        $encoded_params = '';
        foreach ($params as $k => $v) {
            $encoded_params .= "$k=" . urlencode($v) . "&";
        }
        return substr($encoded_params, 0, -1);
    }

    /**
     * Get the custom tinymce params
     *
     * @return array
     */
    public function get_tinymce_params() {
        global $COURSE;

        $params['mcore_chooser_js_url'] = $this->get_chooser_js_url();

        if ($this->has_lti_config() && isset($COURSE->id)) {
            //for backwards compatibility with non-closure chooser
            $lti_params = array(
                'origin' => $this->get_webroot(),
            );
            $params['mcore_chooser_url'] = $this->get_signed_chooser_url(
                $COURSE->id, $lti_params);
        } else {
            $params['mcore_chooser_url'] = $this->get_chooser_url();
        }
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
        if (!function_exists('filter_get_active_in_context')) {
            throw new Zend_Exception('This class can only be called ' .
                'from within the tinymce/lib.php file');
        }
        if (!isset($filters)) {
            $filters = filter_get_active_in_context($context);
        }
        if (array_key_exists('filter/mediacore', $filters)) {
            $params = $params + $this->get_tinymce_params();
            $params['plugins'] .= ',mediacore';
            if (isset($params['theme_advanced_buttons3_add'])) {
                $params['theme_advanced_buttons3_add'] .= ",|,mediacore";
            } else {
                $params['theme_advanced_buttons3_add'] = ",|,mediacore";
            }
        }
        return $params;
    }

    /**
     * Get the embed html by parsing the api1 view url for its slug
     * e.g. https://demo.mediacore.tv/media/{slug}?context_id=2
     *
     * @param string $url
     * @return string $id
     */
    public function get_embed_html_from_api1_view_url($url, $width, $height,
            $courseid=null) {
        $patharr = explode('/', parse_url($url, PHP_URL_PATH));
        $slug = end($patharr);
        $api2_url = $this->get_api2_url('/media/slug:' . $slug);
        return $this->_get_embed_html($api2_url, $width, $height, $courseid);
    }

    /**
     * Get the embed html by parsing the api2 view url for its id
     * e.g. http://demo.mediacore.tv/media/{id}/view
     *
     * @param string $url
     * @return string $id
     */
    public function get_embed_html_from_api2_view_url($url, $width, $height,
        $courseid=null) {
        $patharr = explode('/', parse_url($url, PHP_URL_PATH));
        $id = $patharr[count($patharr) - 2];
        $api2_url = $this->get_api2_url('/media/' . $id);
        return $this->_get_embed_html($api2_url, $width, $height, $courseid);
    }

    /**
     * Get the media embed html LTI signed if applicable
     *
     * @param string $url
     * @param int $width
     * @param int $height
     * @param int|null $courseid
     */
    private function _get_embed_html($url, $width, $height, $courseid=null) {

        $query_params = array(
            'joins' => 'embedcode',
        );
        if (!is_null($courseid)) {
            $query_params['context_id'] = $courseid;
        }
        if ($this->has_lti_config() && !is_null($courseid)) {
            $authtkt_str = $this->get_auth_cookie($courseid);
            $result = $this->get_curl_response($url, $query_params, $authtkt_str);
        } else {
            $result = $this->get_curl_response($url, $query_params);
        }
        if (empty($result)) {
            return $result;
        }
        $result = json_decode($result);
        $html = $result->joins->embedcode->html;

        // replace the width and height with new values
        $patterns = array(
            '/width="([0-9]+)"/',
            '/height="([0-9]+)"/'
        );
        $replace = array(
            'width="' . $width . '"',
            'height="' . $height . '"'
        );
        return preg_replace($patterns, $replace, $html);
    }
}
