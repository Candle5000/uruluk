$(function() {

	let autoTransition = false;

	window.addEventListener('popstate', e => {
		if (location.pathname.split('/').length == 5) {
			if ($("#modal-item").hasClass('show')) {
				autoTransition = true;
				$("#modal-item").modal('hide');
			}
			autoTransition = true;
			$("#" + location.pathname.split('/')[4]).click();
		} else if ($("#modal-item").hasClass('show')) {
			autoTransition = true;
			$("#modal-item").modal('hide');
		}
	});

	$("#modal-item").on('hide.bs.modal', function() {
		if (!autoTransition && location.pathname.split('/').length != 4) {
			history.pushState(null, null,
				'/items/' + location.pathname.split('/')[2]
					+ '/' + location.pathname.split('/')[3]);
		}
		autoTransition = false;
	});

	$("div.selectable").on('click', function() {
		const at = autoTransition;
		autoTransition = false;
		const itemId = $(this).data('itemid');
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
		$.ajax({
			url: '/items/detail/' + itemId,
			type: 'GET',
		}).done(data => {
			const item = data.item;

			$('#detail-floors').children('li.detail-row').remove();
			if (item.floors.length) {
				$('#detail-floor-none').addClass('d-none');
			} else {
				$('#detail-floor-none').removeClass('d-none');
			}
			item.floors.forEach(floor => {
				const row = $($("#modal-floor-row").html());
				row.find(".floor-name").text(floor.short_name)
					.attr('href', '/floors/' + floor.floor_id);
				$('#detail-floors').append(row);
			});

			$('#detail-creatures').children('li.detail-row').remove();
			if (item.creatures.length) {
				$('#detail-creature-none').addClass('d-none');
			} else {
				$('#detail-creature-none').removeClass('d-none');
			}
			item.creatures.forEach(creature => {
				const row = $($("#modal-creature-row").html());
				const imgName = creature.image_name ?
					creature.image_name : 'creature_noimg.png';
				row.find('img').attr('src', '/img/creature/' + imgName);
				row.find('span').text(creature.name_en);
				row.find('.creature-name')
					.attr('href', '/creatures/' + creature.creature_id)
					.addClass(creature.boss == 1 ? 'boss' : 'text-light');
				$('#detail-creatures').append(row);
			});

			if (!at && location.pathname.split('/').length != 5) {
				history.pushState(null, null,
					'/items/' + location.pathname.split('/')[2]
						+ '/' + location.pathname.split('/')[3] + '/' + itemId);
			}

			$("#modal-item").modal("show");
		});
	});

	if (location.pathname.split('/').length == 5) {
		autoTransition = true;
		$("#" + location.pathname.split('/')[4]).click();
	}

});
