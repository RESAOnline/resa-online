import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';

import { HtmlSpecialDecodePipe, ParseDate } from '../pipes/customPipes'

@Injectable({
  providedIn: 'root'
})
export class GlobalService {

  public fullScreen = false;
  public backendV2Activated = false;
  public wpDebugMode = false;
  public supportURL = '';
  public caisseOnline = {activated:false, caisse_url:'', api_caisse_url:'', license_id:'', site_url:''};
  public stripeConnect = {activated:false, stripeConnectId:'', apiStripeConnectUrl:''};
  public swiklyLink = '';
  public groupsManagement = false;
  public staffManagement = false;
  public equipmentsManagement = false;
  public currentLanguage = 'fr_FR';
  public languagesNames = {
    'de_DE':'Deutsh',
    'fr_FR':'Français',
    'nl_NL':'Nederlands',
    'en_GB':'English',
    'en_US':'English(US)'
  }
  public places = [];
  public filterSettings = null;
  public currentDate = new Date();

  constructor(private htmlSpecialDecodePipe:HtmlSpecialDecodePipe, private parseDatePipe:ParseDate){
    this.currentDate = this.clearTime(new Date());
  }

  setCurrentDate(date){
    this.currentDate = this.clearTime(date);
  }

  initializeFilterSettings(filterSettings, places){
    this.filterSettings = filterSettings;
    if(this.filterSettings.favPage == null) this.filterSettings.favPage = '/planningBookings';
    if(this.filterSettings.places == null) this.filterSettings.places = {};
    for(var i = 0; i < places.length; i++){
      var place = places[i];
      if(this.filterSettings.places[place.id] == null){
        this.filterSettings.places[place.id] = true;
      }
    }
  }


  parseDate(date:any){
    return this.parseDatePipe.transform(date);
  }

  setFavPage(favPage){
    this.filterSettings.favPage = favPage;
  }

  getCaisseOnlineLink(){
    return this.caisseOnline.caisse_url + '/login/' + this.caisseOnline.license_id;
  }

  isBrowserCompatibility(){
    var isChrome = navigator.userAgent.indexOf('Chrome') > -1 && navigator.userAgent.indexOf('Chromium') == -1;
    var isFirefox = navigator.userAgent.indexOf('Firefox') > -1 && navigator.userAgent.indexOf('Seamonkey') == -1;
    return isChrome || isFirefox;
	}

  getTextByLocale(object, locale = 'fr_FR'){
    var result = '';
    if(object != null){
      result = object[locale];
      if((result == null || result == '') && typeof object === "object" && object !== null){
        var languages = Object.keys(object);
        if(languages.length > 0){
            for(var i = 0; i < languages.length; i++){
              result = object[languages[i]];
              if(result != null && result != '')
                break;
            }
        }
        else result = '';
      }
    }
    return this.htmlSpecialDecode(result);
  }

  truncateString(defaultString:string, number:number){
    number = Math.min(number, defaultString.length);
    var suffix = '...';
    if(number == defaultString.length){
      suffix = '';
    }
    return defaultString.substring(0, number) + suffix;
  }

  htmlSpecialDecode(value, withoutBr = false){
    return this.htmlSpecialDecodePipe.transform(value, withoutBr);
  }

  htmlSpecialDecodeArray(array, withoutBr = false){
    for(var index in array) {
      array[index] =  this.htmlSpecialDecode(array[index], withoutBr);
    }
  }

  clearTime(date){
    var cloned = new Date(date);
    cloned.setHours(0);
    cloned.setMinutes(0);
    cloned.setSeconds(0);
    cloned.setMilliseconds(0);
    return cloned;
  }

  isSameDate(date1, date2){
    date1 = new Date(date1);
    date2 = new Date(date2);
    return date1.getFullYear() == date2.getFullYear() &&
      date1.getMonth() == date2.getMonth() &&
      date1.getDate() == date2.getDate();
  }

  copyInClipboard(text, successCallback = null){
    let selBox = document.createElement('textarea');
    selBox.style.position = 'fixed';
    selBox.style.left = '0';
    selBox.style.top = '0';
    selBox.style.opacity = '0';
    selBox.value = text;
    document.body.appendChild(selBox);
    selBox.focus();
    selBox.select();
    document.execCommand('copy');
    document.body.removeChild(selBox);
    if(successCallback != null){
        successCallback();
    }
  }


  isEmailValid(email){
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
  }

  generateNewId(array, field, prefix){
    var actual = [];
    if(array=='' ||	array==false){
      array = [];
    }
    else {
      for(var i = 0; i < array.length; i++){
        actual.push(array[i][field]);
      }
    }
    var index = 0;
    var idName = index;
    if(prefix.length > 0) idName = prefix + index;
    var found = true;
    while(found){
      index++;
      idName = index;
      if(prefix.length > 0) idName = prefix + index;
      found = actual.indexOf(idName) !=-1;
    }
    return idName;
  }

  round(value){
    return Math.round(value * 100) / 100;
  }

  getRepeat(num){
    return new Array(num);
  }

  printDiv(id){
    var content = document.getElementById(id).innerHTML;
    var newWindow = window.open ('','', "menubar=yes,scrollbars=yes,resizable=yes");
    newWindow.document.open();
    newWindow.document.write('<html><head><title></title><style>.modal-dialog{ width:100% !important; }</style></head><body>' + content + '</body></html>');
    var arrStyleSheets = document.getElementsByTagName("link");
    for (var i = 0; i < arrStyleSheets.length; i++){
      newWindow.document.head.appendChild(arrStyleSheets[i].cloneNode(true));
    }
    var arrStyle = document.getElementsByTagName("style");
    for (var i = 0; i < arrStyle.length; i++){
      newWindow.document.head.appendChild(arrStyle[i].cloneNode(true));
    }
    newWindow.document.close();
    newWindow.focus();
    setTimeout(function(){
      newWindow.print();
      newWindow.close(); },
      1000);
  }
}
