import { Input, Component } from '@angular/core';

import { NavService } from '../../services/nav.service';


@Component({
  selector: 'tutoButton',
  templateUrl: 'tutoButton.html',
  styleUrls:['./tutoButton.css']
})

export class TutoButtonComponent {

  @Input() url:string = '';
  constructor(private navService:NavService) {

  }

  goToLink(){
    this.navService.goToLink(this.url);
  }
}
