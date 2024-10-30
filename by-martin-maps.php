<?php
/*
Plugin Name: By-Martin Maps
Plugin URI: http://wordpress.org/extend/plugins/by-martin-maps/
Description: A Google Maps integration for Blog
Version: 1.0.2
Author: Martin Hermosilla
Author URI: http://www.by-martin.com/
License: GPL2

    Copyright 2010  Martin Hermosilla  (email : info@by-martin.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

require_once(ABSPATH . 'wp-admin/includes/media.php');

if ( get_option('maps_apikey')  && 
     !get_option('maps_width')  && 
     !get_option('maps_height') &&
     !get_option('maps_file')   &&
     !get_option('maps_sensor') &&
     !get_option('maps_version') &&
     !get_option('maps_title') &&
     !get_option('maps_use_ajax') &&
     !get_option('maps_control') &&
     !get_option('maps_wheel') &&
     !get_option('maps_maptype') &&
     !get_option('maps_overview') &&
     !get_option('maps_scale') &&
     !get_option('maps_custjs')
) {
  update_option('maps_apikey', get_option('maps_apikey'));
  update_option('maps_width', '650');
  update_option('maps_height', '415');
  update_option('maps_file', 'api');
  update_option('maps_sensor', 'false');
  update_option('maps_version', '2');
  update_option('maps_title', '');
  update_option('maps_use_ajax', 'false'); 
  update_option('maps_control', 'none');
  update_option('maps_wheel', 'false');
  update_option('maps_maptype', 'false');
  update_option('maps_overview', 'false');
  update_option('maps_scale', 'false');
  update_option('maps_custjs', '');
}

// Adding Admin menu
$map_w = "";
$map_h = "" ;

if ( is_admin() ) {
   add_action('admin_menu', 'add_options_menu');
   add_action('admin_menu', 'add_metabox_map');
   add_action('admin_init', 'register_settings' );
   add_action('plugins_loaded', 'by_martin_map_init');
   getMapsApiScript( (get_option('maps_use_ajax')=='true'), 'admin_head' );
} else {
   register_sidebar_widget(__('By-Martin Map Widget'), 'widget_by_martin_map');
   getMapsApiScript( (get_option('maps_use_ajax')=='true'), 'wp_head' );
}



/* Adds Settings Options menu... */
function add_options_menu() {
     add_options_page(__('By-Martin Map Options'), 'By-Martin Map', 8, __FILE__, 'options_page');
}

function add_metabox_map() {
   if ( function_exists('add_meta_box') ) {
        add_meta_box('static_map', __('Add Static Map', 'static_map' ),  'addPostMapGadget', 'post', 'advanced');
    	add_meta_box('static_map', __('Add Static Map', 'static_map' ),  'addPostMapGadget', 'page', 'advanced');
   }
}

function register_settings() {
  register_setting( 'maps-option-group', 'maps_apikey' );
  register_setting( 'maps-option-group', 'maps_width' );
  register_setting( 'maps-option-group', 'maps_height' );
  register_setting( 'maps-option-group', 'maps_version' );
  register_setting( 'maps-option-group', 'maps_sensor' );
  register_setting( 'maps-option-group', 'maps_file' );
  register_setting( 'maps-option-group', 'maps_use_ajax' );
  register_setting( 'maps-option-group', 'maps_control' );
  register_setting( 'maps-option-group', 'maps_wheel' );
  register_setting( 'maps-option-group', 'maps_maptype' );
  register_setting( 'maps-option-group', 'maps_overview' );
  register_setting( 'maps-option-group', 'maps_scale' );
  register_setting( 'maps-option-group', 'maps_custjs' );
}

function by_martin_map_init() {
      register_sidebar_widget(__('By-Martin Map Widget'), 'widget_by_martin_map');
      register_widget_control(__('By-Martin Map Widget'), 'widget_options_page', 300, 400);
}


function widget_by_martin_map($args) {
    extract($args);

    echo '<!-- Start Widget -->' . "\n";
    echo $before_widget . "\n";
    if(get_option('maps_title')!='') {
         echo $before_title . get_option('maps_title') . $after_title. "\n";
    }
    displayMap();
    echo $after_widget . "\n";
    echo '<!-- End of Widget -->' . "\n";
}

function ajax_map_inject() {
?>
     <script type="text/javascript">
     //<![CDATA[
       google.load("maps", "<?php echo get_option('maps_version');?>",{"other_params":"sensor=<?php echo get_option('maps_sensor');?>"});

      <?php javascript_init(); ?>

       google.setOnLoadCallback(initialize);
     //]]>
     </script>
<?php
}

