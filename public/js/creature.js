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

  const setPhaseBoost = function (baseTagId, boostVal, level, isPercentage) {
    const base = $(baseTagId);
    const baseTd1 = $("td" + baseTagId);
    const baseTd2 = $("span" + baseTagId).closest("td");
    if (!base.data("base-val") || base.data("base-val") == 0 || !boostVal) {
      baseTd1.removeClass('yellow');
      baseTd2.removeClass('yellow');
      return;
    }
    if (baseTagId == "#detail-as") {
      base.text(Math.round(base.data("base-val") / (1 + boostVal * level / 100) - 0.0000005));
    } else {
      base.text(Math.round(base.data("base-val") * (1 + boostVal * level / 100) - 0.0000005) + (isPercentage ? '%' : ''));
    }
    if (level > 0) {
      baseTd1.addClass('yellow');
      baseTd2.addClass('yellow');
    } else {
      baseTd1.removeClass('yellow');
      baseTd2.removeClass('yellow');
    }
  }

  const setAdPhaseBoost = function (baseTagId, adBoostVal, strBoostVal, level, isMinAd) {
    const base = $(baseTagId);
    const baseTd1 = $("td" + baseTagId);
    const baseTd2 = $("span" + baseTagId).closest("td");
    if (!base.data("base-val") || (base.data("base-val") == 0 && !strBoostVal) || (!adBoostVal && !strBoostVal)) {
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
    base.text(Math.round(base.data("base-val") * (1 + adBoostVal * level / 100) + (isMinAd ? strVal / 2 : strVal) - 0.0000005));
    if (level > 0) {
      baseTd1.addClass('yellow');
      baseTd2.addClass('yellow');
    } else {
      baseTd1.removeClass('yellow');
      baseTd2.removeClass('yellow');
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

  $("#modal-creature").on('hide.bs.modal', function () {
    if (!autoTransition && location.pathname.split('/').length != 2) {
      history.pushState(null, null, '/creatures');
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
            ? Number(creature.min_ad) + Math.floor(Number(creature.str) / 2)
            : creature.min_ad
          : '?';
        const maxAd = creature.max_ad
          ? creature.str
            ? Number(creature.max_ad) + Number(creature.str)
            : creature.max_ad
          : '?';
        const as = creature.as ? creature.as == 0 ? '-' : creature.as : '?';
        $("#detail-image").attr('src', '/img/creature/' + imgName);
        $("#detail-creature-name").text(creature.name);
        $("#detail-min-ad").text(minAd)
          .data("base-val", creature.min_ad);
        $("#detail-max-ad").text(maxAd)
          .data("base-val", creature.max_ad);
        $("#detail-as").text(as)
          .data("base-val", creature.as);
        $("#detail-str").text(creature.str ? creature.str : '?')
          .data("base-val", creature.str);
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
        saTbody.children('tr.row-data').remove();
        if (creature.special_attacks.length == 0) {
          saTbody.find('tr.row-none').removeClass('d-none');
        } else {
          saTbody.find('tr.row-none').addClass('d-none');
        }
        creature.special_attacks.forEach(sa => {
          const row = $($("#modal-sa-row").html());
          row.find(".sa-name").text(sa.name);
          row.find(".sa-note").attr('title', sa.note);
          saTbody.append(row);
        });
        saTbody.find("a.sa-note").tooltip();

        const itemTbody = $("#detail-items");
        itemTbody.children('tr.row-data').remove();
        if (items.length == 0) {
          itemTbody.find('tr.row-none').removeClass('d-none');
        } else {
          itemTbody.find('tr.row-none').addClass('d-none');
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
          history.pushState(null, null, '/creatures/' + creatureId);
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
    setAdPhaseBoost("#detail-min-ad", $("#tb-ad").data("val"), $("#tb-str").data("val"), level, true);
    setAdPhaseBoost("#detail-max-ad", $("#tb-ad").data("val"), $("#tb-str").data("val"), level, false);
    setPhaseBoost("#detail-as", $("#tb-as").data("val"), level, false);
    setPhaseBoost("#detail-str", $("#tb-str").data("val"), level, false);
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

  $("#tooltip-creature-stats-description").tooltip();

});
