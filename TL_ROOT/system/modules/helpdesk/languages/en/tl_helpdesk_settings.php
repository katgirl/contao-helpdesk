<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: Language file for table tl_settings (en)
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

$text = &$GLOBALS['TL_LANG']['tl_helpdesk_settings'];

/**
 * Labels
 */
$text['title']		= 'Forum/Helpdesk General Settings';
$text['version']	= 'Version';
$text['yes']		= 'Yes';
$text['no']			= 'No';
$text['confsync']	= 'Do you really want to synchronize (rebuild) all statistics and cached values now?';

/**
 * Buttons
 */
$text['synchronize'] = array('Synchronize', 'Synchronize (rebuild) all statistics and cached values');
$text['edit']	= array('Edit', 'Edit the forum/helpdesk settings');

/**
 * Fields
 */
$text['tpage'] = array(
	'Topics/tickets per page', 
	'Enter the number of topics/tickets on one page. 0 or empty disables paging.'
);
$text['mpage'] = array(
	'Messages per page', 
	'Enter the number of messages on one page. 0 or empty disables paging.'
);
$text['spage'] = array(
	'Search results per page', 
	'Enter the number of found messages on one page. 0 or empty disables paging.'
);
$text['pagenavsize'] = array(
	'Page navigation size', 
	'Number of pages listed in the page navigation. Set 0 to show "Page N of M" instead.'
);
$text['pagenavctl'] = array(
	'Page navigation controls', 
	'Select the page navigation controls.'
);
$text['edits'] = array(
	'Mark edits of', 
	'Select the level of edit operation recordings.'
);
$text['editswait'] = array(
	'Mark edits after (seconds)', 
	'How long a new message can be changed without getting marked as edited.'
);
$text['postdelay'] = array(
	'Re-post wait time (seconds)', 
	'Minimal period between two posts to hamper flooding.'
);
$text['searchdelay'] = array(
	'Search repeat wait time (seconds)', 
	'Minimal period between two searches to hamper DOS attacks.'
);
$text['searchmax'] = array(
	'Maximum # of search matches', 
	'Maximum number of best matches to return as search result set.'
);
$text['tlsearch'] = array(
	'Enable TYPOlight search', 
	'Enables indexing of the topics/tickets by the TYPOlight search engine, in addition to helpdesks internal search.'
);
$text['recenthours'] = array(
	'Recent time frame', 
	'Maximum post age in hours for the recent topics/tickets display.'
);
$text['images'] = array(
	'Frontend images directory', 
	'Leave empty to use default icons and images.'
);
$text['feeds'] = array(
	'Enable feeds', 
	'Check to enable RSS feeds of the forums and helpdesks.'
);
$text['feedmax'] = array(
	'Number of messages in feed', 
	'Enter the maximum number of messages listed in the feed'
);
$text['feedlimit'] = array(
	'Message size limit', 
	'Enter the maximum number of characters per message text'
);
$text['feedlink'] = array(
	'Base name of the feed', 
	'Enter for example <em>helpdesk</em> for the feed names http://www.example.com/helpdesk&lt;category-number&gt;.xml'
);
$text['feedtitle'] = array(
	'Feed title', 
	'The title will for example be displayed in browser tabs and suggested as shortcut name.'
);
$text['feeddescription'] = array(
	'Feed description', 
	'Displayed as subtitle by most browsers and feed readers.'
);
$text['logging'] = array(
	'Logging', 
	'Select the logging level. Log messages will be recorded in the files system/logs/Helpdesk*.log'
);

/**
 * Reference
 */
$text['loglevel']	= array('Off', 'Brief', 'Detailed', 'Debug');
$text['pagenavoptions']= array('Text', 'Icons');
$text['editoptions']= array('Nobody', 'Normal members only', 'Everybody');

?>