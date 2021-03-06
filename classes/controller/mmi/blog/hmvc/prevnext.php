<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Previous and next post HMVC controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_HMVC_PrevNext extends Controller_MMI_Blog_HMVC
{
	/**
	 * @var array the previous post settings
	 **/
	protected $_prev;

	/**
	 * @var mixed the navigation type settings
	 **/
	protected $_nav_type;

	/**
	 * @var array the next post settings
	 **/
	protected $_next;

	/**
	 * Set the navigation type.
	 *
	 * @access	public
	 * @param	Request	the request that created the controller
	 * @return	void
	 */
	public function __construct($request)
	{
		parent::__construct($request);
		$this->_nav_type = MMI_Blog::get_nav_type();
	}

	/**
	 * Generate the previous and next post links.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		// Initialize the previous and next post settings
		$this->_set_prev_next();
		if (empty($this->_prev) AND empty($this->_next))
		{
			$this->request->response = '';
			return;
		}

		// Set the meta tags
		$this->_meta();
		$this->_nav_meta();

		// Inject media
		if (class_exists('MMI_Request'))
		{
			MMI_Request::less()->add_url('post/prevnext', array('module' => 'mmi-blog'));
		}

		// Set response
		$this->request->response = Kostache::factory('mmi/blog/post/prevnext')->set(array
		(
			'prev'	=> $this->_prev,
			'next'	=> $this->_next,
		))->render();
	}

	/**
	 * Generate the meta tags for the previous and next post.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _meta()
	{
		$meta = MMI_Request::meta();
		$prev = $this->_prev;
		if ( ! empty($prev))
		{
			$url = $prev['url'];
			$rel = 'prefetch prev';
			if ($prev['is_last'])
			{
				$rel = 'last '.$rel;
			}
			if ($prev['is_first'])
			{
				$rel = 'first '.$rel;
			}
			$meta->add_link($url, array
			(
				'rel'	=> $rel,
				'title'	=> HTML::chars($prev['title']),
			));
		}

		$next = $this->_next;
		if ( ! empty($next))
		{
			$url = $next['url'];
			$rel = 'prefetch next';
			if ($next['is_last'])
			{
				$rel = 'last '.$rel;
			}
			if ($next['is_first'])
			{
				$rel = 'first '.$rel;
			}
			$meta->add_link($url, array
			(
				'rel'	=> $rel,
				'title'	=> HTML::chars($next['title']),
			));
		}
	}

	/**
	 * Generate the meta tags for the navigation (archive, category, index, or tag).
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _nav_meta()
	{
		$meta = MMI_Request::meta();

		// Get navigation settings
		$nav_parm = NULL;
		$nav_type = $this->_nav_type;
		if (is_array($nav_type) AND ! empty($nav_type))
		{
			$nav_type = key($this->_nav_type);
			$nav_parm = Arr::get($this->_nav_type, $nav_type);
		}

		switch ($nav_type)
		{
			case MMI_Blog::NAV_ARCHIVE:
				$month = substr($nav_parm, -2);
				$year = substr($nav_parm, 0, 4);
				$date = mktime(0, 0, 0, $month, 1, $year);
				$meta->add_link
				(
					MMI_Blog_Post::get_archive_guid($year, $month),
					array
					(
						'rel'	=> 'archives index up',
						'title'	=> 'articles for '.gmdate('F Y', $date)
					)
				);
			break;

			case MMI_Blog::NAV_CATEGORY:
				if ( ! empty($nav_parm))
				{
					$categories = $this->_post->categories;
					$name = '';
					foreach ($categories as $category)
					{
						if ($nav_parm === $category->slug)
						{
							$name = $category->name;
							break;
						}
					}
					$meta->add_link
					(
						MMI_Blog_Term::get_category_guid($nav_parm),
						array
						(
							'rel'	=> 'index tag up',
							'title'	=> 'articles categorized as '.$name,
						)
					);
				}
			break;

			case MMI_Blog::NAV_TAG:
				if ( ! empty($nav_parm))
				{
					$tags = $this->_post->tags;
					$name = '';
					foreach ($tags as $tag)
					{
						if ($nav_parm === $tag->slug)
						{
							$name = $tag->name;
							break;
						}
					}
					$meta->add_link
					(
						MMI_Blog_Term::get_category_guid($nav_parm),
						array
						(
							'rel'	=> 'index tag up',
							'title'	=> 'articles tagged as '.$name,
						)
					);
				}
			break;

			default:
				$meta->add_link
				(
					MMI_Blog::get_guid(),
					array
					(
						'rel'	=> 'index up',
						'title'	=> 'recent articles',
					)
				);
			break;
		}
	}

	/**
	 * Set the previous and next posts' settings.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _set_prev_next()
	{
		$post_id = $this->_post->id;
		$posts = $this->_get_all_nav_posts();

		$prev = NULL;
		$next = NULL;
		if (is_array($posts) AND ! empty($posts))
		{
			$last = end($posts);
			$first = reset($posts);
			$post = $first;
			while ($post !== FALSE)
			{
				if ($post_id === intval($post['id']))
				{
					// Get previous item
					$prev = prev($posts);
					if ($prev === FALSE)
					{
						$prev = NULL;
						reset($posts);
					}
					else
					{
						$prev = array
						(
							'title'		=> $prev['title'],
							'url'		=> $prev['guid'],
							'is_first'	=> ($prev['id'] === $first['id']),
							'is_last'	=> ($prev['id'] === $last['id']),
						);

						// Return to current item
						next($posts);
					}

					// Get next item
					$next = next($posts);
					if ($next === FALSE)
					{
						$next = NULL;
					}
					else
					{
						$is_first = ($next === $first);
						$is_last = ($next === $last);
						$next = array
						(
							'title'		=> $next['title'],
							'url'		=> $next['guid'],
							'is_first'	=> ($next['id'] === $first['id']),
							'is_last'	=> ($next['id'] === $last['id']),
						);
					}
					break;
				}
				$post = next($posts);
			}
		}
		$this->_prev = $prev;
		$this->_next = $next;
	}

	/**
	 * Return an array of all posts for the current navigation settings.
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function _get_all_nav_posts()
	{
		// Get navigation settings
		$nav_parm = NULL;
		$nav_type = $this->_nav_type;
		if (is_array($nav_type) AND ! empty($nav_type))
		{
			$nav_type = key($this->_nav_type);
			$nav_parm = Arr::get($this->_nav_type, $nav_type);
		}

		// Get posts
		$posts = NULL;
		switch ($nav_type)
		{
			case MMI_Blog::NAV_CATEGORY:
			case MMI_Blog::NAV_TAG:
				$method = ($nav_type === MMI_Blog::NAV_CATEGORY) ? 'get_categories_by_slug' : 'get_tags_by_slug';
				$terms = MMI_Blog_Term::factory($this->_driver)->$method($nav_parm);
				$term = Arr::get($terms, $nav_parm);
				$post_ids = empty($term) ? NULL : $term->post_ids;
				if ( ! empty($post_ids))
				{
					$posts = MMI_Blog_Post::factory($this->_driver)->get_post_list($post_ids);
				}
			break;

			case MMI_Blog::NAV_ARCHIVE:
				$month = substr($nav_parm, -2);
				$year = substr($nav_parm, 0, 4);
				$posts = MMI_Blog_Post::factory($this->_driver)->get_archive_list($year, $month);
			break;

			default:
				$posts = MMI_Blog_Post::factory($this->_driver)->get_post_list();
			break;
		}
		return $posts;
	}
} // End Controller_MMI_Blog_HMVC_PrevNext
