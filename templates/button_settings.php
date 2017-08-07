<?php $options = BeRocket_LMP::get_lmp_option ( 'br_lmp_button_settings' );
$default = BeRocket_LMP::get_lmp_default( 'br_lmp_button_settings' ) ?>
<input name="br_lmp_button_settings[settings_name]" type="hidden" value="br_lmp_button_settings">
<table class="form-table lmp_button_table">
    <tr>
        <th><?php _e( 'Text on button', BeRocket_LMP_domain ) ?></th>
        <td>
            <input class="lmp_button_settings" data-style="text" data-default="<?php echo $default['button_text']; ?>" name="br_lmp_button_settings[button_text]" value="<?php echo $options['button_text']; ?>" type="text">
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Custom Class', BeRocket_LMP_domain ) ?></th>
        <td>
            <input class="lmp_button_settings" name="br_lmp_button_settings[custom_class]" data-default="<?php echo $default['custom_class']; ?>" value="<?php echo $options['custom_class']; ?>" type="text">
        </td>
    </tr>
    <tr>
        <th></th>
        <td>
            <input type="button" value="<?php _e('Set all to default', BeRocket_LMP_domain) ?>" class="all_theme_default_lmp button">
        </td>
    </tr>
</table>
