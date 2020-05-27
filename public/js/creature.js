$(function() {

	let autoTransition = false;

	window.addEventListener('popstate', e => {
		if (location.pathname.split('/').length == 3) {
			if ($("#modal-creature").hasClass('show')) {
				autoTransition = true;
				$("#modal-creature").modal('hide');
			}
			autoTransition = true;
			$("#creature-" + location.pathname.split('/')[2]).click();
		} else if ($("#modal-creature").hasClass('show')) {
			autoTransition = true;
			$("#modal-creature").modal('hide');
		}
	});

	$("#modal-creature").on('hide.bs.modal', function() {
		if (!autoTransition && location.pathname.split('/').length != 2) {
			history.pushState(null, null, '/creatures');
		}
		autoTransition = false;
	});

	$("td.selectable").on('click', function() {
		const at = autoTransition;
		autoTransition = false;
		const creatureId = $(this).data('creatureid');
		$.ajax({
			url: '/creatures/detail/' + creatureId,
			type: 'GET',
		}).done(data => {
			const creature = data.creature;
			const imgName = creature.image_name ?
					creature.image_name : 'creature_noimg.png';
			const as = creature.as ? creature.as == -1 ? '-' : creature.as : '?';
			$("#detail-image").attr('src', '/img/creature/' + imgName);
			$("#detail-name-ja").text(creature.name_ja);
			$("#detail-name-en").text(creature.name_en);
			$("#detail-min-ad").text(creature.min_ad ? creature.min_ad : '?');
			$("#detail-max-ad").text(creature.max_ad ? creature.max_ad : '?');
			$("#detail-as").text(as);
			$("#detail-def").text(creature.def ? creature.def : '?');
			$("#detail-dex").text(creature.dex ? creature.dex : '?');
			$("#detail-vit").text(creature.vit ? creature.vit : '?');
			$("#detail-voh").text((creature.voh ? creature.voh : '?') + '%');
			$("#detail-dr").text((creature.dr ? creature.dr : '?') + '%');
			$("#detail-xp").text(creature.xp ? creature.xp : '?');
			if (creature.boss == 1) {
				$("#detail-name").addClass("boss");
			} else {
				$("#detail-name").removeClass("boss");
			}

			const saTbody = $("#detail-sa");
			saTbody.children('tr').remove();
			if (creature.special_attacks.length == 0) {
				saTbody.append($("<tr>").append($("<td>").addClass("pl-2").text("None")));
			}
			creature.special_attacks.forEach(sa => {
				const row = $($("#modal-sa-row").html());
				row.find(".sa-name").text(sa.name);
				row.find(".sa-note").attr('title', sa.note);
				saTbody.append(row);
			});
			saTbody.find("a.sa-note").tooltip();

			const itemTbody = $("#detail-items");
			itemTbody.children('tr').remove();
			if (creature.items.length == 0) {
				itemTbody.append($("<tr>").append($("<td>").addClass("pl-2").text("None")));
			}
			creature.items.forEach(item => {
				const row = $($("#modal-item-row").html());
				const link = '/items/' + item.item_class.toLowerCase() + '/rare/' + item.item_id;
				const img = item.image_name ? item.image_name : 'item_noimg.png';
				row.find('a').attr('href', link).addClass(item.rarity);
				row.find('img')
						.attr('src', '/img/item/' + img).attr('alt', item.name_en);
				row.find('span').text(item.name_en);
				itemTbody.append(row);
			});

			const floors = $("#detail-floors");
			floors.children('li').remove();
			if (creature.floors.length == 0) {
				floors.append($("<li>").addClass("list-inline-item").text("None"));
			}
			creature.floors.forEach(floor => {
				const li = $($("#modal-floor-li").html());
				li.find(".floor-name").text(floor.short_name).attr('href', "/floors/" + floor.floor_id);
				if (floor.note) {
					li.find(".floor-note").attr('title', floor.note);
				} else {
					li.find(".floor-note").remove();
				}
				floors.append(li);
			});
			floors.find("a.floor-note").tooltip();

			if (!at && location.pathname.split('/').length != 3) {
				history.pushState(null, null, '/creatures/' + creatureId);
			}

			$("#modal-creature").modal("show");
		});
	});

	if (location.pathname.split('/').length == 3) {
		autoTransition = true;
		$("#creature-" + location.pathname.split('/')[2]).click();
	}

});
