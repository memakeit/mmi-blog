<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mustache view for a posts's comments.
 *
 * @package		MMI Blog
 * @category	view
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class View_Kohana_MMI_Blog_Post_Comments extends Kostache
{
	/**
	 * @var string the header text
	 **/
	public $header;

	/**
	 * @var string the corresponding post title
	 **/
	public $post_title;

	/**
	 * @var boolean use AJAX to load the comments
	 **/
	public $use_ajax;

	/**
	 * @var array the comment settings
	 **/
	protected $_comments = TRUE;

	/**
	 * @var integer the number of comments
	 **/
	protected $_comment_count;

	/**
	 * @var array the Atom feed settings
	 **/
	protected $_feed_url;

	/**
	 * Set a variable.
	 *
	 * @access	public
	 * @param	string	the parameter name
	 * @param	mixed	the parameter value
	 * @return	void
	 */
	public function __set($name, $value)
	{
		$name = trim(strtolower($name));
		switch ($name)
		{
			case 'comments':
			case 'feed_url':
				$method = "_process_{$name}";
				$this->$method($value);
			break;
		}
	}

	/**
	 * Process the comment settings.
	 *
	 * @access	protected
	 * @param	array	the comment settings
	 * @return	void
	 */
	protected function _process_comments($comments)
	{
		// Gravatar defaults
		$gravatar = MMI_Gravatar::get_config()->get('defaults', array());
		$gravatar_default_img = Arr::get($gravatar, 'img');
		$gravatar_size = Arr::get($gravatar, 'size');

		if ( ! empty($comments))
		{
			$comment_count = count($comments);
			$this->_comment_count = $comment_count;
			$items = array();

			$i = 0;
			$last = $comment_count - 1;
			foreach ($comments as $comment)
			{
				// 	Set CSS class
				$class = 'comment';
				if ($i === 0)
				{
					$class .= ' first';
				}
				if ($i === $last)
				{
					$class .= ' last';
				}
				$temp['comment_class']['class'] = trim($class);
				$i++;

				$author_email = $comment->author_email;
				if (empty($author_email))
				{
					$gravatar_url = $gravatar_default_img;
				}
				else
				{
					$gravatar_url = MMI_Gravatar::get_gravatar_url($author_email);
				}

				$author_name = $comment->author;
				$author_url = $comment->author_url;
				if (empty($author_url))
				{
					$author = HTML::chars($author_name, FALSE);
				}
				else
				{
					$author = HTML::anchor($author_url, HTML::chars($author_name, FALSE), array('rel' => 'external nofollow'));
				}

				$comment_date = $comment->timestamp;
				$items[] = array
				(
					'author'				=> $author,
					'author_name'			=> $author_name,
					'content'				=> Text::auto_p($comment->content),
					'datetime'				=> gmdate('c', $comment_date),
					'formatted_datetime'	=> gmdate('F j, Y @ g:i a', $comment_date),
					'gravatar_size'			=> $gravatar_size,
					'gravatar_url'			=> $gravatar_url,
					'id'					=> $comment->id,
				);
			}
			$this->_comments = array('items' => $items);
		}
	}

	/**
	 * Process the feed URL.
	 *
	 * @access	protected
	 * @param	array	the feed URL
	 * @return	void
	 */
	protected function _process_feed_url($url)
	{
		if (empty($url))
		{
			$this->_feed_url = FALSE;
		}
		else
		{
			$this->_feed_url = array('url' => $url);
		}
	}

	/**
	 * Get the header class settings.
	 *
	 * @access	protected
	 * @return	mixed
	 */
	protected function _header_class()
	{
		if ($this->_comment_count === 0)
		{
			return array('class' => 'zero');
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Get the loading comments image settings.
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function _loading_img()
	{
		return array
		(
			'src'		=> MMI_Request::get_media_url(MMI_Request::MGR_IMG, 'animated/loading15x128.gif'),
			'height'	=> 15,
			'width'		=> 128,
		);
	}
} // End View_Kohana_MMI_Blog_Post_Comments

