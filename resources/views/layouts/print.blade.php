<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Laporan - SpaceHub')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* A4 tanpa margin atas/samping: header & footer dibuat sendiri supaya posisinya konstan di tiap halaman */
        @page {
            size: A4;
            margin: 0 0 10mm 0;
            @bottom-right {
                content: "Halaman " counter(page) " dari " counter(pages);
                font: 8pt Inter, Arial, sans-serif;
                color: #64748b;
                padding-right: 12mm;
            }
        }
        :root { --print-head: 26mm; --print-foot: 12mm; --print-side: 12mm; }
        .print-frame > thead, .print-frame > tfoot { display: none; }
        @media print {
            html, body { background: #fff !important; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-head { position: fixed; top: 0; left: 0; right: 0; z-index: 10; box-sizing: border-box; padding: 8mm var(--print-side) 4mm; background: #fff; }
            .print-foot { position: fixed; bottom: 0; left: 0; right: 0; z-index: 10; box-sizing: border-box; height: var(--print-foot); padding: 0 var(--print-side); margin: 0; background: #fff; }
            .print-frame { width: 100%; border-collapse: collapse; }
            .print-frame > thead { display: table-header-group; }
            .print-frame > tfoot { display: table-footer-group; }
            .print-frame > thead > tr > td { height: calc(var(--print-head) + 4mm); padding: 0; }
            .print-frame > tfoot > tr > td { height: calc(var(--print-foot) + 4mm); padding: 0; }
            .print-frame > tbody > tr > td { padding: 0 var(--print-side); }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-body-md text-on-surface antialiased print:bg-white">
    <div class="px-4 py-6 print:p-0">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
