<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A document (PDF, image, Excel...) attached to a sales order. Files live in public/uploads/order_documents. */
class OrderMainDocument extends Model
{
    protected $table = 'order_main_documents';

    protected $fillable = ['order_main_id', 'original_name', 'file_name', 'mime_type', 'size', 'uploaded_by'];

    public function getUrlAttribute(): string
    {
        return asset('uploads/order_documents/' . $this->file_name);
    }

    /** "1.2 MB" style size for display */
    public function getReadableSizeAttribute(): string
    {
        $size = (int) $this->size;
        if ($size >= 1048576) return round($size / 1048576, 1) . ' MB';
        if ($size >= 1024) return round($size / 1024) . ' KB';
        return $size . ' B';
    }
}
