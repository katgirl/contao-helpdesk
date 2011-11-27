<?php
/**
 * TYPOlight Helpdesk :: Constants
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

// current release according to repository numbering, for example 10010009 = 01.001.000 stable
define('HELPDESK_RELEASE',			10030009);

// access codes
define('HELPDESK_PRIVATE_SUPPORT',	0);
define('HELPDESK_SHARED_SUPPORT',	1);
define('HELPDESK_PUBLIC_SUPPORT',	2);
define('HELPDESK_PROTECTED_FORUM',	3);
define('HELPDESK_PUBLIC_FORUM',		4);

// roles
define('HELPDESK_GUEST',			0);
define('HELPDESK_CLIENT',			1);
define('HELPDESK_SUPPORTER',		2);
define('HELPDESK_ADMIN',			3);

// log levels
define('HELPDESK_NOLOG',			0);
define('HELPDESK_BRIEFLOG',			1);
define('HELPDESK_DETAILEDLOG',		2);
define('HELPDESK_DEBUGLOG',			3);

define('HELPDESK_CATCOLS',
	"\n `cat`.`id` as `cat_id`," .
	"\n `cat`.`tstamp` as `cat_tstamp`," .
	"\n `cat`.`header` as `cat_header`," .
	"\n `cat`.`title` as `cat_title`," .
	"\n `cat`.`description` as `cat_description`," .
	"\n `cat`.`buttons` as `cat_buttons`," .
	"\n `cat`.`access` as `cat_access`," .
	"\n `cat`.`replyonly` as `cat_replyonly`," .
	"\n `cat`.`fe_clients` as `cat_fe_clients`," .
	"\n `cat`.`be_clients` as `cat_be_clients`," .
	"\n `cat`.`fe_supporters` as `cat_fe_supporters`," .
	"\n `cat`.`be_supporters` as `cat_be_supporters`," .
	"\n `cat`.`published` as `cat_published`," .
	"\n `cat`.`feed` as `cat_feed`," .
	"\n `cat`.`atch` as `cat_atch`," .
	"\n `cat`.`atch_dir` as `cat_atch_dir`," .
	"\n `cat`.`atch_size` as `cat_atch_size`," .
	"\n `cat`.`atch_types` as `cat_atch_types`," .
	"\n `cat`.`notify` as `cat_notify`," .
	"\n `cat`.`notify_astext` as `cat_notify_astext`," .
	"\n `cat`.`notify_atch` as `cat_notify_atch`," .
	"\n `cat`.`notify_name` as `cat_notify_name`," .
	"\n `cat`.`notify_sender` as `cat_notify_sender`," .
	"\n `cat`.`notify_fe_url` as `cat_notify_fe_url`," .
	"\n `cat`.`notify_be_url` as `cat_notify_be_url`," .
	"\n `cat`.`notify_newsubj` as `cat_notify_newsubj`," .
	"\n `cat`.`notify_newtext` as `cat_notify_newtext`," .
	"\n `cat`.`notify_replysubj` as `cat_notify_replysubj`," .
	"\n `cat`.`notify_replytext` as `cat_notify_replytext`," .
	"\n `cat`.`import` as `cat_import`," .
	"\n `cat`.`import_atch` as `cat_import_atch`," .
	"\n `cat`.`import_server` as `cat_import_server`," .
	"\n `cat`.`import_port` as `cat_import_port`," .
	"\n `cat`.`import_type` as `cat_import_type`," .
	"\n `cat`.`import_tls` as `cat_import_tls`," .
	"\n `cat`.`import_username` as `cat_import_username`," .
	"\n `cat`.`import_password` as `cat_import_password`," .
	"\n `cat`.`import_email` as `cat_import_email`," .
	"\n `cat`.`pub_tickets` as `cat_pub_tickets`," .
	"\n `cat`.`pub_replies` as `cat_pub_replies`," .
	"\n `cat`.`pub_latest` as `cat_pub_latest`," .
	"\n `cat`.`all_tickets` as `cat_all_tickets`," .
	"\n `cat`.`all_replies` as `cat_all_replies`," .
	"\n `cat`.`all_latest` as `cat_all_latest`"
);

define('HELPDESK_CATCOLGRP',
	" `cat_id`," .
	" `cat_tstamp`," .
	" `cat_header`," .
	" `cat_title`," .
	" `cat_description`," .
	" `cat_buttons`," .
	" `cat_access`," .
	" `cat_replyonly`," .
	" `cat_fe_clients`," .
	" `cat_be_clients`," .
	" `cat_fe_supporters`," .
	" `cat_be_supporters`," .
	" `cat_published`," .
	" `cat_feed`," .
	" `cat_atch`," .
	" `cat_atch_dir`," .
	" `cat_atch_size`," .
	" `cat_atch_types`," .
	" `cat_notify`," .
	" `cat_notify_astext`," .
	" `cat_notify_name`," .
	" `cat_notify_sender`," .
	" `cat_notify_fe_url`," .
	" `cat_notify_be_url`," .
	" `cat_notify_newsubj`," .
	" `cat_notify_newtext`," .
	" `cat_notify_replysubj`," .
	" `cat_notify_replytext`," .
	" `cat_import`," .
	" `cat_import_server`," .
	" `cat_import_port`," .
	" `cat_import_type`," .
	" `cat_import_tls`," .
	" `cat_import_username`," .
	" `cat_import_password`," .
	" `cat_import_email`," .
	" `cat_pub_tickets`," .
	" `cat_pub_replies`," .
	" `cat_pub_latest`," .
	" `cat_all_tickets`," .
	" `cat_all_replies`," .
	" `cat_all_latest`"
);

define('HELPDESK_TCKCOLS',
	"\n `tck`.`id` as `tck_id`," .
	"\n `tck`.`pid` as `tck_pid`," .
	"\n `tck`.`tstamp` as `tck_tstamp`," .
	"\n `tck`.`client` as `tck_client`," .
	"\n `tck`.`client_be` as `tck_client_be`," .
	"\n `tck`.`supporter` as `tck_supporter`," . 
	"\n `tck`.`supporter_be` as `tck_supporter_be`," . 
	"\n `tck`.`subject` as `tck_subject`," .
	"\n `tck`.`status` as `tck_status`," .
	"\n `tck`.`published` as `tck_published`," .
	"\n `tck`.`pub_replies` as `tck_pub_replies`," .
	"\n `tck`.`pub_latest` as `tck_pub_latest`," .
	"\n `tck`.`all_replies` as `tck_all_replies`," .
	"\n `tck`.`all_latest` as `tck_all_latest`," .
	"\n `tck`.`views` as `tck_views`"
);
define('HELPDESK_TCKCOLGRP',
	" `tck_id`," .
	" `tck_pid`," .
	" `tck_tstamp`," .
	" `tck_client`," .
	" `tck_client_be`," .
	" `tck_supporter`," . 
	" `tck_supporter_be`," . 
	" `tck_subject`," .
	" `tck_status`," .
	" `tck_published`," .
	" `tck_pub_replies`," .
	" `tck_pub_latest`," .
	" `tck_all_replies`," .
	" `tck_all_latest`," .
	" `tck_views`"
);

define('HELPDESK_MSGCOLS',
	"\n `msg`.`id` as `msg_id`," .
	"\n `msg`.`pid` as `msg_pid`," .
	"\n `msg`.`tstamp` as `msg_tstamp`," .
	"\n `msg`.`reply` as `msg_reply`," .
	"\n `msg`.`by_email` as `msg_by_email`," .
	"\n `msg`.`poster` as `msg_poster`," .
	"\n `msg`.`poster_cd` as `msg_poster_cd`," .
	"\n `msg`.`message` as `msg_message`," .
	"\n `msg`.`atch1name` as `msg_atch1name`," .
	"\n `msg`.`atch2name` as `msg_atch2name`," .
	"\n `msg`.`atch3name` as `msg_atch3name`," .
	"\n `msg`.`atch4name` as `msg_atch4name`," .
	"\n `msg`.`atch5name` as `msg_atch5name`," .
	"\n `msg`.`published` as `msg_published`," .
	"\n `msg`.`edited` as `msg_edited`," .
	"\n `msg`.`editor` as `msg_editor`," .
	"\n `msg`.`editor_cd` as `msg_editor_cd`"
);

define('HELPDESK_NTFCOLS',
	"\n `ntf`.`id` as `ntf_id`," .
	"\n `ntf`.`pid` as `ntf_pid`"
);

define('HELPDESK_DEFAULTEDITBUTTONS',
	"bold,italics,underlined,superscript,subscript;".
	"centered,rightaligned,justified;".
	"list,numberedlist,romanlist,alphalist,listitem;".
	"table,tablerow,tablecell".
	"\n".
	"code,php,js,xml,html,css,c++,qt;".
	"quote,information,warning,hyperlink,image;".
	"preview,help".
	"\n".
	"smile,rolleyes,laugh,lol,w00t,wink,bored,tongue,cool,unsure,blush,ohmy,scared,huh,".
	"blink,confused,sad,cry,sneaky,mad,love,sleep,thumbdown,thumbup"
);


?>