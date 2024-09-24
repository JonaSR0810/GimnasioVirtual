<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

// import Joomla controller library
jimport('joomla.application.component.controller');

// Add libraries prefix
JLoader::registerPrefix('FAQBookProLib', JPATH_SITE .DS. 'components' .DS. 'com_faqbookpro' .DS. 'libraries');

class FAQBookProController extends JControllerLegacy
{
	function display($cachable = false, $urlparams = false)
	{
		$app = JFactory::getApplication();
		$input = $app->input;

    // Make sure we have a default view
    if (!$input->get('view'))
		{
      $input->set('view', 'sections');
    }

		parent::display();
  }
}
