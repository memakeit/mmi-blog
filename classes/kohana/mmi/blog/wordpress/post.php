<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog post functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Kohana_MMI_Blog_Wordpress_Post extends MMI_Blog_Post
{
	/**
	 * @var string driver name
	 */
	protected static $_driver = MMI_Blog::BLOG_WORDPRESS;

	/**
	 * @var array database mappings ($column => $alias) for the comments table
	 */
	protected static $_db_mappings = array
	(
		'ID'					=> 'id',
		'post_author'			=> 'author_id',
		'post_date'				=> 'meta_post_date',
		'post_date_gmt'			=> 'timestamp_created',
		'post_content'			=> 'content',
		'post_title'			=> 'title',
		'post_excerpt'			=> 'excerpt',
		'post_status'			=> 'status',
		'comment_status'		=> 'comment_status',
		'ping_status'			=> 'meta_ping_status',
		'post_password'			=> 'meta_post_password',
		'post_name'				=> 'slug',
		'to_ping'				=> 'meta_to_ping',
		'pinged'				=> 'meta_pinged',
		'post_modified'			=> 'meta_post_modified',
		'post_modified_gmt'		=> 'timestamp_modified',
		'post_content_filtered'	=> 'meta_post_content_filtered',
		'post_parent'			=> 'meta_post_parent',
		'guid'					=> 'meta_guid',
		'menu_order'			=> 'meta_menu_order',
		'post_type'				=> 'type',
		'post_mime_type'		=> 'meta_post_mime_type',
		'comment_count'			=> 'comment_count',
	);

	/**
	 * @var array database mappings ($column => $alias) for the posts meta table
	 */
	protected static $_db_meta_mappings = array
	(
		'post_id'		=> 'post_id',
		'meta_key'		=> 'key',
		'meta_value'	=> 'value',
	);

	/**
	 * Get posts. If no id is specified, all posts are returned.
	 *
	 * @param	mixed	id's being selected
	 * @param	boolean	reload cache from database?
	 * @return	array
	 */
	public function get_posts($ids = NULL, $reload_cache = FALSE)
	{
		$post_type = self::TYPE_POST;
		return $this->_get_posts($ids, $post_type, $reload_cache);
	}

	/**
	 * Get pages. If no id is specified, all pages are returned.
	 *
	 * @param	mixed	id's being selected
	 * @param	boolean	reload cache from database?
	 * @return	array
	 */
	public function get_pages($ids = NULL, $reload_cache = FALSE)
	{
		$post_type = self::TYPE_PAGE;
		return $this->_get_posts($ids, $post_type, $reload_cache);
	}

	/**
	 * Get posts. If no id is specified, all posts are returned.
	 *
	 * @param	mixed	id's being selected
	 * @param	string	post type (page | post)
	 * @param	boolean	reload cache from database?
	 * @return	array
	 */
	protected function _get_posts($ids = NULL, $post_type = self::TYPE_POST, $reload_cache = FALSE)
	{
		$driver = self::$_driver;
		$config = MMI_Blog::get_config(TRUE);
		$cache_id = $this->_get_cache_id($driver, 'posts_'.$post_type);
		$cache_lifetime = Arr::path($config, 'cache_lifetimes.post', 0);
		$load_categories = Arr::path($config, 'features.category', FALSE);
		$load_meta = Arr::path($config, 'features.post_meta', FALSE);
		$load_tags = Arr::path($config, 'features.tag', FALSE);

		$posts = NULL;
		if ( ! $reload_cache AND $cache_lifetime > 0)
		{
			$posts = MMI_Cache::get($cache_id, MMI_Cache::CACHE_TYPE_DATA, $cache_lifetime);
		}
		if (empty($posts))
		{
			// Load all data
			$data = Model_WP_Posts::select_by_id(NULL, $post_type, self::$_db_mappings, TRUE, 'ID');
			$posts = array();
			foreach ($data as $id => $fields)
			{
				$posts[$id] = self::factory($driver)->_load($fields, $load_meta);
			}
			if ($load_categories)
			{
				self::_load_categories($posts);
			}
			if ($load_tags)
			{
				self::_load_tags($posts);
			}
			if ($load_meta)
			{
				self::_load_meta($posts);
			}
			if ($cache_lifetime > 0)
			{
				MMI_Cache::set($cache_id, MMI_Cache::CACHE_TYPE_DATA, $posts, $cache_lifetime);
			}
		}

		// If only 1 object in results array, return the object instead of an array of objects
		$results = $this->_extract_results($posts, $ids, TRUE);
		if (MMI_Util::is_set($ids) AND ! is_array($ids) AND count($results) === 1)
		{
			$results = $results[$ids];
		}
		return $results;
	}

	/**
	 * If an author id is specified, load the user settings (display name, email and url).
	 *
	 * @param	string	user id
	 * @return	integer
	 */
	protected function _get_author_id($value)
	{
		$value = intval($value);
		if ($value > 0)
		{
			$user = MMI_Blog_User::factory(self::$_driver)->get_users($value, TRUE);
			$this->author_name = $user->display_name;
			$this->author_email = $user->email;
			$this->author_url = $user->url;
		}
		return $value;
	}

	/**
	 * Get comment count.
	 *
	 * @param	string	count
	 * @return	integer
	 */
	protected function _get_comment_count($value)
	{
		return intval($value);
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
	 * Get timestamp created.
	 *
	 * @param	string	date created
	 * @return	integer
	 */
	protected function _get_timestamp_created($value)
	{
		return strtotime($value);
	}

	/**
	 * Get timestamp modified.
	 *
	 * @param	string	date modified
	 * @return	integer
	 */
	protected function _get_timestamp_modified($value)
	{
		return strtotime($value);
	}

	/**
	 * Load post metadata.
	 *
	 * @param	array	array of blog post objects
	 * @return	void
	 */
	protected static function _load_meta($posts)
	{
		$ids = array();
		foreach ($posts as $item)
		{
			$ids[] = $item->id;
		}
		$meta = Model_WP_PostMeta::select_by_post_id($ids, self::$_db_meta_mappings);
		$current_id;
		$old_id = -1;
		$item_meta;
		foreach ($meta as $item)
		{
			$current_id = intval($item['post_id']);
			if ($current_id !== $old_id)
			{
				if ($old_id > -1 AND count($item_meta > 0))
				{
					$posts[$old_id]->meta = $item_meta;
				}
				$item_meta = $posts[$current_id]->meta;
				$old_id = $current_id;
			}
			$item_meta[Arr::get($item, 'key')] = Arr::get($item, 'value');
		}
		if ($old_id > -1 AND count($item_meta > 0))
		{
			$posts[$old_id]->meta = $item_meta;
		}
	}

	/**
	 * Load post categories.
	 *
	 * @param	array	array of blog post objects
	 * @return	void
	 */
	protected static function _load_categories($posts)
	{
		if ( ! is_array($posts))
		{
			$posts = array();
		}

		foreach ($posts as $id => $item)
		{
			$posts[$id]->categories = array();
		}
		$terms = MMI_Blog_Term::factory(self::$_driver)->get_categories(NULL, TRUE);
		self::_load_terms($posts, $terms);
	}

	/**
	 * Load post tags.
	 *
	 * @param	array	array of blog post objects
	 * @return	void
	 */
	protected static function _load_tags($posts)
	{
		if ( ! is_array($posts))
		{
			$posts = array();
		}

		foreach ($posts as $id => $item)
		{
			$posts[$id]->tags = array();
		}
		$terms = MMI_Blog_Term::factory(self::$_driver)->get_tags(NULL, TRUE);
		self::_load_terms($posts, $terms);
	}

	/**
	 * Load post terms.
	 *
	 * @param	array	array of blog post objects
	 * @param	array	array of blog term objects
	 * @return	void
	 */
	protected static function _load_terms($posts, $terms)
	{
		if ( ! is_array($posts))
		{
			$posts = array();
		}
		if ( ! is_array($terms))
		{
			$terms = array();
		}

		// Get post-ids
		$post_ids = array();
		foreach ($posts as $item)
		{
			$post_ids[] = $item->id;
		}

		if (is_array($terms) AND count($terms) > 0)
		{
			foreach ($terms as $item)
			{
				$found = array_intersect($item->post_ids, $post_ids);
				if (is_array($found) AND count($found) > 0)
				{
					foreach ($found as $found_id)
					{
						$posts[$found_id]->categories[] = $item;
					}
				}
			}
		}
	}
} // End Kohana_MMI_Blog_Wordpress_Post
