<?php defined('SYSPATH') or die('No direct script access.');
/**
 * AJAX controller to retrieve comments for a blog post.
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
	 * Get the comments for a blog post.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$post_id = intval($this->request->param('post_id'));
		$comments = MMI_Blog_Comment::factory($driver)->get_comments($post_id);

		// Process comments
		if (is_array($comments) AND count($comments) > 0)
		{
			// Get gravatar defaults
			$defaults = MMI_Gravatar::get_config()->get('defaults', array());
			$default_img = Arr::get($defaults, 'img');
			$default_img_size = Arr::get($defaults, 'size');

			$comments = array_values($comments);
			$last = count($comments) - 1;
			foreach ($comments as $idx => $comment)
			{
				// Add gravatar settings
				if (empty($comment->gravatar_url))
				{
					$comments[$idx]->gravatar_url = $default_img;
				}
				$comments[$idx]->img_size = $default_img_size;

				// Format dates
				$timestamp = $comment->timestamp;
				$comments[$idx]->time_attribute = gmdate('c', $timestamp);
				$comments[$idx]->time_content = gmdate('F j, Y @ g:i a', $timestamp);

				// Is first or last comment?
				$comments[$idx]->is_first = ($idx === 0);
				$comments[$idx]->is_last = ($idx === $last);

				// Unset unused fields
				unset($comments[$idx]->approved,
					$comments[$idx]->author_email,
					$comments[$idx]->driver,
					$comments[$idx]->meta,
					$comments[$idx]->parent_id,
					$comments[$idx]->post_id,
					$comments[$idx]->timestamp,
					$comments[$idx]->type,
					$comments[$idx]->user_id);
			}
		}
		else
		{
			$comments = array();
		}
		$this->_response = $comments;
	}
} // End Controller_MMI_Blog_Rest_Comments
