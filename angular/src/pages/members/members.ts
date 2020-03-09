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
  selector: 'page-members',
  templateUrl: 'members.html',
  styleUrls:['./members.css']
})

export class MembersComponent extends ComponentCanDeactivate implements OnInit {

  public noMembersLoaded:boolean = true;
  public members:any[] = [];
  public services:any[] = [];
  public settings:any = null;
  public staffUsers = [];

  private displayMembers = [];
  public formMembers = {order:'position', search:'', places:[]};


  private skeletonMember = null;
  public formMember = null;
  private skeletonMemberAvailability = null;
  private skeletonMemberLink = null;
  private skeletonMemberLinkService = null;
  public launchActionCreateRESAMember = false;
  public launchActionSave = false;

  private toSave = false;


  constructor(private userService:UserService, private navService:NavService, private global:GlobalService, private modal: NgbModal) {
    super('Personnel');
  }


  ngOnInit(): void {
    this.userService.get('members/:token', function(data){
      if(data.members) this.members = data.members;
      this.noMembersLoaded = this.members.length == 0;
      if(data.services) this.services = data.services;
      if(data.staffUsers) this.staffUsers = data.staffUsers;
      if(data.settings) {
        this.settings = data.settings;
        for(var i = 0; i < this.settings.places.length; i++){
          if(this.global.filterSettings == null || this.global.filterSettings.places == null || this.global.filterSettings.places[this.settings.places[i].id]){
            this.switchFormMembersPlace(this.settings.places[i].id);
          }
        }
      }
      this.skeletonMember = data.skeletonMember;
      if(this.skeletonMember != null){
        this.formMember = JSON.parse(JSON.stringify(this.skeletonMember));
      }
      this.skeletonMemberAvailability = data.skeletonMemberAvailability;
      this.skeletonMemberLink = data.skeletonMemberLink;
      this.skeletonMemberLinkService = data.skeletonMemberLinkService;
      for(var i = 0; i < this.members.length; i++){
        var member = this.members[i];
        this.formatMember(member);
      }
      this.updatePositions();
      this.calculateDisplayMembers();
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des members';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  formatMember(member){
    this.initializationPermissions(member);
    this.global.htmlSpecialDecodeArray(member.presentation);
    for(var i = 0; i < member.memberAvailabilities.length; i++){
      var memberAvailability = member.memberAvailabilities[i];
      this.formatMemberAvailability(memberAvailability);
    }
  }

  formatMemberAvailability(memberAvailability){
    memberAvailability.synchronized = (memberAvailability.idCalendar.length > 0);
    if(memberAvailability.synchronized && this.getCalendarById(memberAvailability.idCalendar)!=null){
      memberAvailability.nameCalendar = this.getCalendarById(memberAvailability.idCalendar).name;
    }
    else {
      memberAvailability.idCalendar = '';
      memberAvailability.synchronized = false;
    }

    var tokens = memberAvailability.startTime.split(':');
    memberAvailability.startHours = Number(tokens[0]);
    memberAvailability.startMinutes = Number(tokens[1]);
    tokens = memberAvailability.endTime.split(':');
    memberAvailability.endHours = Number(tokens[0]);
    memberAvailability.endMinutes = Number(tokens[1]);
  }

  canDeactivate():boolean{
    return !this.toSave;
  }

  getPlaceName(idPlace){
    if(this.settings != null && this.settings.places != null){
      for(var i = 0; i < this.settings.places.length; i++){
        var place = this.settings.places[i];
        if(place.id == idPlace){
          return place.name[this.global.currentLanguage];
        }
      }
    }
    return '';
  }

  getCalendarById(idCalendar){
    for(var i = 0; i < this.settings.calendars.length; i++){
      var calendar = this.settings.calendars[i];
      if(calendar.id == idCalendar){
        return calendar;
      }
    }
    return null;
  }

  goCalendars(dismiss){
    dismiss();
    this.navService.changeRoute('calendars');
  }

  updateMember(member){
    member.isUpdated = true;
    this.setToSave(true);
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


  /*************************************************************************
   ***************************** MEMBERS ***********************************
   ****************************************************************/

   switchFormMembersPlace(idPlace){
     var index = this.formMembers.places.indexOf(idPlace);
     if(index != -1){
       this.formMembers.places.splice(index, 1);
     }
     else {
       this.formMembers.places.push(idPlace);
     }
     this.calculateDisplayMembers();
   }

   displayMembersOrderBy(order){
     if(this.formMembers.order == order){
       this.formMembers.order = 'r' + order;
     }
     else {
       this.formMembers.order = order;
     }
     this.calculateDisplayMembers();
   }

   calculateDisplayMembers(){
     if(this.formMembers.search.length == 0 && this.settings.places.length == 0){
       this.displayMembers = this.members;
     }
     else {
       var members = [];
       for(var i = 0; i < this.members.length; i++){
         var member = this.members[i];
         var filterName = member.lastname.toLowerCase().indexOf(this.formMembers.search.toLowerCase()) != -1 ||
          member.firstname.toLowerCase().indexOf(this.formMembers.search.toLowerCase()) != -1 ||
          member.nickname.toLowerCase().indexOf(this.formMembers.search.toLowerCase()) != -1;
         var filterPlaces = (this.settings.places.length == 0) || (member.places.length == 0);
         for(var index = 0; index < this.formMembers.places.length; index++){
           filterPlaces = filterPlaces || member.places.indexOf(this.formMembers.places[index]) > -1;
         }
         if(filterName && filterPlaces){
           members.push(member);
         }
       }
       this.displayMembers = members;
     }
     if(this.formMembers.order.length > 0){
       this.displayMembers.sort(function(member1, member2){
         var asc = 1;
         if(this.formMembers.order[0] == 'r') asc = -1;
         if(this.formMembers.order == 'nickname' || this.formMembers.order == 'rnickname'){
           if(member1.nickname < member2.nickname) return -1 * asc;
           if(member1.nickname > member2.nickname) return 1 * asc;
         }
         else if(this.formMembers.order == 'position' || this.formMembers.order == 'rposition'){
           return (member1.position - member2.position) * asc;
         }
         else if(this.formMembers.order == 'lastname' || this.formMembers.order == 'rlastname'){
           var lastName1 = member1.lastname + ' ' + member1.firstname;
           var lastName2 = member2.lastname + ' ' + member2.firstname;
           if(lastName1 < lastName2) return -1 * asc;
           if(lastName1 > lastName2) return 1 * asc;
         }
         else if(this.formMembers.order == 'activated' || this.formMembers.order == 'ractivated'){
           var value1 = member1.activated?1:0;
           var value2 = member2.activated?1:0;
           return (value1 - value2) * asc;
         }
         else if(this.formMembers.order == 'email' || this.formMembers.order == 'remail'){
           if(member1.email < member2.email) return -1 * asc;
           if(member1.email > member2.email) return 1 * asc;
         }
         return 0;
       }.bind(this));
     }
   }


  /*************************************************************************
   ***************************** MEMBER MODAL ******************************
   ****************************************************************/

   addMember(content){
     var member = JSON.parse(JSON.stringify(this.skeletonMember));
     member.position = this.members.length + 1;
     this.openMember(content, member);
   }

   duplicateMember(content, member){
     var newMember = JSON.parse(JSON.stringify(member));
     newMember.id = -1;
     newMember.position = this.members.length + 1;
     newMember.idCustomerLinked = -1;
     newMember.activated = false;
     newMember.isDeletable = true;
     for(var i = 0; i < newMember.memberAvailabilities.length; i++){
       var memberAvailability = newMember.memberAvailabilities[i];
       memberAvailability.id = -1;
       for(var j = 0; j < memberAvailability.memberAvailabilityServices.length; j++){
         var memberAvailabilityService = memberAvailability.memberAvailabilityServices[j];
         memberAvailabilityService.idMemberAvailability = memberAvailability.id;
       }
     }
     for(var i = 0; i < newMember.memberLinks.length; i++){
       var memberLink = newMember.memberLinks[i];
       memberLink.id = -1;
       for(var j = 0; j < memberLink.memberLinkServices.length; j++){
         var memberLinkService = memberLink.memberLinkServices[j];
         memberLinkService.id = -1;
       }
     }
     this.openMember(content, newMember);
   }

   modifyMember(content, member){
     this.openMember(content, member);
   }

   openMember(content, member){
     this.formMember = JSON.parse(JSON.stringify(member));
     if(this.formMember.memberAvailabilities.length == 0){
       this.addMemberAvailability();
     }
     this.modal.open(content, { size: 'lg', backdrop:'static'}).result.then((result) => {
       if(result == 'save'){
         var found = false;
         for(var i = 0; i < this.members.length; i++){
           var member = this.members[i];
           if(member.position == this.formMember.position){
             this.members[i] = JSON.parse(JSON.stringify(this.formMember));
             this.updateMember(this.members[i]);
             found = true;
           }
         }
         if(!found){
           var member = JSON.parse(JSON.stringify(this.formMember));
           this.members.push(member);
           this.updatePositions();
           this.updateMember(member);
         }
         this.noMembersLoaded = false;
         this.save();
       }
     }, (reason) => {
     });
   }

   deleteMember(member) {
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer ' + member.nickname + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         var newMembers = [];
         for(var i = 0; i < this.members.length; i++){
           var localMember = this.members[i];
           if(localMember.position != member.position){
             newMembers.push(localMember);
           }
         }
         this.members = newMembers;
         this.updatePositions();
         this.calculateDisplayMembers();
         this.setToSave(true);
       }
     });
   }

