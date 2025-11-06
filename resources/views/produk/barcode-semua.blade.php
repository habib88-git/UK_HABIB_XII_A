<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Semua Barcode Produk</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            text-align: center;
            margin: 10px;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .label {
            width: 31%;
            border: 1px dashed #000;
            border-radius: 8px;
            padding: 8px;
            margin: 5px;
            text-align: center;
            justify-content: center;
        }

        .label h4 {
            font-size: 12px;
            margin: 0 0 5px 0;
        }

        .barcode {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 8px;
        }

        small {
            font-size: 10px;
        }
    </style>
</head>
<body>
    <h3 style="margin-bottom: 10px;">Daftar Barcode Produk</h3>
    <div class="grid">
        @foreach ($produks as $produk)
            <div class="label">
                <h4>{{ $produk->nama_produk }}</h4>
                <div class="barcode">
                    {!! DNS1D::getBarcodeHTML($produk->barcode, 'EAN13', 1.2, 40) !!}
                </div>
                <small>{{ $produk->barcode }}</small>
            </div>
        @endforeach
    </div>
</body>
</html>
