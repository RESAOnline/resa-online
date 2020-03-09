<div ng-class="{'resa_popupa4':receiptBookingCtrl.format == 'A4', 'resa_popupticket':receiptBookingCtrl.format == 'ticket'}" id="resa_reciept_resa" ng-controller="NewReceiptBookingController as receiptBookingCtrl" ng-show="receiptBookingCtrl.opened">
  <div class="resa_popup_wrapper">
    <div class="resa_popup_content" id="to_print">
      <div class="reciept_reservation" >
        <div class="reciept_btn">
          <input class="btn btn_rouge btn_close_popup no-print" type="button" ng-click="receiptBookingCtrl.close()" value="<?php _e('Close_word', 'resa'); ?>" />
          <input class="btn btn_print_popup no-print" ng-click="receiptBookingCtrl.printDiv('to_print')" type="button" value="<?php _e('print_link_title', 'resa'); ?>" />
          <input ng-if="receiptBookingCtrl.displayFormatButtons" class="btn btn_print_popup no-print" ng-click="receiptBookingCtrl.setFormat('A4')" type="button" value="Format A4" />
          <input ng-if="receiptBookingCtrl.displayFormatButtons" class="btn btn_print_popup no-print" ng-click="receiptBookingCtrl.setFormat('ticket')" type="button" value="Format Ticket" />
        </div>
        <div class="reciept_content">
          <div ng-if="receiptBookingCtrl.settings.company_logo.length > 0" class="reciept_company_logo">
            <img ng-src="{{ receiptBookingCtrl.settings.company_logo }}" />
          </div>
          <div class="reciept_client_infos">
            <p>{{ receiptBookingCtrl.customer.lastName }} {{ receiptBookingCtrl.customer.firstName }}</p>
            <p>{{ receiptBookingCtrl.customer.company | htmlSpecialDecode }}</p>
            <p>{{ receiptBookingCtrl.customer.phone|formatPhone }}</p>
            <p ng-if="receiptBookingCtrl.customer.address.length > 0">{{ receiptBookingCtrl.customer.address }}</p>
            <p ng-if="receiptBookingCtrl.customer.postalCode.length > 0 || receiptBookingCtrl.customer.town.length > 0">{{ receiptBookingCtrl.customer.postalCode }} {{ receiptBookingCtrl.customer.town }}</p>
            <p ng-if="receiptBookingCtrl.customer.country.length > 0">{{ receiptBookingCtrl.customer.country }}</p>
            <p ng-if="receiptBookingCtrl.customer.siret.length > 0">{{ receiptBookingCtrl.customer.siret }}</p>
            <p ng-if="receiptBookingCtrl.customer.legalForm.length > 0">{{ receiptBookingCtrl.customer.legalForm }}</p>
          </div>
          <div class="reciept_company_infos">
            <p><?php _e('created_on_words', 'resa'); ?> : {{ receiptBookingCtrl.booking.creationDate|formatDateTime:'<?php echo $variables['date_format']; ?>' }} <?php _e('to_word', 'resa'); ?> {{ receiptBookingCtrl.booking.creationDate|formatDateTime:'<?php echo $variables['time_format']; ?>' }}</p>
            <p>{{ receiptBookingCtrl.settings.company_name| htmlSpecialDecode  }}</p>
            <p>{{ receiptBookingCtrl.settings.company_address|htmlSpecialDecode }}</p>
            <p>{{ receiptBookingCtrl.settings.company_phone }}</p>
            <p ng-if="receiptBookingCtrl.settings.company_type.length > 0">{{ receiptBookingCtrl.settings.company_type }}</p>
            <p ng-if="receiptBookingCtrl.settings.company_siret.length > 0">{{ receiptBookingCtrl.settings.company_siret }}</p>
          </div>
          <div class="reciept_resa_state">
            <p ng-if="!receiptBookingCtrl.booking.quotation">
              <?php _e('booking_status_words', 'resa'); ?> : <br />
              <b ng-if="receiptBookingCtrl.booking.status == 'ok'"><?php _e('booking_status_ok_word', 'resa'); ?></b>
              <b ng-if="receiptBookingCtrl.booking.status == 'waiting'"><?php _e('booking_status_waiting_word', 'resa'); ?></b>
              <b ng-if="receiptBookingCtrl.booking.status == 'cancelled'"><?php _e('booking_status_cancelled_word', 'resa'); ?></b>
            </p>
          </div>
          <div class="reciept_reservation_infos">
            <h3 ng-if="receiptBookingCtrl.booking.quotation"><?php _e('quotation_word', 'resa'); ?> n° {{ receiptBookingCtrl.getBookingId(receiptBookingCtrl.booking) }}</h3>
            <h3 ng-if="!receiptBookingCtrl.booking.quotation"><?php _e('online_receipt_words', 'resa'); ?>  n° {{ receiptBookingCtrl.getBookingId(receiptBookingCtrl.booking) }}</h3>
            <br />
            <div class="resa_booking resa_booking_header" ng-class="{'no_tax':!receiptBookingCtrl.settings.tvaActivated}">
              <div class="date_hour">
  							<span ng-if="receiptBookingCtrl.booking.appointments.length == 1"><?php _e('Date_and_hours_word', 'resa'); ?></span>
  							<span ng-if="receiptBookingCtrl.booking.appointments.length > 1"><?php _e('Dates_and_hours_word', 'resa'); ?></span>
              </div>
              <div class="state"><?php _e('status_word', 'resa'); ?></div>
              <div class="activity">
  							<span ng-if="receiptBookingCtrl.booking.appointments.length == 1"><?php _e('Activity_word', 'resa'); ?></span>
  							<span ng-if="receiptBookingCtrl.booking.appointments.length > 1"><?php _e('Activities_word', 'resa'); ?></span>
              </div>
              <div ng-if="receiptBookingCtrl.settings.tvaActivated" class="tax"><?php _e('Tax_es_word', 'resa'); ?></div>
              <div class="price"><?php _e('Price_list_word', 'resa'); ?></div>
            </div>
            <div class="resa_booking" ng-if="receiptBookingCtrl.isDisplayed(appointment)" ng-repeat-start="appointment in receiptBookingCtrl.booking.appointments|orderBy:'startDate'" ng-class="{'no_tax':!receiptBookingCtrl.settings.tvaActivated}">
              <div class="date_hour">
                <span ng-if="!receiptBookingCtrl.isCombinedRow(appointment)">
                  {{ appointment.startDate | formatDate:'<?php echo $variables['date_format']; ?>' }} /
                  <span ng-if="!appointment.noEnd">
                    {{ appointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'}} <?php _e('to_word','resa') ?> {{ appointment.endDate | formatDateTime:'<?php echo $variables['time_format']; ?>' }}
                  </span>
                  <span ng-if="appointment.noEnd">
                    <?php _e('begin_word','resa') ?> {{ appointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}
                  </span>
                </span>
                <span ng-if="receiptBookingCtrl.isCombinedRow(appointment)" ng-repeat="displayAppointment in receiptBookingCtrl.getAppointmentsByInternalIdLink(appointment.internalIdLink)">
                  {{ displayAppointment.startDate | formatDate:'<?php echo $variables['date_format']; ?>' }} /
                  <span ng-if="!appointment.noEnd">
                    {{ displayAppointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'}} <?php _e('to_word','resa') ?> {{ displayAppointment.endDate | formatDateTime:'<?php echo $variables['time_format']; ?>' }}<br />
                  </span>
                  <span ng-if="appointment.noEnd">
                    <?php _e('begin_word','resa') ?> {{ displayAppointment.startDate | formatDateTime:'<?php echo $variables['time_format']; ?>'  }}
                  </span>
                </span>
              </div>
              <div class="state">
                <span ng-if="!receiptBookingCtrl.isCombinedRow(appointment)">
                  <span ng-if="appointment.state == 'waiting'"><?php _e('waiting_status_word', 'resa'); ?></span>
                  <span ng-if="appointment.state == 'ok'"><?php _e('ok_status_word', 'resa'); ?></span>
                  <span ng-if="appointment.state == 'cancelled'"><?php _e('cancelled_status_word', 'resa'); ?></span>
                </span>
                <span ng-if="receiptBookingCtrl.isCombinedRow(appointment)" ng-repeat="displayAppointment in receiptBookingCtrl.getAppointmentsByInternalIdLink(appointment.internalIdLink)">
                  <span ng-if="displayAppointment.state == 'waiting'"><?php _e('waiting_status_word', 'resa'); ?></span>
                  <span ng-if="displayAppointment.state == 'ok'"><?php _e('ok_status_word', 'resa'); ?></span>
                  <span ng-if="displayAppointment.state == 'cancelled'"><?php _e('cancelled_status_word', 'resa'); ?></span>
                  <br />
                </span>
              </div>
              <div class="activity">
                <span ng-if="receiptBookingCtrl.getServiceById(appointment.idService).places.length > 0">[
                  <span ng-repeat="place in receiptBookingCtrl.getServiceById(appointment.idService).places">
                    <span ng-if="$index > 0">, </span>
                    {{ receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.getPlaceById(place).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
                  </span>]
                </span>
                {{ receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.getServiceById(appointment.idService).name, '<?php echo get_locale(); ?>')|htmlSpecialDecode }}
              </div>
              <div ng-if="receiptBookingCtrl.settings.tvaActivated"  class="tax">
                <span ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                  <span ng-repeat="vatLine in receiptBookingCtrl.getServicePriceAppointment(receiptBookingCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).vatList">
                    {{ receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.getVatById(vatLine.idVat).name,'<?php echo get_locale(); ?>') }}
                    ({{ (receiptBookingCtrl.displayTVA(vatLine, receiptBookingCtrl.getServicePriceAppointment(receiptBookingCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice), appointmentNumberPrice.number, 1))|number:2 }}){{ receiptBookingCtrl.settings.currency }}<br />
                  </span>
                </span>
              </div>
              <div class="price">
                <span ng-repeat="appointmentNumberPrice in appointment.appointmentNumberPrices">
                  <b>{{ appointmentNumberPrice.number }}</b> x {{ receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.getServicePriceAppointment(receiptBookingCtrl.getServiceById(appointment.idService), appointmentNumberPrice.idPrice).name,'<?php echo get_locale(); ?>')|htmlSpecialDecode
                }} <b ng-if="!appointmentNumberPrice.deactivated">{{ receiptBookingCtrl.round((appointmentNumberPrice.totalPrice * receiptBookingCtrl.getNumberCombined(appointment))) | number:2 }}{{ receiptBookingCtrl.settings.currency }}</b>
                  <br />
                </span>
                | <b>{{ receiptBookingCtrl.round(appointment.totalPriceWithoutReductions * receiptBookingCtrl.getNumberCombined(appointment))|number:2 }}{{ receiptBookingCtrl.settings.currency }} <?php _e('IVAT_word', 'resa'); ?></b>
              </div>
          </div>
          <div class="resa_booking_reduction" ng-repeat-end ng-repeat="appointmentReduction in appointment.appointmentReductions">
            <div class="reduction_title">{{ receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.getReductionById(appointmentReduction.idReduction).name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }} (x{{ appointmentReduction.number }})</div>
            <div class="reduction_description" ng-bind-html="receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.getReductionById(appointmentReduction.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
            <div class="reduction_price">
              <span ng-if="appointmentReduction.type == 0">{{ (appointmentReduction.value * appointmentReduction.number)|negative|number:2 }}{{ receiptBookingCtrl.settings.currency }}</span>
              <span ng-if="appointmentReduction.type == 1">{{ ((receiptBookingCtrl.getTotalPriceNumberPrice(appointment.idService, receiptBookingCtrl.getAppointmentNumberPrices(appointment, appointmentReduction.idPrice), appointment) * appointmentReduction.number) * appointmentReduction.value  / 100)|negative|number:2 }}{{ receiptBookingCtrl.settings.currency }} </span>
              <span ng-if="appointmentReduction.type == 2">{{ (appointmentReduction.value * appointmentReduction.number)|negative|number:2 }}{{ receiptBookingCtrl.settings.currency }}</span>
              <span ng-if="appointmentReduction.type == 3">{{ ((receiptBookingCtrl.getServicePriceAppointment(receiptBookingCtrl.getServiceById(appointment.idService), appointmentReduction.idPrice).price - appointmentReduction.value) * appointmentReduction.number)|negative|number:2 }}{{ receiptBookingCtrl.settings.currency }}</span>
              <span ng-if="appointmentReduction.type == 4">{{ (receiptBookingCtrl.getPriceNumberPrice(appointment.idService, receiptBookingCtrl.getAppointmentNumberPrices(appointment, appointmentReduction.idPrice), appointment) * appointmentReduction.value * appointmentReduction.number)|negative|number:2 }} {{ receiptBookingCtrl.settings.currency }}</span>
              <span ng-if="appointmentReduction.type == 5"></span></div>
            </div>
            <div class="resa_booking_subtotal" ng-if="receiptBookingCtrl.isDisplayed(appointment)">
              <div class="subtotal_title"> <?php _e('Sub_total_word', 'resa'); ?></div>
              <div class="subtotal_value">{{ receiptBookingCtrl.round(appointment.totalPrice * receiptBookingCtrl.getNumberCombined(appointment))|number:2 }} {{ receiptBookingCtrl.settings.currency }}</div>
            </div>

            <div class="resa_booking_reduction" ng-repeat="bookingReduction in receiptBookingCtrl.booking.bookingReductions">
              <div class="reduction_title">{{ receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.getReductionById(bookingReduction.idReduction).name,'<?php echo get_locale(); ?>')|htmlSpecialDecode }} (x{{ bookingReduction.number }})</div>
              <div class="reduction_description" ng-bind-html="receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.getReductionById(bookingReduction.idReduction).presentation,'<?php echo get_locale(); ?>')|displayNewLines"></div>
              <div class="reduction_price">
                <span ng-if="bookingReduction.type == 0">{{ (bookingReduction.value * bookingReduction.number)|negative }}{{ receiptBookingCtrl.settings.currency }}</span>
                <span ng-if="bookingReduction.type == 1">{{ (bookingReduction.value * bookingReduction.number)|negative }}%</span>
                <span ng-if="bookingReduction.type == 2">{{ (bookingReduction.value * bookingReduction.number)|negative }}{{ receiptBookingCtrl.settings.currency }}</span>
                <span ng-if="bookingReduction.type == 5"></span>
              </div>
            </div>

            <div class="resa_booking_reduction" ng-repeat="bookingCustomReduction in receiptBookingCtrl.booking.bookingCustomReductions">
              <div class="reduction_title">{{ bookingCustomReduction.description }}</div>
              <div class="reduction_description">{{ bookingCustomReduction.description }}<span ng-if="receiptBookingCtrl.settings.tvaActivated"> (<?php _e('Vat_word', 'resa'); ?> {{ bookingCustomReduction.vatValue }}% ({{ receiptBookingCtrl.vatValue(bookingCustomReduction.amount, bookingCustomReduction.vatValue) | number:2 }}{{ receiptBookingCtrl.settings.currency }}))</span>
              </div>
              <div class="reduction_price">{{ bookingCustomReduction.amount }}{{ receiptBookingCtrl.settings.currency }}</div>
            </div>
            <div class="resa_booking_total">
              <div class="total_title"><?php _e('Total_word', 'resa'); ?></div>
              <div class="total_value">{{ receiptBookingCtrl.round(receiptBookingCtrl.booking.totalPrice)|number:2 }}{{ receiptBookingCtrl.settings.currency }}</div>
            </div>
            <div class="reciept_resa_paiements">
              <h4 ng-if="receiptBookingCtrl.booking.payments.length > 0"><?php _e('Your_payments_words', 'resa'); ?></h4>
              <div class="paiement_table" ng-if="receiptBookingCtrl.booking.payments.length > 0">
                <div class="paiement_table_head">
                  <div><?php _e('Date_word', 'resa'); ?></div>
                  <div><?php _e('payment_method_title', 'resa'); ?></div>
                  <div><?php _e('amount_field_title', 'resa'); ?></div>
                </div>
                <div class="paiement_table_line" ng-if="payment.state != 'pending' && payment.state != 'cancelled'" ng-repeat="payment in receiptBookingCtrl.booking.payments">
                  <div>{{ payment.paymentDate | formatDateTime:'<?php echo $variables['date_format']; ?>' }}</div>
                  <div>
                    <span ng-if="payment.isRefund">[Remboursement]</span>
                    <span ng-if="payment.isReceipt != null && payment.isReceipt">[Acompte]</span>{{ receiptBookingCtrl.getPaymentName(payment.type, payment.name) }}
                  </div>
                  <div ng-if="payment.repayment"><b>{{ payment.value|negative|number:2 }}{{ receiptBookingCtrl.settings.currency }}</b></div>
                  <div ng-if="!payment.repayment"><b>{{ payment.value|number:2 }}{{ receiptBookingCtrl.settings.currency }}</b></div>
                </div>
              </div>
            </div>
            <div class="reciept_resa_balance" ng-if="!receiptBookingCtrl.booking.quotation">
              <div class="total_balance"><?php _e('amount_to_be_paid_words', 'resa'); ?> : {{ receiptBookingCtrl.round(receiptBookingCtrl.booking.needToPay)|number:2 }}{{ receiptBookingCtrl.settings.currency }}</div>
            </div>
            <div ng-if="!receiptBookingCtrl.booking.quotation && receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.settings.informations_on_receipt,'<?php echo get_locale(); ?>').length > 0">
              <span ng-bind-html="receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.settings.informations_on_receipt,'<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></span>
            </div>
            <div ng-if="receiptBookingCtrl.booking.quotation && receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.settings.informations_on_quotation,'<?php echo get_locale(); ?>').length > 0">
              <span ng-bind-html="receiptBookingCtrl.getTextByLocale(receiptBookingCtrl.settings.informations_on_quotation,'<?php echo get_locale(); ?>')|htmlSpecialDecode:true"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="resa_brand"><?php _e('pub_resa_words', 'resa'); ?></div>
    </div>
  </div>
</div>
