import { CanDeactivate } from '@angular/router';

import { ComponentCanDeactivate } from './ComponentCanDeactivate';

export class CanDeactivateGuard implements CanDeactivate<ComponentCanDeactivate> {
  canDeactivate(component: ComponentCanDeactivate): boolean {
    if(!component.canDeactivate()){
      if (confirm("Les modifications que vous avez apportées ne seront peut-être pas enregistrées.")) {
        component.resetTitle();
        return true;
      } else {
        return false;
      }
    }
    return true;
  }
}
