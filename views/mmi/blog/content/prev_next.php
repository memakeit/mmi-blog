<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/content/prev_next

$output = array();
$output[] = '<div id="prev_next">';
if ( ! empty($prev))
{
	$title = HTML::chars($prev['title']);
	$output[] = '<div id="prev_post">';
	$output[] = HTML::anchor($prev['url'], 'Previous', array
	(
		'rel'	=> 'nofollow',
		'title'	=> $title,
	));
	$output[] = '<small>'.$title.'</small>';
	$output[] = '</div>';
}

if ( ! empty($next))
{
	$title = HTML::chars($next['title']);
	$output[] = '<div id="next_post">';
	$output[] = HTML::anchor($next['url'], 'Next', array
	(
		'rel'	=> 'nofollow',
		'title'	=> $title,
	));
	$output[] = '<small>'.$title.'</small>';
	$output[] = '</div>';
}
$output[] = '</div>';
echo implode(PHP_EOL, $output);
unset($output);
