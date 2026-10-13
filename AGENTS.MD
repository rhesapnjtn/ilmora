# 🎓 ILMORA — Panduan Pengembangan & Standar AI Coding Agent

> **Tujuan:** Menjaga kualitas kode, keamanan, konsistensi arsitektur, integritas data, dan pengalaman pengguna dalam pengembangan Ilmora.

Ilmora adalah platform pembelajaran digital berbasis kursus online dan langganan yang dirancang untuk memberikan pengalaman belajar yang modern, aman, responsif, dan mudah dikembangkan.

Dokumen ini merupakan pedoman utama bagi seluruh developer, kontributor, dan AI coding agent yang bekerja pada proyek Ilmora.

Setiap perubahan wajib mengikuti standar arsitektur, keamanan, kualitas kode, pengujian, dan aturan bisnis yang ditetapkan dalam dokumen ini.

## 📌 Daftar Isi

1. Standar Penulisan Kode
2. Alur Kerja Git
3. Alur Kerja Pengembangan
4. Panduan Pengujian
5. Praktik Keamanan
6. Konvensi Proyek
7. Konvensi Basis Data
8. Konfigurasi Lingkungan
9. Perangkat Pemeriksaan Kualitas Kode
10. Daftar Periksa Deployment
11. Perintah yang Berguna
12. Ketentuan Khusus Proyek Ilmora

---

# 1. Standar Penulisan Kode

Seluruh kode yang dibuat atau dimodifikasi harus konsisten, mudah dibaca, dapat diuji, aman, dan mudah dipelihara.

## 1.1 PHP dan Laravel

* Ikuti standar **PSR-12** untuk penulisan kode PHP.
* Gunakan **Laravel Pint** untuk memformat kode secara konsisten.
* Gunakan `declare(strict_types=1);` pada file PHP baru jika sesuai dengan pola proyek.
* Gunakan fitur Laravel berdasarkan tanggung jawabnya, seperti Form Request, Policy, Eloquent, Middleware, dan Service.
* Terapkan prinsip *Single Responsibility*: setiap kelas harus memiliki tanggung jawab yang jelas.
* Hindari kode duplikat, fungsi terlalu panjang, dan logika bisnis yang tersebar tanpa alasan.
* Utamakan dependency injection untuk mengelola ketergantungan.
* Jangan menggunakan `env()` secara langsung di luar file konfigurasi. Gunakan `config()` di dalam kode aplikasi.
* Jangan menambahkan dependensi baru tanpa alasan yang jelas dan pemeriksaan kompatibilitas.
* Ikuti versi PHP dan Laravel yang benar-benar digunakan proyek.

Contoh:

```php
<?php

declare(strict_types=1);

namespace App\Services;

class CourseService
{
    public function isAvailable(bool $published): bool
    {
        return $published;
    }
}
```

## 1.2 Vue.js dan TypeScript

* Gunakan Vue 3 Composition API.
* Utamakan `<script setup lang="ts">` untuk komponen Vue baru.
* Gunakan TypeScript untuk props, state, fungsi, dan data yang memiliki struktur jelas.
* Pisahkan komponen berdasarkan tanggung jawabnya.
* Gunakan `computed()` untuk nilai turunan dan `ref()` atau `reactive()` untuk state reaktif.
* Gunakan composables untuk logika yang digunakan kembali.
* Hindari komponen besar yang mencampurkan tampilan, validasi, dan logika bisnis kompleks.
* Pastikan setiap interaksi memiliki status loading, error, dan keberhasilan jika diperlukan.
* Hindari penggunaan `any` tanpa alasan yang dapat dipertanggungjawabkan.

Contoh:

```vue
<script setup lang="ts">
import { computed } from 'vue'

interface Course {
    id: number
    title: string
    price: number
}

const props = defineProps<{
    course: Course
}>()

const formattedPrice = computed(() =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(props.course.price),
)
</script>

<template>
    <article>
        <h2>{{ props.course.title }}</h2>
        <p>{{ formattedPrice }}</p>
    </article>
</template>
```

## 1.3 Standar UI/UX

* Gunakan Tailwind CSS sebagai pendekatan utama untuk styling.
* Jangan menambahkan framework CSS lain tanpa alasan yang jelas.
* Gunakan komponen bersama untuk elemen UI yang berulang.
* Pastikan antarmuka responsif pada desktop, tablet, dan ponsel.
* Gunakan label, hierarki informasi, kontras warna, dan navigasi yang jelas.
* Pertahankan konsistensi warna, tipografi, jarak, radius, dan ukuran komponen.
* Utamakan aksesibilitas, termasuk navigasi keyboard dan label yang sesuai.
* Sediakan umpan balik visual untuk interaksi pengguna.
* Hindari tombol yang terlihat aktif tetapi tidak menjalankan fungsi.
* Gunakan loading state, empty state, error state, dan success state sesuai kebutuhan.

### Identitas visual Ilmora

Arah desain Ilmora adalah **Clear & Friendly**, dengan kombinasi warna utama kuning dan ungu yang modern, ramah, serta mudah dikenali.

