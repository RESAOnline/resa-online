"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;

	function NewEditInfoCalendarManagerFactory($filter, FunctionsManager){
		var NewEditInfoCalendarManager = function(scope){
      FunctionsManager.call(this);
  		angular.extend(NewEditInfoCalendarManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.editInfoCalendarActionLaunched = false;
      this.infoCalendar = null;
      this.settings = null;

			this.idPlaces = '';
			this.placesList = [];

			this.manyDays = false;
  		this.date = new Date();
  		this.dateEnd = new Date();
      this.time = new Date(0, 0, 0, this.date.getHours(), this.date.getMinutes());
      this.timeEnd = new Date(0, 0, 0, this.date.getHours(), this.date.getMinutes());

			_self = this;
			_scope = scope;
      _scope.backendCtrl.newEditInfoCalendarCtrl = this;
		}

		NewEditInfoCalendarManager.prototype.initialize = function(infoCalendar, defaultDate, settings, ajaxUrl) {
      _ajaxUrl = ajaxUrl;
      this.infoCalendar = null;
      this.editInfoCalendarActionLaunched = false;
			this.idPlaces = '';
			this.manyDays = false;
      if(infoCalendar != null){
        this.infoCalendar = JSON.parse(JSON.stringify(infoCalendar));
				this.idPlaces = infoCalendar.idPlaces;
    		this.infoCalendar.note = this.infoCalendar.note.replace(new RegExp('<br />', 'g'),'\n');
    		this.infoCalendar.note = this.htmlSpecialDecode(this.infoCalendar.note);
      }
  		this.date = defaultDate;
			this.dateEnd = defaultDate;
			var dateEnd = null;
  		if(!this.isNewInfoCalendar()){
  			this.date = $filter('parseDate')(this.infoCalendar.date);
	  		dateEnd = $filter('parseDate')(this.infoCalendar.dateEnd);
				this.manyDays = !this.isSameDay(this.date, dateEnd);
				if(this.manyDays) this.dateEnd = dateEnd;
				else this.dateEnd = new Date(this.date);
			}
      this.time = new Date(0, 0, 0, this.date.getHours(), this.date.getMinutes());
      if(dateEnd == null){
				this.timeEnd = new Date(0, 0, 0, this.date.getHours(), this.date.getMinutes());
			}
			else {
				this.timeEnd = new Date(0, 0, 0, dateEnd.getHours(), dateEnd.getMinutes());
			}
			this.settings = settings;
			if(this.settings.places.length > 0){
				this.placesList = [{value:'', title:'Tous'}];
				for(var i = 0; i < this.settings.places.length; i++){
					var place = this.settings.places[i];
					this.placesList.push({
						value:',' + place.id + ',',
						title:this.getTextByLocale(place.name)
					});
				}
				if(_scope.backendCtrl != null && _scope.backendCtrl.filters.places != null && this.isNewInfoCalendar()){
					var placeId = '';
					var one = true;
					for(var i = 0; i < this.settings.places.length; i++){
						var place = this.settings.places[i];
						if(_scope.backendCtrl.filters.places[place.id]){
							if(placeId != '') one = false;
							placeId = ',' + place.id + ',';
						}
					}
					if(one) this.idPlaces = placeId;
				}
			}
		}

    NewEditInfoCalendarManager.prototype.close = function(){
      this.opened = false;
      this.infoCalendar = null;
    }

    NewEditInfoCalendarManager.prototype.isNewInfoCalendar = function(){
      return this.infoCalendar == null || this.infoCalendar.id == -1;
    }

		NewEditInfoCalendarManager.prototype.changeStartTime = function(){
			if(this.time.getTime() > this.timeEnd.getTime()){
				this.timeEnd = new Date(0, 0, 0, this.time.getHours() + 1, this.time.getMinutes());
			}
		}

		NewEditInfoCalendarManager.prototype.changeEndTime = function(){
			if(this.time.getTime() > this.timeEnd.getTime() && this.timeEnd.getHours() > 0){
				this.time = new Date(0, 0, 0, this.timeEnd.getHours() - 1, this.timeEnd.getMinutes());
			}
		}

    NewEditInfoCalendarManager.prototype.isOkForm = function(){
      return !this.editInfoCalendarActionLaunched && this.infoCalendar!=null && this.infoCalendar.note != null && this.infoCalendar.note.length > 0 && this.generateEndDate().getTime() > this.generateStartDate().getTime();
    }

		NewEditInfoCalendarManager.prototype.generateStartDate = function(){
			var date = new Date(this.date);
			date.setHours(this.time.getHours());
			date.setMinutes(this.time.getMinutes());
			return date;
		}

		NewEditInfoCalendarManager.prototype.generateEndDate = function(){
			var date = new Date(this.date);
			if(this.manyDays) {
				date = new Date(this.dateEnd);
			}
			date.setHours(this.timeEnd.getHours());
			date.setMinutes(this.timeEnd.getMinutes());
			return date;
		}

    NewEditInfoCalendarManager.prototype.validForm = function(){
      if(this.isOkForm()){
        this.editInfoCalendarActionLaunched = true;
        var infoCalendar = JSON.parse(JSON.stringify(this.infoCalendar));
				infoCalendar.idPlaces = this.idPlaces;
  			infoCalendar.note = infoCalendar.note.replace(new RegExp('\n', 'g'),'<br />');
  			infoCalendar.date = $filter('formatDateTime')(this.generateStartDate());
  			infoCalendar.dateEnd = $filter('formatDateTime')(this.generateEndDate());
				infoCalendar.startTime = this.time.getHours() + ':' + this.time.getMinutes();
				infoCalendar.endTime = this.timeEnd.getHours() + ':' + this.timeEnd.getMinutes();

        var data = {
  				action:'editInfoCalendar',
  				infoCalendar: JSON.stringify(infoCalendar)
  			}

  			jQuery.post(_ajaxUrl, data, function(data) {
  				_scope.$apply(function(){
  					this.editInfoCalendarActionLaunched = false;
  					data = JSON.parse(data);
  					if(typeof data === 'string'){
  						sweetAlert('', data, 'error');
  					}
  					else {
							sweetAlert('', 'Ok', 'success');
              _scope.backendCtrl.updateInfoCalendar(data);
              _self.close();
  					}
  				}.bind(this));
  			}.bind(this)).fail(function(err){
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					_scope.$apply(function(){ this.editInfoCalendarActionLaunched = false; });
  			}).error(function(err){
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					_scope.$apply(function(){ this.editInfoCalendarActionLaunched = false; });
  			});
  		}
  	}

  	NewEditInfoCalendarManager.prototype.deleteInfoCalendar = function(dialogTexts){
			if(!this.editInfoCalendarActionLaunched){
	  	  sweetAlert({
	  		  title: dialogTexts.title,
	  		  text: dialogTexts.text,
	  		  type: "warning",
	  		  showCancelButton: true,
	  		  confirmButtonColor: "#DD6B55",
	  		  confirmButtonText: dialogTexts.confirmButton,
	  		  cancelButtonText: dialogTexts.cancelButton,
	  		  closeOnConfirm: true,
	  		  html: false
	  		}, function(){
					_scope.$apply(function(){ _self.editInfoCalendarActionLaunched = true; });
	  			var data = {
	  				action:'deleteInfoCalendar',
	  				idInfoCalendar: JSON.stringify(_self.infoCalendar.id)
	  			}
	  			jQuery.post(_ajaxUrl, data, function(data) {
	  				_scope.$apply(function(){
	  					_self.editInfoCalendarActionLaunched = false;
	  					data = JSON.parse(data);
	  					if(typeof data === 'string'){
	  						alert(data);
	  					}
	  					else {
								sweetAlert('', 'Ok', 'success');
	  						_scope.backendCtrl.deleteInfoCalendar(_self.infoCalendar);
	              _self.close();
	  					}
	  				});
	  			}).fail(function(err){
	  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
						_scope.$apply(function(){ this.editInfoCalendarActionLaunched = false; });
	  			}).error(function(err){
	  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
						_scope.$apply(function(){ this.editInfoCalendarActionLaunched = false; });
	  			});
	  		});
			}
  	}

		return NewEditInfoCalendarManager;
	}

	angular.module('resa_app').factory('NewEditInfoCalendarManager', NewEditInfoCalendarManagerFactory);
}());
