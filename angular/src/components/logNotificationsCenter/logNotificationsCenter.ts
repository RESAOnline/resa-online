import { OnChanges, OnInit, Component, EventEmitter, SimpleChanges  } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { GlobalService } from '../../services/global.service';
import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { NotificationComponent } from '../../components/notification/notification';
import { ReceiptComponent } from '../../components/receipt/receipt';
import { BookingDialogComponent } from '../../components/bookingDialog/bookingDialog';

import { DatePipe, formatDate } from '@angular/common';


declare var swal: any;

@Component({
  selector: 'logNotificationsCenter',
  templateUrl: 'logNotificationsCenter.html',
  styleUrls:['./logNotificationsCenter.css']
})

export class LogNotificationsCenterComponent implements OnChanges, OnInit {

  settings = null;
  logNotificationsWaitingNumber = 0;
  logNotificationsCenterDisplayed = false;
  lastModificationDateBooking = null;
  logNotifications = [];
  getLogNotificationsLaunched = false;
  criticityDisplayed = 0;
  limit = 10;
  setIntervalTimer = null;

  constructor(public global:GlobalService, private userService:UserService, private navService:NavService, private modalService: NgbModal) {

  }

  ngOnInit(): void {
    this.firstLoad();
  }

  ngOnChanges(changes: SimpleChanges) {

  }

