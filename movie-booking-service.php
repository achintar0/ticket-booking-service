<?php
/*
Plugin Name: Movie Ticket Booking
Description: Adds date and ticket booking for movie products plus an order tracking panel.
Version: 1.13
Author: Achilles Daralas
*/

if (!defined('ABSPATH')) exit;

// Enqueue scripts and styles for admin
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'post-new.php' || $hook === 'post.php') {
        wp_enqueue_script('daterangepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', ['jquery', 'moment'], null, true);
        wp_enqueue_style('daterangepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css');
    }
});

// Add custom fields to admin
add_action('woocommerce_product_options_general_product_data', function () {
    global $post;

    echo '<div class="options_group">';

    // Date Range Picker
    woocommerce_wp_text_input([
        'id' => '_movie_date_range',
        'label' => 'Ημερομηνίες Προβολής',
        'desc_tip' => true,
        'description' => 'Επιλέξτε τις ημερομηνίες προβολής της ταινίας (αρχή - τέλος).',
        'class' => 'short',
    ]);

    // Ticket Types
    woocommerce_wp_text_input([
        'id' => "_ticket_type_1_price",
        'label' => "Γενική Είσοδος Πέμπτης (€)",
        'type' => 'number',
        'custom_attributes' => ['step' => '0.01', 'min' => '0'],
    ]);

    woocommerce_wp_text_input([
        'id' => "_ticket_type_2_price",
        'label' => "Γενική Είσοδος (€)",
        'type' => 'number',
        'custom_attributes' => ['step' => '0.01', 'min' => '0'],
    ]);

    woocommerce_wp_text_input([
        'id' => "_ticket_type_3_price",
        'label' => "Παιδικό ΕΩΣ 12 ΕΤΩΝ (€)",
        'type' => 'number',
        'custom_attributes' => ['step' => '0.01', 'min' => '0'],
    ]);

    woocommerce_wp_text_input([
        'id' => "_ticket_type_4_price",
        'label' => "Άνεργοι (€)",
        'type' => 'number',
        'custom_attributes' => ['step' => '0.01', 'min' => '0'],
    ]);

    woocommerce_wp_text_input([
        'id' => "_ticket_type_5_price",
        'label' => "Φοιτητές (€)",
        'type' => 'number',
        'custom_attributes' => ['step' => '0.01', 'min' => '0'],
    ]);

    woocommerce_wp_text_input([
        'id' => "_ticket_type_6_price",
        'label' => "Πολύτεκνοι - Τρίτεκνοι (€)",
        'type' => 'number',
        'custom_attributes' => ['step' => '0.01', 'min' => '0'],
    ]);

    woocommerce_wp_text_input([
        'id' => "_ticket_type_7_price",
        'label' => "Α.Μ.Ε.Α. (€)",
        'type' => 'number',
        'custom_attributes' => ['step' => '0.01', 'min' => '0'],
    ]);

    echo '</div>';
});

// Save custom fields
add_action('woocommerce_process_product_meta', function ($post_id) {
    if (isset($_POST['_movie_date_range'])) {
        update_post_meta($post_id, '_movie_date_range', sanitize_text_field($_POST['_movie_date_range']));
    }

    for ($i = 1; $i <= 7; $i++) {
        if (isset($_POST["_ticket_type_{$i}_price"])) {
            update_post_meta($post_id, "_ticket_type_{$i}_price", floatval($_POST["_ticket_type_{$i}_price"]));
        }
    }
});

