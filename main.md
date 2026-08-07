# Spesifikasi Proyek: Sistem Restoran (Laravel + API Mobile Sync + POS)

## 1. Ringkasan Proyek
Aplikasi web berbasis Laravel untuk 1 restoran (single outlet) yang berfungsi sebagai:
- Backend + Admin panel untuk pemilik/staff restoran
- Point of Sale (POS) untuk kasir — **online biasa, tidak perlu offline** (kasir & dapur berada di lokasi restoran dengan koneksi internet stabil)
- REST API terdokumentasi untuk disinkronkan dengan mobile app customer, **mobile app wajib bisa dipakai saat offline dan sync tanpa bentrok saat online kembali**

Mobile app customer punya 2 fitur utama: **reservasi meja** dan **order & bayar online** (delivery/pickup).

## 2. Tech Stack yang Direkomendasikan
- **Backend**: Laravel (gunakan versi stabil terbaru yang tersedia saat development dimulai — jangan hardcode versi, cek `laravel new` akan otomatis pakai versi terbaru)
- **Auth**: Laravel Sanctum (token-based, cocok untuk API mobile & POS)
- **Admin/POS/Dapur Panel**: **Laravel Blade** (bukan Filament) dikombinasikan dengan **Tailwind CSS + Alpine.js** (atau Livewire kalau butuh interaktivitas tanpa reload halaman) supaya tampilan modern/kekinian tapi tetap ringan dan gampang dikustomisasi sesuai brand restoran
- **Dokumentasi API**: **Scribe** (auto-generate OpenAPI spec dari route Laravel) supaya tim mobile bisa langsung pakai
- **Database**: **MySQL**
- **POS/Kasir**: web biasa via Blade, **selalu online** (real-time langsung ke server, tidak perlu PWA/offline)
- **Mobile App Customer**: native/cross-platform (Flutter atau React Native) dengan local storage (SQLite via sqflite/WatermelonDB) untuk kapabilitas offline — lihat bagian 6 untuk strategi sync

## 3. Modul Utama
1. Manajemen Menu & Kategori (CRUD, harga, foto, status aktif/nonaktif, stok)
2. Manajemen Meja & Reservasi (nomor meja, kapasitas, status, jadwal booking)
3. Order & Pembayaran — kasir yang input order dan **menentukan tipe order** (dine-in, delivery, atau pickup). **Kalau tipe order = dine-in, kasir wajib pilih meja** dari daftar `restaurant_tables` yang berstatus "kosong" (dropdown/grid pemilihan meja di layar POS, meja otomatis berubah status jadi "terisi" setelah dipilih, dan balik "kosong" setelah order selesai/meja dikosongkan)
4. POS Kasir (web biasa, selalu online, real-time ke server) — titik masuk order pertama kali
5. **Kitchen Display / Panel Dapur** — role baru, menerima order yang sudah diinput kasir secara real-time, lalu update progress masak (lihat alur di bagian 6a)
6. **Laporan Transaksi** — role-based:
   - **Admin**: akses penuh ke semua laporan (penjualan harian/mingguan/bulanan, per kasir, per menu terlaris, laporan meja/reservasi)
   - **Kasir**: hanya bisa lihat riwayat & rekap transaksi yang dia input sendiri (per shift), tidak bisa lihat laporan kasir lain atau data keuangan agregat restoran
   - **Dapur**: tidak ada akses ke laporan transaksi sama sekali
7. Admin Dashboard (kelola user/role, manajemen menu & meja — laporan sudah dipisah di poin 6)
8. API untuk Mobile App (auth, browse menu, reservasi, order, tracking status, riwayat transaksi)

## 4. Garis Besar Skema Database
- `users` — role: **admin, kasir, dapur, customer**
- `restaurant_tables` — nomor meja, kapasitas, status (kosong/terisi/direservasi)
- `reservations` — table_id, customer_id, waktu, jumlah orang, status
- `menu_categories`, `menu_items` — harga, stok, foto, status aktif
- `orders` — channel (pos/mobile), **table_id (wajib diisi kalau order_type = dine-in, diambil dari meja berstatus "kosong")**, order_type (dine-in/delivery/pickup — ditentukan kasir saat input), order_status (lihat state machine di bagian 6a), created_by (kasir yang input)
- `order_items` — order_id, menu_item_id, qty, harga saat order, **item_status** (opsional: kalau mau granular per-item, misal 1 order ada item yang beda progress masaknya)
- `payments` — order_id, metode, status, referensi transaksi
- `mobile_sync_logs` — user_id, device_id, idempotency_key, action_type (order/reservation), payload aksi offline, status (pending/synced/conflict), synced_at (untuk lacak aksi yang dibuat mobile app saat offline)

