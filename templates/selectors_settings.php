<?php $options = BeRocket_LMP::get_lmp_option ( 'br_lmp_selectors_settings' ); ?>
<input name="br_lmp_selectors_settings[settings_name]" type="hidden" value="br_lmp_selectors_settings">
<table class="form-table">
    <tr<?php if ( ! br_is_plugin_active( 'filters', '2.0.5' ) ) { echo ' style="display:none;"'; } ?>>
        <th><?php _e( 'Use selectors from WooCommerce AJAX Products Filter plugin', BeRocket_LMP_domain ) ?></th>
        <td>
            <input id="lmp_use_mobile_hide" name="br_lmp_selectors_settings[use_filters_settings]" value="1" type="checkbox"<?php if ( $options['use_filters_settings'] ) echo ' checked'; ?>>
        </td>
    </tr>
    <tr class="lmp_use_mobile"<?php if ( br_is_plugin_active( 'filters', '2.0.5' ) && $options['use_filters_settings'] ) { echo ' style="display:none;"'; } ?>>
        <th scope="row"><?php _e('Products Container Selector', BeRocket_LMP_domain) ?></th>
        <td>
            <input name="br_lmp_selectors_settings[products]" type='text' value='<?php echo $options['products']?>'/>
        </td>
    </tr>
    <tr class="lmp_use_mobile"<?php if ( br_is_plugin_active( 'filters', '2.0.5' ) && $options['use_filters_settings'] ) { echo ' style="display:none;"'; } ?>>
        <th scope="row"><?php _e('Product Item Selector', BeRocket_LMP_domain) ?></th>
        <td>
            <input name="br_lmp_selectors_settings[item]" type='text' value='<?php echo $options['item']?>'/>
        </td>
    </tr>
    <tr class="lmp_use_mobile"<?php if ( br_is_plugin_active( 'filters', '2.0.5' ) && $options['use_filters_settings'] ) { echo ' style="display:none;"'; } ?>>
        <th scope="row"><?php _e('Pagination Selector', BeRocket_LMP_domain) ?></th>
        <td>
            <input name="br_lmp_selectors_settings[pagination]" type='text' value='<?php echo $options['pagination']?>'/>
        </td>
    </tr>
    <tr class="lmp_use_mobile"<?php if ( br_is_plugin_active( 'filters', '2.0.5' ) && $options['use_filters_settings'] ) { echo ' style="display:none;"'; } ?>>
        <th scope="row"><?php _e('Next Page Selector', BeRocket_LMP_domain) ?></th>
        <td>
            <input name="br_lmp_selectors_settings[next_page]" type='text' value='<?php echo $options['next_page']?>'/>
        </td>
    </tr>
</table>