// Load DateRangePicker JS in admin
add_action('admin_footer', function () {
    global $post;
    if ($post && get_post_type($post) === 'product') {
        ?>
        <script>
        jQuery(function ($) {
            let $dateInput = jQuery('#_movie_date_range');
            let savedValue = $dateInput.val();
            let start = moment();
            let end = moment();

            if (savedValue.includes(' - ')) {
                let parts = savedValue.split(' - ');
                start = moment(parts[0], 'DD-MM-YYYY');
                end = moment(parts[1], 'DD-MM-YYYY');
            }

            $dateInput.daterangepicker({
                autoUpdateInput: true,
                startDate: start,
                endDate: end,
                locale: {
                    format: 'DD-MM-YYYY',
                    cancelLabel: 'Καθαρισμός',
                    applyLabel: 'Εφαρμογή',
                    fromLabel: "Από",
                    toLabel: "Μέχρι",
                    showDropdowns: "true",
                    daysOfWeek: [
                        "Κυ",
                        "Δε",
                        "Τρ",
                        "Τε",
                        "Πέ",
                        "Πα",
                        "Σά"
                    ],
                    monthNames: [
                        "Ιανουάριος",
                        "Φεβρουάριος",
                        "Μάρτιος",
                        "Απρίλιος",
                        "Μάιος",
                        "Ιούνιος",
                        "Ιούλιος",
                        "Αύγουστος",
                        "Σεπτέμβριος",
                        "Οκτώβριος",
                        "Νοέμβριος",
                        "Δεκέμβριος"
                    ],
                }

                
            });

            $dateInput.on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });


            $('#_movie_date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
            });

            $('#_movie_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });
        </script>
        <?php
    }
});

// Remove main price from frontend
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);

// Enqueue frontend script
add_action('wp_enqueue_scripts', function () {
    if (is_product()) {
        wp_enqueue_script('ticket-booking-js', plugin_dir_url(__FILE__) . 'ticket-booking.js', ['jquery'], null, true);
        wp_enqueue_style('daterangepicker-css-front', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css');
        wp_enqueue_script('daterangepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', ['jquery', 'moment'], null, true);
    }
});

// Display fields on product page
add_action('woocommerce_before_add_to_cart_button', function () {
    global $post;
    $date_range = get_post_meta($post->ID, '_movie_date_range', true);

    $date_parts = explode(' - ', $date_range);
    $start_date = $date_parts[0] ?? '';
    $end_date = $date_parts[1] ?? '';


    echo "<script>
        var movieDateStart = '{$start_date}';
        var movieDateEnd = '{$end_date}';
    </script>";

    echo '<div id="booking-date-container" style="margin-bottom: 1em;"><label><strong>Επιλέξτε ημερομηνία:</strong></label></div>';

    echo '<div class="ticket-types">';
    $ticket_prices = array();
    for ($i = 1; $i <= 7; $i++) {
        $price = get_post_meta($post->ID, "_ticket_type_{$i}_price", true);
        if ($price !== '') {
            array_push($ticket_prices, $price);
            
        }
    }

    echo "<div>
                <label>Γενική Είσοδος Πέμπτης (€{$ticket_prices[0]})</label>
                <input type='number' min='0' step='1' name='ticket_type_1' class='ticket-qty' data-price='{$ticket_prices[0]}' value='0' />
                </div>";

    echo "<div>
                <label>Γενική Είσοδος (€{$ticket_prices[1]})</label>
                <input type='number' min='0' step='1' name='ticket_type_2' class='ticket-qty' data-price='{$ticket_prices[1]}' value='0' />
                </div>";
    
    echo "<div>
                <label>Παιδικό ΕΩΣ 12 ΕΤΩΝ (€{$ticket_prices[2]})</label>
                <input type='number' min='0' step='1' name='ticket_type_3' class='ticket-qty' data-price='{$ticket_prices[2]}' value='0' />
                </div>";

    echo "<div>
                <label>Άνεργοι (€{$ticket_prices[3]})</label>
                <input type='number' min='0' step='1' name='ticket_type_4' class='ticket-qty' data-price='{$ticket_prices[3]}' value='0' />
                </div>";


    echo "<div>
                <label>Φοιτητές (€{$ticket_prices[4]})</label>
                <input type='number' min='0' step='1' name='ticket_type_5' class='ticket-qty' data-price='{$ticket_prices[4]}' value='0' />
                </div>";

    echo "<div>
                <label>Πολύτεκνοι - Τρίτεκνοι (€{$ticket_prices[5]})</label>
                <input type='number' min='0' step='1' name='ticket_type_6' class='ticket-qty' data-price='{$ticket_prices[5]}' value='0' />
                </div>";

    echo "<div>
                <label>Α.Μ.Ε.Α. (€{$ticket_prices[6]})</label>
                <input type='number' min='0' step='1' name='ticket_type_7' class='ticket-qty' data-price='{$ticket_prices[6]}' value='0' />
                </div>";

    echo '</div>';

    echo '<div><strong>Σύνολο: €<span id="total_price">0.00</span></strong></div>';

    // Remove default quantity input
    add_filter('woocommerce_is_sold_individually', 'disable_quantity_input_for_tickets', 10, 2);
    function disable_quantity_input_for_tickets($return, $product) {
        if (is_product()) {
            return true;
        }
    return $return;
}


});


add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id, $variation_id) {
    $total_price = 0;
    $ticket_data = [];

    for ($i = 1; $i <= 7; $i++) {
        $qty = isset($_POST["ticket_type_{$i}"]) ? intval($_POST["ticket_type_{$i}"]) : 0;
        $price = get_post_meta($product_id, "_ticket_type_{$i}_price", true);
        if ($qty > 0 && $price !== '') {
            $ticket_data["ticket_type_{$i}"] = $qty;
            $total_price += $qty * floatval($price);
        }
    }

    if ($total_price > 0) {
        $cart_item_data['custom_ticket_data'] = $ticket_data;
        $cart_item_data['custom_price'] = $total_price;
    }

    // Add booking date if present
    if (!empty($_POST['movie_booking_date'])) {
        $cart_item_data['movie_booking_date'] = sanitize_text_field($_POST['movie_booking_date']);
    }

    return $cart_item_data;
}, 10, 3);



