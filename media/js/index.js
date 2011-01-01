/* mmi-blog/index.js */

$('#content article section a').each(function(e)
{
	$(this).attr('rel', 'nofollow external');
});
