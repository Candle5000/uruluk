$(function () {

  $.ajaxSetup({
    cache: false,
    timeout: 10000, // 10sec
  });

  $(document).on('ajaxStart', function () {
    $.LoadingOverlay("show", {
      background: "rgba(0, 0, 0, 0.5)",
      imageColor: "#787878",
    });
  });

  $(document).on('ajaxStop', function () {
    $.LoadingOverlay("hide", true);
  });

  $("#language-setting a").on('click', function (e) {
    const lang = $(this).data('lang');
    document.cookie = 'language=' + lang + ';path=/';
    location.reload();
  });

});
