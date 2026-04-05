# Keshav Madhav - Project Documentation

> **Purpose**: This document provides a complete overview of the project for AI assistants in new conversations. Reference this file at the start of any new conversation.

---

## 1. Project Overview

This is a **garment manufacturing ERP system** built in **Laravel (PHP)**. It manages the full lifecycle of a garment production order — from raw fabric procurement through to final dispatch via sales agents. The system has multiple login panels (Admin, Unit Workers, Owner, Sales Agent).

- **Root Path**: `c:\xampp\htdocs\keshav-madhav`
- **Framework**: Laravel (PHP)
- **Database**: MySQL (via XAMPP)
- **Frontend**: Blade templates, AdminLTE theme, Bootstrap 4, jQuery, Select2
- **Local URL**: `http://127.0.0.1:8000`

---

## 2. User Roles & Panels

| Role | URL | Description |
|---|---|---|
| **Admin** | `/admin` | Full access. Manages orders, assigns work, digitizes slips, runs reports. |
| **Unit Person** | `/unit` | Production worker. Views assigned tasks, uploads completion slips. |
| **Owner** | `/owner` | View-only. Sees orders, lots, payments, summaries. |
| **Sales Agent** | `/agent` | Creates orders, manages shops/customers. |

---

## 3. Production Flow (Core Business Logic)

This is the most important part of the system. The flow is:

```
Admin → Assigns Cutting → Cutting Master → Makes Lots → Sends to Stitching/Printing → ... → Packing → Dispatch
```

### Step-by-step Flow:

1. **Order Created**: Admin creates an order (`OrderMain`), which has products (`OrderProduct`), which have sets (`OrderProductSet`).
2. **Cutting Assignment**: Admin divides an order into parts (sets/lots) and assigns each to a **Cutting Master** (`StageMasterUnit` for stage 3). Each assignment = one `OrderProductSet` record with a `stage_master_unit_id`.
3. **Lot Creation**: Cutting master processes the fabric and makes **Lots** (identified by `design_number` or `lot_no`). These lots travel through stages.
4. **Stage-to-Stage Transfer**: After cutting, the lot is sent to the next stage (e.g., Stitching, Printing). Each transfer creates a **Transaction** record:
   - `OrderStageTransaction` — General stage transfers (Stitching, Checking, Finishing, etc.)
   - `OrderPrintingStageTransaction` — Transfers involving the Printing stage.
   - `OrderPrintingToStichingTransaction` — Transfer from Printing back to Stitching.
5. **Slip Upload**: When a unit person completes work on a lot and sends it forward, they upload a **Production Slip** from their unit panel (`/unit/assignments`).
6. **Slip Digitization (Admin)**: Admin views uploaded slips at `/admin/uploaded-slips`. Admin selects the appropriate lot/order, adds the quantity processed, and creates the stage transaction record. If `total_sent_qty >= assigned_qty`, the task is removed from the unit's pending list; otherwise the unit can send more slips for the remaining quantity.
7. **Time Allocation (Admin)**: Admin can set an **Estimated Completion Time (ETA)** for each stage per lot at the Time Allocation module. This data is stored in `OrderStageWiseTimeTracking` (columns: `stage_id_1` through `stage_id_12`, by lot_no).
8. **Packing**: After all stages, the order is Packed (`PackingMain`, `PackingCarton`, `PackingBox`).
9. **Dispatch**: Packed orders are dispatched to customers.

### Stage IDs (from `master_product_stages` table, ordered by sequence)

| Stage ID | Stage Name | Sequence |
|---|---|---|
| 3 | Cutting | 1 |
| 2 | Embroidery | 2 |
| 1 | Printing & Embroidery | 3 |
| 4 | Stitching | 4 |
| 5 | Kaj | 5 |
| 6 | Washing | 6 |
| 7 | Thread Cutting | 7 |
| 8 | Button Revit | 8 |
| 9 | Pressing | 9 |
| 10 | Checking | 10 |
| 11 | Packing | 11 |
| 12 | Dispatch | 12 |
| 13 | Godam | 13 |

> **Notes**:
> - Stage 3 (Cutting) and Stage 11 (Packing) allow manual "Close Task" by admin (`canCloseTasks = true`).
> - Stage 12 (Dispatch) is excluded from some report filters.
> - ETA is stored in `order_stage_wise_time_tracking` column `stage_id_{id}` (e.g., `stage_id_3` for Cutting).

