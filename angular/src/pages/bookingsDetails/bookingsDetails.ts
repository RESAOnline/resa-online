import { OnInit, Component, ViewChild  } from '@angular/core';
import { NgbTabChangeEvent } from '@ng-bootstrap/ng-bootstrap';
import { DatePipe, formatDate } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { NgbModal, NgbDate, NgbCalendar } from '@ng-bootstrap/ng-bootstrap';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';
import { BookingDialogComponent } from '../../components/bookingDialog/bookingDialog';

declare var swal: any;

@Component({
  selector: 'bookingsDetails',
  templateUrl: 'bookingsDetails.html',
  styleUrls:['./bookingsDetails.css']
})

export class BookingsDetailsComponent extends ComponentCanDeactivate implements OnInit {

  public date:NgbDate = null;
  public states = [];
  public settings = null;
  public appointments = [];
  public groupByService = [];
  public launchAction:boolean = false;
  public dateLoad:Date = null;

  constructor(public global:GlobalService, private userService:UserService, private navService:NavService, private modalService: NgbModal, private route: ActivatedRoute) {
    super('Vue du jour');
  }

  ngOnInit(): void {
    this.date = this.toNgbDate(this.global.parseDate(this.global.currentDate));
    this.launchAction = true;
    this.userService.get('bookingsSettings/:token', function(data){
      this.settings = data.settings;
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
      this.launchAction = false;
      this.route.queryParams.subscribe(params => {
        if(params != null && params.date){
          var date = new Date(params.date);
          this.date = this.toNgbDate(date);
        }
        this.loadData(0);
      });
    }.bind(this), function(error){
      this.launchAction = false;
      var text = 'Impossible de récupérer les données des réservations';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }
  canDeactivate(){ return true; }
  goEditBooking(booking){ this.navService.changeRoute('planningBookings', {booking:booking.id}); }

  toDate(ngbDate:NgbDate):Date{
    return new Date(ngbDate.year, ngbDate.month - 1, ngbDate.day, 0, 0, 0, 0);
  }

  toNgbDate(date:Date):NgbDate{
    return new NgbDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
  }

  updateDate(date){
    this.date = this.toNgbDate(date);
    this.loadData();
  }

  loadData(page = 0){
    if(page == 0){ this.appointments = []; this.groupByService = []; }
    var nbByPage = 20;
    this.launchAction = true;
    var startDate = this.toDate(this.date);
    var endDate = this.toDate(this.date);
    endDate.setDate(endDate.getDate() + 1);

    this.dateLoad = this.toDate(this.date);
    this.global.setCurrentDate(this.dateLoad);

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
      if(data.length == nbByPage){
        this.loadData(page + 1);
      }
      else {
        this.regroupByIdService();
      }
      this.launchAction = false;
    }, (error) => {
      this.launchAction = false;
      var text = 'Impossible de récupérer les données des réservations';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  regroupByIdService(){
    this.groupByService = [];
    for(var i = 0; i < this.appointments.length; i++){
      var appointment = this.appointments[i];
      this.groupByService[appointment.idService] = this.groupByService[appointment.idService] || {service:appointment.service, color:appointment.color, idService:appointment.idService, position:appointment.positionService, appointments:[]};
      this.groupByService[appointment.idService].appointments.push(appointment);
    }
    this.groupByService = Object.values(this.groupByService);
    this.groupByService.sort((group1, group2) => {
      return group1.position - group2.position;
    });
  }

  getNumberHoursCalendar(){
    var date = new Date(this.dateLoad);
    var startDate = this.global.clearTime(date);
    startDate.setHours(this.settings.calendar.start_time);
    var endDate = this.global.clearTime(date);
    endDate.setHours(this.settings.calendar.end_time);
    var seconds = Math.floor((endDate.getTime() - startDate.getTime()) / 1000);
    return seconds/3600;
  }

  getNumberOfPourcent(){
    var timeslotBlock = ((this.getNumberHoursCalendar()) * 2)+ 1;
    return 100 / timeslotBlock;
  };

  getNumberOfPourcentFloor(){
    return this.getNumberOfPourcent();
  };

  getAppointmentWidth(appointment){
    var date = this.global.parseDate(this.dateLoad);
    var startDate = this.global.parseDate(appointment.startDate);
    if(this.global.clearTime(startDate) != this.global.clearTime(date)) {
      startDate = this.global.parseDate(appointment.startDate);
      var localStartDate = this.global.clearTime(date);
      localStartDate.setHours(startDate.getHours());
      localStartDate.setMinutes(startDate.getMinutes());
      startDate = this.global.parseDate(localStartDate);
    }
    var seconds = Math.floor(((this.global.parseDate(appointment.endDate)).getTime() - startDate.getTime()) / 1000);
    return (this.getNumberOfPourcent() * seconds/3600 * 2);
  }

  getAppointmentLeft(appointment){
    var date =this.global.parseDate(this.dateLoad);
    var borderDate = this.global.clearTime(date);
    borderDate.setHours(this.settings.calendar.start_time);
    var startDateTimeCleared = this.global.clearTime(this.global.parseDate(appointment.startDate));
    var startDate =this.global.parseDate(appointment.startDate);
    if(startDateTimeCleared.getTime() != this.global.clearTime(date).getTime()) {
      startDate = this.global.clearTime(date);
    }
    var seconds = Math.floor((startDate.getTime() - borderDate.getTime()) / 1000);
    return (this.getNumberOfPourcent() * seconds/3600 * 2);
  }

  loadBooking(id){
    if(!this.launchAction){
      this.launchAction = true;
      this.userService.get('booking/:token/' + id, (booking) => {
        this.launchAction = false;
        const modalRef = this.modalService.open(BookingDialogComponent, { windowClass: 'mlg' });
        modalRef.componentInstance.setBookingAndSettings(booking, this.settings);
      }, (error) => {
        this.launchAction = false;
        var text = 'Impossible de récupérer les données de la réservation';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
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


}
