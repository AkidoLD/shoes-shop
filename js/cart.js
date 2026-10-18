"use strict";

/* ==========================================
   SHOES - SHOP
   GESTION DU PANIER
========================================== */

const Cart = (() => {

    const STORAGE_KEY = "shoes_shop_cart";

    /*
     * Charger le panier enregistré.
     */

    function getItems() {

        try {

            const savedCart =
                localStorage.getItem(STORAGE_KEY);

            if (!savedCart) {
                return [];
            }

            const items = JSON.parse(savedCart);

            return Array.isArray(items) ? items : [];

        } catch (error) {

            console.error(
                "Erreur de lecture du panier :",
                error
            );

            return [];

        }

    }


    /*
     * Enregistrer le panier.
     */

    function saveItems(items) {

        try {

            localStorage.setItem(
                STORAGE_KEY,
                JSON.stringify(items)
            );

            return true;

        } catch (error) {

            console.error(
                "Impossible d'enregistrer le panier :",
                error
            );

            return false;

        }

    }


    /*
     * Calculer le nombre total d'articles.
     */

    function getCount() {

        return getItems().reduce(
            (total, item) => total + item.quantity,
            0
        );

    }


    /*
     * Calculer le montant total.
     */

    function getTotal() {

        return getItems().reduce(
            (total, item) =>
                total + item.price * item.quantity,
            0
        );

    }


    /*
     * Ajouter un produit.
     */

    function add(product, size, color) {

        if (
            !product ||
            !Number.isInteger(product.id) ||
            !Number.isFinite(product.price) ||
            product.price < 0 ||
            !product.available ||
            !size ||
            !color
        ) {

            return {
                success: false,
                message: "Informations du produit invalides."
            };

        }


        const items = getItems();


        /*
         * Un même produit avec une taille et une
         * couleur différentes constitue une ligne
         * distincte dans le panier.
         */

        const existingItem = items.find(item =>
            item.productId === product.id &&
            item.size === size &&
            item.color === color
        );


        if (existingItem) {

            existingItem.quantity += 1;

        } else {

            items.push({

                cartId:
                    product.id + "-" +
                    size + "-" +
                    color,

                productId: product.id,

                name: product.name,

                reference: product.reference,

                image: product.image,

                price: product.price,

                size: size,

                color: color,

                quantity: 1

            });

        }


        const saved = saveItems(items);


        if (!saved) {

            return {
                success: false,
                message: "Impossible d'enregistrer le panier."
            };

        }


        return {
            success: true,
            message: "Produit ajouté au panier."
        };

    }


    /*
     * Modifier la quantité.
     */

    function updateQuantity(cartId, quantity) {

        if (
            !Number.isInteger(quantity) ||
            quantity < 1 ||
            quantity > 99
        ) {

            return false;

        }


        const items = getItems();

        const item = items.find(
            element => element.cartId === cartId
        );


        if (!item) {
            return false;
        }


        item.quantity = quantity;

        return saveItems(items);

    }


    /*
     * Supprimer une ligne.
     */

    function remove(cartId) {

        const items = getItems().filter(
            item => item.cartId !== cartId
        );

        return saveItems(items);

    }


    /*
     * Vider le panier.
     */

    function clear() {

        try {

            localStorage.removeItem(STORAGE_KEY);

            return true;

        } catch (error) {

            console.error(error);

            return false;

        }

    }


    /*
     * Formater un prix en FCFA.
     */

    function formatPrice(price) {

        return new Intl.NumberFormat("fr-FR", {
            maximumFractionDigits: 0
        }).format(price) + " FCFA";

    }


    /*
     * API publique du panier.
     */

    return {

        getItems,
        getCount,
        getTotal,
        add,
        updateQuantity,
        remove,
        clear,
        formatPrice

    };

})();