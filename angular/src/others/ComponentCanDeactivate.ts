import { HostListener } from "@angular/core";

export abstract class ComponentCanDeactivate {

  private titleChanged = false;
  private lastTitle = '';
  abstract canDeactivate(): boolean;

  constructor(private title:string){
    window.document.title = title;
  }

  @HostListener('window:beforeunload', ['$event'])
  unloadNotification($event: any) {
      if (!this.canDeactivate()) {
          $event.returnValue =true;
      }
  }

  changeTitle(){
    if(!this.titleChanged){
      this.lastTitle = window.document.title;
      window.document.title = '*' + this.lastTitle;
      this.titleChanged = true;
    }
  }

  resetTitle(){
    if(this.titleChanged){
      window.document.title = this.lastTitle;
      this.titleChanged = false;
    }
  }
}
