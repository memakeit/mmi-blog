<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mustache view for a posts's previous and next post links.
 *
 * @package		MMI Blog
 * @category	view
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class View_Kohana_MMI_Blog_Post_PrevNext extends Kostache
{
	/**
	 * @var array the previous link settings
	 **/
	protected $_prev;

	/**
	 * @var array the next link settings
	 **/
	protected $_next;

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
			case 'prev':
			case 'next':
				$method = "_process_{$name}";
				$this->$method($value);
			break;
		}
	}

	/**
	 * Process the previous link settings.
	 *
	 * @access	protected
	 * @param	array	the previous link settings
	 * @return	void
	 */
	protected function _process_prev($prev)
	{
		if (empty($prev))
		{
			$this->_prev = FALSE;
		}
		else
		{
			$this->_prev = $prev;
		}
	}

	/**
	 * Process the next link settings.
	 *
	 * @access	protected
	 * @param	array	the next link settings
	 * @return	void
	 */
	protected function _process_next($next)
	{
		if (empty($next))
		{
			$this->_next = FALSE;
		}
		else
		{
			$this->_next = $next;
		}
	}
} // End View_Kohana_MMI_Blog_Post_PrevNext
