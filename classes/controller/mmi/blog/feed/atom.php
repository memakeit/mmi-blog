<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base Atom feed controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
abstract class Controller_MMI_Blog_Feed_Atom extends Controller
{
	/**
	 * @var string the cache type
	 **/
	public $cache_type = MMI_Cache::CACHE_TYPE_FEED;

	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = FALSE;

	/**
	 * @var MMI_Atom_Feed the feed object
	 **/
	protected $_feed;

	/**
	 * Create the Atom feed object.
	 *
	 * @param	object	the request that created the controller
	 * @return	void
	 */
	public function __construct(Request $request)
	{
		parent::__construct($request);
		$this->_feed = MMI_Atom_Feed::factory();
	}

	/**
	 * Set the content-type and response.
	 *
	 * @return	void
	 */
	public function after()
	{
		if ($this->debug)
		{
			MMI_Debug::dead_xml($this->_feed->render());
		}
		else
		{
			$this->request->headers['Content-Type'] = File::mime_by_ext('atom');
			$this->request->response = $this->_feed->render().'<!-- published @ '.gmdate('Y-m-d H:i:s').' GMT -->';
		}
	}
} // End Controller_MMI_Blog_Feed_Atom
