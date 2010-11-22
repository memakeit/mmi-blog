/* mmi-blog/index.js */

$('#content .content a').each(function(e)
{
	$(this).attr('rel', 'nofollow external');
});
