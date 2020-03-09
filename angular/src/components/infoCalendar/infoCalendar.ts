import { OnInit, Component  } from '@angular/core';
import { DatePipe, formatDate } from '@angular/common';
import { NgbModal, NgbActiveModal, ModalDismissReasons, NgbDate, NgbCalendar } from '@ng-bootstrap/ng-bootstrap';

import { Observable, Subject } from 'rxjs';
import { debounceTime, distinctUntilChanged, map } from 'rxjs/operators';

import { GlobalService } from '../../services/global.service';
import { UserService } from '../../services/user.service';

declare var swal: any;
declare var tinyMCE:any;

@Component({
  selector: 'infoCalendar',
  templateUrl: 'infoCalendar.html',
  styleUrls:['./infoCalendar.css']
})

export class InfoCalendarComponent implements OnInit {

  public settings = null;
  private skeletonInfoCalendar:any = null;
  public formInfoCalendar:any = {manyDays:false, date:new NgbDate(2018,1,1), dateEnd:new NgbDate(2018,1,1), time:new Date(), timeEnd:new Date(), infoCalendar:null};
  public launchAction:boolean = false;
  public infoToLoad = {date:new Date(), infoCalendar:null};
  public placesList = [];

  public startTimeInputUpdate = new Subject<string>();
  public endTimeInputUpdate = new Subject<string>();

  constructor(private userService:UserService, public global:GlobalService, public activeModal: NgbActiveModal, private modalService: NgbModal) {
    this.loadData();

    this.loadData();
    this.startTimeInputUpdate.pipe(
      debounceTime(500),
      distinctUntilChanged())
      .subscribe(value => {
        this.changeStartTime(this.formInfoCalendar);
      });
    this.endTimeInputUpdate.pipe(
      debounceTime(500),
      distinctUntilChanged())
      .subscribe(value => {
        this.changeEndTime(this.formInfoCalendar);
      });
  }

