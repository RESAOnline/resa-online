<!-- Nouveau formulaire 25 juin 2019 //-->
<div ng-app="resa_app" ng-controller="NewFormController as formCtrl" ng-init='formCtrl.init(<?php echo htmlspecialchars($variables['months'], ENT_QUOTES); ?>, <?php echo htmlspecialchars($variables['countries'], ENT_QUOTES); ?>, "<?php echo admin_url('admin-ajax.php'); ?>", "<?php echo $variables['currentUrl']; ?>",
  "<?php echo htmlspecialchars($variables['form'], ENT_QUOTES); ?>", <?php echo htmlspecialchars($variables['serviceSlugs'], ENT_QUOTES); ?>, <?php echo $variables['quotation']; ?>, <?php echo htmlspecialchars($variables['typesAccounts'], ENT_QUOTES); ?>, ["<?php _e('voucher_error_feedback', 'resa'); ?>"])
  ' ng-cloak>
	<div ng-if="!formCtrl.isBrowserCompatibility()" style="color:red; font-weight:bold">
    <span ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.browser_not_compatible_sentence, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></span>
	</div>
  <div id="resa-form" class="resa-talign-center" ng-if="!formCtrl.initializationOk">
    <h2><?php _e('loading_word', 'resa'); ?></h2>
  </div>
  <div id="resa-form" class="resa-talign-center" ng-if="formCtrl.initializationOk && !formCtrl.settings.formActivated">
    <div ng-if="formCtrl.customer.ID != -1 && (formCtrl.customer.role == 'RESA_Manager' || formCtrl.customer.role == 'administrator')">
      <p>Pour éditer ce formulaire, veuillez cliquer sur ce lien : <a href="<?php echo RESA_Variables::getLinkParameters('forms/' . $variables['form']); ?>" target="_blank">Formulaire</a></p>
    </div>
    <div class="form_deactivated" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_deactivated_text, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></div>
  </div>
  <div id="resa-form" ng-if="formCtrl.initializationOk && formCtrl.settings.formActivated">
    <div id="resa-connexion" class="resa-talign-center" ng-if="!formCtrl.isFacebookConnected() && formCtrl.customer.ID == -1">
      <h4><?php _e('Hello_word', 'resa'); ?>, <?php _e('already_customer_words', 'resa'); ?></h4>
      <p ng-if="!formCtrl.displayFormConnectionFirstStep"><?php _e('If_yes_words', 'resa'); ?>, <a ng-click="formCtrl.displayFormConnectionFirstStep = true"><?php _e('connect_you_to_account_words', 'resa'); ?></a></p>
      <div class="resa-facebook-login" ng-if="formCtrl.settings.facebook_activated"  ng-click="formCtrl.login()">
        <img class="resa-facebook-login-image" src="<?php echo plugins_url('..', __FILE__ ); ?>/images/facebook-login.png" /></a>
        <p class="resa-talign-center"  ng-click="formCtrl.login()" >Se connecter avec facebook</p>
      </div>
      <p ng-if="!formCtrl.displayFormConnectionFirstStep"><?php _e('else_continue_your_first_booking_words', 'resa'); ?></p>
    </div>
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
    <div class="resa-talign-center" ng-if="formCtrl.isFacebookConnected() && formCtrl.customer.ID == -1">
      <form>
        <?php _e('create_account_by_facebook_sentence', 'resa'); ?><br />
        {{ formCtrl.customer.lastName }} {{ formCtrl.customer.firstName }}<br />
        <?php _e('email_field_title', 'resa'); ?> : {{ formCtrl.customer.email }}<br />
      </form>
    </div>
    <div class="resa-talign-center" ng-if="formCtrl.customer.ID != -1">
      <div ng-if="formCtrl.customer.role == 'RESA_Manager' || formCtrl.customer.role == 'administrator'">
        <p style="color:red; font-weight:bold;">
          !! Vous êtes connecté en tant que RESA Manager (ou Administrateur), ce formulaire est pour vos client.<br />
          Si vous voulez créer une nouvelle réservation, rendez-vous dans l'administation ! !!
        </p>
        <p>Pour éditer ce formulaire, veuillez cliquer sur ce lien : <a href="<?php echo RESA_Variables::getLinkParameters('forms/' . $variables['form']); ?>" target="_blank">Formulaire</a></p>
      </div>
      <h4><?php _e('Hello_word', 'resa'); ?><span ng-if="formCtrl.customer.firstName.length > 0 || formCtrl.customer.lastName.length > 0"> {{ formCtrl.customer.firstName }}  {{ formCtrl.customer.lastName }}</span><span ng-if="formCtrl.customer.firstName.length == 0 && formCtrl.customer.lastName.length == 0">{{ formCtrl.customer.company }}</span></h4>
      <p><?php _e('you_is_not_words', 'resa'); ?> ? <a ng-click="formCtrl.userDeconnection()"><?php _e('logout_link_title', 'resa'); ?></a></p>
    </div>
    <div id="resa-recent-activity" ng-if="formCtrl.lastBooking">
      <h4><?php _e('last_booking_title', 'resa'); ?></h4>
      <p class="resa-talign-center">
        <?php _e('Booking_word', 'resa'); ?> n°{{ formCtrl.lastBooking.realIdCreation }} <?php _e('booking_first_date_link_word', 'resa'); ?> {{ formCtrl.parseDate(formCtrl.lastBooking.startDate) | formatDateTime:'<?php echo $variables['date_format']; ?>' }}
      </p>
      <p><?php _e('booking_created_words', 'resa'); ?> {{ formCtrl.lastBooking.creationDate | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</p>
      <p>
        <?php _e('booking_status_words', 'resa'); ?>:
        <b class="vert text-important" ng-if="formCtrl.lastBooking.status == 'ok'"><?php _e('booking_status_ok_word', 'resa'); ?></b>
        <b class="jaune text-important" ng-if="formCtrl.lastBooking.status == 'waiting'">
          <?php _e('booking_status_waiting_word', 'resa'); ?>
          <span ng-if="formCtrl.lastBooking.waitingSubState == 1">(<?php _e('payment_word', 'resa'); ?>)</span>
          <span ng-if="formCtrl.lastBooking.waitingSubState == 2">(<?php _e('expired_word', 'resa'); ?>)</span>
        </b>
        <b class="rouge text-important" ng-if="formCtrl.lastBooking.status == 'cancelled'"><?php _e('booking_status_cancelled_word', 'resa'); ?></b>
      </p>
      <p>
        <?php _e('payment_status_words', 'resa'); ?>:
        <b class="vert text-important" ng-if="formCtrl.lastBooking.paymentState == 'complete'"><?php _e('payment_status_complete_word', 'resa'); ?></b>
        <b class="jaune text-important" ng-if="formCtrl.lastBooking.paymentState == 'advancePayment'"><?php _e('payment_status_advancePayment_word', 'resa'); ?></b>
        <b class="jaune text-important" ng-if="formCtrl.lastBooking.paymentState == 'deposit'"><?php _e('payment_status_deposit_word', 'resa'); ?></b>
        <b class="rouge text-important" ng-if="formCtrl.lastBooking.paymentState == 'noPayment'"><?php _e('payment_status_noPayment_word', 'resa'); ?></b>
      </p>
      <p class="resa-talign-right text-important"><?php _e('Total_word','resa'); ?> : {{ formCtrl.lastBooking.totalPrice }}{{ formCtrl.settings.currency }}</p>
      <a class="resa-btn btn-wide btn-dark"ng-click="formCtrl.newBooking()"><?php _e('new_booking_link_title', 'resa'); ?></a>
    </div>
    <div id="resa-form-navigation" ng-if="!formCtrl.lastBooking">
      <div class="navigation-step" ng-show="formCtrl.needSelectPlace()" ng-class="{'selected': formCtrl.step == 1, 'active':formCtrl.step != 1 && !formCtrl.isLocked(1)}" id="step_item{{ formCtrl.numberStep(1) }}" ng-click="formCtrl.setStep(1)"><div class="step-number">{{ formCtrl.getNumberStep(1) }}</div><div class="step-title">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[0]|htmlSpecialDecode }}</div></div>
      <div class="navigation-step" ng-class="{'selected': formCtrl.step == 2, 'active':formCtrl.step != 2 && !formCtrl.isLocked(2)}" id="step_item{{ formCtrl.numberStep(2) }}" ng-click="formCtrl.setStep(2)"><div class="step-number">{{ formCtrl.getNumberStep(2) }}</div><div class="step-title">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[1]|htmlSpecialDecode }}</div></div>
      <div class="navigation-step" ng-class="{'selected': formCtrl.step == 3, 'active':formCtrl.step != 3 && !formCtrl.isLocked(3)}" id="step_item{{ formCtrl.numberStep(3) }}" ng-click="formCtrl.setStep(3)"><div class="step-number">{{ formCtrl.getNumberStep(3) }}</div><div class="step-title size_s">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[2]|htmlSpecialDecode }}</div></div>
      <div class="navigation-step" ng-show="!formCtrl.userIsConnected()" ng-class="{'selected': formCtrl.step == 4, 'active':formCtrl.step != 4 && !formCtrl.isLocked(4)}" id="step_item{{ formCtrl.numberStep(4) }}" ng-click="formCtrl.setStep(4)"><div class="step-number">{{ formCtrl.getNumberStep(4) }}</div><div class="step-title">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[3]|htmlSpecialDecode }}</div></div>
      <div class="navigation-step" ng-show="formCtrl.needSelectParticipants()" ng-class="{'selected': formCtrl.step == 5, 'active':formCtrl.step != 5 && !formCtrl.isLocked(5)}" id="step_item{{ formCtrl.numberStep(5) }}" ng-click="formCtrl.setStep(5)"><div class="step-number">{{ formCtrl.getNumberStep(5) }}</div><div class="step-title size_m">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[4]|htmlSpecialDecode }}</div></div>
      <div class="navigation-step" ng-class="{'selected': formCtrl.step == 6, 'active':formCtrl.step != 6 && !formCtrl.isLocked(6)}" id="step_item{{ formCtrl.numberStep(6) }}" ng-click="formCtrl.setStep(6)"><div class="step-number">{{ formCtrl.getNumberStep(6) }}</div><div class="step-title">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[5]|htmlSpecialDecode }}</div></div>
      <div class="navigation-step" ng-class="{'selected': formCtrl.step == 7, 'active':formCtrl.step != 7 && !formCtrl.isLocked(7)}" id="step_item{{ formCtrl.numberStep(7) }}" ng-click="formCtrl.setStep(7)"><div class="step-number">{{ formCtrl.getNumberStep(7) }}</div><div class="step-title">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[6]|htmlSpecialDecode }}</div></div>
    </div>
    <div id="resa-form-navigation-mobile" ng-if="!formCtrl.lastBooking">
      <div class="navigation-step navigation-previous-step" ng-show="formCtrl.haveBefore()">
        <div class="resa-btn btn-small step-title" ng-click="formCtrl.decrementStep()">« <?php _e('previous_step_link_title', 'resa'); ?></div>
      </div>
      <div class="navigation-step selected">
        <div class="step-title">{{ formCtrl.getTextByLocale(formCtrl.settings.form_steps_title, '<?php echo get_locale(); ?>')[formCtrl.step - 1 ]|htmlSpecialDecode }}</div>
      </div>
      <div class="navigation-step navigation-next-step" ng-show="formCtrl.haveNext()">
        <div class="resa-btn btn-small step-title" ng-click="formCtrl.incrementStep()"><?php _e('next_step_link_title', 'resa'); ?> &#187;</div>
      </div>
    </div>
    <div id="resa-steps-content" ng-if="!formCtrl.lastBooking">
      <div id="steps">
        <div class="resa-talign-center" ng-if="formCtrl.currentPlace != null && formCtrl.step <= 3">
          <p class="resa-place-title" ng-bind-html="formCtrl.formatSelectedPlaceSentence('<?php echo get_locale(); ?>') |htmlSpecialDecode"></p>
          <p class="resa-place-description" ng-bind-html="formCtrl.getTextByLocale(formCtrl.currentPlace.presentation, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
        </div>
        <div class="resa-talign-center" ng-if="formCtrl.serviceParameters.service != null && formCtrl.step == 3">
          <p class="resa-service-title" ng-bind-html="formCtrl.formatSelectedServiceSentence('<?php echo get_locale(); ?>') |htmlSpecialDecode"></p>
          <p class="resa-service-description" ng-bind-html="formCtrl.getTextByLocale(formCtrl.serviceParameters.service.presentation, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
        </div>
        <div id="resa-step-places" ng-show="formCtrl.step == 1">
          <div class="resa-places-content resa-places-cols">
            <div class="resa-place {{ place.slug }}"  ng-click="formCtrl.setCurrentPlace(place); formCtrl.incrementStep();" ng-class="{'selected': formCtrl.currentPlace!=null && place.id == formCtrl.currentPlace.id}" ng-repeat="place in formCtrl.getPlaces() track by $index">
              <img class="rounded xlarge {{ place.slug }}_image" ng-if="formCtrl.settings.form_display_image_place && formCtrl.getTextByLocale(place.image, '<?php echo get_locale(); ?>') != null && formCtrl.getTextByLocale(place.image, '<?php echo get_locale(); ?>').length > 0" ng-src="{{ formCtrl.getTextByLocale(place.image, '<?php echo get_locale(); ?>') }}" />
             <h4 class="resa-talign-center">{{ formCtrl.getTextByLocale(place.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode  }}</h2>
             <p class="resa-talign-justify" ng-bind-html="formCtrl.getTextByLocale(place.presentation, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
            </div>
          </div>
        </div>
        <div id="resa-step-categories" ng-show="formCtrl.step == 2">
          <div class="resa-activities-content activities-col">
            <div class="resa-category {{ category.slug }}" ng-if="formCtrl.numberOfServices(category) > 0 && formCtrl.getCategories().length > 0" ng-repeat="category in formCtrl.getCategories()">
              <h4 class="category-title resa-talign-center">
                {{ formCtrl.getTextByLocale(category.label, '<?php echo get_locale(); ?>')|htmlSpecialDecode  }}
              </h4>
              <img class="category-image {{ category.slug }}_image" ng-if="formCtrl.settings.form_display_image_category && formCtrl.getTextByLocale(category.image, '<?php echo get_locale(); ?>') != null && formCtrl.getTextByLocale(category.image, '<?php echo get_locale(); ?>').length > 0" ng-src="{{ formCtrl.getTextByLocale(category.image, '<?php echo get_locale(); ?>') }}"/>
              <div class="category-activities">
                <div class="activity resa-btn {{ service.slug }}" ng-click="formCtrl.setCurrentService(service); formCtrl.incrementStep();" ng-repeat="service in formCtrl.getServicesByCategory(category)">
                  <h5 class="activity-title resa-talign-center">{{ formCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h5>
                </div>
              </div>
            </div>
            <div class="resa-activity {{ service.slug }}" ng-click="formCtrl.setCurrentService(service); formCtrl.incrementStep();" ng-if="formCtrl.getCategories().length > 0 && formCtrl.noHaveCategory(service)" ng-repeat="service in formCtrl.getServicesByPlace()">
              <img class="category-image {{ service.slug }}_image" ng-if="formCtrl.settings.form_display_image_service && formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>') != null && formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>').length > 0" ng-src="{{ formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>') }}" />
              <h5 class="activity-title resa-talign-center">{{ formCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h5>
              <p class="activity-description" ng-bind-html="formCtrl.getTextByLocale(service.presentation, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
            </div>
            <div class="resa-activity {{ service.slug }}" ng-if="formCtrl.getCategories().length == 0" ng-click="formCtrl.setCurrentService(service); formCtrl.incrementStep();" ng-repeat="service in formCtrl.getServicesByPlace()">
              <img class="category-image {{ service.slug }}_image" ng-if="formCtrl.settings.form_display_image_service && formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>') != null && formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>').length > 0" ng-src="{{ formCtrl.getTextByLocale(service.image, '<?php echo get_locale(); ?>') }}" />
              <h5 class="activity-title resa-talign-center">{{ formCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h5>
              <p class="activity-description" ng-bind-html="formCtrl.getTextByLocale(service.presentation, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
            </div>
          </div>
        </div>
        <div id="resa-step-date" class="{{ formCtrl.serviceParameters.service.slug }}" ng-show="formCtrl.step == 3">
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
          <div class="resa-dates">
            <h3 class="resa-talign-center" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_choose_a_date, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></h3>
            <div class="resa-datepicker resa-talign-center">
              <div id="resa_form" class="calendar" ng-change="formCtrl.setCurrentDate(formCtrl.currentDate)" uib-datepicker ng-model="formCtrl.currentDate" datepicker-options="formCtrl.options" ng-if="formCtrl.serviceParameters.service.typeAvailabilities == 0"></div>
              <div class="resa-month-selector-content" ng-if="formCtrl.serviceParameters.service.typeAvailabilities == 1">
                <select  class="resa-month-selector" ng-change="formCtrl.changeMonthOfDate()" ng-model="formCtrl.monthIndex" ng-options="month.index as (month.date|date:'MMMM - yyyy') for month in formCtrl.months"></select>
                <div class="resa-period-content">
                  <div class="resa-period" ng-class="{'selected':formCtrl.isSelectedGroupDates(dates)}" ng-click="formCtrl.setGroupDates(dates)" ng-repeat="dates in formCtrl.getGroupDates()">
                    <?php _e('from2_word', 'resa'); ?> {{ dates.startDate | formatDate:'<?php echo $variables['date_format']; ?>' }}
                    <?php _e('to2_word', 'resa'); ?> {{ dates.endDate | formatDate:'<?php echo $variables['date_format']; ?>' }}
                  </div>
                  <p class="resa-fstyle-italic"><?php _e('absent_definition_sentence', 'resa'); ?></p>
                </div>
              </div>
              <div class="resa-month-selector-content" ng-if="formCtrl.serviceParameters.service.typeAvailabilities == 2">
                <div class="resa-period-content">
                  <div class="resa-period" ng-class="{'selected':formCtrl.isSelectedManyDates(dates)}" ng-click="formCtrl.setManyDates(dates)" ng-repeat="dates in formCtrl.getManyDates()"><span ng-bind-html="dates|formatManyDatesView"></span></div>
                  <p class="resa-fstyle-italic"><?php _e('absent_definition_sentence', 'resa'); ?></p>
                </div>
    					</div>
              <p class="resa-date_picked text-important" ng-if="formCtrl.serviceParameters.days <= 1" ng-bind-html="formCtrl.formatSelectedDateSentence('<?php echo get_locale(); ?>', (formCtrl.currentDate | formatDate:'<?php echo $variables['date_format']; ?>')) | htmlSpecialDecode">
              </p>
              <p class="resa-date_picked text-important"ng-if="formCtrl.serviceParameters.days > 1" ng-bind-html="formCtrl.formatSelectedDateSentence('<?php echo get_locale(); ?>', (formCtrl.currentDate | formatDate:'<?php echo $variables['date_format']; ?>') + ' - ' + (formCtrl.getEndDateDay() | formatDate:'<?php echo $variables['date_format']; ?>')) | htmlSpecialDecode"></p>
            </div>

            <h3 class="resa-talign-center" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_choose_a_timeslot, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></h3>
            <div class="resa-timeslots resa-talign-center resa-timeslots-2col">
              <div ng-show="formCtrl.currentGetTimeslotsLaunched">Chargement....</div>
              <div class="resa-timeslots-morning resa-timeslots-column" ng-show="!formCtrl.currentGetTimeslotsLaunched">
                <h4><?php _e('Morning_word', 'resa'); ?></h4>
                <div class="resa-timeslot" ng-click="(timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0) || formCtrl.setTimeslot(timeslot); formCtrl.serviceParameters.addNumberPriceForEachServicePrice(); formCtrl.preAddInBasket();"  ng-if="timeslot.isInMorning()" ng-repeat="timeslot in formCtrl.currentTimeslots" ng-class="{'selected':timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)}">
                  <div ng-if="timeslot.idMention != '' && formCtrl.getMentionById(timeslot.idMention)" class="promo-label" style="background-color:{{ formCtrl.getMentionById(timeslot.idMention).backgroundColor }}">
                    {{ formCtrl.getTextByLocale(formCtrl.getMentionById(timeslot.idMention).name,'<?php echo get_locale(); ?>') }}
                  </div>
                  <div ng-if="!formCtrl.serviceParameters.service.customTimeslotsTextActivated">
                    <div ng-class="{'barrer':timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0}" ng-if="!timeslot.noEnd">
                      {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }} <?php _e('to_word', 'resa'); ?> {{ timeslot.dateEnd|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                    </div>
                    <div ng-class="{'barrer':timeslot.getCalculateCapacity(formCtrl.serviceParameters)<=0}" ng-if="timeslot.noEnd">
                      <?php _e('begin_word', 'resa'); ?> {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                    </div>
                    <div ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && (!timeslot.overCapacity || (timeslot.overCapacity && timeslot.display_remaining_position_overcapacity))">
                      <span ng-if="timeslot.numberExclusiveTimeslot() > 1">{{ timeslot.numberExclusiveTimeslot() }} x </span>
            					{{ timeslot.getCalculateCapacity(formCtrl.serviceParameters) }} <?php _e('positions_word', 'resa'); ?>
                    </div>
                    <div ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && timeslot.overCapacity && !timeslot.display_remaining_position_overcapacity">
                      <?php _e('remaining_positions_word', 'resa'); ?>
                    </div>
                    <div ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0">
                      <?php _e('Full_word', 'resa'); ?>
                    </div>
                  </div>
                  <div ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextBase,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && !timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)"
                    ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextBase,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                  </div>
                  <div ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextSelected,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)"
                    ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextSelected,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                  </div>
                  <div ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextCompleted,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0"
                    ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextCompleted,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                  </div>
                </div>
              </div>
              <div class="resa-timeslots-afternoon resa-timeslots-column" ng-show="!formCtrl.currentGetTimeslotsLaunched">
                <h4><?php _e('Afternoon_word', 'resa'); ?></h4>
                <div class="resa-timeslot" ng-click="(timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0) || formCtrl.setTimeslot(timeslot); formCtrl.serviceParameters.addNumberPriceForEachServicePrice(); formCtrl.preAddInBasket();"  ng-if="!timeslot.isInMorning()" ng-repeat="timeslot in formCtrl.currentTimeslots" ng-class="{'selected':timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)}">
                  <div ng-if="timeslot.idMention != '' && formCtrl.getMentionById(timeslot.idMention)" class="promo-label" style="background-color:{{ formCtrl.getMentionById(timeslot.idMention).backgroundColor }}">
                    {{ formCtrl.getTextByLocale(formCtrl.getMentionById(timeslot.idMention).name,'<?php echo get_locale(); ?>') }}
                  </div>
                  <div ng-if="!formCtrl.serviceParameters.service.customTimeslotsTextActivated">
                    <div ng-class="{'barrer':timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0}" ng-if="!timeslot.noEnd">
                      {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }} <?php _e('to_word', 'resa'); ?> {{ timeslot.dateEnd|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                    </div>
                    <div ng-class="{'barrer':timeslot.getCalculateCapacity(formCtrl.serviceParameters)<=0}" ng-if="timeslot.noEnd">
                      <?php _e('begin_word', 'resa'); ?> {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                    </div>
                    <div ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && (!timeslot.overCapacity || (timeslot.overCapacity && timeslot.display_remaining_position_overcapacity))">
                      <span ng-if="timeslot.numberExclusiveTimeslot() > 1">{{ timeslot.numberExclusiveTimeslot() }} x </span>
            					{{ timeslot.getCalculateCapacity(formCtrl.serviceParameters) }} <?php _e('positions_word', 'resa'); ?>
                    </div>
                    <div ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && timeslot.overCapacity && !timeslot.display_remaining_position_overcapacity">
                      <?php _e('remaining_positions_word', 'resa'); ?>
                    </div>
                    <div ng-if="timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0">
                      <?php _e('Full_word', 'resa'); ?>
                    </div>
                  </div>
                  <div ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextBase,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && !timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)"
                    ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextBase,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                  </div>
                  <div ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextSelected,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) > 0 && timeslot.isSameDates(formCtrl.serviceParameters.dateStart, formCtrl.serviceParameters.dateEnd)"
                    ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextSelected,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                  </div>
                  <div ng-if="formCtrl.serviceParameters.service.customTimeslotsTextActivated && formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextCompleted,'<?php echo get_locale(); ?>') && timeslot.getCalculateCapacity(formCtrl.serviceParameters) <= 0"
                    ng-bind-html="formCtrl.formatTimeslotText(timeslot, formCtrl.getTextByLocale(formCtrl.serviceParameters.service.customTimeslotsTextCompleted,'<?php echo get_locale(); ?>'), '<?php echo $variables['time_format']; ?>')">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="resa-prices" ng-if="formCtrl.getRealTotalMax() >= 0">
            <h3 class="resa-recap-title resa-talign-center text-important">
              {{ formCtrl.getTextByLocale(formCtrl.serviceParameters.service.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }},
              {{ formCtrl.serviceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}
              <span ng-if="!formCtrl.serviceParameters.isNoEnd()">
                {{ formCtrl.serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ formCtrl.serviceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}
              </span>
              <span ng-if="formCtrl.serviceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ formCtrl.serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</span>
            </h3>
            <h3 class="resa-prices-title resa-talign-center" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_choose_prices, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></h3>
            <div class="resa-prices-content">
              <div class="resa-price" ng-if="numberPrice.price.activated && formCtrl.displayPriceListWithTypeAccount(numberPrice.price) && !numberPrice.price.extra && formCtrl.serviceParameters.service.defaultSlugServicePrice!=numberPrice.price.slug && (formCtrl.serviceParameters.idsServicePrices.length == 0 || formCtrl.serviceParameters.idsServicePrices.indexOf(numberPrice.price.id) != -1)" ng-repeat="numberPrice in formCtrl.serviceParameters.numberPrices">
                <h3 class="resa-price-title resa-talign-center">{{ formCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h3>
				<div class="price_mention">{{ formCtrl.getTextByLocale(numberPrice.price.mention,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                <p class="resa-price-description resa-talign-center" ng-bind-html="formCtrl.getTextByLocale(numberPrice.price.presentation,'<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
                <p class="resa-price-value resa-talign-center" ng-if="numberPrice.price.notThresholded">
                  <b class="text-important">{{ formCtrl.round(numberPrice.price.price  * formCtrl.serviceParameters.getNumberDays()) }}{{ formCtrl.settings.currency }}</b>
                  <span class="extra_unit b0 italic" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_prices_suffix_by_persons,'<?php echo get_locale(); ?>')|htmlSpecialDecode"></span>
                </p>
                <p class="resa-price-value resa-talign-center" ng-if="!numberPrice.price.notThresholded">
                  <span ng-if="formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) == 0">
                    <?php _e('from_price_words', 'resa'); ?>
                    <b class="text-important">{{ formCtrl.serviceParameters.getMinThresholdPrice(numberPrice) }}{{ formCtrl.settings.currency }}</b>
                  </span>
                  <span ng-if="formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) > 0">
                    <b class="text-important">{{ formCtrl.round(formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) * formCtrl.serviceParameters.getNumberDays()) }} {{ formCtrl.settings.currency }}</b>
                    <?php _e('in_total_words', 'resa') ?>
                  </span>
                </p>
                <div class="resa-price-quatity-title resa-talign-center" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_choose_quantity, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></div>
                <div class="resa-price-quantity resa-talign-center">
                  <div class="quantity-less" ng-click="formCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, -1); numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceZero(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPrice(formCtrl.serviceParameters, numberPrice, $index)); formCtrl.preAddInBasket();">-</div>
                  <input class="quantity-value" ng-change="numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceZero(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPrice(formCtrl.serviceParameters, numberPrice, $index)); formCtrl.preAddInBasket()" ng-model="numberPrice.number" ng-model-options="{ debounce: 500 }" placeholder="0" />
                  <div class="quantity-more" ng-click="formCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, 1); numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceZero(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPrice(formCtrl.serviceParameters, numberPrice, $index));formCtrl.preAddInBasket();">+</div>
                </div>
                <div class="resa-price-quatity-title resa-talign-center" ng-if="!formCtrl.serviceParameters.haveAllMandatoryPrices() && !numberPrice.price.mandatory">
                  <?php _e('select_mandatory_price_words', 'resa') ?>
                </div>
                <div class="resa-price-quantity-equipment resa-talign-center" ng-if="formCtrl.serviceParameters.equipmentsActivated && numberPrice.price.equipments.length > 0 && formCtrl.displayNumberToChoose(formCtrl.serviceParameters, numberPrice, $index)" ng-bind-html="formCtrl.formatRemainingEquipments('<?php echo get_locale(); ?>', formCtrl.serviceParameters.getMaxEquipmentsCanChoose($index) - numberPrice.number)"></div>
              </div>
            </div>
          </div>
          <div class="resa-prices resa-extras" ng-if="formCtrl.getRealTotalMax() >= 0 && formCtrl.haveExtras(formCtrl.serviceParameters.service)">
            <h3 class="resa-prices-title resa-talign-center">4 - <?php _e('addons_and_extras_words', 'resa'); ?></h3>
            <div class="resa-prices-content">
              <div class="resa-price" ng-if="numberPrice.price.activated  && formCtrl.displayPriceListWithTypeAccount(numberPrice.price) && numberPrice.price.extra && formCtrl.serviceParameters.service.defaultSlugServicePrice!=numberPrice.price.slug && (formCtrl.serviceParameters.idsServicePrices.length == 0 || formCtrl.serviceParameters.idsServicePrices.indexOf(numberPrice.price.id) != -1)" ng-repeat="numberPrice in formCtrl.serviceParameters.numberPrices">
                <h3 class="resa-price-title resa-talign-center">{{ formCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h3>
				<div class="price_mention">{{ formCtrl.getTextByLocale(numberPrice.price.mention,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                <p class="resa-price-description resa-talign-center" ng-bind-html="formCtrl.getTextByLocale(numberPrice.price.presentation,'<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
                <p class="resa-price-value resa-talign-center" ng-if="numberPrice.price.notThresholded">
                  <b class="text-important">{{ numberPrice.price.price }}{{ formCtrl.settings.currency }}</b>
                  <span class="extra_unit b0 italic" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_prices_suffix_by_persons,'<?php echo get_locale(); ?>')|htmlSpecialDecode"></span>
                </p>
                <p class="resa-price-value resa-talign-center" ng-if="!numberPrice.price.notThresholded">
                  <span ng-if="formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) == 0">
                    <?php _e('from_price_words', 'resa'); ?>
                    <b class="text-important">{{ formCtrl.serviceParameters.getMinThresholdPrice(numberPrice) }}{{ formCtrl.settings.currency }}</b>
                  </span>
                  <span ng-if="formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) > 0">
                    <b class="text-important">{{ formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) }} {{ formCtrl.settings.currency }}</b>
                    <?php _e('in_total_words', 'resa') ?>
                  </span>
                </p>
                <div class="resa-price-quatity-title resa-talign-center"><?php _e('Quantity_word', 'resa') ?></div>
                <div class="resa-price-quantity resa-talign-center">
                  <div class="quantity-less" ng-click="formCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, -1); numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceZero(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPrice(formCtrl.serviceParameters, numberPrice, $index)); formCtrl.preAddInBasket();">-</div>
                  <input class="quantity-value" ng-change="numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceZero(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPrice(formCtrl.serviceParameters, numberPrice, $index)); formCtrl.preAddInBasket()" ng-model="numberPrice.number" ng-model-options="{ debounce: 500 }" placeholder="0" />
                  <div class="quantity-more" ng-click="formCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, 1); numberPrice.number = formCtrl.minMaxValue(numberPrice.number, formCtrl.getMinNumberPriceZero(formCtrl.serviceParameters, numberPrice), formCtrl.getMaxNumberPrice(formCtrl.serviceParameters, numberPrice, $index));formCtrl.preAddInBasket();">+</div>
                </div>
                <div class="resa-price-quatity-title resa-talign-center" ng-if="!formCtrl.serviceParameters.haveAllMandatoryPrices() && !numberPrice.price.mandatory">
                  <?php _e('select_mandatory_price_words', 'resa') ?>
                </div>
                <div class="resa-price-quantity-equipment resa-talign-center" ng-if="formCtrl.serviceParameters.equipmentsActivated && numberPrice.price.equipments.length > 0 && formCtrl.displayNumberToChoose(formCtrl.serviceParameters, numberPrice, $index)" ng-bind-html="formCtrl.formatRemainingEquipments('<?php echo get_locale(); ?>', formCtrl.serviceParameters.getMaxEquipmentsCanChoose($index) - numberPrice.number)"></div>
              </div>
            </div>
          </div>
          <div class="next-step" ng-if="formCtrl.getRealTotalMax() >= 0">
            <div class="resa-btn btn-small" ng-click="formCtrl.chooseNewDate(); formCtrl.scrollTo('resa-form-navigation')">{{ formCtrl.getTextByLocale(formCtrl.settings.form_add_new_date_text,'<?php echo get_locale(); ?>') |htmlSpecialDecode}}</div>
            <div class="resa-btn btn-small" ng-click="formCtrl.chooseNewActivity(); formCtrl.scrollTo('resa-form-navigation')">{{ formCtrl.getTextByLocale(formCtrl.settings.form_add_new_activity_text,'<?php echo get_locale(); ?>') |htmlSpecialDecode}}</div>
            <div class="resa-btn btn-small" ng-show="formCtrl.haveNext()" ng-click="formCtrl.incrementStep();"><?php _e('next_step_link_title', 'resa'); ?></div>
          </div>
        </div>
        <div id="resa-step-account" ng-show="formCtrl.step == 4">
          <div class="resa-customer-text" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.informations_customer_text, '<?php echo get_locale(); ?>')"></div>
  		  <div class="resa-step-account-registrations">
  			  <div class="resa-already_client" ng-if="formCtrl.customer.ID != -1">
  				<p class="resa-talign-center"><?php _e('connected_with_sentence', 'resa'); ?> <br /><span class="client_name">{{ formCtrl.customer.firstName }} {{ formCtrl.customer.lastName }}</span></p>
  				<p class="resa-talign-center"><?php _e('is_not_you_sentence', 'resa'); ?>&nbsp;<a ng-click="formCtrl.userDeconnection()"><?php _e('to_logout_link_title', 'resa'); ?></a></p>
  			  </div>
  			  <div class="resa-already_client" ng-if="formCtrl.customer.ID == -1 && !formCtrl.isFacebookConnected()">
    				<div class="resa-facebook-login" ng-if="formCtrl.settings.facebook_activated"  ng-click="formCtrl.login()">
              <img class="resa-facebook-login-image" src="<?php echo plugins_url('..', __FILE__ ); ?>/images/facebook-login.png" /></a>
    				  <p class="resa-talign-center"  ng-click="formCtrl.login()" >Se connecter avec facebook</p>
    				</div>
    				<p class="resa-talign-center"><?php _e('already_register_sentence', 'resa'); ?></p>
    				<form class="resa-talign-center">
    				  <input ng-model="formCtrl.customer.login" type="text" placeholder="<?php _e('email_field_title', 'resa') ?>" />
    				  <input ng-init="formCtrl.customer.passwordConnection=''" ng-model="formCtrl.customer.passwordConnection"  type="password" placeholder="<?php _e('password_field_title', 'resa') ?>" />
              <div class="resa-btn btn-small" style="width:auto;" ng-class="{'resa-btn-disabled':formCtrl.customer.passwordConnection.length == 0 || formCtrl.customer.login.length == 0}" ng-click="formCtrl.userConnection()"><?php _e('to_login_link_title', 'resa'); ?></div>
    				</form>
  			  </div>
  			  <div class="resa_create_account" ng-if="formCtrl.customer.ID == -1">
  				<p><?php _e('create_account_sentence', 'resa'); ?></p>
  				<div ng-if="formCtrl.isFacebookConnected()">{{ formCtrl.customer.lastName }}</div>
  				<input ng-if="formCtrl.fieldStates['lastName'] > 0 && (!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.dataFacebook.last_name == null && formCtrl.fieldStates['lastName'] == 1))" class="input_wide" type="text" ng-model="formCtrl.customer.lastName" placeholder="{{ formCtrl.getPlaceolder('<?php _e('last_name_field_title', 'resa') ?>','lastName') }}" />
  				<span ng-class="{'input_error':formCtrl.fieldStates['lastName'] == 1, 'input_info':formCtrl.fieldStates['lastName'] == 2}" ng-show="formCtrl.customer.lastName.length == 0 && formCtrl.fieldStates['lastName'] > 0"><span ng-if="formCtrl.fieldStates['lastName'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['lastName'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
  				<div ng-if="formCtrl.isFacebookConnected()">{{ formCtrl.customer.firstName }}</div>
  				<input ng-if="formCtrl.fieldStates['firstName'] > 0 && (!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.dataFacebook.first_name == null && formCtrl.fieldStates['firstName'] == 1))" class="input_wide" type="text" ng-model="formCtrl.customer.firstName" placeholder="{{ formCtrl.getPlaceolder('<?php _e('first_name_field_title', 'resa') ?>','firstName') }}" />
  				<span ng-class="{'input_error':formCtrl.fieldStates['firstName'] == 1, 'input_info':formCtrl.fieldStates['firstName'] == 2}" ng-show="formCtrl.customer.firstName.length == 0 && formCtrl.fieldStates['firstName'] > 0"><span ng-if="formCtrl.fieldStates['firstName'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['firstName'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
  				<div ng-if="formCtrl.isFacebookConnected()">{{ formCtrl.customer.email }}</div>
  				<input ng-if="(!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.customer.email.length == 0))" class="input_wide" type="email" ng-model="formCtrl.customer.email" placeholder="<?php _e('email_field_title', 'resa') ?>*" />
  				<span class="input_error"  ng-show="formCtrl.customer.email==null || formCtrl.customer.email.length == 0"><?php _e('not_an_email_address_sentence', 'resa') ?></span>

  				<input ng-if="!formCtrl.isFacebookConnected()" class="input_wide" type="password" ng-model="formCtrl.customer.password" placeholder="<?php _e('password_field_title', 'resa') ?>*" ng-init="formCtrl.customer.password = ''">
  				<span class="input_error" ng-show="formCtrl.customer.password.length == 0"><?php _e('required_word', 'resa') ?></span>
  				<span style="color:orange; font-size:14px; font-style:italic; line-height: normal important!;" ng-show="!formCtrl.checkPassword(formCtrl.customer.password)"><?php _e('bad_password_words', 'resa'); ?></span>

  				<input class="input_wide" type="password" ng-model="formCtrl.customer.confirmPassword" placeholder="<?php _e('confirm_password_field_title', 'resa') ?>*"  ng-init="formCtrl.customer.confirmPassword = ''" />
  				<span class="input_error" ng-show="formCtrl.customer.confirmPassword.length == 0"><?php _e('required_word', 'resa') ?></span>
  				<span class="input_error" ng-show="formCtrl.customer.confirmPassword.length > 0 && formCtrl.customer.confirmPassword != formCtrl.customer.password"><?php _e('not_same_password_words', 'resa') ?></span>

  				<input ng-if="formCtrl.fieldStates['company'] > 0 && (!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['company'] == 1))" class="input_wide" type="text" ng-model="formCtrl.customer.company" placeholder="{{ formCtrl.getPlaceolder('<?php _e('company_field_title', 'resa') ?>','company') }}" />
  				<span ng-class="{'input_error':formCtrl.fieldStates['company'] == 1, 'input_info':formCtrl.fieldStates['company'] == 2}" ng-show="formCtrl.customer.company.length == 0 && formCtrl.fieldStates['company'] > 0"><span ng-if="formCtrl.fieldStates['company'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['company'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

  				<input ng-if="formCtrl.fieldStates['phone'] > 0 && (!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['phone'] == 1))" class="input_wide" type="text" ng-model="formCtrl.customer.phone" placeholder="{{ formCtrl.getPlaceolder('<?php _e('phone_field_title', 'resa') ?>','phone') }}" />
  				<span ng-class="{'input_error':formCtrl.fieldStates['phone'] == 1, 'input_info':formCtrl.fieldStates['phone'] == 2}" ng-show="formCtrl.customer.phone.length == 0 && formCtrl.fieldStates['phone'] > 0"><span ng-if="formCtrl.fieldStates['phone'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['phone'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

  				<input ng-if="formCtrl.fieldStates['phone2'] > 0 && (!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['phone2'] == 1))" class="input_wide" type="text" ng-model="formCtrl.customer.phone2" placeholder="{{ formCtrl.getPlaceolder('<?php _e('phone2_field_title', 'resa') ?>','phone2') }}" />
  				<span ng-class="{'input_error':formCtrl.fieldStates['phone2'] == 1, 'input_info':formCtrl.fieldStates['phone2'] == 2}" ng-show="formCtrl.customer.phone2.length == 0 && formCtrl.fieldStates['phone2'] > 0"><span ng-if="formCtrl.fieldStates['phone2'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['phone2'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

  				<input ng-if="formCtrl.fieldStates['address'] > 0 && (!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['address'] == 1))" class="input_wide" type="text" ng-model="formCtrl.customer.address" placeholder="{{ formCtrl.getPlaceolder('<?php _e('address_field_title', 'resa') ?>','address') }}" />
  				<span ng-class="{'input_error':formCtrl.fieldStates['address'] == 1, 'input_info':formCtrl.fieldStates['address'] == 2}" ng-show="formCtrl.customer.address.length == 0 && formCtrl.fieldStates['address'] > 0"><span ng-if="formCtrl.fieldStates['address'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['address'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

  				<input ng-if="formCtrl.fieldStates['postalCode'] > 0 && (!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['postalCode'] == 1))" class="input_wide" type="text" ng-model="formCtrl.customer.postalCode" placeholder="{{ formCtrl.getPlaceolder('<?php _e('postal_code_field_title', 'resa') ?>','postalCode') }}" />
  				<span ng-class="{'input_error':formCtrl.fieldStates['postalCode'] == 1, 'input_info':formCtrl.fieldStates['postalCode'] == 2}" ng-show="formCtrl.customer.postalCode.length == 0 && formCtrl.fieldStates['postalCode'] > 0"><span ng-if="formCtrl.fieldStates['postalCode'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['postalCode'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

  				<input ng-if="formCtrl.fieldStates['town'] > 0 && (!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['town'] == 1))" class="input_wide" type="text" ng-model="formCtrl.customer.town" placeholder="{{ formCtrl.getPlaceolder('<?php _e('town_field_title', 'resa') ?>','town') }}" />
  				<span ng-class="{'input_error':formCtrl.fieldStates['town'] == 1, 'input_info':formCtrl.fieldStates['town'] == 2}" ng-show="formCtrl.customer.town.length == 0 && formCtrl.fieldStates['town'] > 0"><span ng-if="formCtrl.fieldStates['town'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['town'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

  				<select  class="input_wide" ng-if="formCtrl.fieldStates['country'] > 0 && (!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['country'] == 1))" ng-model="formCtrl.customer.country">
  				  <option value="" disabled selected>-- {{ formCtrl.getPlaceolder('<?php _e('country_field_title', 'resa') ?>','country') }} --</option>
  				  <option ng-repeat="country in formCtrl.countries" value="{{ country.code }}">{{ country.name }}</option>
  				</select>
  				<span ng-class="{'input_error':formCtrl.fieldStates['country'] == 1, 'input_info':formCtrl.fieldStates['country'] == 2}" ng-show="formCtrl.customer.country.length == 0 && formCtrl.fieldStates['country'] > 0"><span ng-if="formCtrl.fieldStates['country'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['country'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

          <label for="acceptNewsletters" ng-if="formCtrl.fieldStates['newsletters'] > 0" class="resa-gdpr" style="align-items: center; justify-content: center;">
            <input id="acceptNewsletters" type="checkbox" ng-model="formCtrl.customer.registerNewsletters" />
            <p><?php _e('accept_register_newsletters', 'resa'); ?></p>
          </label>

  				<label for="acceptGDPR" class="resa-gdpr" style="align-items: center; justify-content: center;">
  				  <input id="acceptGDPR" type="checkbox" ng-model="formCtrl.checkboxCustomer" />
  				  <p> <?php _e('gdpr_accept_to_save_data', 'resa'); ?> </p>
  				</label>

  				<input ng-if="formCtrl.fieldStates['siret'] > 0 && (!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['siret'] == 1))" class="input_wide" type="text" ng-model="formCtrl.customer.siret" placeholder="{{ formCtrl.getPlaceolder('<?php _e('siret_field_title', 'resa') ?>','siret') }}" />
  				<span ng-class="{'input_error':formCtrl.fieldStates['siret'] == 1, 'input_info':formCtrl.fieldStates['siret'] == 2}" ng-show="formCtrl.customer.siret.length == 0 && formCtrl.fieldStates['siret'] > 0"><span ng-if="formCtrl.fieldStates['siret'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['siret'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

  				<input ng-if="formCtrl.fieldStates['legalForm'] > 0 && (!formCtrl.isFacebookConnected() || (formCtrl.isFacebookConnected() && formCtrl.fieldStates['legalForm'] == 1))" class="input_wide" type="text" ng-model="formCtrl.customer.legalForm" placeholder="{{ formCtrl.getPlaceolder('<?php _e('legal_form_field_title', 'resa') ?>','legalForm') }}" />
  				<span ng-class="{'input_error':formCtrl.fieldStates['legalForm'] == 1, 'input_info':formCtrl.fieldStates['legalForm'] == 2}" ng-show="formCtrl.customer.legalForm.length == 0 && formCtrl.fieldStates['legalForm'] > 0"><span ng-if="formCtrl.fieldStates['legalForm'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="formCtrl.fieldStates['legalForm'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>

          <div class="resa-btn btn-small" style="width:100%;" ng-class="{'resa-btn-disabled':  !formCtrl.customerIsOkForForm()}" ng-click="(!formCtrl.customerIsOkForForm()) || formCtrl.incrementStep()"><?php _e('ok_link_title', 'resa'); ?></div>
  			  </div>
  			</div>
        </div>
        <div id="resa-step-participants" ng-show="formCtrl.step == 5">
          <h3 class="resa-talign-center"><?php _e('Complementary_informations_words', 'resa'); ?></h3>
          <p class="resa-talign-center" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.informations_participants_text, '<?php echo get_locale(); ?>')"></p>
          <div id="resa-participants-booking">
            <div class="resa-lightbox" ng-controller="ParticipantSelectorController as participantSelectorCtrl" ng-show="participantSelectorCtrl.opened">
              <div class="resa-lightbox-content">
                <table class="resa-participants-list">
                  <thead>
                    <td ng-repeat="field in participantSelectorCtrl.participantParametersFields track by $index">
                      {{ formCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
                    </td>
                    <td><?php _e('Select_word', 'resa'); ?></td>
                  </thead>
                  <tbody>
                    <tr ng-click="participantSelectorCtrl.setParticipant(participant); participantSelectorCtrl.close();" class="participant_information" ng-repeat="participant in participantSelectorCtrl.participants track by $index">
                      <td ng-repeat="field in participantSelectorCtrl.participantParametersFields track by $index">
                        {{ participantSelectorCtrl.getTextByLocale(field.prefix, '<?php echo get_locale(); ?>')}}
                        {{ formCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}
                        {{participantSelectorCtrl.getTextByLocale(field.suffix, '<?php echo get_locale(); ?>')}}
                      </td>
                      <td><div class="resa-btn btn-small"><?php _e('Select_word', 'resa'); ?></div></td>
                    </tr>
                  </tbody>
                </table>
                <div class="resa-btn" ng-click="participantSelectorCtrl.close()"><?php _e('close_link_title', 'resa'); ?></div>
              </div>
            </div>
            <div class="resa-lightbox" ng-if="formCtrl.isServiceParametersAbsencesOpened()" >
              <div class="resa-lightbox-content">
                <h4><?php _e('specify_absences_words', 'resa'); ?></h4>
                <table class="resa-missing-list">
                  <thead>
                    <td ng-click="displayServiceParameters.switchTag('absent')" ng-repeat="displayServiceParameters in formCtrl.getAllServiceParametersLink(formCtrl.serviceParametersAbsences)">
                      {{ displayServiceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}
                    </td>
                  </thead>
                  <tbody>
                    <tr>
                      <td ng-click="displayServiceParameters.switchTag('absent')" ng-repeat="displayServiceParameters in formCtrl.getAllServiceParametersLink(formCtrl.serviceParametersAbsences)">
                        <input id="absent_serviceParameters_{{ displayServiceParameters.id }}" type="checkbox" ng-checked="!displayServiceParameters.haveTag('absent')"  type="checkbox" checked/>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <div class="resa-btn" ng-click="formCtrl.closeServiceParametersAbsences()"><?php _e('close_link_title', 'resa'); ?></div>
              </div>
            </div>
            <span ng-repeat="serviceElement in formCtrl.getBasket()">
              <div  class="service_section" ng-if="!serviceParameters.isLink() && serviceParameters.service.askParticipants" ng-repeat="serviceParameters in serviceElement.servicesParameters">
                <h4 class="participants-booking-service">{{ formCtrl.getTextByLocale(serviceElement.service.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
                <div class="participants-booking-price" ng-repeat="numberPrice in serviceParameters.numberPrices track by $index">
                  <p class="participants-booking-price-infos text-important" ng-if="serviceParameters.isCombined()">
                    <?php _e('begin_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['date_format']; ?>' }}
                    <span ng-if="!serviceParameters.isNoEnd()">
                      <?php _e('from_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                      <?php _e('to_word','resa') ?> {{ serviceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                    </span>
                    <span class="creneau" ng-if="serviceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</span> -
                    {{ numberPrice.number }} <span ng-show="numberPrice.number==1 && !numberPrice.price.extra"><?php _e('person_word', 'resa'); ?></span><span ng-show="numberPrice.number>1 && !numberPrice.price.extra"><?php _e('persons_word', 'resa'); ?></span> -
                    {{ formCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                    <span ng-if="!serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                      <span ng-if="numberPrice.price.notThresholded">{{ formCtrl.round(numberPrice.price.price * serviceParameters.getNumberDays()) }}</span><span ng-if="!numberPrice.price.notThresholded">{{ formCtrl.round(formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) * serviceParameters.getNumberDays()) }}</span>{{ formCtrl.settings.currency }}</span>
                    </span>
                  </p>
                  <p class="participants-booking-price-infos text-important" ng-if="!serviceParameters.isCombined() && !serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                    {{ serviceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}
                   <span ng-if="!serviceParameters.isNoEnd()">
                     <?php _e('from_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                     <?php _e('to_word','resa') ?> {{ serviceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                   </span>
                    <span class="creneau" ng-if="serviceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</span> -
                    {{ numberPrice.number }} <span ng-show="numberPrice.number==1 && !numberPrice.price.extra"><?php _e('person_word', 'resa'); ?></span><span ng-show="numberPrice.number>1 && !numberPrice.price.extra"><?php _e('persons_word', 'resa'); ?></span> -
                    {{ formCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                    <span ng-if="!serviceParameters.isDefaultSlugServicePrice(numberPrice)">
                      <span ng-if="numberPrice.price.notThresholded">{{ formCtrl.round(numberPrice.price.price * serviceParameters.getNumberDays()) }}</span><span ng-if="!numberPrice.price.notThresholded">{{ formCtrl.round(formCtrl.serviceParameters.getPriceNumberPrice(numberPrice) * serviceParameters.getNumberDays()) }}</span>{{ formCtrl.settings.currency }}</span>
                    </span>
                  </p>
                  <table class="participants-content" ng-if="serviceParameters.service.askParticipants && numberPrice.price.participantsParameter != null && numberPrice.price.participantsParameter.length > 0">
                    <thead>
                      <td></td>
                      <td class="field_{{field.varname}}" ng-class="{'helpbox':formCtrl.getTextByLocale(field.presentation,'<?php echo get_locale(); ?>').length > 0}" ng-repeat="field in formCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields track by $index">
                        {{ formCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
                        <span class="helpbox_content"><p ng-bind-html="formCtrl.getTextByLocale(field.presentation,'<?php echo get_locale(); ?>')|displayNewLines"></p></span>
                      </td>
                    </thead>
                    <tbody>
                      <tr ng-repeat="number in serviceParameters.getRepeatNumberByNumber(numberPrice.number) track by $index">
                        <td>
                          <a ng-click="formCtrl.openParticipantSelectorDialog(formCtrl.getParticipants(serviceParameters), formCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields, serviceParameters, numberPrice, $index, false)"><img class="participant-icon" src="<?php echo plugins_url('..', __FILE__ ); ?>/images/participant-icon.png" /></a>
                        </td>
                        <td ng-repeat="field in formCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields track by $index">
                          <input class="participant-input" ng-if="field.type == 'text'" ng-change="formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));" class="client_{{ field.varname }} input_wide info_client_input" list="customersList{{ serviceParameters.id }}_{{ $parent.$parent.$index }}_{{ $index }}" type="text" ng-model="formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname]" placeholder="{{ formCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}" />
                          <input class="participant-input" ng-if="field.type == 'number'" ng-blur="formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname] = formCtrl.minMaxValue(formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname], field.min, field.max);formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));" ng-change="formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));" class="client_{{ field.varname }} input_wide info_client_input"  list="customersList{{ serviceParameters.id }}_{{ $parent.$parent.$index }}_{{ $index }}" type="number" ng-model="formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname]" placeholder="{{ formCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}"  />
                          <datalist ng-if="field.type == 'text' || field.type == 'number'" id="customersList{{ serviceParameters.id }}_{{ $parent.$parent.$index }}_{{ $index }}">
                            <option value="{{ participant[field.varname] }}" ng-repeat="participant in formCtrl.getParticipants(serviceParameters) track by $index">
                          </datalist>
                          <select class="participant-input" ng-if="field.type == 'select'" ng-change="formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));"  class="client_{{ field.varname }} input_wide info_client_input" style="color: black;"  ng-model="formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname]" id="formInput{{ serviceParameters.id }}_{{ $parent.$parent.$index }}_{{ $index }}" ng-init="formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname] = formCtrl.getFirstSelectOption(formCtrl.getServiceParametersLinkNumberPrice(serviceParameters, numberPrice).participants[$parent.$parent.$index][field.varname], field.options)">
                            <option ng-repeat="option in field.options" value="{{ option.id }}">{{ formCtrl.getTextByLocale(option.name,'<?php echo get_locale(); ?>') }}</option>
                          </select>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="resa-missing-btn-content" ng-if="serviceParameters.isCombined()">
                    <div class="resa-btn btn-small" ng-click="formCtrl.setServiceParameters(serviceParameters)">
                      <?php _e('specify_absences_words', 'resa'); ?>
                    </div>
                  </div>
                </div>
              </div>
            </span>
            <div class="next-step">
              <div class="resa-btn btn-small" ng-click="formCtrl.chooseNewActivity(); formCtrl.scrollTo('resa-form-navigation')">{{ formCtrl.getTextByLocale(formCtrl.settings.form_add_new_activity_text,'<?php echo get_locale(); ?>') |htmlSpecialDecode}}</div>
              <div class="resa-btn btn-small" ng-show="formCtrl.haveNext()" ng-click="formCtrl.incrementStep()">
                <?php _e('next_step_link_title', 'resa'); ?>
              </div>
              <div class="resa-talign-center resa-text-alert" ng-show="formCtrl.step == 5 && !formCtrl.participantsIsSelected()">
                <?php _e('empty_field_participant_form_error', 'resa'); ?>
              </div>
            </div>
          </div>
        </div>
        <div id="resa-step-validation" ng-show="formCtrl.step == 6">
          <div id="cart">
            <div class="cart-content">
              <h3 class="resa-talign-center" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.form_recap_booking_title, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></h3>
              <div class="resa-cart-activities" ng-repeat="serviceElement in formCtrl.getBasket()">
                <div class="resa-cart-activity" ng-if="!serviceParameters.isLink()" ng-repeat="serviceParameters in serviceElement.servicesParameters">
                  <h4 class="resa-cart-activity-title">{{ formCtrl.getTextByLocale(serviceElement.service.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
                  <div class="resa-cart-activity-content">
                    <div class="resa-cart-activity-date-content">
                      <div class="resa-cart-activity-datetime" ng-if="!serviceParameters.isCombined()">
                        <div class="resa-cart-activity-date text-important" >
                          {{ serviceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}
                        </div>
                        <div class="resa-cart-activity-time text-important" ng-if="!serviceParameters.isNoEnd()">
                          {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ serviceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                        </div>
                        <div class="resa-cart-activity-time text-important" ng-if="serviceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</div>
                      </div>
                      <div class="resa-cart-activity-datetime" ng-if="serviceParameters.isCombined()">
                        <div class="resa-cart-activity-date text-important">
                          <div class="text-important" ng-repeat="displayServiceParameters in formCtrl.getAllServiceParametersLink(serviceParameters)"
                          ng-class="{'resa-tdecoration-lthrough':displayServiceParameters.haveTag('absent')}">
                            {{ displayServiceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}
                          </div>
                        </div>
                        <div class="resa-cart-activity-time text-important" ng-if="!serviceParameters.isNoEnd()">
                          {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ serviceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                        </div>
                        <div class="resa-cart-activity-time text-important" ng-if="serviceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</div>
                      </div>
                      <div class="resa-cart-activity-prices">
                        <div class="resa-cart-activity-price" ng-repeat="numberPrice in serviceParameters.numberPrices track by $index">
                          <div class="resa-cart-prices-content">
                            <div class="resa-cart-activity-price-name">{{ formCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                            <div class="resa-cart-activity-price-number">
                              {{ numberPrice.number }} <span ng-show="numberPrice.number==1 && !numberPrice.price.extra"><?php _e('person_word', 'resa'); ?></span><span ng-show="numberPrice.number>1 && !numberPrice.price.extra"><?php _e('persons_word', 'resa'); ?></span>
                            </div>
                          </div>
                          <div class="resa-cart-prices-amount text-important" ng-if="serviceParameters.canCalculatePrice(numberPrice)">
                            {{ formCtrl.round(serviceParameters.getPriceNumberPrice(numberPrice) * serviceParameters.getNumberDays()) }}{{ formCtrl.settings.currency }}
                          </div>
                          <div ng-if="!serviceParameters.isDefaultSlugServicePrice(numberPrice)" class="resa-cart-activity-price-actions">
                            <div class="resa-cart-activity-price-action-delete">
                              <a class="action-delete" ng-click="formCtrl.removeBasket(formCtrl.getServiceParametersLink(serviceParameters), $index); formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));">X</a>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="resa-cart-activity-prices" ng-if="formCtrl.mapIdClientObjectReduction['id'+serviceParameters.id].length > 0">
                        <hr />
                        <div class="resa-cart-activity-price" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id'+serviceParameters.id] track by $index">
                          <div class="resa-cart-prices-content">
                            <div class="resa-cart-activity-price-name">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }}</div>
                            <div class="resa-cart-activity-price-number">
                              <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
                              <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                              <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                              <span ng-if="params.type == 3">{{ params.value }}{{ formCtrl.settings.currency }} <span class="tarif_barre"> {{ numberPrice.price.price }}{{ formCtrl.settings.currency }}</span></span>
                              <span ng-if="params.type == 4">{{ params.value * params.number }} <span ng-if="(params.value * params.number) == 1"><?php _e('offer_quantity_words', 'resa') ?></span><span ng-if="(params.value * params.number) > 1"><?php _e('offer_quantities_words', 'resa') ?></span></span>
                              <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                            </div>
                          </div>
                          <div class="resa-cart-prices-amount text-important" ng-if="serviceParameters.canCalculatePrice(numberPrice)">
                            <span ng-if="params.type == 0">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                            <span ng-if="params.type == 1">{{ ((serviceParameters.getPriceNumberPrice(numberPrice) * params.number) * params.value  / 100)|negative }}{{ formCtrl.settings.currency }} </span>
                            <span ng-if="params.type == 2">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                            <span ng-if="params.type == 3">{{ ((numberPrice.price.price - params.value) * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                            <span ng-if="params.type == 4">{{ (serviceParameters.getPriceNumberPrice(numberPrice)/numberPrice.number * params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                            <span ng-if="params.type == 5"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="resa-cart-activity-infos" ng-if="serviceParameters.idParameter > -1">{{ formCtrl.getTextByLocale(formCtrl.getParameter(serviceParameters.idParameter).form,'<?php echo get_locale(); ?>') }}</div>
                  <div class="resa-cart-activity-error" ng-if="formCtrl.idServicesParametersError.id == serviceParameters.id">{{ formCtrl.idServicesParametersError.text }}, <?php _e('error_service_parameters_deprecated_form', 'resa') ?> - <a class="delete_price_btn" ng-click="formCtrl.removeBasket(formCtrl.getServiceParametersLink(serviceParameters), $index); formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));"><?php _e('remove_link_title', 'resa'); ?></a></div>
                </div>
              </div>
              <div class="resa-cart-reductions">
                <div class="active-reductions">
                  <div class="resa-cart-reduction" ng-if="params.promoCode.length == 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id0'] track by $index">
                    <div class="reduction-content">
                      <div class="reduction-title">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                      <div class="reduction-description" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                      <div class="reduction-value resa-fstyle-italic">
                        <span ng-if="params.type == 0">{{ params.value |negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                        <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                        <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                      </div>
                    </div>
                    <div class="reduction-price text-important resa-fweight-bold">
                      <span ng-if="params.type == 0">{{ params.value|negative|number:2 }}{{ formCtrl.settings.currency }}</span>
                      <span ng-if="params.type == 1">{{ (formCtrl.getSubTotalPrice(formCtrl.getAllServicesParameters()) * params.value / 100)|negative|number:2 }}{{ formCtrl.settings.currency }} </span>
                      <span ng-if="params.type == 2">{{ (params.value * formCtrl.getTotalNumber())|negative|number:2 }}{{ formCtrl.settings.currency }}</span>
                      <span ng-if="params.type == 5"></span>
                    </div>
                  </div>
                  <div class="resa-cart-reduction" ng-if="params.promoCode.length > 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id0'] track by $index">
                    <div class="reduction-content">
                      <div class="reduction-title">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                      <div class="reduction-description" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                      <div class="reduction-value resa-fstyle-italic">
                        <span ng-if="params.type == 0">{{ params.value |negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                        <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                        <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                      </div>
                    </div>
                    <div class="reduction-price text-important resa-fweight-bold">
                      <span ng-if="params.type == 0">{{ params.value|negative|number:2 }}{{ formCtrl.settings.currency }}</span>
                      <span ng-if="params.type == 1">{{ (formCtrl.getSubTotalPrice(formCtrl.getAllServicesParameters()) * params.value / 100)|negative|number:2 }}{{ formCtrl.settings.currency }} </span>
                      <span ng-if="params.type == 2">{{ (params.value * formCtrl.getTotalNumber())|negative|number:2 }}{{ formCtrl.settings.currency }}</span>
                      <span ng-if="params.type == 5"></span>
                    </div>
                    <a class="action-delete" ng-if="params.promoCode.length > 0" ng-click="formCtrl.deleteCoupon(params.promoCode)">X</a>
                  </div>
                </div>
                <div class="add-coupon">
                  <input ng-disabled="formCtrl.getReductionsLaunched" type="text" ng-model="formCtrl.currentPromoCode" placeholder="<?php _e('Coupon_word', 'resa'); ?>" />
                  <div class="resa-btn btn-small" ng-class="{'resa-btn-disabled': formCtrl.getReductionsLaunched}" ng-click="formCtrl.getReductions(formCtrl.currentPromoCode)"><?php _e('add_coupon_link_title', 'resa') ?></div>
                  <div ng-if="formCtrl.getReductionsLaunched" class="resa-fstyle-italic"><?php _e('process_reductions_feedback', 'resa'); ?>...</div>
                </div>
              </div>
              <div class="resa-cart-total">
                <div class="resa-cart-total-title text-important"><?php _e('Total_word', 'resa') ?></div>
                <div class="resa-cart-total-value text-important">{{ formCtrl.getTotalPrice()|number:2 }}{{ formCtrl.settings.currency }}</div>
              </div>
              <div class="cart-note">
                <h4 class="resa-talign-center"><?php _e('Customer_note_word', 'resa'); ?></h4>
                <textarea class="cart-textarea" ng-model="formCtrl.bookingCustomerNote" placeholder="{{ formCtrl.getTextByLocale(formCtrl.settings.customer_note_text, '<?php echo get_locale(); ?>') }}"></textarea>
              </div>
              <div class="cart-paiement">
                <h4 class="cart-paiement-title resa-talign-center"><?php _e('Payment_word', 'resa') ?></h4>
                <p ng-if="formCtrl.askPayment()" class="paiement_text" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.informations_payment_text, '<?php echo get_locale(); ?>')"></p>
                <div ng-if="!formCtrl.settings.payment_activate && formCtrl.getTextByLocale(formCtrl.settings.payment_not_activated_text, '<?php echo get_locale(); ?>').length > 0" class="paiement_text" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.payment_not_activated_text, '<?php echo get_locale(); ?>')"></div>
                <h5 class="cart-paiement-title" ng-if="formCtrl.askPayment()"><?php _e('Payment_methods_words', 'resa') ?></h5>
                <div class="cart-paiement-types" ng-if="formCtrl.askPayment()">
                  <div class="cart-payment-{{ payment.id }}" ng-if="payment.activated && formCtrl.getTypeAccountOfCustomer().paymentsTypeList[payment.id]" ng-repeat="payment in formCtrl.paymentsTypeList">
                    <input id="{{ payment.id }}" ng-model="formCtrl.typePayment" type="radio" ng-value="payment" />
                    <label for="{{ payment.id }}">
                      <span class="{{ payment.class }}"></span>
                      <span ng-if="payment.title_public==null || payment.title_public == ''">{{ payment.title }}</span><span ng-if="payment.title_public!=null || payment.title_public != ''">{{ formCtrl.getTextByLocale(payment.title_public, '<?php echo get_locale(); ?>') }}
                      </span>
                    </label>
                    <p class="paiement-instructions" ng-if="payment.text != '' && formCtrl.typePayment.id == payment.id" ng-bind-html="formCtrl.getTextByLocale(payment.text, '<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></p>
                    </span>
                  </div>
                  <div class="cart-paiement-account" ng-if="formCtrl.askPayment() && formCtrl.askAdvancePayment() && formCtrl.canPayAdvancePayment() && formCtrl.typePayment.advancePayment">
                    <h5 class="cart-paiement-title"><?php _e('advance_payment_amount_words', 'resa') ?> <b class="text-important">{{ formCtrl.getTotalPriceAdvancePayment()|number:2 }}{{ formCtrl.settings.currency }}</b></h5>
                    <input id="account" type="checkbox" name="account" checked="checked" ng-model="formCtrl.checkboxAdvancePayment" />
                    <label for="account" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.payment_ask_text_advance_payment, '<?php echo get_locale(); ?>')|displayNewLines"></label>
                  </div>
                </div>
                <div class="cart-validation">
                  <h4 class="cart-paiement-title resa-talign-center" ng-if="!formCtrl.isQuotation()" ><?php _e('Validation_word', 'resa'); ?></h4>
                  <h4 class="cart-paiement-title resa-talign-center" ng-if="formCtrl.isQuotation()" ><?php _e('Send_quotation_request_word', 'resa'); ?></h4>
                  <input id="checkboxPayment" type="checkbox"ng-if="formCtrl.settings.checkbox_payment" ng-model="formCtrl.checkboxAcceptPayment" />
                  <label for="checkboxPayment" ng-if="formCtrl.settings.checkbox_payment" ng-bind-html="formCtrl.getTextByLocale(formCtrl.settings.checkbox_title_payment, '<?php echo get_locale(); ?>')|displayNewLines"></label>
                </div>
              </div>
              <div class="resa-cart-btn">
                <div class="resa-btn btn-small" ng-class="{'resa-btn-disabled' : !formCtrl.formIsOk({customerIsOk:formCtrl.customerIsOkForForm })}" ng-if="!formCtrl.launchValidForm" ng-click="formCtrl.validForm({customerIsOk:formCtrl.customerIsOkForForm })" ng-disabled="!formCtrl.formIsOk({customerIsOk:formCtrl.customerIsOkForForm })"><?php _e('ok_link_title', 'resa'); ?></div>
                <div class="resa-talign-center" ng-if="formCtrl.launchValidForm">
                  <div><?php _e('valid_form_process_link_title', 'resa'); ?></div>
                </div>
                <div class="resa-talign-center">
                  <div class="resa-fstyle-italic" ng-if="!formCtrl.customerIsOkForForm()">!! <?php _e('customer_error_feedback', 'resa'); ?> !!<br /></div>
                  <div class="resa-fstyle-italic" ng-if="!formCtrl.basketIsOk()">!! <?php _e('basket_error_feedback', 'resa'); ?> !!<br /></div>
                  <div class="resa-fstyle-italic" ng-if="!formCtrl.checkboxPayment()">!! <?php _e('checkbox_payment_error_feedback', 'resa'); ?> !!<br /></div>
                  <div class="resa-fstyle-italic" ng-if="!formCtrl.typePaymentChosen()">!! <?php _e('type_payment_error_feedback', 'resa'); ?> !!<br /></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div id="resa-step-end" ng-show="formCtrl.step == 7">
          <div id="step7_content">
            <h2 class="resa-step-end-title resa-talign-center" ng-if="formCtrl.redirect"><?php _e('Redirection_word_form', 'resa') ?></h2>
            <p class="resa-step-end-text" ng-if="!formCtrl.redirect" ng-bind-html="formCtrl.confirmationText|htmlSpecialDecode:true"></p>
          </div>
        </div>
      </div>
      <div id="cart" ng-show="formCtrl.getAllServicesParameters().length > 0 && formCtrl.step <= 5">
        <div class="cart-content">
          <h3 class="resa-talign-center"><?php _e('basket_recapitulative_form_title', 'resa') ?></h3>
          <div class="resa-cart-activities" ng-repeat="serviceElement in formCtrl.getBasket()">
            <div class="resa-cart-activity" ng-if="!serviceParameters.isLink()" ng-repeat="serviceParameters in serviceElement.servicesParameters">
              <h4 class="resa-cart-activity-title">{{ formCtrl.getTextByLocale(serviceElement.service.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
              <div class="resa-cart-activity-content">
                <div class="resa-cart-activity-date-content">
                  <div class="resa-cart-activity-datetime" ng-if="!serviceParameters.isCombined()">
                    <div class="resa-cart-activity-date text-important" >
                      {{ serviceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}
                    </div>
                    <div class="resa-cart-activity-time text-important" ng-if="!serviceParameters.isNoEnd()">
                      {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ serviceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                    </div>
                    <div class="resa-cart-activity-time text-important" ng-if="serviceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</div>
                  </div>
                  <div class="resa-cart-activity-datetime" ng-if="serviceParameters.isCombined()">
                    <div class="resa-cart-activity-date text-important">
                      <div class="text-important" ng-repeat="displayServiceParameters in formCtrl.getAllServiceParametersLink(serviceParameters)"
                      ng-class="{'resa-tdecoration-lthrough':displayServiceParameters.haveTag('absent')}">
                        {{ displayServiceParameters.dateStart | formatDate:'<?php echo $variables['date_format']; ?>' }}
                      </div>
                    </div>
                    <div class="resa-cart-activity-time text-important" ng-if="!serviceParameters.isNoEnd()">
                      {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }} <?php _e('to_word','resa') ?> {{ serviceParameters.dateEnd | formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                    </div>
                    <div class="resa-cart-activity-time text-important" ng-if="serviceParameters.isNoEnd()"><?php _e('begin_word','resa') ?> {{ serviceParameters.dateStart | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}</div>
                  </div>
                  <div class="resa-cart-activity-prices">
                    <div class="resa-cart-activity-price" ng-repeat="numberPrice in serviceParameters.numberPrices track by $index">
                      <div class="resa-cart-prices-content">
                        <div class="resa-cart-activity-price-name">{{ formCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                        <div class="resa-cart-activity-price-number">{{ numberPrice.number }} <span ng-show="numberPrice.number==1 && !numberPrice.price.extra"><?php _e('person_word', 'resa'); ?></span><span ng-show="numberPrice.number>1 && !numberPrice.price.extra"><?php _e('persons_word', 'resa'); ?></span></div>
                      </div>
                      <div class="resa-cart-prices-amount text-important" ng-if="serviceParameters.canCalculatePrice(numberPrice)">
                        {{ formCtrl.round(serviceParameters.getPriceNumberPrice(numberPrice) * serviceParameters.getNumberDays()) }}{{ formCtrl.settings.currency }}
                      </div>
                      <div ng-if="!serviceParameters.isDefaultSlugServicePrice(numberPrice)" class="resa-cart-activity-price-actions">
                        <div class="resa-cart-activity-price-action-delete">
                          <a class="action-delete" ng-click="formCtrl.removeBasket(formCtrl.getServiceParametersLink(serviceParameters), $index); formCtrl.updateCurrentServiceParameters(formCtrl.getServiceParametersLink(serviceParameters));">X</a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="resa-cart-activity-prices" ng-if="formCtrl.mapIdClientObjectReduction['id'+serviceParameters.id].length > 0">
                    <hr />
                    <div class="resa-cart-activity-price" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id'+serviceParameters.id] track by $index">
                      <div class="resa-cart-prices-content">
                        <div class="resa-cart-activity-price-name">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }}</div>
                        <div class="resa-cart-activity-price-number">
                          <span ng-if="params.type == 0">{{ params.value|negative }}{{ formCtrl.settings.currency }}</span>
                          <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                          <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                          <span ng-if="params.type == 3">{{ params.value }}{{ formCtrl.settings.currency }} <span class="tarif_barre"> {{ numberPrice.price.price }}{{ formCtrl.settings.currency }}</span></span>
                          <span ng-if="params.type == 4">{{ params.value * params.number }} <span ng-if="(params.value * params.number) == 1"><?php _e('offer_quantity_words', 'resa') ?></span><span ng-if="(params.value * params.number) > 1"><?php _e('offer_quantities_words', 'resa') ?></span></span>
                          <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                        </div>
                      </div>
                      <div class="resa-cart-prices-amount text-important" ng-if="serviceParameters.canCalculatePrice(numberPrice)">
                        <span ng-if="params.type == 0">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 1">{{ ((serviceParameters.getPriceNumberPrice(numberPrice) * params.number) * params.value  / 100)|negative }}{{ formCtrl.settings.currency }} </span>
                        <span ng-if="params.type == 2">{{ (params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 3">{{ ((numberPrice.price.price - params.value) * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 4">{{ (serviceParameters.getPriceNumberPrice(numberPrice)/numberPrice.number * params.value * params.number)|negative }}{{ formCtrl.settings.currency }}</span>
                        <span ng-if="params.type == 5"></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="resa-cart-reductions">
            <div class="active-reductions">
              <div class="resa-cart-reduction" ng-if="params.promoCode.length == 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id0'] track by $index">
                <div class="reduction-content">
                  <div class="reduction-title">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                  <div class="reduction-description" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                  <div class="reduction-value resa-fstyle-italic">
                    <span ng-if="params.type == 0">{{ params.value |negative }}{{ formCtrl.settings.currency }}</span>
                    <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                    <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                    <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                  </div>
                </div>
                <div class="reduction-price text-important resa-fweight-bold">
                  <span ng-if="params.type == 0">{{ params.value|negative|number:2 }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 1">{{ (formCtrl.getSubTotalPrice(formCtrl.getAllServicesParameters()) * params.value / 100)|negative|number:2 }}{{ formCtrl.settings.currency }} </span>
                  <span ng-if="params.type == 2">{{ (params.value * formCtrl.getTotalNumber())|negative|number:2 }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 5"></span>
                </div>
              </div>
              <div class="resa-cart-reduction" ng-if="params.promoCode.length > 0" ng-repeat="params in formCtrl.mapIdClientObjectReduction['id0'] track by $index">
                <div class="reduction-content">
                  <div class="reduction-title">{{ formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>') }} (x{{ params.number }})</div>
                  <div class="reduction-description" ng-bind-html="formCtrl.getTextByLocale(formCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
                  <div class="reduction-value resa-fstyle-italic">
                    <span ng-if="params.type == 0">{{ params.value |negative }}{{ formCtrl.settings.currency }}</span>
                    <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                    <span ng-if="params.type == 2">{{ params.value|negative }}{{ formCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                    <span ng-if="params.type == 5" ng-bind-html="params.value|htmlSpecialDecode:true"></span>
                  </div>
                </div>
                <div class="reduction-price text-important resa-fweight-bold">
                  <span ng-if="params.type == 0">{{ params.value|negative|number:2 }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 1">{{ (formCtrl.getSubTotalPrice(formCtrl.getAllServicesParameters()) * params.value / 100)|negative|number:2 }}{{ formCtrl.settings.currency }} </span>
                  <span ng-if="params.type == 2">{{ (params.value * formCtrl.getTotalNumber())|negative|number:2 }}{{ formCtrl.settings.currency }}</span>
                  <span ng-if="params.type == 5"></span>
                </div>
                <a class="action-delete" ng-if="params.promoCode.length > 0" ng-click="formCtrl.deleteCoupon(params.promoCode)">X</a>
              </div>
            </div>
            <div class="add-coupon">
              <input ng-disabled="formCtrl.getReductionsLaunched" type="text" ng-model="formCtrl.currentPromoCode" placeholder="<?php _e('Coupon_word', 'resa'); ?>" />
              <div class="resa-btn btn-small" ng-class="{'resa-btn-disabled': formCtrl.getReductionsLaunched}" ng-click="formCtrl.getReductions(formCtrl.currentPromoCode)"><?php _e('add_coupon_link_title', 'resa') ?></div>
              <div ng-if="formCtrl.getReductionsLaunched" class="resa-fstyle-italic"><?php _e('process_reductions_feedback', 'resa'); ?>...</div>
            </div>
          </div>
          <div class="resa-cart-total">
            <div class="resa-cart-total-title text-important"><?php _e('Total_word', 'resa') ?></div>
            <div class="resa-cart-total-value text-important">{{ formCtrl.getTotalPrice()|number:2 }}{{ formCtrl.settings.currency }}</div>
          </div>
          <div class="resa-cart-btn">
            <div class="resa-btn btn-small" ng-click="formCtrl.chooseNewActivity(); formCtrl.scrollTo('resa-form-navigation')">{{ formCtrl.getTextByLocale(formCtrl.settings.form_add_new_activity_text,'<?php echo get_locale(); ?>') |htmlSpecialDecode}}</div>
            <div class="resa-btn btn-small" ng-if="!formCtrl.isLocked(6)" ng-click="formCtrl.setStep(6)">Valider la réservation</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
