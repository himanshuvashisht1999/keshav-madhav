<?php

namespace App\Services\Admin;

use App\Models\OrderMainDocument;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Multiple documents per sales order (create + edit).
 * Files are saved in public/uploads/order_documents; one row per order in order_main_documents.
 */
class OrderDocumentService
{
    public const DIR = 'uploads/order_documents';

    /** Validation rules for the "documents[]" field (max 10 MB per file). */
    public const RULES = [
        'documents' => 'nullable|array|max:20',
        'documents.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,webp,gif,doc,docx,xls,xlsx,csv,txt',
        'remove_documents' => 'nullable|array',
        'remove_documents.*' => 'integer',
    ];

    public const MESSAGES = [
        'documents.*.max' => 'Each document must be 10 MB or smaller.',
        'documents.*.mimes' => 'Documents must be PDF, image, Word, Excel, CSV or text files.',
        'documents.max' => 'You can upload at most 20 documents at a time.',
    ];

    /** Creates the table the first time it is needed, so no manual database step is required. */
    public static function ensureTable(): void
    {
        if (Schema::hasTable('order_main_documents')) {
            return;
        }
        Schema::create('order_main_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_main_id')->index();
            $table->string('original_name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    /** Documents of one order, newest first. */
    public function forOrder($orderMainId)
    {
        self::ensureTable();
        return OrderMainDocument::where('order_main_id', $orderMainId)->orderByDesc('id')->get();
    }

    /**
     * Save the uploaded files once and attach them to every given order
     * (the domestic form can create several orders in one go).
     */
    public function attachUploads(Request $request, array $orderMainIds): int
    {
        $files = array_filter((array) $request->file('documents', []));
        $orderMainIds = array_values(array_filter($orderMainIds));
        if (!$files || !$orderMainIds) {
            return 0;
        }

        self::ensureTable();
        $dir = public_path(self::DIR);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $by = optional(Auth::guard('admin')->user())->name;
        $saved = 0;
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            $original = $file->getClientOriginalName();
            $mime = $file->getClientMimeType();
            $size = $file->getSize();
            $ext = strtolower($file->getClientOriginalExtension());
            $fileName = 'order-doc-' . date('Ymd-His') . '-' . Str::random(10) . ($ext ? '.' . $ext : '');
            $file->move($dir, $fileName);

            foreach ($orderMainIds as $orderMainId) {
                OrderMainDocument::create([
                    'order_main_id' => $orderMainId,
                    'original_name' => mb_substr($original, 0, 250),
                    'file_name' => $fileName,
                    'mime_type' => $mime,
                    'size' => $size,
                    'uploaded_by' => $by,
                ]);
            }
            $saved++;
        }
        return $saved;
    }

    /** Remove the ticked documents of one order (the file is deleted when no other order uses it). */
    public function remove($orderMainId, array $documentIds): int
    {
        if (!$documentIds) {
            return 0;
        }
        self::ensureTable();
        $docs = OrderMainDocument::where('order_main_id', $orderMainId)->whereIn('id', $documentIds)->get();
        foreach ($docs as $doc) {
            $doc->delete();
            if (!OrderMainDocument::where('file_name', $doc->file_name)->exists()) {
                @unlink(public_path(self::DIR . '/' . $doc->file_name));
            }
        }
        return $docs->count();
    }
}
