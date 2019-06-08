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

	// スロットにアイテムを設定
	const setItem = function(link, item) {
		const img = $("<img />")
			.attr("src", "/img/item/" + item.image_name)
			.attr("alt", item.name_en)
			.addClass("item-icon");
		const span = $("<span />")
			.data("rarity", item.rarity)
			.text(" " + item.name_en);
		link.removeClass("common rare artifact")
			.addClass("item-icon")
			.addClass(item.rarity)
			.children().remove();
		link.append(img).append(span);
	}

	// スロットにNoneを設定
	const setNone = function(link) {
		const item = {
			"name_en" : "None",
			"rarity" : "common",
			"image_name" : default_img[link.data("item-class")],
			"attributes" : []
		}
		setItem(link, item);
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
			// アイテムリストを削除
			$("#table-items tbody").children().remove();

			// Noneの選択肢を作成
			const link = $("<a />")
				.attr("href", "javascript:void(0)")
				.data("item-class", target.data("item-class"));
			setNone(link);
			$("#table-items tbody").append($("<tr />").append($("<td />").append(link)));

			// Rareアイテム
			data.items.rare.forEach(item => {
				const link = $("<a />")
					.attr("href", "javascript:void(0)");
				setItem(link, item);
				$("#table-items tbody").append($("<tr />").append($("<td />").append(link)));
			});

			// Artifactアイテム
			data.items.artifact.forEach(item => {
				const link = $("<a />")
					.attr("href", "javascript:void(0)");
				setItem(link, item);
				$("#table-items tbody").append($("<tr />").append($("<td />").append(link)));
			});

			// クリックイベント付与
			$("#table-items a").on("click", function() {
				target.children().remove();
				target.removeClass("common rare artifact");
				target.addClass($(this).find("span").data("rarity"));
				target.append($(this).clone().children());
				$("#modal-items").modal("hide");
			});

			// モーダル表示
			$("#modal-items").modal("show");
		});
	});

	// スロット初期化
	$("select.character-class").change();
	$(".table-item-slot a").toArray().forEach(link => {
		setNone($(link));
	});

});
