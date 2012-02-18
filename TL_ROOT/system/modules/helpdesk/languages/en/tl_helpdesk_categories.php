<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Helpdesk :: Language file for table tl_helpdesk_categories (en)
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

$hdesk_text = &$GLOBALS['TL_LANG']['tl_helpdesk_categories'];
$forum_text = &$GLOBALS['TL_LANG']['tl_helpdesk_fcategories'];

/**
 * Fields
 */
$hdesk_text['header']			= array('Header', 'Header to start a new category group. Leave empty otherwise.');
$hdesk_text['title']			= array('Title', 'Title of this category.');
$hdesk_text['description']		= array('Description', 'An optional description of this category.');
$hdesk_text['buttons']			= array('Editor Buttons', 'Enter a comma separated list of editor buttons. Separate groups by semicolon. Separate lines by newline.');
$hdesk_text['access']			= array('Access', 'Category frontend access mode.');
$hdesk_text['replyonly']		= array('Reply only', 
										"New topics/tickets can only be created by moderators/supporters.<br/>\n".
										"Authorized members/clients can only reply to existing topics.");
$hdesk_text['published']		= array('Published', 'Category is displayed on the frontend to the allowed clients.');
$hdesk_text['fe_clients']		= array('Frontend Members/Clients', 'Frontend members authorized as member/client of this category.');
$hdesk_text['fe_supporters']	= array('Frontend Moderators/Supporters', 'Frontend members authorized as moderator/supporter of this category.');
$hdesk_text['be_clients']		= array('Backend Members/Clients', 'Backend users authorized as member/client of this category.');
$hdesk_text['be_supporters']	= array('Backend Moderators/Supporters', 'Backend users authorized as moderator/supporter of this category.');

$hdesk_text['feed']				= array('Enable RSS feeds', 'Check to enable RSS feeds for this category.');

$hdesk_text['atch']				= array('Enable attachments', 'Enable the use of attachments.');
$hdesk_text['atch_dir']			= array('Attachment directory', 'Enter the path where attachment files are stored.');
$hdesk_text['atch_size']		= array('Attachment size', 'Enter the allowed total size of all attachments in one post.');
$hdesk_text['atch_types']		= array('Attachment types', 
										'Enter a comma separated list of file extensions allowed as attachments.<br />'.
										'Enter * to allow any extension.');

$hdesk_text['notify']			= array('Enable notification', 'Notify the subscribers by email.');
$hdesk_text['notify_astext']	= array('Send as text', 'Check to send as text, uncheck to send as HTML.');
$hdesk_text['notify_name']		= array('Sender name', 'Leave empty to use only the plain email address.');
$hdesk_text['notify_sender']	= array('Sender address', 'Leave empty to use the administrator email address.');
$hdesk_text['notify_fe_url']	= array('Frontend URL base',
										"Should be something like <em>http://www.example.com/forum</em><br/>\n".
										"Used for frontend notification, RSS feeds and article commenting.<br/>\n".
										"Leave empty to disable these features.");
$hdesk_text['notify_be_url']	= array('Backend URL base',
										'Should be something like <em>http://www.yoursite.com/Contao/main.php?do=helpdesk</em><br />'.
										'Leave empty to disable backend user notifications.');
$hdesk_text['notify_newsubj']	= array('Subject for new topics', 'You may use the tags [[poster]], [[subject]] and [[replytag]].');
$hdesk_text['notify_newtext']	= array('Text body for new topics', 'You may use the tags [[poster]], [[subject]], [[message]] and [[link]].');
$hdesk_text['notify_replysubj']	= array('Subject for replies', 'You may use the tags [[poster]], [[subject]] and [[replytag]].');
$hdesk_text['notify_replytext']	= array('Text body for replies', 'You may use the tags [[poster]], [[subject]], [[message]] and [[link]].');

