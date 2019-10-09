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
			itemTbody.children('tr').remove();
			if (creature.items.length == 0) {
				itemTbody.append($("<tr>").append($("<td>").addClass("pl-2").text("None")));
			}
			creature.items.forEach(item => {
				const row = $($("#modal-item-row").html());
				const link = '/items/rare/' + item.item_class + '#' + item.item_id;
				const img = item.image_name ? item.image_name : 'item_noimg.png';
				row.find('a').attr('href', link).addClass(item.rarity);
				row.find('img')
						.attr('src', '/img/item/' + img).attr('alt', item.name_en);
				row.find('span').text(item.name_en);
				itemTbody.append(row);
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
