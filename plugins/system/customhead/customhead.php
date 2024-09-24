<?php
/*
	------------------------------------------------------------------------
	Extension for Joomla 2.5.x - Joomla 3.x
	------------------------------------------------------------------------
	Copyright (C) 2018 Inteldig INC.
	License: GNU General Public License version 2 or later; see LICENSE.txt
	Author: IntelDig
	Website: http://www.chatcompose.com
	Modified: 18/11/2018
	------------------------------------------------------------------------
*/

defined('_JEXEC') or die('No direct access');

jimport('joomla.plugin.plugin');

class plgSystemCustomhead extends JPlugin{
	function onBeforeCompileHead() {
		if (JFactory::getApplication()->isAdmin()){
			return true;
		}
		$doc = JFactory::getDocument();
		$customhead = $this->params->get('customhead');
		$customlang = $this->params->get('customlang');
		if (strlen($customhead) > 0) { $doc->addCustomTag('<link href="https://chatcompose.azureedge.net/static/all/global/export/css/main.5b1bd1fd.css" rel="stylesheet"><script async type="text/javascript" src="https://chatcompose.azureedge.net/static/all/global/export/js/main.a7059cb5.js" user="'.$customhead.'" lang="'.$customlang.'"></script>'); }
	}
}
