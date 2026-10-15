<?php

namespace App\Requests\Admin;

/** Edit order: the same checks as a new order (quantities of 1 or more, order file PDF / picture only). */
class ProductOrderUpdateRequest extends ProductOrderStoreRequest
{
}
