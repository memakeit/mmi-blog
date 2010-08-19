/* mmi-blog/ajax-comments.js */

function load_comments(url, template)
{
	$.ajax({
		cache: false,
		dataType: 'json',
		url: url,
		success: function(data){
			$('#comments_loading').remove();

			// Comment header
			var count = data.length;
			var header = 'Comment';
			header = count + ' ' + (count === 1 ? header : header + 's');
			$('#comments_hdr > a').text(header);
			$('#comment_ct').text(header.toLowerCase());

			// Comments
			var div = $('<div></div>').hide();
			var html = div.append(template, data).html();
			$('#comments').append(innerShiv(html));
		}
	});
}
