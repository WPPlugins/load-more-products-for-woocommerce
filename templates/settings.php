<div class="wrap">
<?php 
$dplugin_name = 'WooCommerce Load More Products';
$dplugin_link = 'http://berocket.com/product/woocommerce-load-more-products';
$dplugin_price = 16;
$dplugin_desc = '';
@ include 'settings_head.php';
@ include 'discount.php';
?>
<div class="wrap show_premium">
    <div id="icon-themes" class="icon32"></div>  
    <h2>Load More Products Settings</h2>  
    <?php settings_errors(); ?>  

    <?php $active_tab = isset( $_GET[ 'tab' ] ) ? @ $_GET[ 'tab' ] : 'general'; ?>  

    <h2 class="nav-tab-wrapper">  
        <a href="?page=br-load-more-products&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', BeRocket_LMP_domain) ?></a> 
        <a href="?page=br-load-more-products&tab=button" class="nav-tab <?php echo $active_tab == 'button' ? 'nav-tab-active' : ''; ?>"><?php _e('Button', BeRocket_LMP_domain) ?></a> 
        <a href="?page=br-load-more-products&tab=selectors" class="nav-tab <?php echo $active_tab == 'selectors' ? 'nav-tab-active' : ''; ?>"><?php _e('Selectors', BeRocket_LMP_domain) ?></a>
        <a href="?page=br-load-more-products&tab=javascript" class="nav-tab <?php echo $active_tab == 'javascript' ? 'nav-tab-active' : ''; ?>"><?php _e('JavaScript', BeRocket_LMP_domain) ?></a> 
    </h2>  

    <form class="lmp_submit_form" method="post" action="options.php">  
        <?php 
        if( $active_tab == 'general' ) { 
            settings_fields( 'br_lmp_general_settings' );
            do_settings_sections( 'br_lmp_general_settings' );
            echo '<input type="submit" class="button-primary" value="'.__('Save Changes', BeRocket_LMP_domain).'" />';
        } else if( $active_tab == 'button' ) {
            settings_fields( 'br_lmp_button_settings' );
            do_settings_sections( 'br_lmp_button_settings' ); 
            echo '<input type="submit" class="button-primary" value="'.__('Save Changes', BeRocket_LMP_domain).'" />';
        } else if( $active_tab == 'selectors' ) {
            settings_fields( 'br_lmp_selectors_settings' );
            do_settings_sections( 'br_lmp_selectors_settings' ); 
            echo '<input type="submit" class="button-primary" value="'.__('Save Changes', BeRocket_LMP_domain).'" />';
        } else if( $active_tab == 'javascript' ) {
            settings_fields( 'br_lmp_javascript_settings' );
            do_settings_sections( 'br_lmp_javascript_settings' ); 
            echo '<input type="submit" class="button-primary" value="'.__('Save Changes', BeRocket_LMP_domain).'" />';
        }
        ?>
    </form>
</div>
<?php
/*
	<h3>Receive more features and control with Paid version of the plugin:</h3>

	<ul>
		<li><b>- Image Lazy Load</b></li>
		<li><b>- 40 Animations for Lazy Load</b></li>
		<li><b>- Customization for Load More Button</b></li>
		<li><b>- Customizable Loading Image</b></li>
		<li><b>- Custom text for "Loading" and "There is no products" messages</b></li>
		<li><b>- WPML support for text</b></li>
		<li><b>- Different Products Load Type for Mobile Devices and Other</b></li>
	</ul>

	<h4>Support the plugin by purchasing paid version. This will provide faster growth, better support and much more functionality for the plugin!</h4>
*/
$feature_list = array(
    'Image Lazy Load',
    '40 Animations for Lazy Load',
    'Customization for Load More Button',
    'Customizable Loading Image',
    'Custom text for "Loading" and "There is no products" messages',
    'WPML support for text',
    'Different Products Load Type for Mobile Devices and Other',
);
@ include 'settings_footer.php';
?>
</div>
