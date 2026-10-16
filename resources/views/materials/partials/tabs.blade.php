@php
    $unmappedProcurementCount = \App\Models\ProcurementVendorPrice::where('sync_status', 'unmapped')->count();
@endphp

<div class="flex items-center gap-1 sm:gap-2 border-b border-gray-250 mb-6 overflow-x-auto">
    <a href="{{ route('materials.index') }}"
       class="px-4 py-2.5 border-b-2 text-sm font-medium whitespace-nowrap transition flex items-center gap-2 {{ request()->routeIs('materials.*') ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-ink' }}">
        <svg class="w-4 h-4 {{ request()->routeIs('materials.*') ? 'text-primary' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
        </svg>
        Bahan Baku (Materials)
    </a>

    <a href="{{ route('suppliers.index') }}"
       class="px-4 py-2.5 border-b-2 text-sm font-medium whitespace-nowrap transition flex items-center gap-2 {{ request()->routeIs('suppliers.*') ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-ink' }}">
        <svg class="w-4 h-4 {{ request()->routeIs('suppliers.*') ? 'text-primary' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Pemasok (Suppliers)
    </a>

    <a href="{{ route('material-suppliers.index') }}"
       class="px-4 py-2.5 border-b-2 text-sm font-medium whitespace-nowrap transition flex items-center gap-2 {{ request()->routeIs('material-suppliers.*') ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-ink' }}">
        <svg class="w-4 h-4 {{ request()->routeIs('material-suppliers.*') ? 'text-primary' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Harga Bahan Pemasok
    </a>

    <a href="{{ route('procurement-prices.index') }}"
       class="px-4 py-2.5 border-b-2 text-sm font-medium whitespace-nowrap transition flex items-center gap-2 {{ request()->routeIs('procurement-prices.*') ? 'border-sky-600 text-sky-700 font-bold' : 'border-transparent text-gray-500 hover:text-sky-700' }}">
        <svg class="w-4 h-4 {{ request()->routeIs('procurement-prices.*') ? 'text-sky-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        Integrasi Procurement (API)
        @if($unmappedProcurementCount > 0)
        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700" title="{{ $unmappedProcurementCount }} item perlu pemetaan">
            {{ $unmappedProcurementCount }}
        </span>
        @endif
    </a>
</div>
