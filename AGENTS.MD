AGENTS.md
🎓 ILMORA — Panduan Pengembangan & Standar AI Coding Agent

Ilmora adalah platform pembelajaran digital berbasis kursus online dan langganan yang dirancang untuk memberikan pengalaman belajar yang modern, aman, responsif, dan mudah dikembangkan.

Dokumen ini menjadi pedoman utama bagi seluruh AI coding agent, developer, dan kontributor yang bekerja pada proyek Ilmora.

Setiap perubahan kode wajib mengikuti standar arsitektur, keamanan, kualitas, dan pengujian yang ditetapkan dalam dokumen ini.

📌 Daftar Isi
Standar Penulisan Kode
Alur Kerja Git
Alur Kerja Pengembangan
Panduan Pengujian
Praktik Keamanan
Konvensi Proyek
Konvensi Basis Data
Konfigurasi Lingkungan
Perangkat Pemeriksaan Kualitas Kode
Daftar Periksa Deployment
Perintah yang Berguna
Ketentuan Khusus Proyek Ilmora
1. Standar Penulisan Kode

Seluruh kode yang dibuat atau dimodifikasi harus konsisten, mudah dibaca, dapat diuji, dan mudah dipelihara.

1.1 Standar PHP dan Laravel
Ikuti standar PSR-12 untuk penulisan kode PHP.
Gunakan Laravel Pint untuk memformat kode secara konsisten.
Gunakan deklarasi strict_types pada file PHP baru jika sesuai dengan pola proyek.
Gunakan fitur Laravel sesuai tanggung jawab masing-masing, seperti Form Request, Policy, Eloquent, Middleware, dan Service.
Terapkan prinsip Single Responsibility: satu kelas sebaiknya memiliki tanggung jawab yang jelas.
Hindari kode duplikat, fungsi terlalu panjang, dan logika bisnis yang tersebar tanpa alasan.
Gunakan dependency injection daripada membuat ketergantungan secara langsung di banyak tempat.
Hindari penggunaan env() secara langsung di luar file konfigurasi. Gunakan config() pada kode aplikasi.
Jangan menambahkan dependensi baru tanpa alasan yang jelas dan evaluasi kompatibilitasnya.

Contoh deklarasi tipe:

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
1.2 Standar Vue.js
Gunakan Vue 3 Composition API.
Utamakan <script setup lang="ts"> untuk komponen Vue baru.
Gunakan TypeScript untuk tipe props, data, fungsi, dan respons API.
Pisahkan komponen berdasarkan tanggung jawabnya.
Gunakan computed() untuk nilai turunan dan ref() atau reactive() untuk state reaktif.
Hindari komponen besar yang mencampurkan tampilan, validasi, dan logika bisnis yang kompleks.
Gunakan composables untuk logika yang perlu digunakan kembali.
Pastikan setiap interaksi pengguna memiliki kondisi loading, error, dan hasil yang jelas jika diperlukan.

Contoh:

<script setup lang="ts">
import { computed, ref } from 'vue'

interface Course {
    id: number
    title: string
    price: number
}

const props = defineProps<{
    course: Course
}>()

const isAffordable = ref(true)

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
        <h2>{{ course.title }}</h2>
        <p>{{ formattedPrice }}</p>
    </article>
</template>
1.3 Standar Tampilan
Gunakan Tailwind CSS untuk styling sebagai pendekatan utama.
Hindari menambahkan framework CSS lain tanpa persetujuan.
Gunakan komponen bersama untuk elemen UI yang berulang.
Pastikan antarmuka responsif pada perangkat desktop, tablet, dan ponsel.
Gunakan label yang jelas, kontras warna yang memadai, dan navigasi yang mudah diakses.
Hindari penggunaan nilai warna, jarak, dan ukuran yang berbeda-beda untuk elemen dengan fungsi serupa jika token desain sudah tersedia.
Jangan mengorbankan aksesibilitas demi tampilan visual.
1.4 Standar Pengujian
Gunakan Pest untuk pengujian PHP apabila sudah tersedia atau ditetapkan sebagai standar proyek.
Gunakan pengujian Feature untuk memeriksa perilaku aplikasi dari sudut pandang pengguna.
Gunakan pengujian Unit untuk menguji logika yang dapat dipisahkan secara mandiri.
Uji perubahan yang memengaruhi autentikasi, otorisasi, langganan, pembayaran, dan akses kursus.
Jangan menghapus atau melemahkan pengujian hanya untuk membuat proses CI berhasil.
2. Alur Kerja Git

Seluruh perubahan harus memiliki tujuan yang jelas, dapat ditinjau, dan mudah dilacak.

2.1 Penamaan Branch