add_action('woocommerce_before_calculate_totals', function ($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['custom_price'])) {
            $cart_item['data']->set_price($cart_item['custom_price']);
        }
    }
});



add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    $ticket_labels = [
        1 => "Γενική Είσοδος Πέμπτης",
        2 => "Γενική Είσοδος",
        3 => "Παιδικό ΕΩΣ 12 ΕΤΩΝ",
        4 => "Άνεργοι",
        5 => "Φοιτητές",
        6 => "Πολύτεκνοι - Τρίτεκνοι",
        7 => "Α.Μ.Ε.Α.",
    ];

    if (isset($cart_item['custom_ticket_data'])) {
        foreach ($cart_item['custom_ticket_data'] as $type => $qty) {
            $type_index = intval(str_replace('ticket_type_', '', $type));
            $label = $ticket_labels[$type_index] ?? ucfirst(str_replace('_', ' ', $type));
            $item_data[] = [
                'key' => $label,
                'value' => $qty,
            ];
        }
    }

    if (!empty($cart_item['movie_booking_date'])) {
        $item_data[] = [
            'key' => 'Ημερομηνία Προβολής',
            'value' => $cart_item['movie_booking_date'],
        ];
    }

    return $item_data;
}, 10, 2);

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);


// Add admin menu for bookings
add_action('admin_menu', function() {
    add_menu_page(
        'Κρατήσεις Ταινιών', // Page title
        'Κρατήσεις Ταινιών', // Menu title
        'edit_posts', // Capability
        'movie-ticket-bookings', // Menu slug
        'display_movie_bookings_page', // Function
        'dashicons-tickets-alt', // Icon
        35 // Position
    );
});