  loadData(){
    this.launchAction = true;
    this.userService.get('bookingsSettings/:token', function(data){
      this.launchAction = false;
      this.settings = data.settings;

      this.placesList = [{value:'', title:'Tous'}];
      for(var i = 0; i < this.settings.places.length; i++){
        var place = this.settings.places[i];
        this.placesList.push({
          value:',' + place.id + ',',
          title:this.global.getTextByLocale(place.name)
        });
      }
      this.skeletonInfoCalendar = data.skeletonInfoCalendar;
      this.clearFormInfoCalendar();
      this.loadInfoCalendar(this.infoToLoad.date, this.infoToLoad.infoCalendar);
    }.bind(this), function(error){
      this.launchAction = false;
      var text = 'Impossible de récupérer les données des contraintes';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  ngOnInit(): void {

  }

  toDate(ngbDate:NgbDate):Date{
    return new Date(ngbDate.year, ngbDate.month - 1, ngbDate.day, 0, 0, 0, 0);
  }

  toNgbDate(date:Date):NgbDate{
    return new NgbDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
  }

  dateToTime(date){
    return (date.getHours()<10?'0'+date.getHours():''+date.getHours()) + ':' + (date.getMinutes()<10?'0'+date.getMinutes():''+date.getMinutes());
  }

  timeToDate(time){
    var tokens = time.split(':');
    return new Date(0, 0, 0, tokens[0], tokens[1], 0, 0);
  }

  isSameDay(date1, date2){
    return date1.getFullYear() == date2.getFullYear() &&
    date1.getMonth() == date2.getMonth() &&
    date1.getDate() == date2.getDate();
  }

  changeStartTime(form){
    var timeDate = this.generateStartDate(form);
    var timeEndDate = this.generateEndDate(form);
    if(timeDate.getTime() >= timeEndDate.getTime()){
      form.timeEnd = this.dateToTime(new Date(0, 0, 0, timeDate.getHours() + 1, timeDate.getMinutes()));
    }
  }

  changeEndTime(form){
    var timeDate = this.generateStartDate(form);
    var timeEndDate = this.generateEndDate(form);
    if(timeDate.getTime() >= timeEndDate.getTime() && timeEndDate.getHours() > 0){
      form.time = this.dateToTime(new Date(0, 0, 0, timeEndDate.getHours() - 1, timeEndDate.getMinutes()));
      console.log(timeDate + ' > ' + timeEndDate + ' = ' + form.time )
    }
  }

  getFirstPlaces(places){
    if(this.global.filterSettings == null || this.global.filterSettings.places == null ) return '';
    for(var i  = 0; i < places.length; i++){
      var place = places[i];
      if(this.global.filterSettings.places[place.id] == null) return '';
      if(this.global.filterSettings.places[place.id]) return place.id;
    }
    return '';
  }

  setInfoCalendar(date, infoCalendar = null){
    this.infoToLoad.date = date;
    this.infoToLoad.infoCalendar = infoCalendar;
  }

  loadInfoCalendar(date, infoCalendar = null){
    if(infoCalendar == null) infoCalendar = this.skeletonInfoCalendar;
    this.formInfoCalendar.infoCalendar = JSON.parse(JSON.stringify(infoCalendar));
    this.formInfoCalendar.infoCalendar.date = new Date(this.formInfoCalendar.infoCalendar.date);
    if(this.formInfoCalendar.infoCalendar.dateEnd == null){
      this.formInfoCalendar.infoCalendar.dateEnd = new Date(this.formInfoCalendar.infoCalendar.date);
    }
    else {
      this.formInfoCalendar.infoCalendar.dateEnd = new Date(this.formInfoCalendar.infoCalendar.dateEnd);
    }
    if(infoCalendar.id > -1){
      this.formInfoCalendar.date = this.toNgbDate(this.formInfoCalendar.infoCalendar.date);
      this.formInfoCalendar.dateEnd = this.toNgbDate(this.formInfoCalendar.infoCalendar.dateEnd);
      this.formInfoCalendar.manyDays = !this.isSameDay(this.formInfoCalendar.infoCalendar.date, this.formInfoCalendar.infoCalendar.dateEnd);
      this.formInfoCalendar.infoCalendar.note = this.global.htmlSpecialDecode(this.formInfoCalendar.infoCalendar.note);
      this.formInfoCalendar.time = this.dateToTime(this.formInfoCalendar.infoCalendar.date);
      this.formInfoCalendar.timeEnd = this.dateToTime(this.formInfoCalendar.infoCalendar.dateEnd);
    }
    else {
      this.formInfoCalendar.date = this.toNgbDate(date);
      this.formInfoCalendar.dateEnd = this.toNgbDate(date);
      this.formInfoCalendar.time = '12:00';
      this.formInfoCalendar.timeEnd = '13:00';
      var firstPlace = this.getFirstPlaces(this.settings.places);
      if(firstPlace != ''){
        this.formInfoCalendar.infoCalendar.idPlaces = ',' + firstPlace + ',';
      }
    }
  }

  isNewInfoCalendar(){
    return this.formInfoCalendar.infoCalendar == null || this.formInfoCalendar.infoCalendar.id == -1;
  }

  isOkFormInfoCalendar(){
    return !this.launchAction && this.formInfoCalendar!=null && this.formInfoCalendar.infoCalendar !=null && this.formInfoCalendar.infoCalendar.note != null && this.formInfoCalendar.infoCalendar.note.length > 0 && this.generateEndDate(this.formInfoCalendar).getTime() > this.generateStartDate(this.formInfoCalendar).getTime();
  }


  generateStartDate(form){
    var date = this.toDate(form.date);
    var timeDate = this.timeToDate(form.time);
    date.setHours(timeDate.getHours());
    date.setMinutes(timeDate.getMinutes());
    return date;
  }

  generateEndDate(form){
    var date = this.toDate(form.date);
    if(form.manyDays) {
      date = this.toDate(form.dateEnd);
    }
    var timeEndDate = this.timeToDate(form.timeEnd);
    date.setHours(timeEndDate.getHours());
    date.setMinutes(timeEndDate.getMinutes());
    return date;
  }

  clearFormInfoCalendar(){
    this.formInfoCalendar = {manyDays:false, date:new NgbDate(0,0,0), dateEnd:new NgbDate(0,0,0), time:new Date(), timeEnd:new Date(), infoCalendar:JSON.parse(JSON.stringify(this.skeletonInfoCalendar))};
  }

  dismiss(){
    this.activeModal.dismiss(0);
  }

  close(value){
    this.activeModal.close(value);
  }

  editInfoCalendar(){
    this.launchAction = true;
    var newInfoCalendar = JSON.parse(JSON.stringify(this.formInfoCalendar.infoCalendar));
    //newInfoCalendar.idPlaces = this.idPlaces;
    newInfoCalendar.note = newInfoCalendar.note.replace(new RegExp('\n', 'g'),'<br />');
    newInfoCalendar.date = formatDate(this.generateStartDate(this.formInfoCalendar), 'yyyy-MM-dd HH:mm:ss', 'fr');
    newInfoCalendar.dateEnd = formatDate(this.generateEndDate(this.formInfoCalendar), 'yyyy-MM-dd HH:mm:ss', 'fr');
    newInfoCalendar.startTime = this.formInfoCalendar.time;
    newInfoCalendar.endTime = this.formInfoCalendar.timeEnd;
    this.userService.post('informationsCalendar/:token', {infoCalendar:JSON.stringify(newInfoCalendar)}, (data) => {
      if(newInfoCalendar.id == -1){
        this.close({type:'added', data:data});
      }
      else {
        this.close({type:'edited', data:data});
      }
      this.launchAction = false;
    }, (error) => {
      this.launchAction = false;
      var text = 'Impossible de sauvegarder l\'information';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  deleteInfoCalendar(){
    swal({
      title: 'Êtes-vous sûr ?',
      text: 'Êtes-vous sûr de supprimer cette information ?',
      icon: 'warning',
      buttons: ['Annuler', 'Supprimer'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        this.launchAction = true;
        var newInfoCalendar = JSON.parse(JSON.stringify(this.formInfoCalendar.infoCalendar));
        this.userService.delete('informationsCalendar/:token/' + this.formInfoCalendar.infoCalendar.id, (data) => {
          this.launchAction = false;
          this.close({type:'deleted', success:data.result, data:newInfoCalendar});
        }, (error) => {
          this.launchAction = false;
          var text = 'Impossible de supprimer la contrainte';
          if(error != null && error.message != null && error.message.length > 0){
            text += ' (' + error.message + ')';
          }
          swal({ title: 'Erreur', text: text, icon: 'error'});
        });
      }
    });
  }

}
