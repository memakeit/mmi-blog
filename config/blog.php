<?php defined('SYSPATH') or die('No direct script access.');

// Blog settings
return array
(
    'cache_lifetimes' => array
    (
        'category'      => 4 * Date::HOUR,
        'comment'       => 0,
        'post'          => 2 * Date::HOUR,
        'tag'           => 4 * Date::HOUR,
        'user'          => 8 * Date::HOUR,
    ),
    'features' => array
    (
        'category'          => TRUE,
        'category_meta'     => FALSE,
        'comment'           => TRUE,
        'comment_gravatar'  => TRUE,
        'comment_meta'      => FALSE,
        'post_meta'         => FALSE,
        'tag'               => FALSE,
        'tag_meta'          => FALSE,
        'user'              => TRUE,
        'user_meta'         => FALSE,
    ),
    'gravatar' => array
    (
        'defaults' => array
        (
            'img'       =>'/media/img/icons/gravatar_default_100_v001.png',
            'rating'    => 'pg',
            'size'      => '100'
        ),
        'valid' => array
        (
            'img'       => array('identicon', 'monsterid', 'wavatar'),
            'rating'    => array('g', 'pg', 'r', 'x'),
            'size'      => array('min' => 1, 'max' => 512)
        )
    ),
);