<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mustache view for a posts's trackbacks.
 *
 * @package		MMI Blog
 * @category	view
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class View_Kohana_MMI_Blog_Post_Trackbacks extends Kostache
{
	/**
	 * @var string the header text
	 **/
	public $header;

	/**
	 * @var boolean use AJAX to load the trackbacks
	 **/
	public $use_ajax;

	/**
	 * @var array the trackback URL settings
	 **/
	protected $_trackback_url;

	/**
	 * @var array the trackback settings
	 **/
	protected $_trackbacks = TRUE;

	/**
	 * Set a variable.
	 *
	 * @access	public
	 * @param	string	the parameter name
	 * @param	mixed	the parameter value
	 * @return	void
	 */
	public function __set($name, $value)
	{
		$name = trim(strtolower($name));
		switch ($name)
		{
			case 'trackback_url':
			case 'trackbacks':
				$method = "_process_{$name}";
				$this->$method($value);
			break;
		}
	}

	/**
	 * Process the trackback settings.
	 *
	 * @access	protected
	 * @param	array	the trackback settings
	 * @return	void
	 */
	protected function _process_trackbacks($trackbacks)
	{
		if ( ! empty($trackbacks))
		{
			$trackback_urls = array();
			foreach ($trackbacks as $trackback)
			{
				$trackback_urls[] = array
				(
					'id'	=> $trackback->id,
					'title'	=> $trackback->content,
					'url'	=> $trackback->author_url,
				);
			}
			$this->_trackbacks = array('trackback_urls' => $trackback_urls);
		}
	}

	/**
	 * Process the trackback URL settings.
	 *
	 * @access	protected
	 * @param	string	the trackback URL
	 * @return	void
	 */
	protected function _process_trackback_url($url)
	{
		if (empty($url))
		{
			$this->_trackback_url = FALSE;
		}
		else
		{
			$this->_trackback_url = array('url' => $url);
		}
	}
} // End View_Kohana_MMI_Blog_Post_Trackbacks

