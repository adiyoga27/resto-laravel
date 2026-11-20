@extends('legal.layout')
@section('title', 'Kebijakan Privasi')

@section('content')
<h1>Kebijakan Privasi Shadja POS</h1>
<p class="updated">Berlaku sejak 7 November 2026</p>

<p>Kebijakan ini menjelaskan bagaimana PT Coding Aja Indonesia ("kami") mengelola data pada aplikasi Shadja POS, aplikasi kasir khusus Shadja Coffee yang dipakai oleh stafnya. Akun dibuat oleh admin Shadja Coffee, bukan melalui pendaftaran mandiri.</p>

<h2>Data yang kami kumpulkan</h2>
<ul>
    <li><strong>Data akun:</strong> nama, alamat email, nomor telepon (opsional), kata sandi yang disimpan dalam bentuk hash, dan peran pengguna.</li>
    <li><strong>Data pelanggan restoran:</strong> nama, nomor telepon, alamat pengiriman, dan catatan yang dimasukkan staf saat membuat order atau reservasi.</li>
    <li><strong>Data transaksi:</strong> item pesanan, meja, diskon, total, metode pembayaran, dan nominal pembayaran. Aplikasi tidak meminta atau menyimpan nomor kartu pembayaran.</li>
    <li><strong>Data teknis:</strong> alamat IP dan jenis perangkat atau browser pada log aktivitas, serta laporan error berisi nama perangkat, sistem operasi, pesan error, dan alamat API yang gagal.</li>
</ul>

<h2>Izin perangkat</h2>
<ul>
    <li><strong>Bluetooth dan USB:</strong> untuk mencari dan terhubung ke printer struk.</li>
    <li><strong>Lokasi:</strong> hanya diminta pada Android 11 ke bawah karena sistem mewajibkannya untuk pencarian Bluetooth. Kami tidak mengumpulkan lokasi Anda.</li>
    <li><strong>Internet:</strong> untuk terhubung ke server restoran.</li>
</ul>

<h2>Cara kami menggunakan data</h2>
<ul>
    <li>Menjalankan fitur login, kasir, order, reservasi, dan cetak struk.</li>
    <li>Menyusun laporan penjualan dan pembukuan restoran.</li>
    <li>Menjaga keamanan akun serta menemukan dan memperbaiki error.</li>
</ul>
<p>Kami tidak menjual data dan tidak menampilkan iklan.</p>

<h2>Pihak ketiga</h2>
<ul>
    <li><strong>Telegram:</strong> laporan error dikirim ke channel privat tim kami. Untuk error koneksi API, hanya metode, alamat, dan status permintaan yang dikirim, tanpa header dan isi permintaan.</li>
    <li><strong>Google Fonts:</strong> aplikasi dapat mengunduh font dari Google, sehingga alamat IP perangkat dapat terlihat oleh Google.</li>
</ul>

<h2>Penyimpanan dan keamanan</h2>
<p>Data dikirim melalui koneksi HTTPS dan disimpan di server aplikasi. Token login dan data profil di perangkat disimpan pada penyimpanan aman perangkat, lalu dihapus saat Anda keluar. Data akun disimpan selama akun aktif. Data transaksi disimpan selama diperlukan untuk pembukuan dan kewajiban hukum.</p>

<h2>Hak Anda</h2>
<p>Anda dapat meminta akses, perbaikan, atau penghapusan data Anda. Untuk menghapus akun, ikuti langkah di halaman <a href="{{ route('legal.delete-account') }}">Hapus Akun</a>.</p>

<h2>Anak-anak</h2>
<p>Aplikasi ini ditujukan untuk staf restoran dan tidak ditujukan untuk anak-anak.</p>

<h2>Perubahan kebijakan</h2>
<p>Perubahan akan kami tampilkan di halaman ini beserta tanggal berlakunya.</p>

<h2>Kontak</h2>
<p>
    PT Coding Aja Indonesia, Denpasar, Bali, Indonesia
    @if (config('services.support.email'))
        <br>Email: <a href="mailto:{{ config('services.support.email') }}">{{ config('services.support.email') }}</a>
    @endif
</p>
@endsection
