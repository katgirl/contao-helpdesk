<?php
/**
 * TYPOlight Helpdesk :: List unread messages template
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */
?>
<!-- 

	TYPOlight Forum/Helpdesk :: Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
	Visit http://www.typolight.org/extension-list/view/helpdesk.html for details.
	
-->
<!-- indexer::stop -->

<?php $hd = &$this->helpdesk; $text = &$hd->text; ?>
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style) { ?> style="<?php echo $this->style; ?>"<?php } ?>>

<div class="breadcrumb"><?php echo $hd->breadcrumb; ?></div>

<div class="topcontrols controls middle">
<?php echo $hd->pageNavigation; ?>
<span class="functions">
<?php if ($hd->markReadLink) { ?>
<?php echo $hd->createImage('checked16'); ?><a href="<?php echo $hd->markReadLink; ?>"><?php echo $text['markallread']; ?></a>
<?php } // if hd->markReadLink ?>
</span>
</div>

<?php if (count($hd->pageResult)) { ?>
<table class="mainlist">
<tr>
<th class="ticketid"> </th>
<th class="subject maxwidth"><?php echo $text['subject']; ?></th>
<th class="replycount centered"><?php echo $text['replies']; ?></th>
<th class="latestpost"><?php echo $text['latestpost']; ?></th>
<th class="category"><?php echo $text['category']; ?></th>
</tr>

<?php foreach ($hd->pageResult as $tck) { ?>
<tr class="datarow<?php if (!$tck->read) echo ' datarow-unread'; ?>">
<td class="iconcol">
<span class="icon"><?php echo $hd->createImage('ticket'.$tck->index.'16'); ?></span>
</td>
<td class="subject">
<a href="<?php echo $tck->listMessagesLink; ?>" class="unreadbold"><?php echo $tck->subject; ?></a>
<br />
<?php echo sprintf($hd->text['postedby'], $tck->client); ?>
<?php 
if (count($tck->pageLinks)>0) {
	echo ' [ ';
	foreach ($tck->pageLinks as $p => $l)
		echo '<a href="' . $l . '">' .$p . '</a> ';
	echo ']';
} // if
?>
</td>
<td class="replycount centered"><?php echo $tck->replycount; ?></td>
<td class="latestpost">
<a href="<?php echo $tck->latestlink; ?>" class="tstamp nowrap"><?php echo $tck->latesttstamp; ?><br /><?php echo $tck->latestposter; ?></a>
</td>
<td class="category"><a href="<?php echo $tck->listTicketsLink; ?>"><?php echo $tck->cat_title; ?></a></td>
</tr>
<?php } // foreach hd->pageResult ?>

</table>
<?php } else { ?>
<div class="helpdesk-notickets"><?php echo $text['notickets']; ?></div>
<?php } // if count hd->pageResult ?>

<div class="bottomcontrols controls middle">
<?php echo $hd->pageNavigation; ?>
<span class="functions">
<?php if ($hd->markReadLink) { ?>
<?php echo $hd->createImage('checked16'); ?><a href="<?php echo $hd->markReadLink; ?>"><?php echo $text['markallread']; ?></a>
<?php } // if hd->markReadLink ?>
</span>
</div>

<div class="breadcrumb"><?php echo $hd->breadcrumb; ?></div>

</div>
<!-- indexer::continue -->
