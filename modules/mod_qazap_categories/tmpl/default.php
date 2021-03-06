<?php
/**
 * default.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazap Categories Module
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;

JHTML::_('bootstrap.tooltip');
$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::base(true) . '/modules/mod_qazap_categories/assets/css/module.css');
$doc->addScript(JUri::base(true) . '/modules/mod_qazap_categories/assets/js/module.js');
?>
<ul class="qazap-categories-module nav menu qazap-categories-<?php echo $moduleclass_sfx; ?>">
  <?php require JModuleHelper::getLayoutPath('mod_qazap_categories', $params->get('layout', 'default').'_items'); ?>  
</ul>
