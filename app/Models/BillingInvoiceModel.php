<?php

namespace App\Models;

use CodeIgniter\Model;

class BillingInvoiceModel extends Model
{
    protected $table = 'billing_invoices';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'invoice_number',
        'order_code',
        'customer_name',
        'customer_email',
        'customer_phone',
        'billing_address',
        'invoice_date',
        'due_date',
        'items_json',
        'subtotal',
        'discount',
        'tax_rate',
        'tax_amount',
        'total',
        'status',
        'notes',
    ];
}
