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
	MMI_Blog_Post::parse_content($post, $excerpt, $img, $body, TRUE);

	if ($is_homepage)
	{
		$output[] = '<h2>'.Text::widont(HTML::chars($post_title, FALSE)).'</h2>';
	}
	else
	{
		$output[] = '<h1>'.Text::widont(HTML::chars($post_title, FALSE)).'</h1>';
	}

	// Begin intro
//	$output[] = '<div class="content first">';
//	$output[] = '<div id="intro">';
//
//	// Image
//	if ( ! empty($img))
//	{
//		$output[] = '<p id="img">'.$img.'</p>';
//	}

	// Toolbox
	if ( ! empty($toolbox))
	{
		$output[] = $toolbox;
	}

//	// Author
//	$author = blog::get_author($post->author_name);
//	$output[] = '<p id="author">';
//	$output[] = '<strong>By:</strong> <span>'.$author.'</span>';
//	$output[] = '</p>';

//	// Categories
//	if (count($category_links) > 0)
//	{
//		$output[] = '<p id="categories">';
//		$output[] = '<strong>Posted in:</strong>';
//		$output[] = '<span class="links">';
//		$output[] = implode(', ', $category_links);
//		$output[] = '</span>';
//		$output[] = '</p>';
//	}
//
//	// Excerpt
//	if ( ! empty($excerpt))
//	{
//		$output[] = '<div id="excerpt" class="wp_content">'.$excerpt.'</div>';
//	}
//
//	// Begin meta
//	$output[] = '<div id="meta">';
//
//	// Calendar
//	$post_date = $post->timestamp_created;
//	$output[] = '<div id="cal">';
//	$output[] = '<div class="date">'.date('j', $post_date).'</div>';
//	$output[] = '<div class="month">'.date('M Y', $post_date).'</div>';
//	$output[] = '</div>';
//
//	// Comments
//	$comment_url = '#comments';
//	if ($use_full_comment_url)
//	{
//		$slug = $post->slug;
//		$comment_url = MMI_Blog::get_post_guid($year, $month, $slug).'/#comments';
//	}
//	$link_title = 'jump to comments about '.HTML::chars($post_title, FALSE);
//	$output[] = '<div id="comment_ct">';
//	$output[] = '<a class="comment" href="'.$comment_url.'" title="'.$link_title.'">comments</a>';
//	$output[] = '<a class="count" href="'.$comment_url.'" title="'.$link_title.'">'.$post['comment_count'].'</a>';
//	$output[] = '</div>';
//
//	// End meta
//	$output[] = '</div>';
//
//	// End intro
//	$output[] = '</div>';
//	$output[] = '</div>';
//
//	// Body
//	if ( ! empty($body))
//	{
//		$last_html = '';
//		$last_paragraph = MMI_Blog_Post::get_last_paragraph($body);
//
//		$last_class = 'wp_content';
//		if (empty($prev_next) OR ( ! empty($prev_next) AND trim($prev_next->render()) === ''))
//		{
//			$last_class .= ' wp_last';
//		}
//		elseif ( ! empty($retweet))
//		{
//			$last_class .= ' wp_retweet';
//		}
//
//		if ( ! empty($last_paragraph))
//		{
//			if (empty($retweet))
//			{
//				$retweet  = '';
//			}
//
//			// Insert retweet
//			$last_html = $last_paragraph[0];
//			$last_inner = $last_paragraph[1];
//			$temp = '<div class="'.$last_class.'">'.$retweet.$last_inner.'</div>';
//			$body = str_replace($last_html, '', $body);
//			$last_html = $temp;
//		}
//
//		if (empty($last_html))
//		{
//			$output[] = '<div class="'.$last_class.'">'.$body.'</div>';
//		}
//		else
//		{
//			$output[] = '<div class="wp_content">'.$body.'</div>';
//			$output[] = $last_html;
//		}
//	}
//
//	// Prev next links
//	if ( ! empty($prev_next))
//	{
//		$output[] = $prev_next;
//	}
//
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
