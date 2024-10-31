<?php
error_reporting(0);
if (!function_exists('add_action'))
{
    require_once("../../../../wp-config.php");
}
if (isset($seoatl_onsite_ga_plugin)) {
    $seoatl_onsite_ga_plugin->loadProfileOptions($_POST['username'],$_POST['password']);
}
?>