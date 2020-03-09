"use strict";

(function(){

	var getReductionById = function(idReduction, reductions){
		for(var i = 0; i < reductions.length; i++){
			var reduction = reductions[i];
			if(reduction.id == idReduction)
				return reduction;
		}
		return null;
	}

	function BasketFactory($log){

		var _self = null;
		var Basket = function(){
			this.mapIdServiceToServiceParameters = [];
			_self = this;
		}

		Basket.prototype.getNextId = function(){
			var max = 0;
			var servicesParameters = this.getAllServicesParameters();
			for(var i = 0; i < servicesParameters.length; i++){
				var serviceParameter = servicesParameters[i];
				max = Math.max(max, serviceParameter.id);
			}
			return max + 1;
		}

		/**
		 * add a new serviceParameters
		 */
		Basket.prototype.addServiceParameters = function(serviceParameters, noChangeId){
			if(noChangeId == null) noChangeId = false;
			var service = serviceParameters.service;
			if(this.mapIdServiceToServiceParameters[service.id] == null) {
				this.mapIdServiceToServiceParameters[service.id] = {
					service:service,
					servicesParameters: []
				};
			}
			// sort.
			var servicesParameters = this.mapIdServiceToServiceParameters[service.id].servicesParameters;
			/*var merged = false;
			for(var i = 0; i < servicesParameters.length; i++){
				var serviceParametersAux = servicesParameters[i];
				merged = merged || serviceParametersAux.mergeServiceParameters(serviceParameters);
			}
			if(!merged){
				if(!noChangeId){
					serviceParameters.id = this.getNextId();
				}
				servicesParameters.push(serviceParameters);
			}
			*/
			servicesParameters.push(serviceParameters);
		}

		/**
		 * Replace value for a serviceParameters return true if replaced
		 */
		Basket.prototype.replaceServiceParameters = function(serviceParameters, force){
			var replaced = this.removeServiceParametersById(serviceParameters);
			if(replaced || force){
				var service = serviceParameters.service;
				if(this.mapIdServiceToServiceParameters[service.id] == null) {
					this.mapIdServiceToServiceParameters[service.id] = {
						service:service,
						servicesParameters: []
					};
				}
				// sort.
				var servicesParameters = this.mapIdServiceToServiceParameters[service.id].servicesParameters;
				servicesParameters.push(serviceParameters);
			}
			return replaced;
		}

		Basket.prototype.getServiceParameters = function(idService, startDate, endDate)	{
			if(this.mapIdServiceToServiceParameters[idService] != null){
				var servicesParameters = this.mapIdServiceToServiceParameters[idService].servicesParameters;
				for(var i = 0; i < servicesParameters.length; i++){
					var serviceParametersAux = servicesParameters[i];
					if(serviceParametersAux.isSameDatesInParams(startDate, endDate)){
						return serviceParametersAux;
					}
				}
			}
			return null;
		}

		Basket.prototype.getServiceParametersLink = function(serviceParameters)	{
			var idService = serviceParameters.service.id;
			if(this.mapIdServiceToServiceParameters[idService] != null){
				var servicesParameters = this.mapIdServiceToServiceParameters[idService].servicesParameters;
				for(var i = 0; i < servicesParameters.length; i++){
					var serviceParametersAux = servicesParameters[i];
					if(serviceParametersAux.id == serviceParameters.idServiceParametersLink){
						return serviceParametersAux;
					}
				}
			}
			return null;
		}

		Basket.prototype.getAllServiceParametersLink = function(serviceParameters)	{
			var allServicesParameters = [];
			var idService = serviceParameters.service.id;
			if(this.mapIdServiceToServiceParameters[idService] != null){
				var servicesParameters = this.mapIdServiceToServiceParameters[idService].servicesParameters;
				for(var i = 0; i < servicesParameters.length; i++){
					var serviceParametersAux = servicesParameters[i];
					if(serviceParametersAux.idServiceParametersLink == serviceParameters.idServiceParametersLink){
						allServicesParameters.push(serviceParametersAux);
					}
				}
			}
			return allServicesParameters;
		}


		Basket.prototype.clear = function(){
			this.mapIdServiceToServiceParameters = [];
		}

		/**
		 * remove service parameters
		 */
		Basket.prototype.removeServiceParameters = function(serviceParameters, index){
			if(serviceParameters.service != null && this.mapIdServiceToServiceParameters[serviceParameters.service.id]!=null){
				var servicesParameters = this.mapIdServiceToServiceParameters[serviceParameters.service.id].servicesParameters;
				servicesParameters.splice(index, 1);
				if(this.mapIdServiceToServiceParameters[serviceParameters.service.id].servicesParameters.length <= 0){
					this.mapIdServiceToServiceParameters.splice(serviceParameters.service.id);
				}
			}
		}

		/**
		 * remove service parameters
		 */
		Basket.prototype.removeServiceParametersById = function(serviceParameters){
			var removed = false;
			this.mapIdServiceToServiceParameters.forEach(function(mapIdServiceToServiceParametersElement){
				if(mapIdServiceToServiceParametersElement!=null){
					var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
					var index = 0;
					var found = false;
					while(!found && index < servicesParameters.length){
						if(servicesParameters[index].id == serviceParameters.id){
							found = true;
						}
						else index++;
					}
					if(found){
						servicesParameters.splice(index, 1);
						removed = true;
					}
					if(mapIdServiceToServiceParametersElement.servicesParameters.length <= 0){
						mapIdServiceToServiceParametersElement = null;
					}
				}
			});
			return removed;
		}

		/**
		 * return the number person selected for same timeslot
		 */
		Basket.prototype.getNumberTimeslot = function(serviceParameters, index){
			if(index == null)
				index = -1;
			var number = 0;
			if(serviceParameters.service != null && this.mapIdServiceToServiceParameters[serviceParameters.service.id]!=null){
				var servicesParameters = this.mapIdServiceToServiceParameters[serviceParameters.service.id].servicesParameters;
				if(index == -1)
					index = servicesParameters.length;
				for(var i = 0; i < index; i++){
					var localServicesParameters = servicesParameters[i];
					if(serviceParameters.dateStart.getTime() == localServicesParameters.dateStart.getTime() &&
							serviceParameters.dateEnd.getTime() == localServicesParameters.dateEnd.getTime()){
						number = localServicesParameters.getNumber();
					}
				}
			}
			return number;
		}


		/**
		 * return the number person selected for same timeslot and same price
		 */
		Basket.prototype.getNumberTimeslotWithSamePrice = function(serviceParameters, numberPrice){
			var number = 0;
			if(serviceParameters.service != null &&  numberPrice.price != null && this.mapIdServiceToServiceParameters[serviceParameters.service.id]!=null){
				var servicesParameters = this.mapIdServiceToServiceParameters[serviceParameters.service.id].servicesParameters;
				for(var i = 0; i < servicesParameters.length; i++){
					var localServicesParameters = servicesParameters[i];
					if(serviceParameters.dateStart.getTime() == localServicesParameters.dateStart.getTime() &&
							serviceParameters.dateEnd.getTime() == localServicesParameters.dateEnd.getTime() &&
							serviceParameters.id != localServicesParameters.id){
						for(var j = 0; j < localServicesParameters.numberPrices.length; j++){
							var localNumberPrice = localServicesParameters.numberPrices[j];
							if(localNumberPrice.price!=null && localNumberPrice.price.id == numberPrice.price.id){
								number += localNumberPrice.number;
							}
						}
					}
				}
			}
			return number;
		}

		/**
		 * get max
		 */
		Basket.prototype.getMax = function(serviceParameters, index){
			return serviceParameters.maxCapacity; // (serviceParameters.maxCapacity - this.getNumberTimeslot(serviceParameters, index));
		}

		/**
		 * return the max price of serviceParameters
		 */
		Basket.prototype.getSubTotalPrice = function(servicesParameters, reductions, mapIdClientObjectReduction, advancePayment, typeAccount){
			var totalNumber = 0;
			for(var i = 0; i < servicesParameters.length; i++){
				var number = 0;
				var serviceParameters = servicesParameters[i];
				if(!serviceParameters.isCancelled()){
					for(var j = 0; j < serviceParameters.numberPrices.length; j++){
						var numberPrice = serviceParameters.numberPrices[j];
						if(serviceParameters.canCalculatePrice(numberPrice)){
							var localNumber = serviceParameters.getPriceNumberPrice(numberPrice);
							if(mapIdClientObjectReduction['id'+serviceParameters.id] != null){
								var allParams = mapIdClientObjectReduction['id'+serviceParameters.id];
								var reductionNumber = 0;
								var reductionPurcent = 0;
								for(var k = 0; k < allParams.length; k++){
									var params = allParams[k];
									if(params.idsPrice.length > 0 && params.idsPrice.indexOf(numberPrice.price.id + '') != -1){
										var reduction = getReductionById(params.idReduction, reductions);
										if(reduction!=null){
											if(params.type == 0){
												if(params.value < 0) reductionNumber -= (params.value * params.number);
												else reductionNumber += (params.value * params.number);
											}
											else if(params.type == 1){
												reductionPurcent += params.value;
											}
											else if(params.type == 2){
												if(params.value < 0) reductionNumber -= (params.value * params.number);
												else reductionNumber += (params.value * params.number);
											}
											else if(params.type == 3){
												localNumber = numberPrice.price.price * (numberPrice.number - params.number);
												localNumber += params.value * params.number;
											}
											else if(params.type == 4){
												var price = serviceParameters.getPriceNumberPrice(numberPrice) / numberPrice.number;
												if(params.value < 0) reductionNumber += Number(params.value * params.number * price);
												else reductionNumber +=   Number(params.value * params.number * price);
											}
										}
									}
								}
								localNumber -= localNumber * reductionPurcent / 100;
								localNumber -= reductionNumber;
							}
							if(advancePayment){
								var advancePaymentByAccountType = serviceParameters.service.advancePaymentByAccountTypes.find(function(element) {
								  return typeAccount.id == element.typeAccount;
								});
								var advancePayment = serviceParameters.service.advancePayment;
								if(advancePaymentByAccountType != null){
									advancePayment = advancePaymentByAccountType.advancePayment;
								}
								localNumber = localNumber - (localNumber * (100 - advancePayment)/100);
							}
							number += localNumber;
						}
					}
				}
				if(mapIdClientObjectReduction['id'+serviceParameters.id] != null){
					var allParams = mapIdClientObjectReduction['id'+serviceParameters.id];
					var reductionNumber = 0;
					var reductionPurcent = 0;
					for(var k = 0; k < allParams.length; k++){
						var params = allParams[k];
						if(params.idsPrice.length == 0){
							var reduction = getReductionById(params.idReduction, reductions);
							if(reduction!=null){
								if(params.type == 0){
									if(params.value < 0) reductionNumber -= (params.value * params.number);
									else reductionNumber +=  (params.value * params.number);
								}
								else if(params.type == 1){
									reductionPurcent +=  params.value;
								}
								else if(params.type == 2){
									if(params.value < 0) reductionNumber -= params.value * params.number;
									else reductionNumber += params.value * params.number;
								}
							}
						}
					}
					number -= number * reductionPurcent / 100;
					number -= reductionNumber;
				}
				totalNumber += number;
			}
			return totalNumber;
		}

		/**
		 * return the total of number of persons
		 */
		Basket.prototype.getTotalNumber = function(){
			var number = 0;
			this.mapIdServiceToServiceParameters.forEach(function(mapIdServiceToServiceParametersElement){
				var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
				for(var i = 0; i < servicesParameters.length; i++){
					number += servicesParameters[i].getNumber();
				}
			});
			return number;
		}

		/**
		 * return total price.
		 */
		Basket.prototype.getTotalPrice = function(reductions, mapIdClientObjectReduction, advancePayment, typeAccount){
			var number = 0;
			this.mapIdServiceToServiceParameters.forEach(function(mapIdServiceToServiceParametersElement){
				if(mapIdServiceToServiceParametersElement!=null){
					var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
					number += _self.getSubTotalPrice(servicesParameters, reductions, mapIdClientObjectReduction, advancePayment, typeAccount);
				}
			});
			var reductionNumber = 0;
			var reductionPurcent = 0;
			if(mapIdClientObjectReduction['id0'] != null){
				var allParams = mapIdClientObjectReduction['id0'];
				for(var i = 0; i < allParams.length; i++){
					var params = allParams[i];
					var reduction = getReductionById(params.idReduction, reductions);
					if(reduction!=null){
						if(params.type == 0){
							if(params.value < 0) reductionNumber -= (params.value * 1) * params.number;
							else reductionNumber += (params.value * 1) * params.number;
						}else if(params.type == 1){
							reductionPurcent += (params.value * 1) * params.number;
						}else if(params.type == 2){
							if(params.value < 0) reductionNumber -= params.value * 1 * params.number;
							else reductionNumber += params.value * 1 * params.number;
						}
					}
				}
			}
			number -= number * reductionPurcent / 100;
			number -= reductionNumber;
			return number;
		}


		/**
		 * Format to real array
		 */
		Basket.prototype.getArray = function(){
			var newMapIdServiceToServiceParameters = [];
			this.mapIdServiceToServiceParameters.forEach(function(mapIdServiceToServiceParametersElement){
				if(mapIdServiceToServiceParametersElement!=null)
					newMapIdServiceToServiceParameters.push(mapIdServiceToServiceParametersElement);
			});
			return newMapIdServiceToServiceParameters;
		}


		/**
		 * return all services parameters to concat them
		 */
		Basket.prototype.getAllServicesParameters = function(){
			var allServicesParameters = [];
			this.mapIdServiceToServiceParameters.forEach(function(mapIdServiceToServiceParametersElement){
				if(mapIdServiceToServiceParametersElement!=null){
					var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
					allServicesParameters = allServicesParameters.concat(servicesParameters);
				}
			});
			return allServicesParameters;
		}

		/**
		 * return all services parameters to concat them
		 */
		Basket.prototype.getAllServicesParametersByIdLink = function(idLink){
			var allServicesParameters = [];
			this.mapIdServiceToServiceParameters.forEach(function(mapIdServiceToServiceParametersElement){
				if(mapIdServiceToServiceParametersElement!=null){
					var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
					for(var i = 0; i < servicesParameters.length; i++){
						var serviceParameters = servicesParameters[i];
						if(serviceParameters.idServiceParametersLink == idLink){
							allServicesParameters.push(serviceParameters);
						}
					}
				}
			});
			return allServicesParameters;
		}

		Basket.prototype.serviceParametersIsOk = function(serviceParameters, index, allowInconsistencies){
			var ok = true;
			var haveOnlyExtra = true;
			if(serviceParameters!=null){
				ok = ok && serviceParameters.service!=null;
				ok = ok && (serviceParameters.haveAllMandatoryPrices() || allowInconsistencies);
				if(serviceParameters.numberPrices.length > 0){
					for(var j = 0; j < serviceParameters.numberPrices.length; j++){
						var numberPrice = serviceParameters.numberPrices[j];
						if(numberPrice.price!=null && !numberPrice.price.extra){ haveOnlyExtra = false; }
						ok = ok && numberPrice.price!=null;
						ok = ok && (numberPrice.number > 0 || serviceParameters.getNumber() > 0)
						ok = ok && (numberPrice.number <= this.getMax(serviceParameters, index) || allowInconsistencies);
					}
				}else ok = false;
			}else ok = false;
			return ok && !haveOnlyExtra;
		}

		Basket.prototype.basketIsOk = function(allowInconsistencies){
			var ok = this.getAllServicesParameters().length > 0;
			this.mapIdServiceToServiceParameters.forEach(function(mapIdServiceToServiceParametersElement, index){
				if(mapIdServiceToServiceParametersElement!=null){
					var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
					for(var i = 0; i < servicesParameters.length; i++){
						var serviceParameters = servicesParameters[i];
						ok = ok && _self.serviceParametersIsOk(serviceParameters, i, allowInconsistencies);
					}
				}
			});
			return ok;
		}

		Basket.prototype.getParticipants = function(serviceParametersParams){
			var allServicesParameters = this.getAllServicesParameters();
			var allParticipants = [];
			for(var i = 0; i < allServicesParameters.length; i++){
				var serviceParameters = allServicesParameters[i];
				if(serviceParametersParams == null || serviceParametersParams.id != serviceParameters.id){
					var participants = serviceParameters.getParticipants();
					for(var k = 0; k < participants.length; k++){
						var participant = participants[k];
						if(allParticipants.indexOf(participant) == -1){
							allParticipants.push(participant);
						}
					}
				}
			}
			return allParticipants;
		}

		/**
		 * set all states
		 */
		Basket.prototype.setAllStates = function(state){
			this.mapIdServiceToServiceParameters.forEach(function(mapIdServiceToServiceParametersElement){
				if(mapIdServiceToServiceParametersElement!=null){
					var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
					for(var i = 0; i < servicesParameters.length; i++){
						var serviceParameters = servicesParameters[i];
						serviceParameters.state = state;
					}
				}
			});
		}

		return Basket;
	}

	angular.module('resa_app').factory('Basket', BasketFactory);
}());
