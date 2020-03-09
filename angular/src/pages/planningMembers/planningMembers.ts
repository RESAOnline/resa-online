import { OnInit, Component  } from '@angular/core';
import { NgbTabChangeEvent } from '@ng-bootstrap/ng-bootstrap';
import { DatePipe, formatDate } from '@angular/common';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { AbstractPlanning } from '../../others/AbstractPlanning';
import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { NotificationComponent } from '../../components/notification/notification';
import { ConstraintComponent } from '../../components/constraint/constraint';
import { ReceiptComponent } from '../../components/receipt/receipt';

declare var swal: any;
declare var $: any;

@Component({
  selector: 'planningMembers',
  templateUrl: 'planningMembers.html',
  styleUrls:['./planningMembers.css']
})

export class PlanningMembersComponent extends AbstractPlanning implements OnInit {

  public members = [];

  constructor(userService:UserService, navService:NavService, global:GlobalService, datePipe:DatePipe, private modalService: NgbModal) {
    super('Membres', userService, navService, global, datePipe);
    this.date = new Date(this.global.currentDate);
    this.ngbDate = this.toNgbDate(this.date);
  }

  firstLoad(){
    this.userService.get('planningSettings/:token', function(data){
      this.settings = data.settings;
      this.planning.min = this.settings.calendar_start_time;
      this.planning.max = this.settings.calendar_end_time;
      this.loadDataWithDate(this.date);
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  ngOnInit(): void {
    this.firstLoad();
  }


  goEditBooking(booking){ this.navService.changeRoute('planningBookings', {booking:booking.id}); }
  isRESAManager(){
    return this.userService.getCurrentUser().role == 'administrator' || this.userService.getCurrentUser().role == 'RESA_Manager';
  }

  updateDate(date){
    this.loadDataWithDate(date);
  }

  loadDataWithDate(date){
    this.date = new Date(date);
    this.currentDate = new Date(date);
    this.global.setCurrentDate(date);
    this.processingData = true;
    this.userService.post('planningMembers/:token', {date: formatDate(this.date, 'yyyy-MM-dd', 'fr')}, function(data){
      this.processingData = false;
      this.planning.min = data.minHours;
      this.planning.max = data.maxHours;
      if(date != this.date){
        this.members = data.members;
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
   * load appointments
   */
  loadDataAppointments(appointment){
    this.loadDataAppointmentsLaunched = true;
    this.userService.get('appointment/:token/' + appointment.id, function(data){
      this.loadDataAppointmentsLaunched = false;
      if(this.selectedTimeslot != null &&
        this.selectedTimeslot.startDate == appointment.startDate &&
        this.selectedTimeslot.endDate == appointment.endDate){
        this.appointments = [data];
      }
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  getTagById(idTag){
    var tag:any = {name:'', color:''};
    if(this.settings != null && this.settings.custom_tags != null){
      tag = this.settings.custom_tags.find(element => element.id == idTag);
    }
    return tag;
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

  openNotificationDialog(content){
    const modalRef = this.modalService.open(NotificationComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.setBooking(this.selectedBooking);
    modalRef.componentInstance.setCustomer(this.selectedBooking.customer);
  }

  openReceipt(){
    const modalRef = this.modalService.open(ReceiptComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.loadBookingReceipt(this.selectedBooking);
  }

  openPaymentStateDialog(content){
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result){
        this.changePaymentStateBooking();
      }
    }, (reason) => {

    });
  }

  selectTimeslotConstraint(timeslot, id){
    if(timeslot.type == 'timeslot'){
      this.selectTimeslot(timeslot, id)
    }
    else if(timeslot.type == 'constraint'){
      this.openConstraintCalendarDialog(timeslot);
    }
  }

  openConstraintCalendarDialog(timeslot = null){
    if(timeslot == null) timeslot = {type:'constraint', constraint:null};
    if(timeslot.type == 'constraint'){
      const modalRef = this.modalService.open(ConstraintComponent, { windowClass: 'mlg' });
      modalRef.componentInstance.setConstraintCalendar(this.date, timeslot.constraint, false);
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


}
