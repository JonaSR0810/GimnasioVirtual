<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

class FAQBookProLibUtilities
{
	public static function getParams($option)
	{
		$application = JFactory::getApplication();

		if ($application->isSite())
		{
		  $params = $application->getParams($option);
		}
		else
		{
		  $params = JComponentHelper::getParams($option);
		}

		return $params;
	}

	public static function getWordLimit($text, $limit, $end_char = '&#8230;')
	{
		if(JString::trim($text) == '')
			return $text;

		// always strip tags for text
		$text = strip_tags($text);
		$find = array(
			"/\r|\n/u",
			"/\t/u",
			"/\s\s+/u"
		);
		$replace = array(
			" ",
			" ",
			" "
		);
		$text = preg_replace($find, $replace, $text);

		preg_match('/\s*(?:\S*\s*){'.(int)$limit.'}/u', $text, $matches);

		if (JString::strlen($matches[0]) == JString::strlen($text))
			$end_char = '';

		return JString::rtrim($matches[0]).$end_char;

	}

	public static function getTimeSince($date)
	{
		$date = strtotime($date);
		$now = JFactory::getDate()->format("Y-m-d H:i:s");
		$now = strtotime($now);
		$since = $now - $date;

		$chunks = array(
			array(60 * 60 * 24 * 365 , JText::_('COM_FAQBOOKPRO_YEAR'), JText::_('COM_FAQBOOKPRO_YEARS')),
			array(60 * 60 * 24 * 30 , JText::_('COM_FAQBOOKPRO_MONTH'), JText::_('COM_FAQBOOKPRO_MONTHS')),
			array(60 * 60 * 24 * 7, JText::_('COM_FAQBOOKPRO_WEEK'), JText::_('COM_FAQBOOKPRO_WEEKS')),
			array(60 * 60 * 24 , JText::_('COM_FAQBOOKPRO_DAY'), JText::_('COM_FAQBOOKPRO_DAYS')),
			array(60 * 60 , JText::_('COM_FAQBOOKPRO_HOUR'), JText::_('COM_FAQBOOKPRO_HOURS')),
			array(60 , JText::_('COM_FAQBOOKPRO_MINUTE'), JText::_('COM_FAQBOOKPRO_MINUTES')),
			array(1 , JText::_('COM_FAQBOOKPRO_SECOND'), JText::_('COM_FAQBOOKPRO_SECONDS'))
		);

		for ($i = 0, $j = count($chunks); $i < $j; $i++)
		{
			$seconds = $chunks[$i][0];
			$name_1 = $chunks[$i][1];
			$name_n = $chunks[$i][2];

			if (($count = floor($since / $seconds)) != 0)
			{
				break;
			}
		}

		$print = ($count == 1) ? '1 '.$name_1 : "$count {$name_n}";

		return $print;
	}

	public static function getActions($component = '', $section = '', $id = 0)
	{
		$user = JFactory::getUser();
		$result = new JObject;

		$path = JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml';

		if ($section && $id)
		{
			$assetName = $component . '.' . $section . '.' . (int) $id;
		}
		else
		{
			$assetName = $component;
		}

		$actions = JAccess::getActionsFromFile($path, "/access/section[@name='component']/");

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	public static function getAuthorisedTopics($action)
	{
		// Brute force method: get all published topic rows for the component and check each one
		// TODO: Modify the way permissions are stored in the db to allow for faster implementation and better scaling
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('t.id AS id, a.name AS asset_name')
			->from('#__minitek_faqbook_topics AS t')
			->join('INNER', '#__assets AS a ON t.asset_id = a.id')
			->where('t.published = 1');
		$db->setQuery($query);
		$allTopics = $db->loadObjectList('id');
		$allowedTopics = array();

		foreach ($allTopics as $topic)
		{
			if (JFactory::getUser()->authorise($action, $topic->asset_name))
			{
				$allowedTopics[] = (int) $topic->id;
			}
		}

		return $allowedTopics;
	}

	public function userExists($id)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('id') . ' = ' . $db->quote($id) . '');
		$db->setQuery($query);
		$row = $db->loadObject();

		return $row;
	}
}