* Gunakan token warna terpusat agar warna konsisten.
* Gunakan kuning untuk aksen, sorotan, atau elemen yang perlu menarik perhatian.
* Gunakan ungu untuk identitas merek dan tindakan utama sesuai sistem desain yang ditetapkan.
* Pastikan teks tetap terbaca di atas setiap warna latar.
* Jangan menggunakan warna aksen secara berlebihan.
* Pertahankan desain yang konsisten di landing page, katalog kursus, dashboard, halaman langganan, dan ruang belajar.
* Jangan mengubah identitas visual secara menyeluruh tanpa kebutuhan atau arahan yang jelas.

## 1.4 Standar Pengujian

* Gunakan Pest untuk pengujian PHP jika sudah tersedia atau ditetapkan sebagai standar proyek.
* Gunakan Feature Test untuk memeriksa perilaku aplikasi dari sudut pandang pengguna.
* Gunakan Unit Test untuk menguji logika yang dapat dipisahkan secara mandiri.
* Uji perubahan yang memengaruhi autentikasi, otorisasi, kursus, langganan, pembayaran, dan akses materi.
* Jangan menghapus atau melemahkan pengujian hanya agar CI berhasil.
* Tambahkan pengujian regresi untuk bug yang diperbaiki.

---

# 2. Alur Kerja Git

Setiap perubahan harus memiliki tujuan yang jelas, dapat ditinjau, dan mudah dilacak.

## 2.1 Penamaan Branch

| Jenis perubahan | Format      | Contoh                      |
| --------------- | ----------- | --------------------------- |
| Fitur baru      | `feat/`     | `feat/course-catalog`       |
| Perbaikan bug   | `fix/`      | `fix/subscription-expiry`   |
| Dokumentasi     | `docs/`     | `docs/update-installation`  |
| Refaktor        | `refactor/` | `refactor/course-service`   |
| Pengujian       | `test/`     | `test/payment-webhook`      |
| Pemeliharaan    | `chore/`    | `chore/update-dependencies` |
| Performa        | `perf/`     | `perf/course-query`         |

Hindari nama branch yang terlalu umum, seperti `update`, `changes`, atau `new-feature`.

## 2.2 Conventional Commits

Gunakan format:

```text
<type>(<scope>): <deskripsi singkat>
```

Contoh:

```text
feat(courses): add course catalog
fix(auth): correct login validation
test(subscription): cover expired subscriptions
docs(readme): update installation guide
refactor(payments): separate payment processing logic
chore(deps): update compatible dependencies
```

Ketentuan:

* Gunakan deskripsi yang jelas dan ringkas.
* Satu commit sebaiknya mewakili satu perubahan logis.
* Jangan memasukkan rahasia, kredensial, atau data pribadi.
* Hindari commit yang mencampurkan fitur tidak berkaitan.
* Jangan menggunakan pesan seperti `fix`, `update`, atau `final` tanpa penjelasan.

## 2.3 Pull Request

Setiap Pull Request (PR) harus:

* Menjelaskan tujuan dan ruang lingkup perubahan.
* Menyebutkan fitur atau bug yang terdampak.
* Menyertakan pengujian yang relevan.
* Lulus seluruh pemeriksaan wajib CI.
* Tidak memperkenalkan rahasia atau data sensitif.
* Menjelaskan perubahan konfigurasi, migrasi, dan dependensi jika ada.
* Menyertakan tangkapan layar jika perubahan UI memerlukannya.

**Jangan menggabungkan PR apabila pemeriksaan wajib gagal atau terdapat masalah keamanan yang belum diselesaikan.**

---

# 3. Alur Kerja Pengembangan

## 3.1 Persiapan Lingkungan Lokal

Ilmora dikembangkan menggunakan Laragon pada Windows dengan Laravel, Vue 3, Inertia.js, Tailwind CSS, dan MySQL sesuai konfigurasi proyek.

Sebelum memulai:

1. Pastikan PHP, Composer, Node.js, npm, dan MySQL tersedia.
2. Buka direktori proyek.
3. Periksa struktur proyek dan file konfigurasi.
4. Instal dependensi sesuai file lock yang tersedia.
5. Siapkan konfigurasi `.env`.
6. Pastikan database lokal sudah dibuat.
7. Jalankan migrasi jika diperlukan.
8. Periksa kondisi Git sebelum mengubah kode.

Contoh:

```powershell
cd C:\laragon\www\ilmora

composer install
npm install
```

Jika `.env` belum tersedia:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

**Jangan menimpa `.env` yang sudah digunakan tanpa memeriksa konfigurasi di dalamnya.**

Contoh konfigurasi lokal:

```dotenv
APP_NAME=Ilmora
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ilmora
DB_USERNAME=root
DB_PASSWORD=
```

Konfigurasi tersebut hanya contoh. Sesuaikan nama database, port, username, dan password dengan lingkungan lokal yang sebenarnya.

Jalankan migrasi jika database telah disiapkan:

```powershell
php artisan migrate
```

## 3.2 Menjalankan Aplikasi

Jalankan Laravel dan Vite di terminal terpisah.

**Terminal 1 — Laravel**

