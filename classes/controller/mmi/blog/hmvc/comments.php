<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Comments HMVC controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_HMVC_Comments extends Controller
{
	/**
	 * @var string the blog driver
	 **/
	protected $_driver;

	/**
	 * @var MMI_Blog_Post the blog post
	 **/
	protected $_post;

	/**
	 * @var boolean load comments via AJAX?
	 **/
	protected $_use_ajax;

	/**
	 * Initialize the comment settings
	 *
	 * @param	Request	the request that created the controller
	 * @return	void
	 */
	public function __construct($request)
	{
		// Only accept internal requests
		if ( ! $request->internal)
		{
			throw new Kohana_Request_Exception('Invalid external request.');
		}
		parent::__construct($request);

		// Comment settings
		$comment_config = MMI_Blog::get_config()->get('comments', array());
		$this->_use_ajax = Arr::get($comment_config, 'use_ajax', FALSE);

		// Load parameters
		$post = isset($request->post) ? ($request->post) : array();
		$this->_post = Arr::get($post, 'post');
		$this->_driver = $this->_post->driver;
	}

	/**
	 * Generate the comments.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		if ($this->_use_ajax)
		{
			$this->_comments_ajax();
		}
		else
		{
			$this->_comments();
		}
	}

	/**
	 * Generate the non-AJAX comments.
	 *
	 * @return	void
	 */
	protected function _comments()
	{
		$post = $this->_post;

		// Get comments
		$comments = MMI_Blog_Comment::factory($this->_driver)->get_comments($post->id);

		// Inject media
		$parent = Request::instance();
		$parent->css->add_url('mmi-blog_comments', array('bundle' => 'blog'));

		// Gravatar defaults
		$defaults = MMI_Gravatar::get_config()->get('defaults', array());
		$default_img = Arr::get($defaults, 'img');
		$default_img_size = Arr::get($defaults, 'size');

		// Set response
		$view = View::factory('mmi/blog/content/comments')
			->set('comments', $comments)
			->set('header', $this->_get_header($comments))
			->set('default_img', $default_img)
			->set('default_img_size', $default_img_size)
			->set('feed_url', $post->comments_feed_guid)
		;
		$this->request->response = $view->render();
	}

	/**
	 * Generate the AJAX comments.
	 *
	 * @return	void
	 */
	protected function _comments_ajax()
	{
		$post = $this->_post;
		$template = MMI_Text::normalize_spaces(View::factory('mmi/blog/js/comments')->render());
		$url = URL::site(Route::get('mmi/blog/rest')->uri(array
		(
			'controller'	=> 'comments',
			'post_id'		=> $post->id,
		)), TRUE);
		$js = "$(window).load(load_comments('$url', '$template'));";

		// Inject media
		$parent = Request::instance();
		$parent->css->add_url('mmi-blog_comments', array('bundle' => 'blog'));
		$parent->js->add_url('mmi-blog_jquery.tmpl', array('bundle' => 'blog'));
		$parent->js->add_url('mmi-blog_innershiv.min', array('bundle' => 'blog'));
		$parent->js->add_url('mmi-blog_ajax-comments', array('bundle' => 'blog'));
		$parent->js->add_inline('ajax_comments', $js);

		// Set response
		$view = View::factory('mmi/blog/content/ajax/comments')
			->set('feed_url', $post->comments_feed_guid)
			->set('header', $this->_get_header())
		;
		$this->request->response = $view->render();
	}

	/**
	 * Get the comments header.
	 *
	 * @param	array	an array of comments
	 * @return	string
	 */
	protected function _get_header($comments = NULL)
	{
		$num_comments = empty($comments) ? 0 : count($comments);
		return $num_comments.' '.ucfirst(Inflector::plural('Comment', $num_comments));
	}
} // End Controller_MMI_Blog_HMVC_Comments
