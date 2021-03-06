<?php defined('SYSPATH') or die('No direct script access.');

// mmi/blog/templates/js/comments

?>
<article class="comment{{if is_first}} first{{/if}}{{if is_last}} last{{/if}}" id="comment-${id}">
	<figure class="grid_2 alpha">
		<img width="${img_size}" height="${img_size}" alt="${author}" src="${gravatar_url}">
	</figure>
	<header class="grid_6 omega">
		By
		{{if author_url}}
			<a href="${author_url}" rel="external nofollow">${author}</a>
		{{else}}
			${author}
		{{/if}}
		on <time pubdate="" datetime="${time_attribute}">${time_content}</time>
	</header>
	{{html content}}
</article>
