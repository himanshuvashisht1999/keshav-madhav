<?php
$mismatched_orders = DB::select("
    SELECT o.id, o.status, COUNT(i.id) as total_items, SUM(IF(i.dispatched_at IS NOT NULL, 1, 0)) as dispatched_items
    FROM agent_orders o
    JOIN agent_order_items i ON o.id = i.agent_order_id
    WHERE o.status IN ('pending', 'delayed')
    GROUP BY o.id, o.status
    HAVING total_items > 0 AND total_items = dispatched_items
");
echo json_encode($mismatched_orders);
