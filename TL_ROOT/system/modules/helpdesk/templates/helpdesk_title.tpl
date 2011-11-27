<?php
/**
 * TYPOlight Helpdesk :: Title module template
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

<?php if ($this->headline) { ?>
<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php } // if this->headline ?>

<?php if ($this->headertext) { ?>
<div class="headertext"><?php echo $this->headertext; ?></div>
<?php } // if this->headertext ?>

<?php if ($this->links) { ?>
<div class="controls middle">
<?php if ($hd->feedLink && !$hd->markReadLink) { ?>
<?php echo $hd->createImage('feed16'); ?><a href="<?php echo $hd->feedLink; ?>"><?php echo $text['feed']; ?></a>
<?php } ?>
<?php if ($hd->searchLink) { ?>
<?php echo $hd->createImage('find24'); ?><a href="<?php echo $hd->searchLink; ?>"><?php echo $text['search']; ?></a>
<?php } ?>
<?php if ($hd->mineLink) { ?>
<?php echo $hd->createImage('mine24'); ?><a href="<?php echo $hd->mineLink; ?>"><?php echo $text['mine']; ?></a>
<?php } ?>
<?php if ($hd->recentLink) { ?>
<?php echo $hd->createImage('recent24'); ?><a href="<?php echo $hd->recentLink; ?>"><?php echo $text['recent']; ?></a>
<?php } ?>
<?php if ($hd->unansweredLink) { ?>
<?php echo $hd->createImage('lemon24'); ?><a href="<?php echo $hd->unansweredLink; ?>"><?php echo $text['unanswered']; ?></a>
<?php } ?>
<?php if ($hd->unreadLink) { ?>
<?php echo $hd->createImage('unread24'); ?><a href="<?php echo $hd->unreadLink; ?>"><?php echo $text['unread']; ?></a>
<?php } ?>
<?php if ($hd->markReadLink) { ?>
<?php echo $hd->createImage('checked24'); ?><a href="<?php echo $hd->markReadLink; ?>"><?php echo $text['markallread']; ?></a>
<?php } ?>
</div>
<?php } // if this->links ?>

</div>

<!-- indexer::continue -->
