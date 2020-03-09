"use strict";

(function(){


	function PaymentManagerFormFactory($log, $document, $window){

		var _self = null;
		var PaymentManagerForm = function(){
			this.data = null;
			_self = this;
		}

		PaymentManagerForm.prototype.systemPay = function(data){
			this.data = data;
			var url = data.url;
			var form = jQuery('<form action="' + url + '" method="post">' +
			  '<input type="hidden" name="vads_action_mode" value="' + data.vads_action_mode + '" />' +
			  '<input type="hidden" name="vads_site_id" value="' + data.vads_site_id + '" />' +
			  '<input type="hidden" name="signature" value="' + data.signature + '" />' +
			  '<input type="hidden" name="vads_ctx_mode" value="' + data.vads_ctx_mode + '" />' +
			  '<input type="hidden" name="vads_amount" value="' + data.vads_amount + '" />' +
			  '<input type="hidden" name="vads_currency" value="' + data.vads_currency + '" />' +
			  '<input type="hidden" name="vads_trans_date" value="' + data.vads_trans_date + '" />' +
			  '<input type="hidden" name="vads_trans_id" value="' + data.vads_trans_id + '" />' +
			  '<input type="hidden" name="vads_payment_config" value="' + data.vads_payment_config + '" />' +
			  '<input type="hidden" name="vads_capture_delay" value="' + data.vads_capture_delay + '" />' +
			  '<input type="hidden" name="vads_validation_mode" value="' + data.vads_validation_mode + '" />' +
			  '<input type="hidden" name="vads_page_action" value="' + data.vads_page_action + '" />' +
			  '<input type="hidden" name="vads_version" value="' + data.vads_version + '" />' +
			  '<input type="hidden" name="vads_url_cancel" value="' + data.vads_url_cancel + '" />' +
			  '<input type="hidden" name="vads_url_error" value="' + data.vads_url_error + '" />' +
			  '<input type="hidden" name="vads_url_refused" value="' + data.vads_url_refused + '" />' +
			  '<input type="hidden" name="vads_url_success" value="' + data.vads_url_success + '" />' +
			  '<input type="hidden" name="vads_url_check" value="' + data.vads_url_check + '" />' +
			  '</form>');
			var body = angular.element(document).find('body');
			body.append(form);
			form.submit();
		}

		PaymentManagerForm.prototype.paypal = function(data){
			/*this.data = data;
			$window.location.href = data.url;*/
			this.data = data;
			var url = data.url;
			jQuery(function($){
				var form = $('<form action="' + url + '" method="post">' +
				  '<input type="hidden" name="business" value="' + data.business + '" />' +
				  '<input type="hidden" name="notify_url" value="' + data.notify_url + '" />' +
				  '<input type="hidden" name="cancel_return" value="' + data.cancel_return + '" />' +
				  '<input type="hidden" name="return" value="' + data.return + '" />' +
				  '<input type="hidden" name="rm" value="' + data.rm + '" />' +
				  '<input type="hidden" name="lc" value="' + data.lc + '" />' +
				  '<input type="hidden" name="no_shipping" value="' + data.no_shipping + '" />' +
				  '<input type="hidden" name="no_note" value="' + data.no_note + '" />' +
				  '<input type="hidden" name="currency_code" value="' + data.currency_code + '" />' +
				  '<input type="hidden" name="page_style" value="' + data.page_style + '" />' +
				  '<input type="hidden" name="charset" value="' + data.charset + '" />' +
				  '<input type="hidden" name="item_name" value="' + data.item_name + '" />' +
				  '<input type="hidden" name="cbt" value="' + data.cbt + '" />' +
				  '<input type="hidden" name="cmd" value="' + data.cmd + '" />' +
				  '<input type="hidden" name="amount" value="' + data.amount + '" />' +
				  '<input type="hidden" name="idCustomer" value="' + data.idCustomer + '" />' +
				  '</form>');
				var body = angular.element(document).find('body');
				body.append(form);
				form.submit();
			});
		}


		PaymentManagerForm.prototype.monetico = function(data){
			/*this.data = data;
			$window.location.href = data.url;*/
			this.data = data;
			var url = data.url;
			jQuery(function($){
				var form = $('<form action="' + url + '" method="post">' +
				  '<input type="hidden" name="version" value="' + data.vads_version + '" />' +
					'<input type="hidden" name="TPE" value="' + data.vads_tpe + '" />' +
					'<input type="hidden" name="date" value="' + data.vads_date + '" />' +
					'<input type="hidden" name="montant" value="' + data.vads_amount + '" />' +
					'<input type="hidden" name="reference" value="' + data.vads_trans_id + '" />' +
					'<input type="hidden" name="MAC" value="' + data.vads_MAC + '" />' +
					'<input type="hidden" name="url_retour" value="' + data.vads_url_return + '" />' +
					'<input type="hidden" name="url_retour_ok" value="' + data.vads_url_ok + '" />' +
					'<input type="hidden" name="url_retour_err" value="' + data.vads_url_error + '" />' +
					'<input type="hidden" name="lgue" value="' + data.vads_language + '" />' +
					'<input type="hidden" name="societe" value="' + data.vads_site_id + '" />' +
					'<input type="hidden" name="texte-libre" value="' + data.vads_text + '" />' +
					'<input type="hidden" name="mail" value="' + data.vads_email + '" />' +
				  '</form>');
				var body = angular.element(document).find('body');
				body.append(form);
				form.submit();
			});
		}

		PaymentManagerForm.prototype.stripe = function(data){
			console.log(data);
			this.data = data;
			var url = data.url;
			jQuery('#step7_content').html('<script src="https://checkout.stripe.com/checkout.js"></script> <div id="buttonGroup" style="display:none"> <button id="customButton"> ' + data.words.payment_word + '</button> <button id="stripeCancel">' + data.words.cancel_word + '</button> </div>');

			var StripeCheckoutFunction = function(){
				var handler = StripeCheckout.configure({
					key: data.vads_public_key,
					image: 'https://stripe.com/img/documentation/checkout/marketplace.png',
					locale: data.vads_local,
					token: function(token) {
						jQuery('#buttonGroup').html(data.words.Redirection_word_form);
						data.token = token.id;
						data.vads_card_number = token.card.last4;
						_self.postStripe(url, data);
						console.log(token);
					}
				});

				jQuery('#customButton').on('click', function(e) {
					// Open Checkout with further options:
					handler.open({
						name: data.words.company_name,
						description: '',
						currency: data.vads_currency,
						amount: data.vads_amount
					});
					e.preventDefault();
				});

				jQuery('#stripeCancel').on('click', function(e) {
					sweetAlert({
	    		  title: data.words.ask_stop_payment_title_dialog,
	    		  text: data.words.ask_stop_payment_text_dialog,
	    		  type: "warning",
	    		  showCancelButton: true,
	    		  confirmButtonColor: "#DD6B55",
	    		  confirmButtonText: data.words.ask_stop_payment_confirmButton_dialog,
	    		  cancelButtonText: data.words.ask_stop_payment_cancelButton_dialog,
	    		  closeOnConfirm: true,
	    		  html: false
	    		}, function(){
						$window.location  = data.vads_url_error;
					});
				});

				// Close Checkout on page navigation:
				window.addEventListener('popstate', function() {
					handler.close();
				});
				jQuery('#buttonGroup').show();
				jQuery('#customButton').click();
			}

			var executeStripeCheckoutFunction = function(){
				try{
					if(StripeCheckout != null){
						StripeCheckoutFunction();
					}
					else {
						setTimeout(executeStripeCheckoutFunction, 300);
					}
				}
				catch(error){
					setTimeout(executeStripeCheckoutFunction, 300);
				}
			}
			setTimeout(executeStripeCheckoutFunction, 300);
		}

		PaymentManagerForm.prototype.postStripe = function(url, data){
			jQuery.ajax({
				type: "POST",
				url: url,
				data: JSON.stringify(data),
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				success: function(result){
					console.log(result);
					//if(sweetAlert != null) sweetAlert('', 'Ok', 'success');
					$window.location  = data.vads_url_ok;
				},
				failure: function(errMsg) {
					console.log(errMsg);
					//if(sweetAlert != null) sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					$window.location = data.vads_url_error;
				},
				error: function(errMsg) {
					console.log(errMsg);
					//if(sweetAlert != null) sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					$window.location  = data.vads_url_error;
				},
			});
		}


		/**
		 * FOR PAYBOX
		 */
		PaymentManagerForm.prototype.paybox = function(data){
			this.data = data;
			var url = data.url;
			var form = jQuery('<form action="' + url + '" method="post">' +
			  '<input type="hidden" name="PBX_SITE" value="' + data.PBX_SITE + '" />' +
			  '<input type="hidden" name="PBX_RANG" value="' + data.PBX_RANG + '" />' +
			  '<input type="hidden" name="PBX_IDENTIFIANT" value="' + data.PBX_IDENTIFIANT + '" />' +
			  '<input type="hidden" name="PBX_TOTAL" value="' + data.PBX_TOTAL + '" />' +
			  '<input type="hidden" name="PBX_DEVISE" value="' + data.PBX_DEVISE + '" />' +
			  '<input type="hidden" name="PBX_CMD" value="' + data.PBX_CMD + '" />' +
			  '<input type="hidden" name="PBX_PORTEUR" value="' + data.PBX_PORTEUR + '" />' +
			  '<input type="hidden" name="PBX_RETOUR" value="' + data.PBX_RETOUR + '" />' +
			  '<input type="hidden" name="PBX_HASH" value="' + data.PBX_HASH + '" />' +
			  '<input type="hidden" name="PBX_TIME" value="' + data.PBX_TIME + '" />' +
			  '<input type="hidden" name="PBX_RUF1" value="' + data.PBX_RUF1 + '" />' +
			  '<input type="hidden" name="PBX_EFFECTUE" value="' + data.PBX_EFFECTUE + '" />' +
			  '<input type="hidden" name="PBX_ANNULE" value="' + data.PBX_ANNULE + '" />' +
			  '<input type="hidden" name="PBX_REFUSE" value="' + data.PBX_REFUSE + '" />' +
			  '<input type="hidden" name="PBX_ATTENTE" value="' + data.PBX_ATTENTE + '" />' +
			  '<input type="hidden" name="PBX_REPONDRE_A" value="' + data.PBX_REPONDRE_A + '" />' +
			  '<input type="hidden" name="PBX_HMAC" value="' + data.PBX_HMAC + '" />' +
			  '</form>');
			var body = angular.element(document).find('body');
			body.append(form);
			form.submit();
		}

		PaymentManagerForm.prototype.swikly = function(data){
			this.data = data;
			$window.location.href = data.url;
		}

		PaymentManagerForm.prototype.setData = function(data){
			this.data = data;
		}

		PaymentManagerForm.prototype.stripeConnect = function(data){
			console.log(data);
			if(data.clientSecret == null){
				$window.location = data.vads_url_error;
			}
			else {
				jQuery('#step7_content').html('<script src="https://js.stripe.com/v3/"></script><h4 style="text-align:center;">' + data.words.title + '</h4><p style="text-align:center;">' + data.words.description + '<br />' + data.words.Amount_to_be_paid_words + ' : ' + (data.vads_amount/100) + data.words.currency  + '</p><form action="' + data.redirectURL + '" method="post" id="payment-form"><div style="width:300px; margin:auto; text-align:center; margin-top:40px; margin-bottom:40px"><div id="card-element"></div><div id="card-errors" style="color:red; font-weight:bold;" role="alert"></div><button style="width:200px; margin-top:30px" class="resa-btn btn-small" id="card-button" data-secret="' + data.clientSecret + '">' + data.words.payment_word + '</button><button style="width:100px; margin-top:30px" class="resa-btn btn-small action-delete" id="stripeCancel">' + data.words.cancel_word + '</button></div></form></div>');

				jQuery('#stripeCancel').on('click', function(e) {
					sweetAlert({
	    		  title: data.words.ask_stop_payment_title_dialog,
	    		  text: data.words.ask_stop_payment_text_dialog,
	    		  type: "warning",
	    		  showCancelButton: true,
	    		  confirmButtonColor: "#DD6B55",
	    		  confirmButtonText: data.words.ask_stop_payment_confirmButton_dialog,
	    		  cancelButtonText: data.words.ask_stop_payment_cancelButton_dialog,
	    		  closeOnConfirm: true,
	    		  html: false
	    		}, function(){
						$window.location  = data.vads_url_error;
					});
				});

				var StripeCheckoutFunction = function(){
					var stripe = Stripe(data.pkKey, { stripeAccount: data.stripeConnectId });
					var elements = stripe.elements();
					var style = {
					  base: {
					    color: '#32325d',
					    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
					    fontSmoothing: 'antialiased',
					    fontSize: '16px',
					    '::placeholder': {
					      color: '#aab7c4'
					    }
					  },
					  invalid: {
					    color: '#fa755a',
					    iconColor: '#fa755a'
					  }
					};



					// Create an instance of the card Element.
					var cardElement = elements.create('card', {style: style});
					cardElement.mount('#card-element');
					var cardButton = document.getElementById('card-button');
					var clientSecret = cardButton.dataset.secret;
					var form = document.getElementById('payment-form');
					form.addEventListener('submit', function(event) {
						jQuery('#card-button').prop("disabled", true);
						jQuery('#card-button').addClass('resa-btn-disabled');
						jQuery('#card-button').text(data.words.payment_word + '...');
						event.preventDefault();
						console.log(clientSecret);
						console.log(cardElement);
						stripe.handleCardPayment(
							clientSecret,
							cardElement
						  ).then(function(result) {
							if (result.error) {
								jQuery('#card-button').prop("disabled", false);
								jQuery('#card-button').removeClass('resa-btn-disabled');
								jQuery('#card-button').text(data.words.payment_word);
								jQuery('#card-errors').text(result.error.message);
							  console.log(result.error);
							} else {
								//Update booking stripe
								console.log(result.paymentIntent);
								jQuery('#card-button').text(data.words.update_booking_word + '...');

								jQuery.ajax({
									type: "POST",
									url: data.validationUrl,
									data: JSON.stringify({
										pid:result.paymentIntent.id,
										stripeConnectId:data.stripeConnectId,
										amount:data.vads_amount
									}),
									contentType: "application/json; charset=utf-8",
									dataType: "json",
									success: function(result){
										jQuery('#card-button').text(data.words.Redirection_word_form + '...');
										$window.location  = data.vads_url_ok;
									},
									failure: function(errMsg) {
										console.log(errMsg);
										$window.location = data.vads_url_error;
									},
									error: function(errMsg) {
										console.log(errMsg);
										$window.location  = data.vads_url_error;
									},
								});
							}
						});
					});
				}

				var executeStripeCheckoutFunction = function(){
					try{
						if(Stripe != null){
							StripeCheckoutFunction();
						}
						else {
							setTimeout(executeStripeCheckoutFunction, 300);
						}
					}
					catch(error){
						setTimeout(executeStripeCheckoutFunction, 300);
					}
				}
				setTimeout(executeStripeCheckoutFunction, 300);
			}
		}

		return PaymentManagerForm;
	}

	angular.module('resa_app').factory('PaymentManagerForm', PaymentManagerFormFactory);
}());
