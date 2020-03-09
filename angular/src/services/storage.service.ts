export class StorageService {

  naming = 'resa-parametrage';

  constructor() {
  }

  setNaming(naming){
    this.naming = naming;
  }

  save(object){
    localStorage[this.naming] = JSON.stringify(object);
  }

  saveData(key, data){
    var object = this.load();
    if(object == null){
      object = {};
    }
    object[key] = data;
    localStorage[this.naming] = JSON.stringify(object);
  }

  load(){
    var object = null;
		if(localStorage[this.naming] != null){
			object = JSON.parse(localStorage[this.naming]);
		}
		return object;
  }
}
