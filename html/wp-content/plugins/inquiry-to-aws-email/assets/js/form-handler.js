jQuery(document).ready(function($) {
    console.log('SIAES form handler JS loaded, AJAX URL: ' + siaes_ajax.ajax_url);
    
    $('#inquiry-form').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        console.log('Serialized form data before submission: ' + formData);
        formData += '&action=siaes_submit_form';
        formData += '&page_id=' + siaes_ajax.page_id;
        formData += '&nonce=' + siaes_ajax.nonce;

        $.ajax({
            url: siaes_ajax.ajax_url,
            type: 'POST',
            data: formData,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', siaes_ajax.nonce);
                console.log('Sending request with nonce: ' + siaes_ajax.nonce);
            },
            success: function(response) {
                console.log('AJAX success response: ', response);
                if (response.success) {
                    alert('Thank you! Your inquiry has been submitted.');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error: ' + error + ', Status: ' + status + ', Response: ' + xhr.responseText);
                alert('Error submitting inquiry. Please try again.');
            }
        });
    });
});