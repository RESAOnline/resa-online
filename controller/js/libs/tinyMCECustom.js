var updateAssociatedTextarea = function ( editor ) {
	var text = editor.getContent();
	jQuery(function($){
		$(document).ready(function(){
			$('#'+ tinymce.activeEditor.id).val(text);
			$('#'+ tinymce.activeEditor.id).trigger('input');
		});
	});
}

var setTinyMCEEnabled = function ( id , enabled) {
	if(enabled!=null && id!=null){
		var editor =  tinyMCE.get(id);
		if(editor != null){
			if(enabled) editor.setMode('design'); 
			else editor.setMode('readonly');
		}
	}
}