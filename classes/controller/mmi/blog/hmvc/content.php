<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Content HMVC controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_HMVC_Content extends Controller_MMI_Blog_HMVC
{
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
	 * Load the request parameters.
	 *
	 * @access	public
	 * @param	Request	the request that created the controller
	 * @return	void
	 */
	public function __construct($request)
	{
		parent::__construct($request);

		// Load parameters
		$config = MMI_Blog::get_config();
		$post = $this->_post;
		if ( ! empty($post))
		{
			$this->_mode = Arr::get($post, 'mode', MMI_Blog_Content::MODE_POPULAR);
			$mode_settings = Arr::get($config->get('content', array()), $this->_mode, array());
			$this->_header = Arr::get($post, 'header', Arr::get($mode_settings, 'header'));
			$this->_max_items = Arr::get($post, 'max_items', Arr::get($mode_settings, 'max_items', 5));
		}
	}

	/**
	 * Generate a list of popular posts.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_popular()
	{
		$links = $this->_get_links($this->_mode);
		$this->_inject_media();
		$this->request->response = MMI_Template_Content::get_link_box($this->_header, $links, array
		(
			'id' => 'sb_posts_'.$this->_mode,
		));
	}

	/**
	 * Generate a list of random posts.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_random()
	{
		$links = $this->_get_links($this->_mode);
		$this->_inject_media();
		$this->request->response = MMI_Template_Content::get_link_box($this->_header, $links, array
		(
			'id' => 'sb_posts_'.$this->_mode,
		));
	}

	/**
	 * Generate a list of recent posts.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_recent()
	{
		$links = $this->_get_links($this->_mode);
		$this->_inject_media();
		$this->request->response = MMI_Template_Content::get_link_box($this->_header, $links, array
		(
			'id' => 'sb_posts_'.$this->_mode,
		));
	}

	/**
	 * Generate a list of related posts.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_related()
	{
		$links = $this->_get_links($this->_mode);
		$this->_inject_media();
		$this->request->response = MMI_Template_Content::get_link_box($this->_header, $links, array
		(
			'id' => 'sb_posts_'.$this->_mode,
		));
	}

	/**
	 * Generate a tabbed post list.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_tabbed()
	{
		$config = MMI_Blog::get_config();
		$mode_settings = Arr::get($config->get('content', array()), $this->_mode, array());
		$tab_id = Arr::get($mode_settings, 'id', 'tabs_post_meta');
		$order = Arr::get($mode_settings, 'order', array(MMI_Blog_Content::MODE_POPULAR, MMI_Blog_Content::MODE_RECENT));

		$tabs = array();
		foreach ($order as $mode => $header)
		{
			$list_items = MMI_Template_Content::get_link_list($this->_get_links($mode));
			$content = View::factory('mmi/template/content/box/tab_links', array
			(
				'list_items' => $list_items,
			))->render();
			$tabs[$header] = $content;
		}

		$this->_inject_media('$("#'.$tab_id.'").mmiTabs();');
		$this->request->response = MMI_Template_Content::get_tab_box('', $tab_id, $tabs, array
		(
			'id' => 'sb_posts_'.$this->_mode,
		));
	}

	/**
	 * Get the links.
	 *
	 * @access	protected
	 * @param	string	the type of links to get
	 * @return	array
	 */
	protected function _get_links($mode)
	{
		$links = array();
		switch ($mode)
		{
			case MMI_Blog_Content::MODE_POPULAR:
				$links = MMI_Blog_Post::factory($this->_driver)->get_popular($this->_max_items);
			break;

			case MMI_Blog_Content::MODE_RANDOM:
				$links = MMI_Blog_Post::factory($this->_driver)->get_random($this->_max_items);
			break;

			case MMI_Blog_Content::MODE_RECENT:
				$links = MMI_Blog_Post::factory($this->_driver)->get_recent($this->_max_items);
			break;

			case MMI_Blog_Content::MODE_RELATED:
				$post_id = Arr::get($this->_post, 'post_id');
				if ( ! empty($post_id))
				{
					$links = MMI_Blog_Post::factory($this->_driver)->get_related($post_id, $this->_max_items);
				}
			break;
		}
		return $links;
	}

	/**
	 * Inject CSS and JavaScript.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _inject_media($js_inline = NULL)
	{
		MMI_Request::css()->add_url('jquery.mmi.tabs', array('module' => 'mmi-template'));
		MMI_Request::js()->add_url('jquery.mmi.tabs', array('module' => 'mmi-template'));
		if ( ! empty($js_inline))
		{
			MMI_Request::js()->add_inline('mmi-tabs', $js_inline);
		}
	}
} // End Controller_MMI_Blog_HMVC_Content
