import { Input, OnInit, Component } from '@angular/core';

import { DatePipe } from '@angular/common';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

import { GlobalService } from '../../services/global.service';
import { UserService } from '../../services/user.service';

declare var swal: any;

@Component({
  selector: 'receipt',
  templateUrl: 'receipt.html',
  styleUrls:['./receipt.css']
})

export class ReceiptComponent implements OnInit {
  @Input() booking = null;

  public launchAction:boolean = false;
  public customer = null;
  public services = [];
  public reductions = [];
  public paymentsTypeToName = {};
  public settings = null;
  public format = 'A4';
  public displayFormatButtons = true;

  constructor(public global:GlobalService, private userService:UserService, private activeModal: NgbActiveModal) {

  }

  ngOnInit(): void {

  }

  close(){ this.activeModal.close(); }

  loadBookingReceipt(booking){
    this.booking = booking;
    this.customer = booking.customer
    this.launchAction = true;
    this.userService.get('receipt/:token/' + booking.id, function(data){
      this.launchAction = false;
      this.services = data.services;
      this.reductions = data.reductions;
      this.settings = data.settings;
      this.paymentsTypeToName = data.paymentsTypeToName;
      this.getPaymentsForBooking(this.booking);
      this.getStripeChargeForTransactionId(this.booking);
    }.bind(this), function(error){
      this.launchAction = false;
      var text = 'Impossible de récupérer les données du client';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  printDiv(id) {
    var content = document.getElementById(id).innerHTML;
    var newWindow = window.open ('','', "menubar=yes,scrollbars=yes,resizable=yes");
    newWindow.document.open();
    newWindow.document.write("<html><head><title></title></head><body class='printing'>"+content+"</body></html>");
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

  setFormat(format){
    this.format = format;
  }

  getPlaceById(idPlace){
    if(this.settings != null)  {
      if(this.settings.places == null || this.settings.places == '' || this.settings.places==false){
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

  getServiceName(service){
    var name = this.global.getTextByLocale(service.name);
    if(this.settings != null && this.settings.places != null && this.settings.places.length > 0 && service.places.length > 0){
      var placesText = '';
      for(var i = 0; i < service.places.length; i++){
        var place = this.settings.places.find(element => element.id == service.places[i]);
        if(i > 0) placesText += ', ';
        placesText += this.global.getTextByLocale(place.name);
      }
      name = '['+placesText+'] ' + name;
    }
    return name;
  }


  getUnitPrice(priceModel, number, hours:number){
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

  getTotalPrice(priceModel, number, hours){
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

  getThresholdPrices(priceModel, number){
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
  getVatById(id){
    for(var i = 0; i < this.settings.vatList.length; i++){
      var vat = this.settings.vatList[i];
      if(vat.id == id){
        return vat;
      }
    }
    return null;
  }


  displayTVA(vatLine, priceModel, number, hours):number{
    var result = 0;
    var vat = this.getVatById(vatLine.idVat);
    if(vat != null){
      if(!vatLine.complete){
        if(vatLine.useFixed){
          var ttc = Number((vatLine.fixed==null?0:vatLine.fixed).toFixed(2));
          var	ht = Number(this.calculateHT(ttc, vat));
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

  calculateHT(ttc, vat){
    return Number((ttc /  (1 + (vat.value / 100))).toFixed(2));
  }


  sumTVA(priceModel, number, hours){
    var result = 0;
    for(var i = 0; i < priceModel.vatList.length - 1; i++){
      var vatLine = priceModel.vatList[i];
      result += Number(this.displayTVA(vatLine, priceModel, number, hours));
    }
    return result;
  }

  vatValue(ttc, vat){
    return ttc - this.calculateHT(ttc, {value:vat});
  }

  /**
   *
   */
  isCaisseOnlineActivated(){
    return this.settings != null && this.settings.caisse_online_activated;
  }


  getPaymentName(idPayment, name){
    var customerType = false;
    if(idPayment.indexOf('customer_') != -1) {
      idPayment = idPayment.substring('customer_'.length);
      customerType = true;
    }
    var result = this.paymentsTypeToName[idPayment];
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

  getServiceById(idService){
    for(var i = 0; i < this.services.length; i++){
      if(this.services[i].id == idService){
        return this.services[i];
      }
    }
    return null;
  }

  getReductionById(idReduction){
    for(var i = 0; i < this.reductions.length; i++){
      if(this.reductions[i].id == idReduction){
        return this.reductions[i];
      }
    }
    return null;
  }

  getServicePriceAppointment(service, idPrice){
    if(service != null){
      for(var i = 0; i < service.servicePrices.length; i++){
        var servicePrice = service.servicePrices[i];
        if(servicePrice.id == idPrice)
          return servicePrice;
      }
    }
    return null;
  }

  getAppointmentNumberPrices(appointment, idPrice){
    for(var i = 0; i < appointment.appointmentNumberPrices.length; i++){
      var appointmentNumberPrice = appointment.appointmentNumberPrices[i];
      if(appointmentNumberPrice.idPrice == idPrice){
        return appointmentNumberPrice;
      }
    }
    return null;
  }

  getPriceNumberPrice(idService, appointmentNumberPrice, appointment){
    if(appointmentNumberPrice != null){
      var priceModel = this.getServicePriceAppointment(this.getServiceById(idService), appointmentNumberPrice.idPrice);
      var endDate = new Date(appointment.endDate);
      var startDate = new Date(appointment.startDate);
      var hours = (endDate.getTime() - startDate.getTime()) / (3600 * 1000);
      return this.getUnitPrice(priceModel, appointmentNumberPrice.number, hours);
    }
    return 0;
  }

  getTotalPriceNumberPrice(idService, appointmentNumberPrice, appointment){
    if(appointmentNumberPrice != null){
      var priceModel = this.getServicePriceAppointment(this.getServiceById(idService), appointmentNumberPrice.idPrice);
      var endDate = new Date(appointment.endDate);
      var startDate = new Date(appointment.startDate);
      var hours = (endDate.getTime() - startDate.getTime()) / (3600 * 1000);
      return this.getTotalPrice(priceModel, appointmentNumberPrice.number, hours);
    }
    return 0;
  }


  round(value){
    return Math.round(value * 100) / 100;
  }

  isDisplayed(appointment){
    if(appointment == null) return false;
    var appointments = this.getAppointmentsByInternalIdLink(appointment.internalIdLink);
    return appointments.length <= 1 || appointments[0].id == appointment.id;
  }

  isCombinedRow(appointment){
    var appointments = this.getAppointmentsByInternalIdLink(appointment.internalIdLink);
    return appointments.length > 1 && appointments[0].id == appointment.id;
  }

  getNumberCombined(appointment){
    var appointments = this.getAppointmentsByInternalIdLink(appointment.internalIdLink);
    if(appointments.length == 0) return 1;
    return appointments.length;
  }

  getAppointmentsByInternalIdLink(internalIdLink){
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
  getTotalPayments(booking, payments){
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

  getCaisseOnlinePaymentForType(payment, payments, type){
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
  filterCancelledPayment(payments){
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
  getInversePayment(payment, payments){
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
  getPaymentsForBooking(booking){
    var idsBooking = booking.linkOldBookings;
    if(idsBooking != '') idsBooking += ',';
    idsBooking += booking.id;
    var idPaymentsList = [];
    for(var i = 0; i < booking.payments.length; i++){
      if(booking.payments[i].id != null){
        idPaymentsList.push(booking.payments[i].id);
      }
    }
    if(this.settings.caisse_online_server_url != ''){
      var url = this.settings.caisse_online_server_url + 'ticketsByIdBooking/' + this.settings.caisse_online_license_id + '/' + idsBooking;
      this.launchAction = true;
      this.userService.getGlobal(url, (tickets) => {
        var idPaymentsList = [];
        for(var i = 0; i < booking.payments.length; i++){
          if(booking.payments[i].id != null){
            idPaymentsList.push(booking.payments[i].id);
          }
        }
        for(var i = 0; i < tickets.length; i++){
          var ticket = tickets[i];
          for(var j = 0; j < ticket.payments.length; j++){
            var payment = ticket.payments[j];
            var idPayment = 'ticket' + ticket.id + '_' + j;
            var note = '';
            var idBookings = [];
            if(idPaymentsList.indexOf(idPayment) == -1){
              idPaymentsList.push(idPayment);
              booking.payments.push({
                id:idPayment,
                isCaisseOnline:true,
                isReceipt:(ticket.type != 'ticket'),
                idBooking:booking.id,
                idBookings:idBookings,
                paymentDate:new Date(ticket.date),
                type:payment.type,
                value:payment.amount,
                name:payment.name,
                idReference:payment.externalId,
                credit:(payment.credit == 'true'),
                vendor:ticket.settings.vendor,
                note:note
              });
            }
          }
        }
        var newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, booking.payments);
        if(newNeedToPay < 0){
          var newPayments = [];
          for(var i = 0; i < booking.payments.length; i++){
            var payment = booking.payments[i];
            if(payment.isCaisseOnline || payment.type != 'systempay' || payment.type != 'paypal' || payment.type != 'monetico' || payment.type != 'stripe' || payment.type != 'paybox'){
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
            else if(payment.type == 'paybox' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'paybox') == null){
              newPayments.push(payment);
            }
          }
          newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, newPayments);
        }
        booking.needToPay = newNeedToPay;
        booking.paymentsCaisseOnlineDone = true;
        this.launchAction = false;
      }, (error) => {
        this.launchAction = false;
        var text = 'Impossible de récupérer les données de la caisse';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'})
      });
    }
  }

  /**
   *
   */
  getStripeChargeForTransactionId(booking){
    if(this.global.stripeConnect.apiStripeConnectUrl != '' && booking.typePaymentChosen == 'stripeConnect'){
      var url = this.global.stripeConnect.apiStripeConnectUrl + 'getCharges.php';
      this.launchAction = true;
      this.userService.postGlobal(url, {
        transactionId:booking.transactionId,
        stripeConnectId:this.global.stripeConnect.stripeConnectId
      }, (charges) => {
        var idPaymentsList = [];
        for(var i = 0; i < booking.payments.length; i++){
          if(booking.payments[i].id != null){
            idPaymentsList.push(booking.payments[i].id);
          }
        }
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
              if(payment.refunded){
                booking.payments.push({
                  id:idPayment + '_r',
                  payment_intent:payment.payment_intent + '_r',
                  isStripeConnect:true,
                  isReceipt:booking.paymentState == 'advancePayment',
                  isCancellable:!payment.refunded,
                  idBooking:payment.metadata?payment.metadata.idBooking:-1,
                  paymentDate:new Date(payment.refunds.data[0].created * 1000),
                  type:'stripeConnect',
                  value:(payment.amount_refunded/100) * -1,
                  name:'Stripe',
                  idReference:payment.id,
                  credit:false,
                  vendor:'Stripe',
                  note:payment.refunds.data[0].reason
                });
              }
            }
          }
        }
        var newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, booking.payments);
        if(newNeedToPay < 0){
          var newPayments = [];
          for(var i = 0; i < booking.payments.length; i++){
            var payment = booking.payments[i];
            if(payment.isCaisseOnline || payment.type != 'systempay' || payment.type != 'paypal' || payment.type != 'monetico' || payment.type != 'stripe' || payment.type != 'stripeConnect' || payment.type != 'paybox'){
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
        }
        booking.needToPay = newNeedToPay;
        booking.paymentsCaisseOnlineDone = true;
        this.launchAction = false;
      }, (error) => {
        this.launchAction = false;
        var text = 'Impossible de récupérer les données de stripe';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'})
      });
    }
  }

}
