"use strict";

/*
 * SHOES - SHOP
 * Moteur du catalogue
 */

const productGrid = document.getElementById("productGrid");
const productSearch = document.getElementById("productSearch");
const categoryFilter = document.getElementById("categoryFilter");
const sortProducts = document.getElementById("sortProducts");
const catalogueStatus = document.getElementById("catalogueStatus");


/*
 * Formater les prix en FCFA
 */

function formatPrice(price) {
    return new Intl.NumberFormat("fr-FR", {
        maximumFractionDigits: 0
    }).format(price) + " FCFA";
}


/*
 * Échapper les caractères spéciaux
 * pour les valeurs insérées dans du HTML.
 */

function escapeHTML(value) {
    return String(value).replace(/[&<>"']/g, function (character) {
        const entities = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;"
        };

        return entities[character];
    });
}


/*
 * Construire une carte produit
 */

function createProductCard(product) {
    const oldPriceHTML = product.oldPrice
        ? `<span class="product-old-price">
               ${formatPrice(product.oldPrice)}
           </span>`
        : "";

    const badges = [];

    if (product.featured) {
        badges.push("POPULAIRE");
    }

    if (product.isNew) {
        badges.push("NOUVEAU");
    }

    const badgeHTML = badges
        .map(function (badge) {
            return `<span class="product-tag">
                        ${badge}
                    </span>`;
        })
        .join("");

    const unavailableClass = product.available
        ? ""
        : "product-unavailable";

    const buttonHTML = product.available
        ? `<a class="btn btn-dark"
              href="produit.html?id=${encodeURIComponent(product.id)}">
              Voir le produit →
           </a>`
        : `<button class="btn btn-outline" disabled>
              Indisponible
           </button>`;

    return `
        <article class="product-card ${unavailableClass}">

            <a
                href="produit.html?id=${encodeURIComponent(product.id)}"
                aria-label="Découvrir ${escapeHTML(product.name)}">

                <div class="product-image">

                    ${product.featured
                        ? `<span class="product-badge">POPULAIRE</span>`
                        : ""}

                    <img
                        src="${escapeHTML(product.image)}"
                        alt="${escapeHTML(product.name)}"
                        loading="lazy">

                </div>

            </a>

            <div class="product-info">

                <div class="product-tags">
                    ${badgeHTML}
                </div>

                <p class="product-category">
                    ${escapeHTML(product.categoryLabel)}
                </p>

                <h3 class="product-name">
                    ${escapeHTML(product.name)}
                </h3>

                <p class="product-reference">
                    Réf. ${escapeHTML(product.reference)}
                </p>

                <p class="product-price">
                    ${formatPrice(product.price)}
                    ${oldPriceHTML}
                </p>

                ${buttonHTML}

            </div>

        </article>
    `;
}


/*
 * Appliquer la recherche,
 * les filtres et le tri.
 */

function getFilteredProducts() {
    let result = PRODUCTS.filter(function (product) {
        return product.available;
    });

    const searchTerm = productSearch.value
        .trim()
        .toLowerCase();

    const selectedCategory = categoryFilter.value;

    const sortOption = sortProducts.value;


    // Recherche par nom, référence ou catégorie

    if (searchTerm) {
        result = result.filter(function (product) {
            const searchableText = [
                product.name,
                product.reference,
                product.categoryLabel,
                product.description
            ].join(" ").toLowerCase();

            return searchableText.includes(searchTerm);
        });
    }


    // Filtrage par catégorie

    if (selectedCategory !== "all") {
        result = result.filter(function (product) {
            return product.category === selectedCategory;
        });
    }


    // Tri

    switch (sortOption) {
        case "price-asc":
            result.sort(function (a, b) {
                return a.price - b.price;
            });
            break;

        case "price-desc":
            result.sort(function (a, b) {
                return b.price - a.price;
            });
            break;

        case "name-asc":
            result.sort(function (a, b) {
                return a.name.localeCompare(b.name, "fr");
            });
            break;
    }

    return result;
}


/*
 * Afficher les produits
 */

function renderProducts() {
    const products = getFilteredProducts();

    if (products.length === 0) {
        productGrid.innerHTML = `
            <div class="empty-state">
                <h3>Aucun produit trouvé</h3>
                <p>
                    Essaie un autre nom ou modifie les filtres.
                </p>
            </div>
        `;

        catalogueStatus.textContent = "Aucun produit trouvé.";

        return;
    }

    productGrid.innerHTML = products
        .map(createProductCard)
        .join("");

    catalogueStatus.textContent =
        products.length + (
            products.length > 1
                ? " produits disponibles."
                : " produit disponible."
        );
}


/*
 * Écouter les interactions
 */

productSearch.addEventListener("input", renderProducts);

categoryFilter.addEventListener("change", renderProducts);

sortProducts.addEventListener("change", renderProducts);


/*
 * Premier affichage
 */

renderProducts();