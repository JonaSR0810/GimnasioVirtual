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
$utilities = new FAQBookProLibUtilities;
$params = $utilities->getParams('com_faqbookpro');
$user = JFactory::getUser();
$app = JFactory::getApplication();
$view = $app->input->get('view', '');
$tab = $app->input->get('tab', '');

if ($sectionId)
{
  $sectionModel = JModelLegacy::getInstance('Section', 'FAQBookProModel');
  $section = $sectionModel->getItem($sectionId);
  $sectionTitle = $section->title;
  $sectionParams = json_decode($section->attribs, false);
  $menu = $app->getMenu();
  $activeMenu = $menu->getActive();
  $topnav_sections = isset($sectionParams->topnav_sections) ? $sectionParams->topnav_sections : false;
  $home_itemid = isset($sectionParams->topnav_root) ? $sectionParams->topnav_root : $activeMenu->id;
  $home_menuitem = $menu->getItem($home_itemid);
  $home_title = $home_menuitem->title;
}

if ($view == 'section' && $tab != 'topics')
{
  $class = 'NavTopUL_lastChild';
}
else
{
  $class = '';
}

?><div class="fbTopNavigation_core_outer">
  <div class="fbTopNavigation_core">
    <div class="fbTopNavigation_wrap">
      <ul class="fbTopNavigation_root fb-hidden-phone"><?php

        // Sections link
        if (($view != 'profile' || $sectionId) && $topnav_sections)
        {
          ?><li class="NavTopUL_home"><?php
            ?><a class="NavTopUL_link" href="<?php echo JRoute::_(FAQBookProHelperRoute::getSectionsRoute($home_itemid)); ?>">
              <i class="fa fa-home NavTopUL_homeIcon"></i>&nbsp;&nbsp;<?php
              echo $home_title;
            ?></a></li><?php
        }

        // Section link
        if ($sectionId)
        {
          ?><li id="top_liid_home" class="NavTopUL_item NavTopUL_section NavTopUL_firstChild <?php echo $class; ?>"><?php
            ?><a class="NavTopUL_link" href="<?php echo JRoute::_(FAQBookProHelperRoute::getSectionRoute($sectionId)); ?>" onclick="return false;"><?php
              if ($topnav_sections)
              {
                ?><i class="fa fa-caret-right NavTopUL_homeIcon"></i>&nbsp;&nbsp;<?php
              }
              else
              {
                ?><i class="fa fa-home NavTopUL_homeIcon"></i>&nbsp;&nbsp;<?php
              }
              echo $sectionTitle;
            ?></a></li><?php
        }

        // Browse Topics
        if ($view == 'section' && $tab == 'topics')
        {
          ?><li class="NavTopUL_item NavTopUL_topics NavTopUL_lastChild"><?php
            ?><a class="NavTopUL_link" href="#" onclick="return false;"><?php
              ?><i class="fa fa-caret-right"></i>&nbsp;&nbsp;<?php
              echo JText::_('COM_FAQBOOKPRO_BROWSE_TOPICS');
            ?></a></li><?php
        }

      ?></ul>
      <span class="NavTopUL_loading"></span>
    </div>

    <div class="NavTopUL_buttons"><?php

      // Menu icon
      if ((isset($sectionParams->leftnav) && $sectionParams->leftnav) || !$sectionId)
      {
        ?><div class="show_menu"><a href="#" onclick="return false;" class="btn btn-default"><i class="fa fa-bars"></i></a></div><?php
      }

    ?></div>
  </div>
  <div class="clearfix"> </div>
</div>
