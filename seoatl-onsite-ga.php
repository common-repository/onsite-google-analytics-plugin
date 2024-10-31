<?php
/* 
Plugin Name: Seoatl On Site Google Analytics
Plugin URI: http://www.seoatl.com/tools/wordpress/on-site-google-analytics-plugin
Version: v0.4.1
Author: <a href="http://twitter.com/seoatl">James Charlesworth</a>
Description: A Google Analytics plugin for viewing GA on your website.
 
Copyright 2010  James Charlesworth  (email : james DOT charlesworth [a t ] g m ail DOT com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributded in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/



if (!class_exists("SeoatlOnSiteGa")) {
	class SeoatlOnSiteGa {
                var $adminOptionsName = "SeoatlOnSiteGaAdminOptions";
               
              


		function SeoatlOnSiteGa() { //constructor
                    set_include_path(dirname(__FILE__).'/php'.PATH_SEPARATOR.get_include_path());

                    require_once 'php/Zend/Loader/Autoloader.php';
                    Zend_Loader_Autoloader::getInstance();
			
		}

                function init() {
                    $this->getAdminOptions();
                   
                }



                function getAdminOptions() {
                    $seoatlAdminOptions = array(
                        'ga_token' => '',
                        'ga_profile_id' => '',
                        'ga_date_range'=>'month',
                        'author_global'=> 0,
                        'ga_form'=>1,
                        'ga_version' => 'Asynchronous',
                        'users'=>array());

                    $seoatlOptions = get_option($this->adminOptionsName);
                    if (!empty($seoatlOptions)) {
                        foreach ($seoatlOptions as $key => $option)
                            $seoatlAdminOptions[$key] = $option;
                    }
                    update_option($this->adminOptionsName, $seoatlAdminOptions);



                    return $seoatlAdminOptions;


                }

                public function getUserLevel($user_level)
                {
                    switch($user_level) {
                        case 10:
                            return 'admin';
                            break;
                        case 7:
                            return 'publisher';
                            break;
                        case 3:
                            return 'author';
                            break;
                        case 2:
                            return 'editor';
                            break;
                        

                    }
                }

                function checkUserPriviledges()
                {
                     global $user_ID;
                     global $post;
                     $seoatlOptions = $this->getAdminOptions();
                     $user_info = get_userdata($user_ID);
                    
                    if (is_page()) {
                        
                        $page = get_page($post->ID);

                        if (is_object($page)) {

                             $author_id = $page->post_author;
                             

                        } else {
                             $author_id = $page['post_author'];

                        }
                       
                        
                    } else {
                        //$author_id = get_post($post->ID)->post_author;///////////////////////////////////////////<!--need to fix this
                   
                    }
                
                 

                     if ( is_object($user_info) && ($user_info->user_level==10)) {
                        
                         return true;

                    } elseif (is_object($user_info) && $seoatlOptions['author_global']==1 && $user_info->user_level>1) {
                        return true;
                    } elseif (in_array($user_ID,$seoatlOptions['users'])) {
                         //check to make sure teh author id and the user id match

                         if ($author_id==$user_ID) {
                      
                             return true;

                            
                         } else {
                             return false;
                         }

                     }  else {
                         return false;
                     }
                }


                function createGoogleAuthUri() {

                    $GA_Feed = 'https://www.google.com/analytics/feeds';
                    $next    = get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=seoatl-onsite-ga.php';
                    return Zend_Gdata_AuthSub::getAuthSubTokenUri($next,$GA_Feed, 0, 1);


                }



                function printAdminPage() {
                    


                  
                    
                    global $wpdb;
                    
                    $seoatlOptions = $this->getAdminOptions();

                    ?>
<div class="wrap">
    <h2>Onsite Google Analytics</h2>
<?php
                    if (isset($_GET['token'])) {
                      
                         $token = $_GET['token'];
                         $seoatlOptions['ga_token'] = Zend_Gdata_AuthSub::getAuthSubSessionToken($token);

                       
                         //Zend_Gdata_AuthSub::getAuthSubSessionToken($seoatlOptions['ga_token']);
                         update_option($this->adminOptionsName, $seoatlOptions);
                         ?>
 <div class="updated"><p><strong><?php _e("Google Account Authenticated.", "SeoatlOnSiteGa");?></strong></p></div>
<?php
                    }

                    if (isset($_POST['update_seoatlOnSiteGaSettings'])) {

                        
                        if (isset($_POST['seoatlGaProfileId'])) {
                            $seoatlOptions['ga_profile_id'] = $_POST['seoatlGaProfileId'];

                        }

                        if (isset($_POST['seoatlGaDateRange'])) {
                            $seoatlOptions['ga_date_range'] = $_POST['seoatlGaDateRange'];

                        }

                        if (isset($_POST['seoatlGaVersion'])) {
                            $seoatlOptions['ga_version'] = $_POST['seoatlGaVersion'];

                        }
                    
                        if (isset($_POST['seoatlGAUsers'])) {
                            $seoatlOptions['users'] = $_POST['seoatlGAUsers'];

                        }

                       
                        if (isset($_POST['seoatlGaAuthorGlobal'])) {
                            $seoatlOptions['author_global'] = $_POST['seoatlGaAuthorGlobal'];

                        }

                        if (isset($_POST['seoatlGAForm'])) {
                            $seoatlOptions['ga_form'] = $_POST['seoatlGAForm'];

                        }
                    
                       // unset($seoatlOptions['ga_password']);
                        update_option($this->adminOptionsName, $seoatlOptions);

                       ?>
                        <div class="updated"><p><strong><?php _e("Settings Updated.", "SeoatlOnSiteGa");?></strong></p></div>

                     <?php
                    }

                   
                    ?>
                        <div class="postbox-container" style="width:65%">
                            <div class="metabox-holder">
                        <form method="post" action="<?php echo get_bloginfo('wpurl') ; ?>/wp-admin/options-general.php?page=seoatl-onsite-ga.php">
                            <div class="postbox">
                            <h3>Seoatl Onsite Google Analytics Settings</h3>
                            <div class="inside" style="margin:10px;">
                           <table class="form-table">
                               <tbody>
                                   <tr>
                                       <th>Google Account</th>
                                       <td>
                                             <a class="button" href="<?php echo $this->createGoogleAuthUri() ?>">Authenticate</a>
                                       </td>
                                   </tr>
                                   <tr>
                                       <th>Google Analytics Profile</th>
                                       <td>
                                           <select id="seoatlGaProfileId" name="seoatlGaProfileId">
                                            <?php echo $this->loadProfileOptions(); ?>
                                           </select>
                                       </td>
                                   </tr>
                                   <tr>
                                       <th>Date Range:</th>
                                       <td>
                                           <select  id="seoatlGaDateRange" name="seoatlGaDateRange">
                                                <option <?php if ($seoatlOptions['ga_date_range']=='today') echo "selected" ;?> value="today">Today</option>
                                                <option <?php if ($seoatlOptions['ga_date_range']=='yesterday') echo "selected" ;?> value="yesterday">Yesterday</option>
                                                <option <?php if ($seoatlOptions['ga_date_range']=='month') echo "selected" ;?> value="month">Past Month</option>
                                             </select>
                                       </td>
                                   </tr>
                                   <tr>
                                       <th>Google Analytics Version:</th>
                                       <td>
                                           <select  id="seoatlGaVersion" name="seoatlGaVersion">
                                                <option <?php if ($seoatlOptions['ga_version']=='Asynchronous') echo "selected" ;?> value="Asynchronous">Asynchronous</option>
                                                <option <?php if ($seoatlOptions['ga_version']=='Traditional') echo "selected" ;?> value="Traditional">Traditional</option>

                                            </select>

                                       </td>
                                   </tr>
                                   <tr>
                                       <th>Select Users Who Can See Analytics
                                       <td>

<select multiple size="5" style="width:400px;height:200px;" name="seoatlGAUsers[]">



<?php
for ( $i=2;$i<=10;$i++) {
		$userlevel = $i;
		$authors = $wpdb->get_results("SELECT * from $wpdb->usermeta WHERE meta_key = 'wp_user_level' AND meta_value = '$userlevel'");
		foreach ( (array) $authors as $author ) {
			$author    = get_userdata( $author->user_id );
			$userlevel = $author->wp2_user_level;
			$name      = $author->nickname;
			if ( $show_fullname && ($author->first_name != '' && $author->last_name != '') ) {
				$name = "$author->first_name $author->last_name";
			}
                        if (in_array($author->ID,$seoatlOptions['users'])) {
                            $selected = 'selected="true"';
                        } else {
                            $selected =null;
                        }

			$link = '<option value="'.$author->ID.'" '.$selected.'>' . $name . ' - '.self::getUserLevel($author->user_level).'</option>';
			echo $link;
		}


	}
?>
                        </select>

                                       </td>
                                   </tr>
                                   <tr>
                                       <th>
                                           Allow authors to view analytics of other authors posts?
                                       </th>
                                       <td>
                     <input type="radio" name="seoatlGaAuthorGlobal" value="1" <?php echo (!isset( $seoatlOptions['author_global']) or ($seoatlOptions['author_global']==1)) ? 'checked' : '' ?> />Yes
                         <input type="radio" name="seoatlGaAuthorGlobal" value="0" <?php echo (isset( $seoatlOptions['author_global']) && ($seoatlOptions['author_global']==0)) ? 'checked' : '' ?> /> No <br />




                                       </td>
                                   </tr>
                                   <tr>
                                       <th>
                                           Track forms?
                                       </th>
                                       <td>
                     <input type="radio" name="seoatlGAForm" value="1" <?php echo (!isset( $seoatlOptions['ga_form']) or ($seoatlOptions['ga_form']==1)) ? 'checked' : '' ?> />Yes
                         <input type="radio" name="seoatlGAForm" value="0" <?php echo (isset( $seoatlOptions['ga_form']) && ($seoatlOptions['ga_form']==0)) ? 'checked' : '' ?> /> No <br />




                                       </td>
                                   </tr>
                     
           
                       
                                 
    
                        
                    
                        
                       
                           </table>
                                <div class="alignright">
                                    <input type="submit" class="button-primary" name="update_seoatlOnSiteGaSettings" value="<?php _e('Update Settings', 'SeoatlOnSiteGa') ?>" />
                                </div>
                                <br class="clear" />
                            </div><!--inside-->
                            </div><!--postbox-->
                        </form>

                            </div>
                         </div><!--postbox-container-->
                         <div class="postbox-container side" style="width:20%">
                             <div class="metabox-holder">
                                 <div class="">
                                     <div class="postbox" id="JamesCharlesworth">
                                        
                                         <h3>
                                            <span> Credits</span>
                                         </h3>
                                         <div class="inside" style="margin:10px;">
                                             <p><i>Created by James Charlesworth</i></p>
                                             <p>If you like this plugin, please consider
                                                 linking to my website:<br/>
                                                 <a target="_blank" href="http://www.jamescharlesworth.com">
                                                     http://www.jamescharlesworth.com
                                                 </a>
                                             </p>
                                             <p>You can also follow me on Twitter:<br />
                                                 <a target="_blank" href="http://twitter.com/seoatl">
                                                    @seoatl
                                                 </a>
                                             </p>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>

</div><?php
 
                   

                }


                 function loadProfileOptions() {

                    global $user_ID;
                     $val=null;
                    
                     if (!self::checkUserPriviledges()) {

                         return;
                     }

                     $seoatlOptions = $this->getAdminOptions();
                     require_once('php/classes/gapi.class.php');
                    
                     
                     $ga = new gapi(null,null,$seoatlOptions['ga_token']);//null->username, null->password -- using oauth instead
                
                     foreach ($ga->requestAccountData(1,10000) as $account) {
                        
                        $val .= '<option ';
                        if ($seoatlOptions['ga_profile_id']==$account->getProfileId())
                                $val.= 'selected';

                        $val.= ' value="'.$account->getProfileId().'">'.$account->getAccountName().' ('.$account->getTitle().')</option>';

                     }

                     return $val;

                }
                
                function addHeaderCode() {
                           global $user_ID;

                    if (function_exists('wp_enqueue_script')) {
                             wp_enqueue_script('seoatl_on_site_ga_d', get_bloginfo('wpurl') . '/wp-content/plugins/onsite-google-analytics-plugin/js/jquery.url.packed.js', array('jquery'), '0.1');
                    }
                     //$user_info = get_userdata($user_ID);
                     if (!self::checkUserPriviledges()) {
                         
                         return;
                         
                     }
                     echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/onsite-google-analytics-plugin/css/seoatl-onsite-ga.css" />' . "\n";
                     echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/onsite-google-analytics-plugin/css/tipsy.css" />' . "\n";
                    if (function_exists('wp_enqueue_script')) {
                        wp_enqueue_script('seoatl_on_site_ga_c', get_bloginfo('wpurl') . '/wp-content/plugins/onsite-google-analytics-plugin/js/jquery-ui-1.8.2.custom.min.js', array('jquery'), '0.1');
                        wp_enqueue_script('seoatl_on_site_ga_b', get_bloginfo('wpurl') . '/wp-content/plugins/onsite-google-analytics-plugin/js/jquery.tipsy.js', array('jquery'), '0.1');
                        wp_enqueue_script('seoatl_on_site_ga_a', get_bloginfo('wpurl') . '/wp-content/plugins/onsite-google-analytics-plugin/js/seoatl-onsite-ga.js.php', array('jquery'), '0.1');
                      
                        
                    }
                    

                    $seoatlOptions = $this->getAdminOptions();

                    if ($seoatlOptions['show_header'] == "false") { return; }

   
                     
                }

                function addFooterCode() {
                  $seoatlOptions = $this->getAdminOptions();
                    global $user_ID;

                    //add jquery form tracking
                    ?>
<?php if ($seoatlOptions['ga_form']) :?>
<script type="text/javascript">
 var xcount=0;
 function seoAtlTimer() {
     var delay = 1000;
     xcount = xcount + 1;
     setTimeout("seoAtlTimer()",delay);

 }

  jQuery(document).ready(function() {
    seoAtlTimer(0);
    var currentPage = jQuery.url.attr("path");
    jQuery(':input').change(function () {

        <?php if ($seoatlOptions['ga_version']!='Asynchronous') : ?>

             pageTracker._trackEvent("Form: " + currentPage, jQuery(this).parents("form").attr("action"), jQuery(this).attr('name')+"|unique0808|"+jQuery(this).val(),xcount);
       <?php else :?>
            //async
            _gaq.push(['_trackEvent', "Form: " + currentPage, jQuery(this).parents("form").attr("action"), jQuery(this).attr('name')+"|unique0808|"+jQuery(this).val(),xcount])
        <?php endif; ?>
       
     
    });
  });
  </script>
<?php endif; ?>
        <?php

                     
                     if (!self::checkUserPriviledges()) {
                         return;
                     }
	             $seoatlOptions = $this->getAdminOptions();

                    
                  
               ?>
                 
                        <div id="onsite-ga-plugin" style="position:fixed;"><div id="onsite-ga-plugin-inner">
                     <img src="<?php bloginfo('wpurl') ?>/wp-content/plugins/onsite-google-analytics-plugin/images/ajax-loader.gif" />
                     <input type="hidden" id="seoatl_onsite_ga_request_uri" name="seoatl_onsite_ga_request_uri" value="<?php echo $_SERVER['REQUEST_URI'];?>" />
                </div></div><br /><br />
                
                <?php 
                //load the form data
               



                }


             


                function loadData($request_uri) {
                     global $user_ID;

                     if (!self::checkUserPriviledges()) {
                         return;
                     }
              
                    //magic
                    $seoatlOptions = $this->getAdminOptions();

                    switch ($seoatlOptions['ga_date_range']) {
                        case 'today':
                            $start_date = date('Y-m-d',strtotime("now"));
                            $end_date   = date('Y-m-d',strtotime("+1 day"));
                            $tip_date = 'today';
                            break;
                        case 'yesterday':
                            $start_date = date('Y-m-d',strtotime("-1 day"));
                            $end_date   = date('Y-m-d',strtotime("-1 day"));
                            $tip_date = 'yesterday';
                            break;
                        case 'month':
                            $start_date = date('Y-m-d',strtotime("-1 month"));
                            $end_date   = date('Y-m-d',strtotime("now"));
                            $tip_date = 'over the last 30 days';
                            break;
                        default:
                            $start_date = date('Y-m-d',strtotime("-1 month"));
                            $end_date   = date('Y-m-d',strtotime("now"));
                            $tip_date = 'over the last 30 days';
                            break;

                    }
                  


                     require_once('php/classes/gapi.class.php');
                     $ga = new gapi(null,null,$seoatlOptions['ga_token']);




		    
                     //http://code.google.com/apis/analytics/docs/gdata/gdataReferenceCommonCalculations.html
                     //bounce rate ga:bounces/ga:entrances
                     //avg time on page ga:timeOnPage/(ga:pageviews - ga:exits)
                     //exit rate ga:exits/ga:pageviews, ga:pagePath

                     $filter = 'pagePath == '.$request_uri; // -----live
                     
                      //$filter = 'pagePath ==/';
                   
                     $ga->requestReportData($seoatlOptions['ga_profile_id'],array('pagePath'),array('timeOnPage','pageviews','visits','entrances','bounces','exits'),null,$filter,$start_date,$end_date);
             
                     foreach($ga->getResults() as $result) {
                         if (($result->getPageviews() - $result->getExits()) > 0) {

                             $seconds = ($result->getTimeOnPage() / ($result->getPageviews() - $result->getExits()));

                         } else {
                             $seconds  = 0;
                         }
                        
                         $avg_time = floor($seconds/60) . ":" . $seconds % 60;
                         $views= $result->getPageviews();
                         if ($ga->getEntrances()>0) {
                             $bounce_rate = number_format(($ga->getBounces()/$ga->getEntrances())*100,2);
                         } else {
                             $bounce_rate = 0.00;
                         }

                         if ($ga->getPageviews()>0) {
                            $exit_rate = number_format(($ga->getExits()/$ga->getPageviews())*100,2);
                         } else {
                             $exit_rate = 0.00;
                         }
                         
                     }

                     unset($ga);
                     $ga = new gapi(null,null,$seoatlOptions['ga_token']);
                     $referral_filter = "medium==referral && pagePath==".$request_uri;
                     //get the referring sites
                     $ga->requestReportData($seoatlOptions['ga_profile_id'],array('source','referralPath','date'),array('visits'),'-date',$referral_filter,$start_date,$end_date);
                     $referring_visits=array();

                     foreach($ga->getResults() as $result)
                     {

                         $referring_visits[] = array('source'=>$result->getSource(),'path'=>$result->getReferralPath(),'visits'=>$result->getVisits() ) ;


                     }

                     $r_total_visits = $ga->getVisits();

                     unset($ga);
                     $ga = new gapi(null,null,$seoatlOptions['ga_token']);
                     $keyword_filter = "ga:keyword!=(not set) && pagePath==".$request_uri;
                     $ga->requestReportData($seoatlOptions['ga_profile_id'],array('keyword','source','medium'),array('visits'),'-visits',$keyword_filter,$start_date,$end_date);
                     $keywords = array();
                     foreach($ga->getResults() as $result)
                     {

                         $keywords[] = array('keyword'=>$result->getKeyword(),'source'=>$result->getSource(),'medium'=>$result->getMedium(),'visits'=>$result->getVisits());

                     }
                     $source=array();
                     $volume=array();
                     foreach($keywords as $key => $row) {
                         $source[$key]= $row['source'];
                         $volume[$key]= $row['visits'];



                     }

                     array_multisort($source, SORT_ASC, $volume, SORT_DESC, $keywords);

                     $total_visits = $ga->getVisits();
                     ?>
                     
                     <div class="onsite-ga-stat"><span class="label">Views: <?php echo $views; ?></span><a href="#" onclick="return false" title="The number of times this page has been viewed by website visitors <?php echo $tip_date; ?>." class="tipTip"><img width="12" height="12" src="<?php bloginfo('wpurl') ?>/wp-content/plugins/onsite-google-analytics-plugin/images/question.png" /></a></div>
                     <div class="onsite-ga-stat"><span class="label">Avg Time on Page: <?php echo $avg_time;?></span><a href="#" onclick="return false" title="The average amount of time visitors spent on this page" class="tipTip"><img width="12" height="12" src="<?php bloginfo('wpurl') ?>/wp-content/plugins/onsite-google-analytics-plugin/images/question.png" /></a></div>

                     <div class="onsite-ga-stat"><span class="label">Bounce Rate:  <?php echo $bounce_rate; ?></span><a href="#" onclick="return false" title="A bounce is determined by a single page visit to your site. The bounce rate is the number of bounces divided by the number of entrances." class="tipTip"><img width="12" height="12" src="<?php bloginfo('wpurl') ?>/wp-content/plugins/onsite-google-analytics-plugin/images/question.png" /></a></div>
                     <div class="onsite-ga-stat"><span class="label">Exit Rate: <?php echo $exit_rate; ?>%</span><a href="#" onclick="return false" title="The number of visitors who exited from your site on this page. The exit rate is calcuated by the number of exits divided by the number of page views." class="tipTip"><img width="12" height="12" src="<?php bloginfo('wpurl') ?>/wp-content/plugins/onsite-google-analytics-plugin/images/question.png" /></a></div>
                     <div class="onsite-ga-stat onsite-ga-hover" id="onsite-ga-referring-sites">
                         <?php if (count($referring_visits)>0) :?>
                         <div class="onsite-ga-referring-sites">
                             <ul>
                                 <?php foreach ($referring_visits as $site) :?>
                                 <li><a href="http://<?php echo $site['source']; ?><?php echo $site['path']; ?>" title="<?php echo $site['source']; ?><?php echo $site['path']; ?>" target="_blank"><?php echo $site['source']; ?></a>: <?php echo $site['visits'];?> Visit<?php if ( $site['visits']>1) echo 's'; ?></li>
                                 <?php endforeach; ?>
                             </ul>

                         </div>
                         <?php endif; ?>
                        <span class="label"><?php echo count($referring_visits); ?> Referring Sites Sent <?php echo $r_total_visits; ?> Visits</span><a href="#" onclick="return false" title="The 20 most recent websites that are currently driving traffic to your website via links <?php echo $tip_date;?>." class="tipTip"><img width="12" height="12" src="<?php bloginfo('wpurl') ?>/wp-content/plugins/onsite-google-analytics-plugin/images/question.png" /></a></div>
                     <div class="onsite-ga-stat onsite-ga-hover onsite-ga-stat-last" id="onsite-ga-keywords">
                         <?php if (count($keywords)>0) :?>
                         <div class="onsite-ga-keywords">

                               
                             <div id="onsite-ga-accordion">
                                 <?php foreach ($keywords as $key => $keyword) :?>

                                 <?php
                              
                                 
                                if ($keywords[$key-1]['source']!=$keyword['source']) {
              
                                 echo'<h3><a href="#">'.ucwords($keyword['source']).'</a></h3><div><ul>';
                                }
                                 ?>

                                 <li><b><?php echo $keyword['keyword'];?></b> (<?php echo $keyword['medium']; ?>): <?php echo $keyword['visits']; ?> </li>
                                 <?php

                                if ($keywords[$key+1]['source']!=$keyword['source']) {
                                 echo '</ul></div>';
                                }
                                
                                 ?>
                                 <?php endforeach; ?>
 
                             </div>
                         </div>
                         <?php endif; ?>


                                <script type="text/javascript">
	jQuery(function() {

 


                jQuery("div.onsite-ga-hover").bind("mouseenter",function(){
                    var id_class = jQuery(this).attr("id");
                  
                    jQuery("."+id_class).css("display","inline");

                    if (id_class=="onsite-ga-keywords") {

                               var icons = {
                                    header: "ui-icon-triangle-1-e",
                                    headerSelected: "ui-icon-triangle-1-s"
                              };
                              jQuery("#onsite-ga-accordion").accordion({
                                    icons: icons
                               });

                    }

                });

                jQuery(".onsite-ga-hover").bind("mouseleave", function(){
                     var id_class = jQuery(this).attr("id");

                    jQuery("."+id_class).css("display","none");
                })






	});
	</script>

                        <span class="label"><?php echo count($keywords); ?> Keywords Sent <?php echo $total_visits; ?> Visits</span> <a href="#" onclick="return false" title="The 20 most recent terms search engine visitors are using to find this page <?php echo $tip_date; ?>." class="tipTip"><img width="12" height="12" src="<?php bloginfo('wpurl') ?>/wp-content/plugins/onsite-google-analytics-plugin/images/question.png" /></a></div>
               <script type="text/javascript">
                     jQuery(function() {
                       jQuery('.tipTip').tipsy({gravity: 's'});
                     });
               </script>
                 <?php

            if ($seoatlOptions['ga_form'])  {
               try {

                 //populate form data
                $ga = new gapi(null,null,$seoatlOptions['ga_token']);
                $referral_filter = "eventCategory==Form: $request_uri";
               //get the referring sites
                $ga->requestReportData($seoatlOptions['ga_profile_id'],array('eventAction','eventLabel','eventCategory'),array('totalEvents','uniqueEvents','eventValue'),'-totalEvents',$referral_filter,$start_date,$end_date,1,10000);
               // echo $seoatlOptions['ga_profile_id'],array('eventAction','eventLabel','eventCategory'),array('uniqueEvents'),'-date',$referral_filter,$start_date,$end_date;


foreach ($ga->getResults() as $result) {

        
        $field = explode("|unique0808|",$result->getEventLabel());

        //see if the field exists
        if (@array_key_exists($field[0],$forms[$result->getEventAction()])) {
            //update the field

             $forms[$result->getEventAction()][$field[0]]["total_events"] = $forms[$result->getEventAction()][$field[0]]['total_events'] +$result->getUniqueEvents();
             $forms[$result->getEventAction()][$field[0]]['data'][]  = array('text'=>$field[1],'time'=>$result->getEventValue(),'total_events'=>$result->getTotalEvents());

        } else {


             $total_events = $result->getTotalEvents();
             $forms[$result->getEventAction()][$field[0]] = array("total_events"=>$result->getUniqueEvents(),"data"=>array(array('text'=>$field[1],"time"=>$result->getEventValue(),"total_events"=>$total_events)),"name"=>$field[0]);

        }
        //print_r( $forms[$result->getEventAction()][$field[0]]);
        //array("field"=>array("name"="author", "count"=>12, "data"=array("asdf","asdf")))
    

}


$first=true;
?>
<script type="text/javascript">
  
    jQuery("form").each(function(){
     
        <?php foreach ($forms as $key=>$form) :?>
<?php
//need to loop through the envents and find the greatest one with the most events
$max_events=0;
foreach ($form as $field ) {
    if ($field['total_events']>$max_events) {
    $max_events = $field['total_events'];
    }

}

?>
        
        if (jQuery(this).attr("action")=='<?php echo $key; ?>') {
            //we have a matching form
            //bind to each input, select, but, etc
            <?php foreach($form as $field) : ?>
                        var li = '';
              
                   var width = jQuery(this).find("[name$=<?php echo $field['name']; ?>]").width();
                <?php $total_time = 0; ?>
                <?php foreach ($field['data'] as $data) :?>
                      
                        li+='<li><?php echo $data['text'] ?> | <?php echo $data['total_events'] ?> Events | <?php echo number_format(($data['time']/$data['total_events']),2) ?> seconds</li>';
                        <?php $total_time = $data['time'] + $total_time; ?>
                <?php endforeach; ?>
                  <?php
                  $ratio =  $field['total_events']/$max_events;

                  if ($ratio > 0.75) {
                      $image = 'ok.png';
                      $color = 'green';
                  } elseif ($ratio>0.5 && $ratio<0.75) {
                      $image = 'warning.png';
                      $color = 'yellow';

                  } else {
                      $image = 'danger.png';
                      $color = 'red';
                  }


                  ?>
             <?php if (!empty($field['name']))  :?>
           
            jQuery(this).find("[name$=<?php echo $field['name']; ?>]").after("<div class=\"onsite-ga-field-values\" ><span style=\"border:1px solid #ccc;\"><b><?php echo $field['name']; ?></b>: Avg Time: <?php echo number_format($total_time/count($field['data']),2); ?> seconds| <?php echo $field['total_events']; ?> Total Events </span><ul>"+li+"</ul></span>");
            //fix any css issues
            jQuery(this).find("[name$=<?php echo $field['name']; ?>]").addClass('onsite-ga-field')
            <?php endif; ?>
            <?php endforeach; ?>
        }
        <?php endforeach; ?>
    })

jQuery(document).ready(function(){
    jQuery(".onsite-ga-field-values").hover(function(){
        jQuery(this).find("ul").slideDown();
    },function(){
        jQuery(this).find("ul").slideUp();
    })
})

</script>

<?php






               } catch(Exception $e) {
                    echo  $e;
               }    
            }//end seoatlOptions
                
                
                }
	}

   

} //End Class


//Initialize the admin panel
if (!function_exists("SeoatlOnSiteGa_ap")) {
    function SeoatlOnSiteGa_ap() {
        global $seoatl_onsite_ga_plugin;
        if (!isset($seoatl_onsite_ga_plugin)) {
            return;
        }
        if (function_exists('add_options_page')) {
            add_options_page('Seoatl Onsite Google Analytics', 'Onsite Google Analytics', 9, basename(__FILE__), array(&$seoatl_onsite_ga_plugin, 'printAdminPage'));
        }

    }
}



if (class_exists("SeoatlOnSiteGa")) {
	$seoatl_onsite_ga_plugin = new SeoatlOnSiteGa();
}



//Actions and Filters	
if (isset($seoatl_onsite_ga_plugin)) {
	//Actions


        
    	add_action('wp_head', array(&$seoatl_onsite_ga_plugin, 'addHeaderCode'), 1);
	add_action('wp_footer', array(&$seoatl_onsite_ga_plugin, 'addFooterCode'), 1);
        add_action('admin_menu', 'SeoatlOnSiteGa_ap');
        add_action('activate_seoatl-onsite-ga/seoatl-onsite-ga.php', array(&$seoatl_onsite_ga_plugin, 'init'));
         

        ////Filters
}