   updatePosition(member){
     if(member.editPosition != member.oldPosition){
       this.members.splice(member.editPosition - 1, 0, this.members.splice(member.oldPosition - 1, 1)[0]);
       this.updatePositions();
     }
   }

   updatePositions(){
     for(var i =0; i < this.members.length;i++){
       var member = this.members[i];
       if(member.position != i){
         member.position = i;
         this.updateMember(member);
       }
       member.editPosition = member.position * 1 + 1;
       member.oldPosition = member.position * 1 + 1;
     }
   }

   switchPlace(idPlace){
     var index = this.formMember.places.indexOf(idPlace);
     if(index != -1){
       this.formMember.places.splice(index, 1);
     }
     else {
       this.formMember.places.push(idPlace);
     }
   }

   memberAssociated(member){
     for(var i = 0; i < this.staffUsers.length; i++){
       var user = this.staffUsers[i];
       if(member.idCustomerLinked == user.ID) return true;
     }
     return false;
   }

   initializationPermissions(member){
     if(this.memberAssociated(member) && member.permissions.only_appointments == null){
       member.permissions = {
         only_appointments:true,
         old_appointments:this.settings.staff_old_appointments_displayed,
         display_customer:this.settings.staff_display_customer,
         display_total:this.settings.staff_display_total,
         display_payments:this.settings.staff_display_payments,
         display_numbers:this.settings.staff_display_numbers,
         display_bookings_tab:this.settings.staff_display_bookings_tab
       };
     }
   }