```powershell
php artisan serve
```

**Terminal 2 — Vite**

```powershell
npm run dev
```

Gunakan alamat lokal yang ditampilkan Laravel untuk membuka aplikasi.

Jika proyek memerlukan queue worker atau scheduler, jalankan layanan tersebut sesuai konfigurasi yang digunakan.

## 3.3 Pemeriksaan Sebelum Commit

Jalankan pemeriksaan yang telah dikonfigurasi:

```powershell
composer validate
vendor/bin/pint --test
php artisan test
npm run build
```

Jika PHPStan, ESLint, atau Prettier sudah dikonfigurasi, jalankan juga skrip yang tersedia pada `composer.json` atau `package.json`.

AI coding agent wajib:

1. Memeriksa struktur dan pola kode yang sudah ada.
2. Memahami penyebab masalah sebelum melakukan perubahan.
3. Membuat perubahan sekecil mungkin untuk memenuhi kebutuhan.
4. Menambahkan atau memperbarui pengujian.
5. Menjalankan pemeriksaan yang relevan.
6. Memeriksa hasil perintah, bukan hanya status proses.
7. Melaporkan pemeriksaan yang berhasil, gagal, atau belum dijalankan.

Jangan pernah mengklaim pengujian berhasil jika perintah belum dijalankan atau hasilnya belum diperiksa.

---

# 4. Panduan Pengujian

Pengujian merupakan bagian wajib dari pengembangan.

## 4.1 Struktur Pengujian

Ikuti struktur yang sudah digunakan proyek. Struktur Laravel yang umum:

```text
tests/
├── Feature/
│   ├── Auth/
│   ├── Courses/
│   ├── Subscriptions/
│   ├── Payments/
│   └── Admin/
└── Unit/
```

Jangan memindahkan seluruh pengujian hanya untuk mengikuti contoh tersebut jika proyek telah memiliki konvensi yang konsisten.

## 4.2 Area Prioritas Pengujian

### A. Autentikasi

* Login dengan kredensial valid dan tidak valid.
* Registrasi jika tersedia.
* Logout.
* Validasi formulir.
* Pemulihan dan penggantian kata sandi.
* Pembatasan akses bagi pengguna yang belum login.
* Hak akses berdasarkan peran pengguna.

### B. Kursus

* Menampilkan katalog dan detail kursus.
* Hanya menampilkan kursus yang dipublikasikan sesuai aturan bisnis.
* Membatasi akses terhadap kursus yang belum dipublikasikan.
* Memastikan akses materi sesuai hak pengguna.
* Membuat, memperbarui, menerbitkan, dan menghapus kursus sesuai otorisasi.
* Mencegah pengguna mengubah kursus milik pihak lain tanpa izin.

### C. Langganan

* Membuat langganan.
* Mengaktifkan langganan setelah pembayaran terverifikasi.
* Menentukan masa berlaku langganan.
* Menangani langganan kedaluwarsa.
* Menangani pembatalan dan perpanjangan jika didukung.
* Mencegah akses premium tanpa langganan yang memenuhi syarat.
* Mencegah perubahan status langganan tanpa otorisasi.

### D. Pembayaran

* Pembuatan transaksi.
* Validasi nominal dan mata uang.
* Penanganan pembayaran berhasil dan gagal.
* Verifikasi callback atau webhook.
* Pencegahan pemrosesan transaksi yang sama secara berulang.
* Penanganan status transaksi yang tidak valid.
* Sinkronisasi status pembayaran dan langganan.

Jangan menggunakan transaksi pembayaran sungguhan untuk pengujian otomatis. Gunakan mock, fake, sandbox, atau lingkungan pengujian yang sesuai.

### E. Panel Admin

* Akses halaman admin.
* Validasi hak akses untuk setiap tindakan.
* Pengelolaan pengguna, kursus, dan langganan.
* Validasi input.
* Fungsi tombol tambah, edit, hapus, simpan, dan tindakan lain.
* Penanganan kegagalan permintaan.
* Konsistensi data setelah tindakan dilakukan.

## 4.3 Contoh Pengujian Pest

```php
<?php

use App\Models\User;

it('redirects guests to login when accessing a protected page', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

it('allows an authenticated user to access the dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/dashboard');

    $response->assertOk();
});
```

Sesuaikan route, middleware, dan sistem autentikasi dengan implementasi aktual proyek.

## 4.4 Aturan Kelulusan

* Jangan menghapus pengujian gagal tanpa memahami penyebabnya.
* Jangan menggunakan assertion yang terlalu longgar agar tes terlihat berhasil.
* Tambahkan pengujian regresi untuk bug yang diperbaiki.
* Jangan menggunakan data produksi dalam pengujian.
* Pastikan pengujian dapat dijalankan secara konsisten di lingkungan CI.
* Investigasi seluruh kegagalan yang relevan sebelum tugas dinyatakan selesai.

---

# 5. Praktik Keamanan

Keamanan wajib diterapkan pada backend, frontend, database, integrasi eksternal, dan proses deployment.

## 5.1 Perlindungan Data dan Rahasia

