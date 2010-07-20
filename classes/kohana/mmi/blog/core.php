<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Core blog functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
abstract class Kohana_MMI_Blog_Core
{
	// Class constants
	const META_PREFIX = 'meta_';

	/**
	 * @var Kohana_Config blog settings
	 */
	protected static $_config;

	/**
	 * Load an object with data from an array.
	 * This method is chainable.
	 *
	 * @param	array	data
	 * @param	boolean	load meta data?
	 * @return	mixed
	 */
	protected function _load($data = array(), $load_meta = FALSE)
	{
		$meta_prefix = self::META_PREFIX;
		$meta_prefix_length = strlen($meta_prefix);
		foreach ($data as $name => $value)
		{
			if (substr($name, 0, $meta_prefix_length) === $meta_prefix)
			{
				// Process meta data
				if ($load_meta)
				{
					$name = substr($name, $meta_prefix_length);
					$method = '_get_'.$name;
					if (method_exists($this, $method))
					{
						$this->meta[$name] = $this->$method($value);
					}
					else
					{
						$this->meta[$name] = $value;
					}
				}
			}
			else
			{
				// Process non-meta data
				$method = '_get_'.$name;
				if (method_exists($this, $method))
				{
					$this->$name = $this->$method($value);
				}
				else
				{
					$this->$name = $value;
				}
			}
		}
		return $this;
	}

	/**
	 * Get the cache id.
	 *
	 * @param	string	blog driver
	 * @param	mixed	id's being cached
	 * @return	string
	 */
	protected function _get_cache_id($driver, $type, $ids = NULL)
	{
		$cache_id = $driver.'_blog_'.$type;
		if ( ! empty($ids))
		{
			if (is_array($ids) AND count($ids) > 0)
			{
				$cache_id .= '_'.implode('_', $ids);
			}
			else
			{
				$cache_id .= '_'.$ids;
			}
		}
		return $cache_id;
	}

	/**
	 * Extract results from a data set based on ids.
	 *
	 * @param	array	items
	 * @param	mixed	id's being extracted
	 * @param	boolean	preserve array keys?
	 * @return	array
	 */
	protected function _extract_results($items, $ids = array(), $preserve_keys = FALSE)
	{
		$result = $preserve_keys ? $items : (array_values($items));
		if (MMI_Util::is_set($ids))
		{
			$result = array();
			if (is_array($ids) AND count($ids) > 0)
			{
				$temp;
				foreach ($ids as $id)
				{
					$temp = Arr::get($items, $id);
					if ( ! empty($temp))
					{
						if ($preserve_keys)
						{
							$result[$id] = $temp;
						}
						else
						{
							$result[] = $temp;
						}
					}
				}
			}
			else
			{
				$temp = Arr::get($items, $ids);
				if ( ! empty($temp))
				{
					if ($preserve_keys)
					{
						$result[$ids] = $temp;
					}
					else
					{
						$result[] = $temp;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Extract multiple results from a data set based on ids.
	 *
	 * @param	array	items
	 * @param	string	item key
	 * @param	mixed	id's being extracted
	 * @param	boolean	preserve array keys?
	 * @return	array
	 */
	protected function _extract_multiple_results($items, $item_key = 'id', $ids = array(), $preserve_keys = FALSE)
	{
		$result = $preserve_keys ? $items : (array_values($items));
		if (MMI_Util::is_set($ids))
		{
			if (is_array($ids) AND count($ids) > 0)
			{
				$result = array();
				foreach ($items as $item)
				{
					if (in_array($item->$item_key, $ids, TRUE))
					{
						if ($preserve_keys)
						{
							$result[$item->$item_key] = $item;
						}
						else
						{
							$result[] = $item;
						}
					}
				}
			}
		}
		return $result;
	}
} // End Kohana_MMI_Blog_Core
