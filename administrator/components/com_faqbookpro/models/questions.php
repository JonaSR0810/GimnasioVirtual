<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class FAQBookProModelQuestions extends JModelList
{
	protected static $items = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'topic', 'a.topic', 'topic_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'pinned', 'a.pinned',
				'created', 'a.created',
				'created_by', 'a.created_by', 'author_name',
				'created_by_name', 'a.created_by_name',
				'created_by_email', 'a.created_by_email',
				'created_by_alias', 'a.created_by_alias',
				'ordering', 'a.ordering',
				'language', 'a.language',
				'hits', 'a.hits',
				'published', 'a.published',
				'author_id',
				'topic_id',
				'level'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$pinned = $this->getUserStateFromRequest($this->context . '.filter.pinned', 'filter_pinned', '');
		$this->setState('filter.pinned', $pinned);

		$topicId = $this->getUserStateFromRequest($this->context . '.filter.topic_id', 'filter_topic_id');
		$this->setState('filter.topic_id', $topicId);

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
		$this->setState('filter.level', $level);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		// List state information.
		parent::populateState('a.id', 'desc');

		// Force a language
		$forcedLanguage = $app->input->get('forcedLanguage');

		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.pinned');
		$id .= ':' . $this->getState('filter.topic_id');
		$id .= ':' . $this->getState('filter.level');
		$id .= ':' . $this->getState('filter.access');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.answers, a.checked_out, a.checked_out_time, a.topicid' .
					', a.state, a.locked, a.pinned, a.resolved, a.private, a.access, a.created, a.created_by, a.created_by_name, a.created_by_email' .
					', a.created_by_alias, a.ordering, a.featured, a.language, a.hits'
			)
		);
		$query->from('#__minitek_faqbook_questions AS a');

		// Join over the language
		$query->select('l.title AS language_title')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the topics.
		$query->select('c.title AS topic_title')
			->join('LEFT', '#__minitek_faqbook_topics AS c ON c.id = a.topicid');

		// Join over the users for the author.
		$query->select('CASE WHEN a.created_by = 0 THEN a.created_by_name ELSE ua.name END as author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state = 0 OR a.state = 1)');
		}

		// Filter by pinned state
		$pinned = $this->getState('filter.pinned');

		if ($pinned != '')
		{
			$query->where('a.pinned = ' . $db->quote($pinned));
		}

		// Filter by a single or group of topics.
		$baselevel = 1;
		$topicId = $this->getState('filter.topic_id');

		if (is_numeric($topicId))
		{
			$topic_tbl = JTable::getInstance('Topic', 'FAQBookProTable');
			$topic_tbl->load($topicId);
			$rgt = $topic_tbl->rgt;
			$lft = $topic_tbl->lft;
			$baselevel = (int) $topic_tbl->level;
			$query->where('c.lft >= ' . (int) $lft)
				->where('c.rgt <= ' . (int) $rgt);
		}
		elseif (is_array($topicId))
		{
			JArrayHelper::toInteger($topicId);
			$topicId = implode(',', $topicId);
			$query->where('a.topicid IN (' . $topicId . ')');
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where('c.level <= ' . ((int) $level + (int) $baselevel - 1));
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');

		if ($orderCol == 'a.ordering' || $orderCol == 'topic_title')
		{
			$orderCol = 'c.title ' . $orderDirn . ', a.ordering';
		}

		if ($orderCol == 'access_level')
		{
			$orderCol = 'ag.title';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to get a list of items.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (JFactory::getApplication()->isSite())
		{
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++)
			{
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups))
				{
					unset($items[$x]);
				}
			}
		}

		return $items;
	}

	public function getBatchTopics($config = array('filter.published' => array(0, 1)))
	{
		$hash = md5('com_faqbookpro.' . serialize($config));

		if (!isset(static::$items[$hash]))
		{
			$config = (array) $config;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.id, a.title, a.level')
				->from('#__minitek_faqbook_topics AS a')
				->where('a.parent_id > 0');

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published = ' . (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					JArrayHelper::toInteger($config['filter.published']);
					$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
				}
			}

			$query->order('a.lft');
			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Assemble the list options.
			static::$items[$hash] = array();

			foreach ($items as &$item)
			{
				$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
				$item->title = str_repeat('- ', $repeat) . $item->title;
				static::$items[$hash][] = JHtml::_('select.option', $item->id, $item->title);
			}
		}

		return static::$items[$hash];
	}
}
