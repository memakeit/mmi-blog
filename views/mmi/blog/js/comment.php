<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/js/comment

?>
<article class="comment{{if is_first}} first{{/if}}{{if is_last}} last{{/if}}" id="comment-${id}">
	<figure class="alpha grid_2">
		<img width="${img_size}" height="${img_size}" alt="${author}" src="${gravatar_url}">
	</figure>
	<header class="omega grid_6">
		By
		{{if author_url}}
			<a href="${author_url}" rel="external nofollow">${author}</a>
		{{else}}
			${author}
		{{/if}}
		on <time pubdate="" datetime="${time_attribute}">${time_content}</time>
	</header>
	<p>{{html content}}</p>
</article>
