(function ($) {
  const currentPath = location.pathname;
  const chapterNumber = currentPath.charAt(currentPath.length - 1);
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
    $("#chapter-list").val(`chapter-${chapterNumber}`);
    $("#chapter-0").remove();
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

  function previousBtnClick() {
    const firstValue = $("#chapter-list option:first-child").val();
    const prvPath = `${currentPath.substring(0, currentPath.length - 1)}${
      parseInt(chapterNumber) - 1
    }`;

    $("#prv-btn").attr("href", prvPath);
    if (chapterNumber !== firstValue.charAt(firstValue.length - 1)) {
      return;
    }
    $("#prv-btn").on("click", function (e) {
      e.preventDefault();
    });
    $("#prv-btn").addClass("disabled-btn");
  }

  function nextBtnClick() {
    const lastValue = $("#chapter-list option:last-child").val();
    const nxtPath = `${currentPath.substring(0, currentPath.length - 1)}${
      parseInt(chapterNumber) + 1
    }`;
    $("#nxt-btn").attr("href", nxtPath);
    if (chapterNumber !== lastValue.charAt(lastValue.length - 1)) {
      return;
    }
    $("#nxt-btn").on("click", function (e) {
      e.preventDefault();
    });
    $("#nxt-btn").addClass("disabled-btn");
  }

  $(document).ready(function () {
    showMenuPopup("#menu-link-1");
    showMenuPopup("#menu-link-2");
    showCurrentChapter();
    if (!currentPath.includes("chapter")) {
      return;
    }
    previousBtnClick();
    nextBtnClick();
  });
})(jQuery);
