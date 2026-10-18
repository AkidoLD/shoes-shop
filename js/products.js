"use strict";

/*
 * SHOES - SHOP
 * Catalogue central des produits
 */

const PRODUCTS = [
    {
        id: 1,
        reference: "SH001",
        name: "Sneakers Urban",
        category: "sneakers",
        categoryLabel: "Sneakers",
        price: 15000,
        oldPrice: 18000,
        image: "https://placehold.co/500x450/f0f0f0/222222?text=Sneakers",
        description: "Des sneakers modernes pour un style urbain.",
        sizes: [39, 40, 41, 42, 43, 44],
        colors: ["Noir", "Blanc"],
        featured: true,
        isNew: true,
        available: true
    },

    {
        id: 2,
        reference: "SH002",
        name: "Classic Elegance",
        category: "ville",
        categoryLabel: "Ville",
        price: 20000,
        oldPrice: null,
        image: "https://placehold.co/500x450/f0f0f0/222222?text=Classiques",
        description: "Des chaussures élégantes pour les occasions spéciales.",
        sizes: [39, 40, 41, 42, 43],
        colors: ["Noir", "Marron"],
        featured: true,
        isNew: false,
        available: true
    },

    {
        id: 3,
        reference: "SH003",
        name: "Sport Active",
        category: "sport",
        categoryLabel: "Sport",
        price: 18000,
        oldPrice: 22000,
        image: "https://placehold.co/500x450/f0f0f0/222222?text=Sport",
        description: "Des chaussures confortables pour les activités sportives.",
        sizes: [40, 41, 42, 43, 44, 45],
        colors: ["Noir", "Rouge", "Blanc"],
        featured: true,
        isNew: true,
        available: true
    },

    {
        id: 4,
        reference: "SH004",
        name: "Sandales Chic",
        category: "sandales",
        categoryLabel: "Sandales",
        price: 8000,
        oldPrice: null,
        image: "https://placehold.co/500x450/f0f0f0/222222?text=Sandales",
        description: "Des sandales simples et élégantes pour le quotidien.",
        sizes: [38, 39, 40, 41, 42, 43],
        colors: ["Noir", "Marron"],
        featured: false,
        isNew: false,
        available: true
    },

    {
        id: 5,
        reference: "SH005",
        name: "Street Runner",
        category: "sneakers",
        categoryLabel: "Sneakers",
        price: 17000,
        oldPrice: null,
        image: "https://placehold.co/500x450/f0f0f0/222222?text=Street+Runner",
        description: "Une paire dynamique inspirée du style streetwear.",
        sizes: [39, 40, 41, 42, 43, 44],
        colors: ["Noir", "Blanc", "Rouge"],
        featured: false,
        isNew: true,
        available: true
    },

    {
        id: 6,
        reference: "SH006",
        name: "Classic Loafer",
        category: "ville",
        categoryLabel: "Ville",
        price: 16000,
        oldPrice: null,
        image: "https://placehold.co/500x450/f0f0f0/222222?text=Loafer",
        description: "Un modèle classique pour une tenue soignée.",
        sizes: [39, 40, 41, 42, 43],
        colors: ["Noir", "Marron"],
        featured: false,
        isNew: false,
        available: true
    },

    {
        id: 7,
        reference: "SH007",
        name: "Power Training",
        category: "sport",
        categoryLabel: "Sport",
        price: 22000,
        oldPrice: 25000,
        image: "https://placehold.co/500x450/f0f0f0/222222?text=Training",
        description: "Un modèle sportif pour accompagner tes entraînements.",
        sizes: [40, 41, 42, 43, 44, 45],
        colors: ["Noir", "Rouge"],
        featured: false,
        isNew: true,
        available: true
    },

    {
        id: 8,
        reference: "SH008",
        name: "Sandales Comfort",
        category: "sandales",
        categoryLabel: "Sandales",
        price: 6500,
        oldPrice: null,
        image: "https://placehold.co/500x450/f0f0f0/222222?text=Comfort",
        description: "Des sandales pratiques pour les journées décontractées.",
        sizes: [38, 39, 40, 41, 42],
        colors: ["Noir", "Marron"],
        featured: false,
        isNew: false,
        available: true
    }
];