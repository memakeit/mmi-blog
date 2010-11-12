/* mmi-blog/post.js */

$(window).load(function()
{
	$('#comments_hdr')
		.delegate('a', 'mouseover', function()
		{
			$(this).find('span').show();
		})
		.delegate('a', 'mouseout', function()
		{
			$(this).find('span').hide();
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
});
