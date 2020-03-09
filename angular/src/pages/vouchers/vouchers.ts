import { Component, OnInit  } from '@angular/core';
import { NgModel } from '@angular/forms';
import { formatDate } from '@angular/common';
import { NgbModal, NgbDate } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute } from '@angular/router';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';
import { BookingDialogComponent } from '../../components/bookingDialog/bookingDialog';

declare var swal: any;

@Component({
  selector: 'page-vouchers',
  templateUrl: 'vouchers.html',
  styleUrls:['./vouchers.css']
})

export class VouchersComponent extends ComponentCanDeactivate implements OnInit {

  public promoCodes = [];
  public settings = null;
  public bookings = [];
  public promoCodeLoaded = '';

  public promoCode:string = 'all';
  public loadVouchers:boolean = false;
  public loadBookingVouchers:boolean = false;
  public loadBookingLaunch:boolean = false;
  public vouchersForm = {
    startDate:this.toNgbDate(new Date()),
    endDate:this.toNgbDate(new Date()),
    limit : 10,
    page : 1,
    nbTotal: 0
  };

  constructor(private userService:UserService, private navService:NavService, private global:GlobalService,
    private route: ActivatedRoute, private modalService: NgbModal) {
    super('Coupons');
  }

  ngOnInit(): void {
    this.launchLoadVouchers();
  }

  launchLoadVouchers(){
    if(!this.loadVouchers){
      this.loadVouchers = true;
      this.userService.get('promoCodes/:token', function(data){
        this.loadVouchers = false;
        this.promoCodes = data.promoCodes;
        this.settings = data.settings;
        if(data.startDate != '' && data.endDate != ''){
          this.vouchersForm.startDate = this.toNgbDate(new Date(data.startDate));
          this.vouchersForm.endDate = this.toNgbDate(new Date(data.endDate));
        }
        var voucher = this.route.snapshot.paramMap.get('voucher');
        if(voucher != null && voucher.length > 0 && this.promoCodes.indexOf(voucher) > -1){
          this.promoCode = voucher;
          this.bookingsPage = 0;
          this.launchLoadBookings();
        }
      }.bind(this), function(error){
        var text = 'Impossible de récupérer les données des activitées';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }

  toDate(ngbDate:NgbDate):Date{
    return new Date(ngbDate.year, ngbDate.month - 1, ngbDate.day, 0, 0, 0, 0);
  }

  toNgbDate(date:Date):NgbDate{
    return new NgbDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
  }

  changeStartDate(){
    var startDate = this.toDate(this.vouchersForm.startDate);
    var endDate = this.toDate(this.vouchersForm.endDate);
    if(startDate > endDate){
      this.vouchersForm.endDate = this.toNgbDate(startDate);
    }
  }

  changeEndDate(){
    var startDate = this.toDate(this.vouchersForm.startDate);
    var endDate = this.toDate(this.vouchersForm.endDate);
    if(startDate > endDate){
      this.vouchersForm.startDate = this.toNgbDate(endDate);
    }
  }

  setPromoCode(promoCode, launch){
    this.promoCode = promoCode;
    this.vouchersForm.page = 0;
    if(launch){
      this.launchLoadBookings();
    }
  }

  launchLoadBookings(){
    if(!this.loadBookingVouchers){
      var starDate = formatDate(this.toDate(this.vouchersForm.startDate), 'yyyy-MM-dd', 'fr');
      var endDate = formatDate(this.toDate(this.vouchersForm.endDate), 'yyyy-MM-dd', 'fr');
      var promoCode = this.promoCode;
      this.loadBookingVouchers = true;
      this.userService.post('promoCodesBookings/:token', {
        promoCode:this.promoCode,
        page:this.vouchersForm.page,
        limit:this.vouchersForm.limit,
        startDate:starDate,
        endDate:endDate
      }, function(data){
        this.loadBookingVouchers = false;
        this.promoCodeLoaded = promoCode;
        this.bookings = data.bookings;
        if(this.vouchersForm.page == 1) this.vouchersForm.nbTotal = data.nbTotal;
      }.bind(this), function(error){
        var text = 'Impossible de récupérer les données des activitées';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));1
    }
  }

  loadBooking(id){
    if(!this.loadBookingLaunch){
      this.loadBookingLaunch = true;
      this.userService.get('booking/:token/' + id, (booking) => {
        this.loadBookingLaunch = false;
        const modalRef = this.modalService.open(BookingDialogComponent, { windowClass: 'mlg' });
        modalRef.componentInstance.setBookingAndSettings(booking, this.settings);
      }, (error) => {
        this.loadBookingLaunch = false;
        var text = 'Impossible de récupérer les données de la réservation';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
  }

  canDeactivate():boolean{ return true; }

  goCustomer(id){ this.navService.changeRoute('customer/' + id); }

  getStatusName(status, devis){
    if(devis) return 'Devis';
    if(status == 'ok') return 'Validée';
    if(status == 'waiting') return 'En attente';
    if(status == 'cancelled' || status == 'abandonned') return 'Annulée';
  }

  getPaymentStateName(status, state){
    if(status == 'cancelled'|| status == 'abandonned') return '?????';
    if(state == 'advancePayment') return 'Acompte';
    if(state == 'complete') return 'Encaissé';
    if(state == 'noPayment') return 'Pas de paiement';
  }

  getLinkCustomerPage(idCustomer){
    return this.userService.getCurrentHost() + '/wp-admin/admin.php?page=resa_appointments&view=display&subPage=clients&id=' + idCustomer;
  }

}
