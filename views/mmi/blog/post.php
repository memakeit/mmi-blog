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
	$post_content = $content = Text::auto_p($post->content);
//	MMI_Blog_Post::parse_content($post, $excerpt, $img, $body, FALSE);

	// Begin article
	$output[] = '<article id="post_'.$post->id.'">';

	// Begin header
	$link_title = 'jump to comments about '.HTML::chars($post_title, FALSE);
	$output[] = '<header class="alpha omega grid_8">';
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
	$output[] = '<span class="alpha grid_6">';
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
	$output[] = '<div class="content">';
	if ( ! empty($post_content))
	{
		if (is_bool($insert_retweet) AND $insert_retweet)
		{
			$last_paragraph = MMI_Text::get_ending_paragraphs($post_content, 1);
			if (is_array($last_paragraph))
			{
				// Insert retweet
				$route = Route::get('mmi/bookmark/hmvc')->uri(array
				(
					'action' 		=> MMI_Bookmark_AddThis::MODE_TWEET,
					'controller'	=> MMI_Bookmark::SERVICE_ADDTHIS,
				));
				$retweet = Request::factory($route);
				$retweet->post = array
				(
					'title'	=> $post_title,
					'url'	=> $post_guid,
				);

				$last_paragraph = $last_paragraph[0];
				$html = $last_paragraph['html'];
				$inner = $last_paragraph['inner'];
				$retweet = $retweet->execute()->response;
				$post_content = str_replace($html, '', $post_content).'<div>'.$retweet.$inner.'</div>';
			}
		}
		$output[] = $post_content;
	}

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

//	// Comment form
//	if ( ! empty($comment_form))
//	{
//		$output[] = '<a name="commentform"></a>';
//		$output[] = $comment_form;
//	}
}
echo implode(PHP_EOL, $output);
unset($output);
