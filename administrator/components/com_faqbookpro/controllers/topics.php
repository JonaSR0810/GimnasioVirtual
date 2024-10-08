<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class FAQBookProControllerTopics extends JControllerAdmin
{
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function getModel($name = 'Topic', $prefix = 'FAQBookProModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function rebuild()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$this->setRedirect(JRoute::_('index.php?option=com_faqbookpro&view=topics', false));
		$model = $this->getModel();

		if ($model->rebuild())
		{
			// Rebuild succeeded.
			$this->setMessage(JText::_('COM_FAQBOOKPRO_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			// Rebuild failed.
			$this->setMessage(JText::_('COM_FAQBOOKPRO_REBUILD_FAILURE'));

			return false;
		}
	}

	public function rebuildroot()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__minitek_faqbook_topics'));
		$query->where($db->quoteName('id').' = '.$db->quote('1'));
		$db->setQuery($query);
		$root_topic = $db->loadObject();

		if ($root_topic)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_faqbookpro&view=topics', false));
			$this->setMessage(JText::_('COM_FAQBOOKPRO_ROOT_TOPIC_EXISTS'));
		}
		else
		{
			$query = $db->getQuery(true);
			$columns = array('id', 'section_id', 'parent_id', 'lft', 'rgt', 'level', 'title', 'alias', 'published', 'access', 'created_user_id', 'created_time', 'language');
			$values = array($db->quote(1), $db->quote(0), $db->quote(0), $db->quote(0), $db->quote(0), $db->quote(0), $db->quote('ROOT'), $db->quote('root'), $db->quote(1), $db->quote(1), $db->quote($userid), $db->quote($created), $db->quote('*'));
			$query
				->insert($db->quoteName('#__minitek_faqbook_topics'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));

			$db->setQuery($query);
			$db->execute();

			$this->setRedirect(JRoute::_('index.php?option=com_faqbookpro&view=topics', false));
			$this->setMessage(JText::_('COM_FAQBOOKPRO_ROOT_TOPIC_CREATED'));
		}
	}

	public function saveorder()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		JLog::add('FAQBookProControllerTopics::saveorder() is deprecated. Function will be removed in 4.0', JLog::WARNING, 'deprecated');

		// Get the arrays from the Request
		$order = $this->input->post->get('order', null, 'array');
		$originalOrder = explode(',', $this->input->getString('original_order_values'));

		// Make sure something has changed
		if (!($order === $originalOrder))
		{
			parent::saveorder();
		}
		else
		{
			// Nothing to reorder
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

			return true;
		}
	}
}
