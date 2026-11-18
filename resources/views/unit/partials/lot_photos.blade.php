{{--
    Photos sent by Stitching for a lot (worker app > Lot Photo). Styles: unit-theme.css (.lp-*).
    $lotPhotoVisible  true when this login may see the section (every stage except Cutting and Printing);
                      the section then always shows, with "No image" when the lot has no photo
    $lotPhotos        collection of App\Models\LotPhoto
    $myUnitId         the logged-in unit: it gets a Delete button on the photos it sent, and only on those
    $lotPhotoTitle    optional heading
--}}
@if(!empty($lotPhotoVisible))
    @php $lotPhotoCount = !empty($lotPhotos) ? $lotPhotos->count() : 0; @endphp
    <div class="xl-head">
        <h3><i class="fas fa-images"></i> {{ $lotPhotoTitle ?? 'Stitching Photos' }}</h3>
        @if($lotPhotoCount > 0)
            <span class="xl-sub">{{ $lotPhotoCount }} photo(s) · tap to view full screen</span>
        @endif
    </div>
    <div class="xl-panel">
        @if($lotPhotoCount > 0)
            <div class="lp-grid {{ $lotPhotoCount === 1 ? 'lp-one' : '' }}">
                @foreach($lotPhotos as $photo)
                    <figure class="lp-item">
                        <img src="{{ $photo->url }}" alt="Lot photo" loading="lazy" data-photo-view
                             data-photo-title="{{ ($photo->unit->name ?? 'Stitching') . ($photo->created_at ? ' · ' . $photo->created_at->format('d M y, h:i A') : '') }}">
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
        @else
            <div class="xl-noimg"><i class="fas fa-image"></i> No image</div>
        @endif
    </div>
@endif
