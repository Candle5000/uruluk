$(function () {

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
    "sad",
    "minsad",
    "maxsad",
    "voh",
    "dr",
    "xpg"
  ];

  // 各スロットのアイテム情報
  const slotItems = [];

  // キャラクタークラスの性能
  const characterAttrs = {
    "sword": {
      "minad": 0,
      "maxad": 0,
      "as": 18,
      "ar": 42,
      "str": 6,
      "def": 0,
      "dex": 6,
      "vit": 100,
      "ws": 56.55,
      "sa": 30,
      "sad": 150,
      "minsad": 0,
      "maxsad": 0,
      "voh": 0,
      "dr": 0,
      "xpg": 0
    },
    "axe": {
      "minad": 0,
      "maxad": 0,
      "as": 25,
      "ar": 48,
      "str": 8,
      "def": 0,
      "dex": 4,
      "vit": 120,
      "ws": 53.55,
      "sa": 30,
      "sad": 300,
      "minsad": 0,
      "maxsad": 0,
      "voh": 0,
      "dr": 0,
      "xpg": 0
    },
    "dagger": {
      "minad": 0,
      "maxad": 0,
      "as": 10,
      "ar": 40,
      "str": 4,
      "def": 0,
      "dex": 8,
      "vit": 80,
      "ws": 59.55,
      "sa": 30,
      "sad": 300,
      "minsad": 0,
      "maxsad": 0,
      "voh": 0,
      "dr": 0,
      "xpg": 0
    }
  };

  // ブーストアップ性能
  const boostUps = {
    "sword": {
      "str": 3,
      "def": 1,
      "dex": 2,
      "vit": 12
    },
    "axe": {
      "str": 4,
      "def": 1,
      "dex": 1,
      "vit": 15
    },
    "dagger": {
      "str": 2,
      "def": 1,
      "dex": 3,
      "vit": 10
    }
  };

  // 空スロットの画像
  const default_img = {
    "sword": "short_sword.png",
    "shield": "small_shield.png",
    "axe": "wood_axe.png",
    "mantle": "mantle.png",
    "dagger": "dagger.png",
    "ring": "ring0.png",
    "helm": "helm_sword.png",
    "armor": "leather_vest.png",
    "gloves": "gloves.png",
    "boots": "boots.png",
    "freshy": "toad.png",
    "puppet": "puppet0.png"
  }

  // ビルドの最大保存数
  const savedBuildsMax = 100;

  // 性能の計算
  const calcAttrs = function () {
    const charaClass = $("select.character-class").val();
    const attrs = Object.assign({}, characterAttrs[charaClass]);
    $("ul.item-skill").children(".item-passive-skill").remove();
    $(".table-attributes .skill-reduce-label").addClass("d-none");
    const skills = [];
    const skillCharacterClass = "skill_" + $("select.character-class").val();
    let reduceDamage = 0;

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
        if (item.skill || item[skillCharacterClass]) {
          const itemSkill = item.skill ? item.skill : item[skillCharacterClass];
          const skillTag = $("<li />");
          skillTag.addClass("item-passive-skill");
          const skillText = item.skill ? item.skill.description : item[skillCharacterClass].description;
          if (itemSkill.trigger_charge > 0) {
            skillTag.addClass("form-check charge-type-skill");
            const skillCheckbox = $("<input />").attr("type", "checkbox").attr("id", "item-skill-" + index).addClass("form-check-input item-skill-check").data("slot-index", index);
            if (itemSkill.enabled) {
              skillCheckbox.attr("checked", "checked");
            }
            skillTag.append(skillCheckbox);
            skillTag.append($("<label />").attr("for", "item-skill-" + index).addClass("form-check-label").text(skillText));
          } else {
            if (itemSkill.effect_type === "reduce") {
              reduceDamage += Number(itemSkill.effect_amount);
            }
            skillTag.text(skillText);
          }
          const skillSortKey = itemSkill.sort_key;
          skills.push({ tag: skillTag, sortKey: skillSortKey });
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

    // スキルを表示
    if (skills.length === 0) {
      $("ul.item-skill .no-skills").removeClass("d-none");
    } else {
      $("ul.item-skill .no-skills").addClass("d-none");
      skills.sort((a, b) => a.sortKey - b.sortKey).forEach(skill => {
        $("ul.item-skill").append(skill.tag);
      });
      $("input.item-skill-check").on("click", function () {
        const item = slotItems[$(this).data("slot-index")];
        const itemSkill = item.skill ? item.skill : item[skillCharacterClass];
        itemSkill.enabled = $(this).prop("checked");
        calcAttrs();
      });
    }

    // チャージ型のパッシブスキル効果を計算
    $(".attr-value").removeClass("item-skill");
    $("input.item-skill-check:checked").toArray().forEach(checkbox => {
      const item = slotItems[$(checkbox).data("slot-index")];
      const itemSkill = item.skill ? item.skill : item[skillCharacterClass];
      const skillAttr = itemSkill.effect_target_attribute;
      if (skillAttr === "sa") {
        attrs[skillAttr] = attrs[skillAttr] + Number(itemSkill.effect_amount);
      } else if (skillAttr === "as") {
        attrs[skillAttr] = attrs[skillAttr] * 100 / itemSkill.effect_amount;
      } else {
        attrs[skillAttr] = attrs[skillAttr] * itemSkill.effect_amount / 100;
      }
      $(".attr-value.attr-" + skillAttr + ",.attr-value:has(.attr-" + skillAttr + ")").addClass("item-skill");
    });

    // ダメージ軽減スキル効果を計算
    if (reduceDamage > 0) {
      reduceDamageValue = attrs.def * reduceDamage / 100;
      $(".table-attributes .skill-reduce-label").removeClass("d-none");
      $(".table-attributes .skill-reduce").text(Math.round(reduceDamageValue));
    }

    // StrをADに加算
    if (attrs.str > 0) {
      attrs.minad += attrs.str / 2;
      attrs.maxad += attrs.str;
    }

    // SADを計算
    attrs.minsad = attrs.minad * attrs.sad / 100;
    attrs.maxsad = attrs.maxad * attrs.sad / 100;

    // 画面に反映
    attrNames.forEach(attrName => {
      if (attrs[attrName] < 0) {
        attrs[attrName] = 0;
      }
      $(".table-attributes .attr-" + attrName).text(Math.round(attrs[attrName] - 0.0000005));

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
    const asTmp = attrs.as > 1 ? attrs.as : 1;
    const attackSpeed = asTmp < 10 ? asTmp + asTmp / 2 ** (asTmp - 2) : asTmp;
    const avgAD = (attrs.minad + attrs.maxad) / 2;
    const dps = (Math.round(avgAD / attackSpeed * 30 * 1000) / 1000).toFixed(3);
    $(".character-dps").text(dps);

    // URLに反映
    history.replaceState('', '', path);

    // 短縮URLをリセット
    $(".text-share").val("");
  }

  // スロットにアイテムを設定
  const setItem = function (link, item) {
    const img = $("<img />")
      .attr("src", "/img/item/" + item.image_name)
      .attr("alt", item.name)
      .addClass("item-icon");
    link.closest("div.d-table-row").find(".item-name").removeClass("common rare artifact")
      .addClass(item.rarity)
      .text(" " + item.name);
    if (item.skill || item["skill_" + $("select.character-class").val()]) {
      link.closest("div.d-table-row").find(".item-skill")
        .removeClass("d-none")
        .attr("title", (item.skill ? item.skill.description : item["skill_" + $("select.character-class").val()].description));
    } else {
      link.closest("div.d-table-row").find(".item-skill")
        .addClass("d-none")
        .attr("title", "");
    }
    if (item.item_id) {
      link.closest("div.d-table-row").find(".item-link")
        .attr("href", "/items/" + item.item_class_name + "/" + (item.rarity == "common" ? item.base_item_id : "rare") + "/" + item.item_id)
        .removeClass("d-none");
    } else {
      link.closest("div.d-table-row").find(".item-link")
        .attr("href", "#")
        .addClass("d-none");
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
      if (attr_cell.children().length > 0) {
        const baseVal = parseFloat($(attr_cell.children()[0]).text());
        if (attrs[index - 1].color != "white" && attrs[index - 1].color != attr.color && Math.abs(baseVal) > Math.abs(value) || Math.abs(value) == 0) {
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
  const setNone = function (link) {
    const item = {
      "name": $('#set-none-name').data('message'),
      "rarity": "common",
      "image_name": default_img[link.data("item-class")],
      "attributes": []
    }
    setItem(link, item);
  }

  // キャラクタークラス変更
  $("select.character-class").on("change", function () {
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
  $("input.boostup,input.character-xp,input.character-kills").on("change", function () {
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

  // アイテム情報保持
  let modalItems = [];
  let modalItemIndex = 0;
  const loadItemCount = 50; // 一度に読み込むアイテム数
  let loading = true;
  let target = null;
  let currentSortOrder = 1;
  let currentSortAttr = '';

  // スクロール読み込み
  $("#modal-items").on("scroll", function () {
    if ($("#modal-items").hasClass("show") && !loading && modalItemIndex < modalItems.length
      && ($("#modal-items").height() + $("#modal-items").scrollTop() > $("#scroll-loading").position().top)) {
      loading = true;
      showItems();
    }
  });

  // ソート順変更
  $(".thead-sort").on("click", function () {
    // ロード開始
    $.LoadingOverlay("show", {
      background: "rgba(0, 0, 0, 0.5)",
      imageColor: "#787878",
    });

    const attrName = $(this).data("attr");
    const charaClass = $("select.character-class").val();
    modalItemIndex = 0;
    modalItems.shift();

    // ソート順の変更
    if (currentSortAttr == attrName) {
      currentSortOrder *= -1;
    } else {
      currentSortOrder = (attrName == 'AS' || attrName == 'SA' || attrName == 'Skill') ? -1 : 1;
    }
    currentSortAttr = attrName;

    // アイテムリストを削除
    $("#table-items>tbody>tr").remove();
    $("#scroll-loading").removeClass("d-none").addClass("d-flex");

    // アイテムをソート
    modalItems.sort((a, b) => {
      let a_val = 0.0;
      let b_val = 0.0;

      if (attrName == 'Skill') {
        const a_skill = a.skill ? a.skill : a["skill_" + charaClass];
        const b_skill = b.skill ? b.skill : b["skill_" + charaClass];
        a_val = a_skill === null ? -9999999 * currentSortOrder : a_skill.sort_key;
        b_val = b_skill === null ? -9999999 * currentSortOrder : b_skill.sort_key;
      } else {
        a.attributes.forEach(attr => {
          if (attrName == 'AD') {
            if (attr.short_name != 'MinAD' && attr.short_name != 'MaxAD' && attr.short_name != 'Str') return;
          } else {
            if (attr.short_name != attrName) return;
          }
          const valueMax = attr.value === null ? attr["value_" + charaClass] : attr.value;
          const maxRequired = attr.max_required ? attr.max_required : attr["max_required_" + charaClass];
          let value;
          let color = attr.color;

          // 性能変動値の計算
          if (attr.based_source && maxRequired && maxRequired != 0 && maxRequired > parseInt($(".character-" + attr.based_source).val())) {
            value = Math.round(valueMax * parseInt($(".character-" + attr.based_source).val()) * 10 / maxRequired) / 10;
          } else {
            value = parseFloat(valueMax == null ? 0 : valueMax);
          }

          // ADの場合、Strを換算
          if (attrName == 'AD' && attr.short_name == 'Str') {
            value *= 1.5;
          }

          // 合計を計算
          a_val += value;
        });

        b.attributes.forEach(attr => {
          if (attrName == 'AD') {
            if (attr.short_name != 'MinAD' && attr.short_name != 'MaxAD' && attr.short_name != 'Str') return;
          } else {
            if (attr.short_name != attrName) return;
          }
          const valueMax = attr.value === null ? attr["value_" + charaClass] : attr.value;
          const maxRequired = attr.max_required ? attr.max_required : attr["max_required_" + charaClass];
          let value;
          let color = attr.color;

          // 性能変動値の計算
          if (attr.based_source && maxRequired && maxRequired != 0 && maxRequired > parseInt($(".character-" + attr.based_source).val())) {
            value = Math.round(valueMax * parseInt($(".character-" + attr.based_source).val()) * 10 / maxRequired) / 10;
          } else {
            value = parseFloat(valueMax == null ? 0 : valueMax);
          }

          // ADの場合、Strを換算
          if (attrName == 'AD' && attr.short_name == 'Str') {
            value *= 1.5;
          }

          // 合計を計算
          b_val += value;
        });
      }

      if (a_val < b_val) return 1 * currentSortOrder;
      if (a_val > b_val) return -1 * currentSortOrder;
      if (a.sort_key < b.sort_key) return -1;
      if (a.sort_key > b.sort_key) return 1;
      return 0;
    });

    // Noneを追加
    if (target.data("slot-index") != 0) {
      modalItems.unshift(null);
    }

    // アイテムリストの表示
    showItems();

    // ロード完了
    $.LoadingOverlay("hide", true);
  });

  // アイテムリストの表示
  const showItems = function () {
    // アイテム
    const startIndex = modalItemIndex;
    for (let i = startIndex; i < modalItems.length && (i == startIndex || i % loadItemCount != 0); i++) {
      const item = modalItems[i];

      // アイテムの行を作成
      if (item == null) {
        // None行
        const row_none = $($("#modal-item-row").html());
        row_none.find("a.item-img").data("item-class", target.data("item-class"));
        setNone(row_none.find("a.item-img"));
        $("#table-items tbody").append(row_none);
      } else {
        const row = $($("#modal-item-row").html());
        setItem(row.find("a.item-img"), item);
        $("#table-items tbody").append(row);
        row.find("a.item-skill[data-toggle=tooltip]").tooltip();
        row.find("a.item-img").data("row-index", i);
      }

      modalItemIndex++;
      if (modalItemIndex % 10 == 0) {
        const labelRow = $($("#modal-label-row").html());
        $("#table-items tbody").append(labelRow);
      }
    }

    // 読み込み完了
    loading = false;
    if (modalItemIndex >= modalItems.length) {
      $("#scroll-loading").removeClass("d-flex").addClass("d-none");
    }

    // クリックイベント付与
    $("#table-items a.item-name").off("click");
    $("#table-items a.item-name").on("click", function () {
      $(this).closest("div.d-table-row").find("a.item-img").click();
    });
    $("#table-items a.item-img").off("click");
    $("#table-items a.item-img").on("click", function () {
      const itemClass = target.data("item-class");
      if ($(this).data("row-index") === undefined) {
        setNone(target);
        slotItems[target.data("slot-index")] = undefined;
      } else {
        const modalItem = modalItems[$(this).data("row-index")];

        // 対象装備枠の取得
        let targetSlot;
        if (target.data("slot-index") == 0) {
          const targetSlots = $(".table-item-slot a").toArray().filter(link => {
            return $(link).data("item-class") == modalItem.item_class_name;
          });
          for (let i = 0; i < targetSlots.length; i++) {
            if (slotItems[$(targetSlots[i]).data("slot-index")] === undefined
              || slotItems[$(targetSlots[i]).data("slot-index")].item_id === undefined) {
              targetSlot = $(targetSlots[i]);
              break;
            }
            if (i + 1 == targetSlots.length) {
              targetSlot = $(targetSlots[i]);
            }
          }
        } else {
          targetSlot = target;
        }

        setItem(targetSlot, modalItem);
        if (modalItem.skill || modalItem["skill_" + $("select.character-class").val()]) {
          const skill = targetSlot.closest("div.d-table-row").find(".item-skill");
          const newSkill = $(skill.prop("outerHTML"));
          skill.remove();
          targetSlot.closest("div.d-table-row").find(".item-name").after(newSkill);
          newSkill.tooltip();
        }
        slotItems[targetSlot.data("slot-index")] = modalItem;
      }
      calcAttrs();
      $("#modal-items").modal("hide");
    });
  }

  // アイテム選択画面を表示
  $(".table-item-slot a.item-name").on("click", function () {
    $(this).closest("div.d-table-row").find("a.item-img").click();
  });
  $(".table-item-slot a.item-img").on("click", function () {
    target = $(this);
    const rarity = [];
    const charaClass = $("select.character-class").val();

    // 検索条件の設定
    if (target.data("item-class") == "freshy" || target.data("item-class") == "puppet") {
      if ($("#modal-items").hasClass("show")) {
        if ($("#search-rarity-common").prop("checked")) rarity.push("common");
        if ($("#search-rarity-rare").prop("checked")) rarity.push("rare");
        if ($("#search-rarity-artifact").prop("checked")) rarity.push("artifact");
      } else {
        rarity.push("common", "rare", "artifact");
        $("#search-rarity-common").prop("checked", true);
        $("#search-rarity-rare").prop("checked", true);
        $("#search-rarity-artifact").prop("checked", true);
        $("#search-obtained-items").prop("checked", false);
        $("#search-obtained-items-form").addClass("d-none");
      }
    } else {
      const searchConditions = loadSearchConditionsFromLocalStorage();
      if ($("#modal-items").hasClass("show")) {
        searchConditions.conditions.common = $("#search-rarity-common").prop("checked");
        searchConditions.conditions.rare = $("#search-rarity-rare").prop("checked");
        searchConditions.conditions.artifact = $("#search-rarity-artifact").prop("checked");
        searchConditions.conditions.obtained = $("#search-obtained-items").prop("checked");
        saveSearchConditionsToLocalStorage(searchConditions);
      }
      $("#search-rarity-common").prop("checked", searchConditions.conditions.common);
      $("#search-rarity-rare").prop("checked", searchConditions.conditions.rare);
      $("#search-rarity-artifact").prop("checked", searchConditions.conditions.artifact);
      $("#search-obtained-items").prop("checked", searchConditions.conditions.obtained);
      $("#search-obtained-items-form").removeClass("d-none");
      if (searchConditions.conditions.common) rarity.push("common");
      if (searchConditions.conditions.rare) rarity.push("rare");
      if (searchConditions.conditions.artifact) rarity.push("artifact");
    }
    $("#link-search-submit").data("item-slot", target.data("slot-index"));
    $("#collapse-search").collapse('show');
    $.ajax({
      url: "/simulator/item/" + target.data("item-class"),
      type: "GET",
      data: {
        "rarity": rarity,
        "characterClass": charaClass
      }
    }).done(data => {
      modalItemIndex = 0;
      modalItems = target.data("slot-index") == 0 ? [] : [null];
      currentSortAttr = '';
      const obtainedItems = loadObtainedItemsObjectFromLocalStorage();
      // HTMLエスケープをデコード
      data.items.forEach(item => {
        item.name = $("<div/>").html(item.name).text();
      });
      Array.prototype.push.apply(modalItems, data.items.filter(item => {
        // 入手済みアイテムをフィルタ
        if ($("#search-obtained-items").prop("checked")) {
          const storableItemId = item.class_flactuable > 0 ? item.class_flactuable_base_id : item.item_id;
          const targetItemClass = target.data("item-class");
          if (!obtainedItems.items[storableItemId]) return false;
          if (targetItemClass == "all") {
            let targetSlotIndex;
            const targetSlots = $(".table-item-slot a").toArray().filter(link => {
              return $(link).data("item-class") == item.item_class_name;
            });
            for (let i = 0; i < targetSlots.length; i++) {
              if (slotItems[$(targetSlots[i]).data("slot-index")] === undefined
                || slotItems[$(targetSlots[i]).data("slot-index")].item_id === undefined) {
                targetSlotIndex = $(targetSlots[i]).data("slot-index");
                break;
              }
              if (i + 1 == targetSlots.length) {
                targetSlotIndex = $(targetSlots[i]).data("slot-index");
              }
            }
            return slotItems.filter((slotItem, slotIndex) => {
              if (slotItem === undefined || slotItem.item_id === undefined) return false;
              const slotItemStorableItemId = slotItem.class_flactuable > 0 ? slotItem.class_flactuable_base_id : slotItem.item_id;
              return targetSlotIndex != slotIndex && storableItemId == slotItemStorableItemId;
            }).length < obtainedItems.items[storableItemId];
          } else if (targetItemClass == "dagger" || targetItemClass == "ring") {
            return slotItems.filter((slotItem, slotIndex) => {
              if (slotItem === undefined || slotItem.item_id === undefined) return false;
              const slotItemStorableItemId = slotItem.class_flactuable > 0 ? slotItem.class_flactuable_base_id : slotItem.item_id;
              return target.data('slot-index') != slotIndex && storableItemId == slotItemStorableItemId;
            }).length < obtainedItems.items[storableItemId];
          }
          return obtainedItems.items[storableItemId] > 0;
        } else {
          return true;
        }
      }));
      $("#scroll-loading").removeClass("d-none").addClass("d-flex");

      // アイテムリストを削除
      $("#table-items>tbody>tr").remove();

      // アイテムリストの表示
      showItems();

      // モーダル表示
      $("#modal-items").modal("show");
    });
  });

  // アイテム検索実行
  $("#link-search-submit").on('click', function () {
    $("a.item-slot" + $(this).data("item-slot")).click();
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
      url: "/s",
      type: "POST",
      data: {
        url: location.pathname + location.search.replace(/\[/g, "%5B").replace(/\]/g, "%5D"),
        csrf_name: $("input#csrf_name").val(),
        csrf_value: $("input#csrf_value").val()
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
  // HTMLエスケープをデコード
  initItems.forEach(item => {
    item.name = $("<div/>").html(item.name).text();
  });
  $(".table-item-slot a.item-img:not(.search-all)").toArray().forEach(link => {
    setItem($(link), initItems[itemIndex]);
    slotItems[itemIndex + 1] = initItems[itemIndex];
    itemIndex++;
  });
  $(".table-item-slot a.item-skill[data-toggle=tooltip]").tooltip();
  calcAttrs();

  // 保存画面を表示
  $("a.link-save").on("click", function () {
    // ロード開始
    $.LoadingOverlay("show", {
      background: "rgba(0, 0, 0, 0.5)",
      imageColor: "#787878",
    });

    slotItems.forEach((item, index) => {
      if (item && item.item_id) {
        $("#table-current-build li.item-slot-" + index + " img")
          .attr("src", "/img/item/" + item.image_name)
          .attr("alt", item.name);
      } else {
        $("#table-current-build li.item-slot-" + index + " img")
          .attr("src", "/img/common/other.png")
          .attr("alt", "");
      }
    });
    loadSavedBuilds();

    $("#modal-save").modal("show");

    // ロード完了
    $.LoadingOverlay("hide", true);
  });

  // ローカルストレージから所持済みアイテムを取得する
  const loadObtainedItemsObjectFromLocalStorage = function () {
    let savedItemsStr = window.localStorage.getItem("obtained-items");
    if (savedItemsStr == null) savedItemsStr = JSON.stringify({ "items": {} });
    return $.parseJSON(savedItemsStr);
  }

  // ローカルストレージから検索条件を取得する
  const loadSearchConditionsFromLocalStorage = function () {
    let savedSearchConditionsStr = window.localStorage.getItem("simulator-search-conditions");
    if (savedSearchConditionsStr == null) savedSearchConditionsStr = JSON.stringify({
      "conditions": {
        "common": false,
        "rare": true,
        "artifact": true,
        "obtained": false
      }
    });
    return $.parseJSON(savedSearchConditionsStr);
  }

  // ローカルストレージに検索条件を保存する
  const saveSearchConditionsToLocalStorage = function (conditions) {
    window.localStorage.setItem("simulator-search-conditions", JSON.stringify(conditions));
  }

  // ローカルストレージからビルドを取得する
  const loadSavedBuildsObjectFromLocalStorage = function () {
    let savedBuildsStr = window.localStorage.getItem("saved-builds");
    if (savedBuildsStr == null) savedBuildsStr = JSON.stringify({ "builds": [] });
    return $.parseJSON(savedBuildsStr);
  }

  // ローカルストレージにビルドを保存する
  const saveBuildsToLocalStorage = function (builds) {
    window.localStorage.setItem("saved-builds", JSON.stringify(builds));
  }

  // ビルドの保存
  $("#modal-save a.link-save-current-build").on("click", function () {
    // ロード開始
    $.LoadingOverlay("show", {
      background: "rgba(0, 0, 0, 0.5)",
      imageColor: "#787878",
    });

    const characterClass = $("select.character-class").val();
    const buildName = $("#modal-save input.text-save-name").val();
    const images = [];
    slotItems.forEach((item) => {
      if (item && item.item_id) {
        images.push(item.image_name);
      } else {
        images.push(null);
      }
    });
    const path = location.pathname + location.search.replace(/\[/g, "%5B").replace(/\]/g, "%5D");

    const savedBuilds = loadSavedBuildsObjectFromLocalStorage();
    if (savedBuilds.builds.length < savedBuildsMax) {
      savedBuilds.builds.push({
        "characterClass": characterClass,
        "buildName": buildName,
        "images": images,
        "path": path
      })
    }
    saveBuildsToLocalStorage(savedBuilds);
    loadSavedBuilds();

    // ロード完了
    $.LoadingOverlay("hide", true);
  });

  // ローカルストレージが別タブで更新された場合
  window.addEventListener("storage", function () {
    $("#modal-save").modal("hide");
  });

  // 保存済みビルドの読み込み
  const loadSavedBuilds = function () {
    $("#table-saved-build>tbody>tr").remove();

    const savedBuilds = loadSavedBuildsObjectFromLocalStorage();
    savedBuilds.builds.forEach((build, index) => {
      const row = $($("#modal-build-row").html());
      row.find(".class-icon").attr("src", "/img/common/" + build.characterClass + ".png");
      row.find(".build-name").text(build.buildName);
      build.images.forEach((image, index) => {
        const src = image == null ? "/img/common/other.png" : "/img/item/" + image;
        row.find(".item-slot-" + (index + 1) + " img").attr("src", src);
      });
      row.find(".link-load-build").attr("href", build.path);
      row.find(".link-delete-build").on("click", function () {
        deleteSavedBuild(index);
      });
      row.find(".link-move-up-build").on("click", function () {
        moveUpSavedBuild(index);
      })
      row.find(".link-move-down-build").on("click", function () {
        moveDownSavedBuild(index);
      })
      $("#table-saved-build>tbody").append(row);
    })

    $("#table-saved-build .builds-count").text(savedBuilds.builds.length + " / " + savedBuildsMax);
    if (savedBuilds.builds.length < savedBuildsMax) {
      $("#table-saved-build .builds-count").removeClass("text-warning");
      $("#modal-save a.link-save-current-build").removeClass("d-none");
    } else {
      $("#table-saved-build .builds-count").addClass("text-warning");
      $("#modal-save a.link-save-current-build").addClass("d-none");
    }
  }

  const deleteSavedBuild = function (buildIndex) {
    // ロード開始
    $.LoadingOverlay("show", {
      background: "rgba(0, 0, 0, 0.5)",
      imageColor: "#787878",
    });

    const savedBuilds = loadSavedBuildsObjectFromLocalStorage();
    savedBuilds.builds.splice(buildIndex, 1);
    saveBuildsToLocalStorage(savedBuilds);
    loadSavedBuilds();

    // ロード完了
    $.LoadingOverlay("hide", true);
  }

  const moveUpSavedBuild = function (buildIndex) {
    if (buildIndex == 0) return;

    // ロード開始
    $.LoadingOverlay("show", {
      background: "rgba(0, 0, 0, 0.5)",
      imageColor: "#787878",
    });

    const savedBuilds = loadSavedBuildsObjectFromLocalStorage();
    savedBuilds.builds = savedBuilds.builds.reduce((resultArray, element, id, originalArray) => [
      ...resultArray,
      id === buildIndex - 1 ? originalArray[buildIndex] :
        id === buildIndex ? originalArray[buildIndex - 1] :
          element
    ], []);
    saveBuildsToLocalStorage(savedBuilds);
    loadSavedBuilds();

    // ロード完了
    $.LoadingOverlay("hide", true);
  }

  const moveDownSavedBuild = function (buildIndex) {
    const savedBuilds = loadSavedBuildsObjectFromLocalStorage();
    if (buildIndex >= savedBuilds.builds.length - 1) return;

    // ロード開始
    $.LoadingOverlay("show", {
      background: "rgba(0, 0, 0, 0.5)",
      imageColor: "#787878",
    });

    savedBuilds.builds = savedBuilds.builds.reduce((resultArray, element, id, originalArray) => [
      ...resultArray,
      id === buildIndex + 1 ? originalArray[buildIndex] :
        id === buildIndex ? originalArray[buildIndex + 1] :
          element
    ], []);
    saveBuildsToLocalStorage(savedBuilds);
    loadSavedBuilds();

    // ロード完了
    $.LoadingOverlay("hide", true);
  }

  // スタッツ略称説明のツールチップ
  $("#tooltip-short-stats-description").tooltip();

  // 所持済みアイテムのツールチップ
  $("#tooltip-obtained-items").tooltip();

});
