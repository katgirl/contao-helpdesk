<?php
/**
 * TYPOlight Helpdesk :: Search window template
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

<form action="<?php echo $hd->formLink; ?>" id="helpdesk_searchform" method="post" >
<div class="formbody searchform">
<input type="hidden" name="helpdesk_action" value="<?php echo $hd->formAction; ?>" />

<!-- search terms -->
<div class="label_container">
<label for="helpdesk_searchterms"><?php echo $text['searchterms']; ?></label>
</div>
<div class="searchterms_container">
<input type="text" tabindex="<?php echo $tabindex++; ?>" maxlength="100" name="helpdesk_searchterms" id="helpdesk_searchterms" value="<?php echo $hd->searchterms; ?>" class="searchterms textinput" />
<input type="submit" class="submit" tabindex="<?php echo $tabindex++; ?>" value="<?php echo $hd->submitText; ?>" />
</div>
<?php if ($hd->searchtermsMissing) { ?>
<div class="error_message middle">
<?php echo $hd->createImage('error16'); ?>
<?php echo $text['searchterms_missing']; ?>
</div>
<?php } else { ?>
<div class="hint"><?php echo $text['searchterms_hint']; ?></div>
<?php } // if hd->searchtermsMissing ?>

<div id="advanced_checkbox_container" class="checkbox_container checkbox_part" style="display:none;">
<input type="checkbox" name="helpdesk_advanced" id="helpdesk_advanced" tabindex="<?php echo $tabindex++; ?>" class="checkbox" value="1"<?php if ($hd->advanced) echo ' checked="checked"'; ?> onclick="displayAdvanced()"/>
<label for="helpdesk_advanced"><?php echo $text['advanced']; ?></label>
</div>

<div id="advanced_settings"><!-- advanced -->

<!-- findmode -->
<fieldset>
<legend><?php echo $text['findmode']; ?></legend>
<?php foreach ($text['findmodes'] as $key => $txt) { ?>
<div class="radio_container radio_part">
<input type="radio" name="helpdesk_findmode" id="helpdesk_find_<?php echo $key; ?>" tabindex="<?php echo $tabindex++; ?>" class="radio" value="<?php echo $key; ?>"<?php if ($hd->findmode==$key) echo ' checked="checked"'; ?> />
<label for="helpdesk_find_<?php echo $key; ?>"><?php echo $txt; ?></label>
</div>
<?php } // foreach findmode ?>
<div class="hint"><?php echo $text['findmode_hint']; ?></div>
</fieldset>

<!-- searchmode -->
<fieldset>
<legend><?php echo $text['searchmode']; ?></legend>
<?php foreach ($text['searchmodes'] as $key => $txt) { ?>
<div class="radio_container radio_part">
<input type="radio" name="helpdesk_searchmode" id="helpdesk_search_<?php echo $key; ?>" tabindex="<?php echo $tabindex++; ?>" class="radio" value="<?php echo $key; ?>"<?php if ($hd->searchmode==$key) echo ' checked="checked"'; ?> />
<label for="helpdesk_search_<?php echo $key; ?>"><?php echo $txt; ?></label>
</div>
<?php } // foreach searchmode ?>
<div class="hint"><?php echo $text['searchmode_hint']; ?></div>
</fieldset>

<!-- searched parts -->
<fieldset>
<legend><?php echo $text['searchparts']; ?></legend>
<div class="checkbox_container checkbox_part">
<input type="checkbox" name="helpdesk_poster" id="helpdesk_poster" tabindex="<?php echo $tabindex++; ?>" class="checkbox" value="1"<?php if ($hd->poster) echo ' checked="checked"'; ?> />
<label for="helpdesk_poster"><?php echo $text['poster']; ?></label>
</div>
<div class="checkbox_container checkbox_part">
<input type="checkbox" name="helpdesk_subject" id="helpdesk_subject" tabindex="<?php echo $tabindex++; ?>" class="checkbox" value="1"<?php if ($hd->subject) echo ' checked="checked"'; ?> />
<label for="helpdesk_subject"><?php echo $text['subject']; ?></label>
</div>
<div class="checkbox_container checkbox_part">
<input type="checkbox" name="helpdesk_message" id="helpdesk_message" tabindex="<?php echo $tabindex++; ?>" class="checkbox" value="1"<?php if ($hd->msgtext) echo ' checked="checked"'; ?> />
<label for="helpdesk_message"><?php echo $text['message']; ?></label>
</div>
<div class="checkbox_container checkbox_part">
<input type="checkbox" name="helpdesk_attachments" id="helpdesk_attachments" tabindex="<?php echo $tabindex++; ?>" class="checkbox" value="1"<?php if ($hd->attachments) echo ' checked="checked"'; ?> />
<label for="helpdesk_attachments"><?php echo $text['attachments']; ?></label>
</div>
<?php if ($hd->noPartsChecked) { ?>
<div class="error_message middle">
<?php echo $hd->createImage('error16'); ?>
<?php echo $text['searchparts_unchecked']; ?>
</div>
<?php } else { ?>
<div class="hint"><?php echo $text['searchparts_hint']; ?></div>
<?php } // if hd->noPartsChecked ?>
</fieldset>

<!-- categories -->
<fieldset>
<legend><?php echo $text['searchcats']; ?></legend>
<?php foreach ($hd->categories as $cat) { ?>
<div class="category_container checkbox_container">
<input type="checkbox" name="helpdesk_categories[]" id="helpdesk_category_<?php echo $cat->id; ?>" tabindex="<?php echo $tabindex++; ?>" class="checkbox" value="<?php echo $cat->id; ?>"<?php if ($cat->checked) echo ' checked="checked"'; ?> />
<label for="helpdesk_category_<?php echo $cat->id; ?>"><?php echo $cat->title; ?></label>
</div>
<?php } // foreach hd->categories ?>
<?php if ($hd->noCategoriesChecked) { ?>
<div class="error_message middle">
<?php echo $hd->createImage('error16'); ?>
<?php echo $text['searchcats_unchecked']; ?>
</div>
<?php } else { ?>
<div class="hint"><?php echo $text['searchcats_hint']; ?></div>
<?php } // if hd->noCategoriesChecked ?>
</fieldset>

</div><!-- advanced -->
</div>
</form>

<div class="breadcrumb"><?php echo $hd->breadcrumb; ?></div>

</div>

<!-- set focus into searchterms -->
<script type="text/javascript">
<!--
function displayAdvanced()
{
	if (document.getElementById('helpdesk_advanced').checked)
		document.getElementById('advanced_settings').style.display = '';
	else {
		document.getElementById('advanced_settings').style.display = 'none';
		document.getElementById('helpdesk_find_messages').checked = true;
		document.getElementById('helpdesk_search_natural').checked = true;
		document.getElementById('helpdesk_poster').checked = false;
		document.getElementById('helpdesk_subject').checked = true;
		document.getElementById('helpdesk_message').checked = true;
		document.getElementById('helpdesk_attachments').checked = false;
		var cbs = document.getElementsByName('helpdesk_categories[]');
		for (var i = 0; i < cbs.length; ++i) cbs[i].checked = true;
	} // if
} // dispAdvanced
document.getElementById('advanced_checkbox_container').style.display = '';
displayAdvanced();
document.forms['helpdesk_searchform'].elements['helpdesk_searchterms'].focus();
//-->
</script>
<!-- indexer::continue -->
