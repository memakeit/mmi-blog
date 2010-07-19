<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP Terms model.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Model_WP_Terms extends Jelly_Model
{
	/**
	 * @var string the table name
	 */
	protected static $_table_name = 'wp3_terms';

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
			->primary_key('term_id')
			->foreign_key('term_id')
			->fields(array
			(
				'term_id' => new Field_Primary,
				'name' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(200)),
				)),
				'slug' => new Field_Slug(array
				(
					'default' => '',
					'rules' => array('max_length' => array(200)),
					'unique' => TRUE,
				)),
				'term_group' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(-9223372036854775808, 9223372036854775807)),
				)),
			)
		);
	}

	/**
	 * Select one or more rows from the database by term id.
	 *
	 * @param	mixed	one or more term id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	string	if specified, the key to be used when returning an associative array
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_term_id($term_ids, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
	{
		$where_parms = array();
		if (MMI_Util::is_set($term_ids))
		{
			$where_parms['term_id'] = $term_ids;
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
} // End Model_WP_Terms
