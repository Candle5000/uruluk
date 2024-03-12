$(function () {

  let autoTransition = false;

  $(".collapse").on('show.bs.collapse', function () {
    $("[aria-controls='" + $(this).attr('id') + "'] svg")
      .removeClass('fa-chevron-circle-down')
      .addClass('fa-chevron-circle-up');
  });

  $(".collapse").on('hide.bs.collapse', function () {
    $("[aria-controls='" + $(this).attr('id') + "'] svg")
      .removeClass('fa-chevron-circle-up')
      .addClass('fa-chevron-circle-down');
  });

  window.addEventListener('popstate', e => {
    if (location.pathname.split('/').length == 5) {
      if ($("#modal-item").hasClass('show')) {
        autoTransition = true;
        $("#modal-item").modal('hide');
      }
      autoTransition = true;
      $("#" + location.pathname.split('/')[4]).click();
    } else if ($("#modal-item").hasClass('show')) {
      autoTransition = true;
      $("#modal-item").modal('hide');
    }
  });

  $("#modal-item").on('hide.bs.modal', function () {
    if (!autoTransition && location.pathname.split('/').length != 4) {
      history.pushState(null, null,
        '/items/' + location.pathname.split('/')[2]
        + '/' + location.pathname.split('/')[3]);
    }
    autoTransition = false;
  });

  $("div.selectable").on('click', function () {
    const at = autoTransition;
    autoTransition = false;
    const itemId = $(this).data('itemid');
    $("#detail-image").children().remove();
    $("#detail-image").append($(this).find("img.item-detail").clone());
    $("#detail-name").removeClass("common rare artifact");
    if ($(this).find(".item-name").hasClass("rare")) {
      $("#detail-name").addClass("rare");
    } else if ($(this).find(".item-name").hasClass("artifact")) {
      $("#detail-name").addClass("artifact");
    } else {
      $("#detail-name").addClass("common");
    }
    $("#detail-item-name").text($(this).find("span.item-name").text());
    $("#detail-main").children().remove();
    $("#detail-main").append($(this).find("ul.detail-main").clone());
    $("#sell-price").text($(this).data('price') !== '' ? $(this).data('price') : '?');
    $.ajax({
      url: '/items/detail/' + itemId,
      type: 'GET',
    }).done(data => {
      const floors = data.floors;
      const bananaFloors = data.banana;
      const treasureFloors = data.treasure;
      const creatures = data.creatures;
      const quests = data.quests;
      const shops = data.shops;
      const tags = data.tags;

      $('#detail-floors').children('li.detail-row').remove();
      if (floors.length) {
        $('#detail-floor-none').addClass('d-none');
      } else {
        $('#detail-floor-none').removeClass('d-none');
      }
      floors.forEach(floor => {
        const row = $($("#modal-floor-row").html());
        row.find(".floor-name").text(floor.short_name)
          .attr('href', '/floors/' + floor.floor_id);
        $('#detail-floors').append(row);
      });

      $('#detail-banana').children('li.detail-row').remove();
      if (bananaFloors.length) {
        $('#detail-banana-none').addClass('d-none');
      } else {
        $('#detail-banana-none').removeClass('d-none');
      }
      bananaFloors.forEach(floor => {
        const row = $($("#modal-banana-row").html());
        row.find(".floor-name").text(floor.short_name)
          .attr('href', '/floors/' + floor.floor_id);
        $('#detail-banana').append(row);
      });

      $('#detail-treasure').children('li.detail-row').remove();
      if (treasureFloors.length) {
        $('#detail-treasure-none').addClass('d-none');
      } else {
        $('#detail-treasure-none').removeClass('d-none');
      }
      treasureFloors.forEach(floor => {
        const row = $($("#modal-treasure-row").html());
        row.find(".floor-name").text(floor.short_name)
          .attr('href', '/floors/' + floor.floor_id);
        if (floor.note) {
          row.find('a.treasure-note').attr('title', floor.note);
        } else {
          row.find('a.treasure-note').remove();
        }
        $('#detail-treasure').append(row);
      });
      $('#detail-treasure').find('a.treasure-note').tooltip();

      $('#detail-creatures').children('li.detail-row').remove();
      if (creatures.length) {
        $('#detail-creature-none').addClass('d-none');
      } else {
        $('#detail-creature-none').removeClass('d-none');
      }
      creatures.forEach(creature => {
        const row = $($("#modal-creature-row").html());
        const imgName = creature.image_name ?
          creature.image_name : 'creature_noimg.png';
        row.find('img').attr('src', '/img/creature/' + imgName);
        row.find('span').text(creature.name_en);
        row.find('.creature-name')
          .attr('href', '/creatures/' + creature.creature_id)
          .addClass(creature.boss == 1 ? 'boss' : 'text-light');
        $('#detail-creatures').append(row);
      });

      $('#detail-shops').children('li.detail-row').remove();
      if (shops.length) {
        $('#detail-shop-none').addClass('d-none');
      } else {
        $('#detail-shop-none').removeClass('d-none');
      }
      shops.forEach(shop => {
        const row = $($("#modal-shop-row").html());
        const imgName = shop.image_name ?
          shop.image_name : 'shop_noimg.png';
        row.find('img').attr('src', '/img/shop/' + imgName);
        row.find('span').text(shop.short_name + '/' + shop.name);
        row.find('.shop-name')
          .attr('href', '/floors/' + shop.floor_id);
        row.find('.shop-price').text('(' + shop.price + ' G)');
        $('#detail-shops').append(row);
      });

      $('#detail-quest-reward').children('li.detail-row').remove();
      if (quests.reward.length) {
        $('#detail-quest-reward-none').addClass('d-none');
      } else {
        $('#detail-quest-reward-none').removeClass('d-none');
      }
      quests.reward.forEach(quest => {
        const row = $($("#modal-quest-reward-row").html());
        row.find('.quest-reward-name')
          .attr('href', '/floors/' + quest.floor_id)
          .text(quest.short_name);
        if (quest.repeatable == 1) {
          row.find('.quest-repeatable').removeClass('d-none');
        }
        if (quest.autosave == 1) {
          row.find('.quest-autosave').removeClass('d-none');
        }
        quest.icons.forEach(icon => {
          if (icon.quest_reward == 1 && icon.quest_icon_id == 1) {
            row.find('.icons').append($('<i class="fa fa-arrow-right mx-1" />'));
          } else if (icon.quest_reward == 1 || icon.quest_icon_id > 1) {
            row.find('.icons').append($('<i class="fa fa-plus mx-1" />'));
          }
          row.find('.icons').append($('<img />')
            .addClass('item-icon')
            .attr('src', '/img/' + icon.image_path));
        });
        $('#detail-quest-reward').append(row);
      });

      $('#detail-quest-required').children('li.detail-row').remove();
      if (quests.required.length) {
        $('#detail-quest-required-none').addClass('d-none');
      } else {
        $('#detail-quest-required-none').removeClass('d-none');
      }
      quests.required.forEach(quest => {
        const row = $($("#modal-quest-required-row").html());
        row.find('.quest-required-name')
          .attr('href', '/floors/' + quest.floor_id)
          .text(quest.short_name);
        if (quest.repeatable == 1) {
          row.find('.quest-repeatable').removeClass('d-none');
        }
        if (quest.autosave == 1) {
          row.find('.quest-autosave').removeClass('d-none');
        }
        quest.icons.forEach(icon => {
          if (icon.quest_reward == 1 && icon.quest_icon_id == 1) {
            row.find('.icons').append($('<i class="fa fa-arrow-right mx-1" />'));
          } else if (icon.quest_reward == 1 || icon.quest_icon_id > 1) {
            row.find('.icons').append($('<i class="fa fa-plus mx-1" />'));
          }
          row.find('.icons').append($('<img />')
            .addClass('item-icon')
            .attr('src', '/img/' + icon.image_path));
        });
        $('#detail-quest-required').append(row);
      });

      $('#detail-tags').children('li.detail-row').remove();
      if (tags.length) {
        $('#detail-tags-none').addClass('d-none');
      } else {
        $('#detail-tags-none').removeClass('d-none');
      }
      tags.forEach(tag => {
        const row = $($("#modal-tag-row").html());
        row.find('.tag-name')
          .attr('href', '/tags/' + tag.tag_url)
          .text(tag.tag_name);
        $('#detail-tags').append(row);
      });

      if (!at && location.pathname.split('/').length != 5) {
        history.pushState(null, null,
          '/items/' + location.pathname.split('/')[2]
          + '/' + location.pathname.split('/')[3] + '/' + itemId);
      }

      $("#modal-item").modal("show");
    });
  });

  if (location.pathname.split('/').length == 5) {
    autoTransition = true;
    $("#" + location.pathname.split('/')[4]).click();
  }

  $("#tooltip-class-stats-description").tooltip();

});
