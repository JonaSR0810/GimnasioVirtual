<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class JFormFieldProFeature extends JFormField
{
	public $type = 'profeature';
	private $params = null;

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$title = $this->get('title');
		$description = $this->get('description');
		$class = $this->get('class');

		if ($description)
		{
			$description = JText::_($description);
		}

		if ($description)
		{
			$html = '<div class="alert alert-'.$class.'">
			<i class="fa fa-lock"></i>&nbsp;&nbsp;'.JText::_($title).'
			<a href="https://www.minitek.gr/joomla/extensions/minitek-faq-book">
			'.JText::_('COM_FAQBOOKPRO_UPGRADE_TO_PRO').'
			</a>
			</div>';
		}

		return $html;
	}

	private function get($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}
