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
	 * Include the XML-RPC classes.
	 *
	 * @param	Request	the request that created the controller
	 * @return	void
	 */
	public function __construct($request)
	{
		parent::__construct($request);
		require_once Kohana::find_file('vendor', 'xmlrpc/xmlrpc_required');
	}

	/**
	 * Create the XML-RPC server.
	 *
	 * @return	void
	 */
	function action_index()
	{
		$server = new MMI_XMLRPC();
	}
} // End Controller_MMI_Blog_XMLRPC
