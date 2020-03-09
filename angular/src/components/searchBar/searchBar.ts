import { OnInit, Component } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { GlobalService } from '../../services/global.service';
import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { BookingDialogComponent } from '../../components/bookingDialog/bookingDialog';

import { DatePipe, formatDate } from '@angular/common';

import { Observable, Subject } from 'rxjs';
import { debounceTime, distinctUntilChanged, map } from 'rxjs/operators';


declare var swal: any;
declare var jQuery: any;

@Component({
  selector: 'searchBar',
  templateUrl: 'searchBar.html',
  styleUrls:['./searchBar.css']
})

export class SearchBarComponent implements OnInit {

  settings = null;
  searchInputValue = '';
  searchResults = null;
  displayResults:boolean = false;
  launchAction:boolean = false;
  searchInputUpdate = new Subject<string>();

  constructor(public global:GlobalService, private userService:UserService, private navService:NavService, private modalService: NgbModal) {
    this.searchInputUpdate.pipe(
      debounceTime(500),
      distinctUntilChanged())
      .subscribe(value => {
        this.search();
      });
  }

  ngOnInit(): void {
    this.firstLoad();
  }

  firstLoad():void {
    this.userService.get('bookingsSettings/:token', (data) => {
      this.settings = data.settings;
    }, (error) => {
      var text = 'Impossible de récupérer les données de la recherche';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  isEmptyResult(){
    return this.searchResults!=null && Object.keys(this.searchResults).length == 0;
  }

  close(){
    this.displayResults = false;
    jQuery('body').unbind('click');
    jQuery('#result-block').unbind('click');
    jQuery('#search-input').unbind('click');
  }

  open(){
    if(!this.displayResults && this.searchInputValue.length > 0){
      this.displayResults = true;
      jQuery('body').click(() => { this.close() });
      jQuery('#result-block').click((event) => { event.stopPropagation(); });
      jQuery('#search-input').click((event) => { event.stopPropagation(); });
    }
  }

  goCustomer(id){ this.close(); this.navService.changeRoute('customer/' + id); }
  goEditBooking(booking){ this.close(); this.navService.changeRoute('planningBookings', {booking:booking.id}); }

  openBooking(booking){
    this.launchAction = true;
    this.userService.get('booking/:token/' + booking.id, (booking) => {
      this.launchAction = false;
      this.close();
      const modalRef = this.modalService.open(BookingDialogComponent, { windowClass: 'mlg' });
      modalRef.componentInstance.setBookingAndSettings(booking, this.settings);
    }, (error) => {
      this.launchAction = false;
      var text = 'Impossible de récupérer les données de la réservation';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  search(){
    if(this.searchInputValue.length > 0){
      this.launchAction = true;
      this.userService.post('search/:token', {value:this.searchInputValue}, (data) => {
        this.open();
        this.searchResults = data;
        this.launchAction = false;
      }, (error) => {
        this.launchAction = false;
        var text = 'Impossible de récupérer les résultats de la recherche';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
    else {
      this.searchResults = null;
      this.close();
    }
  }



}
