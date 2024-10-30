<?php
/*
Plugin Name: Live Mobile Phone News Ticker
Plugin URI: http://www.omio.com
Description: The latest live mobile phone news
Author: Marcin Ciszak
Version: 1.0
Author URI: http://www.omio.com
*/   
   
/*  Copyright 2009  

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/**
* Guess the wp-content and plugin urls/paths
*/
// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

if (!class_exists('OmioNews')) {
    class OmioNews {
        /**
        * @var string The options string name for this plugin
        */
        var $optionsName = 'OmioNews_options';
        
        /**
        * @var string $localizationDomain Domain used for localization
        */
        var $localizationDomain = "OmioNews";
        
        /**
        * @var string $pluginurl The path to this plugin
        */ 
        var $thispluginurl = '';
        /**
        * @var string $pluginurlpath The path to this plugin
        */
        var $thispluginpath = '';
            
        /**
        * @var array $options Stores the options for this plugin
        */
        var $options = array();
        
        //Class Functions
        /**
        * PHP 4 Compatible Constructor
        */
        function OmioNews(){$this->__construct();}
        
        /**
        * PHP 5 Constructor
        */        
        function __construct(){
            //Language Setup
            $locale = get_locale();
            $mo = dirname(__FILE__) . "/languages/" . $this->localizationDomain . "-".$locale.".mo";
            load_textdomain($this->localizationDomain, $mo);

            //"Constants" setup
            $this->thispluginurl = PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)).'/';
            $this->thispluginpath = PLUGIN_PATH . '/' . dirname(plugin_basename(__FILE__)).'/';
            
            $this->getOptions();
						if(!isset($this->options['OmioNews_title'])) {
							$this->options['OmioNews_title'] = "Live Mobile Phone News";
						}
						if(!isset($this->options['OmioNews_width']) || empty($this->options['OmioNews_width'])) {
							$this->options['OmioNews_width'] = "180";
						}
						if(!isset($this->options['OmioNews_height'])) {
							$this->options['OmioNews_height'] = "500";
						}
						if(!isset($this->options['OmioNews_background_colour'])) {
							$this->options['OmioNews_background_colour'] = "#FFFFFF";
						}
						if(!isset($this->options['OmioNews_text_colour'])) {
							$this->options['OmioNews_text_colour'] = "#000000";
						}
						if(!isset($this->options['OmioNews_scroll_speed']) || empty($this->options['OmioNews_scroll_speed'])) {
							$this->options['OmioNews_scroll_speed'] = "800";
						}
						if(!isset($this->options['OmioNews_scroll_interval']) || empty($this->options['OmioNews_scroll_interval'])) {
							$this->options['OmioNews_scroll_interval'] = "3000";
						}
						if(!isset($this->options['OmioNews_number_of_news_visible']) || empty($this->options['OmioNews_number_of_news_visible'])) {
							$this->options['OmioNews_number_of_news_visible'] = "5";
						}
						if(!isset($this->options['OmioNews_link_target']) || empty($this->options['OmioNews_link_target'])) {
							$this->options['OmioNews_link_target'] = "_blank";
						}
						if(!isset($this->options['OmioNews_link_nofollow']) || empty($this->options['OmioNews_link_nofollow'])) {
							$this->options['OmioNews_link_nofollow'] = "";
						}
            
            add_action("admin_menu", array(&$this,"admin_menu_link"));
            
            add_action('plugins_loaded', array(&$this,'register_widgets'));
            
        }
        

        function parseOmioFeed($url, $num_items = 10, $pipe = false) {
					include_once(ABSPATH . WPINC . '/rss.php');
					$text_color = $this->options['OmioNews_text_colour'];
					$bg_color = $this->options['OmioNews_background_colour'];
	        $rss = fetch_rss($url);
	        if ( $rss ) {
						$items = $rss->items;
						if($pipe) {
							shuffle($items);
						}
						$items = array_slice($items, 0, $num_items);
						foreach ( (array) $items as $item ) {
							if($pipe) {
								$url = $item[link];
							} else {
								$url = str_replace('/#comment','',$item[comments]);
							}
							
							if(!$this->displayItem($url)) {
								continue;
							}
							
							$link_title = strip_tags($item[summary]);
							$link_target = ($this->options['OmioNews_link_target'] == 1) ? '_blank' : '_self';
							$link_nofollow = ($this->options['OmioNews_link_nofollow'] == 1) ? 'nofollow' : '';
							echo "<li>\n";
							echo "<a href='$url' title='$link_title' style='color:$text_color;background-color:$bg_color' target='$link_target' rel='$link_nofollow'>";
							echo $item['title'];
							echo "</a><br />\n";
							echo "</li>\n";
						}
	        } else {
	           return 'Sorry - News is not available at this time.';
	        }
				}
				
				
				function displayItem($url) {
					if (preg_match('/omio/i', $url)) return true;
					if (preg_match('/engadgetmobile/i', $url) && $this->options['OmioNews_feed_engadgetmobile'] == 1) return true;
					if (preg_match('/gsmarena/i', $url) && $this->options['OmioNews_feed_gsmarena'] == 1) return true;
					if (preg_match('/reghardware/i', $url) && $this->options['OmioNews_feed_reghardware'] == 1) return true;
					if (preg_match('/intomobile/i', $url) && $this->options['OmioNews_feed_intomobile'] == 1) return true;
					if (preg_match('/pocket-lint/i', $url) && $this->options['OmioNews_feed_pocketlint'] == 1) return true;
					if (preg_match('/unwiredview/i', $url) && $this->options['OmioNews_feed_unwiredview'] == 1) return true;
					return false;
				}


        /**
        * Retrieves the plugin options from the database.
        * @return array
        */
        function getOptions() {
            if (!$theOptions = get_option($this->optionsName)) {
                $theOptions = array('default'=>'options');
                update_option($this->optionsName, $theOptions);
            }
            $this->options = $theOptions;
        }
        /**
        * Saves the admin options to the database.
        */
        function saveAdminOptions(){
            return update_option($this->optionsName, $this->options);
        }
        
        /**
        * @desc Adds the options subpanel
        */
        function admin_menu_link() {
            add_options_page('Live Mobile Phone News Ticker', 'Live Mobile Phone News Ticker', 10, basename(__FILE__), array(&$this,'admin_options_page'));
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );
        }
        
        /**
        * @desc Adds the Settings link to the plugin activate/deactivate page
        */
        function filter_plugin_actions($links, $file) {
           $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
           array_unshift( $links, $settings_link );

           return $links;
        }
        
        /**
        * Adds settings/options page
        */
        function admin_options_page() { 
            if($_POST['OmioNews_save']){
                if (! wp_verify_nonce($_POST['_wpnonce'], 'OmioNews-update-options') ) die('Whoops! There was a problem with the data you posted. Please go back and try again.'); 
                $this->options['OmioNews_title'] = $_POST['OmioNews_title'];                   
                $this->options['OmioNews_background_colour'] = $_POST['OmioNews_background_colour'];
                $this->options['OmioNews_text_colour'] = $_POST['OmioNews_text_colour'];
                $this->options['OmioNews_scroll_speed'] = $_POST['OmioNews_scroll_speed'];
                $this->options['OmioNews_scroll_interval'] = $_POST['OmioNews_scroll_interval'];
                $this->options['OmioNews_width'] = $_POST['OmioNews_width'];
                $this->options['OmioNews_height'] = $_POST['OmioNews_height'];
                $this->options['OmioNews_number_of_news_visible'] = $_POST['OmioNews_number_of_news_visible'];
                $this->options['OmioNews_feed_engadgetmobile'] = ($_POST['OmioNews_feed_engadgetmobile']=='on')?true:false;
                $this->options['OmioNews_feed_gsmarena'] = ($_POST['OmioNews_feed_gsmarena']=='on')?true:false;
                $this->options['OmioNews_feed_reghardware'] = ($_POST['OmioNews_feed_reghardware']=='on')?true:false;
                $this->options['OmioNews_feed_intomobile'] = ($_POST['OmioNews_feed_intomobile']=='on')?true:false;
                $this->options['OmioNews_feed_pocketlint'] = ($_POST['OmioNews_feed_pocketlint']=='on')?true:false;
                $this->options['OmioNews_feed_unwiredview'] = ($_POST['OmioNews_feed_unwiredview']=='on')?true:false;
                $this->options['OmioNews_link_target'] = ($_POST['OmioNews_link_target']=='on')?true:false;
                $this->options['OmioNews_link_nofollow'] = ($_POST['OmioNews_link_nofollow']=='on')?true:false;
                                        
                $this->saveAdminOptions();
                
                echo '<div class="updated"><p>Success! Your changes were sucessfully saved!</p></div>';
            }

					?>
						
						<div class="wrap">
						<h2>Live Mobile Phone News Ticker</h2>
						<form method="post" id="OmioNews_options">
						<?php wp_nonce_field('OmioNews-update-options'); ?>
						    <table width="100%" cellspacing="2" cellpadding="5" class="form-table"> 
						        <tr valign="top"> 
						            <th width="33%" scope="row"><?php _e('Title:', $this->localizationDomain); ?></th> 
						            <td><input name="OmioNews_title" type="text" id="OmioNews_title" size="45" value="<?php echo $this->options['OmioNews_title'] ;?>"/>
						        </td> 
						        </tr>
						        <tr valign="top"> 
						            <th width="33%" scope="row"><?php _e('Background colour:', $this->localizationDomain); ?></th> 
						            <td><input name="OmioNews_background_colour" type="text" id="OmioNews_background_colour" value="<?php echo $this->options['OmioNews_background_colour'];?>"/> (default: #FFFFFF)
						        </td> 
						        </tr>
						        <tr valign="top"> 
						            <th width="33%" scope="row"><?php _e('Text colour:', $this->localizationDomain); ?></th> 
						            <td><input name="OmioNews_text_colour" type="text" id="OmioNews_text_colour" value="<?php echo $this->options['OmioNews_text_colour'];?>"/> (default: #000000)
						            </td> 
						        </tr>
						        <tr valign="top"> 
						            <th width="33%" scope="row"><?php _e('News scroll speed:', $this->localizationDomain); ?></th> 
						            <td><input name="OmioNews_scroll_speed" type="text" id="OmioNews_scroll_speed" value="<?php echo $this->options['OmioNews_scroll_speed'];?>"/>ms (default: 800 [ms])
						            </td> 
						        </tr>
						        <tr valign="top"> 
						            <th width="33%" scope="row"><?php _e('Scroll interval:', $this->localizationDomain); ?></th> 
						            <td><input name="OmioNews_scroll_interval" type="text" id="OmioNews_scroll_interval" value="<?php echo $this->options['OmioNews_scroll_interval'];?>"/>ms (default: 3000 [ms])
						            </td> 
						        </tr>
						        <tr valign="top"> 
						            <th width="33%" scope="row"><?php _e('News visible in the box:', $this->localizationDomain); ?></th> 
						            <td><input name="OmioNews_number_of_news_visible" type="text" id="OmioNews_number_of_news_visible" value="<?php echo $this->options['OmioNews_number_of_news_visible'];?>"/> (default: 5)
						            </td> 
						        </tr>
						        <tr valign="top"> 
						          <th><label for="OmioNews_link_target"><?php _e('Open links in new window:', $this->localizationDomain); ?></label></th><td><input type="checkbox" id="OmioNews_link_target" name="OmioNews_link_target" <?=($this->options['OmioNews_link_target']==true)?'checked="checked"':''?>> (by default link will be open in the same window)</td>
						        </tr>
										<tr valign="top"> 
						            <th><label for="OmioNews_link_nofollow"><?php _e('`nofollow` links:', $this->localizationDomain); ?></label></th><td><input type="checkbox" id="OmioNews_link_nofollow" name="OmioNews_link_nofollow" <?=($this->options['OmioNews_link_nofollow']==true)?'checked="checked"':''?>> (by default all links are followed)</td>
						        </tr>

										<tr>
										<td>
										<fieldset border="1">
										 <legend>News feeds:</legend>
										 <table>
										 <tr valign="top"> 
										                            <th><label for="OmioNews_feed_engadgetmobile"><?php _e('Engadget Mobile', $this->localizationDomain); ?></label></th><td><input type="checkbox" id="OmioNews_feed_engadgetmobile" name="OmioNews_feed_engadgetmobile" <?=($this->options['OmioNews_feed_engadgetmobile']==true)?'checked="checked"':''?>></td>
										                        </tr>
																					 	<tr valign="top"> 
										                            <th><label for="OmioNews_feed_gsmarena"><?php _e('GSM Arena', $this->localizationDomain); ?></label></th><td><input type="checkbox" id="OmioNews_feed_gsmarena" name="OmioNews_feed_gsmarena" <?=($this->options['OmioNews_feed_gsmarena']==true)?'checked="checked"':''?>></td>
										                        </tr>
																				 		<tr valign="top"> 
										                            <th><label for="OmioNews_feed_reghardware"><?php _e('The Register', $this->localizationDomain); ?></label></th><td><input type="checkbox" id="OmioNews_feed_reghardware" name="OmioNews_feed_reghardware" <?=($this->options['OmioNews_feed_reghardware']==true)?'checked="checked"':''?>></td>
										                        </tr>
																				 		<tr valign="top"> 
										                            <th><label for="OmioNews_feed_intomobile"><?php _e('Into Mobile', $this->localizationDomain); ?></label></th><td><input type="checkbox" id="OmioNews_feed_intomobile" name="OmioNews_feed_intomobile" <?=($this->options['OmioNews_feed_intomobile']==true)?'checked="checked"':''?>></td>
										                        </tr>
																				 		<tr valign="top"> 
										                            <th><label for="OmioNews_feed_pocketlint"><?php _e('Pocket-lint.com', $this->localizationDomain); ?></label></th><td><input type="checkbox" id="OmioNews_feed_pocketlint" name="OmioNews_feed_pocketlint" <?=($this->options['OmioNews_feed_pocketlint']==true)?'checked="checked"':''?>></td>
										                        </tr>
																				 		<tr valign="top"> 
										                            <th><label for="OmioNews_feed_unwiredview"><?php _e('Unwired View', $this->localizationDomain); ?></label></th><td><input type="checkbox" id="OmioNews_feed_unwiredview" name="OmioNews_feed_unwiredview" <?=($this->options['OmioNews_feed_unwiredview']==true)?'checked="checked"':''?>></td>
										                        </tr>
										</table>
										</fieldset>
										</td>
										</tr>


						        <tr valign="top"> 
						            <th width="33%" scope="row"><?php _e('Width:', $this->localizationDomain); ?></th> 
						            <td><input name="OmioNews_width" type="text" id="OmioNews_width" value="<?php echo $this->options['OmioNews_width'];?>"/>px. (default: 180)
						            </td> 
						        </tr>
						        <!--<tr valign="top"> 
						            <th width="33%" scope="row"><?php _e('Height:', $this->localizationDomain); ?></th> 
						            <td><input name="OmioNews_height" type="text" id="OmioNews_height" value="<?php echo $this->options['OmioNews_height'];?>"/>px. (default: 500)
						            </td> 
						        </tr>//-->
						        <tr>
						            <th colspan=2><input type="submit" name="OmioNews_save" value="Save" /></th>
						        </tr>
						    </table>
						</form>
						
					<?php
        }


				//============================
        //Live Mobile Phone News Ticker Widget
        //============================
        function display_omio_news($args) {                    
            extract($args);
            echo $before_widget . $before_title . $this->options['OmioNews_title'] . $after_title;
						echo '<link rel="stylesheet" href="wp-content/plugins/omio_news/css/omio_news.css" type="text/css" media="screen"/>';
						echo '<script src="wp-content/plugins/omio_news/js/jquery-1.3.2.min.js" type="text/javascript"></script>';
						echo '<script src="wp-content/plugins/omio_news/js/jcarousellite_1.0.1.pack.js" type="text/javascript"></script>';
						echo "<script type='text/javascript' charset='utf-8'>
						$(document).ready(function() {  
						$('.omio_news_carousel').jCarouselLite({  
						   vertical: true,
						   visible: {$this->options['OmioNews_number_of_news_visible']},  
						   auto: {$this->options['OmioNews_scroll_interval']},  
						   speed: {$this->options['OmioNews_scroll_speed']}
						  });  
						});
						</script>";
						echo '<div id="omio_news_newsticker">';
						echo '<div class="omio_news_carousel">';
            echo '<ul>';
						$this->parseOmioFeed('http://pipes.yahoo.com/pipes/pipe.run?_id=dd659c6ba9047b7132ea52babbfd0563&_render=rss', 50, true);
            echo '</ul>';
						echo '</div>';
						echo '</div>';
						echo '<div style="float:right;"><a href="http://www.omio.com"><img src="wp-content/plugins/omio_news/img/powered_by.png" border="0" alt="Omio Mobile Phone Deals" title="Omio Mobile Phone Deals"></a></div>';
						echo "<style type='text/css' media='screen'>
							#omio_news_newsticker {
								width:{$this->options['OmioNews_width']}px;
								background: {$this->options['OmioNews_background_colour']};
							}
							.omio_news_carousel { width: {$this->options['OmioNews_width']}px; }
						</style>";
			      echo $after_widget;
        }                           
                                                  
        function omio_news_control() {            
        }
        
        /*
        * ============================
        * Plugin Widgets
        * ============================
        */                        
        function register_widgets() {
            if ( function_exists('wp_register_sidebar_widget') ) {
                $widget_ops = array('classname' => 'OmioNews', 'description' => 'Live Mobile Phone News Ticker Widget' );
                wp_register_sidebar_widget('OmioNews-omio_news', 'Live Mobile Phone News Ticker Widget', array($this, 'display_omio_news'), $widget_ops);
                wp_register_widget_control('OmioNews-omio_news', 'Live Mobile Phone News Ticker Widget', array($this, 'omio_news_control'));
            }  
        }       
        
  } //End Class
} //End if class exists statement

if (class_exists('OmioNews')) {
    $OmioNews_var = new OmioNews();
}
?>