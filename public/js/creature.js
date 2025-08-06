$(function () {

  const allCreatures = $.parseJSON($("#init-creatures").html());
  // HTMLエスケープをデコード
  allCreatures.forEach(creature => {
    creature.name = $("<div/>").html(creature.name).text();
  });
  let creatureList = allCreatures;

  const showCreatureList = function () {
    $.LoadingOverlay("show", {
      background: "rgba(0, 0, 0, 0.5)",
      imageColor: "#787878",
    });
    const tbody = $('#creature-list > tbody');
    tbody.children('tr').remove();
    creatureList.forEach((creature, index) => {
      if (index % 10 === 0) {
        tbody.append($($('#list-label-row').html()));
      }
      const row = $($('#list-data-row').html());
      row.find('.selectable')
        .attr('id', 'creature-' + creature['creature_id'])
        .data('creatureid', creature['creature_id'])
        .attr('tabindex', creature['sort_key']);
      const imageName = creature['image_name'] == null
        ? 'creature_noimg.png'
        : creature['image_name'];
      row.find('.item-icon')
        .attr('src', '/img/creature/' + imageName)
        .attr('alt', creature.name);
      if (!creature['boss']) {
        row.find('.boss').removeClass('boss');
      }
      row.find('.creature-name').text(creature.name);
      if (isNaN(creature['str'])) {
        row.find('.min-ad').text(creature['min_ad']);
        row.find('.max-ad').text(creature['max_ad']);
      } else {
        row.find('.min-ad').text(Number(creature['min_ad']) + Math.floor(Number(creature['str']) / 2));
        row.find('.max-ad').text(Number(creature['max_ad']) + Number(creature['str']));
      }
      row.find('.as').text(creature['as']);
      row.find('.def').text(creature['def']);
      row.find('.dex').text(creature['dex']);
      row.find('.vit').text(creature['vit']);
      row.find('.voh').text(creature['voh']);
      row.find('.dr').text(creature['dr']);
      tbody.append(row);
    });
    addSelectRowEvent();
    $.LoadingOverlay("hide", true);
  }

  const filterCreatures = function () {
    const floorId = $('#select-floor').val();
    const isTBOnly = $('#check-tb').prop('checked');
    creatureList = allCreatures;
    if (floorId) {
      creatureList = creatureList.filter(
        creature => creature['floors'] && creature['floors'].some(floor => floor['floor_id'] == floorId));
    }
    if (isTBOnly) {
      creatureList = creatureList.filter(creature => creature['tb'] == 1);
    }
    showCreatureList();
  }

  const setPhaseBoost = function (baseTagId, boostVal, level, isPercentage, enabled) {
    const tbEnabled = $("#detail-tb-boosts").data("tb");
    const base = $(baseTagId);
    const baseTd1 = $("td" + baseTagId);
    const baseTd2 = $("span" + baseTagId).closest("td");
    if (!tbEnabled || !base.data("base-val") || base.data("base-val") == 0 || !boostVal || !enabled) {
      baseTd1.removeClass('yellow');
      baseTd2.removeClass('yellow');
      return;
    }
    if (baseTagId == "#detail-as") {
      const boostedAS = base.data("base-val") * (1 - boostVal * level / 100);
      const currentVal = boostedAS < 5 ? 5 : boostedAS;
      base.data("current-val", currentVal).text(Math.round(currentVal - 0.0000005));
    } else {
      const currentVal = base.data("base-val") * (1 + boostVal * level / 100);
      base.data("current-val", currentVal).text(Math.round(currentVal - 0.0000005) + (isPercentage ? '%' : ''));
    }
    if (level > 0) {
      baseTd1.addClass('yellow');
      baseTd2.addClass('yellow');
    } else {
      baseTd1.removeClass('yellow');
      baseTd2.removeClass('yellow');
    }
  }

  const setVoT = function (level) {
    const tbEnabled = $("#detail-tb-boosts").data("tb");
    const baseVot = $("#detail-vot");
    const vot = baseVot.data("base-val");
    const vit = $("#detail-vit").data("current-val");
    if (vot == 0) {
      $("tr.row-vot").addClass("d-none");
      return;
    }
    $("tr.row-vot").removeClass("d-none");
    $("#detail-vot").text((Math.round(vit * 1000 / vot) / 1000).toFixed(3));
    baseVot.parent().removeClass('yellow');
    baseVot.parent().removeClass('red');
    if (tbEnabled && level > 0) {
      if (baseVot.data("base-val") > 0) {
        baseVot.parent().addClass('yellow');
      } else if (baseVot.data("base-val") < 0) {
        baseVot.parent().addClass('red');
      }
    }
  }

  const setAdPhaseBoost = function (baseTagId, adBoostVal, strBoostVal, level, isMinAd, enabled) {
    const tbEnabled = $("#detail-tb-boosts").data("tb");
    const base = $(baseTagId);
    const baseTd1 = $("td" + baseTagId);
    const baseTd2 = $("span" + baseTagId).closest("td");
    if (!tbEnabled || !base.data("base-val") || (base.data("base-val") == 0 && !strBoostVal) || (!adBoostVal && !strBoostVal) || !enabled) {
      baseTd1.removeClass('yellow');
      baseTd2.removeClass('yellow');
      return;
    }
    const strBaseVal = $("#detail-str").data("base-val");
    const strVal = strBaseVal
      ? strBoostVal
        ? Number(strBaseVal) * (1 + strBoostVal * level / 100)
        : Number(strBaseVal)
      : 0;
    const currentVal = base.data("base-val") * (1 + adBoostVal * level / 100) + (isMinAd ? strVal / 2 : strVal);
    base.data("current-val", currentVal).text(Math.round(currentVal - 0.0000005));
    if (level > 0) {
      baseTd1.addClass('yellow');
      baseTd2.addClass('yellow');
    } else {
      baseTd1.removeClass('yellow');
      baseTd2.removeClass('yellow');
    }
  }

  const setSkillAd = function () {
    const minAd = Number($("#detail-min-ad").data("current-val"));
    const maxAd = Number($("#detail-max-ad").data("current-val"));
    const baseMaxAd = Number($("#detail-max-ad").data("base-val"));
    const baseStr = Number($("#detail-str").data("base-val"));
    const as = Number($("#detail-as").data("current-val"));
    const sad = $("#detail-sad").data("base-val");
    const tbEnabled = $("#detail-tb-boosts").data("tb");
    const tbLevel = $("select.tb-phase").val();
    const isAdBoosted = tbEnabled && maxAd > baseMaxAd + baseStr;
    const isAsBoosted = tbEnabled && $("#detail-as").data("base-val") > as;
    $("#detail-attacks").children("tr").each(function () {
      const row = $(this);
      const damageType = row.data("damage-type");
      const sadEnabled = row.data("sad-enabled");
      const attackCount = row.data("attack-count");
      const doubleAttack = row.data("double-attack");
      const dpsEnabled = row.data("dps-enabled");
      const statsEnabled = row.data("stats-enabled");
      const saStr = Number(row.data("str"));
      const saMinAd = Number(row.data("min-ad"));
      const saMaxAd = Number(row.data("max-ad"));
      const saDex = Number(row.data("dex"));
      const saAs = Number(row.data("as") ?? 0);
      const saTbStr = Number(row.data("tb-str") ?? 0);
      const saTbAd = Number(row.data("tb-ad") ?? 0);
      const saTbDex = Number(row.data("tb-dex") ?? 0);
      const saTbAs = Number(row.data("tb-as") ?? 0);
      const saAd = row.find(".sa-ad").removeClass("yellow");
      const saDexText = row.find(".sa-dex").removeClass("yellow");
      const saAsText = row.find(".sa-as").removeClass("yellow");
      const saDps = row.find(".sa-dps").removeClass("yellow");
      const saAdBoosted = statsEnabled && tbEnabled && tbLevel > 0 && (saTbStr > 0 || saTbAd > 0);
      const saAsEnabled = row.data("as") != null;
      const saAsBoosted = saAsEnabled && tbEnabled && tbLevel > 0 && saTbAs > 0;
      let avgAD = 0;
      if (damageType == "normal") {
        const saCurrentMinAd = statsEnabled
          ? tbEnabled
            ? (saMinAd * (100 + saTbAd * tbLevel) + saStr * (100 + saTbStr * tbLevel) / 2) / 100
            : saMinAd + saStr / 2
          : minAd * (sadEnabled ? sad : 100) / 100;
        const saCurrentMaxAd = statsEnabled
          ? tbEnabled
            ? (saMaxAd * (100 + saTbAd * tbLevel) + saStr * (100 + saTbStr * tbLevel)) / 100
            : saMaxAd + saStr
          : maxAd * (sadEnabled ? sad : 100) / 100;
        saAd.text(Math.round(saCurrentMinAd - 0.0000005) + '～' + Math.round(saCurrentMaxAd - 0.0000005));
        avgAD = (saCurrentMinAd + saCurrentMaxAd) * (doubleAttack ? 2 : 1) / 2;
      } else if (damageType == "actual") {
        avgAD = row.data("ad-actual");
      }
      if (!statsEnabled && isAdBoosted || saAdBoosted) saAd.addClass("yellow");
      if (statsEnabled) {
        const saCurrentDex = tbEnabled
          ? (saDex * (100 + saTbDex * tbLevel)) / 100
          : saDex;
        saDexText.text(Math.round(saCurrentDex - 0.0000005));
        if (tbEnabled && saTbDex > 0 && tbLevel > 0) saDexText.addClass("yellow");
      }
      const saCurrentAs = saAsEnabled
        ? (saAs * (100 - saTbAs * tbLevel)) / 100 > 5
          ? (saAs * (100 - saTbAs * tbLevel)) / 100
          : 5
        : as;
      saAsText.text(Math.round(saCurrentAs - 0.0000005));
      if (tbEnabled && saTbAs > 0 && tbLevel > 0) {
        saAsText.addClass("yellow");
      }
      if (attackCount > 1) {
        row.find(".sa-attack-count-val").text(attackCount);
      } else if (doubleAttack) {
        row.find(".sa-attack-count-val").text(2);
      } else {
        row.find(".sa-attack-count").addClass("d-none");
      }
      if (dpsEnabled) {
        const dps = (Math.round(avgAD * 30 * 1000 / saCurrentAs) / 1000).toFixed(3);
        saDps.text(dps);
        if (damageType == "normal" && (!statsEnabled && isAdBoosted || saAdBoosted || !saAsEnabled && isAsBoosted || saAsBoosted)) saDps.addClass("yellow");
      } else {
        saDps.text("-");
      }
    });
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

  $("#modal-creature").on('hide.bs.modal', function () {
    document.activeElement.blur();
    if (!autoTransition && location.pathname.split('/').length != 2) {
      history.pushState(null, document.title, '/creatures');
    }
    autoTransition = false;
  });

  const addSelectRowEvent = function () {
    $("td.selectable").on('click', function () {
      const at = autoTransition;
      autoTransition = false;
      const creatureId = $(this).data('creatureid');
      $.ajax({
        url: '/creatures/detail/' + creatureId,
        type: 'GET',
      }).done(data => {
        const creature = data.creature;
        creature.name = $('<div/>').html(creature.name).text();
        const items = data.items;
        const floors = data.floors;
        const imgName = creature.image_name ?
          creature.image_name : 'creature_noimg.png';
        const minAd = creature.min_ad
          ? creature.str
            ? Number(creature.min_ad) + Number(creature.str) / 2
            : creature.min_ad
          : '?';
        const maxAd = creature.max_ad
          ? creature.str
            ? Number(creature.max_ad) + Number(creature.str)
            : creature.max_ad
          : '?';
        $("#detail-image").attr('src', '/img/creature/' + imgName);
        $("#detail-creature-name").text(creature.name);
        $("#detail-ad").addClass("d-none")
          .data("enabled", creature.ad_enabled);
        $("#detail-ad-disabled").addClass("d-none");
        if (creature.ad_enabled) {
          $("#detail-ad").removeClass("d-none");
        } else {
          $("#detail-ad-disabled").removeClass("d-none");
        }
        $("#detail-min-ad").text(Math.round(minAd - 0.0000005))
          .data("current-val", minAd)
          .data("base-val", creature.min_ad);
        $("#detail-max-ad").text(maxAd)
          .data("current-val", maxAd)
          .data("base-val", creature.max_ad);
        $("#detail-as").text(creature.as_enabled ? creature.as : '-')
          .data("current-val", creature.as)
          .data("base-val", creature.as)
          .data("enabled", creature.as_enabled);
        $("#detail-str").text(creature.str ? creature.str : '?')
          .data("current-val", creature.str)
          .data("base-val", creature.str);
        $("#detail-def").text(creature.def ? creature.def : '?')
          .data("current-val", creature.def)
          .data("base-val", creature.def);
        $("#detail-dex").text(creature.dex ? creature.dex : '?')
          .data("current-val", creature.dex)
          .data("base-val", creature.dex);
        $("#detail-vit").text(creature.vit ? creature.vit : '?')
          .data("current-val", creature.vit)
          .data("base-val", creature.vit);
        $("#detail-ws").text(creature.ws ? creature.ws : '?')
          .data("current-val", creature.ws)
          .data("base-val", creature.ws);
        $("#detail-voh").text(creature.ad_enabled ? (creature.voh ? creature.voh : '?') + '%' : '-')
          .data("current-val", creature.voh)
          .data("base-val", creature.voh)
          .data("enabled", creature.ad_enabled);
        $("#detail-dr").text((creature.dr ? creature.dr : '?') + '%')
          .data("current-val", creature.dr)
          .data("base-val", creature.dr);
        $("#detail-xp").text(creature.xp ? creature.xp : '?')
          .data("current-val", creature.xp)
          .data("base-val", creature.xp);
        $("#detail-sad").text(creature.sad ? creature.sad_enabled && creature.sad > 0 ? creature.sad + '%' : '-' : '?')
          .data("current-val", creature.sad)
          .data("base-val", creature.sad);
        $("#detail-vot").data("base-val", creature.vot);
        setVoT(0);
        $("#tb-ad").text(creature.ad_enabled && creature.tb_ad ? creature.tb_ad + '%' : '-')
          .data("val", creature.tb_ad);
        $("#tb-as").text(creature.as_enabled && creature.tb_as ? creature.tb_as + '%' : '-')
          .data("val", creature.tb_as);
        $("#tb-str").text(creature.tb_str ? creature.tb_str + '%' : '-')
          .data("val", creature.tb_str);
        $("#tb-def").text(creature.tb_def ? creature.tb_def + '%' : '-')
          .data("val", creature.tb_def);
        $("#tb-dex").text(creature.tb_dex ? creature.tb_dex + '%' : '-')
          .data("val", creature.tb_dex);
        $("#tb-vit").text(creature.tb_vit ? creature.tb_vit + '%' : '-')
          .data("val", creature.tb_vit);
        $("#tb-ws").text(creature.tb_ws ? creature.tb_ws + '%' : '-')
          .data("val", creature.tb_ws);
        $("#tb-voh").text(creature.ad_enabled && creature.tb_voh ? creature.tb_voh + '%' : '-')
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
        $("#detail-tb-boosts").data("tb", creature.tb);
        if (creature.tb) {
          $("#detail-tb-boosts,#detail-row-tb-phase").removeClass("d-none");
        } else {
          $("#detail-tb-boosts,#detail-row-tb-phase").addClass("d-none");
        }

        const saTbody = $("#detail-attacks");
        saTbody.children('tr.row-data').remove();
        if (creature.special_attacks.length == 0) {
          const row = $($("#modal-attacks-none-row").html());
          saTbody.append(row);
        }
        creature.special_attacks.forEach(sa => {
          const row = $($("#modal-attacks-row").html());
          row.data('cooldown', sa.cooldown)
            .data('replace-melee', sa.replace_melee)
            .data('is-once', sa.is_once)
            .data('damage-type', sa.damage_type)
            .data('sad-enabled', sa.sad_enabled)
            .data('ad-relative', sa.ad_relative)
            .data('ad-actual', sa.ad_actual)
            .data('attack-count', sa.attack_count)
            .data('double-attack', sa.double_attack)
            .data('is-spread', sa.is_spread)
            .data('dps-enabled', sa.dps_enabled)
            .data('stats-enabled', sa.stats_enabled)
            .data('str', sa.str)
            .data('min-ad', sa.min_ad)
            .data('max-ad', sa.max_ad)
            .data('dex', sa.dex)
            .data('as', sa.as)
            .data('tb-str', sa.tb_str)
            .data('tb-ad', sa.tb_ad)
            .data('tb-dex', sa.tb_dex)
            .data('tb-as', sa.tb_as)
            .data('duration', sa.duration);
          const img = sa.image_name ? sa.image_name : 'blank.png';
          row.find('img.item-icon')
            .attr('src', '/img/sa/' + img).attr('alt', sa.name);
          row.find(".sa-name").text(sa.name);
          if (sa.cooldown == null) {
            row.find(".sa-cooldown").parent().removeClass("d-table-cell").addClass("d-none");
          } else if (sa.cooldown == 999) {
            row.find(".sa-cooldown").text("?");
          } else {
            row.find(".sa-cooldown").text(Number(sa.cooldown).toLocaleString());
          }
          if (sa.damage_type != "normal") {
            if (sa.attack_count > 1) {
              row.find(".sa-attack-count-val").text(sa.attack_count);
            } else {
              row.find(".sa-attack-count").addClass("d-none");
            }
          }
          if (!sa.dps_enabled && (sa.duration == null || sa.duration < 0) || sa.is_once) {
            row.find(".sa-dps").closest(".d-table").removeClass("d-table").addClass("d-none");
          } else {
            if (!sa.dps_enabled) {
              row.find(".sa-dps").parent().removeClass("d-table-cell").addClass("d-none");
            }
            if (sa.duration == null || sa.duration < 0) {
              row.find(".sa-duration").parent().removeClass("d-table-cell").addClass("d-none");
            } else {
              row.find(".sa-duration").text(Number(sa.duration).toLocaleString());
            }
          }
          switch (sa.damage_type) {
            case 'relative':
              row.find(".sa-ad").addClass("rare").text(sa.ad_relative + "%");
              break;
            case 'actual':
              row.find(".sa-ad").addClass("rare").text(sa.ad_actual);
              break;
            case 'composite':
              row.find(".sa-ad").addClass("rare").text(sa.ad_actual + " + " + sa.ad_relative + "%");
              break;
            case 'none':
              row.find(".sa-ad").closest(".d-table").removeClass("d-table").addClass("d-none");
          }
          if (sa.as != null && sa.as > 0) {
            row.find(".sa-as").text(Math.floor(sa.as));
          } else {
            row.find(".sa-as").parent().removeClass("d-table-cell").addClass("d-none");
          }
          if (!sa.stats_enabled) {
            row.find(".sa-dex").parent().removeClass("d-table-cell").addClass("d-none");
          }
          saTbody.append(row);
        });
        setSkillAd();

        const itemTbody = $("#detail-items");
        itemTbody.children('tr.row-data').remove();
        if (items.length == 0) {
          const row = $($("#modal-item-none-row").html());
          itemTbody.append(row);
        }
        items.forEach(item => {
          item.name = $('<div/>').html(item.name).text();
          const row = $($("#modal-item-row").html());
          const link = '/items/' + item.item_class.toLowerCase() + '/'
            + (item.rarity == 'common' ? item.base_item_id : 'rare') + '/' + item.item_id;
          const img = item.image_name ? item.image_name : 'item_noimg.png';
          row.find('a').attr('href', link).addClass(item.rarity);
          row.find('img.item-icon')
            .attr('src', '/img/item/' + img).attr('alt', item.name);
          row.find('span').text(item.name);
          if (item.class_flactuable == 0) {
            row.find('img.class-icon').remove();
          } else {
            row.find('img.class-icon').attr('src', '/img/common/' + item.item_class.toLowerCase() + '.png');
          }
          itemTbody.append(row);
        });

        const floorList = $("#detail-floors");
        floorList.children('li.list-data').remove();
        if (floors.length == 0) {
          floorList.find('li.list-none').removeClass('d-none');
        } else {
          floorList.find('li.list-none').addClass('d-none');
        }
        floors.forEach(floor => {
          const li = $($("#modal-floor-li").html());
          li.find(".floor-name").text(floor.short_name).attr('href', "/floors/" + floor.floor_id);
          if (floor.description) {
            li.find(".floor-description").attr('title', $('<div/>').html(floor.description).text());
          } else {
            li.find(".floor-description").remove();
          }
          floorList.append(li);
        });
        floorList.find("a.floor-description").tooltip();

        if (!at && location.pathname.split('/').length != 3) {
          history.pushState(null, document.title, '/creatures/' + creatureId);
        }

        $("select.tb-phase").trigger('change');

        $("#modal-creature").modal("show");
      });
    });
  }

  addSelectRowEvent();

  $('#select-floor').on('change', function () {
    filterCreatures();
  });
  $('#check-tb').on('change', function () {
    filterCreatures();
  });

  $("select.tb-phase").on('change', function () {
    const level = $(this).val();
    const adEnabled = $("#detail-ad").data("enabled");
    const asEnabled = $("#detail-as").data("enabled");
    setPhaseBoost("#detail-str", $("#tb-str").data("val"), level, false, adEnabled);
    setAdPhaseBoost("#detail-min-ad", $("#tb-ad").data("val"), $("#tb-str").data("val"), level, true, adEnabled);
    setAdPhaseBoost("#detail-max-ad", $("#tb-ad").data("val"), $("#tb-str").data("val"), level, false, adEnabled);
    setPhaseBoost("#detail-def", $("#tb-def").data("val"), level, false, true);
    setPhaseBoost("#detail-dex", $("#tb-dex").data("val"), level, false, true);
    setPhaseBoost("#detail-vit", $("#tb-vit").data("val"), level, false, true);
    setPhaseBoost("#detail-voh", $("#tb-voh").data("val"), level, true, adEnabled);
    setPhaseBoost("#detail-dr", $("#tb-dr").data("val"), level, true, true);
    setPhaseBoost("#detail-ws", $("#tb-ws").data("val"), level, false, true);
    setPhaseBoost("#detail-as", $("#tb-as").data("val"), level, false, asEnabled);
    setPhaseBoost("#detail-xp", $("#tb-xp").data("val"), level, false, true);
    setVoT(level);
    setSkillAd();
  });

  if (location.pathname.split('/').length == 3) {
    autoTransition = true;
    $("#creature-" + location.pathname.split('/')[2]).click();
  }

  $("#tooltip-creature-stats-description").tooltip();

});
