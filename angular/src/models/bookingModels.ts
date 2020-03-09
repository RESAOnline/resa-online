
export class ServiceParameters {

  public id = 1;
  public idAppointment = -1;
  public update = false;
  public place = '';
  public service = null;
  public dateStart = new Date();
  public dateEnd = new Date();
  public staffs = [];
  public numberPrices = [];
  public maxCapacity = -1;
  public membersUsed = [];
  public state = 'ok';
  public noEnd = false;
  public capacityTimeslot = 0;
  public customTimeslot = false;
  public membersUsedWithMembers = [];
  public idsServicePrices = [];
  public days = 0;
  public idServiceParametersLink = -1;
  public idParameter = -1;
  public tags = [];
  public equipments = [];
  public equipmentsActivated = false;

  constructor(){
    this.addNumberPrice();
  }

  getParticipantsParameter(form_participants_parameters, idParameter){
		if(form_participants_parameters != null){
      return form_participants_parameters.find(element => element.id == idParameter);
		}
		return null;
	}

	fromServiceParametersJSON(serviceParametersJSON, notCapacity){
		serviceParametersJSON = JSON.parse(JSON.stringify(serviceParametersJSON));
		this.id = serviceParametersJSON.id;
		this.idAppointment = serviceParametersJSON.idAppointment;
		this.update = serviceParametersJSON.update;
		this.place = serviceParametersJSON.place;
		this.service = serviceParametersJSON.service;
		this.dateStart = new Date(serviceParametersJSON.dateStart);
		this.dateEnd = new Date(serviceParametersJSON.dateEnd);
		this.staffs = serviceParametersJSON.staffs;
		if(!notCapacity){
			this.maxCapacity = serviceParametersJSON.maxCapacity;
		}
		this.numberPrices = serviceParametersJSON.numberPrices;
		this.membersUsed = serviceParametersJSON.membersUsed;
		this.membersUsedWithMembers = serviceParametersJSON.membersUsedWithMembers;
		this.state = serviceParametersJSON.state;
		this.noEnd = serviceParametersJSON.noEnd;
    this.capacityTimeslot = serviceParametersJSON.capacityTimeslot;
		this.customTimeslot = serviceParametersJSON.customTimeslot;
		this.days = serviceParametersJSON.days;
		this.idServiceParametersLink = serviceParametersJSON.idServiceParametersLink;
		this.idParameter = serviceParametersJSON.idParameter;
		this.tags = JSON.parse(JSON.stringify(serviceParametersJSON.tags));
		if(serviceParametersJSON.equipments != null) this.equipments = JSON.parse(JSON.stringify(serviceParametersJSON.equipments));
		if(serviceParametersJSON.equipmentsActivated != null) this.equipmentsActivated = serviceParametersJSON.equipmentsActivated;
	}

	setService(service){
		this.service = service;
		if(this.service.servicePrices.length == 1){
			this.setPriceForNumberPrice(this.service.servicePrices[0]);
		}
	}

  setCapacityWithTimeslot(timeslot){
    var capacity = timeslot.capacity;
    if(timeslot.typeCapacity == 0) capacity = timeslot.capacityMembers;
    if(timeslot.equipmentsActivated && timeslot.capacityEquipments != -1){
      capacity = Math.min(capacity, timeslot.capacityEquipments);
    }
    if((timeslot.exclusive || timeslot.membersExclusive) && timeslot.numberOfAppointmentsSaved >= timeslot.maxAppointments)
      capacity = 0;
    if(timeslot.activateExclusiveFixedCapacity || timeslot.membersExclusive)
      capacity = timeslot.maxExclusiveFixedCapacity;
    else capacity = capacity - timeslot.usedCapacity;
    this.capacityTimeslot = capacity;
  }

	setPriceForNumberPrice(price){
		if(this.numberPrices.length > 0 && price!=null){
			this.numberPrices[0].price = price;
		}
	}

