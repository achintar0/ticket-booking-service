jQuery(function ($) {
    // Set up the date picker
    jQuery(function($) {
    let $input = $('<input type="text" name="movie_booking_date" id="movie_booking_date" placeholder="Επιλέξτε Ημερομηνία Προβολής" required readonly/>');
    $('#booking-date-container').append($input);

    $input.daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        minDate: movieDateStart,
        maxDate: movieDateEnd,
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

    $input.on('apply.daterangepicker', function(ev, picker) {
        const selectedDate = picker.startDate;
        $(this).val(selectedDate.format('DD-MM-YYYY'));

        // Show only Ticket 1 on Thursday
        const day = selectedDate.day(); // 0 (Sunday) to 6 (Saturday)
        if (day === 4) { // Thursday
            $('[name^=ticket_type_]').closest('div').hide();
            $('[name=ticket_type_1]').closest('div').show();
        } else {
            $('[name^=ticket_type_]').closest('div').show();
            $('[name=ticket_type_1]').closest('div').hide();
        }

        // Trigger price update if needed
        $('.ticket-qty').val(0);
        $('#total_price').text('0.00');
    });
});

    // Ticket price calculator
    function updateTotal() {
        let total = 0;
        $('.ticket-qty').each(function () {
            const qty = parseInt($(this).val()) || 0;
            const price = parseFloat($(this).data('price')) || 0;
            total += qty * price;
        });
        $('#total_price').text(total.toFixed(2));
    }

    $('.ticket-qty').on('input', updateTotal);
});

jQuery(function($) {
    // On page load or ticket/date input change, enable/disable Add to Cart button
    function updateCartButtonState() {
        let totalTickets = 0;

        $('.ticket-qty:visible').each(function () {
            totalTickets += parseInt($(this).val()) || 0;
        });

        const selectedDate = $('#movie_booking_date').val(); // Adjust selector to match your datepicker input
        const $button = $('button.single_add_to_cart_button');

        // Disable button if no tickets OR no date selected
        if (totalTickets === 0 || !selectedDate) {
            $button.prop('disabled', true);
        } else {
            $button.prop('disabled', false);
        }
    }

    // Call this function on load and bind it to changes
    $(document).ready(function () {
        updateCartButtonState();

        // When ticket quantity changes
        $('.ticket-qty').on('input change', updateCartButtonState);

        // When date changes
        $('#movie_booking_date').on('change input', updateCartButtonState);
    });


    // Attach listeners to ticket fields
    $(document).on('input', '.ticket-qty', function () {
        updateCartButtonState();
    });

    // Reset logic when date is picked
    $('#movie_booking_date').on('apply.daterangepicker', function() {
        $('.ticket-qty').val(0);
        $('#total_price').text('0.00');
        updateCartButtonState();
    });

    // Disable button by default on load
    updateCartButtonState();
});

$(function() {
  $('#daterange').daterangepicker({
    opens: 'left',
    autoApply: true
  }, function(start, end, label) {
    console.log("Selected range: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
  });

  $('#daterange').click(); // Automatically trigger the picker on page load
});
