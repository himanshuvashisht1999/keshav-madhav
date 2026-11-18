{{--
    Photos sent by Stitching for a lot (worker app > Lot Photo). Styles: unit-theme.css (.lp-*).
    $lotPhotos     collection of App\Models\LotPhoto (the controller passes an empty one to Cutting and Printing)
    $myUnitId      the logged-in unit: it gets a Delete button on the photos it sent, and only on those
    $lotPhotoTitle optional heading
--}}
@if(!empty($lotPhotos) && $lotPhotos->count() > 0)
    <div class="xl-head">
        <h3><i class="fas fa-images"></i> {{ $lotPhotoTitle ?? 'Stitching Photos' }}</h3>
        <span class="xl-sub">{{ $lotPhotos->count() }} photo(s) · tap to view full screen</span>
    </div>
    <div class="xl-panel">
        <div class="lp-grid {{ $lotPhotos->count() === 1 ? 'lp-one' : '' }}">
            @foreach($lotPhotos as $photo)
                <figure class="lp-item">
                    <img src="{{ $photo->url }}" alt="Lot photo" loading="lazy" onclick="window.open(this.src, '_blank')">
                    <figcaption class="lp-meta">
                        <b>{{ $photo->unit->name ?? '-' }}</b><br>
                        {{ $photo->created_at ? $photo->created_at->format('d M y, h:i A') : '' }}
                    </figcaption>
                    @if(!empty($myUnitId) && (int) $photo->stage_master_unit_id === (int) $myUnitId)
                        <form method="POST" action="{{ route('unit.lot-photos.delete', ['id' => $photo->id]) }}">
                            @csrf
                            <button type="submit" class="xl-btn xl-btn-danger xl-btn-sm" onclick="return confirm('Delete this photo?');">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </form>
                    @endif
                </figure>
            @endforeach
        </div>
    </div>
@endif
