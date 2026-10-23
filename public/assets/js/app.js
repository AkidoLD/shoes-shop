"use strict";

document.querySelectorAll("[data-focus-search]").forEach(function (button) {
    button.addEventListener("click", function () {
        const search = document.getElementById("search");

        if (!search) {
            return;
        }

        search.focus();
        search.scrollIntoView({ behavior: "smooth", block: "center" });
    });
});

const toolbar = document.querySelector(".catalogue-toolbar");

if (toolbar) {
    toolbar.querySelectorAll("select").forEach(function (select) {
        select.addEventListener("change", function () {
            toolbar.submit();
        });
    });
}

["size-option", "color-option"].forEach(function (optionClass) {
    const options = document.querySelectorAll("." + optionClass);

    options.forEach(function (option) {
        option.addEventListener("click", function () {
            options.forEach(function (item) {
                item.classList.remove("selected");
                item.setAttribute("aria-pressed", "false");
            });

            option.classList.add("selected");
            option.setAttribute("aria-pressed", "true");
        });
    });
});