Gunakan format nama branch berikut:

Jenis	Format	Contoh
Fitur baru	feat/	feat/course-catalog
Perbaikan bug	fix/	fix/subscription-expiry
Dokumentasi	docs/	docs/update-installation
Refaktor	refactor/	refactor/course-service
Pengujian	test/	test/payment-webhook
Pemeliharaan	chore/	chore/update-dependencies
Performa	perf/	perf/course-query

Hindari nama branch yang terlalu umum, seperti update, changes, atau new-feature.

2.2 Conventional Commits

Gunakan format:

<type>(<scope>): <deskripsi singkat>

Contoh:

feat(courses): add course catalog
fix(auth): correct login validation
test(subscription): cover expired subscriptions
docs(readme): update installation guide
refactor(payments): separate payment processing logic
chore(deps): update compatible dependencies

Ketentuan:

Gunakan deskripsi yang jelas dan ringkas.
Satu commit sebaiknya mewakili satu perubahan yang logis.
Jangan memasukkan file rahasia, kredensial, atau data pribadi.
Hindari commit yang mencampurkan fitur tidak berkaitan.
Jangan menggunakan pesan seperti fix, update, atau final tanpa penjelasan.
2.3 Pull Request

Setiap Pull Request (PR) harus memenuhi ketentuan berikut:

Menjelaskan tujuan dan ruang lingkup perubahan.
Menyebutkan fitur atau bug yang terdampak.
Menyertakan pengujian yang relevan.
Lulus pemeriksaan kualitas kode dan CI yang diwajibkan.
Tidak memperkenalkan rahasia atau data sensitif.
Menjelaskan perubahan konfigurasi, migrasi, atau dependensi jika ada.
Menyertakan tangkapan layar apabila perubahan memengaruhi tampilan dan diperlukan untuk proses peninjauan.

Jangan menggabungkan PR apabila pemeriksaan wajib gagal atau terdapat masalah keamanan yang belum diselesaikan.

3. Alur Kerja Pengembangan
3.1 Persiapan Lingkungan Lokal

Ilmora dikembangkan secara lokal menggunakan Laragon pada Windows, dengan Laravel, Vue, Inertia.js, dan MySQL sesuai konfigurasi proyek.

Sebelum memulai:

Pastikan PHP, Composer, Node.js, npm, dan MySQL tersedia.
Buka direktori proyek.
Instal dependensi berdasarkan file lock yang tersedia.
Siapkan konfigurasi .env.
Pastikan basis data lokal sudah dibuat.
Jalankan migrasi sesuai kebutuhan.
Jalankan pemeriksaan awal sebelum mengubah kode.

Contoh perintah:

cd C:\laragon\www\ilmora

composer install
npm install

Jika .env belum tersedia, buat dari .env.example:

Copy-Item .env.example .env
php artisan key:generate

Sesuaikan konfigurasi basis data di .env dengan MySQL lokal. Jangan menimpa konfigurasi .env yang sudah digunakan tanpa memeriksa isinya.

Contoh konfigurasi lokal:

APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ilmora
DB_USERNAME=root
DB_PASSWORD=

Konfigurasi di atas hanya contoh. Sesuaikan nama database, port, username, dan password dengan lingkungan lokal yang sebenarnya.

Jalankan migrasi jika database sudah siap:

php artisan migrate
3.2 Menjalankan Aplikasi

Jalankan Laravel dan Vite pada terminal terpisah.

Terminal pertama:

php artisan serve

Terminal kedua:

npm run dev

Gunakan alamat lokal yang ditampilkan oleh Laravel untuk mengakses aplikasi.

Jika proyek memiliki kebutuhan layanan tambahan, seperti queue worker atau scheduler, jalankan sesuai konfigurasi proyek.

3.3 Pemeriksaan Sebelum Commit

Sebelum melakukan commit, jalankan pemeriksaan yang tersedia:

composer validate
vendor/bin/pint --test
php artisan test
npm run build

Jika PHPStan, ESLint, atau Prettier sudah dikonfigurasi, jalankan juga perintah yang tercantum di composer.json dan package.json.

AI coding agent harus:

Memeriksa struktur dan pola kode yang sudah ada.
Memahami penyebab masalah sebelum melakukan perubahan.
Membuat perubahan sekecil mungkin untuk menyelesaikan masalah.
Menambahkan atau memperbarui pengujian.
Menjalankan pemeriksaan yang relevan.
Melaporkan hasil pengujian secara jujur.
Menyebutkan pemeriksaan yang belum dijalankan dan alasannya.

Jangan mengklaim bahwa pengujian berhasil apabila perintahnya belum dijalankan atau hasilnya belum diperiksa.

