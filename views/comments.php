<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php

$output = array();
if ( ! empty($title)) 
{
    $output[] = '<h1>'.HTML::chars($title, FALSE).'</h1>';
}
if ( ! empty($content))
{
    $output[] = $content;
}
$output[] = '<p><em>'.date('Y-m-d H:i:s').'</em></p>';

echo implode(PHP_EOL, $output);
unset($output);