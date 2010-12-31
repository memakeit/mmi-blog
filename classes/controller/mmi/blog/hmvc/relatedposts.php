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
		$num_related_posts = MMI_Blog::get_post_config()->get('num_related_posts', 10);
		$related = MMI_Blog_Post::factory($this->_driver)->get_related($this->_post->id, $num_related_posts);
		if (empty($related))
		{
			$this->request->response = '';
			return;
		}

		// Inject media
		$parent = Request::instance();
		$parent->css->add_url('mmi-blog_related-posts', array('bundle' => 'blog'));

		// Set response
		$this->request->response = View::factory('mmi/blog/content/related_posts', array
		(
			'related' => $related,
		))->render();
	}
} // End Controller_MMI_Blog_HMVC_RelatedPosts
