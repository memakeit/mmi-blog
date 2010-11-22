<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php

$output = array();
if (count($post) > 0)
{
	$author = MMI_Blog_User::format_user($post->author);
	$comment_count = $post->comment_count;
	$categories = $post->categories;
	$post_title = HTML::chars($post->title, FALSE);
	$slug = $post->slug;
	$tags = $post->tags;

	$post_date = $post->timestamp_created;
	$post_guid = $post->guid;

	// Begin article
	$output[] = '<article id="post_'.$post->id.'">';

	// Begin header
	$link_title = 'jump to comments about '.HTML::chars($post_title, FALSE);
	$output[] = '<header class="grid_8 alpha omega">';
	if ($is_homepage)
	{
		$output[] = '<h2>'.Text::widont(HTML::chars($post_title, FALSE)).'</h2>';
	}
	else
	{
		$output[] = '<h1>'.Text::widont(HTML::chars($post_title, FALSE)).'</h1>';
	}

	$output[] = '<p>';
	if ($ajax_comments)
	{
		$link_text = '';
	}
	else
	{
		$link_text = $comment_count.' '.Inflector::plural('comment', $comment_count);
	}
	$output[] = '<a href="#comments" id="comment_ct" title="'.$link_title.'">'.$link_text.'</a>';
	$output[] = '<span class="grid_6 alpha">';
	$output[] = 'By '.$author;
	$date = HTML::anchor($post->archive_guid, gmdate('F j, Y', $post_date), array('rel' => 'archive index', 'title' => 'articles for '.gmdate('F Y', $post_date)));
	$output[] = ' on <time datetime="'.gmdate('c', $post_date).'" pubdate>'.$date.'</time>';

	// Categories
	if (count($categories) > 0)
	{
		$temp = array();
		foreach ($categories as $category)
		{
			$cat_name = $category->name;
			$temp[] = HTML::anchor($category->guid, $cat_name, array('rel' => 'index tag', 'title' => 'articles categorized as '.$cat_name));
		}
		$output[] = ' in '.implode(', ', $temp);
	}
	$output[] = '</span>';
	$output[] = '</p>';

	// Toolbox
	if ( ! empty($toolbox))
	{
		$output[] = $toolbox;
	}

	// End header
	$output[] = '</header>';

	// Begin content
	$output[] = '<div class="post">';
	$paragraphs = MMI_Text::get_paragraphs($post->content);
	$output[] = MMI_Blog_Post::format_content($paragraphs, array
	(
		'bookmark_driver'	=> $bookmark_driver,
		'image_header'		=> TRUE,
		'insert_retweet'	=> TRUE,
		'title'				=> $post_title,
		'url'				=> $post_guid,
	));

	// Tags
	if (count($tags) > 0)
	{
		$output[] = '<p id="tags">';
		$output[] = '<strong>Tags:</strong>';
		$temp = array();
		foreach ($tags as $tag)
		{
			$tag_name = $tag->name;
			$temp[] = HTML::anchor($tag->guid, $tag_name, array('rel' => 'index tag', 'title' => 'articles tagged as '.$tag_name));
		}
		$output[] = implode(', ', $temp);
		$output[] = '</p>';
	}

	// End content
	$output[] = '</div>';

	// End article
	$output[] = '</article>';

	// Previous and next post links
	if ( ! empty($prev_next))
	{
		$output[] = $prev_next;
	}

	// Related post links
	if ( ! empty($related_posts))
	{
		$output[] = $related_posts;
	}

	// Bookmarks
	if ( ! empty($bookmarks))
	{
		$output[] = $bookmarks;
	}

	// Comment form
	if ( ! empty($comment_form))
	{
		$output[] = $comment_form;
	}

	// Comments
	if ( ! empty($comments))
	{
		$output[] = $comments;
	}

	// Trackbacks
	if ( ! empty($trackbacks))
	{
		$output[] = $trackbacks;
	}
}
echo implode(PHP_EOL, $output);
unset($output);
