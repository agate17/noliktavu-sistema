DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;

-- 2) Recreate `orders`
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(255)    NOT NULL,
    customer_email VARCHAR(255)   NOT NULL,
    customer_phone VARCHAR(50),
    status      ENUM('pending','processing','fulfilled','cancelled')
                NOT NULL DEFAULT 'pending',
    total_amount DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    created_by   INT,
    created_at   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP        NOT NULL
                  DEFAULT CURRENT_TIMESTAMP
                  ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user
      FOREIGN KEY (created_by)
      REFERENCES users(id)
      ON DELETE SET NULL
) ENGINE=InnoDB;

-- 3) Recreate `order_items`
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id     INT NOT NULL,
    product_id   INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity     INT         NOT NULL,
    unit_price   DECIMAL(10,2) NOT NULL,
    line_total   DECIMAL(10,2) NOT NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_items_order
      FOREIGN KEY (order_id)
      REFERENCES orders(id)
      ON DELETE CASCADE,
    CONSTRAINT fk_items_product
      FOREIGN KEY (product_id)
      REFERENCES products(id)
      ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4) Indexes for performance
CREATE INDEX idx_orders_status     ON orders(status);
CREATE INDEX idx_orders_created_by ON orders(created_by);
CREATE INDEX idx_orders_created_at ON orders(created_at);

CREATE INDEX idx_items_order_id   ON order_items(order_id);
CREATE INDEX idx_items_product_id ON order_items(product_id);

-- 5) (Optional) Sample data
INSERT INTO orders
  (customer_name, customer_email, customer_phone, status, total_amount, created_by)
VALUES
  ('Jānis Bērziņš','janis.berzins@example.com','+37120123456','pending',   24.00, 1),
  ('Anna Liepa',   'anna.liepa@example.com',   '+37120654321','processing',48.00, 1),
  ('Pēteris Kalns','peteris.kalns@example.com',NULL,            'fulfilled',36.00, 1);

INSERT INTO order_items
  (order_id, product_id, product_name, quantity, unit_price, line_total)
VALUES
  (1, 1, 'Milti', 2, 12.00, 24.00),
  (2, 1, 'Milti', 4, 12.00, 48.00),
  (3, 1, 'Milti', 3, 12.00, 36.00);