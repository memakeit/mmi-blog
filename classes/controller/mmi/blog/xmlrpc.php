<?php defined('SYSPATH') or die('No direct script access.');
/**
 * XML-RPC controller
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_XMLRPC extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug_on = FALSE;

	/**
	 * Create the XML-RPC server.
	 *
	 * @return	void
	 */
	function action_index()
	{
		$server = MMI_XMLRPC::server();
	}
} // End Controller_MMI_Blog_XMLRPC
