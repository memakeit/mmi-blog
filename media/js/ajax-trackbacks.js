/* mmi-blog/ajax-trackbacks.js */

function load_trackbacks(url, template, allow_pingbacks, allow_trackbacks)
{
	$.ajax(
	{
		cache: false,
		dataType: 'json',
		url: url,
		success: function(data)
		{
			if (data.length === 0)
			{
				$('#trackbacks').remove();
				return;
			}
			if (allow_pingbacks || allow_trackbacks)
			{
				// Trackback header
				var count = data.length;
				var header = '';
				if (allow_pingbacks && allow_trackbacks)
				{
					header = count + ' ' + (count === 1 ? 'Pingback' : 'Pingbacks') + ' & ' + (count === 1 ? 'Trackback' : 'Trackbacks');
				}
				else if (allow_pingbacks)
				{
					header = count + ' ' + (count === 1 ? 'Pingback' : 'Pingbacks');
				}
				else if (allow_trackbacks)
				{
					header = count + ' ' + (count === 1 ? 'Trackback' : 'Trackbacks');
				}
				$('#trackbacks_hdr > h3').text(header);

				// Trackbacks
				var div = $('<div></div>').hide();
				var html = div.append(template, data).html();
				$('#trackbacks > ol').append(innerShiv(html));
				$('#trackbacks').show();
			}
			else
			{
				$('#trackbacks').remove();
			}
		}
	});
}
