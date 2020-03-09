import { Component, OnInit, ViewChild } from '@angular/core';
import { NgModel } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { DatePipe } from '@angular/common';

import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';

declare var swal: any;

@Component({
  selector: 'page-activity',
  templateUrl: 'activity.html',
  styleUrls:['./activity.css']
})

export class ActivityComponent extends ComponentCanDeactivate implements OnInit {

  public initTab = 1;
  public activity:any = null;
  private members = [];
  private equipments = [];
  private settings = null;
  public currentDatePreview = new Date();
  public timeslotsPreview = [];

  public formFilters = {activityPrice:'', activityPriceExtra:''};
  private currentIndexActivityPriceEdition = -1;
  public formActivityPrice = null;
  private skeletonServicePrice = null;

  private numberDaysByWeek = 7;
  @ViewChild('tabsRules', { static: false }) ngbTabSet:any = null;
  private skeletonServiceAvailability = null;
  public formTimeslot = null;
  private skeletonServiceTimeslot = null;

  private skeletonServiceMemberPriority = null;

  private activities = null;
  private lastId = 0;
  private slugs = null;
  private toSave = false;
  public formImport = {activity:null, activityPrice:null};
  public launchAction = false;
  public launchActionSave = false;

  public serviceHasChanged = false;
  public formManyDates = {activityAvailability:null, dates:[]}

  constructor(private userService:UserService, private navService:NavService, public global:GlobalService,
    private route: ActivatedRoute, private modalService: NgbModal, private datePipe:DatePipe) {
    super('Activité');
  }

