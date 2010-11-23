<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Content HMVC controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_HMVC_Content extends MMI_HMVC
{
	/**
	 * @var string the blog driver
	 **/
	protected $_driver;

	/**
	 * @var string the header text
	 **/
	protected $_header;

	/**
	 * @var integer the maximum number of list items
	 **/
	protected $_max_items;

	/**
	 * @var string the content generation mode
	 **/
	protected $_mode;

	/**
	 * @var array the request post data
	 **/
	protected $_post;

	/**
	 * Load the request parameters.
	 *
	 * @param	Request	the request that created the controller
	 * @return	void
	 */
	public function __construct($request)
	{
		parent::__construct($request);

		// Load parameters
		$config = MMI_Blog::get_config();
		$this->_driver = $config->get('driver', MMI_Blog::DRIVER_WORDPRESS);

		$post = (isset($request->post)) ? ($request->post) : (array());
		if ( ! empty($post))
		{
			$this->_mode = Arr::get($post, 'mode', MMI_Blog_Content::MODE_POPULAR);
			$mode_settings = Arr::get($config->get('content', array()), $this->_mode, array());
			$this->_header = Arr::get($post, 'header', Arr::get($mode_settings, 'header'));
			$this->_max_items = Arr::get($post, 'max_items', Arr::get($mode_settings, 'max_items', 5));
		}
		$this->_post = $post;
	}

	/**
	 * Generate a list of popular posts.
	 *
	 * @return	void
	 */
	public function action_popular()
	{
		$links = MMI_Blog_Post::factory($this->_driver)->get_popular($this->_max_items);
		foreach ($links as $idx => $link)
		{
			$links[$idx]['url'] = $links[$idx]['guid'];
			unset($links[$idx]['guid']);
		}
		$this->request->response = MMI_Content::get_link_box($this->_header, $links, array
		(
			'id' => 'sb_posts_'.$this->_mode,
		));
	}

	/**
	 * Generate a list of random posts.
	 *
	 * @return	void
	 */
	public function action_random()
	{
		$links = MMI_Blog_Post::factory($this->_driver)->get_random($this->_max_items);
		foreach ($links as $idx => $link)
		{
			$links[$idx]['url'] = $links[$idx]['guid'];
			unset($links[$idx]['guid']);
		}
		$this->request->response = MMI_Content::get_link_box($this->_header, $links, array
		(
			'id' => 'sb_posts_'.$this->_mode,
		));
	}

	/**
	 * Generate a list of recent posts.
	 *
	 * @return	void
	 */
	public function action_recent()
	{
		$links = MMI_Blog_Post::factory($this->_driver)->get_recent($this->_max_items);
		foreach ($links as $idx => $link)
		{
			$links[$idx]['url'] = $links[$idx]['guid'];
			unset($links[$idx]['guid']);
		}
		$this->request->response = MMI_Content::get_link_box($this->_header, $links, array
		(
			'id' => 'sb_posts_'.$this->_mode,
		));
	}

	/**
	 * Generate a list of related posts.
	 *
	 * @return	void
	 */
	public function action_related()
	{
		$links = array();
		$post_id = Arr::get($this->_post, 'post_id');
		if ( ! empty($post_id))
		{
			$links = MMI_Blog_Post::factory($this->_driver)->get_related($post_id, $this->_max_items);
		}
		foreach ($links as $idx => $link)
		{
			$links[$idx]['url'] = $links[$idx]['guid'];
			unset($links[$idx]['guid']);
		}
		$this->request->response = MMI_Content::get_link_box($this->_header, $links, array
		(
			'id' => 'sb_posts_'.$this->_mode,
		));
	}
} // End Controller_MMI_Blog_HMVC_Content
