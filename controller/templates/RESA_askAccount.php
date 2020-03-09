<div ng-app="resa_app" ng-controller="AskAccountController as askAccountCtrl" ng-init='askAccountCtrl.initialize(<?php echo htmlspecialchars($variables['customer'], ENT_QUOTES); ?>, <?php echo htmlspecialchars($variables['settings'], ENT_QUOTES); ?>, <?php echo htmlspecialchars($variables['typeAccount'], ENT_QUOTES); ?>, <?php echo htmlspecialchars($variables['countries'], ENT_QUOTES); ?>, "<?php echo admin_url('admin-ajax.php'); ?>", "<?php echo $variables['currentUrl']; ?>")' ng-cloak>
  <div ng-if="!askAccountCtrl.isBrowserCompatibility()" style="color:red; font-weight:bold">
    <span ng-bind-html="askAccountCtrl.getTextByLocale(askAccountCtrl.settings.browser_not_compatible_sentence, '<?php echo get_locale(); ?>')|htmlSpecialDecode"></span>
  </div>
  <section id="ro_front_formulaire" class="ask_account" ng-if="!askAccountCtrl.askAccountRequestLaunched">
    <div ng-if="askAccountCtrl.customer.ID > -1">
      <h2 class="size_xl"><?php _e('Hello_word', 'resa'); ?> <span ng-if="askAccountCtrl.customer.firstName.length > 0 || askAccountCtrl.customer.lastName.length > 0"> {{ askAccountCtrl.customer.firstName | htmlSpecialDecode }}  {{ askAccountCtrl.customer.lastName | htmlSpecialDecode }}</span><span ng-if="askAccountCtrl.customer.firstName.length == 0 && askAccountCtrl.customer.lastName.length == 0">{{ askAccountCtrl.customer.company }}</span></h2>
      <p><?php _e('need_deconnection_for_ask_account_sentence', 'resa'); ?><br /> <a style="text-decoration: underline;" ng-click="askAccountCtrl.userDeconnection()"><?php _e('logout_link_title', 'resa'); ?></a></p>
    </div>
    <div class="infos_clients" ng-if="askAccountCtrl.customer.ID == -1">
      <a ng-if="askAccountCtrl.settings.facebook_activated && !askAccountCtrl.isFacebookConnected()" ng-click="askAccountCtrl.login()" class="facebook_connection"></a>
      <p class="signin_description"><?php _e('create_account_request_sentence', 'resa'); ?></p>
      <form>
        <span ng-if="askAccountCtrl.isFacebookConnected()">{{ askAccountCtrl.customer.lastName }}<br /></span>
        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.dataFacebook.last_name == null && askAccountCtrl.fieldStates['lastName'] == 1)">
          <input ng-if="askAccountCtrl.fieldStates['lastName'] > 0" class="input_wide" type="text" ng-model="askAccountCtrl.customer.lastName" placeholder="{{ askAccountCtrl.getPlaceolder('<?php _e('last_name_field_title', 'resa') ?>','lastName') }}" />
          <span ng-class="{'input_error':askAccountCtrl.fieldStates['lastName'] == 1, 'input_info':askAccountCtrl.fieldStates['lastName'] == 2}" ng-show="askAccountCtrl.customer.lastName.length == 0 && askAccountCtrl.fieldStates['lastName'] > 0"><span ng-if="askAccountCtrl.fieldStates['lastName'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="askAccountCtrl.fieldStates['lastName'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
        </span>

        <span ng-if="askAccountCtrl.isFacebookConnected()">{{ askAccountCtrl.customer.firstName }}<br /></span>
        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.dataFacebook.first_name == null && askAccountCtrl.fieldStates['firstName'] == 1)">
          <input ng-if="askAccountCtrl.fieldStates['firstName'] > 0" class="input_wide" type="text" ng-model="askAccountCtrl.customer.firstName" placeholder="{{ askAccountCtrl.getPlaceolder('<?php _e('first_name_field_title', 'resa') ?>','firstName') }}" />
          <span ng-class="{'input_error':askAccountCtrl.fieldStates['firstName'] == 1, 'input_info':askAccountCtrl.fieldStates['firstName'] == 2}" ng-show="askAccountCtrl.customer.firstName.length == 0 && askAccountCtrl.fieldStates['firstName'] > 0"><span ng-if="askAccountCtrl.fieldStates['firstName'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="askAccountCtrl.fieldStates['firstName'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
        </span>

        <span ng-if="askAccountCtrl.isFacebookConnected()">{{ askAccountCtrl.customer.email }}<br /></span>
        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.customer.email.length == 0)">
          <input class="input_wide" type="email" ng-model="askAccountCtrl.customer.email" placeholder="<?php _e('email_field_title', 'resa') ?>*" />
          <span class="input_error"  ng-show="askAccountCtrl.customer.email==null || askAccountCtrl.customer.email.length == 0"><?php _e('not_an_email_address_sentence', 'resa') ?></span>
        </span>

        <span ng-if="!askAccountCtrl.isFacebookConnected()">
          <input class="input_wide" type="password" ng-model="askAccountCtrl.customer.password" placeholder="<?php _e('password_field_title', 'resa') ?>*" ng-init="askAccountCtrl.customer.password = ''">
          <span class="input_error" ng-show="askAccountCtrl.customer.password.length == 0"><?php _e('required_word', 'resa') ?></span>
          <span style="color:orange; font-size:14px; font-style:italic; line-height: normal important!;" ng-show="!askAccountCtrl.checkPassword(askAccountCtrl.customer.password)"><?php _e('bad_password_words', 'resa'); ?></span>
          <input class="input_wide" type="password" ng-model="askAccountCtrl.customer.confirmPassword" placeholder="<?php _e('confirm_password_field_title', 'resa') ?>*"  ng-init="askAccountCtrl.customer.confirmPassword = ''" />
          <span class="input_error" ng-show="askAccountCtrl.customer.confirmPassword.length == 0"><?php _e('required_word', 'resa') ?></span>
          <span class="input_error" ng-show="askAccountCtrl.customer.confirmPassword.length > 0 && askAccountCtrl.customer.confirmPassword != askAccountCtrl.customer.password"><?php _e('not_same_password_words', 'resa') ?></span>
        </span>

        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.fieldStates['company'] == 1)">
          <input ng-if="askAccountCtrl.fieldStates['company'] > 0" class="input_wide" type="text" ng-model="askAccountCtrl.customer.company" placeholder="{{ askAccountCtrl.getPlaceolder('<?php _e('company_field_title', 'resa') ?>','company') }}" />
          <span ng-class="{'input_error':askAccountCtrl.fieldStates['company'] == 1, 'input_info':askAccountCtrl.fieldStates['company'] == 2}" ng-show="askAccountCtrl.customer.company.length == 0 && askAccountCtrl.fieldStates['company'] > 0"><span ng-if="askAccountCtrl.fieldStates['company'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="askAccountCtrl.fieldStates['company'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
        </span>

        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.fieldStates['phone'] == 1)">
          <input ng-if="askAccountCtrl.fieldStates['phone'] > 0" class="input_wide" type="text" ng-model="askAccountCtrl.customer.phone" placeholder="{{ askAccountCtrl.getPlaceolder('<?php _e('phone_field_title', 'resa') ?>','phone') }}" />
          <span ng-class="{'input_error':askAccountCtrl.fieldStates['phone'] == 1, 'input_info':askAccountCtrl.fieldStates['phone'] == 2}" ng-show="askAccountCtrl.customer.phone.length == 0 && askAccountCtrl.fieldStates['phone'] > 0"><span ng-if="askAccountCtrl.fieldStates['phone'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="askAccountCtrl.fieldStates['phone'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
        </span>

        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.fieldStates['phone2'] == 1)">
          <input ng-if="askAccountCtrl.fieldStates['phone2'] > 0" class="input_wide" type="text" ng-model="askAccountCtrl.customer.phone2" placeholder="{{ askAccountCtrl.getPlaceolder('<?php _e('phone2_field_title', 'resa') ?>','phone2') }}" />
          <span ng-class="{'input_error':askAccountCtrl.fieldStates['phone2'] == 1, 'input_info':askAccountCtrl.fieldStates['phone2'] == 2}" ng-show="askAccountCtrl.customer.phone2.length == 0 && askAccountCtrl.fieldStates['phone2'] > 0"><span ng-if="askAccountCtrl.fieldStates['phone2'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="askAccountCtrl.fieldStates['phone2'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
        </span>

        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.fieldStates['address'] == 1)">
          <input ng-if="askAccountCtrl.fieldStates['address'] > 0" class="input_wide" type="text" ng-model="askAccountCtrl.customer.address" placeholder="{{ askAccountCtrl.getPlaceolder('<?php _e('address_field_title', 'resa') ?>','address') }}" />
          <span ng-class="{'input_error':askAccountCtrl.fieldStates['address'] == 1, 'input_info':askAccountCtrl.fieldStates['address'] == 2}" ng-show="askAccountCtrl.customer.address.length == 0 && askAccountCtrl.fieldStates['address'] > 0"><span ng-if="askAccountCtrl.fieldStates['address'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="askAccountCtrl.fieldStates['address'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
        </span>

        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.fieldStates['postalCode'] == 1)">
          <input ng-if="askAccountCtrl.fieldStates['postalCode'] > 0" class="input_wide" type="text" ng-model="askAccountCtrl.customer.postalCode" placeholder="{{ askAccountCtrl.getPlaceolder('<?php _e('postal_code_field_title', 'resa') ?>','postalCode') }}" />
          <span ng-class="{'input_error':askAccountCtrl.fieldStates['postalCode'] == 1, 'input_info':askAccountCtrl.fieldStates['postalCode'] == 2}" ng-show="askAccountCtrl.customer.postalCode.length == 0 && askAccountCtrl.fieldStates['postalCode'] > 0"><span ng-if="askAccountCtrl.fieldStates['postalCode'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="askAccountCtrl.fieldStates['postalCode'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
        </span>

        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.fieldStates['town'] == 1)">
          <input ng-if="askAccountCtrl.fieldStates['town'] > 0" class="input_wide" type="text" ng-model="askAccountCtrl.customer.town" placeholder="{{ askAccountCtrl.getPlaceolder('<?php _e('town_field_title', 'resa') ?>','town') }}" />
          <span ng-class="{'input_error':askAccountCtrl.fieldStates['town'] == 1, 'input_info':askAccountCtrl.fieldStates['town'] == 2}" ng-show="askAccountCtrl.customer.town.length == 0 && askAccountCtrl.fieldStates['town'] > 0"><span ng-if="askAccountCtrl.fieldStates['town'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="askAccountCtrl.fieldStates['town'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
        </span>

        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.fieldStates['country'] == 1)">
          <select  class="input_wide" ng-if="askAccountCtrl.fieldStates['country'] > 0" ng-model="askAccountCtrl.customer.country">
            <option value="" disabled selected>-- {{ askAccountCtrl.getPlaceolder('<?php _e('country_field_title', 'resa') ?>','country') }} --</option>
            <option ng-repeat="country in askAccountCtrl.countries" value="{{ country.code }}">{{ country.name }}</option>
          </select>
          <span ng-class="{'input_error':askAccountCtrl.fieldStates['country'] == 1, 'input_info':askAccountCtrl.fieldStates['country'] == 2}" ng-show="askAccountCtrl.customer.country.length == 0 && askAccountCtrl.fieldStates['country'] > 0"><span ng-if="askAccountCtrl.fieldStates['country'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="askAccountCtrl.fieldStates['country'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
        </span>

        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.fieldStates['siret'] == 1)">
          <input ng-if="askAccountCtrl.fieldStates['siret'] > 0" class="input_wide" type="text" ng-model="askAccountCtrl.customer.siret" placeholder="{{ askAccountCtrl.getPlaceolder('<?php _e('siret_field_title', 'resa') ?>','siret') }}" />
          <span ng-class="{'input_error':askAccountCtrl.fieldStates['siret'] == 1, 'input_info':askAccountCtrl.fieldStates['siret'] == 2}" ng-show="askAccountCtrl.customer.siret.length == 0 && askAccountCtrl.fieldStates['siret'] > 0"><span ng-if="askAccountCtrl.fieldStates['siret'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="askAccountCtrl.fieldStates['siret'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
        </span>

        <span ng-if="!askAccountCtrl.isFacebookConnected() || (askAccountCtrl.isFacebookConnected() && askAccountCtrl.fieldStates['legalForm'] == 1)">
          <input ng-if="askAccountCtrl.fieldStates['legalForm'] > 0" class="input_wide" type="text" ng-model="askAccountCtrl.customer.legalForm" placeholder="{{ askAccountCtrl.getPlaceolder('<?php _e('legal_form_field_title', 'resa') ?>','legalForm') }}" />
          <span ng-class="{'input_error':askAccountCtrl.fieldStates['legalForm'] == 1, 'input_info':askAccountCtrl.fieldStates['legalForm'] == 2}" ng-show="askAccountCtrl.customer.legalForm.length == 0 && askAccountCtrl.fieldStates['legalForm'] > 0"><span ng-if="askAccountCtrl.fieldStates['legalForm'] == 1"><?php _e('required_word', 'resa') ?></span><span ng-if="askAccountCtrl.fieldStates['legalForm'] == 2"><?php _e('facultative_word', 'resa') ?></span></span>
        </span>

        <label ng-if="askAccountCtrl.fieldStates['newsletters'] > 0" for="acceptNewsletters">
          <input id="acceptNewsletters" type="checkbox" ng-model="askAccountCtrl.customer.registerNewsletters" />
          <?php _e('accept_register_newsletters', 'resa'); ?><br />
        </label>

        <label for="acceptGDPR"><input id="acceptGDPR" type="checkbox" ng-model="askAccountCtrl.checkboxCustomer" /><?php _e('gdpr_accept_to_save_data', 'resa'); ?></label>
        <br />
        <button ng-disabled="!askAccountCtrl.customerIsOkForForm()" class="input_wide" ng-click="askAccountCtrl.askAccountRequest()">
          <?php _e('ok_link_title', 'resa'); ?><span ng-if="askAccountCtrl.askAccountRequestLaunch">...</span>
        </button>
      </form>
    </div>
  </section>
  <section id="ro_front_formulaire" ng-if="askAccountCtrl.askAccountRequestLaunched">
    <?php _e('create_account_request_done_sentence', 'resa'); ?>
  </section>
</div>
