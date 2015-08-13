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

    private $_client;
    private $_re_api1_public_urls;
    private $_re_api2_public_urls;
    private $_re_embed_url;
    private $_default_thumb_width = 400;
    private $_default_thumb_height = 225;

    /**
     * Constructor
     * @param object $context
     * @param object $localconfig
     */
    public function __construct($context, array $localconfig) {
        parent::__construct($context, $localconfig);
        $this->_client = new mediacore_client();
        $host = $this->_client->get_host();
        $this->_re_api1_public_urls = "/($host)[:0-9]*\/media\/[:a-z0-9_-]+/";
        $this->_re_api2_public_urls = "/($host)[:0-9]*\/api2\/media\/[0-9]+\/view/";
        $this->_re_embed_url = "/($host)[:0-9]*\/media\/[:a-z0-9_-]+\/embed_player.*/";
    }

    /**
     * Filter the page html and look for an <a><img> element added by the chooser
     * or an <a> element added by the moodle file picker
     *
     * NOTE: Thumbnail html from the Chooser and a link from the old filepicker
     *       are of the same form (see $_re_api1_public_urls).
     *       A thumbnail link from the new repository filepicker plugin is
     *       different (see $_re_api2_public_urls).
     *       The latest version of the local lib and rich text editor plugins
     *       use both a trusted and a regular embed url as the href value of
     *       the thumbnail html (this is the preferred route going forward).
     *       Both types of embed_urls will need a user to be authenticated
     *       before they can view the embed.
     *
     * @param string $html
     * @param array $options
     * @return string
     */
    public function filter($html, array $options = array()) {
        global $COURSE;
        $courseid = (isset($COURSE->id)) ? $COURSE->id : null;

        if (empty($html) || !is_string($html) ||
            strpos($html, $this->_client->get_host()) === false) {
            return $html;
        }

        $pagedoc = new DomDocument();
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        @$pagedoc->loadHtml($html);

        $xpath = new DOMXPath($pagedoc);
        foreach ($xpath->query('//a') as $node) {

            $href = $node->getAttribute('href');
            if (empty($href)) {
                continue;
            }

            $embedurl = $width = $height = null;
            if ((boolean)preg_match($this->_re_embed_url, $href)) {
                $embedurl = $this->_maybe_get_lti_signed_url($href, $courseid);
                $imgnode = $node->firstChild;
                extract($this->_get_image_dimensions($imgnode));

            } else if ((boolean)preg_match($this->_re_api1_public_urls, $href)) {
                $embedurl = $this->_get_embed_url_from_slug($href);
                $embedurl = $this->_maybe_get_lti_signed_url($embedurl, $courseid);
                $imgnode = $node->firstChild;
                extract($this->_get_image_dimensions($imgnode));

            } else if ((boolean)preg_match($this->_re_api2_public_urls, $href)) {
                $embedurl = $this->_get_embed_url_from_id($href);
                $embedurl = $this->_maybe_get_lti_signed_url($embedurl, $courseid);
                $width = $this->_default_thumb_width;
                $height = $this->_default_thumb_height;
            }

            if (isset($embedurl, $width, $height)) {
                $iframehtml = $this->_get_iframe_html($embedurl, $width, $height);
                $newnode = $pagedoc->createDocumentFragment();
                $newnode->appendXML($iframehtml);
                $node->parentNode->replaceChild($newnode, $node);
            }
        }
        $pagehtml = $pagedoc->saveHTML();
        //
        //remove the doctype added during save
        $pagehtml = preg_replace('/^<!DOCTYPE.+?>/', '', $pagehtml);

        //remove html and body tags added during save
        return str_replace(
            array('<html>', '</html>', '<body>', '</body>'),
            array('', '', '', ''),
            $pagehtml
        );
    }

    /**
     * Fetch the width an height from an image node
     * @param DOMNode $imgnode
     * @return array
     */
    private function _get_image_dimensions($imgnode) {
        if ($imgnode && $imgnode instanceof DOMElement) {
            $width = $imgnode->getAttribute('width');
            $height = $imgnode->getAttribute('height');
        }
        if (empty($width) || empty($height)
            || ($width == 195 && $height == 110)) {
            // Keep old moodle embeds at the default size
            $width = $this->_default_thumb_width;
            $height = $this->_default_thumb_height;
        }
        return array(
            'width' => $width,
            'height' => $height
        );
    }

    /**
     * Get the embed url by parsing the api1 view url for its slug
     * e.g. https://demo.mediacore.tv/media/{slug}?context_id=2
     *
     * @param string $href
     * @return string
     */
    private function _get_embed_url_from_slug($href) {
        $patharr = explode('/', parse_url($href, PHP_URL_PATH));
        $pathseg = end($patharr);
        return $this->_client->get_url('media', $pathseg, 'embed_player');
    }

    /**
     * Get the embed url by parsing the api2 view url for its id
     * e.g. http://demo.mediacore.tv/media/id:{id}/view
     *
     * @param string $href
     * @return string
     */
    private function _get_embed_url_from_id($href) {
        $patharr = explode('/', parse_url($href, PHP_URL_PATH));
        $id = $patharr[count($patharr) - 2];
        $pathseg = 'id:' . $id;
        return $this->_client->get_url('media', $pathseg, 'embed_player');
    }

    /**
     * Get a url, LTI-signed if applicable
     *
     * @param string $url
     * @param string|null $courseid
     * @return string
     */
    private function _maybe_get_lti_signed_url($url, $courseid=null) {
        global $CFG;

        if ($this->_client->has_lti_config() && !is_null($courseid)) {
            $pos = strpos($url, '?');
            $params = array();
            if ($pos !== false) {
                // Rebuild the query params
                $qs = substr($url, $pos + 1);
                parse_str($qs, $params);
                $url = substr($url, 0, $pos);
            }
            $params['context_id'] = $courseid;
            $url .= '?' . http_build_query($params);
            $site_url = $this->_client->get_siteurl();
            $content_url = $CFG->wwwroot.'/local/mediacore/sign.php';
            $url = str_replace($site_url, $content_url, $url);
        }
        return $url;
    }

    /**
     * Get the iframe html
     * @param string $width
     * @param string $height
     * @return string
     */
    private function _get_iframe_html($url, $width, $height) {
        $template = '<iframe src="URL" ' .
            'width="WIDTH" ' .
            'height="HEIGHT" ' .
            'webkitallowfullscreen="webkitallowfullscreen" ' .
            'allowfullscreen="allowfullscreen" ' .
            'frameborder="0"> ' .
            '</iframe>';
        $patterns = array('/URL/', '/WIDTH/', '/HEIGHT/');
        $replace = array($url, $width, $height);
        return preg_replace($patterns, $replace, $template);
    }

    /**
     * Get a custom video not found error suitable for rendering by the filter
     * @param string $msg
     * @param string $error
     * @return string
     */
    private function _get_embed_error_html($msg=null, $error='') {
        if (is_null($msg)) {
            $msg = get_string('filter_embed_template_failure', 'filter_mediacore');
        }
        return '<div class="mcore-no-video-found-error"><p>' .
            $msg . '<!-- ' . htmlentities($error) . ' -->' .
            '</p></div>';
    }
}
