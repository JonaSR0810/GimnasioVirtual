<?php
/**
* sublayout products
*
* @package	VirtueMart
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL2, see LICENSE.php
* @version $Id: cart.php 7682 2014-02-26 17:07:20Z Milbo $
*/

defined('_JEXEC') or die('Restricted access');

$product = $viewData['product'];
$position = $viewData['position'];
$customTitle = isset($viewData['customTitle'])? $viewData['customTitle']: false;;
if(isset($viewData['class'])){
	$class = $viewData['class'];
} else {
	$class = 'product-fields';
}

if (!empty($product->customfieldsSorted[$position])) {
	?>
	<div class="<?php echo $class?> text-center clearfix">
		<?php
		if($customTitle and isset($product->customfieldsSorted[$position][0])){
			$field = $product->customfieldsSorted[$position][0]; ?>
		<div class="product-fields-title-wrapper">
			<span class="product-fields-title small">
			<?php echo vmText::_ ($field->custom_title) ?>
			<?php if ($field->custom_tip) {
				//echo JHtml::tooltip (vmText::_($field->custom_tip), vmText::_ ($field->custom_title), 'tooltip.png');
			echo '<span class="glyphicon glyphicon-exclamation-sign hasTooltip" title="' . vmText::_($field->custom_title) . '"></span>';
			} ?>
			</span>
		</div>
		<?php
		}
		$custom_title = null;
		foreach ($product->customfieldsSorted[$position] as $field) {
			if ( $field->is_hidden ) continue; //OSP http://forum.virtuemart.net/index.php?topic=99320.0
			?>
		<div class="product-field product-field-type-<?php echo $field->field_type ?> clearfix">
			<?php if (!$customTitle and $field->custom_title != $custom_title and $field->show_title) { ?>
			<div class="product-fields-title-wrapper">
				<span class="product-fields-title small">
				<?php echo vmText::_ ($field->custom_title) ?>
				<?php if ($field->custom_tip) {
				//echo JHtml::tooltip (vmText::_($field->custom_tip), vmText::_ ($field->custom_title), 'tooltip.png');
				echo '<span class="glyphicon glyphicon-exclamation-sign hasTooltip small" title="' . vmText::_($field->custom_tip) . '"></span>';
				} ?>
				</span>
			</div>
			<?php }
			if (!empty($field->display)){ ?>
			<div class="product-field-display"><?php echo $field->display ?></div>
			<?php
			}
			if (!empty($field->custom_desc)){ ?>
			<div class="product-field-desc small"><?php echo vmText::_($field->custom_desc) ?></div>
			<?php
			}
			?>
		</div>
		<?php
			$custom_title = $field->custom_title;
		} ?>
	</div>
<?php
}