(function($) {
	$(function(){

		function getUrlParameter(sParam)
		{
			var sPageURL = window.location.search.substring(1);
			var sURLVariables = sPageURL.split('&');

			for (var i = 0; i < sURLVariables.length; i++)
			{
				var sParameterName = sURLVariables[i].split('=');

				if (sParameterName[0] == sParam)
				{
					return sParameterName[1];
				}
			}
		}

		jQuery(function()
		{
			if (getUrlParameter('view') == 'topic')
			{
				dynamicSection();
			}
		});

		function dynamicSection()
		{
			// Global variables
			var token = window.mfbvars.token + "=1";
			var site_path = window.mfbvars.site_path;

			jQuery('select[name="jform\[parent_id\]"]').change(function(){
				topicId = jQuery(this).val();

				jQuery('#jform_section_id_chzn')
					.addClass('section-disabled')
					.end()
				;

				jQuery.ajax({
					type: "POST",
					url: site_path+"index.php?option=com_faqbookpro&task=topic.dynamicSection&topicid=" + topicId + "&" + token,
					success: function(data)
					{
						if (data != 'root')
						{
							var json = jQuery.parseJSON(data);
							jQuery('select[name="jform\[section_id\]"]').val(json.section_id);
							jQuery('#jform_section_id_chzn').find('.chzn-single > span').html(json.section_title + ' [' + json.section_alias + ']');
							jQuery('#jform_section_id_chzn').find('.chzn-drop').hide();
						}
						else
						{
							jQuery('#jform_section_id_chzn')
								.removeClass('section-disabled')
								.end()
							;

							jQuery('#jform_section_id_chzn').find('.chzn-drop').show();
						}
					}
				});
			});
		}
	})
})(jQuery);
