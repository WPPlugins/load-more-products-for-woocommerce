<?php
/*new berocket_admin_notices(array(
    'start' => 1497880000, // timestamp when notice start
    'end'   => 1497885000, // timestamp when notice end
    'name'  => 'name', //notice name must be unique for this time period
    'html'  => '', //text or html code as content of notice
    'righthtml'  => '<a class="berocket_no_thanks">No thanks</a>', //content in the right block, this is default value. This html code must be added to all notices
    'rightwidth'  => 80, //width of right content is static and will be as this value. berocket_no_thanks block is 60px and 20px is additional
    'nothankswidth'  => 60, //berocket_no_thanks width. set to 0 if block doesn't uses. Or set to any other value if uses other text inside berocket_no_thanks
    'contentwidth'  => 400, //width that uses for mediaquery is image_width + contentwidth + rightwidth
    'subscribe'  => false, //add subscribe form to the righthtml
    'priority'  => 20, //priority of notice. 1-5 is main priority and displays on settings page always
    'height'  => 50, //height of notice. image will be scaled
    'repeat'  => false, //repeat notice after some time. time can use any values that accept function strtotime
    'repeatcount'  => 1, //repeat count. how many times notice will be displayed after close
    'image'  => array(
        'global' => 'http://berocket.com/images/logo-2.png', //image URL from other site. Image will be copied to uploads folder if it possible
        //'local' => 'http://wordpress-site.com/wp-content/uploads/logo-2.png', //notice will be used this image directly
    ),
));*/
//delete_option('berocket_admin_notices'); //remove all notice information
//delete_option('berocket_last_close_notices_time'); //remove wait time before next notice
if( ! class_exists( 'berocket_admin_notices' ) ) {
    class berocket_admin_notices {
        public $find_names, $notice_exist = false;
        public static $last_time = '-24 hours';
        public static $end_soon_time = '+1 hour';
        public static $subscribed = false;
        public static $jquery_script_exist = false;
        function __construct($options = array()) {
            $options = array_merge(
                array(
                    'start'         => 0,
                    'end'           => 0,
                    'name'          => 'sale',
                    'html'          => '',
                    'righthtml'     => '<a class="berocket_no_thanks">No thanks</a>',
                    'rightwidth'    => 80,
                    'nothankswidth' => 60,
                    'contentwidth'  => 400,
                    'subscribe'     => false,
                    'closed'        => '0',
                    'priority'      => 20,
                    'height'        => 50,
                    'repeat'        => false,
                    'repeatcount'   => 1,
                    'image'         => array(
                        'global'    => 'http://berocket.com/images/logo-2.png'
                    ),
                ),
                $options
            );
            self::set_notice_by_path($options);
        }
        public static function sort_notices($notices) {
            return self::sort_array (
                $notices,
                array(
                    1 => 'krsort',
                    2 => 'ksort',
                    3 => 'ksort'
                ),
                array(
                    '1' => SORT_NUMERIC,
                    '2' => SORT_NUMERIC,
                    '3' => SORT_NUMERIC
                )
            );
        }
        public static function sort_array($array, $sort_functions, $options, $count = 3) {
            if( $count > 0 ) {
                if( ! is_array($array) ) {
                    return array();
                }
                $call_function = $sort_functions[$count];
                $call_function($array, $options[$count]);
                if( isset($array[0]) ) {
                    $first_element = $array[0];
                    unset($array[0]);
                    $array[0] = $first_element;
                    unset($first_element);
                }
                foreach($array as $item_id => $item) {
                    if( $count == 2 ) {
                        $time = time();
                        if( $item_id < $time && $item_id != 0 ) {
                            unset($array[$item_id]);
                        } else {
                            $array[$item_id] = self::sort_array($item, $sort_functions, $options, $count - 1);
                        }
                    } else {
                        $array[$item_id] = self::sort_array($item, $sort_functions, $options, $count - 1);
                    }
                    if( isset($array[$item_id]) && ( ! is_array($array[$item_id]) || count($array[$item_id]) == 0 ) ) {
                        unset($array[$item_id]);
                    }
                }
            }
            return $array;
        }
        public static function get_notice_by_path($find_names) {
            $notices = get_option('berocket_admin_notices');
            if( ! is_array($notices) ) {
                $notices = array();
            }
            $current_notice = &$notices;
            foreach($find_names as $find_name) {
                if( isset($current_notice[$find_name]) ) {
                    $new_current_notice = &$current_notice[$find_name];
                    unset($current_notice);
                    $current_notice = &$new_current_notice;
                    unset($new_current_notice);
                } else {
                    unset($current_notice);
                    break;
                }
            }
            return $current_notice;
        }
        public static function set_notice_by_path($options, $replace = false, $find_names = false) {
            self::$subscribed = get_option('berocket_email_subscribed');
            if( self::$subscribed && $options['subscribe'] ) {
                return false;
            }
            $notices = get_option('berocket_admin_notices');
            if( $options['end'] < time() && $options['end'] != 0 ) {
                return false;
            }
            if( $find_names === false ) {
                $find_names = array($options['priority'], $options['end'], $options['start'], $options['name']);
            }
            if( ! is_array($notices) ) {
                $notices = array();
            }
            $current_notice = &$notices;
            foreach($find_names as $find_name) {
                if( ! isset($current_notice[$find_name]) ) {
                    $current_notice[$find_name] = array();
                }
                $new_current_notice = &$current_notice[$find_name];
                unset($current_notice);
                $current_notice = &$new_current_notice;
                unset($new_current_notice);
            }
            if( empty($options['image']) || (empty($options['image']['local']) && empty($options['image']['global'])) ) {
                $options['image'] = array('width' => 0, 'height' => 0, 'scale' => 0);
            } else {
                if( isset($options['image']['global']) ) {
                    $wp_upload = wp_upload_dir();
                    if( ! isset($options['image']['local']) ) {
                        $url_global = $options['image']['global'];
                        $img_local = $wp_upload['basedir'] . '/' . basename($url_global);
                        $url_local = $wp_upload['baseurl'] . '/' . basename($url_global);
                        if( ! file_exists($img_local) && is_writable($wp_upload['path']) ) {
                            file_put_contents($img_local, file_get_contents($url_global));
                        }
                        if( file_exists($img_local) ) {
                            $options['image']['local'] = $url_local;
                            $options['image']['pathlocal'] = $img_local;
                        } else {
                            $options['image']['local'] = $url_global;
                        }
                    }
                }
                $image_size = @ getimagesize($options['image']['local']);
                if( ! empty($image_size[0]) && ! empty($image_size[1]) ) {
                    $options['image']['width'] = $image_size[0];
                    $options['image']['height'] = $image_size[1];
                } else {
                    $options['image']['width'] = $options['height'];
                    $options['image']['height'] = $options['height'];
                }
                $options['image']['scale'] = $options['height'] / $options['image']['height'];
            }
            if( count($current_notice) == 0 ) {
                $current_notice = $options;
            } else {
                if( ! empty($options['image']['local']) && $options['image']['local'] != $current_notice['image']['local'] ) {
                    if( isset($current_notice['image']['pathlocal']) ) {
                        unlink($current_notice['image']['pathlocal']);
                    }
                }
                if( ! $replace ) {
                    $options['closed'] = $current_notice['closed'];
                }
                $current_notice = $options;
            }
            $notices = self::sort_notices($notices);
            update_option('berocket_admin_notices', $notices);
            return true;
        }
        public static function get_notice() {
            $notices = get_option('berocket_admin_notices');
            $last_time = get_option('berocket_last_close_notices_time');
            self::$subscribed = get_option('berocket_email_subscribed');
            if( ! is_array($notices) || count($notices) == 0 ) return false;
            if( $last_time > strtotime(self::$last_time) ) {
                $current_notice = self::get_not_closed_notice($notices, true);
            } else {
                $current_notice = self::get_not_closed_notice($notices);
            }
            update_option('berocket_current_displayed_notice', $current_notice);
            return $current_notice;
        }
        public static function get_notice_for_settings() {
            $notices = get_option('berocket_admin_notices');
            $last_notice = get_option('berocket_admin_notices_last_on_options');
            self::$subscribed = get_option('berocket_email_subscribed');
            $notices = self::get_notices_with_priority($notices);
            if( ! is_array($notices) || count($notices) == 0 ) {
                return false;
            }
            if( $last_notice === false ) {
                $last_notice = 0;
            } else {
                $last_notice++;
            }
            if( count($notices) <= $last_notice ) {
                $last_notice = 0;
            }
            update_option('berocket_admin_notices_last_on_options', $last_notice);
            $notice = $notices[$last_notice];
            return $notice;
        }
        public static function get_not_closed_notice($array, $end_soon = false, $closed = 0, $count = 3) {
            $notice = false;
            if( empty($array) || ! is_array($array) ) {
                $array = array();
            }
            $time = time();
            foreach($array as $item_id => $item) {
                if( $count > 0 ) {
                    if( $count == 2 && $item_id < $time && $item_id != 0 || $count == 1 && $item_id > $time && $item_id != 0 ) {
                        continue;
                    }
                    if( $count == 2 && $item_id < strtotime(self::$end_soon_time) && $item_id != 0 ) {
                        $notice = self::get_not_closed_notice($item, $end_soon, 1, $count - 1);
                    } else {
                        if( $end_soon && $count == 2 ) {
                            break;
                        }
                        $notice = self::get_not_closed_notice($item, $end_soon, $closed, $count - 1);
                    }
                } else {
                    if( $item['closed'] <= $closed && ( ! self::$subscribed || ! $item['subscribe'] ) ) {
                        return $item;
                    }
                }
                if( $notice != false ) break;
            }
            return $notice;
        }
        public static function get_notices_with_priority($array, $priority = 5, $count = 3) {
            $notice = false;
            if( empty($array) || ! is_array($array) ) {
                $array = array();
            }
            $time = time();
            $notices = array();
            foreach($array as $item_id => $item) {
                if( $count > 0 ) {
                    if( $count == 3 && $item_id > $priority || $count == 2 && $item_id < $time && $item_id != 0 || $count == 1 && $item_id > $time && $item_id != 0 ) {
                        continue;
                    }
                    $notice = self::get_notices_with_priority($item, $priority, $count - 1);
                    $notices = array_merge($notices, $notice);
                } else {
                    if( ! self::$subscribed || ! $item['subscribe'] ) {
                        $notices[] = $item;
                    }
                }
            }
            return $notices;
        }
        public static function display_admin_notice() {
            $settings_page = apply_filters('is_berocket_settings_page', false);
            if( $settings_page ) {
                $notice = self::get_notice_for_settings();
            } else {
                $notice = self::get_notice();
            }
            if( ! empty($notice['original']) ) {
                $original_notice = self::get_notice_by_path($notice['original']);
                unset($original_notice['start'], $original_notice['closed'], $original_notice['repeatcount']);
                $notice = $original_notice;
            }
            
            if( $notice !== false ) {
                $notice_data = array(
                    'start'     => $notice['start'],
                    'end'       => $notice['end'],
                    'name'      => $notice['name'],
                    'priority'  => $notice['priority'],
                );
                if( $notice['end'] < strtotime(self::$end_soon_time) && $notice['end'] != 0 ) {
                    $time_left = $notice['end'] - time();
                    $time_left_str = "";
                    $time = $time_left;
                    if ( $time >= 3600 ) {
                        $hours = floor( $time/3600 );
                        $time  = $time%3600;
                        $time_left_str .= sprintf("%02d", $hours) . ":";
                    }
                    if ( $time >= 60 || $time_left >= 3600 ) {
                        $minutes = floor( $time/60 );
                        $time  = $time%60;
                        $time_left_str .= sprintf("%02d", $minutes) . ":";
                    }
                    
                    $time_left_str .= sprintf("%02d", $time);
                    $notice['rightwidth'] += 60;
                    $notice['righthtml'] .= '<div class="berocket_time_left_block">Left<br><span class="berocket_time_left" data-time="' . $time_left . '">' . $time_left_str . '</span></div>';
                }
                if( ! empty($notice['subscribe']) ) {
                    $user_email = wp_get_current_user();
                    if( isset($user_email->user_email) ) {
                        $user_email = $user_email->user_email;
                    } else {
                        $user_email = '';
                    }
                    $notice['righthtml'] = 
                    '<form class="berocket_subscribe_form" method="POST" action="' . admin_url( 'admin-ajax.php' ) . '">
                        <input type="hidden" name="action" value="berocket_subscribe_email">
                        <input class="berocket_subscribe_email" type="email" name="email" value="' . $user_email . '">
                        <input type="submit" class="button-primary button berocket_notice_submit" value="Subscribe">
                    </form>' . $notice['righthtml'];
                    $notice['rightwidth'] += 300;
                }
                echo '
                    <div class="notice berocket_admin_notice" data-notice=\'', json_encode($notice_data), '\'>',
                        ( empty($notice['image']['local']) ? '' : '<img class="berocket_notice_img" src="' . $notice['image']['local'] . '">' ),
                        ( empty($notice['righthtml']) ? '' :
                        '<div class="berocket_notice_right_content">
                            <div class="berocket_notice_content">' . $notice['righthtml'] . '</div>
                            <div class="berocket_notice_after_content"></div>
                        </div>' ),
                        '<div class="berocket_notice_content_wrap">
                            <div class="berocket_notice_content">', $notice['html'], '</div>
                            <div class="berocket_notice_after_content"></div>
                        </div></div>';
                if( $settings_page ) {
                    $notice['rightwidth'] -= $notice['nothankswidth'];
                }
                echo '<style>
                    .berocket_admin_notice {
                        height: ', $notice['height'], 'px;
                        padding: 0;
                        min-width: ', max($notice['image']['width'] * $notice['image']['scale'], $notice['rightwidth']), 'px;
                        border-left: 0 none;
                        border-radius: 3px;
                        overflow: hidden;
                        box-shadow: 0 0 3px 0 rgba(0, 0, 0, 0.2);
                    }
                    .berocket_admin_notice .berocket_notice_img {
                        height: ', $notice['height'], 'px;
                        width: ', ($notice['image']['width'] * $notice['image']['scale']), 'px;
                        float: left;
                    }
                    .berocket_admin_notice .berocket_notice_content_wrap {
                        margin-left: ', ($notice['image']['width'] * $notice['image']['scale'] + 5), 'px;
                        margin-right: ', ($notice['rightwidth'] <= 20 ? 0 : $notice['rightwidth'] + 15), 'px;
                        box-sizing: border-box;
                        height: ', $notice['height'], 'px;
                        overflow: auto;
                        overflow-x: hidden;
                        overflow-y: auto;
                    }
                    .berocket_admin_notice .berocket_notice_content {
                        display: inline-block;
                        vertical-align: middle;
                        padding: 5px;
                        max-width: 99%;
                        box-sizing: border-box;
                    }
                    .berocket_admin_notice .berocket_notice_after_content {
                        display: inline-block;
                        vertical-align: middle;
                        height: 100%;
                        width: 0px;
                    }
                    .berocket_admin_notice .berocket_notice_right_content {',
                        ( $notice['rightwidth'] <= 20 ? ' display: none' : 
                        'height: ' . $notice['height'] . 'px;
                        float: right;
                        width: ' . $notice['rightwidth'] . 'px;
                        -webkit-box-shadow: box-shadow: -1px 0 0 0 rgba(0, 0, 0, 0.1);
                        box-shadow: -1px 0 0 0 rgba(0, 0, 0, 0.1);
                        padding-left: 10px;' ),
                    '}
                    .berocket_admin_notice .berocket_no_thanks {',
                        ( $settings_page ? 'display: none!important;' : 'cursor: pointer;
                        color: #0073aa;
                        opacity: 0.5;
                        display: inline-block;' ),
                    '}
                    .berocket_admin_notice .berocket_no_thanks:hover {
                        opacity: 1;
                    }
                    .berocket_admin_notice .berocket_time_left_block {
                        display: inline-block;
                        text-align: center;
                        vertical-align: middle;
                        padding: 0 0 0 10px;
                    }
                    .berocket_notice_content .berocket_button {
                        margin: 0 0 0 10px;
                        min-width: 80px;
                        max-width: 120px;
                        width: 120px;
                        padding: 6px;
                        display: inline;
                        vertical-align: baseline;
                        color: #fff;
                        box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
                        text-shadow: none;
                        border: 0 none;
                        -moz-user-select: none;
                        background: #ff5252 none repeat scroll 0 0;
                        box-sizing: border-box;
                        cursor: pointer;
                        font-size: 15px;
                        outline: 0 none;
                        position: relative;
                        text-align: center;
                        text-decoration: none;
                        transition: box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s, background-color 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s;
                        white-space: nowrap;
                        height: auto;
                        display: inline-block;
                    }
                    ', ( empty($notice['subscribe']) ? '' : '
                    .berocket_admin_notice .berocket_subscribe_form {
                        display: inline-block;
                        padding-right: 10px;
                    }
                    .berocket_admin_notice .berocket_subscribe_form .berocket_subscribe_email {
                        width: 180px;
                        margin: 0;
                        height: 28px
                        display: inline;
                    }
                    .berocket_admin_notice .berocket_subscribe_form .berocket_notice_submit {
                        margin: 0 0 0 10px;
                        min-width: 80px;
                        max-width: 80px;
                        width: 80px;
                        padding: 0;
                        display: inline;
                        vertical-align: baseline;
                        color: #fff;
                        box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
                        text-shadow: none;
                        border: 0 none;
                        -moz-user-select: none;
                        background: #ff5252 none repeat scroll 0 0;
                        box-sizing: border-box;
                        cursor: pointer;
                        font-size: 14px;
                        outline: 0 none;
                        position: relative;
                        text-align: center;
                        text-decoration: none;
                        transition: box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s, background-color 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s;
                        white-space: nowrap;
                        height: auto;
                    }
                    .berocket_admin_notice .berocket_subscribe_form .berocket_notice_submit:hover,
                    .berocket_admin_notice .berocket_subscribe_form .berocket_notice_submit:focus,
                    .berocket_admin_notice .berocket_subscribe_form .berocket_notice_submit:active{
                        background: #ff6e68 none repeat scroll 0 0;
                        color: white;
                    }' ), '
                    @media screen and (max-width: ', ($notice['image']['width'] * $notice['image']['scale'] + $notice['rightwidth'] + $notice['contentwidth'] + 10), 'px) {
                        div.berocket_admin_notice .berocket_notice_content_wrap {
                            margin-left: 0;
                            margin-right: 0;
                            clear: both;
                            height: initial;
                        }
                        div.berocket_admin_notice {
                            height: initial;
                            text-align: center;
                        }
                        .berocket_admin_notice .berocket_notice_img {
                            float: none;
                            display: inline-block;
                        }
                        div.berocket_admin_notice .berocket_notice_right_content {
                            display: block;
                            float: none;
                            clear: both;
                            width: 100%;
                            -webkit-box-shadow: none;
                            box-shadow: none;
                        }
                    }
                </style>
                <script>
                    jQuery(document).ready(function() {
                        jQuery(document).on("click", ".berocket_admin_notice .berocket_no_thanks", function(event){
                            event.preventDefault();
                            var notice = jQuery(this).parents(".berocket_admin_notice").data("notice");
                            console.log(notice);
                            jQuery.post(ajaxurl, {action:"berocket_admin_close_notice", notice:notice}, function(data){console.log(data)});
                            jQuery(this).parents(".berocket_admin_notice").hide();
                        });
                    });';
                if( $notice['end'] < strtotime(self::$end_soon_time) && $notice['end'] != 0 ) {
                    echo 'setInterval(function(){
                        jQuery(".berocket_admin_notice .berocket_time_left").each(function(i, o) {
                            var left_time = jQuery(o).data("time");
                            var time = left_time;
                            if( time <= 0 ) {
                                jQuery(o).parents(".berocket_admin_notice").hide();
                            } else {
                                time--;
                                jQuery(o).data("time", time);
                                var str = "";
                                if ( time >= 3600 ) {
                                    hours = Math.floor( time/3600 );
                                    time  = time%3600;
                                    str += ("0" + hours).slice(-2) + ":";
                                }
                                if ( time >= 60 || left_time >= 3600 ) {
                                    minutes = Math.floor( time/60 );
                                    time  = time%60;
                                    str += ("0" + minutes).slice(-2) + ":";
                                }
                                seconds = time;
                                str += ("0" + seconds).slice(-2);
                                jQuery(o).html(str);
                            }
                        });
                    }, 1000);';
                }
                echo '</script>';
                self::echo_jquery_functions();
            }
        }
        public static function echo_jquery_functions() {
            if( ! self::$jquery_script_exist ) {
                self::$jquery_script_exist = true;
                echo '<script>
                    jQuery(document).on("berocket_subscribed", ".berocket_admin_notice", function(){
                        jQuery(this).find(".berocket_no_thanks").click();
                    });
                    jQuery(document).on("berocket_incorrect_email", ".berocket_admin_notice", function(){
                        jQuery(this).find(".berocket_subscribe_form").addClass("form-invalid");
                    });
                    jQuery(document).on("change", ".berocket_admin_notice", function(){
                        jQuery(this).find(".berocket_subscribe_form").removeClass("form-invalid");
                    });
                    var berocket_email_submited = false;
                    jQuery(document).on("submit berocket_subscribe_send", ".berocket_subscribe_form", function(event){
                        event.preventDefault();
                        event.stopPropagation();
                        var $this = jQuery(this);
                        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        var email = $this.find("[name=email]").val();
                        if( ! re.test(email) ) {
                            $this.trigger("berocket_incorrect_email");
                            return false;
                        }
                        if( ! berocket_email_submited ) {
                            berocket_email_submited = true;
                            if( $this.is("form") ) {
                                var data = $this.serialize();
                            } else {
                                var data = {email:email, action: $this.find("[name=\'action\']").val()};
                            }
                            var url = $this.attr("action");
                            $this.trigger("berocket_subscribing");
                            jQuery.post(url, data, function(data){
                                $this.trigger("berocket_subscribed");
                            }).fail(function(){
                                $this.trigger("berocket_not_subscribed");
                            });
                        }
                    });
                    jQuery(document).on("berocket_subscribing", ".berocket_subscribe", function(event) {
                        event.preventDefault();
                        jQuery(this).hide();
                    });
                    jQuery(document).on("berocket_incorrect_email", ".berocket_subscribe", function(event) {
                        event.preventDefault();
                        jQuery(this).addClass("form-invalid").find(".error").show();
                    });
                    jQuery(document).on("keyup", ".berocket_subscribe.berocket_subscribe_form .berocket_subscribe_email", function(event) {
                        var keyCode = event.keyCode || event.which;
                        if (keyCode === 13) {
                            event.preventDefault();
                            jQuery(this).parents(".berocket_subscribe_form").trigger("berocket_subscribe_send");
                            return false;
                        }
                    });
                    jQuery(document).on("click", ".berocket_subscribe.berocket_subscribe_form .berocket_notice_submit", function(event) {
                        event.preventDefault();
                        jQuery(this).parents(".berocket_subscribe_form").trigger("berocket_subscribe_send");
                    });
                    
                </script>';
            }
        }
        public static function close_notice($notice = FALSE) {
            self::$subscribed = get_option('berocket_email_subscribed');
            if( ( $notice == FALSE || ! is_array($notice) ) && ! empty($_POST['notice']) ) {
                $notice = $_POST['notice'];
            }
            if( $notice == FALSE || ! is_array($notice) ) {
                wp_die();
            }
            $find_names = array($notice['priority'], $notice['end'], $notice['start'], $notice['name']);
            $current_notice = self::get_notice_by_path($find_names);
            if( isset($current_notice) ) {
                if( $current_notice['end'] < strtotime(self::$end_soon_time) ) {
                    $current_notice['closed'] = 2;
                } else {
                    $current_notice['closed'] = 1;
                }
                if( ! empty($current_notice['repeat']) && ! empty($current_notice['repeatcount']) && ( ! self::$subscribed || ! $current_notice['subscribe'] ) ) {
                    $new_notice = $current_notice;
                    if( empty($current_notice['original']) ) {
                        $new_notice['original'] = $find_names;
                    }
                    $new_notice['repeatcount'] = $current_notice['repeatcount'] - 1;
                    $new_notice['start'] = strtotime($current_notice['repeat']);
                    $new_notice['closed'] = 0;
                    self::set_notice_by_path($new_notice);
                }
                self::set_notice_by_path($current_notice, true);
            }
            update_option('berocket_last_close_notices_time', time());
            wp_die();
        }
        public static function subscribe() {
            if( ! empty($_POST['email']) ) {
                if( $ch = curl_init() ) {
                    update_option('berocket_email_subscribed', true);
                    curl_setopt($ch, CURLOPT_URL,"http://berocket.com/main/subscribe");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "email=" . $_POST['email']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    echo curl_exec ($ch);
                    curl_close ($ch);
                }
            }
            wp_die();
        }
        public static function generate_subscribe_notice() {
            new berocket_admin_notices(array(
                'start' => 0,
                'end'   => 0,
                'name'  => 'subscribe',
                'html'  => 'Subscribe to get latest BeRocket news and updates, plugin recommendations and configuration help, promotional email with discount codes.',
                'subscribe'  => true,
                'image'  => array(
                    'local' => plugin_dir_url( __FILE__ ) . '../images/ad_white_on_orange.png',
                ),
            ));
        }
    }
    add_action( 'admin_notices', array('berocket_admin_notices', 'display_admin_notice') );
    add_action( 'wp_ajax_berocket_admin_close_notice', array('berocket_admin_notices', 'close_notice') );
    add_action( 'wp_ajax_berocket_subscribe_email', array('berocket_admin_notices', 'subscribe') );
}
?>
