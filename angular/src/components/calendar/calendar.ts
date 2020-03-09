import {Input, Output, OnChanges, OnInit, Component, EventEmitter, SimpleChanges  } from '@angular/core';

import { DatePipe } from '@angular/common';

@Component({
  selector: 'calendar',
  templateUrl: 'calendar.html',
  styleUrls:['./calendar.css']
})

export class CalendarComponent implements OnChanges,OnInit {
  @Input() disabled = false;
  @Input() color = 'green';
  @Input() datesModel = '';
  @Input() fixYear = null;
  @Input() fixMonth = null;

  //For preview
  @Input() view = false;
  @Input() colors = [];
  @Input() datesModels = [];

  @Output() ngDatesModel: EventEmitter<any> = new EventEmitter();
  @Output() clickDate: EventEmitter<any> = new EventEmitter();


  private dates = [];
  private DaysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
  private Months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre' ];
  public calendar =  null;
  private d = new Date();
  private currMonth = this.d.getMonth();
  private currYear = this.d.getFullYear();
  private currDay = this.d.getDate();

  constructor(private datePipe: DatePipe) {

  }

  ngOnInit(): void {
    if(this.datesModel != null && this.datesModel.length > 0){
      this.dates = this.datesModel.split(',');
    }
    this.toFirstDate();
  }


  ngOnChanges(changes: SimpleChanges) {
    if(changes.datesModels &&
      changes.datesModels.currentValue != null &&  (changes.datesModels.previousValue == null ||
      changes.datesModels.currentValue.toString() != changes.datesModels.previousValue.toString())){
      this.dates = [];
      for(var i = 0; i < changes.datesModels.currentValue.length; i++){
        var dates = changes.datesModels.currentValue[i];
        this.dates = this.dates.concat(dates);
      }
      this.toFirstDate();
      setTimeout(() => { this.clickDate.emit(this.d); }, 1000);
    }
    if(changes.datesModel &&
      changes.datesModel.currentValue != null &&  (changes.datesModel.previousValue == null ||
      changes.datesModel.currentValue.toString() != changes.datesModel.previousValue.toString())){
      this.dates = [];
      if(this.datesModel != null && this.datesModel.length > 0) this.dates = this.datesModel.split(',');
      this.generateCalendar(this.currYear, this.currMonth);
    }
    if(changes.fixYear){
      this.generateCalendar(this.currYear, this.currMonth);
    }
  }

  toFirstDate(){
    if(this.dates.length > 0 && this.fixYear == null && this.fixMonth == null){
      for(var i = 0; i < this.dates.length; i++){
        var date = new Date(this.dates[i]);
        if(date.getTime() > this.d.getTime()){
          this.setDate(new Date(date));
          break;
        }
      }
    }
    this.generateCalendar(this.currYear, this.currMonth);
  }

  getCurrentMonth(){ return this.fixMonth!=null?this.fixMonth:this.currMonth; }
  getCurrentYear(){ return this.fixYear!=null?this.fixYear:this.currYear; }

  // Goes to next month
  nextMonth() {
    if ( this.currMonth == 11 ) {
      this.currMonth = 0;
      this.currYear = this.currYear + 1;
    }
    else {
      this.currMonth = this.currMonth + 1;
    }
    this.generateCalendar(this.currYear, this.currMonth);
  }

  // Goes to previous month
  previousMonth() {
    if ( this.currMonth == 0 ) {
      this.currMonth = 11;
      this.currYear = this.currYear - 1;
    }
    else {
      this.currMonth = this.currMonth - 1;
    }
    this.generateCalendar(this.currYear, this.currMonth);
  }

  generateCalendar(y, m){
    if(this.fixYear != null) y = this.fixYear;
    if(this.fixMonth != null) m = this.fixMonth;
    var allDates = [];
    var firstDayOfMonth = new Date(y, m, 0).getDay()
    , lastDateOfMonth =  new Date(y, m + 1, 0).getDate()
    , lastDayOfLastMonth = m == 0 ? new Date(y-1, 11, 0).getDate() : new Date(y, m, 0).getDate();

    var i = 1;
    do {
      var dow = new Date(y, m, i).getDay();
      if ( dow != 1 && i == 1 ) {
        var k = lastDayOfLastMonth - firstDayOfMonth + 1;
        for(var j = 0; j < firstDayOfMonth; j++) {
          allDates.push(m == 0 ? new Date(y - 1, 11, k + 1) : new Date(y, m - 1, k));
          k++;
        }
      }
      allDates.push(new Date(y, m, i));
      if ( dow != 0 && i == lastDateOfMonth ) {
        var k=1;
        for(dow; dow <= 6; dow++) {
          allDates.push(new Date(y, m + 1, k));
          k++;
        }
      }
      i++;
    }while(i <= lastDateOfMonth);
    this.calendar = this.listToMatrix(allDates, 7);
  }

