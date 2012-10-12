(function($){

$(function(){

	$('[data-toggle]').each(function(){
		var target = $(this).data('toggle');
		if ($('#'+target).size() > 0) {
			switch ($(this).attr('type')) {
				case 'checkbox':
					$(this).click(function(){
						if ($(this).attr('checked') == undefined) {
							$('#'+target).addClass('disabled');
							$('#'+target+' :input').attr('disabled', 'disabled');
						} else {
							$('#'+target).removeClass('disabled');
							$('#'+target+' :input').removeAttr('disabled');
						}
					}).triggerHandler('click');
					break;
				case 'radio':
					var others = $('[name="'+$(this).attr('name')+'"]').not(this);
					$(this).click(function(){
						if ($(this).attr('checked') == undefined) {
							$('#'+target).addClass('disabled');
							$('#'+target+' :input').attr('disabled', 'disabled');
						} else {
							$('#'+target).removeClass('disabled');
							$('#'+target+' :input').removeAttr('disabled');
							$('[data-toggle]', others).each(function(){
								var other_target = $(this).data('toggle');
								$('#'+other_target).addClass('disabled');
								$('#'+other_target+' :input').attr('disabled', 'disabled');
							});
						}
					}).triggerHandler('click');
					others.click(function(){
						$('#'+target).addClass('disabled');
						$('#'+target+' :input').attr('disabled', 'disabled');
					});
					break;
			}
		}
	});

});

})(jQuery);
