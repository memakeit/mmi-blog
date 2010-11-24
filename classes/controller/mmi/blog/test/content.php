<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Content HMVC test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Content extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = FALSE;

	/**
	 * @var string the CSS URL
	 **/
	protected $_css_url;

	/**
	 * @var string the page HTML
	 **/
	protected $_html;

	/**
	 * @var string the inline JavaScript
	 **/
	protected $_js_inline;

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
		$this->action_tabbed();
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
		$mode = MMI_Blog_Content::MODE_TABBED;
		$this->_process($mode);
		$this->_css_url = 'media/css/jquery.mmiTabs.css';

		$config = MMI_Blog::get_config();
		$mode_settings = Arr::get($config->get('content', array()), $mode, array());
		$tab_id = Arr::get($mode_settings, 'id', 'tabs_post_meta');
		$this->_js_inline = '$("#'.$tab_id.'").mmiTabs();';
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
		$this->_html = $html;
	}

	/**
	 * Render the page.
	 *
	 * @return	void
	 */
	public function after()
	{
		$html = array('<!DOCTYPE html>');
		$html[] = '<html lang="en">';
		$html[] = '<head>';
		$html[] = '<title>'.$this->_title.'</title>';
		$css_url = $this->_css_url;
		if ( ! empty($css_url))
		{
			$html[] = HTML::style($css_url);
		}
		$html[] = '</head>';
		$html[] = '<body>';
		$html[] = $this->_html;
		$js_inline = $this->_js_inline;
		if ( ! empty($js_inline))
		{
			$html[] = HTML::script('https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js');
			$html[] = HTML::script('media/js/jquery.mmiTabs.js');
			$html[] = '<script type="text/javascript">'.PHP_EOL.$js_inline.PHP_EOL.'</script>';
		}
		$html[] = '</body>';
		$html[] = '</html>';

		$this->request->response = implode(PHP_EOL,$html);
	}
} // End Controller_MMI_Blog_Test_Content
