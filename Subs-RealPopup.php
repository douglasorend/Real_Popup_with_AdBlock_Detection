<?php
/**********************************************************************************
* Subs-RealPopup.php                                                              *
***********************************************************************************
* This mod is licensed under the 2-clause BSD License, which can be found here:   *
*	http://opensource.org/licenses/BSD-2-Clause                                   *
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but	  *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY	  *
* or FITNESS FOR A PARTICULAR PURPOSE.											  *
**********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

//================================================================================
// Hook function to load our popup message for the user:
//================================================================================
function RPU_Load()
{
	global $context, $settings, $boardurl, $modSettings, $cookiename;

	// Load language file if we are loading the Help stuff:
	if (isset($_GET['action']) && $_GET['action'] == 'help')
	{
		loadLanguage('RealPopup');
		return;
	}

	// Don't include anything if the mod is disabled!!!
	if (empty($modSettings['realpopup_enabled']))
		return;

	// Let's figure out some parameters before we add everything:
	$fxeffect = (empty($modSettings['realpopup_fxeffect']) ? 'swing' : $modSettings['realpopup_fxeffect']);

	$display = (empty($modSettings['realpopup_display']) ? 'immediate' : $modSettings['realpopup_display']);
	$value = empty($modSettings['realpopup_display_value']) ? '0' : $modSettings['realpopup_display_value'];
	$display = ($display == 'percentage' ? $value . '%' : ($display == 'seconds' ? $value. 's' : 'immediate'));

	$duration = (empty($modSettings['realpopup_duration']) ? 'session' : $modSettings['realpopup_duration']);
	$value = empty($modSettings['realpopup_duration_value']) ? '0' : $modSettings['realpopup_duration_value'];
	$duration = ($duration == 'min' ? $value . 'min' : ($duration == 'hrs' ? $value. 'hrs' : ($duration == 'hrs' ? $value. 'days' : ($duration == 'session' || $duration == 'always' ? $duration : 'session'))));

	// Make sure we don't double declare JQuery:
	if (strpos($context['html_headers'], 'jquery') === false)
		$context['html_headers'] .= '
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>';

	// Let's add everything else:
	$context['html_headers'] .= '
	<script><!-- // --><![CDATA[
		window.jQuery || document.write(\'<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"><\/script>\');
	// ]]></script>
	<link rel="stylesheet" type="text/css" href="' . $boardurl . '/RealPopup/blossomfeaturebox.css" />
	<link rel="stylesheet" type="text/css" href="' . $boardurl . '/RealPopup/optincontent.css" />
	<script type="text/javascript" src="'. $boardurl . '/RealPopup/blossomfeaturebox.js"></script>' . (!empty($modSettings['realpopup_adblock']) ? '
	<script type="text/javascript" src="'. $boardurl . '/RealPopup/blockadblock.js"></script>' : '') . '
	<script type="text/javascript"><!-- // --><![CDATA[
		function UsePopup()
		{
			jQuery(function($){
				blossomfeaturebox.init({
					optinfile: "' . $boardurl . '/RealPopup/optincontent.txt",
					fxeffect: "' . $fxeffect . '",
					displaytype: "' . $display . '",
					displayfreq: {
						duration: "' . $duration . '",
						cookiename: "' . $cookiename . '_featurebox"
					}
				})
			});' . (!empty($modSettings['realpopup_adblock']) ? '
			blossomfeaturebox.$blossomui.on("click", function(){
				blossomfeaturebox.showfeaturebox()
			})' : '') . '			
		}' . (empty($modSettings['realpopup_adblock']) ? '
		UsePopup();' : '
		if(typeof blockAdBlock === "undefined") {
			UsePopup();
		} else {
			blockAdBlock.on(true, UsePopup);
		}') . '
	// ]]></script>';

	// Build any styling differences:
	$style = array();
	if (!empty($modSettings['realpopup_max_width']))
		$style[] = 'max-width: ' . str_replace('pxpx', 'px', $modSettings['realpopup_max_width'] . 'px') . ';';
	if (!empty($modSettings['realpopup_color']))
		$style[] = 'color: #' . substr('000000' . $modSettings['realpopup_color'], 0, 6) . ';';
	if (!empty($modSettings['realpopup_background']))
		$style[] = 'background: #' . substr('000000' . $modSettings['realpopup_background'], -6) . ';';

	// Include any inline styling changes to the forum:
	if (!empty($style) || !empty($modSettings['realpopup_black_screen']))
		$context['html_headers'] .= '
	<style>';
	if (!empty($style))
		$context['html_headers'] .= '
		div.blossomfeaturebox div.optincontent2wrapper{
			' . implode('
			', $style) . '
		}';
	
	// Change the opacity to create a black screen overlay:
	if (!empty($modSettings['realpopup_black_screen']))
		$context['html_headers'] .= '
		div.blossomfeaturebox:before{
			opacity: 1.0;
		}';
	if (!empty($style) || !empty($modSettings['realpopup_black_screen']))
		$context['html_headers'] .= '
	</style>';
}

//================================================================================
// Admin hook functions to add "Real Popup" to the Modification Settings page:
//================================================================================
function RPU_Admin(&$areas)
{
	global $txt;
	loadLanguage('RealPopup');
	$areas['config']['areas']['modsettings']['subsections']['realpopup'] = array($txt['realpopup_title']);
}

function RPU_Hook(&$subactions)
{
	$subactions['realpopup'] = 'RPU_Settings';
}

//================================================================================
// Function dealing mod settings:
//================================================================================
function RPU_Settings($return_config = false)
{
	global $txt, $scripturl, $context, $boarddir, $modSettings;

	isAllowedTo('admin_forum');
	$config_vars = array(
		array('check', 'realpopup_enabled'),
		array('select', 'realpopup_fxeffect',  array(
				'swing' => $txt['realpopup_fx_swing'],
				'slidedown' => $txt['realpopup_fx_slidedown'],
				'starwars' => $txt['realpopup_fx_starwars'],
				'newspaper' => $txt['realpopup_fx_newspaper'],
				'wiggle' => $txt['realpopup_fx_wiggle'],
		)),
		'',
		array('select', 'realpopup_display', array(
			'immediate' => $txt['realpopup_display_immediate'],
			'percentage' => $txt['realpopup_display_percentage'],
			'seconds' => $txt['realpopup_display_seconds'],
		)),
		array('int', 'realpopup_display_value', 'javascript' => ' onkeyup="this.value=this.value.replace(/[^\d]/,\'\')"'),
		'',
		array('select', 'realpopup_duration', array(
			'always' => $txt['realpopup_duration_always'],
			'session' => $txt['realpopup_duration_session'],
			'min' => $txt['realpopup_duration_minutes'],
			'hrs' => $txt['realpopup_duration_hours'],
			'days' => $txt['realpopup_duration_days'],
		)),
		array('int', 'realpopup_duration_value', 'javascript' => ' onkeyup="this.value=this.value.replace(/[^\d]/,\'\')"'),
		'',
		array('text', 'realpopup_max_width'),
		array('text', 'realpopup_color', 'javascript' => ' onkeyup="this.value=this.value.replace(/[^\d|A-F|a-f]/,\'\')"'),
		array('text', 'realpopup_background', 'javascript' => ' onkeyup="this.value=this.value.replace(/[^\d|A-F|a-f]/,\'\')"'),
		'',
		array('check', 'realpopup_adblock'),
		array('check', 'realpopup_black_screen'),
		'',
		array('callback', 'realpopup_contents'),
	);
	if ($return_config)
		return $config_vars;

	// Saving?
	if (isset($_GET['save']))
	{
		// Make sure this is a valid session!!!
		checkSession();

		// Write the contents of the "optincontent.txt" file:
		$handle = @fopen($boarddir . '/RealPopup/optincontent.txt', 'w');
		@fwrite($handle, $_POST['realpopup_contents']);
		fclose($handle);
		unset($_POST['realpopup_contents']);
	
		// Make sure certain values are set:
		$_POST['realpopup_display_value'] = (!isset($_POST['realpopup_display_value']) ? 0 : (int) $_POST['realpopup_display_value']);
		$_POST['realpopup_duration_value'] = (!isset($_POST['realpopup_duration_value']) ? 0 : (int) $_POST['realpopup_duration_value']);
		$_POST['realpopup_max_width'] = (!isset($_POST['realpopup_max_width']) ? '' : $_POST['realpopup_max_width']);
		$_POST['realpopup_color'] = (!isset($_POST['realpopup_color']) ? '' : $_POST['realpopup_color']);
		$_POST['realpopup_background'] = (!isset($_POST['realpopup_background']) ? '' : $_POST['realpopup_background']);

		// Save the settings, then return to config screen:
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=modsettings;sa=realpopup');
	}
	$modSettings['realpopup_contents'] = @file_get_contents($boarddir . '/RealPopup/optincontent.txt');
	prepareDBSettingContext($config_vars);
	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;sa=realpopup;save';
	$context['settings_title'] = $txt['realpopup_title'];
}

//================================================================================
// Admin template function to edit the "optincontent.txt" file:
//================================================================================
function template_callback_realpopup_contents()
{
	global $modSettings, $txt;
	echo '
							<p>', $txt['realpopup_contents'], '</p>
							<textarea rows="10" name="realpopup_contents" id="realpopup_contents" style="width: 99%; align: center;">', $modSettings['realpopup_contents'], '</textarea>';
}

?>