  listToMatrix(list, elementsPerSubArray) {
    var matrix = [], i, k;
    for (i = 0, k = -1; i < list.length; i++) {
      if (i % elementsPerSubArray === 0) {
        k++;
        matrix[k] = [];
      }
      matrix[k].push(list[i]);
    }
    return matrix;
  }

  ifSameMonth(date){
    return date.getMonth() == this.getCurrentMonth();
  }

  ifToday(date){
    var actualDate = new Date();
    if(this.view){ actualDate = this.d; }
    return date.getFullYear() == actualDate.getFullYear() && date.getMonth() == actualDate.getMonth() && date.getDate() == actualDate.getDate();
  }

  getCurrentColor(){
    return this.color!=null && this.color.length > 0?this.color:'green';
  }

  isSelected(date){
    var stringDate = this.getStringDate(date);
    var color = '';
    if(!this.view){
      if(this.dates.indexOf(stringDate) != -1) {
        color = this.getCurrentColor();
      }
    }
    else {
      for(var i = 0; i < this.datesModels.length; i++){
        if(this.datesModels[i].indexOf(stringDate) != -1) {
          color = this.colors[i];
        }
      }
    }
    return color;
  }

  getStringDate(date){
    return this.datePipe.transform(date, 'yyyy-MM-dd');
  }

  setDate(date){
    this.d = new Date(date);
    this.currMonth = this.d.getMonth();
    this.currYear = this.d.getFullYear();
    this.currDay = this.d.getDate();
  }

  clickOnDate(date){
    this.clickDate.emit(date);
    if(!this.disabled){
      this.switchDate(date);
    }
    else {
      var regenerate = false;
      if(date.getMonth() != this.currMonth){
        regenerate = true;
      }
      this.setDate(date);
      if(regenerate){
        this.generateCalendar(this.currYear, this.currMonth);
      }
    }
  }

  switchDate(date){
    if(!this.disabled){
      var stringDate = this.getStringDate(date);
      var index = this.dates.indexOf(stringDate);
      if(index != -1){
        this.dates.splice(index, 1);
      }
      else {
        this.dates.push(stringDate);
      }
      this.setDatesModel();
    }
  }

  selectWeek(line){
    if(!this.disabled){
      for(var i = 0; i < line.length; i++){
        var date = line[i];
        if(date.getMonth() == this.getCurrentMonth()){
          this.switchDate(date);
        }
      }
    }
  }

  selectColomn(index){
    if(!this.disabled){
      for(var i = 0; i < this.calendar.length; i++){
        var line = this.calendar[i];
        var date = line[index];
        if(date.getMonth() == this.getCurrentMonth()){
          this.switchDate(date);
        }
      }
    }
  }

  setDatesModel(){
    this.dates.sort(function(date1, date2){
      return (new Date(date1)).getTime() - (new Date(date2)).getTime();
    });
    this.datesModel = this.dates.join();
    this.ngDatesModel.emit(this.datesModel);
  }

  removeAllMonth(){
    var date = new Date(this.getCurrentYear(), this.getCurrentMonth(), 1);
    do {
      var stringDate = this.getStringDate(date);
      var index = this.dates.indexOf(stringDate);
      if(index != -1){
        this.dates.splice(index, 1);
      }
      date.setDate(date.getDate() + 1);
    }while(date.getMonth() == this.getCurrentMonth());
    this.setDatesModel();
  }


  addAllMonth(){
    var date = new Date(this.getCurrentYear(), this.getCurrentMonth(), 1);
    do {
      var stringDate = this.getStringDate(date);
      var index = this.dates.indexOf(stringDate);
      if(index == -1){
        this.dates.push(stringDate);
      }
      date.setDate(date.getDate() + 1);
    }while(date.getMonth() == this.getCurrentMonth());
    this.setDatesModel();
  }

}
