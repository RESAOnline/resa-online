"use strict";

/**
* Plugin tinymce to add resa shortcodes
*/
(function() {
  tinymce.PluginManager.add('RESAShortcodes', function(editor, url) {
    editor.addButton('RESAShortcodes', {
      type: 'menubutton',
      text: 'Raccourcies',
      icon: false,
      menu: [{
        text: 'Client',
        menu: [{
          text: 'Nom de famille',
          onclick: function() {
            editor.insertContent('[RESA_customer_lastname]');
          }
        },
        {
          text: 'Prénom',
          onclick: function() {
            editor.insertContent('[RESA_customer_firstname]');
          }
        },
        {
          text: 'Entreprise',
          onclick: function() {
            editor.insertContent('[RESA_customer_company]');
          }
        },
        {
          text: 'Téléphone',
          onclick: function() {
            editor.insertContent('[RESA_customer_phone]');
          }
        },
        {
          text: 'Login',
          onclick: function() {
            editor.insertContent('[RESA_customer_login]');
          }
        },
        {
          text: 'Lien Compte client',
          onclick: function() {
            editor.insertContent('[RESA_link_account]');
          }
        }]
      }, {
        text: 'Réservation',
        menu: [{
          text: 'ID réservation',
          onclick: function() {
            editor.insertContent('[RESA_booking_id]');
          }
        },
        {
          text: 'Note publique',
          onclick: function() {
            editor.insertContent('[RESA_booking_note]');
          }
        },
        {
          text: 'Prix',
          onclick: function() {
            editor.insertContent('[RESA_total_price]');
          }
        },
        {
          text: 'Montant de l\'acompte',
          onclick: function() {
            editor.insertContent('[RESA_advancePayment]');
          }
        },
        {
          text: 'Lien de paiement',
          onclick: function() {
            editor.insertContent('[RESA_link_payment_booking]');
          }
        },
        {
          text: 'Date d\'expiration',
          onclick: function() {
            editor.insertContent('[RESA_expiration_date]');
          }
        },
        {
          text: 'Date de la première activité',
          onclick: function() {
            editor.insertContent('[RESA_booking_first_date]');
          }
        },
        {
          text: 'Notification dans les activités',
          onclick: function() {
            editor.insertContent('[RESA_notifications_services]');
          }
        },
        {
          text: 'Montant du paiement (email de paiement)',
          onclick: function() {
            editor.insertContent('[RESA_payment_amount]');
          }
        },
        {
          text: 'Détails de la réservation',
          onclick: function() {
            editor.insertContent('[RESA_booking_details]');
          }
        }]
      }, {
        text: 'Entreprise',
        menu: [{
          text: 'Nom de la société',
          onclick: function() {
            editor.insertContent('[RESA_company_name]');
          }
        }]
      },
      {
        text: 'Logo notification',
        onclick: function() {
          editor.insertContent('[RESA_logo]');
        }
      },
      {
        text: 'Lien fiche client (Manager)',
        onclick: function() {
          editor.insertContent('[RESA_customer_display]');
        }
      }]
    });
    return {
      getMetadata: function () {
        return  {
          name: "Example plugin",
          url: "http://exampleplugindocsurl.com"
        };
      }
    };
  });
})();
