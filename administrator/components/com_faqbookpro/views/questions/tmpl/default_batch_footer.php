<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;
?>

<button class="btn" type="button" onclick="document.getElementById('batch-topic-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value='';document.getElementById('batch-user-id').value=''" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</button>

<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('question.batch');">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
