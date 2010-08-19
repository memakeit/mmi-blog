<?php defined('SYSPATH') or die('No direct script access.');
/**
 * AJAX controller to retrieve trackbacks for a blog post.
 *
 * @package		MMI Template
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Rest_Trackbacks extends MMI_REST_JSON
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
	 * Get the trackbacks for a blog post.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$driver = $this->request->param('driver');
		$post_id = intval($this->request->param('post_id'));
		$trackbacks = MMI_Blog_Comment::factory($driver)->get_trackbacks($post_id);

		// Process trackbacks
		if (is_array($trackbacks) AND count($trackbacks) > 0)
		{
			$trackbacks = array_values($trackbacks);
			$last = count($trackbacks) - 1;
			foreach ($trackbacks as $idx => $trackback)
			{
				// Is first or last comment?
				$trackbacks[$idx]->is_first = ($idx === 0);
				$trackbacks[$idx]->is_last = ($idx === $last);

				unset
				(
					$trackbacks[$idx]->approved,
					$trackbacks[$idx]->author_email,
					$trackbacks[$idx]->content,
					$trackbacks[$idx]->driver,
					$trackbacks[$idx]->gravatar_url,
					$trackbacks[$idx]->meta,
					$trackbacks[$idx]->parent_id,
					$trackbacks[$idx]->post_id,
					$trackbacks[$idx]->timestamp,
					$trackbacks[$idx]->type,
					$trackbacks[$idx]->user_id
				);
			}
		}
		else
		{
			$trackbacks = array();
		}
		$this->_response = $trackbacks;
	}
} // End Controller_MMI_Blog_Rest_Trackbacks
