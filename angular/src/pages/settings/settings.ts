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
  selector: 'page-settings',
  templateUrl: 'settings.html',
  styleUrls:['./settings.css']
})

export class SettingsComponent extends ComponentCanDeactivate implements OnInit {

  public settings:any = null;
  public initTab:number = 1;

  public formAccountCustomer = null;
  public fieldStates = [{id:0, text:'Non Visible'}, {id:1, text:'Visible et obligatoire'}, {id:2, text:'Visible et facultatif'}];
  public fields = [
    {id:'lastName', text:'Nom'},
    {id:'firstName', text:'Prénom'},
    {id:'company', text:'Entreprise'},
    {id:'phone', text:'Téléphone'},
    {id:'phone2', text:'Téléphone 2'},
    {id:'address', text:'Adresse'},
    {id:'postalCode', text:'Code postal'},
    {id:'town', text:'Ville'},
    {id:'country', text:'Pays'},
    {id:'siret', text:'SIRET'},
    {id:'legalForm', text:'Forme juridique'},
    {id:'newsletters', text:'Inscription Newsletters', exceptId:[1]}
  ];
  public paymentsTypeList = [];

  public allPages = [];
  public allCSS = [];
  public services = [];
  public allLanguages = [];
  public formLanguage = null;
  public colors = [];
  public formParticipantParameters = null;
  public formField = null;

  public fileToUpload: File = null;

  public allConnected = [];
  public caisseStatusOk = false;
  public supportURL = '';
  public caisseOnlineInstalled = false;
  public swiklyInstalled = false;
  public resaOnlineFacebookInstalled = false;
  public idParametersUsed = [];
  public statesList = [];
  public formStateParameters = null;
  public configurationFileURL = null;
  public exportFileURL = null;
  public needUpdate = false;
  public acceptStripeConnect:boolean = false;
  public stripeConnectCGU:string = '';

  public queryParams:any = null;

  public checkLaunch = false;
  public launchActionSave = false;
  private toSave = false;
  public launchUpdate = false;

  constructor(private userService:UserService, private navService:NavService, private global:GlobalService,
    private route: ActivatedRoute, private modal: NgbModal) {
    super('Réglages avancés');

    this.route.queryParams.subscribe(params => {
      this.queryParams = params;
    });
  }

