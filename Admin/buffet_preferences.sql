-- Create buffet_preferences table if it doesn't exist
CREATE TABLE IF NOT EXISTS buffet_preferences (
  period_id           INT AUTO_INCREMENT PRIMARY KEY,
  period_name         ENUM('Morning','Noon','Evening') NOT NULL,
  start_time          TIME NOT NULL,
  end_time            TIME NOT NULL,
  base_price          DECIMAL(10,2) NOT NULL,

  -- two simple discount fields (use one, both, or leave NULL)
  fixed_discount      DECIMAL(10,2) DEFAULT NULL,  -- e.g. 5.00 means −5 monetary units
  percentage_discount DECIMAL(5,2)  DEFAULT NULL,  -- e.g. 10.00 means −10 %

  valid_from          DATE DEFAULT NULL,
  valid_to            DATE DEFAULT NULL,
  is_active           TINYINT(1) DEFAULT 1          -- 1 = active, 0 = inactive
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4;

-- Insert initial default periods if the table is empty
INSERT INTO buffet_preferences (period_name, start_time, end_time, base_price, is_active)
SELECT 'Morning', '06:00:00', '10:00:00', 8000.00, 1
WHERE NOT EXISTS (SELECT 1 FROM buffet_preferences WHERE period_name = 'Morning');

INSERT INTO buffet_preferences (period_name, start_time, end_time, base_price, is_active)
SELECT 'Noon', '11:00:00', '14:00:00', 10000.00, 1
WHERE NOT EXISTS (SELECT 1 FROM buffet_preferences WHERE period_name = 'Noon');

INSERT INTO buffet_preferences (period_name, start_time, end_time, base_price, is_active)
SELECT 'Evening', '17:00:00', '22:00:00', 12000.00, 1
WHERE NOT EXISTS (SELECT 1 FROM buffet_preferences WHERE period_name = 'Evening');