   clearIdCustomerLinked(member){
     member.idCustomerLinked = -1;
     member.permissions = {};
   }

   getDisplayName(user, member){
     var displayName = user.displayName;
     if(member.email == user.email){
       displayName += ' (même adresse email)';
     }
     return displayName;
   }

   addMemberAvailability(){
     var memberAvailability = JSON.parse(JSON.stringify(this.skeletonMemberAvailability));
     memberAvailability.idMember = this.formMember.id;
     this.formatMemberAvailability(memberAvailability);
     this.formMember.memberAvailabilities.push(memberAvailability);
   }

   addMemberAvailabilityIfClickOnAdd(event, tabsRules){
     if(event != null && event.nextId == 'tab-add-rule'){
       this.addMemberAvailability();
       this.selectTabsSelect(tabsRules, 'tab-rule-' + (this.formMember.memberAvailabilities.length - 1));
     }
   }

   getTabName(memberAvailability, index){
     var name = 'Règle n°' + (index + 1);
     if(memberAvailability.name!=null && memberAvailability.name.length > 0){
       name = memberAvailability.name;
     }
     return name;
   }


   selectTabsSelect(tabsRules, id){
     setTimeout(() => {
       tabsRules.select(id);
       if(tabsRules.activeId != id){
         this.selectTabsSelect(tabsRules, id)
       }
     }, 150);
   }

