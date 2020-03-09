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
  selector: 'constraint',
  templateUrl: 'constraint.html',
  styleUrls:['./constraint.css']
})

export class ConstraintComponent implements OnInit {

  public settings = null;
  public services:any[] = [];
  public members:any[] = [];
  private skeletonServiceConstraint:any = null;
  private skeletonMemberConstraint:any = null;
  public formConstraintCalendar:any = {manyDays:false, date:new NgbDate(2018,1,1), dateEnd:new NgbDate(2018,1,1), time:'12:00', timeEnd:'13:00', serviceConstraint:null, memberConstraint:null, isServiceConstraint:true};
  public launchAction:boolean = false;

  public constraintToLoad = {date:new Date(), constraint:null, isServiceConstraint:true};


  public startTimeInputUpdate = new Subject<string>();
  public endTimeInputUpdate = new Subject<string>();

  constructor(private userService:UserService, public global:GlobalService, public activeModal: NgbActiveModal, private modalService: NgbModal) {
    this.loadData();
    this.startTimeInputUpdate.pipe(
      debounceTime(500),
      distinctUntilChanged())
      .subscribe(value => {
        this.changeStartTime(this.formConstraintCalendar);
      });
    this.endTimeInputUpdate.pipe(
      debounceTime(500),
      distinctUntilChanged())
      .subscribe(value => {
        this.changeEndTime(this.formConstraintCalendar);
      });
  }

