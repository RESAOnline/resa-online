import { Input, Output, Component, ViewChild, AfterViewInit   } from '@angular/core';
import { NgbModal, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { BookingComponent } from "../booking/booking";

@Component({
  selector: 'bookingDialog',
  templateUrl: 'bookingDialog.html',
  styleUrls:['./bookingDialog.css']
})

export class BookingDialogComponent {

  @ViewChild(BookingComponent, {static: true}) bookingComponent;

  @Input() booking = null;
  @Input() settings = null;
  @Input() displayButtons = {};

  constructor(
    private modalService: NgbModal,
    private activeModal: NgbActiveModal
  ) {

  }

  setBookingAndSettings(booking, settings){
    this.booking = booking;
    this.settings = settings;
    this.bookingComponent.booking = booking;
    this.bookingComponent.settings = settings;
    this.bookingComponent.getPaymentsForBooking(this.booking);
    this.bookingComponent.getStripeChargeForTransactionId(this.booking);
  }

  close(){
    this.activeModal.close()
  }
}
