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
require_once $CFG->dirroot . '/lib/filelib.php';
require_once 'mediacore_config.class.php';
require_once 'mediacore_client.class.php';
require_once 'mediacore_media_rowset.class.php';


/**
 * A class that encapsulates fetching media from the API
 */
class mediacore_media
{
    private $_mcore_client;

    /**
     * Constructor
     *
     * @param mediacore_client $client
     */
    public function __construct($client) {
        $this->_mcore_client = $client;
    }

    /**
     * Fetch media from the media api endpoint url
     * LTI signed if applicable
     *
     * @param string $search
     * @param int $page
     * @param int $per_page
     * @param int|null $course_id
     * @return mediacore_media_rowset
     */
    public function get_media($search='', $page=1, $per_page=30, $course_id=null) {

        $query_params = array(
            'type' => 'video',
            'status' => 'published',
            'joins' => 'thumbs',
            'per_page' => $per_page,
            '_p' => $page,
        );

        if (!is_null($course_id)) {
            $query_params['context_id'] = $course_id;
        }
        if (!empty($search)) {
            $query_params['search'] = urlencode($search);
            $query_params['sort'] = 'relevance';
        }

        $api_url = $this->_mcore_client->get_api2_url('/media');

        if ($this->_mcore_client->has_lti_config() && $course_id) {
            $authtkt_str = $this->_mcore_client->get_auth_cookie($course_id);
            $result = $this->_mcore_client->get_curl_response(
                $api_url, $query_params, $authtkt_str
            );
        } else {
            $result = $this->_mcore_client->get_curl_response(
                $api_url, $query_params
            );
        }

        $rowset = null;
        if (empty($result)) {
            // TODO: report an error?
        } else {
            $result = json_decode($result);
            $rowset = new mediacore_media_rowset(
                $this->_mcore_client, $result->items
            );
        }
        return $rowset;
    }

    /**
     * Get the total media count
     *
     * @param string $search
     * @param int $course_id
     * @return int
     */
    public function get_media_count($search, $course_id) {

        $query_params = array(
            'type' => 'video',
            'status' => 'published',
        );

        if (!is_null($course_id)) {
            $query_params['context_id'] = $course_id;
        }
        if (!empty($search)) {
            $query_params['search'] = urlencode($search);
        }

        $api_url = $this->_mcore_client->get_api2_url('/media/count');

        if ($this->_mcore_client->has_lti_config() && $course_id) {
            $authtkt_str = $this->_mcore_client->get_auth_cookie($course_id);
            $result = $this->_mcore_client->get_curl_response(
                $api_url, $query_params, $authtkt_str
            );
        } else {
            $result = $this->_mcore_client->get_curl_response(
                $api_url, $query_params
            );
        }

        $count = 0;
        if (empty($result)) {
            // TODO: report an error?
        } else {
            $result = json_decode($result);
            $count = $result->count;
        }
        return $count;
    }
}
