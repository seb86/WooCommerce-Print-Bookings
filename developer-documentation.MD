# Developer Documentation

If you want to add and return more booking data, the following action hooks and filters will provide you what you need.

### Add before get bookings button.
Add extra fields before the get bookings button.

** Action Hook:** `do_action( 'woocommerce_print_bookings_before_get_bookings_button' );`

### Form Submission
Use this action to post other field data. Can use any of the fields already set to add conditions to your own.

** Action Hook:** `do_action( 'woocommerce_print_bookings_page_form_submitted', $product_id, $post_status, $category, $start_date, $end_date, $booking_all_day, $start_time, $end_time, $show_order_notes );`

### Print Table Column
Use this action hook to display the column data for the key

** Action Hook:** `do_action( 'woocommerce_print_bookings_column_' . $key, $booking, $order );`

### Filter the query arguments
If you need to filter the query argument for get_posts() this filter will allow you to do that.
> See [get_posts()](https://codex.wordpress.org/Template_Tags/get_posts) in the WordPress codex for more info.

** Filter:** `woocommerce_print_bookings_query`

### Print Table Column
Can be used to add more columns to the table results.

**Default Headers:**
```php
array(
	'booking_date'   => __( 'Booking Date and Time', 'woocommerce-print-bookings' ),
	'customers_name' => __( 'Customers Name', 'woocommerce-print-bookings' ),
	'order'          => __( 'Order', 'woocommerce-print-bookings' ),
	'order_items'    => __( 'Order Items and Meta', 'woocommerce-print-bookings' ),
	'order_notes'    => __( 'Order Notes', 'woocommerce-print-bookings' )
)`

** Filter:** `woocommerce_print_bookings_column`
