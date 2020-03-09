import { Component, OnInit, ViewChild, ElementRef  } from '@angular/core';
import { NgModel } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';

declare var swal: any;

@Component({
  selector: 'page-activities',
  templateUrl: 'activities.html',
  styleUrls:['./activities.css']
})

export class ActivitiesComponent extends ComponentCanDeactivate implements OnInit {

  @ViewChild("categoryName", { static: false }) inputCategoryName: ElementRef;
  @ViewChild("placeName", { static: false }) inputPlaceName: ElementRef;

  public noActivitiesLoaded:boolean = true;
  public activities:any[] = [];
  public settings:any = null;
  public forms:any[] = [];

  public displayActivities = [];
  public formActivities = {order:'position', search:'', places:[]};

  public displayCategories = [];
  public formCategory = {order:'', search:'', display:false, image:'', name:'', slug:'', idCategory:null};

  public displayPlaces = [];
  public formPlace = {order:'', search:'', display:false, image:'', name:'', slug:'', presentation:'', idPlace:null};

  public launchActionSave = false;
  private toSave = {activities:false, categories:false, places: false};


  constructor(private userService:UserService, private navService:NavService, private global:GlobalService, private modalService: NgbModal) {
    super('Activités');
  }


  ngOnInit(): void {
    this.userService.get('activities/:token', function(data){
      if(data.settings) {
        this.settings = data.settings;
        for(var i = 0; i < this.settings.places.length; i++){
          if(this.global.filterSettings == null || this.global.filterSettings.places == null || this.global.filterSettings.places[this.settings.places[i].id]){
            this.switchFormActivitiesPlace(this.settings.places[i].id);
          }
        }
        this.updatePositionsCategory();
        this.calculateDisplayCategories();
        this.calculateDisplayPlaces();
      }
      if(data.activities){
        this.activities = data.activities;
        this.updatePositions();
        this.calculateDisplayActivities();
      }
      this.noActivitiesLoaded = this.activities.length == 0;
      if(data.forms){
        this.forms = data.forms;
      }
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  updatePosition(activity){
    if(activity.editPosition != activity.oldPosition){
      this.activities.splice(activity.editPosition - 1, 0, this.activities.splice(activity.oldPosition - 1, 1)[0]);
      this.updatePositions();
    }
  }

  updatePositions(){
    for(var i = 0; i < this.activities.length;i++){
      var activity = this.activities[i];
      if(activity.position != i){
        activity.position = i;
        this.setToSaveActivity(activity);
      }
      activity.editPosition = activity.position * 1 + 1;
      activity.oldPosition = activity.position * 1 + 1;
    }
  }

  canDeactivate():boolean{
    return !this.toSave.activities && !this.toSave.categories && !this.toSave.places;
  }

  getForms(activity){
    var forms = [];
    for(var i = 0; i < this.forms.length; i++){
      var form = this.forms[i];
      if(form.services.length == 0 || form.services.indexOf(activity.slug) != -1){
        forms.push(form);
      }
    }
    return forms;
  }


  getCategoryName(activity){
    if(this.settings != null && this.settings.categories_services != null){
      for(var i = 0; i < this.settings.categories_services.length; i++){
        var category = this.settings.categories_services[i];
        if(category.id == activity.category){
          return category.label[this.global.currentLanguage];
        }
      }
    }
    return '';
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

  switchFormActivitiesPlace(idPlace){
    var index = this.formActivities.places.indexOf(idPlace);
    if(index != -1){
      this.formActivities.places.splice(index, 1);
    }
    else {
      this.formActivities.places.push(idPlace);
    }
    this.calculateDisplayActivities();
  }


  getNumberActivitiesWithCategory(category){
    var number = 0;
    for(var i = 0; i < this.activities.length; i++){
      var activity = this.activities[i];
      if(activity.category == category.id){
        number++;
      }
    }
    return number;
  }

  /**
   * set the save boolean
   */
  setToSave(element, value){
    this.toSave[element] = value;
    if(value) this.changeTitle();
    else this.resetTitle();
  }

  /**
   *
   */
  getToSave(element){
    return this.toSave[element];
  }

  goFormPage(form){ this.navService.changeRoute('forms/form' + form.id); }
  goFormsPage(){ this.navService.changeRoute('forms'); }

  /*************************************************************************
   ***************************** ACTIVITIES ***********************************
   ****************************************************************/
   setToSaveActivity(activity){
     activity.isUpdated = true;
     this.setToSave('activities', true);
   }

   previewService(idService){
     var route = 'activity/' + idService + '/preview';
     this.navService.changeRoute(route);
   }

   modifyService(idService = null){
     var route = 'activity';
     if(idService){ route += '/' + idService + '/modify'; }
     this.navService.changeRoute(route);
   }

   duplicateService(idService){
     var route = 'activity/' + idService + '/duplicate';
     this.navService.changeRoute(route);
   }

   deleteService(service){
     if(this.settings != null){
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Voulez-vous supprimer le service : ' + service.name[this.global.currentLanguage] + ' ?',
         icon: 'warning',
         buttons: ['Annuler', 'Supprimer'],
         dangerMode: true,
       })
       .then((willDelete) => {
         if (willDelete) {
           var newServices = [];
           for(var i = 0; i < this.activities.length; i++){
             var localActivity = this.activities[i];
             if(service.id != localActivity.id) {
               newServices.push(localActivity);
             }
           }
           this.activities = newServices;
           this.setToSave('activities', true);
           this.updatePositions();
           this.calculateDisplayActivities();
         }
       });
     }
   }

   displayActivitiesOrderBy(order){
     if(this.formActivities.order == order){
       this.formActivities.order = 'r' + order;
     }
     else {
       this.formActivities.order = order;
     }
     this.calculateDisplayActivities();
   }

   calculateDisplayActivities(){
     if(this.formActivities.search.length == 0 && this.settings.places.length == 0){
       this.displayActivities = this.activities;
     }
     else {
       var activities = [];
       for(var i = 0; i < this.activities.length; i++){
         var activity = this.activities[i];
         var filterName = activity.name[this.global.currentLanguage].toLowerCase().indexOf(this.formActivities.search.toLowerCase()) != -1;
         var filterPlaces = (this.settings.places.length == 0) || (activity.places.length == 0);
         for(var index = 0; index < this.formActivities.places.length; index++){
           filterPlaces = filterPlaces || activity.places.indexOf(this.formActivities.places[index]) > -1;
         }
         if(filterName && filterPlaces){
           activities.push(activity);
         }
       }
       this.displayActivities = activities;
     }
     if(this.formActivities.order.length > 0){
       this.displayActivities.sort(function(activity1, activity2){
         var asc = 1;
         if(this.formActivities.order[0] == 'r') asc = -1;
         if(this.formActivities.order == 'name' || this.formActivities.order == 'rname'){
           if(activity1.name[this.global.currentLanguage] < activity2.name[this.global.currentLanguage]) return -1 * asc;
           if(activity1.name[this.global.currentLanguage] > activity2.name[this.global.currentLanguage]) return 1 * asc;
         }
         else if(this.formActivities.order == 'position' || this.formActivities.order == 'rposition'){
           return (activity1.position - activity2.position) * asc;
         }
         else if(this.formActivities.order == 'category' || this.formActivities.order == 'rcategory'){
           var category1 = this.getCategoryName(activity1);
           var category2 = this.getCategoryName(activity2);
           if(category1 < category2) return -1 * asc;
           if(category1 > category2) return 1 * asc;
         }
         else if(this.formActivities.order == 'activated' || this.formActivities.order == 'ractivated'){
           var value1 = activity1.activated?1:0;
           var value2 = activity2.activated?1:0;
           return (value1 - value2) * asc;
         }
         return 0;
       }.bind(this));
     }
   }


  /*************************************************************************
   ***************************** CATEGORY ***********************************
   ****************************************************************/

  updatePositionCategory(category){
    if(category.editPosition != category.oldPosition){
     this.settings.categories_services.splice(category.editPosition - 1, 0, this.settings.categories_services.splice(category.oldPosition - 1, 1)[0]);
     this.updatePositionsCategory();
    }
  }

  updatePositionsCategory(){
    for(var i = 0; i < this.settings.categories_services.length;i++){
     var category = this.settings.categories_services[i];
     if(category.position != i){
       category.position = i;
       this.setToSave('categories', true);
     }
     category.editPosition = category.position * 1 + 1;
     category.oldPosition = category.position * 1 + 1;
    }
  }

  displayCategoriesOrderBy(order){
    if(this.formCategory.order == order){
      this.formCategory.order = 'r' + order;
    }
    else {
      this.formCategory.order = order;
    }
    this.calculateDisplayCategories();
  }

  calculateDisplayCategories(){
    if(this.formCategory.search.length == 0){
      this.displayCategories = this.settings.categories_services;
    }
    else {
      var categories = [];
      for(var i = 0; i < this.settings.categories_services.length; i++){
        var category = this.settings.categories_services[i];
        if(category.label[this.global.currentLanguage].toLowerCase().indexOf(this.formCategory.search.toLowerCase()) != -1){
          categories.push(category);
        }
      }
      this.displayCategories = categories;
    }
    if(this.formCategory.order.length > 0){
      this.displayCategories.sort(function(category1, category2){
        var asc = 1;
        if(this.formCategory.order[0] == 'r') asc = -1;
        if(this.formCategory.order == 'name' || this.formCategory.order == 'rname'){
          if(category1.label[this.global.currentLanguage] < category2.label[this.global.currentLanguage]) return -1 * asc;
          if(category1.label[this.global.currentLanguage] > category2.label[this.global.currentLanguage]) return 1 * asc;
        }
        else if(this.formCategory.order == 'position' || this.formCategory.order == 'rposition'){
          return (category1.position - category2.position) * asc;
        }
        else if(this.formCategory.order == 'slug' || this.formCategory.order == 'rslug'){
          if(category1.slug < category2.slug) return -1 * asc;
          if(category1.slug > category2.slug) return 1 * asc;
        }
        return 0;
      }.bind(this));
    }
  }

  editCategory(category){
    if(this.settings != null){
      this.formCategory.idCategory = category.id;
      this.formCategory.name = category.label[this.global.currentLanguage];
      this.formCategory.image = category.image[this.global.currentLanguage]?category.image[this.global.currentLanguage]:'';
      this.formCategory.slug = category.slug==null?'':category.slug;
      this.focusInputCategoryName();
    }
  }

  deleteCategory(category){
    if(this.settings != null){
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Voulez-vous supprimer la catégorie : ' + category.label[this.global.currentLanguage] + ' ?',
        icon: 'warning',
        buttons: ['Annuler', 'Supprimer'],
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          var newCategory = [];
          for(var i = 0; i < this.settings.categories_services.length; i++){
            var localCategory = this.settings.categories_services[i];
            if(category.id != localCategory.id) {
              newCategory.push(localCategory);
            }
          }
          this.settings.categories_services = newCategory;
          this.updatePositionsCategory();
          this.calculateDisplayCategories();
          this.setToSave('categories', true);
        }
      });
    }
  }


  addCategory(){
    if(this.settings != null && this.formCategory.name.length > 0){
      var actualCategoryServices = [];
      var categories = this.settings.categories_services;
      if(categories =='' || categories == false){  this.settings.categories_services = []; }
      else {
        for(var i = 0; i < categories.length; i++){
          actualCategoryServices.push(categories[i].id);
        }
      }
      var index = 0;
      var idName = 'category_' + index;
      var found = true;
      while(found){
        index++;
        idName = 'category_' + index;
        found = actualCategoryServices.indexOf(idName) !=-1;
      }
      actualCategoryServices.push(idName);
      var category = {id:idName, label:{}, image:{}, slug:''};
      category.label[this.global.currentLanguage] = this.formCategory.name;
      category.image[this.global.currentLanguage] = this.formCategory.image;
      category.slug = this.formCategory.slug;
      this.settings.categories_services.push(category);
      this.formCategory.name = '';
      this.formCategory.image = '';
      this.formCategory.slug = '';
      this.formCategory.display = false;
      this.updatePositionsCategory();
      this.calculateDisplayCategories();
    }
  }

  validateCategory(){
    if(this.formCategory.idCategory == null){ this.addCategory(); }
    else {
      for(var i = 0; i < this.settings.categories_services.length; i++){
        var category = this.settings.categories_services[i];
        if(category.id == this.formCategory.idCategory) {
          this.settings.categories_services[i].label[this.global.currentLanguage] = this.formCategory.name;
          this.settings.categories_services[i].image[this.global.currentLanguage] = this.formCategory.image;
          this.settings.categories_services[i].slug = this.formCategory.slug;
        }
      }
      this.formCategory.display = false;
      this.formCategory.image = '';
      this.formCategory.name = '';
      this.formCategory.slug = '';
      this.formCategory.idCategory = null;
      this.calculateDisplayCategories();
    }
    this.setToSave('categories', true);
  }

  cancelCategory(){
    this.formCategory.display = false;
    this.formCategory.image = '';
    this.formCategory.name = '';
    this.formCategory.slug = '';
    this.formCategory.idCategory = null;
  }

  focusInputCategoryName(){
    this.formCategory.display = true;
    this.inputCategoryName.nativeElement.focus();
  }

  openImageSelectorCategoryDialog(content){
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result!=null && result.type == 'image'){
        this.formCategory.image = result.src;
      }
    }, (reason) => {
    });
  }

  /*************************************************************************
   ***************************** PLACES ***********************************
   ****************************************************************/

   displayPlacesOrderBy(order){
     if(this.formPlace.order == order){
       this.formPlace.order = 'r' + order;
     }
     else {
       this.formPlace.order = order;
     }
     this.calculateDisplayPlaces();
   }


   calculateDisplayPlaces(){
     if(this.formPlace.search.length == 0){
       this.displayPlaces = this.settings.places;
     }
     else {
       var places = [];
       for(var i = 0; i < this.settings.places.length; i++){
         var place = this.settings.places[i];
         if(place.name[this.global.currentLanguage].toLowerCase().indexOf(this.formPlace.search.toLowerCase()) != -1){
           places.push(place);
         }
       }
       this.displayPlaces = places;
     }
     if(this.formPlace.order.length > 0){
       this.displayPlaces.sort(function(place1, place2){
         var asc = 1;
         if(this.formPlace.order[0] == 'r') asc = -1;
         if(this.formPlace.order == 'name' || this.formPlace.order == 'rname'){
           if(place1.name[this.global.currentLanguage] < place2.name[this.global.currentLanguage]) return -1 * asc;
           if(place1.name[this.global.currentLanguage] > place2.name[this.global.currentLanguage]) return 1 * asc;
         }
         else if(this.formPlace.order == 'slug' || this.formPlace.order == 'rslug'){
           if(place1.slug < place2.slug) return -1 * asc;
           if(place1.slug > place2.slug) return 1 * asc;
         }
         return 0;
       }.bind(this));
     }
   }


   editPlace(place){
     if(this.settings != null){
       this.formPlace.idPlace = place.id;
       this.formPlace.image = place.image[this.global.currentLanguage]?place.image[this.global.currentLanguage]:'';
       this.formPlace.name = place.name[this.global.currentLanguage];
       this.formPlace.presentation = place.presentation[this.global.currentLanguage];
       this.formPlace.slug = place.slug;
       this.focusInputPlaceName();
     }
   }

   deletePlace(place){
     if(this.settings != null){
       swal({
         title: 'Êtes-vous sûr ?',
         text: 'Voulez-vous supprimer le lieu : ' + place.name[this.global.currentLanguage] + ' ?',
         icon: 'warning',
         buttons: ['Annuler', 'Supprimer'],
         dangerMode: true,
       })
       .then((willDelete) => {
         if (willDelete) {
           var newPlaces = [];
           for(var i = 0; i < this.settings.places.length; i++){
             var localPlace = this.settings.places[i];
             if(place.id != localPlace.id) {
               newPlaces.push(localPlace);
             }
           }
           this.settings.places = newPlaces;
           this.calculateDisplayPlaces();
           this.setToSave('places', true);
         }
       })
     }
   }


   addPlace(){
     if(this.settings != null && this.formPlace.name.length > 0 && this.formPlace.slug.length > 0){
       var actualIdsPlace = [];
       var places = this.settings.places;
       if(places =='' || places == false){  this.settings.places = []; }
       else {
         for(var i = 0; i < places.length; i++){
           actualIdsPlace.push(places[i].id);
         }
       }
       var index = 0;
			var idPlace = 'place_' + index;
			var found = true;
			while(found){
				index++;
				idPlace = 'place_' + index;
				found = (actualIdsPlace.indexOf(idPlace) != -1);
			}
       actualIdsPlace.push(idPlace);
       var place = {id:idPlace, slug:'', image:{}, name:{}, presentation:{}};
       place.image[this.global.currentLanguage] = this.formPlace.image;
       place.name[this.global.currentLanguage] = this.formPlace.name;
       place.presentation[this.global.currentLanguage] = this.formPlace.presentation;
       place.slug = this.formPlace.slug;
       this.settings.places.push(place);
       this.formPlace.display = false;
       this.formPlace.image = '';
       this.formPlace.name = '';
       this.formPlace.presentation = '';
       this.formPlace.slug = '';
       this.formPlace.idPlace = null;
       this.calculateDisplayPlaces();
     }
   }

   isValidPlace(place){
     return place.name.length > 0 && place.slug.length > 0;
   }

   validatePlace(){
     if(this.formPlace.idPlace == null){ this.addPlace(); }
     else {
       for(var i = 0; i < this.settings.places.length; i++){
         var place = this.settings.places[i];
         if(place.id == this.formPlace.idPlace) {
           this.settings.places[i].image[this.global.currentLanguage] = this.formPlace.image;
           this.settings.places[i].name[this.global.currentLanguage] = this.formPlace.name;
           this.settings.places[i].presentation[this.global.currentLanguage] = this.formPlace.presentation;
           this.settings.places[i].slug = this.formPlace.slug;
         }
       }
       this.formPlace.display = false;
       this.formPlace.image = '';
       this.formPlace.name = '';
       this.formPlace.presentation = '';
       this.formPlace.slug = '';
       this.formPlace.idPlace = null;
       this.calculateDisplayPlaces();
     }
     this.setToSave('places', true);
   }

   cancelPlace(){
     this.formPlace.display = false;
     this.formPlace.image = '';
     this.formPlace.name = '';
     this.formPlace.presentation = '';
     this.formPlace.slug = '';
     this.formPlace.idPlace = null;
   }

   focusInputPlaceName(){
     this.formPlace.display = true;
     this.inputPlaceName.nativeElement.focus();
   }

   openImageSelectorPlaceDialog(content){
     this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
       if(result!=null && result.type == 'image'){
         this.formPlace.image = result.src;
       }
     }, (reason) => {
     });
   }

   /*************************************************************************
    ***************************** SAVE ***********************************
    ****************************************************************/

    save(){
      if(!this.canDeactivate() && !this.launchActionSave){
        var data = {activities:null, categories:null, places:null};
        if(this.toSave.activities) data.activities = JSON.stringify(this.activities);
        else delete data.activities;
        if(this.toSave.categories) data.categories = this.settings.categories_services;
        else delete data.categories;
        if(this.toSave.places) data.places = this.settings.places;
        else delete data.places;
        this.launchActionSave = true;
        this.userService.post('activities/:token', data, function(data){
          this.launchActionSave = false;

          if(this.toSave.places){
            this.global.places = this.settings.places;
            this.global.initializeFilterSettings(this.global.filterSettings, this.settings.places);
          }

          this.setToSave('activities', false);
          this.setToSave('categories', false);
          this.setToSave('places', false);
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