---

## 4. Key Database Tables & Models

### Orders
| Model | Table | Description |
|---|---|---|
| `OrderMain` | `order_mains` | Main order. Has `sku` (order number), customer, etc. |
| `OrderProduct` | `order_products` | Products within an order. |
| `OrderProductSet` | `order_products_sets` | Sets within a product. This is what gets assigned to Cutting Master. Has `design_number` (= lot no for cutting), `total_quantity`, `remain_total_quantity`, `stage_master_unit_id`, `is_closed_for_unit`. |

### Stage Transactions (Task Records)
| Model | Table | Description |
|---|---|---|
| `OrderStageTransaction` | `order_stage_transactions` | General stage transfer. Has `from_stage_id`, `to_stage_id`, `sub_stage_id` (from unit), `sub_stage_id_to` (to unit), `lot_no`, `quantity`, `remaining_quantity`, `is_closed_for_unit`. |
| `OrderPrintingStageTransaction` | `order_printing_stage_transactions` | Printing-specific transactions. Same structure as above. |
| `OrderPrintingToStichingTransaction` | `order_printing_to_stiching_transactions` | Printing → Stitching transfer. Same structure. |

### Unit Persons
| Model | Table | Description |
|---|---|---|
| `StageMasterUnit` | `stage_master_units` | A unit worker. Has `name`, `phone`, `master_stage_id` (which stage they work in), `password` (their login), `status`. |

### Time & Tracking
| Model | Table | Description |
|---|---|---|
| `OrderStageWiseTimeTracking` | `order_stage_wise_time_tracking` | ETA per lot per stage. Has `lot_no`, `stage_id_1` through `stage_id_12` (each = a datetime for estimated completion). |
| `MasterStageWiseTimeAllocation` | `master_stage_wise_time_allocations` | Default time allocation settings per stage. |

### Slip Digitization
| Model | Table | Description |
|---|---|---|
| `ProductionSlipDigitization` | `production_slip_digitization` | A digitized slip uploaded by a unit person. Links to `from_stage_id`, `to_stage_id`, `lot_no`, `stage_master_unit_id`. |
| `ProductionSlipDigitizationParts` | `production_slip_digitization_parts` | Detail lines within a digitized slip. |

### Fabric / Inventory
| Model | Table | Description |
|---|---|---|
| `Fabric` | `fabrics` | Fabric master data. |
| `FabricRollAssigning` | `fabric_roll_assignings` | Fabric rolls assigned to lots. |
| `DomesticInventory` | `domestic_inventories` | Finished goods inventory for sales agents. |

### Stages Master
| Model | Table | Description |
|---|---|---|
| `MasterProductStage` | `master_product_stages` | Stage definitions. `sequence` for ordering. |

---

## 5. Unit Login Panel (`/unit/*`)

- **Login**: Unit person logs in with their `name` (username) and `password` from `stage_master_units` table.
- **Assignments Page** (`/unit/assignments`): Shows the unit person's pending tasks.
  - For **Cutting** stage: Shows `OrderProductSet` records assigned to them.
  - For **Other stages**: Shows `OrderStageTransaction`, `OrderPrintingStageTransaction`, `OrderPrintingToStichingTransaction` records where `sub_stage_id_to` matches their ID.
  - **Close Task Button**: Available for Cutting and Packing stages. Manual close.
  - **Other stages**: Tasks auto-disappear once admin digitizes the slip AND the full quantity is sent.
- **Upload Slip**: Unit person uploads a physical slip image for completed work.

---

## 6. Admin Modules

### Orders
- `/admin/product-order` — Create/manage orders.

### Slip Digitization (Critical Process)
- `/admin/uploaded-slips` — Admin sees all slips uploaded by unit persons.
- Admin selects the slip → chooses lot/order → enters quantity processed.
- Creates `OrderStageTransaction` (or Printing/Stitching variant).
- If `total_sent >= assigned`, the task is removed from unit's pending list. Otherwise, unit can send more slips.

