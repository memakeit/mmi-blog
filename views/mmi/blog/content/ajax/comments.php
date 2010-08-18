<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/content/ajax/comments

$output[] = '<section id="comments" class="alpha omega grid_8">';

// Header
$header = 'Comments';
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

// AJAX comment template goes here

$output[] = '</section>';

echo implode(PHP_EOL, $output);
unset($output);
