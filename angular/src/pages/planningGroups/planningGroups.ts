import {Input, Output, OnChanges, OnInit, Component, Inject, EventEmitter, SimpleChanges, ViewChild  } from '@angular/core';
import { NgbTabChangeEvent } from '@ng-bootstrap/ng-bootstrap';
import { DatePipe, formatDate } from '@angular/common';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { AbstractPlanning } from '../../others/AbstractPlanning';
import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { NotificationComponent } from '../../components/notification/notification';
import { ConstraintComponent } from '../../components/constraint/constraint';
import { InfoCalendarComponent } from '../../components/infoCalendar/infoCalendar';
import { ReceiptComponent } from '../../components/receipt/receipt';
import { BookingDialogComponent } from '../../components/bookingDialog/bookingDialog';

declare var swal: any;
declare var $: any;

@Component({
  selector: 'planningGroups',
  templateUrl: 'planningGroups.html',
  styleUrls:['./planningGroups.css']
})

export class PlanningGroupsComponent extends AbstractPlanning implements OnInit {

  public services = [];
  public groups = [];
  public members = [];
  public infoCalendars = [];
  public groupsWithoutMembers = [];
  public participantsGroupByDates = [];
  public participantsNotInGroup = [];

  public formGroup = {
    groupSelected:null,
    idPlace:null,
    service:null,
    startDate: '',
    endDate: '',
    idParticipants:[],
    formManyDates:[],
    availableMembers:[],
    addIdMember:-1,
    participantsGroupByParameters:[],
    groups:[],
    allGroups:[],
    numberAppointments:0,
    loadAppointments:false,
  };

  public formGroupEdit = {
    oldGroup:null,
    newGroup:null,
    service:null,
    startDate: '',
    endDate: '',
    formManyDates:[],
    loadAppointments:false
  }

  public formMemberGroups = {
    member:null,
    groups:[],
    loadAppointments:false
  }

  public launchAction:boolean = false;

