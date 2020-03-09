<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Mailer
{
	 /**
	  * Send message for booking.
		* @param $justForCustomer : send just for customer if true
		*/
	 public static function sendMessageBooking(RESA_Booking $booking, $sendToStaff, $justForCustomer = false, $subjectCustomer = '', $messageCustomer = '', $idCurrentUser = -1){
		 self::setWpMailWithBooking($booking);
		 $result = array('customer' => true, 'emails' => true, 'members' => true);
		 if(!$booking->isAlreadySentEmail() || $justForCustomer){
			 $booking->setAlreadySentEmail(true);
			 $customer = $booking->getCustomer();
 			 $locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
			 if(get_option('resa_settings_notifications_email_notification_customer_booking')){
				 if(empty($subjectCustomer)){
					 $subjectCustomer = RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_booking_subject')), $locale);
				 }
				 if(empty($messageCustomer)){
					 $messageCustomer =  RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_booking_text')), $locale);
				 }
				 $message = self::formatMessage($booking, $customer, $messageCustomer, true);
				 $subject = self::formatSimpleMessage($booking, $customer, $subjectCustomer);
				 $alreadySent = array();
				 $notificationsServices = '';
				 foreach($booking->getAppointments() as $appointment){
					 if(!in_array($appointment->getIdService(), $alreadySent)){
						 $service = new RESA_Service();
						 $service->loadById($appointment->getIdService());
						 if($service->isLoaded() && $service->isNotificationActivated()){
							 if(!empty($notificationsServices)) $notificationsServices .= '<p><br /></p>';
							 $notificationsServices .= RESA_Tools::getTextByLocale($service->getNotificationMessage(), $locale);
							 array_push($alreadySent, $appointment->getIdService());
						 }
					 }
				 }
				 $message = str_replace('[RESA_notifications_services]', $notificationsServices, $message);
				 if($customer->isLoaded() && !$customer->isDeactivateEmail()){
					 RESA_EmailCustomer::generate($customer->getId(), $booking->getIdCreation(), $idCurrentUser, get_option('wp_mail_from'), $subject, $message, $idCurrentUser != -1)->save();
					 $result['customer'] = self::sendMessage($customer->getEmail(), $subject, $message);
				 }
			 }

			 if(!$justForCustomer){
				 if(get_option('resa_settings_notifications_email_notification_emails_booking')){
					 $emailsText = get_option('resa_settings_notifications_email_notification_emails_booking_emails', '');
					 $emailsArray = unserialize(get_option('resa_settings_notifications_email_notification_emails_booking_places_emails'));
					 foreach($booking->getIdPlaces() as $idPlace){ if(isset($emailsArray->{$idPlace})) $emailsText .= ',' . $emailsArray->{$idPlace}; }
					 $emails = explode(',', $emailsText);
					 if(!empty($emailsText) && count($emails) > 0){
						 $subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_emails_booking_subject')), $locale), false);
						 $message = self::formatMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_emails_booking_text')), $locale));
						 foreach($emails as $email){
						 	$result['emails'] = $result['emails'] && self::sendMessage(trim($email), $subject, $message);
						 }
						}
				 }

				 if(get_option('resa_settings_notifications_email_notification_staff_booking') && $sendToStaff){
				 	 $subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_staff_booking_subject')), $locale));
					 $message = self::formatMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_staff_booking_text')), $locale), false);
					 $allIdMembers = array();
					 foreach($booking->getAppointments() as $appointment){
						 foreach($appointment->getAppointmentMembers() as $appointmentMember){
							 if(!in_array($appointmentMember->getIdMember(), $allIdMembers))
								 array_push($allIdMembers, $appointmentMember->getIdMember());
						 }
					 }
					 foreach($allIdMembers as $idMember){
						 $member = new RESA_Member();
						 $member->loadById($idMember);
						 Logger::DEBUG('Send email for member : '.$member->getNickname());
						 if($member->isLoaded() && !empty($member->getEmail())){
							 $result['members'] = $result['members'] && self::sendMessage($member->getEmail(), $subject, $message);
						 }
					 }
				 }
			 }
		 }
		 return $result;
	 }


		/**
	 * Send message for quotation create by customer.
	 */
	 public static function sendMessageQuotationCustomer(RESA_Booking $booking){
		 if(!$booking->isAlreadySentEmail() && $booking->isQuotation() && $booking->isQuotationRequest()){
			 self::setWpMailWithBooking($booking);
			 $booking->setAlreadySentEmail(true);
			 $customer = $booking->getCustomer();
 				$locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
			 if(get_option('resa_settings_notifications_email_notification_quotation_customer')){
				 $subjectCustomer = RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_subject')), $locale);
				 $messageCustomer =  RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_text')), $locale);
				 $message = self::formatMessage($booking, $customer, $messageCustomer, true);
				 $subject = self::formatSimpleMessage($booking, $customer, $subjectCustomer);
				 if($customer->isLoaded() && !$customer->isDeactivateEmail()){
					 RESA_EmailCustomer::generate($customer->getId(), $booking->getIdCreation(), -1, get_option('wp_mail_from'), $subject, $message, true)->save();
					 self::sendMessage($customer->getEmail(), $subject, $message);
				 }
			 }
			 if(get_option('resa_settings_notifications_email_notification_emails_quotation_requests')){
				 $emailsText = get_option('resa_settings_notifications_email_notification_emails_quotation_requests_emails', '');
				 $emailsArray = unserialize(get_option('resa_settings_notifications_email_notification_emails_quotation_requests_places_emails'));
				 foreach($booking->getIdPlaces() as $idPlace){ if(isset($emailsArray->{$idPlace})) $emailsText .= ',' . $emailsArray->{$idPlace}; }
				 $emails = explode(',', $emailsText);
				 if(!empty($emailsText) && count($emails) > 0){
					 $subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_emails_quotation_requests_subject')), $locale), false);
					 $message = self::formatMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_emails_quotation_requests_text')), $locale));
					 foreach($emails as $email){
						 self::sendMessage(trim($email), $subject, $message);
					 }
				 }
			 }
		 }
	 }

		/**
		* Send message for booking quotation.
		* @param $force : force send if true
		*/
		public static function sendMessageQuotation(RESA_Booking $booking, $force = false, $subjectCustomer = '', $messageCustomer = '', $idCurrentUser = -1){
			if($booking->isQuotation() && (!$booking->isAlreadySentEmail() || $force)){
	 		 	self::setWpMailWithBooking($booking);
				$booking->setAlreadySentEmail(true);
				if(get_option('resa_settings_notifications_email_notification_quotation_customer_booking')){
					$customer = $booking->getCustomer();
					$locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
					if(isset($customer) && $customer->isLoaded()){
						if(empty($subjectCustomer)){
	 					 $subjectCustomer = RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_booking_subject')), $locale);
	 				 }
	 				 if(empty($messageCustomer)){
	 					 $messageCustomer =  RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_booking_text')), $locale);
	 				 }
						$subject = self::formatSimpleMessage($booking, $customer, $subjectCustomer);
						$message = self::formatMessage($booking, $customer, $messageCustomer, true);
						if($customer->isLoaded() && !$customer->isDeactivateEmail()){
							RESA_EmailCustomer::generate($customer->getId(), $booking->getIdCreation(), $idCurrentUser, get_option('wp_mail_from'), $subject, $message, $idCurrentUser != -1)->save();
							self::sendMessage($customer->getEmail(), $subject, $message);
						}
					}
				}
			}
		}


		/**
		* Send message for booking quotation accepted by backend
		* @param $force : force send if true
		*/
		public static function sendMessageQuotationAccepted(RESA_Booking $booking, $force = false, $subjectCustomer = '', $messageCustomer = '', $idCurrentUser = -1, $paymentsType = array(), $stopAdvancePayment = false, $expiredDays = 2){
			$result = true;
			if($booking->isQuotation() && $booking->isQuotationRequest() && (!$booking->isAlreadySentEmail() || $force)){
	 		 	self::setWpMailWithBooking($booking);
				$booking->setAlreadySentEmail(true);
				if(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer')){
					$customer = $booking->getCustomer();
					$locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
					if(isset($customer) && $customer->isLoaded()){
						if(empty($subjectCustomer)){
	 					 $subjectCustomer = RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer_subject')), $locale);
	 				 }
	 				 if(empty($messageCustomer)){
	 					 $messageCustomer =  RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer_text')), $locale);
	 				 }
						$subject = self::formatSimpleMessage($booking, $customer, $subjectCustomer);
						$message = self::formatMessage($booking, $customer, $messageCustomer, true, $paymentsType, $stopAdvancePayment, $expiredDays);
						if($customer->isLoaded() && !$customer->isDeactivateEmail()){
							RESA_EmailCustomer::generate($customer->getId(), $booking->getIdCreation(), $idCurrentUser, get_option('wp_mail_from'), $subject, $message, $idCurrentUser != -1)->save();
							$result = self::sendMessage($customer->getEmail(), $subject, $message);
						}
					}
				}
			}
			return $result;
		}


		public static function sentMessageQuotationAnswered($customer, $booking){
			if(get_option('resa_settings_notifications_email_notification_emails_quotation_answered')){
				self::setWpMailWithBooking($booking);
				$locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
				$emailsText = get_option('resa_settings_notifications_email_notification_emails_quotation_answered_emails', '');
				$emailsArray = unserialize(get_option('resa_settings_notifications_email_notification_emails_payment_booking_places_emails'));
				foreach($booking->getIdPlaces() as $idPlace){ if(isset($emailsArray->{$idPlace})) $emailsText .= ',' . $emailsArray->{$idPlace}; }
				$emails = explode(',', $emailsText);
				if(!empty($emailsText) && count($emails) > 0){
					 $subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_emails_quotation_answered_subject')), $locale));
					$message = self::formatMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_emails_quotation_answered_text')), $locale), true);
					foreach($emails as $email){
					 self::sendMessage(trim($email), $subject, $message);
					}
				}
			}
		}

	 public static function sentMessageAccountCreation($customer, $idCurrentUser = -1){
		 $result = false;
	 	self::setWpMailWithCurrentCustomer();
		 if(get_option('resa_settings_notifications_email_notification_customer_account_creation')){
			 if($customer->isLoaded() && !$customer->isDeactivateEmail()){
				 $booking = new RESA_Booking();
	 			 $locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
				 $subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_account_creation_subject')), $locale));
				 $message = self::formatMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_account_creation_text')), $locale), true);

				 RESA_EmailCustomer::generate($customer->getId(), -1, $idCurrentUser, get_option('wp_mail_from'), $subject, $message, $idCurrentUser != -1)->save();
				 $result = self::sendMessage($customer->getEmail(), $subject, $message);
			 }
		 }
		 return $result;
	 }

	 public static function sentMessagePasswordReinitialization($customer, $idCurrentUser = -1){
		 $result = false;
		 if(get_option('resa_settings_notifications_email_notification_customer_password_reinit')){
			 if($customer->isLoaded() && !$customer->isDeactivateEmail()){
				 $booking = new RESA_Booking();
	 		   $locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
				 $subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_password_reinit_subject')), $locale));
				 $message = self::formatMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_password_reinit_text')), $locale), true);
				 RESA_EmailCustomer::generate($customer->getId(), -1, $idCurrentUser, get_option('wp_mail_from'), $subject, $message, $idCurrentUser!=-1)->save();
				 $result = self::sendMessage($customer->getEmail(), $subject, $message);
			 }
		 }
		 return $result;
	 }

	 public static function sentMessageCustomerPaymentBooking($customer, $booking, $totalPayment = 0){
		 $locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
		 self::setWpMailWithBooking($booking);
		 if(get_option('resa_settings_notifications_email_notification_customer_payment')){
			 if($customer->isLoaded() && !$customer->isDeactivateEmail()){
				 $subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_payment_subject')), $locale));
				 $message = self::formatMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_payment_text')), $locale), true);
				 if($totalPayment > 0){
					 $message = str_replace('[RESA_payment_amount]', $totalPayment.''.get_option('resa_settings_payment_currency'), $message);
				 }
				 RESA_EmailCustomer::generate($customer->getId(), $booking->getIdCreation(), -1, get_option('wp_mail_from'), $subject, $message, false)->save();
				 self::sendMessage($customer->getEmail(), $subject, $message);
			 }
		 }
		 if(get_option('resa_settings_notifications_email_notification_emails_payment_booking')){
			 $emailsText = get_option('resa_settings_notifications_email_notification_emails_payment_booking_emails', '');
			 $emailsArray = unserialize(get_option('resa_settings_notifications_email_notification_emails_payment_booking_places_emails'));
			 foreach($booking->getIdPlaces() as $idPlace){ if(isset($emailsArray->{$idPlace})) $emailsText .= ',' . $emailsArray->{$idPlace}; }
			 $emails = explode(',', $emailsText);
			 if(!empty($emailsText) && count($emails) > 0){
				  $subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_emails_payment_booking_subject')), $locale));
	 			 $message = self::formatMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_emails_payment_booking_text')), $locale), true);
				 if($totalPayment > 0){
					 $message = str_replace('[RESA_payment_amount]', $totalPayment.''.get_option('resa_settings_payment_currency'), $message);
				 }
				 foreach($emails as $email){
					self::sendMessage(trim($email), $subject, $message);
				 }
				}
		 }
	 }

	 public static function sendMessageNeedPayment($bookings, $paymentsType, $stopAdvancePayment, $expiredDays, $subjectCustomer = '', $messageCustomer = '', $idCurrentUser = -1, $language = 'fr_FR'){
		 $result = true;
		 foreach($bookings as $booking){
		 	self::setWpMailWithBooking($booking);
			 $customer = $booking->getCustomer();
			 $locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
			 if($customer->isLoaded() && !$customer->isDeactivateEmail()){
				 if(empty($subjectCustomer)){
					$subjectCustomer =  RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_need_payment_subject')), $locale);
				}
				if(empty($messageCustomer)){
					$messageCustomer =  RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_need_payment_text')), $locale);
				}
				 $subject = self::formatSimpleMessage($booking, $customer, $subjectCustomer);
				 $message = self::formatMessage($booking, $customer, $messageCustomer, true, $paymentsType, $stopAdvancePayment, $expiredDays, $language);
				 RESA_EmailCustomer::generate($customer->getId(), $booking->getIdCreation(), $idCurrentUser, get_option('wp_mail_from'), $subject, $message, $idCurrentUser!=-1)->save();
				 $result = self::sendMessage($customer->getEmail(), $subject, $message);
			 }
		 }
		 return $result;
	 }

	/**
	 * Send a message after the appointment done
	 */
	public static function sendMessageAfterAppointment(RESA_Booking $booking){
		if(get_option('resa_settings_notifications_email_notification_after_appointment')){
			self::setWpMailWithBooking($booking);
			$customer = $booking->getCustomer();
			$locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
			if(isset($customer) && $customer->isLoaded()  && !$customer->isDeactivateEmail() && $customer->isSendRequestForOpinion() && $booking->isLoaded()){
				$subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_after_appointment_subject')), $locale));
				$message = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_after_appointment_text')), $locale));
				RESA_EmailCustomer::generate($customer->getId(), $booking->getIdCreation(), -1, get_option('wp_mail_from'), $subject, $message, false)->save();
				self::sendMessage($customer->getEmail(), $subject, $message);
			}
		}
	}

 /**
	* Send a message before the first appointment
	*/
 public static function sendMessageBeforeAppointment(RESA_Booking $booking){
	 if(get_option('resa_settings_notifications_email_notification_before_appointment')){
		 self::setWpMailWithBooking($booking);
		 $customer = $booking->getCustomer();
		 $locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
		 if(isset($customer) && $customer->isLoaded()  && !$customer->isDeactivateEmail() && $booking->isLoaded()){
			 $subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_before_appointment_subject')), $locale));
			 $message = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_before_appointment_text')), $locale));
			 RESA_EmailCustomer::generate($customer->getId(), $booking->getIdCreation(), -1, get_option('wp_mail_from'), $subject, $message, false)->save();
			 self::sendMessage($customer->getEmail(), $subject, $message);
		 }
	 }
 }

	/**
	 * Send a message no response quotation
	 */
	public static function sendMessageNoResponseQuotation($booking){
		if(get_option('resa_settings_notifications_email_notification_no_response_quotation')){
			self::setWpMailWithBooking($booking);
			$customer = $booking->getCustomer();
			$locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
			if(isset($customer) && $customer->isLoaded() && !$customer->isDeactivateEmail() && $booking->isQuotation()){
				$subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_no_response_quotation_subject')), $locale));
				$message = self::formatMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_no_response_quotation_text')), $locale), true);
				RESA_EmailCustomer::generate($customer->getId(), $booking->getIdCreation(), -1, get_option('wp_mail_from'), $subject, $message, false)->save();
				self::sendMessage($customer->getEmail(), $subject, $message);
			}
		}
	}


	/**
	 * Send message for ask account
	 */
	public static function sendMessageAskAccount($customer){
		$booking = new RESA_Booking();
		$locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
		self::setWpMailWithCurrentCustomer();
		if(get_option('resa_settings_notifications_email_notification_customer_ask_account')){
			if(empty($subjectCustomer)){
				$subjectCustomer = RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_ask_account_subject')), $locale);
			}
			if(empty($messageCustomer)){
				$messageCustomer =  RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_ask_account_text')), $locale);
			}
			$message = self::formatMessage($booking, $customer, $messageCustomer, true);
			$subject = self::formatSimpleMessage($booking, $customer, $subjectCustomer);
			if($customer->isLoaded() && !$customer->isDeactivateEmail()){
				RESA_EmailCustomer::generate($customer->getId(), -1, -1, get_option('wp_mail_from'), $subject, $message, false)->save();
				self::sendMessage($customer->getEmail(), $subject, $message);
			}
		}

		if(get_option('resa_settings_notifications_email_notification_emails_ask_account')){
			$emailsText = get_option('resa_settings_notifications_email_notification_emails_ask_account_emails', '');
			$emails = explode(',', $emailsText);
			if(!empty($emailsText) && count($emails) > 0){
				$subject = self::formatSimpleMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_emails_ask_account_subject')), $locale), false);
				$message = self::formatMessage($booking, $customer, RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_emails_ask_account_text')), $locale));
				foreach($emails as $email){
				 self::sendMessage(trim($email), $subject, $message);
				}
			}
		}
	}

	/**
	 * Send message for ask account
	 */
	public static function sendMessageResponseAskAccount($customer, $accepted, $idCurrentUser = -1){
		$booking = new RESA_Booking();
		$locale = !empty($customer->getLocale())?$customer->getLocale():get_locale();
		self::setWpMailWithCurrentCustomer();
		if($accepted){
			if(get_option('resa_settings_notifications_email_notification_customer_accepted_account')){
				if(empty($subjectCustomer)){
					$subjectCustomer = RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_accepted_account_subject')), $locale);
				}
				if(empty($messageCustomer)){
					$messageCustomer =  RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_accepted_account_text')), $locale);
				}
				$message = self::formatMessage($booking, $customer, $messageCustomer, true);
				$subject = self::formatSimpleMessage($booking, $customer, $subjectCustomer);
				if($customer->isLoaded() && !$customer->isDeactivateEmail()){
					RESA_EmailCustomer::generate($customer->getId(), -1, $idCurrentUser, get_option('wp_mail_from'), $subject, $message, $idCurrentUser!=-1)->save();
					return self::sendMessage($customer->getEmail(), $subject, $message);
				}
			}
		}
		else {
			if(get_option('resa_settings_notifications_email_notification_customer_refused_account')){
				if(empty($subjectCustomer)){
					$subjectCustomer = RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_refused_account_subject')), $locale);
				}
				if(empty($messageCustomer)){
					$messageCustomer =  RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_notifications_email_notification_customer_refused_account_text')), $locale);
				}
				$message = self::formatMessage($booking, $customer, $messageCustomer, true);
				$subject = self::formatSimpleMessage($booking, $customer, $subjectCustomer);
				if($customer->isLoaded() && !$customer->isDeactivateEmail()){
					return self::sendMessage($customer->getEmail(), $subject, $message);
				}
			}
		}
	}

	public static function sendCustomMessage($booking, $customer, $email, $subject, $message, $idCurrentUser = -1, $sender = null, $paymentsType = array(), $stopAdvancePayment = false){
		if($customer->isLoaded() && !$customer->isDeactivateEmail()){
			if($booking->isLoaded()){
				self::setWpMailWithBooking($booking);
			}
			else {
				if(!isset($sender)){
					self::setWpMailWithCurrentCustomer();
				}
				else {
					update_option('wp_mail_from_name', esc_html($sender->sender_name));
					update_option('wp_mail_from', esc_html($sender->sender_email));
				}
			}
			$subject = self::formatSimpleMessage($booking, $customer, $subject);
			$message = self::formatMessage($booking, $customer, $message, true, $paymentsType, $stopAdvancePayment);
			if($email == $customer->getEmail()){
				RESA_EmailCustomer::generate($customer->getId(), $booking->getIdCreation(), $idCurrentUser, get_option('wp_mail_from'), $subject, $message, $idCurrentUser!=-1)->save();
			}
			return self::sendMessage($email, $subject, $message);
		}
	}

	public static function formatSimpleMessage(RESA_Booking $booking, RESA_Customer $customer, $message){
		$formattedMessage = $message;
		if($customer->isLoaded()){
 		 $formattedMessage = str_replace('[RESA_customer_lastname]', $customer->getLastName(), $formattedMessage);
 		 $formattedMessage = str_replace('[RESA_customer_firstname]', $customer->getFirstName(), $formattedMessage);
 		 $formattedMessage = str_replace('[RESA_customer_login]', $customer->getLogin(), $formattedMessage);
 		 $formattedMessage = str_replace('[RESA_customer_company]', $customer->getCompany(), $formattedMessage);
 		 $formattedMessage = str_replace('[RESA_customer_phone]', $customer->getPhone(), $formattedMessage);
 		 $formattedMessage = str_replace('[RESA_customer_display]', admin_url('admin.php?page=resa_appointments&view=display&subPage=clients&id=' . $customer->getId()), $formattedMessage);
		}
		else {
 		 $formattedMessage = str_replace('[RESA_customer_lastname]', '', $formattedMessage);
 		 $formattedMessage = str_replace('[RESA_customer_firstname]', '', $formattedMessage);
 		 $formattedMessage = str_replace('[RESA_customer_login]', '', $formattedMessage);
 		 $formattedMessage = str_replace('[RESA_customer_company]', '', $formattedMessage);
 		 $formattedMessage = str_replace('[RESA_customer_phone]', '', '');
 		 $formattedMessage = str_replace('[RESA_customer_display]', '', $formattedMessage);
		}

	 	if($booking->isLoaded()){
			$formattedMessage = str_replace('[RESA_total_price]', $booking->getTotalPrice().''.get_option('resa_settings_payment_currency'), $formattedMessage);
			$formattedMessage = str_replace('[RESA_advancePayment]', $booking->getAdvancePayment().''.get_option('resa_settings_payment_currency'), $formattedMessage);
			$formattedMessage = str_replace('[RESA_booking_note]', $booking->getPublicNote(), $formattedMessage);
			$formattedMessage = str_replace('[RESA_booking_id]', $booking->getIdCreation(), $formattedMessage);

			$date = DateTime::createFromFormat('Y-m-d H:i:s', $booking->getAppointmentFirstDate());
			$text = date_i18n(get_option('date_format'), $date->getTimestamp()). ' ' . __('to_word', 'resa') . ' ' . date_i18n(get_option('time_format'), $date->getTimestamp());
			$formattedMessage = str_replace('[RESA_booking_first_date]', $text, $formattedMessage);
		}
		else {
			$formattedMessage = str_replace('[RESA_total_price]', '', $formattedMessage);
			$formattedMessage = str_replace('[RESA_advancePayment]','', $formattedMessage);
			$formattedMessage = str_replace('[RESA_booking_note]', '', $formattedMessage);
			$formattedMessage = str_replace('[RESA_booking_id]', '', $formattedMessage);
			$formattedMessage = str_replace('[RESA_booking_first_date]', '', $formattedMessage);
		}

		$formattedMessage = str_replace('[RESA_company_name]', htmlspecialchars_decode(get_option('resa_settings_company_name', ''), ENT_QUOTES), $formattedMessage);

		$customShortcodes = unserialize(get_option('resa_settings_notifications_custom_shortcodes', serialize(array())));
		if(is_array($customShortcodes)){
			foreach($customShortcodes as $shorcodes){
				$varBlock = $shorcodes->block;
				for($i = 0; $i < count($shorcodes->params); $i++){
					$params = (array) $shorcodes->params[$i];
					$varBlock = str_replace('[' . $i . ']', $params['value'], $varBlock);
				}
				$formattedMessage = str_replace($shorcodes->shortcode, $varBlock, $formattedMessage);
			}
		}

		return $formattedMessage;
	}

	/**
	 * @delete $paymentsType
	 * @delete $$stopAdvancePayment
	 */
	public static function formatMessage(RESA_Booking $booking, RESA_Customer $customer, $message, $displayForCustomer = false, $paymentsType = array(), $stopAdvancePayment = false, $expiredDays = 2, $language = 'fr_FR'){
	 $formattedMessage = $message;
	 $key = '[RESA_booking_details]';
	 if($booking->isLoaded()){
		 if(strpos($formattedMessage, $key) !== false){
			$details = RESA_Algorithms::returnBookingDetailsHTML($booking, $displayForCustomer);
			$formattedMessage = str_replace($key, $details, $formattedMessage);
		 }
		 $key = '[RESA_booking_line]';
		 if(strpos($formattedMessage, $key) !== false){
			$details = RESA_Algorithms::returnBookingLineHTML($booking);
			$formattedMessage = str_replace($key, $details, $formattedMessage);
		 }
	 }
	 if($customer->isLoaded() && $customer->isWpUser() && $customer->isRESACustomer()){
		 $urlAccount = get_option('resa_settings_customer_account_url');
		 if($urlAccount!=''){
			if(function_exists('pll_get_post')) $urlAccount = get_permalink(pll_get_post(url_to_postid($urlAccount), $language));
			if($urlAccount!=''){
				$customer->generateNewTokenAllTimes($expiredDays);
				$customer->save(false);
				$linkAutoreconnection = $urlAccount . '?action='.RESA_Customer::$ACTION_AUTOCONNECT.'&token='.$customer->getToken();
				$formattedMessage = str_replace('[RESA_link_account]', $linkAutoreconnection, $formattedMessage);
				$linkPaymentBooking = $linkAutoreconnection . '&id='.$booking->getIdCreation();
				$formattedMessage = str_replace('[RESA_link_payment_booking]', $linkPaymentBooking, $formattedMessage);
				$formattedMessage = str_replace('[RESA_expiration_date]', $customer->getTokenValidation()->format('d-m-Y'), $formattedMessage);
			}
		 }
	 }
	 else {
		 $formattedMessage = str_replace('[RESA_link_account]', '', $formattedMessage);
		 $formattedMessage = str_replace('[RESA_link_payment_booking]', '', $formattedMessage);
		 $formattedMessage = str_replace('[RESA_expiration_date]', '', $formattedMessage);
	 }
	 $formattedMessage = self::formatSimpleMessage($booking, $customer, $formattedMessage);
	 return $formattedMessage;
	}

	/**
	* send a message
	*/
	public static function sendMessage($to, $subject, $body){
		$result = false;
		if(defined('WP_RESA_DEBUG') && WP_RESA_DEBUG){
			$to = 'test@resa-online.fr';
		}
		if(!empty($to)){
			Logger::DEBUG(print_r($body, true));
			$subject = preg_replace('/\r\n|\r|\n|\t/', ' ', $subject);
			$body = preg_replace('/\r\n|\r|\n|\t/', ' ', $body); //echap eol
			$message = '<html><head><style>.ro-table{font-family:arial;clear:both;background-color:#e9e9e9;font-size:12px;text-align:left;margin-top:30px}.ro-table th{padding:5px;font-size:13px;border-bottom:1px solid grey}.ro-table,.ro-table tr{border-collapse:collapse;border-bottom:grey;word-wrap:break-word}.ro-table td{padding:5px;min-width:70px}.ro-soustotal{background-color:#c3c3c3;font-weight:700}.ro-total{background-color:#a7a7a7;font-weight:700}</style></head><body>'.$body.'</body></html>';
			$message = wordwrap($message, 70, "\r\n");
			Logger::INFO('send EMAIL to : ' . $to .' --- by ' . get_option('wp_mail_from') . ' -- ['.$subject.']' . '\n\n');
			$headers[] = 'MIME-Version: 1.0';
			$headers[] =  'Content-Type: text/html; charset=utf-8';
			if(!empty(get_option('wp_mail_from', ''))){
				$headers[] = 'From: '.get_option('wp_mail_from_name').' <'.get_option('wp_mail_from').'>';
			}
			$result = wp_mail( $to, $subject, $message, implode("\r\n", $headers));
			if(defined('WP_RESA_DEBUG') && WP_RESA_DEBUG) $result = true;
		}
		return $result;
	}


	public static function setWpMailWithCurrentCustomer(){
		$currentRESAUser = new RESA_Customer();
		$currentRESAUser->loadCurrentUser();
		$places = array();
		if(is_object($currentRESAUser->getFilterSettings()) && isset($currentRESAUser->getFilterSettings()->places)){
			$filterPlaces = (array)($currentRESAUser->getFilterSettings()->places);
			foreach($filterPlaces as $key => $value){
				if($value) {
					array_push($places, $key);
				}
			}
		}
		self::setWpMailWithIdPlaces($places);
	}


	public static function setWpMailWithBooking($booking){
		$idPlaces = null;
		if(!isset($idPlaces)) $idPlaces = $booking->getAllIdPlaces();
		self::setWpMailWithIdPlaces($idPlaces);
	}

	public static function setWpMailWithIdPlaces($idPlaces){
		$resaSender = NULL;
		$resaSenders = unserialize(get_option('resa_settings_senders'));
		foreach($resaSenders as $resaSenderAux){
			if(!isset($resaSender)){
				$resaSender = $resaSenderAux;
			}
			else if(isset($idPlaces)){
				$idPlace = $resaSenderAux->idPlace;
				if(in_array($idPlace, $idPlaces)){
					$resaSender = $resaSenderAux;
				}
			}
		}
		if(isset($resaSender) && isset($resaSender->sender_name)){
			update_option('wp_mail_from_name', esc_html($resaSender->sender_name));
			update_option('wp_mail_from', esc_html($resaSender->sender_email));
		}
	}
}

?>
