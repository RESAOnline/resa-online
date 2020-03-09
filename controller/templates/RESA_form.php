<!-- Ancien formulaire 25 juin 2019 //-->
<div ng-app="resa_app" ng-controller="NewFormController as formCtrl" ng-init='formCtrl.init(<?php echo htmlspecialchars($variables['months'], ENT_QUOTES); ?>, <?php echo htmlspecialchars($variables['countries'], ENT_QUOTES); ?>, "<?php echo admin_url('admin-ajax.php'); ?>", "<?php echo $variables['currentUrl']; ?>",
  "<?php echo htmlspecialchars($variables['form'], ENT_QUOTES); ?>", <?php echo htmlspecialchars($variables['serviceSlugs'], ENT_QUOTES); ?>, <?php echo $variables['quotation']; ?>, <?php echo htmlspecialchars($variables['typesAccounts'], ENT_QUOTES); ?>, ["<?php _e('voucher_error_feedback', 'resa'); ?>"])
  ' ng-cloak>
  <section style="text-align:center" id="ro_front_formulaire" ng-if="!formCtrl.initializationOk">
    <h2><?php _e('loading_word', 'resa'); ?></h2>
  </section>
  <section id="ro_front_formulaire" ng-if="!formCtrl.settings.formActivated">
    <div ng-if="formCtrl.customer.ID != -1 && (formCtrl.customer.role == 'RESA_Manager' || formCtrl.customer.role == 'administrator')">
      <p>Pour éditer ce formulaire, veuillez cliquer sur ce lien : <a href="<?php echo RESA_Variables::getLinkParameters('forms/' . $variables['form']); ?>" target="_blank">Formulaire</a></p>
    </div>
    <div class="form_deactivated" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_deactivated_text, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></div>
  </section>
	<section ng-if="!formCtrl.isBrowserCompatibility()" style="color:red; font-weight:bold">
    <span ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.browser_not_compatible_sentence, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></span>
	</section>
  <section ng-if="formCtrl.lastBooking && formCtrl.initializationOk && formCtrl.settings.formActivated">
    <h4><?php _e('last_booking_title', 'resa'); ?></h4>
    <div id="cart">
      <h4 class="size_xl client_resa_title cart_title">
        <?php _e('Booking_word', 'resa'); ?> n°{{ formCtrl.lastBooking.realIdCreation }} <?php _e('booking_first_date_link_word', 'resa'); ?> {{ formCtrl.parseDate(formCtrl.lastBooking.startDate) | formatDateTime:'<?php echo $variables['date_format']; ?>' }}
      </h4>
      <h5 class="center italic"><?php _e('booking_created_words', 'resa'); ?> {{ formCtrl.lastBooking.creationDate | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</h5>
      <div class="resa_state"><?php _e('booking_status_words', 'resa'); ?>:
        <b class="vert" ng-if="formCtrl.lastBooking.status == 'ok'"><?php _e('booking_status_ok_word', 'resa'); ?></b>
        <b class="jaune" ng-if="formCtrl.lastBooking.status == 'waiting'">
          <?php _e('booking_status_waiting_word', 'resa'); ?>
          <span ng-if="formCtrl.lastBooking.waitingSubState == 1">(<?php _e('payment_word', 'resa'); ?>)</span>
          <span ng-if="formCtrl.lastBooking.waitingSubState == 2">(<?php _e('expired_word', 'resa'); ?>)</span>
        </b>
        <b class="rouge" ng-if="formCtrl.lastBooking.status == 'cancelled'"><?php _e('booking_status_cancelled_word', 'resa'); ?></b>
      </div>
      <div class="resa_state"><?php _e('payment_status_words', 'resa'); ?>:
        <b class="vert" ng-if="formCtrl.lastBooking.paymentState == 'complete'"><?php _e('payment_status_complete_word', 'resa'); ?></b>
        <b class="jaune" ng-if="formCtrl.lastBooking.paymentState == 'advancePayment'"><?php _e('payment_status_advancePayment_word', 'resa'); ?></b>
        <b class="jaune" ng-if="formCtrl.lastBooking.paymentState == 'deposit'"><?php _e('payment_status_deposit_word', 'resa'); ?></b>
        <b class="rouge" ng-if="formCtrl.lastBooking.paymentState == 'noPayment'"><?php _e('payment_status_noPayment_word', 'resa'); ?></b>
      </div>
      <div class="resa_public_note" ng-if="formCtrl.lastBooking.publicNote.length > 0">
        <h4><?php _e('note_word', 'resa'); ?> : </h4>
        <p ng-bind-html="formCtrl.lastBooking.publicNote|htmlSpecialDecode:true"></p>
      </div>
      <div class="resa_public_note" ng-if="formCtrl.lastBooking.customerNote.length > 0">
        <h4><?php _e('Customer_note_word', 'resa'); ?> : </h4>
        <p ng-bind-html="formCtrl.lastBooking.customerNote|htmlSpecialDecode:true"></p>
      </div>
      <div class="cart_total size_xxl"><?php _e('Total_word','resa'); ?> : {{ formCtrl.lastBooking.totalPrice }}{{ formCtrl.settings.currency }}</div>
    </div>
    <a id="new_booking_link" ng-click="formCtrl.newBooking()"><?php _e('new_booking_link_title', 'resa'); ?></a>
  </section>
  <section id="ro_front_formulaire" ng-if="formCtrl.lastBooking == null && formCtrl.initializationOk && formCtrl.settings.formActivated">
    <div class="particpants_popup" ng-controller="ParticipantSelectorController as participantSelectorCtrl" ng-show="participantSelectorCtrl.opened">
      <div class="particpants_popup_bloc">
        <div class="particpants_popup_content">
          <h4 class="center"><?php _e('participant_selector_title_dialog','resa'); ?></h4>
          <div class="participant_information_titles">
            <h5 class="input_wide" ng-repeat="field in participantSelectorCtrl.participantParametersFields track by $index">
              {{ formCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
            </h5>
          </div>
          <div ng-click="participantSelectorCtrl.setParticipant(participant); participantSelectorCtrl.close();" class="participant_information" ng-repeat="participant in participantSelectorCtrl.participants track by $index">
            <span class="input_wide" ng-repeat="field in participantSelectorCtrl.participantParametersFields track by $index">
              {{ participantSelectorCtrl.getTextByLocale(field.prefix, '<?php echo get_locale(); ?>')}}
              {{ formCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}
              {{participantSelectorCtrl.getTextByLocale(field.suffix, '<?php echo get_locale(); ?>')}}
            </span>
          </div>
          <div class="close_popup center">
            <input ng-click="participantSelectorCtrl.close()" class="btn" type="button" value="<?php _e('close_link_title', 'resa'); ?>" />
          </div>
        </div>
      </div>
    </div>
    <div id="welcome_texts">
      <div ng-if="!formCtrl.isFacebookConnected() && formCtrl.customer.ID == -1">
        <span ng-if="!formCtrl.needConnection()">
          <h2 class="size_xl"><?php _e('Hello_word', 'resa'); ?>, <?php _e('already_customer_words', 'resa'); ?></h2>
          <p ng-if="!formCtrl.displayFormConnectionFirstStep"><?php _e('If_yes_words', 'resa'); ?>, <a ng-click="formCtrl.displayFormConnectionFirstStep = true"><?php _e('connect_you_to_account_words', 'resa'); ?></a></p>
		      <a ng-if="formCtrl.settings.facebook_activated" ng-click="formCtrl.login()" class="facebook_connection facebook_connection_2"></a>
          <p ng-if="!formCtrl.displayFormConnectionFirstStep"><?php _e('else_continue_your_first_booking_words', 'resa'); ?></p>
        </span>
        <span ng-if="formCtrl.needConnection()">
          <h2 class="size_xl"><?php _e('need_connection_words', 'resa'); ?></h2>
        </span>
        <div ng-if="formCtrl.displayFormConnectionFirstStep || formCtrl.needConnection()" class="login">
    			<div class="login_client">
    				<form>
    					<input class="input_wide" ng-model="formCtrl.customer.login" type="text" placeholder="<?php _e('email_field_title', 'resa') ?>" />
    					<input ng-init="formCtrl.customer.passwordConnection=''" ng-model="formCtrl.customer.passwordConnection" class="input_wide" type="password" placeholder="<?php _e('password_field_title', 'resa') ?>" />
    					<button class="input_wide btn" ng-class="{'btn_locked':(formCtrl.customer.passwordConnection.length == 0|| formCtrl.customer.login.length== 0)}" ng-click="(formCtrl.customer.passwordConnection.length == 0|| formCtrl.customer.login.length== 0) || formCtrl.userConnection()"><?php _e('to_login_link_title', 'resa') ?><span ng-if="formCtrl.launchUserConnexion">...</span></button>
    					<a ng-if="!formCtrl.forgottenPasswordCustomerLaunched" ng-click="formCtrl.fotgottenPassword({
    						title: '<?php _e('forgotten_password_title_dialog','resa') ?>',
    						text: '<?php _e('forgotten_password_text_dialog','resa') ?>',
    						confirmButton: '<?php _e('forgotten_password_confirmButton_dialog','resa') ?>',
    						cancelButton: '<?php _e('forgotten_password_cancelButton_dialog','resa') ?>',
    						inputPlaceholder: '<?php _e('forgotten_password_inputPlaceholder_dialog','resa') ?>'
    					})"><?php _e('fotgotten_password_link_title', 'resa'); ?> ?</a>
              <a ng-if="formCtrl.forgottenPasswordCustomerLaunched"><?php _e('fotgotten_password_link_title', 'resa'); ?>...</a>
    				</form>
    			</div>
    		</div>
      </div>
      <div ng-if="formCtrl.isFacebookConnected() && formCtrl.customer.ID == -1">
        <form>
          <?php _e('create_account_by_facebook_sentence', 'resa'); ?><br />
          {{ formCtrl.customer.lastName }} {{ formCtrl.customer.firstName }}<br />
          <?php _e('email_field_title', 'resa'); ?> : {{ formCtrl.customer.email }}<br />
        </form>
      </div>
      <div ng-if="formCtrl.customer.ID != -1">
        <div ng-if="formCtrl.customer.role == 'RESA_Manager' || formCtrl.customer.role == 'administrator'">
          <p style="color:red; font-weight:bold;">
            !! Vous êtes connecté en tant que RESA Manager (ou Administrateur), ce formulaire est pour vos client.<br />
            Si vous voulez créer une nouvelle réservation, rendez-vous dans l'administation ! !!
          </p>
          <p>Pour éditer ce formulaire, veuillez cliquer sur ce lien : <a href="<?php echo RESA_Variables::getLinkParameters('forms/' . $variables['form']); ?>" target="_blank">Formulaire</a></p>
        </div>
        <h2 class="size_xl" ><?php _e('Hello_word', 'resa'); ?><span ng-if="formCtrl.customer.firstName.length > 0 || formCtrl.customer.lastName.length > 0"> {{ formCtrl.customer.firstName }}  {{ formCtrl.customer.lastName }}</span><span ng-if="formCtrl.customer.firstName.length == 0 && formCtrl.customer.lastName.length == 0">{{ formCtrl.customer.company }}</span></h2>
        <p><?php _e('you_is_not_words', 'resa'); ?> ? <a ng-click="formCtrl.userDeconnection()"><?php _e('logout_link_title', 'resa'); ?></a></p>
      </div>
    </div>
    <div ng-if="formCtrl.customer.ID > -1 && formCtrl.needConnection() && !formCtrl.isSameTypeAccount()"><?php _e('can_not_display_form_words', 'resa'); ?></div>
    <div ng-if="formCtrl.displayFormConnected()">
      <div id="steps_navigation" ng-class="{'seven_steps':formCtrl.numberSteps() == 7, 'six_steps':formCtrl.numberSteps() == 6, 'five_steps':formCtrl.numberSteps() == 5, 'four_steps':formCtrl.numberSteps() == 4}">
        <a class="step_item t200all" ng-show="formCtrl.needSelectPlace()" ng-class="{'active': formCtrl.step == 1, 'not_active_yet':formCtrl.isLocked(1)}" id="step_item{{ formCtrl.numberStep(1) }}" ng-click="formCtrl.setStep(1)"><span class="step_number size_xl">{{ formCtrl.getNumberStep(1) }}</span><span class="step_text">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[0]|htmlSpecialDecode }}</span></a>
        <a class="step_item t200all" ng-class="{'active': formCtrl.step == 2, 'not_active_yet':formCtrl.isLocked(2)}" id="step_item{{ formCtrl.numberStep(2) }}" ng-click="formCtrl.setStep(2)"><span class="step_number size_xl">{{ formCtrl.getNumberStep(2) }}</span><span class="step_text">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[1]|htmlSpecialDecode }}</span></a>
        <a class="step_item t200all" ng-class="{'active': formCtrl.step == 3, 'not_active_yet':formCtrl.isLocked(3)}" id="step_item{{ formCtrl.numberStep(3) }}" ng-click="formCtrl.setStep(3)"><span class="step_number size_xl">{{ formCtrl.getNumberStep(3) }}</span><span class="step_text size_s">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[2]|htmlSpecialDecode }}</span></a>
        <a class="step_item t200all" ng-show="!formCtrl.userIsConnected()" ng-class="{'active': formCtrl.step == 4, 'not_active_yet':formCtrl.isLocked(4)}" id="step_item{{ formCtrl.numberStep(4) }}" ng-click="formCtrl.setStep(4)"><span class="step_number size_xl">{{ formCtrl.getNumberStep(4) }}</span><span class="step_text">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[3]|htmlSpecialDecode }}</span></a>
        <a class="step_item t200all" ng-show="formCtrl.needSelectParticipants()" ng-class="{'active': formCtrl.step == 5, 'not_active_yet':formCtrl.isLocked(5)}" id="step_item{{ formCtrl.numberStep(5) }}" ng-click="formCtrl.setStep(5)"><span class="step_number size_xl">{{ formCtrl.getNumberStep(5) }}</span><span class="step_text size_m">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[4]|htmlSpecialDecode }}</span></a>
        <a class="step_item t200all" ng-class="{'active': formCtrl.step == 6, 'not_active_yet':formCtrl.isLocked(6)}" id="step_item{{ formCtrl.numberStep(6) }}" ng-click="formCtrl.setStep(6)"><span class="step_number size_xl">{{ formCtrl.getNumberStep(6) }}</span><span class="step_text">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[5]|htmlSpecialDecode }}</span></a>
        <a class="step_item t200all" ng-class="{'active': formCtrl.step == 7, 'not_active_yet':formCtrl.isLocked(7)}" id="step_item{{ formCtrl.numberStep(7) }}" ng-click="formCtrl.setStep(7)"><span class="step_number size_xl">{{ formCtrl.getNumberStep(7) }}</span><span class="step_text">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[6]|htmlSpecialDecode }}</span></a>
      </div>
      <div id="selected_place" style="text-align: center" ng-if="formCtrl.currentPlace != null && formCtrl.step <= 3">
        <p class="place_title" ng-bind-html="formCtrl.formatSelectedPlaceSentence('<?php echo get_locale(); ?>') |htmlSpecialDecode"></p>
        <p class="place_description" ng-bind-html="formCtrl.getTextByLocale(formCtrl.currentPlace.presentation, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
      </div>
      <div ng-if="formCtrl.serviceParameters.service != null && formCtrl.step == 3" id="selected_service">
        <p class="service_title" ng-bind-html="formCtrl.formatSelectedServiceSentence('<?php echo get_locale(); ?>') |htmlSpecialDecode"></p>
        <p class="service_description" ng-bind-html="formCtrl.getTextByLocale(formCtrl.serviceParameters.service.presentation, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
      </div>
      <div id="steps_content">
        <div id="step1_content" ng-show="formCtrl.step == 1">
          <a ng-click="formCtrl.setCurrentPlace(place); formCtrl.incrementStep();" class="place {{ place.slug }}" ng-class="{'active': formCtrl.currentPlace!=null && place.id == formCtrl.currentPlace.id}" ng-repeat="place in formCtrl.getPlaces() track by $index">
             <img class="place_image {{ place.slug }}_image" ng-if="formCtrl.settings.form_display_image_place && formCtrl.getTextByLocale(place.image, '<?php echo get_locale(); ?>') != null && formCtrl.getTextByLocale(place.image, '<?php echo get_locale(); ?>').length > 0" ng-src="{{ formCtrl.getTextByLocale(place.image, '<?php echo get_locale(); ?>') }}" />
            <h2 class="place_title">{{ formCtrl.getTextByLocale(place.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode  }}</h2>
            <p class="place_description" ng-bind-html="formCtrl.getTextByLocale(place.presentation, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
          </a>
        </div>
        <div class="{{ place.slug }}" id="step2_content" ng-show="formCtrl.step == 2">
          <div ng-if="formCtrl.numberOfServices(category) > 0 && formCtrl.getCategories().length > 0" class="category {{ category.slug }}" ng-repeat="category in formCtrl.getCategories()">
            <div>
              <img class="category_image {{ category.slug }}_image" ng-if="formCtrl.settings.form_display_image_category && formCtrl.getTextByLocale(category.image, '<?php echo get_locale(); ?>') != null && formCtrl.getTextByLocale(category.image, '<?php echo get_locale(); ?>').length > 0" ng-src="{{ formCtrl.getTextByLocale(category.image, '<?php echo get_locale(); ?>') }}" />
              <h3 class="category_title size_xl">{{ formCtrl.getTextByLocale(category.label, '<?php echo get_locale(); ?>')|htmlSpecialDecode  }}</h3>
            </div>
			     <div ng-click="formCtrl.setCurrentService(service); formCtrl.incrementStep();" class="t200all service {{ service.slug }} btn" ng-repeat="service in formCtrl.getServicesByCategory(category)">
              <img class="service_image {{ service.slug }}_image" ng-if="formCtrl.settings.form_display_image_service && formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>') != null && formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>').length > 0" ng-src="{{ formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>') }}" />
              <div class=" service_content">
                <h4 class="service_title size_l">{{ formCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
                <p class="service_description" ng-bind-html="formCtrl.getTextByLocale(service.presentation, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
              </div>
            </div>
          </div>
          <div ng-if="formCtrl.getCategories().length > 0">
            <div ng-click="formCtrl.setCurrentService(service); formCtrl.incrementStep();" class="service {{ service.slug }} btn" ng-if="formCtrl.noHaveCategory(service)" ng-repeat="service in formCtrl.getServicesByPlace()">
              <img class="service_image {{ service.slug }}_image" ng-if="formCtrl.settings.form_display_image_service && formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>') != null && formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>').length > 0" ng-src="{{ formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>') }}" />
              <div class="t200all service_content">
                <h4 class="service_title size_l">{{ formCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
                <p class="service_description" ng-bind-html="formCtrl.getTextByLocale(service.presentation, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
              </div>
            </div>
          </div>
          <div ng-show="formCtrl.getCategories().length == 0" class="category">
            <div ng-click="formCtrl.setCurrentService(service); formCtrl.incrementStep();" class="service {{ service.slug }} btn" ng-repeat="service in formCtrl.getServicesByPlace()">
              <img class="service_image {{ service.slug }}_image" ng-if="formCtrl.settings.form_display_image_service && formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>') != null && formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>').length > 0" ng-src="{{ formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>') }}" />
              <div class="t200all service_content">
                <h4 class="service_title size_l">{{ formCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
                <p class="service_description" ng-bind-html="formCtrl.getTextByLocale(service.presentation, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
              </div>
            </div>
          </div>
        </div>
        <div class="{{ formCtrl.serviceParameters.service.slug }}" id="step3_content" ng-show="formCtrl.step == 3">
          <!-- style dynamique -->
          <style ng-if="formCtrl.settings.enabledColor != ''">
          .resa_enabled{
            background-color: {{ formCtrl.settings.enabledColor }} !important;
          }
          </style>
          <style ng-if="formCtrl.settings.disabledColor != ''">
          .resa_disabled{
            background-color: {{ formCtrl.settings.disabledColor }} !important;
          }
          </style>
          <div id="step3_1">
            <h4 class="step3_1_title size_l" id="date_anchor" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_choose_a_date, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></h4>
            <div id="resa_form" class="calendrier" ng-change="formCtrl.setCurrentDate(formCtrl.currentDate)" uib-datepicker ng-model="formCtrl.currentDate" datepicker-options="formCtrl.options" ng-if="formCtrl.serviceParameters.service.typeAvailabilities == 0"></div>
            <div class="dates_selection" ng-if="formCtrl.serviceParameters.service.typeAvailabilities == 1">
              <div class="months_selection">
                <select ng-change="formCtrl.changeMonthOfDate()" ng-model="formCtrl.monthIndex" ng-options="month.index as (month.date|date:'MMMM - yyyy') for month in formCtrl.months"></select>
              </div>
              <div class="dates_selection_navigation">
                <input ng-click="formCtrl.addMonth(-1)" class="nav_previous btn" type="button" value="&lt; <?php _e('Before_word', 'resa'); ?>" />
                <input ng-click="formCtrl.addMonth(1)" class="nav_next btn" type="button" value="<?php _e('After_word', 'resa'); ?> &gt;" />
              </div>
              <div class="date_selection" ng-class="{'active':formCtrl.isSelectedGroupDates(dates)}" ng-click="formCtrl.setGroupDates(dates)" ng-repeat="dates in formCtrl.getGroupDates()"><?php _e('from2_word', 'resa'); ?> {{ dates.startDate | formatDate:'<?php echo $variables['date_format']; ?>' }}
                <?php _e('to2_word', 'resa'); ?> {{ dates.endDate | formatDate:'<?php echo $variables['date_format']; ?>' }}</div>
              <p class="italic"><?php _e('absent_definition_sentence', 'resa'); ?></p>
  					</div>
            <div class="dates_selection" ng-if="formCtrl.serviceParameters.service.typeAvailabilities == 2">
              <div class="date_selection" ng-class="{'active':formCtrl.isSelectedManyDates(dates)}" ng-click="formCtrl.setManyDates(dates)" ng-repeat="dates in formCtrl.getManyDates()"><span ng-bind-html="dates|formatManyDatesView"></span></div>
              <p class="italic"><?php _e('absent_definition_sentence', 'resa'); ?></p>
  					</div>
          </div>
          <div id="step3_2">
            <h4 class="step3_2_title size_l" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_choose_a_timeslot, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></h4>
      			<div ng-show="formCtrl.currentGetTimeslotsLaunched">Chargement....</div>
            <div class="creneaux morning" ng-show="!formCtrl.currentGetTimeslotsLaunched">
              <div class="morning_title size_m"><?php _e('Morning_word', 'resa'); ?></div>
              <div ng-click="(timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0) || formCtrl.setTimeslot(timeslot); formCtrl.serviceParameters.addNumberPriceForEachServicePrice(); formCtrl.preAddInBasket();"  ng-if="timeslot.isInMorning()" class="creneau t200all size_s" ng-repeat="timeslot in formCtrl.currentTimeslots" ng-class="{'active':timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)}" style="position:relative">
                <div ng-if="timeslot.idMention != '' && formCtrl.getMentionById(timeslot.idMention)" class="promo-label" style="background-color:{{ formCtrl.getMentionById(timeslot.idMention).backgroundColor }}">
                  {{ formCtrl.getTextByLocale(formCtrl.getMentionById(timeslot.idMention).name,'<?php echo get_locale(); ?>') }}
                </div>
                <span ng-if="!formCtrl.serviceParameters.service.customTimeslotsTextActivated">
                  <div class="hour" ng-class="{'barrer':timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0}" ng-if="!timeslot.noEnd">
                    <span class="hour_start">{{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</span>
                    <span class="hour_between_word"><?php _e('to_word', 'resa'); ?></span>
                    <span class="hour_end">{{ timeslot.dateEnd|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</span>
                  </div>
                  <div class="hour" ng-class="{'barrer':timeslot.getCalculateCapacity(formCtrl.serviceParameters)<=0}" ng-if="timeslot.noEnd">
                    <span class="hour_start"><?php _e('begin_word', 'resa'); ?> {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</span>
                  </div>
                  <div class="avalability" ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && (!timeslot.overCapacity || (timeslot.overCapacity && timeslot.display_remaining_position_overcapacity))">
                    <span ng-if="timeslot.numberExclusiveTimeslot() > 1">{{ timeslot.numberExclusiveTimeslot() }} x </span>
          					{{ timeslot.getCalculateCapacity(formCtrl.serviceParameters) }} <?php _e('positions_word', 'resa'); ?>
                  </div>
                  <div class="avalability" ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && timeslot.overCapacity && !timeslot.display_remaining_position_overcapacity">
                    <?php _e('remaining_positions_word', 'resa'); ?>
                  </div>
                  <div class="avalability" ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0">
                    <?php _e('Full_word', 'resa'); ?>
                  </div>
                </span>
                <!-- CUSTOM //-->
                <span ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextBase,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && !timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)"
                  ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextBase,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                </span>
                <span ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextSelected,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)"
                  ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextSelected,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                </span>
                <span ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextCompleted,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0"
                  ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextCompleted,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                </span>
              </div>
            </div>
            <div class="creneaux afternoon" ng-show="!formCtrl.currentGetTimeslotsLaunched">
              <div class="afternoon_title size_m"><?php _e('Afternoon_word', 'resa'); ?></div>
              <div ng-click="(timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0) || formCtrl.setTimeslot(timeslot); formCtrl.serviceParameters.addNumberPriceForEachServicePrice(); formCtrl.preAddInBasket();"  ng-if="!timeslot.isInMorning()" class="creneau t200all size_s" ng-repeat="timeslot in formCtrl.currentTimeslots" ng-class="{'active':timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)}" style="position:relative">
                <div ng-if="timeslot.idMention != '' && formCtrl.getMentionById(timeslot.idMention)" class="promo-label" style="background-color:{{ formCtrl.getMentionById(timeslot.idMention).backgroundColor }}">
                  {{ formCtrl.getTextByLocale(formCtrl.getMentionById(timeslot.idMention).name,'<?php echo get_locale(); ?>') }}
                </div>
                <span ng-if="!formCtrl.serviceParameters.service.customTimeslotsTextActivated">
                  <div class="hour" ng-class="{'barrer':timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0}" ng-if="!timeslot.noEnd">
                    <span class="hour_start">{{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</span>
                    <span class="hour_between_word"><?php _e('to_word', 'resa'); ?></span>
                    <span class="hour_end">{{ timeslot.dateEnd|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</span>
                  </div>
                  <div class="hour" ng-class="{'barrer':timeslot.getCalculateCapacity(formCtrl.serviceParameters)<=0}" ng-if="timeslot.noEnd">
                    <span class="hour_start"><?php _e('begin_word', 'resa'); ?> {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</span>
                  </div>
                  <div class="avalability" ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && (!timeslot.overCapacity || (timeslot.overCapacity && timeslot.display_remaining_position_overcapacity))">
                    <span ng-if="timeslot.numberExclusiveTimeslot() > 1">{{ timeslot.numberExclusiveTimeslot() }} x </span>
          					{{ timeslot.getCalculateCapacity(formCtrl.serviceParameters) }} <?php _e('positions_word', 'resa'); ?>
                  </div>
                  <div class="avalability" ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && timeslot.overCapacity && !timeslot.display_remaining_position_overcapacity">
                    <?php _e('remaining_positions_word', 'resa'); ?>
                  </div>
                  <div class="avalability" ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0">
                    <?php _e('Full_word', 'resa'); ?>
                  </div>
                </span>
                <!-- CUSTOM //-->
                <span ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextBase,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && !timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)"
                  ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextBase,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                </span>
                <span ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextSelected,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)"
                  ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextSelected,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                </span>
                <span ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextCompleted,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0"
                  ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextCompleted,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                </span>
              </div>
            </div>
          </div>
          <div class="size_m" id="step3_date_recap">
            <span ng-if="formCtrl.serviceParameters.days <= 1" ng-bind-html="formCtrl.formatSelectedDateSentence('<?php echo get_locale(); ?>', (formCtrl.currentDate | formatDate:'<?php echo $variables['date_format']; ?>')) | htmlSpecialDecode">
            </span>
            <span ng-if="formCtrl.serviceParameters.days > 1" ng-bind-html="formCtrl.formatSelectedDateSentence('<?php echo get_locale(); ?>', (formCtrl.currentDate | formatDate:'<?php echo $variables['date_format']; ?>') + ' - ' + (formCtrl.getEndDateDay() | formatDate:'<?php echo $variables['date_format']; ?>')) | htmlSpecialDecode">
            </span>
          </div>
          <div class="size_m" id="step3_creneau_recap" ng-if="formCtrl.getRealTotalMax() >= 0">
            <span ng-if="!formCtrl.serviceParameters.isNoEnd()" ng-bind-html="formCtrl.formatSelectedTimeslotSentence('<?php echo get_locale(); ?>', ((formCtrl.serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>') + ' <?php _e('to_word','resa') ?> ' + (formCtrl.serviceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>'))) | htmlSpecialDecode">
            </span>
            <span ng-if="formCtrl.serviceParameters.isNoEnd()" ng-bind-html="formCtrl.formatSelectedTimeslotSentence('<?php echo get_locale(); ?>', ('<?php _e('begin_word', 'resa'); ?> ' + (formCtrl.serviceParameters.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>'))) | htmlSpecialDecode">
            </span>
          </div>
          <div id="step3_3" ng-if="formCtrl.getRealTotalMax() >= 0">
            <h4 class="step3_3_title size_l" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_choose_prices, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></h4>
            <div class="price" ng-if="numberPrice.price.activated && formCtrl.displayPriceListWithTypeAccount(numberPrice.price) && !numberPrice.price.extra && formCtrl.serviceParameters.service.defaultSlugServicePrice!=numberPrice.price.slug && (formCtrl.serviceParameters.idsServicePrices.length == 0 || formCtrl.serviceParameters.idsServicePrices.indexOf(numberPrice.price.id) != -1)" ng-repeat="numberPrice in formCtrl.serviceParameters.numberPrices">
              <div class="price_title b2 size_xl">{{ formCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }} <span class="price_mention b0 size_s">{{ formCtrl.getTextByLocale(numberPrice.price.mention,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span>
              </div>
              <div class="price_description" ng-bind-html="formCtrl.getTextByLocale(numberPrice.price.presentation,'<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></div>
              <div ng-if="formCtrl.serviceParameters.equipmentsActivated && numberPrice.price.equipments.length > 0 && formCtrl.displayNumberToChoose(formCtrl.serviceParameters, numberPrice, $index)" class="price_max_equipments" ng-bind-html="formCtrl.formatRemainingEquipments('<?php echo get_locale(); ?>', formCtrl.serviceParameters.getMaxEquipmentsCanChoose($index) - numberPrice.number)"></div>
              <div class="price_value b1" ng-if="numberPrice.price.notThresholded">{{ formCtrl.round(numberPrice.price.price  * formCtrl.serviceParameters.getNumberDays()) }}
                <span class="price_value_currency">{{ formCtrl.settings.currency }}</span>
                <span class="price_unit b0 italic" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_prices_suffix_by_persons,'<?php echo get_locale(); ?>')|htmlSpecialDecode"></span>
              </div>
              <div class="price_value b1" ng-if="!numberPrice.price.notThresholded">
                <span ng-if="formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) == 0">
                  <span class="price_unit b0 italic"><?php _e('from_price_words', 'resa'); ?></span>
                  {{ formCtrl.serviceParameters.getMinThresholdPrice(numberPrice) }}
                  <span class="price_value_currency">{{ formCtrl.settings.currency }}</span>
                </span>
                <span ng-if="formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) > 0"> {{ formCtrl.round(formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) * formCtrl.serviceParameters.getNumberDays()) }}
                  <span class="price_value_currency">{{ formCtrl.settings.currency }}</span>
                  <span class="price_unit b0 italic"><?php _e('in_total_words', 'resa') ?></span>
                </span>
              </div>
              <div class="price_quantity" ng-if="formCtrl.displayNumberToChoose(formCtrl.serviceParameters, numberPrice, $index)">
                <span ng-if="formCtrl.serviceParameters.haveAllMandatoryPrices() || numberPrice.price.mandatory">
                  <span class="price_quantity_title size_l"  ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_choose_quantity, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></span>
                  <span class="price_quantity_minus_btn t200all btn" ng-click="formCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, -1); numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceZero(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPrice(formCtrl.serviceParameters, numberPrice, $index)); formCtrl.preAddInBasket();">-</span>
                  <input class="price_quantity_input b1 size_l" type="number" ng-change="numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceZero(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPrice(formCtrl.serviceParameters, numberPrice, $index)); formCtrl.preAddInBasket()" ng-model="numberPrice.number" ng-model-options="{ debounce: 500 }" placeholder="0" />
                  <span class="price_quantity_plus_btn t200all btn" ng-click="formCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, 1); numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceZero(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPrice(formCtrl.serviceParameters, numberPrice, $index));formCtrl.preAddInBasket();">+</span>
                </span>
                <span ng-if="!formCtrl.serviceParameters.haveAllMandatoryPrices() && !numberPrice.price.mandatory">
                  <?php _e('select_mandatory_price_words', 'resa') ?>
                </span>
              </div>
              <!--<div class="price_add_to_cart">
                <input class="price_add_btn t200all btn size_s" type="submit" value="Ajouter au panier" />
              </div> //-->
            </div>
          </div>
          <div id="step3_4" ng-if="formCtrl.getRealTotalMax() >= 0 && formCtrl.haveExtras(formCtrl.serviceParameters.service)">
            <h4 class="step3_4_title size_l">4 - <?php _e('addons_and_extras_words', 'resa'); ?></h4>
            <div class="extra" ng-if="numberPrice.price.activated  && formCtrl.displayPriceListWithTypeAccount(numberPrice.price) && numberPrice.price.extra && formCtrl.serviceParameters.service.defaultSlugServicePrice!=numberPrice.price.slug && (formCtrl.serviceParameters.idsServicePrices.length == 0 || formCtrl.serviceParameters.idsServicePrices.indexOf(numberPrice.price.id) != -1)" ng-repeat="numberPrice in formCtrl.serviceParameters.numberPrices">
              <div class="extra_title b1 size_xl">{{ formCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}<span class="extra_mention b0 size_s">{{ formCtrl.getTextByLocale(numberPrice.price.mention,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span>
              </div>
              <div class="extra_description" ng-bind-html="formCtrl.getTextByLocale(numberPrice.price.presentation,'<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></div>
              <div class="extra_value b1" ng-if="numberPrice.price.notThresholded">{{ numberPrice.price.price }}
                <span class="extra_value_currency">{{ formCtrl.settings.currency }}</span>
                <span class="extra_unit b0 italic" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_prices_suffix_by_persons,'<?php echo get_locale(); ?>')|htmlSpecialDecode"></span>
              </div>
              <div class="extra_value b1" ng-if="!numberPrice.price.notThresholded">
                <span ng-if="formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) == 0">
                  <span class="extra_unit b0 italic"><?php _e('from_price_words', 'resa'); ?></span>
                  {{ formCtrl.serviceParameters.getMinThresholdPrice(numberPrice) }}
                  <span class="extra_value_currency">{{ formCtrl.settings.currency }}</span>
                </span>
                <span ng-if="formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) > 0">
                  {{ formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) }}
                  <span class="extra_value_currency">{{ formCtrl.settings.currency }}</span>
                  <span class="extra_unit b0 italic"><?php _e('in_total_words', 'resa') ?></span>
                </span>
              </div>
              <div class="extra_quantity">
                <span ng-if="formCtrl.serviceParameters.haveAllMandatoryPrices() || numberPrice.price.mandatory">
                  <span class="extra_quantity_title size_l"><?php _e('Quantity_word', 'resa') ?></span>
                  <span class="extra_quantity_minus_btn t200all btn" ng-click="formCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, -1); numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceExtra(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPriceExtra(formCtrl.serviceParameters, numberPrice));formCtrl.preAddInBasket();">-</span>
                  <input class="extra_quantity_input size_l" ng-change="formCtrl.preAddInBasket()" type="number" ng-model="numberPrice.number" ng-model-options="{ debounce: 500 }" ng-blur="numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceExtra(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPriceExtra(formCtrl.serviceParameters, numberPrice));" placeholder="0" />
                  <span class="extra_quantity_plus_btn t200all btn" ng-click="formCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, 1);numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceExtra(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPriceExtra(formCtrl.serviceParameters, numberPrice));formCtrl.preAddInBasket();">+</span>
                </span>
                <span ng-if="!formCtrl.serviceParameters.haveAllMandatoryPrices() && !numberPrice.price.mandatory">
                  <?php _e('select_mandatory_price_words', 'resa') ?>
                </span>
              </div>
              <!-- <div class="extra_add_to_cart">
                <input class="extra_add_btn t200all size_s" type="submit" value="Ajouter au panier" />
              </div>//-->
            </div>
          </div>
        </div>
    	  <div ng-show="formCtrl.step == 3" class="add_service_date_btns">
    	     <a class="add_date_step_link add_date_step_linka size_m" ng-click="formCtrl.chooseNewDate(); formCtrl.scrollTo('date_anchor')">{{ formCtrl.getTextByLocale(formCtrl.settings.form_add_new_date_text,'<?php echo get_locale(); ?>') |htmlSpecialDecode}}</a>
    	     <a class="add_service_step_link size_m" ng-click="formCtrl.decrementStep(); formCtrl.scrollTo('steps_navigation')">{{ formCtrl.getTextByLocale(formCtrl.settings.form_add_new_activity_text,'<?php echo get_locale(); ?>') |htmlSpecialDecode}}</a>
    	  </div>
        <div id="step4_content" ng-show="formCtrl.step == 4">
          <div id="step4">
            <p class="customer_text" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.informations_customer_text, '<?php echo get_locale(); ?>')"></p>
            <div class="connected" ng-if="formCtrl.customer.ID != -1">
              <p class="connected_as"><?php _e('connected_with_sentence', 'resa'); ?> <br /><span class="client_name">{{ formCtrl.customer.firstName }} {{ formCtrl.customer.lastName }}</span></p>
              <p class="connected_change_infos no">Vous souhaitez modifier vos coordonnées ?<a href="#">Modifier vos informations</a></p>
              <p class="connected_not_you no"><?php _e('is_not_you_sentence', 'resa'); ?>&nbsp;<a ng-click="formCtrl.userDeconnection()"><?php _e('to_logout_link_title', 'resa'); ?></a></p>
            </div>
            <span ng-if="!formCtrl.isFacebookConnected()">
              <div class="login" ng-if="formCtrl.customer.ID == -1">
                <p>
                  <?php _e('already_register_sentence', 'resa'); ?>
                  <form>
                    <input class="input_wide" ng-model="formCtrl.customer.login" type="text" placeholder="<?php _e('email_field_title', 'resa') ?>" />
                    <input class="input_wide" ng-init="formCtrl.customer.passwordConnection=''" ng-model="formCtrl.customer.passwordConnection"  type="password" placeholder="<?php _e('password_field_title', 'resa') ?>" />
                    <input class="input_wide" ng-click="(formCtrl.customer.passwordConnection.length == 0 || formCtrl.customer.login.length== 0) || formCtrl.userConnection()" type="submit" value="<?php _e('to_login_link_title', 'resa') ?>" />
                  </form>
                </p>
				        <a ng-if="formCtrl.settings.facebook_activated" ng-click="formCtrl.login()" class="facebook_connection facebook_connection_2"></a>
              </div>
            </span>
            <div class="signin" ng-if="formCtrl.customer.ID == -1">
              <p class="signin_description"><?php _e('create_account_sentence', 'resa'); ?></p>
              <form>
                <span ng-if="formCtrl.isFacebookConnected()">{{ formCtrl.customer.lastName }}<br /></span>
                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.dataFacebook.last_name == null && formCtrl.fieldStates['lastName'] == 1)">
                  <input ng-if="formCtrl.fieldStates['lastName'] > 0" class="input_wide" type="text" ng-model="formCtrl.customer.lastName" placeholder="{{ formCtrl.getPlaceolder('<?php _e('last_name_field_title', 'resa') ?>','lastName') }}" />
                  <span ng-class="{'input_error':formCtrl.fieldStates['lastName'] == 1, 'input_info':formCtrl.fieldStates['lastName'] == 2}" ng-show="formCtrl.customer.lastName.length == 0 && formCtrl.fieldStates['lastName'] > 0"><span ng-if="formCtrl.fieldStates['lastName'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['lastName'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
                </span>
                <span ng-if="formCtrl.isFacebookConnected()">{{ formCtrl.customer.firstName }}<br /></span>
                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.dataFacebook.first_name == null && formCtrl.fieldStates['firstName'] == 1)">
                  <input ng-if="formCtrl.fieldStates['firstName'] > 0" class="input_wide" type="text" ng-model="formCtrl.customer.firstName" placeholder="{{ formCtrl.getPlaceolder('<?php _e('first_name_field_title', 'resa') ?>','firstName') }}" />
                  <span ng-class="{'input_error':formCtrl.fieldStates['firstName'] == 1, 'input_info':formCtrl.fieldStates['firstName'] == 2}" ng-show="formCtrl.customer.firstName.length == 0 && formCtrl.fieldStates['firstName'] > 0"><span ng-if="formCtrl.fieldStates['firstName'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['firstName'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
                </span>
                <span ng-if="formCtrl.isFacebookConnected()">{{ formCtrl.customer.email }}<br /></span>
                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.customer.email.length == 0)">
                  <input class="input_wide" type="email" ng-model="formCtrl.customer.email" placeholder="<?php _e('email_field_title', 'resa') ?>*" />
                  <span class="input_error"  ng-show="formCtrl.customer.email==null || formCtrl.customer.email.length == 0"><?php _e('not_an_email_address_sentence', 'resa') ?></span>
                </span>
                <span ng-if="!formCtrl.isFacebookConnected()">
                  <input class="input_wide" type="password" ng-model="formCtrl.customer.password" placeholder="<?php _e('password_field_title', 'resa') ?>*" ng-init="formCtrl.customer.password = ''">
                  <span class="input_error" ng-show="formCtrl.customer.password.length == 0"><?php _e('required_word', 'resa') ?></span>
                  <span style="color:orange; font-size:14px; font-style:italic; line-height: normal important!;" ng-show="!formCtrl.checkPassword(formCtrl.customer.password)"><?php _e('bad_password_words', 'resa'); ?></span>
                  <input class="input_wide" type="password" ng-model="formCtrl.customer.confirmPassword" placeholder="<?php _e('confirm_password_field_title', 'resa') ?>*"  ng-init="formCtrl.customer.confirmPassword = ''" />
                  <span class="input_error" ng-show="formCtrl.customer.confirmPassword.length == 0"><?php _e('required_word', 'resa') ?></span>
                  <span class="input_error" ng-show="formCtrl.customer.confirmPassword.length > 0 && formCtrl.customer.confirmPassword != formCtrl.customer.password"><?php _e('not_same_password_words', 'resa') ?></span>
                </span>

                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['company'] == 1)">
                  <input ng-if="formCtrl.fieldStates['company'] > 0" class="input_wide" type="text" ng-model="formCtrl.customer.company" placeholder="{{ formCtrl.getPlaceolder('<?php _e('company_field_title', 'resa') ?>','company') }}" />
                  <span ng-class="{'input_error':formCtrl.fieldStates['company'] == 1, 'input_info':formCtrl.fieldStates['company'] == 2}" ng-show="formCtrl.customer.company.length == 0 && formCtrl.fieldStates['company'] > 0"><span ng-if="formCtrl.fieldStates['company'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['company'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
                </span>

                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['phone'] == 1)">
                  <input ng-if="formCtrl.fieldStates['phone'] > 0" class="input_wide" type="text" ng-model="formCtrl.customer.phone" placeholder="{{ formCtrl.getPlaceolder('<?php _e('phone_field_title', 'resa') ?>','phone') }}" />
                  <span ng-class="{'input_error':formCtrl.fieldStates['phone'] == 1, 'input_info':formCtrl.fieldStates['phone'] == 2}" ng-show="formCtrl.customer.phone.length == 0 && formCtrl.fieldStates['phone'] > 0"><span ng-if="formCtrl.fieldStates['phone'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['phone'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
                </span>

                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['phone2'] == 1)">
                  <input ng-if="formCtrl.fieldStates['phone2'] > 0" class="input_wide" type="text" ng-model="formCtrl.customer.phone2" placeholder="{{ formCtrl.getPlaceolder('<?php _e('phone2_field_title', 'resa') ?>','phone2') }}" />
                  <span ng-class="{'input_error':formCtrl.fieldStates['phone2'] == 1, 'input_info':formCtrl.fieldStates['phone2'] == 2}" ng-show="formCtrl.customer.phone2.length == 0 && formCtrl.fieldStates['phone2'] > 0"><span ng-if="formCtrl.fieldStates['phone2'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['phone2'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
                </span>

                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['address'] == 1)">
                  <input ng-if="formCtrl.fieldStates['address'] > 0" class="input_wide" type="text" ng-model="formCtrl.customer.address" placeholder="{{ formCtrl.getPlaceolder('<?php _e('address_field_title', 'resa') ?>','address') }}" />
                  <span ng-class="{'input_error':formCtrl.fieldStates['address'] == 1, 'input_info':formCtrl.fieldStates['address'] == 2}" ng-show="formCtrl.customer.address.length == 0 && formCtrl.fieldStates['address'] > 0"><span ng-if="formCtrl.fieldStates['address'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['address'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
                </span>

                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['postalCode'] == 1)">
                  <input ng-if="formCtrl.fieldStates['postalCode'] > 0" class="input_wide" type="text" ng-model="formCtrl.customer.postalCode" placeholder="{{ formCtrl.getPlaceolder('<?php _e('postal_code_field_title', 'resa') ?>','postalCode') }}" />
                  <span ng-class="{'input_error':formCtrl.fieldStates['postalCode'] == 1, 'input_info':formCtrl.fieldStates['postalCode'] == 2}" ng-show="formCtrl.customer.postalCode.length == 0 && formCtrl.fieldStates['postalCode'] > 0"><span ng-if="formCtrl.fieldStates['postalCode'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['postalCode'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
                </span>

                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['town'] == 1)">
                  <input ng-if="formCtrl.fieldStates['town'] > 0" class="input_wide" type="text" ng-model="formCtrl.customer.town" placeholder="{{ formCtrl.getPlaceolder('<?php _e('town_field_title', 'resa') ?>','town') }}" />
                  <span ng-class="{'input_error':formCtrl.fieldStates['town'] == 1, 'input_info':formCtrl.fieldStates['town'] == 2}" ng-show="formCtrl.customer.town.length == 0 && formCtrl.fieldStates['town'] > 0"><span ng-if="formCtrl.fieldStates['town'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['town'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
                </span>

                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['country'] == 1)">
                  <select  class="input_wide" ng-if="formCtrl.fieldStates['country'] > 0" ng-model="formCtrl.customer.country">
                    <option value="" disabled selected>-- {{ formCtrl.getPlaceolder('<?php _e('country_field_title', 'resa') ?>','country') }} --</option>
                    <option ng-repeat="country in formCtrl.countries" value="{{ country.code }}">{{ country.name }}</option>
                  </select>
                  <span ng-class="{'input_error':formCtrl.fieldStates['country'] == 1, 'input_info':formCtrl.fieldStates['country'] == 2}" ng-show="formCtrl.customer.country.length == 0 && formCtrl.fieldStates['country'] > 0"><span ng-if="formCtrl.fieldStates['country'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['country'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
                </span>
                <label ng-if="formCtrl.fieldStates['newsletters'] > 0" for="acceptNewsletters">
                  <input id="acceptNewsletters" type="checkbox" ng-model="formCtrl.customer.registerNewsletters" />
                  <?php _e('accept_register_newsletters', 'resa'); ?><br />
                </label>
                <label for="acceptGDPR"><input id="acceptGDPR" type="checkbox" ng-model="formCtrl.checkboxCustomer" /><?php _e('gdpr_accept_to_save_data', 'resa'); ?></label>

                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['siret'] == 1)">
                  <input ng-if="formCtrl.fieldStates['siret'] > 0" class="input_wide" type="text" ng-model="formCtrl.customer.siret" placeholder="{{ formCtrl.getPlaceolder('<?php _e('siret_field_title', 'resa') ?>','siret') }}" />
      		        <span ng-class="{'input_error':formCtrl.fieldStates['siret'] == 1, 'input_info':formCtrl.fieldStates['siret'] == 2}" ng-show="formCtrl.customer.siret.length == 0 && formCtrl.fieldStates['siret'] > 0"><span ng-if="formCtrl.fieldStates['siret'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['siret'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
                </span>

                <span ng-if="!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['legalForm'] == 1)">
      		        <input ng-if="formCtrl.fieldStates['legalForm'] > 0" class="input_wide" type="text" ng-model="formCtrl.customer.legalForm" placeholder="{{ formCtrl.getPlaceolder('<?php _e('legal_form_field_title', 'resa') ?>','legalForm') }}" />
      		        <span ng-class="{'input_error':formCtrl.fieldStates['legalForm'] == 1, 'input_info':formCtrl.fieldStates['legalForm'] == 2}" ng-show="formCtrl.customer.legalForm.length == 0 && formCtrl.fieldStates['legalForm'] > 0"><span ng-if="formCtrl.fieldStates['legalForm'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['legalForm'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
                </span>

                <input ng-disabled="!formCtrl.customerIsOkForForm()" class="input_wide" ng-click="(!formCtrl.customerIsOkForForm()) || formCtrl.incrementStep()" type="submit" value="<?php _e('ok_link_title', 'resa'); ?>" />
              </form>
            </div>
          </div>
        </div>
        <div id="step5_content" ng-show="formCtrl.step == 5">
          <div id="step5">
            <div id="cart">
              <h4 class="cart_title size_m size_l"><?php _e('Complementary_informations_words', 'resa'); ?></h4>
              <p class="paiement_text" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.informations_participants_text, '<?php echo get_locale(); ?>')"></p>
              <div class="cart_content">
                <span ng-repeat="serviceElement in formCtrl.getBasket()">
                  <div class="service_section" ng-if="!serviceParameters.isLink()" ng-repeat="serviceParameters in serviceElement.servicesParameters">
                    <h4 class="service_title size_l lh_xl">{{ formCtrl.getTextByLocale(serviceElement.service.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
                    <div class="cart_row" ng-class="{'combined_row':serviceParameters.isCombined()}" ng-repeat-start="numberPrice in serviceParameters.numberPrices track by $index">
                      <div class="date_selected size_xl" ng-if="!serviceParameters.isCombined()  && !serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                        <h5 class="date">{{ serviceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}</h5>
                        <h5 class="creneau" ng-if="!serviceParameters.isNoEnd()">{{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ serviceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}</h5>
                        <h5 class="creneau" ng-if="serviceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</h5>
                      </div>
                      <div class="date_selected size_xl" ng-if="serviceParameters.isCombined()  && !serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                        <div class="absence_title size_s helpbox">Présence / date / heure</div>
                        <div  class="date_multi" ng-repeat="displayServiceParameters in formCtrl.getAllServiceParametersLink(serviceParameters)">
                          <label ng-click="displayServiceParameters.switchTag('absent')" class="present_price_btn active absent_check" for="absent_serviceParameters_{{ displayServiceParameters.id }}">
                            <input id="absent_serviceParameters_{{ displayServiceParameters.id }}" type="checkbox" ng-checked="!displayServiceParameters.haveTag('absent')" />
                            <span class="date_creneau" ng-click="displayServiceParameters.switchTag('absent')">
                              <h5 class="date">{{ displayServiceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}</h5><h5 class="creneau" ng-if="!displayServiceParameters.isNoEnd()">{{ displayServiceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ displayServiceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}</h5>
                              <h5 class="creneau" ng-if="displayServiceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ displayServiceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</h5>
                            </span>
                          </label>
                        </div>
                      </div>
                      <div class="order_recap clear">
                        <span class="order_recap_left left fleft"> <span class="order_recap_price_quantity size_l">{{ numberPrice.number }}</span> <span class="order_recap_price_quantity_separator size_l"><span ng-show="numberPrice.number==1 && !numberPrice.price.extra"><?php _e('person_word', 'resa'); ?></span><span ng-show="numberPrice.number>1 && !numberPrice.price.extra"><?php _e('persons_word', 'resa'); ?></span></span> </span>
                        <span class="order_recap_center left fleft">
                          <span class="order_recap_price_title size_l">
                            {{ formCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                            <span ng-if="!serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                              <span ng-if="numberPrice.price.notThresholded">{{ formCtrl.round(numberPrice.price.price * serviceParameters.getNumberDays()) }}</span><span ng-if="!numberPrice.price.notThresholded">{{ formCtrl.round(formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) * serviceParameters.getNumberDays()) }}</span>{{ formCtrl.settings.currency }}</span>
                            </span>
                          </span>
                        <span ng-if="serviceParameters.canCalculatePrice(numberPrice)" class="order_recap_right right fright"> <span class="order_recap_price_total size_l">{{ formCtrl.round(formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) * serviceParameters.getNumberDays()) }}{{ formCtrl.settings.currency }}</span></span>
                      </div>
                      <div class="price_description" ng-bind-html="formCtrl.getTextByLocale(numberPrice.price.presentation,'<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></div>
                      <div class="clients_informations" ng-if="serviceParameters.service.askParticipants && numberPrice.price.participantsParameter != null && numberPrice.price.participantsParameter.length > 0">
                        <h4 class="clients_informations_title size_xl"><?php _e('participants_list_words', 'resa'); ?></h4>
                        <p class="clients_informations_text size_s"><?php _e('form_participants_sentence', 'resa'); ?></p>
                        <div class="client_information client_information_titles">
                          <h5 class="client_information_title size_m"><?php _e('Participant_word', 'resa'); ?></h5>
                          <h5 class="client_{{field.varname}} input_wide info_client_input" ng-class="{'helpbox':formCtrl.getTextByLocale(field.presentation,'<?php echo get_locale(); ?>').length > 0}" ng-repeat="field in formCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields track by $index">{{ formCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
                          <span class="helpbox_content"><p ng-bind-html="formCtrl.getTextByLocale(field.presentation,'<?php echo get_locale(); ?>')|displayNewLines"></p></span></h5>
                        </div>
                        <div class="client_information" ng-repeat="number in serviceParameters.getRepeatNumberByNumber(numberPrice.number) track by $index">
                          <div class="client_information_title size_m">
                            <a ng-click="formCtrl.openParticipantSelectorDialog(formCtrl.getParticipants(serviceParameters), formCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields, serviceParameters, numberPrice, $index, false)"><?php _e('Select_word', 'resa'); ?></a>
                          </div>
                          <span class="client_information_title size_m" ng-repeat="field in formCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields track by $index">
                            <input ng-if="field.type == 'text'" ng-change="formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));" class="client_{{ field.varname }} input_wide info_client_input" list="customersList{{ serviceParameters.id }}_{{ $parent.$parent.$index }}_{{ $index }}" type="text" ng-model="formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname]" placeholder="{{ formCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}" />
                            <input ng-if="field.type == 'number'" ng-blur="formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname] = formCtrl.minMaxValue(formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname], field.min, field.max);formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));" ng-change="formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));" class="client_{{ field.varname }} input_wide info_client_input"  list="customersList{{ serviceParameters.id }}_{{ $parent.$parent.$index }}_{{ $index }}" type="number" ng-model="formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname]" placeholder="{{ formCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}"  />
                            <datalist ng-if="field.type == 'text' || field.type == 'number'" id="customersList{{ serviceParameters.id }}_{{ $parent.$parent.$index }}_{{ $index }}">
                              <option value="{{ participant[field.varname] }}" ng-repeat="participant in formCtrl.getParticipants(serviceParameters) track by $index">
                            </datalist>
                            <select ng-if="field.type == 'select'" ng-change="formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));"  class="client_{{ field.varname }} input_wide info_client_input" style="color: black;"  ng-model="formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname]" id="formInput{{ serviceParameters.id }}_{{ $parent.$parent.$index }}_{{ $index }}" ng-init="formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname] = formCtrl.getFirstSelectOption(formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname], field.options)">
                              <option ng-repeat="option in field.options" value="{{ option.id }}">{{ formCtrl.getTextByLocale(option.name,'<?php echo get_locale(); ?>') }}</option>
                            </select>
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="cart_row_infos" ng-if="serviceParameters.idParameter > -1">{{ formCtrl.getTextByLocale(formCtrl.getParameter(serviceParameters.idParameter).form,'<?php echo get_locale(); ?>') }}</div>
                    <div class="cart_row_infos infos_important" ng-if="formCtrl.idServicesParametersError.id == serviceParameters.id">{{ formCtrl.idServicesParametersError.text }}, <?php _e('error_service_parameters_deprecated_form', 'resa') ?> - <a class="delete_price_btn" ng-click="formCtrl.removeBasket(formCtrl.getServiceParametersLink(serviceParameters), $index); formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));"><?php _e('remove_link_title', 'resa'); ?></a></div>
                    <div class="reduction_row" ng-repeat-end ng-if="params.idsPrice.indexOf(numberPrice.price.id + '') != -1" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id'+serviceParameters.id] track by $index">
                      <div class="reduction_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                      <div class="reduction_description size_xs" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                      <div class="reduction_effect size_m b2">
                        <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                        <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                        <span ng-if="params.type == 3">{{ params.value }}{{ formCtrl.settings.currency }} <span class="tarif_barre"> {{ numberPrice.price.price }}{{ formCtrl.settings.currency }}</span></span>
                        <span ng-if="params.type == 4">{{ params.value * params.number }} <span ng-if="(params.value * params.number) == 1"><?php _e('offer_quantity_words', 'resa') ?></span><span ng-if="(params.value * params.number) > 1"><?php _e('offer_quantities_words', 'resa') ?></span></span>
                        <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                      </div>
                      <div class="reduction_total size_xl">
                        <span ng-if="params.type == 0">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 1">{{ ((serviceParameters.getPriceNumberPrice(numberPrice) * params.number) * params.value  / 100)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 2">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 3">{{ ((numberPrice.price.price - params.value) * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 4">{{ (serviceParameters.getPriceNumberPrice(numberPrice)/numberPrice.number * params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 5"></span></div>
                    </div>
                    <div class="reduction_row" ng-if="params.idsPrice.length == 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id'+serviceParameters.id] track by $index">
                      <div class="reduction_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                      <div class="reduction_description size_xs" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                      <div class="reduction_effect size_m b2">
                        <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                        <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                        <span ng-if="params.type == 3">{{ (numberPrice.price.price * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                      </div>
                      <div class="reduction_total size_xl">
                        <span ng-if="params.type == 0">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 1">{{ (serviceParameters.getPrice() * params.value / 100)|negative }}{{ formCtrl.settings.currency }} </span>
                        <span ng-if="params.type == 2">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 5"></span>
                      </div>
                    </div>
                    <div class="creneau_sous_total size_xl">
                      <?php _e('Sub_total_word', 'resa') ?> : <span ng-if="serviceParameters.isCombined()">{{ formCtrl.getSubTotalPrice(formCtrl.getAllServiceParametersLink(serviceParameters)) }}</span><span ng-if="!serviceParameters.isCombined()">{{ formCtrl.getSubTotalPrice([serviceParameters]) }}</span>{{ formCtrl.settings.currency }}
                    </div>
                  </div>
                  <div class="service_section">
                    <div class="reduction_row" ng-if="params.promoCode.length == 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id0'] track by $index">
                      <div class="reduction_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                      <div class="reduction_description size_xs" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                      <div class="reduction_effect size_m b2">
                        <span ng-if="params.type == 0">{{ params.value |negative }}{{ formCtrl.settings.currency }}</span>
            						<span ng-if="params.type == 1">{{ params.value|negative }}%</span>
            						<span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
            						<span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                      </div>
                      <div class="reduction_total size_xl">
                        <span ng-if="params.type == 0">{{ params.value * params.number|negative }}{{ formCtrl.settings.currency }}</span>
            						<span ng-if="params.type == 1">{{ (formCtrl.getSubTotalPrice(formCtrl.getAllServicesParameters()) * (params.value * params.number) / 100)|negative }}{{ formCtrl.settings.currency }} </span>
            						<span ng-if="params.type == 2">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
            						<span ng-if="params.type == 5"></span>
                      </div>
                    </div>
                  </div>
                  <div class="service_section" ng-if="formCtrl.haveCouponInList(formCtrl.mapIdClientObjectReduction['id0'])">
                    <h4 class="service_title size_l lh_xl"><?php _e('Coupon_word', 'resa'); ?></h4>
                    <div class="reduction_row" ng-if="params.promoCode.length > 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id0'] track by $index">
                      <div class="reduction_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                      <div class="reduction_description size_xs" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                      <div class="reduction_effect size_m b2">
                        <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_sub_total_words', 'resa') ?></span>
            						<span ng-if="params.type == 1">{{ params.value|negative }}%</span>
            						<span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
            						<span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                      </div>
                      <div class="reduction_total size_xl">
                        <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
            						<span ng-if="params.type == 1">{{ (formCtrl.getSubTotalPrice(formCtrl.getAllServicesParameters()) * params.value / 100)|negative }}{{ formCtrl.settings.currency }} </span>
            						<span ng-if="params.type == 2">{{ (params.value * formCtrl.getTotalNumber())|negative }}{{ formCtrl.settings.currency }}</span>
            						<span ng-if="params.type == 5"></span>
                      </div>
                    </div>
                  </div>
                </span>
                <div class="cart_total size_xxl"><?php _e('Total_word', 'resa') ?> : {{ formCtrl.getTotalPrice()|number:2 }}{{ formCtrl.settings.currency }}</div>
              </div>
            </div>
          </div>
        </div>
        <div id="step6_content" ng-show="formCtrl.step == 6">
          <div id="step6_1">
            <div id="cart">
              <h4 class="cart_title size_xl" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_recap_booking_title, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></h4>
              <div class="cart_content">
                <span ng-repeat="serviceElement in formCtrl.getBasket()">
                  <div class="service_section" ng-if="!serviceParameters.isLink()" ng-repeat="serviceParameters in serviceElement.servicesParameters">
                    <h4 class="service_title size_l lh_xl">{{ formCtrl.getTextByLocale(serviceElement.service.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
                    <div class="cart_row" ng-class="{'combined_row':serviceParameters.isCombined()}" ng-repeat-start="numberPrice in serviceParameters.numberPrices track by $index">
                      <div class="date_selected size_xl" ng-if="!serviceParameters.isCombined() && !serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                        <h5 class="date">{{ serviceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}</h5>
                        <h5 class="creneau" ng-if="!serviceParameters.isNoEnd()">{{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ serviceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}</h5>
                        <h5 class="creneau" ng-if="serviceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</h5>
                      </div>
                      <div class="date_selected size_xl" ng-if="serviceParameters.isCombined() && !serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                        <div class="absence_title size_s">Présence / date / heure</div>
                        <div  class="date_multi" ng-repeat="displayServiceParameters in formCtrl.getAllServiceParametersLink(serviceParameters)">
                          <label ng-click="displayServiceParameters.switchTag('absent')" class="present_price_btn active absent_check" for="absent_serviceParameters_{{ displayServiceParameters.id }}">
                            <input id="absent_serviceParameters_{{ displayServiceParameters.id }}" type="checkbox" ng-checked="!displayServiceParameters.haveTag('absent')" />
                            <span class="date_creneau" ng-click="displayServiceParameters.switchTag('absent')">
                              <h5 class="date">{{ displayServiceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}</h5><h5 class="creneau" ng-if="!displayServiceParameters.isNoEnd()">{{ displayServiceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ displayServiceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}</h5>
                              <h5 class="creneau" ng-if="displayServiceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ displayServiceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</h5>
                            </span>
                          </label>
                        </div>
                      </div>
                      <div class="order_recap clear">
                        <span class="order_recap_left left fleft"> <span class="order_recap_price_quantity size_l">{{ numberPrice.number }}</span> <span class="order_recap_price_quantity_separator size_l"><span ng-show="numberPrice.number==1 && !numberPrice.price.extra"><?php _e('person_word', 'resa'); ?></span><span ng-show="numberPrice.number>1 && !numberPrice.price.extra"><?php _e('persons_word', 'resa'); ?></span></span> </span>
                        <span class="order_recap_center left fleft">
                          <span class="order_recap_price_title size_l">{{ formCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                            <span ng-if="!serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                              <span ng-if="numberPrice.price.notThresholded">{{ formCtrl.round(numberPrice.price.price * serviceParameters.getNumberDays()) }}</span><span ng-if="!numberPrice.price.notThresholded">{{ formCtrl.round(serviceParameters.getPriceNumberPrice(numberPrice) * serviceParameters.getNumberDays()) }}</span>{{ formCtrl.settings.currency }}
                            </span>
                          </span>
                        </span>
                        <span ng-if="serviceParameters.canCalculatePrice(numberPrice)" class="order_recap_right right fright"> <span class="order_recap_price_total size_l">{{ formCtrl.round(serviceParameters.getPriceNumberPrice(numberPrice) * serviceParameters.getNumberDays()) }}{{ formCtrl.settings.currency }}</span></span>
                      </div>
                      <div class="price_description" ng-bind-html="formCtrl.getTextByLocale(numberPrice.price.presentation,'<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></div>
                      <div class="clients_informations" ng-if="serviceParameters.service.askParticipants && numberPrice.price.participantsParameter != null && numberPrice.price.participantsParameter.length > 0">
                        <h4 class="clients_informations_title size_m"><?php _e('Participants_words', 'resa'); ?></h4>
                        <table class="recap_client_information size_s">
          								<thead>
          									<tr>
          										<td ng-repeat="field in formCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields">
          											{{ formCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
          										</td>
          									</tr>
          								</thead>
          								<tbody>
          									<tr ng-repeat="participant in numberPrice.participants"><td ng-repeat="field in  formCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields">{{ formCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ formCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{ formCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</td></tr>
          								</tbody>
          							</table>

                      </div>
                    </div>
                    <div class="cart_row_infos" ng-if="serviceParameters.idParameter > -1">{{ formCtrl.getTextByLocale(formCtrl.getParameter(serviceParameters.idParameter).form,'<?php echo get_locale(); ?>') }}</div>
                    <div class="cart_row_infos infos_important" ng-if="formCtrl.idServicesParametersError.id == serviceParameters.id">{{ formCtrl.idServicesParametersError.text }}, <?php _e('error_service_parameters_deprecated_form', 'resa') ?> - <a class="delete_price_btn" ng-click="formCtrl.removeBasket(formCtrl.getServiceParametersLink(serviceParameters), $index); formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));"><?php _e('remove_link_title', 'resa'); ?></a></div>
                    <div class="reduction_row" ng-repeat-end ng-if="params.idsPrice.indexOf(numberPrice.price.id + '') != -1" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id'+serviceParameters.id] track by $index">
                      <div class="reduction_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                      <div class="reduction_description size_xs" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                      <div class="reduction_effect size_m b2">
                        <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                        <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                        <span ng-if="params.type == 3">{{ params.value }}{{ formCtrl.settings.currency }} <span class="tarif_barre"> {{ numberPrice.price.price }}{{ formCtrl.settings.currency }}</span></span>
                        <span ng-if="params.type == 4">{{ params.value * params.number }} <span ng-if="(params.value * params.number) == 1"><?php _e('offer_quantity_words', 'resa') ?></span><span ng-if="(params.value * params.number) > 1"><?php _e('offer_quantities_words', 'resa') ?></span></span>
                        <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                      </div>
                      <div class="reduction_total size_xl">
                        <span ng-if="params.type == 0">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 1">{{ ((serviceParameters.getPriceNumberPrice(numberPrice) * params.number) * params.value  / 100)|negative }}{{ formCtrl.settings.currency }} </span>
                        <span ng-if="params.type == 2">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 3">{{ ((numberPrice.price.price - params.value) * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 4">{{ (serviceParameters.getPriceNumberPrice(numberPrice)/numberPrice.number * params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 5"></span></div>
                    </div>
                    <div class="reduction_row" ng-if="params.idsPrice.length == 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id'+serviceParameters.id] track by $index">
                      <div class="reduction_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                      <div class="reduction_description size_xs" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                      <div class="reduction_effect size_m b2">
                        <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                        <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                        <span ng-if="params.type == 3">{{ (numberPrice.price.price * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                      </div>
                      <div class="reduction_total size_xl">
                        <span ng-if="params.type == 0">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 1">{{ (serviceParameters.getPrice() * params.value / 100)|negative }}{{ formCtrl.settings.currency }} </span>
                        <span ng-if="params.type == 2">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 5"></span>
                      </div>
                    </div>
                    <div class="creneau_sous_total size_xl">
                      <?php _e('Sub_total_word', 'resa') ?> : <span ng-if="serviceParameters.isCombined()">{{ formCtrl.getSubTotalPrice(formCtrl.getAllServiceParametersLink(serviceParameters)) }}</span><span ng-if="!serviceParameters.isCombined()">{{ formCtrl.getSubTotalPrice([serviceParameters]) }}</span>{{ formCtrl.settings.currency }}
                    </div>
                  </div>
                </span>
                <div class="service_section">
                  <div class="reduction_row" ng-if="params.promoCode.length == 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id0'] track by $index">
                    <div class="reduction_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                    <div class="reduction_description size_xs" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                    <div class="reduction_effect size_m b2">
                      <span ng-if="params.type == 0">{{ params.value |negative }}{{ formCtrl.settings.currency }}</span>
                      <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                      <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                      <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                    </div>
                    <div class="reduction_total size_xl">
                      <span ng-if="params.type == 0">{{ params.value * params.number|negative }}{{ formCtrl.settings.currency }}</span>
                      <span ng-if="params.type == 1">{{ (formCtrl.getSubTotalPrice(formCtrl.getAllServicesParameters()) * (params.value * params.number) / 100)|negative }}{{ formCtrl.settings.currency }} </span>
                      <span ng-if="params.type == 2">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                      <span ng-if="params.type == 5"></span>
                    </div>
                  </div>
                </div>
                <div class="service_section" ng-if="formCtrl.haveCouponInList(formCtrl.mapIdClientObjectReduction['id0'])">
                  <h4 class="service_title size_l lh_xl"><?php _e('Coupon_word', 'resa'); ?></h4>
                  <div class="reduction_row" ng-if="params.promoCode.length > 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id0'] track by $index">
                    <div class="reduction_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                    <div class="reduction_description size_xs" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                    <div class="reduction_effect size_m b2">
                      <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_sub_total_words', 'resa') ?></span>
                      <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                      <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
                      <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                    </div>
                    <div class="reduction_total size_xl">
                      <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
                      <span ng-if="params.type == 1">{{ (formCtrl.getSubTotalPrice(formCtrl.getAllServicesParameters()) * params.value / 100)|negative }}{{ formCtrl.settings.currency }} </span>
                      <span ng-if="params.type == 2">{{ (params.value * formCtrl.getTotalNumber())|negative }}{{ formCtrl.settings.currency }}</span>
                      <span ng-if="params.type == 5"></span>
                    </div>
                  </div>
                </div>
                <div class="cart_total size_xxl"><?php _e('Total_word', 'resa') ?> : {{ formCtrl.getTotalPrice()|number:2 }}{{ formCtrl.settings.currency }}</div>
              </div>
            </div>
          </div>
          <div id="step6_1B">
            <div id="client_notes">
              <h4 class="size_xl"><?php _e('Customer_note_word', 'resa'); ?></h4>
              <textarea ng-model="formCtrl.bookingCustomerNote" placeholder="{{ formCtrl.getTextByLocale(formCtrl.settings.customer_note_text, '<?php echo get_locale(); ?>') }}"></textarea>
            </div>
          </div>
          <div id="step6_2">
            <h3 ng-if="formCtrl.askPayment()" class="paiement_title size_xxl"><?php _e('Payment_word', 'resa') ?></h3>
            <p ng-if="formCtrl.askPayment()" class="paiement_text" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.informations_payment_text, '<?php echo get_locale(); ?>')"></p>
            <p ng-if="!formCtrl.settings.payment_activate && formCtrl.getTextByLocale(formCtrl.settings.payment_not_activated_text, '<?php echo get_locale(); ?>').length > 0" class="paiement_text" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.payment_not_activated_text, '<?php echo get_locale(); ?>')"></p>
            <div ng-if="formCtrl.askPayment()" class="paiement_methods">
              <h4 class="paiement_method_title"><?php _e('Payment_methods_words', 'resa') ?></h4>
              <form>
                <div class="method {{ payment.id }}" ng-if="payment.activated && formCtrl.getTypeAccountOfCustomer().paymentsTypeList[payment.id]" ng-repeat="payment in formCtrl.paymentsTypeList">
                  <input id="{{ payment.id }}" ng-model="formCtrl.typePayment" type="radio" ng-value="payment" />
                  <label class="{{ payment.id }} size_l" for="{{ payment.id }}">
                    <span class="{{ payment.class }}"></span>
                    <span ng-if="payment.title_public==null || payment.title_public == ''">{{ payment.title }}</span><span ng-if="payment.title_public!=null || payment.title_public != ''">{{ formCtrl.getTextByLocale(payment.title_public, '<?php echo get_locale(); ?>') }}
                    </span>
                    <span class="description" ng-if="payment.text != '' && formCtrl.typePayment.id == payment.id">
                      <br /><span ng-bind-html="formCtrl.getTextByLocale(payment.text, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></span>
                    </span>
                  </label>
                </div>
              </form>
            </div>
            <div class="account" ng-if="formCtrl.askPayment() && formCtrl.askAdvancePayment() && formCtrl.canPayAdvancePayment() && formCtrl.typePayment.advancePayment">
              <input id="notAdvancePayment" type="radio" ng-model="formCtrl.checkboxAdvancePayment" ng-value="false" />
              <label for="notAdvancePayment"><?php _e('not_to_pay_advance_payment_amount_words', 'resa'); ?></label><br />

               <input id="advancePayment" type="radio" ng-model="formCtrl.checkboxAdvancePayment" ng-value="true" />
               <label for="advancePayment" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.payment_ask_text_advance_payment, '<?php echo get_locale(); ?>')|displayNewLines"></label>
               <?php _e('advance_payment_amount_words', 'resa') ?> <span class="gras">{{ formCtrl.getTotalPriceAdvancePayment()|number:2 }}{{ formCtrl.settings.currency }}</span>
            </div>
            <div class="paiement_validation">
              <h4 ng-if="!formCtrl.isQuotation()" class="paiement_validation_title size_xl"><?php _e('Validation_word', 'resa'); ?></h4>
              <h4 ng-if="formCtrl.isQuotation()" class="paiement_validation_title"><?php _e('Send_quotation_request_word', 'resa'); ?></h4>
              <div class="paiement_validation_form">
                <label class="paiement_cgv_label" ng-if="formCtrl.settings.checkbox_payment">
                  <input class="paiement_method_radio" type="checkbox" ng-model="formCtrl.checkboxAcceptPayment" />
                  <span ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.checkbox_title_payment, '<?php echo get_locale(); ?>')|displayNewLines"></span>
                </label>
                <br />
                <button ng-if="!formCtrl.launchValidForm" class="paiement_validation_btn btn" ng-disabled="!formCtrl.formIsOk({
                  customerIsOk:formCtrl.customerIsOkForForm
                })" ng-click="formCtrl.validForm({customerIsOk:formCtrl.customerIsOkForForm })"><?php _e('ok_link_title', 'resa'); ?></button>
                <div class="center">
                  <span class="italic" ng-if="!formCtrl.customerIsOkForForm()">!! <?php _e('customer_error_feedback', 'resa'); ?> !!<br /></span>
                  <span class="italic" ng-if="!formCtrl.basketIsOk()">!! <?php _e('basket_error_feedback', 'resa'); ?> !!<br /></span>
                  <span class="italic" ng-if="!formCtrl.checkboxPayment()">!! <?php _e('checkbox_payment_error_feedback', 'resa'); ?> !!<br /></span>
                  <span class="italic" ng-if="!formCtrl.typePaymentChosen()">!! <?php _e('type_payment_error_feedback', 'resa'); ?> !!<br /></span>
                </div>

                <input ng-if="formCtrl.launchValidForm" class="paiement_validation_btn btn" type="submit" ng-disabled="true" value="<?php _e('valid_form_process_link_title', 'resa'); ?>" />
                <p ng-show="formCtrl.launchValidForm && !formCtrl.isQuotation()" class="paiement_validation_infos size_s"><?php _e('redict_to_payment_page_sentence', 'resa'); ?></p>
              </div>
            </div>
          </div>
        </div>
        <div id="step7_content" ng-show="formCtrl.step == 7">
    		  <span ng-if="formCtrl.redirect"><?php _e('Redirection_word_form', 'resa') ?></span>
          <div id="step7" ng-if="!formCtrl.redirect">
            <div ng-bind-html="formCtrl.confirmationText|htmlSpecialDecode:true"></div>
          </div>
        </div>
      </div>
      <div id="cart" ng-show="formCtrl.getAllServicesParameters().length > 0 && formCtrl.step < 4">
        <h4 class="cart_title size_xl"><?php _e('basket_recapitulative_form_title', 'resa') ?></h4>
        <div class="cart_content">
          <span ng-repeat="serviceElement in formCtrl.getBasket()">
            <div class="service_section" ng-if="!serviceParameters.isLink()" ng-repeat="serviceParameters in serviceElement.servicesParameters">
              <h4 class="service_title size_l lh_xl">{{ $index + 1 }} - {{ formCtrl.getTextByLocale(serviceElement.service.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
              <div class="cart_row" ng-class="{'combined_row':serviceParameters.isCombined()}" ng-repeat-start="numberPrice in formCtrl.getServiceParametersLink(serviceParameters).numberPrices track by $index">
                <div class="date_selected size_xl" ng-if="!serviceParameters.isCombined() && !serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                  <h5 class="date">{{ serviceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}</h5>
                  <h5 class="creneau" ng-if="!serviceParameters.isNoEnd()">{{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ serviceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}</h5>
                  <h5 class="creneau" ng-if="serviceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</h5>
                </div>
                <div class="date_selected size_xl" ng-if="serviceParameters.isCombined() && !serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                  <div class="absence_title size_s">Présence / date / heure</div>
                  <div  class="date_multi" ng-repeat="displayServiceParameters in formCtrl.getAllServiceParametersLink(serviceParameters)">
                    <label ng-click="displayServiceParameters.switchTag('absent')" class="present_price_btn active absent_check" for="absent_serviceParameters_{{ displayServiceParameters.id }}">
                      <input id="absent_serviceParameters_{{ displayServiceParameters.id }}" type="checkbox" ng-checked="!displayServiceParameters.haveTag('absent')" />
                      <span class="date_creneau" ng-click="displayServiceParameters.switchTag('absent')">
                        <h5 class="date">{{ displayServiceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}</h5><h5 class="creneau" ng-if="!displayServiceParameters.isNoEnd()">{{ displayServiceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ displayServiceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}</h5>
                        <h5 class="creneau" ng-if="displayServiceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ displayServiceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</h5>
                      </span>
                    </label>
                  </div>
                </div>
                <div class="price_title size_m b1">
                  {{ formCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                  <span ng-if="!serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                    <span ng-if="numberPrice.price.notThresholded">{{ formCtrl.round(numberPrice.price.price * serviceParameters.getNumberDays()) }}</span><span ng-if="!numberPrice.price.notThresholded">{{ formCtrl.round(serviceParameters.getPriceNumberPrice(numberPrice) * serviceParameters.getNumberDays()) }}</span>{{ formCtrl.settings.currency }}
                  </span>
                </div>
                <div class="price_description" ng-bind-html="formCtrl.getTextByLocale(numberPrice.price.presentation,'<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></div>
                <div ng-if="serviceParameters.equipmentsActivated && numberPrice.price.equipments.length > 0" class="price_max_equipments" ng-bind-html="formCtrl.formatRemainingEquipments('<?php echo get_locale(); ?>', serviceParameters.getMaxEquipmentsCanChoose($index) - numberPrice.number)"></div>
                <div ng-if="!serviceParameters.isDefaultSlugServicePrice(numberPrice)" class="price_quantity">
                  <input ng-if="!numberPrice.price.extra" class="selected_quantity size_l" type="number" ng-change="numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPrice(serviceParameters, numberPrice), formCtrl.getMaxNumberPrice(serviceParameters, numberPrice, $index));formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters))" ng-model-options="{ debounce: 500 }" ng-model="numberPrice.number"  />
                  <input ng-if="numberPrice.price.extra" class="selected_quantity size_l" type="number" ng-change="numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceExtra(serviceParameters, numberPrice), formCtrl.getMaxNumberPriceExtra(serviceParameters, numberPrice));formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters))" ng-model-options="{ debounce: 500 }" ng-model="numberPrice.number"  />
                </div>
                <div ng-if="serviceParameters.canCalculatePrice(numberPrice)" class="line_total size_xl">
                  {{ formCtrl.round(serviceParameters.getPriceNumberPrice(numberPrice) * serviceParameters.getNumberDays()) }}{{ formCtrl.settings.currency }}
                </div>
                <div ng-if="!serviceParameters.isDefaultSlugServicePrice(numberPrice)" class="delete_price">
                  <a class="delete_price_btn" ng-click="formCtrl.removeBasket(formCtrl.getServiceParametersLink(serviceParameters), $index); formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));"><?php _e('delete_link_title', 'resa'); ?></a>
                </div>
              </div>
              <div class="cart_row_infos" ng-if="serviceParameters.idParameter > -1">{{ formCtrl.getTextByLocale(formCtrl.getParameter(serviceParameters.idParameter).form,'<?php echo get_locale(); ?>') }}</div>
              <div class="cart_row_infos infos_important" ng-if="formCtrl.idServicesParametersError.id == serviceParameters.id">{{ formCtrl.idServicesParametersError.text }}, <?php _e('error_service_parameters_deprecated_form', 'resa') ?> - <a class="delete_price_btn" ng-click="formCtrl.removeBasket(formCtrl.getServiceParametersLink(serviceParameters), $index); formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));"><?php _e('remove_link_title', 'resa'); ?></a></div>
              <div class="reduction_row" ng-repeat-end ng-if="params.idsPrice.indexOf(numberPrice.price.id + '') != -1" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id'+serviceParameters.id] track by $index">
                <div class="reduction_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                <div class="reduction_description size_xs" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                <div class="reduction_effect size_m b2">
                  <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                  <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                  <span ng-if="params.type == 3">{{ params.value }}{{ formCtrl.settings.currency }} <span class="tarif_barre"> {{ numberPrice.price.price }}{{ formCtrl.settings.currency }}</span></span>
                  <span ng-if="params.type == 4">{{ params.value * params.number }} <span ng-if="(params.value * params.number) == 1"><?php _e('offer_quantity_words', 'resa') ?></span><span ng-if="(params.value * params.number) > 1"><?php _e('offer_quantities_words', 'resa') ?></span></span>
                  <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                </div>
                <div class="reduction_total size_xl">
                  <span ng-if="params.type == 0">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 1">{{ ((serviceParameters.getPriceNumberPrice(numberPrice) * params.number) * params.value  / 100)|negative }}{{ formCtrl.settings.currency }} </span>
                  <span ng-if="params.type == 2">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 3">{{ ((numberPrice.price.price - params.value) * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 4">{{ (serviceParameters.getPriceNumberPrice(numberPrice)/numberPrice.number * params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 5"></span></div>
              </div>
              <div class="reduction_row" ng-if="params.idsPrice.length == 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id'+serviceParameters.id] track by $index">
                <div class="reduction_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                <div class="reduction_description size_xs" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                <div class="reduction_effect size_m b2">
                  <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                  <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                  <span ng-if="params.type == 3">{{ (numberPrice.price.price * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                </div>
                <div class="reduction_total size_xl">
                  <span ng-if="params.type == 0">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 1">{{ (serviceParameters.getPrice() * params.value / 100)|negative }}{{ formCtrl.settings.currency }} </span>
                  <span ng-if="params.type == 2">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 5"></span>
                </div>
              </div>
              <div class="creneau_sous_total size_xl">
                <?php _e('Sub_total_word', 'resa') ?> : <span ng-if="serviceParameters.isCombined()">{{ formCtrl.getSubTotalPrice(formCtrl.getAllServiceParametersLink(serviceParameters)) }}</span><span ng-if="!serviceParameters.isCombined()">{{ formCtrl.getSubTotalPrice([serviceParameters]) }}</span>{{ formCtrl.settings.currency }}
              </div>
            </div>
          </span>
          <div class="service_section">
            <div class="reduction_row" ng-if="params.promoCode.length == 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id0'] track by $index">
              <div class="reduction_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
              <div class="reduction_description size_xs" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
              <div class="reduction_effect size_m b2">
                <span ng-if="params.type == 0">{{ params.value |negative }}{{ formCtrl.settings.currency }}</span>
                <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
              </div>
              <div class="reduction_total size_xl">
                <span ng-if="params.type == 0">{{ params.value * params.number|negative }}{{ formCtrl.settings.currency }}</span>
                <span ng-if="params.type == 1">{{ (formCtrl.getSubTotalPrice(formCtrl.getAllServicesParameters()) * (params.value * params.number) / 100)|negative }}{{ formCtrl.settings.currency }} </span>
                <span ng-if="params.type == 2">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                <span ng-if="params.type == 5"></span>
              </div>
            </div>
          </div>
          <div class="coupon_row" ng-if="params.promoCode.length > 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id0'] track by $index">
            <div class="coupon_title size_m">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
            <div class="coupon_description size_xs"  ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
            <div class="coupon_total size_xl">
              <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
              <span ng-if="params.type == 1">{{ (formCtrl.getSubTotalPrice(formCtrl.getAllServicesParameters()) * params.value / 100)|negative }}{{ formCtrl.settings.currency }} </span>
              <span ng-if="params.type == 2">{{ (params.value * formCtrl.getTotalNumber())|negative }}{{ formCtrl.settings.currency }}</span>
              <span ng-if="params.type == 5"></span>
            </div>
            <div><button class="btn btn_add_coupon" ng-if="params.promoCode.length > 0" ng-click="formCtrl.deleteCoupon(params.promoCode)"><?php _e('delete_link_title', 'resa'); ?></button></div>
          </div>
          <div ng-if="true" class="cart_coupon size_m lh_m">
            <h4 class="cart_add_coupon_title"><?php _e('add_coupon_link_title', 'resa') ?></h4>
            <input ng-disabled="formCtrl.getReductionsLaunched" type="text" ng-model="formCtrl.currentPromoCode" placeholder="<?php _e('Coupon_word', 'resa'); ?>" />
            <button ng-disabled="formCtrl.getReductionsLaunched"  class="btn btn_add_coupon" type="button" ng-click="formCtrl.getReductions(formCtrl.currentPromoCode)"> <?php _e('add_link_title', 'resa') ?><span ng-if="formCtrl.getReductionsLaunched">...</span></button>
            <div ng-if="formCtrl.getReductionsLaunched"><?php _e('process_reductions_feedback', 'resa'); ?></div>
          </div>
          <div class="cart_total size_xxl"><?php _e('Total_word', 'resa') ?> : {{ formCtrl.getTotalPrice()|number:2 }}{{ formCtrl.settings.currency }}</div>
        </div>
      </div>
    	<div ng-show="formCtrl.getAllServicesParameters().length > 0 && formCtrl.step == 3" class="add_service_date_btns">
    		<a class="add_date_step_link add_date_step_linkb size_m" ng-click="formCtrl.chooseNewDate(); formCtrl.scrollTo('date_anchor') ">{{ formCtrl.getTextByLocale(formCtrl.settings.form_add_new_date_text,'<?php echo get_locale(); ?>') |htmlSpecialDecode}}</a>
    		<a class="add_service_step_link size_m" ng-click="formCtrl.decrementStep(); formCtrl.scrollTo('steps_navigation') ">{{ formCtrl.getTextByLocale(formCtrl.settings.form_add_new_activity_text,'<?php echo get_locale(); ?>') |htmlSpecialDecode}}</a>
    	</div>
      <div id="next_step_nav">
        <div class="previous_step" ng-show="formCtrl.haveBefore()">
          <a class="previous_step_link size_m" ng-click="formCtrl.decrementStep()"><?php _e('previous_step_link_title', 'resa'); ?></a>
        </div>
        <div class="next_step" ng-show="formCtrl.haveNext()">
          <a class="next_step_link size_m" ng-click="formCtrl.incrementStep()"><?php _e('next_step_link_title', 'resa'); ?></a>
        </div>
        <div class="next_step" ng-show="formCtrl.step == 5 && !formCtrl.participantsIsSelected()">
          <?php _e('empty_field_participant_form_error', 'resa'); ?>
        </div>
      </div>
    </div>
  </section>
</div>
