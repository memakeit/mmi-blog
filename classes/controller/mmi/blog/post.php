<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog post controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Post extends MMI_Template
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * @var boolean load comments via AJAX?
	 **/
	protected $_ajax_comments;

	/**
	 * @var boolean allow pingbacks?
	 **/
	protected $_allow_pingbacks;

	/**
	 * @var boolean allow trackbacks?
	 **/
	protected $_allow_trackbacks;

	/**
	 * @var string the blog driver
	 **/
	protected $_driver;

	/**
	 * @var array the blog feature settings
	 **/
	protected $_features_config;

	/**
	 * @var MMI_Blog_Post the blog post object
	 **/
	protected $_post;

	/**
	 * Ensure the pagination module is loaded.
	 * Load the blog settings from the configuration file.
	 *
	 * @param	object	the request that created the controller
	 * @return	void
	 */
	public function __construct(Request $request)
	{
		parent::__construct($request);
		MMI_Util::load_module('pagination', MODPATH.'pagination');
		$config = MMI_Blog::get_config();
		$this->_driver = $config->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$this->_features_config = $config->get('features', array());

		// Comment settings
		$comment_config = $config->get('comments', array());
		$this->_ajax_comments = Arr::get($comment_config, 'use_ajax', FALSE);
		$this->_allow_pingbacks = Arr::get($comment_config, 'pingbacks', TRUE);
		$this->_allow_trackbacks = Arr::get($comment_config, 'trackbacks', TRUE);
	}

	/**
	 * Display a blog post.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$request = $this->request;
		$month = $request->param('month');
		$year = $request->param('year');
		$slug = $request->param('slug');

		// Get the post
		$archive = MMI_Blog_Post::factory($this->_driver)->get_archive($year, $month);
		$post = Arr::path($archive, $year.$month.'.'.$slug);
		$this->_post = $post;
		unset($archive);

		// Inject CSS and JavaScript
		$this->_inject_media();

		// Get and re-set the nav type
		$this->_nav_type = MMI_Blog::get_nav_type();
		MMI_Blog::set_nav_type($this->_nav_type);

		//
		$this->_meta();

		$view = View::factory('mmi/blog/post')
		 	->set('ajax_comments', $this->_ajax_comments)
		 	->set('bookmarks', $this->_get_bookmarks())
		 	->set('insert_retweet', TRUE)
			->set('is_homepage', FALSE)
			->set('post', $post)
			->set('toolbox', $this->_get_mini_toolbox())
		;

		// Comments and trackbacks
		if (Arr::get($this->_features_config, 'comments', TRUE))
		{
			$view->set('comments', $this->_get_comments());
			if ($this->_allow_pingbacks OR $this->_allow_trackbacks)
			{
				$view->set('trackbacks', $this->_get_trackbacks());
			}
		}

		$this->_title = $post->title;
		$this->add_view('content', self::LAYOUT_ID, 'content', $view);
	}

	/**
	 * Inject CSS and JavaScript.
	 *
	 * @return	void
	 */
	protected function _inject_media()
	{
		$this->add_css_url('mmi-blog_post', array('bundle' => 'blog'));
		$this->add_css_url('mmi-social_addthis.mini', array('bundle' => 'blog'));
		$this->add_css_url('mmi-social_addthis.bookmarks', array('bundle' => 'blog'));
		$this->add_js_url('mmi-blog_post', array('bundle' => 'blog'));
		$this->add_js_url('mmi-social_addthis', array('bundle' => 'blog'));
	}

	protected function _meta()
	{
		$prev_next = $this->_get_prev_next();
		$prev = Arr::get($prev_next, 'prev');
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
			$this->add_meta_link($url, array('rel' => $rel));
		}

		$next = Arr::get($prev_next, 'next');
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
			$this->add_meta_link($url, array('rel' => $rel));
		}

		// Get navigation settings
		$nav_parm = NULL;
		$nav_type = $this->_nav_type;
		if (is_array($nav_type) AND count($nav_type) > 0)
		{
			$nav_type = key($this->_nav_type);
			$nav_parm = Arr::get($this->_nav_type, $nav_type);
		}

		switch ($nav_type)
		{
			case MMI_Blog::NAV_CATEGORY:
			case MMI_Blog::NAV_TAG:
				if ( ! empty($nav_parm))
				{
					$this->add_meta_link
					(
						MMI_Blog_Term::get_category_guid($nav_parm),
						array('rel' => 'index tag up')
					);
				}
				break;

			case MMI_Blog::NAV_ARCHIVE:
				$month = substr($nav_parm, -2);
				$year = substr($nav_parm, 0, 4);
				$this->add_meta_link
				(
					MMI_Blog_Post::get_archive_guid($year, $month),
					array('rel' => 'archives index up')
				);
				break;

			default:
				$this->add_meta_link
				(
					MMI_Blog::get_guid(),
					array('rel' => 'index up')
				);
				break;
		}

		// Set response
		$view = View::factory('mmi/blog/content/prev_next')
			->set('prev', $prev)
			->set('next', $next)
		;
		$this->add_view('prev_next', 'content', 'prev_next', $view);
		$this->add_css_url('mmi-blog_prev-next', array('bundle' => 'blog'));
