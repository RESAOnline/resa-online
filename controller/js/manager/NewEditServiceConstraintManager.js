"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;

	function NewEditServiceConstraintManagerFactory($filter, FunctionsManager){
		var NewEditServiceConstraintManager = function(scope){
      FunctionsManager.call(this);
  		angular.extend(NewEditServiceConstraintManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.editServiceConstraintLaunched = false;
			this.subPage = 'service';
			this.services = [];
			this.members = [];
      this.serviceConstraint = null;
			this.memberConstraint = null;
      this.settings = null;

			this.manyDays = false;
  		this.dateStart = new Date();
  		this.dateEnd = new Date();
      this.timeStart = new Date(0, 0, 0, this.dateStart.getHours(), this.dateStart.getMinutes());
      this.timeEnd = new Date(0, 0, 0, this.dateStart.getHours(), this.dateStart.getMinutes());

			_self = this;
			_scope = scope;
      _scope.backendCtrl.editServiceConstraintCtrl = this;
		}

		NewEditServiceConstraintManager.prototype.initialize = function(serviceConstraint, memberConstraint, services, members, defaultDate, settings, ajaxUrl) {
      _ajaxUrl = ajaxUrl;
      this.serviceConstraint = null;
			this.memberConstraint = null;
			this.settings = settings;
			this.services = services;
			this.members = [];
			for(var i = 0; i < members.length; i++){
				var member = members[i];
				if(_scope.backendCtrl.filters.members[member.id] && _scope.backendCtrl.trueIfPlacesNotFiltered(member, _scope.backendCtrl.filters)){
					this.members.push(member);
				}
			}
      if(serviceConstraint != null){
        this.serviceConstraint = JSON.parse(JSON.stringify(serviceConstraint));
				if(this.serviceConstraint.id > -1) this.setServiceSubPage();
      }
			if(memberConstraint != null){
        this.memberConstraint = JSON.parse(JSON.stringify(memberConstraint));
				if(this.memberConstraint.id > -1) this.setMemberSubPage();
      }

			this.manyDays = false;
  		this.dateStart = defaultDate;
			this.dateEnd = defaultDate;
  		if(!this.isNewServiceConstraint()){
				if(this.isServiceSubPage()){
	  			this.dateStart = $filter('parseDate')(this.serviceConstraint.startDate);
		  		this.dateEnd = $filter('parseDate')(this.serviceConstraint.endDate);
				}
				else {
					this.dateStart = $filter('parseDate')(this.memberConstraint.startDate);
		  		this.dateEnd = $filter('parseDate')(this.memberConstraint.endDate);
				}
  		}
			this.manyDays = !this.isSameDay(this.dateStart, this.dateEnd);
      this.timeStart = new Date(0, 0, 0, this.dateStart.getHours(), this.dateStart.getMinutes());
      this.timeEnd = new Date(0, 0, 0, this.dateEnd.getHours(), this.dateEnd.getMinutes());
		}

    NewEditServiceConstraintManager.prototype.close = function(){
      this.opened = false;
      this.serviceConstraint = null;
    }

		NewEditServiceConstraintManager.prototype.setServiceSubPage = function(){ this.subPage = 'service'; }
		NewEditServiceConstraintManager.prototype.setMemberSubPage = function(){ this.subPage = 'member'; }
		NewEditServiceConstraintManager.prototype.isServiceSubPage = function(){ return this.subPage == 'service'; }
		NewEditServiceConstraintManager.prototype.isMemberSubPage = function(){ return this.subPage == 'member'; }
		NewEditServiceConstraintManager.prototype.isDisplayMenu = function(){ return this.serviceConstraint!=null && this.serviceConstraint.id == -1 && this.memberConstraint != null && this.memberConstraint.id == -1; }

		NewEditServiceConstraintManager.prototype.getPlaceById = function(idPlace){
			if(this.settings.places == null || this.settings.places=='' || this.settings.places==false){
				this.settings.places = [];
			}
			for(var i = 0; i < this.settings.places.length; i++){
				var place = this.settings.places[i];
				if(place.id == idPlace){
					return place;
				}
			}
			return null;
		}

		NewEditServiceConstraintManager.prototype.getServiceName = function(service, locale){
			var name = this.getTextByLocale(service.name, locale);
			if(service.places.length > 0){
				var placesText = '';
				for(var i = 0; i < service.places.length; i++){
					var place = this.getPlaceById(service.places[i]);
					if(i > 0) placesText += ', ';
					placesText += $filter('htmlSpecialDecode')(this.getTextByLocale(place.name, locale));
				}
				name = '['+placesText+'] ' + name;
			}
			return name;
		}

		NewEditServiceConstraintManager.prototype.getServiceNameById = function(idService, locale){
			var service = this.services.find(element => { if(element.id == idService) return element; });
			if(service == null) return '';
			return this.getServiceName(service, locale);
		}

		NewEditServiceConstraintManager.prototype.getMemberName = function(member, locale){
			var name = $filter('htmlSpecialDecode')(member.nickname);
			if(member.places.length > 0){
				var placesText = '';
				for(var i = 0; i < member.places.length; i++){
					var place = this.getPlaceById(member.places[i]);
					if(i > 0) placesText += ', ';
					placesText += $filter('htmlSpecialDecode')(this.getTextByLocale(place.name, locale));
				}
				name = '['+placesText+'] ' + name;
			}
			return name;
		}

		NewEditServiceConstraintManager.prototype.getMemberNameById = function(idMember, locale){
			var member = this.members.find(element => { if(element.id == idMember) return element; });
			if(member == null) return '';
			return this.getMemberName(member, locale);
		}

    NewEditServiceConstraintManager.prototype.isNewServiceConstraint = function(){
      return this.isDisplayMenu();
    }

		NewEditServiceConstraintManager.prototype.changeStartTime = function(){
			if(this.timeStart.getTime() > this.timeEnd.getTime()){
				this.timeEnd = new Date(0, 0, 0, this.timeStart.getHours() + 1, this.timeStart.getMinutes());
			}
		}

		NewEditServiceConstraintManager.prototype.changeEndTime = function(){
			if(this.timeStart.getTime() > this.timeEnd.getTime() && this.timeEnd.getHours() > 0){
				this.timeStart = new Date(0, 0, 0, this.timeEnd.getHours() - 1, this.timeEnd.getMinutes());
			}
		}

    NewEditServiceConstraintManager.prototype.isOkForm = function(){
      return !this.editServiceConstraintLaunched && this.serviceConstraint!=null && this.memberConstraint!=null &&
			 this.generateEndDate().getTime() > this.generateStartDate().getTime() &&
			 ((this.serviceConstraint.idService != -1 && this.isServiceSubPage()) || (this.memberConstraint.idMember != -1 && this.isMemberSubPage()));
    }

		NewEditServiceConstraintManager.prototype.generateStartDate = function(){
			var dateStart = new Date(this.dateStart);
			dateStart.setHours(this.timeStart.getHours());
			dateStart.setMinutes(this.timeStart.getMinutes());
			return dateStart;
		}

		NewEditServiceConstraintManager.prototype.generateEndDate = function(){
			var dateEnd = new Date(this.dateStart);
			if(this.manyDays) dateEnd = new Date(this.dateEnd);
			dateEnd.setHours(this.timeEnd.getHours());
			dateEnd.setMinutes(this.timeEnd.getMinutes());
			return dateEnd;
		}

    NewEditServiceConstraintManager.prototype.validForm = function(){
      if(this.isOkForm()){
        this.editServiceConstraintLaunched = true;
				if(this.isServiceSubPage()){
	        var constraint = JSON.parse(JSON.stringify(this.serviceConstraint));
	  			var dateStart = this.generateStartDate();
					constraint.startDate = $filter('formatDateTime')(dateStart);
	  			var dateEnd = this.generateEndDate();
					constraint.endDate = $filter('formatDateTime')(dateEnd);
					constraint.idService = constraint.idService * 1;
				}
				else {
	        var constraint = JSON.parse(JSON.stringify(this.memberConstraint));
	  			var dateStart = this.generateStartDate();
					constraint.startDate = $filter('formatDateTime')(dateStart);
	  			var dateEnd = this.generateEndDate();
					constraint.endDate = $filter('formatDateTime')(dateEnd);
					constraint.idMember = constraint.idMember * 1;
				}

        var data = {
  				action:'editServiceConstraint',
  				constraint: JSON.stringify(constraint),
					isServiceConstraint: JSON.stringify(this.isServiceSubPage())
  			}

  			jQuery.post(_ajaxUrl, data, function(data) {
  				_scope.$apply(function(){
  					this.editServiceConstraintLaunched = false;
  					data = JSON.parse(data);
  					if(typeof data === 'string'){
  						sweetAlert('', data, 'error');
  					}
  					else {
							sweetAlert('', 'Ok', 'success');
              if(_self.isServiceSubPage()) _scope.backendCtrl.updateServiceConstraints(data);
              else _scope.backendCtrl.updateMemberConstraints(data);
              _self.close();
  					}
  				}.bind(this));
  			}.bind(this)).fail(function(err){
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  			}).error(function(err){
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  			});
  		}
  	}

  	NewEditServiceConstraintManager.prototype.deleteServiceConstraint = function(dialogTexts){
			if(!this.editServiceConstraintLaunched){
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
					_scope.$apply(function(){ _self.editServiceConstraintLaunched = true; });
					var idConstraint = _self.serviceConstraint.id;
					var isServiceConstraint = true;
					if(_self.isMemberSubPage() && _self.memberConstraint.id) {
						isServiceConstraint = false;
						idConstraint = _self.memberConstraint.id;
					}
	  			var data = {
						action:'deleteServiceConstraint',
						idConstraint: JSON.stringify(idConstraint),
						isServiceConstraint: JSON.stringify(isServiceConstraint)
	  			}
	  			jQuery.post(_ajaxUrl, data, function(data) {
	  				_scope.$apply(function(){
							_self.editServiceConstraintLaunched = false;
	  					data = JSON.parse(data);
	  					if(typeof data === 'string'){
	  						sweetAlert('', data, 'error');
	  					}
	  					else {
								sweetAlert('', 'Ok', 'success');
	              if(_self.isServiceSubPage()) _scope.backendCtrl.deleteServiceConstraint(_self.serviceConstraint);
	              else _scope.backendCtrl.deleteMemberConstraint(_self.memberConstraint);
	              _self.close();
	  					}
	  				});
	  			}).fail(function(err){
	  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
	  			}).error(function(err){
	  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
	  			});
	  		});
			}
  	}

		return NewEditServiceConstraintManager;
	}

	angular.module('resa_app').factory('NewEditServiceConstraintManager', NewEditServiceConstraintManagerFactory);
}());
