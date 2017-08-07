(function ($){
    $(document).ready( function () {
        $(document).on( 'click', '#lmp_use_mobile_show', function() {
            if( $(this).prop('checked') ) {
                $('.lmp_use_mobile').show();
            } else {
                $('.lmp_use_mobile').hide();
            }
        });
        $(document).on( 'click', '#lmp_use_mobile_hide', function() {
            if( $(this).prop('checked') ) {
                $('.lmp_use_mobile').hide();
            } else {
                $('.lmp_use_mobile').show();
            }
        });
        function set_button_settings( $settings ) {
            var style = $settings.data('style');
            var type = $settings.data('type');
            var data = $settings.val();
            var $button = $( '.lmp_load_more_button .lmp_button' );
            if ( style == 'text' ) {
                $button.text(data);
            } else {
                if ( type == 'int' ) {
                    $button.css( style, parseInt( data ) );
                } else {
                    $button.css( style, data );
                }
            }
        }
        $(document).on( 'change', '.lmp_button_table .lmp_button_settings', function () {
            set_button_settings( $(this) );
        });
        $(document).on( 'mouseenter', '.lmp_load_more_button .lmp_button', function () {
            $( '.lmp_button_settings_hover' ).each( function ( i, o ) {
                set_button_settings( $(o) );
            });
        });
        $(document).on( 'mouseleave', '.lmp_load_more_button .lmp_button', function () {
            $( '.lmp_button_settings' ).each( function ( i, o ) {
                set_button_settings( $(o) );
            });
        });
        $('.colorpicker_field').each(function (i,o){
            $(o).css('backgroundColor', $(o).data('color'));
            $(o).colpick({
                layout: 'hex',
                submit: 0,
                color: $(o).data('color'),
                onChange: function(hsb,hex,rgb,el,bySetColor) {
                    $(el).css('backgroundColor', '#'+hex).next().val('#'+hex).trigger('change');
                }
            })
        });
        $(document).on( 'click', '.lmp_button_table .all_theme_default_lmp', function ( event ) {
            event.preventDefault();
            $( '.lmp_button_table .lmp_button_settings, .lmp_button_table .lmp_button_settings_hover' ).each( function ( i, o ) {
                $(o).val( $(o).data( 'default' ) );
            });
            $( '.lmp_button_table .lmp_button_settings' ).trigger( 'change' );
            $( '.lmp_button_table .colorpicker_field' ).each( function ( i, o ) {
                $(o).css( 'backgroundColor', $(o).next().val() ).colpickSetColor( $(o).next().val() );
            });
            
        });
    });
})(jQuery);