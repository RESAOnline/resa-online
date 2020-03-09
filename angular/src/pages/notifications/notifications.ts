import { Component, OnInit } from '@angular/core';
import { NgModel } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';

declare var swal: any;
declare var tinyMCE:any;

@Component({
  selector: 'page-notifications',
  templateUrl: 'notifications.html',
  styleUrls:['./notifications.css']
})

export class NotificationsComponent extends ComponentCanDeactivate implements OnInit {

  public settings:any = null;

  public currentEmail = '';
  public sending = false;
  public displayNotifications = [];
  public formNotifications = {order:'', search:''};
  public editionMode = false;
  public formNotification = null;
  public formPreviewNotification = null;

  public displayNotificationsTemplates = [];
  public formNotificationsTemplates = {order:'', search:''};

  public launchAction = false;
  private toSave = {notifications:false, templates:false, senders: false, blocks:false};

  public currentCustomShortcodeParams = null;

  constructor(private userService:UserService, private navService:NavService, private global:GlobalService, private modal: NgbModal) {
    super('Notifications emails');
  }

  ngOnInit(): void {
    this.userService.get('notifications/:token', function(data){
      this.currentEmail = this.userService.getCurrentUser().email;
      if(data.settings) {
        this.settings = data.settings;
        if(this.settings.resa_senders.length == 0){
          this.addRESASender();
        }
      }
      this.generateLocalIdTemplate();
      this.calculateDisplayNotifications();
      this.calculateDisplayNotificationsTemplates();
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des notifications';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  generateLocalIdTemplate(){
    for(var i =0; i < this.settings.notifications_templates.length;i++){
      var notification = this.settings.notifications_templates[i];
      notification.localId = 'notification_template_' + i;
    }
  }

  /**
   *
   */
  canDeactivate():boolean{
    return !this.toSave.notifications && !this.toSave.templates && !this.toSave.senders && !this.toSave.blocks;
  }

  /**
   * set the save boolean
   */
  setToSave(field, value){
    this.toSave[field] = value;
    if(value) this.changeTitle();
    else this.resetTitle();
  }

  getToSave(field){
    return this.toSave[field];
  }

  getRepeat(number){ return new Array(number); }

  stopEditionMode(){
    this.formPreviewNotification = null;
    this.editionMode = false;
  }

  getDomainName(){
    return window.location.hostname;
  }

  emailOnSameDomainName(email){
    if(!this.global.isEmailValid(email)) return false;
    var tokens = email.split('@');
    var tokens = tokens[1].split('.');
    return tokens[0].indexOf(this.getDomainName()) > -1;
  }


  /*************************************************************************
   ***************************** NOTIFICATIONS ***********************************
   ****************************************************************/

   displayNotificationsOrderBy(order){
     if(this.formNotifications.order == order){
       this.formNotifications.order = 'r' + order;
     }
     else {
       this.formNotifications.order = order;
     }
     this.calculateDisplayNotifications();
   }

   calculateDisplayNotifications(){
     if(this.formNotifications.search.length == 0){
       this.displayNotifications = this.settings.notifications;
     }
     else {
       var notifications = [];
       for(var i = 0; i < this.settings.notifications.length; i++){
         var notification = this.settings.notifications[i];
         if(notification.name.toLowerCase().indexOf(this.formNotifications.search.toLowerCase()) != -1 ||
          notification.receiver.toLowerCase().indexOf(this.formNotifications.search.toLowerCase()) != -1){
           notifications.push(notification);
         }
       }
       this.displayNotifications = notifications;
     }
     if(this.formNotifications.order.length > 1){
       this.displayNotifications.sort(function(notif1, notif2){
         var asc = 1;
         if(this.formNotifications.order[0] == 'r' && this.formNotifications.order != 'receiver') asc = -1;
         if(this.formNotifications.order == 'name' || this.formNotifications.order == 'rname'){
           if(notif1.name < notif2.name) return -1 * asc;
           if(notif1.name > notif2.name) return 1 * asc;
         }
         else if(this.formNotifications.order == 'id' || this.formNotifications.order == 'rid'){
           if(notif1.id < notif2.id) return -1 * asc;
           if(notif1.id > notif2.id) return 1 * asc;
         }
         else if(this.formNotifications.order == 'receiver' || this.formNotifications.order == 'rreceiver'){
           if(notif1.receiver < notif2.receiver) return -1 * asc;
           if(notif1.receiver > notif2.receiver) return 1 * asc;
         }
         else if(this.formNotifications.order == 'activated' || this.formNotifications.order == 'ractivated'){
           var value1 = notif1.activated?1:0;
           var value2 = notif2.activated?1:0;
           return (value1 - value2) * asc;
         }
         return 0;
       }.bind(this));
     }
   }


  /*************************************************************************
   ***************************** NOTIFICATION MODAL ******************************
   ****************************************************************/
   openNotification(content, notification){
     this.formNotification = JSON.parse(JSON.stringify(notification));
     if(this.formNotification.text == null){
       this.formNotification.text = {};
     }
     this.previewNotification(this.formNotification);
     this.modal.open(content, { size: 'lg', backdrop:'static' }).result.then((result) => {
       if(result == 'save'){
         for(var i = 0; i < this.settings.notifications.length; i++){
           var localNotification = this.settings.notifications[i];
           if(localNotification.id == this.formNotification.id){
             this.settings.notifications[i] = JSON.parse(JSON.stringify(this.formNotification));
           }
         }
         this.setToSave('notifications', true);
         this.save();
         this.calculateDisplayNotifications();
         this.stopEditionMode();
       }
     }, (reason) => {
       this.stopEditionMode();
     });
   }

   addPicture(content){
     this.modal.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
       if(result!=null && result.type == 'image'){
         tinyMCE.activeEditor.insertContent('<img src="' + result.src + '" />');
       }
       else if(result!=null && result.type == 'url'){
         tinyMCE.activeEditor.insertContent('<a href="' + result.src + '" target="_blank" rel="noopener">' + result.src +'</a>');
       }
     }, (reason) => {
     });
   }

