import { Component, OnInit  } from '@angular/core';
import { NgModel } from '@angular/forms';
import { formatDate } from '@angular/common';
import { NgbModal, NgbDate } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute } from '@angular/router';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';

declare var swal: any;

@Component({
  selector: 'page-members-stats',
  templateUrl: 'membersStats.html',
  styleUrls:['./membersStats.css']
})

export class MembersStatsComponent extends ComponentCanDeactivate implements OnInit {

  public settings = null;
  public statisticsForm = {
    places:{},
    startDate:this.toNgbDate(new Date()),
    endDate:this.toNgbDate(new Date())
  };

  public loadSettings:boolean = false;
  public loadCountHours:boolean = false;
  public members = [];
  public categories = [];
  public selectIdMember = 0;
  public member = null;

  constructor(private userService:UserService, private navService:NavService, public global:GlobalService,
    private route: ActivatedRoute, private modalService: NgbModal) {
    super('Statistiques Personnel');
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

  goPlanningGroup(date){
    this.global.setCurrentDate(date);
    this.navService.changeRoute('planningGroups');
  }

  getStaffWorldMany(){
    if(this.settings == null) return '';
    return this.global.getTextByLocale(this.settings.staff_word_many);
  }

  getSettings(){
    if(!this.loadSettings){
      this.loadSettings = true;
      this.userService.get('initMembersStats/:token', function(data){
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

  launchCountHours(){
    if(!this.loadCountHours){
      this.loadCountHours = true;
      this.members = [];
      this.member = null;
      var starDate = formatDate(this.toDate(this.statisticsForm.startDate), 'yyyy-MM-dd', 'fr');
      var endDate = formatDate(this.toDate(this.statisticsForm.endDate), 'yyyy-MM-dd', 'fr');
      this.userService.post('countHoursMembers/:token', {
        places:this.statisticsForm.places,
        startDate:starDate,
        endDate:endDate
      }, function(data){
        this.loadCountHours = false;
        this.categories = data.categories;
        this.members = data.members;
        if(this.members.length > 0){
          this.member = this.members[0];
          this.selectIdMember = this.member.id;
        }
      }.bind(this), function(error){
        this.loadCountHours = false
        var text = 'Impossible de récupérer le décompte des heures';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      }.bind(this));
    }
  }

  loadMember(){
    this.member = this.members.find(element => element.id == this.selectIdMember);
  }


}
