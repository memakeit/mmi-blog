<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP Options model.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Model_WP_Options extends Jelly_Model
{
	/**
	 * @var string the table name
	 */
	protected static $_table_name = 'wp_options';

	/**
	 * Initialize the model settings.
	 *
	 * @access	public
	 * @param	Jelly_Meta	meta data for the model
	 * @return	void
	 */
	public static function initialize(Jelly_Meta $meta)
	{
		$meta
			->table(self::$_table_name)
			->primary_key('option_id')
			->foreign_key('option_id')
			->fields(array
			(
				'option_id' => new Field_Primary,
				'blog_id' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(-2147483648, 2147483647)),
				)),
				'option_name' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(64)),
					'unique' => TRUE,
				)),
				'option_value' => new Field_Text(array
				(
					'default' => '',
					'rules' => array('max_length' => array(4294967296)),
				)),
				'autoload' => new Field_String(array
				(
					'default' => 'yes',
					'rules' => array('max_length' => array(20)),
				)),
			)
		);
	}

	/**
	 * Select one or more rows from the database by option id.
	 *
	 * @access	public
	 * @param	mixed	one or more option id's
	 * @param	integer	the blog id
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_option_id($option_id, $blog_id = 0, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_params['blog_id'] = $blog_id;
		if ( ! empty($option_id))
		{
			$where_params['option_id'] = $option_id;
		}
		$order_by = array('option_name' => NULL);
		$query_params = array('columns' => $columns, 'limit' => $limit, 'order_by' => $order_by, 'where_params' => $where_params);
		if ($as_array)
		{
			return MMI_DB::select(self::$_table_name, $as_array, $query_params);
		}
		else
		{
			return MMI_Jelly::select(self::$_table_name, $as_array, $query_params);
		}
	}
} // End Model_WP_Options