  ngOnInit(): void {
    var id = this.route.snapshot.paramMap.get('id');
    if(id == null){ id = '-1'; }
    var tab = this.route.snapshot.paramMap.get('tab');
    if(tab == null){ this.initTab = 2; }
    else if(tab == 'preview'){ this.initTab = 1; }
    else if(tab == 'modify' || tab == 'duplicate'){ this.initTab = 2; }
    this.launchAction = true;
    this.userService.get('activity/:token/' + id, function(data){
      this.launchAction = false;
      this.activity = data.activity;
      if(data.members){ this.members = data.members; }
      if(data.equipments){ this.equipments = data.equipments; }
      this.settings = data.settings;
      this.skeletonServicePrice = data.skeletonServicePrice;
      if(this.skeletonServicePrice != null){
        this.formActivityPrice = JSON.parse(JSON.stringify(this.skeletonServicePrice));
      }
      this.skeletonServiceAvailability = data.skeletonServiceAvailability;
      this.skeletonServiceTimeslot = data.skeletonServiceTimeslot;
      this.skeletonServiceMemberPriority = data.skeletonServiceMemberPriority;
      this.activities = data.activities;
      for(var i = 0; i < this.activities.length; i++){
        this.lastId = Math.max(this.lastId, this.activities[i].id);
      }
      this.lastId++;
      this.slugs = data.slugs;

      if(this.activity.id == -1){
        this.addServiceAvailability();
        this.activity.slug = this.generateSlugService();
        this.activity.position = this.slugs.length;
      }
      this.formatActivity();
      if(tab == 'duplicate'){ this.duplicateActivity(); }
      setInterval(() => { this.checkServiceHasChanged(); }, 10000);
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données de l\'activité';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }



  formatActivity(){
    this.activity.oldSlug = this.activity.slug;
    this.global.htmlSpecialDecodeArray(this.activity.name);
    this.global.htmlSpecialDecodeArray(this.activity.notificationMessage);
    this.global.htmlSpecialDecodeArray(this.activity.presentation, true);
    for(var j = 0; j < this.activity.servicePrices.length; j++){
      var servicePrice = this.activity.servicePrices[j];
      servicePrice.oldSlug = servicePrice.slug;
      this.global.htmlSpecialDecodeArray(servicePrice.name);
      this.global.htmlSpecialDecodeArray(servicePrice.mention);
      this.global.htmlSpecialDecodeArray(servicePrice.presentation);
    }
    for(var i = 0; i < this.activity.serviceAvailabilities.length; i++){
      var activityAvailability = this.activity.serviceAvailabilities[i];
      this.formatActivityAvailability(activityAvailability);
    }
  }

  formatTimeslot(timeslot){
    var tokens = timeslot.startTime.split(':');
    timeslot.startHours = Number(tokens[0]);
    timeslot.startMinutes = Number(tokens[1]);
    tokens = timeslot.endTime.split(':');
    timeslot.endHours = Number(tokens[0]);
    timeslot.endMinutes = Number(tokens[1]);
  }

  formatActivityAvailability(activityAvailability){
    if(activityAvailability.color.length == 0) activityAvailability.color = '#008000';
    activityAvailability.synchronized = (activityAvailability.idCalendar.length > 0);
    if(activityAvailability.synchronized && this.getCalendarById(activityAvailability.idCalendar)!=null){
      activityAvailability.nameCalendar = this.getCalendarById(activityAvailability.idCalendar).name;
    }
    else {
      activityAvailability.idCalendar = '';
      activityAvailability.synchronized = false;
    }
    for(var j = 0; j < activityAvailability.timeslots.length; j++){
      var timeslot = activityAvailability.timeslots[j];
      this.formatTimeslot(timeslot);
    }
  }

  havePlaces(){ return this.settings != null && this.settings.places != null && this.settings.places.length > 0; }

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

  getScenarioName(idScenario){
    if(this.settings != null && this.settings.states_parameters != null){
      for(var i = 0; i < this.settings.states_parameters.length; i++){
        var parameter = this.settings.states_parameters[i];
        if(parameter.id == idScenario){
          return parameter.name;
        }
      }
    }
    return '';
  }

  getParticipantParametersName(idParticipantsParameters){
    if(this.settings != null && this.settings.form_participants_parameters != null){
      for(var i = 0; i < this.settings.form_participants_parameters.length; i++){
        var parameter = this.settings.form_participants_parameters[i];
        if(parameter.id == idParticipantsParameters){
          return parameter.label[this.global.currentLanguage];
        }
      }
    }
    return '';
  }

  duplicateActivity(){
    this.activity.id = -1;
    this.activity.name[this.global.currentLanguage] = 'Copie de ' + this.activity.name[this.global.currentLanguage];
    this.activity.slug = this.generateSlugService();
    for(var i = 0; i < this.activity.serviceAvailabilities.length; i++){
      var activityAvailability = this.activity.serviceAvailabilities[i];
      this.updateIdServiceAvailability(activityAvailability, -1);
      for(var j = 0; j < activityAvailability.timeslots.length; j++){
        var timeslot = activityAvailability.timeslots[j];
        timeslot.idsServicePrices = [];
      }
    }
    for(var i = 0; i < this.activity.servicePrices.length; i++){
      var servicePrice = this.activity.servicePrices[i];
      servicePrice.id = -1;
      for(var j = 0; j < servicePrice.vatList.length; j++){
        delete servicePrice.vatList[j].product;
      }
    }
    this.activity.position = this.slugs.length;
    this.activity.linkOldServices = '';
    this.setToSave(true);
  }

  generateSlugService(){
    var index = this.lastId;
    var slug = 'activity' + index;
    while(this.isSlugAlreadyUsed(slug)){
      index++;
      slug = 'activity' + index;
    }
    return slug;
  }

  canDeactivate():boolean{
    return !this.toSave;
  }

  setToSave(value){
    this.toSave = value;
    if(value) this.changeTitle();
    else this.resetTitle();
  }

  getToSave(){
    return this.toSave;
  }

  goActivities(){
    this.navService.changeRoute('activities');
  }

  goCalendars(){
    this.navService.changeRoute('calendars');
  }

  goMembers(){
    this.navService.changeRoute('members');
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

  getCapacityForMember(member, date, timeslot){
    var capacity = 0;
    for(var i = 0; i < member.memberAvailabilities.length; i++){
      var memberAvailability = member.memberAvailabilities[i];
      var stringDate = this.datePipe.transform(date, 'yyyy-MM-dd');
      var dates = memberAvailability.dates.split(',');
      if(dates.indexOf(stringDate) > -1){
        var timeslotStartTime = this.getDateWithTime(timeslot.startTime);
        var timeslotEndTime = this.getDateWithTime(timeslot.endTime);
        var availabilityStartTime = this.getDateWithTime(memberAvailability.startTime);
        var availabilityEndTime = this.getDateWithTime(memberAvailability.endTime);
        if(availabilityStartTime <= timeslotStartTime && timeslotEndTime <= availabilityEndTime){
          for(var j = 0; j < memberAvailability.memberAvailabilityServices.length; j++){
            var memberAvailabilityService = memberAvailability.memberAvailabilityServices[j];
            if(memberAvailabilityService.idService == this.activity.id){
              capacity += memberAvailabilityService.capacity;
            }
          }
        }
      }
    }
    return capacity
  }

  getCapacityForMembers(date, timeslot){
    var capacity = 0;
    for(var i = 0; i < this.members.length; i++){
      var member = this.members[i];
      capacity += this.getCapacityForMember(member, date, timeslot);
    }
    return capacity;
  }

  getColorsPreview(){
    var colors = [];
    for(var i = 0; i < this.activity.serviceAvailabilities.length; i++){
      var serviceAvailability = this.activity.serviceAvailabilities[i];
      if(serviceAvailability.color.length > 0) {
        colors.push(serviceAvailability.color);
      }
      else {
        colors.push('green');
      }
    }
    return colors;
  }

  getDatesPreview(){
    var dates = [];
    for(var i = 0; i < this.activity.serviceAvailabilities.length; i++){
      var serviceAvailability = this.activity.serviceAvailabilities[i];
      if(this.activity.typeAvailabilities == 0){
        dates.push(serviceAvailability.dates.split(','));
      }
      else if(this.activity.typeAvailabilities == 1){
        dates.push(serviceAvailability.groupDates);
      }
      else if(this.activity.typeAvailabilities == 2){
        var localDates = [];
        for(var j = 0; j < serviceAvailability.manyDates.length; j++){
           localDates = dates.concat(serviceAvailability.manyDates[j].split(','));
        }
        dates.push(localDates);
      }
    }
    return dates;
  }

  viewActivityAvailabilities(timeslot){
    var index = -1;
    for(var i = 0; i < this.activity.serviceAvailabilities.length; i++){
      var serviceAvailability = this.activity.serviceAvailabilities[i];
      if(timeslot.idServiceAvailability == serviceAvailability.id){
        index = i;
      }
    }
    this.initTab = 5;
    this.selectTabsSelect(this.ngbTabSet, 'tab-rule-' + index);
  }

  clearTime(date){
    date.setHours(0);
    date.setMinutes(0);
    date.setSeconds(0);
    date.setMilliseconds(0);
    return date;
  }

  setCurrentDatePreview(date){
    this.currentDatePreview = new Date(date);
    var stringCurrentDate = this.datePipe.transform(this.currentDatePreview, 'yyyy-MM-dd');
    var timeslots = [];
    for(var i = 0; i < this.activity.serviceAvailabilities.length; i++){
      var serviceAvailability = this.activity.serviceAvailabilities[i];
      if(this.activity.typeAvailabilities == 0){
        if(serviceAvailability.dates.indexOf(stringCurrentDate) > -1){
          timeslots = timeslots.concat(serviceAvailability.timeslots);
        }
      }
      else if(this.activity.typeAvailabilities == 1){
        for(var j = 0; j < serviceAvailability.groupDates.length; j++){
          var dates = serviceAvailability.groupDates[j];
          if(this.clearTime(new Date(dates.startDate)) <= this.clearTime(new Date(this.currentDatePreview)) &&
              this.clearTime(new Date(this.currentDatePreview)) <= this.clearTime(new Date(dates.endDate))){
            timeslots = timeslots.concat(serviceAvailability.timeslots);
            break;
          }
        }
      }
      else if(this.activity.typeAvailabilities == 2){
        for(var j = 0; j < serviceAvailability.manyDates.length; j++){
          var dates = serviceAvailability.manyDates[j];
          if(dates.indexOf(stringCurrentDate) > -1){
            timeslots = timeslots.concat(serviceAvailability.timeslots);
            break;
          }
        }
      }
    }

    timeslots.sort((timeslot1, timeslot2) => {
      var startDate1 = this.getDateWithTime(timeslot1.startTime);
      var startDate2 = this.getDateWithTime(timeslot2.startTime);
      return startDate1.getTime() - startDate2.getTime();
    });

    this.timeslotsPreview = timeslots;
  }

  getVatById(idVat){
    if(this.settings != null && this.settings.vatList != null){
      for(var i = 0; i < this.settings.vatList.length; i++){
        var vat = this.settings.vatList[i];
        if(vat.id == idVat){
          return vat;
        }
      }
    }
    return null;
  }

  deleteImage(){
    swal({
      title: 'Êtes-vous sûr ?',
      text: 'Voulez-vous enlever cette image ?',
      icon: 'warning',
      buttons: ['Annuler', 'Enlever'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        this.activity.image[this.global.currentLanguage] = '';
        this.setToSave(true);
      }
    });
  }


  openImageSelectorDialog(content){
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result!=null && result.type == 'image'){
        this.activity.image[this.global.currentLanguage] = result.src;
        this.setToSave(true);
      }
    }, (reason) => {
    });
  }

  setImageActivity(image, c){
    c(image);
  }


