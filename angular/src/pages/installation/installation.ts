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
  selector: 'page-installation',
  templateUrl: 'installation.html',
  styleUrls:['./installation.css']
})

export class InstallationComponent extends ComponentCanDeactivate implements OnInit {

  public allPages:any[] = [];
  public allLanguages:any[] = [];
  public settings:any = null;
  public formLanguage = null;
  public tabNumber:number = 1;
  private toSave = false;
  private actionLaunch = false;

  constructor(private userService:UserService, private navService:NavService, private global:GlobalService, private modal: NgbModal) {
    super('Installation');
  }


  ngOnInit(): void {
    this.userService.get('installation/:token', function(data){
      this.allPages = data.allPages;
      this.allLanguages = Object.keys(data.allLanguages).map(function(key) {
        return data.allLanguages[key];
      });
      this.settings = data.settings;
      this.settings.company_name = this.global.htmlSpecialDecode(this.settings.company_name);
      this.settings.company_address = this.global.htmlSpecialDecode(this.settings.company_address);
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données de la première installation';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  canDeactivate():boolean{
    return !this.toSave;
  }

  havePreviousStep(){ return this.tabNumber > 1; }
  previousStep(){ if(this.havePreviousStep()) this.tabNumber--; }
  haveNextStep(){ return this.tabNumber < 6; }
  nextStep(){ if(this.haveNextStep()) this.tabNumber++; }
  setToSave(value){
    this.toSave = value;
    if(value) this.changeTitle();
    else this.resetTitle();
  }
  haveActionLaunch(){ return this.actionLaunch; }

  goActivities(){
    this.navService.changeRoute('activities');
  }

  goMembers(){
    this.navService.changeRoute('members');
  }

  goEquipments(){
    this.navService.changeRoute('equipments');
  }

  isBrowserCompatibility(){
    return this.global.isBrowserCompatibility();
	}

  /*************************************************************************
   ***************************** LOGO ***********************************
   ****************************************************************/

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
        this.settings.company_logo = '';
        this.setToSave(true);
      }
    });
  }


  openImageSelectorDialog(content){
    this.modal.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result!=null && result.type == 'image'){
        this.settings.company_logo = result.src;
        this.setToSave(true);
      }
    }, (reason) => {
    });
  }

  setImageLogo(image, c){
    c(image);
  }

  createPages(){
    if(!this.haveActionLaunch()){
      this.actionLaunch = true;
      this.userService.put('pages/:token', {}, function(data){
        this.actionLaunch = false;
        this.settings.customer_booking_url = data.settings.customer_booking_url;
        this.settings.customer_account_url = data.settings.customer_account_url;
        swal({ title: '', text: 'Ok', icon: 'success'});
      }.bind(this), function(error){
        this.actionLaunch = false;
        var text = 'Impossible de créer la page de formulaire';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }

  /*************************************************************************
   ***************************** LANGUAGE ***********************************
   ****************************************************************/

   addNewLanguage(content){
     this.formLanguage = {};
     this.modal.open(content, { size: 'lg', backdrop:'static'}).result.then((result) => {
       if(result == 'save'){
         this.settings.languages.push(this.formLanguage);
         this.setToSave(true);
       }
     }, (reason) => {
     });
   }

   deleteLanguage(index){
     var language = this.settings.languages[index];
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer la langue ' + this.global.languagesNames[language] + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         this.settings.languages.splice(index, 1);
         this.setToSave(true);
       }
     });
   }

  save(){
    if(!this.actionLaunch){
      this.actionLaunch = true;
      this.userService.post('installation/:token', {settings:JSON.stringify(this.settings)}, function(data){
        this.actionLaunch = false;
        this.settings.resa_first_parameter_done = data.settings.resa_first_parameter_done;
        this.global.staffManagement = data.staffManagement;
        this.global.equipmentsManagement = data.equipmentsManagement;
        this.setToSave(false);
        swal({ title: '', text: 'Sauvegarder des paramètres effectués', icon: 'success'}).then((willDelete) => {
          if(this.settings.equipments_management_actived) this.goEquipments();
          else if(this.settings.staff_management_actived) this.goMembers();
          else this.goActivities();
        });
      }.bind(this), function(error){
        var text = 'Impossible de sauvegarder les données';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));

    }
  }


}
