$(function() {

	// 空スロットの画像
	const default_img = {
		"sword" : "short_sword.png",
		"shield" : "small_shield.png",
		"axe" : "wood_axe.png",
		"mantle" : "mantle.png",
		"dagger" : "dagger.png",
		"ring" : "ring0.png",
		"helm" : "helm_sword.png",
		"armor" : "leather_vest.png",
		"gloves" : "gloves.png",
		"boots" : "boots.png",
		"common" : "item_noimg.png",
		"puppet" : "puppet0.png"
	}

	// スロットのアイテムを削除
	const setNone = function(slot) {
		slot.removeClass("common rare artifact")
			.addClass("item-icon")
			.addClass("common")
			.data("rarity", "common");
		const img = $("<img />")
			.attr("src", "/img/item/" + default_img[slot.data("item-class")])
			.attr("alt", "None");
		const span = $("<span />").text(" " + "None");
		slot.children().remove();
		slot.append(img).append(span);
	}

	// キャラクタークラス変更
	$("select.character-class").on("change", function() {
		switch ($(this).val()) {
			case "1":
				setNone($("a.item-slot1").data("item-class", "sword"));
				setNone($("a.item-slot2").data("item-class", "shield"));
				break;
			case "2":
				setNone($("a.item-slot1").data("item-class", "axe"));
				setNone($("a.item-slot2").data("item-class", "mantle"));
				break;
			case "3":
				setNone($("a.item-slot1").data("item-class", "dagger"));
				setNone($("a.item-slot2").data("item-class", "dagger"));
				break;
		}
	});

	// アイテム選択画面を表示
	$(".table-item-slot a").on("click", function() {
		const target = $(this);
		$.ajax({
			url : "/simulator/rare/" + target.data("item-class"),
			type : "GET"
		}).done(data => {
			$("#table-items tbody").children().remove();
			const link = $("<a />")
				.attr("href", "javascript:void(0)")
				.data("item-class", target.data("item-class"));
			setNone(link);
			$("#table-items tbody").append($("<tr />").append($("<td />").append(link)));
			data.items.rare.forEach(item => {
				const img = $("<img />")
					.attr("src", "/img/item/" + item.image_name)
					.attr("alt", item.name_en)
					.addClass("item-icon");
				const link = $("<a />")
					.attr("href", "javascript:void(0)")
					.addClass("rare")
					.data("name", item.name_en)
					.data("image", item.image_name)
					.data("rarity", "rare")
					.append(img)
					.append($("<span />").text(" " + item.name_en));
				$("#table-items tbody").append($("<tr />").append($("<td />").append(link)));
			});
			data.items.artifact.forEach(item => {
				const img = $("<img />")
					.attr("src", "/img/item/" + item.image_name)
					.attr("alt", item.name_en)
					.addClass("item-icon");
				const link = $("<a />")
					.attr("href", "javascript:void(0)")
					.addClass("artifact")
					.data("name", item.name_en)
					.data("image", item.image_name)
					.data("rarity", "artifact")
					.append(img)
					.append($("<span />").text(" " + item.name_en));
				$("#table-items tbody").append($("<tr />").append($("<td />").append(link)));
			});
			$("#table-items a").on("click", function() {
				target.children().remove();
				target.removeClass("common rare artifact");
				target.addClass($(this).data("rarity"));
				target.append($(this).clone().children());
				$("#modal-items").modal("hide");
			});
			$("#modal-items").modal("show");
		});
	});

	// スロット初期化
	$("select.character-class").change();
	$(".table-item-slot a").toArray().forEach(link => {
		setNone($(link));
	});

});