  firstLoad():void {
    this.getLogNotificationsLaunched = true;
    this.userService.get('initLogNotificationCenter/:token', (data) => {
      this.settings = data.settings;
      this.logNotificationsWaitingNumber = data.logNotificationsWaitingNumber;
      this.getLogNotificationsLaunched = false;
      this.addNewLogNotifications(data.logNotifications);
      clearInterval(this.setIntervalTimer);
      this.setIntervalTimer = setInterval(() => {
        this.checkUpdateLogNotifications();
      }, 15000);
    }, (error) => {
      this.getLogNotificationsLaunched = false;
      var text = 'Impossible de récupérer les données des notifications';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  switchLogNotificationCenter(){
    if(this.logNotificationsCenterDisplayed){
      this.closeLogNotificationCenter();
    }
    else {
      this.logNotificationsCenterDisplayed = true;
      this.openLogNotificationsCenter();
      //Listen click
      /*jQuery('body').click(() => { this.closeLogNotificationCenter(); });
      jQuery('#notif_center_wrapper').click((event) => { event.stopPropagation(); });
      jQuery('.resa_popup').click((event) => { event.stopPropagation(); });*/
    }
  }

  closeLogNotificationCenter(){
    this.logNotificationsCenterDisplayed = false;
    //Deactive listened
    /*jQuery('body').unbind('click');
    jQuery('#notif_center_wrapper').unbind('click');*/
  }


  openLogNotificationsCenter(){
    this.criticityDisplayed = 0;
    this.seeAllLogNotifications();
  }

  loadLogNotificationsAnteriors(criticity, limit = -1){
    if(limit == -1) limit = this.limit;
    var lastIdLogNotifications = 0;
    if(criticity != -1){
      for(var i = this.logNotifications.length - 1; i >= 0; i--){
        var log = this.logNotifications[i];
        if(criticity == log.criticity){
          lastIdLogNotifications = log.id;
          break;
        }
      }
    }
    else {
      lastIdLogNotifications = this.logNotifications[this.logNotifications.length - 1].id;
    }
    this.getLogNotifications(limit, criticity, lastIdLogNotifications);
  }

  openLogNotificationsPage(){
    if(this.logNotifications.length > 0){
      var lastIdLogNotifications = this.logNotifications[0].id;
      this.logNotifications = [];
      this.getLogNotifications(100, -1, lastIdLogNotifications + 1);
    }
  }

  getLogNotifications(limit, criticity, lastIdLogNotifications){
    this.getLogNotificationsLaunched = true;
    this.userService.post('getLogNotifications/:token', {limit:limit, criticity:criticity, lastIdLogNotifications:lastIdLogNotifications}, (data) => {
      this.getLogNotificationsLaunched = false;
      for(var i = 0; i < data.logNotifications.length; i++){
        var logNotification = data.logNotifications[i];
        this.logNotifications.push(logNotification);
      }
    }, (error) => {
    this.getLogNotificationsLaunched = false;
      var text = 'Impossible de récupérer les données des notifications';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }


  addNewLogNotifications(logNotifications){
    for(var i = logNotifications.length - 1; i >= 0; i--){
      var newLogNotification = logNotifications[i];
      if(this.getLogNotificationById(newLogNotification.id) == null){
        this.logNotifications.unshift(newLogNotification);
      }
    }
  }

  getLastIdLogNotifications(){
    if(this.logNotifications.length > 0){
      return this.logNotifications[0].id;
    }
    return 0;
  }

  getLogNotificationById(id){
    for(var i = 0; i < this.logNotifications.length; i++){
      if(this.logNotifications[i].id == id){
        return this.logNotifications[i];
      }
    }
    return null;
  }

  getListPlaces(places){
    var text = '';
    for(var i = 0; i < places.length; i++){
      var place = this.settings.places.find(element => element.id == places[i])
      if(place != null){
        if(text.length > 0) text += ', ';
        text += this.global.getTextByLocale(place.name);
      }
    }
    return text;
  }

  getRemainingTime(log, dateFormat, timeFormat){
    var actualDate = new Date();
    var logDate = new Date(log.creationDate);
    var text = 'Il y a ';
    var seconds = (actualDate.getTime() - logDate.getTime()) / 1000;
    if(seconds < 60) text += '<1min';
    else if(seconds < 3600) text += Math.floor(seconds / 60) + 'min';
    else if(seconds < 3600 * 12) text += Math.floor(seconds / 3600) + 'h';
    else text = formatDate(logDate, 'yyyy-MM-dd HH:mm', 'fr');
    return text;
  }

  getType(log){
    var type = 'type3';
    var actualDate = new Date();
    var logExpiration = new Date(log.expirationDate);
    var days = ((logExpiration.getTime() - actualDate.getTime()) / 1000);
    if(!log.clicked && days <= 3600 * 24 * 4){ type = 'priorite_haute';	}
    else if(!log.clicked){ type = 'priorite_basse'; }
    return type;
  }

  isRecent(log){
    var type = 'vu';
    var actualDate = new Date();
    var logExpiration = new Date(log.creationDate);
    var days = ((logExpiration.getTime() - actualDate.getTime()) / 1000);
    return days <= 3600 * 24;
  }

  clickOnLogNotifications(log){
    log.clicked = true;
    this.getLogNotificationsLaunched = true;
    this.userService.post('clickOnLogNotifications/:token', {idLogNotification:log.id}, (data) => {
      this.getLogNotificationsLaunched = false;
      if(log.idBooking == -1){
        this.navService.changeRoute('customer/' + log.idCustomer);
      }
      else {
        if(data.booking.id > -1){
          const modalRef = this.modalService.open(BookingDialogComponent, { windowClass: 'mlg' });
          modalRef.componentInstance.setBookingAndSettings(data.booking, this.settings);
        }
        else {
          swal({ title: 'Erreur', text: 'Réservation non trouvée, elle a peut-être été supprimée ?', icon: 'error'});
        }
      }
    }, (error) => {
      this.getLogNotificationsLaunched = false;
      var text = 'Impossible de récupérer les données de la notification';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }



  unreadLogNotification(event, log){
    log.clicked = false;
    event.stopPropagation();
    this.userService.post('unreadLogNotification/:token', {idLogNotification:log.id}, (data) => {
      this.getLogNotificationsLaunched = false;
    }, (error) => {
      this.getLogNotificationsLaunched = false;
      var text = 'Impossible de modifier les données de la notification';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }


  seeAllLogNotifications(){
    if(this.logNotificationsWaitingNumber > 0){
      this.logNotificationsWaitingNumber = 0;
      this.userService.post('seeAllLogNotifications/:token', {}, (data) => {
        this.logNotificationsWaitingNumber = 0;
      }, (error) => {
        var text = 'Impossible de modifier les données de la notification';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
  }

  clickAllLogNotifications(){
    if(this.logNotifications.length > 0){
      for(var i = 0; i < this.logNotifications.length; i++){
        var log = this.logNotifications[i];
        log.clicked = true;
      }
      this.userService.post('clickAllLogNotifications/:token', {lastId: this.logNotifications[this.logNotifications.length - 1].id}, (data) => {
      }, (error) => {
        var text = 'Impossible de mettre lu toutes les données de la notification';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
  }

  checkUpdateLogNotifications(){
    this.userService.post('checkUpdateLogNotifications/:token', {lastIdLogNotifications: this.getLastIdLogNotifications()}, (data) => {
      this.logNotificationsWaitingNumber = data.logNotificationsWaitingNumber;
      this.addNewLogNotifications(data.logNotifications);
    }, (error) => {
      /*var text = 'Impossible de recupérer les données de la notification';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});*/
    });
  }

}