function javascript_init() {
    $mapcontrol = 'map.addControl(new '. get_option('maps_control') . '());' . "\n";
    $wheel    = 'map.enableScrollWheelZoom();' . "\n";
    $maptype  = 'map.addControl(new GMapTypeControl());' . "\n";
    $overview = 'map.addControl(new GOverviewMapControl());' . "\n";
    $scale    = 'map.addControl(new GScaleControl());' . "\n";

?>
       var map;       
       function isFdefined( fvar ) {
          return ( eval( "typeof " + fvar + "=='function'") );
       }

       function initialize() {
         map = new google.maps.Map2(document.getElementById("map"));
         map.setCenter(new google.maps.LatLng(<?php echo get_option('maps_lat'); ?>, <?php echo get_option('maps_lng'); ?>), <?php echo get_option('maps_zoom'); ?>);
         <?php if( get_option('maps_control') != 'none' ) echo $mapcontrol; ?>
         <?php if( get_option('maps_wheel') == 'true' ) echo $wheel; ?>
         <?php if( get_option('maps_maptype') == 'true' ) echo $maptype; ?>
         <?php if( get_option('maps_overview') == 'true' ) echo $overview; ?>
         <?php if( get_option('maps_scale') == 'true' ) echo $scale; ?>

	 // we eval if custom_init is implemented
         if( isFdefined( 'custom_init' )  ) {
	     custom_init();
	 }
       }

<?php
      if ( is_admin() ) {
        adminJScript();
      } else { 
        if( get_option('maps_custjs') != '' ) echo get_option('maps_custjs');
      }

}

function simple_init() {
?>
     <script type="text/javascript">
     //<![CDATA[
     <?php javascript_init(); ?>

     // Onload callback
     window.onload = initialize;
     //]]>
     </script>
<?php     
}

function getMapsApiScript($ajax, $which_head) {
     wp_enqueue_script('jquery');
     wp_enqueue_script('jquery-ui-core');

     wp_deregister_script('google_maps');
     if($ajax) {
         $script_url  = 'http://www.google.com/jsapi?key=';
         $script_url .= get_option('maps_apikey');
         wp_register_script('google_maps', $script_url);
         wp_enqueue_script('google_maps');
         add_action($which_head, 'ajax_map_inject');
     } else {
         $script_url  = 'http://maps.google.com/maps?file=';
         $script_url .= get_option('maps_file');
         $script_url .= '&amp;v=';
         $script_url .= get_option('maps_version');
         $script_url .= '&amp;sensor=';
         $script_url .= get_option('maps_sensor');
         $script_url .= '&amp;key=';
         $script_url .= get_option('maps_apikey');
         wp_register_script('google_maps', $script_url);
         wp_enqueue_script('google_maps');
         add_action($which_head, 'simple_init');
     }

}

function options_page() {
  $bgColor     = "#ddd";
  $borderColor = "#ddd";
  $h3bgColor   = "#ddd";
  $hoverColor  = "#bbb";
?>
<style type="text/css">
.wrap {
     background-color: <?php echo $bgColor; ?>;
}
.wrap h3 { 
     color: black;
     background-color: <?php echo $h3bgColor; ?>;
     padding: 4px 8px; 
}
.sidebar {
     width: 200px;
     height: 100%;
     float: left;
     padding: 0px 20px 0px 10px;
}
.main {
     border-left: 2px solid <?php echo $borderColor ; ?>;
     float: left;
     width: 800px;
     padding: 0px 0px 0px 20px;
}
#bar {
    border-bottom: 1px solid #fff;
    height: 24px;
}
#tabs {
    width: 100%;
    position: relative;
}
#tab_bar {
	position:relative;
	float:left;
	width:100%;
         height: 24px;
	padding:0 0 1.75em 1em;
	margin:0;
	list-style:none;
	line-height:1em;
}
#tab_bar LI {
	float:left;
	margin:0;
	padding:0;
}

#tab_bar A {
	display:block;
	color:#444;
	text-decoration:none;
	font-weight:bold;
	background: <?php echo $bgColor; ?>;
	margin:0;
	padding:0.25em 1em;
	border-left:1px solid #fff;
	border-top:1px solid #fff;
	border-right:1px solid #aaa;
}

