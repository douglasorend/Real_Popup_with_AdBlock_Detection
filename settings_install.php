<?php
// If we have found SSI.php and we are outside of SMF, then we are running standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF')) // If we are outside SMF and can't find SSI.php, then throw an error
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as SMF\'s SSI.php.');

require_once($sourcedir.'/Subs-Admin.php');
updateSettings(
	array(
		'realpopup_enabled' => 0,
		'realpopup_fxeffect' => 'swing',
		'realpopup_display' => 'immediate',
		'realpopup_display_value' => 1,
		'realpopup_duration' => 'session',
		'realpopup_duration_value' => 1,
		'realpopup_max_width' => '600px',
	)
);

if (SMF == 'SSI')
	echo 'Congratulations! You have successfully installed the settings for this mod!';
?>