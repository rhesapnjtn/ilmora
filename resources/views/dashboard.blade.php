<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Ilmora - Dashboard</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>

        <script type="text/javascript">
            document.documentElement.classList.add('antialiase');
        </script>
    </head>
    <body class="bg-surface text-on-surface font-body min-h-screen">

        <div id="app">
            <!-- Navigation -->
            <nav class="bg-surface-dim border-b border-outline-variant">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="flex h-14 items-center justify-between">
                        <h1 class="text-display font-bold tracking-tighter text-on-surface">
                            Ilmora Dashboard
                        </h1>
                        <div class="hidden sm:flex items-center gap-2">
                            <span class="text-label-sm font-medium text-on-surface-variant">
                                Selamat datang,
                            </span>
                            <img
                                src="https://ui-avatars.com/api/?name=User&background=3525CD&color=FFFFFF&size=24&bold=true"
                                alt="User avatar"
                                class="h-8 w-8 rounded-full"
                            />
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Banner Section -->
            <section class="bg-primary/10 border-b border-primary/20">
                <div class="max-w-7xl mx-auto px-4 py-8">
                    <div class="grid lg:grid-cols-2 gap-8 items-center">
                        <div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-on-surface mb-4">
                                Selamat Datang di Ilmora
                            </h2>
                            <p class="text-on-surface-variant text-lg max-w-xl">
                                Platform pembelajaran digital untuk pengalaman belajar yang modern dan interaktif.
                            </p>
                        </div>
                        <div class="lg:col-span-1">
                            <img
                                src="https://ui-avatars.com/api/?name=User&background=3525CD&color=FFFFFF&size=24&bold=true"
                                alt="User"
                                class="h-32 w-32 rounded-full object-cover border-2 border-primary"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <main>
                <div class="max-w-7xl mx-auto py-6 sm:py-12">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                        <!-- Course Cards -->
                        <CourseCard
                            v-for="course in courses"
                            :key="course.id"
                            :title="course.title"
                            :level="course.level"
                            :progress="course.progress"
                            :price="course.price"
                        />

                        <!-- Empty state when no courses -->
                        <div v-if="courses.length === 0" class="col-span-full text-on-surface-variant text-sm">
                            Belum ada kursus. Buat kursus pertama Anda.
                        </div>

                    </div>
                </div>
            </main>

            <!-- Footer Section -->
            <footer class="bg-surface-dim border-t border-outline-variant">
                <div class="max-w-7xl mx-auto px-4 py-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="text-on-surface-variant text-sm">
                            &copy; 2026 Ilmora. Hak cipta dilindungi.
                        </div>
                        <div class="flex items-center gap-6">
                            <a href="#" class="text-on-surface-variant hover:text-primary transition-colors">
                                Kebijakan Privasi
                            </a>
                            <a href="#" class="text-on-surface-variant hover:text-primary transition-colors">
                                Syarat Layanan
                            </a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <script type="module">
            // Course data from Laravel Blade
            <% const courses = <%= json_encode($courses ?? []) %>; %>

            createApp({
                data() {
                    return {
                        courses: courses
                    };
                }
            }).mount('#app');
        </script>
    </body>
</html>