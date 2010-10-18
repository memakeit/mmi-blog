<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WordPress comment functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Kohana_MMI_Blog_Wordpress_Comment extends MMI_Blog_Comment
{
	// Type constants
	const TYPE_PINGBACK = 'pingback';
	const TYPE_TRACKBACK = 'trackback';

	/**
	 * @var string driver name
	 */
	protected static $_driver = MMI_Blog::DRIVER_WORDPRESS;

	/**
	 * @var array database mappings ($column => $alias) for the comments table
	 */
	protected static $_db_mappings = array
	(
		'comment_ID'			=> 'id',
		'comment_post_ID'		=> 'post_id',
		'comment_author'		=> 'author',
		'comment_author_email'	=> 'author_email',
		'comment_author_url'	=> 'author_url',
		'comment_author_IP'		=> 'author_ip',
		'comment_date'			=> 'meta_date',
		'comment_date_gmt'		=> 'timestamp',
		'comment_content'		=> 'content',
		'comment_karma'			=> 'meta_karma',
		'comment_approved'		=> 'approved',
		'comment_agent'			=> 'meta_agent',
		'comment_type'			=> 'type',
		'comment_parent'		=> 'parent_id',
		'user_id'				=> 'user_id',
	);

	/**
	 * @var array database mappings ($column => $alias) for the comments meta table
	 */
	protected static $_db_meta_mappings = array
	(
		'comment_id'	=> 'comment_id',
		'meta_key'		=> 'key',
		'meta_value'	=> 'value',
	);

	/**
	 * Check if a comment is already present for a post.
	 * If the author parameter is a string, it represents the author's name.
	 * If the author parameter is an array, the following keys can be used to
	 * specify author details: name, email, url.
	 *
	 * @param	integer	the post id
	 * @param	string	the content to check
	 * @param	array	the author details
	 * @param	string	the comment type (<empty string>|pingback|trackback)
	 * @return	boolean
	 */
	public function is_duplicate($post_id, $content, $author = NULL, $type = NULL)
	{
		return Model_WP_Comments::is_duplicate($post_id, $content, $author, $type);
	}

	/**
	 * Save the comment.
	 *
	 * @return	boolean
	 */
	public function save()
	{
		$mappings = array_flip(self::$_db_mappings);
		$data = array();
		$temp = (array) $this;
		foreach ($temp as $name => $value)
		{
			$name = Arr::get($mappings, $name);
			if (isset($name) AND isset($value))
			{
				$data[$name] = $value;
			}
		}

		$model = Jelly::factory('wp_comments')->set($data);
		$errors = array();
		$success = MMI_Jelly::save($model, $errors);
		return (count($errors) === 0);
	}

	/**
	 * Get recent comments.
	 *
	 * @param	boolean	include trackbacks?
	 * @param	integer	the maximum number of comments to return
	 * @param	boolean	reload cache from database?
	 * @return	array
	 */
	public function get_recent($include_trackbacks = FALSE, $max_num = 10, $reload_cache = TRUE)
	{
		$driver = self::$_driver;
		$config = MMI_Blog::get_config()->get('features', array());
		$load_gravatar = Arr::get($config, 'comment_gravatar', FALSE);
		$load_meta = Arr::get($config, 'comment_meta', FALSE);

		$comments = array();
		$data = Model_WP_Comments::recent_comments($include_trackbacks, self::$_db_mappings, TRUE, $max_num);
		foreach ($data as $id => $fields)
		{
			$comments[] = self::factory($driver)->_load($fields, $load_meta);
			$comments[$id]->driver = $driver;
		}
		if ($load_gravatar)
		{
			self::_load_gravatars($comments);
		}
		if ($load_meta)
		{
			self::_load_meta($comments);
		}
		return $comments;
	}

	/**
	 * Get comments. If no post id is specified, all comments are returned.
	 *
	 * @param	mixed	post id's being selected
	 * @param	boolean	reload cache from database?
	 * @return	array
	 */
	public function get_comments($post_ids = NULL, $reload_cache = TRUE)
	{
		$driver = self::$_driver;
		$config = MMI_Blog::get_config(TRUE);
		$cache_id = $this->_get_cache_id($driver, 'comments');
		$cache_lifetime = Arr::path($config, 'cache_lifetimes.comment', 0);
		$load_gravatar = Arr::path($config, 'features.comment_gravatar', FALSE);
		$load_meta = Arr::path($config, 'features.comment_meta', FALSE);

		$comments = NULL;
		if ( ! $reload_cache AND $cache_lifetime > 0)
		{
			$comments = MMI_Cache::get($cache_id, MMI_Cache::CACHE_TYPE_DATA, $cache_lifetime);
		}
		if ( ! isset($comments))
		{
			$data = Model_WP_Comments::select_comments_by_post_id(NULL, self::$_db_mappings);
			$comments = array();
			foreach ($data as $id => $fields)
			{
				$comments[] = self::factory($driver)->_load($fields, $load_meta);
				$comments[$id]->driver = $driver;
			}
			if ($load_gravatar)
			{
				self::_load_gravatars($comments);
			}
			if ($load_meta)
			{
				self::_load_meta($comments);
			}
			if ($cache_lifetime > 0)
			{
				MMI_Cache::set($cache_id, MMI_Cache::CACHE_TYPE_DATA, $comments, $cache_lifetime);
			}
		}
		return $this->_extract_results($comments, $post_ids, FALSE, 'id', 'post_id');
	}

	/**
	 * Get trackbacks. If no post id is specified, all trackbacks are returned.
	 *
	 * @param	mixed	post id's being selected
	 * @param	boolean	reload cache from database?
	 * @return	array
	 */
	public function get_trackbacks($post_ids = NULL, $reload_cache = TRUE)
	{
		$driver = self::$_driver;
		$config = MMI_Blog::get_config(TRUE);
		$cache_id = $this->_get_cache_id($driver, 'trackbacks');
		$cache_lifetime = Arr::path($config, 'cache_lifetimes.comment', 0);
		$load_meta = Arr::path($config, 'features.comment_meta', FALSE);

		$trackbacks = NULL;
		if ( ! $reload_cache AND $cache_lifetime > 0)
		{
			$trackbacks = MMI_Cache::get($cache_id, MMI_Cache::CACHE_TYPE_DATA, $cache_lifetime);
		}
		if ( ! isset($trackbacks))
		{
			$data = Model_WP_Comments::select_trackbacks_by_post_id(NULL, self::$_db_mappings);
			$trackbacks = array();
			foreach ($data as $id => $fields)
			{
				$trackbacks[] = self::factory($driver)->_load($fields, $load_meta);
				$trackbacks[$id]->driver = $driver;
			}
			if ($load_meta)
			{
				self::_load_meta($comments);
			}
			if ($cache_lifetime > 0)
			{
				MMI_Cache::set($cache_id, MMI_Cache::CACHE_TYPE_DATA, $trackbacks, $cache_lifetime);
			}
		}
		return $this->_extract_results($trackbacks, $post_ids, FALSE, 'id', 'post_id');
	}

	/**
	 * Get approved status.
	 *
	 * @param	integer	comment_approved?
	 * @return	boolean
	 */
	protected function _get_approved($value)
	{
		return (intval($value) === 1);
	}

	/**
	 * Get id.
	 *
	 * @param	string	id
	 * @return	integer
	 */
	protected function _get_id($value)
	{
		return intval($value);
	}

	/**
	 * Get parent id.
	 *
	 * @param	string	parent id
	 * @return	integer
	 */
	protected function _get_parent_id($value)
	{
		return intval($value);
	}

	/**
	 * Get post id.
	 *
	 * @param	string	post id
	 * @return	integer
	 */
	protected function _get_post_id($value)
	{
		return intval($value);
	}

	/**
	 * Get timestamp.
	 *
	 * @param	string	date
	 * @return	integer
	 */
	protected function _get_timestamp($value)
	{
		return strtotime($value);
	}

	/**
	 * If user id is specified, load the user settings (display name, email and url).
	 *
	 * @param	string	user id
	 * @return	integer
	 */
	protected function _get_user_id($value)
	{
		$value = intval($value);
		if ($value > 0)
		{
			$users = MMI_Blog_User::factory(self::$_driver)->get_users($value);
			$user = Arr::get($users, $value);
			if ( ! empty($user))
			{
				$this->author = $user->display_name;
				$this->author_email = $user->email;
				$this->author_url = $user->url;
			}
		}
		return $value;
	}

	/**
	 * Load the comment gravatar URLs.
	 *
	 * @param	array	array of blog comment objects
	 * @return	void
	 */
	protected static function _load_gravatars($comments)
	{
		if (is_array($comments) AND count($comments) > 0)
		{
			foreach ($comments as $idx => $comment)
			{
				$author_email = $comment->author_email;
				if ( ! empty($author_email))
				{
					$comment->gravatar_url = MMI_Gravatar::get_gravatar_url($author_email);
					$comments[$idx] = $comment;
				}
			}
		}
	}

	/**
	 * Load the comment metadata.
	 *
	 * @param	array	array of blog comment objects
	 * @return	void
	 */
	protected static function _load_meta($comments)
	{
		$ids = array();
		foreach ($comments as $comment)
		{
			$ids[] = $comment->id;
		}
		$meta = Model_WP_CommentMeta::select_by_comment_id($ids, self::$_db_meta_mappings);

		$old_id = -1;
		$item_meta = array();
		foreach ($meta as $item)
		{
			$current_id = intval($item['comment_id']);
			if ($current_id !== $old_id)
			{
				if ($old_id > -1 AND count($item_meta > 0))
				{
					$comments[$old_id]->meta = $item_meta;
				}
				$item_meta = $comments[$current_id]->meta;
				$old_id = $current_id;
			}
			$item_meta[Arr::get($item, 'key')] = Arr::get($item, 'value');
		}
		if ($old_id > -1 AND count($item_meta > 0))
		{
			$comments[$old_id]->meta = $item_meta;
		}
	}
} // End Kohana_MMI_Blog_Wordpress_Comment
