<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Language file for frontend (en)
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */
 
$hdesk_text = &$GLOBALS['TL_LANG']['tl_helpdesk_support'];
$forum_text = &$GLOBALS['TL_LANG']['tl_helpdesk_forum'];

$hdesk_text['advanced']					= 'Advanced settings';
$hdesk_text['mine']						= 'Mine';
$hdesk_text['recent']					= 'Recent';
$hdesk_text['views']					= 'Views';
$hdesk_text['unanswered']				= 'Unanswered';
$hdesk_text['unread']					= 'Unread';
$hdesk_text['category']					= 'Category';
$hdesk_text['index']					= 'Index';
$hdesk_text['ticket']					= 'Ticket';
$forum_text['ticket']					= 'Topic';
$hdesk_text['tickets']					= 'Tickets';
$forum_text['tickets']					= 'Topics';
$hdesk_text['newticket']				= 'New ticket';
$forum_text['newticket']				= 'New topic';
$hdesk_text['createticket']				= 'Create ticket';
$forum_text['createticket']				= 'Create topic';
$hdesk_text['nopaste']					= 'Cancel cut/paste';
$hdesk_text['cutmessage']				= 'Cut message';
$hdesk_text['cutticket']				= 'Cut ticket';
$forum_text['cutticket']				= 'Cut topic';
$hdesk_text['paste']					= 'Paste';
$hdesk_text['postreply']				= 'Post reply';
$hdesk_text['updatemessage']			= 'Update message';
$hdesk_text['message']					= 'Message';
$hdesk_text['replies']					= 'Replies';
$hdesk_text['latestpost']				= 'Latest post';
$hdesk_text['postedby']					= 'by %s';
$hdesk_text['lastedit']					= '[Last edited by %s, %s]';
$hdesk_text['subject']					= 'Subject';
$hdesk_text['client']					= 'Client';
$forum_text['client']					= 'Member';
$hdesk_text['supporter']				= 'Supporter';
$forum_text['supporter']				= 'Moderator';
$hdesk_text['owner']					= 'Owner';
$hdesk_text['creator']					= 'Creator';
$forum_text['owner']					= 'Creator';
$hdesk_text['poster']					= 'Posted by';
$hdesk_text['nocategories']				= 'No categories found.';
$hdesk_text['notickets']				= 'No tickets found.';
$forum_text['notickets']				= 'No topics found.';
$hdesk_text['nomessages']				= 'No messages found.';
$hdesk_text['status']					= 'Status';
$hdesk_text['status-0']					= 'Unanswered';
$forum_text['status-0']					= 'Pinned up';
$hdesk_text['status-1']					= 'Answered';
$forum_text['status-1']					= 'Normal';
$hdesk_text['status-2']					= 'Closed';
$hdesk_text['reply']					= 'Reply';
$hdesk_text['close']					= 'Close';
$hdesk_text['open']						= 'Open';
$hdesk_text['edit']						= 'Edit';
$hdesk_text['quote']					= 'Quote';
$hdesk_text['delete']					= 'Delete';
$hdesk_text['publish']					= 'Publish';
$hdesk_text['unpublish']				= 'Unpublish';
$hdesk_text['pinup']					= 'Pin up';
$hdesk_text['unpin']					= 'Unpin';
$hdesk_text['markcatread']				= 'Category read';
$hdesk_text['markallread']				= 'All read';
$hdesk_text['feed']						= 'RSS Feed';
$hdesk_text['globfeed']					= 'Global RSS Feed';
$hdesk_text['wrote']					= '%s wrote:';
$hdesk_text['deleteMessageConfirm']		= 'Do you really want to delete this message?';
$hdesk_text['deleteTicketConfirm']		= 'Do you really want to delete this ticket with all its messages?';
$forum_text['deleteTicketConfirm']		= 'Do you really want to delete this topic with all its messages?';
$hdesk_text['client_hint']				= 'Please select client on behalf you create this ticket, or leave empty otherwise.';
$forum_text['client_hint']				= 'Please select member on behalf you create this message, or leave empty otherwise.';
$hdesk_text['subject_missing']			= 'You must enter a subject!';
$hdesk_text['subject_hint']				= 'Please enter a brief text describing the prime reason of this ticket.';
$forum_text['subject_hint']				= 'Please enter a brief text describing the subject of this topic.';
$hdesk_text['message_missing']['create']= 'You must enter a detailed description!';
$hdesk_text['message_missing']['reply']	= 'You must enter a message text!';
$hdesk_text['message_missing']['edit']	= 'You must enter a message text!';
$hdesk_text['message_hint']['create']	= 'Please enter the detailed description of the issue, along with all known facts.';
$forum_text['message_hint']['create']	= 'Please enter the detailed description.';
$hdesk_text['message_hint']['reply']	= 'Please enter your reply text.';
$hdesk_text['message_hint']['edit']		= 'Please edit the message text.';
$hdesk_text['published']				= 'Published';
$hdesk_text['published_hint']			= 'Message is displayed in frontend to authorized persons.';
$hdesk_text['messages_reverse']			= 'Previous messages in reverse order:';
$hdesk_text['firstpage']				= '« First';
$hdesk_text['prevpage']					= 'Previous';
$hdesk_text['nextpage']					= 'Next';
$hdesk_text['lastpage']					= 'Last »';
$hdesk_text['page_n_of_m']				= 'Page %s of %s';
$hdesk_text['gotopage']					= 'Go to page %s';
$hdesk_text['attachments']				= 'Attachments';
$hdesk_text['atch_size']				= 'Maximum size of all attachments: ';
$hdesk_text['atch_types']				= 'Allowed file types: ';
$hdesk_text['atchtobig']				= 'Attachments exceed the maximum allowed size.';
$hdesk_text['filetobig']				= '%s exceeds the maximum upload file size for this server (check php.ini settings).';
$hdesk_text['filepartial']				= '%s got only partially uploaded.';
$hdesk_text['filebadtype']				= '%s is not of allowed file types.';
$hdesk_text['matchinfo']				= 'Best %s of total %s matches';
$hdesk_text['search']					= 'Search';
$hdesk_text['searchresult']				= 'Results';
$hdesk_text['searchinfo']				= '[#%s by %s, relevance %s]';
$hdesk_text['searchterms']				= 'Search terms';
$hdesk_text['searchterms_missing']		= 'You must enter one or more search terms!';
$hdesk_text['searchterms_hint']			= 'Please enter one or more search terms.';
$hdesk_text['findmode']					= 'List';
$hdesk_text['findmode_hint']			= 'Select what entities to list as result.';
$hdesk_text['searchmode']				= 'Search mode';
$hdesk_text['searchmode_hint']			= 
	"Select the search mode.<br/>\n".
	"Consult <a href=\"http://dev.mysql.com/doc/refman/6.0/en/fulltext-search.html\">MySQL documentation</a> for more information on fulltext search.<br/>\n".
	"SQL wildcards % and _ can be used in 'like' search modes.";
