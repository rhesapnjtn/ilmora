<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ilmora - Dashboard</title>
<link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: { extend: {
      fontFamily: { sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'] },
      colors: {
        primary: '#3525CD',
        surface: '#F7F6FD',
        'surface-dim': '#ECEAF8',
        'on-surface': '#1A1830',
        'on-surface-variant': '#5A5875',
        'outline-variant': '#D9D6EE'
      }
    } }
  };
</script>
<script src="https://cdn.jsdelivr.net/npm/vue@3.5.13/dist/vue.global.prod.js"></script>
<style>
  [v-cloak] { display: none; }
  @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
</style>
</head>
<body class="bg-surface text-on-surface font-sans min-h-screen antialiased">
<div id="app" v-cloak>

  <!-- Navbar -->
  <nav class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-outline-variant">
    <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
      <h1 class="text-lg font-extrabold tracking-tight">Ilmora</h1>
      <div class="relative" @click.stop>
        <button @click="menu = !menu" class="flex items-center gap-2 rounded-full pl-3 pr-1 py-1 hover:bg-surface-dim focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary" :aria-expanded="menu">
          <span class="hidden sm:inline text-sm font-medium text-on-surface-variant">Selamat datang, {{ user.name }}</span>
          <span class="h-8 w-8 rounded-full bg-primary text-white grid place-items-center text-sm font-bold">{{ user.name.charAt(0).toUpperCase() }}</span>
        </button>
        <div v-if="menu" class="absolute right-0 mt-2 w-44 bg-white border border-outline-variant rounded-xl shadow-lg py-1 text-sm">
          <a href="#" class="block px-4 py-2 hover:bg-surface-dim">Profil</a>
          <a href="#" class="block px-4 py-2 hover:bg-surface-dim">Pengaturan</a>
          <a href="#" class="block px-4 py-2 hover:bg-surface-dim text-red-600">Keluar</a>
        </div>
      </div>
    </div>
  </nav>

  <main class="max-w-6xl mx-auto px-4 py-6 sm:py-10 space-y-8">

    <!-- Lanjutkan belajar -->
    <section v-if="loading" class="h-40 rounded-3xl bg-surface-dim animate-pulse"></section>
    <section v-else-if="resume" class="rounded-3xl bg-primary text-white p-6 sm:p-8 grid sm:grid-cols-[1fr_auto] gap-6 items-end">
      <div class="max-w-xl">
        <p class="text-white/75 text-sm mb-1">Lanjutkan belajar</p>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight">{{ resume.title }}</h2>
        <div class="mt-5 flex items-center gap-3">
          <div class="h-2 flex-1 rounded-full bg-white/25 overflow-hidden">
            <div class="h-full rounded-full bg-white transition-all duration-700" :style="{ width: resume.progress + '%' }"></div>
          </div>
          <span class="text-sm font-semibold w-10 text-right">{{ resume.progress }}%</span>
        </div>
      </div>
      <button @click="openCourse(resume)" class="rounded-full bg-white text-primary font-semibold px-6 py-3 hover:bg-surface-dim focus-visible:outline focus-visible:outline-2 focus-visible:outline-white focus-visible:outline-offset-2">
        {{ resume.progress === 0 ? 'Mulai kursus' : 'Lanjutkan kursus' }}
      </button>
    </section>
    <section v-else class="rounded-3xl bg-primary text-white p-6 sm:p-8">
      <h2 class="text-2xl font-extrabold">Semua kursus sudah selesai</h2>
      <p class="text-white/75 mt-1">Kerja bagus. Cari kursus baru di daftar bawah.</p>
    </section>

    <!-- Ringkasan -->
    <section class="grid grid-cols-3 gap-3 sm:gap-4">
      <div v-for="s in stats" :key="s.label" class="bg-white border border-outline-variant rounded-2xl p-4 sm:p-5">
        <template v-if="loading"><div class="h-8 w-16 bg-surface-dim rounded animate-pulse"></div></template>
        <p v-else class="text-2xl sm:text-3xl font-extrabold tabular-nums">{{ s.value.value }}{{ s.suffix }}</p>
        <p class="text-xs sm:text-sm text-on-surface-variant mt-1">{{ s.label }}</p>
      </div>
    </section>

    <!-- Daftar kursus -->
    <section>
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-5">
        <h2 class="text-xl font-bold">Kursus saya</h2>
        <div class="flex flex-col sm:flex-row gap-2">
          <label class="relative">
            <span class="sr-only">Cari kursus</span>
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-on-surface-variant" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="9" r="6"/><path d="m14 14 4 4" stroke-linecap="round"/></svg>
            <input v-model="q" type="search" placeholder="Cari kursus" class="w-full sm:w-56 rounded-full border border-outline-variant bg-white pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
          </label>
          <select v-model="level" aria-label="Filter level" class="rounded-full border border-outline-variant bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            <option>Semua</option><option>Pemula</option><option>Menengah</option><option>Lanjut</option>
          </select>
          <select v-model="sort" aria-label="Urutkan" class="rounded-full border border-outline-variant bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            <option value="terakhir">Terakhir dibuka</option>
            <option value="progress">Progress tertinggi</option>
            <option value="murah">Harga terendah</option>
            <option value="baru">Terbaru</option>
          </select>
        </div>
      </div>

      <div class="flex gap-2 overflow-x-auto pb-2 mb-4">
        <button v-for="t in tabs" :key="t" @click="tab = t"
          :class="tab === t ? 'bg-on-surface text-white' : 'bg-white text-on-surface-variant border border-outline-variant hover:bg-surface-dim'"
          class="shrink-0 rounded-full px-4 py-1.5 text-sm font-medium">{{ t }}</button>
      </div>

      <!-- Skeleton -->
      <div v-if="loading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="n in 6" :key="n" class="h-48 rounded-2xl bg-surface-dim animate-pulse"></div>
      </div>

      <!-- Kosong -->
      <div v-else-if="!courses.length" class="rounded-2xl border border-dashed border-outline-variant p-10 text-center text-on-surface-variant">
        Belum ada kursus. Buat kursus pertama Anda.
      </div>
      <div v-else-if="!filtered.length" class="rounded-2xl border border-dashed border-outline-variant p-10 text-center">
        <p class="font-semibold">Tidak ada kursus yang cocok</p>
        <p class="text-sm text-on-surface-variant mt-1">Coba kata kunci lain atau hapus filter.</p>
        <button @click="reset" class="mt-4 rounded-full bg-primary text-white text-sm font-semibold px-5 py-2">Hapus filter</button>
      </div>

      <!-- Kartu -->
      <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <article v-for="c in filtered" :key="c.id" class="bg-white border border-outline-variant rounded-2xl p-5 flex flex-col transition hover:-translate-y-0.5 hover:shadow-md">
          <div class="flex items-start justify-between gap-3">
            <span :class="levelClass[c.level]" class="rounded-full px-2.5 py-0.5 text-xs font-semibold">{{ c.level }}</span>
            <button @click="toggleFav(c)" :aria-label="c.fav ? 'Hapus dari favorit' : 'Tambah ke favorit'" :aria-pressed="c.fav" class="-m-1 p-1 rounded-full hover:bg-surface-dim focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary">
              <svg class="h-5 w-5" viewBox="0 0 20 20" :fill="c.fav ? '#3525CD' : 'none'" stroke="#3525CD" stroke-width="1.8" stroke-linejoin="round"><path d="M5 3h10v14l-5-3.5L5 17z"/></svg>
            </button>
          </div>
          <h3 class="mt-3 font-bold text-lg leading-snug">{{ c.title }}</h3>
          <p class="text-sm text-on-surface-variant mt-1">{{ status(c) }}</p>
          <div class="mt-4 flex items-center gap-3">
            <div class="h-2 flex-1 rounded-full bg-surface-dim overflow-hidden">
              <div class="h-full rounded-full bg-primary transition-all duration-700" :style="{ width: c.progress + '%' }"></div>
            </div>
            <span class="text-xs font-semibold tabular-nums w-9 text-right">{{ c.progress }}%</span>
          </div>
          <div class="mt-5 pt-4 border-t border-outline-variant flex items-center justify-between">
            <span class="font-semibold">{{ rupiah(c.price) }}</span>
            <button @click="openCourse(c)" class="rounded-full bg-primary text-white text-sm font-semibold px-4 py-2 hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-2">
              {{ c.progress === 0 ? 'Mulai' : c.progress === 100 ? 'Buka lagi' : 'Lanjutkan' }}
            </button>
          </div>
        </article>
      </div>
    </section>
  </main>

  <footer class="border-t border-outline-variant bg-surface-dim mt-6">
    <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-sm text-on-surface-variant">
      <span>&copy; 2026 Ilmora. Hak cipta dilindungi.</span>
      <span class="flex gap-6"><a href="#" class="hover:text-primary">Kebijakan Privasi</a><a href="#" class="hover:text-primary">Syarat Layanan</a></span>
    </div>
  </footer>

  <!-- Toast -->
  <div aria-live="polite" class="fixed bottom-5 inset-x-0 flex justify-center pointer-events-none px-4">
    <div v-if="toast" class="bg-on-surface text-white text-sm rounded-full px-5 py-2.5 shadow-lg">{{ toast }}</div>
  </div>
