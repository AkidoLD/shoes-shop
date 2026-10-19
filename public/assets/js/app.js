"use strict";

/*
 * SHOES - SHOP — interface comfort only.
 *
 * This file never renders essential content (already server-rendered by PHP).
 * It only improves usage: search focus, automatic form submit, option picking.
 */

/*
 * Header search button: move the cursor into the search field.
 */
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

/*
 * Submit the catalogue form as soon as a filter or sort changes.
 */
const toolbar = document.querySelector(".catalogue-toolbar");

if (toolbar) {
    toolbar.querySelectorAll("select").forEach(function (select) {
        select.addEventListener("change", function () {
            toolbar.submit();
        });
    });
}

/*
 * Visual selection of options (size / color) on the product page.
 */
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
