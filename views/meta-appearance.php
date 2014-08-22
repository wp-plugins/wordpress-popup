<?php
/**
 * Metabox "Appearance"
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

$styles = apply_filters( 'popup-styles', array() );

?>
<div class="wpmui-grid-12">
	<div class="col-12">
		<label for="po-style">
			<strong>
				<?php _e( 'Select which style you want to use:', PO_LANG ); ?>
			</strong>
		</label>
	</div>
</div>
<div class="wpmui-grid-12">
	<div class="col-7">
		<input type="hidden"
			class="po-orig-style"
			name="po_orig_style"
			value="<?php echo esc_attr( $popup->style ); ?>" />
		<input type="hidden"
			class="po-orig-style-old"
			name="po_orig_style_old"
			value="<?php echo esc_attr( $popup->deprecated_style ); ?>" />
		<select class="block" id="po-style" name="po_style">
			<?php foreach ( $styles as $key => $data ) : ?>
				<?php if ( $data->deprecated && $popup->style != $key ) { continue; } ?>
				<?php if ( ! $data->pro && PO_VERSION != 'pro' ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"
						data-old="<?php echo esc_attr( $data->deprecated ); ?>"
						<?php selected( $key, $popup->style ); ?>>
						<?php echo esc_attr( $data->name ); ?>
						<?php if ( $data->deprecated ) : ?>*)<?php endif; ?>
					</option>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php foreach ( $styles as $key => $data ) : ?>
				<?php if ( $data->deprecated ) { continue; } ?>
				<?php if ( $data->pro && PO_VERSION != 'pro' ) : ?>
					<option disabled="disabled">
						<?php echo esc_attr( $data->name ); ?> -
						<?php _e( 'PRO Version only', PO_LANG ); ?>
					</option>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="col-5">
		<label>
			<input type="checkbox"
				name="po_no_round_corners"
				<?php checked( $popup->round_corners, false ); ?> />
			<?php _e( 'No rounded corners', PO_LANG ); ?>
		</label>
	</div>
</div>
<?php if ( $popup->deprecated_style ) :
	?>
	<div class="wpmui-grid-12">
		<div class="col-12">
			<p style="margin-top:0"><em><?php _e(
				'*) This style is outdated and does not support all options '.
				'on this page. ' .
				'Once you save your PopUp with a new style you cannot ' .
				'revert to this style!<br />' .
				'Tipp: Use the Preview function to test this PopUp with one ' .
				'of the new styles before saving it.', PO_LANG
			); ?></em></p>
		</div>
	</div>
	<?php
endif; ?>

<div class="pro-only">
	<div class="wpmui-grid-12">
		<div class="col-12 inp-row">
			<label>
				<input type="checkbox"
					readonly="readonly"
					id="po-custom-colors"
					data-toggle=".chk-custom-colors"
					/>
				<?php _e( 'Use custom colors', PO_LANG ); ?>
			</label>
		</div>
	</div>
	<div class="wpmui-grid-12 chk-custom-colors">
		<div class="col-colorpicker inp-row">
			<input type="text"
				class="colorpicker inp-small"
					readonly="readonly"
				value="<?php echo esc_attr( $popup->color['col1'] ); ?>" />
			<br />
			<?php _e( 'Links, button background, heading and subheading', PO_LANG ); ?>
		</div>
		<div class="col-colorpicker inp-row">
			<input type="text"
				class="colorpicker inp-small"
					readonly="readonly"
				value="<?php echo esc_attr( $popup->color['col2'] ); ?>" />
			<br />
			<?php _e( 'Button text', PO_LANG ); ?>
		</div>
	</div>
	<div class="pro-note">
		<div style="padding:50px 0 0;">
		<?php printf(
			__( 'Pro feature only. <a href="%1$s" target="_blank">Find out more &raquo;</a>', PO_LANG ),
			'http://premium.wpmudev.org/project/the-pop-over-plugin/'
		); ?>
		</div>
	</div>
</div>

<div class="wpmui-grid-12">
	<div class="col-12 inp-row">
		<label>
			<input type="checkbox"
				name="po_custom_size"
				id="po-custom-size"
				data-toggle=".chk-custom-size"
				<?php checked( $popup->custom_size ); ?> />
			<?php _e( 'Use custom size (if selected the PopUp won\'t be responsive)', PO_LANG ); ?>
		</label>
	</div>
</div>
<div class="wpmui-grid-12 chk-custom-size">
	<div class="col-5 inp-row">
		<label for="po-size-width"><?php _e( 'Width:', PO_LANG ); ?></label>
		<input type="text"
			id="po-size-width"
			name="po_size_width"
			class="inp-small"
			value="<?php echo esc_attr( $popup->size['width'] ); ?>"
			placeholder="600px" />
	</div>
	<div class="col-5 inp-row">
		<label for="po-size-height"><?php _e( 'Height:', PO_LANG ); ?></label>
		<input type="text"
			id="po-size-height"
			name="po_size_height"
			class="inp-small"
			value="<?php echo esc_attr( $popup->size['height'] ); ?>"
			placeholder="300px" />
	</div>
</div>