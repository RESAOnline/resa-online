import { OnInit, Component  } from '@angular/core';
import { NgbTabChangeEvent } from '@ng-bootstrap/ng-bootstrap';
import { DatePipe, formatDate } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';

declare var swal: any;

@Component({
  selector: 'quotationsList',
  templateUrl: 'quotationsList.html',
  styleUrls:['./quotationsList.css']
})

export class QuotationsListComponent extends ComponentCanDeactivate implements OnInit {

  public settings = [];
  public bookings = [];
  public maxBookings = 0;
  public launchAction:boolean = false;
  public searchForm = {
    nbByPage:10,
    page:1
  };


  constructor(public global:GlobalService, private userService:UserService, private navService:NavService, private modalService: NgbModal) {
    super('Devis');
  }

  ngOnInit(): void {
    this.launchAction = true;
    this.userService.get('bookingsSettings/:token', function(data){
      this.settings = data.settings;
      this.launchAction = false;
      this.loadData();
    }.bind(this), function(error){
      this.launchAction = false;
      var text = 'Impossible de récupérer les données des réservations';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }
  canDeactivate(){ return true; }
  goEditBooking(booking){ this.navService.changeRoute('planningBookings', {booking:booking.id}); }

  loadData(){
    this.launchAction = true;
    this.userService.post('quotations/:token', {nbByPage:this.searchForm.nbByPage, page:this.searchForm.page - 1 }, (data) => {
      this.bookings = data.bookings;
      this.maxBookings = data.max;
      this.launchAction = false;
    }, (error) => {
      this.launchAction = false;
      var text = 'Impossible de récupérer les données des réservations';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  updateDate(date){
    this.loadData();
  }



}
