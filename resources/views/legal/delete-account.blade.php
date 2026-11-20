@extends('legal.layout')
@section('title', 'Hapus Akun')

@section('content')
<h1>Hapus Akun Shadja POS</h1>
<p class="updated">Aplikasi Shadja POS oleh PT Coding Aja Indonesia</p>

<p>Halaman ini menjelaskan cara meminta penghapusan akun Shadja POS beserta data yang terkait.</p>

<h2>Cara meminta penghapusan</h2>
<ol>
    @if (config('services.support.email'))
        <li>Kirim email ke <a href="mailto:{{ config('services.support.email') }}?subject=Permintaan%20Hapus%20Akun%20Shadja%20POS">{{ config('services.support.email') }}</a> dari alamat email akun Anda.</li>
    @else
        <li>Hubungi admin restoran Anda dari alamat email akun Anda.</li>
    @endif
    <li>Tulis subjek <strong>Permintaan Hapus Akun Shadja POS</strong>, lalu cantumkan nama dan email akun Anda.</li>
    <li>Kami akan memverifikasi kepemilikan akun, lalu memproses permintaan paling lambat 30 hari sejak email diterima.</li>
</ol>

<h2>Data yang dihapus</h2>
<ul>
    <li>Nama, email, nomor telepon, dan kata sandi akun.</li>
</ul>

<h2>Data yang tetap disimpan</h2>
<ul>
    <li>Catatan order, reservasi, pembayaran, kas, dan stok milik restoran, untuk pembukuan dan kewajiban hukum. Catatan ini tidak lagi terhubung dengan akun Anda.</li>
    <li>Log aktivitas keamanan, tanpa keterkaitan dengan akun Anda.</li>
</ul>

<p>Baca juga <a href="{{ route('legal.privacy') }}">Kebijakan Privasi</a> kami.</p>
@endsection
