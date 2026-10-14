<?php

declare(strict_types=1);

namespace App\Support;

class DemoCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function categories(): array
    {
        return [
            ['slug' => 'pemrograman', 'name' => 'Pemrograman'],
            ['slug' => 'desain', 'name' => 'Desain UI/UX'],
            ['slug' => 'marketing', 'name' => 'Marketing'],
            ['slug' => 'business', 'name' => 'Business & PM'],
            ['slug' => 'data', 'name' => 'Data Science'],
            ['slug' => 'lainnya', 'name' => 'Lainnya'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function levels(): array
    {
        return ['Pemula', 'Menengah', 'Lanjutan'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function courses(): array
    {
        return [
            [
                'slug' => 'laravel-vue-3-full-stack',
                'title' => 'Laravel 10 + Vue 3 Full Stack Web Development',
                'summary' => 'Bangun aplikasi web modern skala industri dari dasar hingga deployment.',
                'description' => 'Pelajari cara membangun aplikasi web modern menggunakan Laravel 10 dan Vue 3. Mulai dari dasar PHP dan JavaScript hingga arsitektur full stack modern skala industri, termasuk REST API, autentikasi, database, dan deployment.',
                'category' => 'pemrograman',
                'categoryName' => 'Pemrograman',
                'level' => 'Menengah',
                'price' => 299000,
                'rating' => 4.8,
                'reviews' => 1240,
                'students' => 4820,
                'durationHours' => 12,
                'lessonsCount' => 48,
                'instructor' => ['name' => 'Ricky Pratama', 'role' => 'Senior Full Stack Engineer'],
                'accent' => 'primary',
                'popular' => true,
                'free' => false,
            ],
            [
                'slug' => 'ui-ux-design-prototyping',
                'title' => 'UI/UX Design: Fondasi & Prototyping dari Nol hingga Pro',
                'summary' => 'Kuasai design thinking, wireframe, dan prototyping dengan Figma.',
                'description' => 'Mulai dari prinsip dasar desain, riset pengguna, wireframe, hingga menjadi prototipe interaktif yang siap dipresentasikan kepada tim produk.',
                'category' => 'desain',
                'categoryName' => 'Desain UI/UX',
                'level' => 'Pemula',
                'price' => 249000,
                'rating' => 4.9,
                'reviews' => 952,
                'students' => 3980,
                'durationHours' => 9,
                'lessonsCount' => 36,
                'instructor' => ['name' => 'Sarah Amelia', 'role' => 'Lead Product Designer'],
                'accent' => 'secondary',
                'popular' => true,
                'free' => false,
            ],
            [
                'slug' => 'digital-marketing-growth',
                'title' => 'Digital Marketing & Social Media Growth Strategy',
                'summary' => 'Susun strategi pemasaran digital dan konten sosial media yang terukur.',
                'description' => 'Pelajari riset audiens, penyusunan konten, algoritma platform, hingga pengukuran performa kampanye digital untuk pertumbuhan yang berkelanjutan.',
                'category' => 'marketing',
                'categoryName' => 'Marketing',
                'level' => 'Pemula',
                'price' => 199000,
                'rating' => 4.7,
                'reviews' => 1980,
                'students' => 5210,
                'durationHours' => 8,
                'lessonsCount' => 32,
                'instructor' => ['name' => 'Dimas Anggara', 'role' => 'Growth Marketing Lead'],
                'accent' => 'tertiary',
                'popular' => true,
                'free' => false,
            ],
            [
                'slug' => 'python-data-science',
                'title' => 'Python for Data Science & Machine Learning Terapan',
                'summary' => 'Olah data dan bangun model machine learning dengan Python.',
                'description' => 'Dari pengolahan data dengan pandas hingga membangun dan mengevaluasi model machine learning terapan untuk kebutuhan industri.',
                'category' => 'data',
                'categoryName' => 'Data Science',
                'level' => 'Lanjutan',
                'price' => 349000,
                'rating' => 4.9,
                'reviews' => 860,
                'students' => 2140,
                'durationHours' => 14,
                'lessonsCount' => 52,
                'instructor' => ['name' => 'Hendra Wijaya', 'role' => 'Data Scientist'],
                'accent' => 'primary',
                'popular' => false,
                'free' => false,
            ],
            [
                'slug' => 'flutter-firebase',
                'title' => 'Flutter 3 & Firebase Cross Platform App Development',
                'summary' => 'Buat aplikasi mobile Android & iOS dari satu basis kode.',
                'description' => 'Bangun aplikasi mobile cross platform dengan Flutter 3 dan Firebase, mulai dari UI, state management, hingga integrasi backend.',
                'category' => 'pemrograman',
                'categoryName' => 'Pemrograman',
                'level' => 'Menengah',
                'price' => 279000,
                'rating' => 4.8,
                'reviews' => 1120,
                'students' => 3010,
                'durationHours' => 11,
                'lessonsCount' => 40,
                'instructor' => ['name' => 'Rian Ardiansyah', 'role' => 'Mobile Engineer'],
                'accent' => 'secondary',
                'popular' => true,
                'free' => false,
            ],
            [
                'slug' => 'product-management',
                'title' => 'Product Management Fundamentals: Strategi & Eksekusi',
                'summary' => 'Rancang produk yang tepat dengan proses product discovery yang rapi.',
                'description' => 'Pelajari cara mengelola produk digital dari riset pengguna, prioritas fitur, roadmap, hingga peluncuran dan evaluasi bersama tim.',
                'category' => 'business',
                'categoryName' => 'Business & PM',
                'level' => 'Menengah',
                'price' => 229000,
                'rating' => 4.8,
                'reviews' => 1120,
                'students' => 2760,
                'durationHours' => 9,
                'lessonsCount' => 34,
                'instructor' => ['name' => 'Siti Rahma', 'role' => 'Product Lead'],
                'accent' => 'tertiary',
                'popular' => false,
                'free' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $slug): ?array
    {
        $course = null;

        foreach (self::courses() as $item) {
            if ($item['slug'] === $slug) {
                $course = $item;
                break;
            }
        }

        if ($course === null) {
            return null;
        }

        return array_merge($course, [
            'learn' => [
                'Menguasai arsitektur Laravel dan Vue 3 secara menyeluruh',
                'Membangun REST API yang aman dan terstruktur',
                'Mengelola database dengan Eloquent ORM dan migrations',
                'Mengintegrasikan frontend Vue 3 dengan backend Laravel',
                'Menangani autentikasi dan otorisasi pengguna',
                'Melakukan deployment aplikasi ke environment produksi',
            ],
            'includes' => [
                ['label' => '12 jam video pembelajaran', 'icon' => 'clock'],
                ['label' => 'Source code eksklusif', 'icon' => 'code'],
                ['label' => 'Sertifikat penyelesaian', 'icon' => 'award'],
                ['label' => 'Akses selamanya', 'icon' => 'infinity'],
            ],
            'curriculum' => [
                [
                    'title' => 'Pengenalan & Persiapan',
                    'lessons' => [
                        ['id' => '1-1', 'title' => 'Pengenalan Ekosistem Full Stack', 'duration' => '12:40', 'free' => true],
                        ['id' => '1-2', 'title' => 'Instalasi & Konfigurasi Environment', 'duration' => '16:20', 'free' => true],
                    ],
                ],
                [
                    'title' => 'Dasar Laravel',
                    'lessons' => [
                        ['id' => '2-1', 'title' => 'Setup Project Laravel', 'duration' => '18:15', 'free' => false],
                        ['id' => '2-2', 'title' => 'Routing', 'duration' => '15:30', 'free' => false],
                        ['id' => '2-3', 'title' => 'Controller', 'duration' => '20:10', 'free' => false],
                        ['id' => '2-4', 'title' => 'Blade Template', 'duration' => '14:22', 'free' => false],
                        ['id' => '2-5', 'title' => 'Database & Migration', 'duration' => '22:05', 'free' => false],
                        ['id' => '2-6', 'title' => 'Eloquent ORM', 'duration' => '19:40', 'free' => false],
                        ['id' => '2-7', 'title' => 'Authentication', 'duration' => '25:50', 'free' => false],
                    ],
                ],
                [
                    'title' => 'Integrasi Vue 3 & REST API',
                    'lessons' => [
                        ['id' => '3-1', 'title' => 'Menyiapkan Vue 3 & Vite', 'duration' => '21:00', 'free' => false],
                        ['id' => '3-2', 'title' => 'Composition API & State Management', 'duration' => '18:45', 'free' => false],
                        ['id' => '3-3', 'title' => 'Konsumsi REST API Laravel', 'duration' => '24:10', 'free' => false],
                    ],
                ],
            ],
            'progress' => 40,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function lesson(string $slug, string $lessonId): ?array
    {
        $course = self::find($slug);

        if ($course === null) {
            return null;
        }

        $lessons = [];

        foreach ($course['curriculum'] as $module) {
            foreach ($module['lessons'] as $lesson) {
                $lessons[] = array_merge($lesson, ['module' => $module['title']]);
            }
        }

        $index = null;

        foreach ($lessons as $i => $lesson) {
            if ($lesson['id'] === $lessonId) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return null;
        }

        $current = $lessons[$index];

        return [
            'course' => [
                'slug' => $course['slug'],
                'title' => $course['title'],
                'instructor' => $course['instructor'],
                'level' => $course['level'],
                'lessonsCount' => count($lessons),
            ],
            'lesson' => array_merge($current, [
                'index' => $index + 1,
                'total' => count($lessons),
                'description' => 'Dalam bab ini, kita akan mempelajari arsitektur dasar ekosistem modern Laravel 10. Kita membedah konsep MVC (Model-View-Controller), peran Composer, serta bagaimana struktur direktori Laravel disiapkan untuk integrasi dengan antarmuka Vue 3.',
                'code' => "use Illuminate\\Support\\Facades\\Route;\n\nRoute::get('/', function () {\n    return view('welcome');\n});",
                'materials' => [
                    ['label' => 'Modul PDF', 'size' => '2.4 MB', 'type' => 'pdf'],
                    ['label' => 'Source Code ZIP', 'size' => '1.2 MB', 'type' => 'zip'],
                ],
            ]),
            'lessons' => $lessons,
            'prev' => $index > 0 ? $lessons[$index - 1]['id'] : null,
            'next' => $index < count($lessons) - 1 ? $lessons[$index + 1]['id'] : null,
            'progress' => $course['progress'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function certificates(): array
    {
        return [
            [
                'id' => 'ilr-cert-2026-001',
                'courseSlug' => 'laravel-vue-3-full-stack',
                'courseTitle' => 'Web Development Full Stack dengan Laravel & Vue 3',
                'level' => 'Menengah',
                'hours' => 12,
                'issuedAt' => '2026-04-12',
                'instructor' => 'Ricky Pratama',
            ],
            [
                'id' => 'ilr-cert-2026-002',
                'courseSlug' => 'ui-ux-design-prototyping',
                'courseTitle' => 'UI/UX Design: Fondasi & Prototyping',
                'level' => 'Pemula',
                'hours' => 9,
                'issuedAt' => '2026-03-28',
                'instructor' => 'Sarah Amelia',
            ],
            [
                'id' => 'ilr-cert-2026-003',
                'courseSlug' => 'digital-marketing-growth',
                'courseTitle' => 'Digital Marketing & Social Media Growth Strategy',
                'level' => 'Pemula',
                'hours' => 8,
                'issuedAt' => '2026-02-14',
                'instructor' => 'Dimas Anggara',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findCertificate(string $id): ?array
    {
        foreach (self::certificates() as $certificate) {
            if ($certificate['id'] === $id) {
                return $certificate;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function plans(): array
    {
        return [
            [
                'id' => 'basic',
                'name' => 'Basic',
                'monthly' => 99000,
                'annual' => 949000,
                'highlighted' => false,
                'features' => [
                    'Akses semua kursus dasar',
                    'Sertifikat digital',
                    'Akses 1 perangkat',
                    'Support komunitas',
                ],
            ],
            [
                'id' => 'premium',
                'name' => 'Premium',
                'monthly' => 149000,
                'annual' => 1430000,
                'highlighted' => true,
                'features' => [
                    'Akses seluruh katalog kursus',
                    'Sertifikat premium',
                    'Akses 3 perangkat',
                    'Mentor & webinar',
                    'Support prioritas',
                ],
            ],
            [
                'id' => 'pro',
                'name' => 'Pro',
                'monthly' => 249000,
                'annual' => 2390000,
                'highlighted' => false,
                'features' => [
                    'Semua keuntungan paket Premium',
                    'Akses 5 perangkat',
                    'Proyek & portofolio',
                    'Support VIP',
                    'Sesi mentoring bulanan',
                ],
            ],
        ];
    }
}
