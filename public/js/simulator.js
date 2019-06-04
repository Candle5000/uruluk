$(function() {

	// キャラクタークラス変更
	$("select.character-class").on("change", function() {
		switch ($(this).val()) {
			case "1":
				$("a.item-slot1").data("item-class", "sword");
				$("a.item-slot2").data("item-class", "shield");
				break;
			case "2":
				$("a.item-slot1").data("item-class", "axe");
				$("a.item-slot2").data("item-class", "mantle");
				break;
			case "3":
				$("a.item-slot1").data("item-class", "dagger");
				$("a.item-slot2").data("item-class", "dagger");
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
			const img = $("<img />")
				.attr("src", "/img/item/item_noimg.png")
				.attr("alt", "None")
				.addClass("item-icon");
			const link = $("<a />")
				.attr("href", "javascript:void(0)")
				.addClass("text-white")
				.data("name", "None")
				.data("image", "item_noimg.png")
				.append(img)
				.append($("<span />").text(" " + "None"));
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
				target.removeClass("text-white rare artifact");
				target.addClass($(this).data("rarity"));
				target.append($(this).children());
				$("#modal-items").modal("hide");
			});
			$("#modal-items").modal("show");
		});
	});

});
