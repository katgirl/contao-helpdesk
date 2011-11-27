<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * TYPOlight Helpdesk :: Data container array for content element tl_content
 *
 * NOTE: this file was edited with tabs set to 4.
 * @package Helpdesk
 * @copyright Copyright (C) 2007, 2008 by Peter Koch, IBK Software AG
 * @license See accompaning file LICENSE.txt
 */

/**
 * Add palette to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['helpdesk_comments'] = 
	'{type_legend},type,headline;{helpdesk_legend},helpdesk_reference,helpdesk_category;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/**
 * Add fields to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['helpdesk_reference'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_content']['helpdesk_reference'],
	'exclude'		=> true,
	'inputType'		=> 'select',
	'options'		=> array('page', 'article', 'news', 'faq', 'event'),
	'reference'		=> &$GLOBALS['TL_LANG']['tl_content']['helpdesk_item'],
	'eval'			=> array('mandatory'=>true)
);

$GLOBALS['TL_DCA']['tl_content']['fields']['helpdesk_category'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_content']['helpdesk_category'],
	'exclude'       => true,
	'inputType'     => 'select',
	'foreignKey'    => 'tl_helpdesk_categories.title',
	'eval'          => array('mandatory'=>true)
);

/**
 * Class tl_content_helpdesk
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_content_helpdesk extends Backend
{
	/**
	 * Get all faqs and return them as array
	 * @return array
	 */
	public function getFaqs()
	{
		$arrFaq = array();
		$objFaq = $this->Database->execute(
			"SELECT id, question, (SELECT title FROM tl_faq_category WHERE tl_faq.pid=id) AS parent FROM tl_faq ORDER BY parent, sorting");

		while ($objFaq->next())
		{
			$arrFaq[$objFaq->parent][$objFaq->id] = $objFaq->id . ' - ' . $objFaq->question;
		}

		return $arrFaq;
	} // getFaqs
} // tl_content_helpdesk


?>