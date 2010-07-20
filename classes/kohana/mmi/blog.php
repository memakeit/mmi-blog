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
	 * @param	integer	the 4-digit year
	 * @param	integer	the 2-digit month
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
	 * Get the configuration settings.
	 *
	 * @param	boolean	return the configuration as an array?
	 * @return	mixed
	 */
	public static function get_config($as_array = FALSE)
	{
		(self::$_config === NULL) AND self::$_config = Kohana::config('blog');
		$config = self::$_config;
		if ($as_array)
		{
			$config = $config->as_array();
		}
		return $config;
	}
} // End Kohana_MMI_Blog