#tab_bar A:hover,
#tab_bar A:active,
#tab_bar A.here:link,
#tab_bar A.here:visited {
	background: <?php echo $hoverColor ; ?>;
}
#tab_bar A.here:link,
#tab_bar A.here:visited {
	position:relative;
	z-index:102;
}
.hide {
   display: none;
}
.show {
   display: block;
}
#getKey {
   position: absolute;
   width: 800px;
   height: 600px;
   top: 65px;
   left: 430px;
   z-index: 1000;
   background-color: #fff;
   border: 3px solid #21759B;
}
#gktitle {
   text-align:left;
   background-color: #21759B;
   color: #fff;
   font-weight: bold;
   padding: 2px;
}
#gkclose {
   position: absolute;
   right: 0;
   text-align:right;
   background-color: #21759B;
   padding: 2px;
   top:0;
} 
#gkclose a {
   color: #fff;
   padding: 2px;
   text-decoration: none;
   font-weight: bold;
}
#gkifrm {
   width: 800px;
   height: 578px;
}
</style>
<script type="text/javascript">
    var current_tab="#tab1";
    function pickTab(tab, obj) {
         jQuery(tab).show();
         jQuery(current_tab).hide();
         jQuery('A.here').removeClass('here');
         obj.className = 'here';
         current_tab = tab;
    }
    function showGetKey() {
          jQuery('#getKey').show('slow');
    }
    function closeGetKey() {
          jQuery('#getKey').hide('slow');
    }
</script>
     <div class="wrap">
         <div class="sidebar">
             <h3>Plugin</h3>
<p>
              <ul>
                  <li><a href="http://www.by-martin.com/">Author Homepage</a></li>
                  <li><a href="http://www.by-martin.com/productos/by-martin-map-wp-plugin/">Plugin's Homepage</a></li>
              <ul>
</p>
             <h3>Donation</h3>
