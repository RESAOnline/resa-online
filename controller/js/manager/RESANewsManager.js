/**
 * Get RESA Online news
 */
"use strict";

(function(){

	function RESANewsManagerFactory($filter, $location, $anchorScroll){

		var _scope = null;
		var _self = null;
		var _backendCtrl = null;

    var RESANewsManager = function(scope, self){
			this.RESANews = [];
			this.RESANewsLaunched = false;
			this.RESANewsDisplayed = false;

			this.RESANewsLastViewNumber = -1;
			this.RESANewsWaitingNumber = 0;

			_scope = scope;
			_self = self;
			_backendCtrl = self;
    }

		RESANewsManager.prototype.RESANewsIsOpened = function(){
			return this.RESANewsDisplayed;
		}

		RESANewsManager.prototype.openRESANews = function(){
			//console.log('openRESANews');
			this.RESANewsDisplayed = true;
			this.seeAllRESANews();
			event.stopPropagation();

			jQuery('body').click(function(){ _scope.$apply(function(){ _self.cloneRESANews(); }); });
			jQuery('#resa_news_center_content').click(function(event){ event.stopPropagation(); });
			jQuery('.full_screen').click(function(event){ event.stopPropagation(); });
		}

		RESANewsManager.prototype.cloneRESANews = function(){
			//console.log('cloneRESANews');
			this.RESANewsDisplayed = false;
			jQuery('body').unbind('click');
			jQuery('#resa_news_center_content').unbind('click');
		}

		RESANewsManager.prototype.switchRESANewsCenter = function(event){
			if(this.RESANewsIsOpened()){
				this.cloneRESANews(event);
			}
			else {
				this.openRESANews(event);
			}
		}

		RESANewsManager.prototype.calculateNotView = function(){
			var number = 0;
			for(var i = 0; i < this.RESANews.length; i++){
				var news = this.RESANews[i];
				if(news.id == this.RESANewsLastViewNumber){
					break;
				}
				number++;
			}
			this.RESANewsWaitingNumber = number;
		}

		RESANewsManager.prototype.getRESANews = function(page, perPage){
			if(!this.RESANewsLaunched){
				this.RESANewsLaunched = true;
				jQuery.ajax({
			    type: "GET",
			    url: 'https://resa-online.fr/wp-json/wp/v2/posts?page=' + page + '&perPage=' + perPage+'&categories=7',
			    contentType: "application/json; charset=utf-8",
			    dataType: "json",
			    success: function(data){
						_scope.$apply(function(){
							 _self.RESANewsLaunched = false;
							 _self.RESANews = _self.RESANews.concat(data);
							 _self.calculateNotView();
						});
					},
			    failure: function(errMsg) {
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					 _scope.$apply(function(){ _self.searchCustomersLaunched = false; });
					 },
					 error: function(errMsg) {
						 sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
						_scope.$apply(function(){ _self.searchCustomersLaunched = false; });
					},
				});
			}
		}

		RESANewsManager.prototype.seeAllRESANews = function(){
			if(this.RESANewsWaitingNumber > 0 && _self.RESANews.length > 0){
				var data = {
					action:'seeAllRESANews',
					lastId:this.RESANews[0].id
				}
				this.RESANewsWaitingNumber = 0;
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

    return RESANewsManager;
  }
  angular.module('resa_app').factory('RESANewsManager', RESANewsManagerFactory);
}());
