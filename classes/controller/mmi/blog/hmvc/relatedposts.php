<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Related posts HMVC controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_HMVC_RelatedPosts extends Controller_MMI_Blog_HMVC
{
	/**
	 * Generate the related post links.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$num_related_posts = MMI_Blog::get_post_config()->get('num_related_posts', 5);
		$related_posts = MMI_Blog_Post::factory($this->_driver)->get_related($this->_post->id, $num_related_posts);
		if (empty($related_posts))
		{
			$this->request->response = '';
			return;
		}

		// Inject media
		if (class_exists('MMI_Request'))
		{
			MMI_Request::less()->add_url('post/relatedposts', array('bundle' => 'blog', 'module' => 'mmi-blog'));
		}

		// Set response
		$this->request->response = Kostache::factory('mmi/blog/post/relatedposts')->set(array
		(
			'related_posts' => $related_posts,
		))->render();
	}
} // End Controller_MMI_Blog_HMVC_RelatedPosts
