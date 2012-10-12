(function($){

$(function(){

	$(':checkbox[data-toggle]').each(function(){
		var self = $(this);
		var target = self.data('toggle');
		if ($('#'+target).size() == 0) return;
		self.click(function(){
			if (self.attr('checked') == undefined) {
				$('#'+target).addClass('disabled');
				$('#'+target+' :input').attr('disabled', 'disabled');
			} else {
				$('#'+target).removeClass('disabled');
				$('#'+target+' :input').removeAttr('disabled');
			}
		}).triggerHandler('click');
	});

	$(':radio[data-toggle]').each(function(){
		var self = $(this);
		var target = self.data('toggle');
		if ($('#'+target).size() == 0) return;
		var others = $('[name="'+self.attr('name')+'"]').not(self);
		self.click(function(){
			if (self.attr('checked') == undefined) {
				$('#'+target).addClass('disabled');
				$('#'+target+' :input').attr('disabled', 'disabled');
			} else {
				$('#'+target).removeClass('disabled');
				$('#'+target+' :input').removeAttr('disabled');
				others.filter('[data-toggle]').each(function(){
					var other = $(this);
					var other_target = other.data('toggle');
					$('#'+other_target).addClass('disabled');
					$('#'+other_target+' :input').attr('disabled', 'disabled');
				});
			}
		}).triggerHandler('click');
		others.not('[data-toggle]').click(function(){
			if ($(this).attr('checked') != undefined) {
				$('#'+target).addClass('disabled');
				$('#'+target+' :input').attr('disabled', 'disabled');
			}
		});
	});

});

})(jQuery);
