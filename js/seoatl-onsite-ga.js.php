<?php
error_reporting(0);
if (!function_exists('add_action'))
{
    require_once("../../../../wp-config.php");
}
?>
jQuery(document).ready(function(){
    var url = "<?php bloginfo('wpurl') ?>/wp-content/plugins/onsite-google-analytics-plugin/php/seoatl-onsite-ga-ajax.php";
    jQuery("#onsite-ga-plugin-inner").load(url,{'request_uri': jQuery("#seoatl_onsite_ga_request_uri").val()});

});