4. Panduan Pengujian

Pengujian merupakan bagian wajib dari pengembangan, bukan langkah tambahan yang boleh diabaikan.

4.1 Struktur Pengujian

Ikuti struktur yang sudah digunakan proyek. Struktur Laravel yang umum adalah:

tests/
├── Feature/
│   ├── Auth/
│   ├── Courses/
│   ├── Subscriptions/
│   ├── Payments/
│   └── Admin/
└── Unit/

Jangan memindahkan seluruh pengujian hanya untuk mengikuti contoh struktur ini apabila proyek sudah memiliki konvensi yang konsisten.

4.2 Prioritas Pengujian
A. Autentikasi

Uji hal-hal berikut:

Login dengan kredensial valid dan tidak valid.
Registrasi jika fitur registrasi tersedia.
Logout.
Validasi formulir.
Pemulihan dan penggantian kata sandi.
Pembatasan akses bagi pengguna yang belum login.
Hak akses berdasarkan peran pengguna.
B. Kursus

Uji hal-hal berikut:

Menampilkan katalog kursus.
Melihat detail kursus.
Menampilkan kursus yang diterbitkan.
Membatasi akses terhadap kursus yang belum dipublikasikan.
Mengakses materi kursus berdasarkan hak akses pengguna.
Membuat, memperbarui, dan menghapus kursus sesuai otorisasi.
Memastikan pengguna tidak dapat mengubah kursus milik pihak lain tanpa izin.
C. Langganan

Uji hal-hal berikut:

Membuat langganan.
Mengaktifkan langganan setelah status pembayaran diverifikasi.
Menentukan masa berlaku langganan.
Menangani langganan kedaluwarsa.
Menangani pembatalan dan perpanjangan jika didukung.
Mencegah akses premium tanpa langganan yang memenuhi syarat.
Mencegah perubahan status langganan tanpa otorisasi.
D. Pembayaran

Uji hal-hal berikut:

Pembuatan transaksi.
Validasi nominal dan mata uang.
Penanganan pembayaran berhasil dan gagal.
Verifikasi callback atau webhook.
Pencegahan pemrosesan transaksi yang sama berulang kali.
Penanganan status transaksi yang tidak valid.
Sinkronisasi status pembayaran dan langganan.

Jangan menggunakan transaksi pembayaran sungguhan untuk pengujian otomatis. Gunakan mock, fake, sandbox, atau lingkungan pengujian yang sesuai.

E. Panel Admin

Uji hal-hal berikut:

Akses halaman admin.
Validasi hak akses untuk setiap tindakan.
Pengelolaan pengguna, kursus, dan langganan.
Validasi input.
Tombol tambah, edit, hapus, simpan, dan aksi lain yang tersedia.
Penanganan kegagalan ketika permintaan tidak berhasil.
Konsistensi data setelah suatu tindakan dilakukan.
4.3 Contoh Pengujian Pest
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

Contoh tersebut harus disesuaikan dengan route, middleware, dan sistem autentikasi yang benar-benar digunakan proyek.

4.4 Aturan Kelulusan
Jangan menghapus pengujian yang gagal tanpa memahami penyebabnya.
Jangan menggunakan assertion yang terlalu longgar hanya agar pengujian lulus.
Tambahkan pengujian regresi untuk bug yang diperbaiki.
Pastikan pengujian tidak bergantung pada data produksi.
Pastikan pengujian dapat dijalankan secara konsisten di lingkungan CI.
Kegagalan pengujian wajib diselidiki sebelum perubahan dinyatakan selesai.
5. Praktik Keamanan

Keamanan wajib diterapkan pada backend, frontend, basis data, integrasi eksternal, dan proses deployment.

5.1 Perlindungan Data dan Rahasia
Jangan pernah melakukan commit terhadap .env, token akses, kata sandi, private key, atau kredensial lainnya.
Pastikan file rahasia tercantum dalam .gitignore.
Gunakan .env.example hanya untuk nama variabel dan contoh nilai yang tidak bersifat rahasia.
Jangan menampilkan rahasia melalui log, respons API, pesan error, tangkapan layar, atau dokumentasi.
Jangan mengekspos data pribadi pengguna tanpa alasan dan otorisasi yang sah.
Jangan menyalin data produksi ke lingkungan lokal tanpa prosedur perlindungan yang sesuai.

Jika rahasia terlanjur masuk ke Git, hapus dari sumber yang tepat dan lakukan rotasi kredensial. Menghapus file dari commit terbaru saja belum tentu menghilangkan rahasia dari riwayat Git.

