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
  selector: 'page-groups',
  templateUrl: 'groups.html',
  styleUrls:['./groups.css']
})

export class GroupsComponent extends ComponentCanDeactivate implements OnInit {

  public groups:any[] = [];
  public settings:any = null;
  public services:any[] = [];

  public displayGroups = [];
  public formGroups = {order:'', search:'', places:[]};

  private skeletonStaticGroup = null;
  public formGroup = null;

  public launchActionSave = false;
  private toSave = false;


  constructor(private userService:UserService, private navService:NavService, private global:GlobalService, private modal: NgbModal) {
    super('Groupes');
  }


  ngOnInit(): void {
    this.userService.get('groups/:token', function(data){
      if(data.groups) this.groups = data.groups;
      if(data.services) this.services = data.services;
      if(data.settings) {
        this.settings = data.settings;
        for(var i = 0; i < this.settings.places.length; i++){
          if(this.global.filterSettings == null || this.global.filterSettings.places == null || this.global.filterSettings.places[this.settings.places[i].id]){
            this.switchFormGroupsPlace(this.settings.places[i].id);
          }
        }
      }
      this.skeletonStaticGroup = data.skeletonStaticGroup;
      if(this.skeletonStaticGroup != null){
        this.formGroup = JSON.parse(JSON.stringify(this.skeletonStaticGroup));
      }
      this.generateLocalId();
      this.calculateDisplayGroups();
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des groupes';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
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

  generateLocalId(){
    for(var i = 0; i < this.groups.length; i++){
      var group = this.groups[i];
      group.localId = 'group_' + i;
    }
  }

  updateGroup(group){
    group.isUpdated = true;
    this.setToSave(true);
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

  getServiceById(idService){
    if(this.services != null){
      for(var i = 0; i < this.services.length; i++){
        var service = this.services[i];
        if(service.id == idService){
          return service;
        }
      }
    }
    return null;
  }

  getServiceName(idService){
    var service = this.getServiceById(idService);
    if(service != null){
      return this.global.getTextByLocale(service.name, this.global.currentLanguage);
    }
    return '';
  }

  getServicePriceById(service, idServicePrice){
    for(var i = 0; i < service.prices.length; i++){
      var price = service.prices[i];
      if(price.id == idServicePrice){
        return price;
      }
    }
    return null;
  }

  getParticipantFields(group){
    var service = this.getServiceById(group.idService);
    var participantFields = null;
    if(service != null && service.prices.length > 0 && service.prices[0] != null){
      var servicePrice = service.prices[0];
      if(group.idServicePrices.length > 0){
        var local = this.getServicePriceById(service, group.idServicePrices);
        if(this.getServicePriceById(service, group.idServicePrices)){
          servicePrice = local;
        }
      }
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

  getParticipantParametersById(idParticipantParameters){
    for(var i = 0; i < this.settings.form_participants_parameters.length; i++){
      var participantParameters = this.settings.form_participants_parameters[i];
      if(participantParameters.id == idParticipantParameters){
        return participantParameters;
      }
    }
    return null;
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
   ***************************** GROUPS ***********************************
   ****************************************************************/

   switchFormGroupsPlace(idPlace){
     var index = this.formGroups.places.indexOf(idPlace);
     if(index != -1){
       this.formGroups.places.splice(index, 1);
     }
     else {
       this.formGroups.places.push(idPlace);
     }
     this.calculateDisplayGroups();
   }

   displayGroupsOrderBy(order){
     if(this.formGroups.order == order){
       this.formGroups.order = 'r' + order;
     }
     else {
       this.formGroups.order = order;
     }
     this.calculateDisplayGroups();
   }

   calculateDisplayGroups(){
     if(this.formGroups.search.length == 0 && this.settings.places.length == 0){
       this.displayGroups = this.groups;
     }
     else {
       var groups = [];
       for(var i = 0; i < this.groups.length; i++){
         var group = this.groups[i];
         var filterName = group.name.toLowerCase().indexOf(this.formGroups.search.toLowerCase()) != -1;
         var filterPlaces = (this.settings.places.length == 0) || (group.idPlace == '');
         for(var index = 0; index < this.formGroups.places.length; index++){
           filterPlaces = filterPlaces || group.idPlace == this.formGroups.places[index];
         }
         if(filterName && filterPlaces){
           groups.push(group);
         }
       }
       this.displayGroups = groups;
     }
     if(this.formGroups.order.length > 0){
       this.displayGroups.sort(function(group1, group2){
         var asc = 1;
         if(this.formGroups.order[0] == 'r') asc = -1;
         if(this.formGroups.order == 'activated' || this.formGroups.order == 'ractivated'){
           var value1 = group2.activated?1:0;
           var value2 = group2.activated?1:0;
           return (value1 - value2) * asc;
         }
         else if(this.formGroups.order == 'name' || this.formGroups.order == 'rname'){
           if(group1.name < group2.name) return -1 * asc;
           if(group1.name > group2.name) return 1 * asc;
         }
         else if(this.formGroups.order == 'service' || this.formGroups.order == 'rservice'){
           var service1 = this.getServiceName(group1.idService);
           var service2 = this.getServiceName(group2.idService);
           if(service1 < service2) return -1 * asc;
           if(service1 > service2) return 1 * asc;
         }
         else if(this.formGroups.order == 'place' || this.formGroups.order == 'rplace'){
           var place1 = this.getPlaceName(group1.idPlace);
           var place2 = this.getPlaceName(group2.idPlace);
           if(place1 < place2) return -1 * asc;
           if(place1 > place2) return 1 * asc;
         }
         return 0;
       }.bind(this));
     }
   }

  /*************************************************************************
   ***************************** MEMBER MODAL ******************************
   ****************************************************************/

   addGroup(content){
     var group = JSON.parse(JSON.stringify(this.skeletonStaticGroup));
     group.localId = 'group_' + this.groups.length;
     this.openGroup(content, group);
   }

   modifyGroup(content, group){
     this.openGroup(content, group);
   }

   duplicateGroup(content, group){
     var group = JSON.parse(JSON.stringify(group));
     group.id = -1;
     group.localId = 'group_' + this.groups.length;
     this.openGroup(content, group);
   }

   openGroup(content, group){
     this.formGroup = JSON.parse(JSON.stringify(group));
     this.formatGroup(this.formGroup);
     this.modal.open(content, { size: 'lg', backdrop:'static'}).result.then((result) => {
       if(result == 'save'){
         this.formGroup.options = [];
         for(var i = 0; i < this.formGroup.variableOptions.length; i++){
           var variableOption = this.formGroup.variableOptions[i];
           this.formGroup.options.push(variableOption.varname + '=' + variableOption.value);
         }
         var found = false;
         for(var i = 0; i < this.groups.length; i++){
           var group = this.groups[i];
           if(group.localId == this.formGroup.localId){
             this.groups[i] = JSON.parse(JSON.stringify(this.formGroup));
             this.updateGroup(this.groups[i]);
             found = true;
           }
         }
         if(!found){
           var group = JSON.parse(JSON.stringify(this.formGroup));
           this.groups.push(group);
           this.updateGroup(group);
         }
         this.save();
         this.calculateDisplayGroups();
       }
     }, (reason) => {
     });
   }

   deleteGroup(group) {
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer le groupe ' + group.name + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         var newGroups = [];
         for(var i = 0; i < this.groups.length; i++){
           var localGroup = this.groups[i];
           if(localGroup.localId != group.localId){
             newGroups.push(localGroup);
           }
         }
         this.groups = newGroups;
         this.generateLocalId();
         this.calculateDisplayGroups();
         this.setToSave(true);
       }
     });
   }

   isOkGroup(group){
     return this.formGroup.idService > -1 && this.formGroup.name != "";
   }

   closeGroupModal(callback){
     if(this.isOkGroup(this.formGroup)){
       callback('save');
     }
   }

   /*************************************************************************
    ***************************** SAVE ***********************************
    ****************************************************************/


    save(){
      if(!this.canDeactivate() && !this.launchActionSave){
        this.launchActionSave = true;
        this.userService.post('groups/:token', {groups:JSON.stringify(this.groups)}, function(data){
          this.launchActionSave = false;
          this.groups = data;
          this.generateLocalId();
          this.setToSave(false);
          this.calculateDisplayGroups();
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
