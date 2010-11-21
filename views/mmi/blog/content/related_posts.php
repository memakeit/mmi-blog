<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/content/related_posts

$output = array();
if ( ! empty($related))
{
	$output[] = '<section id="related_posts" class="grid_8 alpha omega">';
	$output[] = '<header>Possibly Related Articles</header>';
	$output[] = '<ul class="push_1 grid_7 alpha omega">';
	foreach ($related as $item)
	{
		$output[] = '<li>'.HTML::anchor($item['guid'], $item['title'], array('rel'	=> 'nofollow')).'</li>';
	}
	$output[] = '</ul>';
	$output[] = '</section>';
}
echo implode(PHP_EOL, $output);
unset($output);