   insertPicture(image, c){
     c(image)
   }
   /*************************************************************************
    ***************************** NOTIFICATIONS TEMPLATE ***********************************
    ****************************************************************/

    displayNotificationsTemplatesOrderBy(order){
      if(this.formNotificationsTemplates.order == order){
        this.formNotificationsTemplates.order = 'r' + order;
      }
      else {
        this.formNotificationsTemplates.order = order;
      }
      this.calculateDisplayNotificationsTemplates();
    }

    calculateDisplayNotificationsTemplates(){
      if(this.formNotificationsTemplates.search.length == 0){
        this.displayNotificationsTemplates = this.settings.notifications_templates;
      }
      else {
        var notifications = [];
        for(var i = 0; i < this.settings.notifications_templates.length; i++){
          var notification = this.settings.notifications_templates[i];
          if(notification.name.toLowerCase().indexOf(this.formNotificationsTemplates.search.toLowerCase()) != -1){
            notifications.push(notification);
          }
        }
        this.displayNotificationsTemplates = notifications;
      }
      if(this.formNotificationsTemplates.order.length > 0){
        this.displayNotificationsTemplates.sort(function(notif1, notif2){
          var asc = 1;
          if(this.formNotificationsTemplates.order[0] == 'r') asc = -1;
          if(this.formNotificationsTemplates.order == 'name' || this.formNotificationsTemplates.order == 'rname'){
            if(notif1.name.toLowerCase() < notif2.name.toLowerCase()) return -1 * asc;
            if(notif1.name.toLowerCase() > notif2.name.toLowerCase()) return 1 * asc;
          }
          return 0;
        }.bind(this));
      }
    }

    getDisplaySubjectPart(subject:string):string{
      var result = subject;
      var max = 60;
      if(subject != null && subject.length > max){
        result = subject.substring(0, max - 3) + '...';
      }
      return result;
    }

    /*************************************************************************
     ***************************** NOTIFICATION MODAL ******************************
    ****************************************************************/
    addNotificationTemplate(content){
      var notification = { name:'', localId:'', subject:{}, message:{} };
      notification.localId = 'notification_template_' + this.settings.notifications_templates.length;
      this.editionMode = true;
      this.openNotificationTemplate(content, notification);
    }

    duplicateNotificationTemplate(content, notification){
      notification = JSON.parse(JSON.stringify(notification));
      notification.localId = 'notification_template_' + this.settings.notifications_templates.length;
      this.openNotificationTemplate(content, notification);
    }

    openNotificationTemplate(content, notification){
       this.formNotification = JSON.parse(JSON.stringify(notification));
       this.formNotification.isTemplate = true;
       if(this.formNotification.message == null){
         this.formNotification.message = {};
       }
       this.previewNotification(notification);
       this.modal.open(content, { size: 'lg', backdrop:'static'}).result.then((result) => {
         if(result == 'save'){
           delete this.formNotification.isTemplate;
           var found = false;
           for(var i = 0; i < this.settings.notifications_templates.length; i++){
             var localNotification = this.settings.notifications_templates[i];
             if(localNotification.localId == this.formNotification.localId){
               found = true;
               this.settings.notifications_templates[i] = JSON.parse(JSON.stringify(this.formNotification));
             }
           }
           if(!found){
             this.settings.notifications_templates.push(JSON.parse(JSON.stringify(this.formNotification)));
           }
           this.setToSave('templates', true);
           this.save();
           this.calculateDisplayNotificationsTemplates();
           this.stopEditionMode();
         }
       }, (reason) => {
         this.stopEditionMode();
       });
     }