### Time Allocation
- Admin sets ETAs per stage per lot.
- Stored in `OrderStageWiseTimeTracking` keyed by `lot_no`.
- Column names: `stage_id_3` for Cutting ETA, `stage_id_4` for Stitching ETA, etc.

### Reports Module

**Route prefix**: `admin.report.*` (singular) for older reports, `admin.reports.*` (plural) for newer ones.

| Report | Route | Description |
|---|---|---|
| Sales Order | `admin.report.sales-order` | Sales order summary. |
| Lots Report | `admin.report.lots` | Lot-level tracking. |
| Lot Details | `admin.report.lots.lot-details` | Per-lot production progress using `getLotDetails()` helper. Shows status (Progress/Completed/Delayed/Not Started). |
| Stock Report | `admin.report.stock` | Fabric stock. |
| Unit Assignments | `admin.reports.unit-assignments` | Shows tasks assigned to unit persons. Filterable by unit, stage, lot, order, status. Shows Assigned Qty, Pending Qty, Start/End/ETA times. Status: Pending/Done/Delayed. Export to Excel available. WhatsApp icon per unit person. |
| Production | `admin.report.production` | Production progress. |

### Lot Details Report (Reference for Status Logic)

The `/admin/report/lots/lot-details/{lot_no}` view uses the `getLotDetails($lot_no, $stage_id)` helper function (in `app/Helpers/helpers.php`) to get:
- `total_quantity`, `remaining_quantity` (pending qty)
- `completed_time` (last updated_at of transactions)
- `eta` (from `OrderStageWiseTimeTracking`)

**Status logic**:
- `remaining_quantity > 0` AND `now > eta` → **Delayed**
- `remaining_quantity <= 0` AND `completed_time > eta` → **Delayed Done**
- `remaining_quantity <= 0` → **Completed**
- `remaining_quantity > 0` AND `eta` not passed → **In Progress**
- No transactions yet → **Not Started**

---

## 7. Key Services

| Service | Path | Description |
|---|---|---|
| `ReportService` | `app/Services/Admin/ReportService.php` | All report data logic. Includes `unitAssignments()`, `closeUnitAssignment()`, `reopenUnitAssignment()`. |
| `TimeAllocationService` | `app/Services/Admin/TimeAllocationService.php` | ETA calculation logic based on working hours/days. |

---

## 8. Important Route Groups

```php
// Old reports (singular)
Route::prefix('/report')->name('report.')->group(...)

// New reports (plural) — includes Unit Assignments
Route::prefix('/reports')->name('reports.')->group(...)

// Unit panel
Route::prefix('unit')->group(...)

// Owner panel
Route::prefix('owner')->group(...)

// Sales Agent
Route::prefix('agent')->group(...)
```

---

## 9. Key Business Rules

1. **Cutting/Packing** tasks can be manually closed by admin via "Close Task" button. Other stages close automatically when slip is fully digitized.
2. **is_closed_for_unit = 1** on a record means the task is considered Done.
3. **Lot No** for Cutting = `design_number` on `OrderProductSet`. For all other stages, it's `lot_no` on transaction tables.
4. **Pending Qty**: For Cutting = `remain_total_quantity` on `OrderProductSet`. For others = `remaining_quantity` on transaction tables.
5. **Assigned Qty**: For Cutting = `total_quantity` on `OrderProductSet`. For others = `quantity` on transaction tables.
6. ETA for a stage = `OrderStageWiseTimeTracking.stage_id_{stage_id}` for the given `lot_no`.
7. **Duplicate Unit Names**: Multiple `StageMasterUnit` records can have the same name (same person, multiple records). Always de-duplicate using `->unique('name')` when showing dropdowns.
8. **WhatsApp Integration**: Unit person phone number is in `StageMasterUnit.phone`. WhatsApp link = `https://wa.me/{phone}`.

---

## 10. Common Debugging References

| Issue | What to Check |
|---|---|
| Task not showing for unit person | Check `sub_stage_id_to` matches unit ID in transaction tables |
| ETA not showing | Check `order_stage_wise_time_tracking` for the lot_no |
| Wrong qty showing | Cutting uses `OrderProductSet`; other stages use transaction tables |
| Route not found | Check if route is in `report.` (singular) or `reports.` (plural) group |
| Unit not in dropdown | Check `master_stage_id` matches and `status = 1` in `stage_master_units` |
