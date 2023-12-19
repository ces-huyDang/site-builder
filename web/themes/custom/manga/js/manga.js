(function ($) {
  function showMenuPopup(id) {
    console.log(id);
    const idNumber = id.charAt(id.length - 1);
    $(id).hover(
      function () {
        $("#content-block-" + idNumber).removeClass("d-none");
      },
      function () {
        // On hover out, hide the dropdown menu
        if (!$("#content-block-" + idNumber).is(":hover")) {
          $("#content-block-" + idNumber).addClass("d-none");
        } else {
          $("#content-block-" + idNumber).mouseleave(() => {
            $("#content-block-" + idNumber).addClass("d-none");
          });
        }
      }
    );
  }

  $(document).ready(function () {
    showMenuPopup("#menu-link-1");
    showMenuPopup("#menu-link-2");
  });
})(jQuery);
