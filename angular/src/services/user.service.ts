import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';

import { StorageService } from './storage.service';
import { GlobalService } from './global.service';


@Injectable({
  providedIn: 'root'
})
export class UserService extends StorageService {

  private currentHost = 'http://openresa/';
  private serverRESA = '';
  private token:string = null;
  private user = null;
  private headers = new HttpHeaders({
    'Content-Type': 'application/json; charset=UTF-8',
  });

  constructor(private http: HttpClient, private global:GlobalService) {
    super();
    this.initializeLoad();
    this.setServerRESA(this.currentHost);
  }


  initializeLoad(){
    var object = this.load();
    if(object && object.token){
      this.token = object.token;
    }
  }

  saveTokenInStorage(){
    this.saveData('token', this.token);
  }

  setServerRESA(currentHost){
    if(currentHost != 'http://localhost:4200'){
      this.currentHost = currentHost;
      this.serverRESA = this.currentHost + '/wp-json/resa/v1/';
    }
  }

  isUserNull(){ return this.user == null; }
  getCurrentUser(){ return this.user; }
  getCurrentHost(){ return this.currentHost; }

  getUserByToken(token:string, successCallback, errorCallback){
    if(token != null || this.token != null) {
      if(token == null && this.token != null){
        token = this.token;
      }
      this.http.get(this.serverRESA + 'user/' + token)
      .subscribe((data:any) => {
        this.token = token;
        this.saveTokenInStorage();
        this.user = data.user;
        if(successCallback != null){
          successCallback(data);
        }
      }, error => {
        if(errorCallback != null && error.error != null){
          errorCallback(error.error);
        }
        else {
          errorCallback({message:'error'});
        }
      });
    }
  }

  isRESAManager(){
    return this.user.role == 'administrator' || this.user.role == 'RESA_Manager';
  }

  displayOnlyPlanningGroups(){
    return this.user.role == 'RESA_Staff' && !this.user.permissions.display_bookings_tab;
  }




/*****************************************************************************************
********************************************************************************************
****************************************************************************************/

  getUrl(url){
    url = url.replace(':token', this.token);
    return this.serverRESA + url;
  }

  /**
   * do get http method
   */
  get(url, successCallback, errorCallback){
    if(!this.isUserNull()) {
      this.http.get(this.getUrl(url))
      .subscribe(data => {
        if(successCallback != null){
          successCallback(data);
        }
      }, error => {
        if(errorCallback != null && error.error != null){
          errorCallback(error.error);
        }
        else {
          errorCallback({message:'error'});
        }
      });
    }
  }

  /**
   * do get http method
   */
  getGlobal(url, successCallback, errorCallback){
    this.http.get(url)
    .subscribe(data => {
      if(successCallback != null){
        successCallback(data);
      }
    }, error => {
      if(errorCallback != null && error.error != null){
        errorCallback(error.error);
      }
      else {
        errorCallback({message:'error'});
      }
    });
  }

  /**
   * do post http method
   */
  put(url, data, successCallback, errorCallback){
    if(!this.isUserNull() && data != null) {
      this.http.put(this.getUrl(url), data, {
        headers: this.headers
      })
      .subscribe(data => {
        if(successCallback != null){
          successCallback(data);
        }
      }, error => {
        if(errorCallback != null && error.error != null){
          errorCallback(error.error);
        }
        else {
          errorCallback({message:'error'});
        }
      });
    }
  }

  /**
   * do post http method
   */
  delete(url, successCallback, errorCallback){
    if(!this.isUserNull()) {
      this.http.delete(this.getUrl(url), {
        headers: this.headers
      })
      .subscribe(data => {
        if(successCallback != null){
          successCallback(data);
        }
      }, error => {
        if(errorCallback != null && error.error != null){
          errorCallback(error.error);
        }
        else {
          errorCallback({message:'error'});
        }
      });
    }
  }

  /**
   * do post http method
   */
  post(url, data, successCallback, errorCallback){
    this.postWithHeaders(url, data, this.headers, successCallback, errorCallback);
  }

  /**
   * do post http method
   */
  postGlobal(url, data, successCallback, errorCallback){
    this.postGlobalWithHeaders(url, data, this.headers, successCallback, errorCallback);
  }

  /**
   * generate http headers
   */
  generateHttpHeaders(json){
    return new HttpHeaders(json);
  }

  /**
   * do post http method
   */
  postWithHeaders(url, data, headers, successCallback, errorCallback){
    if(!this.isUserNull() && data != null) {
      this.http.post(this.getUrl(url), data, {
        headers: headers
      })
      .subscribe(data => {
        if(successCallback != null){
          successCallback(data);
        }
      }, error => {
        if(errorCallback != null && error.error != null){
          errorCallback(error.error);
        }
        else {
          errorCallback({message:'error'});
        }
      });
    }
  }

  /**
   * do post http method
   */
  postGlobalWithHeaders(url, data, headers, successCallback, errorCallback){
    if(!this.isUserNull() && data != null) {
      url = url.replace(':token', this.token);
      this.http.post(url, data, {
        headers: headers
      })
      .subscribe(data => {
        if(successCallback != null){
          successCallback(data);
        }
      }, error => {
        if(errorCallback != null && error.error != null){
          errorCallback(error.error);
        }
        else {
          errorCallback({message:'error'});
        }
      });
    }
  }

  /**
   * do get http method
   */
  getGlobalWithHeaders(url, headers, successCallback, errorCallback){
    this.http.get(url, headers)
    .subscribe(data => {
      if(successCallback != null){
        successCallback(data);
      }
    }, error => {
      if(errorCallback != null && error.error != null){
        errorCallback(error.error);
      }
      else {
        errorCallback({message:'error'});
      }
    });
  }


}
