<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;
$params = $this->state->get('params');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "question.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			' . $this->form->getField('content')->save() . '
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>

<?php if ($this->item->private && !$this->canDo->get('core.private.create')) { ?>
	<div class="alert alert-danger">
		<?php echo JText::_('COM_FAQBOOKPRO_QUESTION_PRIVATE_NOT_ACCESS_TO_CHANGE_TOPIC'); ?>
	</div>
<?php } ?>

<form action="<?php echo JRoute::_('index.php?option=com_faqbookpro&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">

		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_FAQBOOKPRO_FIELDSET_GENERAL', true)); ?>
				<div class="row-fluid">
					<div class="span9">
						<fieldset class="adminform">
							<?php echo $this->form->getInput('content'); ?>
						</fieldset>
					</div>
					<div class="span3">
						<fieldset class="form-vertical">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('topicid'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('topicid'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('state'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('state'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('pinned'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('pinned'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('access'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('access'); ?>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'options', JText::_('COM_FAQBOOKPRO_FIELDSET_OPTIONS', true)); ?>
				<div class="row-fluid form-horizontal-desktop">
					<div class="span12">
						<?php echo $this->form->renderFieldset('images'); ?>
					</div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_FAQBOOKPRO_FIELDSET_PUBLISHING', true)); ?>
				<div class="row-fluid form-horizontal-desktop">
					<div class="span6">
						<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
					</div>
					<div class="span6">
						<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
					</div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="jform[language]" value="*" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
