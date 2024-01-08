(function ($) {
  const currentPath = location.pathname;
  function showMenuPopup(id) {
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

  function showCurrentChapter() {
    const chapterNumber = currentPath.charAt(currentPath.length - 1);
    $("#chapter-list").val(`chapter-${chapterNumber}`);
    $("#chapter-0").hide();
  }

  $("#chapter-list").on("change", function () {
    const selectedValue = $(this).val();
    const newChapterNumber = selectedValue.charAt(selectedValue.length - 1);
    const newPath = `${currentPath.substring(
      0,
      currentPath.length - 1
    )}${newChapterNumber}`;
    location.href = newPath;
  });

  $(document).ready(function () {
    showMenuPopup("#menu-link-1");
    showMenuPopup("#menu-link-2");
    showCurrentChapter();
  });
})(jQuery);
