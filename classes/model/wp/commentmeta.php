<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP CommentMeta model.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Model_WP_CommentMeta extends Jelly_Model
{
	/**
	 * @var string the table name
	 */
	protected static $_table_name = 'wp_commentmeta';

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
			->primary_key('meta_id')
			->foreign_key('meta_id')
			->fields(array
			(
				'meta_id' => new Field_Primary,
				'comment_id' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(0, 18446744073709551615)),
				)),
				'meta_key' => new Field_String(array
				(
					'null' => TRUE,
					'rules' => array('max_length' => array(255)),
				)),
				'meta_value' => new Field_Text(array
				(
					'null' => TRUE,
					'rules' => array('max_length' => array(4294967296)),
				)),
			)
		);
	}

	/**
	 * Select one or more rows from the database by meta id.
	 *
	 * @param	mixed	one or more meta id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_meta_id($meta_id, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_params = array();
		if (MMI_Util::is_set($meta_id))
		{
			$where_params['meta_id'] = $meta_id;
		}
		$order_by = array('comment_id' => NULL, 'meta_key' => NULL);
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

	/**
	 * Select one or more rows from the database by comment id.
	 *
	 * @param	mixed	one or more comment id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_comment_id($comment_id, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_params = array();
		if (MMI_Util::is_set($comment_id))
		{
			$where_params['comment_id'] = $comment_id;
		}
		$order_by = array('comment_id' => NULL, 'meta_key' => NULL);
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
} // End Model_WP_CommentMeta
