<?php defined('SYSPATH') or die('No direct script access.');
/**
 * AJAX controller to retrieve blog comments for a post.
 *
 * @package		MMI Template
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Rest_Comments extends MMI_REST_JSON
{
	/**
	 * @var array method map
	 **/
	protected $_action_map = array
	(
		'GET' => array
		(
			'index',
		),
	);

	/**
	 * Get the comments for a post.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$driver = $this->request->param('driver');
		$post_id = intval($this->request->param('post_id'));
		$this->_response = MMI_Blog_Comment::factory($driver)->get_comments($post_id);
	}
} // End Controller_MMI_Blog_Rest_Comments
