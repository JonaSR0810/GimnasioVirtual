<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

require_once( JPATH_SITE.DS.'components'.DS.'com_faqbookpro'.DS.'libraries'.DS.'utilities.php' );

class FAQBookProViewQuestion extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = FAQBookProHelper::getActions('com_faqbookpro', 'question', $this->item->id);
		$model = $this->getModel();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();

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
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$isNew = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Build the actions for new and existing records.
		$canDo = $this->canDo;

		\JToolbarHelper::title(
			\JText::_('COM_FAQBOOKPRO_QUESTION_TITLE_'.($checkedOut ? 'VIEW_QUESTION' : ($isNew ? 'ADD_QUESTION' : 'EDIT_QUESTION'))),
			'pencil-2 article-add'
		);

		// For new records, check the create permission.
		if ($isNew && count(FAQBookProHelper::getAuthorisedTopics('core.create')) > 0)
		{
			JToolbarHelper::apply('question.apply');
			JToolbarHelper::save('question.save');
			JToolbarHelper::save2new('question.save2new');
			JToolbarHelper::cancel('question.cancel');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolbarHelper::apply('question.apply');
					JToolbarHelper::save('question.save');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						JToolbarHelper::save2new('question.save2new');
					}
				}
			}

			JToolbarHelper::cancel('question.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
