<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/content/ajax/trackbacks

$output[] = '<section id="trackbacks" class="alpha omega grid_8">';
$output[] = '<header id ="trackbacks_hdr">';
$output[] = '<span>'.$header.'</span>';
if ( ! empty($trackback_url))
{
	$output[] = '<small>Trackback URL: '.$trackback_url.'</small>';
}
$output[] = '</header>';

$output[] = '<ol class="alpha omega push_1 grid_7">';
// AJAX trackback template goes here
$output[] = '</ol>';
$output[] = '</section>';

echo implode(PHP_EOL, $output);
unset($output);
