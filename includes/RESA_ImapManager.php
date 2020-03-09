<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Implement the hook for GDPR
 */
class RESA_ImapManager
{

  private static $_instance = null;
  private $allMailbox;
  private $activate;


  public static function getInstance($force = false)
  {
    if(is_null(self::$_instance) || $force) {
      self::$_instance = new RESA_ImapManager();
    }
    return self::$_instance;
  }

  private function __construct()
	{
    $this->activate = get_option('resa_settings_mailbox_activated', 0);
    $allMailboxSaved = unserialize(get_option('resa_settings_mailbox', serialize(array())));
    $this->allMailbox = array();
    foreach($allMailboxSaved as $mailbox){
      if(!isset($mailbox->activated) || $mailbox->activated || (count($allMailboxSaved) == 1 && $this->activate)){
        array_push($this->allMailbox, $mailbox);
      }
    }
  }

  public function processEmails(){
    Logger::DEBUG('begin : ' . ($this->activate?'yes':'no') );
    if($this->activate){
      foreach($this->allMailbox as $currentMailbox){
        Logger::DEBUG('begin : ' . $currentMailbox->login);
        $realURL = '{'.$currentMailbox->url.':'.$currentMailbox->port.'/imap/ssl/novalidate-cert}INBOX';
        try {
          $dir = wp_get_upload_dir()['basedir'] . '/resa_attachments';
          if (!is_dir($dir)) {
            mkdir($dir);
          }
          $mailbox = new PhpImap\Mailbox($realURL , $currentMailbox->login, $currentMailbox->password, $dir);
          $mailbox->setTimeouts(5);
          $maxExternalID = RESA_EmailCustomer::getMaxExternalId($currentMailbox->url.':'.$currentMailbox->login);
          $dateStr = RESA_EmailCustomer::getMaxDateForExternalId($currentMailbox->url.':'.$currentMailbox->login);
          $searchRequest = 'ALL';
          if(!empty($dateStr)) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateStr);
            $searchRequest = 'SINCE "'.$date->format('d-M-Y').'"';
          }
          else {
            $date = new DateTime();
            $searchRequest = 'SINCE "'.$date->format('d-M-Y').'"';
          }
          $mailsIds = $mailbox->searchMailbox($searchRequest);
          if($mailsIds) {
            foreach($mailsIds as $mailId){
              if($mailId > $maxExternalID){
                $header = $mailbox->getMailHeader($mailId);
                //Logger::DEBUG(print_r($header->fromAddress, true));
                $customer = new RESA_Customer();
                $customer->loadByEmail($header->fromAddress);
                if($customer->isLoaded()){
                  $mail = $mailbox->getMail($mailId);
                  //echo '<br />'. $customer->getId();
                  //print_r($mail);
                  //echo $mail->id . ' ' . $mail->subject .' ' . $mail->textHtml.' ' . $mail->fromAddress . ' ' . $mail->date;
                  $textHtml = $mail->textHtml;
                  $textHtml = preg_replace('/\r\n|\r|\n/', ' ', $textHtml);
                  $textHtml = preg_replace('/\\\/', '\\\\\\', $textHtml);
                  $textHtml = preg_replace('/\t/', '', $textHtml);
                  $emailCustomer = RESA_EmailCustomer::generate($customer->getId(), -1, $customer->getId(), $header->fromAddress, $mail->subject, $textHtml);
                  $emailCustomer->setExternalId($mail->id);
                  $emailCustomer->setMailbox($currentMailbox->url.':'.$currentMailbox->login);
                  $emailCustomer->setCreationDate($mail->date);
                  $attachments = array();
                  foreach($mail->getAttachments() as $attachment){
                    $name = basename($attachment->filePath);
                    $filePath = wp_get_upload_dir()['baseurl'] . '/resa_attachments/' . $name;
                    array_push($attachments, array('id' => $attachment->id, 'name' => esc_html($attachment->name), 'filePath' => $filePath));
                  }
                  $emailCustomer->setAttachments($attachments);
                  $emailCustomer->save();
                  if($emailCustomer->isLoaded()){
            				$logNotification = RESA_Algorithms::generateLogNotification(20, new RESA_Booking(), $customer, $customer);
                    if(isset($currentMailbox->idPlace)) $logNotification->addIdPlaces(array($currentMailbox->idPlace));
            				if(isset($logNotification))	$logNotification->save();
                  }
                  else {
                    $emailCustomer->setMessage('<b><i>Message trop volumineux, veuillez accéder à votre boite mail pour le consulter</i></b>');
                    $emailCustomer->save();
                    if($emailCustomer->isLoaded()){
              				$logNotification = RESA_Algorithms::generateLogNotification(20, new RESA_Booking(), $customer, $customer);
                      $logNotification->addIdPlaces(array($currentMailbox->idPlace));
              				if(isset($logNotification))	$logNotification->save();
                    }
                  }
                }
              }
            }
          }
          $mailbox->disconnect();
        }
        catch(Exception $e){
        }
      }
    }
  }

  public function allConnected(){
    $connectedArray = array();
    if($this->activate){
      foreach($this->allMailbox as $currentMailbox){
        try {
          $realURL = '{'.$currentMailbox->url.':'.$currentMailbox->port.'/imap/ssl/novalidate-cert}INBOX';
          $dir = wp_get_upload_dir()['basedir'] . '/resa_attachments';
          if (!is_dir($dir)) {
            mkdir($dir);
          }
          $mailbox = new PhpImap\Mailbox($realURL , $currentMailbox->login , $currentMailbox->password, $dir);
          $mailbox->setTimeouts(5);
          $mailbox->checkMailbox();
          array_push($connectedArray, true);
          $mailbox->disconnect();
        }
        catch(Exception $e){
          array_push($connectedArray, $e->getMessage());
        }
      }
    }
    return $connectedArray;
  }


}
