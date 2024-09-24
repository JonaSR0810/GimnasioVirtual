<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class FAQBookProViewTopics extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$utilities = new FAQBookProHelper();
		$utilities->addSubmenu('topics');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item)
		{
			$this->ordering[$item->parent_id][] = $item->id;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo = FAQBookProHelper::getActions('com_faqbookpro', 'topic', $this->state->get('filter.topic_id'));

		JToolbarHelper::title(JText::_('COM_FAQBOOKPRO_TOPICS_TITLE'), 'folder');

		if ($canDo->get('core.create') || (count(FAQBookProHelper::getAuthorisedTopics('core.create'))) > 0)
		{
			JToolbarHelper::addNew('topic.add');
		}

		if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
		{
			JToolbarHelper::editList('topic.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('topics.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('topics.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('topics.archive');
			JToolbarHelper::checkin('topics.checkin');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::custom('topics.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
			JToolbarHelper::custom('topics.rebuildroot', 'refresh.png', 'refresh_f2.png', 'COM_FAQBOOKPRO_TOOLBAR_REBUILD_ROOT_TOPIC', false);
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'topics.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('topics.trash');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_faqbookpro');
		}
	}
}
