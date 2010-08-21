<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/content/ajax/comments

$output[] = '<section id="comments" class="alpha omega grid_8">';

// Header
$output[] = '<header id="comments_hdr">';
if ( ! empty($feed_url))
{
	$output[] = HTML::anchor($feed_url, $header, array('title' => 'subscribe to this article\'s comment feed'));
}
else
{
	$output[] =  $header;
}
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