$hdesk_text['searchparts']				= 'Searched parts';
$hdesk_text['searchparts_unchecked']	= 'You must check one or more parts to search!';
$hdesk_text['searchparts_hint']			= 'Please check one or more parts to search.';
$hdesk_text['searchcats']				= 'Searched categories';
$hdesk_text['searchcats_unchecked']		= 'You must check one category at least!';
$hdesk_text['searchcats_hint']			= 'Please check one or more categories to search.';
$hdesk_text['nomatches']				= 'The search returned no matches.';
$hdesk_text['postcount']				= 'Posts: %s';
$hdesk_text['fromloc']					= 'From: %s';
$hdesk_text['viewprofile']				= 'View profile';
$hdesk_text['warnsearchflood']			= 'Please wait %s seconds before submitting next search.';
$hdesk_text['warnpostflood']			= 'Please wait %s seconds before submitting next post.';
$hdesk_text['continue']					= 'Continue';

$hdesk_text['messagetitle']['info']		= 'Information';
$hdesk_text['messagetitle']['warning']	= 'Warning';
$hdesk_text['messagetitle']['error']	= 'Error';

$hdesk_text['searchmodes']['natural']	= 'Fulltext natural';
$hdesk_text['searchmodes']['boolean']	= 'Fulltext boolean';
$hdesk_text['searchmodes']['like']		= 'Like';
$hdesk_text['searchmodes']['binary']	= 'Like binary';

$hdesk_text['findmodes']['messages']	= 'All relevant messages';
$hdesk_text['findmodes']['topics']		= 'First match in relevant topics';
?>
