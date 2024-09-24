<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

?><div id="fbExtended" class="fbSectionExtended clearfix"><?php
  if ($this->params->get('sections_top_navigation', true))
  {
    ?><div class="fbTopNavigation_core_outer">
  		<div class="fbTopNavigation_core">
  			<div class="fbTopNavigation_wrap">
  				<ul class="fbTopNavigation_root">
  					<li class="NavTopUL_home">
  						<a href="<?php echo JRoute::_(FAQBookProHelperRoute::getSectionsRoute($this->home_itemid)); ?>" class="NavTopUL_link">
  							<i class="fa fa-home NavTopUL_homeIcon"></i>&nbsp;&nbsp;<?php echo $this->home_title; ?>
  						</a>
  					</li>
  				</ul>
  			</div><?php

  			if ($this->params->get('profile_link', false) && JFactory::getUser()->id)
        {
  				?><div class="NavTopUL_buttons">
  					<a href="<?php echo JRoute::_(FAQBookProHelperRoute::getFullProfileRoute(JFactory::getUser()->id)); ?>" class="fbTopNavigation_profile btn btn-default">
  						<i class="fa fa-user"></i>&nbsp;&nbsp;<?php echo JText::_('COM_FAQBOOKPRO_VIEW_PROFILE'); ?>
  					</a>
  				</div><?php
  			}
  		?></div>
  		<div class="clearfix"> </div>
  	</div><?php
  }

	?><div class="fbContent_core">
		<div class="fbContent_root"><?php
			if ($this->params->get('sections_layout', 'columns') == 'columns')
      {
				echo $this->loadTemplate('columns');
			}
			else
			{
				echo $this->loadTemplate('forum');
			}
		?></div>
	</div>
</div>