5.2 Validasi dan Otorisasi
Gunakan Form Request untuk validasi input yang kompleks atau digunakan berulang kali.
Gunakan Policy atau mekanisme otorisasi Laravel yang sesuai untuk memeriksa hak akses.
Validasi dan otorisasi harus dilakukan di server, bukan hanya di frontend.
Jangan menganggap tombol yang disembunyikan berarti akses sudah aman.
Terapkan prinsip hak akses minimum.
Hindari mass assignment yang tidak terkontrol.
Gunakan proteksi CSRF pada alur yang membutuhkannya.
Hindari SQL injection dengan memanfaatkan Eloquent atau query binding.
Tangani unggahan file dengan memvalidasi ukuran, tipe, dan izin akses.
5.3 Pembayaran dan Webhook
Verifikasi tanda tangan, token, atau mekanisme autentikasi webhook sesuai dokumentasi penyedia pembayaran.
Jangan mengaktifkan langganan hanya berdasarkan redirect browser atau parameter dari frontend.
Cocokkan identitas transaksi, nominal, mata uang, dan status pembayaran.
Pastikan pemrosesan webhook idempoten agar notifikasi berulang tidak menyebabkan perubahan ganda.
Simpan kredensial penyedia pembayaran di konfigurasi lingkungan yang aman.
Jangan mencatat data pembayaran sensitif ke log.
Gunakan sandbox untuk pengembangan dan pengujian.
5.4 Transport dan Penanganan Error
Gunakan HTTPS di lingkungan produksi.
Pastikan APP_DEBUG=false di produksi.
Jangan mengembalikan stack trace atau detail internal sistem kepada pengguna.
Gunakan pesan error yang informatif tanpa membocorkan informasi sensitif.
Terapkan rate limiting pada endpoint yang rentan terhadap penyalahgunaan, seperti login dan pemulihan kata sandi.
Jangan menonaktifkan middleware keamanan untuk mengatasi masalah sementara tanpa memahami konsekuensinya.
6. Konvensi Proyek

Gunakan struktur proyek yang sudah ada. Contoh berikut merupakan referensi organisasi, bukan kewajiban untuk membuat seluruh direktori sejak awal.

6.1 Backend Laravel
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

Pedoman:

Controllers: menangani permintaan HTTP dan mengoordinasikan alur aplikasi.
Form Requests: menangani validasi input dan, jika sesuai, otorisasi permintaan.
Models: merepresentasikan entitas dan relasi basis data.
Policies: menentukan izin terhadap resource.
Services: menampung logika bisnis yang kompleks atau digunakan bersama.
Jobs: menangani pekerjaan yang perlu diproses melalui queue.

Jangan membuat kelas Service, Repository, atau pola arsitektur tambahan hanya karena terlihat lebih canggih. Gunakan abstraksi ketika memang memberikan manfaat yang jelas.

6.2 Frontend Vue dan Inertia.js

Contoh struktur:

resources/js/
├── Components/
├── Composables/
├── Layouts/
├── Pages/
├── Types/
└── app.ts

Pedoman:

Pages: merepresentasikan halaman yang dirender melalui Inertia.js.
Components: berisi komponen UI yang dapat digunakan kembali.
Layouts: menyediakan kerangka tampilan yang konsisten.
Composables: menampung logika Vue yang dapat digunakan kembali.
Types: menyimpan tipe TypeScript bersama jika diperlukan.

Gunakan penamaan file dan folder yang konsisten dengan konvensi yang telah ada.

6.3 Routing dan Controller
Definisikan route sesuai kelompok tanggung jawabnya.
Gunakan middleware untuk kebutuhan autentikasi dan pembatasan akses.
Gunakan named routes jika sesuai dengan pola proyek.
Hindari menempatkan seluruh logika bisnis di file route.
Gunakan resource controller untuk operasi CRUD apabila memang sesuai.
Pastikan endpoint yang mengubah data menggunakan metode HTTP dan proteksi yang tepat.
6.4 Prinsip Perubahan Kode

Sebelum mengubah struktur atau menambahkan dependensi:

Periksa implementasi yang sudah ada.
Periksa versi Laravel, Vue, Inertia.js, dan paket terkait.
Ikuti pola yang sudah digunakan apabila masih layak.
Hindari refaktor besar di luar ruang lingkup tugas.
Dokumentasikan perubahan arsitektur yang signifikan.
7. Konvensi Basis Data

Basis data Ilmora harus memiliki struktur yang konsisten, relasi yang benar, dan perlindungan terhadap inkonsistensi data.

7.1 Penamaan

Gunakan konvensi umum Laravel:

