<?php
/**
 * TYPOlight Helpdesk :: Search find result template
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

<?php $hd = &$this->helpdesk; $text = &$hd->text; $tabindex = 1; ?>
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style) { ?> style="<?php echo $this->style; ?>"<?php } ?>>

<div class="breadcrumb"><?php echo $hd->breadcrumb; ?></div>

<?php if (count($hd->pageResult)>0) { ?>

<div class="topfindcontrols controls middle">
<?php echo $hd->pageNavigation; ?>
<span class="matchinfo"><?php echo sprintf($text['matchinfo'], $hd->totrecs, $hd->totmatches); ?></span>
</div>

<?php foreach ($hd->pageResult as $res) { ?>
<div class="searchresult">
<div class="subject"><a href="<?php echo $res['link']; ?>"><?php echo $res['subject']; ?></a></div>
<div class="info"><?php echo sprintf($text['searchinfo'], $res['id'], $res['poster'], $res['relevance']); ?></div>
<div class="message"><?php echo $res['message']; ?></div>
<?php if (count($res['attachments'])>0) { ?>
<div class="attachments">
<?php foreach ($res['attachments'] as $atch) { ?>
<a href="<?php echo $atch['href']; ?>" class="filename"><?php echo $atch['icon']; ?><?php echo $atch['name']; ?></a>
<?php } // foreach msg->attachment ?>
</div>
<?php } // if count msg->attachment ?>
</div>
<?php } // foreach hd->results ?>

<div class="bottomfindcontrols controls middle">
<?php echo $hd->pageNavigation; ?>
<span class="matchinfo"><?php echo sprintf($text['matchinfo'], $hd->totrecs, $hd->totmatches); ?></span>
</div>

<?php } else { ?>

<div class="searchresult">
<div class="searcherror"><?php echo $text['nomatches']; ?></div>
</div>

<?php } // if !result ?>

<div class="breadcrumb"><?php echo $hd->breadcrumb; ?></div>

</div>
<!-- indexer::continue -->

