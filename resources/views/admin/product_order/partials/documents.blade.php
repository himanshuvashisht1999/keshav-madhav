{{-- Sales order documents: add several files (create + edit), remove saved ones on edit.
     Usage:
       @include('admin.product_order.partials.documents', ['documents' => $documents ?? collect()])           full card
       @include('admin.product_order.partials.documents', ['documents' => ..., 'inline' => true])             inside a form row --}}
@php
    $documents = $documents ?? collect();
    $inline = $inline ?? false;
@endphp

@if($inline)
<div class="order-docs order-docs-inline">
    <label class="erp-label">Other Documents (Optional, multiple)</label>
@else
<div class="erp-card mb-2 order-docs">
    <div class="erp-card-header py-1 d-flex justify-content-between align-items-center">
        <span class="erp-card-title"><i class="fas fa-paperclip"></i> Documents (Optional, multiple)</span>
        <span class="small text-muted">PDF, image, Word, Excel, CSV or text &middot; up to 10 MB each</span>
    </div>
    <div class="erp-card-body p-2">
@endif

        @if($errors->has('documents') || $errors->has('documents.*'))
            <div class="alert alert-danger py-1 px-2 mb-2 small">
                {{ $errors->first('documents') ?: collect($errors->get('documents.*'))->flatten()->first() }}
            </div>
        @endif

        @if($documents->isNotEmpty())
            <div class="small font-weight-bold text-muted mb-1">Saved documents ({{ $documents->count() }})</div>
            <ul class="order-docs-list mb-2">
                @foreach($documents as $doc)
                    <li title="{{ $doc->original_name }} · {{ $doc->readable_size }}{{ $doc->created_at ? ' · ' . $doc->created_at->format('d M Y') : '' }}{{ $doc->uploaded_by ? ' · ' . $doc->uploaded_by : '' }}">
                        <i class="fas {{ str_contains((string) $doc->mime_type, 'pdf') ? 'fa-file-pdf text-danger' : (str_starts_with((string) $doc->mime_type, 'image/') ? 'fa-file-image text-primary' : 'fa-file-alt text-secondary') }}"></i>
                        <a href="{{ $doc->url }}" target="_blank" class="order-docs-name">{{ $doc->original_name }}</a>
                        <span class="order-docs-size">{{ $doc->readable_size }}</span>
                        <label class="order-docs-remove mb-0" title="Remove this document when the order is saved">
                            <input type="checkbox" name="remove_documents[]" value="{{ $doc->id }}"> Remove
                        </label>
                    </li>
                @endforeach
            </ul>
        @endif

        <input type="file" name="documents[]" id="orderDocsInput" multiple class="d-none"
               accept=".pdf,.jpg,.jpeg,.png,.webp,.gif,.doc,.docx,.xls,.xlsx,.csv,.txt">
        <input type="file" id="orderDocsPicker" multiple class="d-none"
               accept=".pdf,.jpg,.jpeg,.png,.webp,.gif,.doc,.docx,.xls,.xlsx,.csv,.txt">
        <div class="order-docs-bar">
            <button type="button" class="btn-erp btn-erp-outline order-docs-add" id="orderDocsAdd">
                <i class="fas fa-paperclip mr-1"></i> Add documents
            </button>
            @if($inline)
                <span class="order-docs-hint">PDF, image, Word, Excel &middot; max 10 MB each</span>
            @endif
        </div>
        <ul class="order-docs-list order-docs-new mt-1 mb-0" id="orderDocsNew"></ul>

@if($inline)
</div>
@else
    </div>
</div>
@endif

{{-- tag styles: .order-docs-* in public/admin_assets/style.css (shared with the order view page) --}}

<script>
    (function () {
        var input = document.getElementById('orderDocsInput');
        var picker = document.getElementById('orderDocsPicker');
        var list = document.getElementById('orderDocsNew');
        var addBtn = document.getElementById('orderDocsAdd');
        if (!input || !picker || !list || !addBtn || typeof DataTransfer === 'undefined') { return; }

        var MAX = 10 * 1024 * 1024;
        var allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt'];
        var chosen = new DataTransfer();

        function sizeText(b) { return b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB'; }

        function render() {
            input.files = chosen.files;
            list.innerHTML = '';
            Array.prototype.forEach.call(chosen.files, function (f, i) {
                var li = document.createElement('li');
                var ext = (f.name.split('.').pop() || '').toLowerCase();
                var icon = ext === 'pdf' ? 'fa-file-pdf text-danger'
                    : (['jpg', 'jpeg', 'png', 'webp', 'gif'].indexOf(ext) !== -1 ? 'fa-file-image text-primary'
                    : (['xls', 'xlsx', 'csv'].indexOf(ext) !== -1 ? 'fa-file-excel text-success'
                    : (['doc', 'docx'].indexOf(ext) !== -1 ? 'fa-file-word text-primary' : 'fa-file-alt text-secondary')));
                li.title = f.name + ' (will be saved with the order)';
                li.innerHTML = '<i class="fas ' + icon + '"></i>' +
                    '<span class="order-docs-name"></span>' +
                    '<span class="order-docs-size">' + sizeText(f.size) + '</span>' +
                    '<button type="button" class="order-docs-drop" title="Remove (do not upload)"><i class="fas fa-times"></i></button>';
                li.querySelector('.order-docs-name').textContent = f.name;
                li.querySelector('.order-docs-drop').addEventListener('click', function () {
                    var next = new DataTransfer();
                    Array.prototype.forEach.call(chosen.files, function (g, j) { if (j !== i) next.items.add(g); });
                    chosen = next;
                    render();
                });
                list.appendChild(li);
            });
        }

        addBtn.addEventListener('click', function () { picker.click(); });
        picker.addEventListener('change', function () {
            var skipped = [];
            Array.prototype.forEach.call(picker.files, function (f) {
                var ext = (f.name.split('.').pop() || '').toLowerCase();
                if (allowed.indexOf(ext) === -1) { skipped.push(f.name + ' (file type not allowed)'); return; }
                if (f.size > MAX) { skipped.push(f.name + ' (larger than 10 MB)'); return; }
                chosen.items.add(f);
            });
            picker.value = '';
            render();
            if (skipped.length) { alert('Not added:\n' + skipped.join('\n')); }
        });
    })();
</script>
