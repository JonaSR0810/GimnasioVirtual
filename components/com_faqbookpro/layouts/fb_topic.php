<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$user = JFactory::getUser();
$this->tab = $displayData['tab'];
$this->sectionId = $displayData['sectionId'];
$this->topic = $displayData['topic'];
$this->topic_params = $displayData['topic_params'];
$this->questions_params = $displayData['questions_params'];
$this->utilities = $displayData['utilities'];
$canDo = $this->utilities->getActions('com_faqbookpro', 'topic', $this->topic->id);

?><div class="fbTopic" id="fbTopic_<?php echo $this->topic->id; ?>"><?php
	if ($this->topic_params->show_section_questions == 'active' && $this->topic_params->topicid)
	{
		// Topic Title
		if ($this->topic_params->show_topic_title)
		{
			?><h2 class="fbContent_title clearfix"><?php

				if ($this->topic_params->show_topic_title)
				{
					?><a id="topicPermalink_<?php echo $this->topic->id; ?>" class="fbTopic_permalink" href="<?php echo JRoute::_(FaqBookProHelperRoute::getTopicRoute($this->topic->id)); ?>">
				  	<?php echo $this->topic->title; ?>
					</a><?php
				}

			?></h2><?php
		}

		// Topic Description
		if ($this->topic_params->show_topic_description && $this->topic->description)
		{
			?><div class="fbTopic_description"><?php echo JHtml::_('content.prepare', $this->topic->description); ?></div><?php
		}

		// Topic Image
		if ($this->topic_params->show_topic_image && $this->topic->image)
		{
	  	?><div class="fbTopic_image">
	    	<img src="<?php echo JURI::root().$this->topic->image; ?>" alt="<?php echo $this->topic->image_alt; ?>">
			</div><?php
		}
	}

	// Loader
	if (isset($this->topic->questions))
	{
		$params = $this->utilities->getParams('com_faqbookpro');
		$pagination_limit = $params->get('pagination_limit', 20);
		$questions_found = count($this->topic->questions);
		if ($questions_found > $pagination_limit)
		{
			array_pop($this->topic->questions); // remove extra item from end of array
		}

		?><div class="topic_loader"><?php
			$list_placeholder = new JLayoutFile('fb_list_placeholder');
			echo $list_placeholder->render();
		?></div><?php

		// Questions
		if ($this->topic_params->show_topic_questions && isset($this->topic->questions) && $this->topic->questions)
		{
			?><div class="fbTopic_content">
				<div class="fbContent_qList"><?php
					$questionsData = array();
					$questionsData['sectionId'] = $this->sectionId;
					$questionsData['topic'] = $this->topic;
					$questionsData['questions_params'] = $this->questions_params;
					$questionsData['utilities'] = $this->utilities;
					if ($questions_found > $pagination_limit)
					{
						$questionsData['final_page'] = false;
					}
					else
					{
						$questionsData['final_page'] = true;
					}
					$layout = new JLayoutFile('fb_questions_'.$this->questions_params->questions_theme);
					echo $layout->render($questionsData);
				?></div>
			</div><?php

			if ($questions_found > $pagination_limit)
			{
				?><div class="fbContent_paging" id="fbPaging_<?php echo $this->topic->id; ?>">
					<a href="#" class="fbContent_paging_button<?php
						$paging_class = isset($this->topic_params->issection) ? 'fbContent_section_paging' : '';
						?> fbContent_topic_paging <?php echo $paging_class; ?> btn" data-page="2" data-tab="<?php echo $this->tab; ?>" data-topic="<?php echo $this->topic->id; ?>">		
						<span class="fbContent_paging_text"><?php echo JText::_('COM_FAQBOOKPRO_LOAD_MORE'); ?></span>
						<span class="fbContent_noresults"><?php echo JText::_('COM_FAQBOOKPRO_NO_MORE_ITEMS'); ?></span>
						<span class="fbContent_paging_loader"> </span>
					</a>
				</div><?php
			}
		}
		else if ($this->topic_params->show_topic_questions)
		{
			?><div class="fbTopic_content">
				<div class="fbContent_empty">
					<p><?php echo JText::_('COM_FAQBOOKPRO_NO_QUESTIONS_MESSAGE'); ?></p>
				</div>
			</div><?php
		}
	}
?></div><?php

// Subtopics
if (
	$app->input->get('filter', '', 'STRING') != 'tab'
	&& $this->topic_params->show_section_questions == 'active' && !$this->topic_params->merge_topics && $this->topic_params->show_subtopics && isset($this->topic->subtopics) && $this->topic->subtopics
)
{
	foreach ($this->topic->subtopics as $subtopic)
	{
		$data = array();
		$data['tab'] = $this->tab;
		$data['sectionId'] = $this->sectionId;
		$data['topic'] = $subtopic;
		$data['topic_params'] = $this->topic_params;
		$data['topic_params']->show_topic_title = $this->topic_params->show_subtopics_title;
		$data['topic_params']->show_topic_description = $this->topic_params->show_subtopics_description;
		$data['topic_params']->show_topic_image = $this->topic_params->show_subtopics_image;
		$data['topic_params']->show_topic_questions = $this->topic_params->show_subtopics_questions;
		$data['questions_params'] = $this->questions_params;
		$data['questions_params']->questions_opened = $subtopic->questions_opened;
		$data['utilities'] = $this->utilities;
		$layout = new JLayoutFile('fb_topic'); // recursive
		echo $layout->render($data);
	}
}