<p>
<div style="text-align:center;">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
</div>A little aid to maintain this code would be highly appreciated.<div>
</div>
</p>           
         </div>
         <div class="main">
          <h2>By Martin Map Options</h2>
          <div id="bar">
            <ul id="tab_bar">
                <li><a class="here" href="#" onClick="pickTab('#tab1', this);">Google Maps API Config</a></li>
                <li><a href="#" onClick="pickTab('#tab2', this);">JavaScript Code</a></li>
            </ul>
          </div>

          <div id="tabs">
          <form method="post" action="options.php">
          <?php settings_fields('maps-option-group'); ?>
          
            <div id="tab1">
            <table class="form-table">
               <tr>
                   <th scope="row" valign="top">Maps API Key</th>
                   <td>
                       <input id="maps_apikey" name="maps_apikey" value="<?php echo get_option('maps_apikey'); ?>" />
                       <label for="maps_apikey">
		          <strong><em>Maps API Key (if you don't have one singup for one <a href="JavaScript:showGetKey();">here</a>)</em></strong>
		       </label>
                   </td>
                </tr>
               <tr>
                   <th scope="row" valign="top">Map Width</th>
                   <td>
                       <input id="maps_width" name="maps_width" value="<?php echo get_option('maps_width'); ?>" />
                       <label for="maps_width"><strong><em>Maps Width in px</em></strong></label>
                   </td>
                </tr>
               <tr>
                   <th scope="row" valign="top">Map Height</th>
                   <td>
                       <input id="maps_height" name="maps_height" value="<?php echo get_option('maps_height'); ?>" />
                       <label for="maps_height"><strong><em>Maps Height in px</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Map File</th>
                   <td>
                       <select style="width:145px" id="maps_file" name="maps_file">
                           <option value="api" <?php if(get_option('maps_file')=='api') echo 'selected'; ?>>api</option>
                       </select>
                       <label for="maps_file"><strong><em>Maps File (default api)</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Map Sensor</th>
                   <td>
                       <select style="width:145px" id="maps_sensor" name="maps_sensor">
		         <option value="true" <?php if(get_option('maps_sensor')=='true') echo 'selected'; ?>>true</option>
			 <option value="false" <?php if(get_option('maps_sensor')=='false') echo 'selected'; ?>>false</option>
		       </select>
                       <label for="maps_sensor"><strong><em>Maps Sensor (default false)</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Maps Version</th>
                   <td>
                       <select style="width:145px" id="maps_version" name="maps_version" >
                          <option value="2" <?php if(get_option('maps_version')=='2') echo 'selected'; ?>>2</option>
			  <option value="2.55" <?php if(get_option('maps_version')=='2.55') echo 'selected'; ?>>2.55</option>
                       </select>
                       <label for="maps_version"><strong><em>Maps Version (default 2)</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Use Ajax API</th>
                   <td>
                       <input type="checkbox" id="maps_use_ajax" name="maps_use_ajax" value="true" <?php if( get_option('maps_use_ajax')=='true') echo 'checked=checked'; ?> />
                       <label for="maps_use_ajax"><strong><em>Use Ajax API  (for version 2)</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Map Control (Nav/Zoom)</th>
                   <td>
                       <select id="maps_control" name="maps_control">
                          <option value="none" <?php if(get_option('maps_control')=='none') echo 'selected'; ?>>No map control </option>
                          <option value="MapControl3D" <?php if(get_option('maps_control')=='MapControl3D') echo 'selected'; ?>>3D</option>
                          <option value="GLargeMapControl" <?php if(get_option('maps_control')=='GLargeMapControl') echo 'selected'; ?>>Large</option>
                          <option value="GSmallMapControl" <?php if(get_option('maps_control')=='GSmallMapControl') echo 'selected'; ?>>Small</option>
                          <option value="GSmallZoomControl3D" <?php if(get_option('maps_control')=='GSmallZoomControl3D') echo 'selected'; ?>>Small Zoom 3D</option>
                          <option value="GSmallZoomControl" <?php if(get_option('maps_control')=='GSmallZoomControl') echo 'selected'; ?>>Small Zoom</option>
                       </select>
                       <label for="maps_control"><strong><em>Choose map control</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Use mouse wheel zoom</th>
                   <td>
                       <input type="checkbox" id="maps_wheel" name="maps_wheel" value="true" <?php if( get_option('maps_wheel')=='true') echo 'checked=checked'; ?> />
                       <label for="maps_wheel"><strong><em>Use mouse scroll wheel for zoom</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Use map type tool</th>
                   <td>
                       <input type="checkbox" id="maps_maptype" name="maps_maptype" value="true" <?php if( get_option('maps_maptype')=='true') echo 'checked=checked'; ?> />
                       <label for="maps_maptype"><strong><em>Enable map type tool</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Use map overview tool</th>
                   <td>
                       <input type="checkbox" id="maps_overview" name="maps_overview" value="true" <?php if( get_option('maps_overview')=='true') echo 'checked=checked'; ?> />
                       <label for="maps_overview"><strong><em>Use map overview tool</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Use map scale tool</th>
                   <td>
                       <input type="checkbox" id="maps_scale" name="maps_scale" value="true" <?php if( get_option('maps_scale')=='true') echo 'checked=checked'; ?> />
                       <label for="maps_scale"><strong><em>Use map scale tool</em></strong></label>
                   </td>
                </tr>
            </table>
            </div>

            <div id="tab2" class="hide">  
                <div>Custom Javascript code</div>
                <div>
                       <textarea rows="30" cols="115" id="maps_custjs" name="maps_custjs">
                       <?php if ( !get_option('maps_custjs') ) { ?>
                        // Here you may put your own code.
                        // if you want to add your own initialization code
                        // uncomment the following function signature

                        // function custom_init() {
                        // }
		       <?php } else { 
                           echo get_option('maps_custjs');
                       } ?>
		       
		       </textarea>
                       <label for="maps_custjs" style="vertical-align:top;"><strong><em>Write your own Javascript code without script tags</em></strong></label>
               </div>
            </div>

            <div id="plug_footer">
               <p class="submit">
                 <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
               </p>
              <p style="text-align: right;">
                 &copy; Copyright 2010 <a href="http://www.by-martin.com/">By Martin</a>
              </p>
           </div>
      </form>
      </div>
     </div>

      <div id="getKey" class="hide">
           <div id="gktitle">Sing int to get Key</div>
           <div id="gkclose"><a href="javascript:closeGetKey();">X</a></div>
           <iframe id="gkifrm" src="http://code.google.com/apis/maps/signup.html"></iframe>
      </div>
 </div>
<?php
}
 
