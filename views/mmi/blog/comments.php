<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/comments

$num_comments = count($comments);
$output[] = '<section id="comments" class="alpha omega grid_8">';

// Header
$header = $num_comments.' '.ucfirst(Inflector::plural('Comment', $num_comments));
$output[] = '<header id="comments_hdr">';
if ( ! empty($feed_url))
{
	$output[] = HTML::anchor($feed_url, $header, array('title' => 'subscribe to this article\'s comments'));
}
else
{
	$output[] =  $header;
}
$output[] = '</header>';

// Comments
if ($num_comments > 0)
{
	$i = 0;
	$last = count($comments) - 1;
	foreach ($comments as $comment)
	{
		$class = 'comment';
		if ($i === 0)
		{
			$class .= ' first';
		}
		if ($i === $last)
		{
			$class .= ' last';
		}
		$class = ' class="'.$class.'"';
		$i++;

		$author = $comment->author;
		$author_email = $comment->author_email;
		$author_url = $comment->author_url;
		$timestamp = $comment->timestamp;

		$output[] = '<article id="comment-'.$comment->id.'"'.$class.'>';

		// Gravatar
		$gravatar_url = ( ! empty($author_email)) ? MMI_Gravatar::get_gravatar_url($author_email) : $default_img;
		$output[] = '<figure class="alpha grid_2">';
		$output[] = '<img src="'.$gravatar_url.'" alt="'.HTML::chars($author, FALSE).'" height="'.$default_img_size.'" width="'.$default_img_size.'" />';
		$output[] = '</figure>';

		// Header
		$output[] = '<header class="omega grid_6">';
		$output[] = 'By ';
		if (empty($author_url))
		{
			$output[] = HTML::chars($author, FALSE);
		}
		else
		{
			$output[] = HTML::anchor($author_url, HTML::chars($author, FALSE), array('rel' => 'external nofollow'));
		}
		$output[] = 'on <time datetime="'.gmdate('c', $timestamp).'" pubdate>'.gmdate('F j, Y @ g:i a', $timestamp).'</time>';
		$output[] = '</header>';

		// Content
		$output[] = Text::auto_p($comment->content);

		$output[] = '</article>';
	}
}
$output[] = '</section>';

// Pingbacks and trackbacks
if (is_array($trackbacks) AND count($trackbacks) > 0)
{
	$output[] = '<section id="trackbacks" class="alpha omega grid_8">';
	$output[] = '<header>';
	$output[] = '<span>Pingbacks &amp; Trackbacks</span>';
	if ( ! empty($trackback_url))
	{
		$output[] = '<small>Trackback URL: '.$trackback_url.'</small>';
	}
	$output[] = '</header>';
	$output[] = '<ol class="alpha omega push_1 grid_7">';
	foreach ($trackbacks as $comment)
	{
		$output[] = '<li>';
		$output[] = HTML::anchor($comment->author_url, $comment->author, array('rel' => 'external nofollow'));
		$output[] = '</li>';
	}
	$output[] = '</ol>';
	$output[] = '</section>';
}
echo implode(PHP_EOL, $output);
unset($output);
