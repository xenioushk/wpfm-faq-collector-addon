<?php

$updaterBase = 'https://projects.bluewindlab.net/wpplugin/zipped/plugins/';
$pluginRemoteUpdater = $updaterBase . 'wpfm/notifier_wpfm_fca.php';
new WpAutoUpdater(BWL_WPFM_FCA_PLUGIN_VERSION, $pluginRemoteUpdater, BWL_WPFM_FCA_PLUGIN_UPDATER_SLUG);
