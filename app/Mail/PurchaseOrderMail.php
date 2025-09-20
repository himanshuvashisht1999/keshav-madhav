<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data; // $data is the same object you use in your Blade view
    }

    public function build()
    {
        return $this->subject('Purchase Order #'.$this->data->sku)
                    ->view('admin.emails.purchase_order')
                    ->with(['data' => $this->data]);
    }
}
