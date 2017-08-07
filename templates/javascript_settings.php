<?php $options = BeRocket_LMP::get_lmp_option ( 'br_lmp_javascript_settings' ); ?>
<input name="br_lmp_javascript_settings[settings_name]" type="hidden" value="br_lmp_javascript_settings">
<table class="form-table">
    <tr>
        <th><?php _e( 'Before Update', BeRocket_LMP_domain ) ?></th>
        <td>
            <textarea name="br_lmp_javascript_settings[before_update]"><?php echo $options['before_update']; ?></textarea>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'After Update', BeRocket_LMP_domain ) ?></th>
        <td>
            <textarea name="br_lmp_javascript_settings[after_update]"><?php echo $options['after_update']; ?></textarea>
        </td>
    </tr>
</table>