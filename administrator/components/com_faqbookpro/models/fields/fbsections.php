<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldFBSections extends JFormFieldList
{
	protected $type = 'FBSections';

  protected function getInput()
  {
		$db = JFactory::getDbo();

		// Build the query.
		$query = $db->getQuery(true)
			->select('s.id, CONCAT(s.title, " [", s.alias, "]") AS title')
			->from($db->quoteName('#__minitek_faqbook_sections') . ' AS s')
			->where('s.state = 1')
			->order('s.title');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		$output = JHTML::_('select.genericlist', $options, $this->name.'[]', 'class="inputbox" multiple="multiple" size="10"', 'id', 'title', $this->value, $this->id);

		return $output;
  }
}
