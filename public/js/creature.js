$(function() {

    $("td.selectable").on('click', function() {
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
			itemTbody.children().remove();
			if (creature.items.length == 0) {
				itemTbody.append($("<tr>").append($("<td>").addClass("pl-2").text("None")));
			}
			creature.items.forEach(item => {
				const img = $("<img>").attr('src', '/img/item/' + item.image_name);
				img.attr('alt', item.name_en).addClass('item-icon');
				const span = $("<span>").text(' ' + item.name_en);
				const td = $("<td>").append(img).append(span);
				if (item.rarity != 'common') td.addClass(item.rarity);
				itemTbody.append($("<tr>").append(td));
			});

			const floors = $("#detail-floors");
			floors.children().remove();
			if (creature.floors.length == 0) {
				floors.append($("<li>").addClass("list-inline-item").text("None"));
			}
			creature.floors.forEach(floor => {
				floors.append($("<li>").addClass("list-inline-item").text(floor.short_name));
			});

			$("#modal-creature").modal("show");
		});
    });

});
