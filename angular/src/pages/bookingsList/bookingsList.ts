import { OnChanges, OnInit, Component, ViewChild  } from '@angular/core';
import { DatePipe, formatDate } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { NgbModal, NgbDate, NgbCalendar } from '@ng-bootstrap/ng-bootstrap';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';

declare var swal: any;

@Component({
  selector: 'bookingsList',
  templateUrl: 'bookingsList.html',
  styleUrls:['./bookingsList.css']
})

export class BookingsListComponent extends ComponentCanDeactivate implements OnInit {

  public settings = [];
  public bookings = [];
  public maxBookings = 0;
  public launchAction:boolean = false;
  public searchForm = {
    startDate:this.toNgbDate(new Date()),
    endDate:this.toNgbDate(new Date()),
    nbByPage:10,
    page:1,
    typeSelect:0,
    month:new Date(),
    states:[],
    payments:[]
  };

  public optionsTypeSelect = [];
  public months = [];


  constructor(public global:GlobalService, private userService:UserService, private navService:NavService, private modalService: NgbModal) {
    super('Liste des réservations');
  }

  ngOnInit(): void {
    this.searchForm.startDate = this.toNgbDate(new Date(this.global.currentDate));
    this.searchForm.endDate = this.toNgbDate(new Date(this.global.currentDate));
    this.launchAction = true;
    this.generateSelectbox();
    this.userService.get('bookingsSettings/:token', function(data){
      this.settings = data.settings;
      this.launchAction = false;
      for(var i = 0; i < data.statesList.length; i++){
        var state = data.statesList[i];
        if(state.isFilter){
          this.searchForm.states.push({
            id:state.id,
            name:state.filterName,
            selected:state.selected
          });
        }
      }
      this.searchForm.payments.push({ id:'noPayment', name:'Pas de paiement', selected:true});
      this.searchForm.payments.push({ id:'advancePayment', name:'Acompte', selected:true});
      this.searchForm.payments.push({ id:'deposit', name:'Caution', selected:true});
      this.searchForm.payments.push({ id:'complete', name:'Encaissée', selected:true});
      this.loadData();
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

  generateSelectbox(){
    var actualDate = new Date();
    var tomorowDate = new Date(actualDate);
    tomorowDate.setDate(tomorowDate.getDate() + 1);
    var yesterdayDate = new Date(actualDate);
    yesterdayDate.setDate(yesterdayDate.getDate() - 1);

    this.optionsTypeSelect = [
      'Aujourd\'hui - ' + formatDate(actualDate, 'dd MMMM yyyy', 'fr'),
      'Demain - ' + formatDate(tomorowDate, 'dd MMMM yyyy', 'fr'),
      'Les 7 prochaines jours',
      'Hier - ' + formatDate(yesterdayDate, 'dd MMMM yyyy', 'fr'),
      'Mois'
    ];

    this.months = [];
    var actualDate = new Date();
    for(var i = 0; i < 12; i++){
      var date = new Date();
      date.setMonth(actualDate.getMonth() - i, 0);
      this.months.push({ date:date });
      if(i > 0){
        var date = new Date();
        date.setMonth(actualDate.getMonth() + i, 0);
        this.months.push({ date:date });
      }
    }
    this.months.sort((monthA, monthB) => {
      return monthA.date.getTime() - monthB.date.getTime();
    })
  }

  toDate(ngbDate:NgbDate):Date{
    return new Date(ngbDate.year, ngbDate.month - 1, ngbDate.day, 0, 0, 0, 0);
  }

  toNgbDate(date:Date):NgbDate{
    return new NgbDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
  }

  updateDate(date){
    this.searchForm.startDate = this.toNgbDate(date);
    this.searchForm.endDate = this.toNgbDate(date);
    this.loadData();
  }

  loadData(){
    this.launchAction = true;
    var startDate = this.toDate(this.searchForm.startDate);
    var endDate = this.toDate(this.searchForm.endDate);
    endDate.setDate(endDate.getDate() + 1);
    this.global.setCurrentDate(startDate);

    var startDateString = formatDate(startDate, 'yyyy-MM-dd HH:mm:ss', 'fr');
    var endDateString = formatDate(endDate, 'yyyy-MM-dd HH:mm:ss', 'fr');

    var stateList = [];
    this.searchForm.states.forEach(state => {
      if(state.selected){
        stateList.push(state.id);
      }
    });

    var paymentsList = [];
    this.searchForm.payments.forEach(payment => {
      if(payment.selected){
        paymentsList.push(payment.id);
      }
    });

    this.userService.post('bookings/:token', {startDate:startDateString, endDate:endDateString, nbByPage:this.searchForm.nbByPage, page:this.searchForm.page - 1, stateList:JSON.stringify(stateList), paymentsList:JSON.stringify(paymentsList) }, (data) => {
      this.bookings = data.bookings;
      this.maxBookings = data.max;
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

  changeStartDate(){
    var startDate = this.toDate(this.searchForm.startDate);
    var endDate = this.toDate(this.searchForm.endDate);
    if(startDate > endDate){
      this.searchForm.endDate = this.toNgbDate(startDate);
    }
  }

  changeEndDate(){
    var startDate = this.toDate(this.searchForm.startDate);
    var endDate = this.toDate(this.searchForm.endDate);
    if(startDate > endDate){
      this.searchForm.startDate = this.toNgbDate(endDate);
    }
  }

  changeSelect(){
    var startDate = new Date();
    var endDate = new Date();
    if(this.searchForm.typeSelect == 1) {
      startDate.setDate(startDate.getDate() + 1);
      endDate.setDate(endDate.getDate() + 1);
    }
    else if(this.searchForm.typeSelect == 2){
      endDate.setDate(endDate.getDate() + 7);
    }
    else if(this.searchForm.typeSelect == 3){
      startDate.setDate(startDate.getDate() - 1);
      endDate.setDate(endDate.getDate() - 1);
    }
    else if(this.searchForm.typeSelect == 4){
      var startDate = new Date(this.searchForm.month);
      var endDate = new Date(this.searchForm.month);
      startDate.setDate(1);
      endDate.setDate(1);
      endDate.setMonth(endDate.getMonth() + 1);
      endDate.setDate(endDate.getDate() - 1);
    }
    this.searchForm.startDate = this.toNgbDate(startDate);
    this.searchForm.endDate = this.toNgbDate(endDate);
  }

  changeMonth(){
    if(this.searchForm.typeSelect == 4){
      var startDate = new Date(this.searchForm.month);
      var endDate = new Date(this.searchForm.month);
      startDate.setDate(1);
      endDate.setDate(1);
      endDate.setMonth(endDate.getMonth() + 1);
      endDate.setDate(endDate.getDate() - 1);
      this.searchForm.startDate = this.toNgbDate(startDate);
      this.searchForm.endDate = this.toNgbDate(endDate);
    }
  }



}
