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
	// Driver types
	const DRIVER_WORDPRESS = 'wordpress';

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
	 * @var Kohana_Config the blog feed settings
	 **/
	protected static $_feed_config;

	/**
	 * @var Kohana_Config the blog post settings
	 **/
	protected static $_post_config;

	/**
	 * Get a blog guid.
	 *
	 * @access	public
	 * @param	integer	the page number
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_guid($page = 1, $absolute = TRUE)
	{
		$params = array();
		if (intval($page) > 1)
		{
			$params['page'] = $page;
		}
		$url = Route::get('mmi/blog/index')->uri($params);
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
	 * @access	public
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
	 * @access	public
	 * @param	mixed	the navigation-type parameters
	 * @return	void
	 */
	public static function set_nav_type($nav_type)
	{
		if ( ! empty($nav_type))
		{
			$nav_type = json_encode($nav_type);
		}
		Cookie::set('mmi-bnav', $nav_type, 30 * Date::DAY);
	}

	/**
	 * Does the cache need to be reloaded from the database?
	 *
	 * @access	public
	 * @return	boolean
	 */
	public static function reload_cache()
	{
		return (Kohana::$environment !== Kohana::PRODUCTION);
	}

	/**
	 * Get the configuration settings.
	 *
	 * @access	public
	 * @param	boolean	return the configuration as an array?
	 * @return	mixed
	 */
	public static function get_config($as_array = FALSE)
	{
		(self::$_config === NULL) AND self::$_config = Kohana::config('mmi-blog');
		if ($as_array)
		{
			return self::$_config->as_array();
		}
		return self::$_config;
	}

	/**
	 * Get the feed configuration settings.
	 *
	 * @access	public
	 * @param	boolean	return the configuration as an array?
	 * @return	mixed
	 */
	public static function get_feed_config($as_array = FALSE)
	{
		(self::$_feed_config === NULL) AND self::$_feed_config = Kohana::config('mmi-blog-feed');
		if ($as_array)
		{
			return self::$_feed_config->as_array();
		}
		return self::$_feed_config;
	}

	/**
	 * Get the post configuration settings.
	 *
	 * @access	public
	 * @param	boolean	return the configuration as an array?
	 * @return	mixed
	 */
	public static function get_post_config($as_array = FALSE)
	{
		(self::$_post_config === NULL) AND self::$_post_config = Kohana::config('mmi-blog-post');
		if ($as_array)
		{
			return self::$_post_config->as_array();
		}
		return self::$_post_config;
	}
} // End Kohana_MMI_Blog
