(function($) {
	'use strict';

	$(document).ready(function() {
		// Safety check - if we're on product list page, don't try to mount main card element
		var isProductList = $('.digidownloads-products-list').length > 0;
		var gateway = digidownloads.gateway || 'stripe';
		var stripeElements = {};
		
		// Debug: Check if digidownloads object exists
		if (typeof digidownloads === 'undefined') {
			console.error('DigiDownloads: Ajax variables not loaded');
			return;
		}
		
		console.log('DigiDownloads initialized:', {
			ajax_url: digidownloads.ajax_url,
			gateway: gateway,
			has_nonce: !!digidownloads.nonce
		});

	// Test AJAX connection
	$.ajax({
		url: digidownloads.ajax_url,
		type: 'POST',
		data: { action: 'digidownloads_test' },
		success: function(response) {
			console.log('AJAX Test Success:', response);
		},
		error: function(xhr) {
			console.error('AJAX Test Failed:', xhr.status, xhr.responseText);
		}
	});
		var stripe, elements, cardElement;
		if (gateway === 'stripe') {
			if (typeof digidownloadsStripeKey !== 'undefined' && digidownloadsStripeKey) {
				stripe = Stripe(digidownloadsStripeKey);
				
				var style = {
					base: {
						fontSize: '16px',
						color: '#32325d',
						fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
						'::placeholder': {
							color: '#aab7c4'
						}
					},
					invalid: {
						color: '#dc3232',
						iconColor: '#dc3232'
					}
				};

				// Mount card element only if main checkout exists (not product list)
				if (!isProductList && $('#card-element').length) {
					elements = stripe.elements();
					cardElement = elements.create('card', {
					style: style,
					hidePostalCode: true
					});
					cardElement.mount('#card-element');

					cardElement.on('change', function(event) {
						var displayError = $('#card-errors');
						if (event.error) {
							displayError.text(event.error.message);
						} else {
							displayError.text('');
						}
					});
				}
			}
		}

		// Handle Buy Now button clicks in product list
		$(document).on('click', '.digidownloads-buy-now', function() {
			var $button = $(this);
			var productId = $button.data('product-id');
			var $productItem = $button.closest('.digidownloads-product-item');
			var $checkoutContainer = $productItem.find('.product-checkout-container');
			
			// Toggle checkout form
			if ($checkoutContainer.is(':visible')) {
				$checkoutContainer.slideUp();
				$button.text($button.data('original-text') || 'Buy Now');
			} else {
				// Hide other open checkouts
				$('.product-checkout-container').slideUp();
				$('.digidownloads-buy-now').each(function() {
					$(this).text($(this).data('original-text') || 'Buy Now');
				});
				
				// Store original button text
				if (!$button.data('original-text')) {
					$button.data('original-text', $button.text());
				}
				
				$button.text('Cancel');
				$checkoutContainer.slideDown();
				
				// Initialize Stripe for this inline checkout if needed
				if (gateway === 'stripe' && stripe) {
					var cardElementId = 'card-element-' + productId;
					if ($('#' + cardElementId).length && !stripeElements[productId]) {
						// Create elements instance if not already created
						if (!elements) {
							elements = stripe.elements();
						}
						
						var inlineCardElement = elements.create('card', {
							style: {
								base: {
									fontSize: '16px',
									color: '#32325d',
									fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
									'::placeholder': {
										color: '#aab7c4'
									}
								},
								invalid: {
									color: '#dc3232',
									iconColor: '#dc3232'
								}
							},
							hidePostalCode: true
						});
						inlineCardElement.mount('#' + cardElementId);
						stripeElements[productId] = inlineCardElement;
						
						inlineCardElement.on('change', function(event) {
							var displayError = $productItem.find('.card-errors');
							if (event.error) {
								displayError.text(event.error.message);
							} else {
								displayError.text('');
							}
						});
					}
				}
			}
		});

		// Handle inline checkout form submissions
		$(document).on('submit', '.digidownloads-checkout-form', function(e) {
			e.preventDefault();

			var $form = $(this);
			var $productItem = $form.closest('.digidownloads-product-item');
			var email = $form.find('.buyer-email-input').val();
			var productId = $form.find('input[name="product_id"]').val();
			
			if (!email) {
				$form.find('.card-errors').text('Please enter your email address.');
				return;
			}

			var $submitButton = $form.find('.submit-payment');
			var $processingMsg = $form.find('.payment-processing');

			$submitButton.prop('disabled', true);
			$processingMsg.show();
			$form.find('.card-errors').text('');

			console.log('Submitting checkout:', {
				product_id: productId,
				email: email,
				ajax_url: digidownloads.ajax_url
			});

			// Create checkout session
			$.ajax({
				url: digidownloads.ajax_url,
				type: 'POST',
				data: {
					action: 'digidownloads_create_checkout',
					nonce: digidownloads.nonce,
					product_id: productId,
					email: email
				},
				success: function(response) {
					if (response.success) {
						// Handle based on gateway type
						if (gateway === 'stripe') {
							var cardEl = stripeElements[productId] || cardElement;
							if (!cardEl) {
								$form.find('.card-errors').text('Payment system not initialized.');
								$submitButton.prop('disabled', false);
								$processingMsg.hide();
								return;
							}
							
							// Confirm payment with Stripe
							stripe.confirmCardPayment(response.data.client_secret, {
								payment_method: {
									card: cardEl,
									billing_details: {
										email: email
									}
								}
							}).then(function(result) {
								if (result.error) {
									$form.find('.card-errors').text(result.error.message);
									$submitButton.prop('disabled', false);
									$processingMsg.hide();
								} else {
									if (result.paymentIntent.status === 'succeeded') {
										// Confirm payment on server and send email
										// Use the order_id from metadata, not the Stripe payment intent ID
										var orderId = result.paymentIntent.metadata.order_id;
										console.log('Payment succeeded. Order ID:', orderId, 'Payment Intent ID:', result.paymentIntent.id);
										
										$.ajax({
											url: digidownloads.ajax_url,
											type: 'POST',
											dataType: 'json',
											data: {
												action: 'digidownloads_confirm_stripe_payment',
												nonce: digidownloads.nonce,
												order_id: orderId,
												payment_intent_id: result.paymentIntent.id
											},
											success: function(confirm_response) {
											console.log('Confirmation response:', confirm_response);
											if (confirm_response.success) {
												$form.hide();
												$form.siblings('.payment-success').show();
											} else {
												console.error('Confirmation failed:', confirm_response.data);
												$form.find('.card-errors').text(confirm_response.data.message || 'Payment confirmed but server error occurred.');
												$form.hide();
												$form.siblings('.payment-success').show();
											}
										},
										error: function(xhr, status, error) {
											// Even if confirmation fails, payment succeeded
											console.error('Confirmation AJAX error:', status, error, xhr.responseText);
											console.log('Response status:', xhr.status);
												$form.hide();
												$form.siblings('.payment-success').show();
											},
											complete: function() {
												$submitButton.prop('disabled', false);
												$processingMsg.hide();
											}
										});
									}
								}
							});
						} else if (gateway === 'paypal') {
							// Redirect to PayPal
							if (response.data.approval_url) {
								window.location.href = response.data.approval_url;
							} else {
								$form.find('.card-errors').text('PayPal approval URL not received.');
								$submitButton.prop('disabled', false);
								$processingMsg.hide();
							}
						} else if (gateway === 'razorpay') {
							// Open Razorpay checkout
							if (typeof Razorpay === 'undefined') {
								$form.find('.card-errors').text('Razorpay library not loaded.');
								$submitButton.prop('disabled', false);
								$processingMsg.hide();
								return;
							}
							
							var options = {
								key: digidownloadsRazorpayKey,
								amount: response.data.amount,
								currency: response.data.currency,
								name: 'DigiDownloads',
								description: response.data.description,
								order_id: response.data.order_id,
								handler: function(razorpay_response) {
									// Verify payment on server
									$.ajax({
										url: digidownloads.ajax_url,
										type: 'POST',
										data: {
											action: 'digidownloads_verify_razorpay',
											nonce: digidownloads.nonce,
											razorpay_payment_id: razorpay_response.razorpay_payment_id,
											razorpay_order_id: razorpay_response.razorpay_order_id,
											razorpay_signature: razorpay_response.razorpay_signature,
										},
										success: function(verify_response) {
											if (verify_response.success) {
												$form.hide();
												$form.siblings('.payment-success').show();
											} else {
												$form.find('.card-errors').text(verify_response.data.message || 'Payment verification failed.');
												$submitButton.prop('disabled', false);
												$processingMsg.hide();
											}
										},
										error: function() {
											$form.find('.card-errors').text('Payment verification failed.');
											$submitButton.prop('disabled', false);
											$processingMsg.hide();
										}
									});
								},
								prefill: {
									email: email,
								},
								theme: {
									color: '#0073aa',
								},
							};
							
							var rzp = new Razorpay(options);
							rzp.on('payment.failed', function(razorpay_response) {
								$form.find('.card-errors').text(razorpay_response.error.description);
								$submitButton.prop('disabled', false);
								$processingMsg.hide();
							});
							rzp.open();
						}
					} else {
						$form.find('.card-errors').text(response.data.message);
						$submitButton.prop('disabled', false);
						$processingMsg.hide();
					}
				},
				error: function(xhr, status, error) {
					console.error('AJAX Error:', status, error);
					console.error('Response Text:', xhr.responseText);
					console.error('Status Code:', xhr.status);
					console.error('Full XHR Object:', xhr);
					
					var errorMsg = 'Error: ';
					if (xhr.status === 0) {
						errorMsg = 'Cannot connect to server. Check your internet connection.';
					} else if (xhr.status === 400) {
						errorMsg = 'Bad Request (400): ' + (xhr.responseText || 'Nonce verification failed or invalid request');
					} else if (xhr.status === 403) {
						errorMsg = 'Access Denied (403): Security check failed';
					} else if (xhr.status === 404) {
						errorMsg = 'Not Found (404): AJAX endpoint not found';
					} else if (xhr.status === 500) {
						errorMsg = 'Server Error (500): ' + xhr.responseText;
					} else {
						errorMsg = 'Error ' + xhr.status + ': ' + xhr.responseText;
					}
					
					$form.find('.card-errors').text(errorMsg);
					$submitButton.prop('disabled', false);
					$processingMsg.hide();
				}
			});
		});

		// Handle main checkout form submission (original)
		$('#digidownloads-checkout-form').on('submit', function(e) {
			e.preventDefault();

			var email = $('#buyer_email').val();
			if (!email) {
				$('#card-errors').text('Please enter your email address.');
				return;
			}

			var $submitButton = $('#submit-payment');
			var $processingMsg = $('#payment-processing');

			$submitButton.prop('disabled', true);
			$processingMsg.show();
			$('#card-errors').text('');

			// Create checkout session
			$.ajax({
				url: digidownloads.ajax_url,
				type: 'POST',
				data: {
					action: 'digidownloads_create_checkout',
					nonce: digidownloads.nonce,
					product_id: digidownloadsProductId,
					email: email
				},
				success: function(response) {
					if (response.success) {
						// Handle based on gateway type
						if (gateway === 'stripe') {
							// Confirm payment with Stripe
							stripe.confirmCardPayment(response.data.client_secret, {
								payment_method: {
									card: cardElement,
									billing_details: {
										email: email
									}
								}
							}).then(function(result) {
								if (result.error) {
									$('#card-errors').text(result.error.message);
									$submitButton.prop('disabled', false);
									$processingMsg.hide();
								} else {
									if (result.paymentIntent.status === 'succeeded') {
									// Confirm payment on server and send email
									// Use the order_id from metadata, not the Stripe payment intent ID
									var orderId = result.paymentIntent.metadata.order_id;
									console.log('Payment succeeded. Order ID:', orderId, 'Payment Intent ID:', result.paymentIntent.id);
									console.log('Full payment intent:', result.paymentIntent);
									
									$.ajax({
										url: digidownloads.ajax_url,
										type: 'POST',
										dataType: 'json',
										data: {
											action: 'digidownloads_confirm_stripe_payment',
											nonce: digidownloads.nonce,
											order_id: orderId,
											payment_intent_id: result.paymentIntent.id
										},
										success: function(confirm_response) {
											console.log('Confirmation response:', confirm_response);
											if (confirm_response.success) {
												$('#digidownloads-checkout-form').hide();
												$('#payment-success').show();
											} else {
												console.error('Confirmation failed:', confirm_response.data);
												$('#card-errors').text(confirm_response.data.message || 'Payment confirmed but server error occurred.');
												$('#digidownloads-checkout-form').hide();
												$('#payment-success').show();
											}
										},
										error: function(xhr, status, error) {
											// Even if confirmation fails, payment succeeded, so show success
											console.error('Confirmation AJAX error:', status, error, xhr.responseText);
											console.log('Response status:', xhr.status);
											$('#digidownloads-checkout-form').hide();
											$('#payment-success').show();
										},
										complete: function() {
											$submitButton.prop('disabled', false);
											$processingMsg.hide();
										}
									});
									}
								}
							});
						} else if (gateway === 'paypal') {
							// Redirect to PayPal
							if (response.data.approval_url) {
								window.location.href = response.data.approval_url;
							} else {
								$('#card-errors').text('PayPal approval URL not received.');
								$submitButton.prop('disabled', false);
								$processingMsg.hide();
							}
						} else if (gateway === 'razorpay') {
							// Open Razorpay checkout
							if (typeof Razorpay === 'undefined') {
								$('#card-errors').text('Razorpay library not loaded.');
								$submitButton.prop('disabled', false);
								$processingMsg.hide();
								return;
							}
							
							var options = {
								key: digidownloadsRazorpayKey,
								amount: response.data.amount,
								currency: response.data.currency,
								name: 'DigiDownloads',
								description: response.data.description,
								order_id: response.data.order_id,
								handler: function(razorpay_response) {
									// Verify payment on server
									$.ajax({
										url: digidownloads.ajax_url,
										type: 'POST',
										data: {
											action: 'digidownloads_verify_razorpay',
											nonce: digidownloads.nonce,
											razorpay_payment_id: razorpay_response.razorpay_payment_id,
											razorpay_order_id: razorpay_response.razorpay_order_id,
											razorpay_signature: razorpay_response.razorpay_signature,
										},
										success: function(verify_response) {
											if (verify_response.success) {
												$('#digidownloads-checkout-form').hide();
												$('#payment-success').show();
											} else {
												$('#card-errors').text(verify_response.data.message || 'Payment verification failed.');
												$submitButton.prop('disabled', false);
												$processingMsg.hide();
											}
										},
										error: function() {
											$('#card-errors').text('Payment verification failed.');
											$submitButton.prop('disabled', false);
											$processingMsg.hide();
										}
									});
								},
								prefill: {
									email: email,
								},
								theme: {
									color: '#0073aa',
								},
							};
							
							var rzp = new Razorpay(options);
							rzp.on('payment.failed', function(razorpay_response) {
								$('#card-errors').text(razorpay_response.error.description);
								$submitButton.prop('disabled', false);
								$processingMsg.hide();
							});
							rzp.open();
						}
					} else {
						$('#card-errors').text(response.data.message);
						$submitButton.prop('disabled', false);
						$processingMsg.hide();
					}
				},
				error: function(xhr, status, error) {
					console.error('AJAX Error:', status, error, xhr.responseText);
					$('#card-errors').text('An error occurred. Please check console and try again.');
					$submitButton.prop('disabled', false);
					$processingMsg.hide();
				}
			});
		});
	});
})(jQuery);