     deleteNotificationTemplate(notification) {
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Voulez-vous supprimer le template ' + notification.name + ' ?',
         icon: 'warning',
         buttons: ['Annuler', 'Supprimer'],
         dangerMode: true,
       })
       .then((willDelete) => {
         if (willDelete) {
           var newNotifications = [];
           for(var i = 0; i < this.settings.notifications_templates.length; i++){
             var local = this.settings.notifications_templates[i];
             if(local.localId != notification.localId){
               newNotifications.push(local);
             }
           }
           this.settings.notifications_templates = newNotifications;
           this.generateLocalIdTemplate();
           this.calculateDisplayNotificationsTemplates();
           this.setToSave('templates', true);
         }
       });
     }

   /*************************************************************************
    ***************************** RESA Sender ***********************************
    ****************************************************************/
   addRESASender(){
     this.settings.resa_senders.push({
       sender_name:'',
       sender_email:'',
       idPlace:''
     });
   }

   deleteRESASender(index){
     this.settings.resa_senders.splice(index, 1);
     this.setToSave('senders', true);
   }

   sendNotification(subject, message){
     if(!this.sending){
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Prévisualisé cette notification ?',
         icon: 'warning',
         buttons: ['Annuler', 'Envoyer'],
         dangerMode: true,
         content: {
           element: "input",
           attributes: {
             placeholder: "Email",
             value:this.currentEmail,
             type: "text",
           },
         },
       })
       .then((email) => {
         if(email != null){
           this.sending = true;
           if(email == '') email = this.currentEmail;
           this.currentEmail = email;
           var data = {email:email, subject: subject, message:message};
           this.userService.post('sendNotification/:token', data, function(data){
             swal({ title: 'Ok', text: 'Mail envoyé', icon:'success'});
              this.sending = false;
           }.bind(this), function(error){
             var text = 'Impossible de sauvegarder les données';
             if(error != null && error.message != null && error.message.length > 0){
               text += ' (' + error.message + ')';
             }
             swal({ title: 'Erreur', text: text, icon: 'error'});
            this.sending = false;
           }.bind(this));
         }
      });
      }
   }

   previewNotification(notification){
     if(!this.launchAction){
       this.launchAction = true;
       var text = notification.text;
       if(notification.text == null) text = notification.message;
       var data = {subject:JSON.stringify(notification.subject), message:JSON.stringify(text)}
       this.userService.post('previewNotification/:token', data, function(data){
         this.launchAction = false;
         this.editionMode = false;
         this.formPreviewNotification = data;
       }.bind(this), function(error){
         this.launchAction = false;
         var text = 'Impossible de prévisualisé le message';
         if(error != null && error.message != null && error.message.length > 0){
           text += ' (' + error.message + ')';
         }
         swal({ title: 'Erreur', text: text, icon: 'error'});
       }.bind(this));
     }
   }

   /**
    * Custom blocks
    */

   openImagesCustomShorcodeParams(content, currentCustomShortcodeParams){
     this.currentCustomShortcodeParams = currentCustomShortcodeParams;
     this.modal.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
       if(result!=null && result.type == 'image'){
         this.currentCustomShortcodeParams.value = result.src;
       }
       else if(result!=null && result.type == 'url'){
         this.currentCustomShortcodeParams.value = result.src;
       }
       this.setToSave('blocks', true);
       this.currentCustomShortcodeParams = null;
     }, (reason) => {
     });
   }

   setImageCustomShorcodeParams(image, c){
     c(image)
   }

   /*************************************************************************
    ***************************** SAVE ***********************************
    ****************************************************************/


  save(){
    if(!this.canDeactivate() && !this.launchAction){
      var data = {notifications:null, notifications_templates: null, resa_senders:null, custom_shortcodes:null};
      if(this.toSave.notifications) data.notifications = JSON.stringify(this.settings.notifications);
      else delete data.notifications;
      if(this.toSave.templates) data.notifications_templates = JSON.stringify(this.settings.notifications_templates);
      else delete data.notifications_templates;
      if(this.toSave.senders) data.resa_senders = JSON.stringify(this.settings.resa_senders);
      else delete data.resa_senders;
      if(this.toSave.blocks) data.custom_shortcodes = JSON.stringify(this.settings.custom_shortcodes);
      else delete data.custom_shortcodes;
      this.launchAction = true;
      this.userService.post('notifications/:token', data, function(data){
        this.launchAction = false;
        this.setToSave('notifications', false);
        this.setToSave('templates', false);
        this.setToSave('senders', false);
        this.setToSave('blocks', false);
      }.bind(this), function(error){
        this.launchAction = false;
        var text = 'Impossible de sauvegarder les données';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }
}
