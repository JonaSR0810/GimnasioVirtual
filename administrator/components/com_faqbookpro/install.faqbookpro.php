<?php

/**
 * @title        Minitek FAQ Book
 * @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
 * @license      GNU General Public License version 3 or later.
 * @author url   https://www.minitek.gr/
 * @developers   Minitek.gr
 */

defined('_JEXEC') or die;

if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

use Joomla\CMS\MVC\View\GenericDataException;

class com_faqbookproInstallerScript
{
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 */
	function preflight($type, $parent)
	{
		if (is_object($this->getOldVersion())) {
			// Get old version
			$this->old_version = $this->getOldVersion()->version;

			// Get new version
			$this->release = $parent->get('manifest')->version;

			// Abort if old version is older than 3.3.9
			if (isset($this->old_version) && $this->old_version && version_compare($this->old_version, '3.3.9', '<')) {
				Jerror::raiseWarning(null, 'Cannot install version <strong>' . $this->release . '</strong> over version <strong>' . $this->old_version . '</strong>. Please uninstall version <strong>' . $this->old_version . '</strong> first. All Minitek FAQ Book data will be lost.');
				return false;
			}

			// Run update script if old version is older than 3.9.0
			if (isset($this->old_version) && $this->old_version && version_compare($this->old_version, '3.9.0', '<')) {
				self::update390($parent);
			}

			// Run update script if old version is older than 3.9.1.1
			if (isset($this->old_version) && $this->old_version && version_compare($this->old_version, '3.9.1.1', '<')) {
				self::update3911($parent);
			}

			// Run update script if old version is older than 3.9.3
			if (isset($this->old_version) && $this->old_version && version_compare($this->old_version, '3.9.3', '<')) {
				self::update393($parent);
			}

			// Run update script if old version is older than 3.9.3.1
			if (isset($this->old_version) && $this->old_version && version_compare($this->old_version, '3.9.3.1', '<')) {
				self::update3931($parent);
			}

			// Run update script if old version is older than 3.9.5
			if (isset($this->old_version) && $this->old_version && version_compare($this->old_version, '3.9.5', '<')) {
				self::update395($parent);
			}

			// Run update script if old version is older than 3.9.6
			if (isset($this->old_version) && $this->old_version && version_compare($this->old_version, '3.9.6', '<')) {
				self::update396($parent);
			}
		}
	}

