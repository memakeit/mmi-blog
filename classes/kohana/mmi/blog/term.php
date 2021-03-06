<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog term functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
abstract class Kohana_MMI_Blog_Term extends MMI_Blog_Core
{
	// Class constants
	const TYPE_CATEGORY = 'category';
	const TYPE_TAG = 'tag';

	// Abstract methods
	abstract public function get_categories($ids = NULL, $reload_cache = NULL);
	abstract public function get_categories_by_slug($slugs = NULL, $reload_cache = NULL);
	abstract public function get_category_frequencies($reload_cache = NULL);
	abstract public function get_tags($ids = NULL, $reload_cache = NULL);
	abstract public function get_tags_by_slug($slugs = NULL, $reload_cache = NULL);
	abstract public function get_tag_frequencies($reload_cache = NULL);

	/**
	 * @var string the blog driver
	 **/
	public $driver;

	/**
	 * @var string post guid
	 */
	public $guid;

	/**
	 * @var integer term id
	 */
	public $id;

	/**
	 * @var array term metadata
	 */
	public $meta = array();

	/**
	 * @var string term name
	 */
	public $name;

	/**
	 * @var array term post ids
	 */
	public $post_ids;

	/**
	 * @var string term name
	 */
	public $slug;

	/**
	 * Get a category guid.
	 *
	 * @access	public
	 * @param	string	the category slug
	 * @param	integer	the page number
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_category_guid($slug, $page = 1, $absolute = TRUE)
	{
		$params = array('slug' => URL::title($slug));
		if (intval($page) > 1)
		{
			$params['page'] = $page;
		}
		$url = Route::get('mmi/blog/category')->uri($params);
		return URL::site($url, $absolute);
	}

	/**
	 * Get a tag guid.
	 *
	 * @access	public
	 * @param	string	the tag slug
	 * @param	integer	the page number
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_tag_guid($slug, $page = 1, $absolute = TRUE)
	{
		$params = array('slug' => URL::title($slug));
		if (intval($page) > 1)
		{
			$params['page'] = $page;
		}
		$url = Route::get('mmi/blog/tag')->uri($params);
		return URL::site($url, $absolute);
	}

	/**
	 * Create a term instance.
	 *
	 * @access	public
	 * @throws	Kohana_Exception
	 * @param	string	type of post to create
	 * @return	MMI_Blog_Term
	 */
	public static function factory($type = MMI_Blog::DRIVER_WORDPRESS)
	{
		$class = 'MMI_Blog_'.ucfirst($type).'_Term';
		if ( ! class_exists($class))
		{
			MMI_Log::log_error(__METHOD__, __LINE__, $class.' class does not exist');
			throw new Kohana_Exception(':class class does not exist in :method.', array
			(
				':class'	=> $class,
				':method'	=> __METHOD__
			));
		}
		return new $class;
	}
} // End Kohana_MMI_Blog_Term
