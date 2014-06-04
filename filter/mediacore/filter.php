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
 * Automatic media embedding filter class.
 *
 * @package    filter
 * @subpackage mediacore
 * @copyright  2012 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once $CFG->libdir . '/filelib.php';
require_once $CFG->dirroot . '/local/mediacore/lib.php';
require_once('mediacore_client.class.php');


/**
 * Find instances of MediaCore.tv links and replace the link with embed code
 * from the MediaCore API.
 */
class filter_mediacore extends moodle_text_filter {

    private $_mcore_client;
    private $_api1_view_link_re;
    private $_api2_view_link_re;
    private $_default_thumb_width = 400;
    private $_default_thumb_height = 225;

    /**
     * Constructor
     * @param object $context
     * @param object $localconfig
     */
    public function __construct($context, array $localconfig) {
        parent::__construct($context, $localconfig);
        $this->_mcore_client = new mediacore_client();
        $host = $this->_mcore_client->get_host_and_port();
        $this->_api1_view_link_re = "/$host\/media\/[a-z0-9_-]+\?context_id/";
        $this->_api2_view_link_re = "/$host\/api2\/media\/[0-9]+\/view/";
    }

    /**
     * Filter the page html and look for an <a><img> element added by the chooser
     * or an <a> element added by the moodle file picker
     * NOTE: An embed from the Chooser and a link from the old filepicker are
     *   of the same form (see $_api1_view_link_re).
     * A link from the new repository filepicker plugin is different
     *   (see $_api2_view_link_re).
     * The Chooser style link will updated in the future to match the new link
     *   style.
     * @param string $html
     * @param array $options
     * @return string
     */
    public function filter($html, array $options = array()) {
        global $COURSE;
        $courseid = (isset($COURSE->id)) ? $COURSE->id : null;

        if (empty($html) || !is_string($html) ||
            strpos($html, $this->_mcore_client->get_host_and_port()) === false) {
            return $html;
        }
        $dom = new DomDocument();
        @$dom->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//a') as $node) {
            $href = $node->getAttribute('href');
            if (empty($href)) {
                continue;
            }
            if ((boolean)preg_match($this->_api1_view_link_re, $href) === true) {
                $newnode  = $dom->createDocumentFragment();
                $imgnode = $node->firstChild;

                if ($imgnode && $imgnode instanceof DOMElement) {
                    $width = $imgnode->getAttribute('width');
                    $height = $imgnode->getAttribute('height');
                }

                if (empty($width) || empty($height)
                    || ($width == 195 && $height == 110)) {
                    // Keep old moodle embeds @ the default size
                    $width = $this->_default_thumb_width;
                    $height = $this->_default_thumb_height;
                }

                $html = $this->_get_embed_html_from_api1_view_url(
                    $href, $width, $height, $courseid);
                if (empty($html)) {
                    $html = $this->_get_embed_error_html();
                }
                $newnode->appendXML($html);
                $node->parentNode->replaceChild($newnode, $node);

            } else if ((boolean)preg_match($this->_api2_view_link_re, $href) === true) {
                $newnode  = $dom->createDocumentFragment();

                $width = $this->_default_thumb_width;
                $height = $this->_default_thumb_height;

                $html = $this->_get_embed_html_from_api2_view_url(
                    $href, $width, $height, $courseid);
                if (empty($html)) {
                    $html = $this->_get_embed_error_html();
                }
                $newnode->appendXML($html);
                $node->parentNode->replaceChild($newnode, $node);
            }
        }
        return $dom->saveHTML();
    }

    /**
     * Get the embed html by parsing the api1 view url for its slug
     * e.g. https://demo.mediacore.tv/media/{slug}?context_id=2
     *
     * @param string $href
     * @return string $id
     */
    private function _get_embed_html_from_api1_view_url($href, $width, $height,
            $courseid=null) {
        $patharr = explode('/', parse_url($href, PHP_URL_PATH));
        $slug = end($patharr);
        $url = $this->_mcore_client->get_url('media', 'slug:' . $slug);
        return $this->_get_embed_html($url, $width, $height, $courseid);
    }

    /**
     * Get the embed html by parsing the api2 view url for its id
     * e.g. http://demo.mediacore.tv/media/{id}/view
     *
     * @param string $href
     * @return string $id
     */
    private function _get_embed_html_from_api2_view_url($href, $width, $height,
        $courseid=null) {
        $patharr = explode('/', parse_url($href, PHP_URL_PATH));
        $id = $patharr[count($patharr) - 2];
        $url = $this->_mcore_client->get_url('media', $id);
        return $this->_get_embed_html($url, $width, $height, $courseid);
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

        $params = array(
            'joins' => 'embedcode',
        );
        if (!is_null($courseid)) {
            $params['context_id'] = $courseid;
        }

        if ($this->_mcore_client->has_lti_config() && !is_null($courseid)) {
            $headers = array();
            $authtkt_str = $this->_mcore_client->get_auth_cookie($courseid);
            if (empty($authtkt_str)) {
                // TODO: report an error?
            } else {
                $headers = array('Cookie: ' . $authtkt_str);
                $url .= '?' . $this->_mcore_client->get_query($params);
                $result = $this->_mcore_client->get($url, null, $headers);
            }
        } else {
            $result = $this->_mcore_client->get($url);
        }

        $html = '';
        if (empty($result)) {
            //TODO: report an error?
        } else {
            $result = json_decode($result);
            $embed_url = $result->joins->embedcode->url;
            $html = $this->_create_iframe_html($embed_url, $width, $height, $courseid);
        }
        return $html;
    }


    /**
     * Create an iframe for the embed url with signed lti parameters
     *
     * @param string $embed_url
     * @param int $width
     * @param int $height
     * @param int $courseid
     * @return $html
     */
    private function _create_iframe_html($embed_url, $width, $height, $courseid=null) {
        $params = array('iframe'=>'True');

        $iframe_str = '<iframe' .
            ' src="%src%"' .
            ' width="%width%"' .
            ' height="%height%"' .
            ' allowfullscreen="allowfullscreen"' .
            ' frameborder="0">' .
            '</iframe>';

        $embed_path = parse_url($embed_url, PHP_URL_PATH);
        $endpoint = $this->_mcore_client->get_siteurl() . $embed_path;

        if ($this->_mcore_client->has_lti_config() && !is_null($courseid)) {
            $params = $this->_mcore_client->get_signed_lti_params(
                $endpoint, 'GET', $courseid, $params
            );
        }

        $query = http_build_query($params); // "&" encoded as "&amp;"
        $iframe_src = $endpoint . '?' . $query;

        $patterns = array('/%src%/', '/%width%/', '/%height%/');
        $replace = array($iframe_src, $width, $height);

        return preg_replace($patterns, $replace, $iframe_str);
    }

    /**
     * Get a custom video not found error suitable for rendering by the filter
     * @param string $msg
     * @return string
     */
    private function _get_embed_error_html($msg=null) {
        if (is_null($msg)) {
            $msg = get_string('filter_no_video_found', 'filter_mediacore');
        }
        return '<div class="mcore-no-video-found-error"><p>' . $msg . '</p></div>';
    }
}
