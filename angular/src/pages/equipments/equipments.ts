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
  selector: 'page-equipments',
  templateUrl: 'equipments.html',
  styleUrls:['./equipments.css']
})

export class EquipmentsComponent extends ComponentCanDeactivate implements OnInit {

  public noEquipmentsLoaded:boolean = true;
  public equipments:any[] = [];
  public services:any[] = [];
  public settings:any = null;

  private displayEquipments = [];
  public formEquipments = {order:'position', search:''};

  private skeletonEquipment = null;
  public formEquipment = null;
  public launchActionCreateRESAEquipment = false;
  public launchActionSave = false;

  private toSave = false;


  constructor(
    private userService:UserService,
    private navService:NavService,
    private global:GlobalService,
    private modal: NgbModal
  ) {
    super('Matériels');
  }


  ngOnInit(): void {
    this.userService.get('equipments/:token', function(data){
      if(data.equipments) this.equipments = data.equipments;
      this.noEquipmentsLoaded = (this.equipments.length == 0);
      if(data.services) this.services = data.services;
      if(data.settings) this.settings = data.settings;
      this.skeletonEquipment = data.skeletonEquipment;
      if(this.skeletonEquipment != null){
        this.formEquipment = JSON.parse(JSON.stringify(this.skeletonEquipment));
      }
      for(var i = 0; i < this.equipments.length; i++){
        var equipment = this.equipments[i];
        this.formatEquipment(equipment);
      }
      this.updatePositions();
      this.calculateDisplayEquipments();
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des équipements';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  formatEquipment(equipment){
    this.global.htmlSpecialDecodeArray(equipment.presentation);
  }


  canDeactivate():boolean{
    return !this.toSave;
  }

  isAssociatedWithActivityPrice(equipment, activityPrice){
    if(activityPrice.equipments == false || activityPrice.equipments == null) return false;
    return activityPrice.equipments.indexOf(equipment.id+'') > -1;
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


  updateEquipment(equipment){
    equipment.isUpdated = true;
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
   ***************************** Equipments ***********************************
   ****************************************************************/

   displayEquipmentsOrderBy(order){
     if(this.formEquipments.order == order){
       this.formEquipments.order = 'r' + order;
     }
     else {
       this.formEquipments.order = order;
     }
     this.calculateDisplayEquipments();
   }

   calculateDisplayEquipments(){
     if(this.formEquipments.search.length == 0){
       this.displayEquipments = this.equipments;
     }
     else {
       var equipments = [];
       for(var i = 0; i < this.equipments.length; i++){
         var equipment = this.equipments[i];
         if(equipment.name[this.global.currentLanguage].toLowerCase().indexOf(this.formEquipments.search.toLowerCase()) != -1){
           equipments.push(equipment);
         }
       }
       this.displayEquipments = equipments;
     }
     if(this.formEquipments.order.length > 0){
       this.displayEquipments.sort(function(equipment1, equipment2){
         var asc = 1;
         if(this.formEquipments.order[0] == 'r') asc = -1;
         if(this.formEquipments.order == 'name' || this.formEquipments.order == 'rname'){
           if(equipment1.name[this.global.currentLanguage] < equipment2.name[this.global.currentLanguage]) return -1 * asc;
           if(equipment1.name[this.global.currentLanguage] > equipment2.name[this.global.currentLanguage]) return 1 * asc;
         }
         else if(this.formEquipments.order == 'position' || this.formEquipments.order == 'rposition'){
           if(equipment1.position < equipment2.position) return -1 * asc;
           if(equipment1.position > equipment2.position) return 1 * asc;
         }
         else if(this.formEquipments.order == 'numbers' || this.formEquipments.order == 'rnumbers'){
           if(equipment1.numbers < equipment2.numbers) return -1 * asc;
           if(equipment1.numbers > equipment2.numbers) return 1 * asc;
         }
         return 0;
       }.bind(this));
     }
   }


  /*************************************************************************
   ***************************** EQUIPMENT MODAL ******************************
   ****************************************************************/

   addEquipment(content){
     var equipment = JSON.parse(JSON.stringify(this.skeletonEquipment));
     equipment.position = this.equipments.length + 1;
     this.openEquipment(content, equipment);
   }

   duplicateEquipment(content, equipment){
     var newEquipment = JSON.parse(JSON.stringify(equipment));
     newEquipment.id = -1;
     newEquipment.position = this.equipments.length + 1;
     newEquipment.isDeletable = true;
     this.openEquipment(content, newEquipment);
   }

   modifyEquipment(content, equipment){
     this.openEquipment(content, equipment);
   }

   openEquipment(content, equipment){
     this.formEquipment = JSON.parse(JSON.stringify(equipment));
     this.modal.open(content, { size: 'lg', backdrop:'static'}).result.then((result) => {
       if(result == 'save'){
         var found = false;
         for(var i = 0; i < this.equipments.length; i++){
           var equipment = this.equipments[i];
           if(equipment.position == this.formEquipment.position){
             this.equipments[i] = JSON.parse(JSON.stringify(this.formEquipment));
             this.updateEquipment(this.equipments[i]);
             found = true;
           }
         }
         if(!found){
           var equipment = JSON.parse(JSON.stringify(this.formEquipment));
           this.equipments.push(equipment);
           this.updatePositions();
           this.updateEquipment(equipment);
         }
         this.noEquipmentsLoaded = false;
         this.save();
       }
     }, (reason) => {
     });
   }

   deleteEquipment(equipment) {
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer ' + this.global.getTextByLocale(equipment.name, this.global.currentLanguage) + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         var newEquipments = [];
         for(var i = 0; i < this.equipments.length; i++){
           var localEquipment = this.equipments[i];
           if(localEquipment.position != equipment.position){
             newEquipments.push(localEquipment);
           }
         }
         this.equipments = newEquipments;
         this.updatePositions();
         this.calculateDisplayEquipments();
         this.setToSave(true);
       }
     });
   }

   updatePosition(equipment){
     if(equipment.editPosition != equipment.oldPosition){
       this.equipments.splice(equipment.editPosition - 1, 0, this.equipments.splice(equipment.oldPosition - 1, 1)[0]);
       this.updatePositions();
     }
   }

   updatePositions(){
     for(var i =0; i < this.equipments.length;i++){
       var equipment = this.equipments[i];
       if(equipment.position != i){
         equipment.position = i;
         this.updateEquipment(equipment);
       }
       equipment.editPosition = equipment.position * 1 + 1;
       equipment.oldPosition = equipment.position * 1 + 1;
     }
   }

   switchPlace(idPlace){
     var index = this.formEquipment.places.indexOf(idPlace);
     if(index != -1){
       this.formEquipment.places.splice(index, 1);
     }
     else {
       this.formEquipment.places.push(idPlace);
     }
   }

   getTabName(memberAvailability, index){
     var name = 'Règle n°' + (index + 1);
     if(memberAvailability.name!=null && memberAvailability.name.length > 0){
       name = memberAvailability.name;
     }
     return name;
   }


   isOkEquipment(equipment){
     return equipment.name[this.global.currentLanguage].length > 0 && equipment.numbers > 0;
   }

   closeEquipmentModal(callback){
     if(this.isOkEquipment(this.formEquipment)){
       callback('save');
     }
   }


   /*************************************************************************
    ***************************** SAVE ***********************************
    ****************************************************************/

    save(){
      if(!this.canDeactivate() && ! this.launchActionSave){
        this.launchActionSave = true;
        this.userService.post('equipments/:token', {equipments:JSON.stringify(this.equipments)}, function(data){
          this.launchActionSave = false;
          this.equipments = data;
          for(var i = 0; i < this.equipments.length; i++){
            var equipment = this.equipments[i];
            this.formatEquipment(equipment);
          }
          this.updatePositions();
          this.calculateDisplayEquipments();
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
