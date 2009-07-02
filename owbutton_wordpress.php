<?php
/*
Plugin Name: OnlyWire Button
*/

function ow_function($text) {
    global $post;

    $text .= '<script type="text/javascript" class="owbutton" src="http://onlywire.com/button" title="'.$post->post_title.'" url="'.$post->guid.'"></script>';
    return $text;
}

add_filter('the_content', 'ow_function');

?>
