@php
    // Expects: $user (authenticated parent), $patients (collection of children)
    // Provide an API endpoint returning JSON: [{id,name,available,threshold(optional)}]
    // Example JSON shape for /api/vaccine-stocks:
    // [
    //   {"vaccine":"Hep B","stock":120,"unit":"doses","updated_at":"2025-09-11 09:32:00"},
    //   {"vaccine":"BCG","stock":18,"unit":"doses"},
    //   {"vaccine":"OPV","stock":42,"unit":"doses"}
    // ]
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Parent Dashboard</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link href="{{ asset('css/tailwind-full.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="{{ asset('javascript/logout-helper.js') }}"></script>
    <script src="{{ asset('javascript/session-guard.js') }}" defer></script>
    {{-- Removed aggressive immediate session check that was racing with login --}}
    {{-- The middleware already protects this route, no need for JavaScript check --}}
    <style>
        /* Subtle scroll fade for sections */
        [data-animate]{opacity:0;transform:translateY(16px);transition:.55s cubic-bezier(.4,.7,.2,1);will-change:opacity,transform;}
        [data-animate].in{opacity:1;transform:none;}
        @media (prefers-reduced-motion:reduce){
            [data-animate]{opacity:1 !important;transform:none !important;transition:none !important;}
        }
        .glass {background:rgba(255,255,255,.85);backdrop-filter:blur(14px);}
        .dark .glass {background:rgba(34,36,48,.72);}
    </style>
    <script>
        // Dark mode disabled temporarily. Previous init kept for reference:
        /* (() => {
            const s = localStorage.getItem('theme');
            if(s === 'dark') document.documentElement.classList.add('dark');
        })(); */
    </script>
