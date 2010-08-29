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
	$route = Route::get('mmi/social/hmvc')->uri(array
	(
		'action' 		=> 'mini',
		'controller'	=> 'addthis'
	));
	$toolbox = Request::factory($route);

	$i = 0;
	$last = count($posts) - 1;
	foreach ($posts as $post)
	{
		// Set content class
		$class = 'alpha omega grid_8';
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
		MMI_Blog_Post::parse_content($post, $excerpt, $img, $body, TRUE);

		// Begin article
		$output[] = '<article id="post_'.$post->id.'"'.$class.'>';

		// Begin header
		$link_title = 'jump to comments about '.HTML::chars($post_title, FALSE);
		$output[] = '<header class="alpha omega grid_8">';
		$output[] = '<h2><a href="'.$post_guid.'" title="'.$post_title.'">'.Text::widont($post_title).'</a></h2>';
		$output[] = '<p>';
		$output[] = '<span class="comments omega grid_2"><a href="'.$post_guid.'/#comments" title="'.$link_title.'">'.$comment_count.' '.Inflector::plural('comment', $comment_count).'</a></span>';
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

		// End header
		$output[] = '</header>';

		// Figure
		$output[] = '<figure class="alpha grid_3">';
		if (empty($img))
		{
			$output[] = '<img src="'.URL::site('media/img/icons/48px/Picture.png').'" alt="'.$post_title.'">';
		}
		else
		{
			$output[] = $img;
		}
		$output[] = '</figure>';

		// Begin section
		$output[] = '<section class="omega grid_5">';
		if ( ! empty($excerpt))
		{
			$output[] = '<p>'.$excerpt.'</p>';
		}

		// Tags
		if (count($tags) > 0)
		{
			$output[] = '<p class="tags">';
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

		// Toolbox
		$output[] = '<div class="last">';
		$toolbox->post = array
		(
			'description'	=> $excerpt,
			'title'			=> $post_title,
			'url'			=> $post_guid,
		);
		$output[] = $toolbox->execute()->response;

		// Read more
		$link_title = 'read the article about '.HTML::chars($post_title, FALSE);
		$output[] = '<a class="more" href="'.$post_guid.'" rel="nofollow" title="'.$link_title.'"><strong>Read more</strong></a>';
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
