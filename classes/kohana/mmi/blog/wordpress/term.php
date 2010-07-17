<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog term functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Kohana_MMI_Blog_Wordpress_Term extends MMI_Blog_Term
{
	/**
	 * @var string driver name
	 */
	protected static $_driver = MMI_Blog::BLOG_WORDPRESS;

	/**
	 * @var array database mappings ($column => $alias) for the terms table
	 */
	protected static $_db_mappings = array
	(
		'term_id'		=> 'id',
		'name'			=> 'name',
		'slug'			=> 'slug',
		'term_group'	=> 'meta_group',
	);

	/**
	 * @var array database mappings for the term taxonomy table
	 */
	protected static $_db_taxonomy_mappings = array
	(
		'term_taxonomy_id'	=> 'taxonomy_id',
		'term_id'			=> 'term_id',
		'taxonomy'			=> 'taxonomy',
		'description'		=> 'description',
		'parent'			=> 'meta_parent',
		'count'				=> 'count',
	);

	/**
	 * @var array database mappings for the term relationships table
	 */
	protected static $_db_relationship_mappings = array
	(
		'object_id'			=> 'post_id',
		'term_taxonomy_id'	=> 'taxonomy_id',
		'term_order'		=> 'meta_order',
	);

	/**
	 * Get categories. If no id is specified, all categories are returned.
	 *
	 * @param	mixed	id's being selected
	 * @param	boolean	reload cache from database?
	 * @return	array
	 */
	public function get_categories($ids = NULL, $reload_cache = FALSE)
	{
		$term_type = self::TYPE_CATEGORY;
		return $this->_get_terms($ids, $term_type, $reload_cache);
	}

	/**
	 * Get tags. If no id is specified, all tags are returned.
	 *
	 * @param	mixed	id's being selected
	 * @param	boolean	reload cache from database?
	 * @return	array
	 */
	public function get_tags($ids = NULL, $reload_cache = FALSE)
	{
		$term_type = self::TYPE_TAG;
		return $this->_get_terms($ids, $term_type, $reload_cache);
	}

	/**
	 * Get terms. If no id is specified, all terms are returned.
	 *
	 * @param	mixed	id's being selected
	 * @param	string	term type (category | tag)
	 * @param	boolean	reload cache from database?
	 * @return	array
	 */
	protected function _get_terms($ids = NULL, $term_type = self::TYPE_CATEGORY, $reload_cache = FALSE)
	{
		$driver = self::$_driver;
		$config = self::get_config(TRUE);
		$cache_id = $this->_get_cache_id($driver, 'terms_'.$term_type);
		$cache_lifetime = Arr::path($config, 'cache_lifetimes.'.$term_type, 0);
		$load_meta = Arr::path($config, 'features.'.$term_type.'_meta', FALSE);
		$terms = NULL;

		// Verify the term type is supported
		if ( ! Arr::path($config, 'features.'.$term_type, FALSE))
		{
			MMI_Log::log_info(__METHOD__, __LINE__, ucfirst($term_type).' term type not supported');
			return $terms;
		}

		if ( ! $reload_cache AND $cache_lifetime > 0)
		{
			$terms = MMI_Cache::get($cache_id, MMI_Cache::CACHE_TYPE_DATA, $cache_lifetime);
		}
		if (empty($terms))
		{
			// Load taxonomy data
			$taxonomy = Model_WP_Term_Taxonomy::select_by_taxomony($term_type, self::$_db_taxonomy_mappings);
			$term_ids_taxonomy_ids = array();
			foreach ($taxonomy as $item)
			{
				$term_ids_taxonomy_ids[intval($item['term_id'])] = intval($item['taxonomy_id']);
			}
			$term_ids = array_keys($term_ids_taxonomy_ids);

			// Load relationship data
			$relationships = Model_WP_Term_Relationships::select_by_term_taxonomy_id(array_values($term_ids_taxonomy_ids), self::$_db_relationship_mappings);
			$post_id;
			$taxonomy_id;
			$taxonomy_ids_post_ids = array();
			foreach ($relationships as $item)
			{
				$post_id = intval($item['post_id']);
				$taxonomy_id = intval($item['taxonomy_id']);
				if (array_key_exists($taxonomy_id, $taxonomy_ids_post_ids))
				{
					$taxonomy_ids_post_ids[$taxonomy_id][] = $post_id;
				}
				else
				{
					$taxonomy_ids_post_ids[$taxonomy_id] = array($post_id);
				}
			}

			$data = array();
			if (count($term_ids) > 0)
			{
				$data = Model_WP_Terms::select_by_id($term_ids, self::$_db_mappings, TRUE, 'term_id');
			}

			$terms = array();
			foreach ($data as $id => $fields)
			{
				$terms[$id] = self::factory($driver)->_load($fields, $load_meta);
			}
			if (is_array($terms) AND count($terms) > 0)
			{
				// Associate terms with post ids
				$post_ids;
				$taxonomy_id;
				$term_id;
				foreach ($terms as $term_id => $term)
				{
					$taxonomy_id = $term_ids_taxonomy_ids[$term_id];
					$post_ids = Arr::get($taxonomy_ids_post_ids, $taxonomy_id);
					if ( ! empty($post_ids))
					{
						$term->post_ids = $post_ids;
					}
					$terms[$term_id] = $term;
				}
			}

			if ($cache_lifetime > 0)
			{
				MMI_Cache::set($cache_id, MMI_Cache::CACHE_TYPE_DATA, $terms, $cache_lifetime);
			}
		}
		return $this->_extract_results($terms, $ids, FALSE);
	}

	/**
	 * Get id.
	 *
	 * @param	string	id
	 * @return	integer
	 */
	protected function _get_id($value)
	{
		return intval($value);
	}
} // End Kohana_MMI_Blog_Wordpress_Term
