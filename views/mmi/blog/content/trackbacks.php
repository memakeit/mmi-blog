<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/content/trackbacks

$output[] = '<section id="trackbacks" class="alpha omega grid_8">';
$output[] = '<header id="trackbacks_hdr">';
$output[] = '<span>'.$header.'</span>';
if ( ! empty($trackback_url))
{
	$output[] = '<small>Trackback URL: '.$trackback_url.'</small>';
}
$output[] = '</header>';

$output[] = '<ol class="alpha omega push_1 grid_7">';
if (is_array($trackbacks) AND count($trackbacks) > 0)
{
	foreach ($trackbacks as $trackback)
	{
		$output[] = '<li>';
		$output[] = HTML::anchor($trackback->author_url, $trackback->author, array('rel' => 'external nofollow'));
		$output[] = '</li>';
	}
}
$output[] = '</ol>';
$output[] = '</section>';

echo implode(PHP_EOL, $output);
unset($output);
