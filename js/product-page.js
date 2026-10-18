"use strict";

/*
 * SHOES - SHOP
 * Gestion de la fiche produit
 */


/* ========================================
   CONFIGURATION
======================================== */

// Numéro WhatsApp de la boutique.
// Format international sans + ni espaces.

const WHATSAPP_NUMBER = "237653241046";


/* ========================================
   ÉLÉMENTS HTML
======================================== */

const productDetail =
    document.getElementById("productDetail");

const productError =
    document.getElementById("productError");

const breadcrumbProduct =
    document.getElementById("breadcrumbProduct");


/* ========================================
   OUTILS
======================================== */

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


function formatPrice(price) {
    return new Intl.NumberFormat("fr-FR", {
        maximumFractionDigits: 0
    }).format(price) + " FCFA";
}


/* ========================================
   IDENTIFICATION DU PRODUIT
======================================== */

const params = new URLSearchParams(
    window.location.search
);

const productId = Number(params.get("id"));

const product = PRODUCTS.find(function (item) {
    return item.id === productId;
});


/* ========================================
   ÉTAT DE LA SÉLECTION
======================================== */

let selectedSize = null;
let selectedColor = null;


/* ========================================
   AFFICHAGE D'UNE ERREUR
======================================== */

function showProductError() {
    productDetail.hidden = true;
    productError.hidden = false;

    document.title = "Produit introuvable | SHOES - SHOP";
}


/* ========================================
   AFFICHAGE DU PRODUIT
======================================== */

function renderProduct() {

    if (
        !Number.isInteger(productId) ||
        !product
    ) {
        showProductError();
        return;
    }

    if (!product.available) {
        showProductError();
        return;
    }


    // Mise à jour du titre et du fil d'ariane

    document.title =
        product.name + " | SHOES - SHOP";

    breadcrumbProduct.textContent =
        product.name;


    // Prix promotionnel éventuel

    const oldPriceHTML = product.oldPrice
        ? `<span class="product-detail-old-price">
               ${formatPrice(product.oldPrice)}
           </span>`
        : "";


    // Génération des tailles

    const sizesHTML = product.sizes
        .map(function (size) {
            return `
                <button
                    type="button"
                    class="option-button size-option"
                    data-size="${escapeHTML(size)}"
                    aria-pressed="false">

                    ${escapeHTML(size)}

                </button>
            `;
        })
        .join("");


    // Génération des couleurs

    const colorsHTML = product.colors
        .map(function (color) {
            return `
                <button
                    type="button"
                    class="option-button color-option"
                    data-color="${escapeHTML(color)}"
                    aria-pressed="false">

                    ${escapeHTML(color)}

                </button>
            `;
        })
        .join("");


    // Structure de la fiche produit

    productDetail.innerHTML = `

        <div class="product-detail-layout">


            <!-- IMAGE -->

            <div class="product-detail-image">

                <img
                    src="${escapeHTML(product.image)}"
                    alt="${escapeHTML(product.name)}">

            </div>



            <!-- INFORMATIONS -->

            <div class="product-detail-info">


                <p class="product-detail-category">

                    ${escapeHTML(product.categoryLabel)}

                </p>


                <h1 class="product-detail-title">

                    ${escapeHTML(product.name)}

                </h1>


                <p class="product-detail-reference">

                    Référence :
                    ${escapeHTML(product.reference)}

                </p>


                <p class="product-detail-price">

                    ${formatPrice(product.price)}

                    ${oldPriceHTML}

                </p>


                <p class="product-detail-description">

                    ${escapeHTML(product.description)}

                </p>


                <div class="product-detail-stock">

                    Disponible sur commande

                </div>



                <!-- CHOIX DE LA TAILLE -->

                <div class="product-option">

                    <label>

                        Choisis ta taille

                    </label>

                    <div class="product-size-list">

                        ${sizesHTML}

                    </div>

                </div>



                <!-- CHOIX DE LA COULEUR -->

                <div class="product-option">

                    <label>

                        Choisis ta couleur

                    </label>

                    <div class="product-color-list">

                        ${colorsHTML}

                    </div>

                </div>



                <!-- ACTIONS -->

                <div class="product-detail-actions">

    <button
        type="button"
        id="addToCart"
        class="btn btn-outline">

        🛒 Ajouter au panier

    </button>

    <button
        type="button"
        id="orderWhatsApp"
        class="btn btn-primary">

        Commander directement →

    </button>

    <a
        href="index.html#catalogue"
        class="btn btn-outline">

        ← Retour au catalogue

    </a>

    <a
        href="panier.html"
        class="btn btn-outline">

        Voir mon panier

    </a>

</div>


                <p class="product-note">

                    Confirme la disponibilité de la taille
                    et de la couleur avec notre équipe
                    avant de finaliser ta commande.

                </p>


            </div>

        </div>

    `;


    // Initialisation des interactions

    initializeProductOptions();

updateProductSEO();

}


/* ========================================
   CHOIX DES OPTIONS
======================================== */

