<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Comments HMVC controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_HMVC_Comments extends Controller_MMI_Blog_HMVC
{
	/**
	 * @var boolean load comments via AJAX?
	 **/
	protected $_use_ajax;

	/**
	 * Initialize the comment settings.
	 *
	 * @access	public
	 * @param	Request	the request that created the controller
	 * @return	void
	 */
	public function __construct($request)
	{
		parent::__construct($request);

		// Comment settings
		$comment_config = MMI_Blog::get_config()->get('comments', array());
		$this->_use_ajax = Arr::get($comment_config, 'use_ajax', FALSE);
	}

	/**
	 * Generate the comments.
	 *
	 * @access	public
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
	 * @access	protected
	 * @return	void
	 */
	protected function _comments()
	{
		$post = $this->_post;

		// Get comments
		$comments = MMI_Blog_Comment::factory($this->_driver)->get_comments($post->id);

		// Inject media
		if (class_exists('MMI_Request'))
		{
			MMI_Request::less()->add_url('post/comments', array('module' => 'mmi-blog'));
		}

		// Set response
		$this->request->response = Kostache::factory('mmi/blog/post/comments')->set(array
		(
			'comments'				=> $comments,
			'feed_url'				=> $post->comments_feed_guid,
			'header'				=> $this->_get_header($comments),
			'post_title'			=> $post->title,
			'use_ajax'				=> FALSE,
		))->render();
	}

	/**
	 * Generate the AJAX comments.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _comments_ajax()
	{
		$post = $this->_post;
		$template = MMI_Text::normalize_spaces(View::factory('mmi/blog/templates/js/comments')->render());
		$url = URL::site(Route::get('mmi/blog/rest')->uri(array
		(
			'controller'	=> 'comments',
			'post_id'		=> $post->id,
		)), TRUE);
		$js = "$(window).load(load_comments('$url', '$template'));";

		// Inject media
		if (class_exists('MMI_Request'))
		{
			MMI_Request::less()->add_url('post/comments', array('module' => 'mmi-blog'));
			MMI_Request::js()
				->add_url('jquery.tmpl', array('module' => 'mmi-blog'))
				->add_url('innershiv.min', array('module' => 'mmi-blog'))
				->add_url('ajax-comments', array('module' => 'mmi-blog'))
				->add_inline('ajax_comments', $js)
			;
		}

		// Set response
		$this->request->response = Kostache::factory('mmi/blog/post/comments')->set(array
		(
			'feed_url'		=> $post->comments_feed_guid,
			'header'		=> $this->_get_header(),
			'post_title'	=> $post->title,
			'use_ajax'		=> TRUE,
		))->render();
	}

	/**
	 * Get the comments header.
	 *
	 * @access	protected
	 * @param	array	an array of comments
	 * @return	string
	 */
	protected function _get_header($comments = NULL)
	{
		$num_comments = empty($comments) ? 0 : count($comments);
		return $num_comments.' '.ucfirst(Inflector::plural('Comment', $num_comments));
	}
} // End Controller_MMI_Blog_HMVC_Comments
