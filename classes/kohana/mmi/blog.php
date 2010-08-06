<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog helper functions.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Kohana_MMI_Blog
{
	// Blog types
	const BLOG_WORDPRESS = 'wordpress';

	// Navigation types
	const NAV_ARCHIVE = 'archive';
	const NAV_CATEGORY = 'category';
	const NAV_DEFAULT = 'default';
	const NAV_TAG = 'tag';

	/**
	 * @var Kohana_Config blog settings
	 */
	protected static $_config;

	/**
	 * @var Kohana_Config the blog index settings
	 **/
	protected static $_index_config;

	/**
	 * @var Kohana_Config the blog post settings
	 **/
	protected static $_post_config;

	/**
	 * Get a blog guid.
	 *
	 * @param	integer	the page number
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_blog_guid($page = 1, $absolute = TRUE)
	{
		$parms = array();
		if (intval($page) > 1)
		{
			$parms['page'] = $page;
		}
		$url = Route::get('blog/index')->uri($parms);
		if ($absolute)
		{
			$url = URL::site($url, TRUE);
		}
		return $url;
	}

	/**
	 * Get a post guid.
	 *
	 * @param	integer	the 4-digit year
	 * @param	integer	the 2-digit month
	 * @param	string	the page slug
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_post_guid($year, $month, $slug, $absolute = TRUE)
	{
		$parms = array
		(
			'year'	=> $year,
			'month'	=> str_pad($month, 2, '0', STR_PAD_LEFT),
			'slug'	=> URL::title($slug),
		);
		$url = Route::get('blog/post')->uri($parms);
		if ($absolute)
		{
			$url = URL::site($url, TRUE);
		}
		return $url;
	}

	/**
	 * Get an archive guid.
	 *
	 * @param	integer	the year
	 * @param	integer	the month
	 * @param	integer	the page number
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_archive_guid($year, $month, $page = 1, $absolute = TRUE)
	{
		$parms = array
		(
			'year' => $year,
			'month' => str_pad($month, 2, '0', STR_PAD_LEFT),
		);
		if (intval($page) > 1)
		{
			$parms['page'] = $page;
		}
		$url = Route::get('blog/archive')->uri($parms);
		if ($absolute)
		{
			$url = URL::site($url, TRUE);
		}
		return $url;
	}

	/**
	 * Get a category guid.
	 *
	 * @param	string	the category slug
	 * @param	integer	the page number
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_category_guid($slug, $page = 1, $absolute = TRUE)
	{
		$parms = array('slug' => URL::title($slug));
		if (intval($page) > 1)
		{
			$parms['page'] = $page;
		}
		$url = Route::get('blog/category')->uri($parms);
		if ($absolute)
		{
			$url = URL::site($url, TRUE);
		}
		return $url;
	}

	/**
	 * Get a tag guid.
	 *
	 * @param	string	the tag slug
	 * @param	integer	the page number
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_tag_guid($slug, $page = 1, $absolute = TRUE)
	{
		$parms = array('slug' => URL::title($slug));
		if (intval($page) > 1)
		{
			$parms['page'] = $page;
		}
		$url = Route::get('blog/tag')->uri($parms);
		if ($absolute)
		{
			$url = URL::site($url, TRUE);
		}
		return $url;
	}

	/**
	 * Get a trackback guid.
	 *
	 * @param	integer	the 4-digit year
	 * @param	integer	the 2-digit month
	 * @param	string	the page slug
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_trackback_guid($year, $month, $slug, $absolute = TRUE)
	{
		$parms = array
		(
			'year'	=> $year,
			'month'	=> str_pad($month, 2, '0', STR_PAD_LEFT),
			'slug'	=> URL::title($slug),
		);
		$url = Route::get('blog/trackback')->uri($parms);
		if ($absolute)
		{
			$url = URL::site($url, TRUE);
		}
		return $url;
	}

	/**
	 * Get a feed guid.
	 *
	 * @param	integer	the 4-digit year
	 * @param	integer	the 2-digit month
	 * @param	string	the page slug
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_feed_guid($year, $month, $slug, $absolute = TRUE)
	{
		$parms = array
		(
			'year'	=> $year,
			'month'	=> str_pad($month, 2, '0', STR_PAD_LEFT),
			'slug'	=> URL::title($slug),
		);
		$url = Route::get('blog/feed')->uri($parms);
		if ($absolute)
		{
			$url = URL::site($url, TRUE);
		}
		return $url;
	}

	/**
	 * Parse the post content, extracting (and removing from the body)
	 * an excerpt and initial image if present.
	 *
	 * @param	MMI_Blog_Post	the blog post
	 * @param	string			the post excerpt
	 * @param	string			the initial image
	 * @param	string			the post body (with the excerpt and initial image removed)
	 * @return	void
	 */
	public static function parse_content($post, & $excerpt, & $img, & $body, $use_excerpt = TRUE)
	{
		$body = NULL;
		$excerpt = $post->excerpt;
		$img = NULL;

		$content = Text::auto_p($post->content);
		$content = str_replace(array("\n", "\r"), '', $content);

		// Extract the first paragraph
		$find = '';
		$first_paragraph = self::get_first_paragraph($content);
		if ( ! empty($first_paragraph))
		{
			$find = $first_paragraph[0];
			$first_paragraph = $first_paragraph[1];
		}

		if ( ! empty($first_paragraph))
		{
			// Check for an initial image
			$img_parts;
			if (stripos($first_paragraph, '<img ') === 0)
			{
				$img = $first_paragraph;
			}
			elseif (preg_match('/<a[^>]*><img[^>](.*?)\/><\/a>/i', $first_paragraph, $img_parts) === 1)
			{
				$img = $img_parts[0];
			}

			if ( ! empty($img))
			{
				// Remove the initial image from the content
				$content = str_ireplace($find, '', $content);

				// Extract the first paragraph
				$first_paragraph = self::get_first_paragraph($content);
				if ( ! empty($first_paragraph))
				{
					$find = $first_paragraph[0];
					$first_paragraph = $first_paragraph[1];
				}
			}
		}

		// Set the excerpt
		if (empty($excerpt) AND ! empty($first_paragraph))
		{
			// Remove the excerpt from the content
			$content = str_ireplace($find, '', $content);
			$excerpt = $first_paragraph;
		}
		$body = $content;
	}







	public static function get_first_paragraph($content)
	{
		$content = str_replace(array("\n", "\r"), '', $content);
		$first_paragraph = NULL;
		if (preg_match('/<p[^>]*>(.*?)<\/p>/i', $content, $first_paragraph) === 0)
		{
			$first_paragraph = NULL;
		}
		return $first_paragraph;
	}

	public static function get_last_paragraph($content)
	{
		$content = str_replace(array("\n", "\r"), '', $content);
		$last_paragraph = NULL;
		$matches;
		$num_matches = preg_match_all('/<p[^>]*>(.*?)<\/p>/i', $content, $matches);
		if ($num_matches > 0)
		{
			$idx = $num_matches - 1;
			$last_paragraph = array
			(
				$matches[0][$idx],
				$matches[1][$idx]
			);
		}
		else
		{
			$last_paragraph = NULL;
		}
		return $last_paragraph;
	}

	public static function get_pagination($total_count)
	{
		$config = Kohana::config('pagination.blog');
		$config['total_items'] = $total_count;
		return Pagination::factory($config);
	}

	public static function format_author($user)
	{
		$author = $user->display_name;
		$url = $user->url;
		if ( ! empty($url))
		{
			$author = HTML::anchor($url, HTML::chars($author, FALSE), array('title' => $author));
		}
		else
		{
			$author = HTML::chars($author, FALSE);
		}
		return $author;
	}

	public static function get_nav_type()
	{
		$nav_type = Cookie::get('mmi-bnav', '');
		if ( ! empty($nav_type))
		{
			$nav_type = json_decode($nav_type, TRUE);
		}
		return $nav_type;
	}

	public static function set_nav_type($nav_type)
	{
		if( ! empty($nav_type))
		{
			$nav_type = json_encode($nav_type);
		}
		Cookie::set('mmi-bnav', $nav_type, 30 * Date::DAY);
	}

	/**
	 * Get the configuration settings.
	 *
	 * @param	boolean	return the configuration as an array?
	 * @return	mixed
	 */
	public static function get_config($as_array = FALSE)
	{
		(self::$_config === NULL) AND self::$_config = Kohana::config('mmi-blog');
		$config = self::$_config;
		if ($as_array)
		{
			$config = $config->as_array();
		}
		return $config;
	}

	/**
	 * Get the index configuration settings.
	 *
	 * @param	boolean	return the configuration as an array?
	 * @return	mixed
	 */
	public static function get_index_config($as_array = FALSE)
	{
		(self::$_index_config === NULL) AND self::$_index_config = Kohana::config('mmi-blog-index');
		$config = self::$_index_config;
		if ($as_array)
		{
			$config = $config->as_array();
		}
		return $config;
	}

	/**
	 * Get the post configuration settings.
	 *
	 * @param	boolean	return the configuration as an array?
	 * @return	mixed
	 */
	public static function get_post_config($as_array = FALSE)
	{
		(self::$_post_config === NULL) AND self::$_post_config = Kohana::config('mmi-blog-post');
		$config = self::$_post_config;
		if ($as_array)
		{
			$config = $config->as_array();
		}
		return $config;
	}
} // End Kohana_MMI_Blog
