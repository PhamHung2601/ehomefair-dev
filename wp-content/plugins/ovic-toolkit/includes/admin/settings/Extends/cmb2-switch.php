<?php
function cmb2_render_callback_for_switch( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

    $args = $field->args;

	?>
    <div class="onoffswitch">
        <input value="on" type="checkbox" name="<?php echo $args['id'];?>" class="onoffswitch-checkbox" id="<?php echo $args['id'];?>" <?php checked( $escaped_value, 'on' ); ?>>
        <label class="onoffswitch-label" for="<?php echo $args['id'];?>">
            <span class="onoffswitch-inner"></span>
            <span class="onoffswitch-switch"></span>
        </label>
    </div>
    <p > <span class="cmb2-metabox-description"><?php echo $args['desc'];?></span></p>
    <?php
}
add_action( 'cmb2_render_switch', 'cmb2_render_callback_for_switch', 10, 5 );

function cmb2_sanitize_switch_callback( $override_value, $value ) {
    if( $value == '') return 'off';
    return $value;
}
add_filter( 'cmb2_sanitize_switch', 'cmb2_sanitize_switch_callback', 10, 2 );


