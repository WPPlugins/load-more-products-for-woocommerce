<?php if( ! empty($feature_list) && count($feature_list) > 0 ) { ?>
    <div class="paid_features">
        <?php
        $feature_text = '';
        foreach($feature_list as $feature) {
            $feature_text .= '<li>'.$feature.'</li>';
        }
        $text = '<h3>Receive more features and control with Paid version of the plugin:</h3>
        <div>
        <ul>
            %feature_list%
        </ul>
        </div>
        <div><a class="get_premium_version" href="%link%">PREMIUM VERSION</a></div>
        <p>Support the plugin by purchasing paid version. This will provide faster growth, better support and much more functionality for the plugin</p>';
        $text = str_replace('%feature_list%', $feature_text, $text);
        echo $text;
        ?>
    </div>
    <style>
    .paid_features, .berocket_subscribe {
        border: 1px solid #c29a9a;
        background: white;
        float: right;
        clear: right;
        width: 28%;
        box-sizing: border-box;
        text-align: center;
        padding: 0 25px;
        margin-bottom: 30px;
    }
    .paid_features {
        font-weight: 600;
    }
    .paid_features ul li {
        text-align: left;
    }
    .berocket_subscribe {
        background-color: #2c3644;
        background-image: url(<?php echo plugin_dir_url( __FILE__ ) . 'mail.png'; ?>);
        background-position: right top;
        background-repeat: no-repeat;
        color: #aaa;
        font-size: 16px;
        overflow: hidden;
    }
    .berocket_subscribe h3 {
        color: white;
    }
    .berocket_subscribe p {
        font-size: 16px;
        text-align: left;
    }
    .berocket_subscribe .berocket_subscribe_email {
        background-color: #404c5d;
        border: 0;
        outline: 0;
        color: #aaa;
        width: 100%;
        line-height: 2em;
        font-size: 16px;
    }
    .berocket_subscribe .berocket_notice_submit,
    .get_premium_version {
        display: inline-block;
        background-color: rgb(239, 109, 109);
        border-color: rgb(222, 72, 72);
        color: white;
        font-size: 20px;
        height: auto;
        padding: 10px 41px;
        margin: 1em 0 1em 0;
        text-decoration: none;
        cursor: pointer;
        outline: none;
        box-shadow: none;
        border: none;
        text-shadow: none;
        font-weight: 600;
        line-height: 1em;
        border-radius: 0;
    }
    .berocket_subscribe .berocket_notice_submit:hover,
    .get_premium_version:hover {
        color: white;
        background-color: rgb(222, 72, 72);
        box-shadow: none;
        border: none;
    }
    .berocket_subscribe .berocket_notice_submit:focus,
    .get_premium_version:focus,
    .berocket_subscribe .berocket_notice_submit:active,
    .get_premium_version:active {
        color: white;
        background-color: rgb(222, 72, 72);
        -webkit-box-shadow: 0 0 0 1px #5b9dd9, 0 0 2px 1px rgba(30,140,190,.8);
        box-shadow: 0 0 0 1px #5b9dd9, 0 0 2px 1px rgba(30,140,190,.8);
    }
    .paid_features ul li{
        list-style: initial;
        margin-left: 2em;
    }
    .show_premium {
        float: left;
        width: 70%;
        box-sizing: border-box;
    }
    @media screen and (max-width: 900px) {
        .show_premium,
        .paid_features,
        .berocket_subscribe {
            float: none;
            width: 100%;
        }

        .paid_features,
        .berocket_subscribe {
            margin-top: 30px;
            margin-bottom: 0;
        }
    }
    </style>
    <?php
    $subscribed = get_option('berocket_email_subscribed');
    if( ! $subscribed ) {
        $user_email = wp_get_current_user();
        if( isset($user_email->user_email) ) {
            $user_email = $user_email->user_email;
        } else {
            $user_email = '';
        }
        ?>
        <div class="berocket_subscribe">
            <h3>OUR NEWSLETTER</h3>
            <p>Get awesome content delivered straight to your inbox.</p>
            <form class="berocket_subscribe_form" method="POST" action="<?php echo admin_url( 'admin-ajax.php' ); ?>">
                <input type="hidden" name="action" value="berocket_subscribe_email">
                <input class="berocket_subscribe_email" type="email" name="email" placeholder="Enter your email address" value="<?php echo $user_email; ?>">
                <input type="submit" class="berocket_notice_submit" value="GET UPDATE">
            </form>
        </div>
        <?php
        berocket_admin_notices::echo_jquery_functions();
    }
    $text = '<h4 style="clear:both;">Both <a href="%plugin_link%" target="_blank">Free</a> and <a href="%link%" target="_blank">Paid</a> versions of %plugin_name% developed by <a href="http://berocket.com" target="_blank">BeRocket</a></h4>';
} else {
   $text = '<h4 style="clear:both;"><a href="%plugin_link%" target="_blank">%plugin_name%</a> developed by <a href="http://berocket.com" target="_blank">BeRocket</a></h4>';
}
$text = str_replace('%link%', $dplugin_link, $text);
$text = str_replace('%plugin_name%', @ $plugin_info['Name'], $text);
$text = str_replace('%plugin_link%', @ $plugin_info['PluginURI'], $text);
echo $text;
?>
