<?php
/**
* @title        FAQ Book Pro
* @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die('Restricted access');

if (!defined('DS'))
	define('DS',DIRECTORY_SEPARATOR);

// Include dependancies
jimport('joomla.application.component.controller');

// Check component access
if (!JFactory::getUser()->authorise('core.manage', 'com_faqbookpro'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include basic helper
JLoader::register('FAQBookProHelper', JPATH_COMPONENT_ADMINISTRATOR. '/helpers/faqbookpro.php');

$controller	= JControllerLegacy::getInstance('FAQBookPro');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

$document = JFactory::getDocument();

if($document->getType() != 'raw')
{
	$document->addStyleSheet(JURI::root().'administrator/components/com_faqbookpro/assets/css/style.css?v=3.9.4');
	$document->addStyleSheet('https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css');

	// Add js
	$document->addScript(JURI::root().'administrator/components/com_faqbookpro/assets/js/script.js?v=3.9.4');
}
