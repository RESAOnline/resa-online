import { Location } from '@angular/common';
import { Injectable, Inject } from '@angular/core';
import { Router } from '@angular/router';
import { ActivatedRoute } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class NavService {

  constructor( private router: Router, private route: ActivatedRoute, private location: Location){

  }

  changeRouteWithoutReload(url){
    this.location.go(url);
  }

  goToLink(url: string){
    window.open(url, "_blank");
  }

  changeRoute(route, query = null){
    if(query == null){
      this.router.navigate([route]);
    }
    else {
      this.router.navigate([route], { queryParams: query });
    }
  }

  getRoute(){
    return this.router.url;
  }

  isNoRoute(){
    return this.router.url === '/'  || this.isSameUrl('/?');
  }

  isSameUrl(url){
    return this.router.url.indexOf(url)!=-1;
  }

  getToken(callback){
    this.route.queryParams.subscribe(params => {
      callback(params.token);
    });
  }

  generateServerRESA(){
    if(window.location.href.indexOf('wp-admin') == -1){
      return window.location.protocol + '//'+ window.location.host;
    }
    else {
      return window.location.href.substring(0, window.location.href.indexOf("wp-admin") - 1);
    }
  }

  reload(){
    location.reload();
  }

  clearQueryParams(){
    this.location.go(this.router.url.split('?')[0]);
  }

}
