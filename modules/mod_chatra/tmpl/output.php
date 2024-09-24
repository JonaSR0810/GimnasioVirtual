<?php
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addCustomTag("<script>{$params->get('chatra_widget_code')}</script>");
