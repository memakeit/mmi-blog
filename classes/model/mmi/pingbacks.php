<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pingbacks model.
 *
 * @package     MMI Blog
 * @author      Me Make It
 * @copyright   (c) 2009 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Model_MMI_Pingbacks extends Jelly_Model
{
    protected static $_table_name = 'mmi_pingbacks';
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
                        'pingback'  => 'pingback',
                        'trackback' => 'trackback',
                    ),
                    'default' => 'pingback',
                )),
                'url_xmlrpc' => new Field_Url(array
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
                'post_data' => new Field_Serialized,
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
                'error_number' => new Field_Integer(array
                (
                    'default' => 0,
                    'rules' => array('range' => array(0, 16777215)),
                )),
                'error_message' => new Field_String(array
                (
                    'default' => '',
                    'filters' => array('trim' => NULL),
                    'rules' => array('max_length' => array(255)),
                )),
                'response' => new Field_Serialized,
                'headers' => new Field_Serialized,
                'curl_info' => new Field_Serialized,
                'curl_options' => new Field_Serialized,
                'date_created' => new Field_Timestamp(array
                (
                    'auto_now_create' => TRUE,
                    'pretty_format' => 'Y-m-d G:i:s',
                )),
            )
    	);
	}

    public static function select_by_id($ids, $as_array = TRUE, $array_key = NULL, $limit = NULL)
    {
        $where_parms = array();
        if (MMI_Util::is_set($ids))
        {
            $where_parms['id'] = $ids;
        }
        $query_parms = array('limit' => $limit, 'where_parms' => $where_parms);
        return MMI_Jelly::select(self::$_table_name, $as_array, $array_key, $query_parms);
    }
} // End Model_MMI_Pingbacks