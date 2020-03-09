import { Input, OnInit, Component  } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { GlobalService } from '../../services/global.service';
import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';

import { DatePipe, formatDate } from '@angular/common';

declare var swal: any;
declare var jQuery: any;

@Component({
  selector: 'resaNews',
  templateUrl: 'resaNews.html',
  styleUrls:['./resaNews.css']
})

export class RESANewsComponent implements OnInit {

  RESANews = [];
  RESANewsLaunched = false;
  RESANewsDisplayed = false;

  @Input() RESANewsLastViewNumber = -1;
  RESANewsWaitingNumber = 0;

  constructor(public global:GlobalService, private userService:UserService, private navService:NavService, private modalService: NgbModal) {

  }

  ngOnInit(): void {
    this.getRESANews(1, 10);
  }

  RESANewsIsOpened(){
    return this.RESANewsDisplayed;
  }

  openRESANews(event){
    this.RESANewsDisplayed = true;
    this.seeAllRESANews();
    event.stopPropagation();

    jQuery('body').click(() => { this.cloneRESANews() });
    jQuery('#resa_news_center_content').click((event) => { event.stopPropagation(); });
  }

  cloneRESANews(){
    this.RESANewsDisplayed = false;
    jQuery('body').unbind('click');
    jQuery('#resa_news_center_content').unbind('click');
  }

  switchRESANewsCenter(event){
    if(this.RESANewsIsOpened()){
      this.cloneRESANews();
    }
    else {
      this.openRESANews(event);
    }
  }

  calculateNotView(){
    var number = 0;
    for(var i = 0; i < this.RESANews.length; i++){
      var news = this.RESANews[i];
      if(news.id == this.RESANewsLastViewNumber){
        break;
      }
      number++;
    }
    this.RESANewsWaitingNumber = number;
  }

  getRESANews(page, perPage){
    if(!this.RESANewsLaunched){
      this.RESANewsLaunched = true;
      this.userService.getGlobal('https://resa-online.fr/wp-json/wp/v2/posts?page=' + page + '&perPage=' + perPage+'&categories=7', (data) => {
        this.RESANewsLaunched = false;
        this.RESANews = this.RESANews.concat(data);
        this.calculateNotView();
      }, (error) => {

      });
    }
  }

  seeAllRESANews(){
    if(this.RESANewsWaitingNumber > 0 && this.RESANews.length > 0){
      this.userService.post('seeAllRESANews/:token', {lastId:this.RESANews[0].id}, (data) => {
        this.RESANewsWaitingNumber = 0;
      }, (error) => {
        var text = 'Impossible de récupérer les news depuis le site resa-online';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
  }

}
