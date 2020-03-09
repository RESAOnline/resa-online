<?php
if(!isset($isAdvancePayment)){
  $isAdvancePayment = false;
}
?>
<table class="wp-list-table widefat fixed pages ro-table">
  <thead>
    <tr>
      <th><?php _e('date_table_header', 'resa') ?></th>
      <th><?php _e('hours_table_header', 'resa') ?></th>
      <?php if((isset($showStaff) && $showStaff || !isset($showStaff))){ ?>
        <th><?php _e('staff_table_header', 'resa') ?></th>
        <?php } ?>
      <?php if(count($places) > 0){ ?>
        <th><?php _e('place_table_header','resa') ?></th>
      <?php } ?>
      <th><?php _e('service_table_header', 'resa') ?></th>
      <th><?php _e('price_list_table_header', 'resa') ?></th>
      <th><?php _e('number_table_header', 'resa') ?></th>
      <?php if((isset($showTVA) && $showTVA || !isset($showTVA))){ ?>
        <th><?php _e('total_price_ht_table_header','resa') ?></th>
        <th><?php _e('tva_table_header','resa') ?></th>
      <?php } ?>
      <th><?php _e('total_price_ttc_table_header','resa') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php
    $totalPriceTTC = 0;
    $totalPriceHT = 0;
    foreach($booking->getAppointments() as $appointment){
      $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getStartDate());
      $endDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getEndDate());
      $placeName = '';
      foreach($places as $place){
        if($appointment->getIdPlace() == $place->id){
          $placeName = RESA_Tools::getTextByLocale($place->name, get_locale());
        }
      }
      $state = $appointment->getState();
      $trClass = '';
      if($appointment->isCancelled()) {
        $trClass = 'ro-cancelled';
      }
      else if($appointment->isAbandonned()) {
        $trClass = 'ro-abandonned';
      }
      $members = array();
      foreach($appointment->getAppointmentMembers() as $appointmentMembers){
        $member = new RESA_Member();
        $member->loadById($appointmentMembers->getIdMember());
        array_push($members, array('membre' => $member, 'capacity' => $appointmentMembers->getNumber()));
      }
      $displayMembers = '';
      if(count($members) > 0){
        foreach($members as $member){
          if($displayMembers != '')
            $displayMembers .= ', ';
          if($member['capacity'] == 0){
            $displayMembers .= '<span class="ro_rouge">'.$member['membre']->getNickname().'</span>';
          }
          else{
            $displayMembers .= $member['membre']->getNickname();
          }
        }
      }
      else $displayMembers = 'N/A';

      $service = new RESA_Service();
      $service->loadById($appointment->getIdService());
      $currency = get_option('resa_settings_payment_currency');

      $applicationReductions = array();
      foreach($appointment->getAppointmentReductions() as $appointmentReduction){
        $reduction = new RESA_Reduction();
        $reduction->loadById($appointmentReduction->getIdReduction());
        if(!isset($applicationReductions['price'.$appointmentReduction->getIdPrice()])){
          $applicationReductions['price'.$appointmentReduction->getIdPrice()] = [];
        }
        array_push($applicationReductions['price'.$appointmentReduction->getIdPrice()],
        array('reduction'=>$reduction, 'appointmentReduction'=>$appointmentReduction));
      }

      $subTotalPriceTTC = 0;
      $subTotalPriceHT = 0;
      $index = 0;
      foreach($appointment->getAppointmentNumberPrices() as $appointmentNumberPrice){
        $tva = $booking->getTVA();

        $servicePrice = $service->getServicePriceById($appointmentNumberPrice->getIdPrice());
        if(isset($servicePrice)){
          $priceTTC = $servicePrice->getTotalPrice($appointmentNumberPrice->getNumber(), $appointment->getHours());
          $priceHT = round($priceTTC / (1 + ($tva / 100)),2);
          $priceTVA = $priceTTC - $priceHT;
          if($priceTVA<0) $priceTVA *= -1;
          ?>
          <tr class="<?php echo $trClass; ?>">
            <td data-colname="Date">
            <?php if($index == 0) {
              echo date_i18n(get_option('date_format'), $startDate->getTimestamp());
              if($state == 'cancelled'){
                echo '<br />' . __('cancelled_word', 'resa');
              }
              else if($state == 'waiting'){
                echo '<br />' . __('waiting_word', 'resa');
              }
              else if($state == 'abandonned'){
                echo '<br />' . __('abandonned_word', 'resa');
              }
            }
            ?>
            </td>
            <td data-colname="Heure">
              <?php if(!$appointment->isNoEnd()) { ?>
                <?php if($index == 0) { echo $startDate->format(get_option('time_format')); ?> <?php _e('to_word', 'resa') ?><br /> <?php echo $endDate->format(get_option('time_format')); }?>
              <?php } else {  _e('begin_word', 'resa'); ?> <?php echo $startDate->format(get_option('time_format')); } ?>
            </td>
            <?php if((isset($showStaff) && $showStaff || !isset($showStaff))){ ?>
              <td data-colname="Staff"><?php if($index == 0) echo $displayMembers; ?></td>
            <?php } ?>
            <?php if(count($places) > 0){ ?>
              <td data-colname="Place"><?php if($index == 0) echo $placeName; ?></td>
            <?php } ?>
            <td data-colname="Service"><?php if($index == 0) echo $service->getName(); ?></td>
            <td data-colname="Tarif"><?php echo $servicePrice->getName(); ?>
              <?php if($servicePrice->isNotThresholded()) echo $servicePrice->getPrice().$currency; ?>
            </td>
            <td data-colname="Nombre"><?php echo $appointmentNumberPrice->getNumber(); ?></td>
            <?php if((isset($showTVA) && $showTVA || !isset($showTVA))){ ?>
              <td data-colname="Prix HT"><?php if(!$appointmentNumberPrice->isDeactivated()){ ?><?php echo $priceHT; ?><?php echo $currency; ?><?php } ?></td>
              <td data-colname="Taxe"><?php if(!$appointmentNumberPrice->isDeactivated()){ ?><?php echo $priceTVA; ?><?php echo $currency; ?><br />(<?php echo $tva; ?>%)<?php } ?></td>
            <?php } ?>
            <td data-colname="Prix TTC"><?php if(!$appointmentNumberPrice->isDeactivated()){ ?><?php echo $priceTTC; ?><?php echo $currency; ?><?php } ?></td>
          </tr>
          <?php
          $index++;
          if(isset($applicationReductions['price'.$appointmentNumberPrice->getIdPrice()])){
            if(!$appointment->isCancelled() && !$appointmentNumberPrice->isDeactivated()){
              $subTotalPriceTTC += $priceTTC;
              $subTotalPriceHT += $priceHT;
            }
            foreach($applicationReductions['price'.$appointmentNumberPrice->getIdPrice()] as $params){
              $reduction = $params['reduction'];
              $tva = $booking->getTVA();
              $appointmentReduction = $params['appointmentReduction'];
              $priceTTCValue = 0;
              $effect = $appointmentReduction->getValue().'';
              if($appointmentReduction->getNumber() == 0){
                switch($appointmentReduction->getType()){
                  case 0:
                  $value = $appointmentReduction->getValue();
                  if($value > 0) $value *= -1;
                  $priceTTCValue = $value;
                  $effect = $value.''.$currency;
                  break;
                  case 1:
                  $value = $appointmentReduction->getValue();
                  if($value > 0) $value *= -1;
                  $priceTTCValue = ($priceTTC * $value) / 100;
                  $effect = $value.'%';
                  break;
                  case 2:
                  $value = $appointmentReduction->getValue();
                  if($value > 0) $value *= -1;
                  $priceTTCValue = $value * $appointmentNumberPrice->getNumber();
                  $effect = $value.''.$currency.' '. __('on_price_list_words', 'resa');
                  break;
                  case 3:
                  $value = ($servicePrice->getPrice() - $appointmentReduction->getValue()) * $appointmentNumberPrice->getNumber();
                  if($value < 0) $value *= -1;
                  $priceTTCValue = -$value;
                  $effect = __('new_price_list_words', 'resa').' '.$appointmentReduction->getValue().''.$currency.'';
                  break;
                  case 4:
                  $value = $appointmentReduction->getValue();
                  if($value < 0) $value *= -1;
                  $value = min($value, $appointmentNumberPrice->getNumber());
                  $priceTTCValue = -($value * $servicePrice->getPrice());
                  $effect = $value.' '.__('offer_quantity_words', 'resa');
                  break;
                  default:
                  break;
                }
              }
              else {
                switch($appointmentReduction->getType()){
                  case 0:
                  $value = $appointmentReduction->getValue() * $appointmentReduction->getNumber();
                  if($value > 0) $value *= -1;
                  $priceTTCValue = $value;
                  $effect = $value.''.$currency;
                  break;
                  case 1:
                  $value = $appointmentReduction->getValue();
                  if($value > 0) $value *= -1;
                  $priceTTCValue = ($priceTTC * $value) / 100;
                  $effect = $value.'%';
                  break;
                  case 2:
                  $value = $appointmentReduction->getValue();
                  if($value > 0) $value *= -1;
                  $priceTTCValue = $value * $appointmentReduction->getNumber();
                  $effect = $value.''.$currency.' '. __('on_price_list_words', 'resa');
                  break;
                  case 3:
                  $value = ($servicePrice->getPrice() - $appointmentReduction->getValue()) * $appointmentReduction->getNumber();
                  if($value < 0) $value *= -1;
                  $priceTTCValue = -$value;
                  $effect = __('new_price_list_words', 'resa').' '.$appointmentReduction->getValue().''.$currency.'';
                  break;
                  case 4:
                  $value = $appointmentReduction->getValue();
                  if($value < 0) $value *= -1;
                  $value = $value * $appointmentReduction->getNumber();
                  $priceTTCValue = -($value * $servicePrice->getPrice());
                  $effect = $value.' '.__('offer_quantity_words', 'resa');
                  break;
                  default:
                  break;
                }
              }

              $priceHTValue = round($priceTTCValue / (1 + ($tva / 100)),2);
              $subTotalPriceTTC += $priceTTCValue;
              $subTotalPriceHT += $priceHTValue;
              $priceTVA = $priceTTCValue - $priceHTValue;
              if($priceTVA<0) $priceTVA *= -1;
              $colspan = 4;
              if((isset($showStaff) && $showStaff || !isset($showStaff))) $colspan++;
              if(count($places) > 0) $colspan++;

              $reductionName = $reduction->getName();
              if($appointmentReduction->getNumber() > 0){
                $reductionName .= ' (x'.$appointmentReduction->getNumber().')';
              }
              ?>
              <tr>
                <td><?php echo $reductionName; ?></td>
                <td colspan="<?php echo $colspan; ?>"><?php echo preg_replace(array('/\\\n/'), array('<br />'), $reduction->getPresentation()); ?> (<?php echo $effect; ?>)</td>
                <?php if((isset($showTVA) && $showTVA || !isset($showTVA))){ ?>
                  <td><?php if(!$appointmentNumberPrice->isDeactivated()){ ?><?php echo $priceHTValue; ?><?php echo $currency; ?><?php } ?></td>
                  <td><?php if(!$appointmentNumberPrice->isDeactivated()){ ?><?php echo ($priceTTCValue - $priceHTValue);  ?><?php echo $currency; ?><br />(<?php echo  $tva; ?>%)<?php } ?></td>
                <?php } ?>
                <td><?php if(!$appointmentNumberPrice->isDeactivated()){ ?><?php echo ($priceTTCValue); ?><?php echo $currency; ?><?php } ?></td>
              </tr>
              <?php
              }
            } else {
              if(!$appointment->isCancelled() && !$appointmentNumberPrice->isDeactivated()){
                $subTotalPriceTTC += $priceTTC;
                $subTotalPriceHT += $priceHT;
              }
            }
          }
        }
        if(isset($applicationReductions['price-1'])){
          foreach($applicationReductions['price-1'] as $params){
            $reduction = $params['reduction'];
            $tva = $booking->getTVA();
            $appointmentReduction = $params['appointmentReduction'];
            $priceTTCReduction = 0;
            $effect = $appointmentReduction->getValue().'';
            if($appointmentReduction->getNumber() == 0){
              switch($appointmentReduction->getType()){
                case 0:
                $value = $appointmentReduction->getValue();
                if($value > 0) $value *= -1;
                $priceTTCReduction += $value;
                $effect = $value.''.$currency;
                break;
                case 1:
                $value = $appointmentReduction->getValue();
                if($value > 0) $value *= -1;
                $priceTTCReduction += ($priceTTCReduction * $value) / 100;
                $effect = $value.'%';
                break;
                case 2:
                $value = $appointmentReduction->getValue();
                if($value > 0) $value *= -1;
                $priceTTCReduction += $value * $appointmentNumberPrice->getNumber();
                $effect = $value.''.$currency.' '. __('on_price_list_words', 'resa');
                break;
                default:
                break;
              }
            }
            else {
              switch($appointmentReduction->getType()){
                case 0:
                $value = $appointmentReduction->getValue() * $appointmentReduction->getNumber();
                if($value > 0) $value *= -1;
                $priceTTCReduction += $value;
                $effect = $value.''.$currency;
                break;
                case 1:
                $value = $appointmentReduction->getValue();
                if($value > 0) $value *= -1;
                $priceTTCReduction += ($priceTTCReduction * $value) / 100;
                $effect = $value.'%';
                break;
                case 2:
                $value = $appointmentReduction->getValue();
                if($value > 0) $value *= -1;
                $priceTTCReduction += $value * $appointmentReduction->getNumber();
                $effect = $value.''.$currency.' '. __('on_price_list_words', 'resa');
                break;
                default:
                break;
              }
            }
            $priceHTReduction =  round($priceTTCReduction / (1 + ($tva / 100)),2);
            $subTotalPriceTTC += $priceTTCReduction;
            $subTotalPriceHT += $priceHTReduction;
            $priceTVA = $priceTTCReduction - $priceHTReduction;
            $colspan = 4;
            if((isset($showStaff) && $showStaff || !isset($showStaff))) $colspan++;
            if(count($places) > 0) $colspan++;
            $reductionName = $reduction->getName();
            if($appointmentReduction->getNumber() > 0){
              $reductionName .= ' (x'.$appointmentReduction->getNumber().')';
            }
            $totalPriceTTC += $subTotalPriceTTC;
            $totalPriceHT += $subTotalPriceHT;
            ?>
            <tr>
              <td><?php echo $reductionName; ?></td>
              <td colspan="<?php echo $colspan; ?>"><?php echo preg_replace(array('/\\\n/'), array('<br />'), $reduction->getPresentation()); ?></td>
              <?php if((isset($showTVA) && $showTVA || !isset($showTVA))){ ?>
                <td><?php echo $priceHTReduction; ?><?php echo $currency; ?></td>
                <td><?php echo $priceTVA; ?><?php echo $currency; ?><br />(<?php echo $tva; ?>%)</td>
                <?php } ?>
              <td><?php echo $priceTTCReduction; ?><?php echo $currency; ?></td>
            </tr>
            <?php
          }
        } else {
          $totalPriceTTC += $subTotalPriceTTC;
          $totalPriceHT += $subTotalPriceHT;
        }
        $priceTVA = $subTotalPriceTTC - $subTotalPriceHT;
        $colspan = 5;
        if((isset($showStaff) && $showStaff || !isset($showStaff))) $colspan++;
        if(count($places) > 0) $colspan++;
        ?>
        <tr class="ro-soustotal">
          <td colspan="<?php echo $colspan; ?>"><?php _e('Sub_total_word', 'resa') ?></td>
          <?php if((isset($showTVA) && $showTVA || !isset($showTVA))){ ?>
            <td><?php echo $subTotalPriceHT; ?><?php echo $currency; ?></td>
            <td><?php echo $priceTVA; ?><?php echo $currency; ?></td>
          <?php } ?>
          <td><?php echo $subTotalPriceTTC; ?><?php echo $currency; ?></td>
        </tr>
        <?php
      }
      $reductionNumber = 0;
      $reductionNumberHT = 0;

    foreach($booking->getBookingReductions() as $bookingReduction){
        $reduction = new RESA_Reduction();
        $reduction->loadById($bookingReduction->getIdReduction());
        $tva = $booking->getTVA();
        $effect = $bookingReduction->getValue().'';
        $priceValueTTC = 0;
        if($bookingReduction->getNumber() == 0){
          switch($bookingReduction->getType()){
            case 0:
            $value = $bookingReduction->getValue();
            if($value > 0) $value *= -1;
            $priceValueTTC = $value;
            $reductionNumber -= $priceValueTTC;
            $effect = $value.''.$currency;
            break;
            case 1:
            $value = $bookingReduction->getValue();
            if($value > 0) $value *= -1;
            $priceValueTTC = ($totalPriceTTC * $value) / 100;
            $reductionNumber -= ($totalPriceTTC * $value) / 100;
            $effect = $value.'%';
            break;
            case 2:
            $value = $appointmentReduction->getValue();
            if($value > 0) $value *= -1;
            $priceValueTTC = $value * $booking->getTotalPrice();
            $reductionNumber -= $priceValueTTC;
            $effect = $value.''.$currency.' '. _e('on_price_list_words', 'resa');
            break;
            default:
            break;
          }
        }
        else {
          switch($bookingReduction->getType()){
            case 0:
            $value = $bookingReduction->getValue() * $bookingReduction->getNumber();
            if($value > 0) $value *= -1;
            $priceValueTTC = $value;
            $reductionNumber -= $priceValueTTC;
            $effect = $value.''.$currency;
            break;
            case 1:
            $value = $bookingReduction->getValue();
            if($value > 0) $value *= -1;
            $priceValueTTC = ($totalPriceTTC * $value) / 100;
            $reductionNumber -= ($totalPriceTTC * $value) / 100;
            $effect = $value.'%';
            break;
            case 2:
            $value = $bookingReduction->getValue();
            if($value > 0) $value *= -1;
            $priceValueTTC = $value * $bookingReduction->getNumber();
            $reductionNumber -= $priceValueTTC;
            $effect = $value.''.$currency.' '. _e('on_price_list_words', 'resa');
            break;
            default:
            break;
          }
        }
        $priceValueHT = round($priceValueTTC / (1 + ($tva / 100)),2);
        $reductionNumberHT -= $priceValueHT;
        $priceTVA = $priceValueTTC - $priceValueHT;
        if($priceTVA<0) $priceTVA *= -1;
        $colspan = 2;
        if((isset($showStaff) && $showStaff || !isset($showStaff))) $colspan++;
        if(count($places) > 0) $colspan++;
        $reductionName = $reduction->getName();
        if($bookingReduction->getNumber() > 0){
          $reductionName .= ' (x'.$bookingReduction->getNumber().')';
        }
        ?>
        <tr>
          <td><?php echo $reductionName; ?></td>
          <td><?php echo $bookingReduction->getPromoCode(); ?></td>
          <td colspan="<?php echo $colspan; ?>"><?php echo preg_replace(array('/\\\n/'), array('<br />'), $reduction->getPresentation()); ?></td>
          <td><?php echo $effect; ?></td>
          <?php if((isset($showTVA) && $showTVA || !isset($showTVA))){ ?>
            <td><?php echo $priceValueHT; ?><?php echo $currency; ?></td>
            <td><?php echo $priceTVA; ?><?php echo $currency; ?><br />(<?php echo $tva; ?>%)</td>
          <?php } ?>
          <td><?php echo $priceValueTTC; ?><?php echo $currency; ?></td>
        </tr>
        <?php
      }
      $totalPriceTTC -= $reductionNumber;
      $totalPriceHT -= $reductionNumberHT;

      foreach($booking->getBookingCustomReductions() as $bookingCustomReduction){
        $tva = $booking->getTVA();
        $priceValueTTC = $bookingCustomReduction->getAmount();
        $priceValueHT = round($priceValueTTC / (1 + ($tva / 100)),2);
        $priceTVA = $priceValueTTC - $priceValueHT;
        if($priceTVA<0) $priceTVA *= -1;

        if(!$booking->isCancelled()) {
          $totalPriceTTC += $priceValueTTC;
          $totalPriceHT += $priceValueHT;
        }
        $amount = $bookingCustomReduction->getAmount();
        $colspan = 3;
        if((isset($showStaff) && $showStaff || !isset($showStaff))) $colspan++;
        if(count($places) > 0) $colspan++;

        $trClass = '';
        if($booking->isCancelled()) {
          $trClass = 'ro-cancelled';
        }
        ?>
        <tr class="<?php echo $trClass; ?>">
          <td><?php _e('custom_line_words','resa') ?></td>
          <td colspan="<?php echo $colspan; ?>"><?php echo $bookingCustomReduction->getDescription(); ?></td>
          <td><?php echo $amount; ?><?php echo $currency; ?></td>
          <?php if((isset($showTVA) && $showTVA || !isset($showTVA))){ ?>
          <td><?php echo $priceValueHT; ?><?php echo $currency; ?></td>
          <td><?php echo $priceTVA; ?><?php echo $currency; ?><br />(<?php echo $tva; ?>%)</td>
          <?php } ?>
          <td><?php echo $priceValueTTC; ?><?php echo $currency; ?></td>
        </tr>
        <?php
      }

      $title = __('Total_word', 'resa');
      if($isAdvancePayment){
        $title = __('Total_advance_payment_word', 'resa');
        $totalPriceTTC = $booking->getAdvancePayment();
      }
      $priceTVA = $totalPriceTTC - $totalPriceHT;
      $colspan = 5;
      if((isset($showStaff) && $showStaff || !isset($showStaff))) $colspan++;
      if(count($places) > 0) $colspan++;
      ?>
      <tr class="ro-total">
        <td colspan="<?php echo $colspan; ?>"><?php echo $title; ?></td>
        <?php if((isset($showTVA) && $showTVA || !isset($showTVA))){ ?>
        <td><?php echo $totalPriceHT; ?><?php echo $currency; ?></td>
        <td><?php echo $priceTVA; ?><?php echo $currency; ?></td>
        <?php } ?>
        <td><?php echo $totalPriceTTC; ?><?php echo $currency; ?></td>
      </tr>
      <?php
        if($booking->haveAdvancePayment() && !$isAdvancePayment){
          $colspan = 4;
          if((isset($showStaff) && $showStaff || !isset($showStaff))) $colspan ++;
          if(count($places) > 0) $colspan++;
          if((isset($showTVA) && $showTVA || !isset($showTVA)))
          $colspan += 2;
          ?>
          <tr>
            <td><?php _e('Advance_payment_words','resa') ?></td>
            <td colspan="<?php echo $colspan; ?>"></td>
            <td><?php echo $booking->getAdvancePayment(); ?><?php echo $currency; ?></td>
          </tr>
          <?php
        }
        $idPaymentsTypeToName = RESA_Variables::idPaymentsTypeToName(); //Names
        $colspan = 5;
        if((isset($showStaff) && $showStaff || !isset($showStaff))) $colspan ++;
        if(count($places) > 0) $colspan++;
        if((isset($showTVA) && $showTVA || !isset($showTVA)))
          $colspan += 2;
        foreach($booking->getPayments() as $payment){
          if($payment->isOk()){
            $title = __('Payment_of_words', 'resa');
            $value = $payment->getValue() * -1;
            if($payment->isRepayment()){
              $title = __('Repayment_of_words', 'resa');
              $value = $payment->getValue();
            }
            $totalPriceTTC += $value;
            $paymentDate = DateTime::createFromFormat('Y-m-d H:i:s', $payment->getPaymentDate());
            $title .= ' '.date_i18n(get_option('date_format'), $paymentDate->getTimestamp()).' '.  $paymentDate->format(get_option('time_format'));
            $title .= '('.$idPaymentsTypeToName[$payment->getTypePayment()].')';
            ?>
            <tr>
              <td colspan="<?php echo $colspan; ?>"><?php echo $title; ?></td>
              <td><?php echo $value; ?><?php echo $currency; ?></td>
            </tr>
            <?php
          }
        }
      ?>
      <tr class="ro-total">
        <td colspan="<?php echo $colspan; ?>"><?php _e('Remaining_to_pay_words', 'resa') ?></td>
        <td><?php echo $totalPriceTTC; ?><?php echo $currency; ?></td>
      </tr>
      <?php if(isset($displayForCustomer) && !$displayForCustomer && !empty($booking->getNote())){ ?>
        <tr><td colspan="<?php echo ($colspan + 1); ?>"><?php echo htmlspecialchars_decode($booking->getNote()); ?></td></tr>
      <?php } ?>
      <?php if(isset($displayForCustomer) && !$displayForCustomer && !empty($booking->getStaffNote())){ ?>
        <tr><td colspan="<?php echo ($colspan + 1); ?>"><?php _e('staff_note_words', 'resa'); ?> :<?php echo htmlspecialchars_decode($booking->getStaffNote()); ?></td></tr>
      <?php } ?>
      <?php if(!empty($booking->getPublicNote())){ ?>
        <tr><td colspan="<?php echo ($colspan + 1); ?>"><?php _e('note_word', 'resa'); ?> : <?php echo htmlspecialchars_decode($booking->getPublicNote()); ?></td></tr>
      <?php } ?>
  </tbody>
</table>
