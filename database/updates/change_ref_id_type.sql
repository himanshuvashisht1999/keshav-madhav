-- Update ref_id column type to support prefixed IDs (OD:, AOD:, OR:)
ALTER TABLE payment_adjustments MODIFY ref_id VARCHAR(255) NOT NULL;

-- Note: This is required for the Payment Adjustment module's multi-source selection
-- (Fabric Shipments, Domestic Dispatches, Sales Orders, and Agent Dispatches).
