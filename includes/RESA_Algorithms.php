<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Algorithms
{
	/**
	 * \fn getTimeslots
	 * \brief return all timeslots for a date and service, return the associated member and the capacity and capacity used
	 * \param $date the date
	 * \param $date the date end
	 * \param $service the service
	 * \param $newAppointments the new appointment needed to add new appointment
	 * \param $idBooking the id booking to updated.
	 * \param $isBackEnd true if going to backend
	 * \return the list of timeslots
	 */
	 public static function getTimeslots($date, $dateEnd, RESA_Service $service, $newAppointments, $idBooking, $isBackEnd, $typeAccount){
		$timeslotsService = $service->getTimeslots($date, $dateEnd, $isBackEnd, $typeAccount);
		$allTimeslots = array();
		$membersAvailabilities = array();
		$members = RESA_Member::getAllDataWithIdService($service->getId());
		for($i = 0; $i < count($members); $i++){
			$member = $members[$i];
			if($member->isActivated()){
				$availabilities = $member->getAvailabilityForService($date, $service->getId());
				if(count($availabilities) > 0){
					$array = array(
						"member" => $member,
						"availabilities" => $availabilities
					);
					array_push($membersAvailabilities, $array);
				}
			}
		}
		for($i = 0; $i < count($timeslotsService); $i++){
			$timeslots = $timeslotsService[$i];
			$localTimeslots = $timeslots->toArray();
			$localTimeslots['idService'] = $service->getId();
			$localTimeslots['display_remaining_position_overcapacity'] = RESA_Tools::toJSONBoolean(get_option('resa_settings_form_display_remaining_position_overcapacity')) == 'true';
			$localTimeslots['capacityMembers'] = 0;
			$localTimeslots['usedCapacity'] = 0;
			if(!RESA_Variables::isStaffManagementActivated() || count($membersAvailabilities) <= 0){  //Automatical fix to true
				$localTimeslots['noStaff'] = 1;
			}
			$justEquals = $localTimeslots['noStaff']; //&& !$localTimeslots['equipmentsActivated'];

			$timeslotStartDate = RESA_Algorithms::timeToDate($timeslots->getStartTime(), $date);
			$timeslotEndDate = RESA_Algorithms::timeToDate($timeslots->getEndTime(), $date);
			if($timeslotEndDate < $timeslotStartDate){
				$timeslotEndDate->add(new DateInterval('P1D'));
			}

			$allServiceConstraints = RESA_ServiceConstraint::getAllDataInterval($service->getId(), $timeslotStartDate->format('Y-m-d H:i:s'), $timeslotEndDate->format('Y-m-d H:i:s'));
			$allAppointments = [];

			$appointmentsInDB = RESA_Appointment::getAllDataInterval($timeslotStartDate->format('Y-m-d H:i:s'),$timeslotEndDate->format('Y-m-d H:i:s'), false);
			$statesParameters = unserialize(get_option('resa_settings_states_parameters'));

			foreach($appointmentsInDB as $appointment){
				$appointmentStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getStartDate());
				$appointmentEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getEndDate());
				$bookingIsBackend = RESA_Booking::isBookingBackend($appointment->getIdBooking());
				if($appointment->getIdBooking() != $idBooking && (($appointment->isOk() && !RESA_Booking::isQuotationQuery($appointment->getIdBooking())) || ($appointment->stateCountCapacity($statesParameters, $bookingIsBackend) && (!$appointment->stateExpire($statesParameters, $bookingIsBackend) || !RESA_Booking::isBookingExpired($appointment->getIdBooking()))))){
					if(((!$justEquals || $service->isSameService($appointment->getIdService())) &&
					(($appointmentStartDate <= $timeslotStartDate && $timeslotStartDate < $appointmentEndDate) ||
						($appointmentStartDate < $timeslotEndDate && $timeslotEndDate < $appointmentEndDate) ||
						($timeslotStartDate <= $appointmentStartDate && $appointmentEndDate <= $timeslotEndDate) ||
						($timeslotStartDate < $appointmentStartDate && $appointmentStartDate < $timeslotEndDate))) || ($justEquals &&
						($appointmentStartDate == $timeslotStartDate &&
						$appointmentEndDate == $timeslotEndDate))){
							array_push($allAppointments, $appointment);
					}
				}
			}
			foreach($newAppointments as $appointment){
				$appointmentStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getStartDate());
				$appointmentEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getEndDate());
				if((!$justEquals &&
				(($appointmentStartDate <= $timeslotStartDate && $timeslotStartDate < $appointmentEndDate) ||
					($appointmentStartDate < $timeslotEndDate && $timeslotEndDate < $appointmentEndDate) ||
					($timeslotStartDate <= $appointmentStartDate && $appointmentEndDate <= $timeslotEndDate) ||
					($timeslotStartDate < $appointmentStartDate && $appointmentStartDate < $timeslotEndDate)))
					 || ($justEquals &&
					($appointmentStartDate == $timeslotStartDate &&
					$appointmentEndDate == $timeslotEndDate))){
					array_push($allAppointments, $appointment);
				}
			}

			$localTimeslots['numberOfAppointmentsSaved'] = 0;
			$localTimeslots['equipmentsActivated'] = RESA_Variables::isEquipmentsManagementActivated()?$localTimeslots['equipmentsActivated']:false;
			$equipments = [];
			if($localTimeslots['equipmentsActivated']){
				$allEquipments = $service->getAllEquipments();
				foreach($allEquipments as $idEquipment){
					array_push($equipments, array(
						'idEquipment' => $idEquipment,
						'number' => 0,
						'max' => RESA_Equipment::getNumber($idEquipment)
					));
				}
			}
			foreach($allAppointments as $appointment){
				if($service->isSameService($appointment->getIdService()) && /*$localTimeslots['typeCapacity']==2 &&*/ (($appointment->isOk() && !RESA_Booking::isQuotationQuery($appointment->getIdBooking())) || $appointment->stateCountCapacity($statesParameters, RESA_Booking::isBookingBackend($appointment->getIdBooking())))){
					$localTimeslots['numberOfAppointmentsSaved']++;
					$localTimeslots['usedCapacity'] += $appointment->getNumbers();
					if($localTimeslots['equipmentsActivated']){
						$equipments = $appointment->getCapacityOfEquipments($service, $equipments);
					}
				}
			}

			$capacityEquipments = 0 ;
			if($localTimeslots['equipmentsActivated']){
				for($indexEquipments = 0; $indexEquipments < count($equipments); $indexEquipments++){
					if(!isset($equipments[$indexEquipments]['max'])){
						$equipments[$indexEquipments]['max'] = RESA_Equipment::getNumber($equipments[$indexEquipments]['idEquipment']);
					}
					if($equipments[$indexEquipments]['max'] < $equipments[$indexEquipments]['number']){
						$equipments[$indexEquipments]['number'] = $equipments[$indexEquipments]['max'];
						$equipments[$indexEquipments]['maxReach'] = true;
					}
					$capacityEquipments += $equipments[$indexEquipments]['max'];
				}
			}

			$localTimeslots['equipments'] = $equipments;
			$localTimeslots['capacityEquipments'] = $capacityEquipments;
			if($service->oneServicePriceWithNotEquipments()) $localTimeslots['capacityEquipments'] = -1;
			$localTimeslots['members'] = array();
			$localTimeslots['membersUsed'] = array();
			$localTimeslots['maxExclusiveFixedCapacity'] = $localTimeslots['typeCapacity']==RESA_ServiceTimeslot::$CAPACITY_FIXED?$localTimeslots['capacity']:0;
			if($localTimeslots['activateExclusiveFixedCapacity'] && $localTimeslots['exclusive'] && $localTimeslots['typeCapacity']==RESA_ServiceTimeslot::$CAPACITY_FIXED){
				$localTimeslots['capacity'] = $localTimeslots['capacity'] * $localTimeslots['maxAppointments'];
			}
			$localTimeslots['membersExclusive'] = $localTimeslots['exclusive']?false:$localTimeslots['membersExclusive'];
			if($localTimeslots['noStaff'] != 1){
				$localTimeslots['usedCapacity'] = 0;

				//Get the members for this timeslot
				for($j = 0; $j < count($membersAvailabilities); $j++){
					$availabilities = $membersAvailabilities[$j]['availabilities'];
					$member = $membersAvailabilities[$j]['member'];
					$found = false;
					$index = 0;
					while(!$found && $index < count($availabilities)){
						$availability = $availabilities[$index];
						$availabilityStartDate = RESA_Algorithms::timeToDate($availability[0], $date);
						$availabilityEndDate = RESA_Algorithms::timeToDate($availability[1], $date);
						if($availabilityEndDate < $availabilityStartDate){
							$timeslotEndDate->add(new DateInterval('P1D'));
						}

						if(/*(RESA_Algorithms::timeToDate($availability[0]) <= RESA_Algorithms::timeToDate($timeslots->getStartTime()) &&
							RESA_Algorithms::timeToDate($availability[1]) > RESA_Algorithms::timeToDate($timeslots->getStartTime())) ||
							(RESA_Algorithms::timeToDate($availability[0]) < RESA_Algorithms::timeToDate($timeslots->getEndTime()) &&
							RESA_Algorithms::timeToDate($availability[1]) > RESA_Algorithms::timeToDate($timeslots->getEndTime())) ||*/
							($availabilityStartDate <= $timeslotStartDate && $availabilityEndDate >= $timeslotEndDate)){
							$found = true;
						}
						else {
							$index++;
						}
					}
					$allMemberConstraints = RESA_MemberConstraint::getAllDataInterval($member->getId(), $timeslotStartDate->format('Y-m-d H:i:s'), $timeslotEndDate->format('Y-m-d H:i:s'));
					$found = $found && (count($allMemberConstraints) == 0);
					if($found){
						$capacityLink = 0;
						$capacityOnThisService = 0;
						$capacityOnAnotherService = 0;
						$memberLink = $member->getMemberLinkByService($service->getId());
						foreach($allAppointments as $appointment){
							if(($appointment->isOk() && !RESA_Booking::isQuotationQuery($appointment->getIdBooking())) || $appointment->stateCountCapacity($statesParameters, RESA_Booking::isBookingBackend($appointment->getIdBooking()))){
								if(!$service->isSameService($appointment->getIdService()) && isset($memberLink) && $memberLink->isContainService($appointment->getIdService())){
									$capacityLink += $appointment->getMemberCapacityUsed($membersAvailabilities[$j]['member']->getId());
								}
								else if(!$service->isSameService($appointment->getIdService()) && (!isset($memberLink) || $service->isExclusive())) {
									$capacityOnAnotherService += $appointment->getMemberCapacityUsed($membersAvailabilities[$j]['member']->getId());
								}
								if($service->isSameService($appointment->getIdService())) {
									$capacityOnThisService += $appointment->getMemberCapacityUsed($membersAvailabilities[$j]['member']->getId());
								}
							}
						}
						//Calculated maxCapacity
						$max = 0;
						$min = 999999999999;
						if($capacityLink > 0){
							foreach($memberLink->getMemberLinkServices() as $memberLinkService){
								$idService = $memberLinkService->getIdService();
								if(isset($availability[2][$idService]) && $max < $availability[2][$idService])
									$max = $availability[2][$idService];
								if(isset($availability[2][$idService]) && $min > $availability[2][$idService])
									$min = $availability[2][$idService];
							}
						}

						if($capacityOnAnotherService <= 0 && isset($availability[2][$service->getId()])){
							$capacity = $availability[2][$service->getId()]; // - $capacityOnThisService;
							if($capacityLink > 0){
								//add capacityOnThisService because remove in angular directive (see $localTimeslots['usedCapacity'])
								if($memberLink->getTypeCapacityMethod()==1)
									$capacity = $max - $capacityLink;
								else if($memberLink->getTypeCapacityMethod()==2)
									$capacity = $min - $capacityLink;
							}
							if($capacityOnThisService > 0 && $localTimeslots['membersExclusive']){
								$capacityOnThisService = $capacity;
							}
							$localTimeslots['usedCapacity'] += $capacityOnThisService;
							if($localTimeslots['membersExclusive']){
								$localTimeslots['maxExclusiveFixedCapacity'] = $capacity;
							}
							$localTimeslots['capacityMembers'] += $capacity;
							if(count($allServiceConstraints) > 0){

							}
							array_push($localTimeslots['membersUsed'],array(
								'id'=>$membersAvailabilities[$j]['member']->getId(),
								'nickname'=>$membersAvailabilities[$j]['member']->getNickname(),
								'capacity'=>$capacity,
								'usedCapacity'=>$capacityOnThisService));
							array_push($localTimeslots['members'], $membersAvailabilities[$j]['member']);
						}
					}
				}
			}
			if($localTimeslots['membersExclusive']){
				$localTimeslots['maxAppointments'] = count($localTimeslots['membersUsed']);
			}
			if(count($allServiceConstraints) > 0){
				$localTimeslots['capacity'] = $localTimeslots['usedCapacity'];
				$localTimeslots['capacityMembers'] = $localTimeslots['usedCapacity'];
				$localTimeslots['maxExclusiveFixedCapacity'] = $localTimeslots['usedCapacity'];
			}
			array_push($allTimeslots, $localTimeslots);
		}
		return $allTimeslots;
	}

	/**
	 * \fn getTimeslot
	 * \brief return all timeslots for a date and service, return the associated member and the capacity and capacity used
	 * \param $date the date
	 * \param $service the service
	 * \param $newAppointments the new appointment needed to add new appointment
	 * \param $idBooking the id booking to updated.
	 * \param $isBackEnd the id booking to updated.
	 * \param $isBackEnd true if going to backend
	 * \return the list of timeslots
	 */
	public static function getTimeslot($startDate, $endDate, RESA_Service $service, $newAppointments, $idBooking, $isBackEnd, $typeAccount) {
		$allTimeslots = self::getTimeslots($startDate, $endDate, $service, $newAppointments, $idBooking, $isBackEnd, $typeAccount);
		if(count($allTimeslots) == 1) return $allTimeslots[0];
		else if(count($allTimeslots) > 1){
			foreach($allTimeslots as $timeslot){
				$timeslotStartDate = RESA_Algorithms::timeToDate($timeslot['startTime'], $startDate);
				$timeslotEndDate = RESA_Algorithms::timeToDate($timeslot['endTime'], $endDate);
				if($timeslotStartDate == $startDate && $timeslotEndDate == $endDate){
					return $timeslot;
				}
			}
		}
		return null;
	}

	/**
	 * Set the time to this date or current date
	 */
	public static function timeToDate($time, $date = null){
		if($date == null){
			$date = new DateTime();
		}
		else {
			$date = clone $date;
	 	}
		$time = DateTime::createFromFormat('H:i:s' , $time);
		if(!is_bool($time)){
			$date->setTime($time->format('H'), $time->format('i'), $time->format('s'));
		}
		return $date;
	}


	/**
	 * \fn createAppointmentWithServiceParameters
	 * \brief create appointments with service parameters json
	 */
	public static function createAppointmentsWithServiceParameters($servicesParameters, $customer, $noConstraints, $idBooking, $isBackEnd, $typeAccount){
		$appointments = [];
		if(count($servicesParameters) > 0){
			$cacheTimeslots = [];
			foreach($servicesParameters as $serviceParameters){
				$service = new RESA_Service();
				if(isset($serviceParameters->service)){
					$service->loadById($serviceParameters->service->id);
					$startDate = DateTime::createFromFormat('d-m-Y H:i:s', $serviceParameters->dateStart);
					$endDate = DateTime::createFromFormat('d-m-Y H:i:s', $serviceParameters->dateEnd);
					$timeslot = RESA_Algorithms::getTimeslot($startDate, $endDate, $service, $appointments, $idBooking, $isBackEnd, $typeAccount);
					$startDate = $startDate->format('Y-m-d H:i:s');
					$endDate = $endDate->format('Y-m-d H:i:s');
					$statesParameters = unserialize(get_option('resa_settings_states_parameters'));
					if($noConstraints || isset($timeslot)){
						$appointment = new RESA_Appointment();
						$appointment->setIdBooking($idBooking);
						$appointment->setIdClientObject($serviceParameters->id);
						$appointment->setIdService($service->getId());
						$appointment->setIdPlace($serviceParameters->place);
						$appointment->setStartDate($startDate);
						$appointment->setEndDate($endDate);
						$appointment->setNoEnd($serviceParameters->noEnd);
						$appointment->setInternalIdLink($serviceParameters->idServiceParametersLink);
						if($isBackEnd){
							if($serviceParameters->update){
								$appointment->setState('updated');
							}
							if($serviceParameters->state == 'cancelled'){
								$appointment->setState('cancelled');
							}
							else if($serviceParameters->state == 'abandonned'){
								$appointment->setState('abandonned');
							}
							else if($serviceParameters->state == 'waiting'){
								$appointment->setState('waiting');
							}

							if(isset($timeslot) && $timeslot['idParameter']!=-1){
								$parameter = RESA_Tools::getParameterById($statesParameters, $timeslot['idParameter']);
								if(isset($parameter)){
									$appointment->setIdParameter($parameter->id);
								}
							}
						}
						else if(isset($timeslot) && $timeslot['idParameter']!=-1){
							$parameter = RESA_Tools::getParameterById($statesParameters, $timeslot['idParameter']);
							if(isset($parameter)){
								$appointment->setState($parameter->state);
								$appointment->setIdParameter($parameter->id);
							}
						}
						$appointmentTotalNumber = 0;
						$appointmentTotalPriceWithoutReduction = 0;
						//Add RESA_AppointmentNumberPrice
						$allSlugs = array();
						foreach($serviceParameters->numberPrices as $numberPrice){
							if(isset($numberPrice) && isset($numberPrice->price) && isset($numberPrice->number)){
								array_push($allSlugs, $numberPrice->price->slug);
							}
						}
						foreach($serviceParameters->numberPrices as $numberPrice){
							if(isset($numberPrice) && isset($numberPrice->price) && isset($numberPrice->number)){
								$servicePrice = $service->getServicePriceById($numberPrice->price->id);
								if(isset($servicePrice) &&
									$servicePrice->isActivated() &&
									floor($numberPrice->number) > 0 &&
									($servicePrice->isTypeAccountOk($customer->getTypeAccount()) || $noConstraints) &&
									((isset($timeslot['idsServicePrices']) && (in_array($servicePrice->getId(), $timeslot['idsServicePrices']) || count($timeslot['idsServicePrices']) == 0) || $noConstraints))  &&
									(((($servicePrice->isActivateMinQuantity() && $numberPrice->number >= $servicePrice->getMinQuantity()) || !$servicePrice->isActivateMinQuantity()) &&
									(($servicePrice->isActivateMaxQuantity() && $numberPrice->number <= $servicePrice->getMaxQuantity()) || !$servicePrice->isActivateMaxQuantity())) || $noConstraints) &&
									($servicePrice->haveEnoughEquipments($timeslot['equipments'], $numberPrice->number) || $noConstraints)){

									/*
										$appointmentNumberPrice = $appointment->getAppointmentNumberPriceByIdPrice($numberPrice->price->id);
										if(!isset($appointmentNumberPrice)){
											$appointmentNumberPrice = new RESA_AppointmentNumberPrice();
											$appointmentNumberPrice->setIdPrice($servicePrice->getId());
											$appointmentNumberPrice->setNumber($numberPrice->number);
										}
									*/
									$appointmentNumberPrice = new RESA_AppointmentNumberPrice();
									$appointmentNumberPrice->setIdPrice($servicePrice->getId());
									$appointmentNumberPrice->setNumber($numberPrice->number);
									$appointmentNumberPrice->setTotalPrice($servicePrice->getTotalPrice($appointmentNumberPrice->getNumber(), $appointment->getHours()));
									$appointmentNumberPrice->setDeactivated(!$appointment->canCalculatePrice($service, $appointmentNumberPrice, $allSlugs));
									if(isset($timeslot['equipments'])){
										$timeslot['equipments'] = $servicePrice->useEquipments($timeslot['equipments'], $numberPrice->number);
									}
									if($appointment->canCalculatePrice($service, $appointmentNumberPrice, $allSlugs)) {
										$appointmentTotalPriceWithoutReduction += $appointmentNumberPrice->getTotalPrice();
									}
									if(!$servicePrice->isExtra()){
										$appointmentTotalNumber += $numberPrice->number;
									}
									if($service->isAskParticipants()){
										$participantsParameter = RESA_Tools::getParticipantsParameter($servicePrice->getParticipantsParameter());
										if($participantsParameter != null){
											$participants = $numberPrice->participants;
											if(count($participants) > $numberPrice->number){
												$participants = array_slice($participants, 0, $numberPrice->number);
											}
											foreach($participants as $participant){
												foreach($participantsParameter->fields as $field){
													if(isset($participant->{$field->varname}) && isset($field->mandatory) && $field->mandatory){
														$value = $participant->{$field->varname};
														if((is_null($value) || empty($value) || (is_numeric($value) && $value <= 0)) && !$noConstraints){
															throw new Exception(__('need_participants_informations_error', 'resa'));
														}
														/*
														else if($field->type == 'select' && is_bool(strpos($field->options, $value))){
															Logger::DEBUG($value . ' not present - ' . print_r($field->options, true). ' ' . strpos($field->options, $value));
														}
														*/
													}
													else if(isset($field->mandatory) && $field->mandatory && !$noConstraints) throw new Exception(__('need_participants_informations_error', 'resa'));
												}
											}
											$appointmentNumberPrice->addParticipants($participants);
											if($numberPrice->number != $appointmentNumberPrice->getNumberParticipants() && !$noConstraints){
												throw new Exception(__('need_participants_informations_error', 'resa'));
											}
										}
									}
									$appointmentNumberPrices = $appointment->getAppointmentNumberPrices();
									array_push($appointmentNumberPrices, $appointmentNumberPrice);
									$appointment->setAppointmentNumberPrices($appointmentNumberPrices);
								}
								else if(!isset($servicePrice)){
									throw new RESA_Exception(__('save_booking_error_unknown_price', 'resa'), array('id'=>$serviceParameters->id, 'text' => __('save_booking_error_unknown_price', 'resa')));
								}else if(!$servicePrice->isActivated()){
									throw new RESA_Exception(__('save_booking_error_price_not_activated', 'resa'), array('id'=>$serviceParameters->id, 'text' => __('save_booking_error_price_not_activated', 'resa')));
								}else if(!$servicePrice->isTypeAccountOk($customer->getTypeAccount())){
									throw new RESA_Exception(__('save_booking_error_price_not_for_type_account', 'resa'), array('id'=>$serviceParameters->id, 'text' => __('save_booking_error_price_not_for_type_account', 'resa')));
								}else if(!$servicePrice->haveEnoughEquipments($timeslot['equipments'], $numberPrice->number)){
										throw new RESA_Exception(__('not_enough_equipments', 'resa'), array('id'=>$serviceParameters->id, 'text' => __('not_enough_equipments', 'resa')));
								}else if(isset($timeslot['idsServicePrices']) && !in_array($servicePrice->getId(), $timeslot['idsServicePrices']) && count($timeslot['idsServicePrices']) > 0 && !$noConstraints){
									throw new RESA_Exception(__('save_booking_error_price_unavailable', 'resa'), 		array('id'=>$serviceParameters->id, 'text' => __('save_booking_error_price_unavailable', 'resa')));
								}
								else if(floor($numberPrice->number) <= 0) {
									throw new RESA_Exception(__('save_booking_error_number_price', 'resa'), array('id'=>$serviceParameters->id, 'text' => __('save_booking_error_number_price', 'resa')));
								}
								else {
									throw new RESA_Exception(__('save_booking_error_number_price', 'resa'), array('id'=>$serviceParameters->id, 'text' => __('save_booking_error_number_price', 'resa')));
								}
							}
						}
						if(!$noConstraints && !$service->haveAllMandatoryPrices($appointment)){
							throw new RESA_Exception(__('no_mandatory_price_error_number_price', 'resa'), array('id'=>$serviceParameters->id, 'text' => __('no_mandatory_price_error_number_price', 'resa')));
						}
						$appointment->setNumbers($appointmentTotalNumber);
						$appointment->setTotalPriceWithoutReduction($appointmentTotalPriceWithoutReduction);
						$appointment->setTags($serviceParameters->tags);
						$maxCapacityReach = false;
						$capacity = 0;
						if(!$noConstraints){
							if($timeslot['typeCapacity'] == RESA_ServiceTimeslot::$CAPACITY_MEMBERS){
								$capacity = $timeslot['capacityMembers'] - $timeslot['usedCapacity'];
							}
							else {
								$capacity = $timeslot['capacity'] - $timeslot['usedCapacity'];
							}
							$maxCapacityReach = ($appointmentTotalNumber > $capacity);
							if($maxCapacityReach && !$noConstraints && (!$timeslot['overCapacity'] || $timeslot['overCapacity'] && $capacity <= 0)){
								throw new RESA_Exception(__('save_booking_error_max_capacity_reach', 'resa'), array('id'=>$serviceParameters->id, 'text' => __('save_booking_error_max_capacity_reach', 'resa')));
							}
							else if($appointmentTotalNumber == 0 && !$noConstraints){
								throw new RESA_Exception(__('error_no_price_list_capacity_reach', 'resa'), array('id'=>$serviceParameters->id, 'text' => __('error_no_price_list_capacity_reach', 'resa')));
							}
						}

						//Add RESA_AppointmentMember
						if($noConstraints || ($timeslot['typeCapacity'] == RESA_ServiceTimeslot::$CAPACITY_MEMBERS || !$timeslot['noStaff']) && RESA_Variables::isStaffManagementActivated()){
							$membersIdUsed = array(); //Members id in timelots
							if(is_array($timeslot['membersUsed'])){
								foreach($timeslot['membersUsed'] as $memberUsed){
									if(!in_array($memberUsed['id'], $membersIdUsed)){
										array_push($membersIdUsed, $memberUsed['id']);
									}
								}
							}

							$membersAlreadyUsed = array();
							$membersId = array();
							$membersNoConstaints = array();
							foreach($serviceParameters->staffs as $member){
								if(isset($member) && !in_array($member->id, $membersId)){
									array_push($membersId, $member->id);
									array_push($membersNoConstaints,
										array('id'=>$member->id,
													'nickname'=>$member->nickname,
													'capacity'=>999999,
													'usedCapacity'=>$member->usedCapacity));
								}
							}
							foreach($service->getServiceMemberPriorities() as $serviceMemberPriority){
								if(!in_array($serviceMemberPriority->getIdMember(), $membersId) && in_array($serviceMemberPriority->getIdMember(), $membersIdUsed)){
									array_push($membersId, $serviceMemberPriority->getIdMember());
								}
							}
							if(is_array($timeslot['membersUsed'])){
								foreach($timeslot['membersUsed'] as $memberUsed){
									if(!in_array($memberUsed['id'], $membersId)){
										array_push($membersId, $memberUsed['id']);
									}
								}
							}
							foreach($membersId as $memberId){
								if($appointmentTotalNumber > 0)	{
									$index = 0;
									$membersUsed = null;
									while($index < count($timeslot['membersUsed']) && !isset($membersUsed)){
										if($timeslot['membersUsed'][$index]['id'] == $memberId){
											$membersUsed = $timeslot['membersUsed'][$index];
										}
										else $index++;
									}
									if(!isset($membersUsed)){
										if(!$noConstraints){
											throw new Exception('Unknown member');
										}
										else {
											$index = 0;
											$membersUsed = null;
											while($index < count($membersNoConstaints) && !isset($membersUsed)){
												if($membersNoConstaints[$index]['id'] == $memberId){
													$membersUsed = $membersNoConstaints[$index];
												}
												else $index++;
											}
											if(!isset($membersUsed)){
												throw new Exception('Unknown member no constraints');
											}
										}
									}
									if(isset($membersUsed)) {
										$memberCapacity = $membersUsed['capacity'] - $membersUsed['usedCapacity'];
										if($memberCapacity > 0){
											$number = $appointmentTotalNumber;
											if($number > $memberCapacity)
												$number = $memberCapacity;
											$appointmentMember = new RESA_AppointmentMember();
											$appointmentMember->setIdMember($membersUsed['id']);
											$appointmentMember->setNumber($number);
											$appointmentTotalNumber-=$number;

											$appointmentMembers = $appointment->getAppointmentMembers();
											array_push($appointmentMembers, $appointmentMember);
											$appointment->setAppointmentMembers($appointmentMembers);
										}
										array($membersAlreadyUsed, $membersUsed['id']);
									}
								}
							}
							if(($appointmentTotalNumber != 0 && $timeslot['typeCapacity'] == RESA_ServiceTimeslot::$CAPACITY_MEMBERS) && !$noConstraints && (!$timeslot['overCapacity'] || $timeslot['overCapacity'] && $capacity <= 0))
								 throw new Exception(__('save_booking_error_person_attribution', 'resa'));
						}
						array_push($appointments, $appointment);
					}
					else throw new Exception(__('save_booking_error_timeslot_not_found','resa'));
				}
			}
		}
		else throw new Exception(__('save_booking_error_empty_form','resa'));
		usort($appointments, array("RESA_Appointment", "compare"));
		return $appointments;
	}

	/**
	 * Verify the capacity for timeslot of appointment
	 */
	public static function verifyCapacity($appointment){
		$service = new RESA_Service();
		$service->loadById($appointment->getIdService());
		$dateStart = RESA_Tools::convertStringToDate($appointment->getStartDate());
		$dateEnd = RESA_Tools::convertStringToDate($appointment->getEndDate());

		$timeslotsAlerts = RESA_Alert::getAllData(array(
			'idType' => RESA_Alert::$TIMESLOT_ALERT,
			'idAppointment' => $appointment->getId()));

		if($service->haveTimeslot($dateStart, $dateEnd)){
			$timeslot = self::getTimeslot(
				RESA_Tools::convertStringToDate($appointment->getStartDate()),
				RESA_Tools::convertStringToDate($appointment->getEndDate()),
																				$service, array(), -1, true, 'type_account_0');
			if(count($timeslotsAlerts) > 0){
				foreach($timeslotsAlerts as $timeslotsAlert){
					$timeslotsAlert->deleteMe();
				}
			}
			/***
			 ** Capacity Alert
			**/
			$capacityAlerts = RESA_Alert::getAllData(array(
				'idType' => RESA_Alert::$CAPACITY_ALERT,
				'idService' => $appointment->getIdService(),
				'startDate' =>$appointment->getStartDate(),
				'endDate' =>$appointment->getEndDate()));
			$capacity = $timeslot['capacity'];
			if($timeslot['typeCapacity'] == RESA_ServiceTimeslot::$CAPACITY_MEMBERS){
				$capacity = $timeslot['capacityMembers'];
			}
			$capacity = $capacity - $timeslot['usedCapacity'];
			if($capacity < 0){
				if(count($capacityAlerts) == 0){
					$alert = RESA_Alert::generateAlert(RESA_Variables::getAlertModels()[RESA_Alert::$CAPACITY_ALERT]);
					$alert->setIdService($appointment->getIdService());
					$alert->setStartDate($appointment->getStartDate());
					$alert->setEndDate($appointment->getEndDate());
					Logger::INFO('Add new alert capacity');
					$alert->save();
				}
			}
			else if(count($capacityAlerts) > 0){
				Logger::INFO('Delete alerts');
				foreach($capacityAlerts as $alert){
					$alert->deleteMe();
				}
			}
			/***
			 ** EQUIPMENTS ALERT
			**/
			$equipmentsAlerts = RESA_Alert::getAllData(array(
				'idType' => RESA_Alert::$EQUIPMENT_ALERT,
				'idService' => $appointment->getIdService(),
				'startDate' =>$appointment->getStartDate(),
				'endDate' =>$appointment->getEndDate()));
			$equipments = false;
			foreach($timeslot['equipments'] as $equipment){
				if(isset($equipment['maxReach']) && $equipment['maxReach']){
					$equipments = true;
				}
			}
			if($equipments){
				if(count($equipmentsAlerts) == 0){
					$alert = RESA_Alert::generateAlert(RESA_Variables::getAlertModels()[RESA_Alert::$EQUIPMENT_ALERT]);
					$alert->setIdService($appointment->getIdService());
					$alert->setStartDate($appointment->getStartDate());
					$alert->setEndDate($appointment->getEndDate());
					Logger::INFO('Add new alert equipments');
					$alert->save();
				}
			}
			else if(count($equipmentsAlerts) > 0){
				Logger::INFO('Delete alerts');
				foreach($equipmentsAlerts as $alert){
					$alert->deleteMe();
				}
			}
		}
		else if(!$appointment->isDatePassed()){
			if(count($timeslotsAlerts) == 0){
				$timeslotsAlert = RESA_Alert::generateAlert(RESA_Variables::getAlertModels()[RESA_Alert::$TIMESLOT_ALERT]);
				$timeslotsAlert->setIdAppointment($appointment->getId());
				$timeslotsAlert->setStartDate($appointment->getStartDate());
				$timeslotsAlert->setEndDate($appointment->getEndDate());
				Logger::INFO('Add new alert timeslot');
				$timeslotsAlert->save();
			}
		}
	}

	public static function verifyReductions($booking){
		$promoCodes = $booking->getPromoCodes();
		$appointments= [];
		foreach($booking->getAppointments() as $appointment){
			$appointmentCloned = $appointment->cloneMe();
			$appointmentCloned->setIdClientObject($appointmentCloned->getId());
			array_push($appointments, $appointmentCloned);
		}
		$customer = new RESA_Customer();
		$customer->loadById($booking->getIdCustomer());

		$result = self::getReductionsApplicable($appointments, $customer, '');
		$allReductions = $result['reductions'];
		$mapIdClientObjectReduction = $result['mapIdClientObjectReduction'];

		foreach($promoCodes as $promoCode){
			$result = self::getReductionsApplicable($appointments, $customer, $promoCode);
			$allReductions = array_merge($allReductions, $result['reductions']);
			foreach($result['mapIdClientObjectReduction'] as $id => $params){
				if(!isset($mapIdClientObjectReduction[$id])){
					$mapIdClientObjectReduction[$id] = [];
				}
				$mapIdClientObjectReduction[$id] = array_merge($mapIdClientObjectReduction[$id], $params);
			}
		}
		$newBooking = RESA_Algorithms::fillBookingReductions($appointments, $allReductions, $mapIdClientObjectReduction);
		$alerts = RESA_Alert::getAllData(array(
			'idType' => RESA_Alert::$REDUCTIONS_ALERT,
			'idBooking' => $booking->getId()));
		if(!$booking->haveSameReductions($newBooking)){
			if(count($alerts) == 0){
				$alert = RESA_Alert::generateAlert(RESA_Variables::getAlertModels()[RESA_Alert::$REDUCTIONS_ALERT]);
				$alert->setIdBooking($booking->getId());
				Logger::INFO('Add new alert reduction');
				$alert->save();
			}
		}
		else if(count($alerts) > 0){
			Logger::INFO('Delete alerts');
			foreach($alerts as $alert){
				$alert->deleteMe();
			}
		}
	}

	/**
	 * return the reductions applicable
	 */
	public static function getReductionsApplicable($appointments, RESA_Customer $customer, $promoCode, $isBackEnd = true){
		$mapIdReductionIntervals = array();
		$allReductions = RESA_Reduction::getAllData(array('activated'=>true, 'oldReduction'=>false));
		$reductions = array();
		foreach($allReductions as $reduction){
			if($reduction->getVisibility() == 0 ||
				($reduction->getVisibility() == 1 && $isBackEnd) ||
				($reduction->getVisibility() == 2 && !$isBackEnd) ){
				array_push($reductions, $reduction);
			}
		}
		$reductionsApplicable = array();
		foreach($reductions as $reduction){
			if((!$reduction->isNeedCouponCode() && empty($promoCode)) || (isset($promoCode) && !empty($promoCode) && $reduction->isNeedCouponCode())){
				$reductionConditionsElement = $reduction->getReductionConditionsList();
				foreach($reductionConditionsElement as $reductionConditions){
					$newAppointments = self::regroupAppointmentsByType($appointments, $reductionConditions->getType(), $reductionConditions->isMerge());
					$ifGlobalConditions = count($newAppointments) == 1; //@deprecated
					foreach($newAppointments as $localAppointments){
						$totalPrice = self::totalPrice($localAppointments);
						$result = self::getReductionConditionsApplicableOnServiceParameters($reduction, $localAppointments, $reductionConditions, $ifGlobalConditions, $totalPrice, $customer, $promoCode);
						if($result['result']){
							if(isset($mapIdReductionIntervals[$reduction->getId()])){
								$mapIdReductionIntervals[$reduction->getId()] = array_merge($mapIdReductionIntervals[$reduction->getId()],
								$result['intervals']);
							}
							else {
								array_push($reductionsApplicable, $reduction);
								$mapIdReductionIntervals[$reduction->getId()] = $result['intervals'];
							}
						}

					}
				}
			}
		}
		/**
		 * result.
		 */
		$mapIdClientObjectReduction = array();
		foreach($reductionsApplicable as $reduction){
			foreach($reduction->getReductionApplications() as $reductionApplication){
				if($reductionApplication->getApplicationType() == 2){
					foreach($reductionApplication->getReductionConditionsApplicationList() as $reductionConditionsApplication){
						foreach($appointments as $appointment){
							foreach($reductionConditionsApplication->getReductionConditionsApplications() as $reductionConditionApplication){
								$array = self::numberAndPromoCodesOnSameDateOrSameAppointment($appointment,
									$mapIdReductionIntervals[$reduction->getId()],$reductionApplication->getApplicationTypeOn());
								$number = $array['number'];
								if($number > 0){ //Same date of same appointment
									$result = self::checkReductionConditionApplication($reductionConditionApplication, $appointment);
									if($result['conditionOk']) {
										$idClientObject = 'id'.$appointment->getIdClientObject();
										if(!isset($mapIdClientObjectReduction[$idClientObject])){
											$mapIdClientObjectReduction[$idClientObject] = [];
										}
										if($result['number'] != 0){
											$number = min($number, $result['number']);
										}
										if($number > 0){
											array_push($mapIdClientObjectReduction[$idClientObject],
												array('idReduction'=>$reduction->getId(),
														'type'=>$reductionApplication->getType(),
														'value'=>$reductionApplication->getValue(),
														'vatAmount'=>$reductionApplication->getVatAmount(),
														'number'=>$reductionApplication->isOnlyOne()?1:$number,
														'idsPrice'=>$result['idsPrice'],
														'promoCode' => $array['promoCode']));
										}
									}
								}
							}
						}
					}
				}
				else if($reductionApplication->getApplicationType() == 1){
					foreach($mapIdReductionIntervals[$reduction->getId()] as $interval){
						if(isset($interval['idAppointments'])){
							foreach($interval['idAppointments'] as $idClientObject){
								if($interval['number'] > 0){
									$idClientObject = 'id'.$idClientObject;
									if(!isset($mapIdClientObjectReduction[$idClientObject]))
										$mapIdClientObjectReduction[$idClientObject] = [];
									array_push($mapIdClientObjectReduction[$idClientObject],
										array('idReduction'=>$reduction->getId(),
												'type'=>$reductionApplication->getType(),
												'value'=>$reductionApplication->getValue(),
												'vatAmount'=>$reductionApplication->getVatAmount(),
												'number'=> $reductionApplication->isOnlyOne()?1:$interval['number'],
												'idsPrice' => $interval['idsPrice'][$idClientObject],
												'promoCode' => $interval['promoCode']));
								}
							}
						}
					}
				}
				else {
					$number = 0;
					$promoCode = '';
					foreach($mapIdReductionIntervals[$reduction->getId()] as $interval){
						$number += $interval['number'];
						if(empty($promoCode)) $promoCode = $interval['promoCode'];
					}
					if($number > 0){
						$idClientObject = 'id0';
						if(!isset($mapIdClientObjectReduction[$idClientObject]))
							$mapIdClientObjectReduction[$idClientObject] = [];
							array_push($mapIdClientObjectReduction[$idClientObject],
								array('idReduction'=>$reduction->getId(),
										'type'=>$reductionApplication->getType(),
										'value'=>$reductionApplication->getValue(),
										'vatAmount'=>$reductionApplication->getVatAmount(),
										'number' => $reductionApplication->isOnlyOne()?1:$number,
										'promoCode' => $promoCode ));
					}
				}
			}
		}
		return array('reductions'=>$reductionsApplicable, 'mapIdClientObjectReduction' => $mapIdClientObjectReduction);
	}

	/**
	 * regroup appointments with the type (0 => all booking, 1 => same date, 2=> same appointment, 3 => all bookings)
	 */
	public static function regroupAppointmentsByType($appointments, $typeReductionConditions, $merge){
		$newAppointments = [];
		if($typeReductionConditions == 0){
			$localAppointments = [];
			foreach($appointments as $appointment){
				if(!$appointment->isCancelled()){
					$appointment = $appointment->cloneMe();
					$found = false;
					if($merge){
						foreach($localAppointments as $newAppointment){
							if($newAppointment->getIdService() == $appointment->getIdService()){
								$newAppointment->mergeAppointment($appointment);
								$found = true;
							}
						}
					}
					if(!$found){
						array_push($localAppointments, $appointment);
					}
				}
			}
			array_push($newAppointments, $localAppointments);
		}
		else if($typeReductionConditions == 1){
			foreach($appointments as $appointment){
				if(!$appointment->isCancelled()){
					$appointment = $appointment->cloneMe();
					$foundSameDate = false;
					for($i = 0; $i < count($newAppointments); $i++){
						$localAppointments = $newAppointments[$i];
						if($localAppointments[0]->isSameDate($appointment)){
							$foundSameDate = true;
							$found = false;
							if($merge){
								foreach($localAppointments as $newAppointment){
									if($newAppointment->getIdService() == $appointment->getIdService()){
										$newAppointment->mergeAppointment($appointment);
										$found = true;
									}
								}
							}
							if(!$found){
								array_push($newAppointments[$i], $appointment);
							}
						}
					}
					if(!$foundSameDate) {
						array_push($newAppointments, [$appointment]);
					}
				}
			}
		}
		else if($typeReductionConditions == 2){
			foreach($appointments as $appointment){
				if(!$appointment->isCancelled()){
					$appointment = $appointment->cloneMe();
					array_push($newAppointments, [$appointment]);
				}
			}
		}
		else if($typeReductionConditions == 3){
			$customer = new RESA_Customer();
			$customer->loadCurrentUser();
			if($customer->isLoaded()){
				foreach($customer->getBookings() as $booking){
					if($booking->isOk()){
						foreach($booking->getAppointments() as $appointment){
							if(!$appointment->isCancelled()){
								array_push($appointments, $appointment);
							}
						}
					}
				}
			}
			//Same code of 1
			$localAppointments = [];
			foreach($appointments as $appointment){
				if(!$appointment->isCancelled()){
					$appointment = $appointment->cloneMe();
					$found = false;
					if($merge){
						foreach($localAppointments as $newAppointment){
							if($newAppointment->getIdService() == $appointment->getIdService()){
								$newAppointment->mergeAppointment($appointment);
								$found = true;
							}
						}
					}
					if(!$found){
						array_push($localAppointments, $appointment);
					}
				}
			}
			array_push($newAppointments, $localAppointments);
		}
		return $newAppointments;
	}

	/**
	 * return the total price in fonction of appointments and typeReductionConditions
	 */
	public static function totalPrice($appointments){
		$totalPrice = 0;
		foreach($appointments as $appointment){
			$service = new RESA_Service();
			$service->loadById($appointment->getIdService());
			foreach($appointment->getAppointmentNumberPrices() as $numberPrice){
				$price = $service->getServicePriceById($numberPrice->getIdPrice());
				if(isset($price)){
					$totalPrice += $numberPrice->getNumber() * $price->getPrice();
				}
			}
		}
		return $totalPrice;
	}


	/**
	 * return true if appointment is on same date or same appointment in of type
	 */
		public static function numberAndPromoCodesOnSameDateOrSameAppointment($appointment, $intervals, $type){
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getStartDate());
			$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getEndDate());
			$number = 0;
			$promoCode = '';
			foreach($intervals as $interval){
				if($type == 0){
					if(RESA_Tools::resetTimeToDate($startDate) == RESA_Tools::resetTimeToDate($interval['startDate'])){
						$number += $interval['number'];
						if(empty($promoCode)) $promoCode = $interval['promoCode'];
					}
				}
				else if($type == 1) {
					if($startDate == $interval['startDate'] && $endDate == $interval['endDate']){
						$number += $interval['number'];
						if(empty($promoCode)) $promoCode = $interval['promoCode'];
					}
				}
				else if($type == 2) {
					$number += $interval['number'];
					if(empty($promoCode)) $promoCode = $interval['promoCode'];
				}
			}
			return array('number' => $number, 'promoCode' => $promoCode);
		}

	/**
	 *
	 */
	public static function returnReductionsNotCombinables($appointments, $reductions, $mapIdClientObjectReduction){
		$reductionsNotCombinables = array();
		$reductionsCombinables = array();
		/* Simple algo*/
		foreach($reductions as $reduction){
			if(!$reduction->isCombinable()){
				array_push($reductionsNotCombinables, [$reduction]);
			}
			else array_push($reductionsCombinables, $reduction);
		}
		array_push($reductionsNotCombinables, $reductionsCombinables);

		$index = 0;
		$booking = self::fillBookingReductions($appointments, $reductionsNotCombinables[$index], $mapIdClientObjectReduction);
		$minTotalPrice = $booking->getTotalPrice();
		for($i = 1; $i < count($reductionsNotCombinables); $i++){
			$booking = self::fillBookingReductions($appointments, $reductionsNotCombinables[$i], $mapIdClientObjectReduction);
			if($booking->getTotalPrice() < $minTotalPrice){
				$minTotalPrice = $booking->getTotalPrice();
				$index = $i;
			}
		}
		return $reductionsNotCombinables[$index];
	}


	/**
	 *
	 */
	public static function fillBookingReductions($appointments, $reductions, $mapIdClientObjectReduction){
		$booking = new RESA_Booking();
		$booking->setAppointments($appointments);

		$bookingReductions = array();
		if(isset($mapIdClientObjectReduction['id0'])){
			foreach($mapIdClientObjectReduction['id0'] as $params){
				$bookingReduction = new RESA_BookingReduction();
				$bookingReduction->setIdReduction($params['idReduction']);
				$bookingReduction->setType($params['type']);
				$bookingReduction->setValue($params['value']);
				$bookingReduction->setVatAmount($params['vatAmount']);
				$bookingReduction->setNumber($params['number']);
				$bookingReduction->setPromoCode($params['promoCode']);
				array_push($bookingReductions, $bookingReduction);
			}
		}
		$booking->setBookingReductions($bookingReductions);
		foreach($mapIdClientObjectReduction as $id => $allParams){
			if($id != 'id0'){
				$idClientObject =  substr($id, 2);
				$appointment = RESA_Tools::getAppointmentByIdClientObject($idClientObject, $appointments);
				if(isset($appointment)){
					$appointmentReductions = array();
					foreach($allParams as $params){
						if(count($params['idsPrice']) > 0){
							foreach($params['idsPrice'] as $idPrice){
								if($idPrice != -1){
									$appointmentReduction = new RESA_AppointmentReduction();
									$appointmentReduction->setIdReduction($params['idReduction']);
									$appointmentReduction->setIdPrice($idPrice);
									$appointmentReduction->setType($params['type']);
									$appointmentReduction->setValue($params['value']);
									$appointmentReduction->setVatAmount($params['vatAmount']);
									$appointmentReduction->setNumber($params['number']);
									$appointmentReduction->setPromoCode($params['promoCode']);
									array_push($appointmentReductions, $appointmentReduction);
								}
							}
						}else {
							$appointmentReduction = new RESA_AppointmentReduction();
							$appointmentReduction->setIdReduction($params['idReduction']);
							$appointmentReduction->setIdPrice(-1);
							$appointmentReduction->setType($params['type']);
							$appointmentReduction->setValue($params['value']);
							$appointmentReduction->setNumber($params['number']);
							$appointmentReduction->setPromoCode($params['promoCode']);
							array_push($appointmentReductions, $appointmentReduction);
						}
					}
					$appointment->setAppointmentReductions($appointmentReductions);
				}
			}
		}
		return $booking;
	}

	/**
	 * $ifGlobalConditions : not count intervals
	 */
	public static function getReductionConditionsApplicableOnServiceParameters(RESA_Reduction $reduction, $appointments, $reductionConditions, $ifGlobalConditions, $totalPrice, RESA_Customer $customer, $promoCode){
		$intervals = array();
		$conditionsOk = true;
		$reductionConditions = $reductionConditions->getReductionConditions();
		foreach($reductionConditions as $reductionCondition){
			$results = self::checkReductionCondition2($reduction, $reductionCondition, $appointments, $totalPrice, $customer, $promoCode);
			$conditionsOk = $conditionsOk && $results['conditionsOk'];
			if($results['conditionsOk']){
				$intervals = array_merge($intervals, $results['intervals']);
			}
		}
		return array('result'=>$conditionsOk, 'intervals'=>$intervals);
	}

	/**
	 * Merge all intervals
	 * return a array with one element
	 */
	public static function mergeAllIntervals($intervals){
		$newIntervals = array();
		if(count($intervals) > 0){
			$promoCodes = array();
			$localInterval = $intervals[0];
			for($i = 1; $i < count($intervals); $i++){
				$interval = $intervals[$i];
				if(RESA_Tools::resetTimeToDate($interval['startDate']) < RESA_Tools::resetTimeToDate($localInterval['startDate'])){
					$localInterval['startDate'] = $interval['startDate'];
				}
				if(RESA_Tools::resetTimeToDate($interval['endDate']) > RESA_Tools::resetTimeToDate($localInterval['endDate'])){
					$localInterval['endDate'] = $interval['endDate'];
				}
				if($interval['number'] < $localInterval['number']){
					$localInterval['number'] = $interval['number'];
				}
				if($interval['promoCode'] != $localInterval['promoCode']){
					array_push($promoCodes, $interval['promoCode']);
				}
				foreach($interval['idsPrice'] as $key => $value){
					if(isset($localInterval['idsPrice'][$key])){
						foreach($value as $id){
							if(!in_array($id, $localInterval['idsPrice'][$key])){
								array_push($localInterval['idsPrice'][$key], $id);
							}
						}
					}
					else $localInterval['idsPrice'] = array_merge($localInterval['idsPrice'], $interval['idsPrice']);
				}
				foreach($interval['totalNumber'] as $key => $value){
					if(isset($localInterval['totalNumber'][$key])){
						//$localInterval['totalNumber'][$key] += $value;
					}
					else $localInterval['totalNumber'] = array_merge($localInterval['totalNumber'], $interval['totalNumber']);
				}
				foreach($interval['idAppointments'] as $value){
					if(!in_array($value, $localInterval['idAppointments'])){
						array_push($localInterval['idAppointments'], $value);
					}
				}
			}
			array_push($newIntervals, $localInterval);
			foreach($promoCodes as $promoCode){
				array_push($newIntervals, array('startDate'=>$localInterval['startDate'], 'endDate'=>$localInterval['endDate'], 'number' => 1, 'promoCode'=>$promoCode));
			}
		}
		return $newIntervals;
	}


	public static function checkReductionCondition2(RESA_Reduction $reduction, RESA_ReductionCondition $reductionCondition, $appointments, $totalPrice, RESA_Customer $customer, $promoCode){
		$conditionsOk = true;
		if(count($appointments) > 0){
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointments[0]->getStartDate());
			$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointments[0]->getEndDate());
		}
		else {
			$startDate = new DateTime();
			$endDate = new DateTime();
		}
		$number = 0;
		$returnPromoCode = '';
		$intervals = null;
		if($reductionCondition->getType() == 'services'){
			$notEquals = self::servicesNotEquals($reductionCondition->getReductionConditionServicesNotEquals(), $appointments);
			$conditionsOk = $conditionsOk && $notEquals;
			if($conditionsOk){
				$reductionConditionService = $reductionCondition->getReductionConditionServicesWithoutNotEquals();
				usort($reductionConditionService, array("RESA_ReductionConditionService", "compare"));
				$res = self::servicesSatisfaction($reductionConditionService, $appointments, array());
				//Logger::DEBUG(print_r($res, true));
				$res = self::methodReductionConditionTypeService($reductionCondition, $res);
				$res = self::uniqueless($res, count($reductionCondition->getReductionConditionServicesWithoutNotEquals()));
				//Logger::DEBUG(print_r($res, true));
				$conditionsOk = $conditionsOk && (count($res) > 0);
				$intervals = $res;
			}
		}
		else if($reductionCondition->getType()=='code'){
			if($reductionCondition->getParam1() == 0) $conditionsOk = $conditionsOk && $promoCode == $reductionCondition->getParam2();
			else if($reductionCondition->getParam1() == 1) $conditionsOk = $conditionsOk && $promoCode !=  $reductionCondition->getParam2();
			if($reductionCondition->getParam3() == true){
				$alreadyUsed = 0;
				$alreadyUsed += RESA_AppointmentReduction::numberOfPromoCodeUsed($reductionCondition->getParam2(), $appointments[0]->getIdBooking());
				$alreadyUsed += RESA_BookingReduction::numberOfPromoCodeUsed($reductionCondition->getParam2(), $appointments[0]->getIdBooking());
				$conditionsOk = $conditionsOk && $alreadyUsed < $reductionCondition->getParam4();
			}
			if($conditionsOk){
				$returnPromoCode = $reductionCondition->getParam2();
			}
		}
		else if($reductionCondition->getType()=='amount'){
			if($reductionCondition->getParam1() == 1) $conditionsOk = $conditionsOk && $totalPrice == $reductionCondition->getParam2();
			else if($reductionCondition->getParam1() == 0) $conditionsOk = $conditionsOk && $totalPrice >= $reductionCondition->getParam2();
			else $conditionsOk = $conditionsOk && $totalPrice < $reductionCondition->getParam2();
		}
		else if($reductionCondition->getType()=='registerDate'){
			$actualDate = new DateTime();
			$actualDate->setTimestamp(current_time('timestamp'));
			if(!empty($reductionCondition->getParam2())){
				$date = new DateTime($reductionCondition->getParam2());
				$date = self::timeToDate($reductionCondition->getParam3(), $date);
				if($reductionCondition->getParam1() == 0) $conditionsOk = $conditionsOk && $actualDate < $date;
				else if($reductionCondition->getParam1() == 1) $conditionsOk = $conditionsOk && $actualDate == $date;
				else $conditionsOk = $conditionsOk && $actualDate > $date;
			}
			else {
				$conditionsOk = $conditionsOk && true;
			}
		}
		else if($reductionCondition->getType()=='customer'){
			$conditionsOk = $conditionsOk && $customer->isLoaded() && (
				($customer->isCompanyAccount() && $reductionCondition->getParam1() == 0) ||
				(!$customer->isCompanyAccount() && $reductionCondition->getParam1() == 1) ||
				($customer->getTypeAccount() == $reductionCondition->getParam1()));
		}
		if(!isset($intervals) && $conditionsOk){
			$intervals = array(array(
				'startDate' => $startDate,
				'endDate' => $endDate,
				'number' => 1,
				'promoCode' => $returnPromoCode,
				'idsPrice' => array()
			));
		}
		return array('conditionsOk' => $conditionsOk, 'intervals' => $intervals);
	}

	public static function servicesNotEquals($reductionConditionServices, $appointments){
		foreach($reductionConditionServices as $reductionConditionService){
			foreach($appointments as $appointment){
				if($appointment->getIdService() == $reductionConditionService->getIdService()){
					$conditionOk = true;
					$conditionOfPrice = false;
					$idsPrice = [];
					$idPrices = array();
					if(!empty($reductionConditionService->getPriceList())){
						$idPrices = explode(',', $reductionConditionService->getPriceList());
					}
					$totalNumber = 0;
					foreach($appointment->getAppointmentNumberPrices() as $appointmentNumberPrice){
						if(count($idPrices) == 0 || in_array($appointmentNumberPrice->getIdPrice(),$idPrices)){
							$totalNumber += $appointmentNumberPrice->getNumber();
						}
					}
					if($reductionConditionService->getMethodQuantity() == 1) $conditionOfPrice = $conditionOfPrice || $totalNumber == $reductionConditionService->getNumber();
					else if($reductionConditionService->getMethodQuantity() == 0) $conditionOfPrice = $conditionOfPrice || $totalNumber >= $reductionConditionService->getNumber();
					else if($reductionConditionService->getMethodQuantity() == 2) $conditionOfPrice = $conditionOfPrice || $totalNumber < $reductionConditionService->getNumber();
					else if($reductionConditionService->getMethodQuantity() == 3) {
						$number = floor($totalNumber / $reductionConditionService->getNumber());
						$conditionOfPrice = $conditionOfPrice || ($number > 0);
					}
					else if($reductionConditionService->getMethodQuantity() == 4) {
						$conditionOfPrice = $conditionOfPrice || ($totalNumber >= $reductionConditionService->getNumber() && $totalNumber <= $reductionConditionService->getNumber2());
					}
					$conditionOk = $conditionOk && $conditionOfPrice;
					if($conditionOk) return false;
				}
			}
		}
		return true;
	}

	/**
	 * Algorithme of constraints tatisfaction
	 */
	public static function servicesSatisfaction($reductionConditionServices, $appointments, $result){
		if(count($reductionConditionServices) == 0) {
			return self::mergeAllIntervals($result);
		}
		else if(count($appointments) == 0) {
			$mandatory = (count($reductionConditionServices) == 0);
			for($i = 0; $i < count($reductionConditionServices); $i++){
				$reductionConditionService = $reductionConditionServices[$i];
				$mandatory = $mandatory || $reductionConditionService->isMandatory();
			}
			if($mandatory) return array();
			return self::mergeAllIntervals($result);
		}
		$reductionConditionService = array_shift($reductionConditionServices);
		$appointment = array_shift($appointments);
		$localIntervals = array();
		if(($appointment->getIdService() == $reductionConditionService->getIdService() &&
			$reductionConditionService->checkDateConditions($appointment))){
			$conditionOk = true;
			$conditionOfPrice = false;
			$newResult = $result;
			$number = 1;
			$idPrices = array();
			$idsPrice = [];
			if(!empty($reductionConditionService->getPriceList())){
				$idPrices = explode(',', $reductionConditionService->getPriceList());
			}
			$totalNumber = 0;
			foreach($appointment->getAppointmentNumberPrices() as $appointmentNumberPrice){
				if(count($idPrices) == 0 || in_array($appointmentNumberPrice->getIdPrice(),$idPrices)){
					$totalNumber += $appointmentNumberPrice->getNumber();
				}
			}
			$number = $totalNumber;
			if($reductionConditionService->getMethodQuantity() == 1) $conditionOfPrice = $conditionOfPrice || $totalNumber == $reductionConditionService->getNumber();
			else if($reductionConditionService->getMethodQuantity() == 0) $conditionOfPrice = $conditionOfPrice || $totalNumber >= $reductionConditionService->getNumber();
			else if($reductionConditionService->getMethodQuantity() == 2) $conditionOfPrice = $conditionOfPrice || $totalNumber < $reductionConditionService->getNumber();
			else if($reductionConditionService->getMethodQuantity() == 3) {
				$number = floor($totalNumber / $reductionConditionService->getNumber());
				$conditionOfPrice = $conditionOfPrice || ($number > 0);
			}
			else if($reductionConditionService->getMethodQuantity() == 4) {
				$conditionOfPrice = $conditionOfPrice || ($totalNumber >= $reductionConditionService->getNumber() && $totalNumber <= $reductionConditionService->getNumber2());
			}
			$conditionOk = $conditionOk && $conditionOfPrice;
			if($conditionOfPrice){
				$idsPrice = array_merge($idsPrice, $idPrices);
			}
			if($conditionOk){
				$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getStartDate());
				$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getEndDate());
				array_push($newResult, array('startDate'=>$startDate, 'endDate'=> $endDate, 'number' => $number, 'promoCode'=>'', 'idsPrice'=> array('id'.$appointment->getIdClientObject() => $idsPrice),
				'totalNumber'=>array('id'.$appointment->getIdClientObject() => $totalNumber),
				'idAppointments'=>[$appointment->getIdClientObject()]));
				$tempo = self::servicesSatisfaction($reductionConditionServices, $appointments, $newResult);
				if(count($tempo) > 0) {
					$localIntervals = array_merge($localIntervals, $tempo);
				}
			}
		}
		array_unshift($reductionConditionServices, $reductionConditionService);
		$tempo = self::servicesSatisfaction($reductionConditionServices, $appointments, $result);
		if(count($tempo) > 0) {
			$localIntervals = array_merge($localIntervals, $tempo);
		}
		if(!$reductionConditionService->isMandatory()){
			$reductionConditionService = array_shift($reductionConditionServices);
			array_unshift($appointments, $appointment);
			$tempo = self::servicesSatisfaction($reductionConditionServices, $appointments, $result);
			if(count($tempo) > 0) {
				$localIntervals = array_merge($localIntervals, $tempo);
			}
		}

		return $localIntervals;
	}

	/**
	 * Detection of method reduction conditions
	 */
	public static function methodReductionConditionTypeService($reductionConditions, $intervals){
		$newIntervals = [];
		/*if($reductionConditions->getParam1() == 0){
			foreach($intervals as $interval){
				$interval['number'] = 1;
				array_push($newIntervals, $interval);
			}
		}
		else */
		if($reductionConditions->getParam1() == 1){
			foreach($intervals as $interval){
				$totalSum = 0;
				foreach($interval['totalNumber'] as $id => $value){
					$totalSum += $value;
				}
				$interval['number'] = $totalSum;
				if($totalSum >= $reductionConditions->getParam2()){
					array_push($newIntervals, $interval);
				}
			}
		}
		else if($reductionConditions->getParam1() == 2){
			foreach($intervals as $interval){
				$min = 9999;
				foreach($interval['totalNumber'] as $id => $value){
					$min = min($min, $value);
				}
				$interval['number'] = $min;
				array_push($newIntervals, $interval);
			}
		}
		else if($reductionConditions->getParam1() == 3){
			foreach($intervals as $interval){
				$min = 9999;
				foreach($interval['totalNumber'] as $id => $value){
					$number = floor($value / $reductionConditions->getParam2());
					$min = min($min, $number);
				}
				$interval['number'] = $min;
				array_push($newIntervals, $interval);
			}
		}
		else {
			$newIntervals = $intervals;
		}
		return $newIntervals;
	}

	public static function uniqueless($intervals, $nbServices){
		$newIntervals = array();
		$idsBannished = array();
		foreach($intervals as $interval){
			$idAppointments = $interval['idAppointments'];
			$index = 0;
			$found = false;
			while($index < count($idAppointments) && !$found){
				$found = $found || in_array($idAppointments[$index], $idsBannished);
				$index++;
			}
			if(!$found){
				$idsBannished = array_merge($idsBannished, $idAppointments);
				array_push($newIntervals, $interval);
			}
		}
		return $newIntervals;
	}

	/**
	 *
	 */
	public static function checkReductionConditionApplication(RESA_ReductionConditionApplication $reduction, $appointment){
		$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getStartDate());
		$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getEndDate());
		$conditionOk = true;
		$number = 0;
		$idsPrice = array();
		if($reduction->getType()=='service'){
			if($reduction->getParam1() == 0) $conditionOk = $conditionOk && $appointment->getIdService() == $reduction->getParam2();
			else $conditionOk = $conditionOk && $appointment->getIdService() != $reduction->getParam2();

			$conditionOfPrice = false;
			$idPrices = array();
			if(!empty($reduction->getParam3())){
				$idPrices = explode(',', $reduction->getParam3());
			}
			foreach($appointment->getAppointmentNumberPrices() as $appointmentNumberPrice){
				if(count($idPrices) == 0 || in_array($appointmentNumberPrice->getIdPrice(),$idPrices)){
					$number = $appointmentNumberPrice->getNumber();
					$localConditionOfPrice = false;
					if($reduction->getParam4() == 0) $localConditionOfPrice = $appointmentNumberPrice->getNumber() >= $reduction->getParam5();
					else if($reduction->getParam4() == 1) $localConditionOfPrice = $appointmentNumberPrice->getNumber() < $reduction->getParam5();
					else if($reduction->getParam4() == 2) {
						$number = floor($appointmentNumberPrice->getNumber() / $reduction->getParam5());
						$localConditionOfPrice = ($number > 0);
					}
					if($localConditionOfPrice){
						array_push($idsPrice, $appointmentNumberPrice->getIdPrice());
					}
					$conditionOfPrice = $conditionOfPrice || $localConditionOfPrice;
				}
			}
			$conditionOk = $conditionOk && $conditionOfPrice;
		}
		else if($reduction->getType()=='amount'){
			if($reduction->getParam1() == 1) $conditionOk = $conditionOk && $appointment->getTotalPriceWithoutReduction() == $reduction->getParam2();
			else if($reduction->getParam1() == 0) $conditionOk = $conditionOk && $appointment->getTotalPriceWithoutReduction() >= $reduction->getParam2();
			else $conditionOk = $conditionOk && $totalPrice < $reduction->getParam2();
		}
		else if($reduction->getType()=='date'){
			$date = new DateTime($reduction->getParam2());
			if($reduction->getParam1() == 0) $conditionOk = $conditionOk && $startDate < $date;
			else if($reduction->getParam1() == 1) $conditionOk = $conditionOk && $startDate == $date;
			else $conditionOk = $conditionOk && $startDate > $date;
		}
		else if($reduction->getType()=='time'){
			$date = self::timeToDate($reduction->getParam2(), $startDate);
			if($reduction->getParam1() == 0) $conditionOk = $conditionOk && $endDate < $date;
			else if($reduction->getParam1() == 1) $conditionOk = $conditionOk && $startDate >= $date;
		}
		else if($reduction->getType()=='days'){
			if(!empty($reduction->getParam3())){
				$days = explode(',', $reduction->getParam3());
			}
			$conditionOk = $conditionOk && in_array($startDate->format('w'), $days);
		}
		return array('conditionOk' => $conditionOk, 'idsPrice'=> $idsPrice, 'number' => $number);
	}

	/**
	 * create groups if necessary
	 */
	 public static function createGroupsIfNecessary(RESA_Appointment $appointment, RESA_Booking $booking){
		 $idService = RESA_Service::getLastIdService($appointment->getIdService());
		 $groups = RESA_Group::getAllData(
			 array('idPlace' => $appointment->getIdPlace(),
		 				'idService' => $idService,
						'startDate' => $appointment->getStartDate(),
						'endDate' => $appointment->getEndDate()));
		if(count($groups) == 0){
			$staticGroups = RESA_StaticGroup::getAllData(
				 array('idPlace' => $appointment->getIdPlace(),
			 				'idService' => $idService,
							'activated' => true));
		}
		else {
			$idParticipants = $appointment->getUriParticipants();
			$staticGroups = RESA_StaticGroup::getAllData(
				 array('idPlace' => $appointment->getIdPlace(),
			 				'idService' => $idService,
						  'oneByBooking' => true,
							'activated' => true));
			$oneFound = false;
			foreach($groups as $group){
				$oneFound = $oneFound || $group->haveOneParticipants($idParticipants);
			}
			if($oneFound){
				$staticGroups = [];
			}
		}
		if(count($staticGroups) > 0){
			$service = new RESA_Service();
			$service->loadById($appointment->getIdService());
			$timeslot = self::getTimeslot(RESA_Tools::convertStringToDate($appointment->getStartDate()),
			 		RESA_Tools::convertStringToDate($appointment->getEndDate()),
					$service, array(), -1, true, 'type_account_0');
			$idServicePrices = [];
			if(isset($timeslot)) $idServicePrices = $timeslot['idsServicePrices'];
			foreach($staticGroups as $staticGroup){
				if($staticGroup->haveOneIdServicePrices($idServicePrices)){
					$group = $staticGroup->toGroup($booking->getId(), '(#' . ($booking->getIdCreation()) . ')');
					$group->setStartDate($appointment->getStartDate());
					$group->setEndDate($appointment->getEndDate());
					$group->save();
				}
			}
		}
	}

	 /**
 	 * add participants in groups
 	 */
 	 public static function addParticipantsInGroups(RESA_Appointment $appointment){
		 $idService = RESA_Service::getLastIdService($appointment->getIdService());
		 $groups = RESA_Group::getAllData(
 			 array('idPlace' => $appointment->getIdPlace(),
 		 				'idService' => $idService,
 						'startDate' => $appointment->getStartDate(),
 						'endDate' => $appointment->getEndDate(),
						'oneByBooking' => true));
		if(count($groups) > 0){
			$idParticipants = $appointment->getUriParticipants();
			for($j = 0; $j < count($groups); $j++){
				if($groups[$j]->getNbIdParticipants() == 0 || $groups[$j]->haveOneParticipants($idParticipants)){
					$group = $groups[$j];
				}
			}
			if(isset($group)){
				foreach($appointment->getAppointmentNumberPrices() as $appointmentNumberPrice){
					for($i = 0; $i < count($appointmentNumberPrice->getParticipants()); $i++){
						$participant = $appointmentNumberPrice->getParticipants()[$i];
						$group->addIdParticipant($participant->uri);
					}
				}
				$group->save();
			}
		}
		else {
	 		$groups = RESA_Group::getAllData(
	 			 array('idPlace' => $appointment->getIdPlace(),
	 		 				'idService' => $idService,
	 						'startDate' => $appointment->getStartDate(),
	 						'endDate' => $appointment->getEndDate(),
							'oneByBooking' => false));
	 		if(count($groups) > 0){
				$idGroupsToUpdate = [];
				foreach($appointment->getAppointmentNumberPrices() as $appointmentNumberPrice){
					for($i = 0; $i < count($appointmentNumberPrice->getParticipants()); $i++){
						$participant = $appointmentNumberPrice->getParticipants()[$i];
						$alreadyInGroup = false;
						for($j = 0; $j < count($groups); $j++){
							if($groups[$j]->haveParticipant($participant->uri)){
								$alreadyInGroup = true;
								break;
							}
						}
						for($j = 0; $j < count($groups); $j++){
							if($groups[$j]->participantIsOk($participant) && !$alreadyInGroup){
								$groups[$j]->addIdParticipant($participant->uri);
								if(!in_array($groups[$j], $idGroupsToUpdate)){
									array_push($idGroupsToUpdate, $groups[$j]->getId());
								}
								$alreadyInGroup = true;
								break;
							}
						}
					}
				}
				//Update group
				foreach($idGroupsToUpdate as $idGroup){
					foreach($groups as $group){
						if($idGroup == $group->getId()){
							$group->save();
						}
					}
				}
	 		}
		}
 	}



	/**
	 * generate bill in HTML
	 */
	public static function generateHTMLBill(RESA_Booking $booking, $isAdvancePayment){
		$customer = new RESA_Customer();
		$customer->loadById($booking->getIdCustomer());
		$showStaff = false;
		$showState = false;
		$showTVA = get_option('resa_settings_display_tva_on_bill') && $booking->haveTVA();
		$places = unserialize(get_option('resa_settings_places'));
		if($places == null || $places == false) $places = [];
		$locale = get_locale();
		$title = __('bill_title', 'resa');
		if($isAdvancePayment){
			$title = __('bill_advance_payment_title', 'resa');
		}
		$creationDate = DateTime::createFromFormat('Y-m-d H:i:s', $booking->getCreationDate());
		$displayForCustomer = true;

		ob_start(); ?>
		 <div class="container" id="resa_form">
			<button id="print-button" class="btn btn-default resa_btn"><?php _e('print_link_title', 'resa') ?></button>
        <div class="row">
          <div class="col-md-12 resa_cart_recap resa_recap_and_paiement">
						<div class="container resa_container">
              <div class="row">
                <div class="col-sm-6 col-xs-12 col-md-6 facture_header">
                  <h4 class="resa_h4"><?php echo $title; ?> </h4>
                  <p><?php echo $title; ?> n°<?php echo $booking->getId(); ?></p>
                  <p><?php echo date_i18n(get_option('date_format'), $creationDate->getTimestamp()); ?> - <?php echo $creationDate->format(get_option('time_format')); ?></p>
                  <p><?php _e('Customer_word', 'resa') ?> n°<?php echo $booking->getIdCustomer(); ?></p>
                  <p class="resa_title_description"><?php _e('your_bookings_words','resa') ?></p>
                </div>
                <div class="col-xs-12 facture_header_info_client col-md-4 col-sm-6 facture_header">
                  <h4 class="resa_h4"><?php _e('Customer_word', 'resa') ?></h4>
                  <p><?php echo $customer->getFirstName(); ?> <?php echo $customer->getLastName(); ?></p>
                  <p><?php echo $customer->getCompany(); ?></p>
                  <p><?php echo $customer->getAddress(); ?></p>
                  <p><?php echo $customer->getPostalCode(); ?>  <?php echo $customer->getTown(); ?></p>
                  <p><?php echo $customer->getCountry(); ?></p>
                </div>
              </div>
						</div>
						<?php include( plugin_dir_path( __FILE__ ) . '../controller/templates/RESA_bookingDetails.php'); ?>
					<div class="container resa_container">
						<?php
						$informationsOnBill =
							RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_informations_on_bill')), $locale);
						if($booking->isQuotation()){
							$informationsOnBill =
									RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_informations_on_quotation')), $locale);
						}
						if(!empty($informationsOnBill)){
						?>
							<div class="row facture_paiement_option">
								<div class="col-md-12">
									<p><?php echo nl2br($informationsOnBill) ?></p>
								</div>
							</div>
						<?php
						}
						if(get_option('resa_settings_payment_transfer')){
							$transferText = RESA_Mailer::formatSimpleMessage($booking, $customer,
								RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_payment_transfer_text')), $locale));
						?>
							<div class="row facture_paiement_option">
								<div class="col-md-12">
									<h3><?php echo _e('transfer_payment_title', 'resa') ?></h3>
									<p><?php echo nl2br($transferText); ?></p>
								</div>
							</div>
						<?php
						}
						if(get_option('resa_settings_payment_cheque')){
							$chequeText = RESA_Mailer::formatSimpleMessage($booking, $customer,
								RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_payment_cheque_text')), $locale));
						?>
							<div class="row facture_paiement_option">
								<div class="col-md-12">
									<h3><?php echo _e('cheque_payment_title', 'resa') ?></h3>
									<p><?php echo nl2br($chequeText); ?></p>
								</div>
							</div>
						<?php
						}
						?>
						<div class="row">
							<div class="col-xs-12 col-md-4 facture_footer">
								<p><?php echo get_option('resa_settings_company_name'); ?></p>
								<p><?php echo get_option('resa_settings_company_address'); ?></p>
								<p><?php echo get_option('resa_settings_company_phone'); ?></p>
							</div>
							<div class="facture_header col-xs-12 col-md-4">
								<p class="facture_company_logo_p"><img src="<?php echo  plugin_dir_url( __FILE__ ) . '../controller/'; ?>images/logo.jpg" width="200" class="facture_company_logo" /> </p>
							</div>
							<div class="facture_header col-xs-12 col-md-4 facture_footer">
								<p><?php echo get_option('resa_settings_company_type'); ?></p>
								<p><?php echo get_option('resa_settings_company_siret'); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script>
		jQuery(function($){
			$(document).ready(function(){
				$('#print-button').click(function(){print(); });
			});
		});
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Return booking details HTML
	 */
	public static function returnBookingDetailsHTML(RESA_Booking $booking, $displayForCustomer = false){
		$showTVA = $booking->haveTVA();
		$showStaff = RESA_Variables::isStaffManagementActivated() && !$displayForCustomer;
		$places = unserialize(get_option('resa_settings_places'));
		if($places == null || $places == false) $places = [];
		ob_start(); ?>
		<?php include( plugin_dir_path( __FILE__ ) . '../controller/templates/RESA_bookingDetails.php'); ?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Return booking line HTML
	 */
	public static function returnBookingLineHTML(RESA_Booking $booking){
		$customer = new RESA_Customer();
		$customer->loadById($booking->getIdCustomer());
		$intervals = $booking->getIntervals();
		$totalPrice = $booking->getTotalPrice();
		$needToPay = $booking->getNeedToPay();
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $booking->getCreationDate());
		$state = 'Unknown';
		if($totalPrice == $needToPay && $needToPay != 0){
			$state = __('no_payment_state','resa');
		}
		else if($totalPrice - $needToPay > 0 && $needToPay > 0) {
			$state = __('incomplete_payment_state','resa');
		}
		else if($needToPay == 0 && $totalPrice != 0){
			$state = __('complete_payment_state','resa');
		}
		ob_start(); ?>
		<table class="wp-list-table widefat fixed pages ro-table">
			<tr>
				<td><?php foreach($intervals as $interval){ echo $interval['date'].'('.$interval['number'].')'; } ?></td>
				<td><?php echo $customer->getCompany(); ?></td>
				<td><?php echo $customer->getLastName(); ?> - <?php echo $customer->getFirstName(); ?></td>
				<td><?php echo $customer->getPhone(); ?></td>
				<td><?php echo $customer->getEmail(); ?></td>
				<td><?php echo $state; ?><br /><?php echo $totalPrice - $needToPay; ?> / <?php echo $totalPrice; ?></td>
				<td><?php echo date_i18n(get_option('date_format'), $date->getTimestamp()); ?> <?php echo $date->format(get_option('time_format')); ?></td>
			</tr>
		</table><?php
		return ob_get_clean();
	}

	public static function generateLogNotification($type, $booking, $customer, $currentUser){
		$settingsLogs = unserialize(get_option('resa_settings_log_notifications'));
		if($type >= 0 && $type < count($settingsLogs)){
			$logModel = $settingsLogs[$type];
			$text = RESA_Algorithms::generateTextLogNotification($logModel->text, $booking, $customer, $currentUser);
			$logNotification = RESA_LogNotification::generateLogNotification($logModel, $booking, $customer->getId(), $currentUser->getId(), $booking->getAppointmentFirstDate(), $text);
			return $logNotification;
		}
		return null;
	}

	/**
	 * generate text of log notification
	 */
	public static function generateTextLogNotification($text, RESA_Booking $booking, RESA_Customer $customer, RESA_Customer $currentUser = null){
		$text = str_replace('[idBooking]', $booking->getIdCreation(), $text);
		$text = str_replace('[company]', $customer->getCompany(), $text);
		$text = str_replace('[lastName]', $customer->getLastName(), $text);
		$text = str_replace('[firstName]', $customer->getFirstName(), $text);
		if(isset($currentUser)){
			$text = str_replace('[manager]', $currentUser->getDisplayName(), $text);
		}
		$paymentState = 'Pas de paiement';
		if($booking->isPaymentStateAdvancePayment()) $paymentState = 'Acompte';
		if($booking->isPaymentDepositPayment()) $paymentState = 'Caution';
		if($booking->isPaymentStateComplete()) $paymentState = 'Encaissée';
		$text = str_replace('[paymentState]', $paymentState, $text);
		if($booking->isLoaded()){
			$dateBooking = $booking->getAppointmentFirstDate();
			$date = DateTime::createFromFormat('Y-m-d H:i:s', $dateBooking);
			$text = str_replace('[dateBooking]', date_i18n(get_option('date_format'), $date->getTimestamp()).' à '.$date->format(get_option('time_format')), $text);
		}
		$text = str_replace('  ', ' ', $text);
		return $text;
	}

	public static function getCSSVarColors($form = null){
		$colors = unserialize(get_option('resa_settings_colors'));
		if(isset($form) && isset($form->getColors()->override) && $form->getColors()->override){
			$colors = $form->getColors();
		}
		return '<style type="text/css">
	  body {
	    --primary-color:' . $colors->primaryColor. ';
	    --secondary-color: ' . $colors->secondaryColor. ';

	    --block-n1-bg:transparent;
	    --block-n1-border:none;
	    --block-n1-color:inherit;

	    --block-n2-bg:transparent;
	    --block-n2-border:none;
	    --block-n2-color:inherit;

	    --block-n3-bg:transparent;
	    --block-n3-border:none;
	    --block-n3-color:inherit;

	    --block-n4-bg:#EAEAEA;
	    --block-n4-border:none;
	    --block-n4-color:black;

	    --block-cart-bg:#f9f9f9;
	    --block-cart-border:none;
	    --block-cart-color:inherit;

	    --btn-bg:var(--primary-color);
	    --btn-bg-hover:var(--secondary-color);
	    --btn-bg-active:white;
	    --btn-bg-disabled:lightgrey;

	    --btn-border:1px solid var(--primary-color);
	    --btn-border-hover:1px solid var(--secondary-color);
	    --btn-border-active:1px solid var(--secondary-color);
	    --btn-border-disabled:none;

	    --btn-color:white;
	    --btn-color-hover:white;
	    --btn-color-active:var(--primary-color);
	    --btn-color-disabled:grey;

	    --btn-warn-bg:white;
	    --btn-warn-bg-hover:green;
	    --btn-warn-bg-active:blue;
	    --btn-warn-bg-disabled:lightgrey;

	    --btn-warn-border:none;
	    --btn-warn-border-hover:none;
	    --btn-warn-border-active:none;
	    --btn-warn-border-disabled:none;

	    --btn-warn-color:black;
	    --btn-warn-color-hover:white;
	    --btn-warn-color-active:red;
	    --btn-warn-color-disabled:grey;

	    --btn-alert-bg:#db3939;
	    --btn-alert-bg-hover:#c52d23;
	    --btn-alert-bg-active:#c52d23;
	    --btn-alert-bg-disabled:lightgrey;

	    --btn-alert-border:none;
	    --btn-alert-border-hover:none;
	    --btn-alert-border-active:none;
	    --btn-alert-border-disabled:none;

	    --btn-alert-color:white;
	    --btn-alert-color-hover:white;
	    --btn-alert-color-active:white;
	    --btn-alert-color-disabled:lightgrey;

	    --input-bg:GhostWhite;
	    --input-bg-hover:GhostWhite;
	    --input-bg-active:GhostWhite;
	    --input-bg-disabled:Gainsboro;

	    --input-border:GhostWhite;
	    --input-border-hover:GhostWhite;
	    --input-border-active:GhostWhite;
	    --input-border-disabled:Gainsboro;

	    --input-color:black;
	    --input-color-hover:black;
	    --input-color-active:black;
	    --input-color-disabled:darkgrey;

	    --text-extra-color1:#C7A779;
	    --text-extra-color2:#043456;

	    --bg-extra-color1:#043456;
	    --bg-extra-color2:#C7A779;
	  }
	  </style>';
	}
}
