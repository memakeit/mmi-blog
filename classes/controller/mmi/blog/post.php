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
	 * @var string the blog driver
	 **/
	protected $_driver;

	/**
	 * @var array the blog feature settings
	 **/
	protected $_features_config;

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
		unset($archive);

		$mmi_comments = MMI_Blog_Comment::factory($this->_driver);
		$comments = $mmi_comments->get_comments($post->id);
		$mmi_comments->separate($comments, $trackbacks);

		// Inject CSS and JavaScript
		$this->_inject_media();

		// Get and re-set the nav type
		$nav_type = MMI_Blog::get_nav_type();
		MMI_Blog::set_nav_type($nav_type);

		$this->_title = $post->title;

		$post_title = $post->title;
		$post_url = $post->guid;
		$view = View::factory('mmi/blog/post')
		 	->set('bookmarks', $this->_get_bookmarks($post_title, $post_url))
		 	->set('insert_retweet', TRUE)
			->set('is_homepage', FALSE)
			->set('post', $post)
			->set('toolbox', $this->_get_mini_toolbox($post_title, $post_url))
		;
		if (Arr::get($this->_features_config, 'comment', TRUE))
		{
			$view->set('comments', $this->_get_comments($comments, $trackbacks, $post));
		}

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
		if (Arr::get($this->_features_config, 'comment', TRUE))
		{
			$this->add_css_url('mmi-blog_comments', array('bundle' => 'blog'));
		}

		$this->add_js_url('mmi-social_addthis', array('bundle' => 'blog'));
	}

	protected function _get_comments($comments, $trackbacks, $post)
	{
		$this->_add_comments_ajax($post->id);

		$defaults = MMI_Gravatar::get_config()->get('defaults', array());
		$default_img = Arr::get($defaults, 'img');
		$default_img_size = Arr::get($defaults, 'size');

		$view = View::factory('mmi/blog/comments')
			->set('comments', $comments)
			->set('default_img', $default_img)
			->set('default_img_size', $default_img_size)
			->set('feed_url', $post->comments_feed_guid)
			->set('trackback_url', $post->trackback_guid)
			->set('trackbacks', $trackbacks)
		;
		return $view->render();
	}

	protected function _get_bookmarks($title, $url, $description = NULL)
	{
		$addthis = Request::factory('mmi/social/addthis/bookmarks');
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


	protected function _get_mini_toolbox($title, $url, $description = NULL)
	{
		$addthis = Request::factory('mmi/social/addthis/mini');
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

	protected function _get_tweet($title, $url, $description = NULL)
	{
		$addthis = Request::factory('mmi/social/addthis/tweet');
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

	protected function _add_comments_ajax($post_id)
	{

//		MMI_Debug::dead(file_get_contents(MODPATH.'mmi-blog\media\js\post.js'));
		$this->add_js_url('mmi-blog_jquery.nje.tmpl', array('bundle' => 'blog'));
		$url = URL::site(Route::get('mmi/blog/rest/comments')->uri(array
		(
			'driver'	=> $this->_driver,
			'post_id'	=> $post_id,
		)), TRUE);
$js = <<< EOJS
$(window).load(function(){
	$.getJSON('$url', function(data){
		alert(data[1].content);
	});
});
EOJS;
		$this->add_js_inline('ajax_comments', $js);
	}
} // End Controller_MMI_Blog_Post
