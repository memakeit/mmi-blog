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
	 * @var string the social bookmarking driver
	 **/
	protected $_bookmark_driver;

	/**
	 * @var MMI_Form the comment form object
	 **/
	protected $_comment_form;

	/**
	 * @var string the blog driver
	 **/
	protected $_driver;

	/**
	 * @var MMI_Blog_Comment a blog comment object
	 **/
	protected $_mmi_comment;

	/**
	 * @var MMI_Blog_Post the blog post object
	 **/
	protected $_post;

	/**
	 * Load the blog settings from the configuration file.
	 *
	 * @param	object	the request that created the controller
	 * @return	void
	 */
	public function __construct(Request $request)
	{
		parent::__construct($request);
		$config = MMI_Blog::get_config();
		$this->_bookmark_driver = $config->get('bookmark_driver', MMI_Bookmark::DRIVER_ADDTHIS);
		$this->_driver = $config->get('driver', MMI_Blog::DRIVER_WORDPRESS);
	}

	/**
	 * Display a blog post.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		// Post parameters
		$request = $this->request;
		$month = $request->param('month');
		$year = $request->param('year');
		$slug = $request->param('slug');

		// Comment settings
		$config = MMI_Blog::get_config();
		$features = $config->get('features', array());
		$allow_comments = Arr::get($features, 'comments', TRUE);
		$comment_config = $config->get('comments', array());

		// Get the post
		$post = MMI_Blog_Post::factory($this->_driver)->get_post($year, $month, $slug);
		$this->_post = $post;

		// If comments are open, configure the comment form
		if ($allow_comments AND $post->comments_open())
		{
			$this->_mmi_comment = MMI_Blog_Comment::factory($this->_driver);
			$this->_comment_form = $this->_mmi_comment->get_form();
			$this->_process_comment_form();
		}

		// Inject CSS and JavaScript
		$this->_inject_media();

		// Get and re-set the nav type
		$nav_type = MMI_Blog::get_nav_type();
		MMI_Blog::set_nav_type($nav_type);

		// Configure the view
		$post_features = MMI_Blog::get_post_config()->get('features', array());
		$view = View::factory('mmi/blog/post', array
		(
		 	'ajax_comments'		=> Arr::get($comment_config, 'use_ajax', FALSE),
		 	'bookmark_driver'	=> $this->_bookmark_driver,
		 	'bookmarks'			=> $this->_get_bookmarks(),
		 	'comment_form'		=> $this->_get_comment_form(),
		 	'insert_retweet'	=> Arr::get($post_features, 'insert_retweet', TRUE),
			'is_homepage'		=> FALSE,
			'post'				=> $post,
			'toolbox'			=> $this->_get_pill_bookmarks(),
		));

		// Comments and trackbacks
		if ($allow_comments)
		{
			$view->set('comments', $this->_get_comments());
			$this->_allow_pingbacks = Arr::get($comment_config, 'pingbacks', TRUE);
			$this->_allow_trackbacks = Arr::get($comment_config, 'trackbacks', TRUE);
			if ($this->_allow_pingbacks OR $this->_allow_trackbacks)
			{
				$view->set('trackbacks', $this->_get_trackbacks());
			}

			// Add feed for the posts's comments
			$this->add_meta_link($post->comments_feed_guid, array
			(
				'rel'	=> 'alternate',
				'title'	=> 'Comments for '.HTML::chars($post->title),
				'type'	=> File::mime_by_ext('atom'),
			));
		}

		if (Arr::get($post_features, 'prev_next', FALSE))
		{
			$view->set('prev_next', $this->_get_prev_next());
		}
		if (Arr::get($post_features, 'related_posts', FALSE))
		{
			$view->set('related_posts', $this->_get_related_posts());
		}

		if (Arr::get($post_features, 'facebook_meta', FALSE))
		{
			$this->_set_facebook_meta();
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
		$this->add_css_url('mmi-blog_comment.form', array('bundle' => 'blog'));
		$this->add_css_url('mmi-bookmark_addthis_pill', array('bundle' => 'blog'));
		$this->add_css_url('mmi-bookmark_addthis_bookmarks', array('bundle' => 'blog'));
		$this->add_js_url('mmi-blog_post', array('bundle' => 'blog'));
		$this->add_js_url('mmi-bookmark_addthis', array('bundle' => 'blog'));


		$form = $this->_comment_form;
		if (isset($form))
		{
			$this->add_css_url('mmi-form_form', array('bundle' => 'blog'));
			if ($form->plugin_exists('jquery_validation'))
			{
				$this->add_js_url('mmi-form_jquery.validate.min', array('bundle' => 'blog'));
				$this->add_js_inline('jquery_validate', $form->jqv_get_validation_js());
			}
		}
	}

	/**
	 * Using an HMVC request, get the related posts HTML.
	 *
	 * @return	string
	 */
	protected function _get_related_posts()
	{
		$route = Route::get('mmi/blog/hmvc')->uri(array
		(
			'controller' => 'relatedposts'
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'post' => $this->_post,
		);
		return $hmvc->execute()->response;
	}

	/**
	 * Using an HMVC request, get the previous and next post HTML.
	 *
	 * @return	string
	 */
	protected function _get_prev_next()
	{
		$route = Route::get('mmi/blog/hmvc')->uri(array
		(
			'controller' => 'prevnext'
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'post' => $this->_post,
		);
		return $hmvc->execute()->response;
	}

	/**
	 * Using an HMVC request, get the comments HTML.
	 *
	 * @return	string
	 */
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

	/**
	 * Using an HMVC request, get the trackbacks HTML.
	 *
	 * @return	string
	 */
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

	/**
	 * Using an HMVC request, get the bookmarking widget HTML.
	 *
	 * @return	string
	 */
	protected function _get_bookmarks()
	{
		$post = $this->_post;
		$title = $post->title;
		$url = $post->guid;

		$route = Route::get('mmi/bookmark/hmvc')->uri(array
		(
			'action' 		=> MMI_Bookmark::MODE_BOOKMARKS,
			'controller'	=> $this->_bookmark_driver,
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'title'	=> $title,
			'url'	=> $url,
		);
		return $hmvc->execute()->response;
	}

	/**
	 * Using an HMVC request, get the pill-style bookmarking widget HTML.
	 *
	 * @return	string
	 */
	protected function _get_pill_bookmarks()
	{
		$post = $this->_post;
		$title = $post->title;
		$url = $post->guid;

		$route = Route::get('mmi/bookmark/hmvc')->uri(array
		(
			'action' 		=> MMI_Bookmark::MODE_PILL,
			'controller'	=> $this->_bookmark_driver,
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'title'	=> $title,
			'url'	=> $url,
		);
		return $hmvc->execute()->response;
	}

	/**
	 * Get the comment form view.
	 *
	 * @return	string
	 */
	protected function _get_comment_form()
	{
		$form = '';
		if (isset($this->_comment_form))
		{
			$form = $this->_comment_form->render();
		}
		return View::factory('mmi/blog/content/comment_form', array(
			'form' => $form,
		))->render();
	}

	/**
	 * Do form validation, check for duplicate comments and save the comment.
	 *
	 * @return	void
	 */
	protected function _process_comment_form()
	{
		$form = $this->_comment_form;
		if (isset($form) AND $_POST)
		{
			$valid = $form->valid();
			if ($valid)
			{
				$values = $form->values();
				$is_duplicate = $this->_is_duplicate_comment($values);
				if ($is_duplicate)
				{
					$valid = FALSE;
					$form->error('This comment has already been posted.');
				}
				else
				{
					$valid = $this->_save_comment($values);
					if ( ! $valid)
					{
						$form->error('There was a problem saving your comment. Please try again.');
					}
				}
			}
			if ($valid)
			{
				$form->reset();
			}
		}
	}

	/**
	 * Check if a comment already exists.
	 *
	 * @param	string	the form values
	 * @return	boolean
	 */
	protected function _is_duplicate_comment($values)
	{
		$mappings = array
		(
			'author'		=> 'name',
			'author_email'	=> 'email',
			'author_url'	=> 'url'
		);
		$author = array();
		foreach ($mappings as $key1 => $key2)
		{
			$temp = Arr::get($values, $key1);
			if ( ! empty($temp))
			{
				$author[$key2] = $temp;
			}
		}
		$content = Arr::get($values, 'content');
		return $this->_mmi_comment->is_duplicate($this->_post->id, $content, $author);
	}

	/**
	 * Save the comment.
	 *
	 * @param	string	the form values
	 * @return	boolean
	 */
	protected function _save_comment($values)
	{
		$comment = $this->_mmi_comment;
		$comment->author = Arr::get($values, 'author');
		$comment->author_email = Arr::get($values, 'author_email');
		$comment->author_ip = Arr::get($_SERVER, 'REMOTE_ADDR', '');
		$comment->author_url = str_replace('&', '&amp;', Arr::get($values, 'author_url', ''));
		$comment->content = Arr::get($values, 'content');
		$comment->post_id = $this->_post->id;
		$comment->timestamp = gmdate('Y-m-d H:i:s');
		$success = $comment->save();

		if ($success)
		{
			if ( ! $this->_post->update_comment_count())
			{
				MMI_Log::log_error(__METHOD__, __LINE__, 'Unable to update comment count. Post id: '.$this->_post->id);
			}
		}
		return $success;
	}

	/**
	 * Add Facebook meta tags.
	 *
	 * @return	void
	 */
	protected function _set_facebook_meta()
	{
		$post = $this->_post;
		$this->add_meta_tag('og:site_name', $this->_site_name);
		$this->add_meta_tag('og:type', 'article');
		$this->add_meta_tag('og:title', $post->title);
		$this->add_meta_tag('og:url', $post->guid );
//	"http://ia.media-imdb.com/images/M/MV5BNzM2NDU5ODUzOV5BMl5BanBnXkFtZTcwNDY1MzQyMQ@@._V1._SX98_SY140_.jpg" extracted from <meta property="og:image" />
//	"115109575169727" extracted from <meta property="fb:app_id" />
	}
} // End Controller_MMI_Blog_Post