* Jangan pernah melakukan commit terhadap `.env`, token akses, kata sandi, private key, atau kredensial lainnya.
* Pastikan file rahasia tercantum dalam `.gitignore`.
* Gunakan `.env.example` hanya untuk nama variabel dan contoh nilai yang tidak bersifat rahasia.
* Jangan menampilkan rahasia dalam log, respons API, pesan error, tangkapan layar, atau dokumentasi.
* Jangan mengekspos data pribadi pengguna tanpa alasan dan otorisasi yang sah.
* Jangan menyalin data produksi ke lingkungan lokal tanpa prosedur perlindungan yang sesuai.

Jika rahasia terlanjur masuk ke Git, lakukan rotasi kredensial dan tangani riwayat repository sesuai prosedur. Menghapus file dari commit terbaru saja belum tentu menghilangkan rahasia dari riwayat Git.

## 5.2 Validasi dan Otorisasi

* Gunakan Form Request untuk validasi input yang kompleks atau digunakan berulang kali.
* Gunakan Policy atau mekanisme otorisasi Laravel yang sesuai.
* Lakukan validasi dan otorisasi di server, bukan hanya di frontend.
* Jangan menganggap tombol yang disembunyikan berarti akses sudah aman.
* Terapkan prinsip hak akses minimum.
* Hindari mass assignment yang tidak terkontrol.
* Gunakan proteksi CSRF pada alur yang membutuhkannya.
* Cegah SQL injection dengan Eloquent atau query binding.
* Validasi ukuran, tipe, dan izin akses file yang diunggah.

## 5.3 Pembayaran dan Webhook

* Verifikasi tanda tangan, token, atau mekanisme autentikasi webhook sesuai dokumentasi penyedia.
* Jangan mengaktifkan langganan hanya berdasarkan redirect browser atau parameter frontend.
* Cocokkan identitas transaksi, nominal, mata uang, dan status pembayaran.
* Terapkan idempotensi agar notifikasi berulang tidak menyebabkan perubahan ganda.
* Simpan kredensial penyedia pembayaran dengan aman.
* Jangan mencatat data pembayaran sensitif dalam log.
* Gunakan sandbox untuk pengembangan dan pengujian.

## 5.4 Transport dan Penanganan Error

* Gunakan HTTPS di produksi.
* Pastikan `APP_DEBUG=false` di produksi.
* Jangan mengembalikan stack trace atau detail internal kepada pengguna.
* Gunakan pesan error yang informatif tanpa membocorkan informasi sensitif.
* Terapkan rate limiting pada endpoint yang rentan, seperti login dan pemulihan kata sandi.
* Jangan menonaktifkan middleware keamanan tanpa memahami konsekuensinya.

---

# 6. Konvensi Proyek

Gunakan struktur proyek yang sudah ada. Contoh berikut merupakan referensi, bukan kewajiban untuk membuat seluruh direktori sejak awal.