  switchPlace(idPlace){
    var index = this.activity.places.indexOf(idPlace);
    if(index != -1){
      this.activity.places.splice(index, 1);
    }
    else {
      this.activity.places.push(idPlace);
    }
    this.setToSave(true);
  }

  isActivityPriceFiltered(activityPrice){
    var filter = this.formFilters.activityPrice.toLowerCase().trim();
    if(activityPrice.extra){
      filter = this.formFilters.activityPriceExtra.toLowerCase().trim();
    }
    if(filter.length == 0) return false;
    return activityPrice.name[this.global.currentLanguage].toLowerCase().trim().indexOf(filter) == -1 &&
      activityPrice.slug.toLowerCase().indexOf(filter) == -1;
  }

  getTypeAccountName(idTypeAccount){
    for(var i = 0; i < this.settings.typesAccounts.length; i++){
      var typeAccount = this.settings.typesAccounts[i];
      if(typeAccount.id == idTypeAccount){
        return typeAccount.name[this.global.currentLanguage];
      }
    }
    return '';
  }

  getDisplayTime(hours, days, months){
    var display = '';
    if(months > 0) display += months + ' mois ';
    if(days == 1) display += days + ' jour ';
    if(days > 1) display += days + ' jours ';
    if(hours == 1) display += hours + ' heure ';
    if(hours > 1) display += hours + ' heures ';
    if(display == '') return '1 heure';
    return display;
  }

  setToZeroIfNullOrNegative(value){
    if(value == null) { value = 0; }
    return Math.max(value, 0);
  }

  addServiceMemberPriority(){
    var serviceMemberPriority  = JSON.parse(JSON.stringify(this.skeletonServiceMemberPriority));
    serviceMemberPriority.idService = this.activity.id;
    this.activity.serviceMemberPriorities.push(serviceMemberPriority);
  }

  deleteServiceMemberPriority(index) {
    swal({
      title: 'Êtes-vous sûr ?',
      text: 'Voulez-vous supprimer cette priorité ?',
      icon: 'warning',
      buttons: ['Annuler', 'Supprimer'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        this.activity.serviceMemberPriorities.splice(index, 1);
        this.setToSave(true);
      }
    });
  }

  isSlugAlreadyUsed(slug){
    for(var i = 0; i < this.slugs.length; i++){
      var localSlug = this.slugs[i];
      if(localSlug.slug == slug && this.activity.id != localSlug.id){
        return true;
      }
    }
    return false;
  }

  isSlugNull(slug){
    return slug == null || slug.length == 0;
  }


  /********************************************************************************************
  ***************************************  PRICE LIST ******************************************
  **********************************************************************************************/

  getEquipmentNameOfActivityPrice(activityPrice){
    if(activityPrice.equipments.length > 0){
      for(var i = 0; i < this.equipments.length; i++){
        var equipment = this.equipments[i];
        if(equipment.id == activityPrice.equipments[0]){
          return this.global.getTextByLocale(equipment.name,this.global.currentLanguage);
        }
      }
    }
    return '';
  }

  getPriceOfActivityPrice(activityPrice){
    var price = 0;
    if(activityPrice.thresholdPrices.length == 0 || !activityPrice.thresholdPrices){
      price = activityPrice.price;
    }
    else {
      var thresholdPrice = activityPrice.thresholdPrices[0];
      price = (thresholdPrice.byPerson==null?0:Number(thresholdPrice.byPerson)) + (thresholdPrice.price==null?0:Number(thresholdPrice.price));
    }
    return price;
  }

  addActivityPrice(content, extra){
    var activityPrice = JSON.parse(JSON.stringify(this.skeletonServicePrice));
    activityPrice.idService = this.activity.id;
    activityPrice.slug = this.generateSlugServicePrice();
    activityPrice.extra = extra;
    this.currentIndexActivityPriceEdition = -1;
    this.openActivityPrice(content, activityPrice);
    this.addVat();
  }

  modifyActivityPrice(content, index){
    if(index >= 0){
      this.currentIndexActivityPriceEdition = index;
      var activityPrice = this.activity.servicePrices[index];
      this.openActivityPrice(content, activityPrice);
    }
  }

  removeIdsServicePrices(idsServicePrices, index){
    idsServicePrices.splice(index, 1);
    this.setToSave(true);
  }

  duplicateActivityPrice(content, activityPrice){
    this.currentIndexActivityPriceEdition = -1;
    this.formActivityPrice = JSON.parse(JSON.stringify(activityPrice));
    this.formActivityPrice.id = -1;
    this.formActivityPrice.slug = this.generateSlugServicePrice();
    for(var j = 0; j < this.formActivityPrice.vatList.length; j++){
      delete this.formActivityPrice.vatList[j].product;
    }
    this.openActivityPrice(content, this.formActivityPrice);
  }

