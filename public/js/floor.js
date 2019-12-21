$(function() {

	$(".collapse").on('show.bs.collapse', function() {
		$("[aria-controls='" + $(this).attr('id') + "'] svg")
				.removeClass('fa-chevron-circle-down')
				.addClass('fa-chevron-circle-up');
	});

	$(".collapse").on('hide.bs.collapse', function() {
		$("[aria-controls='" + $(this).attr('id') + "'] svg")
				.removeClass('fa-chevron-circle-up')
				.addClass('fa-chevron-circle-down');
	});

});
