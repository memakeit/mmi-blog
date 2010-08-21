<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/content/comments

$num_comments = count($comments);
$output = array();
$output[] = '<section id="comments" class="alpha omega grid_8">';

// Header
$class = (count($comments) === 0) ? ' class="zero"': '';
$output[] = '<header id="comments_hdr"'.$class.'>';
if ( ! empty($feed_url))
{
	$output[] = HTML::anchor($feed_url, $header, array('title' => 'subscribe to this article\'s comment feed'));
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
		if (empty($author_email))
		{
			$gravatar_url = $default_img;
		}
		else
		{
			$gravatar_url = MMI_Gravatar::get_gravatar_url($author_email);
		}
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

echo implode(PHP_EOL, $output);
unset($output);
