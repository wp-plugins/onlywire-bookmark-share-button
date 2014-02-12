<?php
/*
  Plugin Name: OnlyWire for WordPress [OFFICIAL]
  Plugin URI: http://www.onlywire.com/
  Description: Easily post to millions of sites with one button.
  Version: 1.8
  Author: OnlyWire Engineering
  Author URI: https://www.onlywire.com/
*/

$wpURL = get_bloginfo('wpurl');

//includes
include ("owConfig.php");
include ("postrequest.php");

function ow_function($text)
{
    global $post;

    $code          = get_option('ow_script');
    $enable_button = get_option('ow_autopost_enable');

    if ($enable_button == 'on')
    {
        $post      = get_post($postID);
        $tagstring = "";

        if (get_the_tags($post->ID))
        {
            foreach (get_the_tags($post->ID) as $tag)
            {
                $tagstring .= $prefix.str_replace(" ", "-", trim($tag->name));
                $prefix = ',';
            }
        }
        $url    = urlencode(get_permalink($post->ID));
        $title  = trim(urlencode($post->post_title));
        $tags   = trim($tagstring);

        if (strlen(strip_tags($post->post_content)) > 250)
        {
            $description = urlencode(preg_replace("/\s+/", " ", substr(strip_tags($post->post_content), 0, 250)."..."));
        }
        $text .= '<a href="javascript: void(0);" class="onlywire-button wp" data-url="'.addslashes($url).'" data-title="'.addslashes($title).'" data-description="'.addslashes($description).'" data-tags="'.addslashes($tags).'" data-affid="WPOWPLUG"></a><script src="https://d5k6iufjynyu8.cloudfront.net/script/button.js" type="text/javascript"></script>';
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
    add_option('ow_service_logins');
    add_option('ow_autopost');
    add_option('ow_autopost_revisions_now');
    add_option('ow_autopost_revisions');
    add_option('ow_script', 'on');
    add_option('ow_autopost_enable');
    update_option('ow_autopost_enable', 'on');
    update_option('ow_autopost_revisions_now', 'on');
}

/**
 * Post admin hooks
 */
add_action('admin_menu', "ow_adminInit");
add_action('publish_post', 'ow_post');
add_action('future_post', 'ow_post');
add_filter('plugin_action_links', 'ow_settings_link', 10, 2);

/**
 * Adds an action link to the Plugins page
 */
function ow_settings_link($links, $file)
{
    static $this_plugin;

    if (!$this_plugin)
        $this_plugin = plugin_basename(__FILE__);

    if ($file == $this_plugin)
    {
        $settings_link = '<a href="options-general.php?page=onlywireoptions">'.__('Settings').'</a>';
        $links         = array_merge(array($settings_link), $links); // before other links
    }
    return $links;
}

function ow_adminInit()
{
    if (function_exists("add_meta_box"))
        add_meta_box("onlywire-post", "OnlyWire Bookmark &amp; Share", "ow_posting", "post", "normal", "high");
    add_options_page('OnlyWire Settings', 'OnlyWire Settings', 8, 'onlywireoptions', 'ow_optionsAdmin');
}

function ow_optionsAdmin()
{
    ?>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script>
    var owQuery = $;
        function verifyAutoRevisions() {

            if (document.getElementById("ow_autopost_revisions_now").checked) {
                confirm("Enabling this option may cause you to be banned from bookmarking services for excessive submissions.\n\n\'Cancel\' to stop, \'OK\' to enable it.") ? document.getElementById("ow_autopost_revisions_now").checked = true : document.getElementById("ow_autopost_revisions_now").checked = false;
            }
        }
        function auth() {
            var ow_username = document.getElementById("ow_username").value;
            var ow_password = document.getElementById("ow_password").value;
            var url = "<?php echo site_url() ?>/wp-content/plugins/onlywire-bookmark-share-button/http_auth_call.php?auth_user=" + encodeURIComponent(ow_username.trim()) + "&auth_pw=" + encodeURIComponent(ow_password.trim());
            var xmlhttp;

            if (window.XMLHttpRequest)
            {// code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp = new XMLHttpRequest();
            }
            else
            {	// code for IE6, IE5
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }

            xmlhttp.onreadystatechange = function()
            {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
                {
                    console.log(xmlhttp.responseText);
                    var data = JSON.parse(xmlhttp.responseText);
                    if (data.success === true) {

                        var comma_seperated_logins = [];
                        owQuery("input[name='service_logins[]']:checked").each(function() {
                            comma_seperated_logins.push(this.value);
                        });
                        owQuery("#ow_service_logins").val(comma_seperated_logins);

                        document.getElementById("ow_form").submit();
                        return true;
                    } else {
                        alert(data.error_message);
                        return false;
                    }
                }
            }

            xmlhttp.open("GET", url, true);
            xmlhttp.send();
        }

        /* This is fir select all checkboxes.*/
        function selectAll() {
            owQuery("input[name='service_logins[]']").prop("checked", "checked");
        }

        function selectNone() {
            owQuery("input[name='service_logins[]']").prop("checked", false);
        }

        function resize_iframe()
        {

            var height = window.innerWidth;//Firefox
            if (document.body.clientHeight)
            {
                height = document.body.clientHeight;//IE
            }
            document.getElementById("glu").style.height = parseInt(height - document.getElementById("glu").offsetTop - 8) + "px";
        }

        window.onresize = resize_iframe;
    </script>
	<style>
		h3{margin-bottom: 0px;font-weight: 300 !important;color: #666;}
		label{font-weight: normal !important;}
	</style>
    <div class="wrap" style="
         padding: 10px;
         background: #fcfcfc;
         border: 1px solid #e5e5e5;
         font-size: 13px;
         font-family: Helvetica, Arial, sans-serif !important;
         line-height: 20px;
         color: #333333 !important;
         ">

        <div class="ow_header" style="
             width: 99%;
             display: block;
             padding: 13px 10px 7px 10px;
             margin: 0px;
             border-bottom: 1px solid #e5e5e5;
             background-color: #f0f0f0 !important;
             border-bottom: 1px solid #ccc;
             background-image: -o-linear-gradient(90deg , rgb(227,227,227) 0%, rgb(242,242,242) 100%) !important;
             background-image: -moz-linear-gradient(90deg , rgb(227,227,227) 0%, rgb(242,242,242) 100%) !important;
             background-image: -webkit-linear-gradient(90deg , rgb(227,227,227) 0%, rgb(242,242,242) 100%) !important;
             background-image: -ms-linear-gradient(90deg , rgb(227,227,227) 0%, rgb(242,242,242) 100%) !important;
             ">
            <a href="<?php echo SITE_URL; ?>" target="_blank"><img src="https://d5k6iufjynyu8.cloudfront.net/img/logo/logo.400.png" style="width: 175px;" /></a>  
            <?php
            $userInfo = "";
            if (get_option('ow_username') != "")
            {
                $userInfo = getUser(get_option('ow_username'), get_option('ow_password'));
                if ($userInfo->success)
                {
                    ?>
                    <span class="submission-info" style="padding: 7px;font-size: 14px;font-weight: bold; float: right;border-radius: 5px;background: #fff;-webkit-border-radius: 5px;-moz-border-radius: 5px; border: 1px solid #e5e5e5">Submission Usage: <span style="color: #666666;"><?php echo $userInfo->submission_used ?> / <?php echo $userInfo->submission_limit ?></span></span>    
                    <?php
                }
            }
            ?>
        </div>
        <?php if (get_option('ow_username') != "")
        {
            ?>
            <ul style="margin-top: 25px; margin-left: 40px; float: right; position: absolute; right: 20px;">
                <li style="margin: 5px; float: left;"><a style="color: #f26722; text-decoration: underline;" href="<?php echo SITE_URL; ?>wordpress/my/dashboard" target="_blank">OnlyWire Dashboard</a></li>
                <li style="margin: 5px; float: left;"><a style="color: #f26722; text-decoration: underline;" href="<?php echo SITE_URL; ?>wordpress/add/network" target="_blank">Add/Remove Networks</a></li>
                <li style="margin: 5px; float: left;"><a style="color: #f26722; text-decoration: underline;" href="<?php echo SITE_URL; ?>wordpress/support" target="_blank">Support</a></li>
            </ul>
    <?php } ?>
        <h2 style="font-size: 16px; font-weight: bold; border-bottom: 1px solid #e5e5e5; padding-bottom: 0px; margin-top: 10px; margin-left: 10px;">Settings</h2>

        <?php
        if (!$userInfo->success)
        {
            ?>
            <div style="
                 padding: 5px 35px 5px 14px;
                 margin-bottom: 5px;
                 text-shadow: none;
                 border: 1px solid #fbeed5;
                 -webkit-border-radius: 2px;
                 -moz-border-radius: 2px;
                 border-radius: 2px;
                 font-weight: 300 !important; font-size: 14px !important;
                 font-weight: normal !important;
                 color: rgb(228, 0, 0) !important;
                 background-color: #f2dede;
                 border-color: #eed3d7;
                 margin-top: 10px;
                 ">
                Please correct your OnlyWire Username/Password.
            </div>

            <?php
        }
        else if ($userInfo != "")
        {
            if ($userInfo->account_status != 1)
            {
                ?>
                <div style="
                     padding: 5px 35px 5px 14px;
                     margin-bottom: 5px;
                     text-shadow: none;
                     border: 1px solid #fbeed5;
                     -webkit-border-radius: 2px;
                     -moz-border-radius: 2px;
                     border-radius: 2px;
                     font-weight: 300 !important; font-size: 14px !important;
                     font-weight: normal !important;
                     color: rgb(228, 0, 0) !important;
                     background-color: #f2dede;
                     border-color: #eed3d7;
                     margin-top: 10px;
                     ">
                    Account Status: <?php echo $userInfo->account_status_desc; ?> (<a style="color: #f26722; text-decoration: underline;" href="<?php echo SITE_URL; ?>wordpress/my/account" target="_blank">Update</a>)
                </div>
                <?php
            }
        }
        else
        {
            ?>
            <div style="
                 padding: 5px 35px 5px 14px;
                 margin-bottom: 5px;
                 text-shadow: none;
                 border: 1px solid #fbeed5;
                 -webkit-border-radius: 2px;
                 -moz-border-radius: 2px;
                 border-radius: 2px;
                 font-weight: 300 !important; font-size: 14px !important;
                 font-weight: normal !important;
                 color: rgb(228, 0, 0) !important;
                 background-color: #f2dede;
                 border-color: #eed3d7;
                 margin-top: 10px;
                 ">
                To start using this plugin, please enter your OnlyWire Username and Password in the fields below and select save changes.
            </div>
            <?php
        }
        ?>
        <form id="ow_form" method="post" action="options.php" onSubmit="auth();
                return false;" style="border-bottom: 1px solid #e5e5e5;">
              <?php wp_nonce_field('update-options'); ?>
              <?php
              $code_form = get_option('ow_script');
              ?>
            <input id="ow_script" type="hidden" name="ow_script" value="<?php echo $code_form; ?>" />

            <table class="form-table">
                <tr>
                    <td colspan="2"><h3 style="margin-bottom: 0px;">Account Information</h3></td>
                </tr>
                <tr>
                    <th style="white-space:nowrap; vertical-align: middle; padding-left: 20px;" scope="row">
                    	<label for="ow_username">
    						<?php _e("OnlyWire Username"); ?>:
    					</label>
    				</th>
                    <td><input id="ow_username" type="text" name="ow_username" value="<?php echo get_option('ow_username'); ?>" 
                               style="
                               background-color: #ffffff;
                               border: 1px solid #e5e5e5;
                               -webkit-border-radius: 1px;
                               -moz-border-radius: 1px;
                               border-radius: 1px;
                               padding: 5px;"
                               />
                    </td>

                </tr>

                <tr valign="top">
                    <th style="white-space:nowrap;vertical-align: middle;padding-left: 20px;" scope="row"><label for="ow_password"><?php _e("OnlyWire Password"); ?>:</label></th>
                    <td><input  id="ow_password" type="password" name="ow_password" value="<?php echo get_option('ow_password'); ?>" 
                                style="
                                background-color: #ffffff;
                                border: 1px solid #e5e5e5;
                                -webkit-border-radius: 1px;
                                -moz-border-radius: 1px;
                                border-radius: 1px;
                                padding: 5px;
                                " />
                    </td>
                </tr>
                <tr valign="top">
                    <th style="white-space:nowrap;padding-left: 20px;vertical-align: middle;" scope="row">
                        <label for="ow_autopost"><?php _e("Auto Post All Articles"); ?>:<br/>
                            <span class="small-message" style="color: #999; font-weight: 300; font-size: 11px;">Post all newly created articles to the selected Networks.</span>
                        </label></th>
                    <td><input id="ow_autopost" style="margin-top: -10px;" type="checkbox" name="ow_autopost" <?php
                        if (get_option('ow_autopost') == 'on')
                        {
                            echo 'checked="true"';
                        }
                        ?> /></td>
                    <td style="width:100%;"></td>
                </tr>
                <tr valign="top">
                    <th style="white-space:nowrap;padding-left: 20px;" scope="row">
                        <label for="ow_autopost_enable">
    						<?php _e("Show OnlyWire Share Button"); ?>:<br/>
                            <span class="small-message" style="color: #999; font-weight: 300; font-size: 11px;">Display 'OnlyWire Share' Button under each article.</span>
                        </label>
                    </th>
                    <td style="vertical-align: bottom;"><input id="ow_autopost_enable" style="margin-top: -30px;" type="checkbox" name="ow_autopost_enable" <?php
                        if (get_option('ow_autopost_enable') == 'on')
                        {
                            echo 'checked="true"';
                        }
                        ?> /></td>
                    <td style="width:100%;">

                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h3 style="margin-bottom: 5px;">Networks ( <a style="color: #f26722; text-decoration: underline; font-weight: normal; font-size: 13px;" href="<?php echo SITE_URL; ?>wordpress/add/network" target="_blank">Manage</a> )</h3> 

                        <?php
                        $data = getServiceLogins(get_option('ow_username'), get_option('ow_password'));

                        if (count($data->networks) > 0)
                        {
                            ?>
                            <a href="javascript: void(0);" onclick="selectNone();">Deselect All</a>&nbsp;&nbsp;&nbsp;
                            <a href="javascript: void(0);" onclick="selectAll();">Select All</a>
                            <div id="service_logins" style="margin-top: 20px;">
                                <?php
                                foreach ($data->networks as $network)
                                {
                                    ?>

                                    <div class="checkbox-wrap" style="display: block; float: left; width: 300px; margin: 10px;">
                                        <input type="checkbox" id="<?php echo $network->id; ?>" name="service_logins[]"
                                        <?php
                                        $temp = explode(",", get_option('ow_service_logins'));
                                        if (in_array($network->id, $temp))
                                        {
                                            echo " checked ";
                                        }
                                        ?>
                                               value="<?php echo $network->id; ?>" style="float:left; margin: 5px 0px 5px 5px;"/>  
                                        <div class="label-wrap" style="display: block; width: 250px;float: left;margin-left: 10px;">
                                            <label for="<?php echo $network->id; ?>" style="font-weight: bold; margin-bottom: -5px; display: block; float: left;width: 300px;min-height: 60px;">
                                                <img for="<?php echo $network->id; ?>" src="<?php echo $network->icon; ?>" style="float: left; width: 40px; margin: 5px;border-radius: 50px;border: 5px solid #e5e5e5;box-shadow: 0px 0px 3px 1px #999;margin-top: -10px;"/>
            <?php echo $network->name ?>
                                                <br/>
                                                <span style="margin-top: 0px;float: left;color: #999; font-weight: 300;text-overflow: ellipsis;white-space: nowrap;overflow: hidden; width: 50%;font-size: 12px;"><?php echo $network->description; ?></span> <br/>
                                                <span style="margin-top: -10px;font-weight: normal; color: #d83526; font-size: 12px; float: left;"> 
                                                    <?
                                                    if ($network->status != NULL)
                                                    {
                                                        ?>
                                                        Incorrect login. <a href="<?php echo SITE_URL; ?>wordpress/correct/login/<?php echo $network->id; ?>" target="_blank" style="text-decoration: underline;">Correct it</a> 
                                                        <?php
                                                    }
                                                    ?>
                                                </span>
                                            </label> 
                                        </div>
                                    </div>

                                    <?php
                                }
                                ?>
                            </div>    
                            <?php
                        }
                        elseif (!get_option('ow_username'))
                        {
                            ?>
                            <div class="no-networks" style="width: 98%; border: 1px solid #e5e5e5; padding: 20px; text-align: left; font-size: 13px; ">Please add your username and password above to see your networks</div>
                            <?php
                        }
                        else
                        {
                            ?>

                            <div class="no-networks" style="width: 98%; border: 1px solid #e5e5e5; padding: 20px; text-align: left; font-size: 13px; ">

                                You do not have any networks setup. <br/>

                                <a href="<?php echo SITE_URL; ?>wordpress/add/network" style="
                                   display: inline-block;
                                   padding: 4px 12px;
                                   margin-bottom: 0;
                                   font-size: 13px;
                                   line-height: 20px;
                                   color: #333333;
                                   text-align: center;
                                   text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
                                   vertical-align: middle;
                                   cursor: pointer;
                                   background: #eeeeee;
                                   border: 1px solid #ccc;
                                   -webkit-border-radius: 2px;
                                   -moz-border-radius: 2px;
                                   border-radius: 2px;
                                   margin-top: 10px;
                                   -webkit-box-shadow: none;
                                   box-shadow: none;
                                   -moz-box-shadow: none;
                                   padding: 2px 10px;
                                   height: auto;
                                   " class="button-primary" target="_blank">Add Network</a>

                            </div>

                            <?php
                        }
                        ?>

                    </td>
                </tr>
            </table>


            <input type="hidden" name="ow_service_logins" id="ow_service_logins" value="" />
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="ow_username,ow_password,ow_service_logins,ow_autopost,ow_autopost_revisions_now,ow_script,ow_autopost_enable" />

            <p class="submit">
                <input type="submit" style="
                    background: #f26722;
					-moz-border-radius: 3px;
					-webkit-border-radius: 3px;
					border-radius: 3px;
					border: 1px solid #d83526;
					display: inline-block;
					color: #ffffff !important;
					font-size: 13px;
					padding: 2px 10px;
					text-decoration: none;
					font-weight: 300;
					text-shadow: none;
					min-width: 60px;
					text-align: center;
					-webkit-box-shadow: none;
					box-shadow: none;
					height: auto;
					-moz-box-shadow: none;
					margin: 10px auto;
					width: 130px;
					float: none;
					display: block;
					text-align: center;
               	" class="button-primary" value="<?php _e('Save Changes') ?>" />
            </p>
        </form>

        <iframe src="<?php echo SITE_URL; ?>wordpress/promo?u=<?php echo base64_encode(get_option('ow_username')); ?>" id="glu" width="100%" style="min-height: 400px;" onload="resize_iframe()"></iframe>

    </div>

    <div style="margin-top: 10px; color: #666666;">
        © 2013 OnlyWire, LLC.&nbsp;&nbsp;All Rights Reserved. U.S. Patent Numbers 8,161,102 and 8,359,352.             
    </div>

    <?php
}

/**
 * Function taken from Revision Control WordPress Plugin
 * http://wordpress.org/extend/plugins/revision-control/
 * Determines the post/page's ID based on the 'post' and 'post_ID' POST/GET fields.
 */
function ow_get_page_id()
{
    foreach (array('post_ID', 'post') as $field)
        if (isset($_REQUEST[$field]))
            return absint($_REQUEST[$field]);

    if (isset($_REQUEST['revision']))
        if ($post = get_post($id   = absint($_REQUEST['revision'])))
            return absint($post->post_parent);

    return false;
}

/**
 * Code for the meta box.
 * the post of this goes to the function ow_post()
 */
function ow_posting()
{
    global $post_ID;

    $ow_post_type_id = get_post(ow_get_page_id());
    $ow_post_type    = $ow_post_type_id->post_status;

    $networks = getServiceLogins(get_option('ow_username'), get_option('ow_password'));
    $userInfo = getUser(get_option('ow_username'), get_option('ow_password'));
    if ($userInfo != "")
    {
        if ($userInfo->account_status != 1)
        {
            ?>
            <div style="
                 padding: 5px 35px 5px 14px;
                 margin-bottom: 5px;
                 text-shadow: none;
                 border: 1px solid #fbeed5;
                 -webkit-border-radius: 2px;
                 -moz-border-radius: 2px;
                 border-radius: 2px;
                 font-weight: 300 !important; font-size: 14px !important;
                 font-weight: normal !important;
                 color: rgb(228, 0, 0) !important;
                 background-color: #f2dede;
                 border-color: #eed3d7;
                 margin-top: 10px;
                 ">
                OnlyWire Account Status: <?php echo $userInfo->account_status_desc; ?> (<a style="color: #f26722; text-decoration: underline;" href="<?php echo SITE_URL; ?>wordpress/my/account" target="_blank">Update</a>)
            </div>
            <?php
        }
        else
        {
            if (count($networks->networks) > 0)
            {
                //Check to see if it's a revision ("auto-draft" or "draft" return type is a new post)
                if (($ow_post_type != 'auto-draft') && ($ow_post_type != 'draft'))
                {
                    ?>
                    <label for="ow_post">
                        <input type="checkbox" id="ow_post" name="ow_post" /> Post this revision to OnlyWire	
                    </label>

                    <?php
                }
                else
                {
                    ?>	    
                    <label for="ow_post">
                        <input type="checkbox" <?php echo get_option('ow_autopost') == 'on' ? 'checked="checked"' : ''; ?> id="ow_post" name="ow_post" /> Post this to OnlyWire	
                    </label>
                    <?php
                }
            }
            else
            {
                ?>
                <div class="no-networks" style="width: 95%; border: 1px solid #e5e5e5; padding: 20px; text-align: left; font-size: 13px; font-weight: 300 !important; font-size: 14px !important;
                     font-weight: normal !important;
                     color: rgb(228, 0, 0) !important;
                     background-color: #f2dede;
                     border-color: #eed3d7; ">

                    You do not have any networks setup. <br/>

                    <a href="<?php echo SITE_URL; ?>wordpress/add/network" style="
                       display: inline-block;
                       padding: 4px 12px;
                       margin-bottom: 0;
                       font-size: 13px;
                       line-height: 20px;
                       color: #333333;
                       text-align: center;
                       text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
                       vertical-align: middle;
                       cursor: pointer;
                       background: #eeeeee;
                       border: 1px solid #ccc;
                       -webkit-border-radius: 2px;
                       -moz-border-radius: 2px;
                       border-radius: 2px;
                       margin-top: 10px;
                       -webkit-box-shadow: none;
                       box-shadow: none;
                       -moz-box-shadow: none;
                       padding: 2px 10px;
                       height: auto;

                       " class="button-primary" target="_blank">Add Network</a>

                </div>
                <?
            }
        }
    }
}

/**
 * Return a random tag if none are supplied in the post
 */
function getDefaultTag()
{
    $tags      = array("bookmark", "favorite", "blog", "social", "web", "internet", "share", "organize", "manage", "reference", "tag", "save");
    $rand_keys = array_rand($tags, 2);

    return $tags[$rand_keys[0]];
}

/**
 * @param The post ID
 * Posts this post to OnlyWire
 */
function ow_post($postID)
{
    global $wpdb;
    // Get the correct post ID if revision.
    if ($wpdb->get_var("SELECT post_type FROM $wpdb->posts WHERE ID=$postID") == 'revision')
    {
        $postID = $wpdb->get_var("SELECT post_parent FROM $wpdb->posts WHERE ID=$postID");
    }

    if (isset($_POST['ow_post']) && $_POST['ow_post'] == 'on')
    {
        // the checkbox is selected, let's post to onlywire with user credentials
        $username = get_option('ow_username');
        $password = get_option('ow_password');
        if ($username && $password)
        {

            $post      = get_post($postID);
            $tagstring = "";
            $prefix    = '';

            if (get_the_tags($post->ID))
            {
                foreach (get_the_tags($post->ID) as $tag)
                {
                    $tagstring .= $prefix.str_replace(" ", "-", trim($tag->name));
                    $prefix = ',';
                }
            }

            $d                 = 'm\/d\/Y h\:i\:s T';
            $data['url']       = urlencode(get_permalink($postID));
            $data['title']     = trim(urlencode($post->post_title));
            $data['tags']      = trim($tagstring);
            $data['scheduled'] = urlencode(get_post_time($d, true, $post, false));

            if (strlen(strip_tags($post->post_content)) > 250)
            {
                $data['description'] = urlencode(substr(strip_tags($post->post_content), 0, 250)."...");
            }
            else
            {
                $data['description'] = urlencode(strip_tags($post->post_content));
            }


            if (get_option('ow_service_logins') != false)
            {
                $data['networks'] = trim(get_option('ow_service_logins'));
            }

            createBookmark($data, $username, $password);
        }
    }
}
?>
