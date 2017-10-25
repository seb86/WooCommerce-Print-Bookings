<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

if ( ! empty( $booked_product ) ) {
	$args = array(
		'post_type'      => 'wc_booking',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'order'          => 'ASC',
	);

	// Filter the booking status
	if ( ! empty( $post_status ) ) {
		$post_status_arg = array(
			'post_status' => $post_status
		);
	}

	$args = wp_parse_args( $post_status_arg, $args );

	if ( ! empty( $start_time ) && $booking_all_day == "no" ) {
		$start_date = date_i18n( wc_date_format() . ', ' . wc_time_format(), strtotime( $start_date.$start_time ) );
	}
	else {
		$start_date = date_i18n( wc_date_format() . ', ' . wc_time_format(), strtotime( $start_date ) );
	}

	if ( ! empty( $end_date ) ) {
		if ( ! empty( $end_time ) && $booking_all_day == "no" ) {
			$end_date = date_i18n( wc_date_format() . ', ' . wc_time_format(), strtotime( $end_date.$end_time ) );
		}
		else {
			$end_date = date_i18n( wc_date_format() . ', ' . wc_time_format(), strtotime( $end_date ) );
		}
	}

	if ( empty( $end_date ) ) {
		$booking_date_args = array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => '_booking_product_id',
					'value' => $product_id
				),
				array(
					'key'     => '_booking_start',
					'value'   => esc_sql( date( 'YmdHis', strtotime( $start_date ) ) ),
					'compare' => '>=',
					'orderby' => 'meta_value_num'
				)
			)
		);
	}
	else {
		$booking_date_args = array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => '_booking_product_id',
					'value' => $product_id
				),
				array(
					'key'     => '_booking_start',
					'value'   => esc_sql( date( 'YmdHis', strtotime( $start_date ) ) ),
					'compare' => '>=',
					'orderby' => 'meta_value_num'
				),
				array(
					'key'     => '_booking_end',
					'value'   => esc_sql( date( 'YmdHis', strtotime( $end_date ) ) ),
					'compare' => '<=',
				)
			)
		);
	}

	$args = wp_parse_args( $booking_date_args, $args );

	$args = apply_filters( 'woocommerce_print_bookings_query', $args );

	$bookings = get_posts( $args );
}