$hdesk_text['import']			= array('Enable email import', 'Enable import of support requests by email.');
$hdesk_text['import_server']	= array('Mail server', 'Enter the name of the IMAP4/POP3 server.');
$hdesk_text['import_port']		= array('Port number', 'Enter the port number, or leave empty to use default.');
$hdesk_text['import_type']		= array('Mailbox type', 'Select the mailbox type.');
$hdesk_text['import_tls']		= array('Transport Layer Security (TLS)', 'Select the TLS/SSL mode.');
$hdesk_text['import_username']	= array('Username', 'Enter the username of the mail account.');
$hdesk_text['import_password']	= array('Password', 'Enter the password for the mail account.');
$hdesk_text['import_email']		= array('To: email', 
										'Enter the <em>to</em> email address, or leave empty to process all mail.<br />'.
										'(This should correlate with the sender address of notifications.)');

/**
 * Reference
 */
$hdesk_text['access_options'][0]	= 'Private Support';
$hdesk_text['access_options'][1]	= 'Shared Support';
$hdesk_text['access_options'][2]	= 'Public Support';
$hdesk_text['access_options'][3]	= 'Restricted Forum';
$hdesk_text['access_options'][4]	= 'Public Forum';

$hdesk_text['import_types'][0]		= 'POP3';
$hdesk_text['import_types'][1]		= 'IMAP4';

$hdesk_text['import_tlsopts'][0]	= 'Don\'t use TLS, even if host supports it.';
$hdesk_text['import_tlsopts'][1]	= 'Use TLS if host supports it, validate certificate.';
$hdesk_text['import_tlsopts'][2]	= 'Use TLS if host supports it, don\'t validate certificate';
$hdesk_text['import_tlsopts'][3]	= 'Require TLS, validate certificate.';
$hdesk_text['import_tlsopts'][4]	= 'Require TLS, don\'t validate certificate';
$hdesk_text['import_tlsopts'][5]	= 'Use SSL, validate certificate';
$hdesk_text['import_tlsopts'][6]	= 'Use SSL, don\'t validate certificate';

/**
 * Buttons
 */
$hdesk_text['new']			= array('New category', 'Create a new category.');
$hdesk_text['edit']			= array('Edit', 'Edit the settings of category %s');
$hdesk_text['copy']			= array('Clone', 'Clone (duplicate) this category');
$hdesk_text['delete']		= array('Delete', 'Delete category %s including all tickets');
$hdesk_text['ena_notify']	= array('Enable notification', 'Enable notification for category %s');
$hdesk_text['dis_notify']	= array('Disable notification', 'Disable notification for category %s');
$hdesk_text['ena_import']	= array('Enable email import', 'Enable import of email for category %s');
$hdesk_text['dis_import']	= array('Disable email import', 'Disable import of email for category %s');
$hdesk_text['publish']		= array('Publish', 'Publish category %s for clients');
$hdesk_text['unpublish']	= array('Unpublish', 'Unpublish category %s from clients');
$hdesk_text['orderup']		= array('Move up', 'Move category up one position');
$hdesk_text['orderdown']	= array('Move down', 'Move category down one position');

/**
 * Other labels
 */
$hdesk_text['tickets']		= 'Tickets';
$forum_text['tickets']		= 'Topics';
$hdesk_text['replies']		= 'Replies';

/**
 * Notification default texts
 */
$hdesk_text['notify_subject']['new']	= 'New ticket: [[subject]] [[replytag]]';

$hdesk_text['notify_subject']['reply']	= 'Reply to: [[subject]] [[replytag]]';

$hdesk_text['notify_text']['new'] = 
'[[poster]] has created a new ticket in a category which you subscribed to:<br />
<hr />
[[message]]
<hr />
The ticket is located at:<br />
[[link]]<br />
---<br />
Contao Helpdesk Mailer<br />
(Please retain the reply tag in the subject when replying by email)<br />';

$hdesk_text['notify_text']['reply'] = 
'[[poster]] has replied to a ticket to which you are subscribed:<br />
<hr />
[[message]]
<hr />
The message is located at:<br />
[[link]]<br />
---<br />
Contao Helpdesk Mailer<br />
(Please retain the reply tag in the subject when replying by email)<br />';

?>
