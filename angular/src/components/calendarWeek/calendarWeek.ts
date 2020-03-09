import {Input, Output, OnChanges, OnInit, Component, EventEmitter, SimpleChanges  } from '@angular/core';

import { DatePipe } from '@angular/common';

@Component({
  selector: 'calendar-week',
  templateUrl: 'calendarWeek.html',
  styleUrls:['./calendarWeek.css']
})

export class CalendarWeekComponent implements OnChanges, OnInit {
  @Input() disabled = false;
  @Input() color = '#008000';
  @Input() datesModels = [];
  @Input() days = 1;
  @Input() fixYear = null;
  @Input() fixMonth = null;

  //previes
  @Input() view = false;
  @Input() colors = [];
  @Input() datesModelsArray = [];

  @Output() ngDatesModel: EventEmitter<any> = new EventEmitter();
  @Output() clickDate: EventEmitter<any> = new EventEmitter();

  private DaysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
  private Months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre' ];
  public calendar =  null;
  private d = new Date();
  private currMonth = this.d.getMonth();
  private currYear = this.d.getFullYear();
  private currDay = this.d.getDate();

  constructor(private datePipe: DatePipe) {

  }

  clearTime(date){
    date = new Date(date);
    date.setHours(0);
    date.setMinutes(0);
    date.setSeconds(0);
    date.setMilliseconds(0);
    return date;
  }

  ngOnInit(): void {
    this.toFirstDate();
  }

  ngOnChanges(changes: SimpleChanges) {
    if(changes.datesModelsArray &&
      changes.datesModelsArray.currentValue != null &&  (changes.datesModelsArray.previousValue == null ||
      changes.datesModelsArray.currentValue.toString() != changes.datesModelsArray.previousValue.toString())){
      this.datesModels = [];
      for(var i = 0; i < changes.datesModelsArray.currentValue.length; i++){
        var dates = changes.datesModelsArray.currentValue[i];
        this.datesModels = this.datesModels.concat(dates);
      }
      this.sortDatesModels();
      this.toFirstDate();
      setTimeout(() => { this.clickDate.emit(this.d); }, 1000);
    }
    if(changes.datesModels &&
      changes.datesModels.currentValue != null &&  (changes.datesModels.previousValue == null ||
      changes.datesModels.currentValue.toString() != changes.datesModels.previousValue.toString())){
      this.generateCalendar(this.currYear, this.currMonth);
    }
    if(changes.fixYear){
      this.generateCalendar(this.currYear, this.currMonth);
    }
  }

  toFirstDate(){
    if(this.datesModels.length > 0){
      for(var i = 0; i < this.datesModels.length; i++){
        var dates = this.datesModels[i];
        var date = this.clearTime(new Date(dates.startDate));
        if(date.getTime() > this.clearTime(this.d).getTime()){
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
        for(var j=0; j < firstDayOfMonth; j++) {
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

  getIndexOf(date, datesModels){
    for(var i = 0; i < datesModels.length; i++){
      var dates = datesModels[i];
      if(this.clearTime(dates.startDate) <= date && date <= this.clearTime(dates.endDate)) {
        return i;
      }
    }
    return -1;
  }

  getIndexOfDates(startDate, datesModels, days){
    var date = new Date(startDate);
    do{
      var index = this.getIndexOf(date, datesModels);
      if(index > -1) return index;
      date.setDate(date.getDate() + 1);
      days--;
    }
    while(days > 0);
  }

  colorLuminance(hex, lum) {
  	// validate hex string
  	hex = String(hex).replace(/[^0-9a-f]/gi, '');
  	if (hex.length < 6) {
  		hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
  	}
  	lum = lum || 0;

  	// convert to decimal and change luminosity
  	var rgb = "#", c, i;
  	for (i = 0; i < 3; i++) {
  		c = parseInt(hex.substr(i*2,2), 16);
  		c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
  		rgb += ("00"+c).substr(c.length);
  	}

  	return rgb;
  }

  getCurrentColor(){
    return this.color!=null && this.color.length > 0?this.color:'#008000';
  }

  isSelected(date){
    var color = '';
    if(!this.view){
      var index = this.getIndexOf(date, this.datesModels);
      if(index > -1) {
        color = this.getCurrentColor();
        if(index%2 == 1) color = this.colorLuminance(color, 0.3);
      }
    }
    else {
      for(var i = 0; i< this.datesModelsArray.length; i++){
        var datesModels = this.datesModelsArray[i];
        if(this.getIndexOf(date, datesModels) > -1){
          color = this.colors[i];
          break;
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
      if(date.getMonth() != this.getCurrentMonth()){
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
      var index = this.getIndexOfDates(date, this.datesModels, this.days);
      if(index > -1){
        this.datesModels.splice(index, 1);
      }
      else {
        var startDate = new Date(date);
        startDate.setHours(12);
        var endDate = new Date(date);
        endDate.setHours(12);
        endDate.setDate(endDate.getDate() + (this.days - 1));
        this.datesModels.push({
          startDate:this.getStringDate(startDate),
          endDate:this.getStringDate(endDate)
        });

        this.sortDatesModels();
      }
    }
  }

  sortDatesModels(){
    this.datesModels.sort(function(group1, group2){
      var startDateA = new Date(group1.startDate);
      var startDateB = new Date(group2.startDate);
      return startDateA.getTime() - startDateB.getTime();
    });
  }

  selectWeek(line){
    if(!this.disabled){
      this.switchDate(line[0]);
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


}