## 6.1 Backend Laravel

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Policies/
├── Providers/
├── Services/
└── Jobs/
```

Pedoman:

* **Controllers:** menangani permintaan HTTP dan mengoordinasikan alur aplikasi.
* **Form Requests:** menangani validasi dan, jika sesuai, otorisasi permintaan.
* **Models:** merepresentasikan entitas dan relasi database.
* **Policies:** menentukan izin terhadap resource.
* **Services:** menangani logika bisnis kompleks atau yang digunakan bersama.
* **Jobs:** menangani pekerjaan yang diproses melalui queue.

Jangan membuat Service, Repository, atau abstraksi tambahan hanya karena terlihat lebih canggih. Gunakan abstraksi jika memang memberikan manfaat yang jelas.

## 6.2 Frontend Vue dan Inertia.js

Contoh struktur:

```text
resources/js/
├── Components/
├── Composables/
├── Layouts/
├── Pages/
├── Types/
└── app.ts
```

Pedoman:

* **Pages:** halaman yang dirender melalui Inertia.js.
* **Components:** komponen UI yang dapat digunakan kembali.
* **Layouts:** kerangka tampilan yang konsisten.
* **Composables:** logika Vue yang dapat digunakan kembali.
* **Types:** tipe TypeScript bersama jika diperlukan.

Gunakan penamaan file dan folder yang konsisten dengan konvensi proyek.

## 6.3 Routing dan Controller

* Kelompokkan route berdasarkan tanggung jawab.
* Gunakan middleware untuk autentikasi dan pembatasan akses.
* Gunakan named routes jika sesuai dengan pola proyek.
* Hindari menempatkan seluruh logika bisnis di file route.
* Gunakan resource controller untuk CRUD jika sesuai.
* Pastikan endpoint yang mengubah data menggunakan metode HTTP dan proteksi yang tepat.

## 6.4 Prinsip Perubahan Kode

Sebelum mengubah struktur atau menambahkan dependensi:

1. Periksa implementasi yang sudah ada.
2. Periksa versi Laravel, Vue, Inertia.js, dan paket terkait.
3. Ikuti pola yang sudah digunakan jika masih layak.
4. Hindari refaktor besar di luar ruang lingkup tugas.
5. Dokumentasikan perubahan arsitektur yang signifikan.

---

# 7. Konvensi Basis Data

Database Ilmora harus memiliki struktur konsisten, relasi yang benar, dan perlindungan terhadap inkonsistensi data.

## 7.1 Penamaan

Ikuti konvensi umum Laravel:

* Nama tabel menggunakan bentuk jamak dan `snake_case`.
* Nama kolom menggunakan `snake_case`.
* Primary key menggunakan `id` jika sesuai dengan konvensi proyek.
* Foreign key menggunakan pola `<nama_entitas_singular>_id`.
* Nama model menggunakan bentuk singular dalam `PascalCase`.
* Nama migration menjelaskan perubahan yang dilakukan.

Contoh tabel:

```text
users
courses
course_lessons
subscriptions
payments
```

Contoh kolom:

```text
course_id
user_id
starts_at
expires_at
created_at
updated_at
```

## 7.2 Migration

* Gunakan migration Laravel untuk setiap perubahan struktur database.
* Jangan mengandalkan perubahan manual pada database lokal sebagai satu-satunya sumber perubahan.
* Pastikan migration dapat dijalankan di lingkungan baru.
* Gunakan tipe data yang sesuai.
* Tentukan foreign key dan aturan penghapusan dengan mempertimbangkan integritas data.
* Jangan menghapus kolom atau tabel yang masih digunakan tanpa analisis dampak.
* Untuk perubahan berisiko, pertimbangkan migrasi bertahap yang kompatibel dengan versi aplikasi sebelumnya.

## 7.3 Indeks dan Integritas Data

* Tambahkan indeks pada kolom yang sering digunakan untuk pencarian, filter, pengurutan, atau relasi jika diperlukan.
* Gunakan unique constraint untuk nilai yang memang harus unik.
* Gunakan transaksi database untuk operasi yang harus berhasil atau gagal sebagai satu kesatuan.
* Hindari query berulang yang menyebabkan masalah N+1.
* Gunakan eager loading jika sesuai.
* Periksa performa query sebelum menambahkan optimasi yang tidak diperlukan.

## 7.4 Langganan dan Pembayaran

* Gunakan tipe data uang yang sesuai, seperti `DECIMAL`, bukan floating point.
* Tentukan mata uang secara eksplisit jika sistem mendukungnya.
* Simpan identitas transaksi penyedia pembayaran sesuai kebutuhan integrasi.
* Terapkan aturan unik dan idempotensi agar transaksi tidak diproses berulang.
* Definisikan status pembayaran dan langganan secara konsisten.
* Jangan mengandalkan harga atau status langganan yang dikirim browser sebagai sumber kebenaran.

## 7.5 Pengelolaan Data

* Gunakan factory dan seeder untuk data pengujian atau data awal yang aman.
* Jangan memasukkan data pribadi nyata ke dalam seeder.
* Jangan menjalankan `migrate:fresh` atau perintah penghapusan data pada database penting tanpa izin dan verifikasi eksplisit.
* Jangan mengubah data produksi melalui skrip pengujian.

---

# 8. Konfigurasi Lingkungan

Konfigurasi harus dipisahkan antara lingkungan lokal, pengujian, dan produksi.

## 8.1 Variabel Lingkungan

Contoh kebutuhan umum:

```dotenv
APP_NAME=Ilmora
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ilmora
DB_USERNAME=root
DB_PASSWORD=
```

Variabel tambahan dapat dibutuhkan untuk:

* Email dan notifikasi.
* Cache dan session.
* Queue.
* Penyimpanan file.
* Integrasi pembayaran.
* Layanan eksternal.
* Logging dan pemantauan error.

Gunakan nama variabel sesuai konfigurasi Laravel dan integrasi yang benar-benar digunakan proyek.

## 8.2 Aturan Konfigurasi

* Jangan menyimpan kredensial produksi di repository.
* Jangan menyalin nilai rahasia dari produksi ke `.env.example`.
* Jangan menggunakan `APP_DEBUG=true` di produksi.
* Gunakan `APP_ENV=production` di lingkungan produksi.
* Pastikan `APP_KEY` tersedia dan benar.
* Gunakan kredensial database dengan hak akses minimum.
* Simpan secret produksi melalui mekanisme pengelolaan rahasia yang sesuai.
* Jangan mengubah konfigurasi deployment hanya untuk menyesuaikan lingkungan lokal tanpa mempertimbangkan dampaknya.

---

# 9. Perangkat Pemeriksaan Kualitas Kode

Gunakan perangkat yang telah dikonfigurasi di proyek. Jangan mengasumsikan semua alat tersedia sebelum memeriksa dependensi.

| Perangkat         | Tujuan                                       |
| ----------------- | -------------------------------------------- |
| Laravel Pint      | Memformat kode PHP                           |
| Pest              | Menjalankan pengujian PHP                    |
| PHPStan           | Menganalisis tipe dan potensi kesalahan PHP  |
| ESLint            | Memeriksa kualitas JavaScript dan TypeScript |
| Prettier          | Memformat kode frontend                      |
| Composer Validate | Memvalidasi `composer.json`                  |
| Vite Build        | Memastikan aset frontend dapat dibangun      |
| GitHub Actions    | Mengotomatisasi pemeriksaan CI               |

## 9.1 Perintah Pemeriksaan

```powershell
composer validate
vendor/bin/pint --test
php artisan test
npm run build
```

Untuk PHPStan jika tersedia:

```powershell
vendor/bin/phpstan analyse
```

Untuk melihat skrip frontend:

```powershell
npm run
```

Jalankan skrip yang sesuai dengan konfigurasi `package.json`.

## 9.2 Aturan Penambahan Tools

* Periksa apakah alat sudah tersedia.
* Gunakan versi yang kompatibel dengan PHP, Laravel, Node.js, dan dependensi proyek.
* Hindari duplikasi fungsi antaralat.
* Jangan menambahkan alat baru tanpa manfaat yang jelas.
* Jika alat wajib belum dikonfigurasi, laporkan dan usulkan konfigurasi yang sesuai sebelum menganggap pemeriksaannya terpenuhi.

## 9.3 Integrasi Berkelanjutan

CI sebaiknya menjalankan pemeriksaan format, analisis statis, pengujian otomatis, dan build frontend sesuai kebutuhan proyek.

PR tidak boleh dinyatakan siap digabungkan apabila pemeriksaan wajib gagal.

---

# 10. Daftar Periksa Deployment

Deployment hanya dapat dilakukan setelah aplikasi diperiksa untuk lingkungan produksi.

## 10.1 Konfigurasi Produksi

* [ ] `APP_ENV=production`.
* [ ] `APP_DEBUG=false`.
* [ ] `APP_KEY` tersedia dan benar.
* [ ] HTTPS telah dikonfigurasi.
* [ ] Kredensial database dan layanan eksternal telah diamankan.
* [ ] CORS, session, cookie, dan trusted proxy diperiksa jika relevan.
* [ ] Hak akses file dan direktori sudah sesuai.
* [ ] Tidak ada `.env` atau rahasia yang dipublikasikan.
* [ ] Logging dan penanganan error telah diperiksa.

## 10.2 Database dan Aplikasi

* [ ] Backup tersedia sebelum migrasi berisiko.
* [ ] Migration telah diperiksa dan diuji.
* [ ] Tidak ada perubahan skema yang merusak kompatibilitas tanpa rencana migrasi.
* [ ] Pengujian wajib telah lulus.
* [ ] Build frontend berhasil.
* [ ] Dependensi produksi telah disiapkan sesuai kebutuhan.

## 10.3 Cache dan Optimasi

Perintah berikut dapat digunakan jika sesuai dengan prosedur deployment:

```powershell
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Pastikan konfigurasi aplikasi dan route kompatibel dengan caching sebelum menjalankannya.

