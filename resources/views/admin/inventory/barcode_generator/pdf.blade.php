<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { margin: 0; }
body { 
    margin: 0; 
    padding: 0;
    font-size: 0; /* ERADICATE WHITESPACE TEXT-NODES */
    line-height: 0;
    background: #fff;
}
table.row-table {
    width: 283.46pt;
    height: 255.12pt; /* EXACTLY one page */
    max-height: 255.12pt;
    table-layout: fixed;
    border-collapse: collapse;
}
td.label-box {
    font-family: DejaVu Sans, sans-serif; 
    width: 141.73pt;
    vertical-align: top;
    text-align: center;
    padding-top: 51pt; /* ~18mm */
    padding-bottom: 42pt; /* ~15mm */
    padding-left: 6pt;
    padding-right: 6pt;
    overflow: hidden;
}
.title-line { 
    font-size: 9.5pt; 
    font-weight: 900; 
    text-transform: uppercase; 
    margin-bottom: 1mm; 
    width: 100%; 
    white-space: nowrap; 
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}
.hr { 
    border-top: 1px solid #000; 
    margin: 1mm auto; 
    width: 85%; 
}
.item-line { 
    font-size: 8pt; 
    font-weight: 700; 
    text-transform: uppercase; 
    line-height: 1.2; 
    margin-bottom: 0.8mm; 
}
.barcode-wrapper { 
    margin-top: 2mm; 
    width: 100%;
}
.barcode-img { 
    width: 40mm; 
    height: 11mm; 
    margin: 0 auto;
    display: block;
}
.barcode-text { 
    font-size: 7.5pt; 
    font-weight: 700; 
    margin-top: 0.5mm;
    line-height: 1.2;
}
</style>
</head>
<body>
@foreach($chunks as $key => $row)
<table class="row-table" cellspacing="0" cellpadding="0" style="{{ $key == count($chunks) - 1 ? 'page-break-after: auto;' : 'page-break-after: always;' }}">
    <tr>
        @foreach($row as $info)
        <td class="label-box">
            <!-- Line 1: Name of Garment -->
            <div class="title-line">{{ $info->product_name }}</div>
            <div class="hr"></div>
            <!-- Line 2: Size Set -->
            <div class="item-line">{{ $info->size_group }}</div>
            <!-- Line 3: Quantity and PCs -->
            <div class="item-line">{{ $info->no_of_pcs }} PCs</div>
            <!-- Line 4: Pattern -->
            <div class="item-line">{{ $info->pattern_name }}</div>
            <!-- Line 5: Fitting -->
            <div class="item-line">{{ $info->fitting_name }}</div>
            
            <div class="barcode-wrapper">
                <!-- Line 6: Design Number (Exactly above barcode) -->
                <div class="item-line" style="margin-bottom:1mm; font-size:10pt;"># {{ $info->design_number }}</div>
                <img class="barcode-img" src="data:image/png;base64,{{ DNS1D::getBarcodePNG($info->barcode, 'C128', 1.4, 45) }}">
                <div class="barcode-text">{{ $info->barcode }}</div>
            </div>
        </td>
        @endforeach
        <!-- If chunk has only 1 item, fill the second cell empty -->
        @if(count($row) == 1)
        <td class="label-box"></td>
        @endif
    </tr>
</table>
@endforeach
</body>
</html>