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

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "topic.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			' . $this->form->getField("description")->save() . '
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_faqbookpro&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">

		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_FAQBOOKPRO_FIELDSET_GENERAL', true)); ?>
				<div class="row-fluid">
					<div class="span9">
						<fieldset class="adminform">
						<?php echo $this->form->getLabel('description'); ?>
						<?php echo $this->form->getInput('description'); ?>
					</fieldset>
					</div>
					<div class="span3">
						<fieldset class="form-vertical">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('parent_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('parent_id'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('section_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('section_id'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('published'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('published'); ?>
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
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('language'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('language'); ?>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'params', JText::_('COM_FAQBOOKPRO_FIELDSET_OPTIONS', true)); ?>
				<div class="row-fluid form-horizontal-desktop">
					<div class="span12">
						<?php echo $this->form->renderFieldset('basic'); ?>
					</div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'questions', JText::_('COM_FAQBOOKPRO_FIELDSET_QUESTIONS', true)); ?>
				<div class="row-fluid form-horizontal-desktop">
					<div class="span12">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('qvisibility'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('qvisibility'); ?>
							</div>
						</div>
						<?php echo $this->form->renderFieldset('questions'); ?>
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

			<?php if ($this->canDo->get('core.admin')) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'rules', JText::_('COM_FAQBOOKPRO_FIELDSET_RULES', true)); ?>
					<?php echo $this->form->getInput('rules'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
