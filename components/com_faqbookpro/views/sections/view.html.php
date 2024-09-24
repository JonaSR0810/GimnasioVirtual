<?php

/**
 * @title        Minitek FAQ Book
 * @copyright    Copyright (C) 2011-2020 Minitek, All rights reserved.
 * @license      GNU General Public License version 3 or later.
 * @author url   https://www.minitek.gr/
 * @developers   Minitek.gr
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

jimport('joomla.application.component.view');

class FAQBookProViewSections extends JViewLegacy
{
	function display($tpl = null)
	{
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$this->model = $this->getModel();
		$sectionModel = JModelLegacy::getInstance('Section', 'FAQBookProModel');
		$activeMenu = $app->getMenu()->getActive();
		$this->home_title = $activeMenu->title;
		$this->home_itemid = $activeMenu->id;

		// Get Params & Attribs
		$utilities = $this->model->utilities;
		$this->assignRef('utilities', $utilities);
		$this->params = $utilities->getParams('com_faqbookpro');

		// Get Sections
		$specific_sections = $this->params->get('fb_sections', '');
		$this->sections = $this->model->getSections($specific_sections);

		// Params
		$this->topic_col_class = '';

		// Extra Section data
		foreach ($this->sections as $key => $section) {
			$section->q_count = $this->model->getSectionQuestionsCount($section->id);
			$section->topics = $sectionModel->getSectionTopics($section->id);

			foreach ($section->topics as $topic) {
				$topic->q_count = $this->model->getTopicQuestionsCount($topic->id);
				$topic->children = $this->model->getChildrenTopics($topic->id);

				if ($topic->children) {
					foreach ($topic->children as $child) {
						$child->q_count = $this->model->getTopicQuestionsCount($child->id);

						$childParams = json_decode($child->params, false);
						$child->icon_class = 'folder';
						if (isset($childParams->topic_icon_class)) {
							$child->icon_class = $childParams->topic_icon_class;
						}
					}
				}

				$topicParams = json_decode($topic->params, false);
				$topic->icon_class = 'folder';
				if (isset($topicParams->topic_icon_class)) {
					$topic->icon_class = $topicParams->topic_icon_class;
				}
			}
		}

		// Set metadata
		if ($app->get('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $this->params->get('page_title'));
		} elseif ($app->get('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $this->params->get('page_title'), $app->get('sitename'));
		} else {
			$title = $this->params->get('page_title');
		}

		$document->setTitle($title);

		if ($this->params->get('menu-meta_description')) {
			$document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords')) {
			$document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots')) {
			$document->setMetadata('robots', $this->params->get('robots'));
		}

		if (!is_object($this->params->get('metadata'))) {
			$metadata = new Registry($this->params->get('metadata'));
		}

		$mdata = $metadata->toArray();

		foreach ($mdata as $k => $v) {
			if ($v) {
				$document->setMetadata($k, $v);
			}
		}

		// Menu page display options
		if ($this->params->get('page_heading')) {
			$this->params->set('page_title', $this->params->get('page_heading'));
		}
		$this->params->set('show_page_title', $this->params->get('show_page_heading'));

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Display the view
		parent::display($tpl);
	}
}
