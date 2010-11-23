<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/content/comment_form

$output = array();
$output[] = '<section id="comment_form" class="grid_8 alpha omega">';
$output[] = '<header><h3>Leave a Comment</h3></header>';
if (empty($form))
{
	$output[] = '<div class="closed">Comments are closed for this article.</div>';
}
else
{
	$output[] = $form;
}
$output[] = '</section>';

echo implode(PHP_EOL, $output);
unset($output);
