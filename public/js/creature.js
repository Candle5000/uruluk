$(function() {

	const setPhaseBoost = function(baseTagId, boostVal, level, isPercentage) {
		const base = $(baseTagId);
		if (!base.data("base-val") || base.data("base-val") == 0 || !boostVal) {
			return;
		}
		if (baseTagId == "#detail-as") {
			base.text(Math.round(base.data("base-val") / (1 + boostVal * level / 100)));
		} else {
			base.text(Math.round(base.data("base-val") * (1 + boostVal * level / 100)) + (isPercentage ? '%' : ''));
		}
	}

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
			const as = creature.as ? creature.as == 0 ? '-' : creature.as : '?';
			$("#detail-image").attr('src', '/img/creature/' + imgName);
			$("#detail-name-ja").text(creature.name_ja);
			$("#detail-name-en").text(creature.name_en);
			$("#detail-min-ad").text(creature.min_ad ? creature.min_ad : '?')
				.data("base-val", creature.min_ad);
			$("#detail-max-ad").text(creature.max_ad ? creature.max_ad : '?')
				.data("base-val", creature.max_ad);
			$("#detail-as").text(as)
				.data("base-val", creature.as);
			$("#detail-def").text(creature.def ? creature.def : '?')
				.data("base-val", creature.def);
			$("#detail-dex").text(creature.dex ? creature.dex : '?')
				.data("base-val", creature.dex);
			$("#detail-vit").text(creature.vit ? creature.vit : '?')
				.data("base-val", creature.vit);
			$("#detail-ws").text(creature.ws ? creature.ws : '?')
				.data("base-val", creature.ws);
			$("#detail-voh").text((creature.voh ? creature.voh : '?') + '%')
				.data("base-val", creature.voh);
			$("#detail-dr").text((creature.dr ? creature.dr : '?') + '%')
				.data("base-val", creature.dr);
			$("#detail-xp").text(creature.xp ? creature.xp : '?')
				.data("base-val", creature.xp);
			$("#tb-ad").text(creature.tb_ad ? creature.tb_ad + '%' : '-')
				.data("val", creature.tb_ad);
			$("#tb-as").text(creature.tb_as ? creature.tb_as + '%' : '-')
				.data("val", creature.tb_as);
			$("#tb-def").text(creature.tb_def ? creature.tb_def + '%' : '-')
				.data("val", creature.tb_def);
			$("#tb-dex").text(creature.tb_dex ? creature.tb_dex + '%' : '-')
				.data("val", creature.tb_dex);
			$("#tb-vit").text(creature.tb_vit ? creature.tb_vit + '%' : '-')
				.data("val", creature.tb_vit);
			$("#tb-ws").text(creature.tb_ws ? creature.tb_ws + '%' : '-')
				.data("val", creature.tb_ws);
			$("#tb-voh").text(creature.tb_voh ? creature.tb_voh + '%' : '-')
				.data("val", creature.tb_voh);
			$("#tb-dr").text(creature.tb_dr ? creature.tb_dr + '%' : '-')
				.data("val", creature.tb_dr);
			$("#tb-xp").text(creature.tb_xp ? creature.tb_xp + '%' : '-')
				.data("val", creature.tb_xp);
			if (creature.boss == 1) {
				$("#detail-name").addClass("boss");
			} else {
				$("#detail-name").removeClass("boss");
			}
			if (creature.tb == 1) {
				$("#detail-tb-boosts,#detail-row-tb-phase").removeClass("d-none");
			} else {
				$("#detail-tb-boosts,#detail-row-tb-phase").addClass("d-none");
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
				const link = '/items/' + item.item_class.toLowerCase() + '/'
						+ (item.rarity == 'common' ? item.base_item_id : 'rare') + '/' + item.item_id;
				const img = item.image_name ? item.image_name : 'item_noimg.png';
				row.find('a').attr('href', link).addClass(item.rarity);
				row.find('img.item-icon')
						.attr('src', '/img/item/' + img).attr('alt', item.name_en);
				row.find('span').text(item.name_en);
				if (item.class_flactuable == 0) {
					row.find('img.class-icon').remove();
				} else {
					row.find('img.class-icon').attr('src', '/img/common/' + item.item_class.toLowerCase() + '.png');
				}
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

	$("select.tb-phase").on('change', function() {
		const level = $(this).val();
		setPhaseBoost("#detail-min-ad", $("#tb-ad").data("val"), level, false);
		setPhaseBoost("#detail-max-ad", $("#tb-ad").data("val"), level, false);
		setPhaseBoost("#detail-as", $("#tb-as").data("val"), level, false);
		setPhaseBoost("#detail-def", $("#tb-def").data("val"), level, false);
		setPhaseBoost("#detail-dex", $("#tb-dex").data("val"), level, false);
		setPhaseBoost("#detail-vit", $("#tb-vit").data("val"), level, false);
		setPhaseBoost("#detail-ws", $("#tb-ws").data("val"), level, false);
		setPhaseBoost("#detail-voh", $("#tb-voh").data("val"), level, true);
		setPhaseBoost("#detail-dr", $("#tb-dr").data("val"), level, true);
		setPhaseBoost("#detail-xp", $("#tb-xp").data("val"), level, false);
	});

	if (location.pathname.split('/').length == 3) {
		autoTransition = true;
		$("#creature-" + location.pathname.split('/')[2]).click();
	}

});
