"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;
	var _backendCtrl = null;
	var _stage = null;
	var _margin = 10;
	var _blockHeight = 25;

	function drawLine(x1, y1, x2, y2, color = 'black', size = 1){
		var line = new createjs.Shape();
		line.graphics.setStrokeStyle(size);
		line.graphics.beginStroke(color);
		line.graphics.moveTo(x1, y1);
		line.graphics.lineTo(x2, y2);
		line.graphics.endStroke();
		return line;
	}

	function drawDashedLine(x1, y1, x2, y2, color = 'black'){
		var line = new createjs.Shape();
		line.graphics.setStrokeDash([5, 5], 0);
		line.graphics.beginStroke(color);
		line.graphics.moveTo(x1, y1);
		line.graphics.lineTo(x2, y2);
		line.graphics.endStroke();
		return line;
	}

	function drawText(text, x, y, color = 'black', fontSize = '1em', font='"Segoe UI"'){
		var text = new createjs.Text(text, fontSize + ' ' + font, color);
		text.x = x;
		text.y = y;
		text.textBaseline = "alphabetic";
		return text;
	}

	function drawRect(x, y, width, height, color = 'black'){
		var rect = new createjs.Shape();
		rect.graphics.beginFill(color).drawRect(x, y, width, height);
		return rect;
	}

	function PlanningServiceManagerFactory($filter, FunctionsManager){

		var PlanningServiceManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(PlanningServiceManager.prototype, FunctionsManager.prototype);

      _scope = scope;
			_self = this;
			_backendCtrl = scope.backendCtrl;
			_backendCtrl.planningServiceCtrl = this;

			this.id = '';
			this.heightTimeline = 50;
			this.widthServicesList = 200;
			this.widthTimeline = 100;
			this.numberHours = 10;

			this.mapIdServicesToRapports = {};
		}

		PlanningServiceManager.prototype.initialize = function(id){
			this.id = id;
			_stage = new createjs.Stage(this.id);
			_stage.enableMouseOver();
			this.render();
		}

		PlanningServiceManager.prototype.render = function(){
			//_stage.addChild(drawRect(0, 1, _stage.canvas.width, _stage.canvas.height, 'white'));
			_stage.canvas.width = jQuery('#' + this.id).width();
			_stage.removeAllChildren();
			this.createTimeline();
			this.createRapports();
			this.createTab();
			_stage.update();
		}


		PlanningServiceManager.prototype.hoursToX = function(hours){
			var i = hours - _backendCtrl.settings.calendar.start_time;
			var stepX = this.widthTimeline / (this.numberHours * 2);
			var x = this.widthServicesList + _margin + stepX * i * 2;
			return x;
		}

		PlanningServiceManager.prototype.createTab = function(){
			_stage.addChild(drawLine(0, this.heightTimeline, _stage.canvas.width, this.heightTimeline));
			_stage.addChild(drawLine(this.widthServicesList, 0, this.widthServicesList, _stage.canvas.height));
		}

		PlanningServiceManager.prototype.createTimeline = function(){
			if(_backendCtrl.settings.calendar != null){
				this.widthTimeline = _stage.canvas.width - this.widthServicesList - 1 - (2 * _margin);
				this.numberHours = _backendCtrl.settings.calendar.end_time - _backendCtrl.settings.calendar.start_time;
				var splitTime = 2;
				if(_backendCtrl.settings.calendar.split_time * 1 == 10){
					splitTime = 4;
				}
				var stepX = this.widthTimeline / (this.numberHours * splitTime);
				for(var i = 0; i < this.numberHours; i++){
					var hours = (_backendCtrl.settings.calendar.start_time * 1) + i;
					var firstX = this.widthServicesList + _margin + stepX * i * splitTime;
					if(splitTime == 4) var firstX15 = this.widthServicesList + _margin + stepX * (i * splitTime + 1);
					var firstX30 = this.widthServicesList + _margin + stepX * (i * splitTime + splitTime/2);
					if(splitTime == 4) var firstX45 = this.widthServicesList + _margin + stepX * (i * splitTime + 3);
					_stage.addChild(drawText(hours + 'h', firstX - 5, this.heightTimeline - _margin, 'black', '1.3em','"Segoe UI"'));
					if(splitTime == 4) _stage.addChild(drawText('15', firstX15, this.heightTimeline - _margin));
					_stage.addChild(drawText('30', firstX30, this.heightTimeline - _margin, 'black', '1.1em'));
					if(splitTime == 4) _stage.addChild(drawText('45', firstX45, this.heightTimeline - _margin));
					_stage.addChild(drawDashedLine(firstX + 5, this.heightTimeline, firstX + 5, _stage.canvas.height, 'grey', 1));
					if(splitTime == 4) _stage.addChild(drawDashedLine(firstX15 + 5, this.heightTimeline, firstX15 + 5, _stage.canvas.height, 'grey', 1));
					_stage.addChild(drawDashedLine(firstX30 + 5, this.heightTimeline, firstX30 + 5, _stage.canvas.height, 'grey', 1));
					if(splitTime == 4) _stage.addChild(drawDashedLine(firstX45 + 5, this.heightTimeline, firstX45 + 5, _stage.canvas.height, 'grey', 1));
				}
			}
		}

		PlanningServiceManager.prototype.createRapports = function(){
			this.mapIdServicesToRapports = {};
			var decalage = 0;
			var services = _backendCtrl.getNotOldServices();
			var nbNumberServices = 0;
			for(var i = 0; i < services.length; i++){
				var service = services[i];
				var datePlanningMembers = _backendCtrl.datePlanningMembers;
				if(datePlanningMembers == null) datePlanningMembers = new Date();
				var rapportsByServices = _backendCtrl.getAllFilteredRapportsByServicesWithIdService(service.id, datePlanningMembers);
				var maxLevel = 0;
				for(var j = 0; j < rapportsByServices.length; j++){
					var rapportByServices = rapportsByServices[j];
					var startHours = rapportByServices.startDate.getHours() + rapportByServices.startDate.getMinutes() / 60;
					var endHours = rapportByServices.endDate.getHours()  + rapportByServices.endDate.getMinutes() / 60;
					if(rapportByServices.startDate.getDate() == rapportByServices.endDate.getDate() && rapportByServices.startDate.getTime() > rapportByServices.endDate.getTime()){
						endHours = startHours;
						console.log('echange !');
					}
					if(startHours > endHours) {
						endHours = _backendCtrl.settings.calendar.end_time;
					}

					if(startHours == endHours) endHours += 0.5;
					else if(startHours > endHours) endHours = _backendCtrl.settings.calendar.end_time;

					var x = this.hoursToX(startHours);
					var y = (this.heightTimeline + _margin) + (nbNumberServices + decalage) * (_blockHeight + _margin);
					var width = this.hoursToX(endHours) - x;
					var level = this.getLevel(service.id, x + 5,y,width,_blockHeight);
					y = (this.heightTimeline + _margin) + (nbNumberServices + decalage + level) * (_blockHeight + _margin);
					maxLevel = Math.max(maxLevel, level);

					var serviceBlock = new createjs.Container();
					var block = new createjs.Shape();
					block.graphics.beginFill(service.color).drawRect(0, 0, width, _blockHeight);
					var border = new createjs.Shape();
					border.graphics.beginStroke('grey').drawRect(0, 0, width, _blockHeight);

					var text = new createjs.Text( rapportByServices.appointments.length + ' res. - ' +
						rapportByServices.numberPersons + ' pers.', '1em "Segoe UI"', 'white');
					text.set({
					  textAlign: "center",
					  textBaseline: "middle",
					  x: width / 2,
					  y: _blockHeight / 2
					})
					serviceBlock.set({x: x + 5, y: y});
					serviceBlock.rapportByServices = rapportByServices;

					serviceBlock.on("click", function(event){
						_scope.$apply(function(){
							_self.displayBookings(event.currentTarget.rapportByServices);
						});
					});
					serviceBlock.on("mouseover", function(evt) {
		      	_stage.cursor = "pointer";
		     	});
					serviceBlock.on("mouseout", function(evt) {
		      	_stage.cursor = "default";
		     	});

					if(!this.mapIdServicesToRapports[service.id]){
						this.mapIdServicesToRapports[service.id] = [];
					}
					this.mapIdServicesToRapports[service.id].push({x:x + 5,y:y,width:width,height:_blockHeight,level:level});
					serviceBlock.addChild(block, border, text);
					_stage.addChild(serviceBlock);
				}
				if((_backendCtrl.noHaveCategory(service) ||
					_backendCtrl.trueIfPlacesNotFiltered(service, _backendCtrl.filters)) &&
					_backendCtrl.filters.services[service.id]){
					var y = (this.heightTimeline + _margin) + (nbNumberServices + decalage) * (_blockHeight + _margin);
					decalage += maxLevel;
					nbNumberServices++;
					var serviceBlock = new createjs.Container();
					var block = new createjs.Shape();
					block.graphics.beginFill(service.color).drawRect(0, 0, this.widthServicesList, _blockHeight);
					var border = new createjs.Shape();
					border.graphics.beginStroke('grey').drawRect(0, 0, this.widthServicesList, _blockHeight);
					var text = new createjs.Text($filter('htmlSpecialDecode')(this.getTextByLocale(service.name, _backendCtrl.locale)), '1em "Segoe UI"', 'white');
					text.set({
						textAlign: "center",
						textBaseline: "middle",
						x: this.widthServicesList / 2,
						y: _blockHeight / 2
					})
					serviceBlock.set({x: 0, y: y});
					serviceBlock.addChild(block, border, text);
					_stage.addChild(serviceBlock);
				}
			}
		}

		PlanningServiceManager.prototype.getLevel = function(idService, x, y, width, height){
			var mapIdServiceToRapports =	this.mapIdServicesToRapports[idService];
			var listLevelCuts = [];
			if(mapIdServiceToRapports){
				for(var i = 0; i < mapIdServiceToRapports.length; i++){
					var mapIdServiceToRapport = mapIdServiceToRapports[i];
					/*console.log('numberTimeslots : ' + JSON.stringify(mapIdServiceToRapport) + ' - ' + x + ',' + (x + width));
					console.log('Cas n°1 : ' + (mapIdServiceToRapport.x < x && x < mapIdServiceToRapport.x + mapIdServiceToRapport.width));
					console.log('Cas n°2 : ' + (mapIdServiceToRapport.x > x && x + width > mapIdServiceToRapport.x + mapIdServiceToRapport.width));
					console.log('Cas n°3 : ' + (mapIdServiceToRapport.x < x + width && x + width < mapIdServiceToRapport.x + mapIdServiceToRapport.width));*/
					if((mapIdServiceToRapport.x <= x && x < mapIdServiceToRapport.x + mapIdServiceToRapport.width) ||
						(mapIdServiceToRapport.x > x && x + width > mapIdServiceToRapport.x + mapIdServiceToRapport.width) ||
						(mapIdServiceToRapport.x < x + width && x + width < mapIdServiceToRapport.x + mapIdServiceToRapport.width)){
						listLevelCuts.push(mapIdServiceToRapport.level);
					}
				}
			}
			for(var level = 0; level < 100; level++){
				if(listLevelCuts.indexOf(level) == -1){
					return level;
				}
			}
			return 0;
		}

		PlanningServiceManager.prototype.displayBookings = function(rapportByServices){
			var idsBooking = [];
			var bookings = [];
			for(var i = 0; i < rapportByServices.appointments.length; i++){
				var appointment = rapportByServices.appointments[i];
				if(idsBooking.indexOf(appointment.idBooking) == -1){
					idsBooking.push(appointment.idBooking);
					bookings.push(_backendCtrl.getBookingById(appointment.idBooking));
				}
			}
			_backendCtrl.openDisplayBookings(bookings);
		}

		return PlanningServiceManager;
	}

	angular.module('resa_app').factory('PlanningServiceManager', PlanningServiceManagerFactory);
}());