	/*
	 * $parent is the class calling this method.
	 * update runs if old version is < 3.9.6
	 */
	function update396($parent)
	{
		$db = \JFactory::getDbo();

		// Add column 'last_answer'
		$questions_columns = $db->getTableColumns('#__minitek_faqbook_questions');

		if (!isset($questions_columns['last_answer'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " ADD COLUMN `last_answer` int(10) unsigned NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 396-1: Could not update __minitek_faqbook_questions table.', 500);
				return false;
			}
		}

		// Find last answer for existing questions
		$query = $db->getQuery(true);
		$query->select('MAX(a.id) as last_answer, a.question_id as question_id');
		$query->from('#__minitek_faqbook_answers AS a');
		$query->where('a.state = 1');
		$query->group('a.question_id');
		$query->order('a.id DESC');
		$db->setQuery($query);
		$questions = $db->loadObjectList();

		if (!$questions)
			return;

		foreach ($questions as $question) {
			$fields = array(
				$db->quoteName('last_answer') . ' = ' . $db->quote($question->last_answer)
			);
			$conditions = array(
				$db->quoteName('id') . ' = ' . $db->quote($question->question_id)
			);
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__minitek_faqbook_questions'))
				->set($fields)
				->where($conditions);
			$db->setQuery($query);

			try {
				$result = $db->execute();
			} catch (\Exception $e) {
				return false;
			}
		}
	}

	/*
	 * $parent is the class calling this method.
	 * update runs if old version is < 3.9.5
	 */
	function update395($parent)
	{
		$db = \JFactory::getDbo();
		$answers_columns = $db->getTableColumns('#__minitek_faqbook_answers');

		if (!isset($answers_columns['seen'])) {
			// Add column 'seen'
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_answers` ";
			$query .= " ADD COLUMN `seen` tinyint(3) unsigned NOT NULL DEFAULT '1' ";
			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 395-1: Could not update __minitek_faqbook_answers table.', 500);
				return false;
			}
		}
	}

	/*
	 * $parent is the class calling this method.
	 * update runs if old version is < 3.9.3.1
	 */
	function update3931($parent)
	{
		$db = \JFactory::getDbo();

		// Update __minitek_faqbook_questions
		$questions_columns = $db->getTableColumns('#__minitek_faqbook_questions');

		if (!isset($questions_columns['answers'])) {
			// Add column 'answers'
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " ADD COLUMN `answers` int(10) unsigned NOT NULL DEFAULT '0' ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 3931-1: Could not update _minitek_faqbook_questions table.', 500);
				return false;
			}

			// Get all questions
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__minitek_faqbook_questions');
			$db->setQuery($query);
			$questions = $db->loadObjectList();

			if ($questions) {
				// Count answers for each question
				foreach ($questions as $question) {
					$answers_count = self::getAnswersCount($question->id);

					// Update answers count in table
					$query = $db->getQuery(true);
					$fields = array(
						$db->quoteName('answers') . ' = ' . $db->quote($answers_count)
					);
					$conditions = array(
						$db->quoteName('id') . ' = ' . $db->quote($question->id)
					);
					$query->update($db->quoteName('#__minitek_faqbook_questions'))
						->set($fields)
						->where($conditions);

					$db->setQuery($query);
					$result = $db->execute();

					if (!$result) {
						throw new GenericDataException('Error 3931-2: Could not update __minitek_faqbook_questions table.', 500);
						return false;
					}
				}
			}
		}
	}

	public static function getAnswersCount($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(*)');
		$query->from('#__minitek_faqbook_answers');

		// Filter by question
		$query->where('question_id = ' . $db->quote($id));

		$db->setQuery($query);
		$count = $db->loadResult();

		return $count;
	}

	/*
	 * $parent is the class calling this method.
	 * update runs if old version is < 3.9.3
	 */
	function update393($parent)
	{
		// Create table __minitek_faqbook_attachments
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query = " CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_attachments` (
			`id` int(12) unsigned NOT NULL AUTO_INCREMENT,
		  `parent_id` int(10) unsigned NOT NULL,
		  `parent_type` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		  `name` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		  `encoded_name` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'application/octet-stream',
		  `state` tinyint(3) NOT NULL DEFAULT '0',
		  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `created_by` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 393-1: Could not create table __minitek_faqbook_attachments. If the problem persists the update cannot be completed.', 500);
			return false;
		}
	}

	/*
	 * $parent is the class calling this method.
	 * update runs if old version is < 3.9.1.1
	 */
	function update3911($parent)
	{
		// Set default language for questions
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('language') . ' = ' . $db->quote('*')
		);
		$conditions = array(
			$db->quoteName('language') . ' = ' . $db->quote('')
		);
		$query->update($db->quoteName('#__minitek_faqbook_questions'))
			->set($fields)
			->where($conditions);

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 3911-1: Could not update __minitek_faqbook_questions table.', 500);
			return false;
		}
	}

