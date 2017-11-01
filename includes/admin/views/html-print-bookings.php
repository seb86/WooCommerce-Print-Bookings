<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

$debug_mode = defined( 'WP_DEBUG' ) && WP_DEBUG ? 'yes' : 'no';

if ( ! empty( $product_id ) ) {
	$args = array(
		'post_type'      => 'wc_booking',
		'post_status'    => get_wc_booking_statuses(),
		'posts_per_page' => -1,
		'offset'         => 0,
		'nopaging'       => true,
		'post_parent'    => 0,
		'meta_key'       => '_booking_product_id',
		'meta_value'     => $product_id,
	);

	// Filter the booking status
	if ( ! empty( $post_status ) ) {
		$post_status_arg = array(
			'post_status' => $post_status
		);

		$args = wp_parse_args( $post_status_arg, $args );
	}

	// Filter bookings based on product category if set.
	if ( ! empty( $category ) ) {
		foreach( $product_id as $key => $prod ) {
			if ( ! $this->bookings_has_term( $category, $prod ) ) {
				unset( $product_id[$key] ); // Remove product ID before querying if the category is not assigned to the product.
			}
		}
	}

	if ( ! empty( $start_time ) && $booking_all_day == "no" ) {
		$query_start_date = date_i18n( wc_date_format() . ', ' . wc_time_format(), strtotime( $start_date . $start_time ) );
	}
	else {
		$query_start_date = date_i18n( wc_date_format() . ', ' . wc_time_format(), strtotime( $start_date ) );
	}

	if ( ! empty( $end_date ) ) {
		if ( ! empty( $end_time ) && $booking_all_day == "no" ) {
			$query_end_date = date_i18n( wc_date_format() . ', ' . wc_time_format(), strtotime( $end_date . $end_time ) );
		}
		else {
			$query_end_date = date_i18n( wc_date_format() . ', ' . wc_time_format(), strtotime( $end_date ) );
		}
	}

	if ( empty( $end_date ) ) {
		$booking_date_args = array(
			'meta_query' => array(
				'relation' => 'AND',
				'_booking_start' => array(
					'key'     => '_booking_start',
					'value'   => esc_sql( date( 'YmdHis', strtotime( $query_start_date ) ) ),
					'compare' => '>=',
				)
			),
			'orderby' => array(
				'_booking_start' => 'ASC'
			)
		);
	}
	else {
		$booking_date_args = array(
			'meta_query' => array(
				'relation' => 'AND',
				'_booking_start' => array(
					'key'     => '_booking_start',
					'value'   => esc_sql( date( 'YmdHis', strtotime( $query_start_date ) ) ),
					'compare' => '>=',
				),
				'_booking_end' => array(
					'key'     => '_booking_end',
					'value'   => esc_sql( date( 'YmdHis', strtotime( $query_end_date ) ) ),
					'compare' => '<=',
				)
			),
			'orderby' => array(
				'_booking_start' => 'ASC',
				'_booking_end'   => 'DESC'
			)
		);
	}

	$args = wp_parse_args( $booking_date_args, $args );

	$args = apply_filters( 'woocommerce_print_bookings_query', $args );

	$bookings = get_posts( $args );
}

