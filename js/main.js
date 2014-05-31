jQuery(function($){
	$('*[rel="tooltip"]').tooltip();
	
	$('.input.error input,.input.error textarea').focus(function(){
		$(this).parent().removeClass('error'); 
		$(this).parent().find('.error-message').remove(); 
	})

});

/* -------------------- Plugin DatePicker & DateTimePicker -------------------- */
jQuery(function($){
	$.datepicker.setDefaults($.datepicker.regional['fr']);
	var datapickers = $('.datepicker').datepicker({
	    //minDate : 0,
	    dateFormat : 'yy-mm-dd',
	    changeMonth: true,
		changeYear: true,
	    onSelect: function( date ) {
	    	var option = this.id == 'dateStart' ? 'minDate' : 'maxDate';
	    	datapickers.not('#'+this.id).datepicker('option',option,date);
	    }
	});

	$('.timepicker').timepicker({
	    timeFormat: 'hh:mm:ss',
	    showSecond:true,
	    second : 0
	});

	$('.datetimepicker').datetimepicker({
	    dateFormat : 'yy-mm-dd',
	    separator: ' ',
	    second : 0,
	    timeFormat: 'hh:mm:ss',
	    showSecond:true
	});

});

/* -------------------- Plugin colorpicker -------------------- */
jQuery(function($){
    $('.cp-basic').colorpicker();
});

/* -------------------- Plugin Slider -------------------- */
jQuery(function($){
	$('.range').each(function(){
		var cls      = $(this).attr('class');  
		var matches  = cls.split(/([a-zA-Z]+)\-([0-9]+)/g);
		var elem     = $(this).parents('.form-group'); 
		var colsm10  = $(this).parents('.col-sm-10'); 
		var options  = {}; 
		var input    = elem.find('input');
		var label    = elem.find('.control-label');
		if (label.find('span.sliderValue').length == 0) {label.append(' <span class="sliderValue"></span>')};
		var sliderValue = label.find('span.sliderValue');
		colsm10.append('<div class="uirange"></div>');

		console.log(elem);
		console.log(colsm10);
		console.log(label);
		console.log(sliderValue);

		for(i in matches){
		  i = i * 1; 
		  if(matches[i] == 'min'){
		    options.min  = matches[i+1]*1;
		  } 
		  if(matches[i] == 'max'){
		    options.max  = matches[i+1]*1;
		  } 
		}

		options.slide = function(event, ui){
		  sliderValue.empty().append(ui.value);
		  input.val(ui.value);
			console.log(input);
		}
		options.value = input.val(); 
		options.range = 'min';
		elem.find('.uirange').slider(options); 

		sliderValue.empty().append(input.val()); 
		input.hide();

	});
});
