<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/content/ajax/comments

$output = array();
$output[] = '<section id="comments" class="grid_8 alpha omega">';

// Header
$output[] = '<header id="comments_hdr">';
$output[] = '<h3>';
if ( ! empty($feed_url))
{
	$header .= '<span>subscribe</span>';
	$output[] = HTML::anchor($feed_url, $header, array('title' => 'subscribe to this article\'s comment feed'));
}
else
{
	$output[] = $header;
}
$output[] = '</h3>';
$output[] = '</header>';

// Comments loading
$output[] = '<div id="comments_loading">';
$output[] = '<img src="'.URL::site('media/img/animated/loading15x128.gif').'" height="15" width="128" alt="loading comments ..."/>';
$output[] = 'Loading comments &hellip;';
$output[] = '</div>';

// AJAX comment template goes here
$output[] = '</section>';

echo implode(PHP_EOL, $output);
unset($output);