Instalasi dependensi produksi:

```powershell
composer install --no-dev --prefer-dist --optimize-autoloader
```

Build frontend:

```powershell
npm ci
npm run build
```

Sesuaikan perintah dengan pipeline deployment. Jangan menghapus dependensi pengembangan sebelum proses build selesai jika build masih membutuhkannya.

## 10.4 Queue dan Scheduler

Jika proyek menggunakan queue:

* Pastikan worker berjalan sebagai proses persisten.
* Siapkan mekanisme restart worker setelah deployment.
* Pantau kegagalan job.
* Periksa percobaan ulang dan penanganan kegagalan.

Jika proyek menggunakan Laravel Scheduler:

* Pastikan jadwal telah didefinisikan.
* Jalankan scheduler sesuai konfigurasi server.
* Uji tugas yang berkaitan dengan kedaluwarsa langganan.
* Pastikan proses berulang tidak menyebabkan pemrosesan ganda.

## 10.5 Verifikasi Setelah Deployment

* [ ] Aplikasi dapat diakses.
* [ ] Login dan logout berjalan.
* [ ] Halaman kursus dapat ditampilkan.
* [ ] Hak akses kursus sesuai dengan status langganan.
* [ ] Pembayaran dan webhook berfungsi sesuai lingkungan.
* [ ] Tidak terdapat error kritis pada log.
* [ ] Mekanisme pemulihan atau rollback tersedia untuk perubahan berisiko.

Jangan menyatakan deployment berhasil hanya karena halaman utama dapat dibuka. Verifikasi alur penting sesuai ruang lingkup rilis.

---

# 11. Perintah yang Berguna

## 11.1 Laravel Artisan

```powershell
php artisan serve
php artisan route:list
php artisan migrate
php artisan migrate:status
php artisan make:model Course -m
php artisan make:controller CourseController
php artisan make:request StoreCourseRequest
php artisan make:policy CoursePolicy --model=Course
php artisan test
php artisan optimize:clear
```

Gunakan perintah generator hanya jika sesuai dengan struktur proyek dan kebutuhan implementasi.

## 11.2 Composer

```powershell
composer install
composer validate
```

Utamakan `composer install` untuk memasang dependensi sesuai `composer.lock`.

Jangan menjalankan `composer update` secara rutin tanpa alasan karena perintah tersebut dapat mengubah versi dependensi yang terkunci.

