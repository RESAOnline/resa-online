/**
 * ReductionsManager
 */
"use strict";

(function(){

	function ReductionsManagerFactory($log, $http, $filter, FunctionsManager, ChangeDetectionManager){

		var _skeletonReduction = null;
		var _skeletonReductionConditions = null;
		var _skeletonReductionCondition = null;
		var _skeletonReductionConditionService = null;
		var _skeletonReductionApplication = null;
		var _skeletonReductionConditionsApplication = null;
		var _skeletonReductionConditionApplication = null;
		var _priceToIdService = null;
		var _self = null;
		var _scope = null;
		var _ajaxUrl = null;

		var ReductionsManager = function(scope){
			FunctionsManager.call(this);
			angular.extend(ReductionsManager.prototype, FunctionsManager.prototype);
			ChangeDetectionManager.call(this);
			angular.extend(ReductionsManager.prototype, ChangeDetectionManager.prototype);
			_self = this;
			_scope = scope;
			this.alert = null;
			this.reductions = [];
			this.services = [];
			this.settings = [];
			this.typeCheckReductionConditions = [];
			this.typeReductionConditions = [];
			this.typeReductionApplications = [];
			this.typeApplicationsTypeReduction = [];
			this.typeApplicationConditionsOn = [];
			this.typeReductionApplicationConditions = [];
			this.days = [];
			this.languages = [];
			this.usedLanguages = [];
			this.currentLanguage = '';
			this.locale = '';
			this.loadingData = true;
		}

		ReductionsManager.prototype.reductionUpdated = function(reduction){
			reduction.isUpdated = true;
			this.changeTitle();
		}

		ReductionsManager.prototype.initialize = function(days, languages, locale, ajaxUrl){
			this.days = days;
			this.languages = languages;
			this.locale = locale;
			this.currentLanguage = locale;
			_ajaxUrl = ajaxUrl;
			this.initializationData();
		}

		ReductionsManager.prototype.initializationWithData = function(reductions, services, settings, skeletonReduction, skeletonReductionConditions, skeletonReductionCondition, skeletonReductionConditionService, skeletonReductionApplication, skeletonReductionConditionsApplication, skeletonReductionConditionApplication, typeCheckReductionConditions, typeReductionConditions, typeReductionApplications, typeApplicationsTypeReduction, typeApplicationConditionsOn, typeReductionApplicationConditions)
		{
			this.reductions = reductions;
			this.services = services;
			this.settings = settings;
			_skeletonReduction = skeletonReduction;
			_skeletonReductionConditions = skeletonReductionConditions;
			_skeletonReductionCondition = skeletonReductionCondition;
			_skeletonReductionConditionService = skeletonReductionConditionService;
			_skeletonReductionApplication = skeletonReductionApplication;
			_skeletonReductionConditionsApplication = skeletonReductionConditionsApplication;
			_skeletonReductionConditionApplication = skeletonReductionConditionApplication;
			this.typeCheckReductionConditions = typeCheckReductionConditions;
			for(var i = 0; i < this.typeCheckReductionConditions.length; i++){
				var typeCheckReductionCondition = this.typeCheckReductionConditions[i];
				this.typeReductionConditions[typeCheckReductionCondition.id] = [];
				for(var j = 0; j < typeReductionConditions.length; j++){
					if(typeCheckReductionCondition.idsType.indexOf(typeReductionConditions[j].id) != -1){
						this.typeReductionConditions[typeCheckReductionCondition.id].push(typeReductionConditions[j]);
					}
				}
			}

			this.typeReductionApplications = typeReductionApplications;
			for(var i = 0; i < this.typeReductionApplications.length; i++){
				var typeReductionApplication =  this.typeReductionApplications[i];
				this.typeApplicationsTypeReduction[typeReductionApplication.id] = [];
				for(var j = 0; j < typeApplicationsTypeReduction.length; j++){
					if(typeReductionApplication.applyOn.indexOf(typeApplicationsTypeReduction[j].id) != -1){
						this.typeApplicationsTypeReduction[typeReductionApplication.id].push(typeApplicationsTypeReduction[j]);
					}
				}
			}
			this.typeApplicationConditionsOn = typeApplicationConditionsOn;
			this.typeReductionApplicationConditions = typeReductionApplicationConditions;


			_priceToIdService = [];
			_priceToIdService[-1] = [];
			for(var i = 0; i < this.services.length; i++){
				var service = this.services[i];
				_priceToIdService[service.id] = [];
				for(var j = 0 ; j < service.servicePrices.length; j++){
					_priceToIdService[service.id].push(service.servicePrices[j]);
				}
			}

			for(var i = 0; i < this.reductions.length; i++){
				var reduction = this.reductions[i];
				this.htmlSpecialDecodeArray(reduction.name);
				this.htmlSpecialDecodeArray(reduction.presentation);
				this.reformatDateField(reduction);
			}
			this.loadingData = false;
		}

		ReductionsManager.prototype.initializationData = function(){
			var data = {
				action:'initializationDataReductions'
			}
			jQuery.post(_ajaxUrl, data, function(data) {
				_scope.$apply(function(){
					data = JSON.parse(data);
					_self.initializationWithData(data.reductions, data.services, data.settings, data.skeletonReduction, data.skeletonReductionConditions, data.skeletonReductionCondition, data.skeletonReductionConditionService, data.skeletonReductionApplication, data.skeletonReductionConditionsApplication, data.skeletonReductionConditionApplication, data.typeCheckReductionConditions, data.typeReductionConditions, data.typeReductionApplications, data.typeApplicationsTypeReduction, data.typeApplicationConditionsOn, data.typeReductionApplicationConditions);
				});
			}).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		ReductionsManager.prototype.setCurrentLanguage = function(language){
			this.currentLanguage = language;
		}

		ReductionsManager.prototype.getVoucher = function(reduction){
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

		ReductionsManager.prototype.getVoucherLink = function(reduction){
			var voucher = this.getVoucher(reduction);
			if(voucher == null) return '';
			if(this.settings.customer_booking_url == null || this.settings.customer_booking_url.length == 0) return 'Veuillez définir le lien vers le formulaire client !';
			return this.settings.customer_booking_url + '?voucher=' + voucher;
		}

		ReductionsManager.prototype.reformatDateField = function(reduction){
			for(var j= 0; j < reduction.reductionConditionsList.length; j++){
				var reductionConditions = reduction.reductionConditionsList[j];
				for(var k = 0; k < reductionConditions.reductionConditions.length; k++){
					var reductionCondition = reductionConditions.reductionConditions[k];
					if(reductionCondition.type == 'registerDate'){
						reductionCondition.param2 = new Date(reductionCondition.param2);
					}
					for(var l = 0; l < reductionCondition.reductionConditionServices.length; l++){
						var reductionConditionService = reductionCondition.reductionConditionServices[l];
						for(var m = 0; m < reductionConditionService.dates.length; m++){
							reductionConditionService.dates[m].date = new Date(reductionConditionService.dates[m].date);
						}
					}
				}
			}
			for(var j= 0; j < reduction.reductionApplications.length; j++){
				var reductionApplications = reduction.reductionApplications[j];
				for(var k = 0; k < reductionApplications.reductionConditionsApplicationList.length; k++){
					var reductionConditionsApplicationList = reductionApplications.reductionConditionsApplicationList[k];
					for(var l = 0; l < reductionConditionsApplicationList.reductionConditionsApplications.length; l++){
						var reductionConditionsApplication = reductionConditionsApplicationList.reductionConditionsApplications[l];
						if(reductionConditionsApplication.type == 'date'){
							reductionConditionsApplication.param2 = new Date(reductionConditionsApplication.param2);
						}
					}
				}
			}
		}

		ReductionsManager.prototype.addReduction = function(){
			var reduction = JSON.parse(JSON.stringify(_skeletonReduction));
			reduction.name[this.currentLanguage] = "Réduction " + (this.reductions.length  + 1);
			this.reductions.push(reduction);
			this.reductionUpdated(reduction);
		}

		ReductionsManager.prototype.duplicateReduction = function(reduction){
			var reduction = JSON.parse(JSON.stringify(reduction));
			reduction.id = -1;
			reduction.name[this.currentLanguage] = "Réduction " + (this.reductions.length  + 1);
			for(var i = 0; i < reduction.reductionConditionsList.length; i++){
				var reductionConditions = reduction.reductionConditionsList[i];
				reductionConditions.id = -1;
				for(var j = 0; j < reductionConditions.reductionConditions.length; j++){
					var reductionCondition = reductionConditions.reductionConditions[j];
					reductionCondition.id = -1;
					reductionCondition.idReductionConditions = reductionConditions.id;
					for(var k = 0; k < reductionCondition.reductionConditionServices.length; k++){
						var reductionConditionService = reductionCondition.reductionConditionServices[k];
						reductionConditionService.id = -1;
						reductionCondition.idReductionCondition = reductionCondition.id;
					}
				}
			}
			for(var i = 0; i < reduction.reductionApplications.length; i++){
				var reductionApplication = reduction.reductionApplications[i];
				reductionApplication.id = -1;
				for(var j = 0; j < reductionApplication.reductionConditionsApplicationList.length; j++){
					var reductionConditionsApplication = reductionApplication.reductionConditionsApplicationList[j];
					reductionConditionsApplication.id = -1;
					reductionConditionsApplication.idReductionApplication = reductionApplication.id;
					for(var k = 0; k < reductionConditionsApplication.reductionConditionsApplications.length; k++){
						var reductionConditionApplication = reductionConditionsApplication.reductionConditionsApplications[k];
						reductionConditionApplication.id = -1;
						reductionConditionApplication.idReductionConditionsApplication = reductionConditionsApplication.id;
					}
				}
			}
			reduction.linkOldReductions = '';
			this.reductions.push(JSON.parse(JSON.stringify(reduction)));
			this.reductionUpdated(reduction);
		}

		ReductionsManager.prototype.deleteReduction = function(index){
			this.reductions.splice(index, 1);
			this.changeTitle();
		}

		ReductionsManager.prototype.addReductionConditions = function(reduction){
			var reductionConditions = JSON.parse(JSON.stringify(_skeletonReductionConditions));
			reductionConditions.idReduction = reduction.id;
			reduction.reductionConditionsList.push(reductionConditions);
		}

		ReductionsManager.prototype.deleteReductionConditions = function(reduction, index){
			reduction.reductionConditionsList.splice(index, 1);
		}

		ReductionsManager.prototype.duplicateReductionConditions = function(reduction, reductionConditions){
			var reductionConditions = JSON.parse(JSON.stringify(reductionConditions));
			reductionConditions.id = -1;
			reduction.reductionConditionsList.push(reductionConditions);
			for(var i = 0; i < reductionConditions.reductionConditions.length; i++){
				var reductionCondition = reductionConditions.reductionConditions[i];
				reductionCondition.id = -1;
				reductionCondition.idReductionConditions = reductionConditions.id;
				for(var k = 0; k < reductionCondition.reductionConditionServices.length; k++){
					var reductionConditionService = reductionCondition.reductionConditionServices[k];
					reductionConditionService.id = -1;
					reductionCondition.idReductionCondition = reductionCondition.id;
				}
			}
		}

		ReductionsManager.prototype.reinitReduction = function(reductionCondition){
			reductionCondition.param1 = '';
			reductionCondition.param2 = '';
		}

		ReductionsManager.prototype.addReductionCondition = function(reductionConditions){
			var reductionCondition = JSON.parse(JSON.stringify(_skeletonReductionCondition));
			reductionCondition.idReductionConditions = reductionConditions.id;
			reductionConditions.reductionConditions.push(reductionCondition);
		}

		ReductionsManager.prototype.duplicateReductionCondition = function(reductionConditions, reductionCondition){
			var reductionCondition = JSON.parse(JSON.stringify(reductionCondition));
			reductionCondition.id = -1;
			reductionCondition.idReductionConditions = reductionConditions.id;
			reductionConditions.reductionConditions.push(reductionCondition);
			for(var k = 0; k < reductionCondition.reductionConditionServices.length; k++){
				var reductionConditionService = reductionCondition.reductionConditionServices[k];
				reductionConditionService.id = -1;
				reductionCondition.idReductionCondition = reductionCondition.id;
			}
		}

		ReductionsManager.prototype.deleteReductionCondition = function(reductionConditions, index){
			reductionConditions.reductionConditions.splice(index, 1);
		}

		ReductionsManager.prototype.addReductionConditionService = function(reductionCondition){
			var reductionConditionService = JSON.parse(JSON.stringify(_skeletonReductionConditionService));
			reductionConditionService.idReductionCondition = reductionCondition.id;
			reductionCondition.reductionConditionServices.push(reductionConditionService);
		}

		ReductionsManager.prototype.deleteReductionConditionService = function(reductionCondition, index){
			reductionCondition.reductionConditionServices.splice(index, 1);
		}

		ReductionsManager.prototype.addReductionConditionServiceDate = function(reductionConditionService){
			reductionConditionService.dates.push({method:0, date: new Date()});
		}

		ReductionsManager.prototype.deleteReductionConditionServiceDate = function(reductionConditionService, index){
				reductionConditionService.dates.splice(index, 1);
		}

		ReductionsManager.prototype.addReductionConditionServiceTime = function(reductionConditionService){
			reductionConditionService.times.push({method:0, time: '00:00'});
		}

		ReductionsManager.prototype.deleteReductionConditionServiceTime = function(reductionConditionService, index){
				reductionConditionService.times.splice(index, 1);
		}

		ReductionsManager.prototype.addReductionConditionServiceDays = function(reductionConditionService){
			reductionConditionService.days = [];
			for(var i = 0; i < this.days.length; i++){
				reductionConditionService.days[i] = i;
			}
		}

		ReductionsManager.prototype.deleteReductionConditionServiceDays = function(reductionConditionService, index){
			reductionConditionService.days = [];
		}

		ReductionsManager.prototype.getTypeReductionApplications = function(reductionApplication){
			for(var i = 0; i < this.typeReductionApplications.length; i++){
			var typeReductionApplication = this.typeReductionApplications[i];
				if(typeReductionApplication.id == reductionApplication.type){
					return typeReductionApplication;
				}
			}
			return {id: -1, title: ''};
		}

		ReductionsManager.prototype.addReductionApplication = function(reduction){
			var reductionApplication = JSON.parse(JSON.stringify(_skeletonReductionApplication));
			reductionApplication.idReduction = reduction.id;
			reduction.reductionApplications.push(reductionApplication);
		}

		ReductionsManager.prototype.duplicateReductionApplication = function(reduction, reductionApplication){
			var reductionApplication = JSON.parse(JSON.stringify(reductionApplication));
			reductionApplication.id = -1;
			reduction.reductionApplications.push(reductionApplication);
			for(var i = 0; i < reductionApplication.reductionConditionsApplicationList.length; i++){
				var reductionConditionsApplication = reductionApplication.reductionConditionsApplicationList[i];
				reductionConditionsApplication.id = -1;
				reductionConditionsApplication.idReductionApplication = reductionApplication.id;
				for(var i = 0; i < reductionConditionsApplication.reductionConditionsApplications.length; i++){
					var reductionConditionApplication = reductionConditionsApplication.reductionConditionsApplications[i];
					reductionConditionApplication.id = -1;
					reductionConditionApplication.idReductionConditionsApplication = reductionConditionsApplication.id;
				}
			}
		}

		ReductionsManager.prototype.deleteReductionApplication = function(reduction, index){
			reduction.reductionApplications.splice(index, 1);
		}

		ReductionsManager.prototype.addReductionConditionsApplication = function(reductionApplication){
			var reductionConditionsApplication = JSON.parse(JSON.stringify(_skeletonReductionConditionsApplication));
			reductionConditionsApplication.idReductionApplication = reductionApplication.id;
			reductionApplication.reductionConditionsApplicationList.push(reductionConditionsApplication);
		}

		ReductionsManager.prototype.deleteReductionConditionsApplication = function(reductionApplication, index){
			reductionApplication.reductionConditionsApplicationList.splice(index, 1);
		}
		ReductionsManager.prototype.addReductionConditionApplication = function(reductionConditionsApplication){
			var reductionConditionApplication = JSON.parse(JSON.stringify(_skeletonReductionConditionApplication));
			reductionConditionApplication.idReductionConditionsApplication = reductionConditionsApplication.id;
			reductionConditionsApplication.reductionConditionsApplications.push(reductionConditionApplication);
		}

		ReductionsManager.prototype.deleteReductionConditionApplication = function(reductionConditionsApplication, index){
			reductionConditionsApplication.reductionConditionsApplications.splice(index, 1);
		}

		ReductionsManager.prototype.priceIdIsIn = function(idPrice, text){
			var split = text.split(',');
			return split.indexOf(idPrice)!=-1;
		}

		ReductionsManager.prototype.getServiceById = function(idService){
			for(var i = 0; i < this.services.length; i++){
				if(this.services[i].id == idService){
					return this.services[i];
				}
			}
			return null;
		}

		ReductionsManager.prototype.getPricesOfService = function(idService){
			return _priceToIdService[idService];
		}

		ReductionsManager.prototype.displayAlertSaved = function(){
			this.alert = {type: 'success'};
		}

		ReductionsManager.prototype.closeAlertSaved = function(){
			this.alert = null;
		}

		return ReductionsManager;
	};

	angular.module('resa_app').factory('ReductionsManager', ReductionsManagerFactory);
}());
