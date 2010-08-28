<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Related posts HMVC controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_HMVC_RelatedPosts extends MMI_HMVC
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
	 * Set the post and driver.
	 *
	 * @param	Request	the request that created the controller
	 * @return	void
	 */
	public function __construct($request)
	{
		parent::__construct($request);

		// Load parameters
		$post = isset($request->post) ? ($request->post) : array();
		$this->_post = Arr::get($post, 'post');
		$this->_driver = $this->_post->driver;
	}

	/**
	 * Generate the related post links.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$num_related_posts = MMI_Blog::get_post_config()->get('num_related_posts', 10);
		$related = MMI_Blog_Post::factory($this->_driver)->get_related($this->_post->id, $num_related_posts);
		if (empty($related))
		{
			$this->request->response = '';
			return;
		}

		// Inject media
		$parent = Request::instance();
		$parent->css->add_url('mmi-blog_related.posts', array('bundle' => 'blog'));

		// Set response
		$view = View::factory('mmi/blog/content/related_posts')
			->set('related', $related)
		;
		$this->request->response = $view->render();
	}
} // End Controller_MMI_Blog_HMVC_RelatedPosts
