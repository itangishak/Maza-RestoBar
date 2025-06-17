-- SQL script to populate initial accompaniments data

-- First, ensure the tables exist
CREATE TABLE IF NOT EXISTS buffet_sale_items (
    buffet_item_id INT(11) NOT NULL AUTO_INCREMENT,
    sale_date DATE NOT NULL,
    time_of_day ENUM('Morning', 'Noon', 'Evening') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (buffet_item_id)
);

CREATE TABLE IF NOT EXISTS buffet_accompaniments (
    accompaniment_id INT(11) NOT NULL AUTO_INCREMENT,
    buffet_item_id INT(11) NOT NULL,
    accompaniment_name VARCHAR(255) NOT NULL,
    accompaniment_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (accompaniment_id),
    FOREIGN KEY (buffet_item_id) REFERENCES buffet_sale_items(buffet_item_id)
);

CREATE TABLE IF NOT EXISTS buffet_sale_adjustments (
    adjustment_id INT(11) NOT NULL AUTO_INCREMENT,
    buffet_item_id INT(11) NOT NULL,
    adjustment_type ENUM('Discount', 'Refund', 'Other') NOT NULL,
    adjustment_amount DECIMAL(10,2) NOT NULL,
    adjustment_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (adjustment_id),
    FOREIGN KEY (buffet_item_id) REFERENCES buffet_sale_items(buffet_item_id)
);

-- Create a table to store available accompaniments for selection
CREATE TABLE IF NOT EXISTS available_accompaniments (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Insert some default accompaniments
INSERT INTO available_accompaniments (name, price) VALUES
('Extra Rice', 1500.00),
('Chips', 2000.00),
('Salad', 1000.00),
('Bread', 1000.00),
('Grilled Vegetables', 1500.00),
('Extra Sauce', 500.00),
('Plantain', 1500.00),
('Avocado', 1000.00);

-- Insert default buffet prices for different times of day
INSERT INTO buffet_sale_items (sale_date, time_of_day, price) VALUES
(CURRENT_DATE, 'Morning', 8000.00),
(CURRENT_DATE, 'Noon', 10000.00),
(CURRENT_DATE, 'Evening', 12000.00);
