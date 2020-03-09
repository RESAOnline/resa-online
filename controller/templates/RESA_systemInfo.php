<h1><?php echo get_admin_page_title() ?></h1>
<div>
  Attention cette page est réservée aux administrateurs resa-online avancés, les données sensibles et dangereuses, ne pas toucher !
</div>
<div class="container" id="resa_form" ng-app="resa_app" ng-controller="SystemInfoController as systemInfoCtrl" ng-init='systemInfoCtrl.initialize("<?php echo admin_url('admin-ajax.php'); ?>")' ng-cloak>

<div class="row resa_rule_bloc" ng-show="systemInfoCtrl.importationProgress">
  <div class="col-md-12">
    <h4><?php _e( 'Import_progress_words','resa') ?></h4>
  </div>
  <div class="col-md-12">
    {{ systemInfoCtrl.currentSynchronized }} / {{ systemInfoCtrl.maxToSynchronize }} -
    {{ systemInfoCtrl.importPurcent() }}% - {{ systemInfoCtrl.remainingTime() }}
    <button class="btn btn-default resa_btn" ng-click="systemInfoCtrl.importationProgress = false"><?php _e('cancel_link_title', 'resa'); ?></button>
  </div>
</div>

<div class="row resa_rule_bloc" ng-hide="systemInfoCtrl.importationProgress">
  <div class="col-md-12">
    <h4><?php _e( 'data_and_saveguard_title','resa') ?></h4>
  </div>
  <div class="col-md-12">
      <label><input type="checkbox" ng-model="systemInfoCtrl.extractForNewIntall" /> <?php _e('extract_for_new_intall_checkbox_title', 'resa'); ?><br /></label>
    <button type="button" class="btn btn-default resa_btn" ng-click="systemInfoCtrl.extractAllData()"><?php _e( 'extract_all_data_link_title', 'resa' ) ?></button><br />
    <label>
      <form method="post" enctype="multipart/form-data">
        <input fileread="systemInfoCtrl.fileImport" type="file" name="file" /><br />
        <label><input type="checkbox" ng-model="systemInfoCtrl.loadOptionsImport" /> <?php _e('load_options_checkbox_title', 'resa'); ?><br /></label>
        <?php if(defined('WP_DEBUG') && WP_DEBUG && defined('WP_RESA_DEBUG') && WP_RESA_DEBUG){ ?>
        <label><input type="checkbox" ng-model="systemInfoCtrl.loadCustomersImport" /> <?php _e('load_customers_checkbox_title', 'resa'); ?><br /></label>
        <?php } ?>
        <input ng-click="systemInfoCtrl.importAllData()" class="btn btn-default resa_btn" type="submit" value="<?php _e('import_all_data_link_title', 'resa') ?>" />
      </form>
    </label>
    <?php if(defined('WP_DEBUG') && WP_DEBUG && defined('WP_RESA_DEBUG') && WP_RESA_DEBUG){ ?>
    <button type="button" ng-disabled="systemInfoCtrl.deleteAllBookingsLaunched" class="btn btn-default resa_btn" ng-click="systemInfoCtrl.launchDeleteAllBookings({
      title: '<?php _e('ask_delete_bookings_title_dialog','resa') ?>',
      text: '<?php _e('ask_delete_bookings_text_dialog','resa') ?>',
      confirmButton: '<?php _e('ask_delete_bookings_confirmButton_dialog','resa') ?>',
      cancelButton: '<?php _e('ask_delete_bookings_cancelButton_dialog','resa') ?>'
    })"><?php _e( 'delete_all_bookings_link_title', 'resa' ) ?><span ng-if="systemInfoCtrl.deleteAllBookingsLaunched">...</span></button><br />

    <label><input type="checkbox" ng-model="systemInfoCtrl.deleteWpRESACustomers" /> <?php _e('delete_wp_resa_customers_checkbox_title', 'resa'); ?><br /></label>
    <button ng-hide="systemInfoCtrl.clearAllDataLaunched" type="button" class="btn btn-default resa_btn" ng-click="systemInfoCtrl.launchClearAllData({
      title: '<?php _e('ask_clear_all_data_title_dialog','resa') ?>',
      text: '<?php _e('ask_clear_all_data_text_dialog','resa') ?>',
      confirmButton: '<?php _e('ask_clear_all_data_confirmButton_dialog','resa') ?>',
      cancelButton: '<?php _e('ask_clear_all_data_cancelButton_dialog','resa') ?>'
    })"><?php _e( 'clear_all_data_link_title', 'resa' ) ?></button>
    <button ng-show="systemInfoCtrl.clearAllDataLaunched" type="button" class="btn btn-default resa_btn"><?php _e( 'clear_all_data_link_title', 'resa' ) ?>...</button>
    <br />
    <?php } ?>
  </div>
