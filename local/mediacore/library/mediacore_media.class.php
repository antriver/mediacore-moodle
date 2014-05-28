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
    private $_curr_pg = 1;
    private $_links_self = '/media';
    private $_mcore_client;
    private $_search;

    /**
     * Constructor
     * @param mediacore_client $client
     */
    public function __construct($client) {
        $this->_mcore_client = $client;
    }

    /**
     * Fetch media from the media api endpoint url
     * LTI signed if applicable
     *
     * @param int $curr_pg
     * @param string $search
     * @param int|null $course_id
     * @return mediacore_media_rowset
     */
    public function get_media($search='', $page=1, $per_page=30, $course_id=null) {

        $this->_search = urlencode($search);

        $query_params = array(
            'type' => 'video',
            'status' => 'published',
            'joins' => 'thumbs',
            'per_page' => $per_page,
        );

        if (!is_null($course_id)) {
            $query_params['context_id'] = $course_id;
        }
        if (!empty($this->_search)) {
            $query_params['search'] = $this->_search;
            $query_params['sort'] = 'relevance';
        }

        // load all the media thumbs. no pagination
        // TODO add pagination
        $api_url = $this->_mcore_client->get_api2_url($this->_links_self);

        if ($this->_mcore_client->has_lti_config() && $course_id) {
            $authtkt_str = $this->_mcore_client->get_auth_cookie($course_id);
            $result = $this->_mcore_client->get_curl_response($api_url,
                $query_params, $authtkt_str);
        } else {
            $result = $this->_mcore_client->get_curl_response($api_url,
                $query_params);
        }
        if (empty($result)) {
            return $result;
        }
        $result = json_decode($result);
        $this->_curr_pg = (int)$page;
        $this->_links_self = $result->links->self;
        $this->_items = new mediacore_media_rowset(
            $this->_mcore_client, $result->items
        );

        return $this->_items;
    }

    /**
     * Get the total media count
     *
     * @param string $search
     * @param int $course_id
     * @return int
     */
    public function get_media_count($search, $course_id) {

        $this->_search = urlencode($search);

        $query_params = array(
            'type' => 'video',
            'status' => 'published',
        );

        if (!is_null($course_id)) {
            $query_params['context_id'] = $course_id;
        }
        if (!empty($this->_search)) {
            $query_params['search'] = $this->_search;
        }

        // load all the media thumbs. no pagination
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
        if (empty($result)) {
            return $result;
        }
        $result = json_decode($result);
        return $result->count;
    }

    /**
     * Get the current media rowset page number
     *
     * @return int
     */
    public function get_current_page() {
        return $this->_curr_pg;
    }

    /**
     * Get the current media rowset page number
     * @return int
     */
    public function get_current_page_str() {
        return '' . $this->_curr_pg;
    }
}
