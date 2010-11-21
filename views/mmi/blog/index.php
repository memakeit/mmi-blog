<?php defined('SYSPATH') OR die('No direct access allowed.');

// mmi/blog/index

$output = array();
if ( ! empty($header))
{
	$output[] = '<header><h1>'.HTML::chars($header, FALSE).'</h1></header>';
}

if (count($posts) === 0)
{
	$output[] = '<p class="content hdr">';
	$output[] = 'There currently are not any articles in this section.';
	$output[] = '</p>';
}
else
{
	$route = Route::get('mmi/bookmark/hmvc')->uri(array
	(
		'action' 		=> MMI_Bookmark::MODE_PILL,
		'controller'	=> $bookmark_driver,
	));
	$toolbox = Request::factory($route);

	$i = 0;
	$last = count($posts) - 1;
	foreach ($posts as $post)
	{
		// Set content class
		$class = 'grid_8 alpha omega';
		if ($i === 0)
		{
			$class .= ' first';
		}
		if ($i === $last)
		{
			$class .= ' last';
		}
		if ( ! empty($class))
		{
			$class = ' class="'.trim($class).'"';
		}
		$i++;

		$author = MMI_Blog_User::format_user($post->author);
		$comment_count = $post->comment_count;
		$categories = $post->categories;
		$post_title = HTML::chars($post->title, FALSE);
		$slug = $post->slug;
		$tags = $post->tags;

		$post_date = $post->timestamp_created;
		$post_guid = $post->guid;

		// Begin article
		$output[] = '<article id="post_'.$post->id.'"'.$class.'>';

		// Begin header
		$link_title = 'jump to comments about '.HTML::chars($post_title, FALSE);
		$output[] = '<header class="grid_8 alpha omega">';
		$output[] = '<h2><a href="'.$post_guid.'" title="'.$post_title.'">'.Text::widont($post_title).'</a></h2>';
		$output[] = '<p>';
		$output[] = '<span class="comments grid_2 omega"><a href="'.$post_guid.'/#comments" title="'.$link_title.'">'.$comment_count.' '.Inflector::plural('comment', $comment_count).'</a></span>';
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
		$toolbox->post = array
		(
			'title'			=> $post_title,
			'url'			=> $post_guid,
		);
		$output[] = $toolbox->execute()->response;

		// End header
		$output[] = '</header>';

		// Begin section
		$output[] = '<section class="grid_8 alpha omega">';

		// Excerpt
		$paragraphs = MMI_Text::get_paragraphs($post->content, $excerpt_size);
		$output[] = MMI_Blog_Post::format_content($paragraphs, array
		(
			'image_header' => TRUE,
		));

		// Tags
		$output[] = '<div class="last">';
		if (count($tags) > 0)
		{
			$output[] = '<p class="grid_6 alpha">';
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

		// Read more
		$output[] = '<p class="grid_2 omega">';
		$link_title = 'read the article about '.HTML::chars($post_title, FALSE);
		$output[] = '<a href="'.$post_guid.'" rel="nofollow" title="'.$link_title.'"><strong>Read more</strong></a>';
		$output[] = '</p>';
		$output[] = '</div>';

		// End section
		$output[] = '</section>';

		// End article
		$output[] = '</article>';
	}

	if ( ! empty($pagination))
	{
		$output[] = $pagination;
	}
}
echo implode(PHP_EOL, $output);
unset($output);
