<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class FAQBookProViewDashboard extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->utilities = new FAQBookProHelper();

		// Skip if view == update
		if (JFactory::getApplication()->input->get('view') != 'update')
		{
			// Load dashboard.js
			JHtml::_('bootstrap.framework');
			JFactory::getDocument()->addScript(JURI::root(true).'/administrator/components/com_faqbookpro/assets/js/dashboard.js');

			$this->addToolbar();

			parent::display($tpl);
		}
	}

	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_FAQBOOKPRO_DASHBOARD_TITLE'), '');
	}
}
