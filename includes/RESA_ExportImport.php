<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class RESA_ExportImport
{
  /**
   * add new line
   */
  public static function ln($number = 1){
    $result = '';
    for($i = 0; $i < $number; $i++){
      $result .= "\r\n";
    }
    return $result;
  }

  public static function exportRESAData(){
    global $wpdb;
    $sqlFile = '-- ' . site_url() . self::ln(3);
    $sqlFile .= '-- ' . $wpdb->prefix . self::ln(3);
    foreach(RESA_InstallManager::$allEntities as $entity){
      $exportString = self::exportSQLTable($entity::getTableName());
      if(!empty($exportString)){
        $sqlFile .= $exportString. self::ln(3);
      }
    }
    $sqlFile .=  self::exportRESAOptions(). self::ln(3);
    $sqlFile .=  self::exportSQLTable($wpdb->prefix . 'users'). self::ln(3);
    $sqlFile .=  self::exportSQLTable($wpdb->prefix . 'usermeta'). self::ln(3);
    return $sqlFile;
  }

  /**
   * export resa options
   */
  public static function exportRESAOptions(){
    global $wpdb;
    $installManager = new RESA_InstallManager();
    $properties = array_keys($installManager->options);
    $sqlTable = $wpdb->prefix.'options';
    $result = $wpdb->get_results('SELECT * FROM ' . $sqlTable . ' WHERE option_name IN ("' . implode('","', $properties) . '")');
    return self::exportTableResult($sqlTable, $result, true);
  }


  /**
   * export sql table
   */
  public static function exportSQLTable($sqlTable){
    global $wpdb;
    $result = $wpdb->get_results('SELECT * FROM ' . $sqlTable);
    return self::exportTableResult($sqlTable, $result);
  }

  /**
   *
   */
  public static function exportTableResult($sqlTable, $result, $clearId = false){
    $st_counter = 0;
    $content = '';
    foreach($result as $tuple){
      $properties = array_keys(get_object_vars($tuple));
      if ($st_counter%100 == 0 || $st_counter == 0 ) {
        if($st_counter > 0){
          $content .= self::ln(3);
        }
        $content .= "INSERT INTO ".$sqlTable." (" . implode(',', $properties) . ") VALUES";
      }
      $content .= self::ln() . '(';
      for($i = 0; $i < count($properties); $i++) {
        $value = $tuple->$properties[$i];
        if (isset($value) && (($properties[$i] != 'id' && $properties[$i] != 'option_id') || !$clearId)) {
          $content .= '\'' . addslashes($value) . '\'' ;
        }
        else if($clearId && ($properties[$i] == 'id' || $properties[$i] == 'option_id')){
          $content .= 'NULL';
        }
        else {
          $content .= '\'\'';
        }
        if ($i < (count($properties) - 1)) {
          $content.= ',';
        }
      }
      $content .=")";
      if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1 == count($result)) {
        $content .= ';';
      }
      else {
        $content .= ",";
      }
      $st_counter = $st_counter + 1;
    }
    return $content;
  }

  public static function importRESAData($contentFile){
    global $wpdb;
    $array = explode(self::ln(3), $contentFile);

    $oldSiteUrl = substr($array[0], 3, strlen($array[0]));
    $newSiteUrl = site_url();
    $oldPrefix = substr($array[1], 3, strlen($array[1]));
    $newPrefix = $wpdb->prefix;

    (new RESA_InstallManager())->removeOptions();
    $wpdb->query('TRUNCATE TABLE ' . RESA_Form::getTableName());
    $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'users');
    $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'usermeta');
    foreach($array as $value){
      if(substr( $value, 0, strlen('INSERT')) == 'INSERT'){
        $value = str_replace('INSERT INTO ' . $oldPrefix, 'INSERT INTO ' . $newPrefix, $value);
        $wpdb->query($value);
      }
    }

    //Update image
    $results = $wpdb->get_results('SELECT id, image FROM ' . RESA_Service::getTableName());
    foreach($results as $result){
      $result->image = unserialize($result->image);
      foreach($result->image as $key => $value){
        $result->image->$key = str_replace($oldSiteUrl, $newSiteUrl, $result->image->$key);
      }
      $wpdb->query('UPDATE '.RESA_Service::getTableName().' SET image=\''.serialize($result->image).'\' WHERE id='. $result->id);
    }

    //link in customer email
    $results = $wpdb->get_results('SELECT id, message, attachments FROM ' . RESA_EmailCustomer::getTableName());
    foreach($results as $result){
      $result->message = str_replace($oldSiteUrl, $newSiteUrl, $result->message);
      $result->attachments = unserialize($result->attachments);
      for($i = 0; $i < count($result->attachments); $i++){
        $result->attachments[$i] = str_replace($oldSiteUrl, $newSiteUrl, $result->attachments[$i]);
      }
      $result->attachments = serialize($result->attachments);
      $wpdb->query('UPDATE '.RESA_EmailCustomer::getTableName().' SET message=\''.$result->message.'\', attachments=\''.$result->attachments.'\' WHERE id='. $result->id);
    }

    $results = unserialize(get_option('resa_settings_notifications_custom_shortcodes'));
    foreach($results as $result){
      foreach($result as $key => $value){
        $result->$key = str_replace($oldSiteUrl, $newSiteUrl, $result->$key);
      }
    }
    update_option('resa_settings_notifications_custom_shortcodes', serialize($results));

    //Update anothers
    $results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix .'options WHERE `option_value` LIKE \'%'.$oldSiteUrl.'%\'');
    foreach($results as $result){
      $data = @unserialize($result->option_value);
      if ($data !== false) {
        $result->option_value = unserialize($result->option_value);
        foreach($result->option_value as $key => $value){
          $result->$key = str_replace($oldSiteUrl, $newSiteUrl, $result->$key);
        }
        $result->option_value = serialize($result->option_value);
        $wpdb->query('UPDATE '.$wpdb->prefix .'options SET option_value=\''.$result->option_value.'\' WHERE option_id='. $result->option_id);
      } else {
        $result->option_value = str_replace($oldSiteUrl, $newSiteUrl, $result->option_value);
        $wpdb->query('UPDATE '.$wpdb->prefix .'options SET option_value=\''.$result->option_value.'\' WHERE option_id='. $result->option_id);
      }
    }
  }

}
