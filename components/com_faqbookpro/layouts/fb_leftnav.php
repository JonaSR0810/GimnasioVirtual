<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

$sectionId = $displayData['sectionId'];
$visible = $displayData['visible'];
$utilities = new FAQBookProLibUtilities;
$params = $utilities->getParams('com_faqbookpro');
$sectionModel = JModelLegacy::getInstance('Section', 'FAQBookProModel');
$section = $sectionModel->getItem($sectionId);
$sectionParams = json_decode($section->attribs, false);
$navigation = new FAQBookProLibNavigation;

if (!isset($sectionParams->browse_topics))
{
  $sectionParams->browse_topics = false;
}

$user = JFactory::getUser();
$userid = $user->id;
$items = $sectionModel->getSectionTopics($sectionId);
$app = JFactory::getApplication();

if ($items)
{
  foreach ($items as $item)
  {
    $topicsTree = $navigation->getTopicsTree($item);
    $topics_tree[] = $topicsTree;
  }
}

// Is the left nav minimized?
$is_minimized = $app->getUserState( 'com_faqbookpro.minimized_leftnav', false );
$fb_minimized = '';
$minimized_icon = '<a class="NavLeftUL_anchor NavLeftUL_toggle" href="#"><i class="fa fa-angle-double-left"></i></a>';
$section_title_html = '';

if ((!$is_minimized && $sectionParams->leftnav == '2') || $is_minimized == 'on')
{
  $fb_minimized = 'fb-minimized';
  $minimized_icon = '<a class="NavLeftUL_anchor NavLeftUL_toggle" href="#"><i class="fa fa-angle-double-right"></i></a>';
}

$visible_class = '';
if (!$visible)
{
  $visible_class = 'leftnav-hidden';
}

?><div class="fbLeftNavigation_core fb-hidden <?php echo $fb_minimized; ?> <?php echo $visible_class; ?>"><?php

  // Toggle left navigation
  ?><div class="fbLeftNavigation_toggle">
    <ul class="NavLeftUL_parent">
      <li class="NavLeftUL_item"><?php
        echo $minimized_icon;
      ?></li>
    </ul>
  </div><?php

  // Module position fb-nav-top
  jimport('joomla.application.module.helper');
  $modules_top = JModuleHelper::getModules('fb-nav-top');

  if (count($modules_top))
  {
    ?><div class="fbLeftNavigation_top"><?php
      foreach ($modules_top as $module_top)
      {
        echo JModuleHelper::renderModule($module_top);
      }
    ?></div><?php
  }

  ?><div class="fbLeftNavigation_topics">
    <ul class="NavLeftUL_parent"><?php

      // Browse Topics
      if ($sectionParams->browse_topics && isset($topics_tree) && $topics_tree)
      {
        $browse_topics_class = '';
        if ($app->input->get('view', '') == 'section' && $app->input->get('tab', '') == 'topics')
        {
          $browse_topics_class = 'li_selected';
        }

        ?><li class="NavLeftUL_item NavLeftUL_tab <?php echo $browse_topics_class; ?>" data-tab="topics" data-text="<?php echo JText::_('COM_FAQBOOKPRO_BROWSE_TOPICS'); ?>">
          <a href="<?php echo JRoute::_(FAQBookProHelperRoute::getSectionRoute($sectionId, 'topics')); ?>" id="NavLeftUL_browseTopics" class="NavLeftUL_anchor">
            <i class="fa fa-folder-o"></i><?php
            ?><span class="NavLeftUL_text"><?php echo JText::_('COM_FAQBOOKPRO_BROWSE_TOPICS'); ?></span>
            <span class="NavLeftUL_endpointIcon"></span>
          </a>
        </li><?php
      }

    ?></ul>

    <div class="fbLeftNavigation_wrap">
      <ul id="NavLeftUL" class="NavLeftUL_parent level0"><?php

        // Topics
        if (isset($topics_tree) && $topics_tree)
        {
          foreach ($topics_tree as $topic_tree)
          {
            echo $topic_tree;
          }
        }

      ?></ul>
    </div>
  </div><?php

  // Module position fb-nav-bottom
  $modules_bottom = JModuleHelper::getModules('fb-nav-bottom');

  if (count($modules_bottom))
  {
    ?><div class="fbLeftNavigation_bottom"><?php
      foreach ($modules_bottom as $module_bottom)
      {
        echo JModuleHelper::renderModule($module_bottom);
      }
    ?></div><?php
  }

?></div>