</head>
<body class="min-h-full bg-gray-50 dark:bg-[#16161d] text-gray-800 dark:text-gray-200 font-sans flex flex-col">

    <!-- Top Bar -->
    <header class="w-full bg-gradient-to-r from-primary-700 to-primary-600 dark:from-primary-800 dark:to-primary-700 text-white shadow-lg relative z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-5 h-16 sm:h-20 flex items-center justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-full bg-white/15 flex items-center justify-center ring-1 ring-white/25 flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3.5 3.5M4 12a8 8 0 1116 0 8 8 0 01-16 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm leading-tight opacity-80">Welcome</p>
                    <h1 class="text-base sm:text-lg font-semibold tracking-tight truncate">{{ $user->patients->first()->mother_name ?? $user->username ?? 'Parent' }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <!-- Notification Dropdown -->
                <div class="relative" id="notificationDropdown">
                    <button id="notificationBtn" class="relative h-9 w-9 sm:h-10 sm:w-10 rounded-lg bg-white/15 hover:bg-white/25 flex items-center justify-center transition-colors" aria-label="Notifications">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span id="notifBadge" class="absolute -top-1 -right-1 h-4 w-4 sm:h-5 sm:w-5 rounded-full bg-red-500 text-white text-[9px] sm:text-[10px] font-semibold flex items-center justify-center shadow-sm hidden">0</span>
                    </button>
                    <!-- Notification Dropdown Menu - Compact with scroll -->
                    <div id="notificationMenu" class="hidden absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-2xl ring-1 ring-gray-200 overflow-hidden z-50">
                        <!-- Header -->
                        <div class="px-4 py-3 border-b border-gray-200 bg-primary-50 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                            <div class="flex items-center gap-2">
                                <button id="clearReadBtn" class="text-xs text-gray-500 hover:text-red-600 font-medium whitespace-nowrap transition" title="Clear read notifications">
                                    <svg class="w-3.5 h-3.5 inline-block mr-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Clear read
                                </button>
                                <span class="text-gray-300">|</span>
                                <button id="markAllReadBtn" class="text-xs text-primary-600 hover:text-primary-700 font-medium whitespace-nowrap">Mark all as read</button>
                            </div>
                        </div>
                        <!-- Scrollable Notification List -->
                        <div id="notificationList" class="max-h-80 overflow-y-auto overscroll-contain">
                            <div class="p-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.143 17.082a24.248 24.248 0 003.844.148m-3.844-.148a23.856 23.856 0 01-5.455-1.31 8.964 8.964 0 002.3-5.542m3.155 6.852a3 3 0 005.667 1.97m1.965-2.277L21 21m-4.225-4.225a23.81 23.81 0 003.536-1.003A8.967 8.967 0 0118 9.75V9A6 6 0 006.53 6.53m10.245 10.245L6.53 6.53M3 3l3.53 3.53"/>
                                </svg>
                                <p class="text-sm">No notifications</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Theme toggle removed (dark mode disabled) -->
                {{-- <button id="themeToggle" class="hidden sm:inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 transition text-xs font-medium">Toggle</button> --}}

                <!-- Profile dropdown -->
                <div class="relative" id="profileWrapper">
                    <button id="profileBtn" class="flex items-center gap-2 sm:gap-3 bg-white/10 hover:bg-white/20 rounded-xl pl-1.5 sm:pl-2 pr-2 sm:pr-3 py-1 sm:py-1.5 transition focus:outline-none focus:ring-2 focus:ring-white/40">
                        <img src="{{ asset('images/account.png') }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover ring-2 ring-white/30" alt="Profile">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 opacity-80 hidden sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div id="profileMenu" class="hidden absolute right-0 top-full translate-y-2 w-56 bg-white rounded-xl shadow-2xl ring-1 ring-black/10 overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-sm font-semibold truncate text-gray-800">{{ $user->patients->first()->mother_name ?? $user->username ?? 'Parent' }}</p>
                            <p class="text-xs text-gray-500">Parent Account</p>
                        </div>
                        <nav class="py-1 text-sm text-gray-700">
                            <a href="{{ route('parents.profile') }}" class="flex items-center gap-2 px-4 py-2.5 sm:py-2 hover:bg-primary-50 transition text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12c2.761 0 5-2.462 5-5.5S14.761 1 12 1 7 3.462 7 6.5 9.239 12 12 12z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 21c0-3.313 3.582-6 8-6s8 2.687 8 6"/>
                                </svg>
                                Profile
                            </a>
                            <a href="{{ route('parents.change-password') }}" class="flex items-center gap-2 px-4 py-2.5 sm:py-2 hover:bg-primary-50 transition text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="4" y="11" width="16" height="11" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0v4"/>
                                </svg>
                                Change Password
                            </a>
                            <button id="logoutBtn" class="w-full flex items-center gap-2 px-4 py-2.5 sm:py-2 text-left hover:bg-primary-50 transition text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3M12 8l4 4-4 4"/>
                                </svg>
                                Logout
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-5 py-6 sm:py-10 space-y-8 sm:space-y-14">

        <!-- Children Section -->
        <section data-animate>
            <div class="flex items-center justify-between flex-wrap gap-3 sm:gap-4 mb-4 sm:mb-6">
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-800 dark:text-gray-100">Children</h2>
                <div class="flex gap-2 sm:gap-3">
                    <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-[#222633] ring-1 ring-gray-200 dark:ring-white/10 text-xs font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Data Synced</span>
                    </div>
                    <!-- Placeholder for future "Add Child" (commented if not used) -->
                    {{-- <a href="{{ route('child.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary-600 text-white px-4 py-2 text-sm font-semibold hover:bg-primary-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16M4 12h16"/></svg>
                        Add Child
                    </a> --}}
                </div>
            </div>

            @if($patients->count())
                <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($patients as $patient)
                        <div class="group cursor-pointer relative rounded-xl sm:rounded-2xl bg-white dark:bg-[#222633] ring-1 ring-gray-200 dark:ring-white/10 p-4 sm:p-5 shadow-sm hover:shadow-md hover:ring-primary-300 dark:hover:ring-primary-500 transition"
                             data-patient-id="{{ $patient->id }}">
                            <div class="flex items-start justify-between">
                                <h3 class="font-semibold text-base sm:text-lg text-gray-800 dark:text-gray-100 leading-snug line-clamp-2">
                                    {{ $patient->name }}
                                </h3>
                                <span class="ml-2 inline-flex items-center justify-center h-7 w-7 sm:h-8 sm:w-8 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-300 font-semibold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($patient->name,0,2)) }}
                                </span>
                            </div>
                            <div class="mt-3 sm:mt-4 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12s3-7 9-7 9 7 9 7-3 7-9 7-9-7-9-7z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                View record
                            </div>
                            <span class="absolute inset-0 rounded-xl sm:rounded-2xl ring-2 ring-transparent group-hover:ring-primary-400/60 dark:group-hover:ring-primary-500/60 transition pointer-events-none"></span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl sm:rounded-2xl bg-white dark:bg-[#222633] ring-1 ring-gray-200 dark:ring-white/10 px-4 sm:px-6 py-8 sm:py-12 text-center">
                    <p class="text-gray-600 dark:text-gray-400 text-sm">No children found.</p>
                </div>
            @endif
        </section>

        <!-- Real-Time Vaccine Stocks -->
        <section data-animate id="stocksSection">
            <div class="flex items-start sm:items-center justify-between flex-wrap gap-3 sm:gap-4 mb-4 sm:mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-800 dark:text-gray-100">Real-Time Vaccine Stocks</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Live availability (auto refresh every 30s).</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    <button id="refreshStocks" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold px-3 sm:px-4 py-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6"/><path stroke-linecap="round" stroke-linejoin="round" d="M20 9A8 8 0 006 5.3M4 15a8 8 0 0014 3.7"/>
                        </svg>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                    <button id="toggleStocksView" class="inline-flex items-center gap-1 rounded-lg bg-white dark:bg-[#222633] ring-1 ring-gray-200 dark:ring-white/10 text-xs font-medium px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/10 transition">
                        <svg id="iconGrid" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zM14 4h6v6h-6zM14 14h6v6h-6zM4 14h6v6H4z"/></svg>
                        <span id="stocksViewLabel" class="hidden sm:inline">Cards</span>
                    </button>
                </div>
            </div>

            <!-- Loading skeleton -->
            <div id="stocksLoading" class="grid gap-3 sm:gap-5 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                @for($i=0;$i<6;$i++)
                    <div class="h-28 sm:h-36 rounded-xl sm:rounded-2xl bg-gradient-to-br from-gray-200 to-gray-100 dark:from-gray-700 dark:to-gray-600 animate-pulse"></div>
                @endfor
            </div>

            <!-- Cards container -->
            <div id="stocksCards" class="hidden grid gap-3 sm:gap-5 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3"></div>

            <!-- Table container with horizontal scroll on mobile -->
            <div id="stocksTableWrapper" class="hidden rounded-xl sm:rounded-2xl bg-white dark:bg-[#222633] ring-1 ring-gray-200 dark:ring-white/10 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-[#2c3142] text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold whitespace-nowrap">Vaccine</th>
                            <th class="text-left px-5 py-3 font-semibold whitespace-nowrap">Stock</th>
                            <th class="text-left px-5 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="text-left px-5 py-3 font-semibold whitespace-nowrap">Updated</th>
                        </tr>
                    </thead>
                    <tbody id="stocksTableBody" class="divide-y divide-gray-200 dark:divide-white/10"></tbody>
                </table>
            </div>

            <p id="stocksError" class="hidden mt-4 text-sm font-medium text-red-600 dark:text-red-400"></p>
        </section>

    </main>

    <!-- Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-sm glass rounded-xl sm:rounded-2xl shadow-xl ring-1 ring-black/10 dark:ring-white/10 p-5 sm:p-7">
            <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 text-gray-800 dark:text-gray-100">Logout</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">Are you sure you want to log out?</p>
            <div class="mt-5 sm:mt-6 flex justify-end gap-2 sm:gap-3">
                <button id="cancelLogout" class="px-3 sm:px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-gray-200 transition">Cancel</button>
                <button id="confirmLogout" class="px-3 sm:px-4 py-2 rounded-lg text-sm font-semibold bg-red-600 hover:bg-red-700 text-white transition">Logout</button>
            </div>
        </div>
    </div>

    <!-- Hidden Logout Form -->
    <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <footer class="mt-10 pb-8 text-center text-xs text-gray-500 dark:text-gray-400">
        &copy; {{ date('Y') }} Parent Dashboard
    </footer>

    <script>
        // Intersection animations
        const animEls = [...document.querySelectorAll('[data-animate]')];
        if('IntersectionObserver' in window){
            const io = new IntersectionObserver(es => es.forEach(e=>{
                if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); }
            }),{threshold:.15});
            animEls.forEach(el=>io.observe(el));
        } else animEls.forEach(el=>el.classList.add('in'));

    // Theme toggle disabled (dark mode removed)
    // document.getElementById('themeToggle')?.addEventListener('click', ()=>{});

        // Profile dropdown
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');
        
        profileBtn.addEventListener('click', e=>{
            e.stopPropagation();
            // Close notification menu if open
            const notificationMenu = document.getElementById('notificationMenu');
            if (notificationMenu && !notificationMenu.classList.contains('hidden')) {
                notificationMenu.classList.add('hidden');
            }
            profileMenu.classList.toggle('hidden');
        });
        
        window.addEventListener('click', e=>{
            // Close profile menu
            if(!profileMenu.classList.contains('hidden') && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)){
                profileMenu.classList.add('hidden');
            }
        });

        // Logout modal
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutModal = document.getElementById('logoutModal');
        const cancelLogout = document.getElementById('cancelLogout');
        const confirmLogout = document.getElementById('confirmLogout');

        logoutBtn.addEventListener('click', ()=>{
            profileMenu.classList.add('hidden');
            logoutModal.classList.remove('hidden');
            logoutModal.classList.add('flex');
        });
        cancelLogout.addEventListener('click', ()=> {
            logoutModal.classList.add('hidden');
            logoutModal.classList.remove('flex');
        });
        confirmLogout.addEventListener('click', ()=>{
            // Mark user as logged out for session guard
            if (window.markUserLoggedOut) {
                window.markUserLoggedOut();
            }
            
            // Clear notification system
            if (window.notificationSystem) {
                window.notificationSystem.stopPolling();
            }
            
            // Submit logout form (POST request)
            document.getElementById('logoutForm').submit();
        });

        // Child card navigation
        document.querySelectorAll('[data-patient-id]').forEach(card=>{
            card.addEventListener('click',()=>{
                const id = card.getAttribute('data-patient-id');
                if(id) window.location.href = '/infantsRecord/' + id;
            });
        });

        // Stocks logic
        const stocksAPI = '{{ url("/api/vaccine-stocks") }}'; // implement this route returning JSON
        const loading = document.getElementById('stocksLoading');
        const cards = document.getElementById('stocksCards');
        const tableWrap = document.getElementById('stocksTableWrapper');
        const tableBody = document.getElementById('stocksTableBody');
        const errorBox = document.getElementById('stocksError');
        const refreshBtn = document.getElementById('refreshStocks');
        const toggleViewBtn = document.getElementById('toggleStocksView');
        const viewLabel = document.getElementById('stocksViewLabel');
        const iconGrid = document.getElementById('iconGrid');
        let currentView = 'cards';
        let pollTimer;
        let abortCtrl;

        function classify(stock){
            if(stock === null || stock === undefined) return {label:'Unknown', color:'#6b7280', ring:'ring-gray-400'};
            if(stock < 20) return {label:'Low', color:'#ef4444', ring:'ring-red-400'};
            if(stock < 50) return {label:'Moderate', color:'#f59e0b', ring:'ring-amber-400'};
            return {label:'High', color:'#10b981', ring:'ring-emerald-400'};
        }

        function formatTime(ts){
            if(!ts) return '—';
            return new Date(ts.replace(' ','T')).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
        }

        async function loadStocks(manual=false){
            if(abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();
            errorBox.classList.add('hidden');
            if(manual) loading.classList.remove('hidden');
            try {
                const res = await fetch(stocksAPI, {signal: abortCtrl.signal, headers:{'Accept':'application/json'}});
                if(!res.ok) throw new Error('HTTP '+res.status);
                const data = await res.json();
                renderStocks(data);
            } catch(e){
                errorBox.textContent = 'Unable to load vaccine stocks. ' + (e.name === 'AbortError' ? '' : 'Retry shortly.');
                errorBox.classList.remove('hidden');
            } finally {
                loading.classList.add('hidden');
            }
        }

        function renderStocks(response){
            // Extract data from API Resource format
            const data = response.data || response; // Support both old and new format
            
            // Cards
            cards.innerHTML = '';
            data.forEach(item=>{
                const cls = classify(item.stock);
                // Calculate percentage based on stock levels (0-100 scale)
                // Low (0-19): 0-40%, Moderate (20-49): 40-75%, Adequate (50+): 75-100%
                let pct = 0;
                if(item.stock >= 50) {
                    pct = Math.min(100, 75 + ((item.stock - 50) / 50 * 25));
                } else if(item.stock >= 20) {
                    pct = 40 + ((item.stock - 20) / 30 * 35);
                } else if(item.stock > 0) {
                    pct = (item.stock / 20) * 40;
                }
                pct = Math.round(pct);
                
                cards.insertAdjacentHTML('beforeend', `
                    <div class="rounded-xl sm:rounded-2xl bg-white dark:bg-[#222633] ring-1 ring-gray-200 dark:ring-white/10 p-3 sm:p-5 flex flex-col gap-2 sm:gap-4 shadow-sm hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-2 sm:gap-4">
                            <h4 class="font-semibold text-sm sm:text-base text-gray-800 dark:text-gray-100 leading-snug line-clamp-2">${item.vaccine ?? 'Unknown'}</h4>
                            <span class="inline-flex items-center gap-1 text-[8px] sm:text-[10px] font-semibold uppercase tracking-wide px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-md bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300 flex-shrink-0">${cls.label}</span>
                        </div>
                        <div class="flex items-end gap-1 sm:gap-2">
                            <p class="text-lg sm:text-2xl font-bold text-primary-600 dark:text-primary-300">${item.stock ?? '—'}</p>
                            <span class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 mb-0.5 sm:mb-1">${item.unit ?? 'doses'}</span>
                        </div>
                        <div class="h-1.5 sm:h-2 rounded-full bg-gray-200 dark:bg-white/10 overflow-hidden">
                            <div class="h-full transition-all" style="width:${pct}%; background-color:${cls.color};"></div>
                        </div>
                        <p class="text-[9px] sm:text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3"/>
                            </svg> <span class="hidden sm:inline">Updated</span> ${formatTime(item.updated_at)}
                        </p>
                    </div>
                `);
            });

            // Table
            tableBody.innerHTML = '';
            data.forEach(item=>{
                const cls = classify(item.stock);
                tableBody.insertAdjacentHTML('beforeend', `
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                        <td class="px-5 py-3 font-medium text-gray-800 dark:text-gray-100 whitespace-nowrap">${item.vaccine ?? 'Unknown'}</td>
                        <td class="px-5 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">${item.stock ?? '—'} <span class="text-xs opacity-60">${item.unit ?? 'doses'}</span></td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wide px-2 py-1 rounded-md text-white" style="background-color:${cls.color};">${cls.label}</span>
                        </td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">${formatTime(item.updated_at)}</td>
                    </tr>
                `);
            });

            // Show chosen view
            showStocksView();
        }

        function showStocksView(){
            if(currentView === 'cards'){
                cards.classList.remove('hidden');
                tableWrap.classList.add('hidden');
                viewLabel.textContent = 'Table';
            } else {
                tableWrap.classList.remove('hidden');
                cards.classList.add('hidden');
                viewLabel.textContent = 'Cards';
            }
        }

        toggleViewBtn.addEventListener('click', ()=>{
            currentView = currentView === 'cards' ? 'table' : 'cards';
            showStocksView();
        });

        refreshBtn.addEventListener('click', ()=> loadStocks(true));

        function startPolling(){
            clearInterval(pollTimer);
            pollTimer = setInterval(loadStocks, 30000);
        }

        loadStocks();
        startPolling();

        // Clean up on page unload
        window.addEventListener('beforeunload', ()=>{ clearInterval(pollTimer); if(abortCtrl) abortCtrl.abort(); });
    </script>

    <!-- Notification System -->
    <script src="{{ asset('javascript/notifications.js') }}?v={{ time() }}"></script>

    <!-- PWA and Push Notifications -->
    {{-- <script src="{{ asset('javascript/pwa.js') }}"></script> --}}
    
    <!-- Firebase Cloud Messaging (FCM) - NEW -->
    <script src="{{ asset('javascript/fcm.js') }}"></script>
</body>
</html>