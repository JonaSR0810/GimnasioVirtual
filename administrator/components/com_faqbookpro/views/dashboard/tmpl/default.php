<?php

/**
 * @title        Minitek FAQ Book
 * @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
 * @license      GNU General Public License version 3 or later.
 * @author url   https://www.minitek.gr/
 * @developers   Minitek.gr
 */

defined('_JEXEC') or die;

$local_version = $this->utilities->localVersion();
?>

<div class="minitek-dashboard">
	<div class="row-fluid">

		<div class="span8">
			<div class="media">
				<div class="pull-left">
					<img class="media-object" src="<?php echo JURI::root(true) . '/administrator/components/com_faqbookpro/assets/images/logo.png'; ?>">
				</div>
				<div class="media-body">
					<h2 class="media-heading"><?php echo JText::_('COM_FAQBOOKPRO'); ?> <span class="label label-success">Free</span></h2>
					<?php echo JText::_('COM_FAQBOOKPRO_DESC'); ?>
				</div>
			</div>

			<div class="dashboard-thumbnails">
				<div class="thumbnail">
					<a href="<?php echo JRoute::_('index.php?option=com_faqbookpro&view=sections'); ?>">
						<i class="icon icon-list-2"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SECTIONS'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="<?php echo JRoute::_('index.php?option=com_faqbookpro&view=topics'); ?>">
						<i class="icon icon-folder"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_TOPICS'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="<?php echo JRoute::_('index.php?option=com_faqbookpro&view=questions'); ?>">
						<i class="icon icon-question"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_QUESTIONS'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="#" class="disabled" onclick="return: false;">
						<i class="icon icon-comment"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_ANSWERS'); ?>
							<span class="label label-default">Pro</span>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="#" class="disabled" onclick="return: false;">
						<i class="icon icon-envelope"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_EMAIL_TEMPLATES'); ?>
							<span class="label label-default">Pro</span>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="#" class="disabled" onclick="return: false;">
						<i class="icon icon-folder-plus"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_CUSTOM_FIELDS'); ?>
							<span class="label label-default">Pro</span>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="#" class="disabled" onclick="return: false;">
						<i class="icon icon-checkmark-2"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_CUSTOM_STATES'); ?>
							<span class="label label-default">Pro</span>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="#" class="disabled" onclick="return: false;">
						<i class="icon icon-attachment"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_ATTACHMENTS'); ?>
							<span class="label label-default">Pro</span>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_faqbookpro&path=&return=' . base64_encode(JURI::getInstance()->toString())); ?>">
						<i class="icon icon-cog"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_CONFIGURATION'); ?>
						</span>
					</a>
				</div>

				<div class="thumbnail">
					<a href="https://extensions.joomla.org/extensions/extension/directory-a-documentation/faq/minitek-faq-book/" target="_blank">
						<i class="icon icon-star" style="color: #ffcb52;"></i>
						<span class="thumbnail-title">
							<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_LIKE_THIS_EXTENSION'); ?><br>
							<span class="small">
								<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_LEAVE_A_REVIEW_ON_JED'); ?>
							</span>
						</span>
					</a>
				</div>
			</div>
		</div>

		<div class="span4">

			<div class="dashboard-module">
				<h2 class="nav-header"><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_ABOUT'); ?></h2>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_EXTENSION'); ?></div>
						<div class="span8"><a href="https://www.minitek.gr/joomla/extensions/minitek-faq-book" target="_blank"><?php echo JText::_('COM_FAQBOOKPRO'); ?></a></div>
					</div>
					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_VERSION'); ?></div>
						<div class="span8">
							<span class="label label-default"><?php echo $local_version; ?></span> <span class="label label-default">Free</span>
							<a id="check-version" href="#" class="btn btn-info btn-small pull-right">
								<i class="fa fa-refresh"></i>&nbsp;&nbsp;<?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_CHECK_VERSION'); ?>
							</a>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_DEVELOPER'); ?></div>
						<div class="span8"><a href="https://www.minitek.gr/" target="_blank">Minitek</a></div>
					</div>
					<div class="row-fluid">
						<div class="span4"><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_LICENSE'); ?></div>
						<div class="span8"><a href="https://www.minitek.gr/terms-of-service" target="_blank">GNU GPLv3 Commercial</a></div>
					</div>
				</div>
			</div>

			<div class="dashboard-module">
				<h2 class="nav-header"><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_QUICK_LINKS'); ?></h2>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="span12">
							<span class="icon-book" aria-hidden="true"></span>
							<span>
								<a href="https://www.minitek.gr/support/documentation/joomla/minitek-faq-book" target="_blank"><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_DOCUMENTATION'); ?></a>
							</span>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<span class="icon-list" aria-hidden="true"></span>
							<span>
								<a href="https://www.minitek.gr/support/changelogs/joomla/minitek-faq-book" target="_blank"><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_CHANGELOG'); ?></a>
							</span>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<span class="icon-help" aria-hidden="true"></span>
							<span>
								<a href="https://www.minitek.gr/support/documentation/joomla/minitek-faq-book/faq" target="_blank"><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_FAQ'); ?></a>
							</span>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<span class="icon-question" aria-hidden="true"></span>
							<span>
								<a href="https://www.minitek.gr/support/documentation/joomla/minitek-faq-book/free-vs-pro" target="_blank"><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_SIDEBAR_FREE_VS_PRO'); ?></a>
							</span>
						</div>
					</div>
					<div class="row-fluid text-warning">
						<div class="span12">
							<span class="icon-lock" aria-hidden="true"></span>
							<span>
								<a href="https://www.minitek.gr/joomla/extensions/minitek-faq-book" target="_blank"><?php echo JText::_('COM_FAQBOOKPRO_UPGRADE_TO_PRO'); ?></a>
							</span>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>
</div>