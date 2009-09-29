<?php
/*
Plugin Name: OnlyWire for WordPress
Plugin URI: http://onlywire.com/
Description: Easily post to millions of sites with one button. 
Version: 0.1
Author: ...
Author URI: http://
*/

$wpURL = get_bloginfo('wpurl');

include ("postrequest.php");
include "jsonwrapper/jsonwrapper.php";

function ow_function($text) {
    global $post;

    $code = get_option('ow_script');

    if($code) {
	$text .= '<script type="text/javascript" class="owbutton" src="http://onlywire.com/btn/button_'.$code.'" title="'.$post->post_title.'" url="'.get_permalink($post->ID).'"></script>';
    } else {
        $text .= '<script type="text/javascript" class="owbutton" src="http://onlywire.com/button" title="'.$post->post_title.'" url="'.get_permalink($post->ID).'"></script>';
    }

    return $text;
}

add_filter('the_content', 'ow_function');

/**
 * Plugin activation.
 */
register_activation_hook(__FILE__, 'ow_activate');

function ow_activate()
{
	global $wpdb;
	
	add_option('ow_username');
	add_option('ow_password');
	add_option('ow_script');
}

/**
 * Post admin hooks
 */
add_action('admin_menu', "ow_adminInit");
add_action('save_post', 'ow_post');

function ow_adminInit()
{
	if( function_exists("add_meta_box") )
		add_meta_box("onlywire-post", "OnlyWire", "ow_posting", "post", "advanced");
	
	add_options_page('OnlyWire Options', 'OnlyWire Options', 8, 'onlywireoptions', 'ow_optionsAdmin');
}

function ow_optionsAdmin()
{
?>
    <script>
function getFrameDocument(theId) { 
    
    if(document.getElementById(theId)) {
        var iframe_document = null;
        theId = document.getElementById(theId);
        if (theId.contentDocument) {
            iframe_document = theId.contentDocument;
        }
        else if (theId.contentWindow) {
            iframe_document = theId.contentWindow.document;
        }
        else if (theId.document) {
            iframe_document = theId.document;
        }
        else {
            throw(new Error("Cannot access iframe document."));
        }
        
        return iframe_document;
    }

}
function ajaxObject(url, callbackFunction) {
  var that=this;      
  this.updating = false;
  this.abort = function() {
    if (that.updating) {
      that.updating=false;
      that.AJAX.abort();
      that.AJAX=null;
    }
  }
  this.update = function(passData,postMethod) { 
    if (that.updating) { return false; }
    that.AJAX = null;                          
    if (window.XMLHttpRequest) {              
      that.AJAX=new XMLHttpRequest();              
    } else {                                  
      that.AJAX=new ActiveXObject("Microsoft.XMLHTTP");
    }                                             
    if (that.AJAX==null) {                             
      return false;                               
    } else {
      that.AJAX.onreadystatechange = function() {  
        if (that.AJAX.readyState==4) {             
          that.updating=false;                
          that.callback(that.AJAX.responseText,that.AJAX.status,that.AJAX.responseXML);        
          that.AJAX=null;                                         
        }                                                      
      }                                                        
      that.updating = new Date();                              
      if (/post/i.test(postMethod)) {
        var uri=urlCall+'?'+that.updating.getTime();
        that.AJAX.open("POST", uri, true);
        that.AJAX.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        that.AJAX.setRequestHeader("Content-Length", passData.length);
        that.AJAX.send(passData);
      } else {
        var uri=urlCall+'?'+passData+'&timestamp='+(that.updating.getTime()); 
        that.AJAX.open("GET", uri, true);                             
        that.AJAX.send(null);                                         
      }              
      return true;                                             
    }                                                                           
  }
  var urlCall = url;        
  this.callback = callbackFunction || function () { };
}
function obj2query(obj, forPHP, parentObject){
   if( typeof obj != 'object' ) return '';

   if (arguments.length == 1)
      forPHP = /\.php$/.test(document.location.href);
   
   var rv = '';
   for(var prop in obj) if (obj.hasOwnProperty(prop) ) {

      var qname = parentObject
         ? parentObject + '.' + prop
         : prop;

      // Expand Arrays
      if (obj[prop] instanceof Array)
         for( var i = 0; i < obj[prop].length; i++ )
            if( typeof obj[prop][i] == 'object' )
               rv += '&' + obj2query( obj[prop][i], forPHP, qname );
            else
               rv += '&' + encodeURIComponent(qname) + (forPHP ? '[]' : '')
                    + '=' + encodeURIComponent( obj[prop][i] );

      // Expand Dates
      else if (obj[prop] instanceof Date)
         rv += '&' + encodeURIComponent(qname) + '=' + obj[prop].getTime();

      // Expand Objects
      else if (obj[prop] instanceof Object)
         // If they're String() or Number() etc
         if (obj.toString && obj.toString !== Object.prototype.toString)
            rv += '&' + encodeURIComponent(qname) + '=' + encodeURIComponent( obj[prop].toString() );
         // Otherwise, we want the raw properties
         else
            rv += '&' + obj2query(obj[prop], forPHP, qname);

      // Output non-object
      else
         rv += '&' + encodeURIComponent(qname) + '=' + encodeURIComponent( obj[prop] );

   }
   return rv.replace(/^&/,'');
}


function serialize(form)
{
    var obj = {};
    for(var i=0; i<form.elements.length; i++) {
        obj[form.elements[i].name] = form.elements[i].value;
    }
    return obj2query(obj);
}
function processData(responseText) {
    // update the hidden ow_script input box with responseText, and submit form
    //console.log(responseText);
    document.getElementById("ow_script").value = responseText; 
    document.getElementById("ow_form").submit();
}

function func() {
    var ow_iframe_doc = getFrameDocument("ow_iframe"); 
    var ow_iframe_win = document.getElementById("ow_iframe").contentWindow;
    
    var buttonform = ow_iframe_doc.getElementById("thebuttonform");
    //we must call this javascript from within the iframe, this builds the POST data values
    ow_iframe_win.$('selections').value = ow_iframe_win.buildButtonId();
    
    // call the local buttonid.php (ajax) file to make a request with the data from the form to onlywire, and get back the buttonid
    var s = serialize(buttonform);
    var myRequest = new ajaxObject("<?php echo get_bloginfo('siteurl')?>/wp-content/plugins/onlywire/buttonid.php", processData);
    myRequest.update(s);  // Server is contacted here.
}
    </script>
	<div class="wrap">
	<h2>OnlyWire Options</h2>
	
		<form id="ow_form" method="post" action="options.php" onsubmit="func(); return false;">
			<?php wp_nonce_field('update-options'); ?>
	
            <input id="ow_script" type="hidden" name="ow_script" value="<?php echo get_option('ow_script'); ?>" />

			<table class="form-table">
				<tr valign="top">
					<th style="white-space:nowrap;" scope="row"><label for="ow_username"><?php _e("OnlyWire username"); ?>:</label></th>
					<td><input id="ow_username" type="text" name="ow_username" value="<?php echo get_option('ow_username'); ?>" /></td>
					<td style="width:100%;">The username you use to login on OnlyWire.com.</td>
				</tr>
				
				<tr valign="top">
					<th style="white-space:nowrap;" scope="row"><label for="ow_password"><?php _e("OnlyWire password"); ?>:</label></th>
					<td><input id="ow_password" type="text" name="ow_password" value="<?php echo get_option('ow_password'); ?>" /></td>
					<td style="width:100%;">The password you use to login on OnlyWire.com.</td>
				</tr>

				
			</table>
            <iframe id="ow_iframe" src="<?php echo get_bloginfo('siteurl')."/wp-content/plugins/onlywire/iframe.php"?>" style="width: 100%; height: 710px;" ></iframe>
	
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="ow_username,ow_password,ow_script" />
	
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>

<?php
}