if ( ! empty( $product_id ) && empty( $bookings ) ) {
	$this->errors[] = __( 'No bookings found! <a href="javascript:history.back()">Go back</a> and make a new selection.', 'woocommerce-print-bookings' );
}
?>
<div class="wrap woocommerce-print-bookings">
	<div class="print-bookings-results">
		<h2>
		<?php
		if ( empty( $bookings ) ) {
			_e( 'Print Bookings', 'woocommerce-print-bookings' );
		} else {
			_e( 'Print Bookings', 'woocommerce-print-bookings' ); ?> <a href="#" class="page-title-action print-table-results"><?php _e( 'Print Results', 'woocommerce-print-bookings' ); ?></a>
		<?php } ?>
		</h2>

		<?php
		// If debug mode is enabled then print the search query.
		if ( $debug_mode == 'yes' && ! empty( $args ) ) {
			echo __( 'Debug query', 'woocommerce-print-bookings' ) . ': ';
			print_r($args);
			echo '<br>' . __( 'Bookings Found', 'woocommerce-print-bookings' ) . ': ' . count( $bookings );
		}

		$this->show_errors();

		if ( ! empty( $product_id ) && empty( $bookings ) ) {
			exit();
		}

		// Prevent the page from loading any further if the page was refreshed.
		if ( $step == 2 && empty( $product_id ) ) {
			exit();
		}
		?>

		<p>
		<?php
		$bookable_products = array();

		foreach ( $product_id as $product ) {
			$booked_product = wc_get_product( $product );
			$bookable_products[] = $booked_product->get_name();
		}

		$booked_products = implode( ", ", $bookable_products );

		printf( __( 'Here are your orders for the following bookings <strong>(%s)</strong>', 'woocommerce-print-bookings' ), $booked_products );
		echo " ";

		if ( ! empty( $start_time ) ) {
			printf( __( 'starting on <strong>%1$s</strong> @ <strong>%2$s</strong>', 'woocommerce-print-bookings' ), date_i18n( get_option( 'date_format' ), strtotime( $start_date . $start_time ) ), date_i18n( get_option( 'time_format' ), strtotime( $start_date . $start_time ) ) );
			echo " ";
		}
		else {
			if ( $booking_all_day == "yes" ) {
				_e( 'for the whole day.', 'woocommerce-print-bookings' );
			}
			else {
				printf( __( 'starting on <strong>%s</strong>', 'woocommerce-print-bookings' ), date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ) );
				echo " ";
			}
		}

		if ( ! empty( $end_date ) && $booking_all_day != "yes" ) {
			if ( empty( $end_time ) ) {
				printf( __( 'and ending on <strong>%s</strong>', 'woocommerce-print-bookings' ), date_i18n( get_option( 'date_format' ), strtotime( $end_date ) ) );
			}
			else {
				printf( __( 'and ending on <strong>%1$s</strong> @ <strong>%2$s</strong>', 'woocommerce-print-bookings' ), date_i18n( get_option( 'date_format' ), strtotime( $end_date . $end_time ) ), date_i18n( get_option( 'time_format' ), strtotime( $end_date . $end_time ) ) );
			}
		}

		echo ".";
		?>
		</p>
	</div>

	<div style="overflow-x:auto;">
		<?php $style = $debug_mode == 'yes' ? ' style="margin-top: 14em !important;"' : ''; ?>
		<table id="print-bookings-table"<?php echo $style; ?>>
			<tbody>
				<tr valign="top">
					<?php
					$table_columns = apply_filters( 'woocommerce_print_bookings_column', array(
						'booking_date'   => __( 'Booking Date and Time', 'woocommerce-print-bookings' ),
						'customers_name' => __( 'Customers Name', 'woocommerce-print-bookings' ),
						'order'          => __( 'Order', 'woocommerce-print-bookings' ),
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

					// Skips any bookings with the status "was-in-cart or in-cart".
					if ( $booking->has_status( array( 'was-in-cart', 'in-cart' ) ) ) {
						continue;
					}

					// Order assigned to the booking.
					$order = $booking->get_order();

					// Customers details on the Booking.
					$customer = $booking->get_customer();

					$display_customer = '';

					if ( ! empty( $customer->email ) && ! empty( $customer->name ) ) {
						$display_customer = '<a href="mailto:' . esc_attr( $customer->email ) . '">' . esc_html( $customer->name ) . '</a>';
					} else if ( ! empty( $customer->name ) ) {
						$display_customer = esc_html( $customer->name );
					}

					if ( ! empty( $order ) && isset( $show_order_notes ) && $show_order_notes ) {
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
									echo __( 'Start Date', 'woocommerce-print-bookings' ) . ': <strong>' . date_i18n( "F j, Y g:i a", strtotime( $booking->get_start_date( '', 'F j, Y g:i a' ) ) ) . '</strong>';

									if ( ! empty( $end_date ) ) {
										echo '<br>' . __( 'End Date', 'woocommerce-print-bookings' ) . ': <strong>' . date_i18n( "F j, Y g:i a", strtotime( $booking->get_end_date( '', 'F j, Y g:i a' ) ) ) . '</strong>';
									}
								}

								echo "<br>" . __( 'Booking Status', 'woocommerce-print-bookings' ) . ": <strong>" . esc_html( wc_bookings_get_status_label( $booking->get_status() ) ) . "</strong>";
								if ( $debug_mode == 'yes' ) echo "<br>" . __( 'Booking ID', 'woocommerce-print-bookings' ) . ": <strong>#" . esc_html( $booking->get_id() ) . "</strong>";
							break;

							case 'customers_name':
								echo '<td class="' . $key . '">';

									if ( ! empty( $display_customer ) ) echo $display_customer;

							break;

							case 'order':
								echo '<td class="' . $key . '">';

								if ( ! empty( $order ) ) {
									$order_date = $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i:s' ) : '';

									echo '<a href="' . admin_url( 'post.php?post=' . absint( ( is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id ) ) . '&action=edit' ) . '">#' . esc_html( $order->get_order_number() ) . '</a> - ' . esc_html( wc_get_order_status_name( $order->get_status() ) ) .
									'<br>' . __( 'Order Date', 'woocommerce-print-bookings' ) . ': <strong>' . sprintf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $order_date ) ), date_i18n( wc_date_format(), strtotime( $order_date ) ) ) . '</strong>';
								}
								else {
									echo __( 'No order assigned to booking!', 'woocommerce-print-bookings' );
								}

							break;

							case 'order_items':
								echo '<td class="' . $key . '">';

								if ( ! empty( $order ) ) {

									$items = $order->get_items();

									if ( sizeof( $items ) > 0 ) {
										foreach ( $items as $item ) {
											echo wc_display_item_meta( $item );
										}
									}

								}

							break;

							case 'order_notes':
								echo '<td class="' . $key . '">';

								if ( ! empty( $order ) && isset( $show_order_notes ) && $show_order_notes ) {
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
