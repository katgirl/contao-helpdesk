<?php
/**
 * TYPOlight Helpdesk :: Comment content element template
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */
$text = &$GLOBALS['TL_LANG']['tl_helpdesk_comments'];
if ($this->replyCount == 0) 
	$linktext = $text['writecomment'];
else
	if ($this->replyCount == 1) 
		$linktext = $text['readcomment'];
	else
		$linktext = sprintf($text['readcomments'], $this->replyCount);
?>
<?php if (!is_null($this->ticketLink)) { ?>
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style) { ?> style="<?php echo $this->style; ?>"<?php } ?>>
<?php if ($this->headline) { ?>
<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php } // if headline ?>
<div class="commentlink"><a href="<?php echo $this->ticketLink; ?>"><?php echo $linktext; ?></a></div>
</div>
<!-- indexer::continue -->
<?php } // if ticketLink ?>
