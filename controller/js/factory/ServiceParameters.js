"use strict";

(function(){

	var getParticipantsParameter = function(form_participants_parameters, idParameter){
		if(form_participants_parameters != null){
			for(var i = 0; i < form_participants_parameters.length; i++){
				if(form_participants_parameters[i].id == idParameter){
					return form_participants_parameters[i];
				}
			}
		}
		return null;
	}

	var clearTime = function(date){
		date.setHours(0);
		date.setMinutes(0);
		date.setSeconds(0);
		date.setMilliseconds(0);
		return date;
	}

	function ServiceParametersFactory($filter){

		var ServiceParameters = function(){
			this.id = 1;
			this.idAppointment = -1;
			this.update = false;
			this.place = '';
			this.service = null;
			this.dateStart = new Date();
			this.dateEnd = new Date();
			this.staffs = [];
			this.numberPrices = [];
			this.maxCapacity = -1;
			this.membersUsed = [];
			this.state = 'ok';
			this.noEnd = false;
			this.capacityTimeslot = 0;
			this.customTimeslot = false;
			this.membersUsedWithMembers = [];
			this.idsServicePrices = [];
			this.days = 0;
			this.manyDates = [];
			this.idServiceParametersLink = -1;
			this.idParameter = -1;
			this.tags = [];
			this.equipments = [];
			this.equipmentsActivated = false;
			this.addNumberPrice();
		}

		ServiceParameters.prototype.fromServiceParametersJSON = function(serviceParametersJSON, notCapacity){
			if(notCapacity == null)
				notCapacity = false;
			serviceParametersJSON = JSON.parse(JSON.stringify(serviceParametersJSON));
			this.id = serviceParametersJSON.id;
			this.idAppointment = serviceParametersJSON.idAppointment;
			this.update = serviceParametersJSON.update;
			this.place = serviceParametersJSON.place;
			this.service = serviceParametersJSON.service;
			this.dateStart = $filter('parseDate')(serviceParametersJSON.dateStart);
			this.dateEnd = $filter('parseDate')(serviceParametersJSON.dateEnd);
			this.staffs = serviceParametersJSON.staffs;
			if(!notCapacity){
				this.maxCapacity = serviceParametersJSON.maxCapacity;
			}
			this.numberPrices = serviceParametersJSON.numberPrices;
			this.membersUsed = serviceParametersJSON.membersUsed;
			this.state = serviceParametersJSON.state;
			this.noEnd = serviceParametersJSON.noEnd;
      this.capacityTimeslot = serviceParametersJSON.capacityTimeslot;
			this.customTimeslot = serviceParametersJSON.customTimeslot;
			this.days = serviceParametersJSON.days;
			this.manyDates = serviceParametersJSON.manyDates;
			this.idServiceParametersLink = serviceParametersJSON.idServiceParametersLink;
			this.idParameter = serviceParametersJSON.idParameter;
			this.tags = JSON.parse(JSON.stringify(serviceParametersJSON.tags));
			if(serviceParametersJSON.equipments != null) this.equipments = JSON.parse(JSON.stringify(serviceParametersJSON.equipments));
			if(serviceParametersJSON.equipmentsActivated != null) this.equipmentsActivated = serviceParametersJSON.equipmentsActivated;
		}

		ServiceParameters.prototype.setService = function(service){
			this.service = service;
			if(this.service.servicePrices.length == 1){
				this.setPriceForNumberPrice(this.service.servicePrices[0]);
			}
		}

		ServiceParameters.prototype.setPriceForNumberPrice = function(price){
			if( this.numberPrices.length > 0 && price!=null){
				this.numberPrices[0].price = price;
			}
		}

		/**
		 * deprecated service parameters
		 * return true if the prices of service not same in ServiceParameters
		 * needed when load old basket
		 */
		ServiceParameters.prototype.deprecatedServiceParameters = function(service){
			if(service==null || service.id != this.service.id) return true;
			else {
				for(var j = 0; j < this.numberPrices.length; j++){
					var price = this.numberPrices[j].price;
					var find = false;
					var i = 0;
					while(!find && i < service.servicePrices.length){
						var priceService = service.servicePrices[i];
						if(price != null &&
							priceService.id == price.id &&
							priceService.price == price.price &&
							priceService.activated){
							find = true;
						}
						i++;
					}
					if(!find){
						return true;
					}
				}
			}
			return false;
		}

		ServiceParameters.prototype.isDatesSet = function(){
			return this.maxCapacity != -1;
		}

		ServiceParameters.prototype.clearDates = function(){
			this.dateStart = new Date();
			this.dateEnd = new Date();
			this.maxCapacity = -1;
		}

		ServiceParameters.prototype.setTimes = function(startDate, endDate){
			this.dateStart.setHours(startDate.getHours());
			this.dateStart.setMinutes(startDate.getMinutes());
			this.dateStart.setSeconds(startDate.getSeconds());
			this.dateEnd.setHours(endDate.getHours());
			this.dateEnd.setMinutes(endDate.getMinutes());
			this.dateEnd.setSeconds(endDate.getSeconds());
	  }

		ServiceParameters.prototype.setDate = function(startDate, endDate){
			this.dateStart.setFullYear(startDate.getFullYear());
			this.dateStart.setMonth(startDate.getMonth());
			this.dateStart.setDate(startDate.getDate());
			this.dateEnd.setFullYear(endDate.getFullYear());
			this.dateEnd.setMonth(endDate.getMonth());
			this.dateEnd.setDate(endDate.getDate());
	  }

		ServiceParameters.prototype.setTwoDates = function(years, month, day){
			this.dateStart.setFullYear(years);
			this.dateStart.setMonth(month);
			this.dateStart.setDate(day);
			this.dateEnd.setFullYear(years);
			this.dateEnd.setMonth(month);
			this.dateEnd.setDate(day);
	  }

		ServiceParameters.prototype.isSameDatesInParams = function(startDate, endDate){
			return this.dateStart.getTime() == startDate.getTime() &&
				this.dateEnd.getTime() == endDate.getTime();
		}

		ServiceParameters.prototype.isSameDates = function(serviceParameters){
			return this.dateStart.getTime() == serviceParameters.dateStart.getTime() &&
				this.dateEnd.getTime() == serviceParameters.dateEnd.getTime();
		}

		ServiceParameters.prototype.isNoEnd = function(){
			return this.noEnd;
		}

		ServiceParameters.prototype.getNumberDays = function(){
			if(this.service.typeAvailabilities == 2) return this.manyDates.length;
			return this.days + 1;
		}

		ServiceParameters.prototype.addDaysToDate = function(days){
			this.dateStart.setDate(this.dateStart.getDate() + days);
			this.dateEnd.setDate(this.dateEnd.getDate() + days);
		}


		ServiceParameters.prototype.getEndDateDay = function(){
			var dateEnd = new Date(this.dateStart);
			dateEnd.setDate(dateEnd.getDate() + this.days);
			return dateEnd;
		}

		ServiceParameters.prototype.isLink = function(){
			return this.id != this.idServiceParametersLink;
		}

		ServiceParameters.prototype.isCombined = function(){
			return this.service.typeAvailabilities == 1 || this.service.typeAvailabilities == 2;
		}

		ServiceParameters.prototype.isWeek = function(){
			return this.service.typeAvailabilities == 1;
		}

		ServiceParameters.prototype.mergeServiceParameters = function(serviceParameters){
			var merge = false;
			if(this.isSameDates(serviceParameters)){
				merge = true;
				for(var i = 0; i < serviceParameters.numberPrices.length; i++){
					var anotherNumberPrice = serviceParameters.numberPrices[i];
					var found = false;
					for(var j = 0; j < this.numberPrices.length; j++){
						var numberPrice = this.numberPrices[j];
						if(anotherNumberPrice.price.id == numberPrice.price.id){
							found = true;
							numberPrice.number += anotherNumberPrice.number;
							numberPrice.participants = numberPrice.participants.concat(anotherNumberPrice.participants);
						}
					}
					if(!found){
						 this.numberPrices.push(JSON.parse(JSON.stringify(anotherNumberPrice)));
					}
				}
				for(var i = 0; i < serviceParameters.staffs.length; i++){
					var member = serviceParameters.staffs[i];
					if(!this.memberIsInStaffs(member, this.staffs)){
						this.staffs.push(member);
					}
				}
			}
			return merge;
		}

		ServiceParameters.prototype.updateServicesParameters = function(serviceParameters){
			if(this.id == serviceParameters.id || this.id == serviceParameters.idServiceParametersLink || this.idServiceParametersLink == serviceParameters.idServiceParametersLink){
				for(var i = 0; i < this.numberPrices.length; i++){
					var numberPrice = this.numberPrices[i];
					var anotherNumberPrice = serviceParameters.getNumberPriceByIdPrice(numberPrice.price.id);
					if(anotherNumberPrice == null) {
						numberPrice.number = 0;
						numberPrice.participants = [];
					}
					else {
						numberPrice.number = anotherNumberPrice.number;
						if(anotherNumberPrice.participants != null){
							numberPrice.participants = JSON.parse(JSON.stringify(anotherNumberPrice.participants));
						}
						else {
							numberPrice.participants = [];
						}
					}
				}
			}
		}

		ServiceParameters.prototype.emptyNumberPrice = function(){
			return this.numberPrices.length == 0 || (this.numberPrices.length == 1 && this.numberPrices[0].price == null);
		}

		ServiceParameters.prototype.addNumberPrice = function(){
			this.numberPrices.push({price:null, number:0, participants:[]});
		}

		ServiceParameters.prototype.pushInNumberPrice = function(price, number, participants){
			if(participants == null) participants = [];
			this.numberPrices.push({price:price, number:number, participants:participants});
		}

		ServiceParameters.prototype.isErrorNumberPriceMaxMin = function(){
			for(var i = 0; i < this.numberPrices.length; i++){
				var numberPrice =this.numberPrices[i];
				var price = numberPrice.price;
				if(price.activateMaxQuantity && numberPrice.number > price.maxQuantity) return true;
				if(price.activateMinQuantity && numberPrice.number < price.minQuantity) return true;
			}
			return false;
		}

		ServiceParameters.prototype.clearNumberPriceNumber = function(){
			for(var i = 0; i < this.numberPrices.length; i++){
				this.numberPrices[i].number = 0;
			}
		}

		ServiceParameters.prototype.haveAllMandatoryPrices = function(){
			for(var i = 0; i < this.service.servicePrices.length; i++){
				var price = this.service.servicePrices[i];
				var numberPrice = this.getNumberPriceByIdPrice(price.id);
				if(price.mandatory && (numberPrice == null || numberPrice.number == 0)){
					return false;
				}
			}
			return true;
		}

		ServiceParameters.prototype.removeParticipant = function(numberPrice, $index){
			var localNumberPrice = this.getNumberPriceByIdPrice(numberPrice.price.id);
			localNumberPrice.participants.splice($index, 1);
			localNumberPrice.number--;
		}

		ServiceParameters.prototype.addNumberPriceForEachServicePrice = function(timeslot){
			if(this.service != null){
				var newNumberPrices = [];
				for(var i = 0; i < this.service.servicePrices.length; i++){
					var price = this.service.servicePrices[i];
					if(timeslot==null || timeslot.idsServicePrices==null || timeslot.idsServicePrices.length == 0 || timeslot.idsServicePrices.indexOf(price.id) != -1){
						var number = 0;
						var participants = [];
						var oldNumberPrice = this.getNumberPriceByIdPrice(price.id);
						if(oldNumberPrice!=null) {
							number = oldNumberPrice.number;
							participants = oldNumberPrice.participants;
						}
						newNumberPrices.push({price:price, number:number, participants: participants});
					}
				}
				this.numberPrices = newNumberPrices;
			}
		}

		ServiceParameters.prototype.completeNumberPriceForEachServicePrice = function(){
			if(this.service != null){
				var newNumberPrices = [];
				for(var i = 0; i < this.service.servicePrices.length; i++){
					var price = this.service.servicePrices[i];
					var found = false;
					for(var j = 0; j < this.numberPrices.length; j++){
						if(price.id == this.numberPrices[j].price.id || price.slug == this.numberPrices[j].price.slug){
							found = true;
							this.numberPrices[j].price = price;
							newNumberPrices.push(this.numberPrices[j]);
						}
					}
					if(!found){
						newNumberPrices.push({price:price, number:0});
					}
				}
				this.numberPrices = newNumberPrices;
			}
		}

		ServiceParameters.prototype.getFirstPrice = function(){
			if( this.numberPrices.length > 0)
				return this.numberPrices[0].price;
			else return null;
		}

		ServiceParameters.prototype.resetDates = function(){
			this.dateStart = new Date();
			this.dateEnd = new Date();
		}

		ServiceParameters.prototype.getNumberPriceByIdPrice = function(idPrice){
			for(var i = 0; i < this.numberPrices.length; i++){
				var numberPrice = this.numberPrices[i];
				if(numberPrice.price != null && numberPrice.price.id == idPrice){
					return numberPrice;
				}
			}
			return null;
		}

		ServiceParameters.prototype.getIndexNumberPriceByIdPrice = function(idPrice){
			for(var i = 0; i < this.numberPrices.length; i++){
				var numberPrice = this.numberPrices[i];
				if(numberPrice.price != null && numberPrice.price.id == idPrice){
					return i;
				}
			}
			return -1;
		}

		ServiceParameters.prototype.getNumberPriceBySlug = function(slug){
			for(var i = 0; i < this.numberPrices.length; i++){
				var numberPrice = this.numberPrices[i];
				if(numberPrice.price != null && numberPrice.price.slug == slug){
					return numberPrice;
				}
			}
			return null;
		}

		ServiceParameters.prototype.resetFirstNumber = function()	{
			if( this.numberPrices.length > 0)
				return this.numberPrices[0].number = 0;
		}

		ServiceParameters.prototype.getFirstNumberPrice = function(){
			if(this.numberPrices.length > 0)
				return this.numberPrices[0];
			else return null;
		}

		ServiceParameters.prototype.removeNumberPrice = function(index){
			var numberPrice = this.numberPrices[index];
			if(numberPrice.price.mandatory){
				this.numberPrices = [];
			}
			else {
				this.numberPrices.splice(index, 1);
			}
			if(this.numberPrices.length == 0){
				this.maxCapacity = -1;
				this.addNumberPrice();
				return true;
			}
			else if(this.numberPrices.length == 1){
				return this.numberPrices[0].price == null ||
					this.isDefaultSlugServicePrice(this.numberPrices[0]);				
			}
			return false;
		}

		ServiceParameters.prototype.addStaff = function(){
			this.staffs.push(0);
		}

		ServiceParameters.prototype.removeStaff = function(index){
			this.staffs.splice(index, 1);
		}


		ServiceParameters.prototype.assignStaff = function(staff){
			var currentStaff = this.getStaff(staff.id);
			if(currentStaff == null){
				if(this.needAssignNumber()){
					this.staffs.push(staff);
				}
			}
			else {
				var newStaffs = [];
				for(var i = 0; i < this.staffs.length; i++){
					if(this.staffs[i].id != staff.id){
						newStaffs.push(this.staffs[i]);
					}
				}
				this.staffs = newStaffs;
			}
		}

		ServiceParameters.prototype.displayMemberUsed = function(memberUsed, capacity_word, off_schedule_word){
			var result = memberUsed.nickname + ' - ' + capacity_word + ' : ' + (memberUsed.capacity - memberUsed.usedCapacity);
			if(memberUsed.capacity == 0){
				result = memberUsed.nickname + ' - ' + off_schedule_word;
			}
			return result;
		}

		ServiceParameters.prototype.getMemberUsed = function(index){
			var staffsSlice = this.staffs.slice(0, index);
			var membersUsedReturn = [];
			for(var i = 0; i < this.membersUsed.length; i++){
				var memberUsed = this.membersUsed[i];
				if(!this.memberIsInStaffs(memberUsed,staffsSlice)){
					membersUsedReturn.push(memberUsed);
				}
			}
			return membersUsedReturn;
		}

		ServiceParameters.prototype.mergeMembersUsedWithMembers = function(members){
			var membersUsedReturn = [];
			var nicknameMembersUsed = [];
			for(var i = 0; i < this.membersUsed.length; i++){
				var memberUsed = this.membersUsed[i];
				nicknameMembersUsed.push(memberUsed.nickname);
				membersUsedReturn.push(memberUsed);
			}
			for(var i = 0; i < members.length; i++){
				var member = members[i];
				var memberUsed = {id: member.id, nickname:member.nickname, capacity: 0, usedCapacity: 0};
				if(nicknameMembersUsed.indexOf(member.nickname) == -1){
					membersUsedReturn.push(memberUsed);
				}
			}
			this.membersUsedWithMembers = membersUsedReturn;
		}


		ServiceParameters.prototype.getMemberUsedMembers = function(index){
			var staffsSlice = this.staffs.slice(0, index);
			var membersUsedReturn = [];
			for(var i = 0; i < this.membersUsedWithMembers.length; i++){
				var memberUsed = this.membersUsedWithMembers[i];
				if(!this.memberIsInStaffs(memberUsed,staffsSlice)){
					membersUsedReturn.push(memberUsed);
				}
			}
			return membersUsedReturn;
		}

		ServiceParameters.prototype.memberIsInStaffs = function(member, staffs){
			for(var i = 0; i < staffs.length; i++){
				if(member.id == staffs[i].id)
					return true;
			}
			return false;
		}

		ServiceParameters.prototype.displayStaffs = function(staffs){
			var display = '';
			for(var i = 0; i < this.staffs.length; i++){
				if(display != '') display += ', ';
				if(this.staffs[i].capacity == 0){
					display += '<span class="ro_rouge">'+this.staffs[i].nickname+'</span>';
				}
				else {
					display += this.staffs[i].nickname;
				}
			}
			if(display == ''){
				display = 'auto';
			}
			return display;
		}

		ServiceParameters.prototype.haveExtraStaff = function(){
			for(var i = 0; i < this.staffs.length; i++){
				if(this.staffs[i].capacity == 0){
					return true;
				}
			}

			return false;
		}

		ServiceParameters.prototype.getStaff = function(idStaff){
			for(var i = 0; i < this.staffs.length; i++){
				if(this.staffs[i].id == idStaff){
					return this.staffs[i];
				}
			}
			return null;
		}

		ServiceParameters.prototype.assignNumber = function(staff){
			if(staff!=null && staff.id !=null){
				var totalNumber = this.getNumber();
				for(var i = 0; i < this.staffs.length; i++){
					var localStaff = this.staffs[i];
					var capacity = localStaff.capacity;
					if(capacity == 0) capacity = 9999;
					var capacityNotUsed = capacity - localStaff.usedCapacity;
					var number = capacityNotUsed;
					if(number < 0) number = capacity;
					if(number > totalNumber) number = totalNumber;
					totalNumber-=number;
					if(localStaff.id == staff.id){
						return number;
					}
				}
			}
			return 0;
		}

		ServiceParameters.prototype.needAssignNumber = function(){
			var number = 0;
			for(var i = 0; i < this.staffs.length; i++){
				number += this.assignNumber(this.staffs[i]);
			}
			return this.getNumber() - number;
		}

		ServiceParameters.prototype.getNumber = function(){
			var number = 0;
			for(var i = 0; i < this.numberPrices.length; i++){
				var numberPrice = this.numberPrices[i];
				if(numberPrice.price!=null && !numberPrice.price.extra){
					number += numberPrice.number;
				}
			}
			return number;
		}

		ServiceParameters.prototype.getRepeatNumber = function(){
			var number =  this.getNumber();
			if(number==null || number<=0) return [];
			return (new Array(number));
		}

		ServiceParameters.prototype.getRepeatNumberByNumber = function(number){
			return (new Array(number));
		}


		ServiceParameters.prototype.getNumberWithoutIndex = function(index){
			var number = 0;
			for(var i = 0; i < this.numberPrices.length; i++){
				if(i != index){
					var numberPrice = this.numberPrices[i];
					if(!numberPrice.extra){
						number += numberPrice.number;
					}
				}
			}
			return number;
		}

		ServiceParameters.prototype.addNumberInNumberPrice = function(numberPrice, value){
			numberPrice.number += value;
		}

		ServiceParameters.prototype.getPriceNumberPrice = function(numberPrices){
			var priceModel = numberPrices.price;
			var total = 0;
			if(priceModel != null && numberPrices.number > 0){
				total = numberPrices.number * priceModel.price;
				if(!priceModel.notThresholded){
					total = 0;
					var hours = (this.dateEnd.getTime() - this.dateStart.getTime()) / (3600 * 1000);
					var found = false;
					for(var i = 0; i < priceModel.thresholdPrices.length; i++){
						var thresholdPrice = priceModel.thresholdPrices[i];
						if(thresholdPrice.min <= numberPrices.number && numberPrices.number <= thresholdPrice.max){
							found = true;
							total = Number(numberPrices.number * Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0)) + Number(thresholdPrice.price);
							if(thresholdPrice.byHours!=null && thresholdPrice.byHours){
								total *= hours;
							}
						}
					}
					if(!found){
						var thresholdPrice = priceModel.thresholdPrices[priceModel.thresholdPrices.length - 1];
						total = Number(thresholdPrice.max * Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0)) +
							Number(thresholdPrice.price) +
							Number(Number(thresholdPrice.supPerson!=null?thresholdPrice.supPerson:0) * (numberPrices.number - thresholdPrice.max));
						if(thresholdPrice.byHours!=null && thresholdPrice.byHours){
							total *= hours;
						}
					}
				}
			}
			return total;
		}

		ServiceParameters.prototype.getMinThresholdPrice = function(numberPrices){
			var priceModel = numberPrices.price;
			var price = 0;
			if(priceModel != null){
				numberPrices = JSON.parse(JSON.stringify(numberPrices));
				numberPrices.number = this.getMinNumberPrice(numberPrices);
				return this.getPriceNumberPrice(numberPrices);
			}
			return price;
		}

		ServiceParameters.prototype.getPrice = function(){
			var number = 0;
			for(var i = 0; i < this.numberPrices.length; i++){
				var numberPrices = this.numberPrices[i];
				if(numberPrices.price!=null){
					number += this.getPriceNumberPrice(numberPrices);
				}
			}
			return Math.round(number * 100) / 100;
		}

		ServiceParameters.prototype.getPriceWithDefaultSlugServicePrice = function(){
			var number = this.getPrice();
			//defaut slg
			if(this.service != null && this.service.defaultSlugServicePrice.length > 0){
				var numberPrice = this.getNumberPriceBySlug(this.service.defaultSlugServicePrice);
				if(numberPrice){
					number = this.getPriceNumberPrice(numberPrice);
				}
			}

			return Math.round(number * 100) / 100;
		}

		ServiceParameters.prototype.getMinNumberPrice = function(numberPrice){
			if(numberPrice.price != null && numberPrice.price.activateMinQuantity)
				return numberPrice.price.minQuantity;
			else return 1;
		}

		ServiceParameters.prototype.getMaxNumberPrice = function(numberPrice, totalMax){
			var max = 999;
			if(numberPrice.price != null && numberPrice.price.activateMaxQuantity)
				 max = numberPrice.price.maxQuantity;
			return Math.min(max, totalMax);
		}

		ServiceParameters.prototype.removeNumberPriceNull = function(){
			var find = true;
			while(find){
				var index = 0;
				find = false;
				while(index < this.numberPrices.length && !find){
					if(this.numberPrices[index].number <= 0){
						find = true;
					}
					else index++;
				}
				if(find){
					this.numberPrices.splice(index, 1);
				}
			}
		}

		/**
		 * return the participants
		 */
		ServiceParameters.prototype.getParticipants = function(){
			var participants = [];
			for(var i = 0; i < this.numberPrices.length; i++){
				var numberPrice = this.numberPrices[i];
				if(numberPrice!=null && numberPrice.participants != null){
					for(var j = 0; j < numberPrice.participants.length; j++){
						var participant = numberPrice.participants[j];
						if(participant != null){
							participants.push(participant);
						}
					}
				}
			}
			return participants;
		}

		ServiceParameters.prototype.needSelectParticipants = function(){
			if(this.service!=null && this.service.askParticipants){
				for(var index = 0; index < this.numberPrices.length; index++){
					var numberPrice = this.numberPrices[index];
					if(numberPrice!=null && numberPrice.price !=null && numberPrice.price.participantsParameter != null && numberPrice.price.participantsParameter.length > 0){
						return true;
					}
				}
			}
			return false;
		}


		ServiceParameters.prototype.participantsIsSelected = function(form_participants_parameters){
			if(this.needSelectParticipants()){
				for(var index = 0; index < this.numberPrices.length; index++){
					var numberPrice = this.numberPrices[index];
					if(numberPrice.price.participantsParameter == null || numberPrice.price.participantsParameter.length == 0) return true;
					if(numberPrice.participants == null || numberPrice.participants.length < numberPrice.number) return false;
					for(var i = 0; i < numberPrice.participants.length; i++){
						var participant = numberPrice.participants[i];
						if(participant != null){
							var participantsParameter = getParticipantsParameter(form_participants_parameters, numberPrice.price.participantsParameter);
							if(participantsParameter != null){
								var params = participantsParameter.fields;
								for(var key in params){
									var param = params[key];
									var value = participant[param.varname];
									if(param.mandatory && (value == null || value.length <= 0 || value<=0)){
										return false;
									}
								}
							}
							else return false;
						}
					}
				}
			}
			return true;
		}

		ServiceParameters.prototype.isOk = function(){
			return this.state == 'ok';
		}

		ServiceParameters.prototype.isCancelled = function(){
			return this.state == 'cancelled';
		}

		ServiceParameters.prototype.isAbandonned = function(){
			return this.state == 'abandonned';
		}

		ServiceParameters.prototype.isWaiting = function(){
			return this.state == 'waiting';
		}

		ServiceParameters.prototype.ok = function(){
			this.state = 'ok';
		}

		ServiceParameters.prototype.cancelled = function(){
			this.state = 'cancelled';
		}

		ServiceParameters.prototype.abandonned = function(){
			this.state = 'abandonned';
		}

		ServiceParameters.prototype.waiting = function(){
			this.state = 'waiting';
		}

		ServiceParameters.prototype.putOk = function(){
			return this.state = 'ok';
		}

		ServiceParameters.prototype.haveTag = function(idTag){
			return this.tags.indexOf(idTag) != -1;
		}

		ServiceParameters.prototype.switchTag = function(idTag){
			if(!this.haveTag(idTag)) this.tags.push(idTag);
			else {
				var index = this.tags.indexOf(idTag);
				this.tags.splice(index, 1);
			}
		}

		ServiceParameters.prototype.needDefaultSlugServicePrice = function(){
			if(this.service.defaultSlugServicePrice.length > 0){
				return true;
			}
			return false;
		}

		ServiceParameters.prototype.haveDefaultSlugServicePrice = function(){
			if(this.service!=null && this.service.defaultSlugServicePrice != null && this.service.defaultSlugServicePrice.length > 0){
				return this.getNumberPriceBySlug(this.service.defaultSlugServicePrice) != null;
			}
			return false;
		}

		ServiceParameters.prototype.isDefaultSlugServicePrice = function(numberPrice){
			if(this.service!=null && this.service.defaultSlugServicePrice != null && this.service.defaultSlugServicePrice.length > 0 && numberPrice.price!=null && numberPrice.price.slug == this.service.defaultSlugServicePrice){
				return true;
			}
			return false;
		}

		ServiceParameters.prototype.canCalculatePrice = function(numberPrice){
			return !this.haveDefaultSlugServicePrice() || this.isDefaultSlugServicePrice(numberPrice);
		}

		ServiceParameters.prototype.getServicePriceBySlug = function(slug){
			for(var i = 0; i < this.service.servicePrices.length; i++){
				var servicePrice = this.service.servicePrices[i];
				if(servicePrice.slug == slug){
					return servicePrice;
				}
			}
			return null;
		}

		ServiceParameters.prototype.applyDefaultSlugServicePrice = function(){
			if(this.needDefaultSlugServicePrice() && this.haveAllMandatoryPrices()){
				var servicePrice =  this.getServicePriceBySlug(this.service.defaultSlugServicePrice);
				var numberPrice = this.getNumberPriceBySlug(this.service.defaultSlugServicePrice);
				if(numberPrice){
					this.removeNumberPrice(this.getIndexNumberPriceByIdPrice(numberPrice.price.id));
				}
				if(this.getPrice() > 1 && this.getPrice() < servicePrice.price){
					this.pushInNumberPrice(servicePrice, 1);
				}
			}
		}
		/**
		 *
		 */
		ServiceParameters.prototype.getEquipmentsUse = function(equipment, index){
			var numberUse = 0;
			for(var i = 0; i < this.numberPrices.length; i++){
				if(index != i){
					var numberPrice = this.numberPrices[i];
					var price = numberPrice.price;
					var idEquipment = price.equipments[0];
					if(equipment.idEquipment == idEquipment){
						numberUse += numberPrice.number;
					}
				}
			}
			return numberUse;
		}

		/**
		 *
		 */
		ServiceParameters.prototype.getMaxEquipmentsCanChoose = function(index){
			var numberPrice = this.numberPrices[index];
			var price = numberPrice.price;
			if(price != null){
				var idEquipment = price.equipments[0];
				for(var i = 0; i < this.equipments.length; i++){
					var equipment = this.equipments[i];
					if(equipment.idEquipment == idEquipment){
						return equipment.max - (equipment.number + this.getEquipmentsUse(equipment, index));
					}
				}
			}
			return 9999;
		}

		/**
		 *
		 */
		ServiceParameters.prototype.isOkEquipmentsChoose = function(){
			for(var i = 0; i < this.numberPrices.length; i++){
				var numberPrice = this.numberPrices[i];
				var numberCanChooose = this.getMaxEquipmentsCanChoose(i);
				if(numberPrice.number > numberCanChooose){
					return false;
				}
			}
			return true;
		}


		return ServiceParameters;
	}

	angular.module('resa_app').factory('ServiceParameters', ServiceParametersFactory);
}());