/* Display Options menu on Widget manager */
function widget_options_page() {
 if ($_POST['maps_submit']) {  
     update_option("maps_title", $_POST['maps_title']);
     update_option("maps_width", $_POST['maps_width']);
     update_option("maps_height", $_POST['maps_height']);
     update_option('maps_lat', $_POST['maps_lat']);
     update_option('maps_lng', $_POST['maps_lng']);
     update_option('maps_zoom', $_POST['maps_zoom']);
 }  
?>
     <div class="wrap">
         <h2>By Martin Map Options</h2>
            <table class="form-table">
	    
               <tr>
                   <th scope="row" valign="top">Title</th>
                   <td>
                       <input id="maps_title" name="maps_title" value="<?php echo get_option('maps_title'); ?>" />
                       <label for="maps_title"><strong><em>Title</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Map Width</th>
                   <td>
                       <input id="maps_width" name="maps_width" value="<?php echo get_option('maps_width'); ?>" />
                       <label for="maps_width"><strong><em>Maps Width in px</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Map Height</th>
                   <td>
                       <input id="maps_height" name="maps_height" value="<?php echo get_option('maps_height'); ?>" />
                       <label for="maps_height"><strong><em>Maps Height in px</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Map Center<br />(Lat, Lng)< /th>
                   <td>
                       <input id="maps_lat" name="maps_lat" value="<?php echo get_option('maps_lat'); ?>" />
                       <input id="maps_lng" name="maps_lng" value="<?php echo get_option('maps_lng'); ?>" />
                       <label for="maps_lng"><strong><em>Center Map at Lat, Lng</em></strong></label>
                   </td>
                </tr>

               <tr>
                   <th scope="row" valign="top">Map Initial Zoom</th>
                   <td>
                       <input id="maps_zoom" name="maps_zoom" value="<?php echo get_option('maps_zoom'); ?>" />
                       <label for="maps_zoom"><strong><em>Map initial zoom level (0 - 17)</em></strong></label>
                   </td>
                </tr>

            </table>
            <input type="hidden" id="maps_submit" name="maps_submit" value="1" /> 
   </div>
<?php
}

/* Display Widget itself on Web Page */
function displayMap() {
?>    <div id="map_wrapper" style="height: 100%;overflow: auto;"> 
           <div id="map" style="width: <?php echo get_option('maps_width');?>px;height: <?php echo get_option('maps_height')?>px;"></div>
      </div>
<?php
}

function postMap() {
 $pmap  = '<div id="map_wrapper" style="height: 100%;overflow: auto;">' . "\n";
 $pmap .= '  <div id="map" style="width: 350px;height: 250px;"></div>' . "\n";
 $pmap .= '</div>' . "\n";
 echo $pmap;
}

function genStaticMap() {
    $lat     = "";
    $lng     = "";
    $zoom    = "";
    $width   = "";
    $height  = ""; 
    $maptype = ""; // roadmap
    $sensor  = ""; // true/false
    $key     = get_option('maps_apikey');
    $label   = "";
    $color   = "";

    $statMapUrl  = 'http://maps.google.com/maps/api/staticmap?center=' . $lat . ',' . $lng;
    $statMapUrl .= '&zoom=$zoom&size=' . $width .'x'. $height;
    $statMapUrl .= '&maptype=' . $maptype;
    $statMapUrl .= '&markers=color:' . $color . '|label:' . $label . '|'. $lat . ',' . $lng; // this piece of crap has to repeat for multiple markers
    $statMapUrl .= '&sensor=' . $sensor;
    $statMapUrl .= '&key=' . $key;

    $statMap = '<img src="' . $statMapUrl . '" alt="" />';

    return $statMap;
}

