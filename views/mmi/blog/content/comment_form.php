<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/content/comment_form

$output = array();
$output[] = '<section id="comment_form" class="alpha omega grid_8">';
$output[] = '<header>Leave a Comment</header>';
$output[] = $form;
$output[] = '</section>';

echo implode(PHP_EOL, $output);
unset($output);
