<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APINotificationsController
{

	/**
	 * return notifications
	 */
	private static function getNotifications(){
		$notifications = array();
		$notification = array(
			'id' => 'notification_1',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_booking')) == 'true',
			'name' => 'Nouvelle réservation',
			'description' => 'Email envoyé au client lorsqu\'il réalise une nouvelle réservation',
			'receiver' => 'Client',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_customer_booking_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_customer_booking_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_2',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_staff_booking')) == 'true',
			'name' => 'Nouvelle réservation',
			'description' => 'Email envoyé au membre du personnel assurant la où les activités présente lors d\'une nouvelle réservation',
			'receiver' => 'Membre du personnel',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_staff_booking_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_staff_booking_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_3',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_emails_booking')) == 'true',
			'name' => 'Nouvelle réservation',
			'description' => 'Email envoyé aux emails définies lors d\'une nouvelle réservation',
			'receiver' => 'Emails personnalisés',
			'emails' => get_option('resa_settings_notifications_email_notification_emails_booking_emails'),
			'placesEmails' => unserialize(get_option('resa_settings_notifications_email_notification_emails_booking_places_emails')),
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_emails_booking_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_emails_booking_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_4',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_before_appointment')) == 'true',
			'name' => 'Rappel X jours avant...',
			'description' => 'Email envoyé au client X jours avant la date de la première activité de sa réservation',
			'receiver' => 'Client',
			'daysAP' => intval(get_option('resa_settings_notifications_email_notification_before_appointment_days')),
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_before_appointment_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_before_appointment_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_5',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_after_appointment')) == 'true',
			'name' => 'Demande d\'avis X jours après...',
			'description' => 'Email envoyé au client X jours après la date de la dernière activités de sa réservation',
			'receiver' => 'Client',
			'daysAP' => intval(get_option('resa_settings_notifications_email_notification_after_appointment_days')),
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_after_appointment_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_after_appointment_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_6',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_payment')) == 'true',
			'name' => 'Paiement effectué',
			'description' => 'Email envoyé au client lorsqu\'il réalise un paiement en ligne',
			'receiver' => 'Client',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_customer_payment_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_customer_payment_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_7',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_emails_payment_booking')) == 'true',
			'name' => 'Paiement effectué',
			'description' => 'Email envoyé aux emails définies lorsqu\'un client lorsqu\'il réalise un paiement en ligne',
			'receiver' => 'Emails personnalisés',
			'emails' => get_option('resa_settings_notifications_email_notification_emails_payment_booking_emails'),
			'placesEmails' => unserialize(get_option('resa_settings_notifications_email_notification_emails_payment_booking_places_emails')),
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_emails_payment_booking_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_emails_payment_booking_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_8',
			'activated' => true,
			'name' => 'Demande de paiement',
			'description' => 'Email par défaut de demande de paiement à envoyer à un client',
			'receiver' => 'Client',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_customer_need_payment_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_customer_need_payment_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_9',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_quotation_customer')) == 'true',
			'name' => 'Demande de devis',
			'description' => 'Email envoyé au client lorsqu\'il réalise une demande de devis',
			'receiver' => 'Client',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_10',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_emails_quotation_requests')) == 'true',
			'name' => 'Demande de devis',
			'description' => 'Email envoyé aux emails définies lorsqu\'un client réalise une demande de devis',
			'receiver' => 'Emails personnalisés',
			'emails' => get_option('resa_settings_notifications_email_notification_emails_quotation_requests_emails'),
			'placesEmails' => unserialize(get_option('resa_settings_notifications_email_notification_emails_quotation_requests_places_emails')),
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_emails_quotation_requests_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_emails_quotation_requests_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_11',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_quotation_customer_booking')) == 'true',
			'name' => 'Création d\'un devis',
			'description' => 'Email par défaut à envoyer au client lorsqu\'un devis a été créé côté backend',
			'receiver' => 'Client',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_booking_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_booking_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_12',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer')) == 'true',
			'name' => 'Devis accepté',
			'description' => 'Email envoyé au client lorsque vous acceptez une demande de devis',
			'receiver' => 'Client',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_13',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_emails_quotation_answered')) == 'true',
			'name' => 'Devis accepté par le client',
			'description' => 'Email envoyé aux emails définies lorsqu\'un client accepte une demande de devis',
			'receiver' => 'Emails personnalisés',
			'emails' => get_option('resa_settings_notifications_email_notification_emails_quotation_answered_emails'),
			'placesEmails' => unserialize(get_option('resa_settings_notifications_email_notification_emails_quotation_answered_places_emails')),
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_emails_quotation_answered_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_emails_quotation_answered_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_14',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_no_response_quotation')) == 'true',
			'name' => 'Relance devis en attente',
			'description' => 'Email envoyé au client pour relancer son devis en attente lorsqu\'il a été créé côté backend',
			'receiver' => 'Client',
			'days' => intval(get_option('resa_settings_notifications_email_notification_no_response_quotation_days')),
			'times' => intval(get_option('resa_settings_notifications_email_notification_no_response_quotation_times')),
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_no_response_quotation_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_no_response_quotation_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_15',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_account_creation')) == 'true',
			'name' =>  'Création de compte client',
			'description' => 'Email envoyé au client lors d\'un création de compte',
			'receiver' => 'Client',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_customer_account_creation_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_customer_account_creation_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_16',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_password_reinit')) == 'true',
			'name' =>  'Lien vers le compte client',
			'description' => 'Email envoyé au client pour qu\'il puisse se reconnecter (ne pas oublier de définir le lien vers le compte client)',
			'receiver' => 'Client',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_customer_password_reinit_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_customer_password_reinit_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_17',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_ask_account')) == 'true',
			'name' =>  'Demande de compte client',
			'description' => 'Email envoyé au client lorsqu\'il réalise une demande de compte client (page de formulaire de demande de compte client nécessaire)',
			'receiver' => 'Client',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_customer_ask_account_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_customer_ask_account_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_18',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_emails_ask_account')) == 'true',
			'name' => 'Demande de compte client',
			'description' => 'Email envoyé aux emails définies lorsqu\'un client réalise une demande de compte client (page de formulaire de demande de compte client nécessaire)',
			'receiver' => 'Emails personnalisés',
			'emails' => get_option('resa_settings_notifications_email_notification_emails_ask_account_emails'),
			'placesEmails' => unserialize(get_option('resa_settings_notifications_email_notification_emails_ask_account_places_emails')),
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_emails_ask_account_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_emails_ask_account_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_19',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_accepted_account')) == 'true',
			'name' => 'Demande de compte acceptée',
			'description' => 'Email par défaut envoyé au client lorsque sa demande de compte a été acceptée',
			'receiver' => 'Client',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_customer_accepted_account_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_customer_accepted_account_text'))
		);
		array_push($notifications, (object)$notification);

		$notification = array(
			'id' => 'notification_20',
			'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_refused_account')) == 'true',
			'name' => 'Demande de compte refusée',
			'description' => 'Email par défaut envoyé au client lorsque sa demande de compte a été refusée',
			'receiver' => 'Client',
			'subject' => unserialize(get_option('resa_settings_notifications_email_notification_customer_refused_account_subject')),
			'text' => unserialize(get_option('resa_settings_notifications_email_notification_customer_refused_account_text'))
		);
		array_push($notifications, (object)$notification);
		return $notifications;
	}

	private static function echapEolTab($objectText){
		foreach($objectText as $key => $text){
			$objectText->$key = RESA_Tools::echapEolTab($text);
		}
		return $objectText;
	}

	private static function saveNotifications($notifications){
		foreach($notifications as $notification){
			if($notification->id == 'notification_1'){
				update_option('resa_settings_notifications_email_notification_customer_booking', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_customer_booking_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_customer_booking_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_2'){
				update_option('resa_settings_notifications_email_notification_staff_booking', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_staff_booking_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_staff_booking_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_3'){
				update_option('resa_settings_notifications_email_notification_emails_booking', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_emails_booking_emails', esc_html($notification->emails));
				update_option('resa_settings_notifications_email_notification_emails_booking_places_emails', serialize($notification->placesEmails));
				update_option('resa_settings_notifications_email_notification_emails_booking_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_emails_booking_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_4'){
				update_option('resa_settings_notifications_email_notification_before_appointment', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_before_appointment_days', esc_html($notification->daysAP));
				update_option('resa_settings_notifications_email_notification_before_appointment_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_before_appointment_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_5'){
				update_option('resa_settings_notifications_email_notification_after_appointment', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_after_appointment_days', esc_html($notification->daysAP));
				update_option('resa_settings_notifications_email_notification_after_appointment_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_after_appointment_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_6'){
				update_option('resa_settings_notifications_email_notification_customer_payment', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_customer_payment_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_customer_payment_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_7'){
				update_option('resa_settings_notifications_email_notification_emails_payment_booking', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_emails_payment_booking_emails', esc_html($notification->emails));
				update_option('resa_settings_notifications_email_notification_emails_payment_booking_places_emails', serialize($notification->placesEmails));
				update_option('resa_settings_notifications_email_notification_emails_payment_booking_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_emails_payment_booking_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_8'){
				update_option('resa_settings_notifications_email_notification_customer_need_payment_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_customer_need_payment_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_9'){
				update_option('resa_settings_notifications_email_notification_quotation_customer', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_quotation_customer_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_quotation_customer_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_10'){
				update_option('resa_settings_notifications_email_notification_emails_quotation_requests', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_emails_quotation_requests_emails', esc_html($notification->emails));
				update_option('resa_settings_notifications_email_notification_emails_quotation_requests_places_emails', serialize($notification->placesEmails));
				update_option('resa_settings_notifications_email_notification_emails_quotation_requests_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_emails_quotation_requests_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_11'){
				update_option('resa_settings_notifications_email_notification_quotation_customer_booking', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_quotation_customer_booking_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_quotation_customer_booking_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_12'){
				update_option('resa_settings_notifications_email_notification_quotation_accepted_customer', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_quotation_accepted_customer_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_quotation_accepted_customer_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_13'){
				update_option('resa_settings_notifications_email_notification_emails_quotation_answered', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_emails_quotation_answered_emails', esc_html($notification->emails));
				update_option('resa_settings_notifications_email_notification_emails_quotation_answered_places_emails', serialize($notification->placesEmails));
				update_option('resa_settings_notifications_email_notification_emails_quotation_answered_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_emails_quotation_answered_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_14'){
				update_option('resa_settings_notifications_email_notification_no_response_quotation', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_no_response_quotation_days', esc_html($notification->days));
				update_option('resa_settings_notifications_email_notification_no_response_quotation_times', esc_html($notification->times));
				update_option('resa_settings_notifications_email_notification_no_response_quotation_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_no_response_quotation_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_15'){
				update_option('resa_settings_notifications_email_notification_customer_account_creation', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_customer_account_creation_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_customer_account_creation_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_16'){
				update_option('resa_settings_notifications_email_notification_customer_password_reinit', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_customer_password_reinit_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_customer_password_reinit_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_17'){
				update_option('resa_settings_notifications_email_notification_customer_ask_account', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_customer_ask_account_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_customer_ask_account_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_18'){
				update_option('resa_settings_notifications_email_notification_emails_ask_account', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_emails_ask_account_emails', esc_html($notification->emails));
				update_option('resa_settings_notifications_email_notification_emails_ask_account_places_emails', serialize($notification->placesEmails));
				update_option('resa_settings_notifications_email_notification_emails_ask_account_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_emails_ask_account_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_19'){
				update_option('resa_settings_notifications_email_notification_customer_accepted_account', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_customer_accepted_account_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_customer_accepted_account_text', serialize(self::echapEolTab($notification->text)));
			}
			else if($notification->id == 'notification_20'){
				update_option('resa_settings_notifications_email_notification_customer_refused_account', esc_html($notification->activated));
				update_option('resa_settings_notifications_email_notification_customer_refused_account_subject', serialize(self::echapEolTab($notification->subject)));
				update_option('resa_settings_notifications_email_notification_customer_refused_account_text', serialize(self::echapEolTab($notification->text)));
			}
		}
	}


	public static function init(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$settings = array(
				'languages' => unserialize(get_option('resa_settings_languages')),
				'places' => unserialize(get_option('resa_settings_places')),
				'resa_senders' => unserialize(get_option('resa_settings_senders')),
				'notifications_templates' => unserialize(get_option('resa_settings_notifications_email_notifications_templates')),
				'custom_shortcodes' => unserialize(get_option('resa_settings_notifications_custom_shortcodes')),
			);
			$settings['notifications'] = self::getNotifications();
			$json = '{
				"settings":'.json_encode($settings).'
			}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function save(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(isset($data['notifications'])){
				$notifications = json_decode($data['notifications']);
				self::saveNotifications($notifications);
			}
			if(isset($data['notifications_templates'])){
				$notifications_templates = json_decode($data['notifications_templates']);
				update_option('resa_settings_notifications_email_notifications_templates', serialize($notifications_templates));
				update_option('resa_settings_notifications_email_notifications_templates_last_modification_date', (new DateTime())->format('Y-m-d H:i:s'));
			}
			if(isset($data['resa_senders'])){
				$resa_senders = json_decode($data['resa_senders']);
				update_option('resa_settings_senders', serialize($resa_senders));
			}
			if(isset($data['custom_shortcodes'])){
				$custom_shortcodes = json_decode($data['custom_shortcodes']);
				update_option('resa_settings_notifications_custom_shortcodes', serialize($custom_shortcodes));
			}
			$response->set_data(json_decode('{}'));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	/**
	 * Format subject and message
	 */
	public static function previewNotification(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$subject = '';
			$message = '';
			if(isset($data['subject'])) $subject = json_decode($data['subject']);
			if(isset($data['message'])) $message = json_decode($data['message']);

			$customer = new RESA_Customer();
			$customer->setLoaded(true);
			if(rand(0,1) == 0){
				$customer->setLastName('FONVIEILLE');
				$customer->setFirstName('Damien');
			}
			else {
				$customer->setLastName('FRANCES');
				$customer->setFirstName('Benjamin');
			}
			$customer->setCompany('360 Online');
			$customer->setEmail('360@online.fr');

			$booking = new RESA_Booking();
			$booking->setLoaded(true);
			$booking->setId(42);
			$booking->setPublicNote('Exemple de note');
			$booking->setAppointments(array(new RESA_Appointment()));

			foreach($subject as $key => $value){
				$subject->{$key} = RESA_Mailer::formatSimpleMessage($booking, $customer, $value);
			}
			foreach($message as $key => $value){
				$message->{$key} = RESA_Mailer::formatSimpleMessage($booking, $customer, $value);
			}
			$json = (object)array('subject' => $subject, 'message' => $message);
			$response->set_data($json);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function sendNotification(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$subject = '';
			$message = '';
			$email = '';
			if(isset($data['subject'])) $subject = ($data['subject']);
			if(isset($data['message'])) $message = ($data['message']);
			if(isset($data['email'])) $email = ($data['email']);

			$booking = new RESA_Booking();
			$booking->setId(42);
			$booking->setTotalPrice(100);
			$booking->setAdvancePayment(75);
			$booking->setPublicNote('Exemple de note');
			$booking->setAppointments(array(new RESA_Appointment()));


			$customer = new RESA_Customer();
			$customer->setLastName('Doe');
			$customer->setFirstName('Jhon');
			$customer->setCompany('Test');
			$customer->setLoaded(true);


			$result = RESA_Mailer::sendCustomMessage($booking, $customer, $email, $subject, $message);
			if(!$result){
				$response->set_status(401);
				$response->set_data(array('error' => 'error_send', 'message' => 'Error sending notification'));
			}
			else {
				$response->set_data(json_decode('{}'));
				}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function notificationsSettings(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$settings = array(
				'currency' => get_option('resa_settings_payment_currency'),
				'places' => unserialize(get_option('resa_settings_places')),
				'tags' => unserialize(get_option('resa_settings_custom_tags')),
				'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
				'askAdvancePaymentTypeAccounts' => unserialize(get_option('resa_settings_payment_ask_advance_payment_type_accounts')),
				'form_participants_parameters' => unserialize(get_option('resa_settings_form_participants_parameters')),
				'allLanguages' => RESA_Variables::getLanguages(),
				'languages' => unserialize(get_option('resa_settings_languages')),
				'customer_account_url' => get_option('resa_settings_customer_account_url'),
				'senders' => unserialize(get_option('resa_settings_senders')),
				'paymentsTypeList' => RESA_Variables::paymentsTypeList(),
				'paymentsType' => RESA_Variables::idPaymentsTypeToName(),
 				'notifications_templates' => unserialize(get_option('resa_settings_notifications_email_notifications_templates')),
				'notifications' => array(
					'notification_customer_booking' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_booking')) == 'true',
					'notification_customer_password_reinit' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_password_reinit')) == 'true',
					'notification_quotation_accepted_customer' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer')) == 'true',
					'notification_customer_accepted_account' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_accepted_account')) == 'true',
					'notification_after_appointment' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_after_appointment')) == 'true',
					'notification_customer_booking_subject' => unserialize(get_option('resa_settings_notifications_email_notification_customer_booking_subject')),
					'notification_customer_booking_text' => unserialize(get_option('resa_settings_notifications_email_notification_customer_booking_text')),
					'notification_after_appointment_subject' => unserialize(get_option('resa_settings_notifications_email_notification_after_appointment_subject')),
					'notification_after_appointment_text' => unserialize(get_option('resa_settings_notifications_email_notification_after_appointment_text')),
					'notification_customer_need_payment_subject' => unserialize(get_option('resa_settings_notifications_email_notification_customer_need_payment_subject')),
					'notification_customer_need_payment_text' => unserialize(get_option('resa_settings_notifications_email_notification_customer_need_payment_text')),
					'notification_quotation_customer_booking_subject' => unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_booking_subject')),
					'notification_quotation_customer_booking_text' => unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_booking_text')),
					'notification_quotation_accepted_customer_booking_subject' => unserialize(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer_subject')),
					'notification_quotation_accepted_customer_booking_text' => unserialize(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer_text')),
					'notification_quotation_customer_booking_text' => unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_booking_text')),
					'notifications_templates' => unserialize(get_option('resa_settings_notifications_email_notifications_templates')),
					'notifications_templates_last_modification_date' => get_option('resa_settings_notifications_email_notifications_templates_last_modification_date', (new DateTime())->format('Y-m-d H:i:s'))
				)
			);
			$json = '{
				"settings":'.json_encode($settings).'
			}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function sendNotificationPassword(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try {
				if(isset($data['idCustomer'])){
					$customer = new RESA_Customer();
					$customer->loadByIdWithoutBookings($data['idCustomer']);
					if(!RESA_Mailer::sentMessagePasswordReinitialization($customer, $currentRESAUser->getId())){
						throw new Exception('Erreur dans l\'envoie de l\'email');
					}
					$logNotification = RESA_Algorithms::generateLogNotification(13, new RESA_Booking(), $customer, $currentRESAUser);
					if(isset($logNotification))	$logNotification->save();
					$response->set_data(json_decode('{"result":"ok"}'));
				}
				else {
					throw new Exception('Client non définie');
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'error_send', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function sendEmailToCustomer(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try {
				$idBooking = -1;
				$subject = '';
				$message = '';
				if(isset($data['idBooking']) && is_numeric($data['idBooking'])){
					$idBooking = $data['idBooking'];
				}
				if(isset($data['subject'])){
					$subject = json_decode(stripslashes(sanitize_text_field($data['subject'])));
				}
				if(isset($data['message'])){
					$message = json_decode($data['message']);
				}
				if(empty($subject) || empty($message)){ throw new Exception('Erreur message ou sujet vide'); }
				$booking = new RESA_Booking();
				$booking->loadById($idBooking);
				if(!$booking->isQuotation()){
					$result = RESA_Mailer::sendMessageBooking($booking, false, true, $subject, $message, $currentRESAUser->getId());
					if(!$result['customer']) throw new Exception('Erreur envoie au client');
					if(!$result['emails']) throw new Exception('Erreur envoie aux emails définies');
					if(!$result['members']) throw new Exception('Erreur envoie aux membres du staff associés');
					$booking->save();
					$customer = $booking->getCustomer();
					$logNotification = RESA_Algorithms::generateLogNotification(14, $booking, $customer, $currentRESAUser);
					if(isset($logNotification))	$logNotification->save();
				}
				else if($booking->isQuotation()){
					RESA_Mailer::sendMessageQuotation($booking, true, $subject, $message, $currentRESAUser->getId());
					$customer = $booking->getCustomer();
					$logNotification = RESA_Algorithms::generateLogNotification(17, $booking, $customer, $currentRESAUser);
					if(isset($logNotification))	$logNotification->save();
					$booking->setNumberSentEmailQuotation(0);
					$booking->clearModificationDate();
					$booking->save();
				}
				$response->set_data(json_decode('{"result":"ok"}'));
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'error_send', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function askPayment(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try {
				if(isset($data['idBookings']) && isset($data['paymentsType']) && isset($data['stopAdvancePayment']) &&
					isset($data['expiredDays']) && isset($data['subject']) && isset($data['message']) && isset($data['language'])){
					$paymentsType = json_decode(stripslashes(sanitize_text_field($data['paymentsType'])));
					$idBookings = json_decode(stripslashes(sanitize_text_field($data['idBookings'])));
					$stopAdvancePayment = json_decode(stripslashes(sanitize_text_field($data['stopAdvancePayment'])));
					$expiredDays = json_decode(stripslashes(sanitize_text_field($data['expiredDays'])));
					$subject = json_decode(stripslashes(sanitize_text_field($data['subject'])));
					$message = json_decode(($data['message']));
					$language = stripslashes(sanitize_text_field($data['language']));
					if(empty($subject) || empty($message)){ throw new Exception('Erreur message ou sujet vide'); }

					$bookings = array();
					foreach($idBookings as $idBooking){
						$booking = new RESA_Booking();
						$booking->loadById($idBooking);
						array_push($bookings, $booking);
					}
					if(count($bookings) == 0){
						throw new Exception('Error in askPayment no bookings selected');
					}

					$success = true;
					if(in_array('swikly', $paymentsType) && class_exists('RESA_Swikly') && RESA_Swikly::isSentSwikWithAskPayment()){
						$data = RESA_Swikly::swikly($bookings[0], $bookings[0]->getCustomer(), '', true);
						if($data['success'] == 'error'){
							throw new \Exception("Error Swikly", 1);
						}
					}

					if(!RESA_Mailer::sendMessageNeedPayment($bookings, $paymentsType, $stopAdvancePayment, $expiredDays, $subject, $message, $currentRESAUser->getId(), $language)){
						throw new Exception('Erreur dans l\'envoie de l\'email');
					}


					$textualPaymentTypes = '';
					foreach($paymentsType as $type){
						if(!empty($type)){
							if($textualPaymentTypes!='')
								$textualPaymentTypes .= ',';
							$textualPaymentTypes .= $type;
						}
					}

					//Save ask payment
					foreach($bookings as $booking){
						$askPayment = new RESA_AskPayment();
						$askPayment->setIdBooking($booking->getId());
						$askPayment->setIdUserCreator($currentRESAUser->getId());
						$askPayment->setTypesPayment($textualPaymentTypes);
						$askPayment->setValue($booking->getNeedToPay());
						$askPayment->setTypeAdvancePayment($stopAdvancePayment);
						$askPayment->calculateExpiredDate($expiredDays);

						$allAskPayments = $booking->getAskPayments();
						array_push($allAskPayments, $askPayment);
						$booking->setAskPayments($allAskPayments);
						$booking->clearModificationDate();
						$booking->save(false);

						$customer = $booking->getCustomer();
						$logNotification = RESA_Algorithms::generateLogNotification(16, $booking, $customer, $currentRESAUser);
						if(isset($logNotification))	$logNotification->save();
					}
					$response->set_data(json_decode('{"result":"ok"}'));
				}
				else {
					throw new Exception("Error");
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'error_send', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function sendCustomEmail(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try {
				$idCustomer = json_decode(stripslashes(sanitize_text_field($data['idCustomer'])));
				$customer = new RESA_Customer();
				$customer->loadByIdWithoutBookings($idCustomer);

				$idBooking = json_decode(stripslashes(sanitize_text_field($data['idBooking'])));
				$booking = new RESA_Booking();
				$booking->loadById($idBooking);

				$email = json_decode(stripslashes(sanitize_text_field($data['email'])));
				$subject = json_decode(stripslashes(sanitize_text_field($data['subject'])));
				$message = json_decode($data['message']);

				$sender = json_decode(stripslashes(sanitize_text_field($data['sender'])));
				if(empty($subject) || empty($message)){ throw new Exception('Erreur message ou sujet vide'); }
				$places = array();
				if(is_object($currentRESAUser->getFilterSettings()) && isset($currentRESAUser->getFilterSettings()->places)){
					$filterPlaces = (array)($currentRESAUser->getFilterSettings()->places);
					foreach($filterPlaces as $key => $value){
						if($value) {
							array_push($places, $key);
						}
					}
				}
				$result = RESA_Mailer::sendCustomMessage($booking, $customer, $email, $subject, $message, $currentRESAUser->getId(), $sender);
				if(!$result) throw new Exception('Erreur');
				if($email == $customer->getEmail() && $result){
					$logNotification = RESA_Algorithms::generateLogNotification(15, $booking, $customer, $currentRESAUser);
					$logNotification->addIdPlaces($places);
					if(isset($logNotification))	$logNotification->save();
				}
				$response->set_data(json_decode('{"result":"ok"}'));
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'error_send', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function acceptQuotationBackend(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try {
				if(isset($data['idBooking']) && isset($data['subject']) && isset($data['message']) && isset($data['language'])){
					$idBooking = json_decode(stripslashes(sanitize_text_field($data['idBooking'])));
					$booking = new RESA_Booking();
					$booking->loadById($idBooking);
					if($booking->isLoaded() && $booking->isQuotation() && $booking->isQuotationRequest()){
						$subject = json_decode(stripslashes(sanitize_text_field($data['subject'])));
						$message = json_decode($data['message']);
						$language = stripslashes(sanitize_text_field($data['language']));
						if(empty($subject) || empty($message)){ throw new Exception('Erreur message ou sujet vide'); }
						if(!RESA_Mailer::sendMessageQuotationAccepted($booking, true, $subject, $message, $currentRESAUser->getId())){
							throw new Exception('Erreur dans l\'envoie de l\'email');
						}
						$oldBooking = new RESA_Booking();
						$oldBooking->loadById($idBooking);
						if($oldBooking->isLoaded() && $oldBooking->getIdCustomer() == $booking->getIdCustomer()){
							if(!$oldBooking->isOldBooking()){
								$oldBooking->setOldBooking(true);
								foreach($oldBooking->getAppointments() as $appointment){
									$appointment->setState('updated');
								}
								$booking->setLinkOldBookings($oldBooking->getLinkOldBookings());
								$booking->addLinkOldBookings($oldBooking->getId());
								$booking->setAdvancePayment($oldBooking->getAdvancePayment());
								$booking->setCreationDate($oldBooking->getCreationDate());
								$booking->setTypePaymentChosen($oldBooking->getTypePaymentChosen());
								$booking->setTransactionId($oldBooking->getTransactionId());
								$booking->setIdCreation($oldBooking->getIdCreation());
								$booking->setPaymentState($oldBooking->getPaymentState());
								$booking->setQuotation($oldBooking->isQuotation());
								$booking->setNew();
								$oldBooking->save();
							}
							else {
								throw new Exception(__('create_new_version_bookings_error', 'resa'));
							}
						}
						$booking->clearModificationDate();
						$booking->clearLastDateSentEmailQuotation();
						$booking->setAlreadySentEmail(false);
						$booking->setQuotationRequest(false);
						$booking->save();
						$customer = $booking->getCustomer();
						$logNotification = RESA_Algorithms::generateLogNotification(7, $booking, $customer, $currentRESAUser);
						if(isset($logNotification))	$logNotification->save();
						//TODO
						$response->set_data(json_decode('{"result":"ok"}'));
					}
				}
				else {
					throw new Exception('error');
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'error_send', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function acceptQuotationAndAskPayment(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try {
				if(isset($data['paymentsType']) && isset($data['stopAdvancePayment']) && isset($data['expiredDays']) && isset($data['subject']) && isset($data['message']) && isset($data['idBooking']) && isset($data['language'])){
					$paymentsType = json_decode(stripslashes(sanitize_text_field($data['paymentsType'])));
					$stopAdvancePayment = json_decode(stripslashes(sanitize_text_field($data['stopAdvancePayment'])));
					$expiredDays = json_decode(stripslashes(sanitize_text_field($data['expiredDays'])));
					$subject = json_decode(stripslashes(sanitize_text_field($data['subject'])));
					$message = json_decode($data['message']);
					$idBooking = json_decode(stripslashes(sanitize_text_field($data['idBooking'])));
					$language = stripslashes(sanitize_text_field($data['language']));
					$booking = new RESA_Booking();
					$booking->loadById($idBooking);
					if($booking->isLoaded() && $booking->isQuotation() && $booking->isQuotationRequest()){
						if(!RESA_Mailer::sendMessageQuotationAccepted($booking, true, $subject, $message, $currentRESAUser->getId(), $paymentsType, $stopAdvancePayment, $expiredDays)){
							throw new Exception('Erreur dans l\'envoie de l\'email');
						}
						$oldBooking = new RESA_Booking();
						$oldBooking->loadById($idBooking);
						if($oldBooking->isLoaded() && $oldBooking->getIdCustomer() == $booking->getIdCustomer()){
							if(!$oldBooking->isOldBooking()){
								$oldBooking->setOldBooking(true);
								foreach($oldBooking->getAppointments() as $appointment){
									$appointment->setState('updated');
								}
								$booking->setLinkOldBookings($oldBooking->getLinkOldBookings());
								$booking->addLinkOldBookings($oldBooking->getId());
								$booking->setAdvancePayment($oldBooking->getAdvancePayment());
								$booking->setCreationDate($oldBooking->getCreationDate());
								$booking->setTypePaymentChosen($oldBooking->getTypePaymentChosen());
								$booking->setTransactionId($oldBooking->getTransactionId());
								$booking->setIdCreation($oldBooking->getIdCreation());
								$booking->setPaymentState($oldBooking->getPaymentState());
								$booking->setQuotation($oldBooking->isQuotation());
								$booking->setNew();
								$oldBooking->save();
							}
							else {
								throw new Exception(__('create_new_version_bookings_error', 'resa'));
							}
						}
						$booking->clearModificationDate();
						$booking->clearLastDateSentEmailQuotation();
						$booking->setAlreadySentEmail(false);
						$booking->setQuotationRequest(false);
						$booking->save();
						$customer = $booking->getCustomer();
						$logNotification = RESA_Algorithms::generateLogNotification(7, $booking, $customer, $currentRESAUser);
						if(isset($logNotification))	$logNotification->save();
						$customer->setLocale($language);
						$customer->save(false);


						$bookings = array();
						array_push($bookings, $booking);
						$success = true;
						if(in_array('swikly', $paymentsType) && class_exists('RESA_Swikly') && RESA_Swikly::isSentSwikWithAskPayment()){
							$data = RESA_Swikly::swikly($bookings[0], $bookings[0]->getCustomer(), '', true);
							if($data['success'] == 'error'){
								throw new \Exception("Error Swikly", 1);
							}
						}

						if(!RESA_Mailer::sendMessageNeedPayment($bookings, $paymentsType, $stopAdvancePayment, $expiredDays, $subject, $message, $currentRESAUser->getId(), $language)){
							throw new Exception('Erreur dans l\'envoie de l\'email');
						}

						$textualPaymentTypes = '';
						foreach($paymentsType as $type){
							if(!empty($type)){
								if($textualPaymentTypes!='')
									$textualPaymentTypes .= ',';
								$textualPaymentTypes .= $type;
							}
						}

						//Save ask payment
						foreach($bookings as $booking){
							$askPayment = new RESA_AskPayment();
							$askPayment->setIdBooking($booking->getId());
							$askPayment->setIdUserCreator($currentRESAUser->getId());
							$askPayment->setTypesPayment($textualPaymentTypes);
							$askPayment->setValue($booking->getNeedToPay());
							$askPayment->setTypeAdvancePayment($stopAdvancePayment);
							$askPayment->calculateExpiredDate($expiredDays);

							$allAskPayments = $booking->getAskPayments();
							array_push($allAskPayments, $askPayment);
							$booking->setAskPayments($allAskPayments);
							$booking->clearModificationDate();
							$booking->save(false);

							$customer = $booking->getCustomer();
							$logNotification = RESA_Algorithms::generateLogNotification(16, $booking, $customer, $currentRESAUser);
							if(isset($logNotification))	$logNotification->save();
						}
						//TODO
						$response->set_data(json_decode('{"result":"ok"}'));
					}
				}
				else {
					throw new Exception('error');
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'error_send', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
