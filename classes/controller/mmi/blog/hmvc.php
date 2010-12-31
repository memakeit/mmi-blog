<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base blog HMVC controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
abstract class Controller_MMI_Blog_HMVC extends Controller
{
	/**
	 * @var string the driver name
	 */
	protected $_driver;

	/**
	 * @var MMI_Blog_Post the blog post
	 **/
	protected $_post;

	/**
	 * Only accept internal requests.
	 * Initialize the sharing settings.
	 *
	 * @access	public
	 * @param	Request	the request that created the controller
	 * @return	void
	 */
	public function __construct($request)
	{
		if (Request::instance() === Request::current())
		{
			throw new Kohana_Request_Exception('Invalid external request');
		}
		parent::__construct($request);

		// Load parameters
		$post = (isset($request->post)) ? ($request->post) : (array());
		$this->_post = Arr::get($post, 'post');
		if ($this->_post instanceof MMI_Blog_Post)
		{
			$this->_driver = $this->_post->driver;
		}
		else
		{
			$this->_driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		}
	}
} // End Controller_MMI_Blog_HMVC
