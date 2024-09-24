<?php
/**
* @title        Minitek FAQ Book
* @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class FAQBookProHelper extends JHelperContent
{
	public static $extension = 'com_faqbookpro';

	/**
	 * Configure the Sidebar menu.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_FAQBOOKPRO_DASHBOARD'),
			'index.php?option=com_faqbookpro',
			$vName == 'dashboard'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_FAQBOOKPRO_SECTIONS'),
			'index.php?option=com_faqbookpro&view=sections',
			$vName == 'sections'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_FAQBOOKPRO_TOPICS'),
			'index.php?option=com_faqbookpro&view=topics',
			$vName == 'topics'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_FAQBOOKPRO_QUESTIONS'),
			'index.php?option=com_faqbookpro&view=questions',
			$vName == 'questions'
		);
	}

	/**
	* Gets a list of the actions that can be performed.
	*
	* @param   string   $component  The component name.
	* @param   string   $section    The access section name.
	* @param   integer  $id         The item ID.
	*
	* @return  JObject
	*
	* @since   3.2
	*/
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

	public function latestVersion()
	{
		$params = JComponentHelper::getParams('com_faqbookpro');
		$version = 0;

		$xml_file = @file_get_contents('https://update.minitek.gr/joomla-extensions/minitek_faqbook.xml');

		if ($xml_file)
		{
			$updates = new \SimpleXMLElement($xml_file);

			foreach ($updates as $key => $update)
			{
				$platform = (array)$update->targetplatform->attributes()->version;

				if ($platform[0] == '3.*')
				{
					$version = (string)$update->version;
					break;
				}
			}
		}

		return $version;
	}

	public function localVersion()
	{
		$xml = JFactory::getXML(JPATH_ADMINISTRATOR .'/components/com_faqbookpro/faqbookpro.xml');
		$version = (string)$xml->version;

		return $version;
	}

	/**
	 * Get update message.
	 *
	 * @return  Version number
	 *
	 * @since   3.9.4
	 */
	public static function updateMessage()
	{
		$params = JComponentHelper::getParams('com_faqbookpro');
		$message = 0;

		$xml_file = @file_get_contents('https://update.minitek.gr/joomla-extensions/minitek_faqbook.xml');

		if ($xml_file)
		{
			$updates = new \SimpleXMLElement($xml_file);

			foreach ($updates as $key => $update)
			{
				$platform = (array)$update->targetplatform->attributes()->version;

				if ($platform[0] == '3.*')
				{
					$message = (string)$update->message;

					break;
				}
			}
		}

		return $message;
	}

	/**
	 * Get update message version.
	 *
	 * @return  Version number
	 *
	 * @since   3.9.4
	 */
	public static function updateMessageVersion()
	{
		$params = JComponentHelper::getParams('com_faqbookpro');
		$version = 0;

		$xml_file = @file_get_contents('https://update.minitek.gr/joomla-extensions/minitek_faqbook.xml');

		if ($xml_file)
		{
			$updates = new \SimpleXMLElement($xml_file);

			foreach ($updates as $key => $update)
			{
				$platform = (array)$update->targetplatform->attributes()->version;

				if ($platform[0] == '3.*')
				{
					$version = (string)$update->showmessage;
					
					break;
				}
			}
		}

		return $version;
	}
}