function adminJScript() {
?> 
     var geocoder;
     var enable_report = false;

     function showAddress(response) {
          if(!response || response.Status.code != 200) {
              jQuery('#smap_Address').val('Status Code: ' + response.Status.code);
          } else {
               place = response.Placemark[0];
               jQuery('#smap_address').val(place.address);
          }
     }

     var markers;
     
     function getCoordinates() {
        var address = document.getElementById('smap_address').value;
        var lat;
        var lng;
        geocoder.getLatLng(
              address,
              function(point) {
                  if(!point) {
                     jQuery('#smap_Address').val(address + ' not found.');
                  } else {
                     map.setCenter(point, 13);

                     if(marker) { map.removeOverlay(marker); }

                     marker = new GMarker(point);
                     map.addOverlay(marker);
                     setLatLng(point);
                     geocoder.getLocations(point, showAddress);
                  }
              }
        );
     }

     function getCoordinatesFromText(event) {
           if (event.keyCode == 13) {
                getCoordinates();
           }
     }

     function setLatLng(point) {  
         var matchll = /\(([-.\d]*), ([-.\d]*)/.exec( point );
         if ( matchll ) { 
           lat = parseFloat( matchll[1] );
           lng = parseFloat( matchll[2] );
           lat = lat.toFixed(6);
           lng = lng.toFixed(6);   
           jQuery('#smap_lat').val(lat);
           jQuery('#smap_lng').val(lng);    
           jQuery('#smap_dlat').html(lat);
           jQuery('#smap_dlng').html(lng);    
	   jQuery('#smap_zoom').val(map.getZoom());
         }

     }

     function getLatLngString(lat,lng,zoom)
     {
          // convert decimal latitude, longitude to compass degree minute second
	  var t = "";
	  t += Math.floor(Math.abs(lat))+"&#176; ";
	  var m = Math.floor(60*(Math.abs(lat)%1));
	  var s = (60*(60*(Math.abs(lat)%1)%1)).toFixed(2);
	  if (60 == Math.floor(s))
	  {
	       m = 1+m;
	       s = (60*s%1).toFixed(2);
	  }
	  
	  t += m+"' "+s+'"';
	  t += ((0 == lat) ? "" : ((0 < lat) ? " N" : " S"))+", "
	  t += Math.floor(Math.abs(lng))+"&#176; ";
	  m = Math.floor(60*(Math.abs(lng)%1));
	  s = (60*(60*(Math.abs(lng)%1)%1)).toFixed(2);
	  if (60 == Math.floor(s))
	  {
   	       m = 1+m;
	       s = (60*s%1).toFixed(2);
	  }
	  t += m+"' "+s+'" ';
	  t += (0 == lng) ? "" : ((0 < lng) ? "E" : "W")
	  t += " ("+lat+","+lng;
	  if (zoom)
	  t += " zoom:"+zoom;
	  t += ")";
	  return t;
     }

     function createURL() {
        var statMapUrl = '';
        statMapUrl  = 'http://maps.google.com/maps/api/staticmap?center=' + map.getCenter().lat() + ',' + map.getCenter().lng();
        statMapUrl += '&zoom=' + jQuery('#smap_zoom').val() + '&size=' + jQuery('#smap_width').val() + 'x' + jQuery('#smap_height').val();
        statMapUrl += '&maptype=' + jQuery('#smap_mapType').val();
	for( var  i = 0; i < markers.length; i++ )
	{
           statMapUrl += markers[i];
	   //'&markers=color:' . $color . '|label:' . $label . '|'. $lat . ',' . $lng;
	   // this piece of crap has to repeat for multiple markers
	}
        statMapUrl += '&sensor=' + jQuery('#smap_sensor').val();
        statMapUrl += '&key="<?php echo  get_option('maps_apikey'); ?>"';
	
	return statMapUrl;

        //var statMap = '<img src="' + statMapUrl + '" alt="Static Map" />';
	//return statMap;
     }

     function addMarkerToArray() {
        var label = jQuery('#smap_markerLabel').val();
	var lat   = jQuery('#smap_lat').val();
	var lng   = jQuery('#smap_lng').val();
	var color = jQuery('#smap_markerColor').val();
        var mark  = '&markers=color:' + color + '|label:' + label + '|' + lat + ',' + lng;
        markers.push(mark);
     }

     function getLetter(index) {
        return String.fromCharCode("A".charCodeAt(0) + index);
     }
     
     var baseIcon = new GIcon(G_DEFAULT_ICON);
     baseIcon.iconSize = new GSize(21,34);
     function getColor(color) {
         var c = 'ff0000';

         switch(color) {
	   case "red":
	    c = 'ff0000';
	    break;
	   case "green":
	     c = '009900';
	     break;
	   case "blue":
	     c = '0099ff';
	     break;
	   case "yellow":
	     c = 'ffff00';
	     break;
	 }

	 return c;
     }

     function createMarker(point, index) {
         var letter = getLetter( index );
	 var letteredIcon = new GIcon(baseIcon);
	 var color = getColor(jQuery('#smap_markerColor').val());
	 var labelColor = '000000';
	 var pinProgram = "pin";
	 var label = letter;
	 var baseUrl = "http://chart.apis.google.com/chart?cht=d&chdp=mapsapi&chl=";
	 var iconUrl = baseUrl + pinProgram + "'i\\" + "'[" + label + 
	               "'-2'f\\"  + "hv'a\\]" + "h\\]o\\" + 
		       color  + "'fC\\" + 
		       labelColor  + "'tC\\" + 
		       color  + "'eC\\";

         iconUrl += "Lauto'f\\";
	 letteredIcon.image = iconUrl + "&ext=.png";; //"http://chart.apis.google.com/chart?cht=mm&chs=32x32&chco=" + color + "," + color + "," + color + "&ext=.png";
	 markerOptions = { icon:letteredIcon };
	 var marker = new GMarker(point, markerOptions);
	 GEvent.addListener(marker, "click", function() {
	    removeMarkerFromList( letter );
	 });
	 return marker;
     }

     var i = 0;
     
     function zoomChange() {
         jQuery('#smap_zoom').val(map.getZoom());
     }

     function removeMarkerFromList( letter ) {
        var mark = '#mark-' + letter
        jQuery(mark).remove();
     }

     function validateMap() {
       var retval = 1;
       var missing = '';
       if( ! jQuery('#smap_width').val() ) {
          missing += ' widht,';
       }

       if( ! jQuery('#smap_height').val() ) {
          missing += ' height,';
       }

       if( ! jQuery('#smap_zoom').val() ) {
           missing += ' zoom,';
       }

       if( missing != '' ) { 
           missing = 'the following fields are required:' + missing + ' for the map';
	   alert(missing);
	   retval = 0;
       }
       return retval;
      }


     function addMarkerToList( index ) {
         var letter = getLetter( index );
	 var img = '<img src="http://www.google.com/mapfiles/marker' + letter+ '.png" />';
	 //jQuery('.mlist').append('<span id="mark-' + letter + '">' + img + '</span>');
	 jQuery('#smap_markerLabel').val(letter);
	 addMarkerToArray();
	 var url=createURL();
     }

     function insertMap() {
          var img  = '<img src="' + createURL() + '" alt="Static Map" />';
	  if(validateMap()) {
  	    jQuery('#TB_iframeContent').ready( function() {
	     setTimeout(function() {
	        var src = jQuery('#TB_iframeContent').contents().find('#src');
		src.focus(); 
   	        src.val( createURL() );
		src.blur();

	     }, 2000);
	    });
	  }
     }

     function closePreviewMap() {
          jQuery('#previewMap').hide('slow');
     }

     function previewMap() {
       if( validateMap() ){
         strIMG = '<img src="' + createURL() + '" alt="Static Map" />';
	 jQuery('#previewMap').css('position', 'absolute');
	 jQuery('#previewMap').css('width', jQuery('#smap_width').val() + 100);
	 jQuery('#previewMap').css('height', jQuery('#smap_height').val() + 100);
	 jQuery('#previewMap').css('text-align', 'center;');
	 jQuery('#previewMap').css('border','3px solid #ccc');
         jQuery('#previewMap').css('top', 30);
         jQuery('#previewMap').css('left', 200);
	 jQuery('#previewMap').css('z-index', 1000);
	 jQuery('#contentPreviewMap').html(strIMG);
	 jQuery('#previewMap').show('slow');
       }
     }

     function custom_init() {
         var lat;
         var long;
         geocoder  = new GClientGeocoder();
         markers   = new Array();

         zoomChange();
	 GEvent.addListener(map, 'zoomend', zoomChange);
         GEvent.addListener(map, 'click', function(overlay, point) {
              if(overlay) {
                  /* map.removeOverlay(overlay); */
              } else if(point) {
                  var marker =  createMarker(point, i);
                  map.addOverlay(marker);
                  if ( enable_report ) { 
                     setLatLng(point);
                     geocoder.getLocations(point, showAddress);
                 } else {
                     var message = 'Error extracting info from: ' + point;
                 }
                 //markers.push(marker);
		 addMarkerToList( i );
                 i++;
             }
        });
     } 
<?php
}

function addPostMapGadget() {
?>
    <style type="text/css">
      #addPostMapGadget { width: 100%; height: 295px; <?php /* 380px */ ?> }
      #addPostMapGadgetSearch {width: 100%; border-bottom: 1px solid #ccc; padding-bottom: 2px; margin-bottom:2px; }
      #addPostMapGadgetMapContainer { float: left; width: 67%; }
      #addPostMapGadgetMapValues { float: left; width: 30%;}
<?php /*
      #addPostMapGadgetMapMarkers { clear: left; height: 100px; }
      #addPostMapGadgetMapMarkers div {  padding-top: 5px; }
      #addPostMapGadgetMapMarkers div.ltitle {  background-color: #ccc; font-size: small; padding: 3px; }
      #addPostMapGadgetMapMarkers div.mlist { height: 70px; border: 1px solid #ccc; overflow: hidden; overflow-x: scroll; }
      #addPostMapGadgetMapMarkers div.mlist span { display: block; float: left; width: 35px; height: 45px; margin-left: 2px; border: 1px solid #ccc; text-align: center; vertical-align: middle; }
      #addPostMapGadgetMapMarkers div.mlist span img { vertical-align: middle; }
*/ 
        global $post_ID, $temp_ID;
        $uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
        $context = apply_filters('media_buttons_context', __('Upload/Insert %s'));
        $media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID&amp;tab=type_url";
        $media_title = __('Add Media');

        $image_upload_iframe_src = apply_filters('image_upload_iframe_src', "$media_upload_iframe_src&amp;type=image");

        $out =<<<EOF
<a href="JavaScript:previewMap();">Preview Map</a>&nbsp; 
<a href="{$image_upload_iframe_src}&amp;TB_iframe=true" id="add_image" class="thickbox" title='$image_title' onclick="insertMap(); return false;">Insert Map</a>
EOF;
?>
      .full_width { width: 95%; }
      .td_w { width: 115px;  padding-left: 5px; }
      .hide { display: none; }
      .show { display: block; }
    </style>
    <script type="text/javascript">
        enable_report = true;
    </script>
    <div id="addPostMapGadget">
       <div id="addPostMapGadgetSearch">
         <span>Search Address :</span>
         <span><input type="text" id="smap_address" name="smap_address" style="width: 60%;" /></span>
         <span><input type="button" name="getAddress" value="buscar" style="vertical-align:top;clear:both" onclick="javascript:getCoordinates();" /></span>
       </div>
       <div id="addPostMapGadgetMapContainer">
         <div id="map_wrapper" style="width: 100%;height:250px;overflow: auto;">
            <div id="map" style="width: 100%;height: 250px;"></div>
         </div>
       </div>
       <div id="addPostMapGadgetMapValues">
          <form id="smap_form" name="smap_form" >
          <input type="hidden" name="smap_lat" id="smap_lat" />
          <input type="hidden" name="smap_lng" id="smap_lng" />
          <table width="100%">
             <tr><td class="td_w">Map Width</td>      <td><input class="full_width" type="text" name="smap_width" id="smap_width">    </td></tr>
             <tr><td class="td_w">Map Height</td>     <td><input class="full_width" type="text" name="smap_height" id="smap_height">  </td></tr>
             <tr><td class="td_w">Map Zoom</td>       <td><input class="full_width" type="text" name="smap_zoom" id="smap_zoom">      </td></tr>
             <tr><td class="td_w">Map Sensor</td>     <td>
                                             <select class="full_width" id="smap_sensor" name="smap_sensor">
                                                 <option name="true">true</option>
                                                 <option name="false" selected>false</option>
                                             </select>
                                         </td>
             </tr>
             <tr><td class="td_w">Map Type</td>       <td>
                                             <select class="full_width" id="smap_mapType" name="smap_mapType">
                                                 <option name="roadmap" selected>roadmap</option>
                                                 <option name="satellite">satellite</option>
                                                 <option name="hybrid">hybrid</option>
                                                 <option name="terrain">terrain</option>
                                             </select>
                                         </td>
             </tr>
             <tr><td class="td_w">Marker Label</td>   <td><input class="full_width" type="text" name="smap_markerLabel" id="smap_markerLabel"></td></tr>
             <tr><td class="td_w">Marker Color</td>   <td><select class="full_width" name="smap_markerColor" id="smap_markerColor">
	                                                     <option name="blue" selected>blue</option>
							     <option name="red">red</option>
							     <option name="yellow">yellow</option>
							     <option name="green">green</option>
	                                                  </select>
	                                              </td>
	     </tr>
             <tr><td class="td_w">Latitud</td>        <td><span class="full_width" id="smap_dlat" style="padding: 2px;"></span></td></tr>
             <tr><td class="td_w">Longitud</td>       <td><span class="full_width" id="smap_dlng" style="padding: 2px;"></span></td></tr>
	     <tr><td class="td_w">&nbsp;</td>         <td style="text-align: right;"><?php echo $out; ?></td></tr>
          </table>
	  </form>
       </div>  

       <?php /*
       <div id="addPostMapGadgetMapMarkers">
          <div>
          <div class="ltitle">Map Marker List</div>
          <div id="markList" class="mlist"> </div>
          </div>
       </div>     
       */
       ?>
       <div id="previewMap" class="hide">
             <div id="previewTitle" style="width: 100%;background-color: #ccc; float: left;padding-top: 2px; padding-bottom: 2px;">Preview Map</div>
	     <div id="closePreview" style="position: absolute; right: 0; top:0; padding-top: 2px; padding-bottom: 2px;"><a href="JavaScript:closePreviewMap();">X</a></div>
	     <div id="contentPreviewMap"></div>
       </div>
    </div>
<?php
}
?>