  constructor(userService:UserService, navService:NavService, global:GlobalService, datePipe:DatePipe, private modalService: NgbModal) {
    super('Groupes', userService, navService, global, datePipe);
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
    this.userService.post('planningGroups/:token', {date: formatDate(this.date, 'yyyy-MM-dd', 'fr')}, function(data){
      this.processingData = false;
      this.planning.min = data.minHours;
      this.planning.max = data.maxHours;
      if(date != this.date){
        this.services = data.services;
        this.groups = data.groups;
        this.members = data.members;
        this.groupsWithoutMembers = data.groupsWithoutMembers;
        this.participantsGroupByDates = data.participants;
        this.infoCalendars = data.infoCalendars;
        this.generateParticipantsNotInGroup();
        this.calculatedRealNumberAttribuatedForAllGroups();
      }
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  calculatedRealNumberAttribuated(group){
    group.attribuated = 0;
    for(var i = 0; i < group.idParticipants.length; i++){
      var idParticipant = group.idParticipants[i];
      for(var j = 0; j < this.participantsGroupByDates.length; j++){
        var participantsGroupByDates = this.participantsGroupByDates[j];
        if(participantsGroupByDates.startDate == group.startDate && participantsGroupByDates.endDate == group.endDate &&
          this.participantsGroupByDates[j].participants.findIndex(element => element.uri == idParticipant &&
          element.idService == group.idService) > -1){
          group.attribuated++;
        }
      }
    }
  }

  calculatedRealNumberAttribuatedForAllGroups(){
    this.groups.map(element => { this.calculatedRealNumberAttribuated(element); });
    this.groupsWithoutMembers.map(element => { this.calculatedRealNumberAttribuated(element); });
    this.members.map(member => {
      member.groups.map(element => { if(element.type === 'group'){ this.calculatedRealNumberAttribuated(element); } });
    });
  }

  updateAllGroups(group){
    this.calculatedRealNumberAttribuated(group);
    if(this.formGroup.groupSelected != null && this.formGroup.groupSelected.id == group.id){
      this.formGroup.groupSelected = group;
    }
    for(var j = 0; j < this.groups.length; j++){
      var localGroup = this.groups[j];
      if(localGroup.id == group.id){
        this.groups[j] = group;
        break;
      }
    }
    for(var j = 0; j < this.groupsWithoutMembers.length; j++){
      var localGroup = this.groupsWithoutMembers[j];
      if(localGroup.id == group.id){
        this.groupsWithoutMembers[j] = group;
        break;
      }
    }
    for(var j = 0; j < this.members.length; j++){
      var member = this.members[j];
      for(var k = 0; k < member.groups.length; k++){
        var localGroup = member.groups[k];
        if(localGroup.id == group.id  && localGroup.type === 'group'){
          member.groups[k] = group;
          break;
        }
      }
    }
  }

  deleteAllGroups(group){
    this.groups = this.groups.filter(element => {
      return element.id != group.id;
    });
    this.groupsWithoutMembers = this.groupsWithoutMembers.filter(element => {
      return element.id != group.id;
    });
    for(var j = 0; j < this.members.length; j++){
      var member = this.members[j];
      member.groups = member.groups.filter(element => {
        return element.type !== 'group' || element.id != group.id;
      });
    }
  }

  generateParticipantsNotInGroup(){
    this.participantsNotInGroup = [];
    for(var i = 0; i < this.participantsGroupByDates.length; i++){
      var participantsGroupByDate = this.participantsGroupByDates[i];
      for(var j = 0; j < participantsGroupByDate.participants.length; j++){
        var participant = participantsGroupByDate.participants[j];
        var find = false;
        for(var k = 0; k < this.groups.length; k++){
          var group = this.groups[k];
          if(group.idService == participant.idService && group.idParticipants.indexOf(participant.uri) > -1){
            find = true;
            break;
          }
        }
        if(!find){
          var participantsNotInGroup = this.participantsNotInGroup.find((element) => {
            return element.startDate == participantsGroupByDate.startDate &&
              element.endDate == participantsGroupByDate.endDate &&
              element.idService == participant.idService;
          });
          if(participantsNotInGroup == null){
            participantsNotInGroup = {
              startDate: participantsGroupByDate.startDate,
              endDate: participantsGroupByDate.endDate,
              idService:participant.idService,
              idParticipants:[]
            }
            this.participantsNotInGroup.push(participantsNotInGroup);
          }
          participantsNotInGroup.idParticipants.push(participant.uri);
        }
      }
    }
  }

  getServiceNameById(idService){
    var service = this.services.find(element => element.id == idService);
    if(service != null) return this.getServiceName(service);
    return '';
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

  getPlaceName(idPlace){
    var name = idPlace;
    var place = this.settings.places.find(element => element.id == idPlace);
    if(place != null){
      name = this.global.getTextByLocale(place.name);
    }
    return name;
  }

  getServiceName(service){
    var name = this.global.getTextByLocale(service.name);
    if(service.places.length > 0){
      name = '['+ this.getListPlaces(service.places) +'] ' + name;
    }
    return name;
  }

  getMemberName(member){
    var name = member.nickname;
    if(member.places.length > 0){
      name = '['+ this.getListPlaces(member.places) +'] ' + name;
    }
    return name;
  }

  getDisplayMembers(group){
    var result = '';
    for(var i = 0; i < group.idMembers.length; i++){
      var idMember = group.idMembers[i];
      var memberAux = this.members.find(element => element.id == idMember);
      if(memberAux != null){
        if(result != '') result += ', ';
        result += memberAux.nickname;
      }
    }
    if(result.length > 25){
      result = result.slice(0, 25) + '...';
    }
    return result;
  }

  getTagById(idTag){
    var tag:any = {name:'', color:''};
    if(this.settings != null && this.settings.custom_tags != null){
      tag = this.settings.custom_tags.find(element => element.id == idTag);
    }
    return tag;
  }

  getMemberById(idMember){
    var member = this.members.find(element => element.id == idMember);
    return member;
  }

  getNote(infoCalendar){
    var note = this.global.htmlSpecialDecode(infoCalendar.note);
    if(infoCalendar.idPlaces.length > 0){
      note = '['+ this.getListPlaces(infoCalendar.idPlaces.split(',')) +'] ' + note;
    }
    return note;
  }

  formatGroupDates(date){
    var tokens = date.split('-');
    var dateFormated = new Date(date);
    if(tokens.length == 3){
      if(tokens[2].length > 2){
        tokens[2] = tokens[2].split('T')[0];
      }
      dateFormated = new Date(tokens[0], tokens[1] - 1, tokens[2]);
    }
    return dateFormated;
  }

  getGroupDates(service, startDate){
    startDate = this.global.clearTime(startDate);
    for(var j = 0; j < service.serviceAvailabilities.length; j++){
      var serviceAvailability = service.serviceAvailabilities[j];
      for(var i = 0; i < serviceAvailability.groupDates.length; i++){
        var dates = serviceAvailability.groupDates[i];
        var currentStartDate = this.formatGroupDates(dates.startDate);
        var currentEndDate = this.formatGroupDates(dates.endDate);
        if(currentStartDate <= startDate && startDate <= currentEndDate){
          return {
            startDate:currentStartDate,
            endDate:currentEndDate
          };
        }
      }
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

  getParticipantParametersById(idParticipantsParameter){
    return this.settings.form_participants_parameters.find(element => element.id == idParticipantsParameter);
  }

  generateParticipantsGroupByParameters(appointments, formGroup){
    var participantsGroupByDates = this.participantsGroupByDates.find(element => element.startDate == formGroup.startDate && element.endDate ==  formGroup.endDate);
    if(participantsGroupByDates != null){
      for(var i =  0; i < formGroup.idParticipants.length; i++){
        var participants = participantsGroupByDates.participants.filter(element => element.uri == formGroup.idParticipants[i]);
        for(var j = 0; j < participants.length; j++){ //BECAUSE uri not unique
          var participant = participants[j]
          if(participant != null){
            var participantsParameters = this.settings.form_participants_parameters.find(element => element.id == participant.participantsParameter);
            if(participantsParameters == null) participantsParameters = this.settings.form_participants_parameters[0];
            if(formGroup.participantsGroupByParameters == null) formGroup.participantsGroupByParameters = [];
            var participantsGroup = formGroup.participantsGroupByParameters.find(element => element.id == participantsParameters.id);
            if(participantsGroup == null){
              var participantsParametersCopy = JSON.parse(JSON.stringify(participantsParameters));
              participantsGroup = {
                id:participantsParametersCopy.id,
                parameters:participantsParametersCopy,
                participants:[]
              }
              formGroup.participantsGroupByParameters.push(participantsGroup);
            }
            var participantCopy = JSON.parse(JSON.stringify(participant));
            var appointment = appointments.find(element => element.idAppointment == participantCopy.idAppointment);
            if(appointment != null){
              participantCopy.idPlace = appointment.idPlace;
              participantCopy.idBooking = appointment.idCreation!=-1?appointment.idCreation:appointment.id;
              participantCopy.state = this.settings.statesList.find(element => element.id == appointment.state).filterName;
              participantCopy.askPaymentsStatus = appointment.askPaymentsStatus;
              participantCopy.customer = appointment.customer;
              participantCopy.actualIdGroup = formGroup.groupSelected?formGroup.groupSelected.id:-1;
              participantCopy.actualGroupName = formGroup.groupSelected?formGroup.groupSelected.name:'';
              participantCopy.actualDisplayGroupName = formGroup.groupSelected?formGroup.groupSelected.displayName:'';
              participantCopy.newGroupName = '';
              participantCopy.tags = appointment.tags;
              participantsGroup.participants.push(participantCopy);
            }
          }
        }
      }
    }
  }

  generateGroupsForParticipants(){
    this.formGroup.groups = [];
    for(var i = 0; i < this.groups.length; i++){
      var group = this.groups[i];
      if(group.idService == this.formGroup.service.id &&
          group.startDate == this.formGroup.startDate &&
            group.endDate == this.formGroup.endDate){
        this.formGroup.groups.push(group);
      }
    }
  }

  isSameParticipantGroup(participant, group){
    return (!group.oneByBooking && group.name == participant.actualGroupName) || (group.oneByBooking && group.id == participant.actualIdGroup);
  }

  isApplyInSameDate(startDate, endDate, dates){
    return dates.find((element) => {
      if(element.startDate == startDate && element.endDate == endDate){
        return element;
      }
    }) != null;
  }

  /**
   *
   */
  generateAvailableMembers(){
    this.formGroup.addIdMember = -1;
    this.formGroup.availableMembers = [];
    for(var i = 0; i < this.members.length; i++){
      var member = this.members[i];
      var idMember = member.id;
      if(member.places.indexOf(this.formGroup.idPlace) > -1){
        var startDate = new Date(this.formGroup.groupSelected.startDate);
        var endDate = new Date(this.formGroup.groupSelected.endDate);
        var actualGroupName = '';
        var actualDisplayGroupName = '';
        var sameAppointment = true;
        var alreadyInDate = null;
        var found = false;
        for(var j = 0; j < member.groups.length; j++){
          var group = member.groups[j];
          var startDateB = new Date(group.startDate);
          var endDateB = new Date(group.endDate);
          if((startDate < startDateB && startDateB < endDate) ||
            (startDate < endDateB && endDateB < endDate) ||
            (startDateB <= startDate && endDate <= endDateB) ||
            (startDateB < startDate && startDate < endDateB)){
            found = true;
            if(actualGroupName == '' || (this.formGroup.groupSelected!=null && group.id == this.formGroup.groupSelected.id && group.type === 'group')){
              if(group.type === 'group'){
                actualGroupName = group.name;
                actualDisplayGroupName = group.displayName;
                sameAppointment = group.idService == this.formGroup.groupSelected.idService;
                alreadyInDate = this.global.parseDate(group.startDate);
              }
              else if(group.type === 'constraint'){
                actualGroupName = '[Contrainte]';
                actualDisplayGroupName = '[Contrainte]';
                sameAppointment = false;
                alreadyInDate = this.global.parseDate(group.startDate);
              }
            }
          }
        }
        var newGroupName = this.formGroup.groupSelected.name;
        if(actualGroupName == newGroupName){
          newGroupName = '';
        }
        if(!found){
          for(var j = 0; j < this.formGroup.allGroups.length; j++){
            var group = this.formGroup.allGroups[j];
            if((group.type === 'group' && group.idMembers.findIndex(element => parseInt(element) == idMember) > -1)
              || (group.type === 'constraint' && group.idMember === idMember)){
              found = true;
              if(group.type === 'group'){
                actualGroupName = group.name;
                actualDisplayGroupName = group.displayName;
                sameAppointment = group.idService == this.formGroup.groupSelected.idService;
                alreadyInDate = this.global.parseDate(group.startDate);
              }
              else if(group.type === 'constraint'){
                actualGroupName = '[Contrainte]';
                actualDisplayGroupName = '[Contrainte]';
                sameAppointment = false;
                alreadyInDate = this.global.parseDate(group.startDate);
              }
              break;
            }
          }
        }
        this.formGroup.availableMembers.push({
          id:member.id,
          places:member.places,
          nickname:member.nickname,
          actualGroupName:actualGroupName,
          actualDisplayGroupName:actualDisplayGroupName,
          newGroupName:newGroupName,
          sameAppointment:sameAppointment,
          alreadyInDate:alreadyInDate
        });
      }
    }
  }

  /**
   *
   */
  openGroupOrContraints(data, content){
    if(data.type === 'group'){
      this.openGroup(data, content);
    }
    else if(data.type === 'constraint'){
      this.openConstraintDialog(data.constraint);
    }
  }

  /**
   *
   */
  openGroup(data, content){
    var groupSelected = this.groups.find(element => element.id == data.id);
    var service =  this.services.find(element => element.id == data.idService);
    var idPlace = (service.places.length > 0)?service.places[0]:'';
    this.formGroup = {
      groupSelected : groupSelected,
      idPlace: idPlace,
      service : service,
      startDate: data.startDate,
      endDate: data.endDate,
      idParticipants:data.idParticipants,
      formManyDates: [],
      availableMembers:[],
      addIdMember:-1,
      participantsGroupByParameters :[],
      groups:[],
      allGroups:[],
      numberAppointments:-1,
      loadAppointments:false
    }
    if(service.typeAvailabilities == 1){
      var dates = this.getGroupDates(service, data.startDate);
      if(dates != null){
        var localDate = new Date(dates.startDate);
        var startDate = new Date(data.startDate);
        var endDate = new Date(data.endDate);
        while(localDate <= dates.endDate){
          var startTimeslot = new Date(localDate);
          var endTimeslot = new Date(localDate);
          startTimeslot.setHours(startDate.getHours());
          startTimeslot.setMinutes(startDate.getMinutes());
          startTimeslot.setSeconds(startDate.getSeconds());
          endTimeslot.setHours(endDate.getHours());
          endTimeslot.setMinutes(endDate.getMinutes());
          endTimeslot.setSeconds(endDate.getSeconds());
          this.formGroup.formManyDates.push({
            checked:true,
            actual: (this.global.clearTime(data.startDate).getTime() == this.global.clearTime(new Date(startTimeslot)).getTime()),
            startDate:startTimeslot,
            endDate:endTimeslot
          });
          localDate.setDate(localDate.getDate() + 1);
        }
      }
    }
    else {
      this.formGroup.formManyDates.push({
        checked:true,
        actual: (this.global.clearTime(this.formGroup.startDate).getTime() == this.global.clearTime(new Date()).getTime()),
        startDate:this.global.parseDate(this.formGroup.startDate),
        endDate:this.global.parseDate(this.formGroup.endDate)
      });
    }
    this.generateGroupsForParticipants();
    this.modalService.open(content, { windowClass: 'max', backdrop:'static' }).result.then((result) => {
      if(result){

      }
    }, (reason) => {

    });
    var allDates = [];
    for(var i = 0; i < this.formGroup.formManyDates.length; i++){
      var date = this.formGroup.formManyDates[i];
      if(date.checked){
        allDates.push({
          startDate: formatDate(date.startDate, 'yyyy-MM-dd HH:mm:ss', 'fr'),
          endDate: formatDate(date.endDate, 'yyyy-MM-dd HH:mm:ss', 'fr')
        });
      }
    }
    this.formGroup.loadAppointments = true;
    this.userService.post('openGroup/:token', {
      startDate: data.startDate,
      endDate: data.endDate,
      idService: data.idService,
      dates: allDates
    }, function(data){
      this.formGroup.loadAppointments = false;
      this.formGroup.numberAppointments = data.appointments.length;
      this.formGroup.allGroups = data.groups;
      this.generateParticipantsGroupByParameters(data.appointments, this.formGroup);
      if(this.formGroup.groupSelected != null){
        this.generateAvailableMembers();
      }
    }.bind(this), function(error){
      this.formGroup.loadAppointments = false;
      var text = 'Impossible de récupérer les données du groupe';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  launchSwitchParticipant(participant){
    if(participant.actualGroupName != ''){
      var text = 'Êtes-vous sûr de retirer ce participant de ce groupe ?';
      if(participant.newGroupName != ''){
        var group = this.formGroup.groups.find(element => element.name == participant.newGroupName);
        var displayName = group?group.displayName:participant.newGroupName;
        text = 'Êtes-vous sûr de retirer ce participant de ce groupe et de l\'attribuer au groupe ' + displayName + ' ?';
      }
      swal({
        title: 'Êtes-vous sûr ?',
        text: text,
        icon: 'warning',
        buttons: ['Annuler', 'Oui'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          this.switchParticipant(participant);
        }
      });
    }
    else {
      this.switchParticipant(participant);
    }
  }

  switchParticipant(participant){
    var dates = [];
    for(var i = 0; i < this.formGroup.formManyDates.length; i++){
      var date = this.formGroup.formManyDates[i];
      if(date.checked){
        dates.push({
          startDate: formatDate(date.startDate, 'yyyy-MM-dd HH:mm:ss', 'fr'),
          endDate: formatDate(date.endDate, 'yyyy-MM-dd HH:mm:ss', 'fr')
        });
      }
    }
    this.formGroup.loadAppointments = true;
    this.userService.post('switchParticipant/:token', {
      dates:dates,
      startDate:this.formGroup.startDate,
      endDate:this.formGroup.endDate,
      idService:this.formGroup.service.id,
      idPlace:participant.idPlace,
      participant:participant,
      idGroup:(this.formGroup.groupSelected!=null && this.formGroup.groupSelected.oneByBooking)?this.formGroup.groupSelected.id:-1
    }, function(data){
      if(data.length > 0){
        this.formGroup.loadAppointments = false;
        for(var i = 0; i < data.length; i++){
          var group = data[i];
          if(group.startDate == this.formGroup.startDate &&
            group.endDate == this.formGroup.endDate){
            this.updateAllGroups(group);
          }
        }
        //remove participant
        if(this.isApplyInSameDate(this.formGroup.startDate, this.formGroup.endDate, dates)){
          for(var i = 0; i < this.formGroup.participantsGroupByParameters.length; i++){
            var participantsGroup = this.formGroup.participantsGroupByParameters[i];
            participantsGroup.participants = participantsGroup.participants.filter(element => {
              return element.uri != participant.uri;
            });
          }
        }
        this.generateParticipantsNotInGroup();
        swal({ title: 'OK', text: 'Modifications effectuées', icon: 'success', buttons: false, timer:1000});
      }
    }.bind(this), function(error){
      this.formGroup.loadAppointments = false;
      var text = 'Impossible de modifier l\'attribution du participant';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }


  launchSwitchMember(idMember){
    var member = this.formGroup.availableMembers.find(element => element.id == idMember);
    var actualGroupName = member.actualGroupName;
    var newGroupName = member.newGroupName;
    if(actualGroupName != ''){
      var text = 'Êtes-vous sûr de retirer ce ' + this.global.getTextByLocale(this.settings.staff_word_single) + ' de ce groupe ?';
      if(newGroupName != ''){
        var group = this.formGroup.groups.find(element => element.name == newGroupName);
        var displayName = group?group.displayName:newGroupName;
        text = 'Êtes-vous sûr de retirer ce ' + this.global.getTextByLocale(this.settings.staff_word_single) + ' de ce groupe et de l\'attribuer au groupe ' + displayName + ' ?';
      }
      swal({
        title: 'Êtes-vous sûr ?',
        text: text,
        icon: 'warning',
        buttons: ['Annuler', 'Oui'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          this.switchMember(idMember);
        }
      });
    }
    else {
      this.switchMember(idMember);
    }
  }

  switchMember(idMember){
    var dates = [];
    for(var i = 0; i < this.formGroup.formManyDates.length; i++){
      var date = this.formGroup.formManyDates[i];
      if(date.checked){
        dates.push({
          startDate: formatDate(date.startDate, 'yyyy-MM-dd HH:mm:ss', 'fr'),
          endDate: formatDate(date.endDate, 'yyyy-MM-dd HH:mm:ss', 'fr')
        });
      }
    }
    var member = this.formGroup.availableMembers.find(element => element.id == idMember);
    var actualGroupName = member.actualGroupName;
    var newGroupName = member.newGroupName;
    if(this.isApplyInSameDate(this.formGroup.startDate, this.formGroup.endDate, dates)){
      if(member.newGroupName != '' && member.newGroupName == this.formGroup.groupSelected.name) {
        this.formGroup.groupSelected.idMembers.push(member.id);
      }
      else {
        this.formGroup.groupSelected.idMembers = this.formGroup.groupSelected.idMembers.filter(element => element != member.id);
      }
      this.formGroup.addIdMember = -1;
      member.actualGroupName = member.newGroupName;
      member.newGroupName = '';
    }
    this.formGroup.loadAppointments = true;
    this.userService.post('switchMember/:token', {
      dates:dates,
      startDate:this.formGroup.startDate,
      endDate:this.formGroup.endDate,
      idService:this.formGroup.service.id,
      idPlace:this.formGroup.groupSelected.idPlace,
      member:{
        id:member.id,
        actualGroupName:actualGroupName,
        newGroupName:newGroupName
      },
      idGroup:(this.formGroup.groupSelected!=null && this.formGroup.groupSelected.oneByBooking)?this.formGroup.groupSelected.id:-1
    }, function(data){
      this.formGroup.loadAppointments = false;
      for(var i = 0; i < data.length; i++){
        var group = data[i];
        if(group.startDate == this.formGroup.startDate &&
          group.endDate == this.formGroup.endDate){
          //remove members
          if(group.idMembers.length == 0) this.addGroupWithoutMembers(group);
          if(group.idMembers.length == 1) this.removeGroupWithoutMembers(group);
          this.updateAllGroups(group);
          if(group.name == actualGroupName){
            var memberAux = this.members.find(element => element.id == member.id);
            memberAux.groups = memberAux.groups.filter(element => element.id != group.id);
          }
          if(group.name == newGroupName){
            var memberAux = this.members.find(element => element.id == member.id);
            this.addMemberGroups(group, memberAux);
          }
        }
        for(var j = 0; j < this.formGroup.allGroups.length; j++){
          var localGroup = this.formGroup.allGroups[j];
          if(localGroup.id == group.id){
            this.formGroup.allGroups[j] = group;
            break;
          }
        }
      }
      this.generateAvailableMembers();
      swal({ title: 'OK', text: 'Modifications effectuées', icon: 'success', buttons: false, timer:1000});
    }.bind(this), function(error){
      this.formGroup.loadAppointments = false;
      var text = 'Impossible de modifier l\'attribution du membre';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  getAvailableMemberById(idMember){
    return this.formGroup.availableMembers.find(element => element.id == idMember);
  }

  addGroupCalculateLevel(group, groups){
    var startDateB = this.global.parseDate(group.startDate);
    var endDateB = this.global.parseDate(group.endDate);
    var lastLevel = -1;
    groups.map(element => {
      var startDate = this.global.parseDate(element.startDate);
      var endDate = this.global.parseDate(element.endDate);
      if((startDate < startDateB && startDateB < endDate) ||
        (startDate < endDateB && endDateB < endDate) ||
        (startDateB <= startDate && endDate <= endDateB) ||
        (startDateB < startDate && startDate < endDateB)){
        lastLevel = Math.max(element.level, lastLevel);
      }
    });
    group.level = lastLevel + 1;
    groups.push(group);
    this.orderGroupsByLevel(groups);
  }

  addMemberGroups(group, member){
    this.addGroupCalculateLevel(group, member.groups);
  }

  addGroupWithoutMembers(group){
    this.addGroupCalculateLevel(group, this.groupsWithoutMembers);
  }

  removeGroupWithoutMembers(group){
    group = this.groupsWithoutMembers.find(element => element.id == group.id);
    if(group == null) return;
    var startDateB = this.global.parseDate(group.startDate);
    var endDateB = this.global.parseDate(group.endDate);
    var lastLevel = group.level;
    this.groupsWithoutMembers = this.groupsWithoutMembers.filter(element => element.id != group.id);
    this.groupsWithoutMembers.map(element => {
      var startDate = this.global.parseDate(element.startDate);
      var endDate = this.global.parseDate(element.endDate);
      if((startDate < startDateB && startDateB < endDate) ||
        (startDate < endDateB && endDateB < endDate) ||
        (startDateB <= startDate && endDate <= endDateB) ||
        (startDateB < startDate && startDate < endDateB)){
        if(element.level > lastLevel){
          element.level = element.level - 1;
        }
      }
    });
    this.orderGroupsByLevel(this.groupsWithoutMembers);
  }

  orderGroupsByLevel(groups){
    groups.sort((g1, g2) => {
      if(g1.level == g2.level) {
        var startDate = this.global.parseDate(g1.startDate);
        var startDateB = this.global.parseDate(g2.startDate);
        return startDate.getTime() - startDateB.getTime();
      }
      return g1.level - g2.level;
    });
  }

  loadBooking(id){
    if(!this.formGroup.loadAppointments){
      this.formGroup.loadAppointments = true;
      this.userService.get('booking/:token/' + id, (booking) => {
        this.formGroup.loadAppointments = false;
        const modalRef = this.modalService.open(BookingDialogComponent, { windowClass: 'mlg' });
        modalRef.componentInstance.setBookingAndSettings(booking, this.settings);
      }, (error) => {
        this.formGroup.loadAppointments = false;
        var text = 'Impossible de récupérer les données de la réservation';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
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

  /**
   *
   */
  openGroupDuplicate(formGroup, content){
    this.openGroupEdit(formGroup, content, true);
    var group = JSON.parse(JSON.stringify(formGroup.groupSelected));
    this.formatGroup(group);
    this.formGroupEdit.newGroup = group;
  }

  /**
   *
   */
  openGroupEdit(formGroup, content, isNewGroup){
    var group:any = null;
    if(formGroup.groupSelected != null && !isNewGroup) group = JSON.parse(JSON.stringify(formGroup.groupSelected));
    else group = {
      name:'', presentation:'', color:'#ffffff', max:10, options:[], oneByBooking:false,
      idPlace:((formGroup.service.places.length > 0)?formGroup.service.places[0]:'')
    };
    this.formatGroup(group);
    this.formGroupEdit = {
      oldGroup : (!isNewGroup)?formGroup.groupSelected:null,
      newGroup : group,
      service : formGroup.service,
      startDate: formGroup.startDate,
      endDate: formGroup.endDate,
      formManyDates:[],
      loadAppointments:false
    }
    var service = this.formGroupEdit.service;
    if(service.typeAvailabilities == 1){
      var dates = this.getGroupDates(service, formGroup.startDate);
      if(dates != null){
        var localDate = this.global.parseDate(dates.startDate);
        var startDate = this.global.parseDate(formGroup.startDate);
        var endDate = this.global.parseDate(formGroup.endDate);
        while(localDate <= dates.endDate){
          var startTimeslot = new Date(localDate);
          var endTimeslot = new Date(localDate);
          startTimeslot.setHours(startDate.getHours());
          startTimeslot.setMinutes(startDate.getMinutes());
          startTimeslot.setSeconds(startDate.getSeconds());
          endTimeslot.setHours(endDate.getHours());
          endTimeslot.setMinutes(endDate.getMinutes());
          endTimeslot.setSeconds(endDate.getSeconds());
          this.formGroupEdit.formManyDates.push({
            checked:true,
            actual: (this.global.clearTime(formGroup.startDate).getTime() == this.global.clearTime(new Date(startTimeslot)).getTime()),
            startDate:startTimeslot,
            endDate:endTimeslot
          });
          localDate.setDate(localDate.getDate() + 1);
        }
      }
    }
    else {
      this.formGroupEdit.formManyDates.push({
        checked: true,
        actual: (this.global.clearTime(this.formGroupEdit.startDate).getTime() == this.global.clearTime(new Date()).getTime()),
        startDate:this.global.parseDate(this.formGroupEdit.startDate),
        endDate:this.global.parseDate(this.formGroupEdit.endDate)
      });
    }
    this.modalService.open(content, { windowClass: 'max', backdrop:'static' }).result.then((result) => {
      if(result){
        var dates = [];
        for(var i = 0; i < this.formGroup.formManyDates.length; i++){
          var date = this.formGroup.formManyDates[i];
          if(date.checked){
            dates.push({
              startDate: formatDate(date.startDate, 'yyyy-MM-dd HH:mm:ss', 'fr'),
              endDate: formatDate(date.endDate, 'yyyy-MM-dd HH:mm:ss', 'fr')
            });
          }
        }
        this.formGroupEdit.newGroup.options = [];
        for(var i = 0; i < this.formGroupEdit.newGroup.variableOptions.length; i++){
          var variableOption = this.formGroupEdit.newGroup.variableOptions[i];
          this.formGroupEdit.newGroup.options.push(variableOption.varname + '=' + variableOption.value);
        }
        if(this.formGroupEdit.oldGroup != null && !isNewGroup){
          this.formGroup.loadAppointments = true;
          this.userService.post('dgroup/:token', {
            dates:dates,
            idService:this.formGroupEdit.service.id,
            idPlace:this.formGroupEdit.newGroup.idPlace,
            startDate:this.formGroupEdit.startDate,
            endDate:this.formGroupEdit.endDate,
            oldGroup:this.formGroupEdit.oldGroup,
            newGroup:this.formGroupEdit.newGroup,
            idGroup:(this.formGroupEdit.oldGroup!=null && this.formGroupEdit.oldGroup.oneByBooking)?this.formGroupEdit.oldGroup.id:-1
          }, function(data){
            this.formGroup.loadAppointments = false;
            this.updateAllGroups(data);
            swal({ title: 'OK', text: 'Modifications effectuées', icon: 'success', buttons: false, timer:1000});
          }.bind(this), function(error){
            this.formGroup.loadAppointments = false;
            var text = 'Impossible de sauvegarder les données du groupe';
            if(error != null && error.message != null && error.message.length > 0){
              text += ' (' + error.message + ')';
            }
            swal({ title: 'Erreur', text: text, icon: 'error'});
          }.bind(this));
        }
        else {
          this.formGroup.loadAppointments = true;
          this.userService.put('dgroup/:token', {
            dates:dates,
            idService:this.formGroupEdit.service.id,
            idPlace:this.formGroupEdit.newGroup.idPlace,
            startDate:this.formGroupEdit.startDate,
            endDate:this.formGroupEdit.endDate,
            newGroup:this.formGroupEdit.newGroup
          }, function(data){
            this.formGroup.loadAppointments = false;
            this.calculatedRealNumberAttribuated(data);
            this.addGroupWithoutMembers(data);
            this.groups.push(data);
            this.generateGroupsForParticipants();
            swal({ title: 'OK', text: 'Modifications effectuées', icon: 'success', buttons: false, timer:1000});
          }.bind(this), function(error){
            this.formGroup.loadAppointments = false;
            var text = 'Impossible de sauvegarder les données du groupe';
            if(error != null && error.message != null && error.message.length > 0){
              text += ' (' + error.message + ')';
            }
            swal({ title: 'Erreur', text: text, icon: 'error'});
          }.bind(this));
        }
      }
    }, (reason) => {

    });
  }

  formatGroup(group){
    group.variableOptions = [];
    for(var i = 0; i < group.options.length; i++){
      var option = group.options[i];
      var pair = option.split('=');
      if(pair.length == 2){
        group.variableOptions.push({
          varname:pair[0],
          value:pair[1]
        })
      }
    }
  }

  getParticipantFields(service){
    var participantFields = null;
    if(service != null && service.servicePrices.length > 0 && service.servicePrices[0] != null){
      var servicePrice = service.servicePrices[0];
      if(servicePrice != null && servicePrice.participantsParameter != null){
        participantFields = this.getParticipantParametersById(servicePrice.participantsParameter);
      }
    }
    return participantFields;
  }

  getParticipantField(participantParameters, varname){
    for(var i = 0; i < participantParameters.fields.length; i++){
      var field = participantParameters.fields[i];
      if(field.varname == varname){
        return field
      }
    }
    return null;
  }

  deleteGroup(formGroup){
    swal({
      title: 'Êtes-vous sûr ?',
      text: 'Êtes-vous sûr de supprimer ce groupe sur les dates sélectionnées ?',
      icon: 'warning',
      buttons: ['Annuler', 'Supprimer'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        var dates = [];
        for(var i = 0; i < this.formGroup.formManyDates.length; i++){
          var date = this.formGroup.formManyDates[i];
          if(date.checked){
            dates.push({
              startDate: formatDate(date.startDate, 'yyyy-MM-dd HH:mm:ss', 'fr'),
              endDate: formatDate(date.endDate, 'yyyy-MM-dd HH:mm:ss', 'fr')
            });
          }
        }
        this.formGroup.loadAppointments = true;
        this.userService.post('deletedgroup/:token', {
          dates:dates,
          group:this.formGroup.groupSelected,
          startDate:this.formGroup.startDate,
          endDate:this.formGroup.endDate,
          idService:this.formGroup.service.id,
          idPlace:this.formGroup.groupSelected.idPlace,
          idGroup:(this.formGroup.groupSelected!=null && this.formGroup.groupSelected.oneByBooking)?this.formGroup.groupSelected.id:-1
        }, function(data){
            this.formGroup.loadAppointments = false;
            if(this.formGroup.groupSelected.idMembers.length == 0) this.removeGroupWithoutMembers(data);
            this.deleteAllGroups(data);
            this.generateParticipantsNotInGroup();
            swal({ title: 'OK', text: 'Modifications effectuées', icon: 'success', buttons: false, timer:1000});
        }.bind(this), function(error){
          this.formGroup.loadAppointments = false;
          var text = 'Impossible de récupérer les données du groupe';
          if(error != null && error.message != null && error.message.length > 0){
            text += ' (' + error.message + ')';
          }
          swal({ title: 'Erreur', text: text, icon: 'error'});
        }.bind(this));
      }
    });
  }

  /**
   *
   */
  openMember(idMember, content){
    var member = this.members.find(element => element.id == idMember);
    this.formMemberGroups = {
      member:member,
      groups:[],
      loadAppointments:false
    }
    this.modalService.open(content, { windowClass: 'max', backdrop:'static' }).result.then((result) => {
      if(result){

      }
    }, (reason) => {

    });
    if(member.groups.length > 0){
      this.callOpenGroup(0, member);
    }
  }

  callOpenGroup(index, member){
    this.formMemberGroups.loadAppointments = true;
    var group = JSON.parse(JSON.stringify(member.groups[index]));
    this.userService.post('openGroup/:token', {startDate:group.startDate, endDate:group.endDate, idService:group.idService,dates:[]}, function(data){
      this.generateParticipantsGroupByParameters(data.appointments, group);
      this.formMemberGroups.groups.push(group);
      if(index + 1 < member.groups.length){
        this.callOpenGroup(index + 1, member);
      }
      else {
        this.formMemberGroups.loadAppointments = false;
      }
    }.bind(this), function(error){
      this.formGroup.loadAppointments = false;
      var text = 'Impossible de récupérer les données du groupe';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }


  sendPlanningToMember(id){
    if(!this.formMemberGroups.loadAppointments){
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Voulez-vous vraiment envoyer ce planning au ' + this.global.getTextByLocale(this.settings.staff_word_single) + ' ?',
        icon: 'warning',
        buttons: ['Annuler', 'Oui'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          this.formMemberGroups.loadAppointments = true;
          var content = document.getElementById(id).innerHTML;
          this.userService.put('sendPlanningToMember/:token', {
            idMember: this.formMemberGroups.member.id,
            content:content,
            date:formatDate(this.date, 'EEEE dd MMMM yyyy', 'fr')
          }, function(data){
            this.formMemberGroups.loadAppointments = false;
            swal({ title: 'Ok', text: '', icon: 'success'});
          }.bind(this), function(error){
            this.formMemberGroups.loadAppointments = false;
            var text = 'Impossible d\'envoyer le message.';
            if(error != null && error.message != null && error.message.length > 0){
              text += ' (' + error.message + ')';
            }
            swal({ title: 'Erreur', text: text, icon: 'error'});
          }.bind(this));
        }
      });
    }
  }

  openConstraintDialog(constraint = null){
    if(!this.isRESAManager()) return;
    const modalRef = this.modalService.open(ConstraintComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.setConstraintCalendar(this.date, constraint, false);
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

  openInfoCalendarDialog(infoCalendar = null){
    if(!this.isRESAManager()) return;
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
