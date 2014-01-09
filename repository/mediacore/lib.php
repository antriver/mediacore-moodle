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
 */

defined('MOODLE_INTERNAL') || die('Invalid access');
global $CFG;
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/local/mediacore/lib.php');

define('MEDIACORE_THUMBS_PER_PAGE', 24);

/**
 * repository_mediacore class
 * This is a class used to browse images from mediacore
 *
 * @since 2.0
 * @package    repository_mediacore
 * @copyright  2013 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class repository_mediacore extends repository {
    private $_mcore_client;
    private $_mcore_media;

    /**
     * MediaCore plugin constructor
     * @param int $repositoryid
     * @param object $context
     * @param array $options
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
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

        // This is the request of another page for the last search, retrieve the cached keyword and sort.
        if ($page && !$search_text && isset($SESSION->{$sess_keyword})) {
            $search_text = $SESSION->{$sess_keyword};
        }

        // Save this search in session.
        $SESSION->{$sess_keyword} = $search_text;

        $this->keyword = $search_text;
        $ret  = array();
        $ret['nologin'] = true;
        $ret['page'] = (int)$page;
        if ($ret['page'] < 1) {
            $ret['page'] = 1;
        }

        $ret['list'] = $this->_get_collection($search_text, $ret['page'] - 1);
        $ret['norefresh'] = true;
        $ret['nosearch'] = true;
        $ret['pages'] = $this->_mcore_media->get_page_count();
        return $ret;
    }

    private function _get_collection($search_text, $page = 0) {
        global $COURSE;
        $cid = isset($COURSE->id) ? $COURSE->id : NULL;
        $media = $this->_mcore_media->fetch_media($page, $search_text,
            MEDIACORE_THUMBS_PER_PAGE, $cid);
        if (!$media) {
            // TODO: Return that there was an issue connecting MediaCore.
            return array();
        }

        $files_array = array();
        foreach ($media as $m) {
            $query_string = (isset($lti_type_id) ? '?type_id=' . $lti_type_id : '');
            $thumb = $m->thumbs->s;
            $files_array[] = array(
                'shorttitle' => $this->_truncate_text($m->title, 25),
                'thumbnail_title' => $this->_truncate_text($m->title),
                'title' => $m->title.'.avi',
                'author' => $m->author,
                'datemodified' => strtotime($m->modified_on),
                'datecreated' => strtotime($m->created_on),
                'thumbnail' => $thumb->url,
                'thumbnail_width' => $thumb->x,
                'thumbnail_height' => $thumb->y,
                'size' => '',
                'id' => $m->id,
                'source' => $m->url . $query_string . "#$m->title",
            );
        }
        return $files_array;
    }

    private function _truncate_text($text, $chars = 20, $pad = '...') {
        if (strlen($text) <= $chars) {
            return $text;
        }
        $result = ''; $count = 0;
        $words = explode(' ', $text);
        foreach ($words as $w) {
            $wcount = strlen($w);
            if (($count + $wcount) <= $chars) {
                $result .= $w . ' ';
                $count += $wcount + 1;
            } else {
                break;
            }
        }
        return rtrim($result, ' ') . $pad;
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
        $search->label = get_string('keyword', 'repository_mediacore').': ';
        $search->id   = 'input_text_keyword';
        $search->type = 'text';
        $search->name = 'mediacore_keyword';
        $search->value = '';

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