  openActivityPrice(content, activityPrice){
    this.formActivityPrice = JSON.parse(JSON.stringify(activityPrice));
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      this.replaceActivityPrice();
    }, (reason) => {
    });
  }

  replaceActivityPrice(){
    var newActivityPrice = JSON.parse(JSON.stringify(this.formActivityPrice));
    if(newActivityPrice.equipments[0] == '' || newActivityPrice.equipments[0] == null){
      newActivityPrice.equipments = [];
    }
    if(this.currentIndexActivityPriceEdition ==  -1){
      this.activity.servicePrices.push(newActivityPrice);
    }
    else {
      var oldServicePrice = this.activity.servicePrices[this.currentIndexActivityPriceEdition];
      this.activity.servicePrices[this.currentIndexActivityPriceEdition] = newActivityPrice;
      if(this.activity.defaultSlugServicePrice == oldServicePrice.slug){
        this.activity.defaultSlugServicePrice = newActivityPrice.slug;
      }
      this.currentIndexActivityPriceEdition = -1;
    }
    if(this.activity.defaultSlugServicePrice == newActivityPrice.slug && (newActivityPrice.mandatory || !newActivityPrice.extra)){
      this.activity.defaultSlugServicePrice = '';
    }
    this.setToSave(true);
  }

  deleteActivityPrice(activityPrice, index){
    swal({
      title: 'Êtes-vous sûr ?',
      text: 'Voulez-vous supprimer le tarif : ' + activityPrice.name[this.global.currentLanguage] + ' ?',
      icon: 'warning',
      buttons: ['Annuler', 'Supprimer'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        /*var newServicePrices = [];
        for(var i = 0; i < this.activity.servicePrices.length; i++){
          var localServicePrice = this.activity.servicePrices[i];
          if(activityPrice.id != localServicePrice.id && activityPrice.slug == ) {
            newCategory.push(localCategory);
          }
        }
        this.activity.servicePrices = newServicePrices;*/
        this.activity.servicePrices.splice(index, 1);
        if(this.activity.defaultSlugServicePrice == activityPrice.slug){
          this.activity.defaultSlugServicePrice = '';
        }
        this.setToSave(true);
      }
    });
  }

  isSamePlace(equipment){
    if(this.activity.places.length == 0 || equipment.places.length == 0) return true;
    for(var i = 0; i < equipment.places.length; i++){
      var place = equipment.places[i];
      if(this.activity.places.indexOf(place) > -1){
        return true;
      }
    }
    return false;
  }

  getIndexServicePricesHaveSameServicePriceSlug(slug){
    var allIndex = [];
    for(var i = 0; i < this.activity.servicePrices.length; i++){
      var servicePrice = this.activity.servicePrices[i];
      if(servicePrice.slug == slug){
        allIndex.push(i);
      }
    }
    return allIndex;
  }

  haveSameServicePriceSlug(slug){
    var allActivityPrice = this.getIndexServicePricesHaveSameServicePriceSlug(slug);
    return allActivityPrice.length > 1 || allActivityPrice.length == 1 && allActivityPrice[0] != this.currentIndexActivityPriceEdition;
  }

  getActivityPrice(idActivityPrice){
    for(var i = 0; i < this.activity.servicePrices.length; i++){
      var servicePrice = this.activity.servicePrices[i];
      if(servicePrice.id == idActivityPrice){
        return servicePrice;
      }
    }
    return null;
  }

  getIndexOfActivityPrice(slug){
    for(var i = 0; i < this.activity.servicePrices.length; i++){
      var servicePrice = this.activity.servicePrices[i];
      if(servicePrice.slug == slug){
        return i;
      }
    }
    return -1;
  }


  /**
   * generate a new slug service
   */
   generateSlugServicePrice(){
    var index = 1;
    var slug = this.activity.slug + '_price' + index;
    while(this.getIndexServicePricesHaveSameServicePriceSlug(slug).length > 0){
      index++;
      slug = this.activity.slug + '_price' + index;
    }
    return slug;
  }

  activeActivityPriceVariations(){
    if(this.formActivityPrice.thresholdPrices.length == 0){
      this.addActivityPriceVariation();
    }
  }

  addActivityPriceVariation(){
    this.formActivityPrice.thresholdPrices.push({min:0, max:0, byPerson:0, byHours:false, price:0, supPerson:0});
  }

  switchTypeAccount(idTypeAccount){
    //remove private
    var index = this.formActivityPrice.typesAccounts.indexOf('private');
    if(index != -1){
      this.formActivityPrice.typesAccounts.splice(index, 1);
    }
    var index = this.formActivityPrice.typesAccounts.indexOf(idTypeAccount);
    if(index != -1){
      this.formActivityPrice.typesAccounts.splice(index, 1);
    }
    else {
      this.formActivityPrice.typesAccounts.push(idTypeAccount);
    }
  }

  addAllTypeAccounts(){
    if(this.formActivityPrice.typesAccounts.length == 0 || this.formActivityPrice.typesAccounts.length == 1 && this.formActivityPrice.typesAccounts[0] == 'private'){
      this.formActivityPrice.typesAccounts = [];
      for(var i = 0; i < this.settings.typesAccounts.length; i++){
        var typeAccount = this.settings.typesAccounts[i];
        var index = this.formActivityPrice.typesAccounts.indexOf(typeAccount.id);
        if(index == -1) {
          this.formActivityPrice.typesAccounts.push(typeAccount.id);
        }
      }
    }
  }

  addVat(){
    for(var i = 0; i < this.formActivityPrice.vatList.length; i++){
      var vatList = this.formActivityPrice.vatList[i];
      delete vatList.complete;
    }
    var idVat = this.settings.vatList.find(element => { if(element.value == 0) return element; });
    if(idVat == null) idVat = '';
    else idVat = idVat.id;
    this.formActivityPrice.vatList.push({idVat:idVat, useFixed:false, fixed:0, purcent:0, complete:true, name:'', reference:'', publicName:''});
  }

  removeVat(index){
    this.formActivityPrice.vatList.splice(index, 1);
  }

  displayTTCPrice(vatLine, activityPrice){
    var vat = this.getVatById(vatLine.idVat);
    var ttc = 0;
    if(vat != null){
      if(!vatLine.complete){
        if(vatLine.useFixed){
          ttc = (vatLine.fixed==null?0:vatLine.fixed).toFixed(2);
        }
        else {
          var price = this.getPriceOfActivityPrice(activityPrice);
          ttc = Number((price * vatLine.purcent / 100).toFixed(2));
        }
      }
      else {
        var price = this.getPriceOfActivityPrice(activityPrice);
        ttc = price - this.sumOfTTC(activityPrice, true);
      }
    }
    return ttc;
  }

  displayHTPrice(vatLine, activityPrice){
    var ttc = this.displayTTCPrice(vatLine, activityPrice);
    var vat = this.getVatById(vatLine.idVat);
    var ht = 0;
    if(vat != null){
      ht = Number((ttc /  (1 + (vat.value / 100))).toFixed(2));
    }
    return ht;
  }

  sumOfTTC(activityPrice, withoutLast = false){
    var ttc = 0;
    var end = activityPrice.vatList.length;
    if(withoutLast) { end -= 1; }
    for(var i = 0; i < end; i++){
      var vatLine = activityPrice.vatList[i];
      ttc += Number(this.displayTTCPrice(vatLine, activityPrice));
    }
    return ttc;
  }

  isLastComplete(activityPrice){
    return activityPrice.vatList.length > 0 && activityPrice.vatList[activityPrice.vatList.length - 1].complete;
  }

  calculateHTPrice(vatLine){
    var vat = this.getVatById(vatLine.idVat);
    if(vat != null){
      vatLine.ht = Number((vatLine.ttc /  (1 + (vat.value / 100))).toFixed(2));
    }
  }

  calculateTTCPrice(vatLine){
    var vat = this.getVatById(vatLine.idVat);
    if(vat != null){
      vatLine.ttc = Number((vatLine.ht * (1 + (vat.value / 100))).toFixed(2));
    }
    else {
      vatLine.ttc = 0;
    }
  }


  isOkNameActivityPrice(activityPrice){
    return activityPrice.name != null && activityPrice.name[this.global.currentLanguage] != null && activityPrice.name[this.global.currentLanguage].length > 0;
  }

  isOkActivityPrice(activityPrice){
    return !this.haveSameServicePriceSlug(activityPrice.slug) &&
      !this.isSlugNull(activityPrice.slug) &&
      activityPrice.vatList.length > 0 &&
      this.isOkNameActivityPrice(activityPrice) &&
      this.getPriceOfActivityPrice(activityPrice) >= 0;
  }

  closeActivityPriceModal(callback){
    if(this.isOkActivityPrice(this.formActivityPrice)){
      callback('save');
    }
  }

  formImportActivityChange(val) {
    this.formImport.activity = JSON.parse(this.formImport.activity);
  }

  importActivityPrice(){
    if(this.formImport.activityPrice != null && this.formImport.activityPrice.id > -1){
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Voulez-vous importer et copier ce tarif à la place du tarif en cours ?',
        icon: 'warning',
        buttons: ['Annuler', 'Importer'],
        dangerMode: true
      })
      .then((result) => {
        if (result) {
          this.launchAction = true;
          this.userService.post('importActivityPrice/:token', {idActivityPrice:JSON.stringify(this.formImport.activityPrice.id)}, function(data){
            this.launchAction = false;
            var destServicePrice = this.formActivityPrice;
      			var copyServicePrice = JSON.parse(JSON.stringify(data));
      			copyServicePrice.id = destServicePrice.id;
      			copyServicePrice.idService = destServicePrice.idService;
      			copyServicePrice.slug = destServicePrice.slug;
      		  this.formActivityPrice = copyServicePrice;
            swal({ title: 'Succès', text: 'L\'importation et la copie ont été réalisées', icon: 'success' });
          }.bind(this), function(error){
            var text = 'Impossible d\'importer les données';
            if(error != null && error.message != null && error.message.length > 0){
              text += ' (' + error.message + ')';
            }
            swal({ title: 'Erreur', text: text, icon: 'error'});
          }.bind(this));
        }
      });
    }
  }



  /********************************************************************************************
  *************************************** TIMESLOT ******************************************
  **********************************************************************************************/
  scroll(el: HTMLElement) {
    if(el != null){
      el.scrollIntoView();
    }
  }

  getMentionById(idMention){
    return this.settings.timeslots_mentions.find(element => element.id == idMention);
  }

  getFirstDate(activityAvailability){
    var date = new Date();
    date.setHours(0);
    date.setMinutes(0);
    date.setSeconds(0);
    date.setMilliseconds(0);
    if(this.activity.typeAvailabilities == 0){
      var dates = activityAvailability.dates.split(',');
      for(var i = 0; i < dates.length; i++){
        var newDate = new Date(dates[i] + 'T00:00:00');
        if(newDate.getTime() >= date.getTime()){
          return newDate;
        }
      }
    }
    else if(this.activity.typeAvailabilities == 1){
      for(var i = 0; i < activityAvailability.groupDates.length; i++){
        var dates = activityAvailability.groupDates[i];
        var newDate = new Date(dates.startDate + 'T00:00:00');
        newDate.setHours(0);
        newDate.setMinutes(0);
        newDate.setSeconds(0);
        newDate.setMilliseconds(0);
        if(newDate.getTime() >= date.getTime()){
          return newDate;
        }
      }
    }
    else if(this.activity.typeAvailabilities == 2){
      for(var i = 0; i < activityAvailability.manyDates.length; i++){
        var dates = activityAvailability.manyDates[i].split(',');
        for(var j = 0; j < dates.length; j++){
          var newDate = new Date(dates[j] + 'T00:00:00');
          if(newDate.getTime() >= date.getTime()){
            return newDate;
          }
        }
      }
    }
  }

  getDateWithTime(time){
    var dateEnd = new Date();
    var tokens = time.split(':');
    if(tokens.length == 3){
      dateEnd.setHours(tokens[0]);
      dateEnd.setMinutes(tokens[1]);
      dateEnd.setSeconds(0);
      dateEnd.setMilliseconds(0);
    }
    return dateEnd;
  }

  selectAllTimeslots(activityAvailability){
    if(activityAvailability.allTimeslotsSelected){
      if(activityAvailability.timeslots.length > 0){
        this.modifyTimeslotIfNecessary(activityAvailability, 0);
      }
      for(var i = 0; i < activityAvailability.timeslots.length; i++){
        var timeslot = activityAvailability.timeslots[i];
        timeslot.settingsWillChange = activityAvailability.allTimeslotsSelected;
      }
    }
    else {
      this.cancelFormTimeslot(activityAvailability);
    }
  }

  timeOfTimeslot(timeslot){
    var dateStart = this.getDateWithTime(timeslot.startTime);
    var dateEnd = this.getDateWithTime(timeslot.endTime);

    var diff = {sec:0, min:0, hour:0, day:0};
    var tmp = dateEnd.getTime() - dateStart.getTime();

    tmp = Math.floor(tmp/1000);
    diff.sec = tmp % 60;

    tmp = Math.floor((tmp-diff.sec)/60);
    diff.min = tmp % 60;

    tmp = Math.floor((tmp-diff.min)/60);
    diff.hour = tmp % 24;

    tmp = Math.floor((tmp-diff.hour)/24);
    diff.day = tmp;

    var date = new Date();
    date.setHours(diff.hour);
    date.setMinutes(diff.min);
    return new Date(date);
  }

  generateStartTime(timeslot){
    var hours = '' + timeslot.startHours;
    if(timeslot.startHours < 0) hours = '0' + timeslot.startHours;
    var minutes = '' + timeslot.startMinutes;
    if(timeslot.startMinutes < 0) minutes = '0' + timeslot.startMinutes;
    timeslot.startTime = hours + ':' + minutes + ':00';
  }

  generateEndTime(timeslot){
    var hours = '' + timeslot.endHours;
    if(timeslot.endHours < 0) hours = '0' + timeslot.endHours;
    var minutes = '' + timeslot.endMinutes;
    if(timeslot.endMinutes < 0) minutes = '0' + timeslot.endMinutes;
    timeslot.endTime = hours + ':' + minutes + ':00';
  }

  /**
   * One is not good xD
   */
  addValue(timeslot, input, step, sign, type){
    if(type == 'start'){
      if(input == 'hours'){
        timeslot.startHours += step * sign;
        if(timeslot.startHours > 23){
          timeslot.startHours = timeslot.startHours - 24;
        }
        else if(timeslot.startHours < 0) {
          timeslot.startHours = timeslot.startHours + 24;
        }
      }
      else if(input == 'minutes'){
        timeslot.startMinutes += step * sign;
        if(timeslot.startMinutes > 59){
          timeslot.startMinutes = timeslot.startMinutes - 60;
          this.addValue(timeslot, 'hours', 1, 1, type);
        }
        else if(timeslot.startMinutes < 0) {
          timeslot.startMinutes = timeslot.startMinutes + 60;
          this.addValue(timeslot, 'hours', 1, -1, type);
        }
      }
      this.generateStartTime(timeslot);
    }
    else {
      if(input == 'hours'){
        timeslot.endHours += step * sign;
        if(timeslot.endHours > 23){
          timeslot.endHours = timeslot.endHours - 24;
        }
        else if(timeslot.startHours < 0) {
          timeslot.endHours = timeslot.endHours + 24;
        }
      }
      else if(input == 'minutes'){
        timeslot.endMinutes += step * sign;
        if(timeslot.endMinutes > 59){
          timeslot.endMinutes = timeslot.endMinutes - 60;
          this.addValue(timeslot, 'hours', 1, 1, type);
        }
        else if(timeslot.endMinutes < 0) {
          timeslot.endMinutes = timeslot.endMinutes + 60;
          this.addValue(timeslot, 'hours', 1, -1, type);
        }
      }
      this.generateEndTime(timeslot);
    }
    this.setToSave(true);
  }

  addTimeslot(activityAvailability){
    if(this.skeletonServiceTimeslot != null){
      var timeslot = JSON.parse(JSON.stringify(this.skeletonServiceTimeslot));
      timeslot.id = -1;
      timeslot.idServiceAvailability = activityAvailability.id;
      timeslot.typeCapacity = this.settings.staffManagementActivated?0:1;
      this.formatTimeslot(timeslot);
      activityAvailability.timeslots.push(timeslot);
      this.modifyTimeslotIfNecessary(activityAvailability, activityAvailability.timeslots.length - 1)
    }
  }

  modifyTimeslotIfNecessary(activityAvailability, index){
    if(this.formTimeslot == null){
      this.formTimeslot = JSON.parse(JSON.stringify(activityAvailability.timeslots[index]));
      if(this.formTimeslot.exclusive || this.formTimeslot.activateExclusiveFixedCapacity || this.formTimeslot.overCapacity || this.formTimeslot.membersExclusive){
        this.formTimeslot.displayAdvancedSettings = true;
      }
    }
    this.scroll(document.getElementById('slot_settings'));
    activityAvailability.timeslots[index].settingsWillChange = !activityAvailability.timeslots[index].settingsWillChange;
  }

  modifyTimeslot(activityAvailability, index){
    this.formTimeslot = JSON.parse(JSON.stringify(activityAvailability.timeslots[index]));
    if(this.formTimeslot.exclusive || this.formTimeslot.activateExclusiveFixedCapacity || this.formTimeslot.overCapacity || this.formTimeslot.membersExclusive){
      this.formTimeslot.displayAdvancedSettings = true;
    }
    this.scroll(document.getElementById('slot_settings'));
    activityAvailability.timeslots[index].settingsWillChange = true;
  }

  duplicateTimeslot(activityAvailability, index) {
    var timeslot = JSON.parse(JSON.stringify(activityAvailability.timeslots[index]));
    timeslot.id = -1;
    timeslot.idServiceAvailability = activityAvailability.id;
    activityAvailability.timeslots.push(timeslot);
  }

  deleteTimeslot(activityAvailability, index) {
    var dateStart = this.getDateWithTime(activityAvailability.timeslots[index].startTime);
    var dateEnd = this.getDateWithTime(activityAvailability.timeslots[index].endTime);
    var startTimeString = this.datePipe.transform(dateStart, 'HH\'h\'mm');
    var endTimeString = this.datePipe.transform(dateEnd, 'HH\'h\'mm');
    swal({
      title: 'Êtes-vous sûr ?',
      text: 'Voulez-vous supprimer le créneau entre ' + startTimeString + ' - ' + endTimeString + ' ?',
      icon: 'warning',
      buttons: ['Annuler', 'Supprimer'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        activityAvailability.timeslots.splice(index, 1);
        this.setToSave(true);
        if(this.getTimeslotsNumberSelected(activityAvailability) <= 0){
          this.formTimeslot = null;
        }
      }
    });
  }

  isStaffManagementSelected(){
    return this.settings.staffManagementActivated || this.formTimeslot.typeCapacity == 0;
  }

  haveOneNewServicePrice(){
    for(var i = 0; i < this.activity.servicePrices.length; i++){
      var servicePrice = this.activity.servicePrices[i];
      if(servicePrice.id == -1){
        return true;
      }
    }
    return false;
  }

  switchActivityPrice(idActivityPrice){
    var index = this.formTimeslot.idsServicePrices.indexOf(idActivityPrice);
    if(index != -1){
      this.formTimeslot.idsServicePrices.splice(index, 1);
    }
    else {
      this.formTimeslot.idsServicePrices.push(idActivityPrice);
    }
  }

  switchTypeAccountTimeslots(idTypeAccount){
    //remove private
    var index = this.formTimeslot.typesAccounts.indexOf('private');
    if(index != -1){
      this.formTimeslot.typesAccounts.splice(index, 1);
    }

    var index = this.formTimeslot.typesAccounts.indexOf(idTypeAccount);
    if(index != -1){
      this.formTimeslot.typesAccounts.splice(index, 1);
    }
    else {
      this.formTimeslot.typesAccounts.push(idTypeAccount);
    }
  }

  addAllTypeAccountsTimeslots(){
    if(this.formTimeslot.typesAccounts.length == 0 || this.formTimeslot.typesAccounts.length == 1 && this.formTimeslot.typesAccounts[0] == 'private'){
      this.formTimeslot.typesAccounts = [];
      for(var i = 0; i < this.settings.typesAccounts.length; i++){
        var typeAccount = this.settings.typesAccounts[i];
        var index = this.formTimeslot.typesAccounts.indexOf(typeAccount.id);
        if(index == -1) {
          this.formTimeslot.typesAccounts.push(typeAccount.id);
        }
      }
    }
  }

  getTimeslotsNumberSelected(activityAvailability){
    var numberSelected = 0;
    for(var i = 0; i < activityAvailability.timeslots.length; i++){
      var timeslot = activityAvailability.timeslots[i];
      if(timeslot.settingsWillChange){
        numberSelected++;
      }
    }
    return numberSelected;
  }


  launchSaveTimeslots(activityAvailability){
    var numberSelected = this.getTimeslotsNumberSelected(activityAvailability);
    if(numberSelected <= 1){
      this.saveTimeslots(activityAvailability);
    }
    else {
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Voulez-vous appliquer les modifications sur les ' + numberSelected + ' créneaux sélectionnées ?',
        icon: 'warning',
        buttons: ['Annuler', 'Appliquer'],
        dangerMode: true,
      })
      .then((willApply) => {
        if (willApply) {
          this.saveTimeslots(activityAvailability);
        }
      });
    }
  }

  saveTimeslots(activityAvailability){
    for(var i = 0; i < activityAvailability.timeslots.length; i++){
      var timeslot = activityAvailability.timeslots[i];
      if(timeslot.settingsWillChange){
        if(!this.formTimeslot.pricesExtrasTimeslotNotActivated){
          timeslot.idsServicePrices = this.formTimeslot.idsServicePrices;
        }
        if(!this.formTimeslot.formVisibilyTimeslotNotActivated){
          timeslot.typesAccounts = this.formTimeslot.typesAccounts;
        }
        if(!this.formTimeslot.formCapacitiesTimeslotNotActivated){
          timeslot.typeCapacity = this.formTimeslot.typeCapacity;
          timeslot.equipmentsActivated = this.formTimeslot.equipmentsActivated;
          timeslot.capacity = this.formTimeslot.capacity;
          timeslot.noStaff = this.formTimeslot.noStaff;
          timeslot.noEnd = this.formTimeslot.noEnd;
          timeslot.exclusive = this.formTimeslot.exclusive;
          timeslot.maxAppointments = this.formTimeslot.maxAppointments;
          timeslot.membersExclusive = this.formTimeslot.membersExclusive;
          timeslot.activateExclusiveFixedCapacity = this.formTimeslot.activateExclusiveFixedCapacity;
          timeslot.overCapacity = this.formTimeslot.overCapacity;
        }
        if(!this.formTimeslot.formParametersTimeslotNotActivated){
          timeslot.idParameter = this.formTimeslot.idParameter;
        }
        if(!this.formTimeslot.formMentionsTimeslotNotActivated){
          timeslot.idMention = this.formTimeslot.idMention;
        }
        timeslot.settingsWillChange = false;
      }
    }
    activityAvailability.selectAllTimeslots = false;
    this.setToSave(true);
    this.cancelFormTimeslot(activityAvailability);
  }

  cancelFormTimeslot(activityAvailability){
    activityAvailability.allTimeslotsSelected = false;
    this.formTimeslot = null;
    for(var i = 0; i < activityAvailability.timeslots.length; i++){
      var timeslot = activityAvailability.timeslots[i];
      timeslot.settingsWillChange = false;
    }
  }

  resetFormTimeslot(){
    this.formTimeslot = JSON.parse(JSON.stringify(this.skeletonServiceTimeslot));
  }

  addServiceAvailability(){
    var serviceAvailability  = JSON.parse(JSON.stringify(this.skeletonServiceAvailability));
    serviceAvailability.idService = this.activity.id;
    this.activity.serviceAvailabilities.push(serviceAvailability);
  }

  synchronizeCalendar(activityAvailability, index){
    if(activityAvailability.idCalendar.length > 0){
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Synchroniser le calendrier à la règle n°' + (index+1) + ' et remplacer les dates ?',
        icon: 'warning',
        buttons: ['Annuler', 'Synchroniser'],
        dangerMode: true,
      })
      .then((result) => {
        if(result){
          var calendar = this.getCalendarById(activityAvailability.idCalendar);
          if(calendar != null){
            activityAvailability.color =calendar.color;
            activityAvailability.dates = JSON.parse(JSON.stringify(calendar.dates));
            activityAvailability.groupDates = JSON.parse(JSON.stringify(calendar.groupDates));
            activityAvailability.manyDates = JSON.parse(JSON.stringify(calendar.manyDates));
            activityAvailability.nameCalendar = calendar.name;
            activityAvailability.synchronized = true;
            this.setToSave(true);
          }
        }
      });
    }
  }

  disynchronizeCalendar(activityAvailability, index){
    if(activityAvailability.synchronized){
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Voulez-vous désynchroniser le calendrier de la règle n°' + (index+1) + ' ?',
        icon: 'warning',
        buttons: ['Annuler', 'Désynchroniser'],
        dangerMode: true,
      })
      .then((result) => {
        if(result){
          activityAvailability.synchronized = false;
          activityAvailability.idCalendar  = '';
          activityAvailability.nameCalendar = '';
          this.setToSave(true);
        }
      });
    }
  }

  copyCalendar(activityAvailability, index){
    if(activityAvailability.idCalendarTemporary.length > 0){
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Copier le calendrier pour la règle n°' + (index+1) + ' et remplacer les dates ?',
        icon: 'warning',
        buttons: ['Annuler', 'Copier'],
        dangerMode: true,
      })
      .then((result) => {
        if(result){
          var calendar = this.getCalendarById(activityAvailability.idCalendarTemporary);
          if(calendar != null){
            activityAvailability.color = calendar.color;
            activityAvailability.dates = JSON.parse(JSON.stringify(calendar.dates));
            activityAvailability.groupDates = JSON.parse(JSON.stringify(calendar.groupDates));
            activityAvailability.manyDates = JSON.parse(JSON.stringify(calendar.manyDates));
            activityAvailability.synchronized = false;
            activityAvailability.idCalendar  = '';
            activityAvailability.idCalendarTemporary  = '';
            activityAvailability.nameCalendar = calendar.name;
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

  saveCalendar(activityAvailability, index){
    if(activityAvailability.nameCalendar.length > 0){
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
            if(localCalendar.name == activityAvailability.nameCalendar){
              this.settings.calendars[i].type = this.activity.typeAvailabilities;
              this.settings.calendars[i].color = activityAvailability.color;
              this.settings.calendars[i].dates = activityAvailability.dates;
              this.settings.calendars[i].groupDates = activityAvailability.groupDates;
              this.settings.calendars[i].manyDates = activityAvailability.manyDates;
              calendar = this.settings.calendars[i];
            }
          }
          if(calendar == null){
            var idCalendar = this.generateNewId(this.settings.calendars, 'id', 'calendar_');
            calendar = {id:idCalendar, name:activityAvailability.nameCalendar, type:this.activity.typeAvailabilities, color: activityAvailability.color, dates:activityAvailability.dates, groupDates:activityAvailability.groupDates, manyDates:activityAvailability.manyDates};
            this.settings.calendars.push(calendar);
          }
          this.userService.post('calendar/:token', {calendar:JSON.stringify(calendar)}, function(data){
            activityAvailability.synchronized = true;
            activityAvailability.idCalendar  = data.id;
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

  addServiceAvailabilityIfClickOnAdd(event, tabsRules){
    if(event != null && event.nextId == 'tab-add-rule'){
      this.addServiceAvailability();
      this.selectTabsSelect(tabsRules, 'tab-rule-' + (this.activity.serviceAvailabilities.length - 1));
    }
    else {
      var index = Number(event.activeId.substring(('tab-rule-').length, event.activeId.length));
      if(!isNaN(index) && index >= 0 && index < this.activity.serviceAvailabilities.length){
        this.cancelFormTimeslot(this.activity.serviceAvailabilities[index]);
      }
    }
  }

  getTabName(activityAvailability, index){
    var name = 'Règle n°' + (index + 1);
    if(activityAvailability.name!=null && activityAvailability.name.length > 0){
      name = activityAvailability.name;
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

  duplicateServiceAvailability(index, tabsRules){
    var serviceAvailability  = JSON.parse(JSON.stringify(this.activity.serviceAvailabilities[index]));
    serviceAvailability.idService = this.activity.id;
    this.formatActivityAvailability(serviceAvailability);
    this.updateIdServiceAvailability(serviceAvailability, -1);
    this.activity.serviceAvailabilities.push(serviceAvailability);
    this.selectTabsSelect(tabsRules, 'tab-rule-' + (this.activity.serviceAvailabilities.length - 1));
  }

  updateIdServiceAvailability(serviceAvailability, id){
    serviceAvailability.id = id;
    for(var i = 0; i < serviceAvailability.timeslots.length; i++){
      var timeslot = serviceAvailability.timeslots[i];
      timeslot.id = -1;
      timeslot.idServiceAvailability = serviceAvailability.id;
    }
  }

  removeServiceAvailability(index) {
    if(this.activity.serviceAvailabilities.length > 1){
      var serviceAvailability = this.activity.serviceAvailabilities[index];
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Voulez-vous supprimer la règle n°' + (index+1) + ' ?',
        icon: 'warning',
        buttons: ['Annuler', 'Supprimer'],
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          this.activity.serviceAvailabilities.splice(index, 1);
          this.setToSave(true);
        }
      });
    }
    else {
      swal("Dernière règle de l'agenda" ,  "Vous ne pouvez pas supprimer la dernière règle de l'agenda" ,  "error" );
    }
  }

  setServiceAvailabilityDates(activityAvailability, dates){
    activityAvailability.dates = dates;
    this.setToSave(true);
  }

  setServiceAvailabilityGroupDates(activityAvailability, dates){
    activityAvailability.groupDates = dates;
    this.setToSave(true);
  }

  setServiceAvailabilityManyDates(activityAvailability, dates){
    activityAvailability.manyDates = dates;
    this.setToSave(true);
  }

  openManyDatesDialog(content, activityAvailability, index){
    if(index > -1) {
      this.formManyDates.dates = JSON.parse(JSON.stringify(activityAvailability.manyDates[index]));
    }
    this.formManyDates.activityAvailability = activityAvailability;
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result!=null){
        if(index == -1){
          activityAvailability.manyDates.push(this.formManyDates.dates);
        }
        else {
          activityAvailability.manyDates[index] = JSON.parse(JSON.stringify(this.formManyDates.dates));
        }
        this.formManyDates.dates = [];
        this.setToSave(true);
      }
    }, (reason) => {
    });
  }

  deleteManyDates(activityAvailability, index){
    swal({
      title: 'Êtes-vous sûr ?',
      text: 'Voulez-vous supprimer ce groupe de date ?',
      icon: 'warning',
      buttons: ['Annuler', 'Enlever'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        activityAvailability.manyDates.splice(index, 1);
        this.setToSave(true);
      }
    });
  }

  setServiceFormManyDates(dates){
    this.formManyDates.dates = dates;
  }

  /*************************************************************************
  ****************************** ADVANCE PAYMENT ***************************
  ***************************************************************************/
  addAdvancePaymentAccountType(){
    this.activity.advancePaymentByAccountTypes.push({
      typeAccount:'',
      advancePayment: 100
    })
  }

  deleteAdvancePaymentAccountType(index){
    this.activity.advancePaymentByAccountTypes.splice(index, 1);
  }

  /*************************************************************************
  ******************************* MENTION **********************************
  ***************************************************************************/

  addMention(){
    var idMention = this.generateNewId(this.settings.timeslots_mentions, 'id', 'timeslots_mentions_');
    var mention = {id:idMention, name:{}, backgroundColor: '#fe6d4c'};
    mention.name[this.global.currentLanguage] = '';
    this.settings.timeslots_mentions.push(mention);
  }

  isOkMentions(){
    for(var i = 0; i < this.settings.timeslots_mentions.length; i++){
      var mention = this.settings.timeslots_mentions[i];
      if(mention.name[this.global.currentLanguage] != null &&
        mention.name[this.global.currentLanguage].length == 0){
        return false;
      }
    }
    return true;
  }

  openMentionDialog(content){
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result!=null){
        this.userService.post('settingsLite/:token', {timeslots_mentions:JSON.stringify(this.settings.timeslots_mentions)}, function(data){

        }, function(error){
          this.launchActionSave = false;
          var text = 'Impossible de sauvegarder les mentions';
          if(error != null && error.message != null && error.message.length > 0){
            text += ' (' + error.message + ')';
          }
          swal({ title: 'Erreur', text: text, icon: 'error'});
        });
      }
    }, (reason) => {
    });
  }

  /*************************************************************************
  ***************************************************************************
  ***************************************************************************/
  haveOneServicePriceActivated(){
    for(var i = 0; i < this.activity.servicePrices.length; i++){
      var servicePrice = this.activity.servicePrices[i];
      if(!servicePrice.extra && servicePrice.activated){
        return true;
      }
    }
    return false;
  }

  haveOneServiceAvailability(){
    return this.activity.serviceAvailabilities.length > 0;
  }

  isOkActivityName(){
    return this.activity.name[this.global.currentLanguage] != null && this.activity.name[this.global.currentLanguage].length > 0;
  }

  isOkActivityCalendar(){
     for(var i = 0; i < this.activity.serviceAvailabilities.length; i++){
       var serviceAvailability = this.activity.serviceAvailabilities[i];
       if(serviceAvailability.timeslots.length == 0){ return false; }
       if(serviceAvailability.groupDates.length == 0 && serviceAvailability.dates.length == 0 && serviceAvailability.manyDates.length == 0){ return false; }
     }
     return true;
  }

  isOkActivity(){
    return this.isOkActivityName() &&
      this.isOkActivityCalendar() &&
      !this.isSlugAlreadyUsed(this.activity.slug) &&
      !this.isSlugNull(this.activity.slug) &&
      this.haveOneServicePriceActivated() &&
      this.haveOneServiceAvailability();
  }

  rewriteUrl(){
    var tab = this.route.snapshot.paramMap.get('tab');
    if(tab == 'duplicate') tab = 'modify';
    this.navService.changeRouteWithoutReload('/activity/' + this.activity.id + '/' + tab);
  }

  save(){
    if(!this.canDeactivate() && !this.launchActionSave){
      this.launchActionSave = true;
      this.userService.post('activity/:token', {activity:JSON.stringify(this.activity)}, function(data){
        this.launchActionSave = false;
        this.activity = data.activity;
        this.activities = data.activities;
        this.slugs = data.slugs;
        this.formatActivity();
        this.rewriteUrl();
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

  checkServiceHasChanged(){
    if(!this.launchActionSave && this.activity.id != -1){
      this.userService.post('activityHasChanged/:token', {idService:this.activity.id, modificationDate: this.activity.modificationDate}, function(data){
        this.serviceHasChanged = data.result;
      }.bind(this), function(error){
      }.bind(this));
    }
  }


}