## 11.3 Frontend

```powershell
npm install
npm run dev
npm run build
npm run
```

Gunakan `npm ci` ketika proyek memiliki `package-lock.json` dan memerlukan instalasi yang konsisten, terutama di CI.

## 11.4 Git

```powershell
git status
git branch
git switch -c feat/course-catalog
git add .
git commit -m "feat(courses): add course catalog"
git log --oneline -10
```

Sebelum menjalankan `git add .`, periksa status repository dan pastikan tidak ada file sensitif yang akan ikut di-commit.

## 11.5 Catatan Keselamatan

AI coding agent tidak boleh menjalankan perintah yang menghapus data, mereset repository, mengubah riwayat Git bersama, atau memengaruhi lingkungan produksi tanpa memeriksa dampak dan memperoleh persetujuan yang diperlukan.

---

# 12. Ketentuan Khusus Proyek Ilmora

Bagian ini mendefinisikan prinsip bisnis yang harus dipertahankan selama pengembangan Ilmora.

## 12.1 Autentikasi dan Akun Pengguna

* Halaman dan tindakan yang membutuhkan akun harus dilindungi autentikasi.
* Pengguna yang belum login harus diarahkan ke proses login saat mengakses halaman terlindungi.
* Pengguna tidak boleh melihat atau mengubah data pengguna lain tanpa otorisasi.
* Validasi harus tetap dilakukan di server meskipun frontend sudah melakukan validasi.
* Reset kata sandi harus menggunakan mekanisme yang aman.
* Jangan mengasumsikan peran atau status akun hanya berdasarkan data dari frontend.
* Gunakan paket autentikasi yang sudah dipilih proyek.
* Jangan mengganti atau menambahkan paket autentikasi tanpa mengevaluasi dampaknya terhadap route, session, dan akun pengguna.

## 12.2 Pengelolaan Kursus dan Materi

* Kursus harus memiliki status yang jelas, seperti `draft` atau `published`, sesuai kebutuhan bisnis.
* Hanya pengguna berwenang yang boleh membuat, mengubah, menerbitkan, atau menghapus kursus.
* Materi terbatas tidak boleh dapat diakses hanya dengan mengetahui URL langsung.
* Hak akses materi harus diverifikasi di server.
* URL unduhan, file, dan endpoint materi tidak boleh membocorkan konten berbayar.
* Validasi file sebelum menyimpannya.
* Jangan mengekspos direktori penyimpanan internal atau kredensial akses file.
* Jika menggunakan URL bertanda tangan atau tautan sementara, pastikan masa berlaku dan izin aksesnya sesuai.

## 12.3 Langganan dan Hak Akses

* Tentukan status langganan secara konsisten.
* Bedakan status pembayaran dari status langganan.
* Akses kursus berbayar hanya diberikan jika pengguna memenuhi aturan akses sistem.
* Validasi tanggal mulai dan berakhir langganan di server.
* Tangani status kedaluwarsa secara konsisten.
* Jangan mempercayai status premium yang hanya tersimpan di frontend.
* Pastikan perpanjangan, pembatalan, dan perubahan paket mengikuti aturan bisnis yang ditetapkan.

Jangan mengasumsikan semua langganan diperpanjang otomatis atau semua pembayaran langsung mengaktifkan akses. Ikuti desain bisnis dan integrasi yang benar-benar digunakan.

## 12.4 Integrasi Pembayaran

* Gunakan adapter atau service yang jelas jika logika pembayaran kompleks.
* Simpan konfigurasi sensitif di environment.
* Verifikasi notifikasi pembayaran sebelum memperbarui status transaksi.
* Terapkan idempotensi pada pemrosesan pembayaran.
* Catat perubahan status transaksi secara terkontrol untuk kebutuhan audit.
* Gunakan sandbox selama pengembangan.
* Jangan menganggap pembayaran berhasil hanya karena pengguna kembali ke halaman sukses.
* Jangan mengubah integrasi yang ada tanpa memahami kontrak API dan alur transaksi.

Jika penyedia pembayaran belum dipilih, jangan mengarang endpoint, format webhook, atau mekanisme verifikasi.

## 12.5 Inertia.js dan Navigasi

* Ikuti pola routing Laravel dan Inertia.js yang sudah digunakan.
* Gunakan route server sebagai sumber kebenaran untuk halaman terlindungi.
* Kirim hanya data yang diperlukan melalui props Inertia.
* Jangan mengirim password, token privat, secret, atau atribut internal sensitif ke props Inertia.
* Gunakan validasi dan penanganan error yang konsisten.
* Pastikan navigasi, formulir, dan tindakan pengguna memberikan umpan balik yang jelas.
* Jangan menjadikan otorisasi frontend sebagai pengganti pemeriksaan server.

## 12.6 Queue dan Penjadwalan

Jika proyek menggunakan queue atau scheduler untuk notifikasi, pembayaran, atau langganan:

