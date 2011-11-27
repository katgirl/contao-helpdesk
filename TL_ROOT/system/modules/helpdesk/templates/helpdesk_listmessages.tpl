<?php
/**
 * TYPOlight Helpdesk :: List messages template
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */
?>
<!-- 

	TYPOlight Forum/Helpdesk :: Copyright (C) 2007-2010 by Peter Koch, IBK Software AG
	Visit http://www.typolight.org/extension-list/view/helpdesk.html for details.
	
-->
<!-- indexer::stop -->
<?php 
global $objPage; 
$hd = &$this->helpdesk; 
$cat = &$hd->category; 
$tck = &$hd->ticket; 
$text = &$hd->text; 
$targetFound = false; 
if (is_object($objPage)) 
	$objPage->pageTitle = $tck->subject; 
$mainFuncs = '';
if ($tck->cutTicketLink)
	$mainFuncs .= $hd->createImage('cut16') . '<a href="' . $tck->cutTicketLink . '">' . $text['cutticket'] . "</a>\n";
if ($tck->pasteLink)
	$mainFuncs .= $hd->createImage('paste16') . '<a href="' . $tck->pasteLink . '">' . $text['paste'] . "</a>\n";
if ($hd->nopasteLink)
	$mainFuncs .= $hd->createImage('nopaste16') . '<a href="' . $hd->nopasteLink . '">' . $text['nopaste'] . "</a>\n";
else {
if ($tck->publishTicketLink)
	$mainFuncs .= $hd->createImage('unpublished16') . '<a href="' . $tck->publishTicketLink . '">' . $text['publish'] . "</a>\n";
if ($tck->unpublishTicketLink)
	$mainFuncs .= $hd->createImage('published16') . '<a href="' . $tck->unpublishTicketLink . '">' . $text['unpublish'] . "</a>\n";
if ($tck->openTicketLink)
	$mainFuncs .= $hd->createImage('close16') . '<a href="' . $tck->openTicketLink . '">' . $text['open'] . "</a>\n";
if ($tck->closeTicketLink)
	$mainFuncs .= $hd->createImage('open16') . '<a href="' . $tck->closeTicketLink . '">' . $text['close'] . "</a>\n";
if ($tck->pinupTicketLink)
	$mainFuncs .= $hd->createImage('unpinned16') . '<a href="' . $tck->pinupTicketLink . '">' . $text['pinup'] . "</a>\n";
if ($tck->unpinTicketLink)
	$mainFuncs .= $hd->createImage('pinned16') . '<a href="' . $tck->unpinTicketLink . '">' . $text['unpin'] . "</a>\n";
if ($tck->removeTicketLink)
	$mainFuncs .= $hd->createImage('delete16') . '<a href="' . $tck->removeTicketLink . '" onclick="if (!confirm(\''. $text['deleteTicketConfirm'] . '\')) return false;">' . $text['delete'] . "</a>\n";
if ($tck->replyTicketLink)
	$mainFuncs .= $hd->createImage('add16') . '<a href="' . $tck->replyTicketLink . '">' . $text['reply'] . "</a>\n";
} // clipboard empty
?>
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style) { ?> style="<?php echo $this->style; ?>"<?php } ?>>

<div class="breadcrumb"><?php echo $hd->breadcrumb; ?></div>

<div class="topcontrols controls middle">
<?php echo $hd->pageNavigation; ?>
<span class="functions">
<?php echo $mainFuncs; ?>
</span>
</div>

