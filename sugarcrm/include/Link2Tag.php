<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


/**
 * This class converts embedded http links to html tags.
 * The following video links are converted to <iframe> tags using oEmbed: youtube, flickr, vimeo, and hulu
 * Image links for png, jp(e)g, and gif are converted to <img> tags.
 * All other http links will be converted to <a> tags
 *
 */
class Link2Tag {
    /**
     * oEmbed providers and their api end points.
     * @var array
     */
    protected static $oEmbedProviders = array(
            'youtube.com' => 'http://www.youtube.com/oembed',
            'youtu.be' => 'http://www.youtube.com/oembed',
            'flickr.com' => 'http://www.flickr.com/services/oembed/',
            'vimeo.com' => 'http://vimeo.com/api/oembed.json',
            'hulu.com/watch' => 'http://www.hulu.com/api/oembed.json');
    
    /**
     * Converts video, image, and other links embedded in text to html tags.
     * @param string $text
     * @return string
     */
    public static function convert($text) {
        // http links
        $text = preg_replace_callback(
                '#(?<=[\s>])(\()?([\w]+?://(?:[\w\\x80-\\xff\#$%&~/\-=?@\[\](+]|[.,;:](?![\s<])|(?(1)\)(?![\s<])|\)))+)#is',
                'Link2Tag::convertHttpLink',
                ' '.$text);        
        // email, ftp links?
        return trim($text);
    } 

    /**
     * Converts a http link to html tag
     * @param unknown_type $text
     */
    protected static function convertHttpLink($matches) {
        $result = $matches[0];
        $url = $matches[2];

        if(empty($url)) {
            return $result;
        }
        
        // oEmbed
        foreach(self::$oEmbedProviders as $domain=>$endPoint) {
            if(preg_match("|http://(www\.)?$domain/.*|i",$url)) {
                $result = self::getOEmbedTag($endPoint, $url);
                if(!empty($result)) {
                    return $result;
                }
                break;
            }
        } 

        
        // image
        $result = preg_replace(
                '#(https?://[^\s]+(?=\.(jpe?g|png|gif)))(\.(jpe?g|png|gif))#i',
                '<img src="$1.$2" alt="$1.$2" />',
                $result); 
        
        // other links
        $result = preg_replace(
                '#(?<=[\s>])(\()?([\w]+?://(?:[\w\\x80-\\xff\#$%&~/\-=?@\[\](+]|[.,;:](?![\s<])|(?(1)\)(?![\s<])|\)))+)#is',
                '$1<a href="$2">$2</a>',
                ' '.$result);     
        return trim($result);
    }
    
    /**
     * Calls oEmbed service specified by $endPoint to get information for $contentUrl 
     * then converts the link to a html tag based on the content type.
     * @param string $endPoint
     * @param string $contentUrl
     * @param array $options maxwidth, maxheight etc
     * @return string or false
     */
    public static function getOEmbedTag($endPoint, $contentUrl, $options = array()) {
        $query = array('url' => $contentUrl, 'format' => 'json');
        $queryString = http_build_query(array_merge($query, $options), '', '&');
        $result = @file_get_contents($endPoint.'?'.$queryString);
       
        if(empty($result)) {
            return false;
        }
        
        $data = json_decode(trim($result), false);
        
        if(is_object($data) && !empty($data->type)) {
            $tag = false;
            
            switch($data->type){
                case 'video':
                case 'rich':
                    if(!empty($data->html))
                        $tag = $data->html;
                    break;
                case 'photo':
                    if(!empty($data->url)) {
                        $title = (!empty($data->title)) ? $data->title : '';
                        $url = self::cleanUrl($data->url);
                        
                        if(!empty($url)) {
                            $tag = '<img src="' .htmlentities($url) . '" alt="' . htmlentities($title) . '" />';
                        }
                    }
                
                    break;                    
                case 'link':
                    $title =  (!empty($data->title)) ? $data->title : '';
                    $url = self::cleanUrl($data->url);
                    
                    if(!empty($url)) {                    
                        $tag = '<a href="' . $url . '">' . htmlentities($title) . '</a>';
                    }
                    break;
                default:
                    break;
            }
            
            return $tag;
        
        }
        else{
            return false;
        }
    }
    
    /**
     * Checks if $url is valid.
     * @param string $url
     * @return string
     */
    public static function cleanUrl($url) {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url) ? $url : "";
    }
}