"use strict";

(function(){
	var _self = null;
	var _scope = null;

	function NewReceiptBookingManagerFactory($log, $filter, FunctionsManager){
		var NewReceiptBookingManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(NewReceiptBookingManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.customer = null;
      this.booking = null;
      this.services = [];
      this.reductions = [];
      this.paymentsTypeList = [];
      this.settings = {};
			this.format = 'A4';
			this.displayFormatButtons = true;

      _scope = scope;
      _self = this;

      if(_scope.backendCtrl != null){ _scope.backendCtrl.receiptBookingCtrl = this; }
			if(_scope.accountCtrl != null){ _scope.accountCtrl.receiptBookingCtrl = this; }
		}

    NewReceiptBookingManager.prototype.printDiv = function(id) {
      var content = document.getElementById(id).parentNode.innerHTML;
  		var newWindow = window.open ('','', "menubar=yes,scrollbars=yes,resizable=yes");
  		newWindow.document.open();
			var format = 'resa_popupa4';
			if(this.format != 'A4') format = 'resa_popupticket';
  		newWindow.document.write('<html><head><title></title></head><body><div id="resa_reciept_resa" class="'+ format +'">'+content+'</div></body></html>');
  		var arrStyleSheets = document.getElementsByTagName("link");
  		for (var i = 0; i < arrStyleSheets.length; i++){
  			newWindow.document.head.appendChild(arrStyleSheets[i].cloneNode(true));
  		}
  		var arrStyle = document.getElementsByTagName("style");
  		for (var i = 0; i < arrStyle.length; i++){
  			newWindow.document.head.appendChild(arrStyle[i].cloneNode(true));
  		}
  		newWindow.document.close();
  		newWindow.focus();
  		setTimeout(function(){
				newWindow.print();
				newWindow.close(); },
				1000);
    }

		NewReceiptBookingManager.prototype.initialize = function(customer, booking, services, reductions, paymentsTypeList, displayFormatButtons, settings) {
      this.customer = customer;
      this.booking = JSON.parse(JSON.stringify(booking));
      this.services = services;
      this.reductions = reductions;
      this.paymentsTypeList = paymentsTypeList;
      this.settings = settings;
			this.displayFormatButtons = displayFormatButtons;

			//remove update paiement
			var newPaiments = [];
			for(var i = 0; i < this.booking.payments.length; i++){
				var payment = this.booking.payments[i];
				if(payment.state != 'pending'){
					newPaiments.push(payment);
				}
			}
			this.booking.payments = newPaiments;
			if(this.isCaisseOnlineActivated()){
				this.getPaymentsForBooking(this.booking);
			}
			this.getStripeChargeForTransactionId(this.booking);
		}

    NewReceiptBookingManager.prototype.close = function() {
      this.opened = false;
    }

		/**
		 * set the format
		 */
		NewReceiptBookingManager.prototype.setFormat = function(format){
			this.format = format;
		}

		/**
		 *
		 */
		NewReceiptBookingManager.prototype.getBookingId = function(booking){
			if(booking == null) return 0;
			if(booking.idCreation > -1) return booking.idCreation;
			return booking.id;
		}

		NewReceiptBookingManager.prototype.getPlaceById = function(idPlace){
			if(this.settings != null)  {
				if(this.settings.places == null || this.settings.places=='' || this.settings.places==false){
					this.settings.places = [];
				}
				for(var i = 0; i < this.settings.places.length; i++){
					var place = this.settings.places[i];
					if(place.id == idPlace){
						return place;
					}
				}
			}
			return null;
		}


		NewReceiptBookingManager.prototype.getUnitPrice = function(priceModel, number, hours){
			var total = 0;
			if(priceModel != null){
				total = priceModel.price;
				if(!priceModel.notThresholded){
					total = 0;
					var found = false;
					for(var i = 0; i < priceModel.thresholdPrices.length; i++){
						var thresholdPrice = priceModel.thresholdPrices[i];
						if(thresholdPrice.min <= number && number <= thresholdPrice.max){
							found = true;
							total = Number(Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0)) + Number(thresholdPrice.price);
							if(thresholdPrice.byHours!=null && thresholdPrice.byHours){
								total *= hours;
							}
						}
					}
					if(!found){
						var thresholdPrice = priceModel.thresholdPrices[priceModel.thresholdPrices.length - 1];
						total = Number(thresholdPrice.max * Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0)) +
							Number(thresholdPrice.price) +
							Number(Number(thresholdPrice.supPerson!=null?thresholdPrice.supPerson:0) * (number - thresholdPrice.max));
						if(thresholdPrice.byHours!=null && thresholdPrice.byHours){
							total *= hours;
						}
					}
				}
			}
			return total;
		}

		NewReceiptBookingManager.prototype.getTotalPrice = function(priceModel, number, hours){
			var total = 0;
			if(priceModel != null){
				total = number * priceModel.price;
				if(!priceModel.notThresholded){
					total = 0;
					var found = false;
					for(var i = 0; i < priceModel.thresholdPrices.length; i++){
						var thresholdPrice = priceModel.thresholdPrices[i];
						if(thresholdPrice.min <= number && number <= thresholdPrice.max){
							found = true;
							total = Number(number * Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0)) + Number(thresholdPrice.price);
							if(thresholdPrice.byHours!=null && thresholdPrice.byHours){
								total *= hours;
							}
						}
					}
					if(!found){
						var thresholdPrice = priceModel.thresholdPrices[priceModel.thresholdPrices.length - 1];
						total = Number(thresholdPrice.max * Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0)) +
							Number(thresholdPrice.price) +
							Number(Number(thresholdPrice.supPerson!=null?thresholdPrice.supPerson:0) * (number - thresholdPrice.max));
						if(thresholdPrice.byHours!=null && thresholdPrice.byHours){
							total *= hours;
						}
					}
				}
			}
			return total;
		}

		NewReceiptBookingManager.prototype.getThresholdPrices = function(priceModel, number){
			if(priceModel.thresholdPrices.length > 0){
				for(var i = 0; i < priceModel.thresholdPrices.length; i++){
					var thresholdPrice = priceModel.thresholdPrices[i];
					if(thresholdPrice.min <= number && number <= thresholdPrice.max){
						return thresholdPrice;
					}
				}
				return priceModel.thresholdPrices[priceModel.thresholdPrices.length - 1];
			}
			return null;
		}

		/**
		 *
		 */
		NewReceiptBookingManager.prototype.getVatById = function(id){
			for(var i = 0; i < this.settings.vatList.length; i++){
				var vat = this.settings.vatList[i];
				if(vat.id == id){
					return vat;
				}
			}
			return null;
		}


		NewReceiptBookingManager.prototype.displayTVA = function(vatLine, priceModel, number, hours){
			var result = 0;
			var vat = this.getVatById(vatLine.idVat);
	    if(vat != null){
	      if(!vatLine.complete){
	        if(vatLine.useFixed){
	          var ttc = (vatLine.fixed==null?0:vatLine.fixed).toFixed(2);
						var	ht = this.calculateHT(ttc, vat);
						result = ttc - ht;
	        }
	        else {
						var ttc = 0;
						var ht = 0;
						var thresholdPrice = this.getThresholdPrices(priceModel, number);
						if(thresholdPrice != null){
							var baseVariableTTC = Number(number * Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0)* hours);
							var baseVariableHT = number * this.calculateHT(Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0) * hours, vat);

							var fixeVariableTTC = Number(thresholdPrice.price) * hours;
							var fixeVariableHT = this.calculateHT(fixeVariableTTC, vat);

		          ttc = Number(((baseVariableTTC + fixeVariableTTC) * vatLine.purcent / 100).toFixed(2));
		          ht = Number(((baseVariableHT + fixeVariableHT) * vatLine.purcent / 100).toFixed(2));
						}
						else {
							ttc = Number(((priceModel.price * vatLine.purcent / 100) * number).toFixed(2));
		          ht = Number(((this.calculateHT(priceModel.price, vat) * vatLine.purcent / 100) * number).toFixed(2));
						}
						result = ttc - ht;
	        }
	      }
	      else {
					var ttc = 0;
					var ht = 0;
					var thresholdPrice = this.getThresholdPrices(priceModel, number);
					if(thresholdPrice != null){
						var baseVariableTTC = Number(number * Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0) * hours);
						var baseVariableHT = number * this.calculateHT(Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0) * hours, vat);

						var fixeVariableTTC = Number(thresholdPrice.price) * hours;
						var fixeVariableHT = this.calculateHT(fixeVariableTTC, vat);

						ttc = Number((baseVariableTTC + fixeVariableTTC).toFixed(2));
						ht = Number((baseVariableHT + fixeVariableHT).toFixed(2));
					}
					else {
						ttc = Number((priceModel.price * number).toFixed(2));
						ht = Number((this.calculateHT(priceModel.price, vat) * number).toFixed(2));
					}
	        result = Number((ttc - ht)) - this.sumTVA(priceModel, number, hours);
	      }
	    }
			return result;
		}

		NewReceiptBookingManager.prototype.calculateHT = function(ttc, vat){
		 	return Number((ttc /  (1 + (vat.value / 100))).toFixed(2));
		}


	  NewReceiptBookingManager.prototype.sumTVA = function(priceModel, number, hours){
			var result = 0;
	    for(var i = 0; i < priceModel.vatList.length - 1; i++){
	      var vatLine = priceModel.vatList[i];
	      result += Number(this.displayTVA(vatLine, priceModel, number, hours));
	    }
	    return result;
	  }

		NewReceiptBookingManager.prototype.vatValue = function(ttc, vat){
		 	return ttc - this.calculateHT(ttc, {value:vat});
		}

		/**
		 *
		 */
		NewReceiptBookingManager.prototype.isCaisseOnlineActivated = function(){
			return this.settings != null && this.settings.caisse_online_activated;
		}


    NewReceiptBookingManager.prototype.getPaymentName = function(idPayment, name){
			var customerType = false;
			if(idPayment.indexOf('customer_') != -1) {
				idPayment = idPayment.substring('customer_'.length);
				customerType = true;
			}
			var result = this.paymentsTypeList[idPayment];
			if(result == null){
				for(var i = 0; i < this.settings.custom_payment_types.length; i++){
					var paymentType = this.settings.custom_payment_types[i];
					if(paymentType.id == idPayment){
						result = paymentType.label;
					}
				}
				if(result == null && name != null){
					result = name;
				}
				if(result == null){
					result = '???';
				}
			}
			if(customerType) result = this.getPaymentName('customer', 'Compte client') + '('+ result +')';
			return result;
		}

    NewReceiptBookingManager.prototype.getServiceById = function(idService){
			for(var i = 0; i < this.services.length; i++){
				if(this.services[i].id == idService){
					return this.services[i];
				}
			}
			return null;
		}

		NewReceiptBookingManager.prototype.getReductionById = function(idReduction){
			for(var i = 0; i < this.reductions.length; i++){
				if(this.reductions[i].id == idReduction){
					return this.reductions[i];
				}
			}
			return null;
		}

		NewReceiptBookingManager.prototype.getServicePriceAppointment = function(service, idPrice){
			if(service != null){
				for(var i = 0; i < service.servicePrices.length; i++){
					var servicePrice = service.servicePrices[i];
					if(servicePrice.id == idPrice)
						return servicePrice;
				}
			}
			return null;
		}

		NewReceiptBookingManager.prototype.getAppointmentNumberPrices = function(appointment, idPrice){
			for(var i = 0; i < appointment.appointmentNumberPrices.length; i++){
				var appointmentNumberPrice = appointment.appointmentNumberPrices[i];
				if(appointmentNumberPrice.idPrice == idPrice){
					return appointmentNumberPrice;
				}
			}
			return null;
		}

		NewReceiptBookingManager.prototype.getPriceNumberPrice = function(idService, appointmentNumberPrice, appointment){
			if(appointmentNumberPrice != null){
				var priceModel = this.getServicePriceAppointment(this.getServiceById(idService), appointmentNumberPrice.idPrice);
				var hours = (this.parseDate(appointment.endDate).getTime() - this.parseDate(appointment.startDate).getTime()) / (3600 * 1000);
				return this.getUnitPrice(priceModel, appointmentNumberPrice.number, hours);
			}
			return 0;
		}

		NewReceiptBookingManager.prototype.getTotalPriceNumberPrice = function(idService, appointmentNumberPrice, appointment){
			if(appointmentNumberPrice != null){
				var priceModel = this.getServicePriceAppointment(this.getServiceById(idService), appointmentNumberPrice.idPrice);
				var hours = (this.parseDate(appointment.endDate).getTime() - this.parseDate(appointment.startDate).getTime()) / (3600 * 1000);
				return this.getTotalPrice(priceModel, appointmentNumberPrice.number, hours);
			}
			return 0;
		}


		NewReceiptBookingManager.prototype.round = function(value){
			return Math.round(value * 100) / 100;
		}

		NewReceiptBookingManager.prototype.isDisplayed = function(appointment){
			if(appointment == null) return false;
			var appointments = this.getAppointmentsByInternalIdLink(appointment.internalIdLink);
			return appointments.length <= 1 || appointments[0].id == appointment.id;
		}

		NewReceiptBookingManager.prototype.isCombinedRow = function(appointment){
			var appointments = this.getAppointmentsByInternalIdLink(appointment.internalIdLink);
			return appointments.length > 1 && appointments[0].id == appointment.id;
		}

		NewReceiptBookingManager.prototype.getNumberCombined = function(appointment){
			var appointments = this.getAppointmentsByInternalIdLink(appointment.internalIdLink);
			if(appointments.length == 0) return 1;
			return appointments.length;
		}

		NewReceiptBookingManager.prototype.getAppointmentsByInternalIdLink = function(internalIdLink){
			var appointments = [];
			for(var i = 0; i < this.booking.appointments.length; i++){
				var appointment = this.booking.appointments[i];
				if(appointment.internalIdLink == internalIdLink){
					appointments.push(appointment);
				}
			}
			return appointments
		}

		/**
		 *
		 */
		NewReceiptBookingManager.prototype.getTotalPayments = function(booking, payments){
			var totalPaiements = 0;
			for(var i = 0; i < payments.length; i++){
				var payment = payments[i];
				if(payment.state == 'ok' || payment.state == null){
					if(payment.isReceipt == null ||
						(!payment.isReceipt && booking.paymentState == 'complete') ||
						(payment.isReceipt && booking.paymentState == 'advancePayment')){
						totalPaiements += payment.value * 1;
					}
				}
			}
			return totalPaiements;
		}

		NewReceiptBookingManager.prototype.getCaisseOnlinePaymentForType = function(payment, payments, type){
			for(var i = 0; i < payments.length; i++){
				var localPayment = payments[i];
				if(localPayment.isCaisseOnline != null && localPayment.isCaisseOnline &&
					(payment.value * 1) == localPayment.value && !payment.isCaisseOnline && payment.type == type){
					return localPayment;
				}
			}
			return null;
		}


		/**
		 * return payments with no inverse payment
		 */
		NewReceiptBookingManager.prototype.filterCancelledPayment = function(payments){
			var newPayments = [];
			for(var i = 0; i < payments.length; i++){
				var payment = payments[i];
				if(this.getInversePayment(payment, payments) == null){
					newPayments.push(payment);
				}
			}
			return newPayments;
		}

		/**
		 * return true if there is a inverse payment
		 */
		NewReceiptBookingManager.prototype.getInversePayment = function(payment, payments){
			var newPayments = [];
			for(var i = 0; i < payments.length; i++){
				var localPayment = payments[i];
				if(localPayment.type == payment.type && localPayment.value == payment.value * -1){
					return localPayment;
				}
			}
			return null;
		}


		/**
		 * get payment for booking
		 */
		NewReceiptBookingManager.prototype.getPaymentsForBooking = function(booking){
			var idsBooking = booking.linkOldBookings;
			if(idsBooking != '') idsBooking += ',';
			idsBooking += booking.id;
			var idPaymentsList = [];
			for(var i = 0; i < booking.payments.length; i++){
				if(booking.payments[i].id != null){
					idPaymentsList.push(booking.payments[i].id);
				}
			}
			var url = this.settings.caisse_online_server_url + 'ticketsByIdBooking/' + this.settings.caisse_online_license_id + '/' + idsBooking;
			jQuery.get(url, function(tickets) {
				_scope.$apply(function(){
					for(var i = 0; i < tickets.length; i++){
						var ticket = tickets[i];
						for(var j = 0; j < ticket.payments.length; j++){
							var payment = ticket.payments[j];
							var idPayment = 'ticket' + ticket.id + '_' + j;
							if(idPaymentsList.indexOf(idPayment) == -1){
								booking.payments.push({
									id:idPayment,
									isCaisseOnline: true,
									isReceipt:(ticket.type != 'ticket'),
									idBooking:booking.id,
									paymentDate:new Date(ticket.date),
									type:payment.type,
									value:payment.amount,
									name:payment.name,
									idReference:payment.externalId
								});
							}
						}
					}
					var newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, booking.payments);
					if(newNeedToPay < 0){
						var newPayments = [];
						for(var i = 0; i < booking.payments.length; i++){
							var payment = booking.payments[i];
							if(payment.isCaisseOnline || payment.type != 'systempay' || payment.type != 'paypal' || payment.type != 'monetico' || payment.type != 'stripe' ||  payment.type != 'stripeConnect' || payment.type != 'paybox'){
								newPayments.push(payment);
							}
							else if(payment.type == 'systempay' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'systempay') == null){
								newPayments.push(payment);
							}
							else if(payment.type == 'paypal' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'paypal') == null){
								newPayments.push(payment);
							}
							else if(payment.type == 'monetico' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'monetico') == null){
								newPayments.push(payment);
							}
							else if(payment.type == 'stripe' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'stripe') == null){
								newPayments.push(payment);
							}
							else if(payment.type == 'stripeConnect' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'stripeConnect') == null){
								newPayments.push(payment);
							}
							else if(payment.type == 'paybox' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'paybox') == null){
								newPayments.push(payment);
							}
						}
						newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, newPayments);
						booking.payments = newPayments;
					}
					booking.payments = this.filterCancelledPayment(booking.payments);
					booking.needToPay = newNeedToPay;
					booking.paymentsCaisseOnlineDone = true;
				}.bind(this));
			}.bind(this)).fail(function(err){
			 //sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
		 }).error(function(err){
			 //sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
		 });
		}


		/**
		 * get payment stripe connect for booking
		 */
		NewReceiptBookingManager.prototype.getStripeChargeForTransactionId = function(booking){
			var idsBooking = booking.linkOldBookings;
			if(idsBooking != '') idsBooking += ',';
			idsBooking += booking.id;
			var idPaymentsList = [];
			for(var i = 0; i < booking.payments.length; i++){
				if(booking.payments[i].id != null){
					idPaymentsList.push(booking.payments[i].id);
				}
			}
			if(this.settings.apiStripConnectUrl != '' && booking.typePaymentChosen == 'stripeConnect'){
				jQuery.ajax({
					type: "POST",
					url: this.settings.apiStripConnectUrl + 'getCharges.php',
					data: JSON.stringify({
						transactionId:booking.transactionId,
			      stripeConnectId:this.settings.stripeConnectId
					}),
					contentType: "application/json; charset=utf-8",
					dataType: "json",
					success: function(charges){
						_scope.$apply(function(){
							for(var i = 0; i < charges.length; i++){
				        var charge = charges[i];
				        for(var j = 0; j < charge.length; j++){
				          var payment = charge[j];
				          var idPayment = payment.id;
				          var idBookings = [];
				          if(idPaymentsList.indexOf(idPayment) == -1){
				            idPaymentsList.push(idPayment);
				            booking.payments.push({
				              id:idPayment,
				              payment_intent:payment.payment_intent,
				              isStripeConnect:true,
				              isReceipt:booking.paymentState == 'advancePayment',
				              idBooking:payment.metadata?payment.metadata.idBooking:-1,
				              paymentDate:new Date(payment.created * 1000),
				              type:'stripeConnect',
				              value:payment.amount/100,
				              name:'TEST',
				              idReference:payment.id,
				              credit:false,
				              vendor:'Stripe',
				              note:''
				            });

			              if(payment.refunds.data.length > 0){
			                for(var k = 0; k < payment.refunds.data.length; k++){
			                  var refund = payment.refunds.data[k];
			                  booking.payments.push({
			                    id:idPayment + '_r' + k,
			                    payment_intent:payment.payment_intent + '_r' + k,
			                    isStripeConnect:true,
			                    isReceipt:booking.paymentState == 'advancePayment',
					                isCancellable:false,
			                    idBooking:payment.metadata?payment.metadata.idBooking:-1,
			                    paymentDate:new Date(refund.created * 1000),
			                    type:'stripeConnect',
			                    value:(refund.amount/100) * -1,
			                    name:'Stripe',
			                    idReference:payment.id,
			                    credit:false,
			                    vendor:'Stripe',
			                    refunded:true,
			                    isRefund:true,
			                    note:'',
			                    receipt_url:payment.receipt_url
			                  });
			                }
			              }
				          }
				        }
							}
							var newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, booking.payments);
							if(newNeedToPay < 0){
								var newPayments = [];
								for(var i = 0; i < booking.payments.length; i++){
									var payment = booking.payments[i];
									if(payment.isCaisseOnline || payment.type != 'systempay' || payment.type != 'paypal' || payment.type != 'monetico' || payment.type != 'stripe' ||  payment.type != 'stripeConnect' || payment.type != 'paybox'){
										newPayments.push(payment);
									}
									else if(payment.type == 'systempay' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'systempay') == null){
										newPayments.push(payment);
									}
									else if(payment.type == 'paypal' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'paypal') == null){
										newPayments.push(payment);
									}
									else if(payment.type == 'monetico' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'monetico') == null){
										newPayments.push(payment);
									}
									else if(payment.type == 'stripe' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'stripe') == null){
										newPayments.push(payment);
									}
									else if(payment.type == 'stripeConnect' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'stripeConnect') == null){
										newPayments.push(payment);
									}
									else if(payment.type == 'paybox' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'paybox') == null){
										newPayments.push(payment);
									}
								}
								newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, newPayments);
								booking.payments = newPayments;
							}
							booking.payments = this.filterCancelledPayment(booking.payments);
							booking.needToPay = newNeedToPay;
							booking.paymentsCaisseOnlineDone = true;
						}.bind(this));
					}.bind(this),
					failure: function(errMsg) {
						//sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					},
					error: function(errMsg) {
						//sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					},
				});
			}
		}

		return NewReceiptBookingManager;
	}

	angular.module('resa_app').factory('NewReceiptBookingManager', NewReceiptBookingManagerFactory);
}());
