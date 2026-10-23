<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Documento')</title>
    <style>
        @page { size: A4; margin: 18mm 16mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #182336; margin: 0; }
        .header { padding-bottom: 18px; border-bottom: 2px solid #253a55; margin-bottom: 25px; }
        .eyebrow { text-transform: uppercase; font-size: 10px; letter-spacing: 2px; color: #64748b; }
        h1 { font-size: 24px; margin: 6px 0 0; font-weight: 700; }
        h2 { font-size: 14px; margin: 24px 0 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; text-align: left; }
        th { width: 37%; color: #52627a; font-weight: 600; }
        td { overflow-wrap: anywhere; }
        .muted { font-size: 11px; color: #64748b; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 10px; }
    </style>
</head>
<body>
    <header class="header">
        <div class="eyebrow">Base · Documento interno</div>
        <h1>@yield('title', 'Documento')</h1>
    </header>
    <main>@yield('content')</main>
    <footer class="footer">Documento generado desde Base. Esta copia refleja la información disponible al momento de su generación.</footer>
</body>
</html>
