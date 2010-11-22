/* mmi-blog/post.js */

$(window).load(function()
{
	$('#content div.content a').each(function(e)
	{
		$(this).attr('rel', 'nofollow external');
	});

	$('#prev_post, #next_post')
		.delegate('a', 'mouseover', function()
		{
			$(this).parent().find('small').show();
		})
		.delegate('a', 'mouseout', function()
		{
			$(this).parent().find('small').hide();
		});

	$('#comments_hdr')
		.delegate('a', 'mouseover', function()
		{
			$(this).find('span').show();
		})
		.delegate('a', 'mouseout', function()
		{
			$(this).find('span').hide();
		});

	$('#comments article p a').each(function(e)
	{
		$(this).attr('rel', 'nofollow external');
	});
});
