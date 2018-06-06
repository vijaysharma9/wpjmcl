<?php
$company_id = isset( $field['company_id'] ) ? $field['company_id'] : '';
$company_name = isset( $field['company_name'] ) ? $field['company_name'] : '';
?>
<select name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" <?php if ( ! empty( $field['required'] ) ) echo 'required'; ?> data-placeholder="<?php echo esc_attr( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ); ?>">
	<option value="<?php echo esc_attr( $company_id ); ?>"><?php echo esc_html( $company_name ); ?></option>
</select>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>