<?php if (count($hd->messages)) { ?>
<?php foreach ($hd->messages as $msg) { ?>
<div class="message-container" id="message_<?php echo $msg->id; ?>">
<div class="top-left">
<div class="messagelink"><a href="<?php echo $msg->showMessageLink; ?>"><?php echo $text['message'].' #'.$msg->id;?></a></div>
<div class="avatar"><?php echo Avatar::img($msg->posterObj->avatar); ?></div>
<div class="poster"><?php echo $msg->realname!='' ? $msg->realname.' ('.$msg->poster.')' : $msg->poster; ?></div>
<?php if ($msg->posterObj->helpdesk_role!='' || $msg->supporter) { ?>
<div class="role"><?php echo $msg->posterObj->helpdesk_role!='' ? $msg->posterObj->helpdesk_role : $text['supporter']; ?></div>
<?php } // if role or supporter ?>
<?php if ($msg->location!='') { ?>
<div class="location"><?php echo sprintf($text['fromloc'], $msg->location); ?></div>
<?php } // if location ?>
<div class="postcount"><?php echo sprintf($text['postcount'], $msg->posterObj->helpdesk_postcount); ?></div>
<?php if ($msg->viewProfileLink) { ?>
<div class="profilelink"><a href="<?php echo $msg->viewProfileLink; ?>"><?php echo $text['viewprofile']; ?></a></div>
<?php } // if ?>
</div> <!-- top-left -->
<div class="top-right">
<div class="helpdesk-message helpdesk-messageblock">
<?php 
if ($hd->settings->tlsearch) echo "<!-- indexer::continue -->\n";
echo $msg->message; 
if ($hd->settings->tlsearch) echo "\n<!-- indexer::stop -->\n"
?>
</div>
<?php if ($msg->editor!='') { ?>
<div class="helpdesk-lastedit"><?php echo sprintf($text['lastedit'], $msg->editor, $msg->edited);?></div>
<?php } ?>
<?php if ($msg->signature!='') { ?>
<div class="helpdesk-message helpdesk-signatureblock"><?php echo $msg->signature;?></div>
<?php } ?>
</div>
<div class="clearfloat"></div>
<div class="bottom-left"><?php echo $msg->tstamp;?></div>
<div class="bottom-right">
<div class="buttons">
<?php if ($msg->cutMessageLink) { ?>
<a href="<?php echo $msg->cutMessageLink; ?>" title="<?php echo $text['cutmessage']; ?>"><?php echo $hd->createImage('cut16',$text['cutmessage'],'title="'.$text['cutmessage'].'"'); ?></a>
<?php } ?>
<?php if (!$hd->nopasteLink) { ?>
<?php if ($msg->publishMessageLink) { ?>
<a href="<?php echo $msg->publishMessageLink; ?>" title="<?php echo $text['publish']; ?>"><?php echo $hd->createImage('unpublished16',$text['publish'],'title="'.$text['publish'].'"'); ?></a>
<?php } ?>
<?php if ($msg->unpublishMessageLink) { ?>
<a href="<?php echo $msg->unpublishMessageLink; ?>" title="<?php echo $text['unpublish']; ?>"><?php echo $hd->createImage('published16',$text['unpublish'],'title="'.$text['unpublish'].'"'); ?></a>
<?php } ?>
<?php if ($msg->deleteMessageLink) { ?>
<a href="<?php echo $msg->deleteMessageLink; ?>" class="sublink" onclick="if (!confirm('<?php echo $text['deleteMessageConfirm']; ?>')) return false;"><?php echo $hd->createImage('delete16', $text['delete'], 'title="'.$text['delete'].'"'); ?></a>
<?php } ?>
<?php if ($msg->editMessageLink) { ?>
<a href="<?php echo $msg->editMessageLink; ?>" class="sublink"><?php echo $hd->createImage('edit16',$text['edit'],'title="'.$text['edit'].'"'); ?></a>
<?php } ?>
<?php if ($msg->quoteMessageLink) { ?>
<a href="<?php echo $msg->quoteMessageLink; ?>" class="sublink"><?php echo $hd->createImage('quote16',$text['quote'],'title="'.$text['quote'].'"'); ?></a>
<?php } ?>
<?php if ($tck->replyTicketLink) { ?>
<a href="<?php echo $tck->replyTicketLink; ?>" class="sublink"><?php echo $hd->createImage('add16',$text['reply'],'title="'.$text['reply'].'"'); ?></a>
<?php } ?>
<?php } // clipboard empty ?>
</div> <!-- buttons -->
<div class="attachments">
<?php if (count($msg->attachment)) { ?>
<?php foreach ($msg->attachment as $atch) { ?>
<a href="<?php echo $atch['href']; ?>" class="attachment"><?php echo $atch['icon']; ?> <?php echo $atch['name']; ?></a>
<?php } // foreach ?>
<?php } // if count ?>
</div> 
<div class="clearfloat"></div>
</div> <!-- right-bottom -->
</div> <!-- message-container -->
<?php if ($hd->target==$msg->id) $targetFound = true; ?>
<?php } // foreach hd->messages ?>

<?php } else { ?>
<div class="helpdesk-notickets">
<?php echo $text['nomessages']; ?>
</div>
<?php } // if count hd->messages ?>

<div class="bottomcontrols controls middle">
<?php echo $hd->pageNavigation; ?>
<span class="functions">
<?php echo $mainFuncs; ?>
</span>
</div>

<div class="breadcrumb"><?php echo $hd->breadcrumb; ?></div>

</div>
<?php if ($targetFound) { ?>
<script type="text/javascript">
<!--
function scrollToElement()
{
	var theElement = document.getElementById('message_<?php echo $hd->target; ?>');
	var selectedPosX = 0;
	var selectedPosY = 0;          
	while (theElement != null) {
		selectedPosX += theElement.offsetLeft;
		selectedPosY += theElement.offsetTop;
		theElement = theElement.offsetParent;
	} // while
	window.scrollTo(selectedPosX,selectedPosY);
} // scrollToElement
window.onload=scrollToElement;
//-->
</script>
<?php } // if targetFound ?>
<!-- indexer::continue -->
