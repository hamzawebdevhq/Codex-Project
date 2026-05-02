document.addEventListener("DOMContentLoaded", function () {
  var body = document.body;
  var toggle = document.querySelector(".nav-toggle");
  var nav = document.querySelector(".primary-navigation");
  var header = document.querySelector(".site-header");
  var closeNav = function () {
    body.classList.remove("nav-open");

    if (toggle) {
      toggle.setAttribute("aria-expanded", "false");
    }
  };

  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      var isOpen = body.classList.toggle("nav-open");
      toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });

    nav.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        closeNav();
      });
    });
  }

  var updateHeaderState = function () {
    if (!header) {
      return;
    }

    if (window.scrollY > 16) {
      header.classList.add("is-scrolled");
    } else {
      header.classList.remove("is-scrolled");
    }
  };

  updateHeaderState();
  window.addEventListener("scroll", updateHeaderState, { passive: true });

  window.addEventListener("resize", function () {
    if (window.innerWidth > 820) {
      closeNav();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeNav();
    }
  });
});