/**
 * Code for the meta box.
 * the post of this goes to the function ow_post()
 */
function ow_posting()
{
	global $post_ID;
?>
    <label for="ow_post">
        <input type="checkbox" checked="true" id="ow_post" name="ow_post" /> Post this to OnlyWire	
    </label>
<?php
}

/**
 * @param The post ID
 * Posts this post to OnlyWire
 */
function ow_post( $postID )
{
	global $wpdb;

	// Get the correct post ID if revision.
	if ( $wpdb->get_var("SELECT post_type FROM $wpdb->posts WHERE ID=$postID")=='revision') {
		$postID = $wpdb->get_var("SELECT post_parent FROM $wpdb->posts WHERE ID=$postID");
	}

    
    if(isset($_POST['ow_post']) && $_POST['ow_post'] == 'on') {
        // the checkbox is selected, let's post to onlywire with user credentials
        $username = get_option('ow_username');
        $password = get_option('ow_password');
        if($username && $password) {
            // we have credentials, let's login on Onlywire with this account and post the $postID
            $password = array($username, md5($password));
            $data = array();
            $data['token'] = implode('%26', $password);

            // get the services
            $gservices = file_get_contents("http://onlywire.com/widget/getWidgetData.php?token=".$data['token']);
            // gservices is not "jsonp(..);" let's remove "jsonp(" and ");"
            $gservices = str_replace('jsonp(','',$gservices);
            $gservices = str_replace(');','',$gservices);
            $jservices = json_decode($gservices);

            // $jservices->services is an array of objects
            $service_ids = array();
            foreach($jservices->services as $jobj) {
                array_push($service_ids, $jobj->pk_id);
            }
            $services =  implode(',',$service_ids);

            $data['service'] = $services; 

            $post = get_post($postID); 
            $tags = get_the_tags($postID);
            $tagarr = array();
            // build tags string
            foreach($tags as $tag) {
                array_push($tagarr, $tag->name);
            }
            $tagstring = implode(' ', $tagarr);

            $data['url'] = $post->guid; 
            $data['title'] = $post->post_title;
            $data['tags'] = $tagstring;

            $a = PostRequest("http://onlywire.com/b/saveurl2.php","", $data);

        }
    }

}

?>
