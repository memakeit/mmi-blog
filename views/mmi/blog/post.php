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
	$output[] = '<span class="meta alpha grid_6">';
	$output[] = 'By '.$author;
	$output[] = ' on <time datetime="'.gmdate('c', $post_date).'" pubdate>'.gmdate('F j, Y', $post_date).'</time>';

	// Categories
	if (count($categories) > 0)
	{
		$temp = array();
		foreach ($categories as $category)
		{
			$cat_name = $category->name;
			$temp[] = HTML::anchor($category->guid, $cat_name, array('title' => 'articles for'.$cat_name));
		}
		$output[] = ' in: '.implode(', ', $temp);
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

//	// Figure
//	$output[] = '<figure>';
//	if (empty($img))
//	{
//		$output[] = '<img src="'.URL::site('media/img/icons/48px/Picture.png').'" alt="'.$post_title.'">';
//	}
//	else
//	{
//		$output[] = $img;
//	}
//	$output[] = '</figure>';



	// Content
//	if ( ! empty($excerpt))
//	{
//		$body = '<p>'.$excerpt.'</p>'.$body;
//	}
	$output[] = '<div class="content">';
	if ( ! empty($post_content))
	{
		if (is_bool($insert_retweet) AND $insert_retweet)
		{
			$last_paragraph = MMI_Blog_Post::get_last_paragraph($post_content);
			if ( ! empty($last_paragraph))
			{
				// Insert retweet
				$route = Route::get('mmi/social/hmvc')->uri(array
				(
					'action' 		=> 'tweet',
					'controller'	=> 'addthis'
				));
				$retweet = Request::factory($route);
				$retweet->post = array
				(
					'title'	=> $post_title,
					'url'	=> $post_guid,
				);

				$html = $last_paragraph[0];
				$inner = $last_paragraph[1];
				$retweet = $retweet->execute()->response;
				$post_content = str_replace($html, '', $post_content).'<p>'.$retweet.$inner.'</p>';
			}
		}
		$output[] = $post_content;
	}
	$output[] = '</div>';

//
//	// Prev next links
//	if ( ! empty($prev_next))
//	{
//		$output[] = $prev_next;
//	}
//

	// End article
	$output[] = '</article>';

	// Bookmarks
	if ( ! empty($bookmarks))
	{
		$output[] = $bookmarks;
	}

	// Comments
	if ( ! empty($comments))
	{
		$output[] = '<a name="comments"></a>';
		$output[] = $comments;
	}

	// Trackbacks
	if ( ! empty($trackbacks))
	{
		$output[] = $trackbacks;
	}
//
//	// Comment form
//	if ( ! empty($comment_form))
//	{
//		$output[] = '<a name="commentform"></a>';
//		$output[] = $comment_form;
//	}
}
echo implode(PHP_EOL, $output);
unset($output);
