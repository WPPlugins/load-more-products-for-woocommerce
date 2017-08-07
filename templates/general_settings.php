<?php $options = BeRocket_LMP::get_lmp_option ( 'br_lmp_general_settings' );
$list_grid_options = get_option( 'br_lgv_product_count_option' ); ?>
<input name="br_lmp_general_settings[settings_name]" type="hidden" value="br_lmp_general_settings">
<table class="form-table">
    <tr>
        <th><?php _e( 'Products Loading Type', BeRocket_LMP_domain ) ?></th>
        <td>
            <select name="br_lmp_general_settings[type]">
                <option value="infinity_scroll"<?php if ( $options['type'] == 'infinity_scroll' ) echo ' selected'; ?>><?php _e( 'Infinity Scroll', BeRocket_LMP_domain ) ?></option>
                <option value="more_button"<?php if ( $options['type'] == 'more_button' ) echo ' selected'; ?>><?php _e( 'Load More Button', BeRocket_LMP_domain ) ?></option>
                <option value="pagination"<?php if ( $options['type'] == 'pagination' ) echo ' selected'; ?>><?php _e( 'AJAX Pagination', BeRocket_LMP_domain ) ?></option>
            </select>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Loading Image', BeRocket_LMP_domain ) ?></th>
        <td>
            <?php echo berocket_font_select_upload('', 'br_lmp_loading_image', 'br_lmp_general_settings[loading_image]', $options['loading_image'], true, false); ?>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Buffer Pixels', BeRocket_LMP_domain ) ?></th>
        <td>
            <input name="br_lmp_general_settings[buffer]" value="<?php echo $options['buffer']; ?>" type="number"><label>px</label>
        </td>
    </tr>
</table>
