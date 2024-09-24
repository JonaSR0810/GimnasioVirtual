<?php

/**
 * @title        Minitek FAQ Book
 * @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
 * @license      GNU General Public License version 3 or later.
 * @author url   https://www.minitek.gr/
 * @developers   Minitek.gr
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

jimport('joomla.application.component.view');

class FAQBookProViewTopic extends JViewLegacy
{
	protected $item;

	function display($tpl = null)
	{
		$this->item = $this->get('Item');
		$app = JFactory::getApplication();
		$ajax = $app->input->get('task', false);
		$this->user = JFactory::getUser();
		$this->topicId = $this->item->id;
		$this->model = $this->getModel();

		// Check for no 'access_view'
		if (!$this->item->access_view) {
			if ($this->user->id) {
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
				$app->setHeader('status', 403, true);
				return;
			} else {
				$return = base64_encode(JUri::getInstance());
				$login_url_with_return = JRoute::_('index.php?option=com_users&view=login&return=' . $return);
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'notice');
				$app->redirect($login_url_with_return, 403);
			}
		}

		// Get component params, topic params & section params
		$this->topic = $this->item;
		$this->topic->issubtopic = false;
		$this->sectionId = $this->topic->section_id;
		$sectionModel = JModelLegacy::getInstance('Section', 'FAQBookProModel');
		$this->section = $sectionModel->getItem($this->sectionId);
		$this->sectionParams = json_decode($this->section->attribs, false);
		$this->utilities = $this->model->utilities;
		$this->params = $this->utilities->getParams('com_faqbookpro');
		$topicParams = json_decode($this->topic->params, false);
		$this->topic_params = new stdClass(); // Store topic params
		$this->questions_params = new stdClass(); // Store question params

		// Add hit
		$this->model->addHit($this->topic->id);

		// Get Navigation
		if (!$ajax) {
			// Get Top Navigation
			if (isset($this->sectionParams->topnav) && $this->sectionParams->topnav) {
				$this->topnavigation = true;
			} else {
				$this->topnavigation = false;
			}

			// Get Left Navigation
			$leftnav = $this->sectionParams->leftnav;
			$this->leftnavigation = false;
			if ($leftnav) {
				$this->leftnavigation = true;
			}
		}

		// Load Endpoint Topics / All Topics
		$this->loadAllTopics = 1;
		if (isset($this->sectionParams->load_all_topics)) {
			$this->loadAllTopics = $this->sectionParams->load_all_topics;
		}

		// Get page
		$this->page = $app->input->get('page', '1');

		// Ordering type
		$this->topic_params->ordering_type = 'static';

		// Get active tab
		$active_tab = 'recent';
		if (isset($this->sectionParams->active_tab) && $this->topic_params->ordering_type == 'dynamic') {
			$active_tab = $this->sectionParams->active_tab;
		}
		$this->tab = $app->input->get('tab', $active_tab);

		// Ordering
		if ($this->topic_params->ordering_type == 'dynamic') {
			$ordering = $this->tab;
			$ordering_dir = 'DESC';
		} else if ($this->topic_params->ordering_type == 'static') {
			$ordering = $this->params->get('questions_ordering', 'ordering'); // old value
			$ordering_dir = $this->params->get('questions_ordering_dir', 'DESC'); // old value
			if (isset($this->sectionParams->questions_ordering)) {
				$ordering = $this->sectionParams->questions_ordering;
			}
			if (isset($this->sectionParams->questions_ordering_dir)) {
				$ordering_dir = $this->sectionParams->questions_ordering_dir;
			}
		}

		// Group method
		$this->topic_params->merge_topics = false;

		// Reset qvisibility
		if (!isset($this->topic->qvisibility)) {
			$this->topic->qvisibility = 0;
		}

		// Show topic title
		$this->topic_params->show_topic_title = true;
		if (isset($this->sectionParams->topic_title)) {
			$this->topic_params->show_topic_title = $this->sectionParams->topic_title;
		}

		// Show topic description
		$this->topic_params->show_topic_description = true;
		if (isset($this->sectionParams->topic_description)) {
			$this->topic_params->show_topic_description = $this->sectionParams->topic_description;
		}

		// Show topic image
		$this->topic_params->show_topic_image = false;
		if (isset($this->sectionParams->topic_image)) {
			$this->topic_params->show_topic_image = $this->sectionParams->topic_image;
			$this->topic->image = isset($topicParams->image) ? $topicParams->image : false;
			$this->topic->image_alt = isset($topicParams->image_alt) ? $topicParams->image_alt : false;
		}

		// Topic questions
		$this->topic_params->show_section_questions = 'active';
		$this->topic_params->topicid = $this->topicId;

		if (!isset($topicParams->enable_questions)) {
			$this->topic_params->show_topic_questions = true;
		} else {
			$this->topic_params->show_topic_questions = $topicParams->enable_questions;
		}

		if ($this->topic_params->show_topic_questions) {
			$this->topic->questions = $this->model->getTopicQuestions($this->topicId, $ordering, $ordering_dir, $this->page, $this->topic_params->merge_topics);
		}

		// Questions theme
		$this->questions_params->questions_theme = 'faq';

		// Questions opened
		$this->questions_params->questions_opened = false;
		if (isset($topicParams->questions_opened) && $topicParams->questions_opened != '') {
			$this->questions_params->questions_opened = $topicParams->questions_opened;
		} else {
			if (!isset($this->sectionParams->section_questions_opened)) {
				$this->questions_params->questions_opened = $this->params->get('questions_opened', false); // old value
			} else {
				$this->questions_params->questions_opened = $this->sectionParams->section_questions_opened;
			}
		}

		// Subtopics
		$this->topic_params->show_subtopics = true;
		if (isset($this->sectionParams->show_subtopics)) {
			$this->topic_params->show_subtopics = $this->sectionParams->show_subtopics;
		}

		if (!$this->topic_params->merge_topics && $this->topic_params->show_subtopics) {
			// Show subtopic title
			$this->topic_params->show_subtopics_title = true;
			if (isset($this->sectionParams->subtopics_title)) {
				$this->topic_params->show_subtopics_title = $this->sectionParams->subtopics_title;
			}

			// Show subtopic description
			$this->topic_params->show_subtopics_description = true;
			if (isset($this->sectionParams->subtopics_description)) {
				$this->topic_params->show_subtopics_description = $this->sectionParams->subtopics_description;
			}

			// Show subtopic image
			$this->topic_params->show_subtopics_image = false;
			if (isset($this->sectionParams->subtopics_image)) {
				$this->topic_params->show_subtopics_image = $this->sectionParams->subtopics_image;
			}

			// Show subtopic questions
			$this->topic_params->show_subtopics_questions = true;
			if (isset($this->sectionParams->subtopics_questions)) {
				$this->topic_params->show_subtopics_questions = $this->sectionParams->subtopics_questions;
			}

			$this->topic->subtopics = $this->model->getTopicChildren($this->topicId);

			foreach ($this->topic->subtopics as $key => $subtopic) {
				$subtopic->issubtopic = true;
				$subtopicParams = json_decode($subtopic->params, false);
				$subtopic->image = isset($subtopicParams->image) ? $subtopicParams->image : false;
				$subtopic->image_alt = isset($subtopicParams->image_alt) ? $subtopicParams->image_alt : false;

				// Reset qvisibility
				if (!isset($subtopic->qvisibility)) {
					$subtopic->qvisibility = 0;
				}

				// Questions opened
				$subtopic->questions_opened = $this->questions_params->questions_opened;
				if (isset($subtopicParams->questions_opened) && $subtopicParams->questions_opened != '') {
					$subtopic->questions_opened = $subtopicParams->questions_opened;
				}

				// Questions
				if ($this->topic_params->show_subtopics_questions) {
					if (!isset($subtopicParams->enable_questions)) {
						$subtopicParams->enable_questions = true;
					}

					if ($subtopicParams->enable_questions) {
						$subtopic->questions = $this->model->getTopicQuestions($subtopic->id, $ordering, $ordering_dir, $this->page, $this->topic_params->merge_topics);
					}

					if (isset($subtopic->questions) && $subtopic->questions) {
						foreach ($subtopic->questions as $subquestion) {
							$this->prepareQuestion($subquestion);
						}
					}
				}
			}
		}

		// Show questions link
		$this->questions_params->questions_link = false;

		// Show answers
		$this->questions_params->questions_answers = false;

		// Show state labels
		$this->questions_params->questions_state_labels = false;

		// Show image
		$this->questions_params->questions_image = false;
		if (isset($this->sectionParams->questions_image)) {
			$this->questions_params->questions_image = $this->sectionParams->questions_image;
		}

		// Show introtext
		if (!isset($this->sectionParams->questions_introtext)) {
			$this->questions_params->questions_introtext = $this->params->get('questions_text', true); // old value
		} else {
			$this->questions_params->questions_introtext = $this->sectionParams->questions_introtext;
		}

		// Show views
		$this->questions_params->questions_views = false;

		// Show date
		if (!isset($this->sectionParams->questions_date)) {
			$this->questions_params->questions_date = $this->params->get('questions_date', true); // old value
		} else {
			$this->questions_params->questions_date = $this->sectionParams->questions_date;
		}

		// Show author
		$this->questions_params->questions_author = false;
		if (isset($this->sectionParams->questions_author)) {
			$this->questions_params->questions_author = $this->sectionParams->questions_author;
		}

		// Show topic
		$this->questions_params->questions_topic = false;
		if (isset($this->sectionParams->questions_topic)) {
			$this->questions_params->questions_topic = $this->sectionParams->questions_topic;
		}

		// Prepare questions
		if (isset($this->topic->questions) && $this->topic->questions) {
			foreach ($this->topic->questions as $key => $question) {
				$this->prepareQuestion($question, $this->sectionParams);
			}
		}

		if (!$ajax) {
			$document = JFactory::getDocument();

			// Get javascript variables
			$page_view = $app->input->get('view', '');

			$document->addScriptDeclaration(
				'window.fbvars = {
					site_path: "' . JURI::root() . '",
					page_view: "' . $page_view . '",
					sectionId: "' . $this->sectionId . '",
					topicId: "' . $this->topicId . '",
					leftnav: "' . $leftnav . '",
					loadAllTopics: "' . $this->loadAllTopics . '",
					myid: "' . $this->user->id . '",
					active_tab: "' . $active_tab . '"
				};'
			);

			// Set title
			if ($app->get('sitename_pagetitles', 0) == 1) {
				$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $this->topic->title);
			} elseif ($app->get('sitename_pagetitles', 0) == 2) {
				$title = JText::sprintf('JPAGETITLE', $this->topic->title, $app->get('sitename'));
			} else {
				$title = $this->topic->title;
			}

			$document->setTitle($title);

			// Set metadata
			if ($this->topic->metadesc) {
				$document->setDescription($this->topic->metadesc);
			} elseif ($this->params->get('menu-meta_description')) {
				$document->setDescription($this->params->get('menu-meta_description'));
			}

			if ($this->topic->metakey) {
				$document->setMetadata('keywords', $this->topic->metakey);
			} elseif ($this->params->get('menu-meta_keywords')) {
				$document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
			}

			if (!is_object($this->topic->metadata)) {
				$this->topic->metadata = new Registry($this->topic->metadata);
			}

			$mdata = $this->topic->metadata->toArray();

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
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		// Display the view
		parent::display($tpl);
	}

	public function prepareQuestion($question)
	{
		// Resolved
		if ($question->resolved == '1') {
			$question->resolved_class = 'resolved';
		} else {
			$question->resolved_class = '';
		}

		// Custom state
		$question->customstate = false;

		// Image
		$images = json_decode($question->images, false);
		$question->image = isset($images->image) ? $images->image : false;
		$question->image_alt = isset($images->image_alt) ? $images->image_alt : false;

		// Introtext
		if (!isset($this->sectionParams->questions_introtext_limit)) {
			$this->sectionParams->questions_introtext_limit = $this->params->get('questions_text_limit', '20'); // use old value
		}
		$introtext_limit = $this->sectionParams->questions_introtext_limit;
		$question->introtext = preg_replace('/\{.*\}/', '', $question->content);
		$question->introtext = preg_replace('/\[.*\]/', '', $question->introtext);
		if ($introtext_limit) {
			$question->introtext = $this->utilities->getWordLimit($question->introtext, $introtext_limit);
		}

		// Date
		$question->time_since = $this->utilities->getTimeSince($question->created);

		// Pinned
		if ($question->pinned && $this->tab == 'recent') {
			$question->qListItem_class .= ' qListItem_pinned';
		}

		// Author name
		if (!isset($this->sectionParams->questions_author)) {
			$this->sectionParams->questions_author = $this->params->get('questions_author', true); // old value
			$this->sectionParams->questions_author_name = $this->params->get('questions_author_name', 'username'); // old value
		}
		if ($question->created_by) {
			// Check whether user exists
			$userExists = $this->utilities->userExists($question->created_by);
			$question->created_by = $userExists ? $question->created_by : false;

			if ($this->sectionParams->questions_author_name === 'name') {
				$question->author_name = JFactory::getUser($question->created_by)->name;
			} else if ($this->sectionParams->questions_author_name === 'username') {
				$question->author_name = JFactory::getUser($question->created_by)->username;
			}
		} else if ($question->created_by_name) {
			$question->author_name = $question->created_by_name;
		}

		// Scheduled
		$nowDate = JFactory::getDate()->toSql();
		if ($question->publish_up > $nowDate) {
			$question->qListItem_class .= ' qListItem_scheduled';
		}

		// Unpublished
		if ($question->state == 0) {
			$question->qListItem_class .= ' qListItem_unpublished';
		}

		// Archived
		if ($question->state == 2) {
			$question->qListItem_class .= ' qListItem_archived';
		}

		// Parent topics
		if (isset($this->sectionParams->questions_topic) && $this->sectionParams->questions_topic == 2) {
			$question->topics = $this->model->getTopicParentTopics($question->topicid, $topics = array());
			$parents = array();

			if (count($question->topics)) {
				foreach ($question->topics as $key => $topic) {
					$parent = array();
					$parent['id'] = $topic;
					$parent['title'] = $this->model->getItem($topic)->title;
					$parents[] = $parent;
				}
			}

			$question->parents = $parents;
		}
	}
}
