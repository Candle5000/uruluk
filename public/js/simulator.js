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
		"freshy" : "toad.png",
		"puppet" : "puppet0.png"
	}

	// 性能の計算
	const calcAttrs = function() {
		const charaClass = $("select.character-class").val();
		const attrs = Object.assign({}, characterAttrs[charaClass]);
		$("ul.item-skill").children().remove();
		let noSkills = true;

		// XP, Killsの上限チェック
		if (parseInt($(".character-xp").val()) > parseInt($(".character-xp").attr("max"))) {
			$(".character-xp").val($(".character-xp").attr("max"));
		}
		if (parseInt($(".character-kills").val()) > parseInt($(".character-kills").attr("max"))) {
			$(".character-kills").val($(".character-kills").attr("max"));
		}

		// GETパラメータの初期化
		let path = "/simulator?c=" + charaClass;
		path += "&xp=" + parseInt($(".character-xp").val());
		path += "&kills=" + parseInt($(".character-kills").val());
		path += "&b[0]=" + parseInt($(".nostrum").val());
		path += "&b[1]=" + parseInt($(".elixir").val());
		path += "&b[2]=" + parseInt($(".giogan").val());
		path += "&b[3]=" + parseInt($(".necter").val());
		path += "&b[4]=" + parseInt($(".hydrabrew").val());

		// ブーストアップを加算
		attrs.str += (parseInt($(".nostrum").val()) + parseInt($(".giogan").val())) * boostUps[charaClass].str;
		attrs.def += (parseInt($(".nostrum").val()) + parseInt($(".hydrabrew").val())) * boostUps[charaClass].def;
		attrs.dex += (parseInt($(".nostrum").val()) + parseInt($(".necter").val())) * boostUps[charaClass].dex;
		attrs.vit += (parseInt($(".nostrum").val()) + parseInt($(".elixir").val())) * boostUps[charaClass].vit;

		// 装備アイテムの性能を加算
		slotItems.forEach((item, index) => {
			if (item !== undefined) {

				// スキルの設定
				if (item.skill_en || item["skill_" + $("select.character-class").val() + "_en"]) {
					const skill = $("<li />").text(item.skill_en ? item.skill_en : item["skill_" + $("select.character-class").val() + "_en"]);
					$("ul.item-skill").append(skill);
					noSkills = false;
				}

				// アイテム性能の設定
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

				// GETパラメータに追加
				if (item.item_id) {
					path += "&s[" + (index - 1) + "]=" + item.item_id;
				}
			}
		});

		// スキル無し
		if (noSkills) {
			$("ul.item-skill").append("<li>No Skills</li>");
		}

		// StrをADに加算
		if (attrs.str > 0) {
			attrs.minad += attrs.str / 2;
			attrs.maxad += attrs.str;
		}

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

		// DPSを計算
		$(".character-dps").text((Math.round((attrs.minad + attrs.maxad) * 1000 / (2 * (attrs.as + 1))) / 1000).toFixed(3));

		// URLに反映
		history.replaceState('', '', path);

		// 短縮URLをリセット
		$(".text-share").val("");
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
		if (item.skill_en || item["skill_" + $("select.character-class").val() + "_en"]) {
			link.closest("div.d-table-row").find(".item-skill")
				.removeClass("d-none")
				.attr("title", (item.skill_en ? item.skill_en : item["skill_" + $("select.character-class").val() + "_en"]));
		} else {
			link.closest("div.d-table-row").find(".item-skill")
				.addClass("d-none")
				.attr("title", "");
		}
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
		if (parseInt($(this).val()) > parseInt($(this).attr("max"))) {
			$(this).val($(this).attr("max"));
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
		$("#collapse-search").collapse('hide');
		$.ajax({
			url : "/simulator/item/" + target.data("item-class"),
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

			// アイテム
			data.items.forEach(item => {
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

			// スキルの表示
			$("#table-items a.item-skill[data-toggle=tooltip]").tooltip();

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
					const modalItem = modalItems[$(this).data("row-index")];
					setItem(target, modalItem);
					if (modalItem.skill_en || modalItem["skill_" + $("select.character-class").val() + "_en"]) {
						const skill = target.closest("div.d-table-row").find(".item-skill");
						const newSkill = $(skill.prop("outerHTML"));
						skill.remove();
						target.closest("div.d-table-row").find(".item-name").after(newSkill);
						newSkill.tooltip();
					}
					slotItems[target.data("slot-index")] = modalItems[$(this).data("row-index")];
				}
				calcAttrs();
				$("#modal-items").modal("hide");
			});

			// モーダル表示
			$("#modal-items").modal("show");
		});
	});

	// シェアURLの選択
	$(".text-share").on('click', function (e) {
		if ($(this).val().length == 0) return;
		e.target.setSelectionRange(0, e.target.value.length);
		document.execCommand("copy");
	});

	// シェアボタン
	$("a.link-share").on('click', function () {
		$.ajax({
			url : "/s",
			type : "POST",
			data : {
				url : location.pathname + location.search.replace(/\[/g, "%5B").replace(/\]/g, "%5D"),
				csrf_name : $("input#csrf_name").val(),
				csrf_value : $("input#csrf_value").val()
			}
		}).done(data => {
			if (data.error) {
				alert(data.error.message);
				return;
			}
			$(".text-share").val(location.origin + "/s/" + data.result.urlKey);
		});
	});

	// キャラクタークラスの選択を初期化
	$("select.character-class").change();

	// スロット初期化
	let itemIndex = 0;
	const initItems = $.parseJSON($("#init-items").html());
	$(".table-item-slot a.item-img").toArray().forEach(link => {
		setItem($(link), initItems[itemIndex]);
		slotItems[itemIndex + 1] = initItems[itemIndex];
		itemIndex++;
	});
	$(".table-item-slot a.item-skill[data-toggle=tooltip]").tooltip();
	calcAttrs();

});
