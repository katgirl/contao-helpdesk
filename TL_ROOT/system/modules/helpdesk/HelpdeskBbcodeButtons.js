/**
 * Contao Helpdesk :: bbcode button javascript
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

/**
 * Surround the selected text with text1 and text2.
 */
function helpdeskBbcodeInsert(formID, elementID, text1, text2)
{
	var textarea = document.forms[formID].elements[elementID];
	
	if (typeof document.selection != 'undefined') {
		// internet explorer
		textarea.focus();
		var range = document.selection.createRange();
		var insText = range.text;
		range.text = text1 + insText + text2;
		// adjust cursor position
		range = document.selection.createRange();
		if (insText.length == 0)
			range.move('character', -text2.length);
		else
			range.moveStart('character', text1.length + insText.length + text2.length);
		range.select();
	} else 
	// Can a text range be created?
	if (typeof(textarea.caretPos) != "undefined" && textarea.createTextRange) {
		var caretPos = textarea.caretPos, temp_length = caretPos.text.length;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1)==' ' ? text1+caretPos.text+text2+' ' : text1+caretPos.text+text2;
		if (temp_length == 0) {
			caretPos.moveStart("character", -text2.length);
			caretPos.moveEnd("character", -text2.length);
			caretPos.select();
		} else
			textarea.focus(caretPos);
	} else 
		if (typeof(textarea.selectionStart) != "undefined") {
			// Mozilla text range wrap.
			var begin = textarea.value.substr(0, textarea.selectionStart);
			var selection = textarea.value.substr(textarea.selectionStart, textarea.selectionEnd - textarea.selectionStart);
			var end = textarea.value.substr(textarea.selectionEnd);
			var newCursorPos = textarea.selectionStart;
			var scrollPos = textarea.scrollTop;
			textarea.value = begin + text1 + selection + text2 + end;
			if (textarea.setSelectionRange) {
				if (selection.length == 0)
					textarea.setSelectionRange(newCursorPos + text1.length, newCursorPos + text1.length);
				else
					textarea.setSelectionRange(newCursorPos, newCursorPos + text1.length + selection.length + text2.length);
				textarea.focus();
			} // if
			textarea.scrollTop = scrollPos;
		} else {
			// Just put them on the end, then.
			textarea.value += text1 + text2;
			textarea.focus(textarea.value.length - 1);
		} // if
} // helpdeskBbcodeInsert

/**
 * Show the preview window
 */
function helpdeskBbcodePreview(el, formID, elementID) 
{
	var input = document.forms[formID].elements[elementID];
    window.open(
        el.href+'?bbcode='+encodeURIComponent(input.value),
        'HelpdeskPreview',
        'dependent,scrollbars,resizable,width=600,height=500,left=50,top=50'
    );
} // helpdeskBbcodePreview

/**
 * Show the bbcode help window
 */
function helpdeskBbcodeHelp(el) 
{
    window.open(
        el.href,
        'HelpdeskBbcodeHelp',
        'dependent,scrollbars,resizable,width=600,height=500,left=50,top=50'
    );
} // helpdeskBbcodeHelp