Nama tabel menggunakan bentuk jamak dan snake_case.
Nama kolom menggunakan snake_case.
Primary key menggunakan id jika sesuai dengan konvensi proyek.
Foreign key mengikuti pola <nama_entitas_singular>_id.
Nama model menggunakan bentuk singular dalam PascalCase.
Nama migration menjelaskan perubahan yang dilakukan.

Contoh:

users
courses
course_lessons
subscriptions
payments

Contoh kolom:

course_id
user_id
starts_at
expires_at
created_at
updated_at
7.2 Migration
Gunakan migration Laravel untuk seluruh perubahan struktur basis data.
Jangan mengandalkan perubahan manual pada database lokal sebagai satu-satunya sumber perubahan.
Pastikan migration dapat dijalankan di lingkungan baru.
Gunakan tipe data yang sesuai dengan nilai yang disimpan.
Tentukan foreign key dan aturan penghapusan dengan mempertimbangkan integritas data.
Hindari menghapus kolom atau tabel yang masih digunakan tanpa analisis dampak.
Untuk perubahan berisiko, pertimbangkan strategi migrasi bertahap yang kompatibel dengan versi aplikasi sebelumnya.
7.3 Indeks dan Integritas Data
Tambahkan indeks pada kolom yang sering digunakan untuk pencarian, filter, pengurutan, atau relasi apabila diperlukan.
Gunakan unique constraint untuk nilai yang memang harus unik.
Gunakan transaksi database untuk operasi yang harus berhasil atau gagal sebagai satu kesatuan.
Hindari query berulang yang menimbulkan masalah N+1.
Gunakan eager loading apabila sesuai.
Periksa performa query sebelum menambahkan optimasi yang tidak diperlukan.
7.4 Data Langganan dan Pembayaran
Gunakan nilai uang dengan tipe data yang sesuai, seperti DECIMAL, bukan floating point untuk perhitungan nominal finansial.
Tentukan mata uang secara eksplisit jika sistem mendukungnya.
Simpan identitas transaksi dari penyedia pembayaran sesuai kebutuhan integrasi.
Tentukan aturan unik dan idempotensi agar satu transaksi tidak diproses berulang kali.
Definisikan status pembayaran dan langganan secara konsisten.
Jangan mengandalkan data harga atau status langganan yang dikirim langsung oleh browser sebagai sumber kebenaran.
7.5 Pengelolaan Data
Gunakan factory dan seeder untuk data pengujian atau data awal yang aman.
Jangan memasukkan data pribadi nyata ke dalam seeder.
Jangan menjalankan migrate:fresh atau perintah penghapusan data pada database yang berisi data penting tanpa izin dan verifikasi eksplisit.
Jangan mengubah data produksi melalui skrip pengujian.
8. Konfigurasi Lingkungan

Konfigurasi harus dipisahkan antara lingkungan lokal, pengujian, dan produksi.

8.1 Variabel Lingkungan

Variabel berikut merupakan contoh kebutuhan umum:

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

Variabel tambahan dapat dibutuhkan untuk:

Mail dan notifikasi.
Cache dan session.
Queue.
Penyimpanan file.
Integrasi pembayaran.
Layanan eksternal.
Logging dan pemantauan error.

Gunakan nama variabel sesuai konfigurasi Laravel dan integrasi yang benar-benar digunakan proyek.

8.2 Aturan Konfigurasi
Jangan menyimpan kredensial produksi di repository.
Jangan menyalin nilai rahasia dari lingkungan produksi ke .env.example.
Jangan menggunakan APP_DEBUG=true di produksi.
Gunakan APP_ENV=production pada lingkungan produksi.
Pastikan APP_KEY telah dikonfigurasi dengan benar.
Gunakan kredensial database dengan hak akses minimum.
Simpan secret produksi melalui mekanisme pengelolaan rahasia yang sesuai.
Jangan mengubah konfigurasi deployment hanya untuk menyesuaikan lingkungan lokal tanpa mempertimbangkan dampaknya.
9. Perangkat Pemeriksaan Kualitas Kode

Gunakan perangkat yang telah dikonfigurasi di proyek. Jangan mengasumsikan semua alat tersedia sebelum memeriksa dependensi.

Perangkat	Tujuan
Laravel Pint	Memformat kode PHP
Pest	Menjalankan pengujian PHP
PHPStan	Menganalisis tipe dan potensi kesalahan PHP
ESLint	Memeriksa kualitas kode JavaScript dan TypeScript
Prettier	Memformat kode frontend
Composer Validate	Memvalidasi struktur composer.json
Vite Build	Memastikan aset frontend dapat dibangun
GitHub Actions	Mengotomatisasi pemeriksaan CI
9.1 Perintah Pemeriksaan
composer validate
vendor/bin/pint --test
php artisan test
npm run build

Untuk PHPStan:

vendor/bin/phpstan analyse

