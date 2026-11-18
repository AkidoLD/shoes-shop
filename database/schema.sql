CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    label TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ref TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    description TEXT NOT NULL DEFAULT '',
    price INTEGER NOT NULL CHECK (price >= 0),
    old_price INTEGER NULL CHECK (old_price IS NULL OR old_price >= 0),
    currency TEXT NOT NULL DEFAULT 'XAF',
    image TEXT NOT NULL,
    og_image TEXT NULL,
    category_id INTEGER NULL REFERENCES categories(id) ON DELETE SET NULL,
    available INTEGER NOT NULL DEFAULT 1 CHECK (available IN (0, 1)),
    featured INTEGER NOT NULL DEFAULT 0 CHECK (featured IN (0, 1)),
    is_new INTEGER NOT NULL DEFAULT 0 CHECK (is_new IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_price ON products(price);
CREATE INDEX IF NOT EXISTS idx_products_name ON products(name);

CREATE TABLE IF NOT EXISTS product_sizes (
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    size INTEGER NOT NULL,
    position INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (product_id, size)
);

CREATE TABLE IF NOT EXISTS product_colors (
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    color TEXT NOT NULL,
    position INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (product_id, color)
);
