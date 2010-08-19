<?php defined('SYSPATH') or die('No direct script access.');

// HMVC routes
Route::set('mmi/blog/hmvc', 'mmi/blog/hmvc/<controller>(/<action>)')
->defaults(array
(
	'directory' => 'mmi/blog/hmvc',
));

// REST routes (used for AJAX requests)
Route::set('mmi/blog/rest', 'mmi/blog/rest/<controller>/<post_id>', array('id' => '\d+'))
->defaults(array
(
	'directory' => 'mmi/blog/rest',
));

// Blog routes
Route::set('mmi/blog/index', 'blog(/<page>)', array('page' => '\d+'))
->defaults(array
(
	'controller' 	=> 'index',
	'directory'		=> 'mmi/blog',
));
Route::set('mmi/blog/archive', 'archive/<year>/<month>(/<page>)', array('year' => '\d{4}', 'month' => '\d{2}', 'page' => '\d+'))
->defaults(array
(
	'action'		=> 'archive',
	'controller' 	=> 'index',
	'directory'		=> 'mmi/blog',
));
Route::set('mmi/blog/category', 'category/<slug>(/<page>)', array('slug' => '[a-zA-Z0-9\-]+', 'page' => '\d+'))
->defaults(array
(
	'action'		=> 'category',
	'controller' 	=> 'index',
	'directory'		=> 'mmi/blog',
));
Route::set('mmi/blog/tag', 'tag/<slug>(/<page>)', array('slug' => '[a-zA-Z0-9\-]+', 'page' => '\d+'))
->defaults(array
(
	'action'		=> 'tag',
	'controller' 	=> 'index',
	'directory'		=> 'mmi/blog',
));

Route::set('mmi/blog/post', 'blog/<year>/<month>/<slug>', array('year' => '\d{4}', 'month' => '\d{2}', 'slug' => '[a-zA-Z0-9\-]+'))
->defaults(array
(
	'controller' 	=> 'post',
	'directory'		=> 'mmi/blog',
));

Route::set('mmi/blog/trackback', 'trackback/<year>/<month>/<slug>', array('year' => '\d{4}', 'month' => '\d{2}', 'slug' => '[a-zA-Z0-9\-]+'))
->defaults(array
(
	'controller'	=> 'trackback',
	'directory'		=> 'mmi/blog',
));

Route::set('mmi/blog/feed', 'feed')
->defaults(array
(
	'controller'	=> 'feed',
	'directory'		=> 'mmi/blog',
));
Route::set('mmi/blog/comments', 'comments/<year>/<month>/<slug>', array('year' => '\d{4}', 'month' => '\d{2}', 'slug' => '[a-zA-Z0-9\-]+'))
->defaults(array
(
	'action'		=> 'comments',
	'controller'	=> 'feed',
	'directory'		=> 'mmi/blog',
));

// Test routes
if (Kohana::$environment !== Kohana::PRODUCTION)
{
	Route::set('mmi/blog/test', 'mmi/blog/test/<controller>(/<action>)')
	->defaults(array
	(
		'directory' => 'mmi/blog/test',
	));
}