Untuk ESLint dan Prettier, periksa skrip yang tersedia:

npm run

Jalankan skrip yang sesuai dengan konfigurasi package.json.

9.2 Aturan Penambahan Tools
Periksa apakah alat yang dibutuhkan sudah tersedia.
Gunakan versi yang kompatibel dengan versi PHP, Laravel, Node.js, dan paket proyek.
Hindari duplikasi fungsi antaralat.
Jangan menambahkan alat baru tanpa manfaat yang jelas.
Jika alat wajib belum dikonfigurasi, laporkan dan usulkan konfigurasi yang sesuai sebelum menganggap pemeriksaannya telah terpenuhi.
9.3 Integrasi Berkelanjutan

CI sebaiknya menjalankan pemeriksaan format, analisis statis, pengujian otomatis, dan build frontend sesuai kebutuhan proyek.

PR tidak boleh dinyatakan siap digabungkan apabila pemeriksaan wajib gagal.

10. Daftar Periksa Deployment

Deployment hanya dapat dilakukan setelah aplikasi diperiksa dalam kondisi yang sesuai untuk produksi.

10.1 Konfigurasi Produksi

APP_ENV=production.

APP_DEBUG=false.

APP_KEY tersedia dan benar.

HTTPS telah dikonfigurasi.

Kredensial database dan layanan eksternal telah diamankan.

Konfigurasi CORS, session, cookie, dan trusted proxy telah diperiksa jika relevan.

Hak akses file dan direktori sudah sesuai.

Tidak ada file .env atau rahasia yang dipublikasikan.

Kebijakan logging dan penanganan error telah diperiksa.

10.2 Database dan Aplikasi

Backup tersedia sebelum migrasi yang berisiko.

Migration telah diperiksa dan diuji.

Tidak ada perubahan skema yang merusak kompatibilitas tanpa rencana migrasi.

Pengujian wajib telah lulus.

Build frontend berhasil.

Dependency produksi telah disiapkan sesuai kebutuhan deployment.

10.3 Cache dan Optimasi

Perintah berikut dapat digunakan apabila sesuai dengan prosedur deployment:

php artisan config:cache
php artisan route:cache
php artisan view:cache

Pastikan konfigurasi aplikasi dan route kompatibel dengan caching sebelum menjalankannya.

Untuk instalasi dependency produksi:

composer install --no-dev --prefer-dist --optimize-autoloader

Untuk membangun aset frontend:

npm ci
npm run build

Sesuaikan perintah dengan pipeline deployment yang sebenarnya. Jangan menjalankan perintah yang menghapus dependency pengembangan sebelum proses build selesai jika build masih membutuhkannya.

10.4 Queue dan Scheduler

Jika proyek menggunakan queue:

Worker telah dijalankan menggunakan proses yang persisten.

Mekanisme restart worker setelah deployment telah tersedia.

Kegagalan job dipantau.

Percobaan ulang dan penanganan kegagalan telah diperiksa.

Jika proyek menggunakan Laravel Scheduler:

Jadwal telah didefinisikan.

Scheduler dijalankan sesuai konfigurasi server.

Tugas yang berkaitan dengan kedaluwarsa langganan telah diuji.

Proses berulang tidak menyebabkan pemrosesan ganda.

10.5 Verifikasi Setelah Deployment

Aplikasi dapat diakses.

Login dan logout berjalan.

Halaman kursus dapat ditampilkan.

Hak akses kursus sesuai dengan status langganan.

Alur pembayaran dan webhook berfungsi sesuai lingkungan yang digunakan.

Tidak terdapat error kritis pada log.

Tindakan pemulihan atau rollback tersedia untuk perubahan berisiko.

Jangan menyatakan deployment berhasil hanya karena halaman utama dapat dibuka. Verifikasi alur penting sesuai ruang lingkup rilis.

11. Perintah yang Berguna
11.1 Laravel
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

Gunakan perintah generator hanya ketika sesuai dengan struktur proyek dan kebutuhan implementasi.

11.2 Composer
composer install
composer update
composer validate

Utamakan composer install untuk memasang dependensi sesuai composer.lock.

Jangan menjalankan composer update secara rutin tanpa alasan karena perintah tersebut dapat mengubah versi dependensi yang terkunci.

11.3 Frontend
npm install
npm run dev
npm run build
npm run

Gunakan npm ci ketika proyek memiliki package-lock.json dan membutuhkan instalasi yang konsisten, terutama dalam CI.

11.4 Git
git status
git branch
git switch -c feat/course-catalog
git add .
git commit -m "feat(courses): add course catalog"
git log --oneline -10