> Catatan role `dapur`: user dapur **tidak perlu** akses ke data pembayaran, harga, atau laporan penjualan — cukup lihat daftar order & item yang perlu dimasak, dan tombol untuk update status. Batasi lewat middleware/policy Laravel.

## 5. Kebutuhan API (untuk sync mobile)
- Versioning: `/api/v1/...`
- Auth: Sanctum token (register/login customer, refresh token)
- Endpoint utama:
  - `GET /menu` — daftar menu & kategori
  - `POST /reservations`, `GET /reservations/{id}` — buat & cek reservasi
  - `POST /orders`, `GET /orders/{id}` — buat order & tracking status
  - `POST /payments/callback` — callback dari payment gateway
- Dokumentasi API di-generate otomatis (Scribe) dan diekspos sebagai OpenAPI/Postman collection agar tim mobile bisa langsung integrasi tanpa tanya-tanya manual.

## 5a. Alur Kerja: Kasir → Dapur (Order Status Flow)

Kasir adalah titik input pertama. Setelah order masuk, dapur memantau dan mengupdate progress-nya. Alur status yang disarankan:

```
[Kasir input order] 
   → status: "baru" (masuk ke panel dapur real-time)
[Dapur mulai masak]
   → status: "diproses"
[Dapur selesai masak]
   → status: "siap"
[Kasir/kurir serah terima — sesuai order_type]
   → status: "delivered" (kalau delivery) atau "diambil" (pickup) atau "disajikan" (dine-in)
   → status akhir: "selesai"
```

Poin penting:
- **Kasir** yang menentukan `order_type` (dine-in / delivery / pickup) saat input order — bukan dapur atau customer via mobile untuk order dari POS.
- **Dapur** hanya berwenang mengubah status masak (baru → diproses → siap), tidak bisa mengubah data order, harga, atau tipe pengiriman.
- Panel dapur idealnya real-time (pakai Laravel Echo/WebSocket atau polling interval pendek) supaya order baru langsung muncul tanpa refresh manual.
- Kalau order dari mobile app (customer) juga masuk, alurnya sama — order otomatis muncul di panel dapur, kasir tetap bisa lihat & sesuaikan tipe delivery-nya kalau perlu.

## 6. Bagian Paling Berisiko: POS Offline-First

**Masalah inti**: kasir harus tetap bisa transaksi walau internet mati, lalu data harus tersinkron rapi ke Laravel begitu online — tanpa duplikat, tanpa bentrok stok.

**Pendekatan A — PWA (Progressive Web App)** ✅ *Direkomendasikan untuk skala 1 outlet*
- Kasir pakai browser/tablet biasa, transaksi offline disimpan di IndexedDB
- Service worker sync otomatis ke API begitu koneksi kembali
- Kelebihan: murah, gampang deploy (tinggal buka URL), tidak perlu install app
- Risiko: kapasitas storage browser terbatas, bisa ke-reset kalau cache di-clear manual

**Pendekatan B — Aplikasi Desktop Lokal (Electron/Tauri + SQLite)**
- Kasir install aplikasi khusus, semua transaksi tersimpan di SQLite lokal
- Sync queue dengan idempotency key ke Laravel API
- Kelebihan: jauh lebih robust untuk offline jangka panjang, data lebih aman
- Risiko: butuh effort deploy & update aplikasi ke tiap perangkat kasir

**Hal yang wajib diperhatikan di kedua pendekatan:**
- Setiap transaksi offline harus punya `idempotency_key` unik agar tidak double-insert saat sync
- Stok menu perlu strategi "optimistic update" — dikurangi lokal dulu, divalidasi ulang saat sync
- Perlu log konflik (misal: menu yang sama terjual 2x dari device berbeda saat offline) untuk direview admin

