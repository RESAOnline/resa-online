import { Component, OnInit } from '@angular/core';

import { UserService } from '../services/user.service';
import { NavService } from '../services/nav.service';
import { GlobalService } from '../services/global.service';

declare var is:any;

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {

  public version = '';
  public linkNewUpdate:string = '';
  public staff_word_many = {};
  public initFailed = false;
  public RESANewsLastViewNumber = 0;

  constructor(private userService: UserService, private navService:NavService, public global:GlobalService) {
  }

  ngOnInit(): void {
    this.userService.setServerRESA(this.navService.generateServerRESA());
    this.navService.getToken(function(token){
      this.userService.getUserByToken(token, function(data){
        this.initFailed = false;
        this.version = data.settings.version;
        this.linkNewUpdate = data.settings.linkNewUpdate;
        this.staff_word_many = data.settings.staff_word_many;
        this.global.backendV2Activated = data.settings.backendV2Activated;
        this.global.supportURL = data.settings.supportURL;
        this.global.wpDebugMode = data.settings.wpDebugMode,
        this.global.staffManagement = data.settings.staffManagement;
        this.global.equipmentsManagement = data.settings.equipmentsManagement;
        this.global.groupsManagement = data.settings.groupsManagement;
        this.global.caisseOnline = data.settings.caisse_online;
        this.global.stripeConnect = data.settings.stripeConnect;
        this.global.swiklyLink = data.settings.swikly_link;
        this.global.places = data.settings.places;
        this.RESANewsLastViewNumber = data.settings.RESANewsLastViewNumber;
        this.global.initializeFilterSettings(data.user.filterSettings, data.settings.places);
      }.bind(this), function(error){
        this.initFailed = true;
      }.bind(this));
    }.bind(this));
  }

  isUserNull(){
    return this.userService.isUserNull();
  }

  isBrowserCompatibility(){
    return this.global.isBrowserCompatibility();
	}

  getCurrentUser(){
    return this.userService.getCurrentUser();
  }

  getRoleName(){
    var role = this.getCurrentUser().role;
    var name = role;
    if(name == 'administrator') name = 'Administrateur';
    if(name == 'RESA_Manager') name = 'Manager RESA';
    if(name == 'RESA_Staff') name = 'Membre du personnel';
    return name;
  }

  getRESABackendLink(){
    return this.userService.getCurrentHost() + '/wp-admin/admin.php?page=resa_appointments';
  }

  getCaisseOnlineLink(){
    return this.global.getCaisseOnlineLink();
  }

  getWpBackendLink(){
    return this.userService.getCurrentHost() + '/wp-admin/';
  }

  goStripeConnect(){
    if(this.global.stripeConnect.activated){
      this.navService.goToLink('https://dashboard.stripe.com/');
    }
    else {
      this.navService.changeRoute('settings/payments', {tab:'mop', tab2:'stripeConnect'});
    }
  }

  goCaisseOnline(){
    this.navService.goToLink(this.global.getCaisseOnlineLink());
  }
  goSwikly(){
    this.navService.goToLink(this.global.swiklyLink);
  }

  isDisplayMenu(){ return !this.navService.isSameUrl('installation'); }
  isDisplayFooter(){ return !this.navService.isSameUrl('installation') && !this.navService.isSameUrl('planningBookings') && !this.navService.isSameUrl('planningMembers') && !this.navService.isSameUrl('planningGroups'); }

  goActivities(){ this.navService.changeRoute('activities'); }
  isActivities(){ return this.navService.isSameUrl('activities') || this.navService.isSameUrl('activity'); }

  goMembers(){ this.navService.changeRoute('members'); }
  isMembers(){ return this.navService.isSameUrl('members'); }

  goEquipments(){ this.navService.changeRoute('equipments'); }
  isEquipments(){ return this.navService.isSameUrl('equipments'); }

  goGroups(){ this.navService.changeRoute('groups'); }
  isGroups(){ return this.navService.isSameUrl('groups'); }

  goNotifications(){ this.navService.changeRoute('notifications'); }
  isNotifications(){ return this.navService.isSameUrl('notifications'); }

  goForms(){ this.navService.changeRoute('forms'); }
  isForms(){ return this.navService.isSameUrl('forms'); }

  goSettings(){ this.navService.changeRoute('settings'); }
  isSettings(){ return this.navService.isSameUrl('settings'); }

  goCalendars(){ this.navService.changeRoute('calendars'); }
  isCalendars(){ return this.navService.isSameUrl('calendars'); }

  goReductions(){ this.navService.changeRoute('reductions'); }
  isReductions(){ return this.navService.isSameUrl('reductions'); }

  goVouchers(){ this.navService.changeRoute('vouchers'); }
  isVouchers(){ return this.navService.isSameUrl('vouchers'); }

  goStatisticsMembers(){ this.navService.changeRoute('statisticsMembers'); }
  isStatisticsMembers(){ return this.navService.isSameUrl('statisticsMembers'); }

  goStatisticsBookings(){ this.navService.changeRoute('statisticsBookings'); }
  isStatisticsBookings(){ return this.navService.isSameUrl('statisticsBookings'); }

  goStatisticsActivities(){ this.navService.changeRoute('statisticsActivities'); }
  isStatisticsActivities(){ return this.navService.isSameUrl('statisticsActivities'); }

  goPlanning(){
    if(this.isRESAStaff()){
      if(!this.global.groupsManagement) this.navService.changeRoute('planningMembers');
      else this.navService.changeRoute('planningGroups');
    }
    else this.navService.changeRoute(this.global.filterSettings.favPage);
  }

  goCustomers(){
    this.navService.changeRoute('customers');
  }

  isSeeSettings(){
    return this.getCurrentUser().seeSettings || this.getCurrentUser().role == 'administrator';
  }

  isRESAManager(){
    return this.getCurrentUser().role == 'administrator' || this.getCurrentUser().role == 'RESA_Manager';
  }

  isRESAStaff(){
    return this.getCurrentUser().role == 'RESA_Staff';
  }




}
