<?php
$company_id = isset( $field['company_id'] ) ? $field['company_id'] : '';
$company_name = isset( $field['company_name'] ) ? $field['company_name'] : '';
$option_value = isset( $field['option_value'] ) ? $field['option_value'] : 'id';
?>
<select name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" <?php if ( ! empty( $field['required'] ) ) echo 'required'; ?> data-placeholder="<?php echo esc_attr( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ); ?>" data-option-value="<?php echo esc_attr( $option_value ); ?>">
	<option value="<?php echo esc_attr( $company_id ); ?>"><?php echo esc_html( $company_name ); ?></option>
</select>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>
