/* mmi-blog/post.js */

$(window).load(function()
{
	$('#comments_hdr')
		.delegate('a', 'mouseover', function(){
			$(this).find('span').show();
		})
		.delegate('a', 'mouseout', function(){
			$(this).find('span').hide();
		});
});
