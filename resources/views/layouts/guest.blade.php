<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Login — R&D Management System PT Herbatech Innopharma Industry">
    <title>Login — {{ config('app.name', 'Herbatech R&D') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌿</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <!-- Full-screen split layout -->
    <div class="min-h-screen flex">

        <!-- Left: Decorative Panel (hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 bg-gradient-herbal relative overflow-hidden flex-col items-center justify-center p-12">
            <!-- Decorative circles -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-white/5 rounded-full translate-y-1/3 -translate-x-1/4"></div>
            <div class="absolute bottom-1/3 right-1/4 w-48 h-48 bg-white/5 rounded-full"></div>

            <!-- Pattern overlay -->
            <div class="absolute inset-0 pattern-herbal opacity-30"></div>

            <!-- Content -->
            <div class="relative z-10 text-center max-w-md">
                <div class="text-8xl mb-8 drop-shadow-lg select-none">🌿</div>
                <h1 class="text-4xl font-heading font-bold text-white mb-4 leading-tight">
                    R&D Management<br>System
                </h1>
                <p class="text-white/70 text-lg leading-relaxed">
                    Sistem manajemen riset & pengembangan produk herbal PT Herbatech Innopharma Industry
                </p>

                <!-- Features list -->
                <div class="mt-10 space-y-3 text-left">
                    @foreach([
                        ['icon' => '📋', 'text' => 'Formulasi RM dengan validasi komposisi 100%'],
                        ['icon' => '🧪', 'text' => 'Catatan Trial RM & PM terintegrasi'],
                        ['icon' => '✅', 'text' => 'Approval berjenjang dengan audit trail'],
                    ] as $feature)
                    <div class="flex items-center gap-3 bg-white/10 rounded-xl px-4 py-3">
                        <span class="text-xl">{{ $feature['icon'] }}</span>
                        <span class="text-white/85 text-sm">{{ $feature['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Bottom credit -->
            <p class="absolute bottom-6 text-white/30 text-xs">
                PT Herbatech Innopharma Industry &copy; {{ date('Y') }}
            </p>
        </div>

        <!-- Right: Login Form -->
        <div class="flex-1 flex items-center justify-center p-6 bg-surface">
            <div class="w-full max-w-sm">

                <!-- Mobile logo (visible only on mobile) -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex items-center gap-2 text-primary">
                        <span class="text-4xl">🌿</span>
                        <div class="text-left">
                            <p class="font-heading font-bold text-lg leading-tight">Herbatech R&D</p>
                            <p class="text-xs text-gray-500">PT Herbatech Innopharma Industry</p>
                        </div>
                    </div>
                </div>

                <!-- Card -->
                <div class="bg-white rounded-2xl shadow-card-hover border border-gray-100 p-8">
                    {{ $slot }}
                </div>

                <p class="text-center text-xs text-gray-400 mt-6">
                    © {{ date('Y') }} PT Herbatech Innopharma Industry.<br>
                    Hak cipta dilindungi undang-undang.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