## 7. Kebutuhan Non-Fungsional
- Idempotency untuk semua transaksi POS (hindari duplikasi saat sync)
- Audit log setiap transaksi kasir (siapa, kapan, device mana)
- Role-based access control: admin vs kasir vs customer
- Backup & recovery untuk data POS offline yang belum sempat sync

## 8. Rekomendasi Fase Pengembangan
1. Setup Laravel + skema database + autentikasi (Sanctum)
2. Admin panel + manajemen menu & meja
3. API mobile: reservasi + order online + dokumentasi API
4. POS module versi online (belum offline)
5. Tambah kapabilitas offline POS + mekanisme sync
6. Testing menyeluruh, finalisasi dokumentasi API, deployment

---

## 9. Prompt Siap Pakai (copy-paste ke AI coding assistant)

```
Buatkan aplikasi web restoran menggunakan Laravel (versi stabil terbaru) dengan spesifikasi berikut:

KONTEKS: Single outlet restaurant (1 restoran, tidak multi-cabang).

ROLE USER: admin, kasir, dapur, customer.

TECH STACK: Laravel + Blade (bukan Filament) dengan Tailwind CSS + Alpine.js/Livewire
untuk UI admin/POS/dapur yang modern dan ringan. Database MySQL. Dokumentasi API pakai Scribe.

MODUL YANG DIBUTUHKAN:
1. Admin panel (Blade + Tailwind, modern UI) untuk kelola menu, meja, user/role.
2. POS kasir yang terintegrasi di aplikasi Laravel yang sama — titik input order pertama.
   Kasir menentukan order_type (dine-in/delivery/pickup) saat input. KALAU dine-in, kasir
   WAJIB pilih meja dari grid/dropdown meja yang statusnya "kosong" — meja otomatis berubah
   jadi "terisi" setelah dipilih, dan balik "kosong" setelah order selesai.
   POS WAJIB bisa transaksi saat offline menggunakan pendekatan PWA (service worker +
   IndexedDB), lalu sync otomatis ke server saat online kembali. Gunakan idempotency key
   di setiap transaksi untuk mencegah duplikasi saat sync.
3. Panel Dapur (Kitchen Display) — menerima order yang diinput kasir secara real-time
   (pakai Laravel Echo/WebSocket atau polling), dan dapur bisa update progress order
   dengan status: baru → diproses → siap. Dapur TIDAK boleh mengubah harga, order_type,
   meja, atau data pembayaran — hanya status masak. Batasi akses lewat role middleware/policy.
4. Laporan Transaksi dengan akses berbeda per role:
   - Admin: laporan penjualan lengkap (harian/mingguan/bulanan, per kasir, menu terlaris, meja/reservasi)
   - Kasir: hanya riwayat transaksi yang dia input sendiri (per shift)
   - Dapur: tidak ada akses laporan transaksi
5. REST API versioned (/api/v1) dengan autentikasi Laravel Sanctum untuk mobile app customer,
   mencakup: browse menu, reservasi meja, order & pembayaran (delivery/pickup), tracking status order.
6. Dokumentasi API otomatis menggunakan Scribe (generate OpenAPI spec) agar tim mobile
   bisa langsung integrasi.

SKEMA DATA UTAMA: users (role admin/kasir/dapur/customer), restaurant_tables (status:
kosong/terisi/direservasi), reservations, menu_categories, menu_items, orders (channel
pos/mobile, table_id wajib jika dine-in, order_type dine-in/delivery/pickup, order_status
baru/diproses/siap/selesai, created_by), order_items, payments,
pos_sync_logs (untuk lacak transaksi offline).

ALUR STATUS ORDER: baru (kasir input, pilih meja jika dine-in) → diproses (dapur mulai
masak) → siap (dapur selesai) → selesai/delivered (sesuai order_type).

PRIORITAS: mulai dari setup Laravel + auth + role-based access + skema database (MySQL),
lalu admin panel + manajemen meja, lalu POS kasir (termasuk pilih meja) + panel dapur
(real-time), lalu modul laporan transaksi per role, lalu API mobile + dokumentasi Scribe,
baru terakhir kapabilitas offline POS + sync mechanism.

Tolong mulai dengan migration database dan struktur folder Laravel-nya dulu.
```
