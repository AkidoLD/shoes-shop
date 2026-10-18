"use strict";

/* ==========================================
   AFFICHAGE DU PANIER
========================================== */

const cartContent =
    document.getElementById("cartContent");


/*
 * Échapper les caractères HTML.
 */

function cartEscapeHTML(value) {

    return String(value).replace(/[&<>"']/g, character => {

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
 * Vérifier les données enregistrées.
 *
 * localStorage peut être modifié par l'utilisateur.
 * Nous ne devons donc pas lui faire confiance.
 */

function getValidCartItems() {

    return Cart.getItems().filter(item => {

        return (
            Number.isInteger(item.productId) &&
            typeof item.name === "string" &&
            typeof item.reference === "string" &&
            typeof item.size === "string" &&
            typeof item.color === "string" &&
            Number.isFinite(item.price) &&
            item.price >= 0 &&
            Number.isInteger(item.quantity) &&
            item.quantity >= 1 &&
            item.quantity <= 99
        );

    });

}


/*
 * Afficher un panier vide.
 */

function renderEmptyCart() {

    cartContent.innerHTML = `

        <div class="cart-empty">

            <h2>Ton panier est vide.</h2>

            <p>
                Découvre nos chaussures et ajoute
                tes modèles préférés.
            </p>

            <a href="index.html#catalogue"
               class="btn btn-primary">

                Découvrir les chaussures →

            </a>

        </div>

    `;

}


/*
 * Afficher les articles.
 */

function renderCart() {

    const items = getValidCartItems();

    if (items.length === 0) {

        renderEmptyCart();

        return;

    }


    const total = items.reduce(
        (sum, item) => sum + item.price * item.quantity,
        0
    );


    const itemsHTML = items.map(item => {

        const safeId =
            cartEscapeHTML(item.cartId);

        const subtotal =
            item.price * item.quantity;

        return `

            <article class="cart-item">

                <img
                    class="cart-item-image"
                    src="${cartEscapeHTML(item.image)}"
                    alt="${cartEscapeHTML(item.name)}">

                <div>

                    <h2 class="cart-item-name">
                        ${cartEscapeHTML(item.name)}
                    </h2>

                    <p class="cart-item-info">

                        Référence :
                        ${cartEscapeHTML(item.reference)}

                        <br>

                        Taille :
                        ${cartEscapeHTML(item.size)}

                        <br>

                        Couleur :
                        ${cartEscapeHTML(item.color)}

                    </p>

                    <p class="cart-item-price">

                        ${Cart.formatPrice(subtotal)}

                    </p>

                    <div class="cart-item-actions">

                        <label>

                            Quantité :

                            <input
                                class="cart-quantity"
                                type="number"
                                min="1"
                                max="99"
                                value="${item.quantity}"
                                data-quantity-id="${safeId}">

                        </label>

                        <button
                            type="button"
                            class="cart-remove"
                            data-remove-id="${safeId}">

                            Supprimer

                        </button>

                    </div>

                </div>

            </article>

        `;

    }).join("");


    cartContent.innerHTML = `

        <div class="cart-layout">

            <div class="cart-list">

                ${itemsHTML}

            </div>


            <aside class="cart-summary">

                <h2>Résumé de la commande</h2>

                <div class="cart-summary-row">

                    <span>Articles</span>

                    <strong>${items.reduce(
                        (sum, item) => sum + item.quantity,
                        0
                    )}</strong>

                </div>

                <div class="cart-summary-row">

                    <span>Sous-total</span>

                    <strong>
                        ${Cart.formatPrice(total)}
                    </strong>

                </div>

                <div class="cart-summary-row">

                    <span>Livraison</span>

                    <span>
                        À confirmer
                    </span>

                </div>

                <div class="cart-summary-row cart-summary-total">

                    <span>Total des articles</span>

                    <strong>
                        ${Cart.formatPrice(total)}
                    </strong>

                </div>

                <button
                    type="button"
                    id="checkoutWhatsApp"
                    class="btn btn-primary">

                    Commander sur WhatsApp →

                </button>

                <button
                    type="button"
                    id="clearCart"
                    class="btn btn-outline">

                    Vider le panier

                </button>

            </aside>

        </div>

    `;


    initializeCartEvents();

}


/*
 * Initialiser les événements.
 */

function initializeCartEvents() {


    /*
     * Modifier les quantités.
     */

    document.querySelectorAll(
        "[data-quantity-id]"
    ).forEach(input => {

        input.addEventListener("change", () => {

            const quantity = Number(input.value);

            if (
                !Number.isInteger(quantity) ||
                quantity < 1 ||
                quantity > 99
            ) {

                alert(
                    "La quantité doit être comprise entre 1 et 99."
                );

                renderCart();

                return;

            }

            const success = Cart.updateQuantity(
                input.dataset.quantityId,
                quantity
            );

            if (!success) {

                alert(
                    "Impossible de modifier cette quantité."
                );

            }

            renderCart();

        });

    });


    /*
     * Supprimer un article.
     */

    document.querySelectorAll(
        "[data-remove-id]"
    ).forEach(button => {

        button.addEventListener("click", () => {

            const success = Cart.remove(
                button.dataset.removeId
            );

            if (!success) {

                alert(
                    "Impossible de supprimer cet article."
                );

            }

            renderCart();

        });

    });


    /*
     * Vider le panier.
     */

    document.getElementById(
        "clearCart"
    ).addEventListener("click", () => {

        const confirmed = confirm(
            "Veux-tu vraiment vider ton panier ?"
        );

        if (!confirmed) {
            return;
        }

        if (!Cart.clear()) {

            alert(
                "Impossible de vider le panier."
            );

            return;

        }

        renderCart();

    });


    /*
     * Commander.
     */

    document.getElementById(
        "checkoutWhatsApp"
    ).addEventListener("click", checkoutWhatsApp);

}


/*
 * Générer la commande WhatsApp.
 */

function checkoutWhatsApp() {

    const items = getValidCartItems();

    if (items.length === 0) {

        alert("Ton panier est vide.");

        return;

    }


    const total = items.reduce(
        (sum, item) => sum + item.price * item.quantity,
        0
    );


    const lines = [

        "Bonjour SHOES - SHOP !",

        "",

        "Je souhaite passer cette commande :",

        ""

    ];


    items.forEach((item, index) => {

        lines.push(
            (index + 1) + ". " + item.name
        );

        lines.push(
            "Référence : " + item.reference
        );

        lines.push(
            "Taille : " + item.size
        );

        lines.push(
            "Couleur : " + item.color
        );

        lines.push(
            "Quantité : " + item.quantity
        );

        lines.push(
            "Prix unitaire : " +
            Cart.formatPrice(item.price)
        );

        lines.push(
            "Sous-total : " +
            Cart.formatPrice(item.price * item.quantity)
        );

        lines.push("");

    });


    lines.push(
        "Total des articles : " +
        Cart.formatPrice(total)
    );

    lines.push("");

    lines.push(
        "Merci de confirmer la disponibilité, " +
        "les frais de livraison et le montant final."
    );


    const message = lines.join("\n");

    const url =
        "https://wa.me/237653241046?text=" +
        encodeURIComponent(message);


    window.open(
        url,
        "_blank",
        "noopener,noreferrer"
    );

}


/*
 * Initialisation.
 */

renderCart();