//		MMI_Debug::mdead($this->_mgr_meta);
	}

	/**
	 * Get the previous and next item settings.
	 *
	 * @return	array
	 */
	protected function _get_prev_next()
	{
		$post_id = $this->_post->id;
		$posts = $this->_get_all_nav__posts();

		$prev = NULL;
		$next = NULL;
		if (is_array($posts) AND count($posts) > 0)
		{
			$last = end($posts);
			$first = reset($posts);
			$post = $first;
			while ($post !== FALSE)
			{
				if ($post_id === $post->id)
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
							'title'		=> $prev->title,
							'url'		=> $prev->guid,
							'is_first'	=> ($prev->id === $first->id),
							'is_last'	=> ($prev->id === $last->id),
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
							'title'		=> $next->title,
							'url'		=> $next->guid,
							'is_first'	=> ($next->id === $first->id),
							'is_last'	=> ($next->id === $last->id),
						);
					}
					break;
				}
				$post = next($posts);
			}
		}
		return array('prev' => $prev, 'next' => $next);
	}

	/**
	 * Return an array of all posts for the current navigation settings.
	 *
	 * @return	array
	 */
	protected function _get_all_nav__posts()
	{
		// Get navigation settings
		$nav_parm = NULL;
		$nav_type = $this->_nav_type;
		if (is_array($nav_type) AND count($nav_type) > 0)
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
					$posts = MMI_Blog_Post::factory($this->_driver)->get_posts($post_ids);
				}
				break;

			case MMI_Blog::NAV_ARCHIVE:
				$month = substr($nav_parm, -2);
				$year = substr($nav_parm, 0, 4);
				$posts = MMI_Blog_Post::factory($this->_driver)->get_archive($year, $month);
				$posts = Arr::get($posts, $nav_parm, array());
				break;

			default:
				$posts = MMI_Blog_Post::factory($this->_driver)->get_posts();
				break;
		}
		return $posts;
	}








	protected function _get_comments()
	{
		$route = Route::get('mmi/blog/hmvc')->uri(array
		(
			'controller' => 'comments'
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'post' => $this->_post,
		);
		return $hmvc->execute()->response;
	}

	protected function _get_trackbacks()
	{
		$route = Route::get('mmi/blog/hmvc')->uri(array
		(
			'controller' => 'trackbacks'
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'post' => $this->_post,
		);
		return $hmvc->execute()->response;
	}

	protected function _get_bookmarks()
	{
		$post = $this->_post;
		$title = $post->title;
		$url = $post->guid;

		$route = Route::get('mmi/social/hmvc')->uri(array
		(
			'action' 		=> 'bookmarks',
			'controller'	=> 'addthis'
		));
		$addthis = Request::factory($route);
		$addthis->post = array
		(
			'title'	=> $title,
			'url'	=> $url,
		);
		if ( ! empty($description))
		{
			$addthis->post['description'] = $description;
		}
		return $addthis->execute()->response;
	}

	protected function _get_mini_toolbox()
	{
		$post = $this->_post;
		$title = $post->title;
		$url = $post->guid;

		$route = Route::get('mmi/social/hmvc')->uri(array
		(
			'action' 		=> 'mini',
			'controller'	=> 'addthis'
		));
		$addthis = Request::factory($route);
		$addthis->post = array
		(
			'title'	=> $title,
			'url'	=> $url,
		);
		if ( ! empty($description))
		{
			$addthis->post['description'] = $description;
		}
		return $addthis->execute()->response;
	}
} // End Controller_MMI_Blog_Post
