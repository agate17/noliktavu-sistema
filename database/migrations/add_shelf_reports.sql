USE stash_warehouse;

-- Create shelf reports table
CREATE TABLE IF NOT EXISTS shelf_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('current', 'changes') NOT NULL,
    date_from DATE,
    date_to DATE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create shelf report items table
CREATE TABLE IF NOT EXISTS shelf_report_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT NOT NULL,
    product_id INT NOT NULL,
    shelf_location VARCHAR(50),
    quantity INT,
    FOREIGN KEY (report_id) REFERENCES shelf_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
); 