</div>

<script>
  const { createApp, ref, computed, watch, onMounted } = Vue;

  // Ganti nama ini dengan nama user yang login
  const user = { name: 'Pengguna' };

  // DATA DUMMY. Nanti ganti dengan data dari database/API Laravel.
  const seed = [
    { id: 1, title: 'Dasar Laravel 12',          level: 'Pemula',   progress: 100, price: 0,      lastOpened: 1, createdAt: 1 },
    { id: 2, title: 'Vue 3 untuk Pemula',        level: 'Pemula',   progress: 60,  price: 149000, lastOpened: 6, createdAt: 2 },
    { id: 3, title: 'Tailwind CSS dari Nol',     level: 'Pemula',   progress: 35,  price: 99000,  lastOpened: 5, createdAt: 3 },
    { id: 4, title: 'MySQL dan Desain Database', level: 'Menengah', progress: 0,   price: 179000, lastOpened: 0, createdAt: 4 },
    { id: 5, title: 'REST API dengan Laravel',   level: 'Menengah', progress: 80,  price: 199000, lastOpened: 4, createdAt: 5 },
    { id: 6, title: 'Git dan GitHub Praktis',    level: 'Pemula',   progress: 100, price: 0,      lastOpened: 2, createdAt: 6 },
    { id: 7, title: 'Testing dengan Pest',       level: 'Lanjut',   progress: 0,   price: 249000, lastOpened: 0, createdAt: 7 },
    { id: 8, title: 'Deploy Laravel ke VPS',     level: 'Lanjut',   progress: 15,  price: 299000, lastOpened: 3, createdAt: 8 },
    { id: 9, title: 'Figma untuk Developer',     level: 'Pemula',   progress: 0,   price: 0,      lastOpened: 0, createdAt: 9 }
  ];

  // Angka yang berhitung naik/turun dengan halus
  function animated(getter) {
    const shown = ref(0);
    watch(getter, (to) => {
      const from = shown.value, t0 = performance.now();
      const step = (t) => {
        const p = Math.min((t - t0) / 700, 1);
        shown.value = Math.round(from + (to - from) * p);
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    }, { immediate: true });
    return shown;
  }

  createApp({
    setup() {
      const courses = ref(seed.map(c => ({ ...c, fav: false })));
      const loading = ref(true);
      const q = ref(''), tab = ref('Semua'), level = ref('Semua'), sort = ref('terakhir');
      const menu = ref(false), toast = ref('');
      const tabs = ['Semua', 'Sedang Berjalan', 'Selesai', 'Belum Mulai'];
      const levelClass = {
        Pemula: 'bg-emerald-100 text-emerald-800',
        Menengah: 'bg-amber-100 text-amber-800',
        Lanjut: 'bg-rose-100 text-rose-800'
      };
      let tick = 100, timer;

      const status = c => c.progress === 100 ? 'Selesai' : c.progress === 0 ? 'Belum Mulai' : 'Sedang Berjalan';
      const rupiah = n => n === 0 ? 'Gratis' :
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);

      const filtered = computed(() => {
        const key = q.value.trim().toLowerCase();
        const list = courses.value.filter(c =>
          c.title.toLowerCase().includes(key) &&
          (tab.value === 'Semua' || status(c) === tab.value) &&
          (level.value === 'Semua' || c.level === level.value));
        const by = {
          terakhir: (a, b) => b.lastOpened - a.lastOpened,
          progress: (a, b) => b.progress - a.progress,
          murah:    (a, b) => a.price - b.price,
          baru:     (a, b) => b.createdAt - a.createdAt
        };
        return list.sort(by[sort.value]);
      });

      const resume = computed(() => courses.value
        .filter(c => c.progress < 100)
        .sort((a, b) => b.lastOpened - a.lastOpened)[0]);

      const active = computed(() => courses.value.filter(c => status(c) === 'Sedang Berjalan').length);
      const done = computed(() => courses.value.filter(c => c.progress === 100).length);
      const avg = computed(() => courses.value.length
        ? Math.round(courses.value.reduce((s, c) => s + c.progress, 0) / courses.value.length) : 0);
      const stats = [
        { label: 'Sedang berjalan', value: animated(() => active.value), suffix: '' },
        { label: 'Selesai',         value: animated(() => done.value),   suffix: '' },
        { label: 'Rata-rata progress', value: animated(() => avg.value), suffix: '%' }
      ];

      function say(m) { toast.value = m; clearTimeout(timer); timer = setTimeout(() => toast.value = '', 2200); }
      function openCourse(c) {
        if (c.progress === 100) { say('Membuka ulang: ' + c.title); return; }
        // Demo: setiap klik menambah progress 10%. Ganti dengan pindah ke halaman kursus.
        c.progress = Math.min(100, c.progress + 10);
        c.lastOpened = ++tick;
        say(c.progress === 100 ? 'Selesai: ' + c.title : 'Melanjutkan: ' + c.title);
      }
      function toggleFav(c) { c.fav = !c.fav; say(c.fav ? 'Ditambahkan ke favorit' : 'Dihapus dari favorit'); }
      function reset() { q.value = ''; tab.value = 'Semua'; level.value = 'Semua'; }

      onMounted(() => {
        setTimeout(() => loading.value = false, 700);
        document.addEventListener('click', () => menu.value = false);
      });

      return { user, courses, loading, q, tab, tabs, level, sort, menu, toast, levelClass,
               filtered, resume, stats, status, rupiah, openCourse, toggleFav, reset };
    }
  }).mount('#app');
</script>
</body>
</html>