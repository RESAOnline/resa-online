import { Input, Output, OnChanges, OnInit, OnDestroy, Component, EventEmitter, SimpleChanges  } from '@angular/core';
import { formatDate } from "@angular/common";

import { Subject, Observable } from 'rxjs';
import { debounceTime, distinctUntilChanged, map, switchMap  } from 'rxjs/operators';
import { ActivatedRoute } from '@angular/router';

import { DatePipe } from '@angular/common';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';

import { ServiceParameters, Basket } from '../../models/bookingModels';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';

declare var swal: any;

@Component({
  selector: 'booking-editor',
  templateUrl: 'bookingEditor.html',
  styleUrls:['../../pages/planning/planning.css','./bookingEditor.css']
})

export class BookingEditorComponent implements OnInit, OnChanges {
  @Input() displayModal = false;
  @Input() selectedTimeslot = null;
  @Output() close: EventEmitter<any> = new EventEmitter();

  public settings:any = null;
  public customer:any = null;
  public services = [];
  public members = [];
  public equipments = [];
  public reductions = [];
  public currentPromoCodes = [];
  public oldTimeslots = [];
  public oldParticipants = [];
  public copyCurrentServiceParameters:any = null;

  public currentTab = 1;

  public currentBooking = null;
  public history = [];
  public quotation = false;
  public basket:Basket = new Basket();
  public currentServiceParameters:ServiceParameters = null
  public notes = {private:'', public:'', members:'', customer:''};
  public bookingLoadedIsModified = false;
  public bookingInProcess:boolean = false;
  public customReductions = [];
  public mapIdClientObjectReduction = {};
  public mapIdClientObjectReductionBooking = {};
  public vouchersAdded = [];

  public customTimeslot = {
    startTime:{hour: 12, minute: 0},
    endTime:{hour: 12, minute: 30},
    noEnd: false,
    idParameter:1
  }
  public currentCustomReduction = {id:-1, description: '', amount: 0, vatValue:0};
  public selectorParticipants = {fields:[], participants:[], numberPrice:null, index:-1};
  public voucherLocal = '';
  public synchronizedDays = true;

  public customerAdvancedForm:boolean = false;
  public modelCustomer:any = null;
  private skeletonCustomer:any = null;
  public customers:any[] = [];
  private searchUpdated: Subject<string> = new Subject<string>();
  public searchCustomerDone:boolean = false;

  public callbackSuccessAdd = null;

  public actionLaunch:boolean = false;
  public reductionsLaunched:boolean = false;
  public relanchGetReductions:boolean = false;

  constructor(private userService:UserService, private global:GlobalService, private modalService: NgbModal, private route: ActivatedRoute){
  }

  ngOnInit(){
    this.initialize();
    this.searchUpdated.pipe(debounceTime(1000)).subscribe(text => { this.searchCustomer() });
  }

  ngOnChanges(value:SimpleChanges){

  }

  isTabCustomer(){ return this.currentTab == 1; }
  isTabBooking(){ return this.currentTab == 2; }
  isTabNotes(){ return this.currentTab == 3; }
  isTabHistory(){ return this.currentTab == 4; }
  setTabCustomer(){ this.currentTab = 1; }
  setTabBooking(){ this.currentTab = 2; }
  setTabNotes(){ this.currentTab = 3; }
  setTabHistory(){ this.currentTab = 4; }

