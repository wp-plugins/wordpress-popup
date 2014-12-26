<?php
/**
 * Code-snippet for WordPress plugin list.
 * Used in function WDev()->html->plugin_list()
 *
 * @since  1.1.0
 *
 * Variables:
 *   - $items
 *   - $lang
 *   - $filters
 */

$item_fields = array(
	'class',
	'title',
	'description',
	'version',
	'author',
	'active',
	'action', // Array
	'details', // Array
	'icon',
	'footer',
);

$current = 'current';

?>
<div class="wpmui-list-wrapper">

<?php if ( ! empty( $filters ) ) : ?>
<div class="wp-filter"><ul class="filter-links"><?php
	foreach ( $filters as $key => $label ) {
		printf(
			'<li><a href="#" class="filter %3$s" data-filter="%1$s">%2$s</a></li>',
			$key,
			$label,
			$current
		);
		$current = '';
	}
?></ul></div>
<?php endif; ?>

<div class="wp-list-table widefat wpmui-list-table">
<div class="the-list wpmui-list">
	<?php foreach ( $items as $item ) :
		WDev()->load_fields( $item, $item_fields );
		$item->action = WDev()->get_array( $item->action );
		$item->details = WDev()->get_array( $item->details );

		$item_class = $item->active ? 'active' : '';
		$item_class .= ' ' . $item->class;
		?>
		<div class="list-card <?php echo esc_attr( $item_class ); ?>">
			<div class="list-card-top">
				<span class="badge-active">
					<?php echo esc_html( $lang->active_badge ); ?>
				</span>
				<div class="item-icon"><?php echo '' . $item->icon; ?></div>
				<div class="name">
					<h4 class="toggle-details is-no-detail">
						<?php echo esc_html( $item->title ); ?>
					</h4>
					<h4 class="is-detail">
						<?php echo esc_html( $item->title ); ?>
					</h4>
				</div>
				<div class="desc">
					<?php echo '' . $item->description; ?>
				</div>
				<div class="action-links">
					<span class="toggle-details toggle-link is-detail">
						<div><?php echo esc_html( $lang->close_details ); ?></div>
						<div class="space"></div>
					</span>
					<?php
					foreach ( $item->action as $action ) {
						WDev()->html->element( $action );
					}
					?>
				</div>
				<div class="details">
					<?php
					foreach ( $item->details as $detail ) {
						if ( is_array( $detail ) ) {
							if ( isset( $detail['ajax_data'] )
								&& is_array( $detail['ajax_data'] )
							) {
								$detail['ajax_data']['_is_detail'] = true;
							}
						}
						WDev()->html->element( $detail );
					}
					?>
				</div>
				<div class="fader"></div>
			</div>
			<div class="list-card-bottom">
				<span class="list-card-footer is-no-detail">
					<?php echo '' . $item->footer; ?>
				</span>
				<span class="toggle-details toggle-link is-no-detail">
					<?php echo esc_html( $lang->show_details ); ?>
				</span>
				<span class="toggle-details toggle-link is-detail">
					<?php echo esc_html( $lang->close_details ); ?>
				</span>
			</div>
		</div>
	<?php endforeach; ?>
</div>
</div>
</div>