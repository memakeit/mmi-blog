<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/content/comments

$output = array();
$num_comments = count($comments);
$output[] = '<section id="comments" class="grid_8 alpha omega">';

// Header
$class = (count($comments) === 0) ? ' class="zero"': '';
$output[] = '<header id="comments_hdr"'.$class.'>';
$output[] = '<h3>';
if ( ! empty($feed_url))
{
	$header .= '<span>subscribe</span>';
	$output[] = HTML::anchor($feed_url, $header, array('title' => 'subscribe to this article\'s comment feed'));
}
else
{
	$output[] = $header;
}
$output[] = '</h3>';
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
		if (empty($author_email))
		{
			$gravatar_url = $default_img;
		}
		else
		{
			$gravatar_url = MMI_Gravatar::get_gravatar_url($author_email);
		}
		$output[] = '<figure class="grid_2 alpha">';
		$output[] = '<img src="'.$gravatar_url.'" alt="'.HTML::chars($author, FALSE).'" height="'.$default_img_size.'" width="'.$default_img_size.'" />';
		$output[] = '</figure>';

		// Header
		$output[] = '<header class="grid_6 omega">';
		$output[] = 'By ';
		$author_url = $comment->author_url;
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

echo implode(PHP_EOL, $output);
unset($output);
