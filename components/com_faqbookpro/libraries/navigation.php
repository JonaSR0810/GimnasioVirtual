<?php
/**
* @title        Minitek FAQ Book
* @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class FAQBookProLibNavigation
{
	public static function getTopicsTree($item, $level = 1)
	{
		$topicModel = JModelLegacy::getInstance('Topic', 'FAQBookProModel');
		$topicParams = json_decode($item->params, false);
  		$output = '';
		$subitems = $topicModel->getTopicChildren($item->id);

		if (count($subitems))
		{
			$depth = self::getTopicDepth($item, $levels = 0);

			if ($depth === 0)
			{
		  		$item_class = 'NavLeftUL_endpoint';
				$span_class = 'NavLeftUL_endpointIcon';
			}
			else
			{
		  		$item_class = '';
				$span_class = 'NavLeftUL_navIcon fa fa-chevron-right';
			}

			$output .= '<li id="liid'.$item->id.'" class="NavLeftUL_item NavLeftUL_topic '.$item_class.'">';
			$output .= '<a href="'.JRoute::_(FAQBookProHelperRoute::getTopicRoute($item->id)).'" class="NavLeftUL_anchor" onclick="return false;">';
			$output .= '<span class="topicTitle">';

			if (isset($topicParams->topic_icon_class) && $topicParams->topic_icon_class)
			{
				$output .= '<i class="NavLeftUL_topicIcon fa fa-'.$topicParams->topic_icon_class.'"></i>';
			}

			$output .= $item->title.'<span class="'.$span_class.'"></span>';
			$output .= '</span>';
			$output .= '</a>';
			$output .= '<ul class="NavLeftUL_sublist level'.$level.'">';

			foreach ($subitems as $subitem)
			{
		  		$output .= self::getTopicsTree($subitem, $level + 1);
			}

			$output .= '<li id="backliid'.$item->id.'" class="NavLeftUL_backItem"><a href="#" class="NavLeftUL_anchor" rel="nofollow" onclick="return false;"><span>'.JText::_('COM_FAQBOOKPRO_BACK').'<span class="NavLeftUL_navBackIcon fa fa-mail-reply"></span></span></a></li>';
			$output .= '</ul>';
			$output .= '</li>';
		}
		else
		{
	  		$output .= '<li id="liid'.$item->id.'" class="NavLeftUL_item NavLeftUL_topic NavLeftUL_endpoint">';
			$output .= '<a href="'.JRoute::_(FAQBookProHelperRoute::getTopicRoute($item->id)).'" class="NavLeftUL_anchor" onclick="return false;">';
			$output .= '<span class="topicTitle">';

			if (isset($topicParams->topic_icon_class) && $topicParams->topic_icon_class)
			{
				$output .= '<i class="NavLeftUL_topicIcon fa fa-'.$topicParams->topic_icon_class.'"></i>';
			}

			$output .= $item->title.'<span class="NavLeftUL_endpointIcon"></span>';
			$output .= '</span>';
			$output .= '</a>';
			$output .= '</li>';
		}

		return $output;
	}

	public static function getTopicDepth($item, $levels)
	{
		$topicModel = JModelLegacy::getInstance('Topic', 'FAQBookProModel');
		$children = $topicModel->getTopicChildren($item->id);

		if (count($children))
		{
	  		$levels++;

	  		foreach ($children as $child)
			{
		  		if (count($topicModel->getTopicChildren($child->id)))
				{
					return;
				}
			}
		}

		return $levels;
	}
}
