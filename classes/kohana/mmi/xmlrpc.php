<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * XML-RPC server.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://www.xmlrpc.com/spec
 */
class Kohana_MMI_XMLRPC
{
	// Error constants
	const TRANSPORT_ERROR					= -32300;
	const SYSTEM_ERROR						= -32400;
	const APPLICATION_ERROR					= -32500;

	const SERVER_METHODCALL_ERROR			= -32600;
	const SERVER_METHOD_NOT_FOUND			= -32601;
	const SERVER_INVALID_METHOD_PARAMETERS	= -32602;
	const SERVER_INTERNAL_ERROR				= -32603;

	const PARSE_ERROR						= -32700;
	const PARSE_UNSUPPORTED_ENCODING		= -32701;
	const PARSE_INVALIDCHARACTER			= -32702;

	/**
	 * @var array the methods supported by the XML-RPC server
	 */
	protected static $_methods = array
	(
		'datetime'		=> array('MMI_XMLRPC', 'datetime'),
		'pingback.ping'	=> array('MMI_Blog_Pingback', 'receive'),
	);

	/**
	 * @var IXR_Server singleton instance of the XML-RPC server object
	 */
	protected static $_server;

	/**
	 * Return the singleton instance of the XML-RPC server. If no instance
	 * exists, a new one is created.
	 *
	 * @return	IXR_Server
	 */
	public static function server()
	{
		if ( ! self::$_server)
		{
			// Create the server instance
			require_once Kohana::find_file('vendor', 'xmlrpc/xmlrpc_required');
			self::$_server = new IXR_Server(self::$_methods);
		}
		return self::$_server;
	}

	/**
	 * Get the current date and time.
	 *
	 * @return	string
	 */
	public static function datetime()
	{
		return date('F j, Y @ h:i:s a');
	}
} // End Kohana_MMI_XMLRPC
