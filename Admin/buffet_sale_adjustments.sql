CREATE TABLE IF NOT EXISTS `buffet_sale_adjustments` (
  `adjustment_id` int(11) NOT NULL AUTO_INCREMENT,
  `buffet_item_id` int(11) NOT NULL,
  `adjustment_type` ENUM('discount', 'surcharge', 'tax') NOT NULL DEFAULT 'discount',
  `adjustment_amount` decimal(10,2) NOT NULL,
  `adjustment_reason` text DEFAULT NULL,
  `status` ENUM('active', 'canceled') DEFAULT 'active',
  `cancellation_reason` TEXT DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`adjustment_id`),
  FOREIGN KEY (`buffet_item_id`) REFERENCES `buffet_sale_items`(`buffet_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
