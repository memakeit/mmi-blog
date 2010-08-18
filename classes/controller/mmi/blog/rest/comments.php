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
	 * Get the comments and trackbacks for a post.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$driver = $this->request->param('driver');
		$post_id = intval($this->request->param('post_id'));
		$mmi_comments = MMI_Blog_Comment::factory($driver);
		$comments = $mmi_comments->get_comments($post_id);
		$mmi_comments->separate($comments, $trackbacks);

		$obj = new stdClass;
		$obj->comments = $this->_process_comments($comments);
		$obj->trackbacks = $this->_process_trackbacks($trackbacks);
		$this->_response = $obj;
	}

	/**
	 * Add fields for gravatar settings, formatted dates, and whether the
	 * comment is the first or last in the list. Remove unused fields.
	 *
	 * @param	array	an array of comment objects
	 * @return	array
	 */
	protected function _process_comments($comments)
	{
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
				unset
				(
					$comments[$idx]->approved,
					$comments[$idx]->author_email,
					$comments[$idx]->meta,
					$comments[$idx]->parent_id,
					$comments[$idx]->post_id,
					$comments[$idx]->timestamp,
					$comments[$idx]->type,
					$comments[$idx]->user_id
				);
			}
		}
		else
		{
			$comments = array();
		}
		return $comments;
	}

	/**
	 * Add fields for whether the trackback is the first or last in the list.
	 * Remove unused fields.
	 *
	 * @param	array	an array of trackback objects
	 * @return	array
	 */
	protected function _process_trackbacks($trackbacks)
	{
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
		return $trackbacks;
	}
} // End Controller_MMI_Blog_Rest_Comments
