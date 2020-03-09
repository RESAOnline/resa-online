import { Input, Output, OnInit, OnChanges, Component, EventEmitter, SimpleChanges  } from '@angular/core';

import { DatePipe } from '@angular/common';
import { NgbDate, NgbCalendar } from '@ng-bootstrap/ng-bootstrap';

import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { NavService } from '../../services/nav.service';

declare var jQuery:any;
declare var swal: any;

@Component({
  selector: 'subMenu',
  templateUrl: 'subMenu.html',
  styleUrls:['./subMenu.css']
})

export class SubMenuComponent implements OnInit, OnChanges {

  @Input() date:Date = null;
  @Input() displayLinks:boolean = true;
  @Output() changeDate: EventEmitter<any> = new EventEmitter();
  public ngbDate:NgbDate = null;
  public displayDatePicker:boolean = false;
  public actionLaunch:boolean = false;

  constructor(private userService: UserService, public global:GlobalService, private navService:NavService, private datePipe: DatePipe) {
    this.navService.getRoute();
  }

  ngOnInit(): void {

  }

  ngOnChanges(changes: SimpleChanges) {
    if(changes.date != null &&
      changes.date.currentValue != null &&
      (changes.date.previousValue == null ||
      changes.date.previousValue.getTime() != changes.date.currentValue.getTime())){
      this.ngbDate = this.toNgbDate(changes.date.currentValue);
      this.closeDatePicker();
      if(changes.date.currentValue.getTime() != this.date.getTime()){
        this.changeDate.emit(changes.date.currentValue);
      }
    }
  }

  toDate(ngbDate:NgbDate):Date{
    return new Date(ngbDate.year, ngbDate.month - 1, ngbDate.day, 0, 0, 0, 0);
  }

  toNgbDate(date:Date):NgbDate{
    return new NgbDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
  }

  toggleDate(){
    this.displayDatePicker = !this.displayDatePicker;
    if(this.displayDatePicker){
      jQuery('body').click(() => { this.closeDatePicker(); });
      jQuery('ngb-datepicker').click((event) => { event.stopPropagation(); });
      event.stopPropagation();
    }
    else {
      this.closeDatePicker();
    }
  }

  closeDatePicker(){
    this.displayDatePicker = false;
    jQuery('body').unbind('click');
    jQuery('ngb-datepicker').unbind('click');
  }

  updateDate(){
    this.closeDatePicker();
    var date = this.toDate(this.ngbDate);
    this.global.setCurrentDate(date);
    this.changeDate.emit(date);
  }

  previousDate(){
    var date = this.global.currentDate;
    date.setDate(date.getDate() - 1);
    this.ngbDate = this.toNgbDate(date);
    this.global.setCurrentDate(date);
    this.changeDate.emit(date);
  }

  nextDate(){
    var date = this.global.currentDate;
    date.setDate(date.getDate() + 1);
    this.ngbDate = this.toNgbDate(date);
    this.global.setCurrentDate(date);
    this.changeDate.emit(date);
  }

  isRESAManager(){ return this.userService.isRESAManager(); }
  isDisplayOnlyPlanningGroups(){ return this.userService.displayOnlyPlanningGroups(); }

  goDaily(){ this.navService.changeRoute('daily'); }
  isDaily(){ return this.navService.isSameUrl('daily'); }

  goPlanning(){ this.navService.changeRoute('planningBookings'); }
  isPlanning(){ return this.navService.isSameUrl('planningBookings'); }

  goPlanningMembers(){ this.navService.changeRoute('planningMembers'); }
  isPlanningMembers(){ return this.navService.isSameUrl('planningMembers'); }

  goPlanningGroups(){ this.navService.changeRoute('planningGroups'); }
  isPlanningGroups(){ return this.navService.isSameUrl('planningGroups'); }

  goBookingsCalendars(){ this.navService.changeRoute('bookingsCalendar'); }
  isBookingsCalendars(){ return this.navService.isSameUrl('bookingsCalendar'); }

  goBookingsList(){ this.navService.changeRoute('bookingsList'); }
  isBookingsList(){ return this.navService.isSameUrl('bookingsList'); }

  goBookingsDetails(){ this.navService.changeRoute('bookingsDetails'); }
  isBookingsDetails(){ return this.navService.isSameUrl('bookingsDetails'); }

  goQuotations(){ this.navService.changeRoute('quotations'); }
  isQuotations(){ return this.navService.isSameUrl('quotations'); }

  updateUserSettings(reload = true){
    this.actionLaunch = true;
    this.userService.post('updateUserSettings/:token', {filterSettings:JSON.stringify(this.global.filterSettings)}, (data) => {
      if(reload) this.navService.reload();
      this.actionLaunch = false;
    }, (error) => {
      this.actionLaunch = false;
      var text = 'Impossible de sauvegarder les lieux';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  isFavPage(){ return this.navService.isSameUrl(this.global.filterSettings.favPage); }
  setFavPage(){
    this.global.setFavPage(this.navService.getRoute());
    this.updateUserSettings(false);
  }
}