// Display bookings page
function display_movie_bookings_page() {
    if (!current_user_can('edit_posts')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Handle single order view if ID is set
    if (isset($_GET['order_id'])) {
        display_single_order_details(intval($_GET['order_id']));
        return;
    }

    // Display all orders
    echo '<div class="wrap">';
    echo '<h1>Κρατήσεις Ταινιών</h1>';
    
    // Search form
    echo '<form method="get" action="" style="margin-bottom: 20px;">
            <input type="hidden" name="page" value="movie-ticket-bookings" />
            <input type="text" name="search" id="movie-bookings-search" 
                   placeholder="Αναζήτηση με Αριθμό Παραγγελίας, Όνομα, Ταινία ή Ημερομηνία..." 
                   value="' . (isset($_GET['search']) ? esc_attr($_GET['search']) : '') . '" 
                   style="width: 50%; padding: 8px;" autocomplete="off"/>
            <input type="submit" class="button" value="Αναζήτηση" />
            <a href="?page=movie-ticket-bookings" class="button" style="margin-left: 5px;">Καθαρισμός</a>
          </form>';

    // Get search term if exists
    $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    // Get all orders with movie bookings
    $args = [
        'limit' => -1,
        'status' => array_keys(wc_get_order_statuses()),
        'meta_key' => '_movie_booking_order',
        'meta_value' => 'yes',
    ];

    // If searching, modify the query
    if (!empty($search_term)) {
        $args['meta_query'] = [
            'relation' => 'OR',
            [
                'key' => '_billing_first_name',
                'value' => $search_term,
                'compare' => 'LIKE'
            ],
            [
                'key' => '_billing_last_name',
                'value' => $search_term,
                'compare' => 'LIKE'
            ],
        ];
    }

    $orders = wc_get_orders($args);

    // If empty, try a broader query
    if (empty($orders)) {
        $orders = wc_get_orders([
            'limit' => -1,
            'status' => array_keys(wc_get_order_statuses()),
        ]);
        
        // Filter to only orders with ticket data
        $orders = array_filter($orders, function($order) use ($search_term) {
            $match_found = false;
            
            // Check if order matches search term
            if (!empty($search_term)) {
                $order_id_match = (strpos($order->get_id(), $search_term) !== false);
                $customer_match = (stripos($order->get_formatted_billing_full_name(), $search_term) !== false);
                $movie_match = false;
                
                foreach ($order->get_items() as $item) {
                    if (stripos($item->get_name(), $search_term) !== false) {
                        $movie_match = true;
                        break;
                    }
                }
                
                if (!$order_id_match && !$customer_match && !$movie_match) {
                    return false;
                }
            }
            
            // Check for ticket data
            foreach ($order->get_items() as $item) {
                if ($item->get_meta('Ημερομηνία Προβολής') || 
                    strpos($item->get_name(), 'εισιτήριο') !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    $ticket_labels = [
        1 => "Γενική Είσοδος Πέμπτης",
        2 => "Γενική Είσοδος",
        3 => "Παιδικό ΕΩΣ 12 ΕΤΩΝ",
        4 => "Άνεργοι",
        5 => "Φοιτητές",
        6 => "Πολύτεκνοι - Τρίτεκνοι",
        7 => "Α.Μ.Ε.Α.",
    ];

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr>
            <th>Αριθμός Παραγγελίας</th>
            <th>Πελάτης</th>
            <th>Ημερομηνία Παραγγελίας</th>
            <th>Τίτλος Ταινίας</th>
            <th>Ημερομηνία Προβολής</th>
            <th>Εισιτήρια</th>
            <th>Σύνολο</th>
            <th>Κατάσταση</th>
            <th>Ενέργειες</th>
          </tr></thead>';
    echo '<tbody>';

    foreach ($orders as $order) {
        $order_id = $order->get_id();
        $customer_name = $order->get_formatted_billing_full_name();
        $order_date = $order->get_date_created()->format('d/m/Y H:i');
        $status = wc_get_order_status_name($order->get_status());
    
        // Get booking details
        $booking_date = '';
        $tickets_info = [];
        $movie_title = '';
        
        foreach ($order->get_items() as $item) {
            // Get booking date
            $total = $item->get_total();
            $order_name = $item->get_name();
            $item_booking_date = $item->get_meta('Ημερομηνία Προβολής');
            if ($item_booking_date && empty($booking_date)) {
                $booking_date = $item_booking_date;
            }
            
            // Get ticket quantities
            foreach ($ticket_labels as $id => $label) {
                $qty = $item->get_meta("ticket_type_{$id}") ?: $item->get_meta($label);
                if ($qty) {
                    $tickets_info[] = $label . ': ' . $qty;
                }
            }

            $tickets_display = !empty($tickets_info) ? implode('<br>', $tickets_info) : 'Δεν βρέθηκαν εισιτήρια';
            $movie_title = $order_name;

            if($status == 'Σε επεξεργασία' || $status == 'Processing'){
                $text_color = 'green';
            }
            else{
                $text_color = 'red';
            }

            // Highlight search term in results
            if (!empty($search_term)) {
                $order_id = preg_replace("/($search_term)/i", '<span style="background-color: yellow;">$1</span>', $order_id);
                $customer_name = preg_replace("/($search_term)/i", '<span style="background-color: yellow;">$1</span>', $customer_name);
                $movie_title = preg_replace("/($search_term)/i", '<span style="background-color: yellow;">$1</span>', $movie_title);
                $booking_date = preg_replace("/($search_term)/i", '<span style="background-color: yellow;">$1</span>', $booking_date);
            }

            echo "<tr>
                <td>#$order_id</td>
                <td>$customer_name</td>
                <td>$order_date</td>
                <td>$movie_title</td>
                <td>$booking_date</td>
                <td>$tickets_display</td>
                <td>{$total} €</td>
                <td style='font-weight: 700; color: {$text_color};'>$status</td>
                <td><a href='?page=movie-ticket-bookings&order_id=$order_id' class='button'>Προβολή Λεπτομερειών</a></td>
              </tr>";
        }
    }

    echo '</tbody></table>';
    
    // Add JavaScript to focus the search field
    echo '<script>
    jQuery(document).ready(function($) {
        $("#movie-bookings-search").focus();
    });
    </script>';
    
    echo '</div>';
}

function display_single_order_details($order_id) {
    $order = wc_get_order($order_id);
    
    if (!$order) {
        echo '<div class="error"><p>Η παραγγελία δεν βρέθηκε.</p></div>';
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>Λεπτομέρειες Παραγγελίας #' . $order_id . '</h1>';
    echo '<p><a href="' . admin_url('admin.php?page=movie-ticket-bookings') . '">&larr; Επιστροφή στις κρατήσεις</a></p>';

    // Στοιχεία παραγγελίας
    echo '<div class="postbox" style="margin-bottom: 20px; padding: 20px;">';
    echo '<h2>Στοιχεία Παραγγελίας</h2>';
    echo '<p><strong>Πελάτης:</strong> ' . $order->get_formatted_billing_full_name() . '</p>';
    echo '<p><strong>Email:</strong> ' . $order->get_billing_email() . '</p>';
    echo '<p><strong>Τηλέφωνο:</strong> ' . $order->get_billing_phone() . '</p>';
    echo '<p><strong>Ημερομηνία:</strong> ' . $order->get_date_created()->format('d/m/Y H:i') . '</p>';
    echo '<p><strong>Κατάσταση:</strong> ' . wc_get_order_status_name($order->get_status()) . '</p>';
    echo '<p><strong>Σύνολο:</strong> ' . $order->get_formatted_order_total() . '</p>';
    echo '</div>';

    // Στοιχεία κράτησης
    echo '<div class="postbox" style="padding: 20px;">';
    echo '<h2>Στοιχεία Κράτησης</h2>';

    $ticket_labels = [
        1 => "Γενική Είσοδος Πέμπτης",
        2 => "Γενική Είσοδος",
        3 => "Παιδικό ΕΩΣ 12 ΕΤΩΝ",
        4 => "Άνεργοι",
        5 => "Φοιτητές",
        6 => "Πολύτεκνοι - Τρίτεκνοι",
        7 => "Α.Μ.Ε.Α.",
    ];

    $has_tickets = false;
    
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $product_name = $product ? $product->get_name() : $item->get_name();
        
        echo '<h3>' . esc_html($product_name) . '</h3>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Τύπος Εισιτηρίου</th><th>Ποσότητα</th><th>Τιμή</th><th>Σύνολο</th></tr></thead>';
        echo '<tbody>';

        // Display booking date once
        $booking_date = $item->get_meta('Ημερομηνία Προβολής');
        if ($booking_date) {
            echo '<tr><td colspan="4"><strong>Ημερομηνία Προβολής:</strong> ' . esc_html($booking_date) . '</td></tr>';
        }

        // Check both numeric keys and labels for tickets
        foreach ($ticket_labels as $id => $label) {
            $qty = $item->get_meta("ticket_type_{$id}") ?: $item->get_meta($label);
            
            if ($qty) {
                $price = $item->get_meta("_ticket_type_{$id}_price");
                $total = $qty * $price;
                
                echo '<tr>
                        <td>' . esc_html($label) . '</td>
                        <td>' . esc_html($qty) . '</td>
                        <td>' . wc_price($price) . '</td>
                        <td>' . wc_price($total) . '</td>
                    </tr>';
                
                $has_tickets = true;
            }
        }

        echo '</tbody></table>';
    }

    if (!$has_tickets) {
        echo '<p>Δεν βρέθηκαν εισιτήρια σε αυτήν την παραγγελία.</p>';
    }

    echo '</div>';
    echo '</div>';
}

// Mark orders with movie tickets
add_action('woocommerce_checkout_create_order', function($order) {
    foreach ($order->get_items() as $item) {
        if (isset($item['custom_ticket_data'])) {
            $order->update_meta_data('_movie_booking_order', 'yes');
            break;
        }
    }
});

// Handle ticket data storage - SINGLE SOURCE OF TRUTH
add_action('woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values, $order) {
    $ticket_labels = [
        1 => "Γενική Είσοδος Πέμπτης",
        2 => "Γενική Είσοδος",
        3 => "Παιδικό ΕΩΣ 12 ΕΤΩΝ",
        4 => "Άνεργοι",
        5 => "Φοιτητές",
        6 => "Πολύτεκνοι - Τρίτεκνοι",
        7 => "Α.Μ.Ε.Α.",
    ];
    
    // Clean up any existing meta first
    foreach ($ticket_labels as $id => $label) {
        $item->delete_meta_data("ticket_type_{$id}");
        $item->delete_meta_data($label);
    }
    $item->delete_meta_data('Ημερομηνία Προβολής');
    
    // Store ticket data PROPERLY
    if (isset($values['custom_ticket_data'])) {
        foreach ($values['custom_ticket_data'] as $type => $qty) {
            $type_id = str_replace('ticket_type_', '', $type);
            
            // Store with NUMERIC KEY (primary storage)
            $item->update_meta_data("ticket_type_{$type_id}", $qty);
            
            // Store PRICE reference
            $price = get_post_meta($values['product_id'], "_ticket_type_{$type_id}_price", true);
            if ($price) {
                $item->update_meta_data("_ticket_type_{$type_id}_price", $price);
            }
        }
        
        // Mark order as having tickets
        $order->update_meta_data('_movie_booking_order', 'yes');
    }
    
    // Store booking date ONCE
    if (!empty($values['movie_booking_date'])) {
        $item->update_meta_data('Ημερομηνία Προβολής', $values['movie_booking_date']);
    }
}, 10, 4);

// Display tickets CORRECTLY in admin and emails
add_filter('woocommerce_order_item_get_formatted_meta_data', function($formatted_meta, $item) {
    $ticket_labels = [
        1 => "Γενική Είσοδος Πέμπτης",
        2 => "Γενική Είσοδος",
        3 => "Παιδικό ΕΩΣ 12 ΕΤΩΝ",
        4 => "Άνεργοι",
        5 => "Φοιτητές",
        6 => "Πολύτεκνοι - Τρίτεκνοι",
        7 => "Α.Μ.Ε.Α.",
    ];
    
    $clean_meta = [];
    
    // First handle booking date
    if ($date = $item->get_meta('Ημερομηνία Προβολής')) {
        $clean_meta['booking_date'] = (object)[
            'key' => 'Ημερομηνία Προβολής',
            'value' => $date,
            'display_key' => 'Ημερομηνία Προβολής',
            'display_value' => esc_html($date)
        ];
    }
    
    // Then handle tickets
    foreach ($ticket_labels as $id => $label) {
        if ($qty = $item->get_meta("ticket_type_{$id}")) {
            $price = $item->get_meta("_ticket_type_{$id}_price");
            $clean_meta["ticket_{$id}"] = (object)[
                'key' => "ticket_type_{$id}",
                'value' => $qty,
                'display_key' => $label,
                'display_value' => $qty . ' × ' . wc_price($price) . ' = ' . wc_price($qty * $price)
            ];
        }
    }
    
    return $clean_meta;
}, 10, 2);


function my_custom_styles() {
    wp_enqueue_style('my-custom-style', plugin_dir_url(__FILE__) . 'style.css');
}
add_action('wp_enqueue_scripts', 'my_custom_styles');
