$(function() {

	// 性能名
	const attrNames = [
		"minad",
		"maxad",
		"as",
		"ar",
		"str",
		"def",
		"dex",
		"vit",
		"ws",
		"sa",
		"voh",
		"dr",
		"xpg"
	];

	// 各スロットのアイテム情報
	const slotItems = [];

	// キャラクタークラスの性能
	const characterAttrs = {
		"sword" : {
			"minad" : 0,
			"maxad" : 0,
			"as" : 18,
			"ar" : 42,
			"str" : 6,
			"def" : 0,
			"dex" : 6,
			"vit" : 100,
			"ws" : 56.55,
			"sa" : 30,
			"voh" : 0,
			"dr" : 0,
			"xpg" : 0
		},
		"axe" : {
			"minad" : 0,
			"maxad" : 0,
			"as" : 25,
			"ar" : 48,
			"str" : 8,
			"def" : 0,
			"dex" : 4,
			"vit" : 120,
			"ws" : 53.55,
			"sa" : 30,
			"voh" : 0,
			"dr" : 0,
			"xpg" : 0
		},
		"dagger" : {
			"minad" : 0,
			"maxad" : 0,
			"as" : 10,
			"ar" : 40,
			"str" : 4,
			"def" : 0,
			"dex" : 8,
			"vit" : 80,
			"ws" : 59.55,
			"sa" : 30,
			"voh" : 0,
			"dr" : 0,
			"xpg" : 0
		}
	};

	// ブーストアップ性能
	const boostUps = {
		"sword" : {
			"str" : 3,
			"def" : 1,
			"dex" : 2,
			"vit" : 12
		},
		"axe" : {
			"str" : 4,
			"def" : 1,
			"dex" : 1,
			"vit" : 15
		},
		"dagger" : {
			"str" : 2,
			"def" : 1,
			"dex" : 3,
			"vit" : 10
		}
	};

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

	// 性能の計算
	const calcAttrs = function() {
		const charaClass = $("select.character-class").val();
		const attrs = Object.assign({}, characterAttrs[charaClass]);

		// 装備アイテムの性能を加算
		slotItems.forEach(item => {
			if (item !== undefined) {
				item.attributes.forEach(attr => {
					if (attr.short_name == "AD") return;
					const valueMax = attr.value === null ? attr["value_" + charaClass] : attr.value;
					const maxRequired = attr.max_required ? attr.max_required : attr["max_required_" + charaClass];
					let value;

					// 性能変動値の計算
					if (attr.based_source && maxRequired && maxRequired != 0 && maxRequired > parseInt($(".character-" + attr.based_source).val())) {
						value = Math.round(valueMax * parseInt($(".character-" + attr.based_source).val()) * 100 / maxRequired) / 100;
					} else {
						value = valueMax;
					}

					attrs[attr.short_name.toLowerCase()] += parseFloat(value);
				});
			}
		});

		// ブーストアップを加算
		attrs.str += (parseInt($(".nostrum").val()) + parseInt($(".giogan").val())) * boostUps[charaClass].str;
		attrs.def += (parseInt($(".nostrum").val()) + parseInt($(".hydrabrew").val())) * boostUps[charaClass].def;
		attrs.dex += (parseInt($(".nostrum").val()) + parseInt($(".necter").val())) * boostUps[charaClass].dex;
		attrs.vit += (parseInt($(".nostrum").val()) + parseInt($(".elixir").val())) * boostUps[charaClass].vit;

		// StrをADに加算
		attrs.minad += attrs.str / 2;
		attrs.maxad += attrs.str;

		// 画面に反映
		attrNames.forEach(attrName => {
			if (attrs[attrName] < 0) {
				attrs[attrName] = 0;
			}
			$(".table-attributes .attr-" + attrName).text(Math.round(attrs[attrName]));

			// No Delay判定
			if (attrName == "sa") {
				if (attrs[attrName] == 0) {
					$(".sa-normal").addClass("d-none");
					$(".sa-nodelay").removeClass("d-none");
				} else {
					$(".sa-normal").removeClass("d-none");
					$(".sa-nodelay").addClass("d-none");
				}
			}
		});
	}

	// スロットにアイテムを設定
	const setItem = function(link, item) {
		const img = $("<img />")
			.attr("src", "/img/item/" + item.image_name)
			.attr("alt", item.name_en)
			.addClass("item-icon");
		link.closest("div.d-table-row").find(".item-name").removeClass("common rare artifact")
			.addClass(item.rarity)
			.text(" " + item.name_en);
		link.children().remove();
		link.append(img);
		link.closest("div.d-table-row").find(".attr").children().remove();
		link.closest("div.d-table-row").find(".wave").addClass("d-none");
		const charaClass = $("select.character-class").val();
		item.attributes.forEach((attr, index, attrs) => {
			if (attr.short_name == "AD") return;
			if (attr.short_name == "MinAD") link.closest("div.d-table-row").find(".wave").removeClass("d-none");
			const valueMax = attr.value === null ? attr["value_" + charaClass] : attr.value;
			const attr_cell = link.closest("div.d-table-row").find(".attr-" + attr.short_name.toLowerCase());
			const maxRequired = attr.max_required ? attr.max_required : attr["max_required_" + charaClass];
			let value;
			let color = attr.color;

			// 性能変動値の計算
			if (attr.based_source && maxRequired && maxRequired != 0 && maxRequired > parseInt($(".character-" + attr.based_source).val())) {
				value = Math.round(valueMax * parseInt($(".character-" + attr.based_source).val()) * 10 / maxRequired) / 10;
			} else {
				value = parseFloat(valueMax == null ? 0 : valueMax);
			}

			// 性能値の合計を計算
			if (attr_cell.children().length > 0 && value >= 0) {
				const baseVal = parseFloat($(attr_cell.children()[0]).text());
				if (attrs[index - 1].color != attr.color && Math.abs(baseVal) > Math.abs(value)) {
					color = attrs[index - 1].color;
				}
				value = Math.round((value + baseVal) * 10) / 10;
			}

			// 計算結果を反映
			const span = $("<span />")
				.addClass(color)
				.text(value);
			attr_cell.children().remove();
			attr_cell.append(span);
		});
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
			case "sword":
				setNone($("a.item-slot1").data("item-class", "sword"));
				setNone($("a.item-slot2").data("item-class", "shield"));
				break;
			case "axe":
				setNone($("a.item-slot1").data("item-class", "axe"));
				setNone($("a.item-slot2").data("item-class", "mantle"));
				break;
			case "dagger":
				setNone($("a.item-slot1").data("item-class", "dagger"));
				setNone($("a.item-slot2").data("item-class", "dagger"));
				break;
		}
		slotItems[1] = undefined;
		slotItems[2] = undefined;
		$(".table-item-slot a").toArray().forEach(link => {
			if (slotItems[$(link).data("slot-index")] !== undefined) {
				setItem($(link), slotItems[$(link).data("slot-index")]);
			}
		});
		calcAttrs();
	});

	// ブーストアップ, XP, Kills変更
	$("input.boostup,input.character-xp,input.character-kills").on("change", function() {
		if (!Number.isInteger(parseInt($(this).val())) || $(this).val() < 0) {
			$(this).val(0);
			return;
		}
		$(".table-item-slot a").toArray().forEach(link => {
			if (slotItems[$(link).data("slot-index")] !== undefined) {
				setItem($(link), slotItems[$(link).data("slot-index")]);
			}
		});
		calcAttrs();
	});

	// アイテム選択画面を表示
	$(".table-item-slot a.item-name").on("click", function() {
		$(this).closest("div.d-table-row").find("a.item-img").click();
	});
	$(".table-item-slot a.item-img").on("click", function() {
		const target = $(this);
		$.ajax({
			url : "/simulator/rare/" + target.data("item-class"),
			type : "GET"
		}).done(data => {
			// アイテム情報保持
			const modalItems = [];

			// 項目名行
			let count = 1;

			// アイテムリストを削除
			$("#table-items>tbody>tr").remove();

			// Noneの選択肢を作成
			const row_none = $($("#modal-item-row").html());
			row_none.find("a.item-img").data("item-class", target.data("item-class"));
			setNone(row_none.find("a.item-img"));
			$("#table-items tbody").append(row_none);

			// Rareアイテム
			data.items.rare.forEach(item => {
				const row = $($("#modal-item-row").html());
				setItem(row.find("a.item-img"), item);
				$("#table-items tbody").append(row);
				row.find("a.item-img").data("row-index", modalItems.push(item) - 1);
				count++;
				if (count % 10 == 0) {
					const labelRow = $($("#table-items thead").html());
					$("#table-items tbody").append(labelRow);
				}
			});

			// Artifactアイテム
			data.items.artifact.forEach(item => {
				const row = $($("#modal-item-row").html());
				setItem(row.find("a.item-img"), item);
				$("#table-items tbody").append(row);
				row.find("a.item-img").data("row-index", modalItems.push(item) - 1);
				count++;
				if (count % 10 == 0) {
					const labelRow = $($("#table-items thead").html());
					$("#table-items tbody").append(labelRow);
				}
			});

			// クリックイベント付与
			$("#table-items a.item-name").on("click", function() {
				$(this).closest("div.d-table-row").find("a.item-img").click();
			});
			$("#table-items a.item-img").on("click", function() {
				const itemClass = target.data("item-class");
				if ($(this).data("row-index") === undefined) {
					setNone(target);
					slotItems[target.data("slot-index")] = undefined;
				} else {
					setItem(target, modalItems[$(this).data("row-index")]);
					slotItems[target.data("slot-index")] = modalItems[$(this).data("row-index")];
				}
				calcAttrs();
				$("#modal-items").modal("hide");
			});

			// モーダル表示
			$("#modal-items").modal("show");
		});
	});

	// スロット初期化
	$(".table-item-slot a.item-img").toArray().forEach(link => {
		setNone($(link));
	});
	$("select.character-class").change();

});
