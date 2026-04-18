-- SQL to add new metadata fields to agent_orders table
-- These changes were previously applied via Laravel migration: 
-- 2026_04_18_095823_add_remark_booking_station_transport_to_agent_orders_table.php

ALTER TABLE `agent_orders` 
ADD COLUMN `remark` TEXT NULL AFTER `order_date`,
ADD COLUMN `booking_station` VARCHAR(255) NULL AFTER `remark`,
ADD COLUMN `transport` VARCHAR(255) NULL AFTER `booking_station`;

-- Verification Query
-- SELECT id, order_date, remark, booking_station, transport FROM agent_orders LIMIT 10;
