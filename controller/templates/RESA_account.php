<section ng-app="resa_app" id="ro_front_formulaire" ng-controller="AccountController as accountCtrl"
	ng-init='accountCtrl.initialize("<?php echo admin_url('admin-ajax.php'); ?>", "<?php echo $variables['currentUrl']; ?>", <?php echo htmlspecialchars($variables['countries'], ENT_QUOTES); ?>)' ng-cloak>
	<div ng-if="!accountCtrl.isBrowserCompatibility()" style="color:red; font-weight:bold">
    <span ng-bind-html="accountCtrl.getTextByLocale(accountCtrl.settings.browser_not_compatible_sentence, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></span>
	</div>
	<div class="welcome_client" ng-if="!accountCtrl.dataInitializated">
		<h2><?php _e('loading_customer_data_words', 'resa'); ?></h2>
	</div>
	<div ng-if="accountCtrl.dataInitializated">
		<div class="welcome_client" ng-if="!accountCtrl.isConnected()">
			<h2 class="size_xl"><?php _e('Hello_word', 'resa'); ?>, <?php _e('already_customer_here_words', 'resa'); ?></h2>
			<p><?php _e('If_yes_words', 'resa'); ?>, <?php _e('connect_you_to_account_words', 'resa'); ?></p>
			<a ng-if="accountCtrl.settings.facebook_activated" ng-click="accountCtrl.login()" class="facebook_connection"></a>
			<div class="login">
				<div class="login_client">
					<form>
						<input class="input_wide" ng-model="accountCtrl.customer.login" type="text" placeholder="<?php _e('email_field_title', 'resa') ?>" />
						<input ng-init="accountCtrl.customer.passwordConnection=''" ng-model="accountCtrl.customer.passwordConnection" class="input_wide" type="password" placeholder="<?php _e('password_field_title', 'resa') ?>" />
						<input class="input_wide btn" ng-class="{'btn_locked':(accountCtrl.customer.passwordConnection.length == 0|| accountCtrl.customer.login.length== 0)}" ng-click="(accountCtrl.customer.passwordConnection.length == 0|| accountCtrl.customer.login.length== 0) || accountCtrl.userConnection()" type="submit" value="<?php _e('to_login_link_title', 'resa') ?>" />
						<a ng-click="accountCtrl.fotgottenPassword({
							title: '<?php _e('forgotten_password_title_dialog','resa') ?>',
							text: '<?php _e('forgotten_password_text_dialog','resa') ?>',
							confirmButton: '<?php _e('forgotten_password_confirmButton_dialog','resa') ?>',
							cancelButton: '<?php _e('forgotten_password_cancelButton_dialog','resa') ?>',
							inputPlaceholder: '<?php _e('forgotten_password_inputPlaceholder_dialog','resa') ?>'
						})"><?php _e('fotgotten_password_link_title', 'resa'); ?> ?</a>
					</form>
				</div>
			</div>
		</div>
	  <div class="infos_clients" ng-if="accountCtrl.isConnected()">
	    <div class="edit_infos_client">
	      <div class="edit_infos_client_titles">
	        <h2 class="size_xl"><?php _e('Hello_word', 'resa'); ?><span ng-if="accountCtrl.customer.firstName.length > 0 || accountCtrl.customer.lastName.length > 0"> {{ accountCtrl.customer.firstName }}  {{ accountCtrl.customer.lastName }}</span><span ng-if="accountCtrl.customer.firstName.length == 0 && accountCtrl.customer.lastName.length == 0">{{ accountCtrl.customer.company }}</span></h2>
	        <p><?php _e('you_is_not_words', 'resa'); ?> ? <a ng-click="accountCtrl.userDeconnection()"><?php _e('logout_link_title', 'resa'); ?></a></p>
	      </div>
	    </div>
	  </div>
		<?php include_once('RESA_receipt.php'); ?>
	  <div id="resa_account" ng-if="accountCtrl.isConnected()">
	    <label ng-click="accountCtrl.seeBookings() && accountCtrl.setBooking()" ng-class="{'active':accountCtrl.isSeeBookings()}" for="tab1"><?php _e('my_reservations', 'resa'); ?></label>
	    <label ng-click="accountCtrl.seeQuotations() && accountCtrl.setBooking()" ng-class="{'active':accountCtrl.isSeeQuotations()}"  for="tab2"><?php _e('my_quotations', 'resa'); ?></label>
	    <label ng-click="accountCtrl.seePersonalInformations()" ng-class="{'active':accountCtrl.isSeePersonalInformations()}" for="tab3"><?php _e('my_personal_informations', 'resa'); ?></label>
	    <section ng-if="accountCtrl.isSeeBookings()" id="bookings">
	      <div ng-if="!accountCtrl.isBookingLoaded()">
	        <span class="filters_menu_title"><?php _e('see_bookings', 'resa'); ?> : </span>
	        <div class="filters_menu">
	          <button class="dropbtn">
							<span ng-if="accountCtrl.filterDateBooking == 0"><?php _e('to_come', 'resa'); ?></span>
							<span ng-if="accountCtrl.filterDateBooking == 1"><?php _e('past', 'resa'); ?></span>
							<span ng-if="accountCtrl.filterDateBooking == 2"><?php _e('all_feminin', 'resa'); ?></span>
						</button>
	          <div class="dropdown-content">
	            <a ng-if="accountCtrl.filterDateBooking != 0" ng-click="accountCtrl.filterDateBooking = 0"><?php _e('to_come', 'resa'); ?></a>
	            <a ng-if="accountCtrl.filterDateBooking != 1" ng-click="accountCtrl.filterDateBooking = 1"><?php _e('past', 'resa'); ?></a>
	            <a ng-if="accountCtrl.filterDateBooking != 2" ng-click="accountCtrl.filterDateBooking = 2"><?php _e('all_feminin', 'resa'); ?></a>
	          </div>
	        </div>
	      </div>
	      <div class="bookings">
	        <div class="resa_clients" id="step6_1">
	        <div id="cart" ng-if="!booking.quotation && ((!accountCtrl.isBookingLoaded() && !accountCtrl.isFiltered(booking)) || accountCtrl.booking.id == booking.id)" ng-repeat="booking in accountCtrl.customer.bookings">
	          <h4 class="size_xl client_resa_title cart_title">
	            <span ng-if="booking.intervals.length > 0"><?php _e('Booking_word', 'resa'); ?> n°{{ accountCtrl.getBookingId(booking) }} <?php _e('booking_first_date_link_word', 'resa'); ?> {{ accountCtrl.parseDate(booking.intervals[0].interval.startDate) | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</span>
	          </h4>
	          <h5 class="center italic"><?php _e('booking_created_words', 'resa'); ?> {{ booking.creationDate | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</h5>

	          <div class="resa_state"><?php _e('booking_status_words', 'resa'); ?>:
	            <b class="vert" ng-if="booking.status == 'ok'"><?php _e('booking_status_ok_word', 'resa'); ?></b>
	            <b class="jaune" ng-if="booking.status == 'waiting'">{{ accountCtrl.getWaitingName(booking, '<?php _e('payment_word', 'resa'); ?>', '<?php _e('expired_word', 'resa'); ?>') }}</b>
	            <b class="rouge" ng-if="booking.status == 'cancelled'"><?php _e('booking_status_cancelled_word', 'resa'); ?></b>
	          </div>
	          <div class="resa_state"><?php _e('payment_status_words', 'resa'); ?>:
	            <b class="vert" ng-if="booking.paymentState == 'complete'"><?php _e('payment_status_complete_word', 'resa'); ?></b>
	            <b class="jaune" ng-if="booking.paymentState == 'advancePayment'"><?php _e('payment_status_advancePayment_word', 'resa'); ?></b>
			        <b class="jaune" ng-if="booking.paymentState == 'deposit'"><?php _e('payment_status_deposit_word', 'resa'); ?></b>
	            <b class="rouge" ng-if="booking.paymentState == 'noPayment'"><?php _e('payment_status_noPayment_word', 'resa'); ?></b>
	          </div>
	          <div class="resa_public_note" ng-if="booking.publicNote.length > 0">
	            <h4><?php _e('note_word', 'resa'); ?> : </h4>
	            <p ng-bind-html="booking.publicNote|htmlSpecialDecode:true"></p>
	          </div>
	          <div class="resa_public_note" ng-if="booking.customerNote.length > 0">
	            <h4><?php _e('Customer_note_word', 'resa'); ?> : </h4>
	            <p ng-bind-html="booking.customerNote|htmlSpecialDecode:true"></p>
	          </div>
	          <button class="btn btn_resa_detail" ng-click="booking.showAppointments = !booking.showAppointments" type="button" value="" ><?php _e('See_booking_details_words', 'resa'); ?></button>
	          <div class="cart_content" ng-if="booking.showAppointments">
	            <div class="service_section" ng-if="accountCtrl.isDisplayed(booking, appointment)" ng-repeat="appointment in booking.appointments">
	              <h4 class="service_title size_l lh_xl">
									<span ng-if="accountCtrl.getServiceById(appointment.idService).places.length > 0">[
	                  <span ng-repeat="place in accountCtrl.getServiceById(appointment.idService).places">
	                    <span ng-if="$index > 0">, </span>
	                    {{ accountCtrl.getTextByLocale(accountCtrl.getPlaceById(place).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
	                  </span>]
	                </span>
									{{ accountCtrl.getTextByLocale(accountCtrl.getServiceById(appointment.idService).name, '<?php echo get_locale(); ?>') }}
								</h4>
	              <div class="cart_row" ng-class="{'combined_row':accountCtrl.isCombinedRow(booking, appointment) }" ng-repeat-start="appointmentNumberPrice in appointment.appointmentNumberPrices">
	                <div class="date_selected size_xl" ng-class="{'cancelled':appointment.state == 'cancelled', 'pending': appointment.state == 'waiting', 'valid': appointment.state == 'ok'}" ng-if="!accountCtrl.isCombinedRow(booking, appointment)">
	                  <div class="rdv_state" ng-if="appointment.state == 'waiting'"><?php _e('waiting_status_word', 'resa'); ?></div>
	                  <div class="rdv_state" ng-if="appointment.state == 'ok'"><?php _e('ok_status_word', 'resa'); ?></div>
	                  <div class="rdv_state" ng-if="appointment.state == 'cancelled'"><?php _e('cancelled_status_word', 'resa'); ?></div>
	                  <h5 class="date">{{ appointment.startDate | formatDate:'<?php echo $variables['date_format']; ?>' }}</h5>
	                  <h5 class="creneau" ng-if="!appointment.noEnd">{{ appointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ appointment.endDate | formatDateTime:'<?php echo $variables['time_format']; ?>' }}</h5>
	                  <h5 class="creneau" ng-if="appointment.noEnd"><?php _e('begin_word','resa') ?> {{ appointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</h5>
	                </div>
	                <div class="date_selected size_xl" ng-if="accountCtrl.isCombinedRow(booking, appointment)">
	                  <div class="absence_title size_s" ng-if="booking.appointments.length == 1"><?php _e('Date_and_hours_word', 'resa'); ?></div>
	                  <div class="absence_title size_s" ng-if="booking.appointments.length > 1"><?php _e('Dates_and_hours_word', 'resa'); ?></div>
	                  <div  class="date_multi" ng-repeat="displayAppointment in accountCtrl.getAppointmentsByInternalIdLink(booking, appointment.internalIdLink)">
	                    <span class="date_creneau" ng-class="{'cancelled':displayAppointment.state == 'cancelled', 'pending': displayAppointment.state == 'waiting', 'valid': displayAppointment.state == 'ok'}">
	                      <div class="rdv_state" ng-if="displayAppointment.state == 'waiting'"><?php _e('waiting_status_word', 'resa'); ?></div>
	                      <div class="rdv_state" ng-if="displayAppointment.state == 'ok'"><?php _e('ok_status_word', 'resa'); ?></div>
	                      <div class="rdv_state" ng-if="displayAppointment.state == 'cancelled'"><?php _e('cancelled_status_word', 'resa'); ?></div>
	                      <h5 class="date">{{ displayAppointment.startDate | formatDate:'<?php echo $variables['date_format']; ?>' }}</h5>
	                      <h5 class="creneau" ng-if="!displayAppointment.noEnd">{{ displayAppointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ displayAppointment.endDate | formatDateTime:'<?php echo $variables['time_format']; ?>' }}</h5>
	                      <h5 class="creneau" ng-if="displayAppointment.noEnd"><?php _e('begin_word','resa') ?> {{ displayAppointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</h5>
	                    </span>
	                  </div>
	                </div>
	                <div class="order_recap clear">
	                  <span class="order_recap_left fleft"> <span class="order_recap_price_quantity size_l">{{ appointmentNumberPrice.number }}</span> <span class="order_recap_price_quantity_separator size_l"><span ng-show="appointmentNumberPrice.number==1 && !accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).extra"><?php _e('person_word', 'resa'); ?></span><span ng-show="appointmentNumberPrice.number>1 && !accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).extra"><?php _e('persons_word', 'resa'); ?></span></span> </span>
	                  <span class="order_recap_center fleft"> <span class="order_recap_price_title size_l">
	                    {{ accountCtrl.getTextByLocale(accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).name,'<?php echo get_locale(); ?>') }}&nbsp;
	                    <span ng-if="accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
	                      {{ accountCtrl.round(accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).price * accountCtrl.getNumberCombined(booking, appointment.internalIdLink))	}} {{ accountCtrl.settings.currency }}
	                    </span>
	                  </span></span>
	                  <span ng-if="!appointmentNumberPrice.deactivated" class="order_recap_right right fright">
	                    <span class="order_recap_price_total size_l">
	                      {{ accountCtrl.round(accountCtrl.getPriceNumberPrice(appointment.idService, appointmentNumberPrice, appointment)  * accountCtrl.getNumberCombined(booking, appointment.internalIdLink)) }}{{ accountCtrl.settings.currency }}
	                    </span>
	                  </span>
	                </div>
	                <div class="price_description">{{ accountCtrl.getTextByLocale(accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).description,'<?php echo get_locale(); ?>')
	                }}</div>
	                <div class="clients_informations" ng-if="accountCtrl.getServiceById(appointment.idService).askParticipants  && accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).participantsParameter != null && accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).participantsParameter.length > 0">
	                  <h4 class="clients_informations_title size_m"><?php _e('Participants_words', 'resa'); ?></h4>
	                  <table class="recap_client_information size_s">
	                    <thead>
	                      <tr>
	                        <td ng-repeat="field in accountCtrl.getParticipantsParameter(accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">
	                          {{ accountCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
	                        </td>
	                      </tr>
	                    </thead>
	                    <tbody>
	                      <tr ng-repeat="participant in appointmentNumberPrice.participants track by $index"><td ng-repeat="field in accountCtrl.getParticipantsParameter(accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">{{ accountCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ accountCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{ accountCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</td></tr>
	                    </tbody>
	                  </table>
	                </div>
	              </div>
	              <div class="reduction_row" ng-if="appointmentReduction.idPrice == appointmentNumberPrice.idPrice" ng-repeat-end ng-repeat="appointmentReduction in appointment.appointmentReductions">
	                <div class="reduction_title size_m">{{ accountCtrl.getTextByLocale(accountCtrl.getReductionById(appointmentReduction.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ appointmentReduction.number }})</div>
	                <div class="reduction_description size_xs" ng-bind-html="accountCtrl.getTextByLocale(accountCtrl.getReductionById(appointmentReduction.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
	                <div class="reduction_effect size_m b2">
	                  <span ng-if="appointmentReduction.type == 0">{{ appointmentReduction.value|negative }}{{ accountCtrl.settings.currency }}</span>
	                  <span ng-if="appointmentReduction.type == 1">{{ appointmentReduction.value|negative }}%</span>
	                  <span ng-if="appointmentReduction.type == 2">{{ appointmentReduction.value|negative }}{{ accountCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
	                  <span ng-if="appointmentReduction.type == 3">{{ appointmentReduction.value }}{{ accountCtrl.settings.currency }} <span class="tarif_barre"> {{accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentReduction.idPrice).price }}{{ accountCtrl.settings.currency }}</span></span>
	                  <span ng-if="appointmentReduction.type == 4">{{ appointmentReduction.value }} <?php _e('offer_quantity_words', 'resa') ?></span>
	                  <span ng-if="appointmentReduction.type == 5">{{ appointmentReduction.value }}</span>
	                </div>
	                <div class="reduction_total size_xl">
	                  <span ng-if="appointmentReduction.type == 0">{{ (appointmentReduction.value * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                  <span ng-if="appointmentReduction.type == 1">{{ ((accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).price * appointmentReduction.number) * appointmentReduction.value  / 100)|negative }}{{ accountCtrl.settings.currency }} </span>
	                  <span ng-if="appointmentReduction.type == 2">{{ (appointmentReduction.value * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                  <span ng-if="appointmentReduction.type == 3">{{ ((accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentReduction.idPrice).price - appointmentReduction.value) * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                  <!-- notThresholded !-->
	                  <span ng-if="appointmentReduction.type == 4" ng-if="accountCtrl.getReductionById(appointmentReduction.idReduction).notThresholded">{{ (accountCtrl.getReductionById(appointmentReduction.idReduction).price * appointmentReduction.value * appointmentReduction.number)|negative }} {{ accountCtrl.settings.currency }}</span>
	                  <span ng-if="appointmentReduction.type == 5"></span>
	                </div>
	              </div>
	              <div class="reduction_row" ng-if="appointmentReduction.idPrice == -1" ng-repeat="appointmentReduction in appointment.appointmentReductions">
	                <div class="reduction_title size_m">{{ accountCtrl.getTextByLocale(accountCtrl.getReductionById(appointmentReduction.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ appointmentReduction.number }})</div>
	                <div class="reduction_description size_xs" ng-bind-html="accountCtrl.getTextByLocale(accountCtrl.getReductionById(appointmentReduction.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
	                <div class="reduction_effect size_m b2">
	                  <span ng-if="appointmentReduction.type == 0">{{ appointmentReduction.value|negative }}{{ accountCtrl.settings.currency }}</span>
	                  <span ng-if="appointmentReduction.type == 1">{{ appointmentReduction.value|negative }}%</span>
	                  <span ng-if="appointmentReduction.type == 2">{{ appointmentReduction.value|negative }}{{ accountCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
	                  <span ng-if="appointmentReduction.type == 3">{{ appointmentReduction.value }}{{ accountCtrl.settings.currency }} <span class="tarif_barre"> {{accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentReduction.idPrice).price }}{{ accountCtrl.settings.currency }}</span></span>
	                  <span ng-if="appointmentReduction.type == 4">{{ appointmentReduction.value }} <?php _e('offer_quantity_words', 'resa') ?></span>
	                  <span ng-if="appointmentReduction.type == 5">{{ appointmentReduction.value }}</span>
	                </div>
	                <div class="reduction_total size_xl">
	                  <span ng-if="appointmentReduction.type == 0">{{ (appointmentReduction.value * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                  <span ng-if="appointmentReduction.type == 1">{{ ((accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).price * appointmentReduction.number) * appointmentReduction.value  / 100)|negative }}{{ accountCtrl.settings.currency }} </span>
	                  <span ng-if="appointmentReduction.type == 2">{{ (appointmentReduction.value * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                  <span ng-if="appointmentReduction.type == 3">{{ ((accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentReduction.idPrice).price - appointmentReduction.value) * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                  <!-- notThresholded !-->
	                  <span ng-if="appointmentReduction.type == 4" ng-if="accountCtrl.getReductionById(appointmentReduction.idReduction).notThresholded">{{ (accountCtrl.getReductionById(appointmentReduction.idReduction).price * appointmentReduction.value * appointmentReduction.number)|negative }} {{ accountCtrl.settings.currency }}</span>
	                  <span ng-if="appointmentReduction.type == 5"></span>
	                </div>
	              </div>
	              <div class="creneau_sous_total size_xl"><?php _e('Sub_total_word', 'resa'); ?> : {{ accountCtrl.round(appointment.totalPrice * accountCtrl.getNumberCombined(booking, appointment.internalIdLink)) }}{{ accountCtrl.settings.currency }}</div>
	            </div>
	            <div class="reduction_row" ng-repeat="bookingReduction in booking.bookingReductions">
	              <div class="reduction_title size_m">{{ accountCtrl.getTextByLocale(accountCtrl.getReductionById(bookingReduction.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ bookingReduction.number }})</div>
	              <div class="reduction_description size_xs" ng-bind-html="accountCtrl.getTextByLocale(accountCtrl.getReductionById(bookingReduction.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
	              <div class="reduction_total size_xl">
	                <span ng-if="bookingReduction.type == 0">{{ (bookingReduction.value * bookingReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                <span ng-if="bookingReduction.type == 1">{{ (bookingReduction.value * bookingReduction.number)|negative }}% </span>
	                <span ng-if="bookingReduction.type == 2">{{ (bookingReduction.value * bookingReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                <span ng-if="bookingReduction.type == 5"></span>
	              </div>
	            </div>
	            <div class="reduction_row" ng-repeat="bookingCustomReduction in booking.bookingCustomReductions">
	              <div class="reduction_title size_m"></div>
	              <div class="reduction_description size_xs">{{ bookingCustomReduction.description }}</div>
	              <div class="reduction_total size_xl">{{ bookingCustomReduction.amount }}{{ accountCtrl.settings.currency }}</div>
	            </div>
	          </div>
	          <div class="cart_total size_xxl"><?php _e('Total_word','resa'); ?> : {{ booking.totalPrice }}{{ accountCtrl.settings.currency }}</div>
	          <!-- Payment -->
	          <button ng-if="!accountCtrl.isComplete(booking) && !accountCtrl.isBookingLoaded() && (booking.status == 'ok' || accountCtrl.haveAskPaymentNoExpired(booking))" class="btn btn_resa_paiement" type="button" ng-click="booking.showPayment = !booking.showPayment">
	          <?php _e('Paid_this_booking_words', 'resa'); ?>
	          </button>
	          <div ng-if="booking.showPayment || (accountCtrl.isBookingLoaded() && !accountCtrl.isComplete(booking) && (booking.status == 'ok' || accountCtrl.haveAskPaymentNoExpired(booking)))" id="step6_2">
	            <h3 class="paiement_title size_xxl"><?php _e('Payment_word', 'resa') ?></h3>
	            <p class="paiement_text" ng-bind-html="accountCtrl.getTextByLocale(accountCtrl.settings.informations_payment_text, '<?php echo get_locale(); ?>')"></p>
	            <div class="paiement_methods">
	              <h4 class="paiement_method_title"><?php _e('Payment_methods_words', 'resa') ?></h4>
	              <form>
	                <div class="method {{ payment.id }}" ng-if="payment.activated && accountCtrl.getTypeAccountOfCustomer().paymentsTypeList[payment.id] && payment.id!='onTheSpot' && payment.id!='later' && accountCtrl.isMethodsPayment(booking, payment.id)" ng-repeat="payment in accountCtrl.paymentsTypeList">
	                  <input id="{{ payment.id }}{{ booking.id }}" ng-model="accountCtrl.typePayment" type="radio" ng-value="payment" />
	                  <label class="{{ payment.id }} size_l" for="{{ payment.id }}{{ booking.id }}">
	                    <span class="{{ payment.class }}"></span>
	                    <span ng-if="payment.title_public==null || payment.title_public == ''">{{ payment.title }}</span><span ng-if="payment.title_public!=null || payment.title_public != ''">{{ accountCtrl.getTextByLocale(payment.title_public, '<?php echo get_locale(); ?>') }}
	                    </span>
	                    <span class="description" ng-if="payment.text != '' && accountCtrl.typePayment.id == payment.id">
	                      <br /><span ng-bind-html="accountCtrl.getTextByLocale(payment.text, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></span>
	                    </span>
	                  </label>
	                </div>
	              </form>
	            </div>
	            <div class="account" ng-if="accountCtrl.canPayAdvancePayment(booking) && accountCtrl.askAdvancePayment() && accountCtrl.typePayment.advancePayment && !accountCtrl.alreadyAdvancedPayment(booking) && accountCtrl.getTypeAdvancePayment(booking) != 1">
								<input id="notAdvancePayment{{ booking.id }}" type="radio" ng-model="accountCtrl.checkboxAdvancePayment" ng-value="false" />
								<label for="notAdvancePayment{{ booking.id }}"><?php _e('not_to_pay_advance_payment_amount_words', 'resa'); ?></label><br />

								<input id="advancePayment{{ booking.id }}" type="radio" ng-model="accountCtrl.checkboxAdvancePayment" ng-value="true" />
	              <label for="advancePayment{{ booking.id }}" ng-bind-html="accountCtrl.getTextByLocale(accountCtrl.settings.payment_ask_text_advance_payment, '<?php echo get_locale(); ?>')|displayNewLines"></label>
	               <?php _e('advance_payment_amount_words', 'resa') ?> <span class="gras">{{ booking.advancePayment }}{{ accountCtrl.settings.currency }}</span>
	            </div>
	            <div class="paiement_validation">
	              <h4 class="paiement_validation_title"><?php _e('Validation_word', 'resa'); ?></h4>
	              <div class="paiement_validation_form">
	                <label class="paiement_cgv_label" ng-if="accountCtrl.settings.checkbox_payment">
	                  <input class="paiement_method_radio" type="checkbox" ng-model="accountCtrl.checkboxAcceptPayment" />
	                  <span ng-bind-html="accountCtrl.getTextByLocale(accountCtrl.settings.checkbox_title_payment, '<?php echo get_locale(); ?>')|displayNewLines"></span>
	                </label>
	                <br />
	                <input class="paiement_validation_btn" type="submit" ng-disabled="!accountCtrl.isPaymentFormOk(booking)" ng-click="accountCtrl.payBooking(booking)" value="<?php _e('ok_link_title', 'resa'); ?>" />
	                <p ng-show="accountCtrl.launchPayment" class="paiement_validation_infos size_s"><?php _e('redict_to_payment_page_sentence', 'resa'); ?></p>
									<!-- step7_content need for stripe //-->
									<p ng-if="booking.showPayment || (accountCtrl.isBookingLoaded() && (booking.status == 'ok' || accountCtrl.haveAskPaymentNoExpired(booking)))" id="step7_content"></p>
	              </div>
	            </div>
	            <div ng-if="accountCtrl.paymentData() != null">
	              <span ng-bind-html="accountCtrl.formatText(booking, accountCtrl.getTextByLocale(accountCtrl.paymentData().text, '<?php echo get_locale(); ?>'))|displayNewLines"></span>
	            </div>
	          </div>
	          <button class="btn btn_resa_reciept" type="button" ng-click="accountCtrl.openReceiptBookingDialog(booking)" ><?php _e('See_the_receipt_words', 'resa'); ?></button>
	        </div>
	      </div>

	      </div>
	    </section>

	    <section ng-if="accountCtrl.isSeeQuotations()" id="quotations">
				<div ng-if="!accountCtrl.isBookingLoaded()">
	        <span class="filters_menu_title"><?php _e('see_quotations', 'resa'); ?> : </span>
	        <div class="filters_menu">
	          <button class="dropbtn">
							<span ng-if="accountCtrl.filterDateQuotation == 0"><?php _e('to_come', 'resa'); ?></span>
							<span ng-if="accountCtrl.filterDateQuotation == 1"><?php _e('past', 'resa'); ?></span>
							<span ng-if="accountCtrl.filterDateQuotation == 2"><?php _e('all_feminin', 'resa'); ?></span>
						</button>
	          <div class="dropdown-content">
	            <a ng-if="accountCtrl.filterDateQuotation != 0" ng-click="accountCtrl.filterDateQuotation = 0"><?php _e('to_come', 'resa'); ?></a>
	            <a ng-if="accountCtrl.filterDateQuotation != 1" ng-click="accountCtrl.filterDateQuotation = 1"><?php _e('past', 'resa'); ?></a>
	            <a ng-if="accountCtrl.filterDateQuotation != 2" ng-click="accountCtrl.filterDateQuotation = 2"><?php _e('all_feminin', 'resa'); ?></a>
	          </div>
	        </div>
	      </div>
				<div class="quotations">
	        <div class="resa_clients" id="step6_1" ng-if="accountCtrl.customer.ID != -1">
	        <!-- Quotation part -->
	          <div id="cart" ng-if="booking.quotation  && ((!accountCtrl.isBookingLoaded() && !accountCtrl.isFilteredQuotation(booking))  || accountCtrl.booking.id == booking.id)" ng-repeat="booking in accountCtrl.customer.bookings">
	            <h4 class="size_xl client_resa_title cart_title">
	            <span ng-if="booking.intervals.length > 0"><?php _e('quotation_word', 'resa'); ?> n°{{ accountCtrl.getBookingId(booking) }} <?php _e('booking_first_date_link_word', 'resa'); ?> {{ accountCtrl.parseDate(booking.intervals[0].interval.startDate) | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</span>
	            </h4>
	            <h5 class="center italic"><?php _e('quotation_created_words', 'resa'); ?> {{ booking.creationDate | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</h5>

	            <div class="resa_state"><?php _e('booking_status_words', 'resa'); ?> :
	              <b ng-if="!booking.quotationRequest" class="jaune"><?php _e('booking_status_quotation_word', 'resa'); ?></b>
	              <b ng-if="booking.quotationRequest" class="jaune"><?php _e('booking_status_quotation_request_word', 'resa'); ?></b>
	            </div>
	             <div class="resa_public_note" ng-if="booking.publicNote.length > 0">
	                <h4><?php _e('note_word', 'resa'); ?> : </h4>
			            <p ng-bind-html="booking.publicNote|htmlSpecialDecode:true"></p>
	              </div>
	              <div class="resa_public_note" ng-if="booking.customerNote.length > 0">
	                <h4><?php _e('Customer_note_word', 'resa'); ?> : </h4>
			            <p ng-bind-html="booking.customerNote|htmlSpecialDecode:true"></p>
	              </div>
	            <button class="btn btn_resa_detail" ng-click="booking.showAppointments = !booking.showAppointments" type="button" > <?php _e('See_quotation_details_words', 'resa'); ?></button>
	            <div class="cart_content" ng-if="booking.showAppointments">
	              <div class="service_section" ng-if="accountCtrl.isDisplayed(booking, appointment)" ng-repeat="appointment in booking.appointments">
									<h4 class="service_title size_l lh_xl">
										<span ng-if="accountCtrl.getServiceById(appointment.idService).places.length > 0">[
		                  <span ng-repeat="place in accountCtrl.getServiceById(appointment.idService).places">
		                    <span ng-if="$index > 0">, </span>
		                    {{ accountCtrl.getTextByLocale(accountCtrl.getPlaceById(place).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
		                  </span>]
		                </span>
										{{ accountCtrl.getTextByLocale(accountCtrl.getServiceById(appointment.idService).name, '<?php echo get_locale(); ?>') }}
									</h4>
	                <div class="cart_row" ng-class="{'combined_row':accountCtrl.isCombinedRow(booking, appointment) }" ng-repeat-start="appointmentNumberPrice in appointment.appointmentNumberPrices">
	                  <div class="date_selected size_xl" ng-class="{'cancelled':appointment.state == 'cancelled', 'pending': appointment.state == 'waiting', 'valid': appointment.state == 'ok'}" ng-if="!accountCtrl.isCombinedRow(booking, appointment)">
	                    <div class="rdv_state" ng-if="appointment.state == 'waiting'"><?php _e('waiting_status_word', 'resa'); ?></div>
	                    <div class="rdv_state" ng-if="appointment.state == 'ok'"><?php _e('ok_status_word', 'resa'); ?></div>
	                    <div class="rdv_state" ng-if="appointment.state == 'cancelled'"><?php _e('cancelled_status_word', 'resa'); ?></div>
	                    <h5 class="date">{{ appointment.startDate | formatDate:'<?php echo $variables['date_format']; ?>' }}</h5>
	                    <h5 class="creneau" ng-if="!appointment.noEnd">{{ appointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ appointment.endDate | formatDateTime:'<?php echo $variables['time_format']; ?>' }}</h5>
	                    <h5 class="creneau" ng-if="appointment.noEnd"><?php _e('begin_word','resa') ?> {{ appointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</h5>
	                  </div>
	                  <div class="date_selected size_xl" ng-if="accountCtrl.isCombinedRow(booking, appointment)">
	                    <div class="absence_title size_s" ng-if="booking.appointments.length == 1"><?php _e('Date_and_hours_word', 'resa'); ?></div>
	                    <div class="absence_title size_s" ng-if="booking.appointments.length > 1"><?php _e('Dates_and_hours_word', 'resa'); ?></div>
	                    <div  class="date_multi" ng-repeat="displayAppointment in accountCtrl.getAppointmentsByInternalIdLink(booking, appointment.internalIdLink)">
	                      <span class="date_creneau" ng-class="{'cancelled':displayAppointment.state == 'cancelled', 'pending': displayAppointment.state == 'waiting', 'valid': displayAppointment.state == 'ok'}">
	                        <div class="rdv_state" ng-if="displayAppointment.state == 'waiting'"><?php _e('waiting_status_word', 'resa'); ?></div>
	                        <div class="rdv_state" ng-if="displayAppointment.state == 'ok'"><?php _e('ok_status_word', 'resa'); ?></div>
	                        <div class="rdv_state" ng-if="displayAppointment.state == 'cancelled'"><?php _e('cancelled_status_word', 'resa'); ?></div>
	                        <h5 class="date">{{ displayAppointment.startDate | formatDate:'<?php echo $variables['date_format']; ?>' }}</h5>
	                        <h5 class="creneau" ng-if="!displayAppointment.noEnd">{{ displayAppointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ displayAppointment.endDate | formatDateTime:'<?php echo $variables['time_format']; ?>' }}</h5>
	                        <h5 class="creneau" ng-if="displayAppointment.noEnd"><?php _e('begin_word','resa') ?> {{ displayAppointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</h5>
	                      </span>
	                    </div>
	                  </div>
	                  <div class="order_recap clear">
	                    <span class="order_recap_left fleft"> <span class="order_recap_price_quantity size_l">{{ appointmentNumberPrice.number }}</span> <span class="order_recap_price_quantity_separator size_l"><span ng-show="appointmentNumberPrice.number==1 && !accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).extra"><?php _e('person_word', 'resa'); ?></span><span ng-show="appointmentNumberPrice.number>1 && !accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).extra"><?php _e('persons_word', 'resa'); ?></span></span> </span>
	                    <span class="order_recap_center fleft"> <span class="order_recap_price_title size_l">
	                      {{ accountCtrl.getTextByLocale(accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).name,'<?php echo get_locale(); ?>')	}}&nbsp;
	                      <span ng-if="accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
	                        {{ accountCtrl.round(accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).price * accountCtrl.getNumberCombined(booking, appointment.internalIdLink)) }} {{ accountCtrl.settings.currency }}
	                      </span>
	                    </span></span>
	                    <span ng-if="!appointmentNumberPrice.deactivated" class="order_recap_right right fright">
	                      <span class="order_recap_price_total size_l">
	                        {{ accountCtrl.round(accountCtrl.getPriceNumberPrice(appointment.idService, appointmentNumberPrice, appointment)  * accountCtrl.getNumberCombined(booking, appointment.internalIdLink)) }}{{ accountCtrl.settings.currency }}
	                      </span>
	                    </span>
	                  </div>
	                  <div class="price_description">{{ accountCtrl.getTextByLocale(accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).description,'<?php echo get_locale(); ?>')
	                  }}</div>
	                  <div class="clients_informations" ng-if="accountCtrl.getServiceById(appointment.idService).askParticipants &&  accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).participantsParameter != null && accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).participantsParameter.length > 0">
	                    <h4 class="clients_informations_title size_m"><?php _e('Participants_words', 'resa'); ?></h4>
	                    <table class="recap_client_information size_s">
	                      <thead>
	                        <tr>
	                          <td ng-repeat="field in accountCtrl.getParticipantsParameter(accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">
	                            {{ accountCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
	                          </td>
	                        </tr>
	                      </thead>
	                      <tbody>
	                        <tr ng-repeat="participant in appointmentNumberPrice.participants track by $index"><td ng-repeat="field in accountCtrl.getParticipantsParameter(accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">{{ accountCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ participant[field.varname] }}{{ accountCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</td></tr>
	                      </tbody>
	                    </table>
	                  </div>
	                </div>
	                <div class="reduction_row" ng-if="appointmentReduction.idPrice == appointmentNumberPrice.idPrice" ng-repeat-end ng-repeat="appointmentReduction in appointment.appointmentReductions">
	                  <div class="reduction_title size_m">{{ accountCtrl.getTextByLocale(accountCtrl.getReductionById(appointmentReduction.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ appointmentReduction.number }})</div>
	                  <div class="reduction_description size_xs" ng-bind-html="accountCtrl.getTextByLocale(accountCtrl.getReductionById(appointmentReduction.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
	                  <div class="reduction_effect size_m b2">
	                    <span ng-if="appointmentReduction.type == 0">{{ appointmentReduction.value|negative }}{{ accountCtrl.settings.currency }}</span>
	                    <span ng-if="appointmentReduction.type == 1">{{ appointmentReduction.value|negative }}%</span>
	                    <span ng-if="appointmentReduction.type == 2">{{ appointmentReduction.value|negative }}{{ accountCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
	                    <span ng-if="appointmentReduction.type == 3">{{ appointmentReduction.value }}{{ accountCtrl.settings.currency }} <span class="tarif_barre"> {{accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentReduction.idPrice).price }}{{ accountCtrl.settings.currency }}</span></span>
	                    <span ng-if="appointmentReduction.type == 4">{{ appointmentReduction.value }} <?php _e('offer_quantity_words', 'resa') ?></span>
	                    <span ng-if="appointmentReduction.type == 5">{{ appointmentReduction.value }}</span>
	                  </div>
	                  <div class="reduction_total size_xl">
	                    <span ng-if="appointmentReduction.type == 0">{{ (appointmentReduction.value * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                    <span ng-if="appointmentReduction.type == 1">{{ ((accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).price * appointmentReduction.number) * appointmentReduction.value  / 100)|negative }}{{ accountCtrl.settings.currency }} </span>
	                    <span ng-if="appointmentReduction.type == 2">{{ (appointmentReduction.value * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                    <span ng-if="appointmentReduction.type == 3">{{ ((accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentReduction.idPrice).price - appointmentReduction.value) * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                    <!-- notThresholded !-->
	                    <span ng-if="appointmentReduction.type == 4" ng-if="accountCtrl.getReductionById(appointmentReduction.idReduction).notThresholded">{{ (accountCtrl.getReductionById(appointmentReduction.idReduction).price * appointmentReduction.value * appointmentReduction.number)|negative }} {{ accountCtrl.settings.currency }}</span>
	                    <span ng-if="appointmentReduction.type == 5"></span>
	                  </div>
	                </div>
	                <div class="reduction_row" ng-if="appointmentReduction.idPrice == -1" ng-repeat="appointmentReduction in appointment.appointmentReductions">
	                  <div class="reduction_title size_m">{{ accountCtrl.getTextByLocale(accountCtrl.getReductionById(appointmentReduction.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ appointmentReduction.number }})</div>
	                  <div class="reduction_description size_xs" ng-bind-html="accountCtrl.getTextByLocale(accountCtrl.getReductionById(appointmentReduction.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
	                  <div class="reduction_effect size_m b2">
	                    <span ng-if="appointmentReduction.type == 0">{{ appointmentReduction.value|negative }}{{ accountCtrl.settings.currency }}</span>
	                    <span ng-if="appointmentReduction.type == 1">{{ appointmentReduction.value|negative }}%</span>
	                    <span ng-if="appointmentReduction.type == 2">{{ appointmentReduction.value|negative }}{{ accountCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
	                    <span ng-if="appointmentReduction.type == 3">{{ appointmentReduction.value }}{{ accountCtrl.settings.currency }} <span class="tarif_barre"> {{accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentReduction.idPrice).price }}{{ accountCtrl.settings.currency }}</span></span>
	                    <span ng-if="appointmentReduction.type == 4">{{ appointmentReduction.value }} <?php _e('offer_quantity_words', 'resa') ?></span>
	                    <span ng-if="appointmentReduction.type == 5">{{ appointmentReduction.value }}</span>
	                  </div>
	                  <div class="reduction_total size_xl">
	                    <span ng-if="appointmentReduction.type == 0">{{ (appointmentReduction.value * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                    <span ng-if="appointmentReduction.type == 1">{{ ((accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).price * appointmentReduction.number) * appointmentReduction.value  / 100)|negative }}{{ accountCtrl.settings.currency }} </span>
	                    <span ng-if="appointmentReduction.type == 2">{{ (appointmentReduction.value * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                    <span ng-if="appointmentReduction.type == 3">{{ ((accountCtrl.getServicePriceAppointment(accountCtrl.getServiceById(appointment.idService), appointmentReduction.idPrice).price - appointmentReduction.value) * appointmentReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                    <!-- notThresholded !-->
	                    <span ng-if="appointmentReduction.type == 4" ng-if="accountCtrl.getReductionById(appointmentReduction.idReduction).notThresholded">{{ (accountCtrl.getReductionById(appointmentReduction.idReduction).price * appointmentReduction.value * appointmentReduction.number)|negative }} {{ accountCtrl.settings.currency }}</span>
	                    <span ng-if="appointmentReduction.type == 5"></span>
	                  </div>
	                </div>
	                <div class="creneau_sous_total size_xl"><?php _e('Sub_total_word', 'resa'); ?> : {{ accountCtrl.round(appointment.totalPrice * accountCtrl.getNumberCombined(booking, appointment.internalIdLink)) }}{{ accountCtrl.settings.currency }}</div>
	              </div>
	              <div class="reduction_row" ng-repeat="bookingReduction in booking.bookingReductions">
	                <div class="reduction_title size_m">{{ accountCtrl.getTextByLocale(accountCtrl.getReductionById(bookingReduction.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ bookingReduction.number }})</div>
	                <div class="reduction_description size_xs" ng-bind-html="accountCtrl.getTextByLocale(accountCtrl.getReductionById(bookingReduction.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
	                <div class="reduction_total size_xl">
	                  <span ng-if="bookingReduction.type == 0">{{ (bookingReduction.value * bookingReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                  <span ng-if="bookingReduction.type == 1">{{ (serviceParameters.getPrice() * bookingReduction.value / 100)|negative }}{{ accountCtrl.settings.currency }} </span>
	                  <span ng-if="bookingReduction.type == 2">{{ (bookingReduction.value * bookingReduction.number)|negative }}{{ accountCtrl.settings.currency }}</span>
	                  <span ng-if="bookingReduction.type == 5"></span>
	                </div>
	              </div>
	              <div class="reduction_row" ng-repeat="bookingCustomReduction in booking.bookingCustomReductions">
	                <div class="reduction_title size_m"></div>
	                <div class="reduction_description size_xs">{{ bookingCustomReduction.description }}</div>
	                <div class="reduction_total size_xl">{{ bookingCustomReduction.amount }}{{ accountCtrl.settings.currency }}</div>
	              </div>
	            </div>
	            <div class="cart_total size_xxl"><?php _e('Sub_total_word', 'resa'); ?> : {{ booking.totalPrice }}{{ accountCtrl.settings.currency }}</div>
							<div class="account_resa_actions">
								<button ng-disabled="accountCtrl.launchAcceptQuotation" ng-if="!booking.quotationRequest" class="btn btn_quote_validation" ng-click="accountCtrl.acceptQuotation(booking, {
		              title: '<?php _e('accept_quotation_title_dialog','resa') ?>',
		              text: '<?php _e('accept_quotation_text_dialog','resa') ?>',
		              confirmButton: '<?php _e('accept_quotation_confirmButton_dialog','resa') ?>',
		              cancelButton: '<?php _e('accept_quotation_cancelButton_dialog','resa') ?>'
		            }, '<?php _e('accept_quotation_success_dialog','resa') ?>')">
									<?php _e('accept_quotation_link_title', 'resa'); ?><span ng-if="accountCtrl.launchAcceptQuotation">...</span>
								</button>
								<button ng-disabled="accountCtrl.launchRefuseQuotation" ng-if="!booking.quotationRequest" class="btn btn_quote_refuse" ng-click="accountCtrl.refuseQuotation(booking, {
		              title: '<?php _e('refuse_quotation_title_dialog','resa') ?>',
		              text: '<?php _e('refuse_quotation_text_dialog','resa') ?>',
		              confirmButton: '<?php _e('refuse_quotation_confirmButton_dialog','resa') ?>',
		              cancelButton: '<?php _e('refuse_quotation_cancelButton_dialog','resa') ?>'
		            }, '<?php _e('refuse_quotation_success_dialog','resa') ?>')">
									<?php _e('refuse_quotation_link_title', 'resa'); ?><span ng-if="accountCtrl.launchRefuseQuotation">...</span>
								</button>
	              <button class="btn btn_resa_reciept" type="button" ng-click="accountCtrl.openReceiptBookingDialog(booking)" ><?php _e('See_the_quotation_words', 'resa'); ?></button>
							</div>

	          </div>
	          </div>
	      </div>
	    </section>

	    <section ng-if="accountCtrl.isSeePersonalInformations()" id="personal_informations">
	      <h3 class="size_m"><?php _e('Modify_your_personal_informations_words', 'resa'); ?></h3>
	      <div class="infos_clients" ng-if="accountCtrl.customer != null && accountCtrl.customer.ID != -1">
	        <div class="edit_infos_client">
	          <form>
	            <label><input type="checkbox" ng-model="accountCtrl.changePassword"   /><?php _e('Modify_your_password', 'resa'); ?></label>
	            <span ng-show="accountCtrl.changePassword">
	              <input class="input_wide" type="password" ng-model="accountCtrl.modelCustomer.password" placeholder="<?php _e('password_field_title', 'resa') ?>*" ng-init="accountCtrl.customer.password = ''">
	              <span class="input_error" ng-show="accountCtrl.modelCustomer.password == null || accountCtrl.modelCustomer.password.length == 0"><?php _e('required_word', 'resa') ?></span>
								<span style="color:orange; font-size:14px; font-style:italic; line-height: normal important!;" ng-show="!accountCtrl.checkPassword(accountCtrl.modelCustomer.password)"><?php _e('bad_password_words', 'resa'); ?></span>
	              <input class="input_wide" type="password" ng-model="accountCtrl.modelCustomer.confirmPassword" placeholder="<?php _e('confirm_password_field_title', 'resa') ?>*"  ng-init="accountCtrl.modelCustomer.confirmPassword = ''" />
	              <span class="input_error" ng-show="accountCtrl.modelCustomer.confirmPassword == null || accountCtrl.modelCustomer.confirmPassword.length == 0"><?php _e('required_word', 'resa') ?></span>
	              <span class="input_error" ng-show="accountCtrl.modelCustomer.confirmPassword != null && accountCtrl.modelCustomer.confirmPassword.length > 0 && accountCtrl.modelCustomer.password != accountCtrl.modelCustomer.confirmPassword"><?php _e('not_same_password_words', 'resa') ?></span>
	            </span>
	            <br /><br />
							<input ng-if="accountCtrl.fieldStates['lastName'] > 0" class="input_wide" type="text" ng-model="accountCtrl.modelCustomer.lastName" placeholder="{{ accountCtrl.getPlaceolder('<?php _e('last_name_field_title', 'resa') ?>','lastName') }}" />
							<span ng-class="{'input_error':accountCtrl.fieldStates['lastName'] == 1, 'input_info':accountCtrl.fieldStates['lastName'] == 2}" ng-show="accountCtrl.modelCustomer.lastName.length == 0 && accountCtrl.fieldStates['lastName'] > 0"><span ng-if="accountCtrl.fieldStates['lastName'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="accountCtrl.fieldStates['lastName'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

							<input ng-if="accountCtrl.fieldStates['firstName'] > 0" class="input_wide" type="text" ng-model="accountCtrl.modelCustomer.firstName" placeholder="{{ accountCtrl.getPlaceolder('<?php _e('first_name_field_title', 'resa') ?>','firstName') }}" />
							<span ng-class="{'input_error':accountCtrl.fieldStates['firstName'] == 1, 'input_info':accountCtrl.fieldStates['firstName'] == 2}" ng-show="accountCtrl.modelCustomer.firstName.length == 0 && accountCtrl.fieldStates['firstName'] > 0"><span ng-if="accountCtrl.fieldStates['firstName'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="accountCtrl.fieldStates['firstName'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

	            <?php _e('email_field_title', 'resa') ?> : {{ accountCtrl.modelCustomer.email }}<br />

							<input ng-if="accountCtrl.fieldStates['company'] > 0" class="input_wide" type="text" ng-model="accountCtrl.modelCustomer.company" placeholder="{{ accountCtrl.getPlaceolder('<?php _e('company_field_title', 'resa') ?>','company') }}" />
							<span ng-class="{'input_error':accountCtrl.fieldStates['company'] == 1, 'input_info':accountCtrl.fieldStates['company'] == 2}" ng-show="accountCtrl.modelCustomer.company.length == 0 && accountCtrl.fieldStates['company'] > 0"><span ng-if="accountCtrl.fieldStates['company'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="accountCtrl.fieldStates['company'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

							<input ng-if="accountCtrl.fieldStates['phone'] > 0" class="input_wide" type="text" ng-model="accountCtrl.modelCustomer.phone" placeholder="{{ accountCtrl.getPlaceolder('<?php _e('phone_field_title', 'resa') ?>','phone') }}" />
							<span ng-class="{'input_error':accountCtrl.fieldStates['phone'] == 1, 'input_info':accountCtrl.fieldStates['phone'] == 2}" ng-show="accountCtrl.modelCustomer.phone.length == 0 && accountCtrl.fieldStates['phone'] > 0"><span ng-if="accountCtrl.fieldStates['phone'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="accountCtrl.fieldStates['phone'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

							<input ng-if="accountCtrl.fieldStates['phone2'] > 0" class="input_wide" type="text" ng-model="accountCtrl.modelCustomer.phone2" placeholder="{{ accountCtrl.getPlaceolder('<?php _e('phone2_field_title', 'resa') ?>','phone2') }}" />
							<span ng-class="{'input_error':accountCtrl.fieldStates['phone2'] == 1, 'input_info':accountCtrl.fieldStates['phone2'] == 2}" ng-show="accountCtrl.modelCustomer.phone2.length == 0 && accountCtrl.fieldStates['phone2'] > 0"><span ng-if="accountCtrl.fieldStates['phone2'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="accountCtrl.fieldStates['phone2'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

							<input ng-if="accountCtrl.fieldStates['address'] > 0" class="input_wide" type="text" ng-model="accountCtrl.modelCustomer.address" placeholder="{{ accountCtrl.getPlaceolder('<?php _e('address_field_title', 'resa') ?>','address') }}" />
							<span ng-class="{'input_error':accountCtrl.fieldStates['address'] == 1, 'input_info':accountCtrl.fieldStates['address'] == 2}" ng-show="accountCtrl.modelCustomer.address.length == 0 && accountCtrl.fieldStates['address'] > 0"><span ng-if="accountCtrl.fieldStates['address'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="accountCtrl.fieldStates['address'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

							<input ng-if="accountCtrl.fieldStates['postalCode'] > 0" class="input_wide" type="text" ng-model="accountCtrl.modelCustomer.postalCode" placeholder="{{ accountCtrl.getPlaceolder('<?php _e('postal_code_field_title', 'resa') ?>','postalCode') }}" />
							<span ng-class="{'input_error':accountCtrl.fieldStates['postalCode'] == 1, 'input_info':accountCtrl.fieldStates['postalCode'] == 2}" ng-show="accountCtrl.modelCustomer.postalCode.length == 0 && accountCtrl.fieldStates['postalCode'] > 0"><span ng-if="accountCtrl.fieldStates['postalCode'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="accountCtrl.fieldStates['postalCode'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

							<input ng-if="accountCtrl.fieldStates['town'] > 0" class="input_wide" type="text" ng-model="accountCtrl.modelCustomer.town" placeholder="{{ accountCtrl.getPlaceolder('<?php _e('town_field_title', 'resa') ?>','town') }}" />
							<span ng-class="{'input_error':accountCtrl.fieldStates['town'] == 1, 'input_info':accountCtrl.fieldStates['town'] == 2}" ng-show="accountCtrl.modelCustomer.town.length == 0 && accountCtrl.fieldStates['town'] > 0"><span ng-if="accountCtrl.fieldStates['town'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="accountCtrl.fieldStates['town'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
							<select  class="input_wide" ng-if="accountCtrl.fieldStates['country'] > 0" ng-model="accountCtrl.modelCustomer.country">
								<option value="" disabled selected>-- {{ accountCtrl.getPlaceolder('<?php _e('country_field_title', 'resa') ?>','country') }} --</option>
								<option ng-repeat="country in accountCtrl.countries" value="{{ country.code }}">{{ country.name }}</option>
							</select>
							<span ng-class="{'input_error':accountCtrl.fieldStates['country'] == 1, 'input_info':accountCtrl.fieldStates['country'] == 2}" ng-show="accountCtrl.modelCustomer.country.length == 0 && accountCtrl.fieldStates['country'] > 0"><span ng-if="accountCtrl.fieldStates['country'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="accountCtrl.fieldStates['country'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

							<input ng-if="accountCtrl.fieldStates['siret'] > 0" class="input_wide" type="text" ng-model="accountCtrl.modelCustomer.siret" placeholder="{{ accountCtrl.getPlaceolder('<?php _e('siret_field_title', 'resa') ?>','siret') }}" />
			        <span ng-class="{'input_error':accountCtrl.fieldStates['siret'] == 1, 'input_info':accountCtrl.fieldStates['siret'] == 2}" ng-show="accountCtrl.customer.siret.length == 0 && accountCtrl.fieldStates['siret'] > 0"><span ng-if="accountCtrl.fieldStates['siret'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="accountCtrl.fieldStates['siret'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

			        <input ng-if="accountCtrl.fieldStates['legalForm'] > 0" class="input_wide" type="text" ng-model="accountCtrl.modelCustomer.legalForm" placeholder="{{ accountCtrl.getPlaceolder('<?php _e('legal_form_field_title', 'resa') ?>','legalForm') }}" />
			        <span ng-class="{'input_error':accountCtrl.fieldStates['legalForm'] == 1, 'input_info':accountCtrl.fieldStates['legalForm'] == 2}" ng-show="accountCtrl.customer.legalForm.length == 0 && accountCtrl.fieldStates['legalForm'] > 0"><span ng-if="accountCtrl.fieldStates['legalForm'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="accountCtrl.fieldStates['legalForm'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

							<label ng-if="accountCtrl.fieldStates['newsletters'] > 0" for="acceptNewsletters">
								<input id="acceptNewsletters" type="checkbox" ng-model="accountCtrl.modelCustomer.registerNewsletters" />
								<?php _e('accept_register_newsletters', 'resa'); ?><br />
							</label>

							<label for="acceptGDPR"><input id="acceptGDPR" type="checkbox" ng-model="accountCtrl.checkboxCustomer" /><?php _e('gdpr_accept_to_save_data', 'resa'); ?></label>
	            <button ng-disabled="!accountCtrl.customerIsOk()" class="input_wide btn" ng-click="accountCtrl.modifyCustomer({
	              title: '<?php _e('modify_your_informations_title_dialog','resa') ?>',
	              text: '<?php _e('modify_your_informations_text_dialog','resa') ?>',
	              confirmButton: '<?php _e('modify_your_informations_confirmButton_dialog','resa') ?>',
	              cancelButton: '<?php _e('modify_your_informations_cancelButton_dialog','resa') ?>'
	            })"><span ng-if="!accountCtrl.launchModifyCustomer"><?php _e('modify_link_title', 'resa'); ?></span><span ng-if="accountCtrl.launchModifyCustomer"><?php _e('modification_in_progress_link_title', 'resa'); ?>...</span>
							</button>
	          </form>
						<div><?php _e('gdpr_account_informations', 'resa'); ?></div>
	        </div>
	      </div>
	    </section>
	  </div>
	</div>
</section>
