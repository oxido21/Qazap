<?php
/**
 * qazapcurrency.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('JPATH_PLATFORM') or die;
JFormHelper::loadFieldClass('list');
/**
 * Form Field class for the Qazap Platform.
 * Supports a generic list of options.
 *
 * @package     Qazap.Platform
 * @subpackage  Form
 * @since       1.0.0
 */
class JFormFieldQazapCurrency extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'QazapCurrency';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions()
	{
		static $currencies = null;
		
		if($currencies == null)
		{
			$db  = JFactory::getDBO();
			$sql = $db->getQuery(true) 
				 ->select(array('id', 'currency'))
				 ->from('#__qazap_currencies')
				 ->where('state = 1');
			$db->setQuery($sql);
			$currencies = $db->loadObjectList();			
		}
		
		$options = array(JHtml::_('select.option', 0, JText::_('JSELECT')));
		
		foreach($currencies as $currency)
		{
			$options[] = JHtml::_('select.option', (int) $currency->id, $currency->currency);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
