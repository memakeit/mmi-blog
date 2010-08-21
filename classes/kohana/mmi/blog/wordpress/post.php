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
	protected static $_driver = MMI_Blog::DRIVER_WORDPRESS;

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
	 * Get posts for a month and year.
	 *
	 * @param	integer	year
	 * @param	integer	month
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_archive($year, $month, $reload_cache = NULL)
	{
		$year = intval($year);
		$month = intval($month);

		$archive = array();
		$posts = $this->_get_posts(NULL, self::TYPE_POST, $reload_cache);
		foreach ($posts as $post)
		{
			$created = $post->timestamp_created;
			if (intval(gmdate('Y', $created)) === $year AND intval(gmdate('n', $created)) === $month)
			{
				$archive[gmdate('Ym', $created)][$post->slug] = $post;
			}
		}
		return $archive;
	}

	/**
	 * Get posts. If no id is specified, all posts are returned.
	 *
	 * @param	mixed	id's being selected
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_posts($ids = NULL, $reload_cache = NULL)
	{
		return $this->_get_posts($ids, self::TYPE_POST, $reload_cache);
	}

	/**
	 * Get pages. If no id is specified, all pages are returned.
	 *
	 * @param	mixed	id's being selected
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_pages($ids = NULL, $reload_cache = NULL)
	{
		return $this->_get_posts($ids, self::TYPE_PAGE, $reload_cache);
	}

	/**
	 * Get posts. If no id is specified, all posts are returned.
	 *
	 * @param	mixed	id's being selected
	 * @param	string	post type (page | post)
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	protected function _get_posts($ids = NULL, $post_type = self::TYPE_POST, $reload_cache = NULL)
	{
		if ( ! isset($reload_cache))
		{
			$reload_cache = MMI_Blog::reload_cache();
		}

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
		if ( ! isset($posts))
		{
			// Load all data
			$data = Model_WP_Posts::select_by_id(NULL, $post_type, self::$_db_mappings, TRUE, 'ID');
			$posts = array();
			foreach ($data as $id => $fields)
			{
				$posts[$id] = self::factory($driver)->_load($fields, $load_meta);
				$posts[$id]->driver = $driver;

				// Set the guids
				$post_date = $posts[$id]->timestamp_created;
				$year = gmdate('Y', $post_date);
				$month = gmdate('m', $post_date);
				$slug = $posts[$id]->slug;
				$posts[$id]->guid = self::get_guid($year, $month, $slug);
				$posts[$id]->archive_guid = self::get_archive_guid($year, $month);
				$posts[$id]->comments_feed_guid = self::get_comments_feed_guid($year, $month, $slug);
				$posts[$id]->trackback_guid = self::get_trackback_guid($year, $month, $slug);
			}
			if ($load_categories)
			{
				self::_load_categories($posts);
			}
			if ($load_tags)
			{
				self::_load_tags($posts, ! $load_categories);
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
		return $this->_extract_results($posts, $ids, FALSE);
	}

	/**
	 * If an author id is specified, load the author.
	 *
	 * @param	string	user id
	 * @return	integer
	 */
	protected function _get_author_id($value)
	{
		$value = intval($value);
		if ($value > 0)
		{
			$users = MMI_Blog_User::factory(self::$_driver)->get_users($value);
			foreach ($users as $user)
			{
				if ($value === $user->id)
				{
					$this->author = $user;
					break;
				}
			}
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
		if ( ! (is_array($posts) AND count($posts) > 0))
		{
			return;
		}

		$ids = array();
		foreach ($posts as $post)
		{
			$ids[] = $post->id;
		}
		$meta = Model_WP_PostMeta::select_by_post_id($ids, self::$_db_meta_mappings);

		$old_id = -1;
		$item_meta = array();
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
	 * @param	mixed	reload cache from database?
	 * @return	void
	 */
	protected static function _load_categories($posts, $reload_cache = NULL)
	{
		if ( ! (is_array($posts) AND count($posts) > 0))
		{
			return;
		}

		if ( ! isset($reload_cache))
		{
			$reload_cache = MMI_Blog::reload_cache();
		}

		foreach ($posts as $idx => $post)
		{
			$posts[$idx]->categories = array();
		}
		$terms = MMI_Blog_Term::factory(self::$_driver)->get_categories(NULL, $reload_cache);
		self::_load_terms($posts, $terms, MMI_Blog_Term::TYPE_CATEGORY);
	}

	/**
	 * Load post tags.
	 *
	 * @param	array	array of blog post objects
	 * @param	mixed	reload cache from database?
	 * @return	void
	 */
	protected static function _load_tags($posts, $reload_cache = NULL)
	{
		if ( ! (is_array($posts) AND count($posts) > 0))
		{
			return;
		}

		if ( ! isset($reload_cache))
		{
			$reload_cache = MMI_Blog::reload_cache();
		}

		foreach ($posts as $idx => $post)
		{
			$posts[$idx]->tags = array();
		}
		$terms = MMI_Blog_Term::factory(self::$_driver)->get_tags(NULL, $reload_cache);
		self::_load_terms($posts, $terms, MMI_Blog_Term::TYPE_TAG);
	}

	/**
	 * Load post terms.
	 *
	 * @param	array	array of blog post objects
	 * @param	array	array of blog term objects
	 * @param	string	type of term to load (category | tag)
	 * @return	void
	 */
	protected static function _load_terms($posts, $terms, $type = MMI_Blog_Term::TYPE_CATEGORY)
	{
		if ( ! is_array($terms))
		{
			$terms = array();
		}

		// Get post-ids
		$post_ids = array();
		foreach ($posts as $post)
		{
			$post_ids[] = $post->id;
		}

		if (is_array($terms) AND count($terms) > 0)
		{
			foreach ($terms as $term)
			{
				$found = array_intersect($term->post_ids, $post_ids);
				if (is_array($found) AND count($found) > 0)
				{
					foreach ($found as $found_id)
					{
						switch ($type)
						{
							case MMI_Blog_Term::TYPE_TAG:
								$posts[$found_id]->tags[] = $term;
								break;

							default:
								$posts[$found_id]->categories[] = $term;
								break;
						}
					}
				}
			}
		}
	}
} // End Kohana_MMI_Blog_Wordpress_Post
