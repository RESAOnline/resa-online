import { Component, OnInit  } from '@angular/core';
import { NgModel } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { Subject } from 'rxjs';
import { debounceTime  } from 'rxjs/operators';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';

declare var swal: any;

@Component({
  selector: 'page-customers',
  templateUrl: 'customers.html',
  styleUrls:['./customers.css']
})

export class CustomersComponent extends ComponentCanDeactivate implements OnInit {

  public settings = null;
  public customers = [];
  public launchAction:boolean = false;
  public searchForm = {search:'', limit:10, page:1};
  public nbTotalCustomers = 0;
  private searchUpdated: Subject<string> = new Subject<string>();
  private skeletonCustomer = null;
  public modelCustomer = null;
  public editionMode = false;

  private modalExportCustomers = null;
  private allNbTotalCustomers = 0;
  private currentPurcent = 0;

  constructor(private userService:UserService, private navService:NavService, private global:GlobalService, private modalService: NgbModal) {
    super('Clients');
    this.searchUpdated.pipe(debounceTime(1000)).subscribe(text => { this.searchCustomers() });
  }

  ngOnInit(): void {
    this.userService.get('customersSettings/:token', function(data){
      this.settings = data.settings;
      this.skeletonCustomer = data.skeletonCustomer;
      this.clearModelCustomer();
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des clients';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
    this.searchCustomers();
  }

  goCustomer(id){ this.navService.changeRoute('customer/' + id); }

  clearModelCustomer(){
    this.modelCustomer = JSON.parse(JSON.stringify(this.skeletonCustomer));
    this.modelCustomer.createWpAccount = true;
    this.modelCustomer.notify = true;
  }

  searchCustomers(){
    this.launchAction = true;
    this.userService.post('customers/:token', {search:this.searchForm.search, limit:this.searchForm.limit, page:this.searchForm.page}, function(data){
      this.launchAction = false;
      this.customers = data.customers;
      this.nbTotalCustomers = data.nbTotalCustomers;
    }.bind(this), function(error){
      this.launchAction = false;
      var text = 'Impossible de récupérer les données des clients';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  searchCustomerChanged(){
    this.searchUpdated.next();
  }

  canDeactivate(){ return true; }

  getCurrentUser(){
    return this.userService.getCurrentUser();
  }

  isSeeSettings(){
    return this.getCurrentUser().seeSettings || this.getCurrentUser().role == 'administrator';
  }

  isRESAManager(customer){
    return customer.role == 'RESA_Manager';
  }

  getTypeAccountName(customer){
    var typeAccountName = '';
    if(customer != null && this.settings != null){
      if(customer.role == 'administrator') typeAccountName = 'Administrateur';
      else if(customer.role == 'RESA_Manager') typeAccountName = 'Manageur RESA';
      else if(customer.role == 'RESA_Staff') typeAccountName = this.global.getTextByLocale(this.settings.staff_word_single);
      else {
        var typeAccount = this.settings.typesAccounts.find(element => {
          if(element.id == customer.typeAccount) return element;
        });
        if(typeAccount){
          typeAccountName = this.global.getTextByLocale(typeAccount.name);
        }
      }
    }
    return typeAccountName;
  }

  isValidCustomer(){
    return this.modelCustomer!=null && ((!this.modelCustomer.createWpAccount && this.modelCustomer.phone != null && this.modelCustomer.phone.length > 0) || (this.modelCustomer.createWpAccount && this.modelCustomer.email != null && this.modelCustomer.email.length > 0)) && !this.launchAction;
  }

  openEditCustomerDialog(content){
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result!=null){
        this.launchAction = true;
        this.userService.post('customer/:token', {customer:JSON.stringify(this.modelCustomer)}, function(data){
          this.goCustomer(data.customer.ID);
        }.bind(this), function(error){
          this.actionLaunch = false;
          var text = 'Impossible de récupérer les données des activitées';
          if(error != null && error.message != null && error.message.length > 0){
            text += ' (' + error.message + ')';
          }
          swal({ title: 'Erreur', text: text, icon: 'error'});
        }.bind(this));
      }
    }, (reason) => {
    });
  }

  launchExportCustomers(content){
    this.modalExportCustomers = this.modalService.open(content, { size: 'lg', backdrop:'static' });
    this.modalExportCustomers.result.then((result) => {
      this.modalExportCustomers = null;
      this.launchAction = false;
    }, (reason) => {
      this.modalExportCustomers = null;
      this.launchAction = false;
    });
    this.allNbTotalCustomers = this.nbTotalCustomers;
    this.currentPurcent = 0;
    this.exportCustomers(1, 100);
  }


  exportCustomers(page, limit, filename = ''){
    this.launchAction = true;
    this.userService.post('exportCustomers/:token', {page:page, limit:limit, filename:filename}, function(data){
      if(this.modalExportCustomers != null){
        if(!data.end){
          this.exportCustomers(page + 1, limit, data.filename);
          this.allNbTotalCustomers = data.allNbTotalCustomers;
          this.currentPurcent = Math.floor(((limit * page) / this.allNbTotalCustomers) * 100);
        }
        else {
          this.launchAction = false;
          var link = document.createElement("a");
          link.download = data.fileURL;
          link.href = data.fileURL;
          link.click();
          this.modalExportCustomers.close();
        }
      }
      else {
        this.launchAction = false;
      }
    }.bind(this), function(error){
      this.launchAction = false;
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }



}