  initialize(){
    this.actionLaunch = true;
    this.userService.get('bookingEditor/:token', function(data){
      this.actionLaunch = false;
      if(data.settings) this.settings = data.settings;
      if(data.modelCustomer) {
        data.modelCustomer.createWpAccount = true;
        this.modelCustomer = data.modelCustomer;
        this.skeletonCustomer = JSON.parse(JSON.stringify(data.modelCustomer));
      }
    }.bind(this), function(error){
      this.actionLaunch = false;
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  loadMembers(){
    this.userService.get('membersLite/:token', function(members){
      this.members = members;
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les membres';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  loadEquipments(){
    this.userService.get('equipmentsLite/:token', function(equipments){
      this.equipments = equipments;
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les membres';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  loadCurrentPromoCodes(){
    this.userService.get('currentPromoCodes/:token', function(currentPromoCodes){
      this.currentPromoCodes = currentPromoCodes;
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les codes promos';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  getServiceById(idService, successCallback){
    var service = this.services.find((element) => { return element.id == idService });
    if(service != null){
      successCallback(service);
    }
    else {
      this.userService.get('justOneActivity/:token/' + idService, function(service){
        successCallback(service);
      }.bind(this), function(error){
        var text = 'Impossible de récupérer les données des activitées';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }

  getTimeslot(startDate, endDate, idActivity, successCallback, errorCallback){
    this.userService.post('timeslot/:token', {
      startDate:startDate,
      endDate:endDate,
      idActivity:idActivity
    }, function(timeslot){
      successCallback(timeslot);
    }.bind(this), function(error){
      errorCallback(error);
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  getBookingData(idBooking, successCallback){
    this.userService.get('bookingData/:token/' + idBooking, function(data){
      successCallback(data);
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  loadBooking(booking){
    this.actionLaunch = true;
    this.reinit();
    this.basket = new Basket();
    this.currentBooking = booking;
    this.quotation = this.currentBooking.quotation;
    var allServiceParameters = [];
    this.mapIdClientObjectReductionBooking = {};
    this.getBookingData(booking.id, (data) => {
      this.setCustomer(data.customer);
      this.oldTimeslots = data.timeslots;
      this.history = data.history;
      this.services = data.services;
      this.reductions = data.reductions;
      for(var i = 0; i < this.currentBooking.appointments.length; i++){
        var appointment = this.currentBooking.appointments[i];
        var serviceParameters = new ServiceParameters();
        var service = this.services.find(element => element.id == appointment.idService);
        if(service != null){
          serviceParameters.id = this.basket.getNextId();
          serviceParameters.idAppointment = appointment.id;
          serviceParameters.place = appointment.idPlace;
          serviceParameters.service = service;
          serviceParameters.dateStart = this.global.parseDate(appointment.startDate);
          serviceParameters.dateEnd = this.global.parseDate(appointment.endDate);
          serviceParameters.noEnd = appointment.noEnd;
          serviceParameters.tags = appointment.tags;
          serviceParameters.idServiceParametersLink = appointment.internalIdLink;
          serviceParameters.state = appointment.state;
          serviceParameters.idParameter = appointment.idParameter;
          serviceParameters.numberPrices = [];
          for(var j = 0; j < appointment.appointmentNumberPrices.length; j++){
            var appointmentNumberPrice = appointment.appointmentNumberPrices[j];
            var price = service.servicePrices.find(element => element.id == appointmentNumberPrice.idPrice);
            if(price != null){
              serviceParameters.pushInNumberPrice(price, appointmentNumberPrice.number, appointmentNumberPrice.participants);
            }
          }
          if(appointment.appointmentReductions.length > 0){
            this.mapIdClientObjectReductionBooking['id' + serviceParameters.id] = [];
            var alreadyInserted = [];
            for(var j = 0; j < appointment.appointmentReductions.length; j++){
              var appointmentReductions = JSON.parse(JSON.stringify(appointment.appointmentReductions[j]));
              if(alreadyInserted.indexOf(appointmentReductions.id) == -1){
                this.mapIdClientObjectReductionBooking['id' + serviceParameters.id].push(appointmentReductions);
              }
              if(appointmentReductions.promoCode!=null && appointmentReductions.promoCode.length > 0 && this.vouchersAdded.indexOf(appointmentReductions.promoCode) == -1){
                this.vouchersAdded.push(appointmentReductions.promoCode);
              }
              if(appointmentReductions.idsPrice == null){
                appointmentReductions.idsPrice = [];
              }
              if(appointmentReductions.idPrice != -1){
                appointmentReductions.idsPrice.push(appointmentReductions.idPrice + '');
              }
            }
          }
          if(appointment.appointmentMembers.length > 0){
            for(var j = 0; j < appointment.appointmentMembers.length; j++){
              var appointmentMember = appointment.appointmentMembers[j];
              var member = data.members.find(element => element.id == appointmentMember.idMember);
              if(member != null){
                serviceParameters.staffs.push({
                  id: appointmentMember.idMember,
                  nickname: member.nickname,
                  capacity: appointmentMember.number,
                  usedCapacity: 0
                });
              }
            }
          }
          var timeslot = data.timeslots.find(element => element.idAppointment == appointment.id);
          if(!service.oldService) serviceParameters.addNumberPriceForEachServicePrice(timeslot);
          serviceParameters.setCapacityWithTimeslot(timeslot);
          if(timeslot.isCustom) serviceParameters.customTimeslot = timeslot.isCustom;
          if(timeslot.idsServicePrices) serviceParameters.idsServicePrices = timeslot.idsServicePrices;
          if(timeslot.membersUsed) serviceParameters.membersUsed = timeslot.membersUsed;
          serviceParameters.mergeMembersUsedWithMembers(this.members);
          serviceParameters.maxCapacity = serviceParameters.getNumber();
          this.basket.addServiceParameters(serviceParameters);
        }
      }
      this.mapIdClientObjectReductionBooking['id0'] = [];
      for(var i = 0; i < this.currentBooking.bookingReductions.length; i++){
        var bookingReduction = this.currentBooking.bookingReductions[i];
        if(bookingReduction.promoCode!=null && bookingReduction.promoCode.length > 0 && this.vouchersAdded.indexOf(bookingReduction.promoCode) == -1){
          this.vouchersAdded.push(bookingReduction.promoCode);
        }
        this.mapIdClientObjectReductionBooking['id0'].push(JSON.parse(JSON.stringify(bookingReduction)));
      }
      this.mapIdClientObjectReduction = this.mapIdClientObjectReductionBooking;
      for(var i = 0; i < this.currentBooking.bookingCustomReductions.length; i++){
        this.customReductions.push(JSON.parse(JSON.stringify(this.currentBooking.bookingCustomReductions[i])));
      }
      this.notes.private = this.global.htmlSpecialDecode(this.currentBooking.note);
      this.notes.public = this.global.htmlSpecialDecode(this.currentBooking.publicNote);
      this.notes.members = this.global.htmlSpecialDecode(this.currentBooking.staffNote);
      this.notes.customer = this.global.htmlSpecialDecode(this.currentBooking.customerNote);
      this.setTabBooking();
      this.actionLaunch = false;
    });
  }

  reinit(){
    if(!this.bookingInProcess){
      this.clearCustomer();
      this.customers = [];
      this.customReductions = [];
      this.vouchersAdded = [];
      this.setTabCustomer();
      this.loadMembers(); //TODO optimize
      this.loadEquipments();
      this.loadCurrentPromoCodes();
      this.basket = new Basket();
      this.mapIdClientObjectReduction = {};
      this.currentServiceParameters = null;
      this.currentBooking = null;
      this.copyCurrentServiceParameters = null;
    }
    else {
      this.setTabBooking();
    }
  }

  openBookingEditor(selectedTimeslot){
    this.reinit();
    if(this.currentServiceParameters == null){
      this.addInBasket(selectedTimeslot);
    }
    else {
      this.setTimeslotWithPlanning(selectedTimeslot);
    }
  }

  isNewBooking(){ return this.currentBooking == null; }

  getSearchCustomer(){
    var search = this.modelCustomer.lastName;
    if(search.length > 0 && this.modelCustomer.firstName.length > 0) search += ' ';
    search += this.modelCustomer.firstName;
    return search;
  }

  searchCustomerChanged(){
    this.searchUpdated.next();
  }

  searchCustomer(){
    var search = this.getSearchCustomer().toLowerCase();
    if(this.modelCustomer != null && search.length > 0){
      this.actionLaunch = true;
      this.searchCustomerDone = false;
      this.userService.post('customers/:token', {search:search, page:1, limit:10}, function(data){
        this.actionLaunch = false;
        this.searchCustomerDone = true;
        this.customers = data.customers;
      }.bind(this), function(error){
        this.actionLaunch = false;
        var text = 'Impossible de récupérer les données des activitées';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }

  addCustomer(){
    if(!this.actionLaunch && this.isOkNewCustomer()){
      swal({
        title: 'Créer fiche client ?',
        text: 'Voulez-vous créer cette fiche client ?',
        icon: 'warning',
        buttons: ['Annuler', 'Créer'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          this.actionLaunch = true;
          this.userService.post('customer/:token', {customer:JSON.stringify(this.modelCustomer)}, function(data){
            this.actionLaunch = false;
            this.setCustomer(data.customer);
            this.clearSearchCustomerDone();
            this.setTabBooking();
          }.bind(this), function(error){
            this.actionLaunch = false;
            var text = 'Impossible de récupérer les données des activitées';
            if(error != null && error.message != null && error.message.length > 0){
              text += ' (' + error.message + ')';
            }
            swal({ title: 'Erreur', text: text, icon: 'error'});
          }.bind(this));
        }
      });
    }
  }

  editCustomer(){
    if(!this.actionLaunch && this.isOkNewCustomer()){
      this.actionLaunch = true;
      this.userService.post('customer/:token', {customer:JSON.stringify(this.modelCustomer)}, function(data){
        this.actionLaunch = false;
        this.setCustomer(data.customer);
        this.clearSearchCustomerDone();
        this.setTabBooking();
      }.bind(this), function(error){
        this.actionLaunch = false;
        var text = 'Impossible de récupérer les données des activitées';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }

  clearSearchCustomerDone(){
    this.searchCustomerDone = false;
  }

  getTypeAccountOfCustomer(){
    if(this.customer != null){
      return this.settings.typesAccounts.find(element => element.id == this.customer.typeAccount);
    }
    return null;
  }

  getTypeAccountName(customer){
    var typeAccountName = '';
    if(customer != null){
      var typeAccount = this.settings.typesAccounts.find(element => {
        if(element.id == customer.typeAccount) return element;
      });
      if(typeAccount){
        typeAccountName = this.global.getTextByLocale(typeAccount.name);
      }
    }
    return typeAccountName;
  }

  clearCustomer(){
    this.customer = null;
    this.modelCustomer = JSON.parse(JSON.stringify(this.skeletonCustomer));
  }

  setCustomer(customer){
    if(customer!=null && (this.customer == null || this.customer.ID == customer.ID)){
      this.customer = JSON.parse(JSON.stringify(customer));
      this.modelCustomer = JSON.parse(JSON.stringify(customer));
    }
    else {
      this.clearCustomer();
    }
  }

  isOkNewCustomer(){
    return this.modelCustomer!=null && ((!this.modelCustomer.createWpAccount && this.modelCustomer.phone != null && this.modelCustomer.phone.length > 0) || (this.modelCustomer.createWpAccount && this.modelCustomer.email != null && this.modelCustomer.email.length > 0)) && !this.actionLaunch;
  }

  sendClose(){
    this.bookingInProcess = false;
    this.close.emit();
  }

  getTagById(idTag){
    var tag:any = {name:'', color:''};
    if(this.settings != null && this.settings.custom_tags != null){
      tag = this.settings.custom_tags.find(element => element.id == idTag);
    }
    return tag;
  }

  getEquipmentById(id){
    return this.equipments.find(element => element.id == id);
  }

  getReductionById(id){
    return this.reductions.find(element => element.id == id);
  }

  getPlaceById(id){
    return this.settings.places.find(element => element.id == id);
  }

  getLastService(oldIdService){
    var service = this.services.find((element) => {
      if(!element.oldService && element.id == oldIdService) return element;
      if(!element.oldService && (element.linkOldServices.indexOf(',') == -1) && element.linkOldServices == oldIdService) return element;
      if(!element.oldService && (element.linkOldServices.indexOf(',') > -1) && (element.linkOldServices.indexOf(',' + oldIdService) > -1)) return element;
      if(!element.oldService && (element.linkOldServices.indexOf(',') > -1) && (element.linkOldServices.indexOf(oldIdService + ',') > -1)) return element;
    });
    return service;
  }

  isDisplayAllPricesOfActivity(){
    return this.currentServiceParameters.numberPrices != null && this.currentServiceParameters.numberPrices.length !=
      this.currentServiceParameters.service.servicePrices.length;
  }

  displayAllPricesOfServices(){
    this.currentServiceParameters.addNumberPriceForEachServicePrice(null);
    this.synchronizeBasket('numberPrices');
  }

  getParticipants(){
    var participants = [];
    if(this.customer!= null){
      for(var i = 0; i < this.customer.participants.length; i++){
        participants.push(JSON.parse(JSON.stringify(this.customer.participants[i])));
      }
    }
    var anotherParticipants = this.basket.getParticipants();
    if(this.customer != null){
      for(var i = 0; i < anotherParticipants.length; i++){
        var participant = anotherParticipants[i];
        if(participant != null){
          var found = false;
          var j = 0;
          while(!found && j < participants.length){
            var participantAux = participants[j];
            found = participantAux.lastname == participant.lastname && participantAux.firstname == participant.firstname;
            j++;
          }
          if(!found){
            participants.push(participant);
          }
        }
      }
    }
    else return anotherParticipants;
    return participants;
  }

  setParticipantIfNecessary(numberPrice){
    if(numberPrice.participants == null) numberPrice.participants = [];
    if(numberPrice.price.participantsParameter){
      if(numberPrice.participants.length > numberPrice.number){
        for(var i = parseInt(numberPrice.number); i < numberPrice.participants.length; i++){
          this.oldParticipants.unshift(numberPrice.participants.pop());
        }
      }
      for(var i = 0; i < numberPrice.number; i++){
        if(numberPrice.participants[i] == null && this.oldParticipants.length > 0){
          numberPrice.participants.push(JSON.parse(JSON.stringify(this.oldParticipants.shift())));
        }
      }
      this.setDefaultParticipants(numberPrice);
    }
  }

  setDefaultParticipants(numberPrice){
    if(numberPrice.participants == null) numberPrice.participants = [];
    for(var i = 0; i < numberPrice.number; i++){
      if(numberPrice.participants[i] == null){
        var fields = this.getParticipantsParameter(numberPrice.price.participantsParameter).fields;
        numberPrice.participants[i] = {};
        for(var j = 0; j < fields.length; j++){
          var field = fields[j];
          if(field.type == 'text'){
            numberPrice.participants[i][field.varname] = '';
          }
          else if(field.type == 'number'){
            numberPrice.participants[i][field.varname] = 0;
          }
          else if(field.type == 'select' && field.options.length == 1){
            numberPrice.participants[i][field.varname] = field.options[0].id;
          }
        }
      }
      if((numberPrice.participants[i].lastname == null || numberPrice.participants[i].lastname.length == 0) && this.customer != null){
        numberPrice.participants[i].lastname = this.customer.lastName;
      }
    }
  }

  getFieldValues(varname, term){
    var participants = this.getParticipants();
    var array = [];
    for(var i = 0; i < participants.length; i++){
      var participant = participants[i];
      array.push(participant[varname]);
    }
    array = array.filter(v => v != null && v.toLowerCase().indexOf(term.toLowerCase()) > -1 && v.toLowerCase() != term.toLowerCase()).slice(0, 10);
    var result = [];
    array.forEach(function(item) {
      if(result.indexOf(item) < 0) {
        result.push(item);
      }
    });
    return result;
  }

  setCopyCurrentServiceParameters(copyCurrentServiceParameters){
    this.copyCurrentServiceParameters = new ServiceParameters();
    this.copyCurrentServiceParameters.fromServiceParametersJSON(copyCurrentServiceParameters, true);
    swal({ title: 'Copié', text: '', icon: 'success'});
  }

  isNeedAskConfirmForPaste(){
    return this.copyCurrentServiceParameters.numberPrices.length != this.currentServiceParameters.numberPrices.length;
    for(var i = 0; i < this.copyCurrentServiceParameters.length; i++){
      if(this.copyCurrentServiceParameters[i].number != this.currentServiceParameters.numberPrices[i].number){
        return true;
      }
    }
    return false;
  }

  launchPasteParticipants(){
    if(this.isNeedAskConfirmForPaste()){
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Nous avons détecté que le nombre de tarifs ou le nombre de participants est différent de la copie en cours, voulez-vous vraiment coller ?',
        icon: 'warning',
        buttons: ['Annuler', 'Coller'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          this.pasteNumberPricesParticipants();
        }
      });
    }
    else {
      this.pasteNumberPricesParticipants();
    }
  }

  pasteNumberPricesParticipants(){
    var participants = [];
    for(var i = 0; i < this.copyCurrentServiceParameters.numberPrices.length; i++){
      var numberPrice = this.copyCurrentServiceParameters.numberPrices[i];
      participants = participants.concat(numberPrice.participants);
    }
    var indexParticipant = 0;
    for(var i = 0; i < this.currentServiceParameters.numberPrices.length; i++){
      var numberPrice = this.currentServiceParameters.numberPrices[i];
      if(indexParticipant < participants.length){
        var participantsToCopy = participants.slice(indexParticipant, indexParticipant + numberPrice.number);
        for(var j = 0; j < Math.min(numberPrice.participants.length, participantsToCopy.length); j++){
          numberPrice.participants[j] = JSON.parse(JSON.stringify(participantsToCopy[j]));
        }
      }
      indexParticipant += numberPrice.number;
    }
  }

  searchByField(varname){
    var search = (text$: Observable<string>) =>
      text$.pipe(
        debounceTime(200),
        distinctUntilChanged(),
        map(term => this.getFieldValues(varname, term))
      )
    return search;
  }

  statesDialog(content){
    this.modalService.open(content, { size: 'sm', backdrop:'static'  }).result.then((result) => {
      if(result!=null){
        this.currentServiceParameters.state = result;
      }
    }, (reason) => {
    });
  }

  isGoodNewCustomTimeslot(){
    return (this.customTimeslot.startTime.hour * 3600 + this.customTimeslot.startTime.minute * 60) <
      (this.customTimeslot.endTime.hour * 3600 + this.customTimeslot.endTime.minute * 60);
  }

  customTimeslotDialog(content){
    this.customTimeslot.startTime.hour = this.currentServiceParameters.dateStart.getHours();
    this.customTimeslot.startTime.minute = this.currentServiceParameters.dateStart.getMinutes()
    this.customTimeslot.endTime.hour = this.currentServiceParameters.dateEnd.getHours();
    this.customTimeslot.endTime.minute = this.currentServiceParameters.dateEnd.getMinutes();
    this.customTimeslot.noEnd = this.currentServiceParameters.noEnd;
    this.customTimeslot.idParameter = this.currentServiceParameters.idParameter * 1;

    this.modalService.open(content, { backdrop:'static' }).result.then((result) => {
      if(result!=null){
        if(result != 'planning'){
          this.currentServiceParameters.dateStart.setHours(this.customTimeslot.startTime.hour);
          this.currentServiceParameters.dateStart.setMinutes(this.customTimeslot.startTime.minute);
          this.currentServiceParameters.dateStart = this.global.parseDate(this.currentServiceParameters.dateStart);
          this.currentServiceParameters.dateEnd.setHours(this.customTimeslot.endTime.hour);
          this.currentServiceParameters.dateEnd.setMinutes(this.customTimeslot.endTime.minute);
          this.currentServiceParameters.dateEnd = this.global.parseDate(this.currentServiceParameters.dateEnd);
          this.currentServiceParameters.noEnd = this.customTimeslot.noEnd;
          this.currentServiceParameters.idParameter = this.customTimeslot.idParameter;
          this.bookingLoadedIsModified = true;
          this.synchronizeBasket('timeslots');
        }
        else {
          this.modifyTimeslotOnPlanning();
        }
      }
    }, (reason) => {

    });
  }

  setCurrentServiceParameters(serviceParameters){
    this.currentServiceParameters = serviceParameters;
  }

  updateServiceInCurrentServiceParameters(){
    if(this.currentServiceParameters.service.oldService && this.currentServiceParameters.idAppointment > -1){
      var service = this.getLastService(this.currentServiceParameters.service.id);
      if(service != null){
        this.currentServiceParameters.service = service;
        var timeslot = this.oldTimeslots.find(element => element.idAppointment == this.currentServiceParameters.idAppointment);
        this.oldParticipants = this.currentServiceParameters.getParticipants();
        this.currentServiceParameters.addNumberPriceForEachServicePrice(timeslot);
        this.synchronizeBasket('numberPrices');
      }
    }
  }

  haveGlobalReductions(){
    return this.customReductions.length > 0 ||
      (this.mapIdClientObjectReduction['id0'] != null && this.mapIdClientObjectReduction['id0'].length > 0);
  }

  addNewCustomReductions(){
    this.bookingLoadedIsModified = true;
    this.currentCustomReduction.amount = (this.currentCustomReduction.amount?this.currentCustomReduction.amount:0) * 1;
    this.currentCustomReduction.vatValue = (this.currentCustomReduction.vatValue?this.currentCustomReduction.vatValue:0) * 1;
    this.customReductions.push(JSON.parse(JSON.stringify(this.currentCustomReduction)));
    this.currentCustomReduction = {id:-1, description: '', amount: 0, vatValue:0};
  }

  removeCustomReduction(index){
    swal({
      title: 'Êtes-vous sûr ?',
      text: 'Êtes-vous sûr de retirer cette réduction personnalisée ?',
      icon: 'warning',
      buttons: ['Annuler', 'Supprimer'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        this.customReductions.splice(index, 1);
        this.bookingLoadedIsModified = true;
      }
    });
  }

  isOkCustomerReduction(){
    return this.currentCustomReduction.description.length > 0;
  }

  customReductionsDialog(content, index = -1){
    if(index == -1){
      this.currentCustomReduction = {id:-1, description: '', amount: 0, vatValue:0};
    }
    else {
      var currentCustom = this.customReductions[index];
      this.currentCustomReduction = {id:index, description: currentCustom.description, amount: currentCustom.amount, vatValue: currentCustom.vatValue};
    }
    this.modalService.open(content).result.then((result) => {
      if(result!=null){
        if(this.currentCustomReduction.id == -1){
          this.addNewCustomReductions();
        }
        else {
          this.customReductions[index] = {
            description: this.currentCustomReduction.description, amount: this.currentCustomReduction.amount, vatValue: this.currentCustomReduction.vatValue
          }
        }
      }
    }, (reason) => {

    });
  }

  getParticipantsParameter(idParameter){
    if(this.settings!=null && this.settings.form_participants_parameters != null){
      return this.settings.form_participants_parameters.find(element => element.id == idParameter)
    }
    return null;
  }

  participantsDialog(content){
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result!=null){
        this.synchronizeBasket('numberPrices');
      }
    }, (reason) => {

    });
  }

  getParticipantFieldName(participant, field){
    if(field.type == 'select'){
      for(var i = 0; i < field.options.length; i++){
        var option = field.options[i];
        if(option.id == participant[field.varname]){
          return this.global.getTextByLocale(option.name);
        }
      }
    }
    return participant[field.varname];
  }

  participantDialog(content, fields, numberPrice, index){
    this.selectorParticipants = {fields:fields, participants:this.getParticipants(), numberPrice:numberPrice, index:index};
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result!=null){
        numberPrice.participants[index] = result;
      }
    }, (reason) => {

    });
  }

  minMaxValue(value, min, max){
    if(max < min) return 0;
    if(value == undefined) return min;
    if(min != null) value = Math.max(min, value);
    if(max != null) value = Math.min(max, value);
    return Math.floor(value);
  }

  getMinNumberPrice(serviceParameters, numberPrice){
    var number = this.basket.getNumberTimeslotWithSamePrice(serviceParameters, numberPrice);
    var min = 1;
    if(numberPrice.price != null && numberPrice.price.activateMinQuantity){
      min =  numberPrice.price.minQuantity - number;
      min = Math.max(min, 1);
    }
    return min;
  }

  isBookingInProcess(){
    return this.bookingInProcess;
  }

  addNewSelection(){
    this.bookingInProcess = true;
    this.currentServiceParameters = null;
    this.close.emit();
  }

  modifyTimeslotOnPlanning(){
    this.bookingInProcess = true;
    this.close.emit();
  }

  formatGroupDates(date){
    var tokens = date.split('-');
    var dateFormated = new Date(date);
    if(tokens.length == 3){
      if(tokens[2].length > 2){
        tokens[2] = tokens[2].split('T')[0];
      }
      dateFormated = new Date(tokens[0], tokens[1] - 1, tokens[2]);
    }
    return dateFormated;
  }

  getGroupDates(service, idServiceAvailability, startDate){
    startDate = this.global.clearTime(startDate);
    var serviceAvailability = service.serviceAvailabilities.find(element => element.id == idServiceAvailability);
    if(serviceAvailability != null){
      for(var i = 0; i < serviceAvailability.groupDates.length; i++){
        var dates = serviceAvailability.groupDates[i];
        var currentStartDate = this.formatGroupDates(dates.startDate);
        var currentEndDate = this.formatGroupDates(dates.endDate);
        if(currentStartDate <= startDate && startDate <= currentEndDate){
          return {
            startDate:currentStartDate,
            endDate:currentEndDate
          };
        }
      }
    }

    return null;
  }

  getManyDates(service, idServiceAvailability, startDate){
    startDate = this.global.clearTime(startDate);
    var serviceAvailability = service.serviceAvailabilities.find(element => element.id == idServiceAvailability);
    if(serviceAvailability != null){
      for(var i = 0; i < serviceAvailability.manyDates.length; i++){
        var manyDates = serviceAvailability.manyDates[i].split(',');
        for(var j = 0; j < manyDates.length; j++){
					var date = manyDates[j];
          var localDate = this.formatGroupDates(date);
          if(localDate.getTime() == startDate.getTime()){
            return manyDates;
          }
				}
      }
    }
    return null;
  }

  isTypeManyDays(service){ return service.typeAvailabilities == 1 || service.typeAvailabilities == 2; }
  isSynchronized(idServiceParametersLink){
    return this.basket.isSynchronized(idServiceParametersLink);
  }
  desynchronisetServiceParameters(idServiceParametersLink){
    this.basket.desynchronisetServiceParameters(idServiceParametersLink);
  }

  addInBasket(selectedTimeslot){
    var serviceParameters = new ServiceParameters();
    serviceParameters.id = this.basket.getNextId();
    serviceParameters.dateStart = this.global.parseDate(selectedTimeslot.startDate);
    serviceParameters.dateEnd = this.global.parseDate(selectedTimeslot.endDate);
    this.actionLaunch = true;
    this.getServiceById(selectedTimeslot.idService, (service) => {
      this.getTimeslot(selectedTimeslot.startDate, selectedTimeslot.endDate, selectedTimeslot.idService, (timeslot) => {
        if(timeslot == null){
          timeslot = {
            id:42,
            capacity: 80,
            typeCapacity: 1,
            noStaff: false,
            exclusive: false,
            noEnd: '0',
            maxAppointments: 1,
            capacityMembers: 80,
            usedCapacity:0,
            numberOfAppointmentsSaved:0,
            members:[],
            membersUsed:[],
            isCustom:true,
            idParameter:this.settings.states_parameters.length>0?this.settings.states_parameters[0].id:-1,
            equipments:[]
          }
        }
        serviceParameters.service = service;
        if(service.places.length > 0){
          serviceParameters.place = service.places[0];
        }
        if(this.isTypeManyDays(serviceParameters.service) && !timeslot.isCustom){
          if(serviceParameters.service.typeAvailabilities == 1){
            var dates:any = this.getGroupDates(serviceParameters.service, selectedTimeslot.idServiceAvailability, this.global.parseDate(serviceParameters.dateStart));
            var id = this.basket.getNextId();
            var externalId = this.basket.getServiceParametersLinkId();
            while(dates.startDate <= dates.endDate){
              var localServiceParameters = new ServiceParameters();
              localServiceParameters.fromServiceParametersJSON(serviceParameters, false);
              localServiceParameters.id = this.basket.getNextId();
              var startDate = this.global.parseDate(dates.startDate);
              var endDate = this.global.parseDate(dates.endDate);
              endDate.setDate(startDate.getDate());
              endDate.setMonth(startDate.getMonth());
              endDate.setFullYear(startDate.getFullYear());
              localServiceParameters.setDate(startDate, endDate);
              localServiceParameters.idAppointment = serviceParameters.idAppointment;
              this.pushInBasket(externalId, localServiceParameters, timeslot);
              dates.startDate.setDate(dates.startDate.getDate() + 1);
              if(this.currentServiceParameters == null){
                this.currentServiceParameters = localServiceParameters;
              }
            }
          }
          else if(serviceParameters.service.typeAvailabilities == 2) {
            var dates:any = this.getManyDates(serviceParameters.service, selectedTimeslot.idServiceAvailability, serviceParameters.dateStart);
            var id = this.basket.getNextId();
            var externalId = this.basket.getServiceParametersLinkId();
            for(var i = 0; i < dates.length; i++){
              var localDate = this.formatGroupDates(dates[i]);
              var localServiceParameters = new ServiceParameters();
              localServiceParameters.fromServiceParametersJSON(serviceParameters, false);
              localServiceParameters.id = this.basket.getNextId();
              localServiceParameters.setDate(localDate, localDate);
              localServiceParameters.idAppointment = serviceParameters.idAppointment;
              this.pushInBasket(externalId, localServiceParameters, timeslot);
              if(this.currentServiceParameters == null){
                this.currentServiceParameters = localServiceParameters;
              }
            }
          }
        }
        else {
          this.pushInBasket(this.basket.getServiceParametersLinkId(), serviceParameters, timeslot);
          this.currentServiceParameters = serviceParameters;
        }
        this.actionLaunch = false;
      }, () => {
        this.actionLaunch = false;
      });
    });
  }

  setTimeslotWithPlanning(selectedTimeslot){
    this.actionLaunch = true;
    this.getTimeslot(selectedTimeslot.startDate, selectedTimeslot.endDate, selectedTimeslot.idService, (timeslot) => {
      this.actionLaunch = false;
      this.currentServiceParameters.dateStart = this.global.parseDate(selectedTimeslot.startDate);
      this.currentServiceParameters.dateEnd = this.global.parseDate(selectedTimeslot.endDate);
      this.currentServiceParameters = this.setTimeslot(this.currentServiceParameters, timeslot);
      this.synchronizeBasket('timeslots');
    }, () => {
      this.actionLaunch = false;
    });
  }


  removeServiceParameters(serviceParameters){
    var buttons = {
        cancel: "Annuler",
        delete: {
          text: "Supprimer",
          className:"btn-danger",
          value: 1
        },
        deleteAll: {
          text: "Supprimer tous",
          className:"btn-danger",
          value: 2,
          visible:false
        }
      };
    var text = 'Êtes-vous sûr de retirer cette activité ?';
    if(this.isTypeManyDays(serviceParameters.service)){
      buttons.deleteAll.visible = true;
      text += ' Voulez vous supprimer toutes les activités des jours associées ?'
    }
    swal({
      title: 'Êtes-vous sûr ?',
      text: text,
      icon: 'warning',
      buttons: buttons,
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        if(serviceParameters.id == this.currentServiceParameters.id) this.currentServiceParameters = null;
        if(willDelete == 1) this.basket.removeServiceParametersBy(serviceParameters, 'id');
        if(willDelete == 2) this.basket.removeServiceParametersBy(serviceParameters, 'idServiceParametersLink');
      }
    });
  }

  setTimeslot(serviceParameters, timeslot){
    serviceParameters.addNumberPriceForEachServicePrice(timeslot);
    serviceParameters.noEnd = timeslot.noEnd != '0';
    serviceParameters.setCapacityWithTimeslot(timeslot);
    serviceParameters.customTimeslot = timeslot.isCustom;
    serviceParameters.idsServicePrices = timeslot.idsServicePrices;
    serviceParameters.idParameter = timeslot.idParameter;
    serviceParameters.maxCapacity = 9999;
    serviceParameters.membersUsed = timeslot.membersUsed;
    serviceParameters.mergeMembersUsedWithMembers(this.members);
    for(var i = 0; i < serviceParameters.staffs.length; i++){
      var staff = serviceParameters.staffs[i];
      for(var j = 0; j < serviceParameters.membersUsedWithMembers.length; j++){
        var memberUsed = serviceParameters.membersUsedWithMembers[j];
        if(staff.id == memberUsed.id){
          serviceParameters.staffs[i] = memberUsed;
        }
      }
    }
    serviceParameters.equipments = timeslot.equipments;
    serviceParameters.equipmentsActivated = timeslot.equipmentsActivated;
    if(serviceParameters.idParameter > -1 && this.settings != null && this.settings.states_parameters != null){
      var parameter = this.settings.states_parameters.find(element => element.id == serviceParameters.idParameter);
      serviceParameters.state = parameter.stateBackend;
    }
    return serviceParameters;
  }

  pushInBasket(id, serviceParameters, timeslot){
    serviceParameters = this.setTimeslot(serviceParameters, timeslot);
    serviceParameters.idServiceParametersLink = id;
    this.basket.addServiceParameters(serviceParameters);
    return serviceParameters;
  }

  synchronizeBasket(type){
    if(this.synchronizedDays){
      var allServiceParameters = this.basket.getAllServicesParametersByIdLink(this.currentServiceParameters.idServiceParametersLink);
      for(var i = 0; i < allServiceParameters.length; i++){
        var localServiceParameters = allServiceParameters[i];
        var newServiceParameters = new ServiceParameters();
        newServiceParameters.fromServiceParametersJSON(localServiceParameters, false);
        if(type == 'timeslots'){
          newServiceParameters.setTimes(this.currentServiceParameters.dateStart, this.currentServiceParameters.dateEnd);
        }
        else if(type == 'numberPrices'){
          newServiceParameters.numberPrices = JSON.parse(JSON.stringify(this.currentServiceParameters.numberPrices));
        }
        else if(type == 'members'){
          newServiceParameters.staffs = JSON.parse(JSON.stringify(this.currentServiceParameters.staffs));
        }
        else if(type == 'tags'){
          newServiceParameters.tags = JSON.parse(JSON.stringify(this.currentServiceParameters.tags));
        }
        if(this.basket.replaceServiceParameters(newServiceParameters, false)){
          this.bookingLoadedIsModified = true;
        }
      }
    }
    else {
      if(this.basket.replaceServiceParameters(this.currentServiceParameters, false)){
        this.bookingLoadedIsModified = true;
      }
    }
  }

  customerIsOk(){
    return this.customer!=null && (this.customer.ID != -1 ||
      (this.customer.lastName.length > 0 &&
        (this.customer.email!=null && this.customer.email.length > 0) &&
       ((this.customer.email!=null && this.customer.email.length > 0) ||
       this.customer.phone.length > 0)));
  }

  setAllStates(state){
    this.bookingLoadedIsModified = true;
    this.basket.setAllStates(state);
  }

  serviceParametersIsOk(serviceParameters, index = -1){
    return this.basket.serviceParametersIsOk(serviceParameters, index, true);
  }

  formIsOk(){
    return this.basket.basketIsOk(true) && this.customerIsOk();
  }

  getTotalPriceServicesParameters(serviceParameters){
    return Math.round(this.basket.getSubTotalPrice([serviceParameters], this.reductions, this.mapIdClientObjectReduction, false, this.getTypeAccountOfCustomer()) * 100) / 100;
  }

  getSubTotalPrice(servicesParameters){
    return Math.round(this.basket.getSubTotalPrice(servicesParameters, this.reductions, this.mapIdClientObjectReduction, false, this.getTypeAccountOfCustomer()) * 100) / 100;
  }

  round(value){ return Math.round(value * 100)/100; }

  getTotalPriceWithoutCustomReductions(){
    var amount = this.basket.getTotalPrice(this.reductions, this.mapIdClientObjectReduction, false, this.getTypeAccountOfCustomer());
    return Math.round(amount * 100) / 100;
  }

  getTotalPrice(){
    var amount = this.basket.getTotalPrice(this.reductions, this.mapIdClientObjectReduction, false, this.getTypeAccountOfCustomer());
    for(var i = 0; i < this.customReductions.length; i++){
      var customReductionsAmount = this.customReductions[i].amount;
      amount += customReductionsAmount;
    }
    return Math.round(amount * 100) / 100;
  }

  deleteCoupon(code){
    var index = this.vouchersAdded.indexOf(code);
    if(index != -1){
      this.vouchersAdded.splice(index, 1);
      this.bookingLoadedIsModified = true;
    }
    this.getReductions('');
  }

  getReductions(promoCode){
    if(this.basket.getAllServicesParameters().length > 0){
      var numberReductionsBefore = this.reductions.length;
      var conditionPromoCode = (promoCode!=null && promoCode.length > 0 && this.vouchersAdded.indexOf(promoCode) == -1);
      if((this.currentBooking == null || this.bookingLoadedIsModified || conditionPromoCode) && !this.reductionsLaunched){
        this.reductionsLaunched = true;
        if(conditionPromoCode){
          this.vouchersAdded.push(promoCode);
        }
        var servicesParametersFormated = JSON.parse(JSON.stringify(this.basket.getAllServicesParameters()));
        for(var i = 0; i < servicesParametersFormated.length; i++){
          var serviceParameters = servicesParametersFormated[i];
          serviceParameters.dateStart = formatDate(this.global.parseDate(serviceParameters.dateStart), 'dd-MM-yyyy HH:mm:ss', 'fr');
          serviceParameters.dateEnd = formatDate(this.global.parseDate(serviceParameters.dateEnd), 'dd-MM-yyyy HH:mm:ss', 'fr');
          serviceParameters.numberPrices = serviceParameters.numberPrices.filter(element => element.number > 0 );
        }
        var idBooking = -1;
        if(this.currentBooking != null){
          idBooking = this.currentBooking.id;
        }
        var idCustomer = -1;
        if(this.customer != null){
          idCustomer = this.customer.ID;
        }

        this.userService.post('calculateReductions/:token', {
          servicesParameters: JSON.stringify(servicesParametersFormated),
          couponsList: JSON.stringify(this.vouchersAdded),
          idCustomer: idCustomer,
          idBooking: idBooking,
          allowInconsistencies: true,
          frontForm: false
        }, function(data){
          this.reductionsLaunched = false;
          if(conditionPromoCode) {
            this.currentPromoCode = '';
          }
          if(this.currentBooking == null || this.bookingLoadedIsModified || conditionPromoCode){
            if(conditionPromoCode){
              this.bookingLoadedIsModified = true;
              if(JSON.stringify(this.mapIdClientObjectReduction) == JSON.stringify(data.mapIdClientObjectReduction)){
                swal('', 'Coupon non trouvé ou plus valide !', 'error');
              }
            }
            this.reductions = data.reductions;
            this.mapIdClientObjectReduction = data.mapIdClientObjectReduction;
            if(this.relanchGetReductions){
              this.relanchGetReductions = false;
              this.getReductions('');
            }
          }
        }.bind(this), function(error){
          this.reductionsLaunched = false;
          var text = 'Impossible de récupérer les calculées les réductions';
          if(error != null && error.message != null && error.message.length > 0){
            text += ' (' + error.message + ')';
          }
          swal({ title: 'Erreur', text: text, icon: 'error'});
        }.bind(this));
      }
      else {
        this.relanchGetReductions = true;
      }
    }
  }

  validForm(){
    if(!this.actionLaunch && this.formIsOk()) {
      this.actionLaunch = true;
      var servicesParametersFormated = JSON.parse(JSON.stringify(this.basket.getAllServicesParameters()));
      for(var i = 0; i < servicesParametersFormated.length; i++){
        var serviceParameters = servicesParametersFormated[i];
        serviceParameters.dateStart = formatDate(this.global.parseDate(serviceParameters.dateStart), 'dd-MM-yyyy HH:mm:ss', 'fr');
        serviceParameters.dateEnd = formatDate(this.global.parseDate(serviceParameters.dateEnd), 'dd-MM-yyyy HH:mm:ss', 'fr');
        serviceParameters.numberPrices = serviceParameters.numberPrices.filter(element => element.number > 0 );
      }
      //var customReductionsFormated = JSON.parse(JSON.stringify(this.customReductions));
      var idBooking = -1;
      if(this.currentBooking != null){
        idBooking = this.currentBooking.id;
      }
      var typePayment = 'later';
      this.userService.put('bookingEditor/:token', {
        customer:JSON.stringify(this.customer),
        servicesParameters:JSON.stringify(servicesParametersFormated),
        typePayment:typePayment,
        advancePayment:false,
        couponsList:JSON.stringify(this.vouchersAdded),
        currentUrl:'',
        customReductions:JSON.stringify(this.customReductions),
        bookingNote:this.global.htmlSpecialDecode(this.notes.private),
        bookingPublicNote:this.global.htmlSpecialDecode(this.notes.public),
        bookingStaffNote:this.global.htmlSpecialDecode(this.notes.members),
        bookingCustomerNote:this.global.htmlSpecialDecode(this.notes.customer),
        sendEmailToCustomer:false,
        idBooking:idBooking,
        quotation:this.quotation,
        allowInconsistencies:true,
        frontForm:false,
        idForm:''
      }, function(data){
        this.actionLaunch = false;
        if(this.callbackSuccessAdd){
          this.callbackSuccessAdd(data);
        }
        this.sendClose();

      }.bind(this), function(error){
        this.actionLaunch = false;
        var text = 'Impossible de récupérer sauvegarder la réservation';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }


  deleteBooking(){
    if(this.currentBooking != null){
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Êtes-vous sûr de supprimer la réservation ?',
        icon: 'warning',
        buttons: ['Annuler', 'Supprimer'],
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          this.actionLaunch = true;
          this.userService.delete('booking/:token/' + this.currentBooking.id, function(){
            this.actionLaunch = false;
            if(this.callbackSuccessAdd){
              this.callbackSuccessAdd();
            }
            this.sendClose();
          }.bind(this), function(error){
            this.actionLaunch = false;
            var text = 'Impossible de supprimer la réservation';
            if(error != null && error.message != null && error.message.length > 0){
              text += ' (' + error.message + ')';
            }
            swal({ title: 'Erreur', text: text, icon: 'error'});
          }.bind(this));
        }
      });
    }
  }
}
