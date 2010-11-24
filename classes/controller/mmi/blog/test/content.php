<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Content HMVC test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Content extends MMI_Template
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * @var string the page title
	 **/
	protected $_title = 'Content Test';

	/**
	 * Test HMVC tabbed post widget.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$this->_process( MMI_Blog_Content::MODE_TABBED);
	}

	/**
	 * Test HMVC popular posts widget.
	 *
	 * @return	void
	 */
	public function action_popular()
	{
		$this->_process(MMI_Blog_Content::MODE_POPULAR);
	}

	/**
	 * Test HMVC random posts widget.
	 *
	 * @return	void
	 */
	public function action_random()
	{
		$this->_process(MMI_Blog_Content::MODE_RANDOM);
	}

	/**
	 * Test HMVC recent posts widget.
	 *
	 * @return	void
	 */
	public function action_recent()
	{
		$this->_process(MMI_Blog_Content::MODE_RECENT);
	}

	/**
	 * Test HMVC related posts widget.
	 *
	 * @return	void
	 */
	public function action_related()
	{
		$this->_process(MMI_Blog_Content::MODE_RELATED, array('post_id' => 1));
	}

	/**
	 * Test HMVC tabbed post widget.
	 *
	 * @return	void
	 */
	public function action_tabbed()
	{
		$this->_process( MMI_Blog_Content::MODE_TABBED);
	}

	/**
	 * Make the HMVC request and set the variables used during page rendering.
	 *
	 * @param	string	the rendering mode
	 * @param	array	an associative array of parameters
	 * @return	void
	 */
	protected function _process($mode, $parms = array())
	{
		if ( ! is_array($parms))
		{
			$parms = array();
		}
		$this->_title .= ' :: '.ucfirst($mode);

		$route = Route::get('mmi/blog/hmvc')->uri(array
		(
			'action' 		=> $mode,
			'controller'	=> 'content',
		));
		$request = Request::factory($route);
		$request->post = array_merge($parms, array
		(
			'mode' => $mode,
		));
		$html = $request->execute()->response;
		if ($this->debug)
		{
			$html = MMI_Debug::get($html, $mode).$html;
		}
		$view = View::factory('mmi/template/content/default')
			->set('content', $html)
		;
		$this->add_view('content', self::LAYOUT_ID, 'content', $view);
	}
} // End Controller_MMI_Blog_Test_Content
