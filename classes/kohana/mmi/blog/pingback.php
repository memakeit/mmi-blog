<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pingback functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Kohana_MMI_Blog_Pingback
{
	/**
	 * Create a pingback instance.
	 *
	 * @return	MMI_Blog_Pingback
	 */
	public static function factory()
	{
		return new MMI_Blog_Pingback;
	}
} // End Kohana_MMI_Blog_Pingback