   generateStartTime(memberAvailability){
     var hours = '' + memberAvailability.startHours;
     if(memberAvailability.startHours < 0) hours = '0' + memberAvailability.startHours;
     var minutes = '' + memberAvailability.startMinutes;
     if(memberAvailability.startMinutes < 0) minutes = '0' + memberAvailability.startMinutes;
     memberAvailability.startTime = hours + ':' + minutes + ':00';
   }

   generateEndTime(memberAvailability){
     var hours = '' + memberAvailability.endHours;
     if(memberAvailability.endHours < 0) hours = '0' + memberAvailability.endHours;
     var minutes = '' + memberAvailability.endMinutes;
     if(memberAvailability.endMinutes < 0) minutes = '0' + memberAvailability.endMinutes;
     memberAvailability.endTime = hours + ':' + minutes + ':00';
   }

   /**
    * One is not good xD
    */
   addValue(memberAvailability, input, step, sign, type){
     if(type == 'start'){
       if(input == 'hours'){
         memberAvailability.startHours += step * sign;
         if(memberAvailability.startHours > 23){
           memberAvailability.startHours = memberAvailability.startHours - 24;
         }
         else if(memberAvailability.startHours < 0) {
           memberAvailability.startHours = memberAvailability.startHours + 24;
         }
       }
       else if(input == 'minutes'){
         memberAvailability.startMinutes += step * sign;
         if(memberAvailability.startMinutes > 59){
           memberAvailability.startMinutes = memberAvailability.startMinutes - 60;
           this.addValue(memberAvailability, 'hours', 1, 1, type);
         }
         else if(memberAvailability.startMinutes < 0) {
           memberAvailability.startMinutes = memberAvailability.startMinutes + 60;
           this.addValue(memberAvailability, 'hours', 1, -1, type);
         }
       }
       this.generateStartTime(memberAvailability);
     }
     else {
       if(input == 'hours'){
         memberAvailability.endHours += step * sign;
         if(memberAvailability.endHours > 23){
           memberAvailability.endHours = memberAvailability.endHours - 24;
         }
         else if(memberAvailability.startHours < 0) {
           memberAvailability.endHours = memberAvailability.endHours + 24;
         }
       }
       else if(input == 'minutes'){
         memberAvailability.endMinutes += step * sign;
         if(memberAvailability.endMinutes > 59){
           memberAvailability.endMinutes = memberAvailability.endMinutes - 60;
           this.addValue(memberAvailability, 'hours', 1, 1, type);
         }
         else if(memberAvailability.endMinutes < 0) {
           memberAvailability.endMinutes = memberAvailability.endMinutes + 60;
           this.addValue(memberAvailability, 'hours', 1, -1, type);
         }
       }
       this.generateEndTime(memberAvailability);
     }
     this.setToSave(true);
   }

   setMemberAvailabilityDates(memberAvailability, dates){
     memberAvailability.dates = dates;
   }