  ngOnInit(): void {
    this.setInitTab();
    this.userService.get('settings/:token', function(data){
      if(data.allPages){
        this.allPages = data.allPages;
      }
      if(data.allCSS){
        this.allCSS = data.allCSS;
      }
      if(data.services){
        this.services = data.services;
      }
      if(data.allLanguages){
        this.allLanguages = Object.keys(data.allLanguages).map(function(key) {
          return data.allLanguages[key];
        });
      }
      if(data.colors) this.colors = data.colors;
      if(data.idParametersUsed) this.idParametersUsed = data.idParametersUsed;
      if(data.statesList) {
        this.statesList = [];
        for(var i = 0; i < data.statesList.length; i++){
  				var state = data.statesList[i];
  				if(state.isFilter){
  					this.statesList.push(state)
  				}
  			}
      }
      if(data.allConnected) this.allConnected = data.allConnected;
      if(data.supportURL) this.supportURL = data.supportURL;
      if(data.caisseOnlineInstalled) this.caisseOnlineInstalled = data.caisseOnlineInstalled;
      if(data.swiklyInstalled) this.swiklyInstalled = data.swiklyInstalled;
      if(data.paymentsTypeList) this.paymentsTypeList = data.paymentsTypeList;
      if(data.resaOnlineFacebookInstalled) this.resaOnlineFacebookInstalled = data.resaOnlineFacebookInstalled;
      if(data.needUpdate) this.needUpdate = data.needUpdate;
      if(data.settings){
        this.settings = data.settings;
        for(var i = 0; i < this.settings.types_accounts.length; i++){
          var typeAccount = this.settings.types_accounts[i];
          this.global.htmlSpecialDecodeArray(typeAccount.name);
        }

        this.global.htmlSpecialDecodeArray(this.settings.checkbox_title_payment);
  			this.settings.company_name = this.global.htmlSpecialDecode(this.settings.company_name);
  			this.settings.company_address = this.global.htmlSpecialDecode(this.settings.company_address);

        //Correction undefined
        for(var i = 0; i < this.settings.states_parameters.length; i++){
          var stateParameters = this.settings.states_parameters[i];
          var undefinedForm = stateParameters.form.undefined;
          if(undefinedForm != null && undefinedForm.length > 0){
            this.settings.states_parameters[i].form[this.global.currentLanguage] = undefinedForm;
            delete this.settings.states_parameters[i].form.undefined;
          }
        }
        this.getStatusOfCaisseOnline();

        this.acceptStripeConnect = this.settings.stripe_connect_conditions_validated;
      }
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des réglages';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  setInitTab(){
    var tab = this.route.snapshot.paramMap.get('tab');
    switch(tab) {
      case 'general':
        this.initTab = 1;
        break;
      case 'activities':
        this.initTab = 2;
        break;
      case 'members':
        this.initTab = 3;
        break;
      case 'groups':
        this.initTab = 4;
        break;
      case 'participants':
        this.initTab = 5;
        break;
      case 'form':
        this.initTab = 6;
        break;
      case 'email':
        this.initTab = 8;
        break;
      case 'payments':
        this.initTab = 10;
        break;
      case 'daily':
        this.initTab = 9;
        break;
      case 'caisse':
        this.initTab = 11;
        break;
      case 'support':
        this.initTab = 12;
        break;
      case 'equipments':
        this.initTab = 13;
        break;
      case 'facebook':
        this.initTab = 14;
        break;
      case 'devis':
        this.initTab = 15;
        break;
      default:
        this.initTab = 1;
    }
  }

  changeLanguage(){
    if(this.settings.form_steps_title[this.global.currentLanguage] == null){
      this.settings.form_steps_title[this.global.currentLanguage] = ['', '', '', '', '', '', '', ''];
    }
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

  isTabPaymentGeneral(){ return this.queryParams != null && (this.queryParams.tab == null || this.queryParams.tab == 'general'); }
  isTabPaymentTVA(){ return this.queryParams != null && this.queryParams.tab == 'tva'; }
  isTabPaymentMeansOfPayment(){ return this.queryParams != null && this.queryParams.tab == 'mop'; }
  isTabPaymentStateParameters(){ return this.queryParams != null && this.queryParams.tab == 'sp'; }

  isTabPaymentClassic(){ return this.queryParams != null && (this.queryParams.tab2 == null || this.queryParams.tab2 == 'classics'); }
  isTabPaymentStripeConnect(){ return this.queryParams != null && this.queryParams.tab2 == 'stripeConnect'; }
  isTabPaymentCaisseOnline(){ return this.queryParams != null && this.queryParams.tab2 == 'caisseOnline'; }

  /**
   *
   */
  canDeactivate():boolean{
    return !this.toSave;
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

  getRepeat(number){ return new Array(number); }

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

  goActivity(id){
    this.navService.changeRoute('/activity/' + id + '/modify');
  }

  goNotifications(){
    this.navService.changeRoute('/notifications');
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

  /*************************************************************************
   ***************************** ACCOUNT Customer ***********************************
   ****************************************************************/

   isPaymentTypeActivated(payment){
     var result = false;
     switch(payment.id){
       case 'onTheSpot':
       result = this.settings.on_the_spot;
       break;
       case 'later':
       result = this.settings.later;
       break;
       case 'transfer':
       result = this.settings.transfer;
       break;
       case 'cheque':
       result = this.settings.cheque;
       break;
       case 'paypal':
       result = this.settings.paypal;
       break;
       case 'systempay':
       result = this.settings.systempay;
       break;
       case 'monetico':
       result = this.settings.monetico;
       break;
       case 'stripe':
       result = this.settings.stripe;
       break;
       case 'stripeConnect':
       result = this.settings.stripeConnect;
       break;
       case 'paybox':
       result = this.settings.paybox;
       break;
       case 'swikly':
       result = this.settings.swikly;
       break;
       default:
       result = false;
     }

     return result;
   }

   createPageAccount(){
     if(!this.checkLaunch){
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Voulez-vous créer la page "Mon compte" pour RESA Online ?',
         icon: 'warning',
         buttons: ['Annuler', 'Oui'],
         dangerMode: true,
       })
       .then((success) => {
         if (success) {
           this.checkLaunch = true;
           this.userService.put('page/:token', {type:'account'}, function(data){
             this.checkLaunch = false;
             this.settings.customer_account_url = data.settings.customer_account_url;
             if(data.settings.page){
               var page = data.settings.page;
               page.isAccount = true;
               this.allPages.push(page);
             }
             this.setToSave(true)
             swal({ title: '', text: 'Ok', icon: 'success'});
           }.bind(this), function(error){
             this.checkLaunch = false;
             var text = 'Impossible de créer la page de compte';
             if(error != null && error.message != null && error.message.length > 0){
               text += ' (' + error.message + ')';
             }
             swal({ title: 'Erreur', text: text, icon: 'error'});
           }.bind(this));
         }
       });
     }
   }


   addCustomTypeAccount(content){
     var actual = [];
     var array = this.settings.types_accounts;
     if(array=='' ||	array==false){
       array = [];
     }
     else {
       for(var i = 0; i < array.length; i++){
         actual.push(array[i].id);
       }
     }
     var index = 0;
     var idName = 'type_account_' + index;
     var found = true;
     while(found){
       index++;
       idName = 'type_account_' + index;
       found = actual.indexOf(idName) !=-1;
     }
     var paymentsTypeList = {};
     for(var i = 0; i < this.paymentsTypeList.length; i++){
       var payment = this.paymentsTypeList[i];
       paymentsTypeList[payment.id] = true;
     }
     this.modifyAccountCustomer(content, {id:idName, name:{}, fields:array[0].fields, paymentsTypeList:paymentsTypeList});
   }

   modifyAccountCustomer(content, accountCustomer){
     this.formAccountCustomer = JSON.parse(JSON.stringify(accountCustomer));
     this.modal.open(content, { size: 'lg', backdrop:'static'}).result.then((result) => {
       if(result == 'save'){
         var found = false;
         for(var i = 0; i < this.settings.types_accounts.length; i++){
           var typeAccount = this.settings.types_accounts[i];
           if(typeAccount.id == this.formAccountCustomer.id){
             this.settings.types_accounts[i] = JSON.parse(JSON.stringify(this.formAccountCustomer));
             found = true;
           }
         }
         if(!found){
           this.settings.types_accounts.push(JSON.parse(JSON.stringify(this.formAccountCustomer)));
         }
         this.setToSave(true);
       }
     }, (reason) => {
     });
   }

   deletCustomTypeAccount(index){
     var typeAccount = this.settings.types_accounts[index]
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer le type de compte ' + this.global.getTextByLocale(typeAccount.name) + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         if(index >= 2){
           this.settings.types_accounts.splice(index,1);
           this.setToSave(true);
         }
       }
     });
   }



   haveOnePaymentTypeAccount(accountCustomer){
     for(var i = 0; i < this.paymentsTypeList.length; i++){
       var payment = this.paymentsTypeList[i];
       if(this.isPaymentTypeActivated(payment) && accountCustomer.paymentsTypeList[payment.id]){
         return true;
       }
     }
     return false;
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

  /*************************************************************************
   ***************************** CSS ***************************
   ****************************************************************/

  handleFileInput(event) {
    this.fileToUpload = event.target.files.item(0);
    if(!this.checkLaunch && this.fileToUpload != null){
      this.checkLaunch = true;
      const formData: FormData = new FormData();
      formData.append('file', this.fileToUpload, this.fileToUpload.name);
      this.userService.postWithHeaders('uploadCSSFile/:token', formData, this.userService.generateHttpHeaders(null), function(data){
        this.checkLaunch = false;
        this.allCSS = data.allCSS;
        this.swapCSSLoaded(this.fileToUpload.name.substring(0, this.fileToUpload.name.length -4), true);
        event.target.value = '';
        swal({ title: '', text: 'Ok', icon: 'success'});
      }.bind(this), function(error){
        this.checkLaunch = false;
        event.target.value = '';
        var text = 'Impossible de générer le fichier de configuration';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }
  isCSSLoaded(css){
    var customCSS = [];
    if(this.settings.custom_css.length > 0){
      customCSS = this.settings.custom_css.split(',');
    }
    return customCSS.indexOf(css) > -1;
  }

  swapCSSLoaded(css, keep = false){
    var customCSS = [];
    if(this.settings.custom_css.length > 0){
      customCSS = this.settings.custom_css.split(',');
    }
    var index = customCSS.indexOf(css);
    if(index > -1) {
      if(!keep){
        customCSS.splice(index, 1);
      }
    }
    else customCSS.push(css);
    this.settings.custom_css = customCSS.join(',');
    this.setToSave(true);
  }

  /*************************************************************************
   ***************************** LIEU / CATEGORIE ***************************
   ****************************************************************/
   addPlace(){
     var idPlace = this.generateNewId(this.settings.places, 'id', 'place_');
     this.settings.places.push({
       id:idPlace,
       slug:'',
       name:{},
       presentation:{}
     });
     this.setToSave(true);
   }

   deletePlace(index){
     var place = this.settings.places[index];
     var name = place.name[this.global.currentLanguage]!=null?place.name[this.global.currentLanguage]:'';
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer le lieu ' + name + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         this.settings.places.splice(index, 1);
         this.setToSave(true);
       }
     });
   }

   updatePositionCategory(category){
     if(category.editPosition != category.oldPosition){
      this.settings.form_category_services.splice(category.editPosition - 1, 0, this.settings.form_category_services.splice(category.oldPosition - 1, 1)[0]);
      this.updatePositionsCategory();
     }
   }

   updatePositionsCategory(){
     for(var i = 0; i < this.settings.form_category_services.length;i++){
      var category = this.settings.form_category_services[i];
      if(category.position != i){
        category.position = i;
        this.setToSave(true);
      }
      category.editPosition = category.position * 1 + 1;
      category.oldPosition = category.position * 1 + 1;
     }
   }

   addCategory(){
     var id = this.generateNewId(this.settings.form_category_services, 'id', 'category_');
     var category = {id:id, label:{}};
     this.settings.form_category_services.push(category);
     this.updatePositionsCategory();
     this.setToSave(true);
   }

   deleteCategory(index){
     var category = this.settings.form_category_services[index];
     var name = category.label[this.global.currentLanguage]!=null?category.label[this.global.currentLanguage]:'';
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer la catégorie ' + name + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         this.settings.form_category_services.splice(index, 1);
         this.updatePositionsCategory();
         this.setToSave(true);
       }
     });
   }

   addCustomTag(){
     var id = this.generateNewId(this.settings.custom_tags, 'id', 'tag');
     this.settings.custom_tags.push({
       id:id,
       title:{},
       color:'tag_vert'
     });
     this.changeTitle();
   }

   deleteCustomTag(index){
     var tag = this.settings.custom_tags[index];
     var name = tag.title[this.global.currentLanguage]!=null?tag.title[this.global.currentLanguage]:'';
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer le tag ' + name + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         this.settings.custom_tags.splice(index, 1);
         this.setToSave(true);
       }
     });
   }


   /*************************************************************************
    ***************************** LANGUAGE ***********************************
    ****************************************************************/

    switchAskAdvancePayment(idTypeAccount){
      var index = this.settings.ask_advance_payment_type_accounts.indexOf(idTypeAccount);
      if(index != -1){
        this.settings.ask_advance_payment_type_accounts.splice(index, 1);
      }
      else {
        this.settings.ask_advance_payment_type_accounts.push(idTypeAccount);
      }
      this.setToSave(true);
    }

    formatParameterFields(participantParameters){
      for(var i = 0; i < participantParameters.fields.length; i++){
        var field = participantParameters.fields[i];
        if(field.presentation == null) field.presentation = {};
        if(field.prefix == null) field.prefix = {};
        if(field.suffix == null) field.suffix = {};
      }
    }

    addParticipantParameters(content){
      var idParameter = this.generateNewId(this.settings.form_participants_parameters, 'id', 'participants_parameters_');
			var lastname = {varname:'lastname', name:{}, mandatory: true, type:'text'};
			lastname.name['fr_FR'] = 'Nom de famille';
			var firstname = {varname:'firstname', name:{}, mandatory: true, type:'text'};
			firstname.name['fr_FR'] = 'Prénom';

			var participantParameters = {id:idParameter, label:{}, mandatory: true, fields:[]};
			participantParameters.label[this.global.currentLanguage] = 'Participants ' + (this.settings.form_participants_parameters.length + 1);
			participantParameters.fields.push(lastname);
			participantParameters.fields.push(firstname);
      this.modifyParticipantParameters(content, participantParameters);
    }

    duplicateParticipantParameters(content, participantParameters){
			participantParameters = JSON.parse(JSON.stringify(participantParameters));
      participantParameters.id = this.generateNewId(this.settings.form_participants_parameters, 'id', 'participants_parameters_');
      this.modifyParticipantParameters(content, participantParameters);
    }

    modifyParticipantParameters(content, participantParameters){
      this.formParticipantParameters = JSON.parse(JSON.stringify(participantParameters));
      this.formatParameterFields(this.formParticipantParameters);
      this.modal.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
        if(result == 'save'){
          var found = false;
          for(var i = 0; i < this.settings.form_participants_parameters.length; i++){
            var localParticipantsParameters = this.settings.form_participants_parameters[i];
            if(localParticipantsParameters.id == this.formParticipantParameters.id){
              this.settings.form_participants_parameters[i] = JSON.parse(JSON.stringify(this.formParticipantParameters));
              found = true;
            }
          }
          if(!found){
            this.settings.form_participants_parameters.push(JSON.parse(JSON.stringify(this.formParticipantParameters)));
          }
          this.setToSave(true);
        }
      }, (reason) => {
      });
    }

    isUnicityOfAttribute(attribute){
      var allAttributes = [];
      var unicity = true;
      for(var i = 0; i < this.formParticipantParameters.fields.length; i++){
        var field = this.formParticipantParameters.fields[i];
        if(field[attribute] != null && field[attribute].length >= 0 && allAttributes.indexOf(field[attribute]) == -1){
          allAttributes.push(field[attribute]);
        }
        else {
          unicity = false;
        }
      }
      return unicity;
    }


    saveParticipantParameters(c){
      var unicityOfVarname = this.isUnicityOfAttribute('varname');
      if(unicityOfVarname){
        c('save');
      }
      else if(!unicityOfVarname){
        swal({ title: 'Erreur', text: 'Le champs "varname" doit être unique et non null', icon: 'error'});
      }
    }

    deleteParticipantParameters(index){
      var participantParameters = this.settings.form_participants_parameters[index];
      var name = participantParameters.label[this.global.currentLanguage]!=null?participantParameters.label[this.global.currentLanguage]:'';
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Voulez-vous supprimer le groupe ' + name + ' ?',
        icon: 'warning',
        buttons: ['Annuler', 'Supprimer'],
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          this.settings.form_participants_parameters.splice(index, 1);
          this.setToSave(true);
        }
      });
    }

    addParticipantField(){
			this.formParticipantParameters.fields.push({
				varname:'varname',
				name:{},
				type:'text',
				options: [],
				prefix:{},
				suffix:{},
				presentation:{},
				min:0,
				max:99,
        displaySum:false
			});
		}


    deleteParameterField(participantParameters, index){
      var participantField = participantParameters.fields[index];
      var name = participantField.name[this.global.currentLanguage]!=null?participantField.name[this.global.currentLanguage]:'';
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Voulez-vous supprimer le champs ' + name + ' ?',
        icon: 'warning',
        buttons: ['Annuler', 'Supprimer'],
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          participantParameters.fields.splice(index, 1);
        }
      });
    }


    modifyOptions(content, field){
      this.formField = JSON.parse(JSON.stringify(field));
      if(this.formField.options.length == 0){
        this.addOption();
      }
      this.modal.open(content, { size: 'lg', backdrop:'static' }).result.then((result) => {
        if(result == 'save'){
          field.options = this.formField.options;
        }
      }, (reason) => {
      });
    }

    addOption(){
      var idOption = this.generateNewId(this.formField.options, 'id', 'option_');
      this.formField.options.push({id:idOption, name:{}});
    }


    /*************************************************************************
     ***************************** MAILBOX  ***********************************
     ****************************************************************/

     addMailbox(){
       this.settings.mailbox.push({
         activated:true,
         host:'',
         port:0,
         login:'',
         password:''
       });
     }

     deleteMailbox(index){
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Voulez-vous supprimer cette boîte mail ?',
         icon: 'warning',
         buttons: ['Annuler', 'Supprimer'],
         dangerMode: true,
       })
       .then((willDelete) => {
         if (willDelete) {
           this.settings.mailbox.splice(index, 1);
           this.setToSave(true);
         }
       });
     }

     isConnected(connectedValue){
       if(connectedValue != null && typeof connectedValue === "boolean"){
         return connectedValue;
       }
       return false;
     }

     /*************************************************************************
      ***************************** STRIPE CONNECT ***********************************
      ****************************************************************/
     openStripeConnectConditions(content){
       this.getStripeCGU();
       this.modal.open(content, { windowClass: 'mlg', backdrop:'static'}).result.then((result) => {
         if(result != null){
           window.open(result, '_blank');
         }
       }, (reason) => {
       });
     }

     getStripeCGU(){
       this.checkLaunch = true;
       this.userService.getGlobalWithHeaders(this.global.stripeConnect.apiStripeConnectUrl + 'cgu.php', {responseType: 'text'}, function(data){
         this.checkLaunch = false;
         this.stripeConnectCGU = data;
       }.bind(this), function(error){
         this.checkLaunch = false;
         var text = 'Impossible de recupérer les CGU';
         if(error != null && error.message != null && error.message.length > 0){
           text += ' (' + error.message + ')';
         }
         swal({ title: 'Erreur', text: text, icon: 'error'});
       }.bind(this));
     }

     acceptStripeConnectAction(c){
       if(this.acceptStripeConnect && !this.checkLaunch){
         this.checkLaunch = true;
         this.userService.post('settingsAcceptStripeConnect/:token', {}, function(data){
           this.checkLaunch = false;
           this.settings.stripe_connect_conditions_validated = true;
           c(data.OAuthLink);
         }.bind(this), function(error){
           this.checkLaunch = false;
           var text = 'Impossible de sauvegarder les données';
           if(error != null && error.message != null && error.message.length > 0){
             text += ' (' + error.message + ')';
           }
           swal({ title: 'Erreur', text: text, icon: 'error'});
         }.bind(this));
       }
     }


     /*************************************************************************
      ***************************** VAT ***********************************
      ****************************************************************/

     addVatLine(){
      var idName = this.generateNewId(this.settings.vat_list, 'id', 'vat_');
 			var vat = {id:idName,	label:{}, name:{}, reference:'', value: 0};
 			this.settings.vat_list.push(vat);
 			this.changeTitle();
 		}


    deleteVatLine(index){
      var vat = this.settings.vat_list[index];
      var name = vat.name[this.global.currentLanguage]!=null?vat.name[this.global.currentLanguage]:'';
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Voulez-vous supprimer cette TVA ' + name + ' ?',
        icon: 'warning',
        buttons: ['Annuler', 'Supprimer'],
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          this.settings.vat_list.splice(index, 1);
          this.setToSave(true);
        }
      });
    }

    /*************************************************************************
     ***************************** StateParameter ***********************************
     ****************************************************************/
    addStateParameters(content){
      var id = this.generateNewId(this.settings.states_parameters, 'id', '') * 1;
      this.modifyStateParameters(content, {
				id:id,
				name:'',
        description:'',
				state:'',
				paiement:false,
				paiementState:'',
				capacity:true,
        expired:false,
				form:{},
				stateBackend:'',
				paiementBackend:false,
				paiementStateBackend:'',
				capacityBackend:true,
        expiredBackend:false,
			});
    }

    modifyStateParameters(content, stateParameters){
      this.formStateParameters = JSON.parse(JSON.stringify(stateParameters));
      this.modal.open(content, { windowClass: 'mlg', backdrop:'static'}).result.then((result) => {
        if(result == 'save'){
          var found = false;
          for(var i = 0; i < this.settings.states_parameters.length; i++){
            var stateParameters = this.settings.states_parameters[i];
            if(stateParameters.id == this.formStateParameters.id){
              this.settings.states_parameters[i] = JSON.parse(JSON.stringify(this.formStateParameters));
              found = true;
            }
          }
          if(!found){
            this.settings.states_parameters.push(JSON.parse(JSON.stringify(this.formStateParameters)));
          }
          this.setToSave(true);
        }
      }, (reason) => {
      });
    }

    isOkStateParameters(){
      return this.formStateParameters.name.length > 0 &&
        this.formStateParameters.state.length > 0 &&
        this.formStateParameters.paiementState.length > 0 &&
        this.formStateParameters.stateBackend.length > 0 &&
        this.formStateParameters.paiementStateBackend.length > 0;
    }

    saveStateParameters(c){
      if(this.isOkStateParameters()){
        c('save');
      }
    }


    idParameterIsUsed(id){
			return this.idParametersUsed.indexOf(id+'') != -1;
		}

    deleteStateParameters(index){
      var stateParameters = this.settings.states_parameters[index];
      if(!this.idParameterIsUsed(stateParameters.id)){
        swal({
          title: 'Êtes-vous sûr ?',
          text: 'Voulez-vous supprimer le procésus ' + stateParameters.name + ' ?',
          icon: 'warning',
          buttons: ['Annuler', 'Supprimer'],
          dangerMode: true,
        })
        .then((willDelete) => {
          if (willDelete) {
            this.settings.states_parameters.splice(index,1);
            this.setToSave(true);
          }
        });
      }
    }


  /*************************************************************************
   ***************************** SETTINGS ***********************************
   ****************************************************************/

   clearSynchronizationCustomers(){
     if(!this.checkLaunch){
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Supprimer la synchronisation des clients avec la caisse ?',
         icon: 'warning',
         buttons: ['Annuler', 'Supprimer'],
         dangerMode: true,
       })
       .then((willDelete) => {
         if (willDelete) {
           this.checkLaunch = true;
           this.userService.post('clearSynchronizationCustomers/:token', {}, function(data){
             this.checkLaunch = false;
             swal({ title: '', text: 'Ok', icon: 'success'});
           }.bind(this), function(error){
             this.checkLaunch = false;
             var text = 'Impossible de supprimer la synchronisation des clients';
             if(error != null && error.message != null && error.message.length > 0){
               text += ' (' + error.message + ')';
             }
             swal({ title: 'Erreur', text: text, icon: 'error'});
           }.bind(this));
         }
       });
     }
   }

  forceCaisseOnline(){
    if(!this.checkLaunch){
      this.checkLaunch = true;
      this.userService.post('forceCaisseOnline/:token', {}, function(data){
        this.checkLaunch = false;
        swal({ title: '', text: 'Ok', icon: 'success'});
      }.bind(this), function(error){
        this.checkLaunch = false;
        var text = 'Impossible de forcer la synchronisation de la caisse';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }

 getStatusOfCaisseOnline(){
   if(this.caisseOnlineInstalled && this.settings.caisse_online_activated){
     this.userService.postGlobal(this.global.caisseOnline.api_caisse_url + 'license_status/', {
       id:this.global.caisseOnline.license_id,
       url:this.global.caisseOnline.site_url
     }, function(data){
        this.caisseStatusOk = data.license;
      }.bind(this), function(error){
        this.caisseStatusOk = false;
      }.bind(this));
    }
 }

 deauthorizeAction(force = false){
   if(!this.launchUpdate){
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous vraiment vous déconnecter de Stripe Connect ? Vous ne pourrez plus prendre de paiement en ligne',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         this.launchUpdate = true;
         this.userService.post('stripeConnect/deauthorize/:token', {}, function(data){
           this.global.stripeConnect = data.stripeConnect;
           swal({ title: 'Déconnecter !', text: 'Vous avez été déconnecté de notre stripe', icon: 'success'});
         }.bind(this), function(error){
           this.launchUpdate = false;
           var text = 'Impossible de se déconnecter de stripe';
           if(error != null && error.message != null && error.message.length > 0){
             text += ' (' + error.message + ')';
           }
           swal({ title: 'Erreur', text: text, icon: 'error'});
         }.bind(this));
        }
      });
   }
 }

 configurationFile(){
   if(!this.checkLaunch){
     this.checkLaunch = true;
     this.userService.post('configurationFile/:token', {}, function(data){
       this.checkLaunch = false;
       this.configurationFileURL = data.fileUrl;
       swal({ title: '', text: 'Ok', icon: 'success'});
     }.bind(this), function(error){
       this.checkLaunch = false;
       var text = 'Impossible de générer le fichier de configuration';
       if(error != null && error.message != null && error.message.length > 0){
         text += ' (' + error.message + ')';
       }
       swal({ title: 'Erreur', text: text, icon: 'error'});
     }.bind(this));
   }
 }

 exportFile(){
   if(!this.checkLaunch){
     this.checkLaunch = true;
     this.userService.post('exportFile/:token', {}, function(data){
       this.checkLaunch = false;
       this.exportFileURL = data.fileUrl;
       swal({ title: '', text: 'Ok', icon: 'success'});
     }.bind(this), function(error){
       this.checkLaunch = false;
       var text = 'Impossible de générer le fichier de configuration';
       if(error != null && error.message != null && error.message.length > 0){
         text += ' (' + error.message + ')';
       }
       swal({ title: 'Erreur', text: text, icon: 'error'});
     }.bind(this));
   }
 }

 importFileInput(event) {
   this.fileToUpload = event.target.files.item(0);
   if(!this.checkLaunch && this.fileToUpload != null){
     this.checkLaunch = true;
     const formData: FormData = new FormData();
     formData.append('file', this.fileToUpload, this.fileToUpload.name);
     this.userService.postWithHeaders('importFile/:token', formData, this.userService.generateHttpHeaders(null), function(data){
       this.checkLaunch = false;
       event.target.value = '';
       swal({ title: '', text: 'Ok', icon: 'success'});
     }.bind(this), function(error){
       this.checkLaunch = false;
       event.target.value = '';
       var text = 'Impossible de générer le fichier de configuration';
       if(error != null && error.message != null && error.message.length > 0){
         text += ' (' + error.message + ')';
       }
       swal({ title: 'Erreur', text: text, icon: 'error'});
     }.bind(this));
   }
 }

  save(){
    if(!this.canDeactivate() && !this.launchActionSave){
      this.launchActionSave = true;
      this.userService.post('settings/:token', {settings:JSON.stringify(this.settings)}, function(data){
        this.launchActionSave = false;
        this.allConnected = data.allConnected;
        this.global.supportURL = data.supportURL;
        this.global.caisseOnline = data.caisse_online;
        this.global.stripeConnect = data.stripeConnect;
        this.global.swiklyLink = data.swikly_link;
        this.global.staffManagement = data.staffManagement;
        this.global.equipmentsManagement = data.equipmentsManagement;
        this.global.groupsManagement = data.groupsManagement;
        this.global.backendV2Activated = data.backendV2Activated;
        this.getStatusOfCaisseOnline();
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
