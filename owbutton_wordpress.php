<?php
/*
Plugin Name: OnlyWire Bookmark & Share Button (Official Plugin)
Plugin URI: http://wordpress.org/extend/plugins/onlywire-bookmark-share-button/
Description: Auto-submit your posts to over 20 social networks with one click 
Version: 1.0
Author: OnlyWire Engineering Team 
Author URI: http://onlywire.com
*/

function ow_function($text) {
    global $post;

    $text .= '<script type="text/javascript" class="owbutton" src="http://onlywire.com/button" title="'.$post->post_title.'" url="'.$post->guid.'"></script>';
    return $text;
}

add_filter('the_content', 'ow_function');

?>
