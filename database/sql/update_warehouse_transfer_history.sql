CREATE TABLE IF NOT EXISTS `warehouse_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transfer_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_storeroom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_storeroom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_rack_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transferred_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `warehouse_transfer_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `warehouse_transfer_id` bigint(20) UNSIGNED NOT NULL,
  `domestic_inventory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `packing_carton_id` bigint(20) UNSIGNED DEFAULT NULL,
  `from_rack_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `color_id` bigint(20) UNSIGNED DEFAULT NULL,
  `size_set_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `wt_items_transfer_id_fk` FOREIGN KEY (`warehouse_transfer_id`) REFERENCES `warehouse_transfers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
