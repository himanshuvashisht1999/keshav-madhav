<div class="col-12 col-md-6 mb-3">
    <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-3">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-primary-soft rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px; background: rgba(0,123,255,0.1);">
                    <i class="fas fa-tshirt text-primary"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="font-weight-bold text-dark mb-0" style="font-size: 1rem;">{{ trim($row->product_name) ?: $row->design_number }}</h6>
                    <span class="badge badge-light text-muted border py-1 px-2 mt-1" style="font-size: 0.7rem; border-radius: 6px;">
                        <i class="fas fa-barcode mr-1"></i>{{ $row->design_number }}
                    </span>
                </div>
                <div class="text-right">
                    <div class="h5 font-weight-bold text-success mb-0">{{ $row->available_boxes }}</div>
                    <small class="text-muted uppercase font-weight-bold" style="font-size: 0.6rem; letter-spacing: 0.5px;">Boxes</small>
                </div>
            </div>

            <div class="row no-gutters bg-light rounded-lg p-2 mb-0">
                <div class="col-6 mb-2 pr-1">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-expand-arrows-alt text-muted mr-2" style="font-size: 0.8rem; width: 15px;"></i>
                        <div class="small">
                            <div class="text-muted font-weight-bold" style="font-size: 0.6rem; text-transform: uppercase;">Size Set</div>
                            <div class="font-weight-bold text-dark" style="font-size: 0.75rem;">{{ $row->size_set_name }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-2 pl-1">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-palette text-muted mr-2" style="font-size: 0.8rem; width: 15px;"></i>
                        <div class="small">
                            <div class="text-muted font-weight-bold" style="font-size: 0.6rem; text-transform: uppercase;">Color</div>
                            <div class="font-weight-bold text-dark" style="font-size: 0.75rem;">{{ $row->color_name }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-0 pr-1">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-cut text-muted mr-2" style="font-size: 0.8rem; width: 15px;"></i>
                        <div class="small">
                            <div class="text-muted font-weight-bold" style="font-size: 0.6rem; text-transform: uppercase;">Fitting</div>
                            <div class="font-weight-bold text-dark" style="font-size: 0.75rem;">{{ $row->fitting_name ?: '-' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-0 pl-1">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-stamp text-muted mr-2" style="font-size: 0.8rem; width: 15px;"></i>
                        <div class="small">
                            <div class="text-muted font-weight-bold" style="font-size: 0.6rem; text-transform: uppercase;">Pattern</div>
                            <div class="font-weight-bold text-dark" style="font-size: 0.75rem;">{{ $row->pattern_name ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
