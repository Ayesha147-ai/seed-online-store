-- ============================================================
--   TRACKSEED DATABASE — Complete per Documentation
--   PROJECT ID: BSIT22-G39
--   Tables: users, agents, categories, products, cart,
--           orders, order_items, transactions, feedback
-- ============================================================

CREATE DATABASE IF NOT EXISTS trackseed_db;
USE trackseed_db;

-- ============================================================
-- 1. USERS TABLE
--    Stores all users: farmer, agent, admin
-- ============================================================
CREATE TABLE users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100)  NOT NULL,
    email        VARCHAR(150)  UNIQUE NOT NULL,
    phone        VARCHAR(20),
    password     VARCHAR(255)  NOT NULL,
    role         ENUM('admin','agent','farmer') DEFAULT 'farmer',
    location     VARCHAR(100),                          -- farmer ka location
    status       ENUM('active','blocked','pending') DEFAULT 'active',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 2. AGENTS TABLE
--    Extra info for seed agents
-- ============================================================
CREATE TABLE agents (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    agency_name   VARCHAR(150),
    contact_no    VARCHAR(20),
    cnic          VARCHAR(15),
    province      VARCHAR(50),
    city          VARCHAR(50),
    is_approved   TINYINT DEFAULT 0,   -- 0=pending, 1=approved, 2=rejected
    approved_at   TIMESTAMP NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 3. CATEGORIES TABLE
--    Vegetable, Fruit, Herb
-- ============================================================
CREATE TABLE categories (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(100) NOT NULL,
    slug  VARCHAR(100) NOT NULL UNIQUE
);

INSERT INTO categories (name, slug) VALUES
('Vegetable', 'vegetable'),
('Fruit',     'fruit'),
('Herb',      'herb');

-- ============================================================
-- 4. PRODUCTS (SEEDS) TABLE
--    Agent uploads seeds here — admin approves
-- ============================================================
CREATE TABLE products (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    agent_id     INT NOT NULL,
    category_id  INT NOT NULL,
    name         VARCHAR(200) NOT NULL,
    seed_type    VARCHAR(100),
    description  TEXT,
    price        DECIMAL(10,2) NOT NULL,
    stock        INT DEFAULT 0,
    quality      VARCHAR(100),
    weight       VARCHAR(20),
    season       VARCHAR(50),
    image        VARCHAR(255),
    status       ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id)    REFERENCES users(id)       ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)  ON DELETE CASCADE
);

-- ============================================================
-- 5. CART TABLE (Shopping Cart)
--    Per user, per session
-- ============================================================
CREATE TABLE cart (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    product_id  INT NOT NULL,
    quantity    INT DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- 6. ORDERS TABLE
-- ============================================================
CREATE TABLE orders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_number    VARCHAR(20) UNIQUE NOT NULL,
    user_id         INT NOT NULL,
    full_name       VARCHAR(100),
    email           VARCHAR(150),
    phone           VARCHAR(20),
    city            VARCHAR(50),
    province        VARCHAR(50),
    warehouse       VARCHAR(100),
    address         TEXT,
    payment_method  ENUM('jazzcash','easypaisa','cod','stripe') DEFAULT 'cod',
    subtotal        DECIMAL(10,2) DEFAULT 0,
    delivery_charge DECIMAL(10,2) DEFAULT 50,
    grand_total     DECIMAL(10,2) DEFAULT 0,
    status          ENUM('placed','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'placed',
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- 7. ORDER ITEMS TABLE
-- ============================================================
CREATE TABLE order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT NOT NULL,
    product_id  INT NOT NULL,
    product_name VARCHAR(200),
    quantity    INT DEFAULT 1,
    unit_price  DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- 8. TRANSACTIONS / PAYMENT TABLE
--    pay_method: jazzcash, easypaisa, cod, stripe
--    pay_status: paid, unpaid, pending, failed
-- ============================================================
CREATE TABLE transactions (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    order_id       INT NOT NULL,
    user_id        INT NOT NULL,
    pay_amount     DECIMAL(10,2) NOT NULL,
    pay_date       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pay_method     ENUM('jazzcash','easypaisa','cod','stripe') NOT NULL,
    pay_status     ENUM('paid','unpaid','pending','failed') DEFAULT 'pending',
    transaction_ref VARCHAR(100),   -- JazzCash/Easypaisa/Stripe reference number
    notes          TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id)  ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id)   ON DELETE CASCADE
);

-- ============================================================
-- 9. FEEDBACK / REVIEWS TABLE
--    Only users who placed order can give feedback
-- ============================================================
CREATE TABLE feedback (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    product_id  INT NOT NULL,
    order_id    INT NOT NULL,
    rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_feedback (user_id, order_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE
);

-- ============================================================
-- 10. PASSWORD RESETS TABLE
-- ============================================================
CREATE TABLE password_resets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(150),
    otp        VARCHAR(6),
    expires_at DATETIME,
    used       TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 11. ADMIN USER — Insert directly (no UI registration)
--     Email: admin@trackseed.pk
--     Password: admin123
-- ============================================================
INSERT INTO users (name, email, phone, password, role, status) VALUES
(
    'TrackSeed Admin',
    'admin@trackseed.pk',
    '+923316481168',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'active'
);
