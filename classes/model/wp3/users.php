<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP Users model.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Model_WP_Users extends Jelly_Model
{
	/**
	 * @var string the table name
	 */
	protected static $_table_name = 'wp3_users';

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
			->primary_key('ID')
			->foreign_key('ID')
			->fields(array
			(
				'ID' => new Field_Primary,
				'user_login' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(60)),
				)),
				'user_pass' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(64)),
				)),
				'user_nicename' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(50)),
				)),
				'user_email' => new Field_Email(array
				(
					'default' => '',
					'rules' => array('max_length' => array(100)),
				)),
				'user_url' => new Field_Url(array
				(
					'default' => '',
					'rules' => array('max_length' => array(100)),
				)),
				'user_registered' => new Field_String(array
				(
					'default' => '0000-00-00 00:00:00',
				)),
				'user_activation_key' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(60)),
				)),
				'user_status' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(-2147483648, 2147483647)),
				)),
				'display_name' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(250)),
				)),
			)
		);
	}

	/**
	 * Select one or more rows from the database by id.
	 *
	 * @param	mixed	one or more id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	string	if specified, the key to be used when returning an associative array
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_id($ids, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
	{
		$where_parms = array();
		if (MMI_Util::is_set($ids))
		{
			$where_parms['ID'] = $ids;
		}
		$query_parms = array('columns' => $columns, 'limit' => $limit, 'where_parms' => $where_parms);
		if ($as_array)
		{
			return MMI_DB::select(self::$_table_name, $as_array, $array_key, $query_parms);
		}
		else
		{
			return MMI_Jelly::select(self::$_table_name, $as_array, $array_key, $query_parms);
		}
	}
} // End Model_WP_Users
