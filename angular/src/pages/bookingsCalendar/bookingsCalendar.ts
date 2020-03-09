import { OnInit, Component, ViewChild  } from '@angular/core';
import { NgbTabChangeEvent } from '@ng-bootstrap/ng-bootstrap';
import { DatePipe, formatDate } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { NgbModal, NgbDate, NgbCalendar } from '@ng-bootstrap/ng-bootstrap';
import { CalendarView, CalendarEvent, CalendarEventTimesChangedEvent, CalendarMonthViewDay, DAYS_OF_WEEK } from 'angular-calendar';
import { Subject } from 'rxjs';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';
import { BookingDialogComponent } from '../../components/bookingDialog/bookingDialog';
import { ConstraintComponent } from '../../components/constraint/constraint';
import { InfoCalendarComponent } from '../../components/infoCalendar/infoCalendar';


declare var swal: any;

@Component({
  selector: 'bookingsCalendar',
  templateUrl: 'bookingsCalendar.html',
  styleUrls:['./bookingsCalendar.css']
})

export class BookingsCalendarComponent extends ComponentCanDeactivate implements OnInit {

  public date:Date = new Date();
  public ngbDate:NgbDate = null;
  public settings = null;
  public states = [];
  public searchForm = {edit:false, staticMonths:[], staticYears:[]};

  public alerts = [];
  public appointments = [];
  public infoCalendars = [];
  public serviceConstraintsCalendars = [];
  public memberConstraintsCalendars = [];
  public events: CalendarEvent[] = [];
  public eventsMonth = [];
  public view:CalendarView = CalendarView.Day;
  public CalendarView = CalendarView;
  public weekStartsOn: number = DAYS_OF_WEEK.MONDAY;

  public launchActions = [];

  public refresh: Subject<any> = new Subject();

  private skeletonInfoCalendar:any = null;
  public formInfoCalendar:any = {manyDays:false, date:new NgbDate(0,0,0), dateEnd:new NgbDate(0,0,0), time:new Date(), timeEnd:new Date(), infoCalendar:null};

  public services:any[] = [];
  public members:any[] = [];
  private skeletonServiceConstraint:any = null;
  private skeletonMemberConstraint:any = null;
  public formConstraintCalendar:any = {manyDays:false, date:new NgbDate(0,0,0), dateEnd:new NgbDate(0,0,0), time:new Date(), timeEnd:new Date(), serviceConstraint:null, memberConstraint:null, isServiceConstraint:true};

  @ViewChild('contentInfoCalendar', { static: true }) contentInfoCalendar: any;

  constructor(public global:GlobalService, private userService:UserService, private navService:NavService, private modalService: NgbModal) {
    super('Agenda');
    this.date = new Date(this.global.currentDate);
    this.ngbDate = this.toNgbDate(this.date);
  }

