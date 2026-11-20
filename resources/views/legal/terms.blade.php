@extends('legal.layout')
@section('title', 'Syarat dan Ketentuan')

@section('content')
<h1>Syarat dan Ketentuan Shadja POS</h1>
<p class="updated">Berlaku sejak 20 November 2026</p>

<p>Syarat dan ketentuan ini mengatur penggunaan aplikasi Shadja POS yang disediakan oleh PT Coding Aja Indonesia ("kami") untuk Shadja Coffee. Dengan masuk dan menggunakan aplikasi, Anda menyetujui syarat dan ketentuan ini.</p>

<h2>Pengguna aplikasi</h2>
<p>Shadja POS adalah aplikasi kasir internal untuk staf Shadja Coffee. Akun hanya dibuat oleh admin Shadja Coffee dan tidak tersedia pendaftaran mandiri. Peran akun (admin, kasir, atau dapur) menentukan fitur yang dapat Anda akses.</p>

<h2>Akun dan keamanan</h2>
<ul>
    <li>Jaga kerahasiaan email dan kata sandi Anda, dan jangan bagikan akun kepada orang lain.</li>
    <li>Anda bertanggung jawab atas semua aktivitas yang dilakukan melalui akun Anda.</li>
    <li>Segera hubungi admin restoran jika Anda menduga akun Anda digunakan tanpa izin.</li>
    <li>Admin dapat menonaktifkan atau menghapus akun, misalnya saat staf berhenti bekerja. Akun yang dinonaktifkan langsung keluar dari semua perangkat.</li>
</ul>

<h2>Penggunaan yang diperbolehkan</h2>
<p>Aplikasi hanya boleh digunakan untuk operasional Shadja Coffee, seperti mencatat order, mengelola meja dan reservasi, mencatat pembayaran, dan mencetak struk. Anda tidak boleh:</p>
<ul>
    <li>memasukkan data palsu atau memanipulasi transaksi;</li>
    <li>mengakses fitur atau data di luar hak akses peran Anda;</li>
    <li>mengganggu, membongkar, atau mencoba membobol aplikasi maupun servernya;</li>
    <li>menyalin atau menyebarkan data pelanggan dan transaksi untuk keperluan di luar pekerjaan.</li>
</ul>

<h2>Data dan privasi</h2>
<p>Pengelolaan data dijelaskan dalam <a href="{{ route('legal.privacy') }}">Kebijakan Privasi</a>. Data transaksi yang Anda catat adalah milik Shadja Coffee dan tetap disimpan untuk pembukuan, termasuk setelah akun Anda dihapus.</p>

<h2>Perangkat dan ketersediaan layanan</h2>
<ul>
    <li>Aplikasi membutuhkan koneksi internet untuk terhubung ke server restoran. Data yang dibuat saat koneksi terputus akan dikirim saat koneksi tersedia kembali.</li>
    <li>Fitur cetak struk membutuhkan printer thermal Bluetooth atau USB yang kompatibel.</li>
    <li>Kami berusaha menjaga aplikasi tetap berjalan, tetapi tidak menjamin layanan selalu tersedia tanpa gangguan, misalnya saat pemeliharaan server.</li>
</ul>

<h2>Hak kekayaan intelektual</h2>
<p>Aplikasi Shadja POS, termasuk kode, tampilan, dan merek di dalamnya, adalah milik PT Coding Aja Indonesia atau pemberi lisensinya. Anda hanya mendapat hak terbatas untuk menggunakannya sesuai syarat dan ketentuan ini.</p>

<h2>Batasan tanggung jawab</h2>
<p>Sejauh diizinkan hukum, kami tidak bertanggung jawab atas kerugian yang timbul dari kesalahan input data oleh pengguna, penyalahgunaan akun, gangguan jaringan, atau kerusakan perangkat dan printer milik pengguna.</p>

<h2>Hukum yang berlaku</h2>
<p>Syarat dan ketentuan ini tunduk pada hukum Republik Indonesia.</p>

<h2>Perubahan syarat dan ketentuan</h2>
<p>Perubahan akan kami tampilkan di halaman ini beserta tanggal berlakunya. Dengan tetap menggunakan aplikasi setelah perubahan, Anda dianggap menyetujui syarat dan ketentuan yang baru.</p>

<h2>Kontak</h2>
<p>
    PT Coding Aja Indonesia, Denpasar, Bali, Indonesia
    @if (config('services.support.email'))
        <br>Email: <a href="mailto:{{ config('services.support.email') }}">{{ config('services.support.email') }}</a>
    @endif
</p>
@endsection
