<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$file_name = ( $file['name'] ) ? $file['name'] : wc_get_filename_from_url( $file['file'] );

?>

<tr>

	<td class="file_name">
		<input type="text" class="input_text" placeholder="<?php esc_attr_e( 'File name', 'marketplace' ); ?>" name="_mp_dwnld_file_names[]" value="<?php echo esc_attr( $file_name ); ?>" />
		<input type="hidden" name="_mp_dwnld_file_hashes[]" value="<?php echo esc_attr( $key ); ?>" />
	</td>

	<td class="file_url">
		<input type="text" class="input_text" placeholder="<?php esc_attr_e( 'http://', 'marketplace' ); ?>" name="_mp_dwnld_file_urls[]" value="<?php echo esc_attr( $file['file'] ); ?>" />
	</td>

	<td class="file_url_choose" width="1%">
		<a href="#" class="button upload_file_button upload_downloadable_file" data-choose="<?php esc_attr_e( 'Choose file', 'marketplace' ); ?>" data-update="<?php esc_attr_e( 'Insert file URL', 'marketplace' ); ?>"><?php echo str_replace( ' ', '&nbsp;', esc_html__( 'Choose file', 'marketplace' ) ); ?></a>
	</td>

	<td width="1%">
		<a href="#" id="delprod" class="mp-action delete"><?php esc_html_e( 'Delete', 'marketplace' ); ?></a>
	</td>

</tr>
