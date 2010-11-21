/* mmi-blog/ajax-comments.js */

function load_comments(url, template)
{
	$.ajax(
	{
		cache: false,
		dataType: 'json',
		url: url,
		success: function(data)
		{
			$('#comments_loading').remove();

			// Comment header
			var count = data.length;
			var header = 'Comment';
			header = count + ' ' + (count === 1 ? header : header + 's');
			$('#comments_hdr > a').html(header + '<span>subscribe</span>');
			$('#comment_ct').text(header.toLowerCase());
			if (count === 0)
			{
				$('#comments_hdr').addClass('zero');
			}

			// Comments
			var div = $('<div></div>').hide();
			var html = div.append(template, data).html();
			$('#comments').append(innerShiv(html));
			$('#comments article p a').each(function(e)
			{
				$(this).attr('rel', 'nofollow external');
			});
		}
	});
}
