import {Input, Output, OnChanges, OnInit, Component, Inject, EventEmitter, SimpleChanges  } from '@angular/core';

import { DatePipe } from '@angular/common';
import { UserService } from '../../services/user.service';
import { NavService } from '../../services/nav.service';

declare var swal: any;

@Component({
  selector: 'image-selector',
  templateUrl: 'imageSelector.html',
  styleUrls:['./imageSelector.css']
})

export class ImageSelector implements OnInit {

  public launchAction = false;
  public newImage = '';
  public images = [];

  public currentPage = 0;

  @Output() imageSelected: EventEmitter<any> = new EventEmitter();


  constructor(private userService:UserService, private navService:NavService) {

  }

  ngOnInit(): void {
    this.loadImages(1, 20);
  }

  getMediaLink(){
    return this.userService.getCurrentHost() + '/wp-admin/media-new.php';
  }

  goMediaLink(){
    this.navService.goToLink(this.getMediaLink());
  }

  loadImages(page, perPage){
    this.launchAction = true;
    this.userService.get('../../wp/v2/media/?page=' + page + '&per_page=' + perPage, function(data){
      this.images = this.images.concat(data);
      this.launchAction = false;
      if(data.length == perPage){
        this.loadImages(page + 1, perPage);
      }
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données de l\'activité';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  refresh(){
    if(!this.launchAction){
      this.images = [];
      this.loadImages(1, 20);
    }
  }


  isImage(image){
    return image.media_type == 'image';
  }

  selectImage(image){
    if(this.isImage(image)){
      if(image.media_details.sizes.medium != null) this.imageSelected.emit({type:'image', src:image.media_details.sizes.medium.source_url});
      else if(image.media_details.sizes.thumbnail != null) this.imageSelected.emit({type:'image', src:image.media_details.sizes.thumbnail.source_url});
      else if(image.media_details.sizes.full != null) this.imageSelected.emit({type:'image', src:image.media_details.sizes.full.source_url});
      else if(image.source_url != null) this.imageSelected.emit({type:'image', src:image.source_url});
    }
    else {
      this.imageSelected.emit({type:'url', src:image.source_url});
    }
  }

  loadNewImage(event){
    /*var file = event.srcElement.files[0];
    let testData:FormData = new FormData();
    testData.append('file', file);
    var json = {
      'Content-Type' : 'multipart/form-data',
      'Authorization': 'Basic ' + btoa('damien:Préd4702/17'),
      'Content-Disposition': 'attachment; filename="'+file.name+'"'
    };
    this.userService.postWithHeaders('../../wp/v2/media/', testData, this.userService.generateHttpHeaders(json), function(data){

    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données de l\'activité';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });*/

    var file = event.srcElement.files[0];
    let testData:FormData = new FormData();
    testData.append('file', file);
    var json = {
      'Content-Type' : 'multipart/form-data',
    }
    this.userService.postWithHeaders('uploadImage/:token', testData, this.userService.generateHttpHeaders(json), function(data){
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données de l\'activité';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }





}
