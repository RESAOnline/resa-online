import { Injectable } from '@angular/core';

import * as createjs from 'createjs-module';


@Injectable()
export class GraphicsService {

  createTimeline(stage, sWidth, sHeight, widthServicesList, startTime, endTime, heightTimeline, margin, displayLine=false){
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
      stage.addChild(this.drawText(hours + 'h', firstX - 5, heightTimeline - margin, 'black', '1.3em','"Segoe UI"'));
      if(splitTime == 4) stage.addChild(this.drawText('15', firstX15, heightTimeline - margin));
      stage.addChild(this.drawText('30', firstX30, heightTimeline - margin, 'black', '1.1em'));
      if(splitTime == 4) stage.addChild(this.drawText('45', firstX45, heightTimeline - margin));
      if(displayLine){
        stage.addChild(this.drawDashedLine(firstX + 5, heightTimeline, firstX + 5, sHeight, 'grey', 1));
        if(splitTime == 4) stage.addChild(this.drawDashedLine(firstX15 + 5, heightTimeline, firstX15 + 5, sHeight, 'grey', 1));
        stage.addChild(this.drawDashedLine(firstX30 + 5, heightTimeline, firstX30 + 5, sHeight, 'grey', 1));
        if(splitTime == 4) stage.addChild(this.drawDashedLine(firstX45 + 5, heightTimeline, firstX45 + 5, sHeight, 'grey', 1));
      }
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
}
