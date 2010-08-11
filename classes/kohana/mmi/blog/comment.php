<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog comment functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
abstract class Kohana_MMI_Blog_Comment extends MMI_Blog_Core
{
	// Abstract methods
	abstract public function get_comments($post_ids = NULL, $reload_cache = NULL);

	// Type constants
	const TYPE_PINGBACK = 'pingback';
	const TYPE_TRACKBACK = 'trackback';

	/**
	 * @var boolean comment approved?
	 */
	public $approved;

	/**
	 * @var string comment author
	 */
	public $author;

	/**
	 * @var string comment author's email
	 */
	public $author_email;

	/**
	 * @var string comment author's URL
	 */
	public $author_url;

	/**
	 * @var string comment content
	 */
	public $content;

	/**
	 * @var string gravatar url
	 */
	public $gravatar_url;

	/**
	 * @var integer comment id
	 */
	public $id;

	/**
	 * @var array comment metadata
	 */
	public $meta = array();

	/**
	 * @var integer parent comment id
	 */
	public $parent_id;

	/**
	 * @var integer post id
	 */
	public $post_id;

	/**
	 * @var integer comment timestamp
	 */
	public $timestamp;

	/**
	 * @var string comment type
	 */
	public $type;

	/**
	 * Separate user comments from pingbacks and trackbacks.
	 *
	 * @param	array	user comments
	 * @param	array	pingbacks and trackbacks
	 * @return	void
	 */
	public static function separate_trackbacks(& $comments, & $trackbacks)
	{
		$trackbacks = array();
		if (empty($comments))
		{
			return;
		}

		foreach ($comments as $id => $comment)
		{
			$type = $comment->type;
			if ($type === self::TYPE_PINGBACK OR $type === self::TYPE_TRACKBACK)
			{
				$trackbacks[$id] = $comment;
				unset($comments[$id]);
			}
		}
	}

	/**
	 * Create a comment instance.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	type of post to create
	 * @return	MMI_Blog_Post
	 */
	public static function factory($driver = MMI_Blog::DRIVER_WORDPRESS)
	{
		$class = 'MMI_Blog_'.ucfirst($driver).'_Comment';
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
} // End Kohana_MMI_Blog_Comment
