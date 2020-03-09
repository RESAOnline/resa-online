"use strict";

(function(){

	var _scope = null;
	var _self = null;
	var _log = null;

	var formatBeforeSend = function(reductions){
		var reductions = JSON.parse(JSON.stringify(reductions));
		for(var i = 0; i < reductions.length; i++){
			var reduction = reductions[i];
			for(var j = 0; j < reduction.reductionConditionsList.length; j++){
				for(var k = 0; k < reduction.reductionConditionsList[j].reductionConditions.length; k++){
					var reductionCondition = reduction.reductionConditionsList[j].reductionConditions[k];
					if(reductionCondition.type == 'service'){
						var concat = '';
						for(var index = 0; index < reductionCondition.param3.length; index++){
							if(concat!='') concat += ',';
							concat += reductionCondition.param3[index];
						}
						reductionCondition.param3 = concat;
					}
				}
			}
			for(var j = 0; j < reduction.reductionApplications.length; j++){
				for(var k = 0; k < reduction.reductionApplications[j].reductionConditionsApplicationList.length; k++){
					for(var l = 0; l < reduction.reductionApplications[j].reductionConditionsApplicationList[k].reductionConditionsApplications.length; l++){
						var reductionConditionApplication = reduction.reductionApplications[j].reductionConditionsApplicationList[k].reductionConditionsApplications[l];
						if(reductionConditionApplication.type == 'service'){
							var concat = '';
							for(var index = 0; index < reductionConditionApplication.param3.length; index++){
								if(concat!='') concat += ',';
								concat += reductionConditionApplication.param3[index];
							}
							reductionConditionApplication.param3 = concat;
						}
					}
				}
			}
		}
		return reductions;
	}

	function ReductionsController(ReductionsManager, $scope, $log){
		ReductionsManager.call(this, $scope);
		angular.extend(ReductionsController.prototype, ReductionsManager.prototype);

		_scope = $scope;
		_self = this;
		_log = $log;

		this.dateOptions = {formatYear: 'yy',maxDate: new Date(2020, 5, 22),startingDay: 1};
		this.date = new Date();
	}

	ReductionsController.prototype.formatDate = function(date){
		return this.parseDate(date);
	}

	ReductionsController.prototype.removeReduction = function(index, dialogTexts){
		var name = this.getTextByLocale(this.reductions[index].name, this.locale);
		sweetAlert({
		  title: dialogTexts.title + ' ' + name + ' ?',
		  text: dialogTexts.text + ' ' + name + ' ?',
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonColor: "#DD6B55",
		  confirmButtonText: dialogTexts.confirmButton,
		  cancelButtonText: dialogTexts.cancelButton,
		  closeOnConfirm: true
		}, function(){
			_scope.$apply(function(){
				_self.deleteReduction(index);
			});
		});
	}


	ReductionsController.prototype.updateReductions = function(){
		var reductions = formatBeforeSend(this.reductions);
		var data = {
			action:'updateReductions',
			reductions: JSON.stringify(reductions)
		}
		jQuery.post(ajaxurl, data, function(data) {
			_scope.$apply(function(){
				data = JSON.parse(data);
				_log.debug('Succes : ' + JSON.stringify(data));
				for(var i = 0; i < data.length; i++){
					var reduction = data[i];
					for(var index in reduction.name) {
					  reduction.name[index] = reduction.name[index].replace(new RegExp('&#039;', 'g'),'\'');
					}
					_self.htmlSpecialDecodeArray(reduction.name);
					_self.htmlSpecialDecodeArray(reduction.presentation);
					_self.reformatDateField(reduction);
				}
				_self.reductions = data;
				_self.resetTitle();
				_self.displayAlertSaved();
			});
		}).fail(function(err){
			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
		}).error(function(err){
			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
		});
	}

	angular.module('resa_app').controller('ReductionsController', ReductionsController);
}());
