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
	public static function get_guid($page = 1, $absolute = TRUE)
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
	 * Load the navigation-type parameters from a cookie.
	 * The following types of navigation are supported:
	 * 		archive		array('archive', $slug)
	 * 		category	array('category', $slug)
	 * 		index		''
	 * 		tag			array('tag', $slug)
	 *
	 * @return	mixed
	 */
	public static function get_nav_type()
	{
		$nav_type = Cookie::get('mmi-bnav', '');
		if ( ! empty($nav_type))
		{
			$nav_type = json_decode($nav_type, TRUE);
		}
		return $nav_type;
	}

	/**
	 * Save the navigation-type parameters to a cookie.
	 * The following types of navigation are supported:
	 * 		archive		array('archive', $slug)
	 * 		category	array('category', $slug)
	 * 		index		''
	 * 		tag			array('tag', $slug)
	 *
	 * @param	mixed	the navigation-type parameters
	 * @return	void
	 */
	public static function set_nav_type($nav_type)
	{
		if( ! empty($nav_type))
		{
			$nav_type = json_encode($nav_type);
		}
		Cookie::set('mmi-bnav', $nav_type, 30 * Date::DAY);
	}

	/**
	 * Does the cache need to be reloaded from the database?
	 *
	 * @return	boolean
	 */
	public static function reload_cache()
	{
		return (Kohana::$environment !== Kohana::PRODUCTION);
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
