"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;
	var _backendCtrl = null;

	function LogNotificationsCenterManagerFactory($filter, FunctionsManager){

		var LogNotificationsCenterManager = function(scope, backendCtrl){
  		FunctionsManager.call(this);
  		angular.extend(LogNotificationsCenterManager.prototype, FunctionsManager.prototype);

			this.logNotificationsWaitingNumber = 0;
			this.logNotificationsCenterDisplayed = false;
			this.lastModificationDateBooking = null;
			this.logNotifications = [];
			this.getLogNotificationsLaunched = false;
			this.criticityDisplayed = 0;
			this.limit = 10;

      _scope = scope;
			_self = this;
			_backendCtrl = backendCtrl;
		}

		LogNotificationsCenterManager.prototype.openLogNotificationsCenter = function(){
			this.criticityDisplayed = 0;
			this.seeAllLogNotifications();
		}

		LogNotificationsCenterManager.prototype.loadLogNotificationsAnteriors = function(criticity, limit = -1){
			if(limit == -1) limit = this.limit;
			var lastIdLogNotifications = 0;
			if(criticity != -1){
				for(var i = this.logNotifications.length - 1; i >= 0; i--){
					var log = this.logNotifications[i];
					if(criticity == log.criticity){
						lastIdLogNotifications = log.id;
						break;
					}
				}
			}
			else lastIdLogNotifications = this.logNotifications[this.logNotifications.length - 1].id;
			this.getLogNotifications(limit, criticity, lastIdLogNotifications);
		}

		LogNotificationsCenterManager.prototype.openLogNotificationsPage = function(){
			if(this.logNotifications.length > 0){
				var lastIdLogNotifications = this.logNotifications[0].id;
				this.logNotifications = [];
				this.getLogNotifications(100, -1, lastIdLogNotifications + 1);
			}
		}

		LogNotificationsCenterManager.prototype.getLogNotifications = function(limit, criticity, lastIdLogNotifications){
			var data = {
				action:'getLogNotifications',
				limit: limit,
				criticity: criticity,
				lastIdLogNotifications:lastIdLogNotifications
			}
			this.getLogNotificationsLaunched = true;
			jQuery.post(_backendCtrl.getAjaxUrl(), data, function(data) {
				_scope.$apply(function(){
					data = JSON.parse(data);
					this.getLogNotificationsLaunched = false;
					for(var i = 0; i < data.logNotifications.length; i++){
						var logNotification = data.logNotifications[i];
						this.logNotifications.push(logNotification);
					}
				}.bind(this));
			}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				_scope.$apply(function(){ _self.getLogNotificationsLaunched = false; });
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				_scope.$apply(function(){ _self.getLogNotificationsLaunched = false; });
			});
		}


		LogNotificationsCenterManager.prototype.addNewLogNotifications = function(logNotifications){
			for(var i = logNotifications.length - 1; i >= 0; i--){
				var newLogNotification = logNotifications[i];
				if(this.getLogNotificationById(newLogNotification.id) == null){
					this.logNotifications.unshift(newLogNotification);
				}
			}
		}

		LogNotificationsCenterManager.prototype.getLastIdLogNotifications = function(){
			if(this.logNotifications.length > 0){
				return this.logNotifications[0].id;
			}
			return 0;
		}

		LogNotificationsCenterManager.prototype.getLogNotificationById = function(id){
			for(var i = 0; i < this.logNotifications.length; i++){
				if(this.logNotifications[i].id == id){
					return this.logNotifications[i];
				}
			}
			return null;
		}

		LogNotificationsCenterManager.prototype.getRemainingTime = function(log, dateFormat, timeFormat){
			var actualDate = new Date();
			var logDate = this.parseDate(log.creationDate);
			var text = 'Il y a ';
			var seconds = (actualDate.getTime() - logDate.getTime()) / 1000;
			if(seconds < 60) text += '<1min';
			else if(seconds < 3600) text += Math.floor(seconds / 60) + 'min';
			else if(seconds < 3600 * 12) text += Math.floor(seconds / 3600) + 'h';
			else text = $filter('formatDateTime')(logDate, dateFormat) + ' à ' + $filter('formatDateTime')(logDate, timeFormat);
			return text;
		}

		LogNotificationsCenterManager.prototype.getType = function(log){
			var type = 'type3';
			var actualDate = new Date();
			var logExpiration = this.parseDate(log.expirationDate);
			var days = ((logExpiration.getTime() - actualDate.getTime()) / 1000);
			if(!log.clicked && days <= 3600 * 24 * 4){ type = 'priorite_haute';	}
			else if(!log.clicked){ type = 'priorite_basse'; }
			return type;
		}

		LogNotificationsCenterManager.prototype.isRecent = function(log){
			var type = 'vu';
			var actualDate = new Date();
			var logExpiration = this.parseDate(log.creationDate);
			var days = ((logExpiration.getTime() - actualDate.getTime()) / 1000);
			return days <= 3600 * 24;
		}

		LogNotificationsCenterManager.prototype.clickOnLogNotifications = function(log){
			log.clicked = true;
			var data = {
				action:'clickOnLogNotifications',
				idLogNotification:JSON.stringify(log.id)
			}
			this.getLogNotificationsLaunched = true;
			jQuery.post(_backendCtrl.getAjaxUrl(), data, function(data) {
				_scope.$apply(function(){
					if(data != null){
						if(log.idBooking == -1){
							_backendCtrl.openCustomerDialog(log.idCustomer);
						}
						else {
							data = JSON.parse(data);
							if(_backendCtrl.getCustomerById(data.customer) != null){
								_backendCtrl.updateCustomer(data.customer);
							}
							else _backendCtrl.addCustomer(data.customer);
							if(data.booking.id > -1){
								var booking = _backendCtrl.addBookingAnnotations(data.booking);
								_backendCtrl.openDisplayBooking(booking);
							}
							else {
								sweetAlert('', 'Réservation non trouvée, elle a peut-être été supprimée ?', 'error');
							}
						}
					}
					_self.getLogNotificationsLaunched = false;
				}.bind(this));
			}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				_scope.$apply(function(){ _self.getLogNotificationsLaunched = false; });
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				_scope.$apply(function(){ _self.getLogNotificationsLaunched = false; });
			});
		}



		LogNotificationsCenterManager.prototype.unreadLogNotification = function(event, log){
			log.clicked = false;
			event.stopPropagation();
			var data = {
				action:'unreadLogNotification',
				idLogNotification:JSON.stringify(log.id)
			}
			jQuery.post(_backendCtrl.getAjaxUrl(), data, function(data) {
				_scope.$apply(function(){
					_self.getLogNotificationsLaunched = false;
				}.bind(this));
			}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				_scope.$apply(function(){ _self.getLogNotificationsLaunched = false; });
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				_scope.$apply(function(){ _self.getLogNotificationsLaunched = false; });
			});
		}


		LogNotificationsCenterManager.prototype.seeAllLogNotifications = function(){
			if(this.logNotificationsWaitingNumber > 0){
				var data = {
					action:'seeAllLogNotifications'
				}
				this.logNotificationsWaitingNumber = 0;
				jQuery.post(_backendCtrl.getAjaxUrl(), data, function(data) {
					_scope.$apply(function(){

					}.bind(this));
				}.bind(this)).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}

		LogNotificationsCenterManager.prototype.clickAllLogNotifications = function(){
			if(this.logNotifications.length > 0){
				for(var i = 0; i < this.logNotifications.length; i++){
					var log = this.logNotifications[i];
					log.clicked = true;
				}
				var data = {
					action:'clickAllLogNotifications',
					lastId: this.logNotifications[this.logNotifications.length - 1].id
				}
				jQuery.post(_backendCtrl.getAjaxUrl(), data, function(data) {
					_scope.$apply(function(){

					}.bind(this));
				}.bind(this)).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}

		return LogNotificationsCenterManager;
	}

	angular.module('resa_app').factory('LogNotificationsCenterManager', LogNotificationsCenterManagerFactory);
}());
