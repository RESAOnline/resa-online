import { OnInit, Component, ViewChild  } from '@angular/core';
import { NgbTabChangeEvent } from '@ng-bootstrap/ng-bootstrap';
import { DatePipe, formatDate } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { AbstractPlanning } from '../../others/AbstractPlanning';
import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ConstraintComponent } from '../../components/constraint/constraint';
import { InfoCalendarComponent } from '../../components/infoCalendar/infoCalendar';
import { NotificationComponent } from '../../components/notification/notification';
import { ReceiptComponent } from '../../components/receipt/receipt';

import { BookingEditorComponent } from '../../components/bookingEditor/bookingEditor';

declare var swal: any;
declare var $: any;

@Component({
  selector: 'planningBookings',
  templateUrl: 'planning.html',
  styleUrls:['./planning.css']
})

export class PlanningComponent extends AbstractPlanning implements OnInit {

  public infoCalendars = [];
  public services = [];
  public tabActivitiesForm = {name:'', activities:[], checkAll:true};
  public indexTab = -1;

  //Popup booking
  public displayPopupBooking = false;

  @ViewChild(BookingEditorComponent, { static: true }) bookingEditor;

  constructor(userService:UserService, navService:NavService, global:GlobalService, datePipe:DatePipe, private modalService: NgbModal, private route: ActivatedRoute) {
    super('Réservations', userService, navService, global, datePipe);
    this.modalService.dismissAll();
    this.route.queryParams.subscribe(params => {
      if(params != null && params.date){
        var date = new Date(params.date);
        this.ngbDate = this.toNgbDate(date);
        this.loadDataWithDate(date);
      }
    });
    this.date = new Date(this.global.currentDate);
    this.ngbDate = this.toNgbDate(this.date);
  }

  ngAfterViewInit() {
    this.bookingEditor.callbackSuccessAdd = (data) => {
      this.firstLoad(false);
      if(data != null){
        this.selectedBooking = this.formatBooking(data.booking);
        this.handler = true;
        if(!this.selectedBooking.oneUpdate){
          this.openNotificationDialog();
        }
      }
      else {
        this.selectedBooking = null;
      }
    }
  }

  updateDate(date){
    this.loadDataWithDate(date);
  }

