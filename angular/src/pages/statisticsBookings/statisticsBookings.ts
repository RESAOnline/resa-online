import { Component, OnInit  } from '@angular/core';
import { NgModel } from '@angular/forms';
import { formatDate } from '@angular/common';
import { NgbModal, NgbDate } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute } from '@angular/router';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';
import * as createjs from 'createjs-module';

declare var swal: any;

@Component({
  selector: 'page-statistics-bookings',
  templateUrl: 'statisticsBookings.html',
  styleUrls:['./statisticsBookings.css']
})

export class StatisticsBookingsComponent extends ComponentCanDeactivate implements OnInit {

  public settings = null;
  public statisticsForm = {
    places:{},
    startDate:this.toNgbDate(new Date()),
    endDate:this.toNgbDate(new Date())
  };

  public loadSettings:boolean = false;
  public loadStatistics:boolean = false;
  public statistics = null;

  constructor(private userService:UserService, private navService:NavService, public global:GlobalService,
    private route: ActivatedRoute, private modalService: NgbModal) {
    super('Statistiques réservations');
  }

  ngOnInit(): void {
    this.getSettings();
  }

  canDeactivate():boolean{ return true; }

  toDate(ngbDate:NgbDate):Date{
    return new Date(ngbDate.year, ngbDate.month - 1, ngbDate.day, 0, 0, 0, 0);
  }

  toNgbDate(date:Date):NgbDate{
    return new NgbDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
  }

  changeStartDate(){
    var startDate = this.toDate(this.statisticsForm.startDate);
    var endDate = this.toDate(this.statisticsForm.endDate);
    if(startDate > endDate){
      this.statisticsForm.endDate = this.toNgbDate(startDate);
    }
  }

  changeEndDate(){
    var startDate = this.toDate(this.statisticsForm.startDate);
    var endDate = this.toDate(this.statisticsForm.endDate);
    if(startDate > endDate){
      this.statisticsForm.startDate = this.toNgbDate(endDate);
    }
  }

  getPlaceName(idPlace){
    if(this.settings != null && this.settings.places != null){
      var place = this.settings.places.find(element => element.id == idPlace);
      if(place != null) return place.name[this.global.currentLanguage];
    }
    return '';
  }


  getSettings(){
    if(!this.loadSettings){
      this.loadSettings = true;
      this.userService.get('initStatisticsBookings/:token', function(data){
        this.loadSettings = false;
        this.settings = data.settings;
        this.statisticsForm.places = this.global.filterSettings.places;
        if(data.startDate != '' && data.endDate != ''){
          this.statisticsForm.startDate = this.toNgbDate(new Date(data.startDate));
          this.statisticsForm.endDate = this.toNgbDate(new Date(data.endDate));
        }
      }.bind(this), function(error){
        this.loadSettings = false
        var text = 'Impossible d\'initialiser la page';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }

  getStatisticsBookings(){
    if(!this.loadStatistics){
      this.loadStatistics = true;
      this.statistics = null;
      var starDate = formatDate(this.toDate(this.statisticsForm.startDate), 'yyyy-MM-dd', 'fr');
      var endDate = formatDate(this.toDate(this.statisticsForm.endDate), 'yyyy-MM-dd', 'fr');
      this.userService.post('statisticsBookings/:token', {
        places:this.statisticsForm.places,
        startDate:starDate,
        endDate:endDate
      }, function(data){
        this.loadStatistics = false;
        this.statistics = data;
        let endDate = new Date(this.toDate(this.statisticsForm.endDate));
        let date = new Date(this.toDate(this.statisticsForm.startDate));
      }.bind(this), function(error){
        this.loadStatistics = false
        var text = 'Impossible de récupérer les statistiques des réservations';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }



}
