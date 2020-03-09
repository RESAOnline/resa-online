import { Pipe, PipeTransform } from '@angular/core';
import { formatDate } from '@angular/common';

@Pipe({name: 'htmlSpecialDecode'})
export class HtmlSpecialDecodePipe implements PipeTransform {
  transform(value: string, withoutBr: boolean = false): string {
    if(value != null){
      value = value.replace(new RegExp('&#039;', 'g'),'\'');
      value = value.replace(new RegExp('&quot;', 'g'),'"');
      value = value.replace(new RegExp('&lt;', 'g'),'<');
      value = value.replace(new RegExp('&gt;', 'g'),'>');
      value = value.replace(new RegExp('&amp;', 'g'),'&');
      value = value.replace(new RegExp('\\\\', 'g'),'\\');
      if(withoutBr == null || !withoutBr){
        value = value.replace(new RegExp('<br />', 'g'), '\n');
      }
    }
    return value;
  }
}


@Pipe({name: 'formatNewLineHtml'})
export class FormatNewLineHtml implements PipeTransform {
  transform(value: string): string {
    if(value != null){
      value = value.replace(/\n/g, '<br \>');
    }
    return value;
  }
}

@Pipe({name: 'formatManyDatesView'})
export class FormatManyDatesView implements PipeTransform {

  capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  formatMessage(currentMonthsYears){
    var result = '';
    for(var j = 0; j < currentMonthsYears.days.length; j++){
      var day = currentMonthsYears.days[j];
      result += ((j>0)?', ':'') + day;
    }
    var dateFormated = this.capitalizeFirstLetter(formatDate((new Date(currentMonthsYears.years, currentMonthsYears.month-1)),'MMMM yyyy', 'fr'));
    result += ' ' + dateFormated + '<br />';
    return result;
  }

  transform(dates: string): string {
    var result = '';
    if(dates != null){
      var exploded = dates.split(',');
      var currentMonthsYears = null;
      for(var i = 0; i < exploded.length; i++){
        var date = exploded[i];
        var tokens = date.split('-');
        if(currentMonthsYears == null){
          currentMonthsYears = {years:tokens[0], month:tokens[1], days:[tokens[2]]};
        }
        else {
          if(currentMonthsYears.month == tokens[1]){
            currentMonthsYears.days.push(tokens[2]);
          }
          else {
            result += this.formatMessage(currentMonthsYears);
            currentMonthsYears = {years:tokens[0], month:tokens[1], days:[tokens[2]]};
          }
        }
      }
      if(currentMonthsYears.days.length > 0){
        result += this.formatMessage(currentMonthsYears);
      }
    }
    return result;
  }
}

@Pipe({name: 'formatPhone'})
export class FormatPhone implements PipeTransform {
  transform(phone: string): string {
    var newPhone = '';
    if(phone!=null){
      var size = phone.length / 2;
      var begin = 0;
      if(size != Math.floor(size)){
        newPhone = phone[0];
        begin = 1;
      }
      for(var i = 0; i < size; i++){
        newPhone += ' ' + phone.substr(begin + (i * 2), 2);
      }
    }
    return newPhone;
  }
}

@Pipe({name: 'negative'})
export class Negative implements PipeTransform {
  transform(value: number): number {
    if(value > 0)
      value *= -1;
    return value;
  }
}

@Pipe({name: 'parseDate'})
export class ParseDate implements PipeTransform {
  transform(date: any): Date {
    if(typeof(date) === 'string'){
      var split = date.split(' ');
      if(split.length == 2){
        var dateTokens = split[0].split('-');
        var timeTokens = split[1].split(':');
        //time[0] = time[0] * 1 + 1;
        date = new Date(
          parseInt(dateTokens[0]),
          parseInt(dateTokens[1]) - 1,
          parseInt(dateTokens[2]),
          parseInt(timeTokens[0]),
          parseInt(timeTokens[1]),
          parseInt(timeTokens[2]), 0);
      }
      else if(date.split('-').length == 3 && date.length == 10){
        var dateTokens = date.split('-');
        date = new Date(
          parseInt(dateTokens[0]),
          parseInt(dateTokens[1]) - 1,
          parseInt(dateTokens[2])
        );
      }
      else {
        date = new Date(date);
      }
    }
    return new Date(date);
  }
}
