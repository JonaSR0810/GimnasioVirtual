<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class FAQBookProViewTopic extends JViewLegacy
{
	protected $form;
	protected $item;
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
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = FAQBookProHelper::getActions('com_faqbookpro', 'topic', $this->item->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Get javascript variables
		JFactory::getDocument()->addScriptDeclaration('window.mfbvars = {
			token: "'.JSession::getFormToken().'",
			site_path: "'.JURI::base().'",
		};');

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

		// Built the actions for new and existing records.
		$canDo = $this->canDo;

		\JToolbarHelper::title(
			\JText::_('COM_FAQBOOKPRO_TOPIC_TITLE_'.($checkedOut ? 'VIEW_TOPIC' : ($isNew ? 'ADD_TOPIC' : 'EDIT_TOPIC'))),
			'pencil-2 article-add'
		);

		// For new records, check the create permission.
		if ($isNew && count(FAQBookProHelper::getAuthorisedTopics('core.create')) > 0)
		{
			JToolbarHelper::apply('topic.apply');
			JToolbarHelper::save('topic.save');
			JToolbarHelper::save2new('topic.save2new');
			JToolbarHelper::cancel('topic.cancel');
		}
		// If not checked out, can save the item.
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_user_id == $userId))
				{
					JToolbarHelper::apply('topic.apply');
					JToolbarHelper::save('topic.save');

					if ($canDo->get('core.create'))
					{
						JToolbarHelper::save2new('topic.save2new');
					}
				}
			}

			JToolbarHelper::cancel('topic.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
