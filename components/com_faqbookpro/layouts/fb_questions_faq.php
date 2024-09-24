<?php
/**
* @title        Minitek FAQ Book
* @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$user = JFactory::getUser();
$this->sectionId = $displayData['sectionId'];
$this->topic = $displayData['topic'];
$this->questions_params = $displayData['questions_params'];
$this->utilities = $displayData['utilities'];
$final_page = $displayData['final_page'];

if (isset($this->topic->questions) && $this->topic->questions)
{
	$faq_open = '';
	$fa_class = 'fa-caret-right';
	if ($this->questions_params->questions_opened)
	{
		$faq_open = 'faq_open';
		$fa_class = 'fa-caret-down';
	}

	foreach ($this->topic->questions as $key => $question)
	{
		$canDo = $this->utilities->getActions('com_faqbookpro', 'topic', $question->topicid);

		?><div class="fbContent_qListItem <?php echo $question->qListItem_class; ?>"><?php
			$nowDate = JFactory::getDate()->toSql();
			if ($question->publish_up > $nowDate)
			{
				?><div class="scheduled-header" title="<?php echo JText::sprintf('COM_FAQBOOKPRO_SCHEDULED_QUESTION_DESC_D', $question->publish_up); ?>"><?php
					echo JText::_('COM_FAQBOOKPRO_SCHEDULED_QUESTION');
				?></div><?php
			}
			if ($question->state == -2)
			{
				?><div class="trashed-header">
					<?php echo JText::_('COM_FAQBOOKPRO_TRASHED_QUESTION'); ?>
				</div><?php
			}
			if ($question->state == 0)
			{
				?><div class="unpublished-header">
					<?php echo JText::_('COM_FAQBOOKPRO_UNPUBLISHED_QUESTION'); ?>
				</div><?php
			}
			if ($question->state == 2)
			{
				?><div class="archived-header">
					<?php echo JText::_('COM_FAQBOOKPRO_ARCHIVED_QUESTION'); ?>
				</div><?php
			}

			?><div data-id="<?php echo $question->id; ?>" class="fbContent_qFaqItemInner <?php echo $faq_open; ?>">
				<div class="qListItem_preview">
					<h3 class="qListItem_header">
						<a href="#" data-id="<?php echo $question->id; ?>" class="qListItem_link qFaqItem_link" onclick="return false;"><?php
							?><span class="qFaqItem_triangle">
								<span class="fa <?php echo $fa_class; ?>"></span>
							</span><?php
							if ($question->pinned)
							{
								?><i class="fa fa-thumb-tack qListItem_pinned" title="<?php echo JText::_('COM_FAQBOOKPRO_PINNED_QUESTION'); ?>"></i> <?php
							}
							?><span><?php
								echo $question->title;
							?></span><?php
						?></a>
					</h3><?php
					if ($this->questions_params->questions_introtext && $question->introtext)
					{
						?><div class="qFaqItem_introtext"><?php
							echo $question->introtext;
						?></div><?php
					}
				?></div>

				<div id="a_w_<?php echo $question->id; ?>">
					<div class="qFaqItem_answer"><?php
						if ($this->questions_params->questions_image && $question->image)
						{
							?><img src="<?php echo JURI::root().$question->image; ?>" alt="<?php echo $question->image_alt; ?>" /><?php
						}
						?><div class="qListItem_introtext"><?php
							echo JHtml::_('content.prepare', $question->content);
						?></div><?php
						?><div class="qListItem_info"><?php
							if ($this->questions_params->questions_date)
							{
								?><span class="qListItem_date" title="<?php echo $question->created; ?>"><?php
									echo JText::_('COM_FAQBOOKPRO_ASKED');
									?>&nbsp;<?php
									echo $question->time_since;
									?>&nbsp;<?php
									echo JText::_('COM_FAQBOOKPRO_ASKED_AGO');
								?></span><?php
							}
							if ($this->questions_params->questions_author)
							{
								if ($question->created_by)
								{
									?><span><?php
										echo JText::_('COM_FAQBOOKPRO_BY');
										?>&nbsp;<?php
										echo $question->author_name;
									?></span><?php
								}
								else
								{
									if ($question->created_by_name)
									{
										?><span><?php
											echo JText::_('COM_FAQBOOKPRO_BY');
											?>&nbsp;<?php
											echo $question->created_by_name;
										?></span><?php
									}
								}
							}
						?></div><?php
						if ($this->questions_params->questions_topic)
						{
							?><div class="qListItem_topic">
								<a href="<?php echo JRoute::_(FAQBookProHelperRoute::getTopicRoute($question->topicid, '')); ?>">
									<span><?php echo $question->topic_title; ?></span>
								</a><?php
								if ($this->questions_params->questions_topic == 2)
								{
									if ($question->parents)
									{
										foreach ($question->parents as $parent)
										{
											?><a href="<?php echo JRoute::_(FAQBookProHelperRoute::getTopicRoute($parent['id'], '')); ?>">
												<span><?php echo $parent['title']; ?></span>
											</a><?php
										}
									}
								}
							?></div><?php
						}						
					?></div>
				</div>
			</div>
		</div><?php
	}
	if ($final_page)
	{
		?><div class="endofitems"></div><?php
	}
}
