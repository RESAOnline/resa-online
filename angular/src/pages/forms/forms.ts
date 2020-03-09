import { Component, ViewChild, OnInit  } from '@angular/core';
import { NgModel } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';

declare var swal: any;

@Component({
  selector: 'page-forms',
  templateUrl: 'forms.html',
  styleUrls:['./forms.css']
})

export class FormsComponent extends ComponentCanDeactivate implements OnInit  {

  @ViewChild('content', { static: true }) private modalContent:any;

  public allFormPages:any[] = [];
  public allCSS:any[] = [];
  public forms:any[] = [];
  public services:any[] = [];
  public settings:any = null;
  public promoCodes:any[] = [];
  public allAskAccountsPage:any[] = [];
  public allAccountsPage:any[] = [];

  private displayForms = [];
  public formForms = {order:'name', search:''};

  private skeletonForm = null;
  public formForm = null;

  public formShortcodePrice = {service:null, servicePrice:null};
  public formAskAccount = {idTypeAccount:null};
  public formShortcode = {url:null, place:null, service:null};

  public launchActionSave = false;
  public launchAction = false;

  private toSave = false;


  constructor(private userService:UserService, private navService:NavService, private global:GlobalService,
    private route: ActivatedRoute, private modal: NgbModal) {
    super('Formulaires');
  }