	/**
	 * deprecated service parameters
	 * return true if the prices of service not same in ServiceParameters
	 * needed when load old basket
	 */
	deprecatedServiceParameters(service):boolean{
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

	isDatesSet():boolean{
		return this.maxCapacity != -1;
	}

	clearDates(){
		this.dateStart = new Date();
		this.dateEnd = new Date();
		this.maxCapacity = -1;
	}

  getStringDate(){
    return this.dateStart.getFullYear() + '-' + this.dateStart.getMonth() + '-' + this.dateStart.getDate();
  }

  getDate(){
    var date = new Date(this.dateStart);
    date.setHours(0);
		date.setMinutes(0);
		date.setSeconds(0);
    date.setMilliseconds(0);
    return date;
  }

	setTimes(startDate, endDate){
		this.dateStart.setHours(startDate.getHours());
		this.dateStart.setMinutes(startDate.getMinutes());
		this.dateStart.setSeconds(startDate.getSeconds());
		this.dateEnd.setHours(endDate.getHours());
		this.dateEnd.setMinutes(endDate.getMinutes());
		this.dateEnd.setSeconds(endDate.getSeconds());
  }

	setDate(startDate, endDate){
		this.dateStart.setFullYear(startDate.getFullYear());
		this.dateStart.setMonth(startDate.getMonth());
		this.dateStart.setDate(startDate.getDate());
		this.dateEnd.setFullYear(endDate.getFullYear());
		this.dateEnd.setMonth(endDate.getMonth());
		this.dateEnd.setDate(endDate.getDate());
  }

	isSameDatesInParams(startDate, endDate){
		return this.dateStart.getTime() == startDate.getTime() &&
			this.dateEnd.getTime() == endDate.getTime();
	}

	isSameDates(serviceParameters){
		return this.dateStart.getTime() == serviceParameters.dateStart.getTime() &&
			this.dateEnd.getTime() == serviceParameters.dateEnd.getTime();
	}

	isNoEnd(){
		return this.noEnd;
	}

	getNumberDays(){
		return this.days + 1;
	}

	addDaysToDate(days){
		this.dateStart.setDate(this.dateStart.getDate() + days);
		this.dateEnd.setDate(this.dateEnd.getDate() + days);
	}


	getEndDateDay(){
		var dateEnd = new Date(this.dateStart);
		dateEnd.setDate(dateEnd.getDate() + this.days);
		return dateEnd;
	}

	isLink(){
		return this.id != this.idServiceParametersLink;
	}

	isWeek(){
		return this.service.typeAvailabilities == 1;
	}


	mergeServiceParameters(serviceParameters){
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

	updateServicesParameters(serviceParameters){
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

	addNumberPrice(){
		this.numberPrices.push({price:null, number:0, participants:[]});
	}

	pushInNumberPrice(price, number, participants){
		this.numberPrices.push({price:price, number:number, participants:participants});
	}

	isErrorNumberPriceMaxMin(){
		for(var i = 0; i < this.numberPrices.length; i++){
			var numberPrice =this.numberPrices[i];
			var price = numberPrice.price;
			if(price.activateMaxQuantity && numberPrice.number > price.maxQuantity) return true;
			if(price.activateMinQuantity && numberPrice.number < price.minQuantity) return true;
		}
		return false;
	}

	clearNumberPriceNumber(){
		for(var i = 0; i < this.numberPrices.length; i++){
			this.numberPrices[i].number = 0;
		}
	}

	haveAllMandatoryPrices(){
		for(var i = 0; i < this.service.servicePrices.length; i++){
			var price = this.service.servicePrices[i];
			var numberPrice = this.getNumberPriceByIdPrice(price.id);
			if(price.mandatory && (numberPrice == null || numberPrice.number == 0)){
				return false;
			}
		}
		return true;
	}

	removeParticipant(numberPrice, $index){
		var localNumberPrice = this.getNumberPriceByIdPrice(numberPrice.price.id);
		localNumberPrice.participants.splice($index, 1);
		localNumberPrice.number--;
	}

	addNumberPriceForEachServicePrice(timeslot){
		if(this.service != null){
			var newNumberPrices = [];
			for(var i = 0; i < this.service.servicePrices.length; i++){
				var price = this.service.servicePrices[i];
        var oldNumberPrice = this.getNumberPriceByIdPrice(price.id);
				if(timeslot==null || timeslot.idsServicePrices==null || timeslot.idsServicePrices.length == 0 || timeslot.idsServicePrices.indexOf(price.id) != -1 || oldNumberPrice!=null){
					var number = 0;
					var participants = [];
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

	completeNumberPriceForEachServicePrice(){
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

	getFirstPrice(){
		if( this.numberPrices.length > 0)
			return this.numberPrices[0].price;
		else return null;
	}

	resetDates(){
		this.dateStart = new Date();
		this.dateEnd = new Date();
	}

	getNumberPriceByIdPrice(idPrice){
		for(var i = 0; i < this.numberPrices.length; i++){
			var numberPrice = this.numberPrices[i];
			if(numberPrice.price != null && numberPrice.price.id == idPrice){
				return numberPrice;
			}
		}
		return null;
	}

	getIndexNumberPriceByIdPrice(idPrice){
		for(var i = 0; i < this.numberPrices.length; i++){
			var numberPrice = this.numberPrices[i];
			if(numberPrice.price != null && numberPrice.price.id == idPrice){
				return i;
			}
		}
		return -1;
	}

	getNumberPriceBySlug(slug){
		for(var i = 0; i < this.numberPrices.length; i++){
			var numberPrice = this.numberPrices[i];
			if(numberPrice.price != null && numberPrice.price.slug == slug){
				return numberPrice;
			}
		}
		return null;
	}

	resetFirstNumber()	{
		if( this.numberPrices.length > 0)
			return this.numberPrices[0].number = 0;
	}

	getFirstNumberPrice(){
		if(this.numberPrices.length > 0)
			return this.numberPrices[0];
		else return null;
	}



	removeNumberPrice(index){
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

	addStaff(){
		this.staffs.push(0);
	}

	removeStaff(index){
		this.staffs.splice(index, 1);
	}


	assignStaff(staff){
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

	displayMemberUsed(memberUsed, capacity_word, off_schedule_word){
		var result = memberUsed.nickname + ' - ' + capacity_word + ' : ' + (memberUsed.capacity - memberUsed.usedCapacity);
		if(memberUsed.capacity == 0){
			result = memberUsed.nickname + ' - ' + off_schedule_word;
		}
		return result;
	}

	getMemberUsed(index){
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

	mergeMembersUsedWithMembers(members){
		var membersUsedReturn = [];
		var nicknameMembersUsed = [];
		for(var i = 0; i < this.membersUsed.length; i++){
			var memberUsed = this.membersUsed[i];
			nicknameMembersUsed.push(memberUsed.nickname);
			membersUsedReturn.push(memberUsed);
		}
		for(var i = 0; i < members.length; i++){
			var member = members[i];
			var memberUsed:any = {id: member.id, nickname:member.nickname, capacity: 0, usedCapacity: 0};
			if(nicknameMembersUsed.indexOf(member.nickname) == -1){
				membersUsedReturn.push(memberUsed);
			}
		}
		this.membersUsedWithMembers = membersUsedReturn;
	}


	getMemberUsedMembers(index){
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

	memberIsInStaffs(member, staffs){
		for(var i = 0; i < staffs.length; i++){
			if(member.id == staffs[i].id)
				return true;
		}
		return false;
	}

	displayStaffs(staffs){
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

	haveExtraStaff(){
		for(var i = 0; i < this.staffs.length; i++){
			if(this.staffs[i].capacity == 0){
				return true;
			}
		}

		return false;
	}

	getStaff(idStaff){
		for(var i = 0; i < this.staffs.length; i++){
			if(this.staffs[i].id == idStaff){
				return this.staffs[i];
			}
		}
		return null;
	}

	assignNumber(staff){
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

	needAssignNumber(){
		var number = 0;
		for(var i = 0; i < this.staffs.length; i++){
			number += this.assignNumber(this.staffs[i]);
		}
		return this.getNumber() - number;
	}

	getNumber(){
		var number = 0;
		for(var i = 0; i < this.numberPrices.length; i++){
			var numberPrice = this.numberPrices[i];
			if(numberPrice.price!=null && !numberPrice.price.extra){
				number += numberPrice.number;
			}
		}
		return number;
	}

	getRepeatNumber(){
		var number =  this.getNumber();
		if(number==null || number<=0) return [];
		return (new Array(number));
	}

	getRepeatNumberByNumber(number){
		return (new Array(number));
	}


	getNumberWithoutIndex(index){
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

	addNumberInNumberPrice(numberPrice, value){
		numberPrice.number += value;
	}

	getPriceNumberPrice(numberPrices){
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

	getMinThresholdPrice(numberPrices){
		var priceModel = numberPrices.price;
		var price = 0;
		if(priceModel != null){
			numberPrices = JSON.parse(JSON.stringify(numberPrices));
			numberPrices.number = this.getMinNumberPrice(numberPrices);
			return this.getPriceNumberPrice(numberPrices);
		}
		return price;
	}

	getPrice(){
		var number = 0;
		for(var i = 0; i < this.numberPrices.length; i++){
			var numberPrices = this.numberPrices[i];
			if(numberPrices.price!=null){
				number += this.getPriceNumberPrice(numberPrices);
			}
		}
		return Math.round(number * 100) / 100;
	}

	getPriceWithDefaultSlugServicePrice(){
		var number = this.getPrice();
		//defaut slg
		if(this.service != null && this.service.defaultSlugServicePrice.length > 0){
			var numberPrice = this.getNumberPriceBySlug(this.service.defaultSlugServicePrice);
			if(numberPrice && numberPrice.number > 0){
				number = this.getPriceNumberPrice(numberPrice);
			}
		}
		return Math.round(number * 100) / 100;
	}

	getMinNumberPrice(numberPrice){
		if(numberPrice.price != null && numberPrice.price.activateMinQuantity)
			return numberPrice.price.minQuantity;
		else return 1;
	}

	getMaxNumberPrice(numberPrice, totalMax){
		var max = 999;
		if(numberPrice.price != null && numberPrice.price.activateMaxQuantity)
			 max = numberPrice.price.maxQuantity;
		return Math.min(max, totalMax);
	}

	removeNumberPriceNull(){
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

  removeAllNumberPriceZero(){
    this.numberPrices = this.numberPrices.filter(element => element.number > 0 );
  }

	/**
	 * return the participants
	 */
	getParticipants(){
		var participants = [];
		for(var i = 0; i < this.numberPrices.length; i++){
			var numberPrice = this.numberPrices[i];
			if(numberPrice!=null && numberPrice.participants != null){
				for(var j = 0; j < numberPrice.participants.length; j++){
					var participant = numberPrice.participants[j];
          var participantExist = participants.find(element => (participant.lastname == null || participant.lastname == element.lastname) && (participant.firstname == null || participant.firstname == element.firstname));
					if(participantExist == null){
						participants.push(JSON.parse(JSON.stringify(participant)));
					}
				}
			}
		}
		return participants;
	}

	needSelectParticipants(){
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

	participantsIsSelected(form_participants_parameters){
		if(this.needSelectParticipants()){
			for(var index = 0; index < this.numberPrices.length; index++){
				var numberPrice = this.numberPrices[index];
				if(numberPrice.price.participantsParameter == null || numberPrice.price.participantsParameter.length == 0) return true;
				if(numberPrice.participants == null || numberPrice.participants.length < numberPrice.number) return false;
				for(var i = 0; i < numberPrice.participants.length; i++){
					var participant = numberPrice.participants[i];
					if(participant != null){
						var participantsParameter = this.getParticipantsParameter(form_participants_parameters, numberPrice.price.participantsParameter);
						if(participantsParameter != null){
							var params = participantsParameter.fields;
							for(var key in params){
								var param = params[key];
								var value = participant[param.varname];
								if(param.mandatory &&
                  (value == null || value.length <= 0 || value <= 0)){
									return false;
								}
                else if(param.mandatory && param.type == 'select' && (param.options.findIndex(element => element.id == value) == -1)){
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

	isOk(){
		return this.state == 'ok';
	}

	isCancelled(){
		return this.state == 'cancelled';
	}

	isAbandonned(){
		return this.state == 'abandonned';
	}

	isWaiting(){
		return this.state == 'waiting';
	}

	ok(){
		this.state = 'ok';
	}

	cancelled(){
		this.state = 'cancelled';
	}

	abandonned(){
		this.state = 'abandonned';
	}

	waiting(){
		this.state = 'waiting';
	}

	putOk(){
		return this.state = 'ok';
	}

	haveTag(idTag){
		return this.tags.indexOf(idTag) != -1;
	}

	switchTag(idTag){
		if(!this.haveTag(idTag)) this.tags.push(idTag);
		else {
			var index = this.tags.indexOf(idTag);
			this.tags.splice(index, 1);
		}
	}

	needDefaultSlugServicePrice(){
		if(this.service.defaultSlugServicePrice.length > 0){
			return true;
		}
		return false;
	}

	haveDefaultSlugServicePrice(){
		if(this.service!=null && this.service.defaultSlugServicePrice != null && this.service.defaultSlugServicePrice.length > 0){
      var numberPrice = this.getNumberPriceBySlug(this.service.defaultSlugServicePrice);
			return numberPrice != null && numberPrice.number > 0;
		}
		return false;
	}

	isDefaultSlugServicePrice(numberPrice){
		if(this.service!=null && this.service.defaultSlugServicePrice != null && this.service.defaultSlugServicePrice.length > 0 && numberPrice.price!=null && numberPrice.price.slug == this.service.defaultSlugServicePrice){
			return true;
		}
		return false;
	}

	canCalculatePrice(numberPrice){
		return !this.haveDefaultSlugServicePrice() || this.isDefaultSlugServicePrice(numberPrice);
	}

	getServicePriceBySlug(slug){
		for(var i = 0; i < this.service.servicePrices.length; i++){
			var servicePrice = this.service.servicePrices[i];
			if(servicePrice.slug == slug){
				return servicePrice;
			}
		}
		return null;
	}

	applyDefaultSlugServicePrice(){
		if(this.needDefaultSlugServicePrice() && this.haveAllMandatoryPrices()){
			var servicePrice =  this.getServicePriceBySlug(this.service.defaultSlugServicePrice);
			var numberPrice = this.getNumberPriceBySlug(this.service.defaultSlugServicePrice);
			if(numberPrice){
				this.removeNumberPrice(this.getIndexNumberPriceByIdPrice(numberPrice.price.id));
			}
			if(this.getPrice() > 1 && this.getPrice() < servicePrice.price){
				this.pushInNumberPrice(servicePrice, 1, []);
			}
		}
	}
	/**
	 *
	 */
	getEquipmentsUse(equipment, index){
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
	getMaxEquipmentsCanChoose(index){
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
	isOkEquipmentsChoose(){
		for(var i = 0; i < this.numberPrices.length; i++){
			var numberPrice = this.numberPrices[i];
			var numberCanChooose = this.getMaxEquipmentsCanChoose(i);
			if(numberPrice.number > numberCanChooose){
				return false;
			}
		}
		return true;
	}

}


export class Basket {
  public mapDateToServiceParameters:any = {};

  getNextId(){
    var max = 0;
    var servicesParameters = this.getAllServicesParameters();
    for(var i = 0; i < servicesParameters.length; i++){
      var serviceParameter = servicesParameters[i];
      max = Math.max(max, serviceParameter.id);
    }
    return max + 1;
  }

  getServiceParametersLinkId(){
    var max = 0;
    var servicesParameters = this.getAllServicesParameters();
    for(var i = 0; i < servicesParameters.length; i++){
      var serviceParameter = servicesParameters[i];
      max = Math.max(max, serviceParameter.idServiceParametersLink);
    }
    return max + 1;
  }

  isSynchronized(idServiceParametersLink){
    var numberSynchronized = 0;
    var servicesParameters = this.getAllServicesParameters();
    for(var i = 0; i < servicesParameters.length; i++){
      var serviceParameter = servicesParameters[i];
      if(serviceParameter.idServiceParametersLink === idServiceParametersLink){
        numberSynchronized++;
      }
    }
    return numberSynchronized > 1;
  }

  desynchronisetServiceParameters(idServiceParametersLink){
    var numberSynchronized = 0;
    var lastIdLink = this.getServiceParametersLinkId();
    var servicesParameters = this.getAllServicesParameters();
    for(var i = 0; i < servicesParameters.length; i++){
      var serviceParameter = servicesParameters[i];
      if(serviceParameter.idServiceParametersLink === idServiceParametersLink){
        if(numberSynchronized > 0){
           serviceParameter.idServiceParametersLink = lastIdLink;
           lastIdLink++;
         }
         else {
           numberSynchronized++;
         }
      }
    }
  }

  sortMapDateToServiceParameters(){
    var ordered = {};
    Object.keys(this.mapDateToServiceParameters).sort((aux1, aux2) => {
      return (new Date(aux1)).getTime() - (new Date(aux2)).getTime();
    }).forEach((key) => {
      ordered[key] = this.mapDateToServiceParameters[key];
    });
    this.mapDateToServiceParameters = ordered;
  }

  /**
   * add a new serviceParameters
   */
  addServiceParameters(serviceParameters){
    if(this.mapDateToServiceParameters[serviceParameters.getStringDate()] == null) {
      this.mapDateToServiceParameters[serviceParameters.getStringDate()] = {
        date:serviceParameters.getDate(),
        servicesParameters: []
      };
      this.sortMapDateToServiceParameters();
    }
    var servicesParameters = this.mapDateToServiceParameters[serviceParameters.getStringDate()].servicesParameters;
    servicesParameters.push(serviceParameters);
    servicesParameters.sort((sp1, sp2) => {
      return sp1.id - sp2.id;
    });
  }

  /**
   * Replace value for a serviceParameters return true if replaced
   */
  replaceServiceParameters(serviceParameters, force){
    var replaced = this.removeServiceParametersBy(serviceParameters, 'id');
    if(replaced || force){
      if(this.mapDateToServiceParameters[serviceParameters.getStringDate()] == null) {
        this.mapDateToServiceParameters[serviceParameters.getStringDate()] = {
          date:serviceParameters.getDate(),
          servicesParameters: []
        };
        this.sortMapDateToServiceParameters();
      }
      var servicesParameters = this.mapDateToServiceParameters[serviceParameters.getStringDate()].servicesParameters;
      servicesParameters.push(serviceParameters);
      servicesParameters.sort((sp1, sp2) => {
        return sp1.id - sp2.id;
      });
    }
    return replaced;
  }

  getReductionById(idReduction, reductions){
		for(var i = 0; i < reductions.length; i++){
			var reduction = reductions[i];
			if(reduction.id == idReduction)
				return reduction;
		}
		return null;
	}

  getServiceParameters(startDate, endDate)	{
    var stringDate =  startDate.getFullYear() + '-' + startDate.getMonth() + '-' + startDate.getDate();
    if(this.mapDateToServiceParameters[stringDate] != null){
      var servicesParameters = this.mapDateToServiceParameters[stringDate].servicesParameters;
      for(var i = 0; i < servicesParameters.length; i++){
        var serviceParametersAux = servicesParameters[i];
        if(serviceParametersAux.isSameDatesInParams(startDate, endDate)){
          return serviceParametersAux;
        }
      }
    }
    return null;
  }

  getServiceParametersLink(serviceParameters)	{
    if(this.mapDateToServiceParameters[serviceParameters.getStringDate()] != null){
      var servicesParameters = this.mapDateToServiceParameters[serviceParameters.getStringDate()].servicesParameters;
      for(var i = 0; i < servicesParameters.length; i++){
        var serviceParametersAux = servicesParameters[i];
        if(serviceParametersAux.id == serviceParameters.idServiceParametersLink){
          return serviceParametersAux;
        }
      }
    }
    return null;
  }

  getAllServiceParametersLink(serviceParameters)	{
    var allServicesParameters = [];
    if(this.mapDateToServiceParameters[serviceParameters.getStringDate()] != null){
      var servicesParameters = this.mapDateToServiceParameters[serviceParameters.getStringDate()].servicesParameters;
      for(var i = 0; i < servicesParameters.length; i++){
        var serviceParametersAux = servicesParameters[i];
        if(serviceParametersAux.idServiceParametersLink == serviceParameters.idServiceParametersLink){
          allServicesParameters.push(serviceParametersAux);
        }
      }
    }
    return allServicesParameters;
  }


  clear(){
    this.mapDateToServiceParameters = {};
  }

  /**
   * remove service parameters
   */
  removeServiceParameters(serviceParameters, index){
    if(serviceParameters.service != null && this.mapDateToServiceParameters[serviceParameters.getStringDate()]!=null){
      var servicesParameters = this.mapDateToServiceParameters[serviceParameters.getStringDate()].servicesParameters;
      servicesParameters.splice(index, 1);
      if(this.mapDateToServiceParameters[serviceParameters.getStringDate()].servicesParameters.length <= 0){
        this.mapDateToServiceParameters.splice(serviceParameters.getStringDate()); //TODO
      }
    }
  }

  /**
   * remove service parameters
   */
  removeServiceParametersBy(serviceParameters, type:string){
    var removed = false;
    for(var key in this.mapDateToServiceParameters){
      var mapDateToServiceParametersElement = this.mapDateToServiceParameters[key];
      var servicesParameters = mapDateToServiceParametersElement.servicesParameters;
      var index = 0;
      var found = false;
      while(!found && index < servicesParameters.length){
        if(servicesParameters[index][type] == serviceParameters[type]){
          found = true;
        }
        else index++;
      }
      if(found){
        servicesParameters.splice(index, 1);
        removed = true;
      }
      if(mapDateToServiceParametersElement.servicesParameters.length <= 0){
        delete this.mapDateToServiceParameters[key];
      }
    }
    return removed;
  }

  /**
   * return the number person selected for same timeslot
   */
  getNumberTimeslot(serviceParameters, index){
    if(index == null)
      index = -1;
    var number = 0;
    if(serviceParameters.service != null && this.mapDateToServiceParameters[serviceParameters.getStringDate()]!=null){
      var servicesParameters = this.mapDateToServiceParameters[serviceParameters.getStringDate()].servicesParameters;
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
  getNumberTimeslotWithSamePrice(serviceParameters, numberPrice){
    var number = 0;
    if(serviceParameters.service != null &&  numberPrice.price != null && this.mapDateToServiceParameters[serviceParameters.getStringDate()]!=null){
      var servicesParameters = this.mapDateToServiceParameters[serviceParameters.getStringDate()].servicesParameters;
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
  getMax(serviceParameters, index){
    return serviceParameters.maxCapacity; // (serviceParameters.maxCapacity - this.getNumberTimeslot(serviceParameters, index));
  }

  /**
   * return the max price of serviceParameters
   */
  getSubTotalPrice(servicesParameters, reductions, mapIdClientObjectReduction, advancePayment, typeAccount){
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
                  var reduction = this.getReductionById(params.idReduction, reductions);
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
            var reduction = this.getReductionById(params.idReduction, reductions);
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
  getTotalNumber(){
    var number = 0;
    for(var key in this.mapDateToServiceParameters){
      var mapIdServiceToServiceParametersElement = this.mapDateToServiceParameters[key];
      var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
      for(var i = 0; i < servicesParameters.length; i++){
        number += servicesParameters[i].getNumber();
      }
    }
    return number;
  }

  /**
   * return total price.
   */
  getTotalPrice(reductions, mapIdClientObjectReduction, advancePayment, typeAccount){
    var number = 0;
    for(var key in this.mapDateToServiceParameters){
      var mapIdServiceToServiceParametersElement = this.mapDateToServiceParameters[key];
      var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
      number += this.getSubTotalPrice(servicesParameters, reductions, mapIdClientObjectReduction, advancePayment, typeAccount);
    }
    var reductionNumber = 0;
    var reductionPurcent = 0;
    if(mapIdClientObjectReduction['id0'] != null){
      var allParams = mapIdClientObjectReduction['id0'];
      for(var i = 0; i < allParams.length; i++){
        var params = allParams[i];
        var reduction = this.getReductionById(params.idReduction, reductions);
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
  getArray(){
    var newMapIdServiceToServiceParameters = [];
    for(var key in this.mapDateToServiceParameters){
      var mapIdServiceToServiceParametersElement = this.mapDateToServiceParameters[key];
      newMapIdServiceToServiceParameters.push(mapIdServiceToServiceParametersElement);
    }
    return newMapIdServiceToServiceParameters;
  }


  /**
   * return all services parameters to concat them
   */
  getAllServicesParameters(){
    var allServicesParameters = [];
    for(var key in this.mapDateToServiceParameters){
      var mapIdServiceToServiceParametersElement = this.mapDateToServiceParameters[key];
      var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
      allServicesParameters = allServicesParameters.concat(servicesParameters);
    }
    return allServicesParameters;
  }


  /**
   * return all services parameters to concat them
   */
  getAllServicesParametersByIdLink(idLink){
    var allServicesParameters = [];
    for(var key in this.mapDateToServiceParameters){
      var mapIdServiceToServiceParametersElement = this.mapDateToServiceParameters[key];
      var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
      for(var i = 0; i < servicesParameters.length; i++){
        var serviceParameters = servicesParameters[i];
        if(serviceParameters.idServiceParametersLink == idLink){
          allServicesParameters.push(serviceParameters);
        }
      }
    }
    return allServicesParameters;
  }

  serviceParametersIsOk(serviceParameters, index, allowInconsistencies){
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

  basketIsOk(allowInconsistencies){
    var ok = this.getAllServicesParameters().length > 0;
    for(var key in this.mapDateToServiceParameters){
      var mapIdServiceToServiceParametersElement = this.mapDateToServiceParameters[key];
      var servicesParameters = mapIdServiceToServiceParametersElement.servicesParameters;
      for(var i = 0; i < servicesParameters.length; i++){
        var serviceParameters = servicesParameters[i];
        ok = ok && this.serviceParametersIsOk(serviceParameters, i, allowInconsistencies);
      }
    }
    return ok;
  }

  getParticipants(){
    var allServicesParameters = this.getAllServicesParameters();
    var allParticipants = [];
    for(var i = 0; i < allServicesParameters.length; i++){
      var serviceParameters = allServicesParameters[i];
      var participants = serviceParameters.getParticipants();
      for(var k = 0; k < participants.length; k++){
        var participant = participants[k];
        var participantExist = allParticipants.find(element => (participant.lastname == null || participant.lastname == element.lastname) && (participant.firstname == null || participant.firstname == element.firstname));
        if(participantExist == null){
          allParticipants.push(participant);
        }
      }
    }
    return allParticipants;
  }

  /**
   * set all states
   */
  setAllStates(state){
    for(var key in this.mapDateToServiceParameters){
      var servicesParameters = this.mapDateToServiceParameters[key].servicesParameters;
      for(var i = 0; i < servicesParameters.length; i++){
        var serviceParameters = servicesParameters[i];
        serviceParameters.state = state;
      }
    }
  }
}