	/*
	 * $parent is the class calling this method.
	 * update runs if old version is < 3.9.0
	 */
	function update390($parent)
	{
		// Delete file admin/assets/images/logo-white.png
		$admin_assets_images_logowhite_png = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_faqbookpro' . DS . 'assets' . DS . 'images' . DS . 'logo-white.png';
		if (file_exists($admin_assets_images_logowhite_png)) {
			\JFile::delete($admin_assets_images_logowhite_png);
		};

		// Delete file admin/controllers/votes.php
		$admin_controllers_votes_php = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_faqbookpro' . DS . 'controllers' . DS . 'votes.php';
		if (file_exists($admin_controllers_votes_php)) {
			\JFile::delete($admin_controllers_votes_php);
		};

		// Delete file admin/models/votes.php
		$admin_models_votes_php = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_faqbookpro' . DS . 'models' . DS . 'votes.php';
		if (file_exists($admin_models_votes_php)) {
			\JFile::delete($admin_models_votes_php);
		};

		// Delete file admin/models/fields/fbpmanagerlist.php
		$admin_models_fields_fbpmanagerlist_php = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_faqbookpro' . DS . 'models' . DS . 'fields' . DS . 'fbpmanagerlist.php';
		if (file_exists($admin_models_fields_fbpmanagerlist_php)) {
			\JFile::delete($admin_models_fields_fbpmanagerlist_php);
		};

		// Delete file admin/models/fields/fbpsections.php
		$admin_models_fields_fbpsections_php = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_faqbookpro' . DS . 'models' . DS . 'fields' . DS . 'fbpsections.php';
		if (file_exists($admin_models_fields_fbpsections_php)) {
			\JFile::delete($admin_models_fields_fbpsections_php);
		};

		// Delete folder admin/sql/updates
		$admin_sql_updates = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_faqbookpro' . DS . 'sql' . DS . 'updates';
		if (\JFolder::exists($admin_sql_updates)) {
			\JFolder::delete($admin_sql_updates);
		};

		// Delete folder admin/views/about
		$admin_views_about = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_faqbookpro' . DS . 'views' . DS . 'about';
		if (\JFolder::exists($admin_views_about)) {
			\JFolder::delete($admin_views_about);
		};

		// Delete file admin/views/questions/tmpl/modal.php
		$admin_views_questions_tmpl_modal_php = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_faqbookpro' . DS . 'views' . DS . 'questions' . DS . 'tmpl' . DS . 'modal.php';
		if (file_exists($admin_views_questions_tmpl_modal_php)) {
			\JFile::delete($admin_views_questions_tmpl_modal_php);
		};

		// Delete folder admin/views/votes
		$admin_views_votes = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_faqbookpro' . DS . 'views' . DS . 'votes';
		if (\JFolder::exists($admin_views_votes)) {
			\JFolder::delete($admin_views_votes);
		};

		// Delete file site/assets/css/faqbook.css
		$site_assets_css_faqbook_css = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'assets' . DS . 'css' . DS . 'faqbook.css';
		if (file_exists($site_assets_css_faqbook_css)) {
			\JFile::delete($site_assets_css_faqbook_css);
		};

		// Delete file site/controllers/myquestions.php
		$site_controllers_myquestions_php = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'controllers' . DS . 'myquestions.php';
		if (file_exists($site_controllers_myquestions_php)) {
			\JFile::delete($site_controllers_myquestions_php);
		};

		// Delete file site/assets/images/thumbs_down_icon.png
		$site_assets_images_thumbs_down_icon_png = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'assets' . DS . 'images' . DS . 'thumbs_down_icon.png';
		if (file_exists($site_assets_images_thumbs_down_icon_png)) {
			\JFile::delete($site_assets_images_thumbs_down_icon_png);
		};

		// Delete file site/assets/images/thumbs_up_icon.png
		$site_assets_images_thumbs_up_icon_png = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'assets' . DS . 'images' . DS . 'thumbs_up_icon.png';
		if (file_exists($site_assets_images_thumbs_up_icon_png)) {
			\JFile::delete($site_assets_images_thumbs_up_icon_png);
		};

		// Delete file site/assets/images/triangle-b.png
		$site_assets_images_triangle_b_png = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'assets' . DS . 'images' . DS . 'triangle-b.png';
		if (file_exists($site_assets_images_triangle_b_png)) {
			\JFile::delete($site_assets_images_triangle_b_png);
		};

		// Delete file site/assets/images/triangle-r.png
		$site_assets_images_triangle_r_png = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'assets' . DS . 'images' . DS . 'triangle-r.png';
		if (file_exists($site_assets_images_triangle_r_png)) {
			\JFile::delete($site_assets_images_triangle_r_png);
		};

		// Delete file site/assets/images/loaderbig.gif
		$site_assets_images_loaderbig_gif = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'assets' . DS . 'images' . DS . 'loaderbig.gif';
		if (file_exists($site_assets_images_loaderbig_gif)) {
			\JFile::delete($site_assets_images_loaderbig_gif);
		};

		// Delete file site/assets/js/faqbook.js
		$site_assets_js_faqbook_js = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'assets' . DS . 'js' . DS . 'faqbook.js';
		if (file_exists($site_assets_js_faqbook_js)) {
			\JFile::delete($site_assets_js_faqbook_js);
		};

		// Delete folder site/language/ru-RU
		$site_language_ru_RU = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'language' . DS . 'ru-RU';
		if (\JFolder::exists($site_language_ru_RU)) {
			\JFolder::delete($site_language_ru_RU);
		};

		// Delete file site/layouts/fbp_question.php
		$site_layouts_fbp_question_php = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'layouts' . DS . 'fbp_question.php';
		if (file_exists($site_layouts_fbp_question_php)) {
			\JFile::delete($site_layouts_fbp_question_php);
		};

		// Delete file site/layouts/fbp_questions.php
		$site_layouts_fbp_questions_php = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'layouts' . DS . 'fbp_questions.php';
		if (file_exists($site_layouts_fbp_questions_php)) {
			\JFile::delete($site_layouts_fbp_questions_php);
		};

		// Delete file site/layouts/fbp_topic.php
		$site_layouts_fbp_topic_php = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'layouts' . DS . 'fbp_topic.php';
		if (file_exists($site_layouts_fbp_topic_php)) {
			\JFile::delete($site_layouts_fbp_topic_php);
		};

		// Delete file site/layouts/searchtools.php
		$site_layouts_searchtools_php = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'layouts' . DS . 'searchtools.php';
		if (file_exists($site_layouts_searchtools_php)) {
			\JFile::delete($site_layouts_searchtools_php);
		};

		// Delete folder site/libraries/utilities
		$site_libraries_utilities = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'libraries' . DS . 'utilities';
		if (\JFolder::exists($site_libraries_utilities)) {
			\JFolder::delete($site_libraries_utilities);
		};

		// Delete folder site/models/fields
		$site_models_fields = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'models' . DS . 'fields';
		if (\JFolder::exists($site_models_fields)) {
			\JFolder::delete($site_models_fields);
		};

		// Delete file site/models/myquestions.php
		$site_models_myquestions_php = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'models' . DS . 'myquestions.php';
		if (file_exists($site_models_myquestions_php)) {
			\JFile::delete($site_models_myquestions_php);
		};

		// Delete folder site/views/myquestions
		$site_views_myquestions = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'views' . DS . 'myquestions';
		if (\JFolder::exists($site_views_myquestions)) {
			\JFolder::delete($site_views_myquestions);
		};

		// Delete file site/views/question/tmpl/default.xml
		$site_views_question_tmpl_default_xml = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'views' . DS . 'question' . DS . 'tmpl' . DS . 'default.xml';
		if (file_exists($site_views_question_tmpl_default_xml)) {
			\JFile::delete($site_views_question_tmpl_default_xml);
		};

		// Delete file site/views/sections/tmpl/default_content.php
		$site_views_sections_tmpl_default_content_php = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'views' . DS . 'sections' . DS . 'tmpl' . DS . 'default_content.php';
		if (file_exists($site_views_sections_tmpl_default_content_php)) {
			\JFile::delete($site_views_sections_tmpl_default_content_php);
		};

		// Delete file site/views/topic/tmpl/default_topic_questions.php
		$site_views_topic_tmpl_default_topic_questions_php = JPATH_SITE . DS . 'components' . DS . 'com_faqbookpro' . DS . 'views' . DS . 'topic' . DS . 'tmpl' . DS . 'default_topic_questions.php';
		if (file_exists($site_views_topic_tmpl_default_topic_questions_php)) {
			\JFile::delete($site_views_topic_tmpl_default_topic_questions_php);
		};

		// Update tables
		$db = \JFactory::getDbo();

		// Update __minitek_faqbook_questions
		$questions_columns = $db->getTableColumns('#__minitek_faqbook_questions');

		// rename 'introtext' to 'content'
		if (isset($questions_columns['introtext'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " CHANGE `introtext` `content` mediumtext ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-1: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// rename 'answered' to 'resolved'
		if (isset($questions_columns['answered'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " CHANGE `answered` `resolved` varchar(255) NOT NULL DEFAULT '0' ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-2: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// add 'locked'
		if (!isset($questions_columns['locked'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " ADD COLUMN `locked` tinyint(3) NOT NULL DEFAULT '0' ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-3: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// add 'private'
		if (!isset($questions_columns['private'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " ADD COLUMN `private` tinyint(3) NOT NULL DEFAULT '0' ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-4: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// add 'pinned'
		if (!isset($questions_columns['pinned'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " ADD COLUMN `pinned` tinyint(3) NOT NULL DEFAULT '0' ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-5: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// add 'created_by_name'
		if (!isset($questions_columns['created_by_name'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " ADD COLUMN `created_by_name` varchar(128) NOT NULL DEFAULT '' ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-6: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// add 'assigned_to'
		if (!isset($questions_columns['assigned_to'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " ADD COLUMN `assigned_to` int(10) unsigned NOT NULL DEFAULT '0' ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-7: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// add 'customfields'
		if (!isset($questions_columns['customfields'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " ADD COLUMN `customfields` mediumtext NOT NULL ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-8: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// delete 'fulltext'
		if (isset($questions_columns['fulltext'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " DROP COLUMN `fulltext` ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-9: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// delete 'urls'
		if (isset($questions_columns['urls'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " DROP COLUMN `urls` ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-10: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// delete 'version'
		if (isset($questions_columns['version'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " DROP COLUMN `version` ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-11: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// delete 'xreference'
		if (isset($questions_columns['xreference'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_questions` ";
			$query .= " DROP COLUMN `xreference` ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-12: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// Update __minitek_faqbook_topics
		$topics_columns = $db->getTableColumns('#__minitek_faqbook_topics');

		// add 'qvisibility'
		if (!isset($topics_columns['qvisibility'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_topics` ";
			$query .= " ADD COLUMN `qvisibility` tinyint(1) NOT NULL DEFAULT '0' ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-13: Could not update __minitek_faqbook_topics table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// Update __minitek_faqbook_votes
		$votes_columns = $db->getTableColumns('#__minitek_faqbook_votes');

		// rename 'question_id' to 'target_id'
		if (isset($votes_columns['question_id'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_votes` ";
			$query .= " CHANGE `question_id` `target_id` int(11) unsigned NOT NULL ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-14: Could not update __minitek_faqbook_votes table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// add 'target_type'
		if (!isset($votes_columns['target_type'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_votes` ";
			$query .= " ADD COLUMN `target_type` varchar(50) NOT NULL DEFAULT 'question' ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-15: Could not update __minitek_faqbook_votes table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// update 'user_ip'
		if (isset($votes_columns['user_ip'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_votes` ";
			$query .= " CHANGE `user_ip` `user_ip` varchar(128) NOT NULL ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-16: Could not update __minitek_faqbook_votes table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// delete 'reason'
		if (isset($votes_columns['reason'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_votes` ";
			$query .= " DROP COLUMN `reason` ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-17: Could not update _minitek_faqbook_questions table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// Update __minitek_faqbook_email_templates
		$templates_columns = $db->getTableColumns('#__minitek_faqbook_email_templates');

		// delete 'description'
		if (isset($templates_columns['description'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_email_templates` ";
			$query .= " DROP COLUMN `description` ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-18: Could not update __minitek_faqbook_email_templates table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// delete 'created'
		if (isset($templates_columns['created'])) {
			$query = $db->getQuery(true);
			$query = " ALTER TABLE `#__minitek_faqbook_email_templates` ";
			$query .= " DROP COLUMN `created` ";

			$db->setQuery($query);
			$result = $db->execute();

			if (!$result) {
				throw new GenericDataException('Error 390-19: Could not update __minitek_faqbook_email_templates table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
				return false;
			}
		}

		// empty table
		$query = $db->getQuery(true);
		$query = " TRUNCATE TABLE `#__minitek_faqbook_email_templates` ";

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 390-20: Could not update __minitek_faqbook_email_templates table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
			return false;
		}

		// add default templates

		// new-question-admin
		$query = $db->getQuery(true);
		$columns = array('template_key', 'title', 'subject', 'content', 'state', 'language');
		$values = array(
			$db->quote('new-question-admin'),
			$db->quote('New Question - Moderator notification'),
			$db->quote('New Question posted: [QUESTION_TITLE]'),
			$db->quote('<div style="background-color:#f0f0f0;padding:10px;text-align: left;"><div style="background-color:#f9f9f9;padding:10px 15px">Hello [RECIPIENT_NAME],<br><br>A new question has been posted in the topic <a target="_blank" style="font-weight:bold" href="[TOPIC_URL]">[TOPIC_TITLE]</a> by [AUTHOR_NAME].<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a><div>[QUESTION_CONTENT]</div></div><br></div></div>'),
			$db->quote(1),
			$db->quote('*')
		);
		$query
			->insert($db->quoteName('#__minitek_faqbook_email_templates'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 390-21: Could not update __minitek_faqbook_email_templates table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
			return false;
		}

		// new-answer-author
		$query = $db->getQuery(true);
		$columns = array('template_key', 'title', 'subject', 'content', 'state', 'language');
		$values = array(
			$db->quote('new-answer-author'),
			$db->quote('New Answer - Author notification'),
			$db->quote('Answer posted: [QUESTION_TITLE]'),
			$db->quote('<div style="background-color:#f0f0f0;padding:10px;text-align: left;"><div style="background-color:#f9f9f9;padding:10px 15px">Hello [RECIPIENT_NAME],<br><br>A new answer has been posted to your question <a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a>.<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><div>[ANSWER_CONTENT]</div></div><br></div></div>'),
			$db->quote(1),
			$db->quote('*')
		);
		$query
			->insert($db->quoteName('#__minitek_faqbook_email_templates'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 390-22: Could not update __minitek_faqbook_email_templates table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
			return false;
		}

		// new-answer-admin
		$query = $db->getQuery(true);
		$columns = array('template_key', 'title', 'subject', 'content', 'state', 'language');
		$values = array(
			$db->quote('new-answer-admin'),
			$db->quote('New Answer - Moderator notification'),
			$db->quote('Answer posted: [QUESTION_TITLE]'),
			$db->quote('<div style="background-color:#f0f0f0;padding:10px;text-align: left;"><div style="background-color:#f9f9f9;padding:10px 15px">Hello [RECIPIENT_NAME],<br><br>A new answer has been posted to the question <a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a>.<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><div>[ANSWER_CONTENT]</div></div><br></div></div>'),
			$db->quote(1),
			$db->quote('*')
		);
		$query
			->insert($db->quoteName('#__minitek_faqbook_email_templates'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 390-23: Could not update __minitek_faqbook_email_templates table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
			return false;
		}

		// selected-answer-author
		$query = $db->getQuery(true);
		$columns = array('template_key', 'title', 'subject', 'content', 'state', 'language');
		$values = array(
			$db->quote('selected-answer-author'),
			$db->quote('Selected Answer - Author notification'),
			$db->quote('Your answer was selected: [QUESTION_TITLE]'),
			$db->quote('<div style="background-color:#f0f0f0;padding:10px;text-align: left;"><div style="background-color:#f9f9f9;padding:10px 15px">Hello [RECIPIENT_NAME],<br><br>Your answer to the question <a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a> was selected as the best answer.<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><div>[ANSWER_CONTENT]</div></div><br></div></div>'),
			$db->quote(1),
			$db->quote('*')
		);
		$query
			->insert($db->quoteName('#__minitek_faqbook_email_templates'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 390-24: Could not update __minitek_faqbook_email_templates table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
			return false;
		}

		// new-question-hash
		$query = $db->getQuery(true);
		$columns = array('template_key', 'title', 'subject', 'content', 'state', 'language');
		$values = array(
			$db->quote('new-question-hash'),
			$db->quote('New Question Hash - Author notification'),
			$db->quote('New Question posted: [QUESTION_TITLE]'),
			$db->quote('<div style="background-color:#f0f0f0;padding:10px;text-align: left;"><div style="background-color:#f9f9f9;padding:10px 15px">Hello [RECIPIENT_NAME],<br><br>Your question has been posted in the topic <a target="_blank" style="font-weight:bold" href="[TOPIC_URL]">[TOPIC_TITLE]</a>.<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a><div>[QUESTION_CONTENT]</div><br><br><div>Question hash:</div><div>[QUESTION_HASH]</div></div><br></div></div>'),
			$db->quote(1),
			$db->quote('*')
		);
		$query
			->insert($db->quoteName('#__minitek_faqbook_email_templates'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 390-25: Could not update __minitek_faqbook_email_templates table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
			return false;
		}

		// assign-question-admin
		$query = $db->getQuery(true);
		$columns = array('template_key', 'title', 'subject', 'content', 'state', 'language');
		$values = array(
			$db->quote('assign-question-admin'),
			$db->quote('New assigned Question - Moderator notification'),
			$db->quote('New assigned question: [QUESTION_TITLE]'),
			$db->quote('<div style="background-color:#f0f0f0;padding:10px;text-align: left;"><div style="background-color:#f9f9f9;padding:10px 15px">Hello [RECIPIENT_NAME],<br><br>A new question has been assigned to you.<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a><div>[QUESTION_CONTENT]</div></div><br></div></div>'),
			$db->quote(1),
			$db->quote('*')
		);
		$query
			->insert($db->quoteName('#__minitek_faqbook_email_templates'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 390-26: Could not update __minitek_faqbook_email_templates table. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
			return false;
		}

		// Create table __minitek_faqbook_answers
		$query = $db->getQuery(true);
		$query = " CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_answers` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
		  `question_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `content` mediumtext NOT NULL,
		  `selected` tinyint(3) NOT NULL DEFAULT '0',
		  `state` tinyint(3) NOT NULL DEFAULT '0',
		  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
		  `created_by_name` varchar(128) NOT NULL DEFAULT '',
		  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
		  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
		  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
		";

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 390-27: Could not create table __minitek_faqbook_answers. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
			return false;
		}

		// Create table __minitek_faqbook_customfields_topics
		$query = $db->getQuery(true);
		$query = " CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_customfields_topics` (
			`customfield_id` int(11) NOT NULL,
		  `topicid` int(11) NOT NULL,
		  PRIMARY KEY (`customfield_id`,`topicid`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 390-28: Could not create table __minitek_faqbook_customfields_topics. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
			return false;
		}

		// Create table __minitek_faqbook_customfields
		$query = $db->getQuery(true);
		$query = " CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_customfields` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `asset_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
		  `title` varchar(255) NOT NULL DEFAULT '',
		  `alias` varchar(255) NOT NULL DEFAULT '',
		  `description` mediumtext NOT NULL,
		  `show` varchar(100) NOT NULL DEFAULT 'all',
		  `type` varchar(100) NOT NULL DEFAULT 'text',
		  `options` mediumtext,
		  `default` varchar(255) DEFAULT '',
		  `required` tinyint(3) NOT NULL DEFAULT '0',
		  `valid_label` varchar(255) DEFAULT '',
		  `invalid_label` varchar(255) DEFAULT '',
		  `params` mediumtext,
		  `state` tinyint(3) NOT NULL DEFAULT '1',
		  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
		  `created_by` bigint(20) NOT NULL DEFAULT '0',
		  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `modified_by` bigint(20) NOT NULL DEFAULT '0',
		  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
		  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 390-29: Could not create table __minitek_faqbook_customfields. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
			return false;
		}

		// Create table __minitek_faqbook_customstates
		$query = $db->getQuery(true);
		$query = " CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_customstates` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `asset_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
		  `title` varchar(255) NOT NULL DEFAULT '',
		  `alias` varchar(255) NOT NULL DEFAULT '',
		  `color` varchar(10) DEFAULT '#5bc0de',
		  `params` mediumtext,
		  `state` tinyint(3) NOT NULL DEFAULT '1',
		  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
		  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";

		$db->setQuery($query);
		$result = $db->execute();

		if (!$result) {
			throw new GenericDataException('Error 390-30: Could not create table __minitek_faqbook_customstates. If the problem persists the update cannot be completed. In this case, uninstall the component and reinstall the new version. All FAQ Book data will be lost.', 500);
			return false;
		}
	}

	/*
	 * $parent is the class calling this method.
	 * install runs after the database scripts are executed.
	 * If the extension is new, the install method is run.
	 * If install returns false, Joomla will abort the install and undo everything already done.
	 */
	function install($parent)
	{
	}

	/*
	 * $parent is the class calling this method.
	 * update runs after the database scripts are executed.
	 * If the extension exists, then the update method is run.
	 * If this returns false, Joomla will abort the update and undo everything already done.
	 */
	function update($parent)
	{
	}

	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * postflight is run after the extension is registered in the database.
	 */
	function postflight($type, $parent)
	{
	}

	/*
	 * $parent is the class calling this method
	 * uninstall runs before any other action is taken (file removal or database processing).
	 */
	function uninstall($parent)
	{
	}

	private static function getOldVersion()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT manifest_cache FROM ' . $db->quoteName('#__extensions');
		$query .= ' WHERE ' . $db->quoteName('element') . ' = ' . $db->quote('com_faqbookpro') . ' ';
		$db->setQuery($query);
		$row = $db->loadObject();

		if ($row) {
			$manifest_cache = json_decode($row->manifest_cache, false);
			return $manifest_cache;
		} else {
			return false;
		}
	}
}