  ngOnInit(): void {
    this.userService.get('forms/:token', function(data){
      if(data.allFormPages) this.allFormPages = data.allFormPages;
      if(data.allCSS) this.allCSS = data.allCSS;
      if(data.forms) this.forms = data.forms;
      if(data.services) this.services = data.services;
      if(data.settings) this.settings = data.settings;
      if(data.promoCodes) this.promoCodes = data.promoCodes;
      if(data.allAskAccountsPage) this.allAskAccountsPage = data.allAskAccountsPage;
      if(data.allAccountsPage) this.allAccountsPage = data.allAccountsPage;
      this.skeletonForm = data.skeletonForm;
      if(this.skeletonForm != null){
        this.formForm = JSON.parse(JSON.stringify(this.skeletonForm));
      }
      for(var i = 0; i < this.forms.length; i++){
        var form = this.forms[i];
        this.formatForm(form);
      }
      this.calculateDisplayForms();
      this.changeLanguage();
      var idForm = this.route.snapshot.paramMap.get('idForm');
      if(idForm != ''){
        for(var i = 0; i < this.forms.length; i++){
          var form = this.forms[i];
          if('form' + form.id == idForm){
            this.modifyForm(this.modalContent, form);
          }
        }
      }
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des formulaires';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  formatForm(form){

  }

  changeLanguage(){
    for(var i = 0; i < this.forms.length; i++){
      var form = this.forms[i];
      if(form.stepsTitle == false) form.stepsTitle = {};
      if(form.stepsTitle[this.global.currentLanguage] == null){
        form.stepsTitle[this.global.currentLanguage] = ['', '', '', '', '', '', '', ''];
      }
    }
  }

  getRepeat(number){ return new Array(number); }

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

  getPlaceSlug(idPlace){
    if(this.settings != null && this.settings.places != null){
      for(var i = 0; i < this.settings.places.length; i++){
        var place = this.settings.places[i];
        if(place.id == idPlace){
          return place.slug;
        }
      }
    }
    return '';
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

   goSettings(){
     this.navService.changeRoute('settings/form');
   }

  canDeactivate():boolean{
    return !this.toSave;
  }

  notHaveDeactivatedText(form):boolean{
    for(var i = 0; i < this.settings.languages.length; i++){
      var language = this.settings.languages[i];
      if(form.deactivatedText[language] == null || form.deactivatedText[language].length == 0){
        return true;
      }
    }
    return false;
  }

  isPageIncludeForm(page, form){
    return page.content.includes('"form' + form.id + '"');
  }

  getFormPage(form){
    for(var i = 0; i < this.allFormPages.length; i++){
      var content = this.allFormPages[i].content;
      if(content.includes('"form' + form.id + '"')){
        return this.allFormPages[i];
      }
    }
    return false;
  }

  updateForm(form){
    form.isUpdated = true;
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
   ***************************** FORMS ***********************************
   ****************************************************************/

   displayFormsOrderBy(order){
     if(this.formForms.order == order){
       this.formForms.order = 'r' + order;
     }
     else {
       this.formForms.order = order;
     }
     this.calculateDisplayForms();
   }

   calculateDisplayForms(){
     if(this.formForms.search.length == 0){
       this.displayForms = this.forms;
     }
     else {
       var forms = [];
       for(var i = 0; i < this.forms.length; i++){
         var form = this.forms[i];
         if(form.name.toLowerCase().indexOf(this.formForms.search.toLowerCase()) != -1){
           forms.push(form);
         }
       }
       this.displayForms = forms;
     }
     if(this.formForms.order.length > 0){
       this.displayForms.sort(function(form1, form2){
         var asc = 1;
         if(this.formForms.order[0] == 'r') asc = -1;
         if(this.formForms.order == 'name' || this.formForms.order == 'rname'){
           if(form1.name.toLowerCase() < form2.name.toLowerCase()) return -1 * asc;
           if(form1.name.toLowerCase() > form2.name.toLowerCase()) return 1 * asc;
         }
         return 0;
       }.bind(this));
     }
   }


   /**
    * return the resa form shortcode
    */
   getRESAFormShortcode(form){
     return '[RESA_form form="form' + form.id +'"]';
   }


  /*************************************************************************
   ***************************** FORM MODAL ******************************
   ****************************************************************/

   addForm(content){
     var form = JSON.parse(JSON.stringify(this.skeletonForm));
     form.id = this.generateNewId(this.forms, 'id', '');
     this.openForm(content, form);
   }

   duplicateForm(content, form){
     var newForm = JSON.parse(JSON.stringify(form));
     newForm.id = this.generateNewId(this.forms, 'id', '');
     this.openForm(content, newForm);
   }

   modifyForm(content, form){
     this.openForm(content, form);
   }

   openForm(content, form){
     this.formForm = JSON.parse(JSON.stringify(form));
     this.formForm.allServicesSelected = false;
     this.modal.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
       if(result == 'save'){
         var found = false;
         for(var i = 0; i < this.forms.length; i++){
           var form = this.forms[i];
           if(form.id == this.formForm.id){
             this.forms[i] = JSON.parse(JSON.stringify(this.formForm));
             this.updateForm(this.forms[i]);
             found = true;
           }
         }
         if(!found){
           var form = JSON.parse(JSON.stringify(this.formForm));
           this.forms.push(form);
           this.updateForm(form);
         }
         this.setToSave(true);
         this.calculateDisplayForms();
         this.save();
       }
     }, (reason) => {
     });
   }

   deleteForm(form) {
     swal({
       title: 'Êtes-vous sûr ?',
       text: 'Voulez-vous supprimer ' + form.name + ' ?',
       icon: 'warning',
       buttons: ['Annuler', 'Supprimer'],
       dangerMode: true,
     })
     .then((willDelete) => {
       if (willDelete) {
         var newForms = [];
         for(var i = 0; i < this.forms.length; i++){
           var localForm = this.forms[i];
           if(localForm.id != form.id){
             newForms.push(localForm);
           }
         }
         this.forms = newForms;
         this.setToSave(true);
         this.calculateDisplayForms();
       }
     });
   }

   createWordpressPage(form) {
     var page = this.getFormPage(form);
     if(page === false){
       var text = 'Voulez-vous vraiment créer une page wordpress pour le formulaire ' + form.name + ' ? Veuillez choisir le nom de la page : ';
       swal({
         title: 'Êtes-vous sûr ?',
         text:text,
         content: 'input',
         icon: 'warning',
         buttons: ['Annuler', 'Créer'],
         dangerMode: true
       })
       .then((name) => {
         if (name && name.length > 0) {
           this.launchAction = true;
           this.userService.put('pageForm/:token', {idForm:form.id, name:name}, function(data){
             this.launchAction = false;
             this.allFormPages = data.allFormPages;
             swal({ title: '', text: 'Ok', icon: 'success'});
           }.bind(this), function(error){
             this.actionLaunch = false;
             var text = 'Impossible de créer la page du formulaire';
             if(error != null && error.message != null && error.message.length > 0){
               text += ' (' + error.message + ')';
             }
             swal({ title: 'Erreur', text: text, icon: 'error'});
           }.bind(this));
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

   selectAllServices(){
     for(var i = 0; i < this.services.length; i++){
       var service = this.services[i];
       var index = this.formForm.services.indexOf(service.slug);
       if(index != -1 && !this.formForm.allServicesSelected){
         this.formForm.services.splice(index, 1);
       }
       if(index == -1 && this.formForm.allServicesSelected){
         this.formForm.services.push(service.slug);
       }
     }
   }

   switchService(slug){
     var index = this.formForm.services.indexOf(slug);
     if(index != -1){
       this.formForm.services.splice(index, 1);
     }
     else {
       this.formForm.services.push(slug);
     }
   }

   switchTypeAccount(idTypeAccount){
     var index = this.formForm.typesAccounts.indexOf(idTypeAccount);
     if(index != -1){
       this.formForm.typesAccounts.splice(index, 1);
     }
     else {
       this.formForm.typesAccounts.push(idTypeAccount);
     }
   }

   isOkForm(form){
     return true;
   }

   closeFormModal(callback){
     if(this.isOkForm(this.formForm)){
       callback('save');
     }
   }

      /*************************************************************************
       ***************************** CSS ***************************
       ****************************************************************/

   handleFileInput(event) {
     var fileToUpload = event.target.files.item(0);
     if(!this.launchAction && fileToUpload != null){
       this.launchAction = true;
       const formData: FormData = new FormData();
       formData.append('file', fileToUpload, fileToUpload.name);
       this.userService.postWithHeaders('uploadCSSFile/:token', formData, this.userService.generateHttpHeaders(null), function(data){
         this.launchAction = false;
         this.allCSS = data.allCSS;
         this.swapCSSLoaded(fileToUpload.name.substring(0, fileToUpload.name.length -4), true);
         event.target.value = '';
         swal({ title: '', text: 'Ok', icon: 'success'});
       }.bind(this), function(error){
         this.launchAction = false;
         event.target.value = '';
         var text = 'Impossible de générer le fichier de configuration';
         if(error != null && error.message != null && error.message.length > 0){
           text += ' (' + error.message + ')';
         }
         swal({ title: 'Erreur', text: text, icon: 'error'});
       }.bind(this));
     }
   }

   isCSSLoadedSettings(css){
     var customCSS = [];
     if(this.settings.custom_css.length > 0){
       customCSS = this.settings.custom_css.split(',');
     }
     return customCSS.indexOf(css) > -1;
   }


   isCSSLoaded(css){
     var customCSS = [];
     if(this.formForm.customCSS.length > 0){
       customCSS = this.formForm.customCSS.split(',');
     }
     return customCSS.indexOf(css) > -1;
   }

   swapCSSLoaded(css, keep = false){
     if(!this.isCSSLoadedSettings(css)){
       var customCSS = [];
       if(this.formForm.customCSS.length > 0){
         customCSS = this.formForm.customCSS.split(',');
       }
       var index = customCSS.indexOf(css);
       if(index > -1) {
         if(!keep){
           customCSS.splice(index, 1);
         }
       }
       else customCSS.push(css);
       this.formForm.customCSS = customCSS.join(',');
     }
   }


  /****************************************************************
   ***************************** FIN CSS ***************************
   ****************************************************************/

   getRESAPriceShortcode(){
     var result = '';
     if(this.formShortcodePrice.service != null && this.formShortcodePrice.servicePrice != null){
       result = '[RESA_price service="' + this.formShortcodePrice.service.slug + '" price="' + this.formShortcodePrice.servicePrice.slug + '"]'
     }
     return result;
   }

   getRESAFormShortcodePlace(){
     var result = '';
     if(this.formShortcode.url == null) return 'Pas de lien !';
     var formPage = this.formShortcode.url;
     if(formPage == null) return 'Pas de lien !';
     if(this.formShortcode.place == null) return formPage;
     return formPage + '?place=' + this.formShortcode.place.slug;
   }

   getRESAFormShortcodeService(){
     var result = '';
     if(this.formShortcode.url == null) return 'Pas de lien !';
     var formPage = this.formShortcode.url;
     if(formPage == null) return 'Pas de lien !';
     if(this.formShortcode.service == null) return formPage;
     var link = formPage + '?';
     if(this.formShortcode.place != null && this.formShortcode.service.places.indexOf(this.formShortcode.place.id) > -1) link += 'place=' + this.formShortcode.place.slug + '&';
     else if(this.formShortcode.service.places.length > 0) link += 'place=' + this.getPlaceSlug(this.formShortcode.service.places[0]) + '&';
     return link + 'service=' + this.formShortcode.service.slug;
   }

   copyInClipboard(text){
     this.global.copyInClipboard(text, function(){
       swal({ title: '', text: 'Copié !', icon: 'success'});
     });
   }

   getTypeAccountInPage(content){
     var typeAccount = this.settings.typesAccounts.find(function(element){
       if(content.indexOf('"' + element.id + '"') > -1){
         return element;
       }
     });
    var name = '';
    if(typeAccount){
     name = this.global.getTextByLocale(typeAccount.name);
    }
    return name;
   }


   createWordpressAskAccountPage(){
     if(this.formAskAccount.idTypeAccount != null){
       var text = 'Voulez-vous vraiment créer une page wordpress pour le demande de compte ? Veuillez choisir le nom de la page : ';
       swal({
         title: 'Êtes-vous sûr ?',
         text:text,
         content: 'input',
         icon: 'warning',
         buttons: ['Annuler', 'Créer'],
         dangerMode: true
       })
       .then((name) => {
         if (name && name.length > 0) {
           this.launchAction = true;
           this.userService.put('pageAskAccount/:token', {idTypeAccount:this.formAskAccount.idTypeAccount, name:name}, function(data){
             this.launchAction = false;
             this.allAskAccountsPage = data.allAskAccountsPage;
             swal({ title: '', text: 'Ok', icon: 'success'});
           }.bind(this), function(error){
             this.actionLaunch = false;
             var text = 'Impossible de créer la page de demande de compte';
             if(error != null && error.message != null && error.message.length > 0){
               text += ' (' + error.message + ')';
             }
             swal({ title: 'Erreur', text: text, icon: 'error'});
           }.bind(this));
         }
       });
     }
   }

   createPageAccount(){
     if(!this.launchAction){
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Voulez-vous créer la page "Mon compte" pour RESA Online ?',
         icon: 'warning',
         buttons: ['Annuler', 'Oui'],
         dangerMode: true,
       })
       .then((success) => {
         if (success) {
           this.launchAction = true;
           this.userService.put('page/:token', {type:'account'}, function(data){
             this.launchAction = false;
             this.settings.customer_account_url = data.settings.customer_account_url;
             if(data.settings.page){
               this.allAccountsPage.push(data.settings.page);
             }
             swal({ title: '', text: 'Ok', icon: 'success'});
           }.bind(this), function(error){
             this.launchAction = false;
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

   /*************************************************************************
    ***************************** SAVE ***********************************
    ****************************************************************/

    save(){
      if(!this.canDeactivate() && ! this.launchActionSave){
        this.launchActionSave = true;
        this.userService.post('forms/:token', {forms:JSON.stringify(this.forms), customer_account_url:this.settings.customer_account_url}, function(data){
          this.launchActionSave = false;
          this.forms = data;
          for(var i = 0; i < this.forms.length; i++){
            var form = this.forms[i];
            this.formatForm(form);
          }
          this.calculateDisplayForms();
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
