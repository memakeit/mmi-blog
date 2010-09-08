<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pingbacks model.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Model_MMI_Pingbacks extends Jelly_Model
{
	/**
	 * @var string the table name
	 */
	protected static $_table_name = 'mmi_pingbacks';

	/**
	 * Initialize the model settings.
	 *
	 * @param	Jelly_Meta	meta data for the model
	 * @return	void
	 */
	public static function initialize(Jelly_Meta $meta)
	{
		$meta
			->table(self::$_table_name)
			->primary_key('id')
			->foreign_key('id')
			->fields(array
			(
				'id' => new Field_Primary,
				'success' => new Field_Boolean(array
				(
					'default' => 0,
					'rules' => array('range' => array(0, 1)),
				)),
				'type' => new Field_Enum(array
				(
					'choices' => array
					(
						'pingback' => 'pingback',
						'trackback' => 'trackback',
					),
					'default' => 'pingback',
				)),
				'url_xmlrpc' => new Field_String(array
				(
					'rules' => array
					(
						'max_length' => array(255),
						'not_empty' => NULL,
					),
				)),
				'url_from' => new Field_Url(array
				(
					'rules' => array
					(
						'max_length' => array(255),
						'not_empty' => NULL,
					),
				)),
				'url_to' => new Field_Url(array
				(
					'rules' => array
					(
						'max_length' => array(255),
						'not_empty' => NULL,
					),
				)),
				'post_data' => new Field_Serialized(array
				(
					'null' => TRUE,
				)),
				'http_status_code' => new Field_Integer(array
				(
					'default' => 200,
					'rules' => array('range' => array(0, 999)),
				)),
				'content_type' => new Field_String(array
				(
					'default' => 'text/xml',
					'filters' => array('trim' => NULL),
					'rules' => array('max_length' => array(255)),
				)),
				'error_num' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(0, 16777215)),
				)),
				'error_msg' => new Field_String(array
				(
					'default' => '',
					'filters' => array('trim' => NULL),
					'rules' => array('max_length' => array(255)),
				)),
				'response' => new Field_Serialized(array
				(
					'null' => TRUE,
				)),
				'http_headers' => new Field_Serialized(array
				(
					'null' => TRUE,
				)),
				'curl_info' => new Field_Serialized(array
				(
					'null' => TRUE,
				)),
				'curl_options' => new Field_Serialized(array
				(
					'null' => TRUE,
				)),
				'date_created' => new Field_Timestamp(array
				(
					'auto_now_create' => TRUE,
					'pretty_format' => 'Y-m-d G:i:s',
				)),
			)
		);
	}

	/**
	 * Select one or more rows from the database by id.
	 *
	 * @param	mixed	one or more id's
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_id($id, $as_array = TRUE, $limit = NULL)
	{
		$where_parms = array();
		if (MMI_Util::is_set($id))
		{
			$where_parms['id'] = $id;
		}
		$query_parms = array('limit' => $limit, 'where_parms' => $where_parms);
		return MMI_Jelly::select(self::$_table_name, $as_array, $query_parms);
	}
} // End Model_MMI_Pingbacks
