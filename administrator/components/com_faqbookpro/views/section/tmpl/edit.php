<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "section.cancel" || document.formvalidator.isValid(document.id("item-form")))
		{
			' . $this->form->getField("description")->save() . '
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_faqbookpro&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

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
						<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
					</div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'navigation', JText::_('COM_FAQBOOKPRO_FIELDSET_NAVIGATION', true)); ?>
				<div class="row-fluid form-horizontal-desktop">
					<div class="span12">
						<?php echo $this->form->renderFieldset('navigation'); ?>
					</div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'options', JText::_('COM_FAQBOOKPRO_FIELDSET_OPTIONS', true)); ?>
				<div class="row-fluid form-horizontal-desktop">
					<div class="span12">
						<div class="alert alert-info">
							<span class="icon-info" aria-hidden="true"></span> <?php echo JText::_('COM_FAQBOOKPRO_FIELDSET_OPTIONS_DESC'); ?>
						</div>
						<?php echo $this->form->renderFieldset('options'); ?>
					</div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'topics', JText::_('COM_FAQBOOKPRO_FIELDSET_TOPIC', true)); ?>
				<div class="row-fluid form-horizontal-desktop">
					<div class="span12">
						<div class="alert alert-info">
							<span class="icon-info" aria-hidden="true"></span> <?php echo JText::_('COM_FAQBOOKPRO_FIELDSET_TOPIC_DESC'); ?>
						</div>
						<?php echo $this->form->renderFieldset('section_topics'); ?>
					</div>
				</div>
				<div class="row-fluid form-horizontal-desktop separate_topic">
					<div class="span12">
						<?php echo $this->form->renderFieldset('separate_topic'); ?>
					</div>
				</div>
				<div class="row-fluid form-horizontal-desktop">
					<div class="span12">
						<?php echo $this->form->renderFieldset('topic_ordering'); ?>
					</div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'questions', JText::_('COM_FAQBOOKPRO_FIELDSET_QUESTIONS', true)); ?>
				<div class="row-fluid form-horizontal-desktop">
					<div class="span12">
						<div class="alert alert-info">
							<span class="icon-info" aria-hidden="true"></span> <?php echo JText::_('COM_FAQBOOKPRO_FIELDSET_QUESTIONS_DESC'); ?>
						</div>
						<?php echo $this->form->renderFieldset('section_questions'); ?>
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
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