if ( ! empty( $booked_product ) && empty( $bookings ) ) {
	$this->errors[] = __( 'No bookings found! <a href="javascript:history.back()">Go back</a> and make a new selection.', 'woocommerce-print-bookings' );
}
?>
<div class="wrap woocommerce-print-bookings">
	<div class="print-bookings-results">
		<h2>
		<?php
		if ( empty( $booked_product ) ) {
			_e( 'Print Bookings', 'woocommerce-print-bookings' );
		} else {
			_e( 'Print Bookings', 'woocommerce-print-bookings' ); ?> <a href="#" class="page-title-action print-table-results"><?php _e( 'Print Results', 'woocommerce-print-bookings' ); ?></a>
		<?php } ?>
		</h2>

		<?php
		if ( WP_DEBUG ) print_r($args);

		$this->show_errors();

		if ( ! empty( $booked_product ) && empty( $bookings ) ) {
			exit();
		}

		// Prevent the page from loading any further if the page was refreshed.
		if ( $step == 2 && empty( $booked_product ) ) {
			exit();
		}
		?>

		<p>
		<?php if ( ! empty( $end_date ) ) {
			if ( $booking_all_day == "yes" ) {
				printf( __( 'Here are your orders for booking (%1$s) starting on %2$s and ending on %3$s.', 'woocommerce-print-bookings' ), $booked_product->get_name(), date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ), date_i18n( get_option( 'date_format' ), strtotime( $end_date ) ) );
			}
			else{
				printf( __( 'Here are your orders for booking (%1$s) starting on %2$s and ending on %3$s.', 'woocommerce-print-bookings' ), $booked_product->get_name(), date_i18n( get_option( 'date_format' ), strtotime( $start_date.$start_time ) ), date_i18n( get_option( 'date_format' ), strtotime( $end_date.$end_time ) ) );
			}
		}
		else{
			printf( __( 'Here are your orders for booking (%1$s) starting on %2$s.', 'woocommerce-print-bookings' ), $booked_product->get_name(), date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ) );
		}
		?>
		</p>
	</div>

	<form method="POST" name="print-bookings-form" action="<?php echo add_query_arg( array( 'page' => 'print-bookings', 'step' => '2' ), admin_url( "edit.php?post_type=wc_booking" ) ); ?>">
		<input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
		<input type="hidden" name="product_category" value="<?php echo $category; ?>" />
		<input type="hidden" name="booking_start_date" value="<?php echo $start_date; ?>" />
		<input type="hidden" name="booking_end_date" value="<?php echo $end_date; ?>" />
		<input type="hidden" name="booking_all_day" value="<?php echo $booking_all_day; ?>" />
		<input type="hidden" name="booking_start_time" value="<?php echo $start_time; ?>" />
		<input type="hidden" name="booking_end_time" value="<?php echo $end_time; ?>" />
		<input type="hidden" name="show_order_notes" value="<?php echo $show_order_notes; ?>" />
		<?php do_action( 'woocommerce_print_bookings_hidden_fields' ); ?>
		<?php wp_nonce_field( 'get_bookings' ); ?>
	</form>

	<div style="overflow-x:auto;">
		<table id="print-bookings-table">
			<tbody>
				<tr valign="top">
					<?php
					$table_columns = apply_filters( 'woocommerce_print_bookings_column', array(
						'booking_date'   => __( 'Booking Date and Time', 'woocommerce-print-bookings' ),
						'customers_name' => __( 'Customers Name', 'woocommerce-print-bookings' ),
						'order'          => '#' . __( 'Order', 'woocommerce-print-bookings' ),
						'order_items'    => __( 'Order Items and Meta', 'woocommerce-print-bookings' ),
						'order_notes'    => __( 'Order Notes', 'woocommerce-print-bookings' )
					) );

					foreach ( $table_columns as $key => $column ) {
						$style = '';

						if ( $key == 'order_notes' && ! $show_order_notes ) {
							$style = ' style="display: none;"';
						}

						echo '<th class="' . $key . '"' . $style . ' scope="col">' . $column . '</th>';
					}
					?>
				</tr>

				<?php
				foreach ( $bookings as $booking ) {
					$booking = new WC_Booking( $booking->ID );

					// Order assigned to the booking.
					$order = $booking->get_order();

					// Customers details on the Booking.
					$customer = $booking->get_customer();

					if ( ! empty( $customer->email ) && ! empty( $customer->name ) ) {
						$display_customer = '<a href="mailto:' . esc_attr( $customer->email ) . '">' . esc_html( $customer->name ) . '</a>';
					} else {
						$display_customer = esc_html( $customer->name );
					}

					if ( isset( $show_order_notes ) && $show_order_notes ) {
						$notes = wc_get_order_notes( array(
							'order_id' => $order->get_id(),
							'type'     => 'customer'
						) );

						$display_notes = '';

						if ( $notes ) {
							$display_notes .= '<ul class="order_notes">';

							foreach ( $notes as $note ) {
								$note_classes   = array( 'note' );
								$note_classes[] = $note->customer_note ? 'customer-note' : '';
								$note_classes[] = 'system' === $note->added_by ? 'system-note' : '';
								$note_classes   = array_filter( $note_classes );

								$display_notes .= '<li rel="' . absint( $note->id ) . '" class="' . esc_attr( implode( ' ', $note_classes ) ) . '">';
								$display_notes .= '<div class="note_content">' . wpautop( wptexturize( wp_kses_post( $note->content ) ) ) . '</div>';
								$display_notes .= '<p class="meta"><abbr class="exact-date" title="' . $note->date_created->date( 'y-m-d h:i:s' ) . '">' . sprintf( __( 'added on %1$s at %2$s', 'woocommerce-print-bookings' ), $note->date_created->date_i18n( wc_date_format() ), $note->date_created->date_i18n( wc_time_format() ) ) . '</abbr>';

								if ( 'system' !== $note->added_by ) :
									/* translators: %s: note author */
									$display_notes .= sprintf( ' ' . __( 'by %s', 'woocommerce-print-bookings' ), $note->added_by );
								endif;

								$display_notes .= '</p></li>';
							}

							$display_notes .= '</ul>';
						}
					} // END show order notes

					echo '<tr>';

					foreach ( $table_columns as $key => $column ) {
						switch( $key ) {
							case 'booking_date':
								echo '<td class="' . $key . '">';

								if ( $booking->is_all_day() ) {
									echo __( 'All Day', 'woocommerce-print-bookings' );
								}
								else {
									echo __( 'Start Date', 'woocommerce-print-bookings' ) . ': <strong>' . date_i18n( "F j, Y g:ia", strtotime( $booking->get_start_date( '', 'F j, Y g:ia' ) ) ) . '</strong>';

									if ( ! empty( $end_date ) ) {
										echo '<br>' . __( 'End Date', 'woocommerce-print-bookings' ) . ': <strong>' . date_i18n( "F j, Y g:ia", strtotime( $booking->get_end_date( '', 'F j, Y g:ia' ) ) ) . '</strong>';
									}
								}

								echo "<br>" . __( 'Booking Status', 'woocommerce-print-bookings' ) . ": <strong>" . esc_html( wc_bookings_get_status_label( $booking->get_status() ) ) . "</strong>";
							break;

							case 'customers_name':
								echo '<td class="' . $key . '">';
								echo $display_customer;
							break;

							case 'order':
								echo '<td class="' . $key . '">';

								$order_date = $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i:s' ) : '';

								echo '<a href="' . admin_url( 'post.php?post=' . absint( ( is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id ) ) . '&action=edit' ) . '">#' . esc_html( $order->get_order_number() ) . '</a> - ' . esc_html( wc_get_order_status_name( $order->get_status() ) ) .
								'<br>' . __( 'Order Date', 'woocommerce-print-bookings' ) . ': <strong>' . sprintf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $order_date ) ), date_i18n( wc_date_format(), strtotime( $order_date ) ) ) . '</strong>';

							break;

							case 'order_items':
								echo '<td class="' . $key . '">';

								$items = $order->get_items();

								if ( sizeof( $items ) > 0 ) {
									foreach ( $items as $item ) {
										echo wc_display_item_meta( $item );
									}
								}
							break;

							case 'order_notes':
								if ( isset( $show_order_notes ) && $show_order_notes ) {
									echo '<td class="' . $key . '">';
									echo $display_notes;
								}
							break;

							default:
								echo '<td>';
								do_action( 'woocommerce_print_bookings_column_' . $key, $booking, $order );
						} // END switch

						echo '</td>';

					} // END foreach

					echo '</tr>';
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
</div>
