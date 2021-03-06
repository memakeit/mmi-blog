<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Trackbacks HMVC controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_HMVC_Trackbacks extends Controller_MMI_Blog_HMVC
{
	/**
	 * @var boolean allow pingbacks?
	 **/
	protected $_allow_pingbacks;

	/**
	 * @var boolean allow trackbacks?
	 **/
	protected $_allow_trackbacks;

	/**
	 * @var boolean load comments via AJAX?
	 **/
	protected $_use_ajax;

	/**
	 * Initialize the trackback settings.
	 *
	 * @access	public
	 * @param	Request	the request that created the controller
	 * @return	void
	 */
	public function __construct($request)
	{
		parent::__construct($request);

		// Trackback settings
		$comment_config = MMI_Blog::get_config()->get('comments', array());
		$this->_allow_pingbacks = Arr::get($comment_config, 'pingbacks', TRUE);
		$this->_allow_trackbacks = Arr::get($comment_config, 'trackbacks', TRUE);
		$this->_use_ajax = Arr::get($comment_config, 'use_ajax', FALSE);
	}

	/**
	 * Generate the trackbacks.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		if ($this->_use_ajax)
		{
			$this->_trackbacks_ajax();
		}
		else
		{
			$this->_trackbacks();
		}
	}

	/**
	 * Generate the non-AJAX trackbacks.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _trackbacks()
	{
		// Inject media
		if (class_exists('MMI_Request'))
		{
			MMI_Request::less()->add_url('post/trackbacks', array('module' => 'mmi-blog'));
		}

		// Get trackbacks
		$post = $this->_post;
		$trackbacks = MMI_Blog_Comment::factory($this->_driver)->get_trackbacks($post->id);
		if (count($trackbacks) === 0)
		{
			return;
		}

		// Set response
		$this->request->response = Kostache::factory('mmi/blog/post/trackbacks')->set(array
		(
			'header'		=> $this->_get_header($trackbacks),
			'trackback_url'	=> $post->trackback_guid,
			'trackbacks'	=> $trackbacks,
			'use_ajax'		=> FALSE,
		))->render();
	}

	/**
	 * Generate the AJAX trackbacks.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _trackbacks_ajax()
	{
		$post = $this->_post;
		$template = MMI_Text::normalize_spaces(View::factory('mmi/blog/templates/js/trackbacks')->render());
		$url = URL::site(Route::get('mmi/blog/rest')->uri(array
		(
			'controller'	=> 'trackbacks',
			'post_id'		=> $post->id,
		)), TRUE);
		$js = "$(window).load(load_trackbacks('$url', '$template', {$this->_allow_pingbacks}, {$this->_allow_trackbacks}));";

		// Inject media
		if (class_exists('MMI_Request'))
		{
			MMI_Request::less()->add_url('post/trackbacks', array('module' => 'mmi-blog'));
			MMI_Request::js()
				->add_url('jquery.tmpl', array('module' => 'mmi-blog'))
				->add_url('innershiv.min', array('module' => 'mmi-blog'))
				->add_url('ajax-trackbacks', array('module' => 'mmi-blog'))
				->add_inline('ajax_trackbacks', $js)
			;
		}

		// Set response
		$this->request->response = Kostache::factory('mmi/blog/post/trackbacks')->set(array
		(
			'header'		=> $this->_get_header(),
			'trackback_url'	=> $post->trackback_guid,
			'use_ajax'		=> TRUE,
		))->render();
	}

	/**
	 * Get the trackbacks header.
	 *
	 * @access	protected
	 * @param	array	an array of trackbacks
	 * @return	string
	 */
	protected function _get_header($trackbacks = NULL)
	{
		$allow_pingbacks = $this->_allow_pingbacks;
		$allow_trackbacks = $this->_allow_trackbacks;
		$num_trackbacks = empty($trackbacks) ? 0 : count($trackbacks);
		if ($allow_pingbacks AND $allow_trackbacks)
		{
			$header = $num_trackbacks.' '.ucfirst(Inflector::plural('Pingback', $num_trackbacks)).' & '.ucfirst(Inflector::plural('Trackback', $num_trackbacks));
		}
		elseif ($allow_pingbacks)
		{
			$header = $num_trackbacks.' '.ucfirst(Inflector::plural('Pingback', $num_trackbacks));
		}
		elseif ($allow_trackbacks)
		{
			$header = $num_trackbacks.' '.ucfirst(Inflector::plural('Trackback', $num_trackbacks));
		}
		return $header;
	}
} // End Controller_MMI_Blog_HMVC_Trackbacks