  ngOnInit(): void {
    var idLaunchAction = this.addLaunchAction();
    this.userService.get('bookingsSettings/:token', function(data){
      this.settings = data.settings;
      this.skeletonInfoCalendar = data.skeletonInfoCalendar;
      this.skeletonServiceConstraint = data.skeletonServiceConstraint;
      this.skeletonMemberConstraint = data.skeletonMemberConstraint;
      this.services = data.services;
      this.members = data.members;

      for(var i = 0; i < data.statesList.length; i++){
        var state = data.statesList[i];
        if(state.isFilter){
          this.states.push({
            id:state.id,
            name:state.filterName,
            selected:state.selected
          });
        }
      }
      this.generateSelectbox();
      this.stopLaunchAction(idLaunchAction);
      this.reloadDataFunctionOfView();
    }.bind(this), function(error){
      this.stopLaunchAction(idLaunchAction);
      var text = 'Impossible de récupérer les données des réservations';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }
  canDeactivate(){ return true; }
  goPlanning(){ this.navService.changeRoute('planningBookings'); }
  goEditBooking(booking){ this.navService.changeRoute('planningBookings', {booking:booking.id}); }
  isRESAManager(){
    return this.userService.getCurrentUser().role == 'administrator' || this.userService.getCurrentUser().role == 'RESA_Manager';
  }

  generateSelectbox(){
    var actualDate = new Date();
    this.searchForm.staticMonths = [];
    this.searchForm.staticYears = [];
    actualDate.setFullYear(actualDate.getFullYear() + 2);
    this.searchForm.staticYears.push(formatDate(actualDate, 'yyyy', 'fr'));
    for(var i = 0; i < 12; i++){
      actualDate.setMonth(i);
      this.searchForm.staticMonths.push(formatDate(actualDate, 'MMMM', 'fr'));
      if(i < 4){
        actualDate.setFullYear(actualDate.getFullYear() - 1);
        this.searchForm.staticYears.push(formatDate(actualDate, 'yyyy', 'fr'));
      }
    }
  }

  toDate(ngbDate:NgbDate):Date{
    return new Date(ngbDate.year, ngbDate.month - 1, ngbDate.day, 0, 0, 0, 0);
  }

  toNgbDate(date:Date):NgbDate{
    return new NgbDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
  }

  addLaunchAction(){
    var id = Math.random()+'';
    this.launchActions.push(id);
    return id;
  }

  haveLaunchAction(){ return this.launchActions.length > 0; }
  stopLaunchAction(id){
    var index = this.launchActions.findIndex(element => element == id);
    if(index > -1) { this.launchActions.splice(index, 1); }
  }

  loadData(page){
    if(page == 0){
      this.appointments = [];
    }
    var nbByPage = 40;
    var idLaunchAction = this.addLaunchAction();
    var startDate = new Date(this.date);
    startDate.setHours(0);
    startDate.setMinutes(0);
    startDate.setSeconds(0);
    startDate.setMilliseconds(0);
    var endDate = new Date(startDate);
    endDate.setDate(endDate.getDate() + 1);

    this.global.setCurrentDate(startDate);

    var startDateString = formatDate(startDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    var endDateString = formatDate(endDate, 'yyyy-MM-dd HH:mm:ss', 'fr');

    var stateList = [];
    this.states.map(state => {
      if(state.selected){
        stateList.push(state.id);
      }
    });

    this.userService.post('appointmentsEvent/:token', {startDate:startDateString, endDate:endDateString, nbByPage:nbByPage, page:page, stateList:JSON.stringify(stateList) }, (data) => {
      this.appointments = this.appointments.concat(data);
      for(var i = 0; i < data.length; i++){
        var appointment = data[i];
        this.events = [...this.events,this.appointmentToCalendarEvent(appointment)];
      }
      if(data.length == nbByPage){
        this.loadData(page + 1);
      }
      this.stopLaunchAction(idLaunchAction);
    }, (error) => {
      this.stopLaunchAction(idLaunchAction);
      var text = 'Impossible de récupérer les données des réservations';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  loadAlerts(){
    this.alerts = [];
    var idLaunchAction = this.addLaunchAction();
    var startDate = new Date(this.date);
    startDate.setHours(0);
    startDate.setMinutes(0);
    startDate.setSeconds(0);
    startDate.setMilliseconds(0);
    var endDate = new Date(startDate);
    endDate.setDate(endDate.getDate() + 1);

    var startDateString = formatDate(startDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    var endDateString = formatDate(endDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    this.userService.post('getAlertsCalendar/:token', {startDate:startDateString, endDate:endDateString }, (data) => {
      this.alerts = this.alerts.concat(data);
      this.stopLaunchAction(idLaunchAction);
      this.loadData(0);
    }, (error) => {
      this.stopLaunchAction(idLaunchAction);
      var text = 'Impossible de récupérer les données des réservations';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }


  loadInfoCalendar(){
    var idLaunchAction = this.addLaunchAction();
    this.infoCalendars = [];
    var startDate = new Date(this.date);
    startDate.setHours(0);
    startDate.setMinutes(0);
    startDate.setSeconds(0);
    startDate.setMilliseconds(0);
    var endDate = new Date(startDate);
    endDate.setDate(endDate.getDate() + 1);

    var startDateString = formatDate(startDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    var endDateString = formatDate(endDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    this.userService.post('getInformationsCalendar/:token', {startDate:startDateString, endDate:endDateString}, (data) => {
      this.infoCalendars = this.infoCalendars.concat(data);
      for(var i = 0; i < data.length; i++){
        var infoCalendar = data[i];
        this.events = [...this.events, this.infoCalendarToCalendarEvent(infoCalendar)];
      }
      this.stopLaunchAction(idLaunchAction);
    }, (error) => {
      this.stopLaunchAction(idLaunchAction);
      var text = 'Impossible de récupérer les données des réservations';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  loadConstraintsCalendar(){
    var idLaunchAction = this.addLaunchAction();
    this.serviceConstraintsCalendars = this.memberConstraintsCalendars = [];
    var startDate = new Date(this.date);
    startDate.setHours(0);
    startDate.setMinutes(0);
    startDate.setSeconds(0);
    startDate.setMilliseconds(0);
    var endDate = new Date(startDate);
    endDate.setDate(endDate.getDate() + 1);

    var startDateString = formatDate(startDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    var endDateString = formatDate(endDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    this.userService.post('getConstraintsCalendar/:token', {startDate:startDateString, endDate:endDateString}, (data) => {
      this.serviceConstraintsCalendars = this.serviceConstraintsCalendars.concat(data.serviceConstraints);
      for(var i = 0; i < data.serviceConstraints.length; i++){
        var constraint = data.serviceConstraints[i];
        this.events = [...this.events, this.constraintCalendarToCalendarEvent(constraint, true)];
      }
      this.memberConstraintsCalendars = this.memberConstraintsCalendars.concat(data.memberConstraints);
      for(var i = 0; i < data.memberConstraints.length; i++){
        var constraint = data.memberConstraints[i];
        this.events = [...this.events, this.constraintCalendarToCalendarEvent(constraint, false)];
      }
      this.stopLaunchAction(idLaunchAction);
    }, (error) => {
      this.stopLaunchAction(idLaunchAction);
      var text = 'Impossible de récupérer les données des réservations';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }


  loadDataMonth(page){
    if(page == 0){
      this.eventsMonth = [];
    }
    var nbByPage = 20;
    var idLaunchAction = this.addLaunchAction();
    var startDate = new Date(this.date.getFullYear(), this.date.getMonth(), 1);
    startDate.setHours(0);
    startDate.setMinutes(0);
    startDate.setSeconds(0);
    startDate.setMilliseconds(0);
    var endDate = new Date(this.date.getFullYear(), this.date.getMonth(), 1);
    endDate.setMonth(endDate.getMonth() + 1);

    var startDateString = formatDate(startDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    var endDateString = formatDate(endDate, 'yyyy-MM-dd HH:mm:ss', 'fr');

    var stateList = [];
    this.states.map(state => {
      if(state.selected){
        stateList.push(state.id);
      }
    });

    this.userService.post('appointmentsEventMonths/:token', {startDate:startDateString, endDate:endDateString, stateList:JSON.stringify(stateList)}, (data) => {
      for(var i = 0; i < data.events.length; i++){
        var events = data.events[i];
        var numberAlerts = 0;
        if(data.alerts[events.date]){
          numberAlerts = data.alerts[events.date].numbers;
        }
        this.eventsMonth = [...this.eventsMonth, {
          start: this.global.parseDate(events.date),
          end: this.global.parseDate(events.date),
          title: events.name + ' x' + events.numbers,
          color: {
            primary: events.color,
            secondary: events.color
          },
          allDay: false,
          resizable: {
            beforeStart: false,
            afterEnd: false
          },
          draggable: false,
          meta: {
            incrementsBadgeTotal: false,
            type:events.idService,
            numbers:events.numbers,
            numberAlerts:numberAlerts
          }
        }];
      }

      this.stopLaunchAction(idLaunchAction);
    }, (error) => {
      this.stopLaunchAction(idLaunchAction);
      var text = 'Impossible de récupérer les données des réservations';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  loadInfoCalendarMonth(){
    var idLaunchAction = this.addLaunchAction();
    var startDate = new Date(this.date.getFullYear(), this.date.getMonth(), 1);
    startDate.setHours(0);
    startDate.setMinutes(0);
    startDate.setSeconds(0);
    startDate.setMilliseconds(0);
    var endDate = new Date(this.date.getFullYear(), this.date.getMonth(), 1);
    endDate.setMonth(endDate.getMonth() + 1);

    var startDateString = formatDate(startDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    var endDateString = formatDate(endDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    this.userService.post('getInformationsCalendarMonth/:token', {startDate:startDateString, endDate:endDateString}, (data) => {
      for(var key in data){
        var infos = data[key];
        this.eventsMonth = [...this.eventsMonth, {
          start: this.global.parseDate(infos.date),
          end: this.global.parseDate(infos.date),
          title: this.global.htmlSpecialDecode(infos.note, true),
          color: {
            primary: this.settings.calendar.info_calendar_color,
            secondary: this.settings.calendar.info_calendar_color
          },
          allDay: false,
          resizable: {
            beforeStart: false,
            afterEnd: false
          },
          draggable: false,
          meta: {
            incrementsBadgeTotal: false,
            type:'infoCalendar',
            numbers:infos.numbers
          }
        }];
      }
      this.stopLaunchAction(idLaunchAction);
    }, (error) => {
      this.stopLaunchAction(idLaunchAction);
      var text = 'Impossible de récupérer les données des informations';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  loadConstraintsCalendarMonth(){
    var idLaunchAction = this.addLaunchAction();
    var startDate = new Date(this.date.getFullYear(), this.date.getMonth(), 1);
    startDate.setHours(0);
    startDate.setMinutes(0);
    startDate.setSeconds(0);
    startDate.setMilliseconds(0);
    var endDate = new Date(this.date.getFullYear(), this.date.getMonth(), 1);
    endDate.setMonth(endDate.getMonth() + 1);

    var startDateString = formatDate(startDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    var endDateString = formatDate(endDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    this.userService.post('getConstraintsCalendarMonth/:token', {startDate:startDateString, endDate:endDateString}, (data) => {
      for(var key in data){
        var constraint = data[key];
        this.eventsMonth = [...this.eventsMonth, {
          start: this.global.parseDate(constraint.date),
          end: this.global.parseDate(constraint.date),
          title: 'Contraintes x' + constraint.numbers,
          color: {
            primary: this.settings.calendar.service_constraint_color,
            secondary: this.settings.calendar.service_constraint_color
          },
          allDay: false,
          resizable: {
            beforeStart: false,
            afterEnd: false
          },
          draggable: false,
          meta: {
            incrementsBadgeTotal: false,
            type:'constraint',
            numbers:constraint.numbers
          }
        }];
      }
      this.stopLaunchAction(idLaunchAction);
    }, (error) => {
      this.stopLaunchAction(idLaunchAction);
      var text = 'Impossible de récupérer les données des contraintes';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  updateDate(date){
    this.date = new Date(date);
    this.ngbDate = this.toNgbDate(this.date);
    this.reloadDataFunctionOfView();
  }

  reloadDataFunctionOfViewWithDate(){
    this.date = this.toDate(this.ngbDate);
    this.searchForm.edit = false;
    this.reloadDataFunctionOfView();
  }

  reloadDataFunctionOfView(){
    this.ngbDate = this.toNgbDate(this.date);
    if(this.view == CalendarView.Day) {
      this.events = [];
      this.loadAlerts();
      this.loadInfoCalendar();
      this.loadConstraintsCalendar();
    }
    else {
      this.eventsMonth = [];
      this.loadDataMonth(0);
      this.loadInfoCalendarMonth();
      this.loadConstraintsCalendarMonth();
    }
  }

  setView(view: CalendarView) {
    this.view = view;
    this.reloadDataFunctionOfView();
  }

  handleEvent(action: string, event: CalendarEvent): void {
    if(event.meta != null && event.meta.id != null && event.meta.type == 'appointment'){
      var idLaunchAction = this.addLaunchAction();
      this.userService.get('booking/:token/' + event.meta.id, (data) => {
        this.stopLaunchAction(idLaunchAction);
        this.openBooking(data);
      }, (error) => {
        this.stopLaunchAction(idLaunchAction);
        var text = 'Impossible de récupérer les données de la réservation';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
    else if(event.meta != null && event.meta.id != null && event.meta.type == 'infoCalendar'){
      var infoCalendar = this.infoCalendars.find(element => element.id == event.meta.id);
      if(infoCalendar == null) infoCalendar = this.formInfoCalendar;
      this.openInfoCalendarDialog(infoCalendar);
    }
    else if(event.meta != null && event.meta.id != null && event.meta.type == 'constraint'){
      var constraint = null;
      if(event.meta.isServiceConstraint) constraint = this.serviceConstraintsCalendars.find(element => element.id == event.meta.id);
      else constraint = this.memberConstraintsCalendars.find(element => element.id == event.meta.id);
      if(constraint == null) constraint = this.formConstraintCalendar.serviceConstraint;
      this.openConstraintCalendarDialog(constraint, event.meta.isServiceConstraint);
    }
  }

  eventTimesChanged({
    event,
    newStart,
    newEnd
  }: CalendarEventTimesChangedEvent): void {

  }

  dayClicked({ date, events }: { date: Date; events: CalendarEvent[] }): void {
    this.date = date;
    this.setView(CalendarView.Day);
  }

  beforeMonthViewRender({ body }: { body: CalendarMonthViewDay[] }): void {
    body.forEach(cell => {
      const groups: any = {};
      cell.events.forEach((event: CalendarEvent<{ type: string }>) => {
        groups[event.meta.type] = groups[event.meta.type] || {color:event.color,events:[]};
        groups[event.meta.type].events.push(event);
      });
      cell['eventGroups'] = Object.entries(groups);
    });
  }

  appointmentToCalendarEvent(appointment):CalendarEvent{
    //need calendarView bug else when change calendar display
    var serviceName = appointment.service;
    if(appointment.idPlace != null &&  this.settings != null &&  this.settings.places != null){
      var place = this.settings.places.find(element => element.id == appointment.idPlace);
      if(place != null){
        serviceName = '['+ this.global.getTextByLocale(place.name)+'] ' + serviceName;
      }
    }
    var startDate = this.global.parseDate(appointment.startDate);
    var endDate = this.global.parseDate(appointment.endDate);
    var diffHours = endDate.getHours() - startDate.getHours();
    diffHours = Math.min(4, diffHours);
    diffHours = Math.max(1, diffHours);

    var selectedClass = '';
    var presentation = '<div id="appointment'+appointment.id+'" class="creneau '+selectedClass+' t'+diffHours+'"> <div class="creneau_content" style="{background-color:'+appointment.color+'}">';
    if(appointment.state == 'ok') presentation += '<div class="creneau_state valid" title="Confirmé"></div>';
    if(appointment.state == 'waiting') presentation += '<div class="creneau_state pending" title="Non confirmé"></div>';
    if(appointment.state == 'cancelled') presentation += '<div class="creneau_state cancelled" title="Annulé"></div>';
    if(appointment.state == 'abandonned') presentation += '<div class="creneau_state cancelled" title="Abandonné"></div>';
    presentation += '<div class=""> '+ (appointment.quotation?'Devis':'Réservation')  + ' N°' + appointment.id +'</div>';
    if(!appointment.noEnd){
      presentation += '<div class="creneau_time">'+formatDate(startDate, 'HH:mm', 'fr')+' - '+formatDate(endDate, 'HH:mm', 'fr')+'</div>';
    }
    else {
      presentation += '<div class="creneau_time">A partir de <span class="resa_cal_creneau_debut">'+formatDate(startDate, 'HH:mm', 'fr')+'</div>';
    }
    presentation += '<div class="creneau_service">'+serviceName+'</div>';
    var customerDetails = '';
    if(appointment.customer != null){
      customerDetails += '<div class="client_name">';
      if(appointment.customer.company!=null && appointment.customer.company.length > 0){
        customerDetails += '['+appointment.customer.company+'] ';
      }
      customerDetails += appointment.customer.lastName + ' ' +appointment.customer.firstName+'</div>';
    }
    if(appointment.numbers != null){
      if(appointment.numbers > 1) customerDetails += '<div class="client_number">'+appointment.numbers+' pers.</div>';
      if(appointment.numbers == 1) customerDetails += '<div class="client_number">'+appointment.numbers+' pers.</div>';
    }
    if(customerDetails != ''){
      presentation += '<div class="creneau_client">' + customerDetails + '</div>';
    }
    if(appointment.totalPrice != null){
      presentation += '<div class="creneau_resa_price">Total : '+ appointment.totalPrice + this.settings.currency + '</div>';
    }
    var displayMembersSentence = '';
    for(var j = 0; j < appointment.members.length; j++){
      if(displayMembersSentence != '') displayMembersSentence += ', ';
      displayMembersSentence += appointment.members[j].name;
    }
    if(displayMembersSentence != ''){
      presentation += '<div class="creneau_members">'+displayMembersSentence+'</div>';
    }
    if(appointment.note.length > 0) presentation += '<div class="creneau_note">'+this.global.htmlSpecialDecode(appointment.note)+'</div>';


    var displayAlertsSentence = '';
    for(var indexAlert = 0; indexAlert < appointment.alerts.length; indexAlert++){
      var alert = appointment.alerts[indexAlert];
      displayAlertsSentence += '<div class="creneau_alert">' + alert.name + '</div>';
    }
    for(var indexAlert = 0; indexAlert < this.alerts.length; indexAlert++){
      var alert = this.alerts[indexAlert];
      var alertStartDate = this.global.parseDate(alert.startDate);
      var alertEndDate = this.global.parseDate(alert.endDate);
      if(alertStartDate.getTime() == startDate.getTime() && alertEndDate.getTime() == endDate.getTime() && appointment.idService == alert.idService){
        displayAlertsSentence += '<div class="creneau_alert">' + alert.name + '</div>';
      }
    }
    if(displayAlertsSentence != ''){
      presentation += '<div class="creneau_alerts">'+displayAlertsSentence+'</div>';
    }

    presentation += '</div></div>';
    appointment.presentation  = presentation;
    if(startDate.getTime() > endDate.getTime()){
      endDate = this.global.parseDate(startDate);
    }
    if(startDate.getTime() == endDate.getTime()){
      endDate.setMinutes(endDate.getMinutes() + 30);
    }

    return {
      start: startDate,
      end: endDate,
      title: presentation,
      color: {
        primary: appointment.color,
        secondary: appointment.color
      },
      cssClass: 'roundRect',
      allDay: false,
      resizable: {
        beforeStart: false,
        afterEnd: false
      },
      draggable: false,
      meta: {
        incrementsBadgeTotal: false,
        id:appointment.id,
        type:'appointment'
      }
    };
  }

  setTimeWithStringTime(date, stringTime){
    var tokens = stringTime.split(':');
    if(tokens.length == 2){
      date.setHours(tokens[0]);
      date.setMinutes(tokens[1]);
      date.setSeconds(0);
      date.setMilliseconds(0);
    }
    return date;
  }

  infoCalendarToCalendarEvent(infoCalendar){
    var startDate = this.setTimeWithStringTime(new Date(this.date), infoCalendar.startTime);
    var endDate = this.setTimeWithStringTime(new Date(this.date), infoCalendar.endTime);
    if(infoCalendar.endTime == ''){ endDate = this.global.parseDate(infoCalendar.dateEnd); }
    return {
      start: startDate,
      end: endDate,
      title: '<div id="info'+ infoCalendar.id +'">' + this.global.htmlSpecialDecode(infoCalendar.note, true) +'</div>',
      color: {
        primary: this.settings.calendar.info_calendar_color,
        secondary: this.settings.calendar.info_calendar_color
      },
      cssClass: 'roundRect',
      allDay: false,
      resizable: {
        beforeStart: false,
        afterEnd: false
      },
      draggable: false,
      meta: {
        incrementsBadgeTotal: false,
        id:infoCalendar.id,
        type:'infoCalendar'
      }
    };
  }

  constraintCalendarToCalendarEvent(constraint, isServiceConstraint){
    var startDate = this.global.parseDate(constraint.startDate);
    var endDate = this.global.parseDate(constraint.endDate);
    var name = 'Unknown';
    if(isServiceConstraint) name = constraint.service;
    else name = constraint.member;

    return {
      start: startDate,
      end: endDate,
      title: '<div id="constraint'+constraint.id+'" class="resa_cal_nom_service"> Contrainte sur ' + (isServiceConstraint?'l\'activité':'le ' + this.global.getTextByLocale(this.settings.staff_word_single)) + ' ' + name + '</div>',
      color: {
        primary: this.settings.calendar.service_constraint_color,
        secondary: this.settings.calendar.service_constraint_color
      },
      cssClass: 'roundRect',
      allDay: false,
      resizable: {
        beforeStart: false,
        afterEnd: false
      },
      draggable: false,
      meta: {
        incrementsBadgeTotal: false,
        id:constraint.id,
        isServiceConstraint:isServiceConstraint,
        type:'constraint'
      }
    };
  }

  openBooking(booking){
    const modalRef = this.modalService.open(BookingDialogComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.setBookingAndSettings(booking, this.settings);
  }

  /*****
  * INFO CALENDAR
  *****/
  openInfoCalendarDialog(infoCalendar){
    const modalRef = this.modalService.open(InfoCalendarComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.setInfoCalendar(this.date, infoCalendar);
    modalRef.result.then((result) => {
      if(result && result.type == 'added'){
        this.infoCalendars.push(result.data);
        this.events = [...this.events, this.infoCalendarToCalendarEvent(result.data)];
        this.refresh.next();
      }
      else if(result && result.type == 'edited'){
        for(var i = 0; i < this.infoCalendars.length; i++){
          if(this.infoCalendars[i].id == result.data.id){
            this.infoCalendars[i] = result.data;
          }
        }
        for(var i = 0; i < this.events.length; i++){
          if(this.events[i].meta.id == result.data.id && this.events[i].meta.type == 'infoCalendar'){
            this.events[i] = this.infoCalendarToCalendarEvent(result.data);
            this.refresh.next();
          }
        }
      }
      else if(result && result.type == 'deleted'){
        if(result.success){
          var index = this.infoCalendars.findIndex(element => element.id == infoCalendar.id);
          this.infoCalendars.splice(index, 1);
          var index = this.events.findIndex(element => element.meta.id == infoCalendar.id && element.meta.type == 'infoCalendar');
          this.events.splice(index, 1);
          this.refresh.next();
          swal({ title: 'OK', text: '', icon: 'success'});
        }
        else {
          swal({ title: 'Error', text: '', icon: 'error'});
        }
      }
    }, (reason) => {

    });
  }

  /*****
  * CONSTRAINT CALENDAR
  *****/
  openConstraintCalendarDialog(constraint = null, isServiceConstraint = true){
    const modalRef = this.modalService.open(ConstraintComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.setConstraintCalendar(this.date, constraint, isServiceConstraint);
    modalRef.result.then((result) => {
      if(result && result.type == 'added'){
        if(isServiceConstraint) this.serviceConstraintsCalendars.push(result.data);
        else this.memberConstraintsCalendars.push(result.data);
        this.events = [...this.events, this.constraintCalendarToCalendarEvent(result.data, result.isServiceConstraint)];
        this.refresh.next();
      }
      else if(result && result.type == 'edited'){
        var arrayToReplace = this.serviceConstraintsCalendars;
        if(!isServiceConstraint) this.memberConstraintsCalendars.push(result.data);
        for(var i = 0; i < arrayToReplace.length; i++){
          if(arrayToReplace[i].id == result.data.id){
            arrayToReplace[i] = result.data;
          }
        }
        for(var i = 0; i < this.events.length; i++){
          if(this.events[i].meta.id == result.data.id && (this.events[i].meta.isServiceConstraint == isServiceConstraint) && this.events[i].meta.type == 'constraint'){
            this.events[i] = this.constraintCalendarToCalendarEvent(result.data, result.isServiceConstraint);
            this.refresh.next();
          }
        }
      }
      else if(result && result.type == 'deleted'){
        if(result.success){
          var arrayToReplace = this.serviceConstraintsCalendars;
          if(!isServiceConstraint) arrayToReplace = this.memberConstraintsCalendars;
          var index = arrayToReplace.findIndex(element => element.id == result.data.id);
          arrayToReplace.splice(index, 1);
          var index = this.events.findIndex(element => element.meta.id == result.data.id && (element.meta.isServiceConstraint == result.isServiceConstraint) && element.meta.type == 'constraint');
          this.events.splice(index, 1);
          this.refresh.next();
          swal({ title: 'OK', text: '', icon: 'success'});
        }
        else {
          swal({ title: 'Error', text: '', icon: 'error'});
        }
      }
    }, (reason) => {

    });
  }

}