</div>
<div class="row resa_rule_bloc" ng-hide="systemInfoCtrl.importationProgress">
  <div class="col-md-12">
    <h4>Statisques</h4>
    <div ng-if="systemInfoCtrl.statistics != null">
      <b>Total réservations:</b> {{ systemInfoCtrl.statistics.total }}<br />
      <b>Réservation faite en ligne :</b> {{ systemInfoCtrl.statistics.nbBookingsOnline }} soit {{ ((systemInfoCtrl.statistics.nbBookingsOnline/systemInfoCtrl.statistics.total)*100).toFixed(2) }}% <br />
      <b>Réservation faite côté backend :</b> {{ systemInfoCtrl.statistics.nbBookingsBackend }} soit {{ ((systemInfoCtrl.statistics.nbBookingsBackend/systemInfoCtrl.statistics.total) * 100).toFixed(2) }}%<br />
    </div>
    <div ng-if="systemInfoCtrl.statistics == null">Chargement des données statistiques...</div>
  </div>
</div>
<div class="row resa_rule_bloc" ng-hide="systemInfoCtrl.importationProgress">
  <div class="col-md-12">
    <h4>Diagnostique des clients</h4>
    <div>
      Afin de savoir si il y a des conflits d'ID
    </div>
  </div>
  <div class="col-md-12">
    <button ng-disabled="systemInfoCtrl.launchCustomersDiagnostic" type="button" class="btn btn-default resa_btn" ng-click="systemInfoCtrl.customersDiagnostic()">Diagnostique des clients</button><br />
    <span ng-show="systemInfoCtrl.launchCustomersDiagnostic">Diagnostique en cours........</span>
    <span ng-if="systemInfoCtrl.customersDiagnosticResults != null">
      <br />Résultat :  {{ systemInfoCtrl.customersDiagnosticResults.length }} conflit_s<br />
      <span ng-repeat="customers in systemInfoCtrl.customersDiagnosticResults">
        Conflit ID : <b>{{ customers[0].ID }}</b> entre <b>{{ customers[0].lastName }}</b> <b>{{ customers[0].firstName }}</b> et <b>{{ customers[1].lastName }}</b> <b>{{ customers[1].firstName }}</b>
        <a ng-click="systemInfoCtrl.resolveCustomerConflict(customers[1])">Résoudre conflit</a>
        <br />
      </span>
    </span>
  </div>
</div>
<div class="row resa_rule_bloc" ng-hide="systemInfoCtrl.importationProgress">
  <div class="col-md-12">
    <h4>Mettre a jour les dates de validité des tokens</h4>
    <div>Permet de mettre a jour les dates de validité des tokens de connexion automatique</div>
  </div>
  <div class="col-md-12">
    <button ng-disabled="systemInfoCtrl.launchSetTokensValidations" type="button" class="btn btn-default resa_btn" ng-click="systemInfoCtrl.setTokensValidations()">Lancer le script</button><br />
    <span ng-show="systemInfoCtrl.launchSetTokensValidations">Script en cours........</span>
  </div>
</div>
<?php if(defined('WP_DEBUG') && WP_DEBUG && defined('WP_RESA_DEBUG') && WP_RESA_DEBUG){ ?>
  <div class="row resa_rule_bloc" ng-hide="systemInfoCtrl.importationProgress">
    <div class="col-md-12">
      <h4>Forcer mise à jour BDD</h4>
      <div>
        Attention peut écraser certains réglages si cela a déjà été forcé une première fois.
      </div>
    </div>
    <div class="col-md-12">
      <button ng-disabled="systemInfoCtrl.launchForceUpdateDatabase" type="button" class="btn btn-default resa_btn" ng-click="systemInfoCtrl.forceLastUpdateDatabase()">Forcer dernière mise à jour</button><br />
      <span ng-show="systemInfoCtrl.launchForceUpdateDatabase">Mise à jour en cours........</span>
    </div>
  </div>
<?php } ?>
</div>
