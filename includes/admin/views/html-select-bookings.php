<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

wp_enqueue_style( 'woocommerce_admin_styles' );
wp_enqueue_script( 'wc-enhanced-select' );
wp_enqueue_script( 'jquery-ui-datepicker' );
wp_enqueue_style( 'jquery-ui-style' );

$bookable_products = array( '' => __( 'Select product', 'woocommerce-print-bookings' ) );

foreach ( WC_Bookings_Admin::get_booking_products() as $bookable_product ) {
	$bookable_products[ $bookable_product->get_id() ] = $bookable_product->get_name();

	/*$resources = $bookable_product->get_resources();

	foreach ( $resources as $resource ) {
		$bookable_products[ $bookable_product->get_id() . '=>' . $resource->get_id() ] = '&nbsp;&nbsp;&nbsp;' . $resource->get_name();
	}*/
}

?>
<div class="wrap woocommerce-print-bookings">
	<h2><?php _e( 'Print Bookings', 'woocommerce-print-bookings' ); ?></h2>

	<p><?php _e( 'Select a bookable product, product category and the date and time of the bookings you wish to get results for and press the "Get Bookings" button.', 'woocommerce-print-bookings' ); ?></p>

	<?php $this->show_errors(); ?>

	<form method="POST" name="print-bookings-form" action="<?php echo add_query_arg( array( 'page' => 'print-bookings', 'step' => '2' ), admin_url( "edit.php?post_type=wc_booking" ) ); ?>">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="booked_product"><?php _e( 'Booked Product', 'woocommerce-print-bookings' ); ?></label>
					</th>
					<td>
						<?php
						woocommerce_wp_select( array(
							'id'            => 'product_id',
							'class'         => 'wc-enhanced-select',
							'wrapper_class' => 'form-field form-field-wide',
							'label'         => '',
							'options'       => $bookable_products,
							'style'         => 'width:320px;'
						) );
						?>
					</td>
				</tr>
				<!--tr valign="top">
					<th scope="row">
						<label for="product"><?php _e( 'Is Bookable Product or a Resource?', 'woocommerce-print-bookings' ); ?></label>
					</th>
					<td>
						<?php
						woocommerce_wp_select( array(
							'id'            => 'product_or_resource',
							'class'         => 'wc-enhanced-select',
							'wrapper_class' => 'form-field form-field-wide',
							'label'         => '',
							'options'       => array(
								'_booking_product_id'  => esc_attr( 'Bookable Product', 'woocommerce-print-bookings' ),
								'_booking_resource_id' => esc_attr( 'Resource', 'woocommerce-print-bookings' )
							),
							'style'         => 'width:320px;'
						) );
						?>
					</td>
				</tr-->
				<tr valign="top">
					<th scope="row">
						<label for="category"><?php _e( 'Category', 'woocommerce-print-bookings' ); ?></label>
					</th>
					<td>
						<?php
						$categories = get_categories( array(
							'orderby'    => 'name',
							'parent'     => 0,
							'hide_empty' => 0,
							'taxonomy'   => 'product_cat'
						) );

						$category_options = array(
							'' => esc_attr( 'Select Product Category', 'woocommerce-print-bookings' )
						);

						foreach ( $categories as $category ) {
							$category_options[$category->term_id] = esc_html( $category->cat_name ) . " (" . esc_html( $category->category_count ) . ")";
						}

						woocommerce_wp_select( array(
							'id'            => 'product_category',
							'class'         => 'wc-enhanced-select',
							'wrapper_class' => 'form-field form-field-wide',
							'label'         => '',
							'options'       => $category_options,
							'style'         => 'width:320px;'
						) );
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="bookable_start_date"><?php _e( 'Booking date &amp; time', 'woocommerce-print-bookings' ); ?></label>
					</th>
					<td>
						<?php
						woocommerce_wp_text_input( array(
							'id'          => 'booking_start_date',
							'label'       => __( 'Start date:', 'woocommerce-print-bookings' ),
							'placeholder' => 'yyyy-mm-dd',
							'value'       => date( 'Y-m-d' ),
							'class'       => 'booking_start_date',
							'style'       => 'width:100px;'
						) );

						woocommerce_wp_text_input( array(
							'id'          => 'booking_end_date',
							'label'       => __( 'End date:', 'woocommerce-print-bookings' ),
							'placeholder' => 'yyyy-mm-dd',
							'value'       => date( 'Y-m-d' ),
							'class'       => 'booking_end_date',
							'style'       => 'width:100px;'
						) );

						woocommerce_wp_checkbox( array(
							'id'          => 'booking_all_day',
							'label'       => __( 'All day booking:', 'woocommerce-print-bookings' ),
							'description' => __( 'Check this box if the booking is for all day.', 'woocommerce-print-bookings' ),
							'value'       => 'no',
						) );

						woocommerce_wp_text_input( array(
							'id'          => 'booking_start_time',
							'label'       => __( 'Start time:', 'woocommerce-print-bookings' ),
							'placeholder' => 'hh:mm',
							'style'       => 'width:100px;',
							'value'       => date( 'H:i' )
						) );

						woocommerce_wp_text_input( array(
							'id'          => 'booking_end_time',
							'label'       => __( 'End time:', 'woocommerce-print-bookings' ),
							'placeholder' => 'hh:mm',
							'style'       => 'width:100px;',
							'value'       => '23:59'
						) );
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="order_notes"><?php _e( 'Order Notes', 'woocommerce-print-bookings' ); ?></label>
					</th>
					<td>
						<?php
						woocommerce_wp_checkbox( array(
							'id'          => 'show_order_notes',
							'label'       => '',
							'description' => __( 'Check this box to show order notes.', 'woocommerce-print-bookings' ),
							'value'       => 'no',
						) );
						?>
					</td>
				</tr>

				<?php do_action( 'woocommerce_print_bookings_before_print_button' ); ?>
				<tr valign="top">
					<th scope="row">&nbsp;</th>
					<td>
						<input type="submit" name="get_bookings" class="button-primary" value="<?php _e( 'Get Bookings', 'woocommerce-print-bookings' ); ?>" />
						<?php wp_nonce_field( 'get_bookings' ); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
