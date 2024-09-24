<?php
/**
* @title        Minitek FAQ Book
* @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

if (!defined('DS'))
	define('DS',DIRECTORY_SEPARATOR);

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'route.php' );

jimport('joomla.filesystem.file');
jimport( 'joomla.application.component.helper' );
$params  = JComponentHelper::getParams('com_faqbookpro');
$document = JFactory::getDocument();

// Fix relative links
if ($params->get('fix_relative', false))
{
	$document->base = JURI::root();
}

// Add stylesheets
$document->addStyleSheet(JURI::base().'components/com_faqbookpro/assets/css/style.css');
$document->addStyleSheet(JURI::base().'media/jui/css/icomoon.css');

if ($params->get('load_fontawesome', 1))
{
	$document->addStyleSheet('https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css');
}

// Load jQuery
if ($params->get('load_jquery', 1))
{
	JHtml::_('jquery.framework');
}

$app = JFactory::getApplication();
$view = $app->input->get('view', '');

// Add scripts
if ($view && $view != 'sections' && $view != 'profile' && $view != 'questions')
{
	$document->addCustomTag('<script src="'.JURI::base(true).'/components/com_faqbookpro/assets/js/script.js" type="text/javascript"></script>');
}

// Add controller
$controller	= JControllerLegacy::getInstance('FAQBookPro');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
