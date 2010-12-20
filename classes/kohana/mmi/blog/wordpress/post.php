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
	 * Get a list of posts. If no id is specified, all posts are returned.
	 * Minimal post details are returned for each list item.
	 *
	 * @param	mixed	id's being selected
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_post_list($ids = NULL, $reload_cache = NULL)
	{
		return self::_get_post_list($ids, self::TYPE_POST, $reload_cache);
	}

	/**
	 * Get a list of pages. If no id is specified, all pages are returned.
	 * Minimal page details are returned for each list item.
	 *
	 * @param	mixed	id's being selected
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_page_list($ids = NULL, $reload_cache = NULL)
	{
		return self::_get_post_list($ids, self::TYPE_PAGE, $reload_cache);
	}

	/**
	 * Get whether comments are open.
	 *
	 * @return	boolean
	 */
	public function comments_open()
	{
		return (strcasecmp($this->comment_status, 'open') === 0);
	}

	/**
	 * Update the comment count.
	 *
	 * @return	boolean
	 */
	public function update_comment_count()
	{
		return Model_WP_Posts::update_comment_count($this->id, $this->comment_count + 1);
	}

	/**
	 * Get a page using its slug.
	 *
	 * @param	string	the page slug
	 * @return	array
	 */
	public function get_page($slug)
	{
		$data = Model_WP_Posts::get_page($slug, self::$_db_mappings);
		if (empty($data))
		{
			return NULL;
		}

		// Create a post object
		$config = MMI_Blog::get_config()->get('features', array());
		$load_meta = Arr::get($config, 'post_meta', FALSE);
		$post = $this->_get_post($data, $load_meta);
		if ( ! $post instanceof MMI_Blog_Post)
		{
			return NULL;
		}

		// Load the post's categories, tags, and meta data
		$load_categories = Arr::get($config, 'category', FALSE);
		$load_tags = Arr::get($config, 'tag', FALSE);
		$post_id = $post->id;
		$post = array($post_id => $post);
		if ($load_categories)
		{
			self::_load_categories($post);
		}
		if ($load_tags)
		{
			self::_load_tags($post);
		}
		if ($load_meta)
		{
			self::_load_meta($post);
		}
		return $post[$post_id];
	}

	/**
	 * Get a post using its year, month, and slug.
	 *
	 * @param	string	the post year
	 * @param	string	the post month
	 * @param	string	the post slug
	 * @return	array
	 */
	public function get_post($year, $month, $slug)
	{
		$data = Model_WP_Posts::get_post($year, $month, $slug, self::$_db_mappings);
		if (empty($data))
		{
			return NULL;
		}

		// Create a post object
		$config = MMI_Blog::get_config()->get('features', array());
		$load_meta = Arr::get($config, 'post_meta', FALSE);
		$post = $this->_get_post($data, $load_meta);
		if ( ! $post instanceof MMI_Blog_Post)
		{
			return NULL;
		}

		// Load the post's categories, tags, and meta data
		$load_categories = Arr::get($config, 'category', FALSE);
		$load_tags = Arr::get($config, 'tag', FALSE);
		$post_id = $post->id;
		$post = array($post_id => $post);
		if ($load_categories)
		{
			self::_load_categories($post);
		}
		if ($load_tags)
		{
			self::_load_tags($post);
		}
		if ($load_meta)
		{
			self::_load_meta($post);
		}

		return $post[$post_id];
	}

	/**
	 * Get popular (most-viewed) posts.
	 *
	 * @param	integer	the maximum number of popular posts to return
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_popular($max_num = 10, $reload_cache = NULL)
	{
		if ( ! isset($reload_cache))
		{
			$reload_cache = MMI_Blog::reload_cache();
		}

		// Get page views
		$page_views = Model_MMI_Page_Views::select_by_route_name('mmi/blog/post', TRUE);
		if (count($page_views) === 0)
		{
			return array();
		}
		elseif ($max_num > count($page_views))
		{
			$max_num = count($page_views);
		}

		// Process page views
		$temp = array();
		foreach ($page_views as $view)
		{
			$num = str_pad($view['num_page_views'], 8, '0', STR_PAD_LEFT);
			$temp[$num] = Arr::get($view, 'request_parms', array());
		}
		krsort($temp);
		$temp = array_slice($temp, 0 , $max_num);

		$popular = array();
		foreach ($temp as $item)
		{
			$slug = $item['year'].'/'.$item['month'].'/'.$item['slug'];
			$popular[$slug] = array();
		}
		unset($temp);

		// Find post guid and title
		$count = 0;
		$posts = $this->_get_post_list(NULL, self::TYPE_POST, $reload_cache);
		foreach ($posts as $post)
		{
			$timestamp = $post['timestamp_created'];
			$month = date('m', $timestamp);
			$year = date('Y', $timestamp);
			$slug = $year.'/'.$month.'/'.$post['slug'];
			if (array_key_exists($slug, $popular))
			{
				$popular[$slug]['url'] = $post['guid'];
				$popular[$slug]['title'] = $post['title'];
				if (++$count === $max_num)
				{
					break;
				}
			}
		}
		unset($posts);
		return array_values($popular);
	}

	/**
	 * Get random posts.
	 *
	 * @param	integer	the maximum number of random posts to return
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_random($max_num = 10, $reload_cache = NULL)
	{
		if ( ! isset($reload_cache))
		{
			$reload_cache = MMI_Blog::reload_cache();
		}

		$posts = $this->_get_post_list(NULL, self::TYPE_POST, $reload_cache);
		if (count($posts) === 0)
		{
			return array();
		}
		elseif ($max_num > count($posts))
		{
			$max_num = count($posts);
		}

		$random = array();
		$keys = array_rand($posts, $max_num);
		foreach ($keys as $key)
		{
			$post = $posts[$key];
			$random[] = array
			(
				'url'	=> $post['guid'],
				'title'	=> $post['title'],
			);
		}
		unset($posts);
		return $random;
	}

	/**
	 * Get recent posts.
	 *
	 * @param	integer	the maximum number of recent posts to return
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_recent($max_num = 10, $reload_cache = NULL)
	{
		if ( ! isset($reload_cache))
		{
			$reload_cache = MMI_Blog::reload_cache();
		}

		$posts = $this->_get_post_list(NULL, self::TYPE_POST, $reload_cache);
		if (count($posts) === 0)
		{
			return array();
		}
		elseif ($max_num > count($posts))
		{
			$max_num = count($posts);
		}

		$recent = array();
		foreach ($posts as $post)
		{
			$recent[$post['timestamp_created']] = array
			(
				'url'	=> $post['guid'],
				'title'	=> $post['title'],
			);
		}
		unset($posts);
		krsort($recent);
		return array_slice($recent, 0 , $max_num);
	}

	/**
	 * Get related posts for the post id specified.
	 *
	 * @param	integer	the post id to find related posts for
	 * @param	integer	the maximum number of related posts to return
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_related($id, $max_num = 10, $reload_cache = NULL)
	{
		if ( ! isset($reload_cache))
		{
			$reload_cache = MMI_Blog::reload_cache();
		}

		$posts = $this->_get_post_list(NULL, self::TYPE_POST, $reload_cache);
		if (count($posts) === 0)
		{
			return array();
		}
		elseif ($max_num > count($posts))
		{
			$max_num = count($posts) - 1;
		}

		$id = intval($id);
		$cat_ids = array();
		$tag_ids = array();
		$temp = array();
		foreach ($posts as $post)
		{
			$post_id = intval($post['id']);
			if ($post_id === $id)
			{
				// Category and tag ids for the post id specified
				foreach ($post['categories'] as $category)
				{
					$cat_ids[] = $category;
				}
				foreach ($post['tags'] as $tag)
				{
					$tag_ids[] = $tag;
				}
			}
			else
			{
				// Data for the other posts
				$temp[$post_id] = array
				(
					'cat_ids'	=> array(),
					'created'	=> $post['timestamp_created'],
					'guid'		=> $post['guid'],
					'tag_ids'	=> array(),
					'title'		=> $post['title'],
				);
				foreach ($post['categories'] as $category)
				{
					$temp[$post_id]['cat_ids'][] = $category;
				}
				foreach ($post['tags'] as $tag)
				{
					$temp[$post_id]['tag_ids'][] = $tag;
				}
			}
		}
		unset($posts);

		if (empty($temp))
		{
			// Only one posts exists
			return array();
		}
		elseif (empty($cat_ids) AND empty($tag_ids))
		{
			// No categories or tags found for the post
			return array();
		}

		// Match related posts based on category and / or tag ids
		$related = array();
		foreach ($temp as $post_id => $item)
		{
			$cat_matches = array_intersect($cat_ids, Arr::get($item, 'cat_ids', array()));
			$tag_matches = array_intersect($tag_ids, Arr::get($item, 'tag_ids', array()));
			if ( ! empty($cat_matches) OR ! empty($tag_matches))
			{
				$related[] = array
				(
					'cat_count'	=> count($cat_matches),
					'created'	=> $item['created'],
					'guid'		=> $item['guid'],
					'tag_count'	=> count($tag_matches),
					'title'		=> $item['title'],
				);
			}
		}

		// Sort the related posts by category + tag count and by date created
		$temp = $related;
		$related = array();
		foreach ($temp as $item)
		{
			$weight = $item['cat_count'] + $item['tag_count'];
			$weight = str_pad($weight, 4, '0', STR_PAD_LEFT).'_'.$item['created'];
			$related[$weight] = array
			(
				'url'	=> $item['guid'],
				'title'	=> $item['title'],
			);
		}
		unset($temp);
		krsort($related);
		return array_values(array_slice($related, 0, $max_num));
	}

	/**
	 * Get a list of posts for a month and year.
	 * Minimal post details are returned for each list item.
	 *
	 * @param	integer	year
	 * @param	integer	month
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_archive_list($year, $month, $reload_cache = NULL)
	{
		$posts = $this->_get_post_list(NULL, self::TYPE_POST, $reload_cache);
		if (count($posts) === 0)
		{
			return array();
		}

		$year = intval($year);
		$month = intval($month);
		$archive = array();
		foreach ($posts as $post)
		{
			$created = $post['timestamp_created'];
			if (intval(gmdate('Y', $created)) === $year AND intval(gmdate('n', $created)) === $month)
			{
				$archive[] = $post;
			}
		}
		return $archive;
	}

	/**
	 * Get archive frequencies (the number of posts per month and year).
	 *
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_archive_frequencies($reload_cache = NULL)
	{
		$posts = $this->_get_post_list(NULL, self::TYPE_POST, $reload_cache);
		if (count($posts) === 0)
		{
			return array();
		}

		$archives = array();
		foreach ($posts as $post)
		{
			$timestamp = $post['timestamp_created'];
			$key = date('Ym', $timestamp);
			if (array_key_exists($key, $archives))
			{
				$archives[$key]['count']++;
			}
			else
			{
				$archives[$key] = array
				(
					'count'	=> 1,
					'guid'	=> $post['guid'],
					'name'	=> date('F Y', $timestamp),
				);
			}
		}
		unset($posts);

		$frequencies= array();
		foreach ($archives as $slug => $archive)
		{
			$count = $archive['count'];
			$key = strtolower(str_pad($count, 4, 0, STR_PAD_LEFT).'_'.$slug);
			$frequencies[$key] = array
			(
				'count'	=> $count,
				'guid'	=> $archive['guid'],
				'name'	=> $archive['name'],
			);
		}
		unset($archives);
		krsort($frequencies);
		return array_values($frequencies);
	}

	/**
	 * Get posts. If no id is specified, all posts are returned.
	 *
	 * @param	mixed	id's being selected
	 * @return	array
	 */
	public function get_posts($ids = NULL)
	{
		return $this->_get_posts($ids, self::TYPE_POST);
	}

	/**
	 * Get pages. If no id is specified, all pages are returned.
	 *
	 * @param	mixed	id's being selected
	 * @return	array
	 */
	public function get_pages($ids = NULL, $reload_cache = NULL)
	{
		return $this->_get_posts($ids, self::TYPE_PAGE);
	}

	/**
	 * Get posts. If no id is specified, all posts are returned.
	 *
	 * @param	mixed	id's being selected
	 * @param	string	post type (page | post)
	 * @return	array
	 */
	protected function _get_posts($ids = NULL, $post_type = self::TYPE_POST)
	{
		if ( ! isset($reload_cache))
		{
			$reload_cache = MMI_Blog::reload_cache();
		}

		$config = MMI_Blog::get_config(TRUE);
		$features = Arr::get($config, 'features', array());
		$load_categories = Arr::get($features, 'category', FALSE);
		$load_meta = Arr::get($features, 'post_meta', FALSE);
		$load_tags = Arr::get($features, 'tag', FALSE);

		$posts = NULL;
		if ( ! isset($posts))
		{
			// Load all data
			$data = Model_WP_Posts::select_by_id($ids, $post_type, self::$_db_mappings, TRUE);
			$posts = array();
			foreach ($data as $fields)
			{
				$id = $fields[self::$_db_mappings['ID']];
				$posts[$id] = $this->_get_post($fields, $load_meta);
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
		}
		return $posts;
	}

	/**
	 * Create a post object and set its driver and guid properties.
	 *
	 * @param	array	an associative array of post details
	 * @param	boolean	load the meta-data (from the post details)?
	 * @return	array
	 */
	protected function _get_post($data, $load_meta = FALSE)
	{
		$driver = self::$_driver;
		$post = self::factory($driver)->_load($data, $load_meta);
		$post->driver = $driver;

		// Set the guids
		$post_date = $post->timestamp_created;
		$year = gmdate('Y', $post_date);
		$month = gmdate('m', $post_date);
		$slug = $post->slug;
		$post->guid = self::get_guid($year, $month, $slug);
		$post->archive_guid = self::get_archive_guid($year, $month);
		$post->comments_feed_guid = self::get_comments_feed_guid($year, $month, $slug);
		$post->trackback_guid = self::get_trackback_guid($year, $month, $slug);

		return $post;
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

	/**
	 * Get a list of posts. If no id is specified, all posts are returned.
	 * Minimal post details are returned for each list item.
	 *
	 * @param	mixed	id's being selected
	 * @param	string	post type (page | post)
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	protected function _get_post_list($ids = NULL, $post_type = self::TYPE_POST, $reload_cache = NULL)
	{
		if ( ! isset($reload_cache))
		{
			$reload_cache = MMI_Blog::reload_cache();
		}

		$config = MMI_Blog::get_config(TRUE);
		$cache_id = $this->_get_cache_id(self::$_driver, 'post_list_'.$post_type);
		$cache_lifetime = Arr::path($config, 'cache_lifetimes.post', 0);
		$features = Arr::get($config, 'features', array());
		$load_categories = Arr::get($features, 'category', FALSE);
		$load_tags = Arr::get($features, 'tag', FALSE);

		$posts = NULL;
		if ( ! $reload_cache AND $cache_lifetime > 0)
		{
			$posts = MMI_Cache::instance()->get($cache_id, MMI_Cache::CACHE_TYPE_DATA, $cache_lifetime);
		}
		if ( ! isset($posts))
		{
			$cols = array_intersect_key(self::$_db_mappings, array
			(
				'ID'			=> 'id',
				'post_title'	=> 'title',
				'post_name'		=> 'slug',
				'post_date_gmt'	=> 'timestamp_created',
			));

			// Load all post list data
			$data = Model_WP_Posts::select_by_id(NULL, $post_type, $cols, TRUE);
			$posts = array();
			foreach ($data as $fields)
			{
				$id = $fields[$cols['ID']];
				$post_date = strtotime($fields[$cols['post_date_gmt']]);
				$year = gmdate('Y', $post_date);
				$month = gmdate('m', $post_date);
				$slug = $fields[$cols['post_name']];
				$guid = self::get_guid($year, $month, $slug);

				$fields[$cols['post_date_gmt']] = $post_date;
				$fields['guid'] = $guid;
				$posts[$id] = $fields;
			}
			if ($load_categories)
			{
				$posts = self::_set_category_ids($posts, $reload_cache);
			}
			if ($load_tags)
			{
				$posts = self::_set_tag_ids($posts, $reload_cache);
			}
			if ($cache_lifetime > 0)
			{
				MMI_Cache::instance()->set($cache_id, MMI_Cache::CACHE_TYPE_DATA, $posts, $cache_lifetime);
			}
		}

		if (empty($ids))
		{
			$posts = array_values($posts);
		}
		else
		{
			$posts = array_values(array_intersect_key($posts, array_fill_keys($ids, $ids)));
		}
		return $posts;
	}

	/**
	 * Set the category ids for each post.
	 *
	 * @param	array	array of blog posts (represented as arrays)
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	protected static function _set_category_ids($posts, $reload_cache = NULL)
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
			$posts[$idx]['categories'] = array();
		}
		$terms = MMI_Blog_Term::factory(self::$_driver)->get_categories(NULL, $reload_cache);
		return self::_set_term_ids($posts, $terms, MMI_Blog_Term::TYPE_CATEGORY);
	}

	/**
	 * Set the tag ids for each post.
	 *
	 * @param	array	array of blog posts (represented as arrays)
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	protected static function _set_tag_ids($posts, $reload_cache = NULL)
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
			$posts[$idx]['tags'] = array();
		}
		$terms = MMI_Blog_Term::factory(self::$_driver)->get_tags(NULL, $reload_cache);
		return self::_set_term_ids($posts, $terms, MMI_Blog_Term::TYPE_TAG);
	}

	/**
	 * Set the term ids for each post.
	 *
	 * @param	array	array of blog posts (represented as arrays)
	 * @param	array	array of blog term objects
	 * @param	string	type of term to load (category | tag)
	 * @return	array
	 */
	protected static function _set_term_ids($posts, $terms, $type = MMI_Blog_Term::TYPE_CATEGORY)
	{
		if ( ! is_array($terms))
		{
			$terms = array();
		}

		// Get post-ids
		$post_ids = array();
		foreach ($posts as $post)
		{
			$post_ids[] = intval($post['id']);
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
								$posts[$found_id]['tags'][] = $term->id;
							break;

							default:
								$posts[$found_id]['categories'][] = $term->id;
							break;
						}
					}
				}
			}
		}
		return $posts;
	}
} // End Kohana_MMI_Blog_Wordpress_Post
