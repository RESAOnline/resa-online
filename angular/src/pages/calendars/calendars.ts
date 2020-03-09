import { Component, OnInit  } from '@angular/core';
import { NgModel } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';

declare var swal: any;

@Component({
  selector: 'page-calendars',
  templateUrl: 'calendars.html',
  styleUrls:['./calendars.css']
})

export class CalendarsComponent extends ComponentCanDeactivate implements OnInit {

  public settings:any = null;
  public displayCalendars = [];
  public formCalendars = {order:'', search:''};
  public formCalendar = null;

  public anotherFormCalendar = {days:7, year:2018, years:[]};
  public launchActionSave = false;
  private toSave = false;

  constructor(private userService:UserService, private navService:NavService, private global:GlobalService, private modal: NgbModal) {
    super('Calendriers');
  }

  ngOnInit(): void {
    this.userService.get('calendars/:token', function(data){
      if(data.settings) {
        this.settings = data.settings;
        this.calculateDisplayCalendars();
      }
      this.anotherFormCalendar.year = (new Date()).getFullYear();
      this.anotherFormCalendar.years.push(this.anotherFormCalendar.year - 1);
      this.anotherFormCalendar.years.push(this.anotherFormCalendar.year);
      this.anotherFormCalendar.years.push(this.anotherFormCalendar.year + 1);
      this.anotherFormCalendar.years.push(this.anotherFormCalendar.year + 2);
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des calendriers';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }


  /**
  *
  */
  canDeactivate():boolean{
    return !this.toSave;
  }

  /**
  * set the save boolean
  */
  setToSave(value){
    this.toSave = value;
    if(value) this.changeTitle();
    else this.resetTitle();
  }

  getToSave(){
    return this.toSave;
  }

  getRepeat(number){ return new Array(number); }

  generateNewId(array, field, prefix){
    var actual = [];
    if(array=='' ||	array==false){
      array = [];
    }
    else {
      for(var i = 0; i < array.length; i++){
        actual.push(array[i][field]);
      }
    }
    var index = 0;
    var idName = index;
    if(prefix.length > 0) idName = prefix + index;
    var found = true;
    while(found){
      index++;
      idName = index;
      if(prefix.length > 0) idName = prefix + index;
      found = actual.indexOf(idName) !=-1;
    }
    return idName;
  }

  displayCalendarsOrderBy(order){
    if(this.formCalendars.order == order){
      this.formCalendars.order = 'r' + order;
    }
    else {
      this.formCalendars.order = order;
    }
    this.calculateDisplayCalendars();
  }

  calculateDisplayCalendars(){
    if(this.formCalendars.search.length == 0){
      this.displayCalendars = this.settings.calendars;
    }
    else {
      var calendars = [];
      for(var i = 0; i < this.settings.calendars.length; i++){
        var calendar = this.settings.calendars[i];
        if(calendar.name.toLowerCase().indexOf(this.formCalendars.search.toLowerCase()) != -1){
          calendars.push(calendar);
        }
      }
      this.displayCalendars = calendars;
    }
    if(this.formCalendars.order.length > 1){
      this.displayCalendars.sort(function(notif1, notif2){
        var asc = 1;
        if(this.formCalendars.order[0] == 'r') asc = -1;
        if(this.formCalendars.order == 'name' || this.formCalendars.order == 'rname'){
          if(notif1.name < notif2.name) return -1 * asc;
          if(notif1.name > notif2.name) return 1 * asc;
        }
        return 0;
      }.bind(this));
    }
  }

  formatCalendar(calendar){
    for(var i = 0; i < calendar.groupDates.length; i++){
      calendar.groupDates[i].startDate = new Date(calendar.groupDates[i].startDate);
      calendar.groupDates[i].endDate = new Date(calendar.groupDates[i].endDate);
    }
  }

  addNewCalendar(content){
    var idCalendar = this.generateNewId(this.settings.calendars, 'id', 'calendar_');
    var calendar = {id:idCalendar, color:'#008000', name:'', type:0, dates:'', groupDates:[], manyDates:[], currentIndexManyDates:-1};
    this.openCalendar(content, calendar);
  }

  duplicateCalendar(content, calendar){
    var idCalendar = this.generateNewId(this.settings.calendars, 'id', 'calendar_');
    var newCalendar = JSON.parse(JSON.stringify(calendar));
    newCalendar.id = idCalendar;
    this.openCalendar(content, newCalendar);
  }

  isOkName(calendar){
    return calendar.name.length > 0;
  }

  isOkCalendar(){
    return this.isOkName(this.formCalendar);
  }

  openCalendar(content, calendar){
    this.formCalendar = JSON.parse(JSON.stringify(calendar));
    if(this.formCalendar.color == null || this.formCalendar.color.length == 0) this.formCalendar.color = '#008000';
    this.formatCalendar(this.formCalendar);
    this.modal.open(content, { windowClass: 'mlg', backdrop:'static'}).result.then((result) => {
      if(result == 'save'){
        var found = false;
        for(var i = 0; i < this.settings.calendars.length; i++){
          var localCalendar = this.settings.calendars[i];
          if(localCalendar.id == this.formCalendar.id){
            found = true;
            this.settings.calendars[i] = JSON.parse(JSON.stringify(this.formCalendar));
          }
        }
        if(!found){
          this.settings.calendars.push(JSON.parse(JSON.stringify(this.formCalendar)));
        }
        this.setToSave(true);
        this.calculateDisplayCalendars();
        this.save();
      }
    }, (reason) => {
    });
  }

  deleteCalendar(calendar) {
    var text = document.createElement("div");
    text.innerHTML = 'Voulez-vous supprimer le calendrier <b>' + calendar.name + '</b> ainsi que de <span class="text-danger">désynchroniser toutes les règles</span> contenant ce calendrier ?';
    swal({
      title: 'Êtes-vous sûr ?',
      content: text,
      icon: 'warning',
      buttons: ['Annuler', 'Supprimer'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        var newCalendars = [];
        for(var i = 0; i < this.settings.calendars.length; i++){
          var local = this.settings.calendars[i];
          if(local.id != calendar.id){
            newCalendars.push(local);
          }
        }
        this.settings.calendars = newCalendars;
        this.calculateDisplayCalendars();
        this.setToSave(true);
      }
    });
  }

  clearCalendar(){
    var text = document.createElement("div");
    text.innerHTML = 'Voulez-vous supprimer les dates du calendrier pour pouvoir le recommencer ?';
    swal({
      title: 'Êtes-vous sûr ?',
      content: text,
      icon: 'warning',
      buttons: ['Annuler', 'Supprimer'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        this.formCalendar.dates = '';
        this.formCalendar.groupDates = [];
        this.formCalendar.manyDates = [];
      }
    });
  }

  setDates(dates){
    this.formCalendar.dates = dates;
  }

  setGroupDates(dates){
    this.formCalendar.groupDates = dates;
  }

  addManyDates(){
    this.formCalendar.manyDates.push('');
    this.formCalendar.currentIndexManyDates = this.formCalendar.manyDates.length - 1;
  }

  modifyManyDates(index){
    this.formCalendar.currentIndexManyDates = index;
  }

  deleteManyDates(index){
    this.formCalendar.manyDates.splice(index, 1);
    if(index == this.formCalendar.currentIndexManyDates){
      this.stopManyDates();
    }
  }

  stopManyDates(){
    this.formCalendar.currentIndexManyDates = -1;
  }

  setManyDates(dates){
    this.formCalendar.manyDates[this.formCalendar.currentIndexManyDates] = dates;
  }



  save(){
    if(!this.canDeactivate() && !this.launchActionSave){
      this.launchActionSave = true;
      this.userService.post('calendars/:token', {calendars:JSON.stringify(this.settings.calendars)}, function(data){
        this.launchActionSave = false;
        this.setToSave(false);
      }.bind(this), function(error){
        this.launchActionSave = false;
        var text = 'Impossible de sauvegarder les données';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }
}
