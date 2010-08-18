function load_comments(url, tmpl_comments, tmpl_trackbacks, allow_pingbacks, allow_trackbacks)
{
	$.ajax({
		cache: false,
		dataType: 'json',
		url: url,
		success: function(data){
			process_comments(data.comments, tmpl_comments);
			if (allow_pingbacks || allow_trackbacks)
			{
				process_trackbacks(data.trackbacks, tmpl_trackbacks, allow_pingbacks, allow_trackbacks);
			}
		}
	});
};

function process_comments(data, template)
{
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

function process_trackbacks(data, template, allow_pingbacks, allow_trackbacks)
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
	$('#trackbacks_hdr > span').text(header);

	// Trackbacks
	var div = $('<div></div>').hide();
	var html = div.append(template, data).html();
	$('#trackbacks > ol').append(innerShiv(html));
}
