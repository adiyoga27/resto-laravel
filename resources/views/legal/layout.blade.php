<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Shadja POS</title>
    <style>
        :root { color-scheme: light; }
        body { margin: 0; background: #f8fafc; color: #1e293b; font: 16px/1.7 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        main { box-sizing: border-box; max-width: 760px; margin: 0 auto; padding: 32px 16px 48px; }
        h1 { margin: 0 0 4px; font-size: 1.75rem; line-height: 1.3; color: #1e1b4b; }
        h2 { margin: 32px 0 8px; font-size: 1.15rem; color: #1e1b4b; }
        a { color: #4338ca; overflow-wrap: anywhere; }
        ul, ol { padding-left: 22px; }
        .updated { margin: 0 0 24px; color: #64748b; font-size: .9rem; }
        footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: .9rem; }
    </style>
</head>
<body>
<main>
    @yield('content')
    <footer>
        Shadja POS &middot; PT Coding Aja Indonesia, Denpasar, Bali, Indonesia<br>
        <a href="{{ route('legal.privacy') }}">Kebijakan Privasi</a> &middot;
        <a href="{{ route('legal.delete-account') }}">Hapus Akun</a>
    </footer>
</main>
</body>
</html>
