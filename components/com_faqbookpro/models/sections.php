<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class FAQBookProModelSections extends JModelLegacy
{
	var $utilities = null;

	function __construct()
	{
		$this->utilities = $this->getUtilitiesLib();

	 	parent::__construct();
	}

	public function getUtilitiesLib()
	{
		$utilities = new FAQBookProLibUtilities;

		return $utilities;
	}

	public static function getSections($sections = false)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__minitek_faqbook_sections');

		if ($sections)
		{
			$query->where('id IN (' . implode(',', $sections) . ')');
		}

		$query->where('state = 1');
		$query->where('access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');

		// Filter by language
		$query->where('language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

		$query->order('ordering');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if ($rows)
		{
			return $rows;
		}
		else
		{
			return false;
		}
	}

	public static function getChildrenTopics($topicId)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__minitek_faqbook_topics');
		$query->where('published = 1');
		$query->where('parent_id = '.$db->quote($topicId));
		$query->where('access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		$query->order('lft');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if ($rows)
		{
			return $rows;
		}
		else
		{
			return false;
		}
	}

	public function getSectionQuestionsCount($sectionId)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__minitek_faqbook_questions AS a');

		// Join over the topics
		$query->select('c.title AS topic_title, c.path AS topic_route, c.access AS topic_access, c.alias AS topic_alias')
			->join('LEFT', '#__minitek_faqbook_topics AS c ON c.id = a.topicid');

		// Join over the sections
		$query->select('section.title as section_title, section.id as section_id')
			->join('LEFT', '#__minitek_faqbook_sections as section ON section.id = c.section_id');

		// Filter by access level
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')')
			->where('c.access IN (' . $groups . ')')
			->where('section.access IN (' . $groups . ')');

		// Filter by state
		$query->where('a.state = 1')
			->where('c.published = 1')
			->where('section.state = 1');

		// Don't count private questions
		$query->where('a.private = 0');

		// Filter by start and end dates.
		$nullDate = $db->quote($db->getNullDate());
		$date = JFactory::getDate();
		$nowDate = $db->quote($date->toSql());

		$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

		// Filter by section
		$query->where('section.id = ' . $db->quote($sectionId));
		$query->order('a.created DESC');
		$db->setQuery($query);

		// Get the results
		$count = $db->loadResult();

		return $count;
	}

	public function getTopicQuestionsCount($topicId)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__minitek_faqbook_questions AS a');

		// Join over the topics
		$query->select('c.title AS topic_title, c.path AS topic_route, c.access AS topic_access, c.alias AS topic_alias')
			->join('LEFT', '#__minitek_faqbook_topics AS c ON c.id = a.topicid');

		// Filter by access level
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')')
			->where('c.access IN (' . $groups . ')');

		// Filter by state
		$query->where('a.state = 1')
			->where('c.published = 1');

		// Don't count private questions
		$query->where('a.private = 0');

		// Filter by start and end dates.
		$nullDate = $db->quote($db->getNullDate());
		$date = JFactory::getDate();
		$nowDate = $db->quote($date->toSql());

		$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

		// Filter by topic (including children topics)
		$topic_tbl = JTable::getInstance('Topic', 'FAQBookProTable');
		$topic_tbl->load($topicId);
		$rgt = $topic_tbl->rgt;
		$lft = $topic_tbl->lft;
		$baselevel = (int) $topic_tbl->level;
		$query->where('c.lft >= ' . (int) $lft)
			->where('c.rgt <= ' . (int) $rgt);
		$query->order('a.created DESC');
		$db->setQuery($query);

		// Get the results
		$count = $db->loadResult();

		return $count;
	}
}
