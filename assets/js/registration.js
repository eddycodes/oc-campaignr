/**
 * Event Registration JavaScript
 */

(function($) {
    'use strict';

    // Initialize registration functionality
    $(document).ready(function() {

        // Handle registration modal
        $(document).on('click', '.btn-register-event', function(e) {
            e.preventDefault();

            var eventId = $(this).data('event-id');
            var eventName = $(this).data('event-name');
            var eventDate = $(this).data('event-date') || '';

            // Set event ID in form
            $('#modal-event-id').val(eventId);

            // Update modal with event info
            if (eventName) {
                $('#modal-event-name').text(eventName);
                $('#registrationModalLabel').text('Register for ' + eventName);
            }

            if (eventDate) {
                $('#modal-event-date').html('<i class="fa fa-calendar"></i> ' + eventDate);
                $('#modal-event-info').show();
            }

            // Clear form fields
            $('#registrationModal form')[0].reset();
            $('#registrationModal .text-danger').text('');
            $('#registrationModal .form-control').removeClass('is-invalid is-valid');

            // Re-set the event ID after reset
            $('#modal-event-id').val(eventId);

            // Open registration modal (Bootstrap 5)
            var modal = new bootstrap.Modal(document.getElementById('registrationModal'));
            modal.show();
        });

        // Form validation feedback
        $(document).on('ajaxValidation', '.registration-form', function(event, fieldName, fieldMessages, isValid) {
            var $field = $(this).find('[name="' + fieldName + '"]');
            var $feedback = $field.siblings('[data-validate-for="' + fieldName + '"]');

            if (!isValid) {
                $field.addClass('is-invalid');
                $feedback.text(fieldMessages.join(', '));
            } else {
                $field.removeClass('is-invalid').addClass('is-valid');
                $feedback.text('');
            }
        });

        // Clear validation on input
        $(document).on('input', '.registration-form .form-control', function() {
            $(this).removeClass('is-invalid is-valid');
            $(this).siblings('.text-danger').text('');
        });

        // Handle successful registration
        $(document).on('ajaxSuccess', '.registration-form', function() {
            // Scroll to top of form to show success message
            if (!$(this).closest('.modal').length) {
                $('html, body').animate({
                    scrollTop: $('#registration-form').offset().top - 100
                }, 500);
            }
        });

        // Close modal on successful registration (with delay for user to see message)
        $(document).on('ajaxSuccess', '.registration-modal .registration-form', function() {
            setTimeout(function() {
                $('.registration-modal').modal('hide');
            }, 1500);
        });
    });

})(jQuery);
