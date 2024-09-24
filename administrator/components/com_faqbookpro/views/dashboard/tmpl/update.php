<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

$local_version = $this->utilities->localVersion();
$latest_version = $this->utilities->latestVersion();
$message = $this->utilities->updateMessage();
$message_version = $this->utilities->updateMessageVersion();
$type = JFactory::getApplication()->input->get('type', 'auto');

if ($latest_version && version_compare($latest_version, $local_version, '>')) { ?>
  <div class="alert alert-success" id="update-box">
    <div class="update-info">
      <div style="margin: 0 0 10px;"><span><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_A_NEW_VERSION_IS_AVAILABLE'); ?></span></div>
      <div>
        <span class="label label-success"><?php echo $latest_version; ?></span> <span class="label label-success">Free</span>
        <a class="btn btn-primary" href="<?php echo JRoute::_('index.php?option=com_installer&view=update'); ?>"><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_UPDATE_NOW'); ?></a>
      </div>
      <?php if ($message && version_compare($local_version, $message_version, '<')) { ?>
        <div class="alert alert-danger" style="margin: 15px 0 0;">
          <div class="update-info">
            <div>
              <h3>WARNING</h3>
              <p><?php echo $message; ?></p>
              <p><small><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_DISABLE_MESSAGE'); ?></small></p>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
<?php } else if ($type == 'check') { ?>
  <?php if ($latest_version) { ?>
  <div class="alert alert-success" id="update-box">
    <div class="update-info">
      <div>
        <?php if ($latest_version == $local_version) { ?>
          <span><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_YOU_HAVE_THE_LATEST_VERSION'); ?></span>
        <?php } else { ?>
          <span><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_YOU_HAVE_A_DEVELOPMENT_VERSION'); ?></span>
        <?php } ?>
        <span class="label label-success"><?php echo $latest_version; ?></span> <span class="label label-success">Free</span>
      </div>
    </div>
  </div>
  <?php } else { ?>
    <div class="alert alert-danger" id="update-box">
      <div class="update-info">
        <div>
          <span><?php echo JText::_('COM_FAQBOOKPRO_DASHBOARD_COULD_NOT_FETCH_UPDATE_INFO'); ?></span>
        </div>
      </div>
    </div>
  <?php } ?>
<?php }
