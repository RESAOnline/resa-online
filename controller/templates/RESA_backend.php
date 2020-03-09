<div id="resa_online_admin" class="wrap" ng-class="{'full_screen':backendCtrl.fullScreenMode}" ng-app="resa_app" ng-controller="BackendController as backendCtrl" ng-init='backendCtrl.initialize({
  persons_word: "<?php _e('persons_word','resa'); ?>",
  person_word: "<?php _e('person_word','resa'); ?>",
  begin_word: "<?php _e('begin_word','resa'); ?>",
  Total_word: "<?php _e('Total_word', 'resa'); ?>",
  no_payment_state: "<?php _e('no_payment_state', 'resa'); ?>",
  incomplete_payment_state: "<?php _e('incomplete_payment_state', 'resa'); ?>",
  complete_payment_state: "<?php _e('complete_payment_state', 'resa'); ?>",
  Assigned_to_words: "<?php _e('Assigned_to_words', 'resa'); ?>",
  have_a_note_words: "<?php _e('have_a_note_words', 'resa'); ?>",
  delete_info_calendar_title_dialog: "<?php _e('delete_info_calendar_title_dialog', 'resa'); ?>",
  delete_info_calendar_text_dialog: "<?php _e('delete_info_calendar_text_dialog', 'resa'); ?>",
  delete_info_calendar_confirmButton_dialog: "<?php _e('delete_info_calendar_confirmButton_dialog', 'resa'); ?>",
  delete_info_calendar_cancelButton_dialog: "<?php _e('delete_info_calendar_cancelButton_dialog', 'resa'); ?>",
  delete_service_constraint_title_dialog: "<?php _e('delete_info_calendar_title_dialog', 'resa'); ?>",
  delete_service_constraint_text_dialog: "<?php _e('delete_service_constraint_text_dialog', 'resa'); ?>",
  delete_service_constraint_confirmButton_dialog: "<?php _e('delete_service_constraint_confirmButton_dialog', 'resa'); ?>",
  delete_service_constraint_cancelButton_dialog: "<?php _e('delete_service_constraint_cancelButton_dialog', 'resa'); ?>"
},"<?php echo htmlspecialchars($variables['time_format'], ENT_QUOTES); ?>", <?php echo $variables['groupsManagement']?'true':'false'; ?>,"<?php echo get_locale(); ?>", "<?php echo admin_url('admin-ajax.php'); ?>", "<?php echo RESA_Variables::getCurrentPageURL(); ?>",
<?php echo $variables['months']; ?>, <?php echo htmlspecialchars($variables['countries'], ENT_QUOTES); ?>)' ng-cloak>
  <div class="resa_header">
    <div class="resa_header_logo">
      <img class="resa_logo" src="<?php echo plugins_url('..', __FILE__ ); ?>/images/logo_resa-online.jpg" />
      <?php if(defined('WP_DEBUG') && WP_DEBUG && defined('WP_RESA_DEBUG') && WP_RESA_DEBUG){ ?> <span style="color:red; font-weight:bold">DEBUG</span> <?php } ?>
    </div>
    <div class="resa_header_raccourcis">
      <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_bookings_tab']){ ?>
        <a class="reservation" ng-class="{'active':backendCtrl.currentPage == 'reservations'}" ng-click="backendCtrl.setCurrentPage('reservations')">Réservations</a>
      <?php } ?>
      <a class="planning" ng-if="backendCtrl.settings.groupsManagement" ng-class="{'active':backendCtrl.currentPage == 'planning'}" ng-click="backendCtrl.setCurrentPage('planning')">Planning</a>
      <?php if(!RESA_Variables::staffIsConnected()){ ?>
        <a class="quotition" ng-class="{'active':backendCtrl.currentPage == 'quotations'}" ng-click="backendCtrl.setCurrentPage('quotations')">Devis</a>
        <a class="client" ng-class="{'active':backendCtrl.currentPage == 'clients'}" ng-click="backendCtrl.setCurrentPage('clients')">Clients</a>
        <a class="daily" target="_blank" href="{{ backendCtrl.settings.daily_link }}">Journée</a>
        <a class="vouchers" target="_blank" href="{{ backendCtrl.settings.vouchers_link }}">Coupons</a>
        <a class="settings" target="_blank" href="{{ backendCtrl.settings.settings_link }}">Réglages</a>
        <a class="support" target="_blank" href="{{ backendCtrl.settings.support_url }}">Support</a>
        <a ng-if="backendCtrl.isCaisseOnlineActivated()" class="caisse" target="_blank" href="{{ backendCtrl.getCaisseOnlineLink() }}">Caisse</a>
        <a ng-if="backendCtrl.settings.swikly_link != ''" class="swikly" target="_blank" href="{{ backendCtrl.settings.swikly_link }}">Swikly</a>
      <?php } ?>
      <a class="retour" target="_blank" href="<?php echo get_site_url().'/wp-admin'; ?>">Retour</a>
      <span>
        <a class="resa_news" ng-click="backendCtrl.switchRESANewsCenter($event)">
          <span class="dashicons dashicons-welcome-widgets-menus"></span>
          <span class="alert_number" ng-show="backendCtrl.RESANewsWaitingNumber > 0">{{ backendCtrl.RESANewsWaitingNumber }}</span>
        </a>
        <div ng-if="backendCtrl.RESANewsIsOpened()" id="resa_news_center_wrapper">
          <div id="resa_news_title">
            Nouveautés de RESA Online
          </div>
          <div id="resa_news_center_content">
            <div class="loader_box" ng-show="backendCtrl.RESANewsLaunched">
              <div class="loader_content">
                <div class="loader">
                  <div class="shadow"></div>
                  <div class="box"></div>
                </div>
              </div>
            </div>
            <div ng-if="backendCtrl.RESANews.length == 0">
              Pas de nouveauté
            </div>
            <div ng-repeat="news in backendCtrl.RESANews" class="notif">
              <div class="resa_news_infos">
                <span ng-bind-html="news.excerpt.rendered|htmlSpecialDecode:true"></span><br />
              </div>
              <div class="resa_news_time">
                 <span class="time">{{ news.date| formatDateTime:'<?php echo $variables['date_format'].' '.$variables['time_format']; ?>' }}</span>
                 <a class="see" target="_blank" href="{{ news.link }}">En savoir +</a>
              </div>
            </div>
          </div>
        </div>
      </span>
	    <a class="full_screen" title="Mode plein écran" ng-click="backendCtrl.fullScreenMode = !backendCtrl.fullScreenMode"><img src="<?php echo plugin_dir_url(__FILE__).'../images/ico_full_screen.png'; ?>" /></a>
    </div>
    <div class="resa_header_infos">
      <p>
        Version <span ng-class="{'helpbox':backendCtrl.settings.currentVersion != null && backendCtrl.settings.currentVersion != backendCtrl.settings.version}">{{ backendCtrl.settings.version }} <span ng-if="backendCtrl.settings.currentVersion != null && backendCtrl.settings.currentVersion != backendCtrl.settings.version" class="helpbox_content">RESA Online a été mis à jour<br />Veuillez recharger la page pour prendre<br /> en compte les modifications.</span></span>
        <?php if(current_user_can('update_plugins')) { ?>
        <a target="_self" ng-if="backendCtrl.settings.newUpdate" href="<?php echo admin_url(); ?>update-core.php">Nouvelle version</a>
        <?php } ?>
      </p>
      <p class="infos_user" ng-if="backendCtrl.currentRESAUser">
        Connecté_e en tant que : <br />
        <b>{{ backendCtrl.currentRESAUser.displayName }}</b>
        <b>({{ backendCtrl.currentRESAUser.role }})</b>
      </p>
    </div>
    <div ng-if="backendCtrl.settings != null && !backendCtrl.settings.staffIsConnected" id="notif_center_wrapper">
      <div ng-click="backendCtrl.switchLogNotificationCenter()" id="notif_center_button">
        <span class="icon_notif_center dashicons dashicons-megaphone"></span>
        <span id="alert_number" ng-show="backendCtrl.logNotificationsWaitingNumber > 0">{{ backendCtrl.logNotificationsWaitingNumber }}</span>
      </div>
      <div ng-if="backendCtrl.logNotificationsCenterDisplayed" id="notif_center_content">
        <div class="loader_box" ng-show="backendCtrl.getLogNotificationsLaunched">
          <div class="loader_content">
            <div class="loader">
              <div class="shadow"></div>
              <div class="box"></div>
            </div>
          </div>
        </div>
        <div id="notif_center_content_header">
          <a class="all_seen" ng-click="backendCtrl.clickAllLogNotifications()">Tout marquer comme vu</a>
          <a class="log_link" ng-click="backendCtrl.closeLogNotificationCenter(); backendCtrl.setCurrentPage('logNotifications')">Voir le journal complet</a>
        </div>
        <div id="notif_center_content_tabs">
          <div id="tabs_menu">
            <div class="tab_item" ng-class="{'active':backendCtrl.criticityDisplayed == 0}" ng-click="backendCtrl.criticityDisplayed = 0">Externes</div>
            <div class="tab_item" ng-class="{'active':backendCtrl.criticityDisplayed == 1}" ng-click="backendCtrl.criticityDisplayed = 1">Internes</div>
          </div>
          <div class="tab_content active">
            <div class="separator">Récent</div>
            <div ng-click="backendCtrl.clickOnLogNotifications(log)" ng-if="backendCtrl.criticityDisplayed == log.criticity && backendCtrl.isRecent(log)" ng-repeat="log in backendCtrl.logNotifications" class="notif {{ backendCtrl.getType(log) }}">
              <div class="notif_icon">
                <span ng-if="log.idBooking == -1" class="icon dashicons dashicons-admin-users"></span>
                <span ng-if="log.idBooking > -1" class="icon dashicons dashicons-media-default"></span>
              </div>
              <div class="notif_infos">
                <span ng-bind-html="log.text|htmlSpecialDecode:true"></span>
              </div>
              <div class="notif_time">
                {{ backendCtrl.getRemainingTime(log, '<?php echo $variables['date_format']; ?>', '<?php echo $variables['time_format']; ?>')}}
                <span ng-if="log.idPlaces.length > 0"> - {{ backendCtrl.getListPlaces(log.idPlaces.split(',')) }}</span>
                <span ng-if="log.clicked">- <a id="not_read" ng-click="backendCtrl.unreadLogNotification($event, log)">Marquer non lu</a></span>
              </div>
            </div>
            <div class="separator">Antérieur</div>
            <div ng-click="backendCtrl.clickOnLogNotifications(log)" ng-if="backendCtrl.criticityDisplayed == log.criticity && !backendCtrl.isRecent(log)" ng-repeat="log in backendCtrl.logNotifications" class="notif {{ backendCtrl.getType(log) }}">
              <div class="notif_icon">
                <span ng-if="log.idBooking == -1" class="icon dashicons dashicons-admin-users"></span>
                <span ng-if="log.idBooking > -1" class="icon dashicons dashicons-media-default"></span>
              </div>
              <div class="notif_infos">
                <span ng-bind-html="log.text|htmlSpecialDecode:true"></span>
              </div>
              <div class="notif_time">
                {{ backendCtrl.getRemainingTime(log, '<?php echo $variables['date_format']; ?>', '<?php echo $variables['time_format']; ?>') }}
                <span ng-if="log.idPlaces.length > 0"> - {{ backendCtrl.getListPlaces(log.idPlaces.split(',')) }}</span>
                <span ng-if="log.clicked">- <a id="not_read" ng-click="backendCtrl.unreadLogNotification($event, log)">Marquer non lu</a></span>
              </div>
            </div>
            <div id="load_more" ng-click="backendCtrl.loadLogNotificationsAnteriors(backendCtrl.criticityDisplayed)">Plus anciennes</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="resa_content">
    <div class="resa_popup" id="resa_client" ng-controller="CustomerController as customerCtrl" ng-show="customerCtrl.opened">
      <div class="resa_popup_wrapper">
        <div class="resa_popup_content">
          <div class="client_reservation">
            <div class="popup_header">
              <div class="resa_popup_header_wrapper">
                <div class="popup_header_title">
                  <h2>Fiche client</h2>
                </div>
                <div class="popup_header_menu">
                  <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="customerCtrl.close()"><span class="title_box_content">Fermer</span></div>
                </div>
              </div>
            </div>
            <div class="popup_content">
              <!-- visualisateur d'email //-->
              <div class="resa_sous_popup" ng-if="customerCtrl.emailCustomerDisplayed != null">
                <div class="resa_sous_popup_wrapper">
                  <div class="resa_sous_popup_close">
                    <input class="btn btn_rouge" type="button" ng-click="customerCtrl.closeEmailCustomer()" value="Fermer" />
                  </div>
                  <div class="resa_sous_popup_content">
                    <span ng-if="customerCtrl.emailCustomerDisplayed.idSender != -1 && customerCtrl.emailCustomerDisplayed.idSender != customerCtrl.customer.ID">Expédié par <b>{{ backendCtrl.getCustomerManagerById(customerCtrl.emailCustomerDisplayed.idSender).displayName }}</b><br /></span>
                    <span ng-if="customerCtrl.emailCustomerDisplayed.idSender != -1 && customerCtrl.emailCustomerDisplayed.idSender == customerCtrl.customer.ID">Expédié par <b>{{ customerCtrl.customer.lastName | htmlSpecialDecode }} {{ customerCtrl.customer.firstName | htmlSpecialDecode }}</b><br /></span>
                    <span ng-if="customerCtrl.emailCustomerDisplayed.emailSender.length > 0">Email d'envoie <b>{{ customerCtrl.emailCustomerDisplayed.emailSender }}</b><br /></span>
                    Sujet : {{ customerCtrl.emailCustomerDisplayed.subject|htmlSpecialDecode:true }}<br />
                    Message : <br />
                    <div ng-bind-html="customerCtrl.emailCustomerDisplayed.message|htmlSpecialDecode:true|trusted"></div>
                    <div ng-if="customerCtrl.emailCustomerDisplayed.attachments.length > 0">
                      Pièces jointes :<br />
                      <span ng-repeat="attachment in customerCtrl.emailCustomerDisplayed.attachments">
                        <a target="_blank"  href="{{ attachment.filePath }}">{{ attachment.name|htmlSpecialDecode:true }}</a><br />
                      </span>
                    </div>
                    <div ng-if="customerCtrl.emailCustomerDisplayed.idSender == customerCtrl.customer.ID"><a class="btn btn_vert"  ng-click="customerCtrl.openNotificationDialog()">Répondre</a></div>
                  </div>
                </div>
              </div>
              <div class="fiche_client">
                <div class="client_informations">
                  <div ng-class="{'client_informations_edit':customerCtrl.editionMode, 'client_informations_view':!customerCtrl.editionMode}">
                    <h3 class="client_informations_title">Informations clients</h3>
                    <div class="client_information_line">
                      <label>Nom : </label><input tabindex="1" ng-if="customerCtrl.editionMode" type="text" placeholder="Nom" ng-model="customerCtrl.modelCustomer.lastName" />
                      <div ng-if="!customerCtrl.editionMode">{{ customerCtrl.customer.lastName | htmlSpecialDecode }}</div>
                    </div>
                    <div class="client_information_line">
                      <label>Prénom : </label><input tabindex="2" ng-if="customerCtrl.editionMode" type="text" placeholder="Prénom" ng-model="customerCtrl.modelCustomer.firstName" />
                      <div ng-if="!customerCtrl.editionMode">{{ customerCtrl.customer.firstName | htmlSpecialDecode }}</div>
                    </div>
                    <div class="client_information_line">
                      <label>Email : </label> <input tabindex="3" ng-if="customerCtrl.editionMode" type="text" placeholder="Email" ng-model="customerCtrl.modelCustomer.email" />
                      <div ng-if="!customerCtrl.editionMode">{{ customerCtrl.customer.email }}</div>
                    </div>
                    <div class="client_information_line">
                      <label>Entreprise : </label> <input tabindex="4" ng-if="customerCtrl.editionMode" type="text" placeholder="Entreprise" ng-model="customerCtrl.modelCustomer.company" />
                      <div ng-if="!customerCtrl.editionMode">{{ customerCtrl.customer.company | htmlSpecialDecode }}</div>
                    </div>
                    <div class="client_information_line">
                      <label>Téléphone : </label> <input tabindex="5" ng-if="customerCtrl.editionMode" type="text" placeholder="Téléphone" ng-model="customerCtrl.modelCustomer.phone" />
                      <div ng-if="!customerCtrl.editionMode">{{ customerCtrl.customer.phone|formatPhone }}</div>
                    </div>
                    <div class="client_information_line">
                      <label>Téléphone 2 : </label> <input tabindex="6" ng-if="customerCtrl.editionMode" type="text" placeholder="Téléphone" ng-model="customerCtrl.modelCustomer.phone2" />
                      <div ng-if="!customerCtrl.editionMode">{{ customerCtrl.customer.phone2|formatPhone }}</div>
                    </div>
                    <div class="client_information_line">
                      <label>Adresse : </label> <input tabindex="7" ng-if="customerCtrl.editionMode" type="text" placeholder="Adresse" ng-model="customerCtrl.modelCustomer.address" />
                      <div ng-if="!customerCtrl.editionMode">{{ customerCtrl.customer.address }}</div>
                    </div>
                    <div class="client_information_line">
                      <label>Ville : </label> <input tabindex="8" ng-if="customerCtrl.editionMode" type="text" placeholder="Ville" ng-model="customerCtrl.modelCustomer.town" />
                      <div ng-if="!customerCtrl.editionMode">{{ customerCtrl.customer.town }}</div>
                    </div>
                    <div class="client_information_line">
                      <label>Code postal : </label> <input tabindex="9" ng-if="customerCtrl.editionMode" type="text" placeholder="Code postal" ng-model="customerCtrl.modelCustomer.postalCode" />
                      <div ng-if="!customerCtrl.editionMode">{{ customerCtrl.modelCustomer.postalCode }}</div>
                    </div>
                    <div class="client_information_line">
                      <label>Pays : </label>
                      <select ng-if="customerCtrl.editionMode" ng-model="customerCtrl.modelCustomer.country" tabindex="10">
                        <option value="" disabled selected>-- Pays --</option>
                        <option ng-repeat="country in backendCtrl.countries" value="{{ country.code }}">{{ country.name }}</option>
                      </select>
                      <div ng-if="!customerCtrl.editionMode">{{ backendCtrl.getCountryByCode(customerCtrl.modelCustomer.country) }}</div>
                    </div>
                    <div class="client_information_line">
                      <label>SIRET : </label> <input tabindex="11" ng-if="customerCtrl.editionMode" type="text" placeholder="SIRET" ng-model="customerCtrl.modelCustomer.siret" />
                      <div ng-if="!customerCtrl.editionMode">{{ customerCtrl.customer.siret }}</div>
                    </div>
                    <div class="client_information_line">
                      <label>Forme juridique : </label> <input tabindex="12" ng-if="customerCtrl.editionMode" type="text" placeholder="Forme juridique" ng-model="customerCtrl.modelCustomer.legalForm" />
                      <div ng-if="!customerCtrl.editionMode">{{ customerCtrl.customer.legalForm }}</div>
                    </div>
                    <div class="client_information_line" ng-if="customerCtrl.editionMode">
                      <label for="company_account_customers">Type de compte : </label><select tabindex="13" id="company_account_customers" ng-change="customerCtrl.modelCustomer.companyAccount = false" ng-model="customerCtrl.modelCustomer.typeAccount" ng-options="typeAccount.id as (backendCtrl.getTextByLocale(typeAccount.name)| htmlSpecialDecode) for typeAccount in backendCtrl.settings.typesAccounts"></select>
                    </div>
                    <div class="client_information_line">
                      <label>Langue : </label>
                      <select tabindex="14" ng-if="customerCtrl.editionMode" ng-model="customerCtrl.modelCustomer.locale">
                        <option ng-repeat="language in backendCtrl.settings.languages" value="{{ language }}">{{ backendCtrl.settings.allLanguages[language][0] }}</option>
                      </select>
                      <div ng-if="!customerCtrl.editionMode && customerCtrl.modelCustomer.locale.length > 0">{{ backendCtrl.settings.allLanguages[customerCtrl.modelCustomer.locale][0] }}</div>
                      <div ng-if="!customerCtrl.editionMode && customerCtrl.modelCustomer.locale.length == 0">Non définie</div>
                    </div>
                    <div class="client_information_line center" ng-if="!customerCtrl.editionMode">
                      <b><i>{{ customerCtrl.getTypeAccountName() }}<span ng-if="customerCtrl.customer.idFacebook.length > 0">(Facebook)</span></i></b>
                    </div>
                    <div class="client_information_line reverse" ng-if="customerCtrl.editionMode && !customerCtrl.customer.isWpUser && !customerCtrl.customer.askAccount">
                      <div><input tabindex="15" id="wp_account" type="checkbox" ng-model="customerCtrl.modelCustomer.createWpAccount" /></div>
                      <label for="wp_account">Créer un compte sur le site (email nécessaire)</label>
                    </div>
                    <div class="client_information_line center warning" ng-if="customerCtrl.customer!=null && !customerCtrl.editionMode && !customerCtrl.customer.isWpUser && !customerCtrl.customer.askAccount">
                      <b><i>Pas de compte sur le site (ne pourra pas voir ses réservations)</i></b>
                    </div>
                    <div class="client_information_line center info" ng-if="!customerCtrl.editionMode && customerCtrl.customer.askAccount">
                      <b><i>Demande de compte</i></b>
                    </div>
                    <div class="client_information_line reverse" ng-if="customerCtrl.editionMode">
                      <div><input tabindex="16" id="company_sendRequestForOpinion" ng-true-value="'yes'" ng-false-value="'no'" type="checkbox" ng-model="customerCtrl.modelCustomer.sendRequestForOpinion" /></div>
                      <label for="company_sendRequestForOpinion">Activer demande d'avis ?</label>
                    </div>
                    <div class="client_information_line center" ng-if="!customerCtrl.editionMode">
                      <i ng-if="customerCtrl.customer.sendRequestForOpinion == 'yes'">Demande d'avis activé</i>
                      <i ng-if="customerCtrl.customer.sendRequestForOpinion == 'no'">Demande d'avis désactivé</i>
                    </div>
                    <div class="client_note">
                      <h4 ng-if="customerCtrl.customer.privateNotes.length > 0">Note privée sur le client</h4>
                      <textarea tabindex="17" ng-if="customerCtrl.editionMode" ng-model="customerCtrl.modelCustomer.privateNotes" placeholder="Note privée"></textarea>
                      <span ng-if="!customerCtrl.editionMode" ng-bind-html="customerCtrl.customer.privateNotes|htmlSpecialDecode:true"></span>
                    </div>
                    <br />
                    <b class="warning" ng-if="customerCtrl.editionMode && customerCtrl.modelCustomer.createWpAccount && (customerCtrl.modelCustomer.email == null || customerCtrl.modelCustomer.email.length == 0)">Veuillez renseigner une adresse email correcte !<br /></b>
                    <b class="warning" ng-if="customerCtrl.editionMode && !customerCtrl.modelCustomer.createWpAccount && (customerCtrl.modelCustomer.phone == null || customerCtrl.modelCustomer.phone.length == 0)">Veuillez renseigner un numéro de téléphone !<br /></b>
                    <div ng-if="customerCtrl.editionMode" class="btn" ng-class="{'btn_vert':customerCtrl.isOkCustomer(), 'btn_locked':!customerCtrl.isOkCustomer()}" ng-click="customerCtrl.modifyCustomer()">Enregistrer</div>
                    <div ng-if="customerCtrl.editionMode" class="btn btn_jaune" ng-click="customerCtrl.editionMode = false">Annuler</div>
                    <div ng-if="!customerCtrl.editionMode" class="btn btn_vert title_box dashicons dashicons-edit" ng-click="customerCtrl.editionMode = true">
                      <span class="title_box_content">Modifier les informations clients</span>
                    </div>
                    <div ng-if="!customerCtrl.isSynchronizedCaisseOnline() && backendCtrl.isCaisseOnlineActivated()" class="btn btn_vert" ng-click="customerCtrl.modifyCustomer()">
                      Synchroniser ce client sur Caisse-Online
                    </div>
                    <button ng-class="{'btn_bleu': customerCtrl.settings.notification_customer_accepted_account, 'btn_locked':!customerCtrl.settings.notification_customer_accepted_account}" class="btn" ng-disabled="!customerCtrl.settings.notification_customer_accepted_account" ng-if="customerCtrl.customer.askAccount" ng-click="customerCtrl.acceptAskAccount()">Accepter compte</button>
                    <span class="warning" ng-if="customerCtrl.customer.askAccount && !backendCtrl.settings.notification_customer_accepted_account">Vous ne pouvez pas accepter la demande de compte car la notification d'acceptation n'est pas activée</span>
                    <button class="btn btn_rouge" ng-if="customerCtrl.isDeletable()" ng-click="customerCtrl.deleteCustomerAction({
                      title: '<?php _e('ask_delete_customer_title_dialog','resa') ?>',
                      text: '<?php _e('ask_delete_customer_text_dialog','resa') ?>',
                      confirmButton: '<?php _e('ask_delete_customer_confirmButton_dialog','resa') ?>',
                      cancelButton: '<?php _e('ask_delete_customer_cancelButton_dialog','resa') ?>'
                    })"><span ng-if="!customerCtrl.customer.askAccount"><?php _e('delete_customer_link_title', 'resa'); ?></span><span ng-if="customerCtrl.customer.askAccount">Refuser compte</span></button>
                    <span ng-if="customerCtrl.isSynchronizedCaisseOnline()">Client synchronisé sur Caisse-Online</span>
                    <div class="btn btn_notify title_box dashicons dashicons-email-alt" ng-click="backendCtrl.openNotificationDialog(customerCtrl.customer)">
                      <span class="title_box_content">Envoyer une notification</span>
                    </div>
                  </div>
                  <span ng-if="!customerCtrl.isDeletable()">
                    <span class="helpbox">Non supprimable <span class="helpbox_content">Vous ne pouvez pas supprimer ce compte client car il possède des réservations ou que ce n'est pas un compte RESA Client.</span></span>
                  </span>
                </div>
                <div class="client_solde">
                  <?php if(defined('WP_DEBUG') && WP_DEBUG && defined('WP_RESA_DEBUG') && WP_RESA_DEBUG){ ?>
                    <input class="btn_wide" ng-class="{'btn_locked':customerCtrl.synchronizationCaisseOnlineAction, 'btn_vert':!customerCtrl.synchronizationCaisseOnlineAction}" ng-if="customerCtrl.isSynchronizedCaisseOnline()" type="button" ng-click="customerCtrl.clearSynchronizationCaisseOnline({
                      title: 'Supprimer ?',
                      text: 'Supprimer la synchronisation avec Caisse Online',
                      confirmButton: 'Oui',
                      cancelButton: 'Non'
                    })" value="[DEBUG]Supprimer synchronisation Caisse Online" />
                  <?php } ?>
                  <div ng-if="!customerCtrl.editionMode" class="client_balance" ng-class="{'balance_null':customerCtrl.balance==0, 'balance_negative':customerCtrl.balance<0, 'balance_positive':customerCtrl.balance>0}">
                    Solde client : {{ customerCtrl.round(customerCtrl.balance)|number:2 }}{{ customerCtrl.settings.currency }}
                  </div>
                  <div ng-if="!customerCtrl.editionMode && customerCtrl.isSynchronizedCaisseOnline()" class="client_balance" ng-class="{'balance_null':customerCtrl.getCaisseOnlineBalance() == 0, 'balance_negative':customerCtrl.getCaisseOnlineBalance() < 0, 'balance_positive':customerCtrl.getCaisseOnlineBalance() > 0}">
                    Solde client sur Caisse-Online : <span class="helpbox">{{ customerCtrl.getCaisseOnlineBalance()|number:2 }}{{ customerCtrl.settings.currency }}
                    <span class="helpbox_content">Une fois que toutes les réservations du client sont encaissées ce montant doit être égal à zéro !</span></span>
                  </div>
                </div>
                <div class="client_historic">
                  <h3 class="client_historic_title">
                    Historique
                    <a class="btn title_box"
                    ng-class="{'btn_locked':customerCtrl.getCaisseOnlinePaymentsLaunched, 'btn_vert':!customerCtrl.getCaisseOnlinePaymentsLaunched, 'dashicons dashicons-image-rotate':!customerCtrl.getCaisseOnlinePaymentsLaunched}"
                    ng-if="customerCtrl.isSynchronizedCaisseOnline()" ng-click="customerCtrl.getAnotherPayments()">
                    <span ng-if="!customerCtrl.getCaisseOnlinePaymentsLaunched" class="title_box_content">Charger les paiements CAISSE-Online</span>
                    <span ng-if="customerCtrl.getCaisseOnlinePaymentsLaunched" class="title_box_content">Chargement en cours</span>
                    </a>
                    <a class="btn title_box btn_jaune dashicons dashicons-image-rotate" ng-if="customerCtrl.isSynchronizedEmails()" ng-click="customerCtrl.forceProcessEmails()">
                     <span class="title_box_content">Forcer la récupération d'emails</span>
                    </a>
                  </h3>
                  <div id="historic_filter">
                    <div ng-class="{'active':customerCtrl.page == 0}" ng-click="customerCtrl.page = 0">Tous</div>
                    <div ng-class="{'active':customerCtrl.page == 1}" ng-click="customerCtrl.page = 1">Paiements</div>
                    <div ng-class="{'active':customerCtrl.page == 2}" ng-click="customerCtrl.page = 2">Notifications</div>
                    <div ng-class="{'active':customerCtrl.page == 3}" ng-click="customerCtrl.page = 3">Emails</div>
                  </div>
                  <div id="historic_content">
                    <table>
                      <thead>
                        <tr>
                          <th>Date</th>
                          <th>Type</th>
                          <th>Utilisateur</th>
                          <th>Réservation</th>
                          <th>Infos</th>
                          <th>Note</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr ng-if="customerCtrl.isDisplayed(line, <?php echo (defined('WP_DEBUG') && WP_DEBUG && defined('WP_RESA_DEBUG') && WP_RESA_DEBUG)?'true':'false'; ?>)" ng-repeat="line in customerCtrl.historic">
                          <td>
                            <span ng-if="line.isPayment">
                              {{ line.paymentDate | formatDateTime:'<?php echo $variables['date_format'].' '.$variables['time_format']; ?>' }}
                            </span>
                            <span ng-if="line.isLogNotification">
                              {{ line.creationDate | formatDateTime:'<?php echo $variables['date_format'].' '.$variables['time_format']; ?>' }}
                            </span>
                            <span ng-if="line.isEmailCustomer">
                              {{ line.creationDate | formatDateTime:'<?php echo $variables['date_format'].' '.$variables['time_format']; ?>' }}
                            </span>
                          </td>
                          <td>
                            <span ng-if="line.isPayment && line.isAskPayment">
                              demande de paiement <br />
                              <span ng-bind-html="customerCtrl.isAskPaymentState(payment)"></span>
                            </span>
                            <span ng-if="line.isPayment && !line.isAskPayment">
                              <span ng-if="line.isDeposit != null && line.isDeposit && line.value >= 0">Caution</span>
                              <span ng-if="line.isReceipt != null && line.isReceipt && line.value >= 0">Acompte</span>
                              <span ng-if="line.isReceipt != null && line.isReceipt && line.value < 0">Remboursement acompte</span>
                              <span ng-if="(line.isReceipt == null || !line.isReceipt) && !line.isDeposit">Encaissement</span>
                            </span>
                            <span ng-if="line.isLogNotification">Notification</span>
                            <span ng-if="line.isEmailCustomer">Email</span>
                          </td>
                          <td>
                            <span ng-if="line.idUserCreator >= 0">{{ backendCtrl.getCustomerManagerById(line.idUserCreator).displayName }}</span>
                            <span ng-if="line.idSender >= 0">{{ backendCtrl.getCustomerManagerById(line.idSender).displayName }}</span>
                            <span ng-if="line.vendor.length > 0">{{ line.vendor }} (Caisse)</span>
                          </td>
                          <td>
                            <a ng-if="(line.idBookings == null || line.idBookings.length == 0) && line.idBookingCreation != -1" ng-click="customerCtrl.openDisplayBooking(customerCtrl.getBookingIdCreationOrId(line.idBookingCreation))">{{ line.idBookingCreation }}</a>
                            <a ng-if="(line.idBookings == null || line.idBookings.length == 0) && line.idBookingCreation == null && line.idBooking != -1" ng-click="customerCtrl.openDisplayBooking(customerCtrl.getBookingIdCreationOrId(line.idBooking))">{{ line.idBooking }}</a>
                            <span ng-repeat="idBooking in line.idBookings track by $index">
                              <span ng-if="$index > 0">, </span>
                              <a ng-if="idBooking != -1" ng-click="customerCtrl.openDisplayBooking(customerCtrl.getBookingIdCreationOrId(idBooking))">{{ idBooking }}</a>
                            </span>
                          </td>
                          <td>
                            <span ng-if="line.isPayment && !line.isAskPayment">
                              <span ng-if="line.isDeposit != null && line.isDeposit && line.value >= 0">Caution</span>
                              <span ng-if="line.isReceipt != null && line.isReceipt && line.value >= 0">Acompte</span>
                              <span ng-if="line.isReceipt != null && line.isReceipt && line.value < 0">Remboursement acompte</span>
                              <span ng-if="(line.isReceipt == null || !line.isReceipt) && !line.isDeposit">Encaissement</span><br />
                              {{ customerCtrl.getPaymentName(line.type, line.name) }} <br />
                              <b>
                                Montant : <span ng-if="line.repayment">{{ line.value|negative }}{{ customerCtrl.settings.currency }}</span>
                                <span ng-if="!line.repayment">{{ line.value }}{{ customerCtrl.settings.currency }}</span>
                              </b>
                              <span ng-if="line.idReference.length > 0"><br />ref : {{ line.idReference }}</span>
                              <span ng-if="line.state == 'cancelled'"><br />Annulé</span>
                              <span ng-if="line.state == 'pending'"><br />En attente</span>
                            </span>
                            <span ng-if="line.isPayment && line.isAskPayment">{{ customerCtrl.getAllPaymentName(line.types) }}</span>
                          </td>
                          <td>
                            <span ng-if="line.isPayment && !line.isAskPayment" ng-bind-html="line.note|htmlSpecialDecode:true"></span>
                            <span ng-if="line.isPayment && line.isAskPayment">Demande de paiement <br />expire le <b>{{ line.expiredDate | formatDateTime:'<?php echo $variables['date_format'].' '.$variables['time_format']; ?>' }}</b></span>
                            <span ng-if="line.isLogNotification" ng-bind-html="line.text|htmlSpecialDecode:true"></span>
                            <span ng-if="line.isEmailCustomer" ng-bind-html="line.subject|htmlSpecialDecode:true"></span>
                          </td>
                          <td class="flex">
                            <a ng-if="line.isEmailCustomer" class="btn dashicons dashicons-admin-comments title_box" ng-click="customerCtrl.openEmailCustomer(line)" ><span class="title_box_content">Visualiser</span></a>
                            <input ng-if="line.isPayment && !line.isAskPayment && line.isCancellable" class="btn" ng-class="{'btn_rouge':line.isCancellable, 'btn_locked':!line.isCancellable}" type="button" ng-click="customerCtrl.cancelPaymentAction(line,{
                              title: '<?php _e('cancel_payment_title_dialog','resa') ?>',
                              text: '<?php _e('cancel_payment_text_dialog','resa') ?>',
                              confirmButton: '<?php _e('cancel_payment_confirmButton_dialog','resa') ?>',
                              cancelButton: '<?php _e('cancel_payment_cancelButton_dialog','resa') ?>'
                            });" value="Supprimer" />
                           <a ng-if="line.isEmailCustomer" class="btn btn_rouge dashicons dashicons-trash title_box" ng-click="customerCtrl.deleteEmailCustomerAction(line.id)"><span class="title_box_content">Supprimer</span></a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="client_reservations">
                  <h3>Réservation<span ng-if="customerCtrl.bookings.length > 0">s</span> du client
                    <select ng-if="customerCtrl.bookings.length > 1" ng-model="customerCtrl.bookingsOrderBy">
                      <option ng-selected="customerCtrl.bookingsOrderBy == 0" value="0">Trier par date d'enregistrement décroissante</option>
                      <option ng-selected="customerCtrl.bookingsOrderBy == 1" value="1">Trier par date d'enregistrement croissante</option>
                      <option ng-selected="customerCtrl.bookingsOrderBy == 2" value="2">Trier par date d'activité décroissante</option>
                      <option ng-selected="customerCtrl.bookingsOrderBy == 3" value="3">Trier par date d'activité croissante</option>
                    </select>
                    <span class="btn btn_vert" ng-click="backendCtrl.openEditBookingCustomer(customerCtrl.customer)">
                      Nouvelle réservation
                    </span>
                    <span ng-if="customerCtrl.isSynchronizedCaisseOnline()" ng-class="{'btn_locked':customerCtrl.synchronizationCaisseOnlineAction, 'btn_jaune':!customerCtrl.synchronizationCaisseOnlineAction}" class="btn" ng-click="customerCtrl.payBookingsCaisseOnline()">
                      Envoyer toutes les réservations sur la caisse
                    </span>
                  </h3>
                  <div class="resa_reservation {{ booking.cssPaymentState }}" ng-repeat="booking in customerCtrl.bookings | orderBy:customerCtrl.executeBookingsOrderBy:(customerCtrl.bookingsOrderBy==0 || customerCtrl.bookingsOrderBy==2)" ng-class="{'light': !backendCtrl.seeBookingDetails,'quote': booking.quotation, 'valid': !booking.quotation && booking.status=='ok', 'pending': !booking.quotation && booking.status=='waiting', 'cancelled': !booking.quotation && booking.status=='cancelled', 'abandonned': !booking.quotation && booking.status=='abandonned', 'inerror': false, 'incomplete': false}">
                    <div class="reservation_client">
                      <div class="resa_id">N°{{ backendCtrl.getBookingId(booking) }}</div>
                      <div class="client_name">
                        <span ng-if="booking.customer.lastName.length > 0" class="client_lastname">{{ booking.customer.lastName | htmlSpecialDecode }}</span>
                        <span ng-if="booking.customer.firstName.length > 0" class="client_firstname">{{booking.customer.firstName | htmlSpecialDecode }}</span>
                      </div>
                      <div class="client_company">
                        <span ng-if="booking.customer.company.length > 0">{{ booking.customer.company | htmlSpecialDecode }}</span>
                        <span ng-if="booking.customer.phone.length > 0"><span ng-if="booking.customer.company.length > 0"><br /></span>{{ booking.customer.phone | formatPhone }}</span>
                        <span ng-if="booking.customer.phone2.length > 0"><span ng-if="booking.customer.company.length > 0 || booking.customer.phone.length > 0"><br /></span>{{ booking.customer.phone2 | formatPhone }}</span>
                      </div>
                      <div ng-if="booking.customer.privateNotes.length > 0" class="client_note on b1 helpbox">
                        1 Note Client
                        <span class="helpbox_content"><h4>Note privé client :</h4><p ng-bind-html="booking.customer.privateNotes|htmlSpecialDecode:true"></p></span>
                      </div>
                    </div>
                    <div class="reservation_states">
                      <div class="states_state state_valid">Validée</div>
                      <div class="states_state state_quote">{{ backendCtrl.getQuotationName(booking) }}</div>
                      <div class="states_state state_cancelled">Annulée</div>
                      <div class="states_state state_abandonned">Abandonnée</div>
                      <div class="states_state state_pending">{{ backendCtrl.getWaitingName(booking) }}</div>
                      <div class="states_state state_inerror">Erreur</div>
                      <div class="states_state state_incomplete">Incomplète</div>
                      <div class="states_paiement" ng-class="{'helpbox':booking.payments.length > 0}">
                        <div class="state_paiement paiement_none">Pas de paiement</div>
                        <div class="state_paiement paiement_incomplete">
                          <span ng-if="!backendCtrl.isDeposit(booking)">Acompte</span>
                          <span ng-if="backendCtrl.isDeposit(booking)">Caution</span>
                        </div>
                        <div class="state_paiement paiement_done">Encaissée</div>
                        <div class="state_paiement paiement_overpaiement">Trop perçu</div>
                        <div class="state_paiement paiement_remboursement">Remboursement dû</div>
                        <div class="state_paiement paiement_remboursement_done">Remboursement complet</div>
                        <div class="helpbox_content" ng-if="booking.payments.length > 0">
                          <table class="table">
                            <thead>
                              <tr>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Type de paiement</th>
                                <th>Note</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr ng-if="payment.state != 'pending'" ng-repeat="payment in booking.payments">
                                <td>{{ payment.paymentDate | formatDateTime:'<?php echo $variables['date_format'].' '.$variables['time_format']; ?>' }}
                                <span class="ro-cancelled" ng-if="payment.state=='cancelled'"><br /><?php _e('cancelled_word', 'resa'); ?></span></td>
                                <td ng-if="payment.repayment">{{ payment.value|negative }}{{ customerCtrl.settings.currency }}</td>
                                <td ng-if="!payment.repayment">{{ payment.value }}{{ customerCtrl.settings.currency }}</td>
                                <td>
                                  <span ng-if="payment.isReceipt != null && payment.isReceipt && payment.value >= 0">[Acompte]</span>
                                  <span ng-if="payment.isReceipt != null && payment.isReceipt && payment.value < 0">[Remboursement acompte]</span>
                                  {{ customerCtrl.getPaymentName(payment.type, payment.name) }}
                                </td>
                                <td class="ng-binding">{{ payment.note }}</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <div class="states_paiement_value">
                        <span ng-if="booking.status != 'cancelled' && booking.status != 'abandonned'">
                          <span ng-if="booking.paymentsCaisseOnlineDone || (!backendCtrl.isCaisseOnlineActivated() && backendCtrl.round(booking.totalPrice - booking.needToPay) > 0)">{{ backendCtrl.round(booking.totalPrice - booking.needToPay) }}{{ backendCtrl.settings.currency }} / </span>
                          {{ booking.totalPrice }}{{ backendCtrl.settings.currency }}
                        </span>
                        <span ng-if="(booking.status == 'cancelled' || booking.status == 'abandonned') && booking.needToPay < 0 && (booking.paymentsCaisseOnlineDone || !backendCtrl.isCaisseOnlineActivated())">
                          {{ -(booking.needToPay - booking.totalPrice) }}{{ backendCtrl.settings.currency }}
                        </span>
                        <a ng-if="backendCtrl.isCaisseOnlineActivated()" ng-click="backendCtrl.getPaymentsForBooking(booking)">Paiements</a>
                      </div>
                    </div>
                    <div class="reservation_notes">
                      <?php if(!RESA_Variables::staffIsConnected()){ ?>
                      <div class="reservation_private_note" ng-class="{'on helpbox': booking.note.length > 0, 'off': booking.note.length == 0}">
                        Note privée
                        <span class="helpbox_content" ng-if="booking.note.length > 0"><h4>Note privée :</h4><p ng-bind-html="booking.note|htmlSpecialDecode:true"></p></span>
                      </div>
                      <div class="reservation_public_note" ng-class="{'on helpbox': booking.publicNote.length > 0, 'off': booking.publicNote.length == 0}">
                        Note publique
                        <span class="helpbox_content" ng-if="booking.publicNote.length > 0"><h4>Note publique :</h4><p ng-bind-html="booking.publicNote|htmlSpecialDecode:true"></p></span>
                      </div>
                      <div class="reservation_customer_note" ng-class="{'on helpbox': booking.customerNote.length > 0, 'off': booking.customerNote.length == 0}">
                        Remarque cliente
                        <span class="helpbox_content" ng-if="booking.customerNote.length > 0"><h4>Remarque cliente :</h4><p ng-bind-html="booking.customerNote|htmlSpecialDecode:true"></p></span>
                      </div>
                      <?php } ?>
                      <div class="reservation_member_note" ng-class="{'on helpbox': booking.staffNote.length > 0, 'off': booking.staffNote.length == 0}">
                        Note {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                        <span class="helpbox_content" ng-if="booking.staffNote.length > 0"><h4>Note {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} :</h4><p ng-bind-html="booking.staffNote|htmlSpecialDecode:true"></p></span>
                      </div>
                    </div>
                    <div class="reservation_suivi">
                      <div class="suivi_title">
                        <div class="title_date">Créée le </div>
                        <div class="title_time">à</div>
                        <div class="title_user"><?php _e('by_word', 'resa') ?></div>
                      </div>
                      <div class="suivi_content">
                        <div class="content_date">{{ booking.creationDate|formatDateTime:'<?php echo $variables['date_format']; ?>' }}</div>
                        <div class="content_time">{{ booking.creationDate|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</div>
                        <div class="content_user">{{ booking.userCreator }}</div>
                      </div>
                    </div>
                    <div class="reservation_actions">
                      <?php if(!RESA_Variables::staffIsConnected()){ ?>
                      <span class="btn btn_modifier title_box dashicons dashicons-edit" ng-click="backendCtrl.openEditBooking(booking)">
                        <span ng-if="!booking.quotation" class="title_box_content">Modifier la réservation</span>
                        <span ng-if="booking.quotation" class="title_box_content">Modifier le devis</span>
                      </span>
                      <span class="btn btn_modifier title_box dashicons dashicons-calendar-alt" ng-click="customerCtrl.loadBookingPeriod(booking)">
                       <span class="title_box_content">Charger les réservations de la période de cette réservation</span>
                      </span>
                      <span class="btn btn_notify title_box dashicons dashicons-email-alt" ng-click="backendCtrl.openNotificationDialog(customerCtrl.customer, booking)">
                        <span class="title_box_content">Envoyer une notification</span>
                      </span>
                      <span ng-if="!booking.quotation" class="btn btn_bleu btn_client ask_paiement_btn title_box dashicons dashicons-money" ng-click="backendCtrl.openNotificationDialog(customerCtrl.customer, booking, 'askPayment')">
                        <span class="title_box_content">Demander un paiement</span>
                      </span>
                      <span class="btn btn_client ask_paiement_btn title_box dashicons dashicons-cart" ng-if="backendCtrl.isCaisseOnlineActivated() && !booking.quotation" ng-click="backendCtrl.payBookingCaisseOnline(booking)">
                        <span class="title_box_content">Envoyer la réservation sur la caisse</span>
                      </span>
                      <span class="btn btn_client ask_paiement_btn title_box dashicons dashicons-update" ng-if="backendCtrl.isCaisseOnlineActivated() && !booking.quotation" ng-click="customerCtrl.calculateBookingPaymentState(booking)">
                        <span class="title_box_content">Mettre à jour état paiement</span>
                      </span>
              				<span class="btn btn_client title_box dashicons dashicons-tag" ng-if="!backendCtrl.isCaisseOnlineActivated() && !booking.quotation"
                      ng-click="backendCtrl.openPaymentStateDialog(booking);">
                        <span class="title_box_content">Changer l'état de paiement</span>
                      </span>
                      <span ng-if="!booking.quotation" class="btn btn_client ask_paiement_btn title_box" ng-click="backendCtrl.openPayBookingDialog(customerCtrl.customer, booking)">Payer
                        <span class="title_box_content">Payer la réservation</span>
                      </span>
                      <span class="btn btn_reciept title_box dashicons dashicons-format-aside" ng-click="backendCtrl.openReceiptBookingDialog(booking, customerCtrl.customer)">
                        <span ng-if="!booking.quotation" class="title_box_content">Reçu de la réservation</span>
                        <span ng-if="booking.quotation" class="title_box_content">Reçu du devis</span>
                      </span>
                      <?php } ?>
                    </div>
                    <div class="reservation_bookings">
                      <div class="booking" ng-class="{'confirmed':appointment.state == 'ok','not_confirmed':appointment.state == 'waiting','cancelled':appointment.state == 'cancelled' || appointment.state == 'abandonned'}" ng-repeat="appointment in booking.appointments|orderBy:'startDate'">
                        <div class="booking_date">{{ appointment.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }}</div>
                        <div class="booking_hour">
                          <span ng-if="!appointment.noEnd">
                            {{ appointment.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                            <?php _e('to_word', 'resa') ?>
                            {{ appointment.endDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                          </span>
                          <span ng-if="appointment.noEnd">
                            <?php _e('begin_word', 'resa'); ?> {{ appointment.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                          </span>
                        </div>
                        <div class="booking_nb_people" ng-class="{'helpbox': backendCtrl.getServiceById(appointment.idService).askParticipants}">
                          {{ appointment.numbers }} pers.
                          <span ng-if="backendCtrl.getServiceById(appointment.idService).askParticipants" class="helpbox_content"><h4>Participants</h4>
                            <span ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                              <table>
                								<thead>
                									<tr>
                										<td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">
                											{{ backendCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
                										</td>
                									</tr>
                								</thead>
                								<tbody>
                									<tr ng-repeat="participant in appointmentNumberPrice.participants track by $index"><td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">{{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</td></tr>
                								</tbody>
                                <tfoot  ng-if="backendCtrl.isDisplaySum(backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields)">
                                  <tr>
                                    <td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields"><span ng-if="field.displaySum">Somme : {{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getDisplaySum(field, appointmentNumberPrice.participants) }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</span></td>
                                  </tr>
                                </tfoot>
                							</table>
                            </span>
                          </span>
                        </div>
                        <div class="booking_states">
                          <div class="states_state state_confirmed">Confirmée</div>
                          <div class="states_state state_not_confirmed">Non confirmée</div>
                          <div class="states_state state_cancelled">Annulée</div>
                          <div class="states_state state_inerror">Erreur</div>
                          <div class="states_state state_incomplete">Incomplète</div>
                        </div>
                        <div class="booking_place"><span ng-if="appointment.idPlace != ''">{{ backendCtrl.getTextByLocale(backendCtrl.getPlaceById(appointment.idPlace).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span></div>
                        <div class="booking_service">{{ backendCtrl.getTextByLocale(
                              backendCtrl.getServiceById(appointment.idService).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                        <div class="booking_prices">
                          <div class="prices_price" ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                            <span class="price_nb">{{ appointmentNumberPrice.number }}</span>
                            <span class="price_name">
                              {{ backendCtrl.getServicePriceAppointmentName(appointment.idService, appointmentNumberPrice.idPrice) |htmlSpecialDecode
            									}}
                              <span ng-if="backendCtrl.haveEquipments(appointment.idService, appointmentNumberPrice.idPrice)">
                                ({{ backendCtrl.getServicePriceAppointmentEquipmentName(appointment.idService, appointmentNumberPrice.idPrice)|htmlSpecialDecode
              									}})
                              </span>
                            </span>
                            <span class="price_value" ng-if="!appointmentNumberPrice.deactivated">
                              <span ng-if="appointmentNumberPrice.totalPrice != 0">{{ appointmentNumberPrice.totalPrice }}  {{ backendCtrl.settings.currency }}</span>
                              <span ng-if="appointmentNumberPrice.totalPrice == 0">
                                <span ng-if="backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
                                  {{ backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).price }} {{ backendCtrl.settings.currency }}
                                </span>
                                <span ng-if="!backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
                                  {{ backendCtrl.getPriceNumberPrice(appointment.idService, appointmentNumberPrice) }} {{ backendCtrl.settings.currency }}
                                </span>
                              </span>
                            </span>
                          </div>
                        </div>
                        <div class="booking_members" ng-if="backendCtrl.settings.staffManagement">
                          <div class="member" ng-if="appointmentMember.number > 0" ng-repeat="appointmentMember in appointment.appointmentMembers">{{ backendCtrl.getMemberById(appointmentMember.idMember).nickname|htmlSpecialDecode }}<br /></div>
                        </div>
                        <div class="booking_tags">
                          <div ng-if="backendCtrl.getTagById(idTag)" class="tag {{ backendCtrl.getTagById(idTag).color }}" ng-repeat="idTag in appointment.tags">
                            {{ backendCtrl.getTextByLocale(backendCtrl.getTagById(idTag).title, '<?php echo get_locale(); ?>') }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="popup_footer">
              <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="customerCtrl.close()"><span class="title_box_content">Fermer</span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="loader_box" ng-show="customerCtrl.launchEditCustomer || backendCtrl.payBookingActionLaunched || customerCtrl.getCaisseOnlinePaymentsLaunched">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="resa_popup" id="resa_add_resa" ng-controller="EditBookingController as editBookingCtrl" ng-show="editBookingCtrl.opened">
      <div class="resa_popup_wrapper">
        <div class="resa_popup_content">
          <div class="popin" ng-controller="ParticipantSelectorController as participantSelectorCtrl" ng-show="participantSelectorCtrl.opened">
            <div class="popin_content">
              <h4>Sélectionner un participant déjà présent</h4>
              <div class="participants_informations_content">
                <div class="participant_information_titles">
                  <h5 class="input_wide" ng-repeat="field in participantSelectorCtrl.participantParametersFields track by $index">
                    {{ participantSelectorCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
                  </h5>
                </div>
                <div ng-click="participantSelectorCtrl.setParticipant(participant); participantSelectorCtrl.close();" class="participant_information" ng-repeat="participant in participantSelectorCtrl.participants track by $index">
                  <span ng-repeat="field in participantSelectorCtrl.participantParametersFields track by $index">
                    {{ participantSelectorCtrl.getTextByLocale(field.prefix, '<?php echo get_locale(); ?>')}}
                    {{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}
                    {{participantSelectorCtrl.getTextByLocale(field.suffix, '<?php echo get_locale(); ?>')}}
                  </span>
                </div>
              </div>
              <br />
              <div class="btn btn_jaune title_box dashicons dashicons-no" ng-click="participantSelectorCtrl.close()"><span class="title_box_content">Fermer</span></div>
            </div>
          </div>
          <div class="add_reservation">
            <div class="popup_header">
              <div class="resa_popup_header_wrapper">
                <div class="popup_header_title">
                  <h2 ng-if="editBookingCtrl.isNewBooking() && !editBookingCtrl.quotation">Ajouter une réservation</h2>
                  <h2 ng-if="!editBookingCtrl.isNewBooking() && !editBookingCtrl.quotation">Éditer une réservation</h2>
                  <h2 ng-if="editBookingCtrl.isNewBooking() && editBookingCtrl.quotation">Ajouter un devis</h2>
                  <h2 ng-if="!editBookingCtrl.isNewBooking() && editBookingCtrl.quotation">Éditer un devis</h2>
                </div>
                <div class="popup_header_menu">
                  <div ng-if="!editBookingCtrl.isNewBooking()" class="btn" ng-class="{'btn_vert':editBookingCtrl.formIsOkEditBooking(),'btn_locked':!editBookingCtrl.formIsOkEditBooking()}" ng-disabled="!editBookingCtrl.formIsOkEditBooking()" ng-click="editBookingCtrl.validForm()" ng-class="{'off':!editBookingCtrl.formIsOkEditBooking()}">Sauvegarder</div>
                  <div class="btn helpbox" ng-class="{'btn_bleu':editBookingCtrl.formIsOkEditBooking(),'btn_locked':!editBookingCtrl.formIsOkEditBooking()}" ng-disabled="!editBookingCtrl.formIsOkEditBooking()" ng-click="editBookingCtrl.openNotificationAfterRegister=true; editBookingCtrl.validForm()" ng-class="{'off':!editBookingCtrl.formIsOkEditBooking()}">Enregistrer et notifier <span class="helpbox_content">Enregistre la réservation et ouvre la page de notifications</span></div>
                  <div class="btn btn_jaune" ng-click="editBookingCtrl.close()">Fermer sans enregistrer</div>
                  <div class="btn" ng-class="{'btn_rouge':!editBookingCtrl.launchValidForm,'btn_locked':editBookingCtrl.launchValidForm}" ng-disabled="editBookingCtrl.launchValidForm" ng-if="!editBookingCtrl.isNewBooking()" ng-click="editBookingCtrl.deleteBooking({
                    title: '<?php _e('ask_delete_booking_title_dialog','resa') ?>',
                    text: '<?php _e('ask_delete_booking_text_dialog','resa') ?>',
                    confirmButton: '<?php _e('ask_delete_booking_confirmButton_dialog','resa') ?>',
                    cancelButton: '<?php _e('ask_delete_booking_cancelButton_dialog','resa') ?>'
                  });">Supprimer <span ng-if="!editBookingCtrl.quotation">la réservation</span><span ng-if="editBookingCtrl.quotation">le devis</span><span ng-if="editBookingCtrl.launchValidForm">...</span></div>
                </div>
              </div>
            </div>
            <div class="popup_content">
              <div class="reservation_content">
                Type :
                <div ng-click="editBookingCtrl.switchQuotation()" class="btn btn_bleu btn_switch_resa title_box" ng-class="{'active':editBookingCtrl.quotation}"> Devis<span class="title_box_content">Créer un nouveau devis</span></div>
                <div ng-click="editBookingCtrl.switchQuotation()" class="btn btn_vert btn_switch_resa title_box"ng-class="{'active':!editBookingCtrl.quotation}"> Réservation<span class="title_box_content">Créer une nouvelle réservation</span></div>
                <br /><br />
                <h2 class="add_booking_title" ng-if="!editBookingCtrl.isNewBooking() && !editBookingCtrl.quotation">Réservation n°{{ backendCtrl.getBookingId(editBookingCtrl.booking) }}</h2>
                <h2 class="add_booking_title" ng-if="editBookingCtrl.isNewBooking() && !editBookingCtrl.quotation">Nouvelle réservation </h2>
                <h2 class="add_booking_title" ng-if="!editBookingCtrl.isNewBooking() && editBookingCtrl.quotation">Devis n°{{ backendCtrl.getBookingId(editBookingCtrl.booking) }}</h2>
                <h2 class="add_booking_title" ng-if="editBookingCtrl.isNewBooking() && editBookingCtrl.quotation">Nouveau devis </h2>
                <p class="states_state state_abandonned" ng-if="editBookingCtrl.booking.status == 'abandonned'"><b>Réservation abandonnée : </b>Attention : Cette réservation a été abandonnée par le client avant l'étape de paiement. Si vous la modifiez son état ne sera plus abandonnée (Contactez le client pour faire la vente ou annuler la réservation).</p>

                <!-- Proposition modification des états -->
                <div style="text-align:center;">
                  <b>Etat : </b>
                  <div class="btn btn_vert title_box" title="Validée au planning" ng-click="editBookingCtrl.okBooking()">Valider<span class="title_box_content">Validée au planning</span></div> |
                  <div class="btn btn_jaune title_box" title="Non confirmée au planning" ng-click="editBookingCtrl.waitingBooking()">Non confirmé <span class="title_box_content">Non confirmée au planning</span></div>|
                  <div class="btn btn_cancel title_box" title="Cela annule toutes les lignes et passe la réservation en état annulée" ng-click="editBookingCtrl.cancelBooking()">Annuler <span class="title_box_content">Cela annule toutes les lignes et passe la réservation en état annulée</span></div>
                </div>

                <div class="resa_reservation {{ backendCtrl.calculateBookingPayment(editBookingCtrl.booking) }}" ng-class="{'quote': editBookingCtrl.quotation, 'valid': !editBookingCtrl.quotation && editBookingCtrl.booking.status=='ok', 'pending': !editBookingCtrl.quotation && editBookingCtrl.booking.status=='waiting', 'cancelled': !editBookingCtrl.quotation && editBookingCtrl.booking.status=='cancelled', 'abandonned': !editBookingCtrl.quotation && editBookingCtrl.booking.status=='abandonned', 'inerror': false, 'incomplete': false}">
                  <div class="reservation_client">
                    <div id="search_client_title"> Client <span ng-if="editBookingCtrl.customer == null" class="helpbox"><span class="helpbox_content">Recherchez un client existant dans le champs ci-dessous (3 caractères minimums) et sélectionnez-le dans la liste ou ajouter un nouveau client en renseignant ses informations.</span></span>
                    </div>
                    <div class="important" ng-if="editBookingCtrl.customer == null">Rechercher ou créer un nouveau client </div>
                    <span ng-click="editBookingCtrl.displayPopupCustomer = true" class="btn btn_vert title_box dashicons dashicons-plus" ng-if="editBookingCtrl.customer == null"><span class="title_box_content">Créer un nouveau client...</span></span>
                    <span ng-click="editBookingCtrl.editCustomer()" class="btn btn_jaune title_box dashicons dashicons-edit" ng-if="editBookingCtrl.customer != null"><span class="title_box_content">Modifier la fiche client</span></span>
                    <span class="btn btn_bleu title_box dashicons dashicons-search" ng-click="editBookingCtrl.customer = null" ng-if="editBookingCtrl.customer != null"><span class="title_box_content">Rechercher un autre client</span></span>
                    <input ng-if="editBookingCtrl.customer == null" ng-change="editBookingCtrl.searchCustomersAction()" class="search_client_input" ng-model="editBookingCtrl.filterCustomers" ng-model-options="{ debounce: 800 }" type="search" placeholder="Nom, Prénom, Entreprise, Téléphone, email..." ng-disabled="editBookingCtrl.searchCustomersLaunched" />
                    <div id="client_results">
                      <div class="client_results_content" ng-if="editBookingCtrl.displayedCustomers.length && editBookingCtrl.customer == null">
                        <div class="client_results_title" ng-if="editBookingCtrl.displayedCustomers.length && editBookingCtrl.customer == null">
                          <span class="lastname_title">Nom</span>
                          <span class="firstname_title">Prénom</span>
                          <span class="email_title">Email</span>
                          <span class="company_title">Entreprise</span>
                          <span class="phone_title">Téléphone</span>
                          <span class="type_account_title">Type de compte</span>
                        </div>
                        <div ng-if="editBookingCtrl.customer == null" class="client_result" ng-class="{'active':editBookingCtrl.customer.ID == customer.ID}" ng-click="editBookingCtrl.setCustomer(customer)" title="Cliquez pour sélectionner ce client" ng-repeat="customer in editBookingCtrl.displayedCustomers">
                          <span class="lastname">
                            {{ customer.lastName | htmlSpecialDecode}}
                          </span>
                          <span class="firstname">{{ customer.firstName | htmlSpecialDecode }}</span>
                          <span class="email">{{ customer.email }}</span>
                          <span class="company">{{ customer.company | htmlSpecialDecode }}</span>
                          <span class="phone">{{ customer.phone|formatPhone }} <span ng-if="customer.phone2.length > 0"> / {{ customer.phone2|formatPhone }}</span></span>
                          <span class="client_account_info">
                            <span class="companyAccount">
                              {{ backendCtrl.getTypeAccountName(customer) }}<i ng-if="customer.idFacebook.length > 0">(Facebook)</i><br />
                              <i ng-if="!customer.isWpUser && !customer.askAccount" class="isWpUser">Pas de compte sur le site<br /></i>
                              <i ng-if="customer.askAccount" class="isWpUser" style="color:#00BFFF">Demande de compte</i>
                            </span>
                          </span>
                        </div>
                      </div>
                    </div>
                    <form class="add_new_client" ng-if="editBookingCtrl.displayPopupCustomer">
                      <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="editBookingCtrl.closePopupCustomer()"></div>
                      <h4 ng-if="editBookingCtrl.skeletonCustomer.ID == -1" class="add_new_client_title">Ajouter un nouveau client</h4>
                      <h4 ng-if="editBookingCtrl.skeletonCustomer.ID > -1" class="add_new_client_title">Editer le client</h4>
                      <input type="text" placeholder="Nom" id="lastname" ng-model="editBookingCtrl.skeletonCustomer.lastName" />
                      <input type="text" placeholder="Prénom" id="firstname" ng-model="editBookingCtrl.skeletonCustomer.firstName" />
                      <input type="email" placeholder="Email" id="email" ng-model="editBookingCtrl.skeletonCustomer.email" />
                      <input type="text" placeholder="Entreprise" ng-model="editBookingCtrl.skeletonCustomer.company" />
                      <input type="text" placeholder="Téléphone" ng-model="editBookingCtrl.skeletonCustomer.phone" />
                      <input type="text" placeholder="Téléphone 2" ng-model="editBookingCtrl.skeletonCustomer.phone2" />
                      <input class="add_new_client_adress" type="text" placeholder="Adresse" ng-model="editBookingCtrl.skeletonCustomer.address" />
                      <input type="text" placeholder="Ville" ng-model="editBookingCtrl.skeletonCustomer.town" />
                      <input type="text" placeholder="Code postal" ng-model="editBookingCtrl.skeletonCustomer.postalCode" />
                      <select ng-model="editBookingCtrl.skeletonCustomer.country">
                        <option value="" disabled selected>-- Pays --</option>
                        <option ng-repeat="country in backendCtrl.countries" value="{{ country.code }}">{{ country.name }}</option>
                      </select>
                      <select ng-model="editBookingCtrl.skeletonCustomer.locale">
                        <option value="" disabled selected>-- Langue --</option>
                        <option ng-repeat="language in backendCtrl.settings.languages" value="{{ language }}">{{ backendCtrl.settings.allLanguages[language][0] }}</option>
                      </select>
                      <input type="text" placeholder="SIRET" ng-model="editBookingCtrl.skeletonCustomer.siret" />
                      <input type="text" placeholder="Forme juridique" ng-model="editBookingCtrl.skeletonCustomer.legalForm" />
                      <div class="company_account">
                        <label for="company_account_customers">Type de compte</label>
                        <select id="company_account_customers" ng-model="editBookingCtrl.skeletonCustomer.typeAccount" ng-options="typeAccount.id as (editBookingCtrl.getTextByLocale(typeAccount.name)|htmlSpecialDecode) for typeAccount in editBookingCtrl.settings.typesAccounts"></select>
                      </div>
                      <div ng-if="!editBookingCtrl.skeletonCustomer.isWpUser" class="wp_account">
                        <input id="wp_account_booking" type="checkbox" ng-model="editBookingCtrl.skeletonCustomer.createWpAccount" />
                        <label for="wp_account_booking">Créer un compte sur le site (email nécessaire)</label>
                      </div>
                      <div ng-if="editBookingCtrl.skeletonCustomer.ID == -1 && editBookingCtrl.skeletonCustomer.createWpAccount" class="notify_wp_account">
                        <input id="notify_wp_account_booking" type="checkbox" ng-model="editBookingCtrl.skeletonCustomer.notify" />
                        <label for="notify_wp_account_booking">Notifier le client par email</label>
                      </div>
                      <input ng-if="editBookingCtrl.skeletonCustomer.ID == -1" ng-disabled="!editBookingCtrl.isOkCustomer()" ng-click="editBookingCtrl.createCustomer()" class="add_new_client_btn btn btn_vert" type="button" value="Créer le client" />
                      <input ng-if="editBookingCtrl.skeletonCustomer.ID > -1" ng-disabled="!editBookingCtrl.isOkCustomer()" ng-click="editBookingCtrl.createCustomer()" class="add_new_client_btn btn btn_vert" type="button" value="Editer le client" />
                      <b class="warning" ng-if="editBookingCtrl.skeletonCustomer.createWpAccount && (editBookingCtrl.skeletonCustomer.email == null || editBookingCtrl.skeletonCustomer.email.length == 0)">Veuillez renseigner une adresse email correcte !<br /></b>
                      <b class="warning" ng-if="!editBookingCtrl.skeletonCustomer.createWpAccount && (editBookingCtrl.skeletonCustomer.phone == null || editBookingCtrl.skeletonCustomer.phone.length == 0)">Veuillez renseigner un numéro de téléphone !<br /></b>
                    </form>
                    <div class="client_name">
                      <span ng-if="editBookingCtrl.customer.lastName.length > 0" class="client_lastname">{{ editBookingCtrl.customer.lastName | htmlSpecialDecode }}</span>
                      <span ng-if="editBookingCtrl.customer.firstName.length > 0" class="client_firstname">{{editBookingCtrl.customer.firstName | htmlSpecialDecode }}</span>
                    </div>
                    <div class="client_company">
                      <span ng-if="editBookingCtrl.customer.company.length > 0">{{ editBookingCtrl.customer.company | htmlSpecialDecode }}</span>
                      <span ng-if="editBookingCtrl.customer.phone.length > 0"><span ng-if="editBookingCtrl.customer.company.length > 0"><br /></span>{{ editBookingCtrl.customer.phone | formatPhone }}</span>
                      <span ng-if="editBookingCtrl.customer.phone2.length > 0"><span ng-if="editBookingCtrl.customer.company.length > 0 || editBookingCtrl.customer.phone.length > 0"><br /></span>{{ editBookingCtrl.customer.phone2 | formatPhone }}</span>
                    </div>
                    <div ng-if="editBookingCtrl.customer.privateNotes.length > 0" class="client_note on b1 helpbox">
                      1 Note Client
                      <span class="helpbox_content"><h4>Note privé client :</h4><p ng-bind-html="editBookingCtrl.customer.privateNotes|htmlSpecialDecode:true"></p></span>
                    </div>
                  </div>
                  <div class="reservation_states">
                    <div class="states_state state_valid">Validée</div>
                    <div class="states_state state_quote"><span ng-if="editBookingCtrl.isNewBooking()">Devis</span><span ng-if="!editBookingCtrl.isNewBooking()">{{ backendCtrl.getQuotationName(editBookingCtrl.booking) }}</span></div>
                    <div class="states_state state_cancelled">Annulée</div>
                    <div class="states_state state_abandonned">Abandonnée</div>
                    <div class="states_state state_pending"><span ng-if="editBookingCtrl.isNewBooking()">Non confirmée</span><span ng-if="!editBookingCtrl.isNewBooking()">{{ backendCtrl.getWaitingName(editBookingCtrl.booking) }}</span></div>
                    <div class="states_state state_inerror">Erreur</div>
                    <div class="states_state state_incomplete">Incomplète</div>
                    <div class="states_paiement" ng-if="!editBookingCtrl.isNewBooking()">
                      <div class="state_paiement paiement_none">Pas de paiement</div>
                      <div class="state_paiement paiement_incomplete">
                        <span ng-if="!backendCtrl.isDeposit(editBookingCtrl.booking)">Acompte</span>
                        <span ng-if="backendCtrl.isDeposit(editBookingCtrl.booking)">Caution</span>
                      </div>
                      <div class="state_paiement paiement_done">Encaissée</div>
                      <div class="state_paiement paiement_overpaiement">Trop perçu</div>
                      <div class="state_paiement paiement_remboursement">Remboursement dû</div>
                      <div class="state_paiement paiement_remboursement_done">Remboursement complet</div>
                    </div>
                    <div class="states_paiement_value" ng-if="!editBookingCtrl.isNewBooking()">
                      <span ng-if="editBookingCtrl.booking.status != 'cancelled' && editBookingCtrl.booking.status != 'abandonned'">
                        <span ng-if="editBookingCtrl.booking.paymentsCaisseOnlineDone || (!backendCtrl.isCaisseOnlineActivated() && backendCtrl.round(editBookingCtrl.booking.totalPrice - editBookingCtrl.booking.needToPay) > 0)">{{ backendCtrl.round(editBookingCtrl.booking.totalPrice - editBookingCtrl.booking.needToPay) }}{{ backendCtrl.settings.currency }} / </span>
                        {{ editBookingCtrl.booking.totalPrice }}{{ backendCtrl.settings.currency }}
                      </span>
                      <span ng-if="(editBookingCtrl.booking.status == 'cancelled' || editBookingCtrl.booking.status == 'abandonned') && editBookingCtrl.booking.needToPay < 0 && (editBookingCtrl.booking.paymentsCaisseOnlineDone || !backendCtrl.isCaisseOnlineActivated())">
                        {{ -(editBookingCtrl.booking.needToPay - editBookingCtrl.booking.totalPrice) }}{{ backendCtrl.settings.currency }}
                      </span>
                      <a ng-if="backendCtrl.isCaisseOnlineActivated()" ng-click="backendCtrl.getPaymentsForBooking(editBookingCtrl.booking)">Paiements</a>
                    </div>
                  </div>
                  <div class="reservation_notes">
                    <?php if(!RESA_Variables::staffIsConnected()){ ?>
                    <div class="reservation_private_note" ng-class="{'on helpbox': editBookingCtrl.bookingNote.length > 0, 'off': editBookingCtrl.bookingNote.length == 0}">
                      Note privée
                      <span class="helpbox_content" ng-if="editBookingCtrl.bookingNote.length > 0"><h4>Note privée : </h4><p ng-bind-html="editBookingCtrl.bookingNote|htmlSpecialDecode:true"></p></span>
                    </div>
                    <div class="reservation_public_note" ng-class="{'on helpbox': editBookingCtrl.bookingPublicNote.length > 0, 'off': editBookingCtrl.bookingPublicNote.length == 0}">
                      Note publique
                      <span class="helpbox_content" ng-if="editBookingCtrl.bookingPublicNote.length > 0"><h4>Note publique :</h4><p ng-bind-html="editBookingCtrl.bookingPublicNote|htmlSpecialDecode:true"></p></span>
                    </div>
                    <div class="reservation_customer_note" ng-class="{'on helpbox': editBookingCtrl.bookingCustomerNote.length > 0, 'off': editBookingCtrl.bookingCustomerNote.length == 0}">
                      Remarque cliente
                      <span class="helpbox_content" ng-if="editBookingCtrl.bookingCustomerNote.length > 0"><h4>Remarque cliente :</h4><p ng-bind-html="editBookingCtrl.bookingCustomerNote|htmlSpecialDecode:true"></p></span>
                    </div>
                    <?php } ?>
                    <div ng-if="editBookingCtrl.settings.staffManagement" class="reservation_member_note" ng-class="{'on helpbox': editBookingCtrl.bookingStaffNote.length > 0, 'off': editBookingCtrl.bookingStaffNote.length == 0}">
                      Note {{ editBookingCtrl.getTextByLocale(editBookingCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                      <span class="helpbox_content" ng-if="editBookingCtrl.bookingStaffNote.length > 0"><h4>Note des {{ editBookingCtrl.getTextByLocale(editBookingCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} :</h4><p ng-bind-html="editBookingCtrl.bookingStaffNote|htmlSpecialDecode:true"></p></span>
                    </div>
                    <div ng-if="appointmentsCtrl.settings.staffManagement" class="reservation_member_note" ng-class="{'on helpbox': editBookingCtrl.booking.staffNote.length > 0, 'off': editBookingCtrl.booking.staffNote.length == 0}">
                      Note {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                      <span class="helpbox_content" ng-if="editBookingCtrl.booking.staffNote.length > 0"><h4>Note des {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} de la réservation :</h4><p ng-bind-html="editBookingCtrl.booking.staffNote|htmlSpecialDecode:true"></p></span>
                    </div>
                  </div>
                  <div class="reservation_suivi" ng-if="!editBookingCtrl.isNewBooking()">
                    <div class="suivi_title">
                      <div class="title_date">Créée le </div>
                      <div class="title_time">à</div>
                      <div class="title_user"><?php _e('by_word', 'resa') ?></div>
                    </div>
                    <div class="suivi_content">
                      <div class="content_date">{{ editBookingCtrl.booking.creationDate|formatDateTime:'<?php echo $variables['date_format']; ?>' }}</div>
                      <div class="content_time">{{ editBookingCtrl.booking.creationDate|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</div>
                      <div class="content_user">{{ editBookingCtrl.booking.userCreator }}</div>
                    </div>
                  </div>
                  <div class="reservation_actions">
                  </div>
                  <div class="reservation_bookings">
                    <div ng-click="editBookingCtrl.modifyServiceParameters(serviceParameters)" class="content_line" ng-repeat="serviceParameters in editBookingCtrl.getAllServicesParameters()|orderBy:'dateStart'">
                      <div class="booking" ng-class="{'editing': editBookingCtrl.displayEditMode && serviceParameters.id == editBookingCtrl.serviceParameters.id, 'confirmed':(serviceParameters.isOk() && !editBookingCtrl.displayEditMode) || (editBookingCtrl.serviceParameters.isOk() && editBookingCtrl.displayEditMode && serviceParameters.id == editBookingCtrl.serviceParameters.id) || (serviceParameters.isOk() && editBookingCtrl.displayEditMode && serviceParameters.id != editBookingCtrl.serviceParameters.id),'not_confirmed':(serviceParameters.isWaiting() && !editBookingCtrl.displayEditMode) || (editBookingCtrl.serviceParameters.isWaiting() && editBookingCtrl.displayEditMode && serviceParameters.id == editBookingCtrl.serviceParameters.id) || (serviceParameters.isWaiting() && editBookingCtrl.displayEditMode && serviceParameters.id != editBookingCtrl.serviceParameters.id),'cancelled':((serviceParameters.isCancelled() || serviceParameters.isAbandonned()) && !editBookingCtrl.displayEditMode) || ((editBookingCtrl.serviceParameters.isCancelled() || editBookingCtrl.serviceParameters.isAbandonned()) && editBookingCtrl.displayEditMode && serviceParameters.id == editBookingCtrl.serviceParameters.id) || ((serviceParameters.isCancelled() || serviceParameters.isAbandonned()) && editBookingCtrl.displayEditMode && serviceParameters.id != editBookingCtrl.serviceParameters.id)}">
                        <div class="booking_date">{{ serviceParameters.dateStart|formatDate:'<?php echo $variables['date_format']; ?>' }}</div>
                        <div class="booking_hour">
                          <span ng-if="!serviceParameters.noEnd">
                            {{ serviceParameters.dateStart|formatDate:'<?php echo $variables['time_format']; ?>' }}
                            <?php _e('to_word', 'resa') ?>
                            {{ serviceParameters.dateEnd|formatDate:'<?php echo $variables['time_format']; ?>' }}
                          </span>
                          <span ng-if="serviceParameters.noEnd">
                            <?php _e('begin_word', 'resa'); ?> {{ serviceParameters.dateStart|formatDate:'<?php echo $variables['time_format']; ?>' }}
                          </span>
                        </div>
                        <div class="booking_nb_people" ng-class="{'helpbox':serviceParameters.service.askParticipants}">
                          {{ serviceParameters.getNumber() }} pers.
                          <span ng-if="serviceParameters.service.askParticipants" class="helpbox_content"><h4>Participants</h4>
                            <span ng-repeat="numberPrice in serviceParameters.numberPrices">
                              <table>
                                <thead>
                                  <tr>
                                    <td ng-repeat="field in editBookingCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields">
                                      {{ editBookingCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
                                    </td>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr ng-repeat="participant in numberPrice.participants track by $index"><td ng-repeat="field in editBookingCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields">{{ editBookingCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{ editBookingCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</td></tr>
                                </tbody>
                                <tfoot ng-if="backendCtrl.isDisplaySum(editBookingCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields)">
                                  <tr>
                                    <td ng-repeat="field in editBookingCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields"><span ng-if="field.displaySum">Somme : {{ editBookingCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getDisplaySum(field, numberPrice.participants) }}{{ editBookingCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</span></td>
                                  </tr>
                                </tfoot>
                              </table>
                            </span>
                          </span>
                        </div>
                        <div class="booking_states">
                          <div class="states_state state_confirmed">Confirmée</div>
                          <div class="states_state state_not_confirmed">Non confirmée</div>
                          <div class="states_state state_cancelled">Annulée</div>
                          <div class="states_state state_inerror">Erreur</div>
                          <div class="states_state state_incomplete">Incomplète</div>
                        </div>
                        <div class="booking_place"><span ng-if="serviceParameters.idPlace != ''">{{ editBookingCtrl.getTextByLocale(editBookingCtrl.getPlaceById(serviceParameters.place).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span></div>
                        <div class="booking_service">
                          {{ editBookingCtrl.getTextByLocale(serviceParameters.service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                        </div>
                        <div class="booking_prices">
                          <div class="prices_price" ng-repeat="numberPrice in serviceParameters.numberPrices">
                            <span class="price_nb">{{ numberPrice.number }}</span>
                            <span class="price_name">
                              {{ editBookingCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode
                              }}
                              <span ng-if="numberPrice.price.equipments.length > 0">
                                ({{ editBookingCtrl.getTextByLocale(editBookingCtrl.getEquipmentById((numberPrice.price.equipments[0])).name,'<?php echo get_locale(); ?>')|htmlSpecialDecode
              									}})
                              </span>
                            </span>
                            <span class="price_value" ng-if="serviceParameters.canCalculatePrice(numberPrice)">
                              <span ng-if="numberPrice.price.notThresholded">{{ editBookingCtrl.round(numberPrice.price.price * numberPrice.number) }}</span>
                              <span ng-if="!numberPrice.price.notThresholded">{{ editBookingCtrl.round(serviceParameters.getPriceNumberPrice(numberPrice)) }}</span> {{ editBookingCtrl.settings.currency }}
                            </span>
                          </div>
                        </div>
                        <div class="booking_members">
                          <div class="member"ng-if="editBookingCtrl.settings.staffManagement && !editBookingCtrl.settings.groupsManagement" ng-bind-html="serviceParameters.displayStaffs()"></div>
                        </div>
                        <div class="booking_tags">
                          <div class="tag {{ editBookingCtrl.getTagById(idTag).color }}" ng-repeat="idTag in serviceParameters.tags">
                            {{ editBookingCtrl.getTextByLocale(editBookingCtrl.getTagById(idTag).title, '<?php echo get_locale(); ?>') }}
                          </div>
                        </div>
                        <div class="booking_total">{{ serviceParameters.getPriceWithDefaultSlugServicePrice() }}{{editBookingCtrl.settings.currency}}</div>
                        <div class="booking_edit" ng-if="editBookingCtrl.displayEditMode && serviceParameters.id == editBookingCtrl.serviceParameters.id">
                          <div class="booking_edit_content">
                            <div class="recap_menu_actions_top">
                              <h3>Vous êtes en train de modifier la réservation <span class="helpbox"><span class="helpbox_content"> Modifiez les éléments de cette ligne de la réservation à l'aide des paramètres ci-dessous. <br />Utilisez les boutons :<br />"Valider" pour valider les modifications<br />"Annuler" pour annuler les modifications (celles-ci seront perdues)<br />"Supprimer" pour supprimer cette ligne.</span></span></h3>
                              <label ng-if="editBookingCtrl.serviceParameters.isCombined()">
                                <input type="checkbox" ng-model="editBookingCtrl.synchronizeWeek" />
                                Synchroniser au autre rendez-vous associé (semaine, groupes de dates) (Pour changer de semaine ou groupe veuillez refaire la réservation)
                              </label>
                              <div class="recap_menu_actions">
                                <div class="btn btn_wide" ng-disabled="!editBookingCtrl.canAddInBasket()" ng-click="!editBookingCtrl.canAddInBasket() || editBookingCtrl.addCurrentServiceInBasket()" ng-class="{'btn_vert':editBookingCtrl.canAddInBasket(), 'btn_locked':!editBookingCtrl.canAddInBasket()}">Valider</div>
                                <div class="btn btn_jaune btn_wide" ng-click="editBookingCtrl.cancel($event)">Annuler</div>
                                <div class="btn btn_rouge btn_wide" ng-click="editBookingCtrl.removeServiceParameters(serviceParameters)">Supprimer</div>
                              </div>
                            </div>
                            <div class="col_place_service">
                              <div class="edit_place edit_bloc" ng-if="editBookingCtrl.needSelectPlace()">
                                <h3>Lieu</h3>
                                <div class="infos" ng-if="editBookingCtrl.serviceParameters.place == ''">Veuillez sélectionner un lieu</div>
                                <div class="edit_places">
                                  <div class="place" ng-click="editBookingCtrl.changePlace(place)" ng-class="{'active':editBookingCtrl.serviceParameters.place == place.id}" ng-repeat="place in editBookingCtrl.getPlaces() track by $index"> {{ editBookingCtrl.getTextByLocale(place.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                                </div>
                              </div>
                              <div class="edit_service edit_bloc" ng-if="editBookingCtrl.serviceParameters.place != '' || !editBookingCtrl.needSelectPlace()">
                                <h3><?php _e('Services_word', 'resa'); ?></h3>

                                <div class="edit_services">
                                  <div class="service" ng-click="editBookingCtrl.changeService(service)" ng-class="{'active':editBookingCtrl.serviceParameters.service.id == service.id}" ng-repeat="service in editBookingCtrl.getServicesByPlace(editBookingCtrl.serviceParameters.place)"> {{ editBookingCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                                </div>
                                <div class="infos" ng-if="editBookingCtrl.serviceParameters.service == null">Veuillez sélectionner un service</div>
                              </div>
                            </div>
                            <div class="col_date">
                              <div class="edit_date edit_bloc">
                                <h3>Date :</h3>
                                <div class="several_date">
                                  <input id="several_date" ng-change="editBookingCtrl.clearDates()" type="checkbox" ng-model="editBookingCtrl.manyTimeslots" />
                                  <label for="several_date">Sélectionner plusieurs créneaux</label>
                                  <span class="helpbox"><span class="helpbox_content">Cocher cette case pour répéter une réservation sur plusieurs dates. Cela génèrera plusieurs rendez-vous dans cette réservation lorsque vous l'enregistrerai. Vous pourrez modifier chaque rendez-vous individuellement par la suite.</span></span>
                                </div>
                                <div class="date_creneaux">
                                  <div class="creneaux_date_before_title" ng-if="!editBookingCtrl.serviceParameters.isCombined() || !editBookingCtrl.synchronizeWeek">
                                    <div class="arrow_left btn" ng-click="editBookingCtrl.decrementDate()"> &larr;</div>
                                    <div class="jmoinsun">j-1</div>
                                  </div>
                                  <div class="creneaux_date_title" ng-if="!editBookingCtrl.serviceParameters.isCombined() || !editBookingCtrl.synchronizeWeek">
                                    <input ng-change="editBookingCtrl.changeCurrentDate()" type="date" ng-model="editBookingCtrl.currentDate" />
                                  </div>
                                  <div class="creneaux_date_after_title" ng-if="!editBookingCtrl.serviceParameters.isCombined() || !editBookingCtrl.synchronizeWeek">
                                    <div class="jplusun">j+1</div>
                                    <div class="arrow_right btn" ng-click="editBookingCtrl.incrementDate()"> &rarr;</div>
                                  </div>
                                  <div ng-class="{'creneaux_date_before':$index==0, 'creneaux_date':$index==1, 'creneaux_date_after':$index==2}" ng-repeat="date in editBookingCtrl.dates">
                                    <div ng-click="editBookingCtrl.selectTimeslot(timeslot)" class="creneau {{ timeslot.getClass(editBookingCtrl.serviceParameters.dateStart, editBookingCtrl.serviceParameters.dateEnd, editBookingCtrl.serviceParameters.getNumber()) }}" ng-class="{'active':editBookingCtrl.getIndexTimeslot(timeslot)!=-1 || timeslot.isSameDates(editBookingCtrl.serviceParameters.dateStart, editBookingCtrl.serviceParameters.dateEnd)}" ng-repeat="timeslot in editBookingCtrl.timeslots[date.getTime()]|orderBy:'dateEnd'">
                                      <p class="creneau_time">
                                        <span ng-if="timeslot.isCustom">Personnalisé<br /></span>
                                        <span ng-if="!timeslot.noEnd">
                                          {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }} <?php _e('to_word', 'resa'); ?> {{ timeslot.dateEnd|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                        </span>
                                        <span ng-if="timeslot.noEnd">
                                          <?php _e('begin_word', 'resa'); ?>
                                          {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                        </span>
                                      </p>
                                      <p class="creneau_space">
                                        <span ng-if="!timeslot.isSameDates(editBookingCtrl.serviceParameters.dateStart, editBookingCtrl.serviceParameters.dateEnd)">
                                        {{ timeslot.getCapacity() }}/{{ timeslot.getMaxCapacity() }} <?php _e('positions_word', 'resa'); ?>
                                        </span>
                                        <span ng-if="timeslot.isSameDates(editBookingCtrl.serviceParameters.dateStart, editBookingCtrl.serviceParameters.dateEnd)">
                                        {{ timeslot.getCapacity() - editBookingCtrl.serviceParameters.getNumber() }}/{{ timeslot.getMaxCapacity() }} <?php _e('positions_word', 'resa'); ?>
                                        </span>
                                      </p>
                                    </div>
                                    <div ng-show="editBookingCtrl.timeslots[date.getTime()]==null && editBookingCtrl.isProgressLoad" class="loader_box">
                                      <div class="loader_content">
                                        <div class="loader">
                                          <div class="shadow"></div>
                                          <div class="box"></div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="infos" ng-if="!editBookingCtrl.serviceParameters.isDatesSet()">Veuillez sélectionner un creneau</div>
                                <div class="creneau_recap" ng-if="editBookingCtrl.manyTimeslots && editBookingCtrl.serviceParameters.isDatesSet()">
                                  <h4>Creneaux sélectionnés</h4>
                                  <ul>
                                    <li>
                                      {{ editBookingCtrl.serviceParameters.dateStart|formatDateTime:'<?php echo $variables['date_format']; ?>' }} :
                                      <span ng-if="!editBookingCtrl.serviceParameters.noEnd">
                                        {{ editBookingCtrl.serviceParameters.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }} <?php _e('to_word', 'resa'); ?> {{ editBookingCtrl.serviceParameters.dateEnd|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                      </span>
                                      <span ng-if="editBookingCtrl.serviceParameters.noEnd">
                                        <?php _e('begin_word', 'resa'); ?>
                                        {{ editBookingCtrl.serviceParameters.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                      </span>
                                    </li>
                                    <li ng-repeat="timeslot in editBookingCtrl.selectedTimeslots">
                                      {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['date_format']; ?>' }} :
                                      <span ng-if="!timeslot.noEnd">
                                        {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }} <?php _e('to_word', 'resa'); ?> {{ timeslot.dateEnd|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                      </span>
                                      <span ng-if="timeslot.noEnd">
                                        <?php _e('begin_word', 'resa'); ?>
                                        {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                      </span>
                                    </li>
                                  </ul>
                                  <input class="btn" type="button" ng-click="editBookingCtrl.unselectDates()" value="Tout désélectionner" />
                                </div>
                                <div class="creneau_custom">
                                  <h4>Créer un créneau le {{ editBookingCtrl.currentDate|formatDateTime:'<?php echo $variables['date_format']; ?>' }}</h4>
                                  <div class="creneau_custom_debut">Début
                                    <input class="input_nb" type="number" ng-model="editBookingCtrl.customTimeslot.startHours" ng-blur="editBookingCtrl.customTimeslot.startHours = editBookingCtrl.minMaxHours(editBookingCtrl.customTimeslot.startHours)" /> h
                                    <input class="input_nb" type="number" ng-model="editBookingCtrl.customTimeslot.startMinutes" ng-blur="editBookingCtrl.customTimeslot.startMinutes = editBookingCtrl.minMaxMinutes(editBookingCtrl.customTimeslot.startMinutes)" /> min
                                  </div>
                                  <div class="creneau_custom_fin">Fin
                                    <input class="input_nb" type="number"  ng-model="editBookingCtrl.customTimeslot.endHours" ng-blur="editBookingCtrl.customTimeslot.endHours = editBookingCtrl.minMaxHours(editBookingCtrl.customTimeslot.endHours)" /> h
                                    <input class="input_nb" type="number"  ng-model="editBookingCtrl.customTimeslot.endMinutes"  ng-blur="editBookingCtrl.customTimeslot.endMinutes = editBookingCtrl.minMaxMinutes(editBookingCtrl.customTimeslot.endMinutes)" /> min
                                    <br />
                                  </div>
                                  <div class="creneau_custom_sans_fin">
                                    <input id="sans_fin" type="checkbox"  ng-model="editBookingCtrl.customTimeslot.noEnd" />
                                    <label for="sans_fin">Pas d'heure de fin</label>
                                    <span class="helpbox"><span class="helpbox_content">Aucune heure de fin ne sera affichée. Par défaut sur le calendrier la réservation occupera 30 min si rien n'est renseigné.</span></span>
                                  </div>
                                  <h4>Scénario de réservation</h4>
                                  <div class="helpbox" ng-repeat="parameter in editBookingCtrl.settings.states_parameters">
                                    <input class="form-check-input" type="radio" name="customParametersTimeslot" id="formInput_tpc{{ $index }}" ng-model="editBookingCtrl.customTimeslot.idParameter" ng-value="parameter.id">
                                    <label class="form-check-label" for="formInput_tpc{{ $index }}">{{ parameter.name }}</label>
                                    <span class="helpbox_content">{{ parameter.description }}</span>
                                  </div>
                                  <input ng-disabled="!editBookingCtrl.isGoodNewCustomTimeslot()" class="btn" type="button" value="Ajouter et sélectionner" ng-click="editBookingCtrl.createNewCustomTimeslot()">
                                  <span class="warning" ng-if="!editBookingCtrl.isGoodNewCustomTimeslot()">Date de début supérieure à la date de fin</span>
                                </div>
                              </div>
                            </div>
                            <div class="col_prices">
                              <div class="edit_prices edit_bloc" ng-if="editBookingCtrl.serviceParameters.isDatesSet()">
                                <h3><?php _e('Price_list_word', 'resa') ?></h3>
                                <div class="infos" ng-if="editBookingCtrl.serviceParameters.getNumber() <= 0">Veuillez sélectionner un tarif</div>
                                <div class="prices">
                                  <input id="displayAllPrices" type="checkbox" ng-model="editBookingCtrl.serviceParameters.displayAllPrices" />
                                  <label for="displayAllPrices">Afficher tous les tarifs <span class="helpbox"><span class="helpbox_content">Les tarifs sont filtrés en fonction du type de compte du client<br />
                                  Type du compte : {{ editBookingCtrl.getTextByLocale(editBookingCtrl.getTypeAccountOfCustomer().name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span></span></label>
                                  <div class="price" ng-if="(editBookingCtrl.displayPriceListWithTypeAccount(numberPrice.price) || editBookingCtrl.serviceParameters.displayAllPrices) && numberPrice.price.activated" ng-class="{'active':numberPrice.number>0}" ng-repeat="numberPrice in editBookingCtrl.serviceParameters.numberPrices">
                                    <div class="price_value">
                                      <span ng-if="numberPrice.number == 0">
                                        {{ editBookingCtrl.serviceParameters.getMinThresholdPrice(numberPrice) }} <span class="price_value_currency">{{ editBookingCtrl.settings.currency }}</span>
                                      </span>
                                      <span ng-if="numberPrice.number > 0">
                                        <span ng-if="numberPrice.price.notThresholded">{{ editBookingCtrl.round(numberPrice.price.price  * editBookingCtrl.getNumberDays()) }}</span>
                                        <span ng-if="!numberPrice.price.notThresholded">{{ editBookingCtrl.round(editBookingCtrl.serviceParameters.getPriceNumberPrice(numberPrice) * editBookingCtrl.getNumberDays()) }}</span> {{ editBookingCtrl.settings.currency }}
                                      </span>
                                    </div>
                                    <div class="price_name">
                                      {{ editBookingCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode
                                      }}
                                      <span ng-if="numberPrice.price.private"><br />(privé)</span>
                                      ({{ numberPrice.price.slug }})
                                      <span ng-if="editBookingCtrl.serviceParameters.service.defaultSlugServicePrice == numberPrice.price.slug"><br />Montant minimum</span>
                                    </div>
                                    <div class="price_nb_add btn" ng-click="editBookingCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, 1); numberPrice.number = editBookingCtrl.minMaxValue(numberPrice.number, editBookingCtrl.getMinNumberPrice(editBookingCtrl.serviceParameters, numberPrice), editBookingCtrl.getMaxNumberPrice(editBookingCtrl.serviceParameters, numberPrice, $index)); editBookingCtrl.setParticipantIfNecessary(numberPrice)">+</div>
                                    <div class="price_nb_less btn" ng-click="editBookingCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, -1); numberPrice.number = editBookingCtrl.minMaxValue(numberPrice.number, 0, editBookingCtrl.getMaxNumberPrice(editBookingCtrl.serviceParameters, numberPrice, $index)); editBookingCtrl.setParticipantIfNecessary(numberPrice)">-</div>
                                    <input class="price_quantity" ng-disabled="editBookingCtrl.getMaxNumberPrice(editBookingCtrl.serviceParameters, numberPrice, $index) == -1" ng-change="editBookingCtrl.setParticipantIfNecessary(numberPrice)" ng-blur="numberPrice.number = editBookingCtrl.minMaxValue(numberPrice.number, 0, editBookingCtrl.getMaxNumberPrice(editBookingCtrl.serviceParameters, numberPrice, $index));" ng-model="numberPrice.number" type="number" />
                                    <div ng-if="editBookingCtrl.serviceParameters.equipmentsActivated && numberPrice.price.equipments.length > 0">
                                      {{   editBookingCtrl.getTextByLocale(editBookingCtrl.getEquipmentById((numberPrice.price.equipments[0])).name,'<?php echo get_locale(); ?>')|htmlSpecialDecode
                                    }} restant : {{ editBookingCtrl.serviceParameters.getMaxEquipmentsCanChoose($index) - numberPrice.number }}
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col_members">
                              <div class="edit_members edit_bloc" ng-if="editBookingCtrl.serviceParameters.isDatesSet() && editBookingCtrl.settings.staffManagement && !editBookingCtrl.settings.groupsManagement">
                                <h3>{{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h3>
                                <div class="members">
                                  <div class="btn btn_wide" ng-class="{'active':editBookingCtrl.serviceParameters.staffs.length == 0}" ng-click="editBookingCtrl.serviceParameters.staffs = []">Automatique</div>
                                  <div ng-click="editBookingCtrl.serviceParameters.assignStaff(memberUsed)" class="member" ng-class="{'active':editBookingCtrl.serviceParameters.getStaff(memberUsed.id) != null, 'full':memberUsed.capacity <= memberUsed.usedCapacity}" ng-if="memberUsed.capacity > 0" ng-repeat="memberUsed in editBookingCtrl.getStaff(0) track by $index">
                                    <div class="name">{{ memberUsed.nickname|htmlSpecialDecode }}</div>
                                    <div class="capacity">{{editBookingCtrl.serviceParameters.assignNumber(editBookingCtrl.serviceParameters.getStaff(memberUsed.id)) }} / {{ memberUsed.capacity - memberUsed.usedCapacity }}</div>
                                  </div>
                                </div>
                                <h4 ng-if="editBookingCtrl.serviceParameters.getMemberUsedMembers(0).length > 0">Autre {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
                                <div ng-if="editBookingCtrl.serviceParameters.getMemberUsedMembers(0).length > 0">{{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} non prévu par défaut</div>
                                <div class="members" ng-if="editBookingCtrl.serviceParameters.getMemberUsedMembers(0).length > 0">
                                  <div ng-click="editBookingCtrl.serviceParameters.assignStaff(memberUsed)" class="member" ng-class="{'active':editBookingCtrl.serviceParameters.getStaff(memberUsed.id) != null}" ng-if="memberUsed.capacity == 0" ng-repeat="memberUsed in editBookingCtrl.getStaff(0) track by $index">
                                    <div class="name">{{ memberUsed.nickname|htmlSpecialDecode }}</div>
                                    <div class="capacity">{{editBookingCtrl.serviceParameters.assignNumber(editBookingCtrl.serviceParameters.getStaff(memberUsed.id)) }}</div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col_state_tag">
                              <div class="edit_state edit_bloc" ng-if="editBookingCtrl.serviceParameters.isDatesSet()">
                                <h3>État</h3>
                                <div class="states">
                                  <div class="states_state state_confirmed" ng-if="!editBookingCtrl.serviceParameters.isOk()" ng-click="editBookingCtrl.serviceParameters.ok()">Confirmé</div>
                                  <div class="states_state state_not_confirmed" ng-if="!editBookingCtrl.serviceParameters.isWaiting()" ng-click="editBookingCtrl.serviceParameters.waiting()">Non confirmé</div>
                                  <div class="states_state state_cancelled"  ng-if="!editBookingCtrl.serviceParameters.isCancelled()" ng-click="editBookingCtrl.serviceParameters.cancelled()">Annulée</div>
                                </div>
                              </div>
                              <div class="edit_tags edit_bloc" ng-if="editBookingCtrl.serviceParameters.isDatesSet()">
                                <h3>Tags</h3>
                                <div class="tags booking_tags" ng-repeat="tag in editBookingCtrl.settings.appointmentTags">
                                  <div class="untag" ng-class="{'active':editBookingCtrl.serviceParameters.haveTag(tag.id)}" ng-click="editBookingCtrl.serviceParameters.switchTag(tag.id)">
                                    <div class="tag {{ tag.color }}">{{ editBookingCtrl.getTextByLocale(tag.title,'<?php echo get_locale(); ?>') }}</div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col_clients_infos" ng-if="editBookingCtrl.serviceParameters.needSelectParticipants() && editBookingCtrl.serviceParameters.isDatesSet()">
                              <h3>Informations participants</h3>
                              <div class="edit_clients_infos edit_bloc">
                                <div ng-if="numberPrice.number > 0 && numberPrice.price.participantsParameter != null && numberPrice.price.participantsParameter.length > 0" ng-repeat="numberPrice in editBookingCtrl.serviceParameters.numberPrices" class="clients_infos_tarif">
                                  <div class="clients_infos_tarif_title">{{ numberPrice.price.price }}{{ editBookingCtrl.settings.currency }} - {{ editBookingCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>') }} x {{ numberPrice.number }}</div>
                                  <div class="client_info" ng-repeat="number in editBookingCtrl.serviceParameters.getRepeatNumberByNumber(numberPrice.number) track by $index">
                									  <div class="number">{{ $index + 1 }}</div>
                										<input ng-click="editBookingCtrl.openParticipantSelectorDialog(editBookingCtrl.getParticipants(editBookingCtrl.serviceParameters), editBookingCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields, editBookingCtrl.serviceParameters, numberPrice, $index, true)" class="btn btn_bleu" type="button" value="Sélectionner">
                                    <span ng-repeat="field in editBookingCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields">
                    									<input ng-show="field.type == 'text' || field.type == 'number'" list="customersList{{ editBookingCtrl.serviceParameters.id }}_{{ $parent.$index }}_{{ $index }}" id="formInput{{ editBookingCtrl.serviceParameters.id }}_{{ $parent.$index }}_{{ $index }}" ng-change="editBookingCtrl.bookingLoadedIsModified=true" type="{{ field.type }}" ng-model="numberPrice.participants[$parent.$index][field.varname]" placeholder="{{ editBookingCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}" />
                                      <datalist ng-show="field.type == 'text'" id="customersList{{ editBookingCtrl.serviceParameters.id }}_{{ $parent.$index }}_{{ $index }}">
                                        <option value="{{ participant[field.varname] }}" ng-repeat="participant in editBookingCtrl.getParticipants(editBookingCtrl.serviceParameters) track by $index">
                                      </datalist>
                                      <span ng-show="field.type == 'select'">
                                        <select class="form-control" style="color: black;"  ng-model="numberPrice.participants[$parent.$index][field.varname]" id="formInput{{ editBookingCtrl.serviceParameters.id }}_{{ $parent.$index }}_{{ $index }}"  ng-init="numberPrice.participants[$parent.$index][field.varname] = editBookingCtrl.getFirstSelectOption(numberPrice.participants[$parent.$index][field.varname], field.options)">
                                          <option ng-repeat="option in field.options track by $index" value="{{ option.id }}">{{ editBookingCtrl.getTextByLocale(option.name,'<?php echo get_locale(); ?>') }}</option>
                                        </select>
                                      </span>
                                    </span>
                                    <input ng-click="editBookingCtrl.serviceParameters.removeParticipant(numberPrice, $index);" class="btn btn_rouge" type="button" value="Enlever">
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col_recap_total edit_bloc">
                              <h3>Validation <span class="helpbox"> <span class="helpbox_content">Les réductions seront calculées une fois la ligne validée.</span></span></h3>
                              <div class="recap_menu">
                                <div class="infos" ng-if="editBookingCtrl.serviceParameters.customTimeslot || editBookingCtrl.serviceParameters.haveExtraStaff() ||
                                !editBookingCtrl.serviceParameters.participantsIsSelected(editBookingCtrl.settings.form_participants_parameters) ||
                                (editBookingCtrl.serviceParameters.capacityTimeslot - editBookingCtrl.serviceParameters.getNumber()) < 0 ||
                                !editBookingCtrl.serviceParameters.isOkEquipmentsChoose()">
                                  Attention, cette ligne ne respecte pas les critères automatiques suivant :
                                <span ng-if="editBookingCtrl.serviceParameters.customTimeslot"><br />+ Créneau personnalisé</span>
                                <span ng-if="editBookingCtrl.serviceParameters.haveExtraStaff()"><br />+ {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_single, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} non prévue</span>
                                <span ng-if="editBookingCtrl.serviceParameters.isErrorNumberPriceMaxMin()"><br />+ Attention quantité minimale ou maximale d'un tarif non respecté</span>
                                <span ng-if="!editBookingCtrl.serviceParameters.participantsIsSelected(editBookingCtrl.settings.form_participants_parameters)"><br />+ Participants non remplit</span>
                                <span ng-if="(editBookingCtrl.serviceParameters.capacityTimeslot - editBookingCtrl.serviceParameters.getNumber()) < 0"><br />+ Sur-Capacité</span>
                                <span ng-if="!editBookingCtrl.serviceParameters.isOkEquipmentsChoose()"><br />+ Sur-utilisation du matériel</span>
                                </div>
                                <div class="recap_menu_actions">
                                  <div class="btn btn_wide" ng-disabled="!editBookingCtrl.canAddInBasket()" ng-click="!editBookingCtrl.canAddInBasket() || editBookingCtrl.addCurrentServiceInBasket()" ng-class="{'btn_vert':editBookingCtrl.canAddInBasket(), 'btn_locked':!editBookingCtrl.canAddInBasket()}">Valider</div>
                                  <div class="btn btn_jaune btn_wide" ng-click="editBookingCtrl.cancel($event)">Annuler</div>
                                  <div class="btn btn_rouge btn_wide" ng-click="editBookingCtrl.removeServiceParameters(serviceParameters)">Supprimer</div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="reductions">
                        <div class="reduction" ng-if="params.idsPrice.length > 0" ng-repeat="params in editBookingCtrl.mapIdClientObjectReduction['id'+serviceParameters.id] track by $index">
                          <div class="reduction_action" ng-if="params.promoCode.length > 0">
                            <btn class="btn resa_coupon_line_delete" ng-if="params.promoCode.length > 0" ng-click="editBookingCtrl.deleteCoupon(params.promoCode)">Supprimer</btn>
                          </div>
                          <div class="reduction_title">{{ editBookingCtrl.getTextByLocale(editBookingCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }} (x{{ params.number }})</div>
                          <div class="reduction_description"><span ng-bind-html="editBookingCtrl.getTextByLocale(editBookingCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></span></div>
                          <div class="reduction_value">
                            <span ng-if="params.type == 0">{{ params.value|negative }}{{ editBookingCtrl.settings.currency }}</span>
                            <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                            <span ng-if="params.type == 2">{{ params.value|negative }}{{ editBookingCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                            <span ng-if="params.type == 3">{{ params.value }}{{ editBookingCtrl.settings.currency }}
                              <span class="ro_tarif_barre"> {{ serviceParameters.getNumberPriceByIdPrice(params.idsPrice[0]).price.price }}{{ editBookingCtrl.settings.currency }}</span></span>
                            <span ng-if="params.type == 4">{{ params.value }} <?php _e('offer_quantity_words', 'resa') ?></span>
                            <span ng-if="params.type == 5">{{ params.value }}</span>
                          </div>
                        </div>
                        <div class="reduction" ng-if="params.idsPrice.length == 0" ng-repeat="params in editBookingCtrl.mapIdClientObjectReduction['id'+serviceParameters.id] track by $index">
                          <div class="reduction_action" ng-if="params.promoCode.length > 0">
                            <btn class="btn resa_coupon_line_delete" ng-if="params.promoCode.length > 0" ng-click="editBookingCtrl.deleteCoupon(params.promoCode)">Supprimer</btn>
                          </div>
                          <div class="reduction_title">{{ editBookingCtrl.getTextByLocale(editBookingCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }} (x{{ params.number }})</div>
                          <div class="reduction_description"><span ng-bind-html="editBookingCtrl.getTextByLocale(editBookingCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></span></div>
                          <div class="reduction_value">
                            <span ng-if="params.type == 0">{{ params.value|negative }}{{ editBookingCtrl.settings.currency }}</span>
                            <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                            <span ng-if="params.type == 2">{{ params.value|negative }}{{ editBookingCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                            <span ng-if="params.type == 3">{{ params.value }}{{ editBookingCtrl.settings.currency }} <span class="ro_tarif_barre"> {{ numberPrice.price.price }}{{ editBookingCtrl.settings.currency }}</span></span>
                            <span ng-if="params.type == 4">{{ params.value }} <?php _e('offer_quantity_words', 'resa') ?></span>
                            <span ng-if="params.type == 5">{{ params.value }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="content_line" ng-if="editBookingCtrl.displayEditMode && editBookingCtrl.isNewServiceParameters">
                      <div class="booking complete" ng-class="{'editing': editBookingCtrl.displayEditMode, 'confirmed':editBookingCtrl.canAddInBasket() && editBookingCtrl.serviceParameters.isOk(),'not_confirmed':editBookingCtrl.canAddInBasket() && editBookingCtrl.serviceParameters.isWaiting(),'cancelled':editBookingCtrl.canAddInBasket() && (editBookingCtrl.serviceParameters.isCancelled() || editBookingCtrl.serviceParameters.isAbandonned()), 'incomplete':!editBookingCtrl.canAddInBasket()}">
                        <div class="booking_date">
                          <span ng-if="editBookingCtrl.serviceParameters.isDatesSet()"> {{ editBookingCtrl.serviceParameters.dateStart|formatDate:'<?php echo $variables['date_format']; ?>' }}
                          </span>
                        </div>
                        <div class="booking_hour">
                          <span ng-if="editBookingCtrl.serviceParameters.isDatesSet()">
                            <span ng-if="!editBookingCtrl.serviceParameters.noEnd">
                              {{ editBookingCtrl.serviceParameters.dateStart|formatDate:'<?php echo $variables['time_format']; ?>' }}
                              <?php _e('to_word', 'resa') ?>
                              {{ editBookingCtrl.serviceParameters.dateEnd|formatDate:'<?php echo $variables['time_format']; ?>' }}
                            </span>
                            <span ng-if="editBookingCtrl.serviceParameters.noEnd">
                              <?php _e('begin_word', 'resa'); ?> {{ editBookingCtrl.serviceParameters.dateStart|formatDate:'<?php echo $variables['time_format']; ?>' }}
                            </span>
                          </span>
                        </div>
                        <div class="booking_nb_people" ng-class="{'helpbox':editBookingCtrl.serviceParameters.service.askParticipants}">
                          {{ editBookingCtrl.serviceParameters.getNumber() }} pers.
                          <span ng-if="editBookingCtrl.serviceParameters.service.askParticipants" class="helpbox_content"><h4>Participants</h4>
                            <span ng-repeat="numberPrice in editBookingCtrl.serviceParameters.numberPrices">
                              <table>
                                <thead>
                                  <tr>
                                    <td ng-repeat="field in editBookingCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields">
                                      {{ editBookingCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
                                    </td>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr ng-repeat="participant in numberPrice.participants track by $index"><td ng-repeat="field in editBookingCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields">{{ editBookingCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{ editBookingCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</td></tr>
                                </tbody>
                              </table>
                            </span>
                          </span>
                        </div>
                        <div class="booking_states">
                          <div class="states_state state_confirmed">Confirmée</div>
                          <div class="states_state state_not_confirmed">Non confirmée</div>
                          <div class="states_state state_cancelled">Annulée</div>
                          <div class="states_state state_inerror">Erreur</div>
                          <div class="states_state state_incomplete">Incomplète</div>
                        </div>
                        <div class="booking_place"><span ng-if="editBookingCtrl.serviceParameters.idPlace != ''">{{ editBookingCtrl.getTextByLocale(editBookingCtrl.getPlaceById(editBookingCtrl.serviceParameters.place).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span></div>
                        <div class="booking_service">
                        {{ editBookingCtrl.getTextByLocale(
                            editBookingCtrl.serviceParameters.service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                          </div>
                        <div class="booking_prices">
                          <div class="prices_price" ng-if="numberPrice.number > 0" ng-repeat="numberPrice in editBookingCtrl.serviceParameters.numberPrices">
                            <span class="price_nb">{{ numberPrice.number }}</span>
                            <span class="price_name">
                              {{ editBookingCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode
                              }}
                              <span ng-if="numberPrice.price.equipments.length > 0">
                                ({{ editBookingCtrl.getTextByLocale(editBookingCtrl.getEquipmentById((numberPrice.price.equipments[0])).name,'<?php echo get_locale(); ?>')|htmlSpecialDecode
              									}})
                              </span>
                            </span>
                            <span class="price_value" ng-if="editBookingCtrl.serviceParameters.canCalculatePrice(numberPrice)">
                              <span ng-if="numberPrice.price.notThresholded">{{ editBookingCtrl.round(numberPrice.price.price) }}</span>
                              <span ng-if="!numberPrice.price.notThresholded">{{ editBookingCtrl.round(editBookingCtrl.serviceParameters.getPriceNumberPrice(numberPrice)) }}</span> {{ editBookingCtrl.settings.currency }}
                            </span>
                          </div>
                        </div>
                        <div class="booking_members">
                          <div class="member" ng-if="editBookingCtrl.settings.staffManagement && !editBookingCtrl.settings.groupsManagement" ng-bind-html="editBookingCtrl.serviceParameters.displayStaffs()"></div>
                        </div>
                        <div class="booking_tags">
                          <div class="tag {{ editBookingCtrl.getTagById(idTag).color }}" ng-repeat="idTag in editBookingCtrl.serviceParameters.tags">
                            {{ editBookingCtrl.getTextByLocale(editBookingCtrl.getTagById(idTag).title, '<?php echo get_locale(); ?>') }}
                          </div>
                        </div>
                        <div class="booking_total">{{ editBookingCtrl.serviceParameters.getPriceWithDefaultSlugServicePrice() }}{{editBookingCtrl.settings.currency}}</div>
                        <div class="booking_edit">
                          <div class="booking_edit_content">
                            <div class="recap_menu_actions_top">
                              <h3>Ajout d'un nouveau rendez-vous <span class="helpbox"><span class="helpbox_content"> Remplissez les éléments de cette ligne de la réservation à l'aide des paramètres ci-dessous. <br />Utilisez les boutons :<br />"Valider" pour valider l'ajout<br />"Annuler" pour annuler les ajouts (celles-ci seront perdues)</span></span></h3>
                              <div class="recap_menu_actions">
                                <div class="btn btn_wide" ng-disabled="!editBookingCtrl.canAddInBasket()" ng-click="!editBookingCtrl.canAddInBasket() || editBookingCtrl.addCurrentServiceInBasket() ||
                                  editBookingCtrl.feedbackAddBasket({
                                    title:'<?php _e('add_in_basket_success', 'resa'); ?>'
                                  })" ng-class="{'btn_vert':editBookingCtrl.canAddInBasket(), 'btn_locked':!editBookingCtrl.canAddInBasket()}">Valider</div>
                                <div class="btn btn_jaune btn_wide" ng-click="editBookingCtrl.cancel($event)">Annuler</div>
                              </div>
                            </div>
                            <div class="col_place_service">
                              <div class="edit_place edit_bloc" ng-if="editBookingCtrl.needSelectPlace()">
                                <h3>Lieu</h3>
                                <div class="infos" ng-if="editBookingCtrl.serviceParameters.place == ''">Veuillez sélectionner un lieu</div>
                                <div class="edit_places">
                                  <div class="place" ng-click="editBookingCtrl.changePlace(place)" ng-class="{'active':editBookingCtrl.serviceParameters.place == place.id}" ng-repeat="place in editBookingCtrl.getPlaces() track by $index"> {{ editBookingCtrl.getTextByLocale(place.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                                </div>
                              </div>
                              <div class="edit_service edit_bloc" ng-if="editBookingCtrl.serviceParameters.place != '' || !editBookingCtrl.needSelectPlace()">
                                <h3><?php _e('Services_word', 'resa'); ?></h3>
                                <div class="infos" ng-if="editBookingCtrl.serviceParameters.service == null">Veuillez sélectionner un service</div>
                                <div class="edit_services">
                                  <div class="service" ng-click="editBookingCtrl.changeService(service)" ng-class="{'active':editBookingCtrl.serviceParameters.service.id == service.id}" ng-repeat="service in editBookingCtrl.getServicesByPlace(editBookingCtrl.serviceParameters.place)"> {{ editBookingCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                                </div>
                              </div>
                            </div>
                            <div class="col_date">
                              <div class="edit_date edit_bloc" ng-if="editBookingCtrl.serviceParameters.service != null">
                                <h3>Date :</h3>
                                <div class="infos" ng-if="!editBookingCtrl.serviceParameters.isDatesSet()">Veuillez sélectionner un créneau</div>
                                <div class="several_date" ng-if="editBookingCtrl.serviceParameters.service.typeAvailabilities == 1 && editBookingCtrl.serviceParameters.isDatesSet()">
                                  <input id="several_date_week_mode" type="checkbox" ng-model="editBookingCtrl.weekMode" ng-true-value="false" ng-false-value="true" />
                                  <label for="several_date_week_mode">Customiser la sélection (hors semaine)</label>
                                  <span class="helpbox"><span class="helpbox_content">Cocher cette case pour choisirs plusieurs creneaux sur plusieurs dates. Cela génèrera plusieurs rendez-vous dans cette réservation lorsque vous l'enregistrerait. Vous pourrez modifier chaque rendez-vous individuellement par la suite.</span></span>
                                </div>
                                <div class="several_date" ng-if="editBookingCtrl.serviceParameters.service.typeAvailabilities != 1">
                                  <input id="several_date" ng-change="editBookingCtrl.clearDates()" type="checkbox" ng-model="editBookingCtrl.manyTimeslots" />
                                  <label for="several_date">Sélectionner plusieurs créneaux</label>
                                  <span class="helpbox"><span class="helpbox_content">Cocher cette case pour répéter une réservation sur plusieurs dates. Cela génèrera plusieurs rendez-vous dans cette réservation lorsque vous l'enregistrerait. Vous pourrez modifier chaque rendez-vous individuellement par la suite.</span></span>
                                </div>
                                <div class="date_creneaux">
                                  <div class="creneaux_date_before_title" ng-if="!editBookingCtrl.serviceParameters.isCombined()">
                                    <div class="arrow_left btn" ng-click="editBookingCtrl.decrementDate()"> &larr;</div>
                                    <div class="jmoinsun">j-1</div>
                                  </div>
                                  <div class="creneaux_date_title" ng-if="!editBookingCtrl.serviceParameters.isCombined()">
                                    <input ng-change="editBookingCtrl.changeCurrentDate()" type="date" ng-model="editBookingCtrl.currentDate" />
                                  </div>
                                  <div class="creneaux_date_after_title" ng-if="!editBookingCtrl.serviceParameters.isCombined()">
                                    <div class="jplusun">j+1</div>
                                    <div class="arrow_right btn" ng-click="editBookingCtrl.incrementDate()"> &rarr;</div>
                                  </div>
                                  <div class="creneaux_date_before_title" ng-if="editBookingCtrl.serviceParameters.isWeek()">
                                    <div class="arrow_left btn" ng-click="editBookingCtrl.addMonth(-1)"> &larr;</div>
                                    <div class="jmoinsun">m-1</div>
                                  </div>
                                  <div class="creneaux_date_title" ng-if="editBookingCtrl.serviceParameters.isWeek()">
                                    <select ng-change="editBookingCtrl.changeCurrentMonthDate()" ng-model="editBookingCtrl.monthIndex" ng-options="month.index as (month.date|date:'MMMM - yyyy') for month in editBookingCtrl.months"></select>
                                  </div>
                                  <div class="creneaux_date_after_title" ng-if="editBookingCtrl.serviceParameters.isWeek()">
                                    <div class="jplusun">m+1</div>
                                    <div class="arrow_right btn" ng-click="editBookingCtrl.addMonth(1)"> &rarr;</div>
                                  </div>
                                  <div class="creneaux_week" ng-if="editBookingCtrl.serviceParameters.service.typeAvailabilities == 1">
                                    <div class="creneau" ng-class="{'active':editBookingCtrl.isWeekSelected(dates)}" ng-click="editBookingCtrl.setWeek(dates)" ng-repeat="dates in editBookingCtrl.getGroupDates()">
                                      Semaine du {{ dates.startDate|formatDateTime:'<?php echo $variables['date_format']; ?>' }} au {{ dates.endDate|formatDateTime:'<?php echo $variables['date_format']; ?>' }}
                                    </div>
                                  </div>
                                  <div class="creneaux_week" ng-if="editBookingCtrl.serviceParameters.service.typeAvailabilities == 2">
                                    <div class="creneau" ng-class="{'active':editBookingCtrl.isSelectedManyDates(dates)}" ng-click="editBookingCtrl.setServiceParametersManyDates(dates);" ng-repeat="dates in editBookingCtrl.getManyDates()"><span ng-bind-html="dates|formatManyDatesView"></span></div>
                                  </div>
                                  <div ng-class="{'creneaux_date_before':$index==0, 'creneaux_date':$index==1, 'creneaux_date_after':$index==2}" ng-repeat="date in editBookingCtrl.dates">
                                    <div ng-click="editBookingCtrl.selectTimeslot(timeslot)" class="creneau {{ timeslot.getClass(editBookingCtrl.serviceParameters.dateStart, editBookingCtrl.serviceParameters.dateEnd, editBookingCtrl.serviceParameters.getNumber()) }}" ng-class="{'active':editBookingCtrl.getIndexTimeslot(timeslot)!=-1 || timeslot.isSameDates(editBookingCtrl.serviceParameters.dateStart, editBookingCtrl.serviceParameters.dateEnd)}" ng-repeat="timeslot in editBookingCtrl.timeslots[date.getTime()]|orderBy:'dateEnd'">
                                      <p class="creneau_time">
                                        <span ng-if="timeslot.isCustom">Personnalisé<br /></span>
                                        <span ng-if="!timeslot.noEnd">
                                          {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }} <?php _e('to_word', 'resa'); ?> {{ timeslot.dateEnd|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                        </span>
                                        <span ng-if="timeslot.noEnd">
                                          <?php _e('begin_word', 'resa'); ?>
                                          {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                        </span>
                                      </p>
                                      <p class="creneau_space">
                                        <span ng-if="!timeslot.isSameDates(editBookingCtrl.serviceParameters.dateStart, editBookingCtrl.serviceParameters.dateEnd)">
                                          {{ timeslot.getCapacity() }}/{{ timeslot.getMaxCapacity() }} <?php _e('positions_word', 'resa'); ?>
                                        </span>
                                        <span ng-if="timeslot.isSameDates(editBookingCtrl.serviceParameters.dateStart, editBookingCtrl.serviceParameters.dateEnd)">
                                        {{ timeslot.getCapacity() - editBookingCtrl.serviceParameters.getNumber() }}/{{ timeslot.getMaxCapacity() }} <?php _e('positions_word', 'resa'); ?>
                                        </span>
                                      </p>
                                    </div>
                                    <div ng-show="editBookingCtrl.timeslots[date.getTime()]==null && editBookingCtrl.isProgressLoad" class="loader_box">
                                      <div class="loader_content">
                                        <div class="loader">
                                          <div class="shadow"></div>
                                          <div class="box"></div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="creneau_recap" ng-if="editBookingCtrl.manyTimeslots && editBookingCtrl.serviceParameters.isDatesSet()">
                                  <h4>Creneaux sélectionnés</h4>
                                  <ul>
                                    <li>
                                      {{ editBookingCtrl.serviceParameters.dateStart|formatDateTime:'<?php echo $variables['date_format']; ?>' }} :
                                      <span ng-if="!editBookingCtrl.serviceParameters.noEnd">
                                        {{ editBookingCtrl.serviceParameters.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }} <?php _e('to_word', 'resa'); ?> {{ editBookingCtrl.serviceParameters.dateEnd|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                      </span>
                                      <span ng-if="editBookingCtrl.serviceParameters.noEnd">
                                        <?php _e('begin_word', 'resa'); ?>
                                        {{ editBookingCtrl.serviceParameters.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                      </span>
                                    </li>
                                    <li ng-repeat="timeslot in editBookingCtrl.selectedTimeslots">
                                      {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['date_format']; ?>' }} :
                                      <span ng-if="!timeslot.noEnd">
                                        {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }} <?php _e('to_word', 'resa'); ?> {{ timeslot.dateEnd|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                      </span>
                                      <span ng-if="timeslot.noEnd">
                                        <?php _e('begin_word', 'resa'); ?>
                                        {{ timeslot.dateStart|formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                                      </span>
                                    </li>
                                  </ul>
                                  <input class="btn" type="button" ng-click="editBookingCtrl.allUnselected()" value="Tout désélectionner" />
                                </div>
                                <div class="creneau_custom">
                                  <h4>Créer un créneau le {{ editBookingCtrl.currentDate|formatDateTime:'<?php echo $variables['date_format']; ?>' }}</h4>
                                  <div class="creneau_custom_debut">Début
                                    <input class="input_nb" type="number" ng-model="editBookingCtrl.customTimeslot.startHours" ng-blur="editBookingCtrl.customTimeslot.startHours = editBookingCtrl.minMaxHours(editBookingCtrl.customTimeslot.startHours)" /> h
                                    <input class="input_nb" type="number" ng-model="editBookingCtrl.customTimeslot.startMinutes" ng-blur="editBookingCtrl.customTimeslot.startMinutes = editBookingCtrl.minMaxMinutes(editBookingCtrl.customTimeslot.startMinutes)" /> min
                                  </div>
                                  <div class="creneau_custom_fin">Fin
                                    <input class="input_nb" type="number"  ng-model="editBookingCtrl.customTimeslot.endHours" ng-blur="editBookingCtrl.customTimeslot.endHours = editBookingCtrl.minMaxHours(editBookingCtrl.customTimeslot.endHours)" /> h
                                    <input class="input_nb" type="number"  ng-model="editBookingCtrl.customTimeslot.endMinutes"  ng-blur="editBookingCtrl.customTimeslot.endMinutes = editBookingCtrl.minMaxMinutes(editBookingCtrl.customTimeslot.endMinutes)" /> min
                                    <br />
                                  </div>
                                  <div class="creneau_custom_sans_fin">
                                    <input id="sans_fin" type="checkbox"  ng-model="editBookingCtrl.customTimeslot.noEnd" />
                                    <label for="sans_fin">Pas d'heure de fin</label>
                                    <span class="helpbox"><span class="helpbox_content">Aucune heure de fin ne sera affichée. Par défaut sur le calendrier la réservation occupera 30 min si rien n'est renseigné.</span></span>
                                  </div>
                                  <h4>Scénario de réservation</h4>
                                  <div class="helpbox" ng-repeat="parameter in editBookingCtrl.settings.states_parameters">
                                    <input class="form-check-input" type="radio" name="customParametersTimeslot" id="formInput_tpc{{ $index }}" ng-model="editBookingCtrl.customTimeslot.idParameter" ng-value="parameter.id">
                                    <label class="form-check-label" for="formInput_tpc{{ $index }}">{{ parameter.name }}</label>
                                    <span class="helpbox_content">{{ parameter.description }}</span>
                                  </div>
                                  <input ng-disabled="!editBookingCtrl.isGoodNewCustomTimeslot()"  class="btn" type="button" value="Ajouter et sélectionner"  ng-click="editBookingCtrl.createNewCustomTimeslot()">
                                  <span class="warning" ng-if="!editBookingCtrl.isGoodNewCustomTimeslot()">Date de début supérieure à la date de fin</span>
                                </div>
                              </div>
                            </div>
                            <div class="col_prices">
                              <div class="edit_prices edit_bloc" ng-if="editBookingCtrl.serviceParameters.isDatesSet()">
                                <h3><?php _e('Price_list_word', 'resa') ?></h3>

                                <div class="prices">
                                  <input id="displayAllPrices" type="checkbox" ng-model="editBookingCtrl.serviceParameters.displayAllPrices" />
                                  <label for="displayAllPrices">Afficher tous les tarifs <span class="helpbox"><span class="helpbox_content">Les tarifs sont filtrés en fonction du type de compte du client<br />
                                  Type du compte : {{ editBookingCtrl.getTextByLocale(editBookingCtrl.getTypeAccountOfCustomer().name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span></span></label>
                                  <div class="price" ng-if="(editBookingCtrl.displayPriceListWithTypeAccount(numberPrice.price) || editBookingCtrl.serviceParameters.displayAllPrices) && numberPrice.price.activated" ng-class="{'active':numberPrice.number>0}" ng-repeat="numberPrice in editBookingCtrl.serviceParameters.numberPrices">
                                    <div class="price_value">
                                      <span ng-if="numberPrice.number == 0">
                                        {{ editBookingCtrl.serviceParameters.getMinThresholdPrice(numberPrice) }} <span class="price_value_currency">{{ editBookingCtrl.settings.currency }}</span>
                                      </span>
                                      <span ng-if="numberPrice.number > 0">
                                        <span ng-if="numberPrice.price.notThresholded">{{ editBookingCtrl.round(numberPrice.price.price  * editBookingCtrl.getNumberDays()) }}</span>
                                        <span ng-if="!numberPrice.price.notThresholded">{{ editBookingCtrl.round(editBookingCtrl.serviceParameters.getPriceNumberPrice(numberPrice) * editBookingCtrl.getNumberDays()) }}</span> {{ editBookingCtrl.settings.currency }}
                                      </span>
                                    </div>
                                    <div class="price_name">
                                      {{ editBookingCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                                      <span ng-if="numberPrice.price.private"><br />(privé)</span><br />
                                      ({{ numberPrice.price.slug }})
                                      <span ng-if="editBookingCtrl.serviceParameters.service.defaultSlugServicePrice == numberPrice.price.slug"><br />Montant minimum</span>
                                    </div>
                                    <div class="price_nb_add btn" ng-click="editBookingCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, 1); numberPrice.number = editBookingCtrl.minMaxValue(numberPrice.number, editBookingCtrl.getMinNumberPrice(editBookingCtrl.serviceParameters, numberPrice), editBookingCtrl.getMaxNumberPrice(editBookingCtrl.serviceParameters, numberPrice, $index));
                                    editBookingCtrl.setDefaultParticipants(numberPrice);">+</div>
                                    <div class="price_nb_less btn" ng-click="editBookingCtrl.serviceParameters.addNumberInNumberPrice(numberPrice, -1); numberPrice.number = editBookingCtrl.minMaxValue(numberPrice.number, 0, editBookingCtrl.getMaxNumberPrice(editBookingCtrl.serviceParameters, numberPrice, $index));
                                    editBookingCtrl.setDefaultParticipants(numberPrice);">-</div>
                                    <input class="price_quantity" ng-disabled="editBookingCtrl.getMaxNumberPrice(editBookingCtrl.serviceParameters, numberPrice, $index) == -1" ng-blur="numberPrice.number = editBookingCtrl.minMaxValue(numberPrice.number, 0, editBookingCtrl.getMaxNumberPrice(editBookingCtrl.serviceParameters, numberPrice, $index));
                                    editBookingCtrl.setDefaultParticipants(numberPrice);" ng-model="numberPrice.number" type="number" />
                                    <div ng-if="editBookingCtrl.serviceParameters.equipmentsActivated && numberPrice.price.equipments.length > 0">
                                      {{ editBookingCtrl.getTextByLocale(editBookingCtrl.getEquipmentById((numberPrice.price.equipments[0])).name,'<?php echo get_locale(); ?>')|htmlSpecialDecode
                                      }} restant : {{ editBookingCtrl.serviceParameters.getMaxEquipmentsCanChoose($index) - numberPrice.number }}
                                    </div>
                                  </div>
                                </div>
                                <div class="infos" ng-if="editBookingCtrl.serviceParameters.getNumber() <= 0">Veuillez sélectionner un tarif</div>
                              </div>
                            </div>
                            <div class="col_members">
                              <div class="edit_members edit_bloc" ng-if="editBookingCtrl.serviceParameters.isDatesSet() && editBookingCtrl.settings.staffManagement && !editBookingCtrl.settings.groupsManagement">
                                <h3>{{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h3>
                                <div class="members">
                                  <div class="btn btn_wide" ng-class="{'active':editBookingCtrl.serviceParameters.staffs.length == 0}" ng-click="editBookingCtrl.serviceParameters.staffs = []">Automatique</div>
                                  <div ng-click="editBookingCtrl.serviceParameters.assignStaff(memberUsed)" class="member" ng-class="{'active':editBookingCtrl.serviceParameters.getStaff(memberUsed.id) != null, 'full':memberUsed.capacity <= memberUsed.usedCapacity}" ng-if="memberUsed.capacity > 0" ng-repeat="memberUsed in editBookingCtrl.getStaff(0) track by $index">
                                    <div class="name">{{ memberUsed.nickname|htmlSpecialDecode }}</div>
                                    <div class="capacity">{{editBookingCtrl.serviceParameters.assignNumber(editBookingCtrl.serviceParameters.getStaff(memberUsed.id)) }} / {{ memberUsed.capacity - memberUsed.usedCapacity }}</div>
                                  </div>
                                </div>
                                <h4 ng-if="editBookingCtrl.serviceParameters.getMemberUsedMembers(0).length > 0">Autre {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
                                <div ng-if="editBookingCtrl.serviceParameters.getMemberUsedMembers(0).length > 0">{{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} non prévus par défaut</div>
                                <div class="members" ng-if="editBookingCtrl.serviceParameters.getMemberUsedMembers(0).length > 0">
                                  <div ng-click="editBookingCtrl.serviceParameters.assignStaff(memberUsed)" class="member" ng-class="{'active':editBookingCtrl.serviceParameters.getStaff(memberUsed.id) != null}" ng-if="memberUsed.capacity == 0" ng-repeat="memberUsed in editBookingCtrl.getStaff(0) track by $index">
                                    <div class="name">{{ memberUsed.nickname|htmlSpecialDecode }}</div>
                                    <div class="capacity">{{editBookingCtrl.serviceParameters.assignNumber(editBookingCtrl.serviceParameters.getStaff(memberUsed.id)) }}</div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col_state_tag">
                              <div class="edit_state edit_bloc" ng-if="editBookingCtrl.serviceParameters.isDatesSet()">
                                <h3>État</h3>
                                <div class="states">
                                  <div class="states_state state_confirmed" ng-if="!editBookingCtrl.serviceParameters.isOk()" ng-click="editBookingCtrl.serviceParameters.ok()">Confirmé</div>
                                  <div class="states_state state_not_confirmed" ng-if="!editBookingCtrl.serviceParameters.isWaiting()" ng-click="editBookingCtrl.serviceParameters.waiting()">Non confirmé</div>
                                  <div class="states_state state_cancelled"  ng-if="!editBookingCtrl.serviceParameters.isCancelled()" ng-click="editBookingCtrl.serviceParameters.cancelled()">Annulée</div>
                                </div>
                              </div>
                              <div class="edit_tags edit_bloc">
                                <h3>Tags</h3>
                                <div class="tags booking_tags" ng-repeat="tag in editBookingCtrl.settings.appointmentTags">
                                  <div class="untag" ng-class="{'active':editBookingCtrl.serviceParameters.haveTag(tag.id)}" ng-click="editBookingCtrl.serviceParameters.switchTag(tag.id)">
                                    <div class="tag {{ tag.color }}">{{ editBookingCtrl.getTextByLocale(tag.title,'<?php echo get_locale(); ?>') }}</div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col_clients_infos" ng-if="editBookingCtrl.serviceParameters.needSelectParticipants() && editBookingCtrl.serviceParameters.isDatesSet()">
                              <h3>Informations participants</h3>
                              <div class="edit_clients_infos edit_bloc">
                                <div ng-if="numberPrice.number > 0 && numberPrice.price.participantsParameter != null && numberPrice.price.participantsParameter.length > 0" ng-repeat="numberPrice in editBookingCtrl.serviceParameters.numberPrices" class="clients_infos_tarif">
                                  <div class="clients_infos_tarif_title">{{ numberPrice.price.price }}{{ editBookingCtrl.settings.currency }} - {{ editBookingCtrl.getTextByLocale(numberPrice.price.name,'<?php echo get_locale(); ?>') }} x {{ numberPrice.number }}</div>
                                  <div class="client_info" ng-repeat="number in editBookingCtrl.serviceParameters.getRepeatNumberByNumber(numberPrice.number) track by $index">
                  									<div class="number">
                                      {{ $index + 1 }}
                                    </div>
                										<input ng-click="editBookingCtrl.openParticipantSelectorDialog(editBookingCtrl.getParticipants(editBookingCtrl.serviceParameters), editBookingCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields, editBookingCtrl.serviceParameters, numberPrice, $index, false)" class="btn btn_bleu" type="button" value="Sélectionner">
                                    <span ng-repeat="field in editBookingCtrl.getParticipantsParameter(numberPrice.price.participantsParameter).fields">
                    									<input ng-show="field.type == 'text' || field.type == 'number'" list="customersList{{ editBookingCtrl.serviceParameters.id }}_{{ $parent.$index }}_{{ $index }}" id="formInput{{ editBookingCtrl.serviceParameters.id }}_{{ $parent.$index }}_{{ $index }}" ng-change="editBookingCtrl.bookingLoadedIsModified=true" type="{{ field.type }}" ng-model="numberPrice.participants[$parent.$index][field.varname]" placeholder="{{ editBookingCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}" />
                                      <datalist ng-show="field.type == 'text'" id="customersList{{ editBookingCtrl.serviceParameters.id }}_{{ $parent.$index }}_{{ $index }}">
                                        <option value="{{ participant[field.varname] }}" ng-repeat="participant in editBookingCtrl.getParticipants(editBookingCtrl.serviceParameters) track by $index">
                                      </datalist>
                                      <span ng-show="field.type == 'select'">
                                        <select class="form-control" style="color: black;"  ng-model="numberPrice.participants[$parent.$index][field.varname]" id="formInput{{ editBookingCtrl.serviceParameters.id }}_{{ $parent.$index }}_{{ $index }}" ng-init="numberPrice.participants[$parent.$index][field.varname] = editBookingCtrl.getFirstSelectOption(numberPrice.participants[$parent.$index][field.varname], field.options)">
                                          <option ng-repeat="option in field.options track by $index" value="{{ option.id }}">{{ editBookingCtrl.getTextByLocale(option.name,'<?php echo get_locale(); ?>') }}</option>
                                        </select>
                                      </span>
                                    </span>
                                    <input ng-click="editBookingCtrl.serviceParameters.removeParticipant(numberPrice, $index);" class="btn btn_rouge" type="button" value="Enlever">
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col_recap_total edit_bloc">
                              <h3>Validation <span class="helpbox"> <span class="helpbox_content">Les réductions seront calculées une fois la ligne validée.</span></span></h3>
                              <div class="recap_menu">
                                <div class="infos" ng-if="editBookingCtrl.serviceParameters.customTimeslot || editBookingCtrl.serviceParameters.haveExtraStaff() ||
                                !editBookingCtrl.serviceParameters.participantsIsSelected(editBookingCtrl.settings.form_participants_parameters) ||
                                (editBookingCtrl.serviceParameters.capacityTimeslot - editBookingCtrl.serviceParameters.getNumber()) < 0 ||
                                !editBookingCtrl.serviceParameters.isOkEquipmentsChoose()">
                                Attention, cette ligne ne respecte pas les critères automatiques suivant :
                                <span ng-if="editBookingCtrl.serviceParameters.customTimeslot"><br />+ Créneau personnalisé</span>
                                <span ng-if="editBookingCtrl.serviceParameters.haveExtraStaff()"><br />+ {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_single, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} non prévue</span>
                                <span ng-if="editBookingCtrl.serviceParameters.isErrorNumberPriceMaxMin()"><br />+ Attention quantité minimale ou maximale d'un tarif non respecté</span>
                                <span ng-if="!editBookingCtrl.serviceParameters.participantsIsSelected(editBookingCtrl.settings.form_participants_parameters)"><br />+ Participants non remplit</span>
                                <span ng-if="(editBookingCtrl.serviceParameters.capacityTimeslot - editBookingCtrl.serviceParameters.getNumber()) < 0"><br />+ Sur-Capacité</span>
                                <span ng-if="!editBookingCtrl.serviceParameters.isOkEquipmentsChoose()"><br />+ Sur-utilisation du matériel</span>
                                </div>
                                <div class="recap_menu_actions">
                                  <div class="btn btn_wide" ng-disabled="!editBookingCtrl.canAddInBasket()" ng-click="!editBookingCtrl.canAddInBasket() || editBookingCtrl.addCurrentServiceInBasket() ||
                                    editBookingCtrl.feedbackAddBasket({
                                      title:'<?php _e('add_in_basket_success', 'resa'); ?>'
                                    })" ng-class="{'btn_vert':editBookingCtrl.canAddInBasket(), 'btn_locked':!editBookingCtrl.canAddInBasket()}">Valider</div>
                                  <div class="btn btn_jaune btn_wide" ng-click="editBookingCtrl.cancel($event)">Annuler</div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div ng-if="editBookingCtrl.getAllServicesParameters().length > 0" class="resa_sub_total_line">
                        <div class="resa_sub_total_line_title">Sous-total du service</div>
                        <div class="resa_sub_total_line_value">{{ editBookingCtrl.round(editBookingCtrl.getSubTotalPrice([editBookingCtrl.serviceParameters]) * editBookingCtrl.getNumberDays()) }}{{ editBookingCtrl.settings.currency }}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="resa_sub_total_line" ng-if="editBookingCtrl.getAllServicesParameters().length > 0">
                  <div class="resa_sub_total_line_title">Sous-total <span ng-if="!editBookingCtrl.quotation">réservation</span><span ng-if="editBookingCtrl.quotation">devis</span></div>
                  <div class="resa_sub_total_line_value">{{ editBookingCtrl.getSubTotalPrice(editBookingCtrl.getAllServicesParameters()) }}{{ editBookingCtrl.settings.currency }}</div>
                </div>
                <div class="reductions">
                  <div class="reduction" ng-if="params.promoCode.length == 0" ng-repeat="params in editBookingCtrl.mapIdClientObjectReduction['id0'] track by $index">
                    <div class="reduction_action"  ng-if="params.promoCode.length > 0">
                      <btn class="btn resa_coupon_line_delete" ng-if="params.promoCode.length > 0" ng-click="editBookingCtrl.deleteCoupon(params.promoCode)">Supprimer</btn>
                    </div>
                    <div class="reduction_title">{{ editBookingCtrl.getTextByLocale(editBookingCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }} (x{{ params.number }})</div>
                    <div class="reduction_description"><span ng-bind-html="editBookingCtrl.getTextByLocale(editBookingCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></span></div>
                    <div class="reduction_value">
                      <span ng-if="params.type == 0">{{ params.value|negative }}{{ editBookingCtrl.settings.currency }}</span>
                      <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                      <span ng-if="params.type == 2">{{ params.value|negative }}{{ editBookingCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                      <span ng-if="params.type == 5">{{ params.value }}</span>
                    </div>
                  </div>
                </div>
                <div ng-if="!editBookingCtrl.displayEditMode || !editBookingCtrl.isNewServiceParameters" class="add_line">
                  <div class="add_service">
                    <input ng-click="editBookingCtrl.createNewServiceParameters()" class="add_service_btn btn_vert" id="add_appointment_in_booking" type="button" value="Ajouter un rendez-vous" />
                  </div>
                </div>
                <div class="resa_sub_total_line" ng-if="editBookingCtrl.getAllServicesParameters().length > 0 && editBookingCtrl.haveGlobalReduction()">
                  <div class="resa_sub_total_line_title">Sous-total réservation</div>
                  <div class="resa_sub_total_line_value">{{ editBookingCtrl.getTotalPriceWithoutCustomReductions() }}{{ editBookingCtrl.settings.currency }}</div>
                </div>
                <div class="resa_custom_line" ng-repeat="customReduction in editBookingCtrl.customReductions track by $index">
                  <btn class="btn resa_custom_line_delete" ng-click="editBookingCtrl.removeCustomReductions($index)">Supprimer</btn>
                  <div class="resa_custom_line_title"><?php _e('custom_line_words', 'resa'); ?></div>
                  <div class="resa_custom_line_description">
                    <span>{{ customReduction.description|htmlSpecialDecode}}</span>
                    <span>(TVA {{ customReduction.vatValue }}%)</span>
                  </div>
                  <div class="resa_custom_line_value">{{ customReduction.amount }}{{ editBookingCtrl.settings.currency }}</div>
                </div>
                <div class="resa_coupon_line" ng-if="params.promoCode.length > 0" ng-repeat="params in editBookingCtrl.mapIdClientObjectReduction['id0'] track by $index">
                  <btn class="btn resa_coupon_line_delete" ng-if="params.promoCode.length > 0" ng-click="editBookingCtrl.deleteCoupon(params.promoCode)">Supprimer</btn>
                  <div class="resa_coupon_line_title">{{params.promoCode}} - {{ editBookingCtrl.getTextByLocale(editBookingCtrl.getReductionById(params.idReduction).name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                  <div class="resa_coupon_line_description"><span ng-bind-html="editBookingCtrl.getTextByLocale(editBookingCtrl.getReductionById(params.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></span></div>
                  <div class="resa_coupon_line_value">
                    <span ng-if="params.type == 0">{{ params.value|negative }}{{ editBookingCtrl.settings.currency }}</span>
                    <span ng-if="params.type == 1">{{ params.value|negative }}%</span>
                    <span ng-if="params.type == 2">{{ params.value|negative }}{{ editBookingCtrl.settings.currency }} <?php _e('on_price_list_words', 'resa') ?></span>
                    <span ng-if="params.type == 5">{{ params.value }}</span>
                  </div>
                </div>
                <div class="resa_total_line">
                  <div class="resa_total_line_title">Total <span ng-if="!editBookingCtrl.quotation">réservation</span><span ng-if="editBookingCtrl.quotation">devis</span></div>
                  <div class="resa_total_line_value">{{ editBookingCtrl.getTotalPrice() }}{{ editBookingCtrl.settings.currency }}</div>
                </div>
                <div ng-if="editBookingCtrl.getAllServicesParameters().length > 0 && editBookingCtrl.currentPromoCodes.length > 0" class="add_line add_coupon">
                  <div class="coupon">
                    <select ng-model="voucherLocal" ng-change="editBookingCtrl.currentPromoCode = voucherLocal">
                      <option value="" disabled selected>-- Coupon --</option>
                      <option ng-repeat="voucher in editBookingCtrl.currentPromoCodes" value="{{ voucher }}">{{ voucher }}</option>
                    </select>
                    <input  ng-disabled="editBookingCtrl.getReductionsLaunched" class="coupon_value" type="text" placeholder="<?php _e('Value_coupon_words','resa'); ?>" ng-model="editBookingCtrl.currentPromoCode" />
                    <button  ng-disabled="editBookingCtrl.getReductionsLaunched" class="coupon_btn btn_vert" type="button" ng-click="editBookingCtrl.getReductions(editBookingCtrl.currentPromoCode)"><?php _e('add_coupon_link_title','resa'); ?><span ng-if="editBookingCtrl.getReductionsLaunched">...</span></button>
                    <div style="color:red;" ng-if="editBookingCtrl.getReductionsLaunched"><br />Calcul réductions en cours, veuillez patienter...</div>
                  </div>
                </div>
                <div ng-if="editBookingCtrl.getAllServicesParameters().length > 0" class="add_line add_coupon">
                  <div class="custom_line">
                    <input class="custom_line_description"  ng-model="editBookingCtrl.currentCustomReduction.description" type="text" placeholder="<?php _e('description_field_title', 'resa') ?>" />
                    <input class="custom_line_value" type="number"  ng-model="editBookingCtrl.currentCustomReduction.amount" placeholder="Valeur ici" />
                    TVA : <select ng-model="editBookingCtrl.currentCustomReduction.vatValue" ng-options="vat.value as editBookingCtrl.getTextByLocale(vat.name,'<?php echo get_locale(); ?>') for vat in editBookingCtrl.settings.vatList"></select>
                    <input ng-click="editBookingCtrl.addNewCustomReductions()" class="custom_line_btn btn_vert" type="button" value="<?php _e('add_custom_reductions_link_title','resa') ?>" />
                  </div>
                </div>
                <div class="add_notes">
                  <div class="note note_private">
                    <h3 class="note_public_title">Note privée</h3>
                    <textarea class="note_public_content" ng-change="editBookingCtrl.bookingLoadedIsModified=true" ng-model="editBookingCtrl.bookingNote"></textarea>
                  </div>
                  <div class="note note_public">
                    <h3 class="note_public_title">Note publique</h3>
                    <textarea class="note_public_content" ng-change="editBookingCtrl.bookingLoadedIsModified=true" ng-model="editBookingCtrl.bookingPublicNote"></textarea>
                  </div>
                  <div class="note note_member">
                    <h3 class="note_public_title">Note pour les {{ editBookingCtrl.getTextByLocale(editBookingCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h3>
                    <textarea class="note_public_content" ng-change="editBookingCtrl.bookingLoadedIsModified=true" ng-model="editBookingCtrl.bookingStaffNote"></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="reservation_history" ng-if="!editBookingCtrl.isNewBooking()">
            <h2 class="add_booking_title">Historique</h2>
            <p>Ici se trouve la liste de toutes les notifications lévées sur cette réservation.<input ng-click="editBookingCtrl.getLogNotificationsHistory()" class="btn" type="button" value="Charger l'historique" /></p>
            <div class="reservation_history_content">
              <div class="history_title" ng-if="editBookingCtrl.logNotificationsHistory.length > 0">
                <div class="history_title_date">Date</div>
                <div class="history_title_hour">Heure</div>
                <div class="history_title_name">Texte</div>
              </div>
              <div class="history_contents" ng-if="editBookingCtrl.logNotificationsHistory.length > 0">
                <div class="history_content" ng-repeat="log in editBookingCtrl.logNotificationsHistory">
                  <div class="history_content_date">{{ log.creationDate | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</div>
                  <div class="history_content_hour">{{ log.creationDate | formatDateTime:'<?php echo $variables['time_format']; ?>' }}</div>
                  <div class="history_content_name"><span ng-bind-html="log.text|htmlSpecialDecode:true"></span></div>
                </div>
              </div>
              <div class="loader_box" ng-if="editBookingCtrl.loadingLogNotificationsHistory">
                <div class="loader_content">
                  <div class="loader">
                    <div class="shadow"></div>
                    <div class="box"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="loader_box" ng-show="editBookingCtrl.launchValidForm || editBookingCtrl.processActionCustomer || editBookingCtrl.loadingLogNotificationsHistory || editBookingCtrl.searchCustomersLaunched">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="resa_popup" id="resa_notification" ng-controller="NotificationController as notificationCtrl" ng-show="notificationCtrl.opened">
      <div class="resa_popup_wrapper">
        <div class="resa_popup_content">
          <div class="notification_reservation">
            <div class="popup_header">
              <div class="resa_popup_header_wrapper">
                <div class="popup_header_title">
                  <h2>Envoyer une notification au client</h2>
                </div>
                <div class="popup_header_menu">
                  <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="notificationCtrl.close()"><span class="title_box_content">Fermer</span></div>
                </div>
              </div>
            </div>
            <div class="popup_content">
              <div ng-if="!notificationCtrl.noBooking()" class="resa_reservation {{ backendCtrl.calculateBookingPayment(notificationCtrl.booking) }}" ng-class="{'quote': notificationCtrl.booking.quotation, 'valid': !notificationCtrl.booking.quotation && notificationCtrl.booking.status=='ok', 'pending': !notificationCtrl.booking.quotation && notificationCtrl.booking.status=='waiting', 'cancelled': !notificationCtrl.booking.quotation && notificationCtrl.booking.status=='cancelled', 'abandonned': !notificationCtrl.booking.quotation && notificationCtrl.booking.status=='abandonned', 'inerror': false, 'incomplete': false}">
                <div class="reservation_client">
                  <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_customer']){ ?>
                    <div class="resa_id">N°{{backendCtrl.getBookingId(notificationCtrl.booking) }}</div>
                    <div class="client_name">
                      <span ng-if="notificationCtrl.customer.lastName.length > 0" class="client_lastname">{{ notificationCtrl.customer.lastName | htmlSpecialDecode }}</span>
                      <span ng-if="notificationCtrl.customer.firstName.length > 0" class="client_firstname">{{notificationCtrl.customer.firstName | htmlSpecialDecode }}</span>
                    </div>
                    <div class="client_company">
                      <span ng-if="notificationCtrl.customer.company.length > 0">{{ notificationCtrl.customer.company | htmlSpecialDecode }}</span>
                      <span ng-if="notificationCtrl.customer.phone.length > 0"><span ng-if="notificationCtrl.customer.company.length > 0"><br /></span>{{ notificationCtrl.customer.phone | formatPhone }}</span>
                      <span ng-if="notificationCtrl.customer.phone2.length > 0"><span ng-if="notificationCtrl.customer.company.length > 0 || notificationCtrl.customer.phone.length > 0"><br /></span>{{ notificationCtrl.customer.phone2 | formatPhone }}</span>
                    </div>
                    <div ng-if="notificationCtrl.customer.privateNotes.length > 0" class="client_note on b1 helpbox">
                      1 Note Client
                      <span class="helpbox_content"><h4>Note privé client :</h4><p ng-bind-html="notificationCtrl.customer.privateNotes|displayNewLines"></p></span>
                    </div>
                  <?php } ?>
                </div>
                <div class="reservation_states">
                  <div class="states_state state_valid">Validée</div>
                  <div class="states_state state_quote">{{ backendCtrl.getQuotationName(notificationCtrl.booking) }}</div>
                  <div class="states_state state_cancelled">Annulée</div>
                  <div class="states_state state_abandonned">Abandonnée</div>
                  <div class="states_state state_pending">{{ backendCtrl.getWaitingName(notificationCtrl.booking) }}</div>
                  <div class="states_state state_inerror">Erreur</div>
                  <div class="states_state state_incomplete">Incomplète</div>
                  <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_payments']){ ?>
                    <div class="states_paiement" ng-class="{'helpbox':notificationCtrl.booking.payments.length > 0}">
                      <div class="state_paiement paiement_none">Pas de paiement</div>
                      <div class="state_paiement paiement_incomplete">
                        <span ng-if="!backendCtrl.isDeposit(notificationCtrl.booking)">Acompte</span>
                        <span ng-if="backendCtrl.isDeposit(notificationCtrl.booking)">Caution</span>
                      </div>
                      <div class="state_paiement paiement_done">Encaissée</div>
                      <div class="state_paiement paiement_overpaiement">Trop perçu</div>
                      <div class="state_paiement paiement_remboursement">Remboursement dû</div>
                      <div class="state_paiement paiement_remboursement_done">Remboursement complet</div>
                      <div class="helpbox_content" ng-if="notificationCtrl.booking.payments.length > 0">
      									<table class="table">
      										<thead>
      											<tr>
      												<th>Date</th>
      												<th>Montant</th>
      												<th>Type de paiement</th>
      												<th>Note</th>
      											</tr>
      										</thead>
      										<tbody>
      											<tr ng-if="payment.state != 'pending'" ng-repeat="payment in notificationCtrl.booking.payments">
      												<td>{{ payment.paymentDate | formatDateTime:'<?php echo $variables['date_format'].' '.$variables['time_format']; ?>' }}
      					              <span class="ro-cancelled" ng-if="payment.state=='cancelled'"><br /><?php _e('cancelled_word', 'resa'); ?></span></td>
      												<td ng-if="payment.repayment">{{ payment.value|negative }}{{ backendCtrl.settings.currency }}</td>
      												<td ng-if="!payment.repayment">{{ payment.value }}{{ backendCtrl.settings.currency }}</td>
                              <td>
                                <span ng-if="payment.isReceipt != null && payment.isReceipt && payment.value >= 0">[Acompte]</span>
                                <span ng-if="payment.isReceipt != null && payment.isReceipt && payment.value < 0">[Remboursement acompte]</span>
                                {{ backendCtrl.getPaymentName(payment.type, payment.name) }}
                              </td>
      												<td class="ng-binding">{{ payment.note }}</td>
      											</tr>
      										</tbody>
      									</table>
      								</div>
                    </div>
                    <div class="states_paiement_value">
                      <span ng-if="notificationCtrl.booking.status != 'cancelled' && notificationCtrl.booking.status != 'abandonned'">
                        <span ng-if="notificationCtrl.booking.paymentsCaisseOnlineDone || (!backendCtrl.isCaisseOnlineActivated() && backendCtrl.round(notificationCtrl.booking.totalPrice - notificationCtrl.booking.needToPay) > 0)">{{ backendCtrl.round(notificationCtrl.booking.totalPrice - notificationCtrl.booking.needToPay) }}{{ backendCtrl.settings.currency }} / </span>
                        {{ notificationCtrl.booking.totalPrice }}{{ backendCtrl.settings.currency }}
                      </span>
                      <span ng-if="(notificationCtrl.booking.status == 'cancelled' || notificationCtrl.booking.status == 'abandonned') && notificationCtrl.booking.needToPay < 0 && (notificationCtrl.booking.paymentsCaisseOnlineDone ||  !backendCtrl.isCaisseOnlineActivated())">
                        {{ -(notificationCtrl.booking.needToPay - notificationCtrl.booking.totalPrice) }}{{ backendCtrl.settings.currency }}
                      </span>
                      <a ng-if="backendCtrl.isCaisseOnlineActivated()" ng-click="backendCtrl.getPaymentsForBooking(notificationCtrl.booking)">Paiements</a>
                    </div>
                  <?php } ?>
                </div>
                <div class="reservation_notes">
                  <?php if(!RESA_Variables::staffIsConnected()){ ?>
                  <div class="reservation_private_note" ng-class="{'on helpbox': notificationCtrl.booking.note.length > 0, 'off': notificationCtrl.booking.note.length == 0}">
                    Note privée
                    <span class="helpbox_content" ng-if="notificationCtrl.booking.note.length > 0"><h4>Note privée :</h4><p ng-bind-html="notificationCtrl.booking.note|htmlSpecialDecode:true"></p></span>
                  </div>
                  <div class="reservation_public_note" ng-class="{'on helpbox': notificationCtrl.booking.publicNote.length > 0, 'off': notificationCtrl.booking.publicNote.length == 0}">
                    Note publique
                    <span class="helpbox_content" ng-if="notificationCtrl.booking.publicNote.length > 0"><h4>Note publique :</h4><p ng-bind-html="notificationCtrl.booking.publicNote|htmlSpecialDecode:true"></p></span>
                  </div>
                  <div class="reservation_customer_note" ng-class="{'on helpbox': notificationCtrl.booking.customerNote.length > 0, 'off': notificationCtrl.booking.customerNote.length == 0}">
                    Remarque cliente
                    <span class="helpbox_content" ng-if="notificationCtrl.booking.customerNote.length > 0"><h4>Remarque cliente :</h4><p ng-bind-html="notificationCtrl.booking.customerNote|htmlSpecialDecode:true"></p></span>
                  </div>
                  <?php } ?>
                  <div ng-if="backendCtrl.settings.staffManagement" class="reservation_member_note" ng-class="{'on helpbox': notificationCtrl.booking.staffNote.length > 0, 'off': notificationCtrl.booking.staffNote.length == 0}">
                    Note {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                    <span class="helpbox_content" ng-if="notificationCtrl.booking.staffNote.length > 0"><h4>Note des {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} :</h4><p ng-bind-html="notificationCtrl.booking.staffNote|htmlSpecialDecode:true"></p></span>
                  </div>
                </div>
                <div class="reservation_suivi">
                  <div class="suivi_title">
                    <div class="title_date">Créée le </div>
                    <div class="title_time">à</div>
                    <div class="title_user"><?php _e('by_word', 'resa') ?></div>
                  </div>
                  <div class="suivi_content">
                    <div class="content_date">{{ notificationCtrl.booking.creationDate|formatDateTime:'<?php echo $variables['date_format']; ?>' }}</div>
                    <div class="content_time">{{ notificationCtrl.booking.creationDate|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</div>
                    <div class="content_user">{{ notificationCtrl.booking.userCreator }}</div>
                  </div>
                </div>
                <div class="reservation_actions no-print">
                  <?php if(!RESA_Variables::staffIsConnected()){ ?>
                  <span class="btn btn_client title_box dashicons dashicons-admin-users" ng-click="backendCtrl.openCustomerDialog(notificationCtrl.booking.idCustomer); notificationCtrl.close()">
                    <span class="title_box_content">Voir la fiche client</span>
                  </span>
                  <span class="btn btn_client title_box dashicons dashicons-cart" ng-if="backendCtrl.isCaisseOnlineActivated() && !notificationCtrl.booking.quotation" ng-click="backendCtrl.payBookingCaisseOnline(notificationCtrl.booking)">
                    <span class="title_box_content">Envoyer la réservation sur la caisse</span>
                  </span>
                  <span class="btn btn_client title_box dashicons dashicons-tag" ng-if="!backendCtrl.isCaisseOnlineActivated() && !notificationCtrl.booking.quotation"
                  ng-click="backendCtrl.openPaymentStateDialog(notificationCtrl.booking);">
                    <span class="title_box_content">Changer l'état de paiement</span>
                  </span>
                  <?php } ?>
        				</div>
                <div class="reservation_bookings">
                  <div class="booking" ng-class="{'confirmed':appointment.state == 'ok','not_confirmed':appointment.state == 'waiting','cancelled':appointment.state == 'cancelled' || appointment.state == 'abandonned'}" ng-repeat="appointment in notificationCtrl.booking.appointments|orderBy:'startDate'">
                    <div class="booking_date">{{ appointment.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }}</div>
                    <div class="booking_hour">
                      <span ng-if="!appointment.noEnd">
      									{{ appointment.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      									<?php _e('to_word', 'resa') ?>
      									{{ appointment.endDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      								</span>
      								<span ng-if="appointment.noEnd">
      									<?php _e('begin_word', 'resa'); ?> {{ appointment.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      								</span>
                    </div>
                    <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_numbers']){ ?>
                    <div class="booking_nb_people" ng-class="{'helpbox': backendCtrl.getServiceById(appointment.idService).askParticipants}">
                      {{ appointment.numbers }} pers.
                      <span ng-if="backendCtrl.getServiceById(appointment.idService).askParticipants" class="helpbox_content"><h4>Participants</h4>
                        <span ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                          <table>
                            <thead>
                              <tr>
                                <td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">
                                  {{ backendCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
                                </td>
                              </tr>
                            </thead>
                            <tbody>
                              <tr ng-repeat="participant in appointmentNumberPrice.participants track by $index"><td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">{{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</td></tr>
                            </tbody>
                            <tfoot  ng-if="backendCtrl.isDisplaySum(backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields)">
                              <tr>
                                <td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields"><span ng-if="field.displaySum">Somme : {{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getDisplaySum(field, appointmentNumberPrice.participants) }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</span></td>
                              </tr>
                            </tfoot>
                          </table>
                        </span>
                      </span>
                    </div>
                    <?php } ?>
                    <div class="booking_states">
                      <div class="states_state state_confirmed">Confirmée</div>
                      <div class="states_state state_not_confirmed">Non confirmée</div>
                      <div class="states_state state_cancelled">Annulée</div>
                      <div class="states_state state_inerror">Erreur</div>
                      <div class="states_state state_incomplete">Incomplète</div>
                    </div>
                    <div class="booking_place"><span ng-if="appointment.idPlace != ''">{{ backendCtrl.getTextByLocale(backendCtrl.getPlaceById(appointment.idPlace).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span></div>
                    <div class="booking_service">{{ backendCtrl.getTextByLocale(
      										backendCtrl.getServiceById(appointment.idService).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                    <div class="booking_prices">
                      <div class="prices_price" ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                        <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_numbers']){ ?>
                        <span class="price_nb">{{ appointmentNumberPrice.number }}</span>
                        <?php } ?>
                        <span class="price_name">
                          {{ backendCtrl.getServicePriceAppointmentName(appointment.idService, appointmentNumberPrice.idPrice) |htmlSpecialDecode
        									}}
                          <span ng-if="backendCtrl.haveEquipments(appointment.idService, appointmentNumberPrice.idPrice)">
                            ({{ backendCtrl.getServicePriceAppointmentEquipmentName(appointment.idService, appointmentNumberPrice.idPrice)|htmlSpecialDecode
          									}})
                          </span>
                        </span>
                        <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_payments']){ ?>
                          <span class="price_value" ng-if="!appointmentNumberPrice.deactivated">
                            <span ng-if="appointmentNumberPrice.totalPrice != 0">{{ appointmentNumberPrice.totalPrice }}  {{ backendCtrl.settings.currency }}</span>
                            <span ng-if="appointmentNumberPrice.totalPrice == 0">
                              <span ng-if="backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
                                {{ backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).price }} {{ backendCtrl.settings.currency }}
                              </span>
                              <span ng-if="!backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
                                {{ backendCtrl.getPriceNumberPrice(appointment.idService, appointmentNumberPrice) }} {{ backendCtrl.settings.currency }}
                              </span>
                            </span>
                          </span>
                        <?php } ?>
                      </div>
                    </div>
                    <div class="booking_members" ng-if="backendCtrl.settings.staffManagement">
                      <div class="member" ng-if="appointmentMember.number > 0" ng-repeat="appointmentMember in appointment.appointmentMembers">{{ backendCtrl.getMemberById(appointmentMember.idMember).nickname|htmlSpecialDecode }}<br /></div>
                    </div>
                    <div class="booking_tags">
                      <div class="tag {{ backendCtrl.getTagById(idTag).color }}" ng-repeat="idTag in appointment.tags">
                        {{ backendCtrl.getTextByLocale(backendCtrl.getTagById(idTag).title, '<?php echo get_locale(); ?>') }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="client_notifications">
                <div class="notifications_email">
                  <h3 class="notifications_email_title">Notifications emails</h3>
                  <div style="color:orange" ng-if="notificationCtrl.settings.customer_account_url == null || notificationCtrl.settings.customer_account_url.length == 0">Le lien vers le compte client n'est pas défini !</div>
                  <div style="color:orange" ng-if="notificationCtrl.customer.email == null || notificationCtrl.customer.email.length == 0">L'email de ce client n'est pas défini !</div>
                  <div class="notification">
                    <div class="notif_title">Réinitialisation du mot de passe</div>
                    <input class="btn btn_wide notif_btn" ng-disabled="!notificationCtrl.notificationCustomerAccountEmailOk()" value="Envoyer" type="submit" ng-class="{'btn_locked': !notificationCtrl.notificationCustomerAccountEmailOk()}"  ng-click="notificationCtrl.sentMessagePasswordReinitialization({
                      title: '<?php _e('ask_send_email_creation_account_title_dialog','resa') ?>',
                      text: '<?php _e('ask_send_email_creation_account_text_dialog','resa') ?>',
                      confirmButton: '<?php _e('ask_send_email_creation_account_confirmButton_dialog','resa') ?>',
                      cancelButton: '<?php _e('ask_send_email_creation_account_cancelButton_dialog','resa') ?>'
                    })" />
                    <span class="warning" ng-if="!notificationCtrl.settings.notification_customer_password_reinit"><br />Notification réinitialisation email désactivée</span>
                    <span class="warning" ng-if="notificationCtrl.settings.customer_account_url.length == 0"><br />Lien du compte client erroné</span>
                  </div>
                  <div class="notification" ng-if="!notificationCtrl.isQuotation() && !notificationCtrl.noBooking()">
                    <div class="notif_title">Récapitulatif de la réservation</div>
                    <input ng-disabled="!notificationCtrl.notificationBookingEmailOk()" class="btn btn_wide notif_btn" ng-class="{'btn_locked': !notificationCtrl.notificationBookingEmailOk()}" type="submit" value="Envoyer" ng-click="notificationCtrl.displayEmailBookingContent()" />
                    <span class="warning" ng-if="notificationCtrl.customer.email == null || notificationCtrl.customer.email.length == 0"><br />Ce client ne possède pas d'adresse email</span>
                    <span class="warning" ng-if="!notificationCtrl.settings.notification_customer_booking"><br />Notification désactivée dans les réglages</span>
                    <span class="warning" ng-if="notificationCtrl.customer.deactivateEmail"><br />Ce client ne souhaite plus recevoir de notification</span>
                  </div>
                  <div class="notification" ng-if="!notificationCtrl.isQuotation() && !notificationCtrl.noBooking()">
                    <div class="notif_title">Demande de paiement</div>
                    <input ng-disabled="!notificationCtrl.canNotifyCustomer()" class="btn btn_wide notif_btn" type="submit" value="Editer" ng-click="notificationCtrl.displayAskPaymentEmailContent()" />
                    <span class="warning" ng-if="notificationCtrl.customer.deactivateEmail"><br />Ce client ne souhaite plus recevoir de notification</span>
                  </div>
                  <div class="notification" ng-if="!notificationCtrl.isQuotation() && !notificationCtrl.noBooking()">
                    <div class="notif_title">Demande d'avis client</div>
                    <input ng-disabled="!notificationCtrl.notificationAfterBookingEmailOk()" class="btn btn_wide notif_btn" ng-class="{'btn_locked': !notificationCtrl.notificationAfterBookingEmailOk()}" type="submit" value="Editer" ng-click="notificationCtrl.displayAfterAppointmentEmailContent()" />
                    <span class="warning" ng-if="notificationCtrl.customer.email == null || notificationCtrl.customer.email.length == 0"><br />Ce client ne possède pas d'adresse email</span>
                    <span class="warning" ng-if="!notificationCtrl.settings.notification_after_appointment"><br />Notification désactivée dans les réglages</span>
                    <span class="warning" ng-if="notificationCtrl.customer.deactivateEmail"><br />Ce client ne souhaite plus recevoir de notification</span>
                  </div>
                  <div class="notification" ng-if="notificationCtrl.isQuotation() && !notificationCtrl.isQuotationRequest()  && !notificationCtrl.noBooking()">
                    <div class="notif_title">Rappel devis en attente / Nouveau devis</div>
                    <input ng-disabled="!notificationCtrl.notificationBookingEmailOk()" class="btn btn_wide notif_btn" ng-class="{'btn_locked': !notificationCtrl.notificationBookingEmailOk()}" type="submit" value="Editer" ng-click="notificationCtrl.displayEmailBookingQuotationContent();" />
                    <span class="warning" ng-if="notificationCtrl.customer.email == null || notificationCtrl.customer.email.length == 0">Ce client ne possède pas d'adresse email</span>
                    <span class="warning" ng-if="notificationCtrl.customer.deactivateEmail"><br />Ce client ne souhaite plus recevoir de notification</span>
                  </div>
                  <div class="notification" ng-if="notificationCtrl.isQuotation() && !notificationCtrl.isQuotationRequest() && !notificationCtrl.noBooking()">
                    <div class="notif_title">Envoyer devis et demande de paiement</div>
                    <input  ng-disabled="!notificationCtrl.canNotifyCustomer()" class="btn btn_wide notif_btn" type="submit" value="Editer" ng-click="notificationCtrl.displayAskPaymentEmailContent()" />
                    <span class="warning" ng-if="notificationCtrl.customer.deactivateEmail"><br />Ce client ne souhaite plus recevoir de notification</span>
                  </div>
                  <div class="notification" ng-if="notificationCtrl.isQuotation() && notificationCtrl.isQuotationRequest()">
                    <div class="notif_title">Accepter ce devis</div>
                    <input ng-disabled="!notificationCtrl.notificationBookingEmailOk()" class="btn btn_wide notif_btn" ng-class="{'btn_locked': !notificationCtrl.notificationBookingEmailOk()}" type="submit" value="Editer" ng-click="notificationCtrl.displayAcceptQuotationContent();" />
                    <span class="warning" ng-if="!notificationCtrl.settings.notification_quotation_accepted_customer"><br />Notification accepté devis désactivée</span>
                    <span class="warning" ng-if="notificationCtrl.customer.email == null || notificationCtrl.customer.email.length == 0">Ce client ne possède pas d'adresse email</span>
                    <span class="warning" ng-if="notificationCtrl.customer.deactivateEmail"><br />Ce client ne souhaite plus recevoir de notification</span>
                  </div>
                  <div class="notification" ng-if="notificationCtrl.isQuotation() && notificationCtrl.isQuotationRequest() && !notificationCtrl.noBooking()">
                    <div class="notif_title">Accepter ce devis et demande de paiement</div>
                    <input class="btn btn_wide notif_btn" type="submit" value="Editer" ng-click="notificationCtrl.displayAcceptQuotationAndAskPaymentContent()" />
                    <span class="warning" ng-if="!notificationCtrl.settings.notification_quotation_accepted_customer"><br />Notification accepté devis désactivée</span>
                    <span class="warning" ng-if="notificationCtrl.customer.email == null || notificationCtrl.customer.email.length == 0">Ce client ne possède pas d'adresse email</span>
                    <span class="warning" ng-if="notificationCtrl.customer.deactivateEmail"><br />Ce client ne souhaite plus recevoir de notification</span>
                  </div>
                  <div class="notification">
                    <div class="notif_title">Email libre</div>
                    <input ng-disabled="!notificationCtrl.canNotifyCustomer()" class="btn btn_wide notif_btn" type="submit" value="Rédiger" ng-click="notificationCtrl.displayCustomEmailContent()" />
                    <span class="warning" ng-if="notificationCtrl.customer.email == null || notificationCtrl.customer.email.length == 0">Ce client ne possède pas d'adresse email</span>
                    <span class="warning" ng-if="notificationCtrl.customer.deactivateEmail"><br />Ce client ne souhaite plus recevoir de notification</span>
                  </div>
                </div>
                <!--
                <div class="notifications_sms">
                  <h3 class="notifications_sms_title">Notifications sms</h3>
                  <p>Bientôt disponible...</p>
                  <div class="notification no">
                    <div class="notif_title">Création du compte client </div>
                    <input class="btn notif_btn" value="Envoyer" />
                  </div>
                  <div class="notification no">
                    <div class="notif_title">Réinitialisation du mot de passe </div>
                    <input class="btn notif_btn" value="Envoyer" />
                  </div>
                  <div class="notification no">
                    <div class="notif_title">Récapitulatif de la réservation</div>
                    <input class="btn notif_btn" value="Envoyer" />
                  </div>
                  <div class="notification no">
                    <div class="notif_title">Demande de paiement</div>
                    <input class="btn notif_btn" value="Envoyer" />
                  </div>
                  <div class="notification no">
                    <div class="notif_title">Email libre</div>
                    <textarea class="notif_textare"></textarea>
                    <input class="btn notif_btn" value="Envoyer" />
                  </div>
                </div>
                //-->
              </div>
              <div class="email_notification_content" ng-show="notificationCtrl.displayEmailContent">
                <div class="notification_content_options">
                  <div class="ask_paiement" ng-if="notificationCtrl.typeEmailContent == 'askPayment' || notificationCtrl.typeEmailContent == 'acceptQuotationAndAskPayment'">
                    <h3 class="content_options_title">Options de demande de paiement</h3>
                    <div class="value btn_wide">Total réservation  : {{ notificationCtrl.getTotalAmount() }}{{ notificationCtrl.settings.currency }}</div>
                    <div class="ask_paiement_options">
                      <div class="ask_paiement_metods">
                        <h4>Méthodes de paiement proposées</h4>
                        <div ng-if="payment.activated && payment.id!='onTheSpot' && payment.id!='later' && !payment.custom && notificationCtrl.getTypeAccountOfCustomer().paymentsTypeList[payment.id]" class="ask_paiement_metod" ng-repeat="payment in notificationCtrl.paymentsTypeList">
                          <input id="{{ $index }}" type="checkbox" ng-model="notificationCtrl.paymentsType[$index]" ng-true-value="'{{ payment.id }}'" ng-false-value="''" />
                          <label for="{{ $index }}">{{ payment.title }}</label>
                        </div>
                      </div>
                      <div class="ask_paiement_account">
                        <h4>Demande expire après : </h4>
                        <select ng-model="notificationCtrl.expiredDays">
                          <option ng-selected="notificationCtrl.expiredDays == 2">2</option>
                          <option>3</option>
                          <option>4</option>
                          <option>5</option>
                          <option>10</option>
                          <option>20</option>
                          <option>30</option>
                        </select>
                        <label for="acompte">Jours</label><br />
                        Date d'expiration : {{ notificationCtrl.getExpirationDate()  | formatDateTime:'<?php echo $variables['date_format']; ?>' }}
                        <div ng-if="notificationCtrl.canPayAdvancePayment()">
                          <h4>Autoriser le paiement d'un acompte</h4>
                          <input id="acompte" type="checkbox" ng-true-value="false" ng-false-value="true" ng-model="notificationCtrl.stopAdvancePayment" />
                          <label for="acompte">Acompte</label>
                        </div>
                        <div ng-if="!notificationCtrl.canPayAdvancePayment()">
                          <b class="warning"><br />Attention, ce type de compte n'est pas autorisé à payer un acompte !</b>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="notification_content_render">
                  <h3 class="content_render_title">Aperçu de l'email</h3>
                  <div class="content_render_template">
                    Charger template :
                    <select class="render_template_select" ng-model="notificationCtrl.indexTemplate">
                      <option value="-1" ng-selected="notificationCtrl.indexTemplate == -1">Par défaut</option>
                      <option value="{{ $index }}" ng-repeat="notification in notificationCtrl.settings['notifications'].notifications_templates track by $index">{{ notification.name }}</option>
                    </select>
                    <input ng-click="notificationCtrl.changeTemplate()" class="btn btn_vert" type="button" value="Charger ce template" />
                    <input ng-if="notificationCtrl.indexTemplate >= 0" ng-disabled="notificationCtrl.launchNotificationAction" ng-click="notificationCtrl.deleteTemplate({
                      title: 'Supprimer ?',
                      text: 'Voulez-vous supprimer ce template ?',
                      confirmButton: 'Oui',
                      cancelButton: 'Non'
                    })" class="btn btn_rouge" type="button" value="Supprimer ce template" />
                  </div>
                  <div class="resa_lang" ng-if="notificationCtrl.settings.languages.length > 1">
          					<span ng-class="{'active_lang': notificationCtrl.currentLanguage == language}" ng-click="notificationCtrl.setCurrentLanguage(language)" ng-repeat="language in notificationCtrl.settings.languages">
          						 <img ng-src="<?php echo plugin_dir_url(__FILE__).'../images/flags/'; ?>{{ language }}.png" />
          						</span>
          				</div>
                  <div ng-if="notificationCtrl.settings.senders.length > 1 && notificationCtrl.noBooking()" class="content_render_subject">
                    <span>Email d'envoie</span>
                    <select ng-model="notificationCtrl.currentSender" ng-options="sender as (sender.sender_name + '<'+ sender.sender_email + '>') for sender in notificationCtrl.settings.senders"></select>
                  </div>
                  <div class="content_render_subject">
                    <span>Sujet de l'email</span>
                    <input class="btn" type="text" value="Sujet de l'email ici" ng-model="notificationCtrl.subject[notificationCtrl.currentLanguage]" />
                  </div>
                  <div id="resa_form" class="content_render_editor">
                    <!-- Ici se trouve l'editeur -->
                     <?php wp_editor('', 'notificationEditor'); ?>
                     <wp-editor-wrapper ng-if="notificationCtrl.displayEmailContent" id="'notificationEditor'" ng-model="notificationCtrl.message[notificationCtrl.currentLanguage]">
    								 </wp-editor-wrapper>
                  </div>
                  <div style="color:red; font-weight: bold;" ng-if="notificationCtrl.isRESAAdminOrStaff()">Shortcode du lien compte client et du lien de paiement sont désactivés pour les managers et les admins !!!</div>
                  <div style="color:red; font-weight: bold;" ng-if="notificationCtrl.isNotWpRESACustomer()">Cet utilisateur ne possède pas de compte sur le site, il ne pourra pas payer ou voir ses réservations</div>
                  <span class="warning" ng-if="notificationCtrl.customer.email == null || notificationCtrl.customer.email.length == 0">Ce client ne possède pas d'adresse email</span>
                  <span class="warning" ng-if="notificationCtrl.customer.deactivateEmail"><br />Ce client ne souhaite plus recevoir de notification</span>
                  <div class="content_render_actions">
                    <div class="render_actions_send">
                      <h4>Envoyer / Annuler</h4>
                      <div ng-if="(notificationCtrl.typeEmailContent == 'askPayment' || notificationCtrl.typeEmailContent == 'acceptQuotationAndAskPayment') && notificationCtrl.alreadyAskPayment()" class="warning">
                        Cette réservation possède déjà une demande de paiement mais vous pouvez toujours en refaire une nouvelle.
                      </div>
                      <label>
                        <input type="checkbox" ng-model="notificationCtrl.checkboxNotCloseAfterSend" />Ne pas fermer la fenêtre après l'envoie de la notification !<br /><br />
                      </label>
                      <input ng-disabled="!notificationCtrl.canToSendEmail()" class="btn" ng-class="{'btn_locked': !notificationCtrl.canToSendEmail(), 'btn_vert': notificationCtrl.canToSendEmail()}" type="submit" value="Envoyer au client" ng-click="notificationCtrl.sendEmailNotification({
                          title: '<?php _e('ask_send_email_to_customer_title_dialog','resa') ?>',
                          text: '<?php _e('ask_send_email_to_customer_text_dialog','resa') ?>',
                          confirmButton: '<?php _e('ask_send_email_to_customer_confirmButton_dialog','resa') ?>',
                          cancelButton: '<?php _e('ask_send_email_to_customer_cancelButton_dialog','resa') ?>'
                        });" />
                      <input ng-click="notificationCtrl.displayEmailContent = false" class="btn btn_rouge" type="button" value="Annuler" />
                      <div style="color:red; font-weight: bold;" ng-if="notificationCtrl.isNotWpRESACustomer() && (notificationCtrl.haveLinkCustomerAccount(notificationCtrl.message[notificationCtrl.currentLanguage]) || notificationCtrl.haveLinkPaymentBooking(notificationCtrl.message[notificationCtrl.currentLanguage]))">
                        Ce client n'a pas de compte sur le site, vous ne pouvez pas lui envoyer un lien de connexion à son compte ! Veuillez enlever le lien du message pour continuer ou lui créer un compte sur le site.
                      </div>
                    </div>
                    <div class="render_actions_test">
                      <h4>Envoyer un aperçu a l'adresse :</h4>
                      <input ng-disabled="!notificationCtrl.canToSendEmail()" class="btn" type="text" ng-model="notificationCtrl.currentPreviousEmail" />
                      <input ng-disabled="!notificationCtrl.canToSendEmail()" class="btn" ng-class="{'btn_locked': !notificationCtrl.canToSendEmail(), 'btn_vert': notificationCtrl.canToSendEmail()}" class="btn btn_bleu" ng-click="notificationCtrl.sendCustomEmail(notificationCtrl.currentPreviousEmail, false)" type="button" value="Envoyer" />
                    </div>
                    <div class="render_actions_test">
                      <h4>Enregistrer comme template :</h4>
                      <input ng-model="notificationCtrl.nameTemplate" class="btn" type="text" value="Nom du template" />
                      <input ng-if="notificationCtrl.indexTemplate > -1" ng-click="notificationCtrl.editTemplate()" class="btn btn_jaune" type="button" value="Enregistrer" />
                      <input ng-click="notificationCtrl.createTemplate()" class="btn btn_jaune" type="button" value="Créer nouveau template" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="popup_footer">
              <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="notificationCtrl.close()"><span class="title_box_content">Fermer</span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="loader_box" ng-show="notificationCtrl.launchNotificationAction || backendCtrl.payBookingActionLaunched">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="resa_popup" id="resa_notification" ng-controller="DisplayBookingsController as displayBookingsCtrl" ng-show="displayBookingsCtrl.opened">
      <div class="resa_popup_wrapper">
        <div class="resa_popup_content">
          <div class="notification_reservation">
            <div class="popup_header">
              <div class="resa_popup_header_wrapper">
                <div class="popup_header_title">
                  <h2>Réservation</h2>
                </div>
                <div class="popup_header_menu">
                  <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="displayBookingsCtrl.close()"><span class="title_box_content">Fermer</span></div>
                </div>
              </div>
            </div>
            <div class="popup_content" ng-if="!displayBookingsCtrl.noBookings()">
              <div ng-repeat="booking in displayBookingsCtrl.bookings" class="resa_reservation {{ backendCtrl.calculateBookingPayment(booking) }}" ng-class="{'quote': booking.quotation, 'valid': !booking.quotation && booking.status=='ok', 'pending': !booking.quotation && booking.status=='waiting', 'cancelled': !booking.quotation && booking.status=='cancelled', 'abandonned': !booking.quotation && booking.status=='abandonned', 'inerror': false, 'incomplete': false}">
                <div class="reservation_client">
                  <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_customer']){ ?>
                    <div class="resa_id">N°{{backendCtrl.getBookingId(booking) }}</div>
                    <div class="client_name">
                      <span ng-if="booking.customer.lastName.length > 0" class="client_lastname">{{ booking.customer.lastName | htmlSpecialDecode }}</span>
                      <span ng-if="booking.customer.firstName.length > 0" class="client_firstname">{{booking.customer.firstName | htmlSpecialDecode }}</span>
                    </div>
                    <div class="client_company">
                      <span ng-if="booking.customer.company.length > 0">{{ booking.customer.company | htmlSpecialDecode }}</span>
                      <span ng-if="booking.customer.phone.length > 0"><span ng-if="booking.customer.company.length > 0"><br /></span>{{ booking.customer.phone | formatPhone }}</span>
                      <span ng-if="booking.customer.phone2.length > 0"><span ng-if="booking.customer.company.length > 0 || booking.customer.phone.length > 0"><br /></span>{{ booking.customer.phone2 | formatPhone }}</span>
                    </div>
                    <div ng-if="booking.customer.privateNotes.length > 0" class="client_note on b1 helpbox">
                      1 Note Client
                      <span class="helpbox_content"><h4>Note privé client :</h4><p ng-bind-html="booking.customer.privateNotes|htmlSpecialDecode:true"></p></span>
                    </div>
                  <?php } ?>
                </div>
                <div class="reservation_states">
                  <div class="states_state state_valid">Validée</div>
                  <div class="states_state state_quote">{{ backendCtrl.getQuotationName(booking) }}</div>
                  <div class="states_state state_cancelled">Annulée</div>
                  <div class="states_state state_abandonned">Abandonnée</div>
                  <div class="states_state state_pending">{{ backendCtrl.getWaitingName(booking) }}</div>
                  <div class="states_state state_inerror">Erreur</div>
                  <div class="states_state state_incomplete">Incomplète</div>
                  <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_payments']){ ?>
                    <div class="states_paiement" ng-class="{'helpbox':booking.payments.length > 0}">
                      <div class="state_paiement paiement_none">Pas de paiement</div>
                      <div class="state_paiement paiement_incomplete">
                        <span ng-if="!backendCtrl.isDeposit(booking)">Acompte</span>
                        <span ng-if="backendCtrl.isDeposit(booking)">Caution</span>
                      </div>
                      <div class="state_paiement paiement_done">Encaissée</div>
                      <div class="state_paiement paiement_overpaiement">Trop perçu</div>
                      <div class="state_paiement paiement_remboursement">Remboursement dû</div>
                      <div class="state_paiement paiement_remboursement_done">Remboursement complet</div>
                      <div class="helpbox_content" ng-if="booking.payments.length > 0">
      									<table class="table">
      										<thead>
      											<tr>
      												<th>Date</th>
      												<th>Montant</th>
      												<th>Type de paiement</th>
      												<th>Note</th>
      											</tr>
      										</thead>
      										<tbody>
      											<tr ng-if="payment.state != 'pending'" ng-repeat="payment in booking.payments">
      												<td>{{ payment.paymentDate | formatDateTime:'<?php echo $variables['date_format'].' '.$variables['time_format']; ?>' }}
      					              <span class="ro-cancelled" ng-if="payment.state=='cancelled'"><br /><?php _e('cancelled_word', 'resa'); ?></span></td>
      												<td ng-if="payment.repayment">{{ payment.value|negative }}{{ backendCtrl.settings.currency }}</td>
      												<td ng-if="!payment.repayment">{{ payment.value }}{{ backendCtrl.settings.currency }}</td>
                              <td>
                                <span ng-if="payment.isReceipt != null && payment.isReceipt && payment.value >= 0">[Acompte]</span>
                                <span ng-if="payment.isReceipt != null && payment.isReceipt && payment.value < 0">[Remboursement acompte]</span>
                                {{ backendCtrl.getPaymentName(payment.type, payment.name) }}
                              </td>
      												<td class="ng-binding">{{ payment.note }}</td>
      											</tr>
      										</tbody>
      									</table>
      								</div>
                    </div>
                    <div class="states_paiement_value">
                      <span ng-if="booking.status != 'cancelled' && booking.status != 'abandonned'">
                        <span ng-if="booking.paymentsCaisseOnlineDone || (!backendCtrl.isCaisseOnlineActivated() && backendCtrl.round(booking.totalPrice - booking.needToPay) > 0)">{{ backendCtrl.round(booking.totalPrice - booking.needToPay) }}{{ backendCtrl.settings.currency }} / </span>
                        {{ booking.totalPrice }}{{ backendCtrl.settings.currency }}
                      </span>
                      <span ng-if="(booking.status == 'cancelled' || booking.status == 'abandonned') && booking.needToPay < 0 && (booking.paymentsCaisseOnlineDone ||  !backendCtrl.isCaisseOnlineActivated())">
                        {{ -(booking.needToPay - booking.totalPrice) }}{{ backendCtrl.settings.currency }}
                      </span>
                      <a ng-if="backendCtrl.isCaisseOnlineActivated()" ng-click="backendCtrl.getPaymentsForBooking(booking)">Paiements</a>
                    </div>
                  <?php } ?>
                </div>
                <div class="reservation_notes">
                  <?php if(!RESA_Variables::staffIsConnected()){ ?>
                  <div class="reservation_private_note" ng-class="{'on helpbox': booking.note.length > 0, 'off': booking.note.length == 0}">
                    Note privée
                    <span class="helpbox_content" ng-if="booking.note.length > 0"><h4>Note privée :</h4><p ng-bind-html="booking.note|htmlSpecialDecode:true"></p></span>
                  </div>
                  <div class="reservation_public_note" ng-class="{'on helpbox': booking.publicNote.length > 0, 'off': booking.publicNote.length == 0}">
                    Note publique
                    <span class="helpbox_content" ng-if="booking.publicNote.length > 0"><h4>Note publique :</h4><p ng-bind-html="booking.publicNote|htmlSpecialDecode:true"></p></span>
                  </div>
                  <div class="reservation_customer_note" ng-class="{'on helpbox': booking.customerNote.length > 0, 'off': booking.customerNote.length == 0}">
                    Remarque cliente
                    <span class="helpbox_content" ng-if="booking.customerNote.length > 0"><h4>Remarque cliente :</h4><p ng-bind-html="booking.customerNote|htmlSpecialDecode:true"></p></span>
                  </div>
                  <?php } ?>
                  <div ng-if="backendCtrl.settings.staffManagement" class="reservation_member_note" ng-class="{'on helpbox': booking.staffNote.length > 0, 'off': booking.staffNote.length == 0}">
                    Note {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                    <span class="helpbox_content" ng-if="booking.staffNote.length > 0"><h4>Note des {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} :</h4><p ng-bind-html="booking.staffNote|htmlSpecialDecode:true"></p></span>
                  </div>
                </div>
                <div class="reservation_suivi">
                  <div class="suivi_title">
                    <div class="title_date">Créée le </div>
                    <div class="title_time">à</div>
                    <div class="title_user"><?php _e('by_word', 'resa') ?></div>
                  </div>
                  <div class="suivi_content">
                    <div class="content_date">{{ booking.creationDate|formatDateTime:'<?php echo $variables['date_format']; ?>' }}</div>
                    <div class="content_time">{{ booking.creationDate|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</div>
                    <div class="content_user">{{ booking.userCreator }}</div>
                  </div>
                </div>
                <div class="reservation_actions no-print">
                  <?php if(!RESA_Variables::staffIsConnected()){ ?>
                  <span class="btn btn_modifier title_box dashicons dashicons-edit" ng-click="backendCtrl.openEditBooking(booking); displayBookingsCtrl.close()">
                   <span ng-if="!booking.quotation" class="title_box_content">Modifier la réservation</span>
                   <span ng-if="booking.quotation" class="title_box_content">Modifier le devis</span>
                  </span>
                  <span class="btn btn_modifier title_box dashicons dashicons-calendar-alt" ng-click="backendCtrl.loadBookingPeriod(booking); displayBookingsCtrl.close()">
                   <span class="title_box_content">Charger les réservations de la période de cette réservation</span>
                  </span>
                  <span class="btn btn_notify title_box dashicons dashicons-email-alt" ng-click="backendCtrl.openNotificationDialog(backendCtrl.getCustomerById(booking.idCustomer),booking); displayBookingsCtrl.close()">
                    <span class="title_box_content">Envoyer une notification</span>
                  </span>
                  <span class="btn btn_client title_box dashicons dashicons-admin-users" ng-click="backendCtrl.openCustomerDialog(booking.idCustomer); displayBookingsCtrl.close()">
                    <span class="title_box_content">Voir la fiche client</span>
                  </span>
                  <span class="btn btn_client title_box dashicons dashicons-cart" ng-if="backendCtrl.isCaisseOnlineActivated() && !booking.quotation" ng-click="backendCtrl.payBookingCaisseOnline(booking)">
                    <span class="title_box_content">Envoyer la réservation sur la caisse</span>
                  </span>
                  <span class="btn btn_client title_box dashicons dashicons-tag" ng-if="!backendCtrl.isCaisseOnlineActivated() && !booking.quotation"
                  ng-click="backendCtrl.openPaymentStateDialog(booking);">
                    <span class="title_box_content">Changer l'état de paiement</span>
                  </span>
                  <span class="btn btn_reciept title_box dashicons dashicons-format-aside" ng-click="backendCtrl.openReceiptBookingDialog(booking); displayBookingsCtrl.close()">
                    <span ng-if="!booking.quotation" class="title_box_content">Reçu de la réservation</span>
                    <span ng-if="booking.quotation" class="title_box_content">Reçu du devis</span>
                  </span>
                  <?php } ?>
        				</div>
                <div class="reservation_bookings">
                  <div class="booking" ng-class="{'confirmed':appointment.state == 'ok','not_confirmed':appointment.state == 'waiting','cancelled':appointment.state == 'cancelled' || appointment.state == 'abandonned'}" ng-repeat="appointment in booking.appointments|orderBy:'startDate'">
                    <div class="booking_date">{{ appointment.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }}</div>
                    <div class="booking_hour">
                      <span ng-if="!appointment.noEnd">
      									{{ appointment.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      									<?php _e('to_word', 'resa') ?>
      									{{ appointment.endDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      								</span>
      								<span ng-if="appointment.noEnd">
      									<?php _e('begin_word', 'resa'); ?> {{ appointment.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      								</span>
                    </div>
                    <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_numbers']){ ?>
                    <div class="booking_nb_people" ng-class="{'helpbox': backendCtrl.getServiceById(appointment.idService).askParticipants}">
                      {{ appointment.numbers }} pers.
                      <span ng-if="backendCtrl.getServiceById(appointment.idService).askParticipants" class="helpbox_content"><h4>Participants</h4>
                        <span ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                          <table>
                            <thead>
                              <tr>
                                <td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">
                                  {{ backendCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
                                </td>
                              </tr>
                            </thead>
                            <tbody>
                              <tr ng-repeat="participant in appointmentNumberPrice.participants track by $index"><td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">{{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</td></tr>
                            </tbody>
                            <tfoot  ng-if="backendCtrl.isDisplaySum(backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields)">
                              <tr>
                                <td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields"><span ng-if="field.displaySum">Somme : {{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getDisplaySum(field, appointmentNumberPrice.participants) }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</span></td>
                              </tr>
                            </tfoot>
                          </table>
                        </span>
                      </span>
                    </div>
                    <?php } ?>
                    <div class="booking_states">
                      <div class="states_state state_confirmed">Confirmée</div>
                      <div class="states_state state_not_confirmed">Non confirmée</div>
                      <div class="states_state state_cancelled">Annulée</div>
                      <div class="states_state state_inerror">Erreur</div>
                      <div class="states_state state_incomplete">Incomplète</div>
                    </div>
                    <div class="booking_place"><span ng-if="appointment.idPlace != ''">{{ backendCtrl.getTextByLocale(backendCtrl.getPlaceById(appointment.idPlace).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span></div>
                    <div class="booking_service">{{ backendCtrl.getTextByLocale(
      										backendCtrl.getServiceById(appointment.idService).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                    <div class="booking_prices">
                      <div class="prices_price" ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                        <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_numbers']){ ?>
                        <span class="price_nb">{{ appointmentNumberPrice.number }}</span>
                        <?php } ?>
                        <span class="price_name">
                          {{ backendCtrl.getServicePriceAppointmentName(appointment.idService, appointmentNumberPrice.idPrice) |htmlSpecialDecode
        									}}
                          <span ng-if="backendCtrl.haveEquipments(appointment.idService, appointmentNumberPrice.idPrice)">
                            ({{ backendCtrl.getServicePriceAppointmentEquipmentName(appointment.idService, appointmentNumberPrice.idPrice)|htmlSpecialDecode
          									}})
                          </span>
                        </span>
                        <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_payments']){ ?>
                          <span class="price_value" ng-if="!appointmentNumberPrice.deactivated">
                            <span ng-if="appointmentNumberPrice.totalPrice != 0">{{ appointmentNumberPrice.totalPrice }}  {{ backendCtrl.settings.currency }}</span>
                            <span ng-if="appointmentNumberPrice.totalPrice == 0">
                              <span ng-if="backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
                                {{ backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).price }} {{ backendCtrl.settings.currency }}
                              </span>
                              <span ng-if="!backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
                                {{ backendCtrl.getPriceNumberPrice(appointment.idService, appointmentNumberPrice) }} {{ backendCtrl.settings.currency }}
                              </span>
                            </span>
                          </span>
                        <?php } ?>
                      </div>
                    </div>
                    <div class="booking_members" ng-if="backendCtrl.settings.staffManagement">
                      <div class="member" ng-if="appointmentMember.number > 0" ng-repeat="appointmentMember in appointment.appointmentMembers">{{ backendCtrl.getMemberById(appointmentMember.idMember).nickname|htmlSpecialDecode }}<br /></div>
                    </div>
                    <div class="booking_tags">
                      <div class="tag {{ backendCtrl.getTagById(idTag).color }}" ng-repeat="idTag in appointment.tags">
                        {{ backendCtrl.getTextByLocale(backendCtrl.getTagById(idTag).title, '<?php echo get_locale(); ?>') }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="popup_footer">
              <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="displayBookingsCtrl.close()"><span class="title_box_content">Fermer</span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="loader_box" ng-show="false">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="resa_popup" ng-controller="NewEditInfoCalendarController as newEditInfoCalendarCtrl" ng-show="newEditInfoCalendarCtrl.opened">
      <div class="resa_popup_wrapper">
        <div class="resa_popup_content">
          <div class="notification_reservation">
            <div class="popup_header">
              <div class="resa_popup_header_wrapper">
                <div class="popup_header_title">
                  <h2 class="modal-title text-center" ng-if="newEditInfoCalendarCtrl.isNewInfoCalendar()"><?php _e('add_info_calendar_dialog_title','resa') ?></h2>
                  <h2 class="modal-title text-center" ng-if="!newEditInfoCalendarCtrl.isNewInfoCalendar()"><?php _e('edit_info_calendar_dialog_title','resa') ?></h2>
                </div>
                <div class="popup_header_menu">
                  <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="newEditInfoCalendarCtrl.close()"><span class="title_box_content">Fermer</span></div>
                </div>
              </div>
            </div>
            <div class="popup_content">
              <p><b><em> Permet d'ajouter une information sur l'affichage du calendrier et sur le planning moniteur.<br /> </em></b></p>
              <h4>Date</h4>
              <div class="add_paiement_account">
                <span ng-if="newEditInfoCalendarCtrl.manyDays">Du :</span><input type="date" ng-model="newEditInfoCalendarCtrl.date" />
                <label><input type="checkbox" ng-model="newEditInfoCalendarCtrl.manyDays" />
                  <span class="helpbox">Sur plusieurs jours <span class="helpbox_content"><p>Cocher cette case afin de pouvoir répéter cette information sur plusieurs jours.</p></span></span></label><br />
                <span ng-if="newEditInfoCalendarCtrl.manyDays">Jusqu'au : <input type="date" ng-model="newEditInfoCalendarCtrl.dateEnd" /><br /></span>
                <input type="time" ng-model="newEditInfoCalendarCtrl.time" ng-model-options="{ debounce: 500 }" ng-change="newEditInfoCalendarCtrl.changeStartTime()" /> à
                <input type="time" ng-model="newEditInfoCalendarCtrl.timeEnd" ng-model-options="{ debounce: 500 }" ng-change="newEditInfoCalendarCtrl.changeEndTime()" />
              </div>
              <h4>Description</h4>
              <textarea rows="10" class="large-text" ng-model="newEditInfoCalendarCtrl.infoCalendar.note" placeholder="<?php _e('description_field_title','resa') ?>"></textarea>
              <span ng-if="newEditInfoCalendarCtrl.placesList.length > 0">
                <h4>Lieu</h4>
                <select ng-options="place.value as place.title for place in newEditInfoCalendarCtrl.placesList" ng-model="newEditInfoCalendarCtrl.idPlaces"></select>
              </span>
            </div>
            <div class="popup_footer">
              <div class="btn" ng-class="{'btn_vert':newEditInfoCalendarCtrl.isOkForm(),'btn_locked':!newEditInfoCalendarCtrl.isOkForm()}" ng-click="newEditInfoCalendarCtrl.validForm()">Valider</div>
              <div class="btn btn_rouge" ng-if="!newEditInfoCalendarCtrl.isNewInfoCalendar()" ng-click="newEditInfoCalendarCtrl.deleteInfoCalendar({
                title: '<?php _e('delete_info_calendar_title_dialog','resa') ?>',
                text: '<?php _e('delete_info_calendar_text_dialog','resa') ?>',
                confirmButton: '<?php _e('delete_info_calendar_confirmButton_dialog','resa') ?>',
                cancelButton: '<?php _e('delete_info_calendar_cancelButton_dialog','resa') ?>'
              })">Supprimer</div>
            </div>
          </div>
        </div>
      </div>
      <div class="loader_box" ng-show="newEditInfoCalendarCtrl.editInfoCalendarActionLaunched">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="resa_popup" ng-controller="NewEditServiceConstraintController as editServiceConstraintCtrl" ng-show="editServiceConstraintCtrl.opened">
      <div class="resa_popup_wrapper">
        <div class="resa_popup_content">
          <div class="notification_reservation">
            <div class="popup_header">
              <div class="resa_popup_header_wrapper">
                <div class="popup_header_title">
                  <h2 class="modal-title text-center" ng-if="editServiceConstraintCtrl.isNewServiceConstraint() && editServiceConstraintCtrl.isServiceSubPage()"><?php _e('add_service_constraint_dialog_title','resa') ?></h2>
                  <h2 class="modal-title text-center" ng-if="!editServiceConstraintCtrl.isNewServiceConstraint() && editServiceConstraintCtrl.isServiceSubPage()"><?php _e('edit_service_constraint_dialog_title','resa') ?></h2>
                  <h2 class="modal-title text-center" ng-if="editServiceConstraintCtrl.isNewServiceConstraint() && editServiceConstraintCtrl.isMemberSubPage()">Ajout d'une contrainte sur un {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_single, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h2>
                  <h2 class="modal-title text-center" ng-if="!editServiceConstraintCtrl.isNewServiceConstraint() && editServiceConstraintCtrl.isMemberSubPage()">Edition d'une contrainte sur un {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_single, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h2>
                </div>
                <div class="popup_header_menu">
                  <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="editServiceConstraintCtrl.close()"><span class="title_box_content">Fermer</span></div>
                </div>
              </div>
            </div>
            <div class="popup_content">
              <div ng-if="editServiceConstraintCtrl.isDisplayMenu()">
                <h2 class="nav-tab-wrapper">
                  <a class="nav-tab resa_list_tab" ng-class="{'nav-tab-active':editServiceConstraintCtrl.isServiceSubPage()}" ng-click="editServiceConstraintCtrl.setServiceSubPage()" style="">Contrainte sur un service</a>
                  <a class="nav-tab resa_list_tab" ng-class="{'nav-tab-active':editServiceConstraintCtrl.isMemberSubPage()}" ng-click="editServiceConstraintCtrl.setMemberSubPage()" style="">Contrainte sur un {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_single, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</a>
                </h2>
              </div>
              <div ng-if="editServiceConstraintCtrl.isServiceSubPage()">
                <p><b><em> Permet de bloquer la réservation d'un service sur la date et l'heure indiquée seulement sur le formulaire client.<br /> </em></b></p>
                <h4>Services</h4>
                <select id="formInput5-1" ng-options="service.id+'' as editServiceConstraintCtrl.getServiceName(service, backendCtrl.locale) for service in editServiceConstraintCtrl.services"  ng-model="editServiceConstraintCtrl.serviceConstraint.idService"></select>
              </div>
              <div ng-if="editServiceConstraintCtrl.isMemberSubPage()">
                <p><b><em> Permet de bloquer le membre sur la date et l'heure indiquée.<br /> </em></b></p>
                <h4>{{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4>
                <select id="formInput5-5" ng-options="member.id+'' as editServiceConstraintCtrl.getMemberName(member, backendCtrl.locale) for member in editServiceConstraintCtrl.members"  ng-model="editServiceConstraintCtrl.memberConstraint.idMember"></select>
              </div>
              <h4>Date</h4>
              <div class="add_paiement_account">
                <span ng-if="newEditInfoCalendarCtrl.manyDays">Du :</span><input type="date" ng-model="editServiceConstraintCtrl.dateStart" />
                <label><input type="checkbox" ng-model="editServiceConstraintCtrl.manyDays" />
                  <span class="helpbox">Sur plusieurs jours <span class="helpbox_content"><p>Cocher cette case afin de pouvoir répéter cette contrainte sur plusieurs jours.</p></span></span></label><br />
                <span ng-if="editServiceConstraintCtrl.manyDays">Jusqu'au : <input type="date" ng-model="editServiceConstraintCtrl.dateEnd" /><br /></span>
              </div>
              <h4>Heures</h4>
              <div class="add_paiement_account">
                Heure début : <input type="time" ng-model="editServiceConstraintCtrl.timeStart" ng-model-options="{ debounce: 500 }" ng-change="editServiceConstraintCtrl.changeStartTime()" />
              </div>
              <div class="add_paiement_account">
                Heure fin : <input type="time" ng-model="editServiceConstraintCtrl.timeEnd" ng-model-options="{ debounce: 500 }" ng-change="editServiceConstraintCtrl.changeEndTime()" />
              </div>
              <p style="color:orange" ng-if="editServiceConstraintCtrl.isOkForm() && editServiceConstraintCtrl.isServiceSubPage()">
                Bloquer la réservation sur le service : <span style="font-weight:bold">{{ editServiceConstraintCtrl.getServiceNameById(editServiceConstraintCtrl.serviceConstraint.idService, '<?php echo get_locale(); ?>') }}</span>
                <span ng-if="!editServiceConstraintCtrl.manyDays"> le <span style="font-weight:bold">{{ editServiceConstraintCtrl.generateStartDate()|formatDate:'<?php echo $variables['date_format']; ?>' }}</span> de <span style="font-weight:bold">{{ editServiceConstraintCtrl.generateStartDate()|formatDate:'<?php echo $variables['time_format']; ?>' }} à {{ editServiceConstraintCtrl.generateEndDate()|formatDate:'<?php echo $variables['time_format']; ?>' }}</span></span>
                <span ng-if="editServiceConstraintCtrl.manyDays"> du <span style="font-weight:bold">{{ editServiceConstraintCtrl.generateStartDate()|formatDate:'<?php echo $variables['date_format']; ?> <?php echo $variables['time_format']; ?>' }} au {{ editServiceConstraintCtrl.generateEndDate()|formatDate:'<?php echo $variables['date_format']; ?> <?php echo $variables['time_format']; ?>' }}</span></span> ?
              </p>
              <p style="color:orange; font-weight:bold" ng-if="editServiceConstraintCtrl.isOkForm() && editServiceConstraintCtrl.isMemberSubPage()">
                Rendre indisponible le {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_single, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} : <span style="font-weight:bold">{{ editServiceConstraintCtrl.getMemberNameById(editServiceConstraintCtrl.memberConstraint.idMember, '<?php echo get_locale(); ?>') }}</span>
                <span ng-if="!editServiceConstraintCtrl.manyDays"> le <span style="font-weight:bold">{{ editServiceConstraintCtrl.generateStartDate()|formatDate:'<?php echo $variables['date_format']; ?>' }}</span> de <span style="font-weight:bold">{{ editServiceConstraintCtrl.generateStartDate()|formatDate:'<?php echo $variables['time_format']; ?>' }} à {{ editServiceConstraintCtrl.generateEndDate()|formatDate:'<?php echo $variables['time_format']; ?>' }}</span></span>
                <span ng-if="editServiceConstraintCtrl.manyDays"> du <span style="font-weight:bold">{{ editServiceConstraintCtrl.generateStartDate()|formatDate:'<?php echo $variables['date_format']; ?> <?php echo $variables['time_format']; ?>' }} au {{ editServiceConstraintCtrl.generateEndDate()|formatDate:'<?php echo $variables['date_format']; ?> <?php echo $variables['time_format']; ?>' }}</span></span> ?
              </p>
            </div>
            <div class="popup_footer">
              <div class="btn" ng-class="{'btn_vert':editServiceConstraintCtrl.isOkForm(),'btn_locked':!editServiceConstraintCtrl.isOkForm()}" ng-click="editServiceConstraintCtrl.validForm()">Valider</div>
              <div class="btn btn_rouge" ng-if="!editServiceConstraintCtrl.isNewServiceConstraint()" ng-click="editServiceConstraintCtrl.deleteServiceConstraint({
      					title: '<?php _e('delete_service_constraint_title_dialog','resa') ?>',
      					text: '<?php _e('delete_service_constraint_text_dialog','resa') ?>',
      					confirmButton: '<?php _e('delete_service_constraint_confirmButton_dialog','resa') ?>',
      					cancelButton: '<?php _e('delete_service_constraint_cancelButton_dialog','resa') ?>'
      				});">Supprimer</div>
            </div>
          </div>
        </div>
      </div>
      <div class="loader_box" ng-show="editServiceConstraintCtrl.editServiceConstraintLaunched">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="resa_popup" id="resa_edit_group" ng-controller="GroupsController as groupsCtrl" ng-show="groupsCtrl.opened">
      <div class="resa_popup_wrapper">
        <div class="resa_popup_content">
          <div class="edit_group">
            <div class="popup_header">
              <div class="resa_popup_header_wrapper">
                <div class="popup_header_title">
                  <h2>Gérer les groupes</h2>
                  <h3>
                    <span ng-if="groupsCtrl.rapportByServices.idPlace!=''">[{{ groupsCtrl.getTextByLocale(backendCtrl.getPlaceById(groupsCtrl.rapportByServices.idPlace).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}]</span>  {{ groupsCtrl.getTextByLocale(groupsCtrl.rapportByServices.service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} -
                    {{ groupsCtrl.rapportByServices.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }} -
                    <span ng-if="!groupsCtrl.rapportByServices.noEnd">
                      {{ groupsCtrl.rapportByServices.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                      <?php _e('to_word', 'resa') ?>
                      {{ groupsCtrl.rapportByServices.endDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                    </span>
                    <span ng-if="groupsCtrl.rapportByServices.noEnd">
                      <?php _e('begin_word', 'resa'); ?> {{ groupsCtrl.rapportByServices.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                    </span></h3>
                </div>
                <div class="popup_header_menu">
                  <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="groupsCtrl.close()"><span class="title_box_content">Fermer</span></div>
                </div>
              </div>
            </div>
            <div class="popup_content">
              <div class="edit_group_sync" ng-if="groupsCtrl.week.length > 0">
                <h3>Synchronisation des modifications</h3>
                <p>Cocher les jours sur lequels les modifications seront appliquées.<br />Cela permet de modifier un groupe pour une réservation sur la semaine.<br /><b class="rouge">Attention les modifications écraseront les groupes actuels.</b></p>
                <div class="groups_sync">
                  <div class="group_sync" ng-class="{'jour_actuel':day.actual}" ng-click="groupsCtrl.switchCheckedDay(day)" ng-repeat="day in groupsCtrl.week">
                    <input type="checkbox" ng-checked="day.checked" />
                    <label>{{ day.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }}</label>
                  </div>
                </div>
              </div>
              <div class="edit_group_content">
                <div class="edit_group_group_list">
                  <h3>Liste des groupes</h3>
        					<div ng-if="groupsCtrl.getNumberParticipantsNotInGroups() > 0" class="report_nb_notgrouped important">
        						<b>Non attribuées : </b>{{ groupsCtrl.getNumberParticipantsNotInGroups() }} personne(s)
        					</div>
                  <div class="group" style="border: 1.5px solid {{ group.color }}" ng-click="groupsCtrl.close(); backendCtrl.openGroupManagerDialog(groupsCtrl.rapportByServices, group)" ng-repeat="group in groupsCtrl.rapportByServices.groups">
                    <h3 class="group_title">{{ backendCtrl.getGroupName(group) }}<a ng-click="backendCtrl.openGroupManagerDialog(groupsCtrl.rapportByServices, group, true)" class="group_edit btn btn_bleu">Editer</a></h3>
                    <p class="group_description">{{ group.presentation|htmlSpecialDecode }}</p>
                    <div class="group_participant"><span ng-class="{'bg_rouge': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) > group.max, 'bg_gris': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) == 0, 'bg_vert': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) <= group.max}">{{ backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) }} / {{ group.max }} personnes</span></div>
                    <div class="members">
                      <div class="member_title">{{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} :</div>
                      <div class="member" ng-repeat="idMember in group.idMembers track by $index">{{ groupsCtrl.getMemberById(idMember).nickname|htmlSpecialDecode }}</div>
                      <div ng-if="group.idMembers.length == 0" class="bg_rouge">Aucun</div>
                    </div>
                  </div>
                  <div class="group">
                    <h3 class="group_title">Ajouter un groupe</h3>
                    <input class="group_title" type="text" ng-model="groupsCtrl.newGroup.name" placeholder="Nom du groupe" />
					          <input class="group_title" type="number" ng-model="groupsCtrl.newGroup.max"  placeholder="Capacité" />
                    <input class="group_title" type="color" ng-model="groupsCtrl.newGroup.color" placeholder="" />
                    <textarea class="group_description" ng-model="groupsCtrl.newGroup.presentation" placeholder="Description du groupe"></textarea>
                    <input ng-disabled="!groupsCtrl.isOkGroup()" class="btn_wide" ng-class="{'btn_vert':groupsCtrl.isOkGroup(), 'btn_locked': !groupsCtrl.isOkGroup()}" type="button" ng-click="groupsCtrl.addGroup()" value="Ajouter" />
                  </div>
                </div>
                <div class="edit_group_group_edition">
                  <div class="group_edition_members">
                    <h3>Attribuer des {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h3>
                    <div class="group_members_table table">
                      <div class="table_titles">
                        <div class="table_title">
                          <input type="checkbox" ng-model="groupsCtrl.assignmentMembers.all" ng-change="groupsCtrl.putAllMembers()" />
                        </div>
                        <div class="table_title">
                          <a ng-click="groupsCtrl.changeOrderByMember('nickname'); groupsCtrl.orderMembersBy();">{{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</a>
                        </div>
                        <div class="table_title">
                          <a ng-click="groupsCtrl.changeOrderByMember('availability'); groupsCtrl.orderMembersBy();">Disponible</a>
                        </div>
                        <div class="table_title">
                          <a ng-click="groupsCtrl.changeOrderByMember('group'); groupsCtrl.orderMembersBy();">Groupe</a>
                        </div>
                      </div>
                      <div class="table_contents">
                        <div class="table_line" ng-if="backendCtrl.trueIfPlacesNotFiltered(member, backendCtrl.filters)" ng-class="{'bg_rouge':member.groupOut}" ng-click="groupsCtrl.toggleAssignmentMember(member)" ng-repeat="member in groupsCtrl.displayedMembers">
                          <div>
                            <input type="checkbox" ng-checked="groupsCtrl.assignmentMembers.members[member.id]" />
                          </div>
                          <div>{{ member.nickname|htmlSpecialDecode }}</div>
                          <div>{{ member.availability }} ({{ backendCtrl.getListPlaces(member.places) }})</div>
                          <div><span ng-class="{'helpbox':member.groupOut}">{{ member.groupName }}<span ng-if="member.groupOut" class="helpbox_content">
                            Ce moniteur est associé à un ou plusieurs groupes qui ne sont pas sur le même créneau ou activité.<br />
                            <span ng-if="group.idService != groupsCtrl.rapportByServices.service.id" ng-repeat="group in member.groups">
                              Groupe : {{ backendCtrl.getGroupName(group) }} - {{ group.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }} le {{ group.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }} à {{ group.endDate|formatDate:'<?php echo $variables['time_format']; ?>' }}<br />
                            </span>
                          </span></span></div>
                        </div>
                      </div>
                    </div>
                    <div class="add_selection_to">
                      <div>Attribuer la sélection à
                        <select name="group" ng-model="groupsCtrl.assignmentMembers.idGroup" ng-options="group.id as backendCtrl.getGroupName(group) for group in groupsCtrl.groupsList">
                        </select>
                        <input class="btn_vert" type="button" value="Attribuer" ng-click="groupsCtrl.assignmentMembersAskAction({
                          title: '<?php _e('change_members_group_title_dialog','resa') ?>',
                          text: '<?php _e('change_members_group_text_dialog','resa') ?>',
                          confirmButton: '<?php _e('change_members_group_confirmButton_dialog','resa') ?>',
                          cancelButton: '<?php _e('change_members_group_cancelButton_dialog','resa') ?>'
                        },{
                          title: '<?php _e('change_members_group_place_title_dialog','resa') ?>',
                          text: '<?php _e('change_members_group_place_text_dialog','resa') ?>',
                          confirmButton: '<?php _e('change_members_group_place_confirmButton_dialog','resa') ?>',
                          cancelButton: '<?php _e('change_members_group_place_cancelButton_dialog','resa') ?>'
                        })" />
                      </div>
                    </div>
                  </div>
                  <div class="group_edition_participants">
                    <h3>Attribuer des Participants <a ng-click="groupsCtrl.reinitFilters()">(Afficher tous)</a></h3>
                    <div class="edition_participants_type" ng-repeat="participantParameters in groupsCtrl.participantsParameters">
                      <div class="group_participant_table table grid{{ participantParameters.fields.length + 1 }}">
                        <div class="table_titles">
                          <div class="table_title">
                            <input type="checkbox" ng-model="groupsCtrl.assignmentParticipants.all[participantParameters.id]" ng-change="groupsCtrl.putAllParticipants(participantParameters.id)" />
                          </div>
                          <div class="table_title" ng-repeat="field in participantParameters.fields">
                            <a class="advanced_filter"><span ng-click="groupsCtrl.changeOrderBy(participantParameters.id, field.varname); groupsCtrl.orderParticipantsBy(participantParameters.id);">{{ groupsCtrl.getTextByLocale(field.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span>
                              <span class="advanced_filter_content"><ul>
                                <li ng-if="value.value.length > 0" ng-click="value.checked = !value.checked; groupsCtrl.filteredParticipants(participantParameters.id)" ng-repeat="value in groupsCtrl.filters[participantParameters.id][field.varname]" ng-class="{'checked':value.checked, 'unchecked':!value.checked}">
                                  <label>{{ backendCtrl.getTextByLocale(field.prefix, '<?php echo get_locale(); ?>')}}{{ value.value }}{{backendCtrl.getTextByLocale(field.suffix, '<?php echo get_locale(); ?>')}}</label>
                                </li>
                                <li ng-click="groupsCtrl.filtersAll[participantParameters.id][field.varname]=!groupsCtrl.filtersAll[participantParameters.id][field.varname]; groupsCtrl.changeAllFilters(participantParameters.id, field.varname)"  ng-class="{'checked':groupsCtrl.filtersAll[participantParameters.id][field.varname], 'unchecked':!groupsCtrl.filtersAll[participantParameters.id][field.varname]}">
                                  <label>Tous / Aucun</label>
                                </li>
                              </ul></span>
                            </a>
                          </div>
                        </div>
                        <div class="table_contents">
                          <div class="table_line" ng-click="groupsCtrl.assignmentParticipants.participants[participant.uri] = !groupsCtrl.assignmentParticipants.participants[participant.uri]" ng-repeat="participant in groupsCtrl.displayedParticipants[participantParameters.id] track by $index">
                            <div>
                              <input type="checkbox" ng-checked="groupsCtrl.assignmentParticipants.participants[participant.uri]" />
                            </div>
                            <div ng-repeat="field in participantParameters.fields">
                              <span ng-if="field.varname != 'state'">
                                {{ backendCtrl.getTextByLocale(field.prefix, '<?php echo get_locale(); ?>')}}
                                <span ng-class="{'important':backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') == 'Aucun'}">{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{backendCtrl.getTextByLocale(field.suffix, '<?php echo get_locale(); ?>')}}</span>
                              </span>
                              <span ng-if="field.varname == 'state'">
                                <a ng-click="groupsCtrl.openDisplayBookingOfParameterUri(participant.uri)">{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}</a>
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="add_selection_to">
                        <div>Attribuer la sélection à
                          <select name="group" ng-model="groupsCtrl.assignmentParticipants.idGroup" ng-options="group.id as backendCtrl.getGroupName(group) for group in groupsCtrl.groupsList">
                          </select>
                          <input class="btn_vert" type="button" value="Attribuer" ng-click="groupsCtrl.assignmentParticipantsAskAction({
                            title: '<?php _e('change_participants_group_title_dialog','resa') ?>',
                            text: '<?php _e('change_participants_group_text_dialog','resa') ?>',
                            confirmButton: '<?php _e('change_participants_group_confirmButton_dialog','resa') ?>',
                            cancelButton: '<?php _e('change_participants_group_cancelButton_dialog','resa') ?>'
                          })" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="loader_box" ng-show="groupsCtrl.launchGetGroups || groupsCtrl.launchEditGroup">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="resa_popup" id="resa_detail_group" ng-controller="GroupController as groupCtrl" ng-show="groupCtrl.opened">
      <div class="resa_popup_wrapper">
        <div class="resa_popup_content">
          <div class="edit_group">
            <div class="popup_header">
              <div class="resa_popup_header_wrapper">
                <div class="popup_header_title">
                  <h2>Détails du groupe</h2>
                  <h3><span ng-if="groupsCtrl.rapportByServices.idPlace!=''">[{{ groupCtrl.getTextByLocale(backendCtrl.getPlaceById(groupCtrl.rapportByServices.idPlace).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}]</span> {{ backendCtrl.getGroupName(groupCtrl.group) }} - {{ groupCtrl.getTextByLocale(groupCtrl.rapportByServices.service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} -
                    {{ groupCtrl.rapportByServices.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }} -
                    <span ng-if="!groupCtrl.rapportByServices.noEnd">
                      {{ groupCtrl.rapportByServices.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                      <?php _e('to_word', 'resa') ?>
                      {{ groupCtrl.rapportByServices.endDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                    </span>
                    <span ng-if="groupCtrl.rapportByServices.noEnd">
                      <?php _e('begin_word', 'resa'); ?> {{ groupCtrl.rapportByServices.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                    </span></h3>
                </div>
                <div class="popup_header_menu">
                  <?php if(!RESA_Variables::staffIsConnected()){ ?>
                  <div class="btn btn_vert" ng-click="groupCtrl.validerForm()">Enregistrer et fermer</div>
                  <div class="btn" ng-click="groupCtrl.printDiv('printGroup')">Imprimer</div>
                  <div class="btn btn_bleu" ng-click="groupCtrl.close(); backendCtrl.openGroupsManagerDialog(groupCtrl.rapportByServices)">Gérer les groupes</div>
                  <div class="btn btn_jaune" ng-click="groupCtrl.close()">Fermer sans enregistrer</div>
                  <div class="btn btn_rouge" ng-click="groupCtrl.deleteGroupAction({
                    title: '<?php _e('delete_group_title_dialog','resa') ?>',
                    text: '<?php _e('delete_group_text_dialog','resa') ?>',
                    confirmButton: '<?php _e('delete_group_confirmButton_dialog','resa') ?>',
                    cancelButton: '<?php _e('delete_group_cancelButton_dialog','resa') ?>'
                  })">Supprimer</div>
                  <?php } ?>

                  <?php if(RESA_Variables::staffIsConnected()){ ?>
                  <div class="btn btn_jaune title_box dashicons dashicons-no" ng-click="groupCtrl.close()"><span class="title_box_content">Fermer</span></div>
                  <?php } ?>
                </div>
              </div>
            </div>
            <div class="popup_content">
				      <br /><br />
              <?php if(!RESA_Variables::staffIsConnected()){ ?>
              <div class="edit_group_sync" ng-if="groupCtrl.week.length > 0">
                <h3>Synchronisation des modifications</h3>
                <p>Cocher les jours sur lequels les modifications seront appliquées.<br />Cela permet de modifier un groupe pour une réservation sur la semaine.<br /><b class="rouge">Attention les modifications écraseront les groupes actuels.</b></p>
                <div class="groups_sync">
                  <div class="group_sync" ng-class="{'jour_actuel':day.actual}" ng-click="groupCtrl.switchCheckedDay(day)" ng-repeat="day in groupCtrl.week">
                    <input type="checkbox" ng-checked="day.checked" />
                    <label>{{ day.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }}</label>
                  </div>
                </div>
              </div>
              <?php } ?>
      				<h3 class="group_title">{{ backendCtrl.getGroupName(groupCtrl.group) }} <?php if(!RESA_Variables::staffIsConnected()){ ?><a ng-click="groupCtrl.editGroupView = true" class="group_edit btn btn_bleu">Editer</a><?php } ?></h3>
      				<p class="group_description">{{ groupCtrl.group.presentation|htmlSpecialDecode }}</p>
      				<div class="edit_group_infos" ng-if="groupCtrl.editGroupView">
      					<h3 class="group_title">Editer le groupe</h3>
      					Capacité : <input class="group_title" type="number" ng-model="groupCtrl.group.max" placeholder="Capacité" />
      					Couleur : <input class="group_title" type="color" ng-model="groupCtrl.group.color" placeholder="" />
      					<textarea class="group_description" ng-model="groupCtrl.group.presentation"></textarea>
      					<input class="btn_vert" type="button" ng-click="groupCtrl.editGroupView = false; groupCtrl.editGroups([groupCtrl.group])" value="Modifer" /><input class="btn_rouge" type="button" ng-click="groupCtrl.editGroupView = false" value="Annuler" />
      				</div>
              <div ng-if="groupCtrl.group!=null" class="group_participant"><span ng-class="{'bg_rouge': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(groupCtrl.group), groupCtrl.group) > groupCtrl.group.max, 'bg_gris': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(groupCtrl.group), groupCtrl.group) == 0, 'bg_vert': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(groupCtrl.group), groupCtrl.group) <= groupCtrl.group.max}">{{ backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(groupCtrl.group), groupCtrl.group) }} / {{ groupCtrl.group.max }} personnes</span></div>
              <div ng-if="groupCtrl.group.idMembers.length == 0" class="bg_rouge">
                Aucun {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_single, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
              </div>
              <div class="group_content" id="printGroup">
                <div class="group_members">
                  <h3>{{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} dans ce groupe</h3>
                  <div class="member" ng-repeat="member in groupCtrl.displayedMembers track by $index">
                    <?php if(!RESA_Variables::staffIsConnected()){ ?>
                      <a class="btn btn_rouge" ng-click="groupCtrl.removeMemberToThisGroup({
                      title: '<?php _e('remove_member_group_title_dialog','resa') ?>',
                      text: '<?php _e('remove_member_group_text_dialog','resa') ?>',
                      confirmButton: '<?php _e('remove_member_group_confirmButton_dialog','resa') ?>',
                      cancelButton: '<?php _e('remove_member_group_cancelButton_dialog','resa') ?>'
                    }, $index)">X</a>
                    <?php } ?> [{{ backendCtrl.getListPlaces(member.places) }}]{{ member.nickname|htmlSpecialDecode }}
                    <span ng-class="{'helpbox bg_rouge':member.groupOut}"><span ng-if="member.groupOut" class="helpbox_content">
                      Ce moniteur est associé à un ou plusieurs groupes qui ne sont pas sur le même créneau ou activité, cependant l'enregistrement ne le supprimera pas de ces groupes, veuillez le retirer des dates indiquées ou utiliser la gestion des groupes pour l'ajouter à ce groupe.<br />
                      <span ng-if="group.idService != groupCtrl.group.idService" ng-repeat="group in member.groups">
                        Groupe : {{ backendCtrl.getGroupName(group) }} - {{ group.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }} le {{ group.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }} à {{ group.endDate|formatDate:'<?php echo $variables['time_format']; ?>' }}<br />
                      </span>
                    </span></span>
                  </div>
                  <?php if(!RESA_Variables::staffIsConnected()){ ?>
                  <br /><br />
                  <div>Ajouter {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_single, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} :
                    <select ng-model="groupCtrl.addMemberModel">
                      <option ng-if="backendCtrl.trueIfPlacesNotFiltered(member, backendCtrl.filters) && groupCtrl.currentIdMembers.indexOf(member.id) == -1 && groupCtrl.groupIsInRapport(groupCtrl.getGroupByIdMember(member.id))" ng-repeat="member in groupCtrl.members" value="{{ member.id }}">
                        {{ groupCtrl.getDisplayOption(member) }}
                      </option>
                    </select><a class="btn btn_vert" ng-click="groupCtrl.addMemberToThisGroup({
                      title: '<?php _e('change_member_group_title_dialog','resa') ?>',
                      text: '<?php _e('change_member_group_text_dialog','resa') ?>',
                      confirmButton: '<?php _e('change_member_confirmButton_dialog','resa') ?>',
                      cancelButton: '<?php _e('change_member_cancelButton_dialog','resa') ?>'
                    },{
                      title: '<?php _e('change_member_place_group_title_dialog','resa') ?>',
                      text: '<?php _e('change_member_place_group_text_dialog','resa') ?>',
                      confirmButton: '<?php _e('change_member_place_confirmButton_dialog','resa') ?>',
                      cancelButton: '<?php _e('change_member_place_cancelButton_dialog','resa') ?>'
                    })">Ajouter</a></div>
                  <?php } ?>
                </div>
                <div class="group_edition_participants">
                  <h3>Attribuer des Participants ({{ groupCtrl.group.idParticipants.length }})</h3>
                  <div class="edition_participants_type" ng-repeat="participantParameters in groupCtrl.participantsParameters">
                    <div class="group_participant_table table grid{{ participantParameters.fields.length+1 }}">
                      <div class="table_titles">
                        <div class="table_title" ng-repeat="field in participantParameters.fields">
                          <a>{{ groupCtrl.getTextByLocale(field.name, '<?php echo get_locale(); ?>') }}</a>
                        </div>
                        <?php if(!RESA_Variables::staffIsConnected()){ ?> <div class="table_title">Groupe</div> <?php } ?>
                      </div>
                      <div class="table_contents">
                        <div class="table_line" ng-repeat="participant in groupCtrl.participants[participantParameters.id] track by $index">
                          <div ng-if="field.varname != 'state'" ng-repeat="field in participantParameters.fields">{{ backendCtrl.getTextByLocale(field.prefix, '<?php echo get_locale(); ?>')}}{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{backendCtrl.getTextByLocale(field.suffix, '<?php echo get_locale(); ?>')}}</div>
                          <div ng-if="field.varname == 'state'" ng-repeat="field in participantParameters.fields">
                            <?php if(!RESA_Variables::staffIsConnected()){ ?>
                              <a ng-click="groupCtrl.openDisplayBookingOfParameterUri(participant.uri)">{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}</a>
                            <?php } ?>
                            <span style="color:orange; font-weight: bold;" ng-if="groupCtrl.isAbsent(participant.uri)"><br />Absent</span>
                            <span ng-if="groupCtrl.getCustomerOfParameterUri(participant.uri).phone.length > 0"><?php if(!RESA_Variables::staffIsConnected()){ ?><br /><?php } ?>{{ groupCtrl.getCustomerOfParameterUri(participant.uri).phone | formatPhone }}</span>
                            <span ng-if="groupCtrl.getCustomerOfParameterUri(participant.uri).phone2.length > 0"><br />{{ groupCtrl.getCustomerOfParameterUri(participant.uri).phone2 | formatPhone }}</span>
                          </div>
                          <?php if(!RESA_Variables::staffIsConnected()){ ?>
                          <div><select ng-model="groupCtrl.assignmentParticipants[participant.uri]" ng-options="group.id as backendCtrl.getGroupName(group) for group in groupCtrl.groupsList"></select></div>
                          <?php } ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="loader_box" ng-show="groupCtrl.validFormLaunched || groupCtrl.launchGetGroups">
                <div class="loader_content">
                  <div class="loader">
                    <div class="shadow"></div>
                    <div class="box"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="resa_sous_popup add_paiement" ng-controller="NewAttachBookingController as attachBookingCtrl" ng-show="attachBookingCtrl.opened">
      <div class="resa_sous_popup_wrapper">
        <div class="resa_sous_popup_close">
          <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="attachBookingCtrl.close()"><span class="title_box_content">Fermer</span></div>
        </div>
        <div class="resa_sous_popup_content">
          <h3><?php _e('attach_booking_for_customer_sentence','resa') ?></h3>
          <label class="control-label" for="selectedBooking"><?php _e('associated_booking_words', 'resa') ?></label>
          <select id="selectedBooking" class="form-control" ng-model="attachBookingCtrl.payment.idBooking" ng-options="booking.id as ('<?php _e('Booking_of_words', 'resa'); ?> '+booking.creationDate+' -- '+attachBookingCtrl.displayNeedToPay(booking.needToPay)+' '+attachBookingCtrl.settings.currency) for booking in attachBookingCtrl.bookings"></select>
          <br />
          {{ attachBookingCtrl.payment.paymentDate | formatDateTime:'<?php echo $variables['date_format'].' '.$variables['time_format']; ?>' }}
          <br />
          <?php _e('amount_field_title','resa') ?> {{ attachBookingCtrl.payment.value }}{{ attachBookingCtrl.settings.currency }}
          <br />
          <?php _e('payment_type_title','resa') ?> {{ attachBookingCtrl.payment.type }}
          <br />
          <input class="btn" ng-class="{'btn_vert':attachBookingCtrl.isOkForm(), 'btn_locked':!attachBookingCtrl.isOkForm()}" type="button" ng-click="attachBookingCtrl.attachBooking()" value="Attacher le paiement" />
        </div>
      </div>
    </div>
    <div class="resa_sous_popup" ng-controller="NewPayBookingController as payBookingCtrl" ng-show="payBookingCtrl.opened">
      <div class="resa_sous_popup_wrapper">
        <div class="resa_sous_popup_close">
          <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="payBookingCtrl.close()"><span class="title_box_content">Fermer</span></div>
        </div>
        <div class="resa_sous_popup_content">
          <h3><?php _e('pay_booking_dialog_title','resa') ?></h3>
          <div ng-if="payment.id.indexOf('type_payment') == -1 && payment.activated && payment.id!='onTheSpot' && payment.id!='later'" ng-repeat="payment in payBookingCtrl.paymentsTypeList">
            <label class="control-label resa_paiement_method">
              <input type="radio" name="group" ng-model="payBookingCtrl.typePayment" ng-value="payment">
              {{ backendCtrl.getPaymentName(payment.title, payment.name) }}
            </label>
            <p ng-if="payBookingCtrl.typePayment.id==payment.id && payBookingCtrl.getTextByLocale(payment.text, '<?php echo get_locale(); ?>').length > 0" ng-bind-html="payBookingCtrl.getTextByLocale(payment.text, '<?php echo get_locale(); ?>') | displayNewLines"></p>
            <!-- step7_content need for stripe //-->
            <p ng-if="payment.id == 'stripe' || payment.id == 'stripeConnect'" id="step7_content"></p>
          </div>
          <span ng-if="payBookingCtrl.settings.payment_ask_advance_payment && payBookingCtrl.typePayment.advancePayment && !payBookingCtrl.alreadyAdvancedPayment()">
            <?php _e('advance_payment_title_form', 'resa') ?>
            <p>
              <label>
                <input class="control-label" type="checkbox" ng-model="payBookingCtrl.checkboxAdvancePayment" />
                <span ng-bind-html="payBookingCtrl.getTextByLocale(payBookingCtrl.settings.payment_ask_text_advance_payment, '<?php echo get_locale(); ?>')|displayNewLines"></span>
              </label>
              <br />
              <?php _e('advance_payment_amount_words', 'resa') ?> {{ payBookingCtrl.booking.advancePayment }}{{ payBookingCtrl.settings.currency }}
            </p>
          </span>
          <input class="btn" ng-class="{'btn_vert':payBookingCtrl.isOkForm(), 'btn_locked':!payBookingCtrl.isOkForm()}" type="button" ng-click="payBookingCtrl.payBooking('<?php echo $variables['currentUrl']; ?>')" value="Paiement réservation" />
        </div>
      </div>
      <div class="loader_box" ng-show="backendCtrl.payBookingActionLaunched">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="resa_sous_popup" ng-controller="PaymentStateController as paymentStateCtrl" ng-show="paymentStateCtrl.opened">
      <div class="resa_sous_popup_wrapper">
        <div class="resa_sous_popup_close">
          <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="paymentStateCtrl.close()"><span class="title_box_content">Fermer</span></div>
        </div>
        <div class="resa_sous_popup_content changer_etat_paiement">
          <h3>Changement de l'état de paiement</h3>
          Etat de paiement actuel :
          <b>
          <span ng-if="paymentStateCtrl.booking.paymentState == 'noPayment'">Pas de paiement</span>
          <span ng-if="paymentStateCtrl.booking.paymentState == 'advancePayment'">Acompte</span>
          <span ng-if="paymentStateCtrl.booking.paymentState == 'deposit'">Caution</span>
          <span ng-if="paymentStateCtrl.booking.paymentState == 'complete'">Encaissée</span>
          </b>
          <br /><br />
          Nouvel état de paiement : <select ng-model="paymentStateCtrl.newPaymentState"><option value="noPayment">Pas de paiement</option><option value="advancePayment">Acompte</option><option value="deposit">Caution</option><option value="complete">Encaissée</option></select><br /><br />
          <input class="btn" ng-class="{'btn_vert':paymentStateCtrl.isOkForm(), 'btn_locked':!paymentStateCtrl.isOkForm()}" type="button" ng-click="paymentStateCtrl.setPaymentStateBooking()" value="Valider le changement" />

          <div><!-- DESIGN -->
            Vous pouvez à tout moment activer le paiement en ligne avec un compte <a href="https://caisse-online.fr/" target="_blank">Caisse Online</a>, le changement d'état de la réservation se fera automatiquement lorsqu'un paiement sera enregistré sur la caisse.
          </div>
        </div>
      </div>
      <div class="loader_box" ng-show="paymentStateCtrl.setPaymentStateActionLaunched">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
          </div>
        </div>
      </div>
    </div>
    <?php include_once('RESA_receipt.php'); ?>
    <div id="resa_reservation" ng-show="backendCtrl.currentPage == 'reservations'">
      <div class="resa_actions">
        <?php if(!RESA_Variables::staffIsConnected()){ ?>
        <a class="resa_btn page-title-action plus_ico btn_add_resa" ng-click="backendCtrl.openEditBooking()">Réservation</a>
        <a class="resa_btn page-title-action plus_ico" ng-click="backendCtrl.setCurrentPage('clients')">Clients</a>
        <a class="resa_btn page-title-action plus_ico" ng-click="backendCtrl.openEditInfoCalendarDialog()">Information</a>
        <a class="resa_btn page-title-action plus_ico" ng-click="backendCtrl.openEditServiceConstraintDialog()">Contrainte</a>
        <?php } ?>
        <div style="color:red; font-weight:bold;" ng-if="backendCtrl.isCaisseOnlineActivatedButNoCaisseOnline()"><?php _e('Attention, nous détectons que vous avez la synchronisation avec la caisse activée mais il vous manque le plugin correspondant pour s\'y connecter. Veuillez nous contacter via le support de RESA Online !', 'resa' ); ?></div>
        <div class="warning" ng-if="backendCtrl.isFirstParameterNotDone()">
          <?php _e('Veuiller procéder à la première installation et aux réglages de RESA Online : ', 'resa' ); ?><br />
          <a target="_blank" href="<?php echo RESA_Variables::getLinkParameters('installation'); ?>" class="btn btn_vert">Aller à la première installation</a>
        </div>
        <div style="color:orange; font-weight:bold;" ng-if="backendCtrl.alertNotSameDateBackend">
          <?php _e('Nous détectons que vous n\'utilisez pas la même fuseau horaire que votre site Wordpress. Veuillez le modifier dans "Réglages" > "Général" de Wordpress.', 'resa' ); ?><br />
        </div>
        <div style="color:orange; font-weight:bold;" ng-if="backendCtrl.settings.needUpdate">
          <?php _e('Une mise à jour est prévue pour vos données de RESA Online, rendez-vous dans les réglages avancés pour l\'effectuer', 'resa' ); ?><br />
					<a target="_parent" href="<?php echo RESA_Variables::getLinkParameters('settings/support'); ?>" class="btn btn_vert">Aller aux réglages avancés</a>
        </div>
      </div>
      <div class="resa_views_menu">
        <h2 class="nav-tab-wrapper">
          <a class="nav-tab resa_list_tab" ng-class="{'nav-tab-active':backendCtrl.viewMode === 'calendar'}" ng-click="backendCtrl.setViewMode('calendar')"><?php _e('calendar_link_title','resa') ?></a>
          <a class="nav-tab  resa_list_tab" ng-class="{'nav-tab-active':backendCtrl.viewMode === 'bookingsList'}" ng-click="backendCtrl.setViewMode('bookingsList');"><?php _e('bookings_list_link_title','resa') ?></a>
          <!--
          RAPPORT BY SERVICE
          <a class="nav-tab resa_report_tab" ng-class="{'nav-tab-active':backendCtrl.viewMode === 'rapports'}" ng-click="backendCtrl.setViewMode('rapports');">Rapports</a>
          //-->
          <a class="nav-tab resa_report_tab" ng-class="{'nav-tab-active':backendCtrl.viewMode === 'planning'}" ng-click="backendCtrl.setViewMode('planning');">Planning (βeta)</a>
          <a class="nav-tab resa_report_tab" ng-class="{'nav-tab-active':backendCtrl.viewMode === 'planningDetails'}" ng-click="backendCtrl.setViewMode('planningDetails');">Planning imprimable</a>
        </h2>
      </div>
      <div class="resa_views">
        <div class="resa_filters_left">
          <div class="filter_type" ng-if="backendCtrl.settings.places!=null && backendCtrl.settings.places.length > 0">
            <h3 class="filter_type_title close"><div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(backendCtrl.filters.places, backendCtrl.allFiltersIsOff(backendCtrl.filters.places)); backendCtrl.generateFilteredBookings(); backendCtrl.setFilterSettings()" class="tgl tgl-light" id="type1filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(backendCtrl.filters.places)" />
              <label class="tgl-btn" for="type1filter1" title="tous / aucun"></label>
            </div><span class="helpbox">Par lieu<span class="helpbox_content">Veuillez recharger les données pour appliquer le filtre sur le lieu</span></span></h3>
            <div class="filter_type_filters">
              <div class="filter" ng-repeat="place in backendCtrl.settings.places track by $index">
                <label for="lfilter_places{{$index}}" class="filter_name">{{ backendCtrl.getTextByLocale(place.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings(); backendCtrl.setFilterSettings()" class="tgl tgl-light" id="lfilter_places{{$index}}" type="checkbox" ng-model="backendCtrl.filters.places[place.id]" />
                  <label class="tgl-btn" for="lfilter_places{{$index}}"></label>
                </div>
              </div>
              <button ng-click="backendCtrl.launchBackgroundAppointmentsLoading()" class="btn btn_vert">Charger</button>
            </div>
          </div>
          <div class="filter_type">
            <h3 class="filter_type_title close"><div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(backendCtrl.filters.states, backendCtrl.allFiltersIsOff(backendCtrl.filters.states)); backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="type2filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(backendCtrl.filters.states)" />
              <label class="tgl-btn" for="type2filter1" title="tous / aucun"></label>
            </div>Par état</h3>
            <div class="filter_type_filters">
              <div class="filter">
                <label for="rfilter_state_quote" class="filter_name">Devis</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="rfilter_state_quote" type="checkbox" ng-model="backendCtrl.filters.states['quotation']" />
                  <label class="tgl-btn" for="rfilter_state_quote"></label>
                </div>
              </div>
              <br />
              <div class="filter" ng-if="state.isFilter" ng-repeat="state in backendCtrl.settings.statesList.slice(0,2)">
                <label for="rfilter_state{{ $index }}" class="filter_name">{{ state.filterName }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="rfilter_state{{ $index }}" type="checkbox" ng-model="backendCtrl.filters.states[state.id]" />
                  <label class="tgl-btn" for="rfilter_state{{ $index }}"></label>
                </div>
              </div>
              <div class="filter">
                <label for="rfilter_state_waiting_payment" class="filter_name">Attente paiement</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="rfilter_state_waiting_payment" type="checkbox" ng-model="backendCtrl.filters.states['waiting_payment']" />
                  <label class="tgl-btn" for="rfilter_state_waiting_payment"></label>
                </div>
              </div>
              <div class="filter">
                <label for="rfilter_state_waiting_expired" class="filter_name">Paiement expiré</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="rfilter_state_waiting_expired" type="checkbox" ng-model="backendCtrl.filters.states['waiting_expired']" />
                  <label class="tgl-btn" for="rfilter_state_waiting_expired"></label>
                </div>
              </div>
              <div class="filter" ng-if="state.isFilter" ng-repeat="state in backendCtrl.settings.statesList.slice(2,backendCtrl.settings.statesList.length)">
                <label for="rfilter_state{{ $index + 3 }}" class="filter_name">{{ state.filterName }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="rfilter_state{{ $index + 3 }}" type="checkbox" ng-model="backendCtrl.filters.states[state.id]" />
                  <label class="tgl-btn" for="rfilter_state{{ $index + 3 }}"></label>
                </div>
              </div>
            </div>
          </div>
          <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_payments']){ ?>
          <div class="filter_type">
            <h3 class="filter_type_title close"> <div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(backendCtrl.filters.statePaymentsList, backendCtrl.allFiltersIsOff(backendCtrl.filters.statePaymentsList)); backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="type3filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(backendCtrl.filters.statePaymentsList)" />
              <label class="tgl-btn" for="type3filter1" title="tous / aucun"></label>
            </div>Par état de paiement</h3>
            <div class="filter_type_filters">
              <div ng-if="state.displayFilter" class="filter" ng-repeat="state in backendCtrl.settings.statePaymentsList">
                <label for="pfilter_statePayments{{$index}}" class="filter_name">{{ state.title }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="pfilter_statePayments{{$index}}" type="checkbox" ng-model="backendCtrl.filters.statePaymentsList[state.id]" />
                  <label class="tgl-btn" for="pfilter_statePayments{{$index}}"></label>
                </div>
              </div>
            </div>
          </div>
          <!--
          <div class="filter_type">
            <h3 class="filter_type_title close"> <div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(backendCtrl.filters.paymentsTypeList, backendCtrl.allFiltersIsOff(backendCtrl.filters.paymentsTypeList)); backendCtrl.generateFilteredBookings();" class="tgl tgl-light" id="type4filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(backendCtrl.filters.paymentsTypeList)" />
              <label class="tgl-btn" for="type4filter1" title="tous / aucun"></label>
            </div>Moyens de paiements</h3>
            <div class="filter_type_filters">
              <div class="filter" ng-repeat="payment in backendCtrl.paymentsTypeList">
                <label for="mpfilter_paymentsType{{ $index }}" class="filter_name">{{ payment.title }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="mpfilter_paymentsType{{ $index }}" type="checkbox" ng-model="backendCtrl.filters.paymentsTypeList[$index]"
                  ng-true-value="'{{ payment.id }}'" />
                  <label class="tgl-btn" for="mpfilter_paymentsType{{ $index }}"></label>
                </div>
              </div>
            </div>
          </div>
          //-->
          <?php } ?>
          <div class="filter_type">
            <h3 class="filter_type_title close"> <div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(backendCtrl.filters.services, backendCtrl.allFiltersIsOff(backendCtrl.filters.services)); backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="type5filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(backendCtrl.filters.services)" />
              <label class="tgl-btn" for="type5filter1" title="tous / aucun"></label>
            </div>Par service</h3>
            <div class="filter_type_filters">
              <div class="filter" ng-repeat="service in backendCtrl.getServicesByNoPlace()">
                <label for="filter_service_noplace_{{ service.id }}" class="filter_name">{{ backendCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="filter_service_noplace_{{ service.id }}" type="checkbox" ng-model="backendCtrl.filters.services[service.id]" />
                  <label class="tgl-btn" for="filter_service_noplace_{{ service.id }}"></label>
                </div>
              </div>
            </div>
            <div class="filter_type_filters">
              <div class="filter" ng-if="backendCtrl.noHaveCategory(service) && backendCtrl.trueIfPlacesNotFiltered(service, backendCtrl.filters)" ng-repeat="service in backendCtrl.getServicesByPlace()">
                <label for="filter_service_nocategory_{{ service.id }}" class="filter_name">{{ backendCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="filter_service_nocategory_{{ service.id }}" type="checkbox" ng-model="backendCtrl.filters.services[service.id]" />
                  <label class="tgl-btn" for="filter_service_nocategory_{{ service.id }}"></label>
                </div>
              </div>
            </div>
            <div class="filter_type_filters" ng-if="backendCtrl.numberOfServices(category) > 0" ng-repeat="category in backendCtrl.getCategories()">
              <h4 class="filter_category"> <div class="filter_input">
              </div>{{ backendCtrl.getTextByLocale(category.label, '<?php echo get_locale(); ?>') }}</h4>
              <div class="filter" ng-if="backendCtrl.trueIfPlacesNotFiltered(service, backendCtrl.filters)" ng-repeat="service in backendCtrl.getServicesByCategory(category)">
                <label class="filter_name" for="filter_service{{ service.id }}">{{ backendCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="filter_service{{ service.id }}" type="checkbox" ng-model="backendCtrl.filters.services[service.id]" />
                  <label class="tgl-btn" for="filter_service{{ service.id  }}"></label>
                </div>
              </div>
            </div>
          </div>
          <div class="filter_type" ng-if="backendCtrl.settings.staffManagement && backendCtrl.members.length > 0">
            <h3 class="filter_type_title close"> <div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(backendCtrl.filters.members, backendCtrl.allFiltersIsOff(backendCtrl.filters.members)); backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="type6filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(backendCtrl.filters.members)" />
              <label class="tgl-btn" for="type6filter1" title="tous / aucun"></label>
            </div>Par {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_single, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h3>
            <div class="filter_type_filters">
              <div class="filter" ng-if="backendCtrl.trueIfPlacesNotFiltered(member, backendCtrl.filters) && backendCtrl.displayMemberFilter(member)" ng-repeat="member in backendCtrl.members">
                <span class="filter_name">{{ member.nickname|htmlSpecialDecode }}</span>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="stafffilter{{ $index }}" type="checkbox" ng-model="backendCtrl.filters.members[member.id]"/>
                  <label class="tgl-btn" for="stafffilter{{ $index }}"></label>
                </div>
              </div>
            </div>
          </div>
          <div class="filter_type">
            <h3 class="filter_type_title close"><div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(backendCtrl.filters.tags, backendCtrl.allFiltersIsOff(backendCtrl.filters.tags)); backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="type1filter15" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(backendCtrl.filters.tags)" />
              <label class="tgl-btn" for="type1filter15" title="tous / aucun"></label>
            </div>Par tag</h3>
            <div class="filter_type_filters">
              <div class="filter">
                <label for="lfilter_no_tag" class="filter_name"><?php _e('No_tag_words','resa'); ?></label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="lfilter_no_tag" type="checkbox" ng-model="backendCtrl.filters.tags['no_tag']" />
                  <label class="tgl-btn" for="lfilter_no_tag"></label>
                </div>
              </div>
              <div class="filter" ng-repeat="tag in backendCtrl.settings.appointmentTags">
                <label for="lfilter_tags{{$index}}" class="filter_name">{{ backendCtrl.getTextByLocale(tag.title, '<?php echo get_locale(); ?>') }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="lfilter_tags{{$index}}" type="checkbox" ng-model="backendCtrl.filters.tags[tag.id]" />
                  <label class="tgl-btn" for="lfilter_tags{{$index}}"></label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="resa_filters_top">
          <div class="filter_type filters_date">
            <select ng-change="backendCtrl.changeDates();" ng-model="backendCtrl.filters.seeBookingsValue">
              <option value="1" ng-selected="backendCtrl.filters.seeBookingsValue == 1"><?php _e('today_word', 'resa') ?> - {{ backendCtrl.today() | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</option>
              <option value="2" ng-selected="backendCtrl.filters.seeBookingsValue == 2"><?php _e('tomorrow_word', 'resa') ?> - {{ backendCtrl.tomorrow() | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</option>
              <option value="3" ng-selected="backendCtrl.filters.seeBookingsValue == 3"><?php _e('in_seven_days_words', 'resa') ?></option>
              <option value="4" ng-selected="backendCtrl.filters.seeBookingsValue == 4"><?php _e('yesterday_word','resa') ?> - {{ backendCtrl.yesterday() | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</option>
              <option value="5" ng-selected="backendCtrl.filters.seeBookingsValue == 5">Date personnalisée</option>
              <option value="7" ng-selected="backendCtrl.filters.seeBookingsValue == 7">Mois</option>
              <option value="6" ng-selected="backendCtrl.filters.seeBookingsValue == 6">Période personnalisée</option>
            </select>
            <span ng-if="backendCtrl.filters.seeBookingsValue == 5 || backendCtrl.filters.seeBookingsValue == 6">
              <input <?php /* uib-datepicker-popup */ ?> ng-click="backendCtrl.popupDate1=true" is-open="backendCtrl.popupDate1" datepicker-options="backendCtrl.dateOptions" type="date" class="form-control" placeholder="0" ng-change="backendCtrl.setCustomDate(backendCtrl.filters.dates.startDate)" ng-model="backendCtrl.filters.dates.startDate" close-text="<?php _e('Close_word', 'resa') ?>" clear-text="<?php _e('Clear_word', 'resa') ?>" current-text="<?php _e('Today_word', 'resa') ?>">
            </span>
            <span ng-if="backendCtrl.filters.seeBookingsValue == 6">
              <input <?php /* uib-datepicker-popup */ ?> ng-click="backendCtrl.popupDate2=true" is-open="backendCtrl.popupDate2" datepicker-options="backendCtrl.dateOptions" type="date" class="form-control" placeholder="0"  ng-model="backendCtrl.filters.dates.endDate" close-text="<?php _e('Close_word', 'resa') ?>" clear-text="<?php _e('Clear_word', 'resa') ?>" current-text="<?php _e('Today_word', 'resa') ?>">
            </span>
            <span ng-if="backendCtrl.filters.seeBookingsValue == 7">
              <select ng-change="backendCtrl.changeCurrentMonthDate()" ng-model="backendCtrl.filters.month" ng-options="month as (month.date|date:'MMMM - yyyy') for month in backendCtrl.filtersMonths"></select>
            </span>
            <span class="btn btn_vert title_box dashicons dashicons-image-rotate" ng-click="backendCtrl.launchBackgroundAppointmentsLoading()">
              <span class="title_box_content">Charger la période</span>
            </span>
          </div>
          <div class="filter_type filters_name">
            <input type="search" ng-change="backendCtrl.regenerateFilteredBookingsByName()" ng-model-options="{ debounce: 500 }" ng-model="backendCtrl.filters.search" placeholder="Id, nom, prénom, téléphone, entreprise, email..." />
          </div>
          <div class="filter_type filters_infos helpbox">
            {{ backendCtrl.getNumberBookings() }} réservations / {{ backendCtrl.getNumberAppointments() }} rendez-vous
            <br />
            <b>{{ backendCtrl.getNumberPersons() }} personnes</b>
            <span class="helpbox_content"><p>Ne compte que les réservations valides !</p></span>
          </div>
          <!--
          <div class="filter_type light_mode">
            <span class="light_mode_title" ng-click="backendCtrl.seeBookingDetails = !backendCtrl.seeBookingDetails">Détails</span>
            <div class="filter_input light_mode_input">
              <input class="tgl tgl-light" id="light_mode" type="checkbox" ng-model="backendCtrl.seeBookingDetails" />
              <label class="tgl-btn" for="light_mode" title="tous / aucun"></label>
            </div>
          </div>
         -->
          <div class="filter_type filters_autres">
            <btn class="btn" ng-click="backendCtrl.reinitFilters()"><?php _e('reinitialize_link_title', 'resa'); ?></btn>
            <btn class="btn" ng-if="backendCtrl.viewMode === 'bookingsList'" ng-click="backendCtrl.printDiv('printBookings')">Imprimer</btn>
            <btn class="btn" ng-if="backendCtrl.viewMode === 'planningDetails'" ng-click="backendCtrl.printDiv('planning_print')">Imprimer</btn>

			      <div class="filter_input participant_filter">
              <input class="tgl tgl-light" id="participantfilter" ng-model="backendCtrl.seeBookingParticipants" type="checkbox" />
              <label class="tgl-btn" for="participantfilter" title="Afficher / Masquer les participants"></label>
            </div>
          </div>
        </div>
        <div class="resa_view_content">
          <div class="resa_view_reservations" ng-if="backendCtrl.viewMode === 'bookingsList'">
            <div class="resa_pagination">
              <div class="pagination_left">
                <ul uib-pagination ng-change="backendCtrl.applyPaginationBookings()" total-items="backendCtrl.filteredBookings.length" items-per-page="backendCtrl.paginationBookings.step" ng-model="backendCtrl.paginationBookings.number" max-size="5" class="pagination-sm pagination" boundary-link-numbers="true" rotate="true" previous-text="<?php _e('previous_link_title', 'resa') ?>" next-text="<?php _e('next_link_title', 'resa') ?>"></ul>
              </div>
              <div class="pagination_right">
                <select ng-change="backendCtrl.applyPaginationBookings()" ng-model="backendCtrl.paginationBookings.step">
    							<option ng-value="10" ng-selected="backendCtrl.paginationBookings.step == 10">10</option>
                  <option ng-value="20">20</option>
                  <option ng-value="30">30</option>
                  <option ng-value="40">40</option>
                  <option ng-value="50">50</option>
    						</select>
    						<?php _e('by_page_words', 'resa'); ?>
              </div>
            </div>
            <div class="resa_list" id="printBookings">
              <div class="resa_reservation {{ booking.cssPaymentState }}" ng-repeat="booking in backendCtrl.displayedBookings" ng-class="{'light': !backendCtrl.seeBookingDetails,'quote': booking.quotation, 'valid': !booking.quotation && booking.status=='ok', 'pending': !booking.quotation && booking.status=='waiting', 'cancelled': !booking.quotation && booking.status=='cancelled', 'abandonned': !booking.quotation && booking.status=='abandonned', 'inerror': false, 'incomplete': false}">
                <div class="reservation_client">
                  <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_customer']){ ?>
                    <div class="resa_id">N°{{backendCtrl.getBookingId(booking)}}</div>
                    <div class="client_name">
                      <span ng-if="booking.customer.lastName.length > 0" class="client_lastname">{{ booking.customer.lastName | htmlSpecialDecode }}</span>
                      <span ng-if="booking.customer.firstName.length > 0" class="client_firstname">{{booking.customer.firstName | htmlSpecialDecode }}</span>
                    </div>
                    <div class="client_company">
                      <span ng-if="booking.customer.company.length > 0">{{ booking.customer.company | htmlSpecialDecode }}</span>
                      <span ng-if="booking.customer.phone.length > 0"><span ng-if="booking.customer.company.length > 0"><br /></span>{{ booking.customer.phone | formatPhone }}</span>
                      <span ng-if="booking.customer.phone2.length > 0"><span ng-if="booking.customer.company.length > 0 || booking.customer.phone.length > 0"><br /></span>{{ booking.customer.phone2 | formatPhone }}</span>
                    </div>
                    <div ng-if="booking.customer.privateNotes.length > 0" class="client_note on b1 helpbox">
                      1 Note Client
                      <span class="helpbox_content"><h4>Note privé client :</h4><p ng-bind-html="booking.customer.privateNotes|htmlSpecialDecode:true"></p></span>
                    </div>
                  <?php } ?>
                </div>
                <div class="reservation_states">
                  <div class="states_state state_valid">Validée</div>
                  <div class="states_state state_quote">{{ backendCtrl.getQuotationName(booking) }}</div>
                  <div class="states_state state_cancelled">Annulée</div>
                  <div class="states_state state_abandonned">Abandonnée</div>
                  <div class="states_state state_pending">{{ backendCtrl.getWaitingName(booking) }}</div>
                  <div class="states_state state_inerror">Erreur</div>
                  <div class="states_state state_incomplete">Incomplète</div>
                  <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_payments']){ ?>
                    <div class="states_paiement" ng-class="{'helpbox':booking.payments.length > 0}">
                      <div class="state_paiement paiement_none">Pas de paiement</div>
                      <div class="state_paiement paiement_incomplete">
                        <span ng-if="!backendCtrl.isDeposit(booking)">Acompte</span>
                        <span ng-if="backendCtrl.isDeposit(booking)">Caution</span>
                      </div>
                      <div class="state_paiement paiement_done">Encaissée</div>
                      <div class="state_paiement paiement_overpaiement">Trop perçu</div>
                      <div class="state_paiement paiement_remboursement">Remboursement dû</div>
                      <div class="state_paiement paiement_remboursement_done">Remboursement complet</div>
                      <div class="helpbox_content" ng-if="booking.payments.length > 0">
  											<table class="table">
  												<thead>
  													<tr>
  														<th>Date</th>
  														<th>Montant</th>
  														<th>Type de paiement</th>
  														<th>Note</th>
  													</tr>
  												</thead>
  												<tbody>
  													<tr ng-if="payment.state != 'pending'" ng-repeat="payment in booking.payments">
  														<td>{{ payment.paymentDate | formatDateTime:'<?php echo $variables['date_format'].' '.$variables['time_format']; ?>' }}
  							              <span class="ro-cancelled" ng-if="payment.state=='cancelled'"><br /><?php _e('cancelled_word', 'resa'); ?></span></td>
  														<td ng-if="payment.repayment">{{ payment.value|negative }}{{ backendCtrl.settings.currency }}</td>
  														<td ng-if="!payment.repayment">{{ payment.value }}{{ backendCtrl.settings.currency }}</td>
  														<td>
                                <span ng-if="payment.isReceipt != null && payment.isReceipt && payment.value >= 0">[Acompte]</span>
                                <span ng-if="payment.isReceipt != null && payment.isReceipt && payment.value < 0">[Remboursement acompte]</span>
                                {{ backendCtrl.getPaymentName(payment.type, payment.name) }}
                              </td>
  														<td class="ng-binding">{{ payment.note }}</td>
  													</tr>
  												</tbody>
  											</table>
  										</div>
                    </div>
                    <div class="states_paiement_value">
                      <span ng-if="booking.status != 'cancelled' && booking.status != 'abandonned'">
                        <span ng-if="booking.paymentsCaisseOnlineDone || (!backendCtrl.isCaisseOnlineActivated() && backendCtrl.round(booking.totalPrice - booking.needToPay) > 0)">{{ backendCtrl.round(booking.totalPrice - booking.needToPay) }}{{ backendCtrl.settings.currency }} / </span>
                        {{ booking.totalPrice }}{{ backendCtrl.settings.currency }}
                      </span>
                      <span ng-if="(booking.status == 'cancelled' || booking.status == 'abandonned') && booking.needToPay < 0 && (booking.paymentsCaisseOnlineDone || !backendCtrl.isCaisseOnlineActivated())">
                        {{ -(booking.needToPay - booking.totalPrice) }}{{ backendCtrl.settings.currency }}
                      </span>
                      <a class="no-print" ng-if="backendCtrl.isCaisseOnlineActivated()" ng-click="backendCtrl.getPaymentsForBooking(booking)">Paiements</a>
                    </div>
                  <?php } ?>
                </div>
                <div class="reservation_notes no-print">
                  <?php if(!RESA_Variables::staffIsConnected()){ ?>
                  <div class="reservation_private_note" ng-class="{'on helpbox': booking.note.length > 0, 'off': booking.note.length == 0}">
                    Note privée
                    <span class="helpbox_content" ng-if="booking.note.length > 0"><h4>Note privée :</h4><p ng-bind-html="booking.note|htmlSpecialDecode:true"></p></span>
                  </div>
                  <div class="reservation_public_note" ng-class="{'on helpbox': booking.publicNote.length > 0, 'off': booking.publicNote.length == 0}">
                    Note publique
                    <span class="helpbox_content" ng-if="booking.publicNote.length > 0"><h4>Note publique :</h4><p ng-bind-html="booking.publicNote|htmlSpecialDecode:true"></p></span>
                  </div>
                  <div class="reservation_customer_note" ng-class="{'on helpbox': booking.customerNote.length > 0, 'off': booking.customerNote.length == 0}">
                    Remarque cliente
                    <span class="helpbox_content" ng-if="booking.customerNote.length > 0"><h4>Remarque cliente :</h4><p ng-bind-html="booking.customerNote|htmlSpecialDecode:true"></p></span>
                  </div>
                  <?php } ?>
                  <div ng-if="backendCtrl.settings.staffManagement" class="reservation_member_note" ng-class="{'on helpbox': booking.staffNote.length > 0, 'off': booking.staffNote.length == 0}">
                    Note {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                    <span class="helpbox_content" ng-if="booking.staffNote.length > 0"><h4>Note des {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} :</h4><p ng-bind-html="booking.staffNote|htmlSpecialDecode:true"></p></span>
                  </div>
                </div>
                <div class="reservation_suivi">
                  <div class="suivi_title">
                    <div class="title_date">Créée le </div>
                    <div class="title_time">à</div>
                    <div class="title_user"><?php _e('by_word', 'resa') ?></div>
                  </div>
                  <div class="suivi_content">
                    <div class="content_date">{{ booking.creationDate|formatDateTime:'<?php echo $variables['date_format']; ?>' }}</div>
                    <div class="content_time">{{ booking.creationDate|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</div>
                    <div class="content_user">{{ booking.userCreator }}</div>
                  </div>
                </div>
                <div class="reservation_actions no-print">
                  <?php if(!RESA_Variables::staffIsConnected()){ ?>
                    <span class="btn btn_modifier title_box dashicons dashicons-edit"  ng-click="backendCtrl.openEditBooking(backendCtrl.getBookingById(booking.id))">
                     <span ng-if="!booking.quotation" class="title_box_content">Modifier la réservation</span>
                     <span ng-if="booking.quotation" class="title_box_content">Modifier le devis</span>
                    </span>
                    <span class="btn btn_notify title_box dashicons dashicons-email-alt" ng-click="backendCtrl.openNotificationDialog(backendCtrl.getCustomerById(booking.idCustomer), backendCtrl.getBookingById(booking.id))">
                      <span class="title_box_content">Envoyer une notification</span>
                    </span>
                    <span class="btn btn_client title_box dashicons dashicons-admin-users" ng-click="backendCtrl.openCustomerDialog(booking.idCustomer)">
                      <span class="title_box_content">Voir la fiche client</span>
                    </span>
            				<span class="btn btn_client title_box dashicons dashicons-cart" ng-if="backendCtrl.isCaisseOnlineActivated() && !booking.quotation" ng-click="backendCtrl.payBookingCaisseOnline(booking)">
                      <span class="title_box_content">Envoyer la réservation sur la caisse</span>
                    </span>
            				<span class="btn btn_client title_box dashicons dashicons-tag" ng-if="!backendCtrl.isCaisseOnlineActivated() && !booking.quotation"
                    ng-click="backendCtrl.openPaymentStateDialog(booking);">
                      <span class="title_box_content">Changer l'état de paiement</span>
                    </span>
                    <span class="btn btn_reciept title_box dashicons dashicons-format-aside" ng-click="backendCtrl.openReceiptBookingDialog(backendCtrl.getBookingById(booking.id))">
                      <span ng-if="!booking.quotation" class="title_box_content">Reçu de la réservation</span>
                      <span ng-if="booking.quotation" class="title_box_content">Reçu du devis</span>
                    </span>
                  <?php } ?>
                </div>
                <div class="reservation_bookings">
                  <div class="booking" ng-class="{'confirmed':appointment.state == 'ok','not_confirmed':appointment.state == 'waiting','cancelled':appointment.state == 'cancelled' || appointment.state == 'abandonned', 'show_participants':backendCtrl.seeBookingParticipants && backendCtrl.getServiceById(appointment.idService).askParticipants }" ng-repeat="appointment in booking.appointments|orderBy:'startDate'">
                    <div class="booking_date">{{ appointment.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }}</div>
                    <div class="booking_hour">
                      <span ng-if="!appointment.noEnd">
      									{{ appointment.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      									<?php _e('to_word', 'resa') ?>
      									{{ appointment.endDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      								</span>
      								<span ng-if="appointment.noEnd">
      									<?php _e('begin_word', 'resa'); ?> {{ appointment.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      								</span>
                    </div>
                    <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_numbers']){ ?>
                    <div class="booking_nb_people" ng-class="{'helpbox': backendCtrl.getServiceById(appointment.idService).askParticipants}">
                      {{ appointment.numbers }} pers.
                      <span ng-if="backendCtrl.getServiceById(appointment.idService).askParticipants" class="helpbox_content"><h4>Participants</h4>
                        <span ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                          <table>
                            <thead>
                              <tr>
                                <td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">
                                  {{ backendCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
                                </td>
                              </tr>
                            </thead>
                            <tbody>
                              <tr ng-repeat="participant in appointmentNumberPrice.participants track by $index"><td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">{{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</td></tr>
                            </tbody>
                            <tfoot  ng-if="backendCtrl.isDisplaySum(backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields)">
                              <tr>
                                <td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields"><span class="gras2" ng-if="field.displaySum">Somme : {{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getDisplaySum(field, appointmentNumberPrice.participants) }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</span></td>
                              </tr>
                            </tfoot>
                          </table>
                        </span>
                      </span>
                    </div>
                    <?php } ?>
                    <div class="booking_states">
                      <div class="states_state state_confirmed">Confirmée</div>
                      <div class="states_state state_not_confirmed">Non confirmée</div>
                      <div class="states_state state_cancelled">Annulée</div>
                      <div class="states_state state_inerror">Erreur</div>
                      <div class="states_state state_incomplete">Incomplète</div>
                    </div>
                    <div class="booking_place"><span ng-if="appointment.idPlace != ''">{{ backendCtrl.getTextByLocale(backendCtrl.getPlaceById(appointment.idPlace).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span>
					           </div>
                    <div class="booking_service">{{ backendCtrl.getTextByLocale(
      										backendCtrl.getServiceById(appointment.idService).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                    <div class="booking_prices">
                      <div class="prices_price" ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                        <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_numbers']){ ?>
                        <span class="price_nb">{{ appointmentNumberPrice.number }}</span>
                        <?php } ?>
                        <span class="price_name">
                          {{ backendCtrl.getServicePriceAppointmentName(appointment.idService, appointmentNumberPrice.idPrice) |htmlSpecialDecode
        									}}
                          <span ng-if="backendCtrl.haveEquipments(appointment.idService, appointmentNumberPrice.idPrice)">
                            ({{ backendCtrl.getServicePriceAppointmentEquipmentName(appointment.idService, appointmentNumberPrice.idPrice)|htmlSpecialDecode
          									}})
                          </span>
                        </span>
                        <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_payments']){ ?>
                          <span class="price_value" ng-if="!appointmentNumberPrice.deactivated">
                            <span ng-if="appointmentNumberPrice.totalPrice != 0">{{ appointmentNumberPrice.totalPrice }}  {{ backendCtrl.settings.currency }}</span>
                            <span ng-if="appointmentNumberPrice.totalPrice == 0">
                              <span ng-if="backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
                                {{ backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).price }} {{ backendCtrl.settings.currency }}
                              </span>
                              <span ng-if="!backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
                                {{ backendCtrl.getPriceNumberPrice(appointment.idService, appointmentNumberPrice) }} {{ backendCtrl.settings.currency }}
                              </span>
                            </span>
                          </span>
                        <?php } ?>
                      </div>
                    </div>
                    <div class="booking_members" ng-if="backendCtrl.settings.staffManagement">
                      <div class="member" ng-if="appointmentMember.number > 0" ng-repeat="appointmentMember in appointment.appointmentMembers">{{ backendCtrl.getMemberById(appointmentMember.idMember).nickname|htmlSpecialDecode }}<br /></div>
                    </div>
                    <div class="booking_tags">
                      <div class="tag {{ backendCtrl.getTagById(idTag).color }}" ng-repeat="idTag in appointment.tags">
                        {{ backendCtrl.getTextByLocale(backendCtrl.getTagById(idTag).title, '<?php echo get_locale(); ?>') }}
                      </div>
                    </div>
					          <div class="table_participant" ng-if="backendCtrl.getServiceById(appointment.idService).askParticipants">
                        <span ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                          <table>
                            <tbody>
                              <tr ng-repeat="participant in appointmentNumberPrice.participants track by $index"><td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">{{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</td></tr>
                            </tbody>
                            <tfoot  ng-if="backendCtrl.isDisplaySum(backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields)">
                              <tr>
                                <td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields"><span class="gras2" ng-if="field.displaySum">Somme : {{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getDisplaySum(field, appointmentNumberPrice.participants) }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</span></td>
                              </tr>
                            </tfoot>
                          </table>
                        </span>
					           </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="resa_view_calendar" ng-if="backendCtrl.viewMode === 'calendar'">
            <div class="resa_calendar">
              <h3>{{ backendCtrl.calendarTitle }}</h3>
      				<div class="col-md-6 text-center">
      					<div class="btn-group">
      						<button class="btn btn-primary" mwl-date-modifier date="backendCtrl.viewDate" decrement="backendCtrl.calendarView" ng-click="backendCtrl.changeCalendarViewDate()" ><?php _e('previous_link_title', 'resa') ?></button>
      						<button class="btn btn-default" mwl-date-modifier date="backendCtrl.viewDate" set-to-today  ng-click="backendCtrl.changeCalendarViewDate()"><?php _e('today_link_title', 'resa') ?></button>
      						<button class="btn btn-primary" mwl-date-modifier date="backendCtrl.viewDate" increment="backendCtrl.calendarView"  ng-click="backendCtrl.changeCalendarViewDate()"><?php _e('next_link_title', 'resa') ?></button>
      					</div>
      				</div>
      				<div class="col-md-6 text-center">
      					<div class="btn-group">
      						<button class="btn btn-primary" ng-model="backendCtrl.calendarView" uib-btn-radio="'month'" ng-click="backendCtrl.changeCalendarViewDate()"><?php _e('month_link_title', 'resa') ?></button>
      						<button class="btn btn-primary" ng-model="backendCtrl.calendarView" uib-btn-radio="'week'" ng-click="backendCtrl.changeCalendarViewDate()"><?php _e('week_link_title', 'resa') ?></button>
      						<button class="btn btn-primary" ng-model="backendCtrl.calendarView" uib-btn-radio="'day'" ng-click="backendCtrl.changeCalendarViewDate()"><?php _e('day_link_title', 'resa') ?></button>
      					</div>
      				</div>
      				<br />
      				<br />

      				<script id="groupedMonthEvents.html" type="text/ng-template">
      					<div mwl-droppable
      					  on-drop="vm.handleEventDrop(dropData.event, day.date, dropData.draggedFromDate)"
      					  mwl-drag-select="!!vm.onDateRangeSelect"
      					  on-drag-select-start="vm.onDragSelectStart(day)"
      					  on-drag-select-move="vm.onDragSelectMove(day)"
      					  on-drag-select-end="vm.onDragSelectEnd(day)"
      					  class="cal-month-day {{ day.cssClass }}"
      						ng-click="vm.calendarCtrl.dateClicked(day.date)"
      					  ng-class="{
      					    'cal-day-outmonth': !day.inMonth,
      					    'cal-day-inmonth': day.inMonth,
      					    'cal-day-weekend': day.isWeekend,
      					    'cal-day-past': day.isPast,
      					    'cal-day-today': day.isToday,
      					    'cal-day-future': day.isFuture,
      					    'cal-day-selected': vm.dateRangeSelect && vm.dateRangeSelect.startDate <= day.date && day.date <= vm.dateRangeSelect.endDate,
      					    'cal-day-open': dayIndex === vm.openDayIndex
      					  }">
      					<small
      				    class="cal-events-num badge badge-important pull-left"
      				    ng-show="day.badgeTotal > 0 && (vm.calendarConfig.displayAllMonthEvents || day.inMonth)"
      				    ng-bind="day.badgeTotal">
      				  </small>
      			    <span
      			      class="pull-right"
      			      data-cal-date
      			      ng-click="vm.calendarCtrl.dateClicked(day.date)"
      			      ng-bind="day.label">
      			    </span>
      					<div class="events-list">
      			      <span ng-repeat="(type, events) in day.groups track by type">
      							<a style="text-align:center;" href="javascript:;"
      						    ng-click="$event.stopPropagation(); vm.onEventClick({calendarEvent: events[0]})"
      						    class="pull-left event"
      						    ng-class="event.cssClass"
      						    ng-style="{backgroundColor: events[0].color.primary}"
      						    ng-mousedown="$event.stopPropagation()"
      						    ng-mouseenter="vm.highlightEvent(events[0], true)"
      						    ng-mouseleave="vm.highlightEvent(events[0], false)"
      						    tooltip-append-to-body="true"
      						    uib-tooltip-html="vm.calendarEventTitle.monthViewTooltip(events[0]) | calendarTrustAsHtml"
      						    mwl-draggable="events[0].draggable === true"
      						    drop-data="{event: events[0], draggedFromDate: day.date.toDate()}"
      						    auto-scroll="vm.draggableAutoScroll">
      								<span style="color:white; text-decoration:none!important;" ng-show="events.length > 1">{{ events.length }}</span>
      						  </a>
      						</span>
      					</div>
      					<div class="cal-day-tick" ng-show="dayIndex === vm.openDayIndex && (vm.cellAutoOpenDisabled || vm.view[vm.openDayIndex].events.length > 0) && !vm.slideBoxDisabled">
      				    <i class="glyphicon glyphicon-chevron-up"></i>
      				    <i class="fa fa-chevron-up"></i>
      				  </div>

      					<div id="cal-week-box" ng-if="$first && rowHovered">
      				    <span ng-bind="vm.getWeekNumberLabel(day)"></span>
      				  </div>
      			  </div>
      			  </script>

      				<mwl-calendar
      				    view="backendCtrl.calendarView"
      				    view-date="backendCtrl.viewDate"
      				    events="backendCtrl.events"
      				    view-title="backendCtrl.calendarTitle"
      				    on-event-click="backendCtrl.eventClicked(calendarEvent)"
      						on-view-change-click="backendCtrl.dateClicked(calendarDate)"
      				    on-event-times-changed="calendarEvent.startsAt = calendarNewEventStart; calendarEvent.endsAt = calendarNewEventEnd; backendCtrl.eventTimesChanged(calendarEvent);"
      						cell-auto-open-disabled="true"
      						cell-modifier="backendCtrl.groupEvents(calendarCell)"
      				    day-view-start="<?php echo $variables['calendar']['start_time']; ?>:00"
      				    day-view-end="<?php echo $variables['calendar']['end_time']; ?>:00"
      						day-view-split="<?php echo $variables['calendar']['split_time']; ?>">
      				</mwl-calendar>
            </div>
          </div>
          <?php
          // RAPPORT BY SERVICE
          /*
          <div class="resa_view_report" ng-if="backendCtrl.viewMode === 'rapports'">
            <div class="resa_report_menu" ng-if="backendCtrl.settings.staffManagement">
              <h2 class="nav-tab-wrapper">
                <a class="nav-tab cal_tab" ng-class="{'nav-tab-active': backendCtrl.rapportsBy == 'services'}" ng-click="backendCtrl.setCurrentRapports('services')">Rapports par service</a>
              </h2>
            </div>
            <div class="resa_reports">
              <div class="resa_pagination" ng-if="backendCtrl.rapportsBy == 'services'">
                <div class="pagination_left">
                  <ul uib-pagination ng-change="backendCtrl.applyPaginationRapportsByServices()" total-items="backendCtrl.filteredRapportsByServices.length" items-per-page="backendCtrl.paginationRapportsByServices.step" ng-model="backendCtrl.paginationRapportsByServices.number" max-size="5" class="pagination-sm pagination" boundary-link-numbers="true" rotate="true" previous-text="<?php _e('previous_link_title', 'resa') ?>" next-text="<?php _e('next_link_title', 'resa') ?>"></ul>
                </div>
                <div class="pagination_right">
                  <select ng-change="backendCtrl.applyPaginationRapportsByServices()" ng-model="backendCtrl.paginationRapportsByServices.step">
                    <option ng-value="10" ng-selected="backendCtrl.paginationRapportsByServices.step == 10">10</option>
                    <option ng-value="20">20</option>
                    <option ng-value="30">30</option>
                    <option ng-value="40">40</option>
                    <option ng-value="50">50</option>
                  </select>
                  <?php _e('by_page_words', 'resa'); ?>
                </div>
              </div>
              <div class="resa_reports_by_services" ng-if="backendCtrl.rapportsBy == 'services'">
                <div class="report service_report" id="printRapport{{ $index }}" ng-repeat="rapportByServices in backendCtrl.displayedRapportsByServices">
                  <div class="service_report_headline report_headline">
                      <div class="report_title"><span ng-if="rapportByServices.idPlace!=''">[{{ backendCtrl.getTextByLocale(backendCtrl.getPlaceById(rapportByServices.idPlace).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}]</span> {{ backendCtrl.getTextByLocale(rapportByServices.service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                      <div class="report_datetime">
                        <div class="report_date">
                          <b>Date : </b>{{ rapportByServices.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }}
                        </div>
                        <div class="report_time">
                          <b>Créneau : </b>
                          <span ng-if="!rapportByServices.noEnd">
                            {{ rapportByServices.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                            <?php _e('to_word', 'resa') ?>
                            {{ rapportByServices.endDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                          </span>
                          <span ng-if="rapportByServices.noEnd">
                            <?php _e('begin_word', 'resa'); ?> {{ rapportByServices.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                          </span>
                        </div>
                      </div>
                      <div class="report_infos">
                        <div class="report_info">
                          <b>Infos : </b>{{ rapportByServices.appointments.length }} rendez-vous
                        </div>
                        <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_numbers']){ ?>
                          <div class="report_nb">
                            <b>Nombres : </b>{{ rapportByServices.numberPersons }} personne(s)
                          </div>
            						  <div ng-if="backendCtrl.getNumberParticipantsNotInGroups(rapportByServices) > 0" class="report_nb_notgrouped important">
                            <b>Non attribuées : </b>{{ backendCtrl.getNumberParticipantsNotInGroups(rapportByServices) }} personne(s)
                          </div>
                        <?php } ?>
                      </div>
                      <div class="report_actions no-print">
                        <?php if(!RESA_Variables::staffIsConnected()){ ?>
                          <span ng-if="backendCtrl.settings.groupsManagement" class="btn btn_print title_box dashicons dashicons-groups" ng-click="backendCtrl.openGroupsManagerDialog(backendCtrl.getRapportByServicesWithFiltered(rapportByServices))">
                            <span class="title_box_content">Gérer les groupes</span>
                          </span>
                          <span ng-if="backendCtrl.settings.groupsManagement" class="btn btn_edit_group title_box dashicons dashicons-calendar-alt" ng-click="backendCtrl.setCustomDate(rapportByServices.startDate); backendCtrl.generateFilteredBookings(); backendCtrl.setCurrentPage('planning'); backendCtrl.setViewMode('members');">
                            <span class="title_box_content">Voir Planning {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span>
                          </span>
                        <?php } ?>
                        <span class="btn btn_print title_box dashicons dashicons-format-aside no-print" ng-click="backendCtrl.printDiv('printRapport' + $index)">
                          <span class="title_box_content">Imprimer</span>
                        </span>
                      </div>
                  </div>
                  <div class="service_report_content grid7">
                      <div class="report_content_titles">
                        <div class="title_infos">Infos réservation</div>
                      </div>
                      <div class="report_content_bookings">
                        <div class="report_content_booking" ng-repeat="appointment in rapportByServices.appointments track by $index">
                          <div class="resa_reservation {{ booking.cssPaymentState }}" ng-class="{'quote': appointment.quotation, 'valid': !appointment.quotation && appointment.state=='ok', 'pending': !appointment.quotation && appointment.state=='waiting', 'cancelled': !appointment.quotation && appointment.state=='cancelled', 'abandonned': !appointment.quotation && appointment.state=='abandonned', 'inerror': false, 'incomplete': false }">
                            <div class="report_content_booking_infos">
                              <div class="reservation_client">
                                  <div class="resa_id">N°{{backendCtrl.getBookingId(appointment)}}</div>
                                  <div class="client_name">
									                <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_customer']){ ?>
                                    <span ng-if="appointment.customer.lastName.length > 0" class="client_lastname">{{ appointment.customer.lastName | htmlSpecialDecode }}<br /></span>
                                    <span ng-if="appointment.customer.firstName.length > 0" class="client_firstname">{{ appointment.customer.firstName | htmlSpecialDecode }}<br /></span>
                                    <div class="client_company">
                                      <span ng-if="appointment.customer.company.length > 0">{{ appointment.customer.company | htmlSpecialDecode }}</span>
                                      <span ng-if="appointment.customer.phone.length > 0"><span ng-if="appointment.customer.company.length > 0"><br /></span>{{ appointment.customer.phone|formatPhone }}</span>
                                      <span ng-if="appointment.customer.phone2.length > 0"><span ng-if="appointment.customer.company.length > 0 || appointment.customer.phone.length > 0"><br /></span>{{ appointment.customer.phone2 | formatPhone }}</span>
                                    </div>
                  								 <?php } ?>
                  									<div class="booking_tags">
                                      <div class="tag {{ backendCtrl.getTagById(idTag).color }}" ng-repeat="idTag in appointment.tags">
                                        {{ backendCtrl.getTextByLocale(backendCtrl.getTagById(idTag).title, '<?php echo get_locale(); ?>') }}
                                      </div>
                  									</div>
                                  </div>
                              </div>
                              <div class="reservation_states">
                                <div class="states_state state_valid">Validé</div>
                                <div class="states_state state_quote">{{ backendCtrl.getQuotationName(backendCtrl.getBookingById(appointment.idBooking)) }}</div>
                                <div class="states_state state_cancelled">Annulé</div>
                                <div class="states_state state_abandonned">Abandonné</div>
                                <div class="states_state state_pending">{{ backendCtrl.getWaitingName(backendCtrl.getBookingById(appointment.idBooking)) }}</div>
                                <div class="states_state state_inerror">Erreur</div>
                                <div class="states_state state_incomplete">Incomplète</div>
                                <div class="states_paiement">
                                  <div class="state_paiement paiement_none">Pas de paiement</div>
                                  <div class="state_paiement paiement_incomplete">
                                    <span ng-if="!backendCtrl.isDeposit(booking)">Acompte</span>
                                    <span ng-if="backendCtrl.isDeposit(booking)">Caution</span>
                                  </div>
                                  <div class="state_paiement paiement_done">Encaissée</div>
                                  <div class="state_paiement paiement_overpaiement">Trop perçu</div>
                                  <div class="state_paiement paiement_remboursement">Remboursement dû</div>
                                  <div class="state_paiement paiement_remboursement_done">Remboursement complet</div>
                                </div>
                                <?php if(!RESA_Variables::staffIsConnected()){ ?>
  								                <span ng-click="backendCtrl.openEditBooking(backendCtrl.getBookingById(appointment.idBooking))" class="btn  no-print title_box dashicons dashicons-edit">
                                    <span ng-if="!appointment.quotation" class="title_box_content">Modifier la réservation</span>
                                    <span ng-if="appointment.quotation" class="title_box_content">Modifier le devis</span>
                                  </span>
  								                <span ng-click="backendCtrl.openNotificationDialog(backendCtrl.getCustomerById(appointment.customer.ID), backendCtrl.getBookingById(appointment.idBooking))" class="btn no-print title_box dashicons dashicons-email-alt" ><span class="title_box_content">Envoyer une notification</span></span>
                                <?php } ?>
                              </div>
                            </div>
                            <div class="report_content_booking_participants">
                              <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_numbers']){ ?>
                                <div class="participant" ng-repeat-start="appointmentNumberPrice in appointment.appointmentNumberPrices">
                                  <div class="participants_{{ field.varname }}" ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields"><b>{{ backendCtrl.getTextByLocale(field.name, '<?php echo get_locale(); ?>') }}</b></div>
                                  <div ng-if="backendCtrl.settings.groupsManagement" class="participants_group"><b>Groupe</b></div>
                                </div>
                                <div class="participant" ng-repeat-end ng-repeat="participant in appointmentNumberPrice.participants track by $index">
                                  <div class="participants_{{ field.varname }}" ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields track by $index">{{ backendCtrl.getTextByLocale(field.prefix, '<?php echo get_locale(); ?>')}}{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getTextByLocale(field.suffix, '<?php echo get_locale(); ?>')}}</div>
                                  <div ng-if="backendCtrl.settings.groupsManagement" class="participants_group" >{{ backendCtrl.getGroupByIdParticipantName(rapportByServices, participant.uri) }}</div>
                                </div>
                              <?php } ?>
                            </div>
                          </div>
                      </div>
                    </div>
                  </div>
                  <div class="service_report_members" ng-if="backendCtrl.settings.staffManagement"  ng-class="{'no-print':rapportByServices.idMembers.length == 0}">
                    <h4 class="report_members_title">{{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} attribués</h4>
                    <h5 class="report_members_capacity helpbox no-print" ng-if="false"> Capacité = 9 / 16<span class="helpbox_content"> <h4>Capacité des {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h4><p>Capacité = nombre de personne / capacité maximum des {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</p></span></h5>
                    <div class="report_members_list">
                      <div class="member" ng-repeat="idMember in rapportByServices.idMembers">
                        {{ backendCtrl.getMemberById(idMember).nickname|htmlSpecialDecode }}
                      </div>
                      <input ng-if="false" class="btn_wide no-print" type="button" value="Modifier" />
                    </div>
                    <h4 class="report_members_title" ng-if="backendCtrl.settings.groupsManagement">Groupes associés</h4>
                    <div class="report_members_list_group" ng-if="backendCtrl.settings.groupsManagement">
                      <div class="a_group" style="border: 1.5px solid {{ group.color }}" ng-repeat="group in rapportByServices.groups">
                        <span class="a_group_name">{{ backendCtrl.getGroupName(group) }}</span>
                        <span ng-class="{'bg_rouge': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) > group.max, 'bg_gris': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) == 0, 'bg_vert': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) <= group.max}">{{ backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) }} / {{ group.max }} pers.</span>
                        <span ng-repeat="idMember in group.idMembers track by $index"><br />{{ backendCtrl.getMemberById(idMember).nickname|htmlSpecialDecode }}</span>
                      </div>
                    </div>

                    <!-- TODO plus tard (modification des membres depuis le rapport)
                    <div class="report_members_modify no">
                      <div class="modify_member">
                        <input class="member_input" list="members" />
                        <datalist id="members">
                          <option value="Francis (x)"> </option>
                          <option value="Gérard (x)"> </option>
                          <option value="George (x)"> </option>
                          <option value="Martin (x)"></option>
                        </datalist>
                        <input type="button" value="Supprimer" />
                      </div>
                      <input class="btn_wide" type="button" value="Ajouter" />
                      <input class="btn_wide btn_vert" type="button" value="Valider" />
                      <input class="btn_wide btn_jaune" type="button" value="Annuler" />
                    </div>
                    -->
                  </div>
                </div>
              </div>
            </div>
          </div>
          */
          ?>
          <div ng-controller="PlanningServiceController as planningServiceCtrl" class="resa_view_planning" ng-if="backendCtrl.viewMode === 'planning'">
            <div class="btn-group">
              <input type="date" ng-model="backendCtrl.datePlanningMembers" ng-change="backendCtrl.changeDatePlanningMembers()" />
      				<button class="btn btn-primary" ng-click="backendCtrl.decrementDatePlanningMembers(); planningServiceCtrl.render();"><?php _e('previous_link_title', 'resa') ?></button>
      				<button class="btn btn-primary" ng-click="backendCtrl.incrementDatePlanningMembers(); planningServiceCtrl.render();"><?php _e('next_link_title', 'resa') ?></button>
              Date du planning : {{ backendCtrl.datePlanningMembers|formatDate:'EEEE <?php echo $variables['date_format']; ?>' }}
    			  </div>
            <canvas id="planningService" ng-init="planningServiceCtrl.initialize('planningService')" height="3000"></canvas>
          </div>
          <div id="planning_print" ng-if="backendCtrl.viewMode === 'planningDetails'">
            <div class="btn-group no-print">
              <input type="date" ng-model="backendCtrl.datePlanningMembers" ng-change="backendCtrl.changeDatePlanningMembers()" />
      				<button class="btn btn-primary" ng-click="backendCtrl.decrementDatePlanningMembers(); planningServiceCtrl.render();"><?php _e('previous_link_title', 'resa') ?></button>
      				<button class="btn btn-primary" ng-click="backendCtrl.incrementDatePlanningMembers(); planningServiceCtrl.render();"><?php _e('next_link_title', 'resa') ?></button>
              Date du planning : {{ backendCtrl.datePlanningMembers|formatDate:'EEEE <?php echo $variables['date_format']; ?>' }}
    			  </div>
            <div class="table-planning" ng-if="backendCtrl.getAllFilteredRapportsByServicesWithIdService(service.id, backendCtrl.datePlanningMembers).length > 0" ng-repeat="service in backendCtrl.getNotOldServices()">
              <div class="head">
                <div class="col2">{{ backendCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                <div class="timeline">
                  <span class="tl_hour" style="width:{{ backendCtrl.getNumberOfPourcentFloor() }}%;"  ng-repeat-start="num in backendCtrl.getRepeat(backendCtrl.settings.calendar.end_time - backendCtrl.settings.calendar.start_time) track by $index"><span class="hour_txt">{{ (backendCtrl.settings.calendar.start_time*1 + $index) }}h</span></span>
                  <span class="tl_min" style="width:{{ backendCtrl.getNumberOfPourcentFloor() }}%;" ng-repeat-end><span class="min_txt">30</span></span>
                  <span class="tl_hour">
                    <span class="hour_txt">{{ backendCtrl.settings.calendar.end_time }}h</span>
                  </span>
                </div>
                <div class="col">Etat</div>
              </div>
              <div class="body" ng-repeat="rapportByService in backendCtrl.getAllFilteredRapportsByServicesWithIdService(service.id, backendCtrl.datePlanningMembers)">
                <span ng-repeat="appointment in rapportByService.appointments">
                  <div class="body" ng-if="appointment.appointmentMembers.length > 0" ng-repeat="appointmentMember in appointment.appointmentMembers">
                    <div class="col">{{ backendCtrl.getMemberById(appointmentMember.idMember).nickname|htmlSpecialDecode }}<!-- appointmentMember //--></div>
                    <div class="col customer_name"><span ng-if="appointment.customer.company.length > 0">{{ appointment.customer.company | htmlSpecialDecode }} </span>{{ appointment.customer.lastName | htmlSpecialDecode }} {{ appointment.customer.firstName | htmlSpecialDecode }}</div><div class="timeline"><div ng-click="backendCtrl.openDisplayBookings([backendCtrl.getBookingById(appointment.idBooking)]);" class="creneau" style="height: 30px; width:{{ backendCtrl.getAppointmentWidth(rapportByService) }}%; margin-left:{{ backendCtrl.getAppointmentLeft(rapportByService) }}%; background-color:{{ service.color }}; color:white; text-align:center;">{{ appointmentMember.number }}</div></div><div class="col">
                      <span ng-if="appointment.state == 'ok'">Confirmé</span>
                      <span ng-if="appointment.state == 'waiting'">En attente</span>
                    </div>
                  </div>
                  <div class="body" ng-if="appointment.appointmentMembers.length == 0">
                    <div class="col2 customer_name"><span ng-if="appointment.customer.company.length > 0">{{ appointment.customer.company | htmlSpecialDecode }} </span>{{ appointment.customer.lastName | htmlSpecialDecode }} {{ appointment.customer.firstName | htmlSpecialDecode }}</div>
                    <div class="timeline">
                      <div ng-click="backendCtrl.openDisplayBookings([backendCtrl.getBookingById(appointment.idBooking)]);" class="creneau" style="height: 30px; width:{{ backendCtrl.getAppointmentWidth(rapportByService) }}%; margin-left: {{ backendCtrl.getAppointmentLeft(rapportByService) }}%; background-color:{{ service.color }}; color:white; text-align:center;">
                        {{ appointment.numbers }}
                      </div>
                    </div>
                    <div class="col">
                      <span ng-if="appointment.state == 'ok'">Confirmé</span>
                      <span ng-if="appointment.state == 'waiting'">En attente</span>
                    </div>
                  </div>
                </span>
              </div>
              <div class="footer">
                <!-- Partie sur le total -->
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="loader_box" ng-show="backendCtrl.loadingData || backendCtrl.actionDropProcess || backendCtrl.launchAddNewCustomer || backendCtrl.payBookingActionLaunched">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
            <a ng-if="backendCtrl.loadingData && !backendCtrl.firstLoadingData" class="link" ng-click="backendCtrl.cancelBackgroundAppointmentsLoading()">Annuler</a>
          </div>
        </div>
      </div>
    </div>
    <div id="resa_reservation" ng-show="backendCtrl.currentPage == 'quotations'" ng-controller="QuotationsListController as quotationsListCtrl">
      <div class="resa_actions">
        <?php if(!RESA_Variables::staffIsConnected()){ ?>
          <a class="resa_btn page-title-action plus_ico btn_add_resa" ng-click="quotationsListCtrl.openEditBooking()">Ajouter un devis</a>
        <?php } ?>
        <div style="color:red; font-weight:bold;" ng-if="backendCtrl.isCaisseOnlineActivatedButNoCaisseOnline()"><?php _e('Attention, nous détectons que vous avez la synchronisation avec la caisse activée mais il vous manque le plugin correspondant pour s\'y connecter. Veuillez nous contacter via le support de RESA Online !', 'resa' ); ?></div>
        <div class="warning" ng-if="backendCtrl.isFirstParameterNotDone()">
          <?php _e('Veuiller procéder à la première installation et aux réglages de RESA Online : ', 'resa' ); ?><br />
          <a target="_blank" href="<?php echo RESA_Variables::getLinkParameters('installation'); ?>" class="button button-primary">Aller à la première installation</a>
        </div>
      </div>
      <div class="resa_views">
        <div class="resa_filters_left">
          <div class="filter_type" ng-if="backendCtrl.settings.places!=null && backendCtrl.settings.places.length > 0">
            <h3 class="filter_type_title close"><div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(quotationsListCtrl.filters.places, backendCtrl.allFiltersIsOff(quotationsListCtrl.filters.places)); quotationsListCtrl.generateFilteredBookings(); quotationsListCtrl.setFilterSettings()" class="tgl tgl-light" id="quotation_type1filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(quotationsListCtrl.filters.places)" />
              <label class="tgl-btn" for="quotation_type1filter1" title="tous / aucun"></label>
            </div><span class="helpbox">Par lieu<span class="helpbox_content">Veuillez recharger les données pour appliquer le filtre sur le lieu</span></span></h3>
            <div class="filter_type_filters">
              <div class="filter" ng-repeat="place in backendCtrl.settings.places track by $index">
                <label for="quotation_lfilter_places{{$index}}" class="filter_name">{{ quotationsListCtrl.getTextByLocale(place.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings(); quotationsListCtrl.setFilterSettings();" class="tgl tgl-light" id="quotation_lfilter_places{{$index}}" type="checkbox" ng-model="quotationsListCtrl.filters.places[place.id]" />
                  <label class="tgl-btn" for="quotation_lfilter_places{{$index}}"></label>
                </div>
              </div>
              <button ng-click="quotationsListCtrl.launchGetQuotations()" class="btn btn_vert">Charger</button>
            </div>
          </div>
          <div class="filter_type">
            <h3 class="filter_type_title close"><div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(quotationsListCtrl.filters.states, backendCtrl.allFiltersIsOff(quotationsListCtrl.filters.states)); quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_type2filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(quotationsListCtrl.filters.states)" />
              <label class="tgl-btn" for="quotation_type2filter1" title="tous / aucun"></label>
            </div>Par état</h3>
            <div class="filter_type_filters">
              <div class="filter">
                <label for="quotation_rfilter_state_quote" class="filter_name">Demande de devis</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_rfilter_state_quote" type="checkbox" ng-model="quotationsListCtrl.filters.states['quotation_waiting']" />
                  <label class="tgl-btn" for="quotation_rfilter_state_quote"></label>
                </div>
              </div>
              <div class="filter">
                <label for="quotation_rfilter_state_quote_customer" class="filter_name">Devis en attente</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_rfilter_state_quote_customer" type="checkbox" ng-model="quotationsListCtrl.filters.states['quotation_waiting_customer']" />
                  <label class="tgl-btn" for="quotation_rfilter_state_quote_customer"></label>
                </div>
              </div>
              <div class="filter">
                <label for="quotation_rfilter_state_quote_customer_expired" class="filter_name">Devis en attente(expiré)</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_rfilter_state_quote_customer_expired" type="checkbox" ng-model="quotationsListCtrl.filters.states['quotation_waiting_customer_expired']" />
                  <label class="tgl-btn" for="quotation_rfilter_state_quote_customer_expired"></label>
                </div>
              </div>
              <br />
              <div class="filter" ng-if="state.isFilter" ng-repeat="state in backendCtrl.settings.statesList.slice(0,2)">
                <label for="quotation_rfilter_state{{ $index }}" class="filter_name">{{ state.filterName }}</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_rfilter_state{{ $index }}" type="checkbox" ng-model="quotationsListCtrl.filters.states[state.id]" />
                  <label class="tgl-btn" for="quotation_rfilter_state{{ $index }}"></label>
                </div>
              </div>
              <div class="filter">
                <label for="quotation_rfilter_state_waiting_payment" class="filter_name">Attente paiement</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_rfilter_state_waiting_payment" type="checkbox" ng-model="quotationsListCtrl.filters.states['waiting_payment']" />
                  <label class="tgl-btn" for="quotation_rfilter_state_waiting_payment"></label>
                </div>
              </div>
              <div class="filter">
                <label for="rfilter_state_waiting_expired" class="filter_name">Paiement expiré</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_rfilter_state_waiting_expired" type="checkbox" ng-model="quotationsListCtrl.filters.states['waiting_expired']" />
                  <label class="tgl-btn" for="quotation_rfilter_state_waiting_expired"></label>
                </div>
              </div>
              <div class="filter" ng-if="state.isFilter" ng-repeat="state in backendCtrl.settings.statesList.slice(2,backendCtrl.settings.statesList.length)">
                <label for="rfilter_state{{ $index + 3 }}" class="filter_name">{{ state.filterName }}</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_rfilter_state{{ $index + 3 }}" type="checkbox" ng-model="quotationsListCtrl.filters.states[state.id]" />
                  <label class="tgl-btn" for="quotation_rfilter_state{{ $index + 3 }}"></label>
                </div>
              </div>
            </div>
          </div>
          <div class="filter_type">
            <h3 class="filter_type_title close"> <div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(quotationsListCtrl.filters.services, backendCtrl.allFiltersIsOff(backendCtrl.filters.services)); backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_type5filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(quotationsListCtrl.filters.services)" />
              <label class="tgl-btn" for="quotation_type5filter1" title="tous / aucun"></label>
            </div>Par service</h3>
            <div class="filter_type_filters">
              <div class="filter" ng-repeat="service in backendCtrl.getServicesByNoPlace()">
                <label for="quotation_filter_service_noplace_{{ service.id }}" class="filter_name">{{ quotationsListCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_filter_service_noplace_{{ service.id }}" type="checkbox" ng-model="quotationsListCtrl.filters.services[service.id]" />
                  <label class="tgl-btn" for="quotation_filter_service_noplace_{{ service.id }}"></label>
                </div>
              </div>
            </div>
            <div class="filter_type_filters">
              <div class="filter" ng-if="backendCtrl.noHaveCategory(service) && backendCtrl.trueIfPlacesNotFiltered(service,quotationsListCtrl.filters)" ng-repeat="service in backendCtrl.getServicesByPlace()">
                <label for="quotation_filter_service_nocategory_{{ service.id }}" class="filter_name">{{ backendCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_filter_service_nocategory_{{ service.id }}" type="checkbox" ng-model="quotationsListCtrl.filters.services[service.id]" />
                  <label class="tgl-btn" for="quotation_filter_service_nocategory_{{ service.id }}"></label>
                </div>
              </div>
            </div>
            <div class="filter_type_filters" ng-if="backendCtrl.numberOfServices(category) > 0" ng-repeat="category in backendCtrl.getCategories()">
              <h4 class="filter_category"> <div class="filter_input">
              </div>{{ backendCtrl.getTextByLocale(category.label, '<?php echo get_locale(); ?>') }}</h4>
              <div class="filter" ng-if="backendCtrl.trueIfPlacesNotFiltered(service, quotationsListCtrl.filters)" ng-repeat="service in backendCtrl.getServicesByCategory(category)">
                <label class="filter_name" for="quotation_filter_service{{ service.id }}">{{ quotationsListCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_filter_service{{ service.id }}" type="checkbox" ng-model="quotationsListCtrl.filters.services[service.id]" />
                  <label class="tgl-btn" for="quotation_filter_service{{ service.id }}"></label>
                </div>
              </div>
            </div>
          </div>
          <div class="filter_type" ng-if="backendCtrl.settings.staffManagement && backendCtrl.members.length > 0">
            <h3 class="filter_type_title close"> <div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(quotationsListCtrl.filters.members, backendCtrl.allFiltersIsOff(quotationsListCtrl.filters.members)); quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_type6filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(quotationsListCtrl.filters.members)" />
              <label class="tgl-btn" for="quotation_type6filter1" title="tous / aucun"></label>
            </div>Par {{ quotationsListCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h3>
            <div class="filter_type_filters">
              <div class="filter" ng-if="backendCtrl.trueIfPlacesNotFiltered(member,quotationsListCtrl.filters)" ng-repeat="member in backendCtrl.members">
                <span class="filter_name">{{ member.nickname|htmlSpecialDecode }}</span>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_stafffilter{{ $index }}" type="checkbox" ng-model="quotationsListCtrl.filters.members[member.id]"/>
                  <label class="tgl-btn" for="quotation_stafffilter{{ $index }}"></label>
                </div>
              </div>
            </div>
          </div>
          <div class="filter_type">
            <h3 class="filter_type_title close"><div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(quotationsListCtrl.filters.tags, backendCtrl.allFiltersIsOff(quotationsListCtrl.filters.tags)); quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_type1filter15" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(quotationsListCtrl.filters.tags)" />
              <label class="tgl-btn" for="quotation_type1filter15" title="tous / aucun"></label>
            </div>Par tag</h3>
            <div class="filter_type_filters">
              <div class="filter">
                <label for="quotation_lfilter_no_tag" class="filter_name"><?php _e('No_tag_words','resa'); ?></label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_lfilter_no_tag" type="checkbox" ng-model="quotationsListCtrl.filters.tags['no_tag']" />
                  <label class="tgl-btn" for="quotation_lfilter_no_tag"></label>
                </div>
              </div>
              <div class="filter" ng-repeat="tag in backendCtrl.settings.appointmentTags">
                <label for="quotation_lfilter_tags{{$index}}" class="filter_name">{{ quotationsListCtrl.getTextByLocale(tag.title, '<?php echo get_locale(); ?>') }}</label>
                <div class="filter_input">
                  <input ng-change="quotationsListCtrl.generateFilteredBookings()" class="tgl tgl-light" id="quotation_lfilter_tags{{$index}}" type="checkbox" ng-model="quotationsListCtrl.filters.tags[tag.id]" />
                  <label class="tgl-btn" for="quotation_lfilter_tags{{$index}}"></label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="resa_filters_top">
          <div class="filter_type filters_date">
            <select ng-change="quotationsListCtrl.generateFilteredBookings();" ng-model="quotationsListCtrl.filters.seeBookingsValue">
              <option value="1" ng-selected="backendCtrl.filters.seeBookingsValue == '1'">Tous</option>
              <option value="2" ng-selected="backendCtrl.filters.seeBookingsValue == '2'">Passés</option>
              <option value="3" ng-selected="backendCtrl.filters.seeBookingsValue == '3'">A venir</option>
            </select>
            <span class="btn btn_vert title_box dashicons dashicons-image-rotate" ng-click="quotationsListCtrl.launchGetQuotations()">
              <span class="title_box_content">Charger les devis</span>
            </span>
          </div>
          <div class="filter_type filters_name">
            <input type="search" ng-change="quotationsListCtrl.generateFilteredBookings()" ng-model-options="{ debounce: 500 }" ng-model="quotationsListCtrl.filters.search" placeholder="Id, nom, prénom, téléphone, entreprise, email..." />
          </div>
          <!--
          <div class="filter_type light_mode">
            <span class="light_mode_title" ng-click="backendCtrl.seeBookingDetails = !backendCtrl.seeBookingDetails">Détails</span>
            <div class="filter_input light_mode_input">
              <input class="tgl tgl-light" id="light_mode" type="checkbox" ng-model="backendCtrl.seeBookingDetails" />
              <label class="tgl-btn" for="light_mode" title="tous / aucun"></label>
            </div>
          </div>
          //-->
          <div class="filter_type filters_autres">
            <btn class="btn" ng-click="quotationsListCtrl.reinitFilters()"><?php _e('reinitialize_link_title', 'resa'); ?></btn>
            <btn class="btn" ng-click="backendCtrl.printDiv('printQuotations')">Imprimer</btn>
          </div>
        </div>
        <div class="resa_view_content">
          <div class="resa_view_reservations">
            <div class="resa_pagination" ng-if="quotationsListCtrl.filteredBookings.length > 0">
              <div class="pagination_left">
                <ul uib-pagination ng-change="quotationsListCtrl.applyPaginationBookings()" total-items="quotationsListCtrl.filteredBookings.length" items-per-page="quotationsListCtrl.paginationBookings.step" ng-model="quotationsListCtrl.paginationBookings.number" max-size="5" class="pagination-sm pagination" boundary-link-numbers="true" rotate="true" previous-text="<?php _e('previous_link_title', 'resa') ?>" next-text="<?php _e('next_link_title', 'resa') ?>"></ul>
              </div>
              <div class="pagination_right">
                <select ng-change="quotationsListCtrl.applyPaginationBookings()" ng-model="quotationsListCtrl.paginationBookings.step">
    							<option ng-value="10" ng-selected="quotationsListCtrl.paginationBookings.step == 10">10</option>
                  <option ng-value="20">20</option>
                  <option ng-value="30">30</option>
                  <option ng-value="40">40</option>
                  <option ng-value="50">50</option>
    						</select>
    						<?php _e('by_page_words', 'resa'); ?>
              </div>
            </div>
            <div class="resa_list" id="printQuotations">
              <div class="resa_reservation {{ booking.cssPaymentState }}" ng-repeat="booking in quotationsListCtrl.displayedBookings" ng-class="{'light': !backendCtrl.seeBookingDetails,'quote': booking.quotation, 'valid': !booking.quotation && booking.status=='ok', 'pending': !booking.quotation && booking.status=='waiting', 'cancelled': !booking.quotation && booking.status=='cancelled', 'abandonned': !booking.quotation && booking.status=='abandonned', 'inerror': false, 'incomplete': false}">
                <div class="reservation_client">
                  <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_customer']){ ?>
                    <div class="resa_id">N°{{backendCtrl.getBookingId(booking)}}</div>
                    <div class="client_name">
                      <span ng-if="booking.customer.lastName.length > 0" class="client_lastname">{{ booking.customer.lastName | htmlSpecialDecode }}</span>
                      <span ng-if="booking.customer.firstName.length > 0" class="client_firstname">{{booking.customer.firstName | htmlSpecialDecode }}</span>
                    </div>
                    <div class="client_company">
                      <span ng-if="booking.customer.company.length > 0">{{ booking.customer.company | htmlSpecialDecode }}</span>
                      <span ng-if="booking.customer.phone.length > 0"><span ng-if="booking.customer.company.length > 0"><br /></span>{{ booking.customer.phone | formatPhone }}</span>
                      <span ng-if="booking.customer.phone2.length > 0"><span ng-if="booking.customer.company.length > 0 || booking.customer.phone.length > 0"><br /></span>{{ booking.customer.phone2 | formatPhone }}</span>
                    </div>
                    <div ng-if="booking.customer.privateNotes.length > 0" class="client_note on b1 helpbox">
                      1 Note Client
                      <span class="helpbox_content"><h4>Note privé client :</h4><p ng-bind-html="booking.customer.privateNotes|htmlSpecialDecode:true"></p></span>
                    </div>
                  <?php } ?>
                </div>
                <div class="reservation_states">
                  <div class="states_state state_valid">Validée</div>
                  <div class="states_state state_quote">{{ backendCtrl.getQuotationName(booking) }}</div>
                  <div class="states_state state_cancelled">Annulée</div>
                  <div class="states_state state_abandonned">Abandonnée</div>
                  <div class="states_state state_pending">{{ backendCtrl.getWaitingName(booking) }}</div>
                  <div class="states_state state_inerror">Erreur</div>
                  <div class="states_state state_incomplete">Incomplète</div>
                  <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_payments']){ ?>
                    <div class="states_paiement" ng-class="{'helpbox':booking.payments.length > 0}">
                      <div class="state_paiement paiement_none">Pas de paiement</div>
                      <div class="state_paiement paiement_incomplete">
                        <span ng-if="!backendCtrl.isDeposit(booking)">Acompte</span>
                        <span ng-if="backendCtrl.isDeposit(booking)">Caution</span>
                      </div>
                      <div class="state_paiement paiement_done">Encaissée</div>
                      <div class="state_paiement paiement_overpaiement">Trop perçu</div>
                      <div class="state_paiement paiement_remboursement">Remboursement dû</div>
                      <div class="state_paiement paiement_remboursement_done">Remboursement complet</div>
                      <div class="helpbox_content" ng-if="booking.payments.length > 0">
  											<table class="table">
  												<thead>
  													<tr>
  														<th>Date</th>
  														<th>Montant</th>
  														<th>Type de paiement</th>
  														<th>Note</th>
  													</tr>
  												</thead>
  												<tbody>
  													<tr ng-if="payment.state != 'pending'" ng-repeat="payment in booking.payments">
  														<td>{{ payment.paymentDate | formatDateTime:'<?php echo $variables['date_format'].' '.$variables['time_format']; ?>' }}
  							              <span class="ro-cancelled" ng-if="payment.state=='cancelled'"><br /><?php _e('cancelled_word', 'resa'); ?></span></td>
  														<td ng-if="payment.repayment">{{ payment.value|negative }}{{ backendCtrl.settings.currency }}</td>
  														<td ng-if="!payment.repayment">{{ payment.value }}{{ backendCtrl.settings.currency }}</td>
  														<td>
                                <span ng-if="payment.isReceipt != null && payment.isReceipt && payment.value >= 0">[Acompte]</span>
                                <span ng-if="payment.isReceipt != null && payment.isReceipt && payment.value < 0">[Remboursement acompte]</span>
                                {{ backendCtrl.getPaymentName(payment.type, payment.name) }}
                              </td>
  														<td class="ng-binding">{{ payment.note }}</td>
  													</tr>
  												</tbody>
  											</table>
  										</div>
                    </div>
                    <div class="states_paiement_value">
                      <span ng-if="booking.status != 'cancelled' && booking.status != 'abandonned'">
                        <span ng-if="booking.paymentsCaisseOnlineDone || (!backendCtrl.isCaisseOnlineActivated() && backendCtrl.round(booking.totalPrice - booking.needToPay) > 0)">{{ backendCtrl.round(booking.totalPrice - booking.needToPay) }}{{ backendCtrl.settings.currency }} / </span>
                        {{ booking.totalPrice }}{{ backendCtrl.settings.currency }}
                      </span>
                      <span ng-if="(booking.status == 'cancelled' || booking.status == 'abandonned') && booking.needToPay < 0 && (booking.paymentsCaisseOnlineDone || !backendCtrl.isCaisseOnlineActivated())">
                        {{ -(booking.needToPay - booking.totalPrice) }}{{ backendCtrl.settings.currency }}
                      </span>
                      <a ng-if="backendCtrl.isCaisseOnlineActivated()" ng-click="backendCtrl.getPaymentsForBooking(booking)">Paiements</a>
                    </div>
                  <?php } ?>
                </div>
                <div class="reservation_notes">
                  <?php if(!RESA_Variables::staffIsConnected()){ ?>
                  <div class="reservation_private_note" ng-class="{'on helpbox': booking.note.length > 0, 'off': booking.note.length == 0}">
                    Note privée
                    <span class="helpbox_content" ng-if="booking.note.length > 0"><h4>Note privée :</h4><p ng-bind-html="booking.note|htmlSpecialDecode:true"></p></span>
                  </div>
                  <div class="reservation_public_note" ng-class="{'on helpbox': booking.publicNote.length > 0, 'off': booking.publicNote.length == 0}">
                    Note publique
                    <span class="helpbox_content" ng-if="booking.publicNote.length > 0"><h4>Note publique :</h4><p ng-bind-html="booking.publicNote|htmlSpecialDecode:true"></p></span>
                  </div>
                  <div class="reservation_customer_note" ng-class="{'on helpbox': booking.customerNote.length > 0, 'off': booking.customerNote.length == 0}">
                    Remarque cliente
                    <span class="helpbox_content" ng-if="booking.customerNote.length > 0"><h4>Remarque cliente :</h4><p ng-bind-html="booking.customerNote|htmlSpecialDecode:true"></p></span>
                  </div>
                  <?php } ?>
                  <div ng-if="backendCtrl.settings.staffManagement" class="reservation_member_note" ng-class="{'on helpbox': booking.staffNote.length > 0, 'off': booking.staffNote.length == 0}">
                    Note {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                    <span class="helpbox_content" ng-if="booking.staffNote.length > 0"><h4>Note des {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} :</h4><p ng-bind-html="booking.staffNote|htmlSpecialDecode:true"></p></span>
                  </div>
                </div>
                <div class="reservation_suivi">
                  <div class="suivi_title">
                    <div class="title_date">Créée le </div>
                    <div class="title_time">à</div>
                    <div class="title_user"><?php _e('by_word', 'resa') ?></div>
                  </div>
                  <div class="suivi_content">
                    <div class="content_date">{{ booking.creationDate|formatDateTime:'<?php echo $variables['date_format']; ?>' }}</div>
                    <div class="content_time">{{ booking.creationDate|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</div>
                    <div class="content_user">{{ booking.userCreator }}</div>
                  </div>
                </div>
                <div class="reservation_actions no-print">
                  <?php if(!RESA_Variables::staffIsConnected()){ ?>
                    <span class="btn btn_modifier title_box dashicons dashicons-edit"  ng-click="backendCtrl.openEditBooking(quotationsListCtrl.getBookingById(booking.id))">
                      <span ng-if="!booking.quotation" class="title_box_content">Modifier la réservation</span>
                      <span ng-if="booking.quotation" class="title_box_content">Modifier le devis</span>
                    </span>

                    <span class="btn btn_notify title_box dashicons dashicons-email-alt" ng-click="backendCtrl.openNotificationDialog(quotationsListCtrl.getCustomerById(booking.idCustomer), quotationsListCtrl.getBookingById(booking.id))">
                      <span class="title_box_content">Envoyer une notification</span>
                    </span>

                    <span class="btn btn_client title_box dashicons dashicons-admin-users" ng-click="backendCtrl.openCustomerDialog(booking.idCustomer)">
                      <span class="title_box_content">Voir la fiche client</span>
                    </span>

                    <span class="btn btn_reciept title_box dashicons dashicons-format-aside" ng-click="backendCtrl.openReceiptBookingDialog(quotationsListCtrl.getBookingById(booking.id), quotationsListCtrl.getCustomerById(booking.idCustomer))">
                      <span ng-if="!booking.quotation" class="title_box_content">Reçu de la réservation</span>
                      <span ng-if="booking.quotation" class="title_box_content">Reçu du devis</span>
                    </span>
                  <?php } ?>
                </div>
                <div class="reservation_bookings">
                  <div class="booking" ng-class="{'confirmed':appointment.state == 'ok','not_confirmed':appointment.state == 'waiting','cancelled':appointment.state == 'cancelled' || appointment.state == 'abandonned'}" ng-repeat="appointment in booking.appointments|orderBy:'startDate'">
                    <div class="booking_date">{{ appointment.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }}</div>
                    <div class="booking_hour">
                      <span ng-if="!appointment.noEnd">
      									{{ appointment.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      									<?php _e('to_word', 'resa') ?>
      									{{ appointment.endDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      								</span>
      								<span ng-if="appointment.noEnd">
      									<?php _e('begin_word', 'resa'); ?> {{ appointment.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
      								</span>
                    </div>
                    <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_numbers']){ ?>
                    <div class="booking_nb_people" ng-class="{'helpbox': backendCtrl.getServiceById(appointment.idService).askParticipants}">
                      {{ appointment.numbers }} pers.
                      <span ng-if="backendCtrl.getServiceById(appointment.idService).askParticipants" class="helpbox_content"><h4>Participants</h4>
                        <span ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                          <table>
                            <thead>
                              <tr>
                                <td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">
                                  {{ backendCtrl.getTextByLocale(field.name,'<?php echo get_locale(); ?>') }}
                                </td>
                              </tr>
                            </thead>
                            <tbody>
                              <tr ng-repeat="participant in appointmentNumberPrice.participants track by $index"><td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields">{{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getParticipantFieldName(participant, field,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</td></tr>
                            </tbody>
                            <tfoot  ng-if="backendCtrl.isDisplaySum(backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields)">
                              <tr>
                                <td ng-repeat="field in backendCtrl.getParticipantsParameter(backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService),appointmentNumberPrice.idPrice).participantsParameter).fields"><span ng-if="field.displaySum">Somme : {{ backendCtrl.getTextByLocale(field.prefix,'<?php echo get_locale(); ?>') }}{{ backendCtrl.getDisplaySum(field, appointmentNumberPrice.participants) }}{{ backendCtrl.getTextByLocale(field.suffix,'<?php echo get_locale(); ?>') }}</span></td>
                              </tr>
                            </tfoot>
                          </table>
                        </span>
                      </span>
                    </div>
                    <?php } ?>
                    <div class="booking_states">
                      <div class="states_state state_confirmed">Confirmée</div>
                      <div class="states_state state_not_confirmed">Non confirmée</div>
                      <div class="states_state state_cancelled">Annulée</div>
                      <div class="states_state state_inerror">Erreur</div>
                      <div class="states_state state_incomplete">Incomplète</div>
                    </div>
                    <div class="booking_place"><span ng-if="appointment.idPlace != ''">{{ backendCtrl.getTextByLocale(backendCtrl.getPlaceById(appointment.idPlace).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span></div>
                    <div class="booking_service">{{ backendCtrl.getTextByLocale(
      										backendCtrl.getServiceById(appointment.idService).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                    <div class="booking_prices">
                      <div class="prices_price" ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                        <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_numbers']){ ?>
                        <span class="price_nb">{{ appointmentNumberPrice.number }}</span>
                        <?php } ?>
                        <span class="price_name">
                          {{ backendCtrl.getServicePriceAppointmentName(appointment.idService, appointmentNumberPrice.idPrice) |htmlSpecialDecode
        									}}
                          <span ng-if="backendCtrl.haveEquipments(appointment.idService, appointmentNumberPrice.idPrice)">
                            ({{ backendCtrl.getServicePriceAppointmentEquipmentName(appointment.idService, appointmentNumberPrice.idPrice)|htmlSpecialDecode
          									}})
                          </span>
                        </span>
                        <?php if(!RESA_Variables::staffIsConnected() || $variables['settings']['staff_display_payments']){ ?>
                          <span class="price_value" ng-if="!appointmentNumberPrice.deactivated">
                            <span ng-if="appointmentNumberPrice.totalPrice != 0">{{ appointmentNumberPrice.totalPrice }}  {{ backendCtrl.settings.currency }}</span>
                            <span ng-if="appointmentNumberPrice.totalPrice == 0">
                              <span ng-if="backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
                                {{ backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).price }} {{ backendCtrl.settings.currency }}
                              </span>
                              <span ng-if="!backendCtrl.getServicePriceAppointment(backendCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).notThresholded">
                                {{ backendCtrl.getPriceNumberPrice(appointment.idService, appointmentNumberPrice) }} {{ backendCtrl.settings.currency }}
                              </span>
                            </span>
                          </span>
                        <?php } ?>
                      </div>
                    </div>
                    <div class="booking_members" ng-if="backendCtrl.settings.staffManagement">
                      <div class="member" ng-if="appointmentMember.number > 0" ng-repeat="appointmentMember in appointment.appointmentMembers">{{ backendCtrl.getMemberById(appointmentMember.idMember).nickname|htmlSpecialDecode }}<br /></div>
                    </div>
                    <div class="booking_tags">
                      <div class="tag {{ backendCtrl.getTagById(idTag).color }}" ng-repeat="idTag in appointment.tags">
                        {{ backendCtrl.getTextByLocale(backendCtrl.getTagById(idTag).title, '<?php echo get_locale(); ?>') }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        </div>
        <div class="loader_box" ng-show="backendCtrl.loadingData || backendCtrl.actionDropProcess || backendCtrl.launchAddNewCustomer || quotationsListCtrl.getQuotationsLaunched">
          <div class="loader_content">
            <div class="loader">
              <div class="shadow"></div>
              <div class="box"></div>
              <a ng-if="quotationsListCtrl.getQuotationsLaunched" class="link" ng-click="quotationsListCtrl.stopGetQuotations()">Annuler</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="resa_planning" ng-if="backendCtrl.settings.groupsManagement" ng-show="backendCtrl.currentPage == 'planning'">
      <div class="resa_actions">
        <?php if(!RESA_Variables::staffIsConnected()){ ?>
        <a class="resa_btn page-title-action plus_ico btn_add_resa" ng-click="backendCtrl.openEditBooking()">Réservation</a>
        <a class="resa_btn page-title-action plus_ico" ng-click="backendCtrl.setCurrentPage('clients')">Client</a>
        <a class="resa_btn page-title-action plus_ico" ng-click="backendCtrl.openEditInfoCalendarDialog()">Information</a>
        <a class="resa_btn page-title-action plus_ico" ng-click="backendCtrl.openEditServiceConstraintDialog()">Contrainte</a>
        <?php } ?>
        <div style="color:red; font-weight:bold;" ng-if="backendCtrl.isCaisseOnlineActivatedButNoCaisseOnline()"><?php _e('Attention, nous détectons que vous avez la synchronisation avec la caisse activée mais il vous manque le plugin correspondant pour s\'y connecter. Veuillez nous contacter via le support de RESA Online !', 'resa' ); ?></div>
        <div class="warning" ng-if="backendCtrl.isFirstParameterNotDone()">
          <?php _e('Veuiller procéder à la première installation et aux réglages de RESA Online : ', 'resa' ); ?><br />
          <a target="_blank" href="<?php echo RESA_Variables::getLinkParameters('installation'); ?>" class="button button-primary">Aller à la première installation</a>
        </div>
      </div>
      <div class="resa_views_menu">
        <h2 class="nav-tab-wrapper">
          <a class="nav-tab cal_tab" ng-class="{'nav-tab-active':backendCtrl.viewMode === 'members'}" ng-click="backendCtrl.setViewMode('members');">Planning {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</a>
          <?php if(!RESA_Variables::staffIsConnected()){ ?>
            <a class="nav-tab group_tab" ng-class="{'nav-tab-active':backendCtrl.viewMode === 'rapports'}" ng-click="backendCtrl.setViewMode('rapports');">Gérer les groupes</a>
          <?php } ?>
        </h2>
      </div>
      <div class="resa_views">
        <div class="resa_filters_left">
          <div class="filter_type" ng-if="backendCtrl.settings.places!=null && backendCtrl.settings.places.length > 0">
            <h3 class="filter_type_title close"><div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(backendCtrl.filters.places, backendCtrl.allFiltersIsOff(backendCtrl.filters.places)); backendCtrl.generateFilteredBookings(); backendCtrl.setFilterSettings();" class="tgl tgl-light" id="type1filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(backendCtrl.filters.places)" />
              <label class="tgl-btn" for="type1filter1" title="tous / aucun"></label>
            </div><span class="helpbox">Par lieu<span class="helpbox_content">Veuillez recharger les données pour appliquer le filtre sur le lieu</span></span></h3>
            <div class="filter_type_filters">
              <div class="filter" ng-repeat="place in backendCtrl.settings.places track by $index">
                <label for="lfilter_places{{$index}}" class="filter_name">{{ backendCtrl.getTextByLocale(place.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings(); backendCtrl.setFilterSettings();" class="tgl tgl-light" id="lfilter_places{{$index}}" type="checkbox" ng-model="backendCtrl.filters.places[place.id]" />
                  <label class="tgl-btn" for="lfilter_places{{$index}}"></label>
                </div>
              </div>
              <button ng-click="backendCtrl.launchBackgroundAppointmentsLoading()" class="btn btn_vert">Charger</button>
            </div>
          </div>
          <div class="filter_type">
            <h3 class="filter_type_title close"> <div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(backendCtrl.filters.services, backendCtrl.allFiltersIsOff(backendCtrl.filters.services)); backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="type5filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(backendCtrl.filters.services)" />
              <label class="tgl-btn" for="type5filter1" title="tous / aucun"></label>
            </div>Par service</h3>
            <div class="filter_type_filters">
              <div class="filter" ng-repeat="service in backendCtrl.getServicesByNoPlace()">
                <label for="filter_service_noplace_{{ service.id }}" class="filter_name">{{ backendCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="filter_service_noplace_{{ service.id }}" type="checkbox" ng-model="backendCtrl.filters.services[service.id]" />
                  <label class="tgl-btn" for="filter_service_noplace_{{ service.id }}"></label>
                </div>
              </div>
            </div>
            <div class="filter_type_filters">
              <div class="filter" ng-if="backendCtrl.noHaveCategory(service) && backendCtrl.trueIfPlacesNotFiltered(service,backendCtrl.filters)" ng-repeat="service in backendCtrl.getServicesByPlace()">
                <label for="filter_service_nocategory_{{ service.id }}" class="filter_name">{{ backendCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="filter_service_nocategory_{{ service.id }}" type="checkbox" ng-model="backendCtrl.filters.services[service.id]" />
                  <label class="tgl-btn" for="filter_service_nocategory_{{ service.id }}"></label>
                </div>
              </div>
            </div>
            <div class="filter_type_filters" ng-if="backendCtrl.numberOfServices(category) > 0" ng-repeat="category in backendCtrl.getCategories()">
              <h4 class="filter_category"> <div class="filter_input">
              </div>{{ backendCtrl.getTextByLocale(category.label, '<?php echo get_locale(); ?>') }}</h4>
              <div class="filter" ng-if="backendCtrl.trueIfPlacesNotFiltered(service,backendCtrl.filters)" ng-repeat="service in backendCtrl.getServicesByCategory(category)">
                <label class="filter_name" for="filter_service{{ service.id }}">{{ backendCtrl.getTextByLocale(service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</label>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="filter_service{{ service.id }}" type="checkbox" ng-model="backendCtrl.filters.services[service.id]" />
                  <label class="tgl-btn" for="filter_service{{ service.id }}"></label>
                </div>
              </div>
            </div>
          </div>
          <div class="filter_type" ng-if="backendCtrl.settings.staffManagement && backendCtrl.members.length > 0">
            <h3 class="filter_type_title close"> <div class="filter_input">
              <input ng-click="backendCtrl.setAllValueFilters(backendCtrl.filters.members, backendCtrl.allFiltersIsOff(backendCtrl.filters.members)); backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="type6filter1" type="checkbox" ng-checked="!backendCtrl.allFiltersIsOff(backendCtrl.filters.members)" />
              <label class="tgl-btn" for="type6filter1" title="tous / aucun"></label>
            </div>Par {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_single, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</h3>
            <div class="filter_type_filters">
              <div class="filter" ng-if="backendCtrl.trueIfPlacesNotFiltered(member,backendCtrl.filters) && backendCtrl.displayMemberFilter(member)" ng-repeat="member in backendCtrl.members">
                <span class="filter_name">{{ member.nickname|htmlSpecialDecode }}</span>
                <div class="filter_input">
                  <input ng-change="backendCtrl.generateFilteredBookings()" class="tgl tgl-light" id="stafffilter{{ $index }}" type="checkbox" ng-model="backendCtrl.filters.members[member.id]"/>
                  <label class="tgl-btn" for="stafffilter{{ $index }}"></label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="resa_filters_top">
          <div class="filter_type filters_date">
            <select ng-change="backendCtrl.changeDates();" ng-model="backendCtrl.filters.seeBookingsValue">
              <option value="1" ng-selected="backendCtrl.filters.seeBookingsValue == 1"><?php _e('today_word', 'resa') ?> - {{ backendCtrl.today() | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</option>
              <option value="2" ng-selected="backendCtrl.filters.seeBookingsValue == 2"><?php _e('tomorrow_word', 'resa') ?> - {{ backendCtrl.tomorrow() | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</option>
              <option value="3" ng-selected="backendCtrl.filters.seeBookingsValue == 3"><?php _e('in_seven_days_words', 'resa') ?></option>
              <option value="4" ng-selected="backendCtrl.filters.seeBookingsValue == 4"><?php _e('yesterday_word','resa') ?> - {{ backendCtrl.yesterday() | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</option>
              <option value="5" ng-selected="backendCtrl.filters.seeBookingsValue == 5">Date personnalisée</option>
              <option value="7" ng-selected="backendCtrl.filters.seeBookingsValue == 7">Mois</option>
              <option value="6" ng-selected="backendCtrl.filters.seeBookingsValue == 6">Période personnalisée</option>
            </select>
            <span ng-if="backendCtrl.filters.seeBookingsValue == 5 || backendCtrl.filters.seeBookingsValue == 6">
              <input <?php /* uib-datepicker-popup */ ?> ng-click="backendCtrl.popupDate1=true" is-open="backendCtrl.popupDate1" datepicker-options="backendCtrl.dateOptions" type="date" class="form-control" placeholder="0"  ng-change="backendCtrl.setCustomDate(backendCtrl.filters.dates.startDate)" ng-model="backendCtrl.filters.dates.startDate" close-text="<?php _e('Close_word', 'resa') ?>" clear-text="<?php _e('Clear_word', 'resa') ?>" current-text="<?php _e('Today_word', 'resa') ?>">
            </span>
            <span ng-if="backendCtrl.filters.seeBookingsValue == 6"><br />
              <input <?php /* uib-datepicker-popup */ ?> ng-click="backendCtrl.popupDate2=true" is-open="backendCtrl.popupDate2" datepicker-options="backendCtrl.dateOptions" type="date" class="form-control" placeholder="0"  ng-model="backendCtrl.filters.dates.endDate" close-text="<?php _e('Close_word', 'resa') ?>" clear-text="<?php _e('Clear_word', 'resa') ?>" current-text="<?php _e('Today_word', 'resa') ?>">
            </span>
            <span ng-if="backendCtrl.filters.seeBookingsValue == 7">
              <select ng-change="backendCtrl.changeCurrentMonthDate()" ng-model="backendCtrl.filters.month" ng-options="month as (month.date|date:'MMMM - yyyy') for month in backendCtrl.filtersMonths"></select>
            </span>
            <span class="btn btn_vert title_box dashicons dashicons-image-rotate" ng-click="backendCtrl.launchBackgroundAppointmentsLoading()">
              <span class="title_box_content">Charger la période</span>
            </span>
          </div>
          <div class="filter_type filters_autres">
            <btn class="btn" ng-click="backendCtrl.reinitFilters()"><?php _e('reinitialize_link_title', 'resa'); ?></btn>
          </div>
    		   <div class="filter_type filters_date_planning">
      			<div ng-if="backendCtrl.datePlanningMembers != null">
      			  <div class="btn-group">
                Date du planning : {{ backendCtrl.datePlanningMembers|formatDate:'EEEE <?php echo $variables['date_format']; ?>' }}
        				<button class="btn btn-primary" ng-click="backendCtrl.decrementDatePlanningMembers();"><?php _e('previous_link_title', 'resa') ?></button>
        				<button class="btn btn-primary" ng-click="backendCtrl.incrementDatePlanningMembers();"><?php _e('next_link_title', 'resa') ?></button>
      			  </div>
      			</div>
            <div ng-if="backendCtrl.datePlanningMembers == null">
              Pas de date sélectionnée - <input type="date" ng-change="backendCtrl.generateRapportsServicesByDateWithNotInGroups()" class="form-control" placeholder="0" ng-model="backendCtrl.datePlanningMembers">
      			</div>
    		  </div>
          <div ng-if="backendCtrl.viewMode === 'members'" class="filter_type filters_date_planning">
            <label><input type="checkbox" ng-model="backendCtrl.displayMembersNotAvailables" /><span class="helpbox">Afficher/Masquer {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} indisponibles <span class="helpbox_content">Permet d'afficher tous les {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} même ceux non prévus au planning (emploi du temps ou contraintes)</span></span></label>
          </div>
        </div>
        <div class="resa_view_content">
          <div class="planning_members" ng-if="backendCtrl.viewMode === 'members'">
            <div ng-if="backendCtrl.datePlanningMembers != null" class="planning_members_timeline">
              <div class="tl_time tl_hour">
                <div class="th_content">9h</div>
              </div>
              <div class="tl_time tl_min">
                <div class="th_content">30</div>
              </div>
              <div class="tl_time tl_hour">
                <div class="th_content">10h</div>
              </div>
              <div class="tl_time tl_min">
                <div class="th_content">30</div>
              </div>
              <div class="tl_time tl_hour">
                <div class="th_content">11h</div>
              </div>
              <div class="tl_time tl_min">
                <div class="th_content">30</div>
              </div>
              <div class="tl_time tl_hour">
                <div class="th_content">12h</div>
              </div>
              <div class="tl_time tl_min">
                <div class="th_content">30</div>
              </div>
              <div class="tl_time tl_hour">
                <div class="th_content">13h</div>
              </div>
              <div class="tl_time tl_min">
                <div class="th_content">30</div>
              </div>
              <div class="tl_time tl_hour">
                <div class="th_content">14h</div>
              </div>
              <div class="tl_time tl_min">
                <div class="th_content">30</div>
              </div>
              <div class="tl_time tl_hour">
                <div class="th_content">15h</div>
              </div>
              <div class="tl_time tl_min">
                <div class="th_content">30</div>
              </div>
              <div class="tl_time tl_hour">
                <div class="th_content">16h</div>
              </div>
              <div class="tl_time tl_min">
                <div class="th_content">30</div>
              </div>
              <div class="tl_time tl_hour">
                <div class="th_content">17h</div>
              </div>
              <div class="tl_time tl_min">
                <div class="th_content">30</div>
              </div>
              <div class="tl_time tl_hour">
                <div class="th_content">18h</div>
              </div>
              <div class="tl_time tl_min">
                <div class="th_content">30</div>
              </div>
            </div>
            <div ng-if="backendCtrl.datePlanningMembers != null" class="planning_members_line">
              <?php if(!RESA_Variables::staffIsConnected()){ ?>
              <div class="member_line" ng-if="backendCtrl.rapportsByServicesWithParticipantsNotAttribuated.length > 0">
                <div class="member_infos">
                  <div class="title_not_attribuated">Personnes non attribuées</div>
                </div>
                <div style="height:auto" class="groups" ng-repeat="rapports in backendCtrl.rapportsByServicesWithParticipantsNotAttribuated">
                  <div class="groups_group planning_infos helpbox" style="height: 70px; width:{{ backendCtrl.getGroupWidth(rapports) }}%; margin-left: {{ backendCtrl.getGroupLeft(rapports) }}%; background-color:#d62c2c;">
                    <div class="planning_infos_content">
                      <span ng-click="backendCtrl.openGroupsManagerDialog(backendCtrl.getRapportByServicesWithFiltered(rapportByServices))" ng-repeat="rapportByServices in rapports.rapportsByServices">
                        <span ng-if="rapportByServices.idPlace!=''">[{{ backendCtrl.getTextByLocale(backendCtrl.getPlaceById(rapportByServices.idPlace).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}] </span>{{ backendCtrl.getTextByLocale(rapportByServices.service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} : {{ backendCtrl.getNumberParticipantsNotInGroups(rapportByServices) }} personne(s)<br />
                      </span>
                      <span class="helpbox_content">
                        <span ng-repeat="rapportByServices in rapports.rapportsByServices">
                          <span ng-if="rapportByServices.idPlace!=''">[{{ backendCtrl.getTextByLocale(backendCtrl.getPlaceById(rapportByServices.idPlace).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}] </span>{{ backendCtrl.getTextByLocale(rapportByServices.service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} : {{ backendCtrl.getNumberParticipantsNotInGroups(rapportByServices) }} personne(s)<br />
                        </span>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <?php } ?>
              <div class="member_line" ng-if="backendCtrl.getInfosCalendarByDate(backendCtrl.datePlanningMembers).length > 0">
                <div class="member_infos">
                  <div class="title_infos">Informations</div>
                </div>
                <div ng-click="backendCtrl.openEditInfoCalendarDialog(info)" class="groups" ng-repeat="info in backendCtrl.getInfosCalendarByDate(backendCtrl.datePlanningMembers)">
                  <div class="groups_group planning_infos helpbox" style="width:{{backendCtrl.getInfoWidth(info)}}%; margin-left: {{ backendCtrl.getInfoLeft(info)}}%;">
                    <div class="planning_infos_content" ng-bind-html="backendCtrl.getInfoCalendarNote(info)"></div>
                    <div class="helpbox_content" ng-bind-html="backendCtrl.getInfoCalendarNote(info)"></div>
                  </div>
                </div>
              </div>
              <div class="member_sep_line">
                <div class="member_sep_infos">
                  <div class="sep_title infos">Groupes attribués</div>
                </div>
              </div>
              <div class="member_line" ng-if="backendCtrl.filters.members[member.id] && backendCtrl.trueIfPlacesNotFiltered(member,backendCtrl.filters) && (backendCtrl.isMemberIsAvailable(member, backendCtrl.datePlanningMembers) || backendCtrl.displayMembersNotAvailables || backendCtrl.getGroupsByIdMembers(member.id).length > 0)" ng-repeat="member in backendCtrl.members">
                <div class="member_infos">
                  <div class="member_firstname">{{ member.firstname }}</div>
                  <div class="member_lastname">{{ member.lastname }}</div>
                  <div class="member_nickname">{{ member.nickname|htmlSpecialDecode }}<span style="color:red;" class="helpbox" ng-if="!backendCtrl.isMemberIsAvailable(member, backendCtrl.datePlanningMembers)">  <br />Non disponible<span class="helpbox_content">Ce {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_single, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} est défini comme indisponible à cette date (emploi du temps ou contraintes) !</span></span></div>
                  <div class="member_options"><input class="btn" type="button" value="Option" /></div>
                </div>
                <div class="groups">
                  <div ng-click="backendCtrl.openEditServiceConstraintDialog(null, memberConstraint);" class="groups_group" style="width:{{backendCtrl.getGroupWidth(memberConstraint)}}%; left: {{ backendCtrl.getGroupLeft(memberConstraint)}}%; background-color:{{ backendCtrl.settings.calendar.service_constraint_color }};" ng-repeat="memberConstraint in backendCtrl.getMemberConstraintsByIdMember(member.id)">
                    <div class="group_name">
                      Contrainte !
                    </div>
                  </div>
                  <div ng-click="backendCtrl.openGroupManagerDialog(backendCtrl.getRapportByServicesByGroup(group), group)" class="groups_group" style="width:{{backendCtrl.getGroupWidth(group)}}%; left: {{ backendCtrl.getGroupLeft(group)}}%; background-color:{{ group.color }};" ng-repeat="group in backendCtrl.getGroupsByIdMembers(member.id)">
                    <div class="group_name">
                      {{ backendCtrl.getGroupNameFunctionOfSize(group) }}<span ng-if="group.oneByBooking"> - <span style="font-size:10px;">{{ backendCtrl.getBookingCustomerWithGroup(group).lastName }}</span></span>
                      <span ng-if="group.oneByBooking" class="helpbox"><span class="helpbox_content">{{ backendCtrl.getDisplayBookingCustomerWithGroup(group) }}</span></span>
                      <span ng-class="{'bg_rouge': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) > group.max, 'bg_gris': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) == 0, 'bg_vert': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) <= group.max}">{{ backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) }} / {{ group.max }}</span>
                    </div>
                  </div>
                </div>
              </div>
      				<div class="member_sep_line">
      					<div class="member_sep_infos">
      						<div class="sep_title">Groupe sans {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
      					</div>
      					<div class="groups"></div>
      				</div>
      				<div class="member_line not_member">
      					<div class="member_infos"></div>
      					<div class="groups">
                  <div ng-click="backendCtrl.openGroupManagerDialog(backendCtrl.getRapportByServicesByGroup(group), group)" class="groups_group" style="width:{{backendCtrl.getGroupWidth(group)}}%; margin-left: {{ backendCtrl.getGroupLeft(group)}}%; background-color:{{ group.color }};" ng-repeat="group in backendCtrl.getGroupsWithNoMember()">
                    <div class="group_name">
                      {{ backendCtrl.getGroupNameFunctionOfSize(group) }}<span ng-if="group.oneByBooking"> - <span style="font-size:10px;">{{ backendCtrl.getBookingCustomerWithGroup(group).lastName }}</span></span>
                      <span ng-if="group.oneByBooking" class="helpbox"><span class="helpbox_content">{{ backendCtrl.getDisplayBookingCustomerWithGroup(group) }}</span></span>
                      <span ng-class="{'bg_rouge': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) > group.max, 'bg_gris': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) == 0, 'bg_vert': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) <= group.max}">{{ backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) }} / {{ group.max }}</span>
                    </div>
                  </div>
      					</div>
      				</div>
            </div>
          </div>
          <div class="planning_groups" ng-if="backendCtrl.viewMode === 'rapports'">
            <div class="resa_pagination">
              <div class="pagination_left">
                <ul uib-pagination ng-change="backendCtrl.applyPaginationRapportsByServicesPlanning()" total-items="backendCtrl.filteredRapportsByServicesPlanning.length" items-per-page="backendCtrl.paginationRapportsByServicesPlanning.step" ng-model="backendCtrl.paginationRapportsByServicesPlanning.number" max-size="5" class="pagination-sm pagination" boundary-link-numbers="true" rotate="true" previous-text="<?php _e('previous_link_title', 'resa') ?>" next-text="<?php _e('next_link_title', 'resa') ?>"></ul>
              </div>
              <div class="pagination_right">
                <select ng-change="backendCtrl.applyPaginationRapportsByServicesPlanning()" ng-model="backendCtrl.paginationRapportsByServicesPlanning.step">
                  <option ng-value="10" ng-selected="backendCtrl.paginationRapportsByServicesPlanning.step == 10">10</option>
                  <option ng-value="20">20</option>
                  <option ng-value="30">30</option>
                  <option ng-value="40">40</option>
                  <option ng-value="50">50</option>
                </select>
                <?php _e('by_page_words', 'resa'); ?>
              </div>
            </div>
            <div class="service_groups" id="printRapportGroup{{ $index }}" ng-repeat="rapportByServices in backendCtrl.displayedRapportsByServicesPlanning">
              <div class="planning_group_headline">
                <div class="report_title"><span ng-if="rapportByServices.idPlace!=''">[{{ backendCtrl.getTextByLocale(backendCtrl.getPlaceById(rapportByServices.idPlace).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}]</span> {{ backendCtrl.getTextByLocale(rapportByServices.service.name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</div>
                <div class="report_datetime">
                  <div class="report_date">
                    <b>Date : </b>{{ rapportByServices.startDate|formatDate:'<?php echo $variables['date_format']; ?>' }}
                  </div>
                  <div class="report_time">
                    <b>Créneau : </b>
                    <span ng-if="!rapportByServices.noEnd">
                      {{ rapportByServices.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                      <?php _e('to_word', 'resa') ?>
                      {{ rapportByServices.endDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                    </span>
                    <span ng-if="rapportByServices.noEnd">
                      <?php _e('begin_word', 'resa'); ?> {{ rapportByServices.startDate|formatDate:'<?php echo $variables['time_format']; ?>' }}
                    </span>
                  </div>
                </div>
                <div class="report_infos">
                  <div class="report_info">
                    <b>Infos : </b>{{ rapportByServices.groups.length }} groupe(s)
                  </div>
                  <div class="report_nb">
                    <b>Nombres : </b>{{ rapportByServices.numberPersons }} personne(s)
                  </div>
                  <div ng-if="backendCtrl.getNumberParticipantsNotInGroups(rapportByServices) > 0" class="report_nb_notgrouped important">
                    <b>Non attribuées : </b>{{ backendCtrl.getNumberParticipantsNotInGroups(rapportByServices) }} personne(s)
                  </div>
                </div>
                <div class="report_actions no-print">
                  <span ng-if="backendCtrl.settings.groupsManagement" class="btn btn_print title_box dashicons dashicons-groups" ng-click="backendCtrl.openGroupsManagerDialog(backendCtrl.getRapportByServicesWithFiltered(rapportByServices))">
                    <span class="title_box_content">Gérer les groupes</span>
                  </span>
                  <span ng-if="backendCtrl.settings.groupsManagement" class="btn btn_edit_group title_box dashicons dashicons-calendar-alt" ng-click="backendCtrl.setCustomDate(rapportByServices.startDate); backendCtrl.generateFilteredBookings(); backendCtrl.setViewMode('members');">
                    <span class="title_box_content">Voir Planning {{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}</span>
                  </span>
                  <span class="btn btn_print title_box dashicons dashicons-format-aside no-print" ng-click="backendCtrl.printDiv('printRapportGroup' + $index)">
                    <span class="title_box_content">Imprimer</span>
                  </span>

                </div>
              </div>
              <div class="planning_group_content">
                <div class="group" style="border: 1.5px solid {{ group.color }}" ng-click="backendCtrl.openGroupManagerDialog(rapportByServices, group)" ng-repeat="group in rapportByServices.groups">
                  <h3 class="group_title">{{ backendCtrl.getGroupName(group) }}</h3>
                  <p class="group_description">{{ group.presentation }}</p>
                  <div class="group_participant" >Participants : <span ng-class="{'bg_rouge': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) > group.max, 'bg_gris': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) == 0, 'bg_vert': backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) <= group.max}">{{ backendCtrl.getNumberParticipantsInGroup(backendCtrl.getRapportByServicesByGroup(group), group) }} / {{ group.max }} personnes</span></div>
                  <div class="members">
                    <div class="member_title">{{ backendCtrl.getTextByLocale(backendCtrl.settings.staff_word_many, '<?php echo get_locale(); ?>')|htmlSpecialDecode }} :</div>
                    <div class="member" ng-repeat="idMember in group.idMembers track by $index">{{ backendCtrl.getMemberById(idMember).nickname|htmlSpecialDecode }}</div>
                    <div ng-if="group.idMembers.length == 0" class="bg_rouge">Aucun</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="loader_box" ng-show="backendCtrl.loadingData">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
            <a ng-if="backendCtrl.loadingData && !backendCtrl.firstLoadingData" class="link" ng-click="backendCtrl.cancelBackgroundAppointmentsLoading()">Annuler</a>
          </div>
        </div>
      </div>
    </div>
    <div id="resa_clients_list" ng-show="backendCtrl.currentPage == 'clients'">
      <span id="create_client_btn" class="btn btn_vert title_box dashicons dashicons-plus" ng-click="backendCtrl.displayPopupCustomer = true"><span class="btn_text">Créer une fiche client</span></span>
      <form class="add_new_client" ng-if="backendCtrl.displayPopupCustomer">
        <div class="btn btn_rouge title_box dashicons dashicons-no" ng-click="backendCtrl.closePopupCustomer()"></div>
        <h4 class="add_new_client_title">Ajouter un nouveau client</h4>
        <input type="text" placeholder="Nom" id="lastname" ng-model="backendCtrl.newCustomer.lastName" />
        <input type="text" placeholder="Prénom" id="firstname" ng-model="backendCtrl.newCustomer.firstName" />
        <input type="email" placeholder="Email" id="email" ng-model="backendCtrl.newCustomer.email" />
        <input type="text" placeholder="Entreprise" ng-model="backendCtrl.newCustomer.company" />
        <input type="text" placeholder="Téléphone" ng-model="backendCtrl.newCustomer.phone" />
        <input type="text" placeholder="Téléphone 2" ng-model="backendCtrl.newCustomer.phone2" />
        <input class="add_new_client_adress" type="text" placeholder="Adresse" ng-model="backendCtrl.newCustomer.address" />
        <input type="text" placeholder="Ville" ng-model="backendCtrl.newCustomer.town" />
        <input type="text" placeholder="Code postal" ng-model="backendCtrl.newCustomer.postalCode" />
        <select ng-model="backendCtrl.newCustomer.country">
          <option value="" disabled selected>-- Pays --</option>
          <option ng-repeat="country in backendCtrl.countries" value="{{ country.code }}">{{ country.name }}</option>
        </select>
        <select ng-model="backendCtrl.newCustomer.locale">
          <option value="" disabled selected>-- Langue --</option>
          <option ng-repeat="language in backendCtrl.settings.languages" value="{{ language }}">{{ backendCtrl.settings.allLanguages[language][0] }}</option>
        </select>
        <input type="text" placeholder="SIRET" ng-model="backendCtrl.newCustomer.siret" />
        <input type="text" placeholder="Forme juridique" ng-model="backendCtrl.newCustomer.legalForm" />
        <div class="company_account">
          <label for="company_account_customers">Type de compte</label>
          <select id="company_account_customers" ng-model="backendCtrl.newCustomer.typeAccount" ng-options="typeAccount.id as (backendCtrl.getTextByLocale(typeAccount.name)|htmlSpecialDecode) for typeAccount in backendCtrl.settings.typesAccounts"></select>
        </div>
        <div class="wp_account">
          <input id="wp_account_customers" type="checkbox" ng-model="backendCtrl.newCustomer.createWpAccount" />
          <label for="wp_account_customers">Créer un compte sur le site (email nécessaire)</label>
        </div>
        <div ng-if="backendCtrl.newCustomer.createWpAccount" class="notify_wp_account">
          <input id="notify_wp_account_customers" type="checkbox" ng-model="backendCtrl.newCustomer.notify" />
          <label for="notify_wp_account_customers">Notifier le client par email</label>
        </div>
        <input ng-disabled="!backendCtrl.isOkNewCustomer()" ng-class="{'btn_locked':!backendCtrl.isOkNewCustomer(), 'btn_vert':backendCtrl.isOkNewCustomer()}" ng-click="backendCtrl.createCustomer()" class="add_new_client_btn btn" type="button" value="Créer client" />
        <b class="warning" ng-if="backendCtrl.newCustomer.createWpAccount && (backendCtrl.newCustomer.email == null || backendCtrl.newCustomer.email.length == 0)">Veuillez renseigner une adresse email correcte !<br /></b>
        <b class="warning" ng-if="!backendCtrl.newCustomer.createWpAccount && (backendCtrl.newCustomer.phone == null || backendCtrl.newCustomer.phone.length == 0)">Veuillez renseigner un numéro de téléphone !<br /></b>
      </form>
      <div class="filter_type filters_name">
        <h4>Rechercher un client (Total : {{ backendCtrl.nbTotalCustomers }})</h4>
        <input type="search" ng-change="backendCtrl.searchCustomersAction()" ng-model-options="{ debounce: 800 }" placeholder="Nom, prénom, entreprise, téléphone, email..." ng-model="backendCtrl.searchCustomer" ng-disabled="backendCtrl.searchCustomersLaunched" />
      </div>
      <div class="resa_pagination">
        <div class="pagination_left">
          <ul uib-pagination ng-change="backendCtrl.searchCustomersAction()" total-items="backendCtrl.nbTotalCustomers" items-per-page="backendCtrl.paginationCustomers.step" ng-model="backendCtrl.paginationCustomers.number" max-size="5" class="pagination-sm pagination" boundary-link-numbers="true" rotate="true" previous-text="<?php _e('previous_link_title', 'resa') ?>" next-text="<?php _e('next_link_title', 'resa') ?>"></ul>
        </div>
        <div class="pagination_right">
          <select ng-change="backendCtrl.searchCustomersAction()" ng-model="backendCtrl.paginationCustomers.step">
            <option ng-value="10" ng-selected="backendCtrl.paginationCustomers.step == 10">10</option>
            <option ng-value="20">20</option>
            <option ng-value="30">30</option>
            <option ng-value="40">40</option>
            <option ng-value="50">50</option>
          </select>
          <?php _e('by_page_words', 'resa'); ?>
        </div>
      </div>
      <div class="client_results">
        <div class="loader_box" ng-show="backendCtrl.searchCustomersLaunched">
          <div class="loader_content">
            <div class="loader">
              <div class="shadow"></div>
              <div class="box"></div>
            </div>
          </div>
        </div>
        <div class="client_results_content">
          <div class="client_results_title">
            <span class="lastname_title">Nom</span>
            <span class="firstname_title">Prénom</span>
            <span class="email_title">Email</span>
            <span class="company_title">Entreprise</span>
            <span class="phone_title">Téléphone</span>
            <span class="type_account_title">Type de compte</span>
          </div>
          <div class="client_result" title="Cliquez pour sélectionner ce client" ng-repeat="customer in backendCtrl.displayedCustomers" ng-click="backendCtrl.openCustomerDialog(customer.ID)">
            <span class="lastname">{{ customer.lastName | htmlSpecialDecode }}</span>
            <span class="firstname">{{ customer.firstName | htmlSpecialDecode }}</span>
            <span class="email">{{ customer.email }}</span>
            <span class="company">{{ customer.company | htmlSpecialDecode }}</span>
            <span class="phone">{{ customer.phone|formatPhone }} <span ng-if="customer.phone2.length > 0"> / {{ customer.phone2|formatPhone }}</span></span>
            <span class="client_account_info">
              <span class="companyAccount">
                {{ backendCtrl.getTypeAccountName(customer) }}<i ng-if="customer.idFacebook.length > 0">(Facebook)</i><br />
                <i ng-if="!customer.isWpUser && !customer.askAccount" class="isWpUser">(Pas de compte sur le site)<br /></i>
                <i ng-if="customer.askAccount" class="isWpUser" style="color:#00BFFF">Demande de compte</i>
              </span>
            </span>
          </div>
        </div>
      </div>
      <div class="loader_box" ng-show="backendCtrl.loadingData || backendCtrl.launchAddNewCustomer">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
            <a ng-if="backendCtrl.loadingData && !backendCtrl.firstLoadingData" class="link" ng-click="backendCtrl.cancelBackgroundAppointmentsLoading()">Annuler</a>
          </div>
        </div>
      </div>
    </div>
    <div ng-show="backendCtrl.currentPage == 'logNotifications'" id="notif_center_historic">
      <h2>Journal des notifications</h2>
      <p style="text-align:center; font-style: italic">Le journal des notifications regroupe l'intégralité des notifications</p>
      <div class="tab_content active">
        <div ng-click="backendCtrl.clickOnLogNotifications(log)" ng-repeat="log in backendCtrl.logNotifications" class="notif {{ backendCtrl.getType(log) }}">
          <div class="notif_icon">
            <span ng-if="log.idBooking == -1" class="icon dashicons dashicons-admin-users"></span>
            <span ng-if="log.idBooking > -1" class="icon dashicons dashicons-media-default"></span>
          </div>
          <div class="notif_infos">
            <span ng-bind-html="log.text|htmlSpecialDecode:true"></span>
          </div>
          <div class="notif_time">
            {{ backendCtrl.getRemainingTime(log, '<?php echo $variables['date_format']; ?>', '<?php echo $variables['time_format']; ?>')}}
            <span ng-if="log.idPlaces.length > 0"> - {{ backendCtrl.getListPlaces(log.idPlaces.split(',')) }}</span>
            <span ng-if="log.clicked">- <a id="not_read" ng-click="backendCtrl.unreadLogNotification($event, log)">Marquer non lu</a></span>
          </div>
        </div>
      </div>
      <div id="load_more" ng-if="!backendCtrl.getLogNotificationsLaunched" ng-click="backendCtrl.loadLogNotificationsAnteriors(-1, 30)">Charger 30 notifications plus anciennes</div>
      <div id="load_more" ng-click="backendCtrl.clickAllLogNotifications()">Tout marquer comme vu</div>
      <div class="loader_box" ng-show="backendCtrl.loadingData || backendCtrl.getLogNotificationsLaunched">
        <div class="loader_content">
          <div class="loader">
            <div class="shadow"></div>
            <div class="box"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