* Pastikan job aman ketika dijalankan ulang.
* Gunakan idempotensi untuk proses yang memengaruhi pembayaran atau hak akses.
* Tangani kegagalan job secara eksplisit.
* Jangan menganggap job berhasil hanya karena sudah masuk antrean.
* Pastikan jadwal kedaluwarsa langganan sesuai aturan bisnis.
* Hindari tugas terjadwal yang menghapus data penting tanpa kebijakan retensi yang jelas.

## 12.7 Privasi dan Perlindungan Data

* Terapkan prinsip minimisasi data: kumpulkan dan tampilkan hanya data yang diperlukan.
* Jangan mengekspos informasi pribadi dalam log, respons API, dokumentasi, atau pesan error.
* Jangan menampilkan data pengguna lain di halaman kursus, profil, admin, atau laporan tanpa izin.
* Lindungi data pembayaran dan identitas akun.
* Gunakan data dummy atau sintetis untuk pengujian dan dokumentasi.
* Pastikan endpoint ekspor, unduhan, dan laporan memiliki pemeriksaan otorisasi.
* Jangan mengirim data pribadi ke layanan eksternal tanpa dasar dan konfigurasi yang sesuai.

## 12.8 Pengalaman Pengguna

* Gunakan antarmuka yang konsisten dan responsif.
* Sediakan indikator loading ketika proses membutuhkan waktu.
* Berikan pesan error yang dapat dipahami.
* Berikan konfirmasi untuk tindakan berisiko atau sulit dibatalkan.
* Pastikan setiap tombol menjalankan aksi yang dijanjikan.
* Hindari tombol dekoratif yang terlihat aktif tetapi tidak berfungsi.
* Pastikan formulir memiliki validasi yang jelas.
* Jangan menampilkan data contoh seolah-olah merupakan data produksi nyata.
* Pertahankan identitas visual Ilmora yang **Clear & Friendly**, dengan kuning dan ungu sebagai warna utama sesuai token desain yang berlaku.

## 12.9 Aturan Wajib untuk AI Coding Agent

Setiap AI coding agent yang bekerja pada Ilmora wajib:

1. Membaca `AGENTS.md` sebelum melakukan perubahan.
2. Memeriksa struktur proyek, konfigurasi, dependensi, dan implementasi yang sudah ada.
3. Memahami kebutuhan pengguna sebelum menentukan pendekatan teknis.
4. Menghindari perubahan yang tidak berkaitan dengan tugas.
5. Tidak mengarang API, model, route, tabel, atau fungsi yang belum diverifikasi.
6. Tidak menghapus fitur atau mengubah perilaku yang sudah ada tanpa alasan yang jelas.
7. Tidak melemahkan keamanan atau pengujian untuk menyelesaikan masalah dengan cepat.
8. Menambahkan atau memperbarui pengujian yang relevan.
9. Menjalankan pemeriksaan yang dapat dilakukan di lingkungan yang tersedia.
10. Melaporkan file yang diubah, alasan perubahan, hasil pengujian, dan pekerjaan yang belum selesai.
11. Meminta konfirmasi sebelum tindakan destruktif, migrasi berisiko, perubahan arsitektur besar, atau operasi terhadap data penting.
12. Menjaga kerahasiaan data pengguna, kredensial, token, dan informasi internal proyek.
13. Tidak mengklaim suatu fitur berfungsi jika belum diverifikasi.
14. Mengutamakan perubahan yang terukur dan dapat ditinjau daripada refaktor besar tanpa kebutuhan.

## 12.10 Definisi Selesai (*Definition of Done*)

Sebuah tugas hanya dapat dinyatakan selesai jika:

* [ ] Kebutuhan utama telah dipenuhi.
* [ ] Implementasi mengikuti pola dan standar proyek.
* [ ] Validasi dan otorisasi diterapkan sesuai kebutuhan.
* [ ] Tidak ada rahasia atau data pribadi yang terekspos.
* [ ] Pengujian relevan telah dijalankan.
* [ ] Pemeriksaan kualitas kode yang diwajibkan telah dilakukan.
* [ ] Perubahan tidak merusak alur penting yang sudah tersedia.
* [ ] Dokumentasi diperbarui jika diperlukan.
* [ ] Hasil pengujian dan keterbatasannya dilaporkan dengan jujur.
* [ ] Tidak ada masalah kritis yang sengaja diabaikan.

---

# Prinsip Akhir

Keamanan, kebenaran logika bisnis, integritas data, dan kualitas pengalaman pengguna lebih penting daripada menyelesaikan tugas dengan cepat.

Setiap perubahan pada Ilmora harus dapat dijelaskan, diuji, dipelihara, dan disesuaikan dengan kebutuhan proyek yang sebenarnya.

Jika instruksi tugas bertentangan dengan praktik keamanan atau integritas data, jangan menerapkan perubahan berisiko secara diam-diam. Jelaskan konflik tersebut dan ajukan pendekatan yang lebih aman.

**Dokumen ini merupakan pedoman utama pengembangan Ilmora.** Jika konfigurasi proyek aktual berbeda dari contoh di atas, periksa implementasi terlebih dahulu dan sesuaikan perubahan tanpa mengorbankan keamanan, kualitas kode, atau integritas data.
