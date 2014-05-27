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
 * MediaCore repository search
 *
 * @package    repository_mediacore
 * @category   repository
 * @copyright  2013 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once $CFG->dirroot . '/repository/lib.php';
require_once $CFG->dirroot . '/local/mediacore/lib.php';
require_once 'mediacore_client.class.php';
require_once 'mediacore_media.class.php';


/**
 * repository_mediacore class
 * This is a class used to browse images from mediacore
 *
 * @since 2.0
 * @package    repository_mediacore
 * @copyright  2013 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class repository_mediacore extends repository
{
    private $_mcore_client;
    private $_mcore_media;

    /**
     * MediaCore plugin constructor
     * @param int $repositoryid
     * @param object $context
     * @param array $options
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID,
            $options = array()) {
        parent::__construct($repositoryid, $context, $options);
        $this->_mcore_client = new mediacore_client();
        $this->_mcore_media = new mediacore_media($this->_mcore_client);
    }

    public function check_login() {
        return !empty($this->keyword);
    }

    /**
     * Return search results
     * @param string $search_text
     * @return array
     */
    public function search($search_text, $page = 0) {
        global $SESSION, $COURSE;
        $sess_keyword = 'mediacore_'.$this->id.'_keyword';

        // This is the request of another page for the last search, retrieve
        //  the cached keyword and sort.
        if ($page && !$search_text && isset($SESSION->{$sess_keyword})) {
            $search_text = $SESSION->{$sess_keyword};
        }

        // Save this search in session.
        $SESSION->{$sess_keyword} = $search_text;
        $this->keyword = $search_text;

        $ret  = array();
        $ret['page'] = (int)$page;
        if (empty($ret['page']) || $ret['page'] < 1) {
            $ret['page'] = 1;
        }
        $ret['list'] = $this->_get_media($search_text, $ret['page']);
        $ret['nologin'] = true;
        $ret['norefresh'] = true;
        $ret['nosearch'] = true;

        //TODO add pagination
        $ret['pages'] = 1;
        return $ret;
    }

    private function _get_media($search_text, $page=1) {
        global $COURSE;
        $cid = isset($COURSE->id) ? $COURSE->id : null;
        $items = $this->_mcore_media->get_media($search_text, $page, $cid);
        if (empty($items)) {
            // TODO: Return that there was an issue connecting MediaCore.
            return array();
        }
        $files_array = array();
        foreach ($items as $item) {
            $files_array[] = $item->get_repository_file_array();
        }
        return $files_array;
    }


    /**
     * MediaCore plugin doesn't support global search
     */
    public function global_search() {
        return false;
    }

    public function get_listing($path='', $page = '') {
        return array();
    }

    /**
     * Generate search form
     */
    public function print_login($ajax = true) {
        $search = new stdClass();
        $search->type = 'text';
        $search->id   = 'mediacore_search';
        $search->name = 's';
        $search->label = get_string('keyword', 'repository_mediacore').': ';

        $ret = array();
        $ret['login'] = array($search);
        $ret['login_btn_label'] = get_string('search');
        $ret['login_btn_action'] = 'search';
        // Indicates that login form can be cached in filepicker.js.
        $ret['allowcaching'] = true;
        return $ret;
    }

    /**
     * What kind of files will be in this repository?
     *
     * @return array
     */
    public function supported_filetypes() {
        return array('video');
    }

    /**
     * Tells how the file can be picked from this repository
     *
     * Returns FILE_EXTERNAL
     *
     * @return int
     */
    public function supported_returntypes() {
        return (FILE_EXTERNAL);
    }
}