  firstLoad(readQueryParams = true){
    this.userService.get('planningSettings/:token', function(data){
      this.settings = data.settings;
      this.planning.min = this.settings.calendar_start_time;
      this.planning.max = this.settings.calendar_end_time;
      this.loadDataWithDate(this.date);
      if(readQueryParams) this.readQueryParams();
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  readQueryParams(){
    this.route.queryParams.subscribe(params => {
      if(params != null && params.customer){
        this.userService.get('customer/:token/' + params.customer, function(data){
          this.openEditBooking(data);
          this.actionLaunch = false;
        }.bind(this), function(error){
          this.actionLaunch = false;
          var text = 'Impossible de récupérer les données des activitées';
          if(error != null && error.message != null && error.message.length > 0){
            text += ' (' + error.message + ')';
          }
          swal({ title: 'Erreur', text: text, icon: 'error'});
        }.bind(this));
        this.navService.clearQueryParams();
      }
      else if(params != null && params.booking){
        this.loadDataBooking(params.booking, true, () => {
          this.modifyBooking();
        });
        this.navService.clearQueryParams();
      }
    });
  }

  ngOnInit(): void {
    this.firstLoad();
  }


  goPrintPage(){ this.navService.changeRoute('bookingsDetails'); }
  isRESAManager(){
    return this.userService.getCurrentUser().role == 'administrator' || this.userService.getCurrentUser().role == 'RESA_Manager';
  }

  getListPlaces(places){
    var text = '';
    for(var i = 0; i < places.length; i++){
      var place = this.settings.places.find(element => element.id == places[i]);
      if(place != null){
        if(text.length > 0) text += ', ';
        text += this.global.getTextByLocale(place.name);
      }
    }
    return text;
  }

  getNote(infoCalendar){
    var note = this.global.htmlSpecialDecode(infoCalendar.note);
    if(infoCalendar.idPlaces.length > 0){
      note = '['+ this.getListPlaces(infoCalendar.idPlaces.split(',')) +'] ' + note;
    }
    return note;
  }

  setIndexTab(index){
    this.indexTab = index;
  }

  oneActivityDisplayed(tabActivities){
    for(var i = 0; i < tabActivities.activities.length; i++){
      let id = tabActivities.activities[i];
      if(this.services.findIndex((service) => service.id == id) > -1){
        return true;
      }
    }
    return false;
  }

  loadDataWithDate(date){
    this.date = new Date(date);
    this.currentDate = new Date(date);
    this.global.setCurrentDate(date);
    this.processingData = true;
    this.closeTimeslotDetails();

    this.userService.post('planningServices/:token', {date: formatDate(this.date, 'yyyy-MM-dd', 'fr')}, function(data){
      this.processingData = false;
      this.planning.min = data.minHours;
      this.planning.max = data.maxHours;
      if(date != this.date){
        this.infoCalendars = data.infoCalendars;
        this.services = data.services;
        for(var i = 0; i < this.services.length; i++){
          this.loadDataTimeslots(this.services[i], date);
        }
      }
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  /**
   * load timeslots
   */
  loadDataTimeslots(service, date){
    this.userService.get('planningTimeslots/:token/' + service.id + '/' + formatDate(date, 'yyyy-MM-dd', 'fr'), function(data){
      for(var i = 0; i < data.length; i++){
        var newTimeslot = data[i];
        for(var j = 0; j < service.timeslots.length; j++){
          var timeslot = service.timeslots[j];
          if(newTimeslot.startDate == timeslot.startDate && newTimeslot.endDate == timeslot.endDate){
            timeslot.usedCapacity = newTimeslot.usedCapacity;
            timeslot.totalCapacity = newTimeslot.capacity;
            if(newTimeslot.typeCapacity == 0) timeslot.totalCapacity = newTimeslot.capacityMembers;
            if(newTimeslot.equipmentsActivated && newTimeslot.capacityEquipments != -1){
              timeslot.totalCapacity = Math.min(timeslot.totalCapacity, newTimeslot.capacityEquipments);
            }
            timeslot.membersUsed = newTimeslot.membersUsed;
          }
        }
      }
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  selectTimeslotConstraint(timeslot, id){
    if(timeslot.type == 'timeslot'){
      this.selectedTimeslot = timeslot;
      this.selectedTimeslotOpened = true;
      this.selectedIdTimeslot = id;
      this.appointments = [];
      this.pageAppointments = 1;
      this.nbMaxAppointments = 0;
      if(timeslot.numbers > 0){
        this.setPositionCreaneauDetails(id);
        this.loadDataAppointments(timeslot);
      }
      else {
        this.setDisplayPopupBooking(true);
      }
    }
    else if(timeslot.type == 'constraint'){
      this.openConstraintCalendarDialog(timeslot);
    }
  }

  addActivityOnBooking(idService){
    var startDate = new Date(this.date);
    startDate.setHours(12);
    var endDate = new Date(startDate);
    endDate.setMinutes(30);
    this.selectedTimeslot = {
      startDate:formatDate(startDate, 'yyyy-MM-dd HH:mm:ss', 'fr'),
      endDate:formatDate(endDate, 'yyyy-MM-dd HH:mm:ss', 'fr'),
      idService:idService
    };
    this.setDisplayPopupBooking(true);
  }

  /**
   * load appointments
   */
  loadDataAppointments(timeslot){
    this.loadDataAppointmentsLaunched = true;
    this.userService.post('appointments/:token', {idActivity:timeslot.idService, dateStart:timeslot.startDate, dateEnd:timeslot.endDate, nbByPage:10, page:this.pageAppointments - 1}, function(data){
      this.loadDataAppointmentsLaunched = false;
      if(this.selectedTimeslot != null &&
        this.selectedTimeslot.startDate == timeslot.startDate &&
        this.selectedTimeslot.endDate == timeslot.endDate){
        this.appointments = data.appointments;
        this.nbMaxAppointments = data.max;
      }
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  reloadDataAppointments(){
    this.appointments = [];
    this.loadDataAppointments(this.selectedTimeslot);
  }

  closeHandler(){
    return this.handler = false;
  }

  cancelBooking(){
    this.handler = false;
    this.selectedBooking = null;
  }


  isDisplayActivity(activity){
    return this.indexTab == -1 ||
      this.settings.tabsActivities[this.indexTab].activities.findIndex(element => element == activity.id) > -1;
  }

  checkAllActivityInTabForm(activity){
    if(!this.tabActivitiesForm.checkAll) this.tabActivitiesForm.activities = [];
    else {
      for(var i = 0; i < this.services.length; i++){
        var activity = this.services[i];
        var index = this.tabActivitiesForm.activities.findIndex(element => element == activity.id);
        if(index == -1) this.tabActivitiesForm.activities.push(activity.id);
      }
    }
  }

  switchActivity(activity){
    var index = this.tabActivitiesForm.activities.findIndex(element => element == activity.id);
    if(index == -1) this.tabActivitiesForm.activities.push(activity.id);
    else this.tabActivitiesForm.activities.splice(index, 1);
  }

  openTabActivitiesForm(content, index){
    if(this.services.length > 0){
      if(index == -1){
        this.services.map(element => this.switchActivity(element));
      }
      else {
        this.tabActivitiesForm = JSON.parse(JSON.stringify(this.settings.tabsActivities[index]));
      }
      this.modalService.open(content, { backdrop:'static'  }).result.then((result) => {
        if(result!=null && result == 'ok'){
          if(index == -1){
            this.settings.tabsActivities.push(JSON.parse(JSON.stringify(this.tabActivitiesForm)));
            this.setIndexTab(this.settings.tabsActivities.length - 1);
          }
          else {
            this.settings.tabsActivities[index] = JSON.parse(JSON.stringify(this.tabActivitiesForm));
          }
          this.tabActivitiesForm = {name:'', activities:[], checkAll:true};
          this.userService.post('settingsLite/:token', {tabs_activities:JSON.stringify(this.settings.tabsActivities)}, function(data){

          }, function(error){
            var text = 'Impossible de sauvegarder les onglets';
            if(error != null && error.message != null && error.message.length > 0){
              text += ' (' + error.message + ')';
            }
            swal({ title: 'Erreur', text: text, icon: 'error'});
          });
        }
        else if(result!=null && result == 'delete' && index > -1){
          swal({
            title: 'Êtes-vous sûr ?',
            text: 'Êtes-vous sûr de supprimer cet onglet ?',
            icon: 'warning',
            buttons: ['Annuler', 'Supprimer'],
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              this.settings.tabsActivities.splice(index, 1);
              this.setIndexTab(-1);
              this.tabActivitiesForm = {name:'', activities:[], checkAll:true};
              this.userService.post('settingsLite/:token', {tabs_activities:JSON.stringify(this.settings.tabsActivities)}, function(data){

              }, function(error){
                var text = 'Impossible de sauvegarder les onglets';
                if(error != null && error.message != null && error.message.length > 0){
                  text += ' (' + error.message + ')';
                }
                swal({ title: 'Erreur', text: text, icon: 'error'});
              });
            }
          });
        }
      }, (reason) => {
        this.tabActivitiesForm = {name:'', activities:[], checkAll:true};
      });
    }
  }

  openNotificationDialog(){
    const modalRef = this.modalService.open(NotificationComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.setBooking(this.selectedBooking);
    modalRef.componentInstance.setCustomer(this.selectedBooking.customer);
  }

  openReceipt(){
    const modalRef = this.modalService.open(ReceiptComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.loadBookingReceipt(this.selectedBooking);
  }

  getTagById(idTag){
    var tag:any = {name:'', color:''};
    if(this.settings != null && this.settings.custom_tags != null){
      tag = this.settings.custom_tags.find(element => element.id == idTag);
    }
    return tag;
  }

  getPlaceById(id){
    return this.settings.places.find(element => element.id == id);
  }

  getParticipantsParameter(idParameter){
    if(this.settings!=null && this.settings.form_participants_parameters != null){
      return this.settings.form_participants_parameters.find(element => element.id == idParameter)
    }
    return null;
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

  isBookingInProcess(){ return this.bookingEditor.isBookingInProcess(); }

  setDisplayPopupBooking(displayPopupBooking){
    if(displayPopupBooking){
      this.bookingEditor.openBookingEditor(this.selectedTimeslot);
    }
    this.closeTimeslotDetails();
    this.displayPopupBooking = displayPopupBooking;
  }

  modifyBooking(){
    this.bookingEditor.loadBooking(this.selectedBooking);
    this.displayPopupBooking = true;
  }

  openAddBooking(){
    this.bookingEditor.reinit();
    this.displayPopupBooking = true;
  }

  openEditBooking(customer){
    this.bookingEditor.reinit();
    this.bookingEditor.setCustomer(customer);
    this.displayPopupBooking = true;
  }

  openPaymentStateDialog(content){
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result){
        this.changePaymentStateBooking();
      }
    }, (reason) => {

    });
  }

  openConstraintCalendarDialog(timeslot = null){
    if(timeslot == null) timeslot = {type:'constraint', constraint:null};
    if(timeslot.type == 'constraint'){
      const modalRef = this.modalService.open(ConstraintComponent, { windowClass: 'mlg' });
      modalRef.componentInstance.setConstraintCalendar(this.date, timeslot.constraint, true);
      modalRef.result.then((result) => {
        if(result && result.type == 'added'){
          this.firstLoad(false);
        }
        else if(result && result.type == 'edited'){
          this.firstLoad(false);
        }
        else if(result && result.type == 'deleted'){
          if(result.success){
            this.firstLoad(false);
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

  openInfoCalendarDialog(infoCalendar = null){
    const modalRef = this.modalService.open(InfoCalendarComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.setInfoCalendar(this.date, infoCalendar);
    modalRef.result.then((result) => {
      if(result && result.type == 'added'){
        this.firstLoad();
      }
      else if(result && result.type == 'edited'){
        this.firstLoad();
      }
      else if(result && result.type == 'deleted'){
        if(result.success){
          this.firstLoad();
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