  loadData(){
    this.launchAction = true;
    this.userService.get('bookingsSettings/:token', function(data){
      this.launchAction = false;
      this.settings = data.settings;
      this.skeletonServiceConstraint = data.skeletonServiceConstraint;
      this.skeletonMemberConstraint = data.skeletonMemberConstraint;
      this.services = data.services;
      this.members = data.members;
      this.clearFormConstraintCalendar();
      this.loadConstraintCalendar(this.constraintToLoad.date, this.constraintToLoad.constraint, this.constraintToLoad.isServiceConstraint);

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

  setConstraintCalendar(date, constraint = null, isServiceConstraint = true){
    this.constraintToLoad.date = date;
    this.constraintToLoad.constraint = constraint;
    this.constraintToLoad.isServiceConstraint = isServiceConstraint;
  }

  loadConstraintCalendar(date, constraint = null, isServiceConstraint = true){
    if(constraint == null) constraint = this.skeletonServiceConstraint;
    constraint = JSON.parse(JSON.stringify(constraint));
    constraint.startDate = new Date(constraint.startDate);
    if(constraint.endDate == null) constraint.endDate = new Date(constraint.startDate);
    else constraint.endDate = new Date(constraint.endDate);
    if(isServiceConstraint) this.formConstraintCalendar.serviceConstraint = constraint;
    else this.formConstraintCalendar.memberConstraint = constraint;
    this.formConstraintCalendar.isServiceConstraint = isServiceConstraint;
    if(constraint.id > -1){
      this.formConstraintCalendar.date = this.toNgbDate(constraint.startDate);
      this.formConstraintCalendar.dateEnd = this.toNgbDate(constraint.endDate);
      this.formConstraintCalendar.manyDays = !this.isSameDay(constraint.startDate, constraint.endDate);
      this.formConstraintCalendar.time = this.dateToTime(constraint.startDate);
      this.formConstraintCalendar.timeEnd = this.dateToTime(constraint.endDate);
    }
    else {
      this.formConstraintCalendar.date = this.toNgbDate(date);
      this.formConstraintCalendar.dateEnd = this.toNgbDate(date);
      this.formConstraintCalendar.time = '12:00';
      this.formConstraintCalendar.timeEnd = '13:00';
    }
  }

  isNewConstraint(){
    return this.formConstraintCalendar.serviceConstraint == null || this.formConstraintCalendar.memberConstraint == null ||  this.formConstraintCalendar.serviceConstraint.id == -1 && this.formConstraintCalendar.memberConstraint.id == -1;
  }

  getServiceName(service){
    var name = this.global.getTextByLocale(service.name);
    if(this.settings != null && this.settings.places != null && this.settings.places.length > 0 && service.places.length > 0){
      var placesText = '';
      for(var i = 0; i < service.places.length; i++){
        var place = this.settings.places.find(element => element.id == service.places[i]);
        if(i > 0) placesText += ', ';
        placesText += this.global.getTextByLocale(place.name);
      }
      name = '['+placesText+'] ' + name;
    }
    return name;
  }

  getMemberName(member){
    var name = this.global.htmlSpecialDecode(member.nickname);
    if(this.settings != null && this.settings.places != null && this.settings.places.length > 0 && member.places.length > 0){
      var placesText = '';
      for(var i = 0; i < member.places.length; i++){
        var place = this.settings.places.find(element => element.id == member.places[i]);
        if(i > 0) placesText += ', ';
        placesText += this.global.getTextByLocale(place.name);
      }
      name = '['+placesText+'] ' + name;
    }
    return name;
  }

  getServiceNameById(idService){
    var service = this.services.find(element => { if(element.id == idService) return element; });
    if(service == null) return '';
    return this.getServiceName(service);
  }

  getMemberNameById(idMember){
    var member = this.members.find(element => { if(element.id == idMember) return element; });
    if(member == null) return '';
    return this.getMemberName(member);
  }

  isOkFormConstraintCalendar(){
    return !this.launchAction && this.formConstraintCalendar.serviceConstraint!=null && this.formConstraintCalendar.memberConstraint!=null &&
    this.generateEndDate(this.formConstraintCalendar).getTime() > this.generateStartDate(this.formConstraintCalendar).getTime() &&
    ((this.formConstraintCalendar.serviceConstraint.idService != null && this.formConstraintCalendar.serviceConstraint.idService != -1 && this.formConstraintCalendar.isServiceConstraint) || (this.formConstraintCalendar.memberConstraint.idMember != null && this.formConstraintCalendar.memberConstraint.idMember != -1 && !this.formConstraintCalendar.isServiceConstraint));
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

  clearFormConstraintCalendar(){
    this.formConstraintCalendar = {manyDays:false, date:this.toNgbDate(new Date()), dateEnd:this.toNgbDate(new Date()), time:'12:00', timeEnd:'13:00', serviceConstraint:JSON.parse(JSON.stringify(this.skeletonServiceConstraint)), memberConstraint:JSON.parse(JSON.stringify(this.skeletonMemberConstraint)), isServiceConstraint:true};
  }

  dismiss(){
    this.activeModal.dismiss(0);
  }

  close(value){
    this.activeModal.close(value);
  }

  editConstraintCalendar(){
    this.launchAction = true;
    var newConstraint = JSON.parse(JSON.stringify(this.formConstraintCalendar.serviceConstraint));
    if(!this.formConstraintCalendar.isServiceConstraint) newConstraint = JSON.parse(JSON.stringify(this.formConstraintCalendar.memberConstraint));
    newConstraint.startDate = formatDate(this.generateStartDate(this.formConstraintCalendar), 'yyyy-MM-dd HH:mm:ss', 'fr');
    newConstraint.endDate = formatDate(this.generateEndDate(this.formConstraintCalendar), 'yyyy-MM-dd HH:mm:ss', 'fr');
    this.userService.post('constraintsCalendar/:token', {constraint:JSON.stringify(newConstraint), isServiceConstraint:this.formConstraintCalendar.isServiceConstraint}, (data) => {
      if(newConstraint.id == -1){
        this.close({type:'added', data:data, isServiceConstraint:this.formConstraintCalendar.isServiceConstraint});
      }
      else {
        this.close({type:'edited', data:data, isServiceConstraint:this.formConstraintCalendar.isServiceConstraint});
      }
      this.launchAction = false;
    }, (error) => {
      this.launchAction = false;
      var text = 'Impossible de sauvegarder la contrainte';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  deleteConstraintCalendar(){
    swal({
      title: 'Êtes-vous sûr ?',
      text: 'Êtes-vous sûr de supprimer cette contrainte ?',
      icon: 'warning',
      buttons: ['Annuler', 'Supprimer'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        this.launchAction = true;
        var newConstraint = JSON.parse(JSON.stringify(this.formConstraintCalendar.serviceConstraint));
        if(!this.formConstraintCalendar.isServiceConstraint) newConstraint = JSON.parse(JSON.stringify(this.formConstraintCalendar.memberConstraint));
        this.userService.delete('constraintsCalendar/:token/' + newConstraint.id + '/' + (this.formConstraintCalendar.isServiceConstraint?1:0), (data) => {
          this.launchAction = false;
          this.close({type:'deleted', success:data.result, data:newConstraint, isServiceConstraint:this.formConstraintCalendar.isServiceConstraint});
        }, (error) => {
          this.launchAction = false;
          var text = 'Impossible de supprimer la contraint';
          if(error != null && error.message != null && error.message.length > 0){
            text += ' (' + error.message + ')';
          }
          swal({ title: 'Erreur', text: text, icon: 'error'});
        });
      }
    });
  }

}
