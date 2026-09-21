<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? setting('maintenance_title', 'Sistem Dalam Pemeliharaan') }} — {{ setting('app_name', 'Herbatech R&D') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Favicon -->
    @if(setting('app_favicon'))
    <link rel="icon" type="image/png" href="{{ asset('storage/' . setting('app_favicon')) }}">
    @else
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌿</text></svg>">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            900: '#064e3b',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes pulse-subtle {
            0%, 100% { transform: scale(1); opacity: 0.9; }
            50% { transform: scale(1.05); opacity: 1; }
        }
        .animate-pulse-subtle {
            animation: pulse-subtle 3s ease-in-out infinite;
        }
        .mesh-gradient {
            background: radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.12) 0px, transparent 50%),
                        radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%),
                        radial-gradient(at 50% 50%, rgba(245, 158, 11, 0.06) 0px, transparent 50%),
                        #f8fafc;
        }
    </style>
</head>
<body class="mesh-gradient min-h-screen flex flex-col justify-between text-slate-800 antialiased font-sans selection:bg-brand-500 selection:text-white">

    <!-- Top Header -->
    <header class="p-6 sm:p-8 flex items-center justify-between max-w-6xl w-full mx-auto">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-slate-200/80 flex items-center justify-center overflow-hidden flex-shrink-0">
                @if(setting('app_logo'))
                <img src="{{ asset('storage/' . setting('app_logo')) }}" class="w-full h-full object-cover">
                @else
                <span class="text-2xl">🌿</span>
                @endif
            </div>
            <div>
                <h1 class="font-heading font-bold text-slate-900 text-sm sm:text-base leading-tight">{{ setting('app_name', 'Herbatech R&D') }}</h1>
                <p class="text-xs text-slate-500 leading-tight">{{ setting('company_name', 'PT Herbatech Innopharma') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
            </span>
            <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 bg-amber-50 border border-amber-200/80 px-2.5 py-1 rounded-full">
                Maintenance Mode
            </span>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="max-w-xl w-full bg-white/90 backdrop-blur-md rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/80 p-8 sm:p-10 text-center relative overflow-hidden">
            
            <!-- Background Decorative Accent -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-400/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-amber-400/10 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Illustration / Icon -->
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-tr from-amber-50 to-emerald-50 border border-emerald-100/80 text-emerald-600 shadow-inner mb-6 animate-pulse-subtle">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                </svg>
            </div>

            <!-- Title & Message -->
            <h2 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-3">
                {{ $title ?? setting('maintenance_title', 'Sistem Sedang Dalam Pemeliharaan') }}
            </h2>
            
            <p class="text-slate-600 text-sm sm:text-base leading-relaxed max-w-md mx-auto mb-8">
                {{ $message ?? setting('maintenance_message', 'Saat ini kami sedang melakukan peningkatan sistem dan pemeliharaan berkala untuk kenyamanan Anda. Sistem akan segera dapat diakses kembali.') }}
            </p>

            <!-- Countdown Timer Section (if end time is provided) -->
            @php($targetTime = $endTime ?? setting('maintenance_end_time'))
            @if($targetTime)
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5 mb-8 text-center" id="countdown-wrapper" data-target="{{ $targetTime }}">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Estimasi Selesai Pemeliharaan
                </p>
                <div class="grid grid-cols-4 gap-2 sm:gap-3 max-w-xs mx-auto">
                    <div class="bg-white rounded-xl py-2.5 px-2 border border-slate-200/70 shadow-sm">
                        <span id="days" class="font-heading font-bold text-xl sm:text-2xl text-slate-800">00</span>
                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-medium mt-0.5">Hari</span>
                    </div>
                    <div class="bg-white rounded-xl py-2.5 px-2 border border-slate-200/70 shadow-sm">
                        <span id="hours" class="font-heading font-bold text-xl sm:text-2xl text-slate-800">00</span>
                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-medium mt-0.5">Jam</span>
                    </div>
                    <div class="bg-white rounded-xl py-2.5 px-2 border border-slate-200/70 shadow-sm">
                        <span id="minutes" class="font-heading font-bold text-xl sm:text-2xl text-slate-800">00</span>
                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-medium mt-0.5">Menit</span>
                    </div>
                    <div class="bg-white rounded-xl py-2.5 px-2 border border-slate-200/70 shadow-sm">
                        <span id="seconds" class="font-heading font-bold text-xl sm:text-2xl text-emerald-600">00</span>
                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-medium mt-0.5">Detik</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <button onclick="window.location.reload()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-xl shadow-sm transition active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Cek / Muat Ulang</span>
                </button>
                
                <a href="{{ route('login') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-xl transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span>Login Administrator</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="p-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} {{ setting('company_name', 'PT Herbatech Innopharma') }} — {{ setting('app_name', 'Herbatech R&D Management System') }}. All rights reserved.</p>
    </footer>

    <!-- Countdown Script -->
    <script>
        (function() {
            const wrapper = document.getElementById('countdown-wrapper');
            if (!wrapper) return;

            const targetStr = wrapper.dataset.target;
            if (!targetStr) return;

            const targetDate = new Date(targetStr).getTime();
            if (isNaN(targetDate)) return;

            const daysEl = document.getElementById('days');
            const hoursEl = document.getElementById('hours');
            const minutesEl = document.getElementById('minutes');
            const secondsEl = document.getElementById('seconds');

            function updateCountdown() {
                const now = new Date().getTime();
                const distance = targetDate - now;

                if (distance < 0) {
                    if (daysEl) daysEl.innerText = "00";
                    if (hoursEl) hoursEl.innerText = "00";
                    if (minutesEl) minutesEl.innerText = "00";
                    if (secondsEl) secondsEl.innerText = "00";
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                if (daysEl) daysEl.innerText = String(days).padStart(2, '0');
                if (hoursEl) hoursEl.innerText = String(hours).padStart(2, '0');
                if (minutesEl) minutesEl.innerText = String(minutes).padStart(2, '0');
                if (secondsEl) secondsEl.innerText = String(seconds).padStart(2, '0');
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        })();
    </script>
</body>
</html>
