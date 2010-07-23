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
	// Abstract methods
	abstract public function get_categories($ids = NULL, $reload_cache = FALSE);
	abstract public function get_tags($ids = NULL, $reload_cache = FALSE);

	// Class constants
	const TYPE_CATEGORY = 'category';
	const TYPE_TAG = 'tag';

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
	 * Create a term instance.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	type of post to create
	 * @return	MMI_Blog_Term
	 */
	public static function factory($type = MMI_Blog::BLOG_WORDPRESS)
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
