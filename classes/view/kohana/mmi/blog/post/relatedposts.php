<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mustache view for a posts's related posts.
 *
 * @package		MMI Blog
 * @category	view
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class View_Kohana_MMI_Blog_Post_RelatedPosts extends Kostache
{
	/**
	 * @var array the related post settings
	 **/
	protected $_related_posts;

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
			case 'related_posts':
				$method = "_process_{$name}";
				$this->$method($value);
			break;
		}
	}

	/**
	 * Process the related post settings.
	 *
	 * @access	protected
	 * @param	array	the related post settings
	 * @return	void
	 */
	protected function _process_related_posts($related_posts)
	{
		if (empty($related_posts))
		{
			$this->_related_posts = FALSE;
		}
		else
		{
			$this->_related_posts = array('posts' => $related_posts);
		}
	}
} // End View_Kohana_MMI_Blog_Post_RelatedPosts