Sebelum menjalankan git add ., periksa status repository dan pastikan tidak ada file sensitif yang akan ikut di-commit.

11.5 Catatan Keselamatan

AI coding agent tidak boleh menjalankan perintah yang menghapus data, mereset repository, mengubah riwayat Git bersama, atau memengaruhi lingkungan produksi tanpa memeriksa dampak dan memperoleh persetujuan yang diperlukan.

12. Ketentuan Khusus Proyek Ilmora

Bagian ini mendefinisikan prinsip bisnis yang perlu dipertahankan ketika mengembangkan Ilmora.

12.1 Autentikasi dan Akun Pengguna
Halaman dan tindakan yang membutuhkan akun harus dilindungi oleh autentikasi.
Pengguna yang belum login harus diarahkan ke proses login jika mencoba mengakses halaman terlindungi.
Pengguna tidak boleh melihat atau mengubah data akun pengguna lain tanpa otorisasi.
Validasi harus tetap dilakukan di server meskipun frontend sudah melakukan validasi.
Alur reset kata sandi harus menggunakan mekanisme yang aman.
Jangan mengasumsikan peran atau status akun hanya berdasarkan data yang dikirim dari frontend.

Gunakan paket autentikasi yang sudah dipilih proyek. Jangan mengganti atau menambahkan paket autentikasi tanpa mengevaluasi dampaknya terhadap route, session, dan sistem pengguna yang sudah ada.

12.2 Pengelolaan Kursus dan Materi
Kursus harus memiliki status yang jelas, seperti draft atau published, sesuai kebutuhan bisnis.
Hanya pengguna yang memiliki izin yang boleh membuat, mengubah, menerbitkan, atau menghapus kursus.
Materi yang dibatasi tidak boleh dapat diakses hanya dengan mengetahui URL langsungnya.
Hak akses materi harus diverifikasi di server.
Pastikan URL unduhan, file, dan endpoint materi tidak membocorkan konten berbayar.
Validasi unggahan file sebelum menyimpannya.
Jangan mengekspos direktori penyimpanan internal atau kredensial akses file.

Jika menggunakan URL bertanda tangan atau tautan unduhan sementara, pastikan masa berlaku dan izin aksesnya sesuai kebutuhan.

12.3 Langganan dan Hak Akses
Tentukan status langganan secara konsisten.
Bedakan status pembayaran dari status langganan.
Akses kursus berbayar hanya diberikan jika pengguna memenuhi aturan akses yang ditentukan sistem.
Validasi tanggal mulai dan berakhir langganan di server.
Tangani status kedaluwarsa secara konsisten.
Jangan mempercayai status premium yang hanya tersimpan di frontend.
Jika terdapat perpanjangan, pembatalan, atau perubahan paket, pastikan transisinya mengikuti aturan bisnis yang ditetapkan.

Jangan mengasumsikan semua langganan otomatis diperpanjang atau semua pembayaran langsung mengaktifkan akses. Ikuti desain bisnis dan integrasi yang benar-benar digunakan.

12.4 Integrasi Pembayaran
Gunakan adapter atau service yang jelas jika integrasi pembayaran memiliki logika yang kompleks.
Simpan konfigurasi sensitif di environment.
Verifikasi notifikasi pembayaran sebelum memperbarui status transaksi.
Terapkan idempotensi pada pemrosesan pembayaran.
Catat perubahan status transaksi secara terkontrol untuk kebutuhan audit.
Gunakan sandbox selama pengembangan.
Jangan pernah menganggap pembayaran berhasil hanya karena pengguna kembali ke halaman sukses.
Jangan mengubah integrasi pembayaran yang ada tanpa memahami kontrak API dan alur transaksinya.

Jika penyedia pembayaran belum dipilih, jangan membuat asumsi tentang nama endpoint, format webhook, atau mekanisme verifikasinya.

12.5 Inertia.js dan Navigasi
Ikuti pola routing Laravel dan Inertia.js yang sudah digunakan.
Gunakan route server sebagai sumber kebenaran untuk halaman yang dilindungi.
Pastikan props yang dikirim ke halaman hanya berisi data yang dibutuhkan.
Jangan mengirim password, token privat, secret, atau atribut internal sensitif ke props Inertia.
Gunakan mekanisme validasi dan error handling yang konsisten.
Pastikan navigasi, formulir, dan aksi pengguna memberikan umpan balik yang jelas.
Hindari melakukan duplikasi logika otorisasi di frontend sebagai pengganti pemeriksaan server.
12.6 Queue dan Penjadwalan

Jika proyek menggunakan queue atau scheduler untuk notifikasi, pemrosesan pembayaran, atau pengelolaan langganan:

Pastikan job dapat ditangani dengan aman jika dijalankan ulang.
Gunakan mekanisme idempotensi untuk proses yang dapat memengaruhi pembayaran atau hak akses.
Tangani kegagalan job secara eksplisit.
Jangan menganggap job berhasil hanya karena sudah dimasukkan ke antrean.
Pastikan jadwal kedaluwarsa langganan sesuai dengan aturan bisnis.
Hindari proses terjadwal yang menghapus data penting tanpa kebijakan retensi yang jelas.
12.7 Privasi dan Perlindungan Data
Terapkan prinsip minimisasi data: kumpulkan dan tampilkan hanya data yang diperlukan.
Jangan mengekspos informasi pribadi pengguna dalam log, respons API, dokumentasi, atau pesan error.
Jangan menampilkan data pengguna lain di halaman kursus, profil, admin, atau laporan tanpa izin yang sesuai.
Lindungi data pembayaran dan identitas akun.
Gunakan data dummy atau data sintetis dalam pengujian dan dokumentasi.
Pastikan endpoint ekspor, unduhan, dan laporan memiliki pemeriksaan otorisasi.
Jangan menyalin data pribadi ke layanan eksternal tanpa dasar dan konfigurasi yang sesuai.
12.8 Pengalaman Pengguna
Gunakan antarmuka yang konsisten dan responsif.
Sediakan indikator loading ketika proses membutuhkan waktu.
Berikan pesan error yang dapat dipahami pengguna.
Berikan konfirmasi untuk tindakan yang berisiko atau sulit dibatalkan.
Pastikan tombol benar-benar menjalankan aksi yang dijanjikan.
Hindari tombol dekoratif yang terlihat aktif tetapi tidak memiliki fungsi.
Pastikan formulir memiliki validasi yang jelas.
Jangan menggunakan data contoh seolah-olah merupakan data produksi nyata.
12.9 Aturan Wajib untuk AI Coding Agent

Setiap AI coding agent yang bekerja pada Ilmora wajib:

Membaca AGENTS.md sebelum melakukan perubahan.
Memeriksa struktur proyek, file konfigurasi, dependensi, dan pola implementasi yang sudah ada.
Memahami kebutuhan pengguna sebelum menentukan pendekatan teknis.
Menghindari perubahan yang tidak berkaitan dengan tugas.
Tidak mengarang API, model, route, tabel, atau fungsi yang belum diverifikasi.
Tidak menghapus fitur atau mengubah perilaku yang sudah ada tanpa alasan yang jelas.
Tidak melemahkan keamanan atau pengujian untuk menyelesaikan masalah dengan cepat.
Menambahkan atau memperbarui pengujian yang relevan.
Menjalankan pemeriksaan yang dapat dilakukan di lingkungan yang tersedia.
Melaporkan file yang diubah, alasan perubahan, hasil pengujian, dan pekerjaan yang belum selesai.
Meminta konfirmasi sebelum melakukan tindakan destruktif, migrasi berisiko, perubahan arsitektur besar, atau operasi terhadap data penting.
Menjaga kerahasiaan data pengguna, kredensial, token, dan informasi internal proyek.
12.10 Definisi Selesai (Definition of Done)

Sebuah tugas hanya dapat dinyatakan selesai jika:

Kebutuhan utama telah dipenuhi.

Implementasi mengikuti pola dan standar proyek.

Validasi dan otorisasi telah diterapkan sesuai kebutuhan.

Tidak ada rahasia atau data pribadi yang terekspos.

Pengujian baru atau yang relevan telah dijalankan.

Pemeriksaan kualitas kode yang diwajibkan telah dilakukan.

Perubahan tidak merusak alur penting yang sudah tersedia.

Dokumentasi diperbarui jika diperlukan.

Hasil dan keterbatasan pengujian dilaporkan dengan jujur.

Prinsip Akhir

Keamanan, kebenaran logika bisnis, integritas data, dan kualitas pengalaman pengguna lebih penting daripada sekadar menyelesaikan tugas dengan cepat.

Setiap perubahan pada Ilmora harus dapat dijelaskan, dapat diuji, aman untuk dipelihara, dan sesuai dengan kebutuhan proyek yang sebenarnya.

Jika instruksi tugas bertentangan dengan praktik keamanan atau integritas data, jangan menerapkan perubahan berisiko secara diam-diam. Jelaskan konflik tersebut dan ajukan pendekatan yang lebih aman.

Dokumen ini merupakan pedoman pengembangan utama Ilmora. Jika konfigurasi proyek yang sebenarnya berbeda dari contoh di atas, periksa implementasi aktual terlebih dahulu dan sesuaikan perubahan tanpa mengorbankan prinsip keamanan dan kualitas.