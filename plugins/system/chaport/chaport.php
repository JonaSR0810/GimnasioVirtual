<?php

/*
 * @version;   						1.0.0
 * @category;						widget
 * @copyright;   					Copyright (c) 2019 Chaport.
 * @license;   						GNU GPLv3 http://www.gnu.org/licenses/gpl.html
 * @link ;   						https://www.chaport.com/
 */

	defined( '_JEXEC' ) or die( 'Direct Access Denied' );

	jimport('joomla.plugin.plugin');

	jimport('joomla.html.parameter');

	class plgSystemChaport extends JPlugin {

		function __construct(&$subject, $config)	{
			parent::__construct($subject, $config);
			$this->_plugin = JPluginHelper::getPlugin("system", "chaport");
			$this->_params = new JRegistry( $this->_plugin->params );
		}

		function onAfterRender() {
			$app = JFactory::getApplication();
			$doc = JFactory::getDocument();
			$app_id = $this->params->get('app_id');
			$check_code = $this->params->get('installation_type');
			$install_code = $this->params->get('installation_code');
			$widget_code = '';

			$isAdmin = method_exists($app, 'isClient')
				? $app->isClient('administrator')
				: $app->isAdmin();

			if($isAdmin || strpos($_SERVER["PHP_SELF"], "index.php") === false) {
				return;
			}

			if($check_code == 1){
				if (empty($app_id)) return;

				$widget_code = "<!-- Begin of Chaport Live Chat code -->
				<script type=\"text/javascript\">
				(function(w,d,v3){
				w.chaportConfig = { appId : '{$app_id}' };

				if(w.chaport)return;v3=w.chaport={};v3._q=[];v3._l={};v3.q=function(){v3._q.push(arguments)};v3.on=function(e,fn){if(!v3._l[e])v3._l[e]=[];v3._l[e].push(fn)};var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://app.chaport.com/javascripts/insert.js';var ss=d.getElementsByTagName('script')[0];ss.parentNode.insertBefore(s,ss)})(window, document);
				</script>
				<!-- End of Chaport Live Chat code -->";
			} elseif ($check_code == 2) {
				if (empty($install_code)) return;
				$widget_code = $install_code;
			}
			$user = JFactory::getUser();
			$set_data = '';
			if (!$user->guest and !empty($user->email) and !empty($user->name)){
				$set_data = "<script type=\"text/javascript\">
				window.chaport.q('setVisitorData', [ {
  				email:   '{$user->email}',
  				name:    '{$user->name}'
				}]);
				</script>";
			}

			$block = JFactory::getApplication()->getBody();
			$block = str_replace("</body>", $widget_code . $set_data . "</body>", $block);
			JFactory::getApplication()->setBody($block);
			return true;

		}
	}
?>