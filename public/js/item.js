$(function() {

	$("div.selectable").on('click', function() {
		$("#detail-image").children().remove();
		$("#detail-image").append($(this).find("img.item-detail").clone());
		$("#detail-name").removeClass("common rare artifact");
		if ($(this).find(".item-name").hasClass("rare")) {
			$("#detail-name").addClass("rare");
		} else if ($(this).find(".item-name").hasClass("artifact")) {
			$("#detail-name").addClass("artifact");
		} else {
			$("#detail-name").addClass("common");
		}
		$("#detail-name-ja").text($(this).find("span.name-ja").text());
		$("#detail-name-en").text($(this).find("span.name-en").text());
		$("#detail-main").children().remove();
		$("#detail-main").append($(this).find("ul.detail-main").clone());
		$("#sell-price").text($(this).data('price') !== '' ? $(this).data('price') : '?');
		$("#modal-item").modal("show");
	});

});
