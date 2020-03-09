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
  selector: 'page-reductions',
  templateUrl: 'reductions.html',
  styleUrls:['./reductions.css']
})

export class ReductionsComponent extends ComponentCanDeactivate implements OnInit {

  public reductions:any[] = [];
  public services:any[] = [];
  public anotherReductions:boolean = false;
  public settings:any = null;

  private displayReductions = [];
  public formReductions = {order:'position', search:''};

  public formReduction = null;
  private skeletonReduction = null;
  private skeletonReductionConditions = null;
  private skeletonReductionCondition = null;
  private skeletonReductionConditionService = null;
  private skeletonReductionApplication = null;
  private skeletonReductionConditionsApplication = null;
  private skeletonReductionConditionApplication = null;

  private skeletonMemberAvailability = null;
  private skeletonMemberLink = null;
  private skeletonMemberLinkService = null;

  public launchActionSave = false;

  private toSave = false;


  constructor(private userService:UserService, private navService:NavService, private global:GlobalService, private modal: NgbModal) {
    super('Reductions');
  }


  ngOnInit(): void {
    this.userService.get('reductions/:token', function(data){
      if(data.reductions) this.reductions = data.reductions;
      if(data.anotherReductions) this.anotherReductions = data.anotherReductions;
      if(data.services) this.services = data.services;
      if(data.settings) {
        this.settings = data.settings;
      }
      this.skeletonReduction = data.skeletonReduction;
      if(this.skeletonReduction != null){
        this.formReduction = JSON.parse(JSON.stringify(this.skeletonReduction));
      }
      this.skeletonReductionConditions = data.skeletonReductionConditions;
      this.skeletonReductionCondition = data.skeletonReductionCondition;
      this.skeletonReductionConditionService = data.skeletonReductionConditionService;
      this.skeletonReductionApplication = data.skeletonReductionApplication;
      this.skeletonReductionConditionsApplication = data.skeletonReductionConditionsApplication;
      this.skeletonReductionConditionApplication = data.skeletonReductionConditionApplication;

      for(var i = 0; i < this.reductions.length; i++){
        var reduction = this.reductions[i];
        this.formatReduction(reduction);
      }
      this.updatePositions();
      this.calculateDisplayReductions();
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des réductions';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  formatReduction(reduction){
    for(var i = 0; i < reduction.reductionConditionsList.length; i++){
      var reductionConditions = reduction.reductionConditionsList[i];
      for(var j = 0; j < reductionConditions.reductionConditions.length; j++){
        var reductionCondition = reductionConditions.reductionConditions[j];
        if(reductionCondition.type == 'registerDate'){
          reductionCondition.param2 = new Date(reductionCondition.param2);
          reductionCondition.pickerDateValue = {
            year:reductionCondition.param2.getFullYear(),
            month:reductionCondition.param2.getMonth() + 1,
            day:reductionCondition.param2.getDate(),
          }
        }
      }
    }
  }

  goReductions(){
    return this.userService.getCurrentHost() + '/wp-admin/admin.php?page=resa_reductions';
  }

  goVoucher(voucher){
    this.navService.changeRoute('vouchers/' + voucher);
  }

  getVoucher(reduction){
    for(var i = 0; i < reduction.reductionConditionsList.length; i++){
      var reductionConditions = reduction.reductionConditionsList[i];
      for(var j = 0; j < reductionConditions.reductionConditions.length; j++){
        var reductionCondition = reductionConditions.reductionConditions[j];
        if(reductionCondition.type == 'code'){
          return reductionCondition.param2;
        }
      }
    }
    return null;
  }

  getVoucherLink(reduction){
    var voucher = this.getVoucher(reduction);
    if(voucher == null) return '';
    if(this.settings.customer_booking_url == null || this.settings.customer_booking_url.length == 0) return 'Veuillez définir le lien vers le formulaire client !';
    return this.settings.customer_booking_url + '?voucher=' + voucher;
  }

  copyInClipboard(text){
    this.global.copyInClipboard(text, function(){
      swal({ title: '', text: 'Copié !', icon: 'success'});
    });
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


  updateReduction(reduction){
    reduction.isUpdated = true;
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
   ***************************** Reductions ***********************************
   ****************************************************************/

   displayReductionsOrderBy(order){
     if(this.formReductions.order == order){
       this.formReductions.order = 'r' + order;
     }
     else {
       this.formReductions.order = order;
     }
     this.calculateDisplayReductions();
   }

   calculateDisplayReductions(){
     if(this.formReductions.search.length == 0){
       this.displayReductions = this.reductions;
     }
     else {
       var reductions = [];
       for(var i = 0; i < this.reductions.length; i++){
         var reduction = this.reductions[i];
         if(reduction.name[this.global.currentLanguage].toLowerCase().indexOf(this.formReductions.search.toLowerCase()) != -1){
           reductions.push(reduction);
         }
       }
       this.displayReductions = reductions;
     }
     if(this.formReductions.order.length > 0){
       this.displayReductions.sort(function(reduction1, reduction2){
         var asc = 1;
         if(this.formReductions.order[0] == 'r') asc = -1;
         if(this.formReductions.order == 'name' || this.formReductions.order == 'rname'){
           if(reduction1.name[this.global.currentLanguage] < reduction2.name[this.global.currentLanguage]) return -1 * asc;
           if(reduction1.name[this.global.currentLanguage] > reduction2.name[this.global.currentLanguage]) return 1 * asc;
         }
         else if(this.formReductions.order == 'position' || this.formReductions.order == 'rposition'){
           return (reduction1.position - reduction2.position) * asc;
         }
         else if(this.formReductions.order == 'visibility' || this.formReductions.order == 'rvisibility'){
           return (reduction1.visibility - reduction2.visibility) * asc;
         }
         else if(this.formReductions.order == 'activated' || this.formReductions.order == 'ractivated'){
           var value1 = reduction1.activated?1:0;
           var value2 = reduction2.activated?1:0;
           return (value1 - value2) * asc;
         }
         return 0;
       }.bind(this));
     }
   }

   updatePosition(reduction){
     if(reduction.editPosition != reduction.oldPosition){
       this.reductions.splice(reduction.editPosition - 1, 0, this.reductions.splice(reduction.oldPosition - 1, 1)[0]);
       this.updatePositions();
     }
   }

   updatePositions(){
     for(var i =0; i < this.reductions.length;i++){
       var reduction = this.reductions[i];
       if(reduction.position != i){
         reduction.position = i;
         this.updateReduction(reduction);
       }
       reduction.editPosition = reduction.position * 1 + 1;
       reduction.oldPosition = reduction.position * 1 + 1;
     }
   }


  /*************************************************************************
   ***************************** MEMBER MODAL ******************************
   ****************************************************************/
   initiateAVoucher(reduction){
     var reductionConditions = this.addReductionConditions(reduction);

     var reductionCondition = this.addReductionCondition(reductionConditions);
     reductionCondition.type = 'code';
     reductionCondition.param1 = 0;

     var reductionCondition = this.addReductionCondition(reductionConditions);
     reductionCondition.type = 'registerDate';
     reductionCondition.param1 = 2;

     var reductionCondition = this.addReductionCondition(reductionConditions);
     reductionCondition.type = 'registerDate';
     reductionCondition.param1 = 0;

     var reductionApplication = this.addReductionApplication(reduction);
     reductionApplication.onlyOne = true;
     reductionApplication.type = 1;
     reductionApplication.applicationType = 0;
     return reduction;
   }


   isValidVoucher(reduction){
     return reduction.reductionConditionsList[0] != null &&
     reduction.reductionConditionsList[0].reductionConditions[0] != null &&
     reduction.reductionConditionsList[0].reductionConditions[0].type == 'code' &&
      reduction.reductionConditionsList[0].reductionConditions[1] != null &&
      reduction.reductionConditionsList[0].reductionConditions[1].type == 'registerDate' &&
      reduction.reductionConditionsList[0].reductionConditions[2]  != null &&
      reduction.reductionConditionsList[0].reductionConditions[2].type == 'registerDate';
   }



   fixUsage(reductionCondition){
     if(reductionCondition.param4 == null) reductionCondition.param3 = false;
     else reductionCondition.param3 = true;
   }


   addReduction(content){
     var reduction = JSON.parse(JSON.stringify(this.skeletonReduction));
     reduction.position = this.reductions.length + 1;
     reduction = this.initiateAVoucher(reduction);
     this.openReduction(content, reduction);
   }

   duplicateReduction(content, reduction){
     var newReduction = JSON.parse(JSON.stringify(reduction));
     newReduction.id = -1;
     newReduction.position = this.reductions.length + 1;
     newReduction.activated = false;
     newReduction.isDeletable = true;
     //TODO
     this.openReduction(content, newReduction);
   }

   modifyReduction(content, reduction){
     this.openReduction(content, reduction);
   }

   openReduction(content, reduction){
     this.formReduction = JSON.parse(JSON.stringify(reduction));
     this.modal.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
       if(result == 'save'){
         var found = false;
         for(var i = 0; i < this.reductions.length; i++){
           var reduction = this.reductions[i];
           if(reduction.position == this.formReduction.position){
             this.reductions[i] = JSON.parse(JSON.stringify(this.formReduction));
             this.updateReduction(this.reductions[i]);
             found = true;
           }
         }
         if(!found){
           var reduction = JSON.parse(JSON.stringify(this.formReduction));
           this.reductions.push(reduction);
           this.updatePositions();
           this.updateReduction(reduction);
         }
         this.save();
       }
     }, (reason) => {
     });
   }

   deleteReduction(reduction) {
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer ' + reduction.name[this.global.currentLanguage] + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         var newReductions = [];
         for(var i = 0; i < this.reductions.length; i++){
           var local = this.reductions[i];
           if(local.position != reduction.position){
             newReductions.push(local);
           }
         }
         this.reductions = newReductions;
         this.updatePositions();
         this.calculateDisplayReductions();
         this.setToSave(true);
       }
     });
   }


   isOkReduction(reduction){
     return this.isOkNameReduction(reduction) &&
      this.getVoucher(reduction).length > 0 &&
      reduction.reductionApplications[0].value > 0
   }

   isOkNameReduction(reduction){
     return reduction.name != null && reduction.name[this.global.currentLanguage] != null && reduction.name[this.global.currentLanguage].length > 0;
   }

   closeReductionModal(callback){
     if(this.isOkReduction(this.formReduction)){
       callback('save');
     }
   }

   closeAndGoBVoucher(callback){
     this.goVoucher(this.getVoucher(this.formReduction));
     callback();
   }

   addReductionConditions(reduction){
     var reductionConditions = JSON.parse(JSON.stringify(this.skeletonReductionConditions));
     reductionConditions.idReduction = reduction.id;
     reduction.reductionConditionsList.push(reductionConditions);
     return reductionConditions;
   }

   addReductionCondition(reductionConditions){
     var reductionCondition = JSON.parse(JSON.stringify(this.skeletonReductionCondition));
     reductionCondition.idReductionConditions = reductionConditions.id;
     reductionConditions.reductionConditions.push(reductionCondition);
     return reductionCondition;
   }


   addReductionApplication(reduction){
     var reductionApplication = JSON.parse(JSON.stringify(this.skeletonReductionApplication));
     reductionApplication.idReduction = reduction.id;
     reduction.reductionApplications.push(reductionApplication);
     return reductionApplication;
   }


   onConditionDateSelected(reduction, dateModel, index){
     var date = new Date(dateModel.year, dateModel.month - 1, dateModel.day);
     date = this.global.clearTime(date);
     if(index == 2){
       date.setHours(23);
       date.setMinutes(59);
       date.setSeconds(59);
     }
     reduction.reductionConditionsList[0].reductionConditions[index].param2 = date;
   }


   /*************************************************************************
    ***************************** SAVE ***********************************
    ****************************************************************/




    save(){
      if(!this.canDeactivate() && !this.launchActionSave){
        this.launchActionSave = true;
        this.userService.post('reductions/:token', {reductions:JSON.stringify(this.reductions)}, function(data){
          this.launchActionSave = false;
          this.reductions = data;
          for(var i = 0; i < this.reductions.length; i++){
            var reduction = this.reductions[i];
            this.formatReduction(reduction);
          }
          this.updatePositions();
          this.calculateDisplayReductions();
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
