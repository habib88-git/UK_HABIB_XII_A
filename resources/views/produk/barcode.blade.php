<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Barcode {{ $produk->nama_produk }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 10px;
        }
        .label {
            border: 1px dashed #000;
            padding: 8px;
            border-radius: 6px;
            display: inline-block;
        }
        h4 {
            margin: 4px 0;
            font-size: 12px;
        }
        .barcode {
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="label">
        <h4>{{ $produk->nama_produk }}</h4>
        <div class="barcode">
            {!! DNS1D::getBarcodeHTML($produk->barcode, 'EAN13', 1.5, 40) !!}
        </div>
        <small>{{ $produk->barcode }}</small>
    </div>
</body>
</html>
