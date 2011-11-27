<?php
/**
 * TYPOlight Helpdesk :: Edit message template
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

<?php $hd = &$this->helpdesk; $cat = &$hd->category; $text = &$hd->text; $tabindex = 1; ?>
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style) { ?> style="<?php echo $this->style; ?>"<?php } ?>>

<div class="breadcrumb"><?php echo $hd->breadcrumb; ?></div>

<form action="<?php echo $hd->formLink; ?>" id="helpdesk_editform" method="post" enctype="multipart/form-data" >
<div class="formbody">
<input type="hidden" name="helpdesk_action" value="<?php echo $hd->formAction; ?>" />

<?php if (is_array($hd->clientOptions) && count($hd->clientOptions)) { ?>
<div class="label_container"><label for="helpdesk_client"><?php echo $text['client']; ?></label></div>
<div class="client_container">
<select name="helpdesk_client" id="helpdesk_client" class="selectinput">
<option value=""<?php if (!$hd->client) echo 'selected="selected"'; ?>>&nbsp;</option>
<?php foreach ($hd->clientOptions as $name)
echo '<option'. ($name==$hd->clientOption ? ' selected="selected"' : '') .'>' . $name . '</option>'."\n";
?>
</select> 
</div>
<div class="hint"><?php echo $text['client_hint']; ?></div>
<?php } // if is_array hd->clientOptions... ?>

<?php if ($hd->editSubject) { ?>
<!-- subject -->
<div class="label_container">
<?php if ($hd->subjectMissing) { ?>
<div class="error_message middle">
<?php echo $hd->createImage('error16'); ?>
<?php echo $text['subject_missing']; ?>
</div>
<?php } // if hd->subjectMissing ?>
<label for="helpdesk_subject"><?php echo $text['subject']; ?></label>
</div>
<div class="subject_container"><input type="text" size="100" tabindex="<?php echo $tabindex++; ?>" maxlength="100" name="helpdesk_subject" id="helpdesk_subject" value="<?php echo $hd->subject; ?>" class="subject textinput" /></div>
<div class="hint"><?php echo $text['subject_hint']; ?></div>
<?php } // if hd->editSubject ?>

<div class="label_container">
<?php if ($hd->messageMissing) { ?>
<div class="error_message middle">
<?php echo $hd->createImage('error16'); ?>
<?php echo $text['message_missing'][$hd->formMode]; ?>
</div>
<?php } // if ?>
<label for="helpdesk_message"><?php echo $text['message']; ?></label>
</div>
<div class="message_container"><?php echo $hd->editorButtons; ?>
<textarea name="helpdesk_message" id="helpdesk_message" tabindex="<?php echo $tabindex++; ?>" class="message" rows="15" cols="80"><?php echo $hd->msgtext; ?></textarea>
</div>
<div class="hint"><?php echo $text['message_hint'][$hd->formMode]; ?></div>

<?php if ($hd->editPublished) { ?>
<div class="published_container checkbox_container">
<input type="checkbox" name="helpdesk_published" id="helpdesk_published" tabindex="<?php echo $tabindex++; ?>" class="checkbox" value="1"<?php if (intval($hd->published)) echo ' checked="checked"'; ?> />
<label for="helpdesk_published"><?php echo $text['published']; ?></label>
</div>
<div class="hint"><?php echo $text['published_hint']; ?></div>
<?php } // if hd->editPublished ?>

<?php if ($cat->atch) { ?>
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $cat->atch_size; ?>" />
<fieldset>
<legend><?php echo $text['attachments']; ?></legend>

<?php for ($atch = 1; $atch <= 5; $atch++) { ?>
<div class="attachment middle">
<?php echo $atch; ?>:&nbsp;
<?php if ($hd->attachment[$atch]) { ?>
<?php echo $hd->attachment[$atch]['name']; ?>&nbsp;
<input type="checkbox" name="atchdelete<?php echo $atch; ?>" id="atchdelete<?php echo $atch; ?>" tabindex="<?php echo $tabindex++; ?>" class="checkbox" value="1" />
<label for="atchdelete<?php echo $atch; ?>"><?php echo $text['delete']; ?></label>
<?php } else { ?>
<input type="file" size="60" name="attachment<?php echo $atch; ?>" id="attachment<?php echo $atch; ?>" tabindex="<?php echo $tabindex++; ?>" class="textinput" />
<?php } // if ?>
</div>
<?php } // for ?>

<?php if ($hd->atcherrs) { ?>
<div class="error_atch"><?php echo $hd->atcherrs; ?></div>
<?php } else { ?>
<div class="atchinfo hint">
<?php echo $text['atch_size'].$cat->atch_size; ?><br />
<?php echo $text['atch_types'].implode($cat->atch_types,', '); ?>
</div>
<?php } // if hd->atcherrs ?>
</fieldset>
<?php } // if cat->atch ?>


<div class="submit_container"><input type="submit" class="submit" tabindex="<?php echo $tabindex++; ?>" value="<?php echo $hd->submitText; ?>" /></div>
</div>
</form>

<?php if (isset($hd->messages) && count($hd->messages)) { ?>
<div class="messages-reverse"><?php echo $text['messages_reverse']; ?></div>
<div class="messages-reverse-list">
<?php foreach ($hd->messages as $msg) { ?>
<div class="message-container" id="message_<?php echo $msg->id; ?>">
<div class="top-left">
<div class="messagelink"><a href="<?php echo $msg->showMessageLink; ?>"><?php echo $text['message'].' #'.$msg->id;?></a></div>
<div class="poster"><?php echo $msg->poster; ?></div>
<?php if ($msg->supporter) { ?>
<div class="role"><?php echo $text['supporter']; ?></div>
<?php } // if supporter ?>
</div> <!-- top-left -->
<div class="top-right">
<div class="helpdesk-message"><?php echo $msg->message;?></div>
</div>
<div class="clearfloat"></div>
<div class="bottom-left"><?php echo $msg->tstamp;?></div>
<div class="bottom-right">
<div class="buttons">
<a href="#<?php echo $text['quote']; ?>" class="sublink" onclick="helpdeskBbcodeInsert('helpdesk_editform','helpdesk_message',<?php echo $msg->jsquote; ?>,''); return false;">
<?php echo $hd->createImage('quote16',$text['quote'],'title="'.$text['quote'].'"'); ?>
</a>
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
</div> <!-- message-reverse-list -->
<?php } // if isset hd->messages.. ?>

<div class="breadcrumb"><?php echo $hd->breadcrumb; ?></div>

</div>
<script type="text/javascript">
<!--
document.getElementById('helpdeskbbbuttons').style.display = '';
document.forms['helpdesk_editform'].elements['helpdesk_<?php echo ($hd->editSubject) ? 'subject' : 'message'; ?>'].focus();
//-->
</script>
<!-- indexer::continue -->