function initializeProductOptions() {

    const sizeButtons =
        document.querySelectorAll(".size-option");

    const colorButtons =
        document.querySelectorAll(".color-option");


    // Sélection de la taille

    sizeButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            selectedSize = button.dataset.size;

            sizeButtons.forEach(function (item) {

                item.classList.remove("selected");

                item.setAttribute(
                    "aria-pressed",
                    "false"
                );

            });

            button.classList.add("selected");

            button.setAttribute(
                "aria-pressed",
                "true"
            );

        });

    });


    // Sélection de la couleur

    colorButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            selectedColor = button.dataset.color;

            colorButtons.forEach(function (item) {

                item.classList.remove("selected");

                item.setAttribute(
                    "aria-pressed",
                    "false"
                );

            });

            button.classList.add("selected");

            button.setAttribute(
                "aria-pressed",
                "true"
            );

        });

    });


    // Bouton de commande

    document
        .getElementById("orderWhatsApp")
        .addEventListener("click", orderOnWhatsApp);
    
    /*
 * Ajouter au panier.
 */

document
    .getElementById("addToCart")
    .addEventListener("click", function () {

        if (!selectedSize) {

            alert("Veuillez choisir une taille.");

            return;

        }

        if (!selectedColor) {

            alert("Veuillez choisir une couleur.");

            return;

        }

        const result = Cart.add(
            product,
            selectedSize,
            selectedColor
        );

        if (!result.success) {

            alert(result.message);

            return;

        }

        alert("Chaussure ajoutée au panier !");

    });

}


/* ========================================
   COMMANDE WHATSAPP
======================================== */

function orderOnWhatsApp() {

    if (!selectedSize) {

        alert("Veuillez choisir une taille.");

        return;

    }

    if (!selectedColor) {

        alert("Veuillez choisir une couleur.");

        return;

    }


    const message = [

        "Bonjour SHOES - SHOP !",

        "",

        "Je souhaite commander cette chaussure :",

        "Produit : " + product.name,

        "Référence : " + product.reference,

        "Prix : " + formatPrice(product.price),

        "Taille : " + selectedSize,

        "Couleur : " + selectedColor,

        "",

        "Merci de confirmer la disponibilité."

    ].join("\n");


    const whatsappURL =
        "https://wa.me/" +
        WHATSAPP_NUMBER +
        "?text=" +
        encodeURIComponent(message);


    window.open(
        whatsappURL,
        "_blank",
        "noopener,noreferrer"
    );

}


/* ========================================
   INITIALISATION
======================================== */

renderProduct();

/* ========================================
   SEO DYNAMIQUE
======================================== */


/*
 * Mettre à jour une balise meta.
 */

function updateMetaTag(attribute, key, content) {

    let tag = document.querySelector(
        `meta[${attribute}="${key}"]`
    );

    if (!tag) {

        tag = document.createElement("meta");

        tag.setAttribute(attribute, key);

        document.head.appendChild(tag);

    }

    tag.setAttribute("content", content);

}


/*
 * Mettre à jour les métadonnées du produit.
 */

function updateProductSEO() {

    if (!product || !product.available) {
        return;
    }

    const productName = product.name;

    const productDescription =
        product.description;

    const productImage =
        product.image;

    const productPrice =
        String(product.price);

    const productReference =
        product.reference;

    const canonicalProductURL =
        window.location.href.split("#")[0];


    /*
     * Titre du document.
     */

    document.title =
        productName + " | SHOES - SHOP";


    /*
     * Métadonnées SEO.
     */

    updateMetaTag(
        "name",
        "description",
        productDescription
    );


    /*
     * Open Graph.
     */

    updateMetaTag(
        "property",
        "og:type",
        "product"
    );

    updateMetaTag(
        "property",
        "og:site_name",
        "SHOES - SHOP"
    );

    updateMetaTag(
        "property",
        "og:title",
        productName + " | SHOES - SHOP"
    );

    updateMetaTag(
        "property",
        "og:description",
        productDescription
    );

    updateMetaTag(
        "property",
        "og:image",
        productImage
    );

    updateMetaTag(
        "property",
        "og:url",
        canonicalProductURL
    );


    /*
     * Informations commerciales Open Graph.
     */

    updateMetaTag(
        "property",
        "product:price:amount",
        productPrice
    );

    updateMetaTag(
        "property",
        "product:price:currency",
        "XAF"
    );


    /*
     * Twitter / X.
     */

    updateMetaTag(
        "name",
        "twitter:card",
        "summary_large_image"
    );

    updateMetaTag(
        "name",
        "twitter:title",
        productName + " | SHOES - SHOP"
    );

    updateMetaTag(
        "name",
        "twitter:description",
        productDescription
    );

    updateMetaTag(
        "name",
        "twitter:image",
        productImage
    );


    /*
     * Données structurées Schema.org.
     */

    const structuredData = {

        "@context": "https://schema.org",

        "@type": "Product",

        "name": productName,

        "description": productDescription,

        "sku": productReference,

        "category": product.categoryLabel,

        "image": [
            productImage
        ],

        "offers": {

            "@type": "Offer",

            "priceCurrency": "XAF",

            "price": productPrice,

            "availability":
                "https://schema.org/InStock",

            "url": canonicalProductURL

        }

    };


    let jsonLd = document.getElementById(
        "productStructuredData"
    );

    if (!jsonLd) {

        jsonLd = document.createElement("script");

        jsonLd.type = "application/ld+json";

        jsonLd.id = "productStructuredData";

        document.head.appendChild(jsonLd);

    }


    jsonLd.textContent =
        JSON.stringify(structuredData, null, 2);

}