import { Component, OnInit  } from '@angular/core';
import { NgModel } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { formatDate } from '@angular/common';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';
import * as createjs from 'createjs-module';

declare var swal: any;

@Component({
  selector: 'page-daily',
  templateUrl: 'daily.html',
  styleUrls:['./daily.css']
})

export class DailyComponent extends ComponentCanDeactivate implements OnInit {

  public settings:any = [];
  public date:Date = null;
  public nextDate:Date = null;
  public services = [];
  public globalStatistics = null;
  public statistics = [];
  public dataView = [];

  public idWeather = '';

  public lightGreen = '#C4DF9C';
  public lightOrange = '#FEC689';
  public orange = '#FBAE5C';
  public green = '#82CA9C';
  public red = '#F26C4F';

  public dataSelected = null;


  constructor(public global:GlobalService, private userService:UserService, private navService:NavService) {
    super('Arrivées');
    this.date = new Date(this.global.currentDate);
    this.nextDate = new Date(this.date);
  }

  loadScript(url) {
    let node = document.createElement('script');
    node.src = url;
    node.type = 'text/javascript';
    document.getElementsByTagName('head')[0].appendChild(node);
  }


  ngOnInit(): void {
    this.userService.get('daily/:token', function(data){
      this.settings = data.settings;
      this.generateWeather();
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
    this.loadDataWithDate(this.date);
  }

  getPlaceName(idPlace){
    if(this.settings != null && this.settings.places != null){
      for(var i = 0; i < this.settings.places.length; i++){
        var place = this.settings.places[i];
        if(place.id == idPlace){
          return this.global.getTextByLocale(place.name);
        }
      }
    }
    return '';
  }

  loadToday(){
    this.loadDataWithDate(new Date());
  }

  loadTomorow(){
    var today = new Date();
    today.setDate(today.getDate() + 1);
    this.loadDataWithDate(today);
  }

  updateDate(date){
    this.loadDataWithDate(date);
  }

  launchLoadDate(){
    this.loadDataWithDate(this.nextDate);
  }

  loadDataWithDate(date){
    this.date = new Date(date);
    this.nextDate = new Date(date);
    this.globalStatistics = null;
    this.global.setCurrentDate(new Date(date));
    this.userService.post('daily/:token', {date:formatDate(date, 'yyyy-MM-dd', 'fr')}, function(data){
      this.services = data.services;
      for(var i = 0; i < this.services.length; i++){
        this.services[i].display = true;
      }
      this.globalStatistics = data.global;
      this.statistics = data.statistics;
      this.dataSelected = null;
      this.generateDataView();
      setTimeout(function(){ this.generateGraphics(); }.bind(this), 1000);
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  getRESABackendLink(){
    return this.userService.getCurrentHost() + '/wp-admin/admin.php?page=resa_appointments';
  }

  getServicesById(idService){
    for(var i = 0; i < this.services.length; i++){
      var service = this.services[i];
      if(service.id == idService){
        return service;
      }
    }
    return null;
  }

  isSameHours(date1, date2){
    date1 = new Date(date1);
    date2 = new Date(date2);
    return date1.getHours() == date2.getHours() && date1.getMinutes() == date2.getMinutes();
  }

  goSettingsDaily(){
    this.navService.changeRoute('settings/daily');
  }

  generateWeather(){
    var url = this.settings.urlWeather;
    if(url.length > 0){
      var tokens = url.split('/');
      this.idWeather = 'cont_' + tokens[tokens.length - 1];
      this.loadScript(url);
    }
  }


  generateDataView(){
    this.dataView = [];
    for(var i = 0; i < this.statistics.length; i++){
      var statistics = this.statistics[i];
      var service = this.getServicesById(statistics.idService);
      if(service != null && service.display){
        var startDate = this.global.parseDate(statistics.startDate);
        var found = false;
        this.dataView.map(function(element){
          if(element.startDate.getTime() == startDate.getTime()){
            found = true;
            element.numbers += statistics.numbers;
            element.groups += statistics.groups;
          }
        });
        if(!found){
          this.dataView.push({numbers:statistics.numbers, groups:statistics.groups, startDate:this.global.parseDate(statistics.startDate)});
        }
      }
    }
  }

  regenerateDataView(){
    this.generateDataView();
    this.generateGraphics();
  }

  generateGraphics(){
    var stage = new createjs.Stage('demoCanvas');
    if(stage.canvas != null){
      stage.enableMouseOver();
      var sWidth = (stage.canvas as any).clientWidth;
      var sHeight = (stage.canvas as any).clientHeight;
      (stage.canvas as any).width = sWidth;
      (stage.canvas as any).height = sHeight;
      this.createTimeline(stage, sWidth, sHeight, 10, this.global.parseDate(this.globalStatistics.minDate), this.global.parseDate(this.globalStatistics.maxDate), sHeight, 10);
      this.createGraphics(stage, sWidth, sHeight, 10, this.global.parseDate(this.globalStatistics.minDate), this.global.parseDate(this.globalStatistics.maxDate), sHeight - 30, 10);
      stage.update();
    }
  }



  createTimeline(stage, sWidth, sHeight, widthServicesList, startTime, endTime, heightTimeline, margin){
    var widthTimeline = this.getWidthTimeline(sWidth, widthServicesList, margin) - 15;
    var numberHours = this.getNumberHours(startTime, endTime);
    var splitTime = 2;
    var stepX = widthTimeline / (numberHours * splitTime);
    for(var i = 0; i <= numberHours; i++){
      var hours = startTime.getHours() + i;
      var firstX = widthServicesList + margin + stepX * i * splitTime;
      if(splitTime == 4) var firstX15 = widthServicesList + margin + stepX * (i * splitTime + 1);
      var firstX30 = widthServicesList + margin + stepX * (i * splitTime + splitTime/2);
      if(splitTime == 4) var firstX45 = widthServicesList + margin + stepX * (i * splitTime + 3);
      stage.addChild(this.drawText(hours%24 + 'h', firstX - 5, heightTimeline - margin, 'black', '1.3em','"Segoe UI"'));
      if(splitTime == 4) stage.addChild(this.drawText('15', firstX15, heightTimeline - margin));
      stage.addChild(this.drawText('30', firstX30, heightTimeline - margin, 'black', '1.1em'));
      if(splitTime == 4) stage.addChild(this.drawText('45', firstX45, heightTimeline - margin));
    }
  }

  createGraphics(stage, sWidth, sHeight, widthServicesList, startTime, endTime, heightTimeline, margin){
    stage.addChild(this.drawRect(0, 0, sWidth, heightTimeline, 'white'));
    var maxValue = 0;
    for(var i = 0; i < this.dataView.length; i++){
      maxValue = Math.max(maxValue, this.dataView[i].numbers);
    }
    for(var i = 0; i < this.dataView.length; i++){
      var data = this.dataView[i];
      var startDate = this.global.parseDate(data.startDate);
      var hours = startDate.getHours() + startDate.getMinutes() / 60;
      var numbers = data.numbers;
      var groups = data.groups;

      var color = this.lightGreen;
      if(numbers > 10) color = this.green;
      if(numbers > 30) color = this.lightOrange;
      if(numbers > 50) color = this.orange;
      if(numbers > 70) color = this.red;

      var heightGroup = 20;

      var numberHours = this.getNumberHours(startTime, endTime);
      var x = this.hoursToX(hours, startTime.getHours(), numberHours, this.getWidthTimeline(sWidth, widthServicesList, margin), widthServicesList, margin);
      var width = this.hoursToX(hours + 0.5, startTime.getHours(), numberHours, this.getWidthTimeline(sWidth, widthServicesList, margin), widthServicesList, margin) - x;
      var height = Math.max(this.heightHistogram(numbers, maxValue, heightTimeline - 2 * margin - heightGroup), 25);
      var serviceBlock = new createjs.Container();
      var block = new createjs.Shape();
      block.graphics.beginFill(color).drawRect(0, 0, width, height);
      var border = new createjs.Shape();
      border.graphics.beginStroke('white').drawRect(0, 0, width, height);
      var text = new createjs.Text(numbers, '1.5em "Segoe UI"', 'white');
      text.set({ textAlign: 'center', textBaseline: 'bottom', x: width / 2, y: height});
      serviceBlock.set({x: x + 5, y: heightTimeline - height});
      (serviceBlock as any).data = data;

      if(groups > 0){
        var icoGroup = new createjs.Bitmap('assets/image/ico_group.png');
        icoGroup.image.onload = function() {
          stage.update();
        }
        icoGroup.set({x:x + 5, y:heightTimeline - height - margin - 20});
        stage.addChild(icoGroup);
        stage.addChild(this.drawText('x' + groups, x + width / 2 + 10, heightTimeline - height - margin, 'black', '1.2em'));
      }

      serviceBlock.on("click", function(event){
        this.dataSelected = event.currentTarget.data;
      }.bind(this));
      serviceBlock.on("mouseover", function(evt) {
        stage.cursor = "pointer";
      });
      serviceBlock.on("mouseout", function(evt) {
        stage.cursor = "default";
      });


      serviceBlock.addChild(block, border, text);
      stage.addChild(serviceBlock);
    }
  }

  getWidthTimeline(sWidth, widthServicesList, margin){
    return sWidth - widthServicesList - 1 - (2 * margin);
  }

  getNumberHours(startTime, endTime){
    return Math.floor((endTime.getTime() - startTime.getTime()) / (3600 * 1000));
  }

  heightHistogram(value, maxValue, maxHeightSize){
    return (maxHeightSize * value) / maxValue;
  }

  hoursToX(hours, startHours, numberHours, widthTimeline, widthServicesList, margin){
    var i = hours - startHours;
    var stepX = widthTimeline / (numberHours * 2);
    var x = widthServicesList + margin + stepX * i * 2;
    return x;
  }

  drawLine(x1, y1, x2, y2, color = 'black', size = 1){
    var line = new createjs.Shape();
    line.graphics.setStrokeStyle(size);
    line.graphics.beginStroke(color);
    line.graphics.moveTo(x1, y1);
    line.graphics.lineTo(x2, y2);
    line.graphics.endStroke();
    return line;
  }

  drawDashedLine(x1, y1, x2, y2, color = 'black', size = 1 ){
    var line = new createjs.Shape();
    line.graphics.setStrokeDash([5, 5], 0);
    line.graphics.beginStroke(color);
    line.graphics.moveTo(x1, y1);
    line.graphics.lineTo(x2, y2);
    line.graphics.endStroke();
    return line;
  }

  drawText(text, x, y, color = 'black', fontSize = '1em', font='"Segoe UI"'){
    var textObject = new createjs.Text(text, fontSize + ' ' + font, color);
    textObject.x = x;
    textObject.y = y;
    textObject.textBaseline = 'alphabetic';
    return textObject;
  }

  drawRect(x, y, width, height, color = 'black'){
    var rect = new createjs.Shape();
    rect.graphics.beginFill(color).drawRect(x, y, width, height);
    return rect;
  }

  canDeactivate():boolean{ return true; }

}