   synchronizeCalendar(memberAvailability, index){
     if(memberAvailability.idCalendar.length > 0){
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Synchroniser le calendrier à la règle n°' + (index+1) + ' et remplacer les dates ?',
         icon: 'warning',
         buttons: ['Annuler', 'Synchroniser'],
         dangerMode: true,
       })
       .then((result) => {
         if(result){
           var calendar = this.getCalendarById(memberAvailability.idCalendar);
           if(calendar != null){
             memberAvailability.color = calendar.color;
             memberAvailability.dates = JSON.parse(JSON.stringify(calendar.dates));
             memberAvailability.nameCalendar = calendar.name;
             memberAvailability.synchronized = true;
             this.setToSave(true);
           }
         }
       });
     }
   }

   disynchronizeCalendar(memberAvailability, index){
     if(memberAvailability.synchronized){
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Voulez-vous désynchroniser le calendrier de la règle n°' + (index+1) + ' ?',
         icon: 'warning',
         buttons: ['Annuler', 'Désynchroniser'],
         dangerMode: true,
       })
       .then((result) => {
         if(result){
           memberAvailability.synchronized = false;
           memberAvailability.idCalendar  = '';
           memberAvailability.nameCalendar = '';
           this.setToSave(true);
         }
       });
     }
   }

   copyCalendar(memberAvailability, index){
     if(memberAvailability.idCalendarTemporary.length > 0){
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Copier le calendrier pour la règle n°' + (index+1) + ' et remplacer les dates ?',
         icon: 'warning',
         buttons: ['Annuler', 'Copier'],
         dangerMode: true,
       })
       .then((result) => {
         if(result){
           var calendar = this.getCalendarById(memberAvailability.idCalendarTemporary);
           if(calendar != null){
             memberAvailability.color = calendar.color;
             memberAvailability.dates = JSON.parse(JSON.stringify(calendar.dates));
             memberAvailability.synchronized = false;
             memberAvailability.idCalendar  = '';
             memberAvailability.idCalendarTemporary  = '';
             memberAvailability.nameCalendar = calendar.name;
             this.setToSave(true);
           }
         }
       });
     }
   }

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

   saveCalendar(memberAvailability, index){
     if(memberAvailability.nameCalendar.length > 0){
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Sauvegarder le calendrier et synchroniser ce calendrier pour la règle n°' + (index+1) + ' ?',
         icon: 'warning',
         buttons: ['Annuler', 'Sauvegarder'],
         dangerMode: true,
       })
       .then((result) => {
         if(result){
           var calendar = null;
           for(var i = 0; i < this.settings.calendars.length; i++){
             var localCalendar = this.settings.calendars[i];
             if(localCalendar.name == memberAvailability.nameCalendar){
               this.settings.calendars[i].color = memberAvailability.color;
               this.settings.calendars[i].dates = memberAvailability.dates;
               calendar = this.settings.calendars[i];
             }
           }
           if(calendar == null){
             var idCalendar = this.generateNewId(this.settings.calendars, 'id', 'calendar_');
             calendar = {id:idCalendar, name:memberAvailability.nameCalendar, type:0, color: memberAvailability.color, dates:memberAvailability.dates, groupDates:[]};
             this.settings.calendars.push(calendar);
           }
           this.userService.post('calendar/:token', {calendar:JSON.stringify(calendar)}, function(data){
             memberAvailability.synchronized = true;
             memberAvailability.idCalendar  = data.id;
             this.setToSave(true);
           }.bind(this), function(error){
             var text = 'Impossible de sauvegarder le calendrier';
             if(error != null && error.message != null && error.message.length > 0){
               text += ' (' + error.message + ')';
             }
             swal({ title: 'Erreur', text: text, icon: 'error'});
           }.bind(this));
         }
       });
     }
   }

   addAttachService(memberAvailability){
     memberAvailability.memberAvailabilityServices.push({
       idMemberAvailability: memberAvailability.id,
       idService:0,
       capacity:0
     });
   }

   removeAttachService(memberAvailability, index){
     var memberAvailabilityServices = memberAvailability.memberAvailabilityServices[index];
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer cette activité ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         memberAvailability.memberAvailabilityServices.splice(index, 1);
       }
     });
   }

  getServiceName(service){
     var name = this.global.htmlSpecialDecode(this.global.getTextByLocale(service.name, this.global.currentLanguage));
     if(service.places.length > 0){
       var placesText = '';
       for(var i = 0; i < service.places.length; i++){
         var placeName = this.getPlaceName(service.places[i]);
         if(i > 0) placesText += ', ';
         placesText += this.global.htmlSpecialDecode(placeName);
       }
       name = '['+placesText+'] ' + name;
     }
     return name;
   }


   duplicateMemberAvailability(index, tabsRules){
     var memberAvailability  = JSON.parse(JSON.stringify(this.formMember.memberAvailabilities[index]));
     memberAvailability.id = -1;
     memberAvailability.idMember = this.formMember.id;
     this.formatMemberAvailability(memberAvailability);
     for(var j = 0; j < memberAvailability.memberAvailabilityServices.length; j++){
       var memberAvailabilityService = memberAvailability.memberAvailabilityServices[j];
       memberAvailabilityService.idMemberAvailability = memberAvailability.id;
     }
     this.formMember.memberAvailabilities.push(memberAvailability);
     this.selectTabsSelect(tabsRules, 'tab-rule-' + (this.formMember.memberAvailabilities.length - 1));
   }


   removeMemberAvailability(index) {
     var memberAvailability = this.formMember.memberAvailabilities[index]
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer la règle n°' + (index+1) + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         this.formMember.memberAvailabilities.splice(index, 1);
       }
     });
   }

   addMemberLink() {
     var memberLink  = JSON.parse(JSON.stringify(this.skeletonMemberLink));
     memberLink.idMember = this.formMember.id;
     this.formMember.memberLinks.push(memberLink);
   }

   removeMemberLink(index) {
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer la liaison n°' + (index+1) + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         this.formMember.memberLinks.splice(index, 1);
       }
     });
   }

   addMemberLinkService(memberLink){
     var memberLinkService = JSON.parse(JSON.stringify(this.skeletonMemberLinkService));
     memberLinkService.idMemberLink = memberLink.id;
     memberLinkService.typeCapacityMethod = 0;
     memberLink.memberLinkServices.push(memberLinkService);
   }

   deleteMemberLinkService(memberLink, index) {
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer cette activité ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         memberLink.memberLinkServices.splice(index, 1);
       }
     });
   }

   isServiceInMemberAvailabilities(service){
     for(var i = 0; i < this.formMember.memberAvailabilities.length; i++){
       var memberAvailability = this.formMember.memberAvailabilities[i];
       for(var j = 0; j < memberAvailability.memberAvailabilityServices.length; j++){
         var memberAvailabilityService = memberAvailability.memberAvailabilityServices[j];
         if(memberAvailabilityService.idService == service.id){
           return true;
         }
       }
     }
     return false;
   }

   isServiceUsed(service){
     for(var i = 0; i < this.formMember.memberLinks.length; i++){
       var memberLink = this.formMember.memberLinks[i];
       for(var j = 0; j < memberLink.memberLinkServices.length; j++){
         var memberLinkService = memberLink.memberLinkServices[j];
         if(memberLinkService.idService == service.id){
           return true;
         }
       }
     }
     return false;
   }


   isOkMember(member){
     return true;
   }

   closeMemberModal(callback){
     if(this.isOkMember(this.formMember)){
       callback('save');
     }
   }


   /*************************************************************************
    ***************************** SAVE ***********************************
    ****************************************************************/
    staffIsPresent(id){
      for(var i = 0; i < this.staffUsers.length; i++){
        var staff = this.staffUsers[i];
        if(staff.ID == id){
          return true;
        }
      }
      return false;
    }

    canCreateRESAMembre(member){
      return member.nickname.length > 0 && member.email.length > 0;
    }

    createRESAMember(member){
      if(!this.launchActionCreateRESAMember){
        swal({
          title: 'Êtes-vous sûr ?',
          text: 'Créer un nouveau compte wordpress avec le membre : ' + member.nickname + ' ?',
          icon: 'warning',
          buttons: ['Annuler', 'Oui'],
          dangerMode: true,
        })
        .then((willDelete) => {
          if (willDelete) {
            this.launchActionCreateRESAMember = true;
            this.userService.post('createStaff/:token', {member:JSON.stringify(member)}, function(staffUser){
              this.launchActionCreateRESAMember = false;
              member.idCustomerLinked = staffUser.ID;
              if(!this.staffIsPresent(staffUser.ID)){
                this.staffUsers.push(staffUser);
              }
              this.initializationPermissions(member);
            }.bind(this), function(error){
              this.launchActionCreateRESAMember = false;
              var text = 'Impossible de créer un compte wordpress pour ce membre';
              if(error != null && error.message != null && error.message.length > 0){
                text += ' (' + error.message + ')';
              }
              swal({ title: 'Erreur', text: text, icon: 'error'});
            }.bind(this));
          }
        });
      }
    }

    save(){
      if(!this.canDeactivate() && ! this.launchActionSave){
        this.launchActionSave = true;
        this.userService.post('members/:token', {members:JSON.stringify(this.members)}, function(data){
          this.launchActionSave = false;
          this.members = data;
          for(var i = 0; i < this.members.length; i++){
            var member = this.members[i];
            this.formatMember(member);
          }
          this.updatePositions();
          this.calculateDisplayMembers();
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
