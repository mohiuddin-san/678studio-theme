jQuery(document).ready(function ($) {
	console.log('SIAES form handler JS loaded');

	if (typeof siaes_ajax === 'undefined') {
		console.error(
			'siaes_ajax is undefined. Ensure wp_localize_script is called.'
		);
		return;
	}

	console.log('AJAX URL:', siaes_ajax.ajax_url);
	console.log('Page ID:', siaes_ajax.page_id);
	console.log('Nonce:', siaes_ajax.nonce);
	console.log('API URL:', siaes_ajax.api_url);

	// Ensure shop-id field exists
	if ($('#shop-id').length === 0) {
		$('#inquiry-form').append(
			'<input type="hidden" id="shop-id" name="shop-id">'
		);
		console.log('Added hidden shop-id field');
	}

	// Handle shop dropdown change
	$('#store-select').on('change', function () {
		var shopId = $(this).val();
		$('#shop-id').val(shopId);
		console.log('Selected shop ID:', shopId);
	});

	// Initialize shop-id
	var initialShopId = $('#store-select').val() || '';
	$('#shop-id').val(initialShopId);
	console.log('Initialized shop-id with:', initialShopId);

	// Handle form submission
	$('#inquiry-form').on('submit', function (e) {
		e.preventDefault();
		console.log('Form submission triggered');

		// Validate required fields
		var isValid = true;
		$('#inquiry-form [required]').each(function () {
			if (!$(this).val()) {
				isValid = false;
				$(this).addClass('error');
			} else {
				$(this).removeClass('error');
			}
		});

		if (!isValid) {
			alert('Please fill out all required fields.');
			return;
		}

		var formData = $(this).serializeArray();
		formData.push({ name: 'action', value: 'siaes_submit_form' });
		formData.push({ name: 'page_id', value: siaes_ajax.page_id });
		formData.push({ name: 'nonce', value: siaes_ajax.nonce });

		console.log('Form data:', formData);

		$.ajax({
			url: siaes_ajax.ajax_url,
			type: 'POST',
			data: formData,
			success: function (response) {
				console.log('AJAX response:', response);
				if (response.success) {
					alert('Thank you! Your inquiry has been submitted.');
					$('#inquiry-form')[0].reset();
					$('#store-select').val(''); // Reset dropdown
					$('#shop-id').val(''); // Reset hidden field
				} else {
					console.error('Submission failed:', response.data);
					alert('Error: ' + (response.data || 'Unknown error'));
				}
			},
			error: function (xhr, status, error) {
				console.error('AJAX error:', status, error, xhr.responseText);
				alert('Error submitting inquiry. Please try again.');
			},
		});
	});
});
