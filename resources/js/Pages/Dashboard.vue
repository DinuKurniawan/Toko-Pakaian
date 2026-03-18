<script setup>
import { Head, usePage, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value.role === "admin");

// Admin props
const totalProducts = computed(() => page.props.totalProducts ?? 0);
const totalUsers = computed(() => page.props.totalUsers ?? 0);
const totalCategories = computed(() => page.props.totalCategories ?? 0);
const totalCartItems = computed(() => page.props.totalCartItems ?? 0);
const latestProducts = computed(() => page.props.latestProducts ?? []);
const recentUsers = computed(() => page.props.recentUsers ?? []);
const productsByCategory = computed(() => page.props.productsByCategory ?? []);
const featuredProducts = computed(() => page.props.featuredProducts ?? 0);
const totalOrders = computed(() => page.props.totalOrders ?? 0);
const pendingOrders = computed(() => page.props.pendingOrders ?? 0);
const totalRevenue = computed(() => page.props.totalRevenue ?? 0);
const recentOrders = computed(() => page.props.recentOrders ?? []);
const pendingReviews = computed(() => page.props.pendingReviews ?? 0);
const lowStockProducts = computed(() => page.props.lowStockProducts ?? []);
const stalePendingOrdersCount = computed(
    () => page.props.stalePendingOrdersCount ?? 0,
);
const stalePendingRevenue = computed(
    () => page.props.stalePendingRevenue ?? 0,
);
const stalePendingDays = computed(() => page.props.stalePendingDays ?? 3);
const topCustomers = computed(() => page.props.topCustomers ?? []);
const repeatCustomersCount = computed(
    () => page.props.repeatCustomersCount ?? 0,
);
const averageOrderValue = computed(() => page.props.averageOrderValue ?? 0);

// User props
const cartItems = computed(() => page.props.cartItems ?? []);
const cartTotal = computed(() => page.props.cartTotal ?? 0);
const userTotalOrders = computed(() => page.props.totalOrders ?? 0);
const totalSpent = computed(() => page.props.totalSpent ?? 0);
const userRecentOrders = computed(() => page.props.recentOrders ?? []);
const wishlistCount = computed(() => page.props.wishlistCount ?? 0);
const orderStatusSummary = computed(() => page.props.orderStatusSummary ?? {});
const promoNotifications = computed(() => page.props.promoNotifications ?? []);
const wishlistAlerts = computed(() => page.props.wishlistAlerts ?? []);

// UI state
const isSidebarOpen = ref(false);
const currentTime = ref(new Date());

// Update clock
setInterval(() => {
    currentTime.value = new Date();
}, 60000);

const greeting = computed(() => {
    const hour = currentTime.value.getHours();
    if (hour < 12) return "Selamat Pagi";
    if (hour < 15) return "Selamat Siang";
    if (hour < 18) return "Selamat Sore";
    return "Selamat Malam";
});

const formattedDate = computed(() => {
    return currentTime.value.toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
};

const getProductImageUrl = (path) => {
    if (!path) return "https://placehold.co/320x420?text=No+Image";
    if (path.startsWith("http")) return path;
    if (path.startsWith("/storage/")) return path;
    if (path.startsWith("storage/")) return `/${path}`;
    return `/storage/${path}`;
};

const formatTimeAgo = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return "Baru saja";
    if (diffMins < 60) return `${diffMins} menit lalu`;
    if (diffHours < 24) return `${diffHours} jam lalu`;
    if (diffDays < 7) return `${diffDays} hari lalu`;
    return date.toLocaleDateString("id-ID", { day: "numeric", month: "short" });
};

const cartSubtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => {
        return sum + (item.product?.price ?? 0) * item.quantity;
    }, 0);
});

const userOrderStatusCards = computed(() => [
    {
        key: "pending",
        label: "Menunggu",
        total: Number(orderStatusSummary.value.pending ?? 0),
        class: "border-amber-100 bg-amber-50 text-amber-700",
    },
    {
        key: "processing",
        label: "Diproses",
        total: Number(orderStatusSummary.value.processing ?? 0),
        class: "border-blue-100 bg-blue-50 text-blue-700",
    },
    {
        key: "shipped",
        label: "Dikirim",
        total: Number(orderStatusSummary.value.shipped ?? 0),
        class: "border-indigo-100 bg-indigo-50 text-indigo-700",
    },
    {
        key: "completed",
        label: "Selesai",
        total: Number(orderStatusSummary.value.completed ?? 0),
        class: "border-emerald-100 bg-emerald-50 text-emerald-700",
    },
]);

const totalAvailableStock = (product) => Number(product?.stock_summary?.total ?? 0);
const lowestAvailableStock = (product) => Number(product?.stock_summary?.lowest ?? 0);
const formatVoucherReward = (voucher) =>
    voucher.type === "percent"
        ? `${voucher.value}% OFF`
        : `${formatPrice(voucher.value)} OFF`;

// User sidebar navigation
const userNav = [
    {
        label: "Dashboard",
        href: route("dashboard"),
        icon: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
        active: true,
    },
    {
        label: "Pesanan Saya",
        href: route("orders.index"),
        icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01",
        active: false,
    },
    {
        label: "Wishlist",
        href: route("wishlist.index"),
        icon: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z",
        active: false,
    },
    {
        label: "Keranjang",
        href: route("cart.index"),
        icon: "M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z",
        active: false,
    },
];
</script>

<template>
    <Head title="Dashboard | LUMIERE" />

    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900">
        <!-- Admin Sidebar -->
        <AdminSidebar
            v-if="isAdmin"
            :is-sidebar-open="isSidebarOpen"
            active-page="dashboard"
            @close-sidebar="isSidebarOpen = false"
        />

        <!-- User Sidebar Mobile Overlay -->
        <div
            v-if="!isAdmin && isSidebarOpen"
            @click="isSidebarOpen = false"
            class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden"
        ></div>

        <!-- User Sidebar -->
        <aside
            v-if="!isAdmin"
            :class="[
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transition-transform duration-300 lg:translate-x-0 lg:static lg:flex-shrink-0 flex flex-col',
            ]"
        >
            <div class="h-16 flex items-center px-6 border-b border-gray-100">
                <Link
                    href="/"
                    class="text-2xl font-bold tracking-tighter text-gray-900"
                >
                    LUMIÈRE<span class="text-indigo-600">.</span>
                </Link>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                <div
                    class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3 mt-2"
                >
                    Menu Utama
                </div>
                <Link
                    v-for="item in userNav"
                    :key="item.label"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors"
                    :class="
                        item.active
                            ? 'bg-indigo-50 text-indigo-700'
                            : 'text-gray-600 hover:bg-gray-50'
                    "
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            :d="item.icon"
                        />
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <div class="p-4 border-t border-gray-100">
                <div class="flex items-center gap-3 px-3 py-2 mb-3">
                    <div
                        class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold"
                    >
                        {{ user.name.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ user.name }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ user.email }}
                        </p>
                    </div>
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                        />
                    </svg>
                    Keluar
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Topbar -->
            <header
                class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-4">
                    <button
                        @click="isSidebarOpen = true"
                        class="lg:hidden text-gray-500 hover:text-gray-900"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Dashboard
                    </h2>
                </div>
                <Link
                    href="/"
                    class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors"
                >
                    Kembali ke Toko &rarr;
                </Link>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <!-- ==================== ADMIN DASHBOARD ==================== -->
                <div v-if="isAdmin">
                    <!-- Welcome -->
                    <div class="mb-8">
                        <h1
                            class="text-2xl sm:text-3xl font-bold text-gray-900"
                        >
                            {{ greeting }}, {{ user.name.split(" ")[0] }}
                        </h1>
                        <p class="text-gray-500 mt-1">
                            Berikut ringkasan performa toko LUMIERE Anda.
                        </p>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-6">
                        <!-- Total Revenue -->
                        <div class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-300">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-11 h-11 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <Link :href="route('admin.reports.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Laporan</Link>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ formatPrice(totalRevenue) }}</p>
                            <p class="text-sm text-gray-500 mt-1">Total Pendapatan</p>
                        </div>

                        <!-- Total Orders -->
                        <div class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-amber-100 transition-all duration-300">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-11 h-11 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                </div>
                                <Link :href="route('admin.orders.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Lihat</Link>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 tabular-nums">{{ totalOrders }}</p>
                            <p class="text-sm text-gray-500 mt-1">Total Pesanan</p>
                            <p v-if="pendingOrders > 0" class="text-xs text-amber-600 font-medium mt-1">{{ pendingOrders }} menunggu konfirmasi</p>
                        </div>

                        <!-- Total Produk -->
                        <div class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-100 transition-all duration-300">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-11 h-11 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                </div>
                                <Link :href="route('admin.products.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Kelola</Link>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 tabular-nums">{{ totalProducts }}</p>
                            <p class="text-sm text-gray-500 mt-1">Total Produk</p>
                        </div>

                        <!-- Pelanggan -->
                        <div class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-rose-100 transition-all duration-300">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-11 h-11 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <Link :href="route('admin.users.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Lihat</Link>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 tabular-nums">{{ totalUsers }}</p>
                            <p class="text-sm text-gray-500 mt-1">Pelanggan Terdaftar</p>
                        </div>
                    </div>

                    <!-- Alert Banners (pending reviews + low stock) -->
                    <div class="space-y-3 mb-6">
                        <div v-if="stalePendingOrdersCount > 0" class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <div>
                                    <p class="text-sm font-semibold text-red-800">
                                        {{ stalePendingOrdersCount }} pesanan tertahan lebih dari {{ stalePendingDays }} hari
                                    </p>
                                    <p class="text-sm text-red-700 mt-1">
                                        Nilai transaksi yang butuh ditindaklanjuti mencapai {{ formatPrice(stalePendingRevenue) }}.
                                    </p>
                                </div>
                            </div>
                            <Link :href="route('admin.orders.index', { status: 'pending' })" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition-colors">
                                Prioritaskan Pesanan Pending
                            </Link>
                        </div>

                        <div v-if="pendingReviews > 0" class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <p class="text-sm font-medium text-amber-800">Ada <strong>{{ pendingReviews }}</strong> ulasan menunggu moderasi</p>
                            </div>
                            <Link :href="route('admin.reviews.index')" class="text-sm font-semibold text-amber-700 hover:text-amber-800">Review &rarr;</Link>
                        </div>
                        <div v-if="lowStockProducts.length > 0" class="bg-red-50 border border-red-200 rounded-2xl px-5 py-3">
                            <div class="flex items-center gap-3 mb-2">
                                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <p class="text-sm font-semibold text-red-800">Stok menipis! Prioritaskan restock produk ini.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link
                                    v-for="p in lowStockProducts"
                                    :key="p.id"
                                    :href="route('admin.products.edit', p.id)"
                                    class="text-xs bg-white border border-red-200 text-red-700 px-3 py-1.5 rounded-full font-medium hover:bg-red-100 transition-colors"
                                >
                                    {{ p.name }}
                                    <span class="text-red-500">
                                        · min {{ lowestAvailableStock(p) }} / total {{ totalAvailableStock(p) }}
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">
                                        Customer Insights
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Ringkasan pelanggan paling bernilai.
                                    </p>
                                </div>
                                <Link
                                    :href="route('admin.users.index')"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                >
                                    Lihat User
                                </Link>
                            </div>

                            <div class="mt-6 space-y-4">
                                <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4">
                                    <p class="text-sm font-medium text-indigo-700">
                                        Repeat Customer
                                    </p>
                                    <p class="mt-2 text-3xl font-bold text-indigo-900 tabular-nums">
                                        {{ repeatCustomersCount }}
                                    </p>
                                    <p class="mt-1 text-sm text-indigo-700">
                                        pelanggan dengan 2+ transaksi sukses
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                                    <p class="text-sm font-medium text-emerald-700">
                                        Average Order Value
                                    </p>
                                    <p class="mt-2 text-2xl font-bold text-emerald-900 tabular-nums">
                                        {{ formatPrice(averageOrderValue) }}
                                    </p>
                                    <p class="mt-1 text-sm text-emerald-700">
                                        rata-rata nilai pesanan sukses
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">
                                        Top Customer
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        Pelanggan dengan kontribusi omzet terbesar.
                                    </p>
                                </div>
                                <Link
                                    :href="route('admin.reports.index')"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                >
                                    Buka Laporan
                                </Link>
                            </div>

                            <div v-if="topCustomers.length > 0" class="divide-y divide-gray-100">
                                <div
                                    v-for="customer in topCustomers"
                                    :key="customer.user_id"
                                    class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">
                                            {{ customer.user?.name ?? "Pelanggan" }}
                                        </p>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ customer.user?.email ?? "Email tidak tersedia" }}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-3 text-sm">
                                        <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-3 py-1 font-medium text-gray-700">
                                            {{ customer.total_orders }} order
                                        </span>
                                        <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 font-semibold text-emerald-700">
                                            {{ formatPrice(customer.total_spent) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            order terakhir {{ formatTimeAgo(customer.last_order_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="p-8 text-center text-sm text-gray-500">
                                Belum ada cukup data transaksi untuk menghitung insight pelanggan.
                            </div>
                        </div>
                    </div>

                    <!-- Main Grid: Product Table + Category Breakdown -->
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
                        <!-- Latest Products Table -->
                        <div
                            class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                        >
                            <div
                                class="px-6 py-5 border-b border-gray-100 flex items-center justify-between"
                            >
                                <div>
                                    <h3
                                        class="text-base font-semibold text-gray-900"
                                    >
                                        Produk Terbaru
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        5 produk terakhir ditambahkan
                                    </p>
                                </div>
                                <Link
                                    :href="route('admin.products.index')"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition-colors"
                                    >Lihat Semua</Link
                                >
                            </div>

                            <div
                                v-if="latestProducts.length > 0"
                                class="overflow-x-auto"
                            >
                                <table
                                    class="min-w-full divide-y divide-gray-100"
                                >
                                    <thead>
                                        <tr class="bg-gray-50/60">
                                            <th
                                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                            >
                                                Produk
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                            >
                                                Kategori
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                            >
                                                Harga
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                            >
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <tr
                                            v-for="product in latestProducts"
                                            :key="product.id"
                                            class="hover:bg-gray-50/50 transition-colors"
                                        >
                                            <td class="px-6 py-4">
                                                <div
                                                    class="flex items-center gap-3"
                                                >
                                                    <div
                                                        class="h-10 w-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0"
                                                    >
                                                        <img
                                                            :src="
                                                                product.image.startsWith(
                                                                    'http',
                                                                )
                                                                    ? product.image
                                                                    : '/storage/' +
                                                                      product.image
                                                            "
                                                            :alt="product.name"
                                                            class="h-full w-full object-cover"
                                                        />
                                                    </div>
                                                    <span
                                                        class="text-sm font-medium text-gray-900 line-clamp-1"
                                                        >{{
                                                            product.name
                                                        }}</span
                                                    >
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700"
                                                >
                                                    {{ product.category }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-6 py-4 text-sm font-semibold text-gray-900 tabular-nums"
                                            >
                                                {{ formatPrice(product.price) }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    v-if="product.is_featured"
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-medium bg-amber-50 text-amber-700"
                                                >
                                                    <svg
                                                        class="w-3 h-3"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                                        />
                                                    </svg>
                                                    Unggulan
                                                </span>
                                                <span
                                                    v-else
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-50 text-gray-500"
                                                >
                                                    Reguler
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="p-12 text-center">
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-300"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                    />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">
                                    Belum ada produk.
                                </p>
                                <Link
                                    :href="route('admin.products.create')"
                                    class="mt-3 inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                    >Tambah Produk Pertama &rarr;</Link
                                >
                            </div>
                        </div>

                        <!-- Right Column: Category + Quick Actions -->
                        <div class="space-y-6">
                            <!-- Category Breakdown -->
                            <div
                                class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                            >
                                <div class="px-6 py-5 border-b border-gray-100">
                                    <h3
                                        class="text-base font-semibold text-gray-900"
                                    >
                                        Distribusi Kategori
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        Produk per kategori
                                    </p>
                                </div>
                                <div class="p-6">
                                    <div
                                        v-if="productsByCategory.length > 0"
                                        class="space-y-4"
                                    >
                                        <div
                                            v-for="(
                                                cat, index
                                            ) in productsByCategory"
                                            :key="cat.category"
                                        >
                                            <div
                                                class="flex items-center justify-between mb-1.5"
                                            >
                                                <span
                                                    class="text-sm font-medium text-gray-700"
                                                    >{{ cat.category }}</span
                                                >
                                                <span
                                                    class="text-sm font-semibold text-gray-900 tabular-nums"
                                                    >{{
                                                        cat.total
                                                    }}
                                                    produk</span
                                                >
                                            </div>
                                            <div
                                                class="w-full bg-gray-100 rounded-full h-2 overflow-hidden"
                                            >
                                                <div
                                                    class="h-full rounded-full transition-all duration-700 ease-out"
                                                    :class="[
                                                        index % 3 === 0
                                                            ? 'bg-indigo-500'
                                                            : index % 3 === 1
                                                              ? 'bg-emerald-500'
                                                              : 'bg-amber-500',
                                                    ]"
                                                    :style="{
                                                        width:
                                                            totalProducts > 0
                                                                ? (cat.total /
                                                                      totalProducts) *
                                                                      100 +
                                                                  '%'
                                                                : '0%',
                                                    }"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm text-gray-500 text-center py-4"
                                    >
                                        Belum ada data kategori.
                                    </p>
                                </div>
                            </div>

                            <!-- Quick Stats Card -->
                            <div
                                class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden"
                            >
                                <div
                                    class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-8 translate-x-8 blur-2xl"
                                ></div>
                                <div
                                    class="absolute bottom-0 left-0 w-24 h-24 bg-indigo-400/20 rounded-full translate-y-6 -translate-x-6 blur-xl"
                                ></div>
                                <div class="relative z-10">
                                    <p
                                        class="text-indigo-200 text-sm font-medium"
                                    >
                                        Produk Unggulan
                                    </p>
                                    <p
                                        class="text-4xl font-bold mt-1 tabular-nums"
                                    >
                                        {{ featuredProducts }}
                                    </p>
                                    <p class="text-indigo-200 text-sm mt-3">
                                        dari {{ totalProducts }} total produk
                                        ditandai sebagai unggulan
                                    </p>
                                    <Link
                                        :href="route('admin.products.index')"
                                        class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-white bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors"
                                    >
                                        Kelola Produk
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Users Table -->
                    <div
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                    >
                        <div
                            class="px-6 py-5 border-b border-gray-100 flex items-center justify-between"
                        >
                            <div>
                                <h3
                                    class="text-base font-semibold text-gray-900"
                                >
                                    Pelanggan Terbaru
                                </h3>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    5 pelanggan terakhir bergabung
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="recentUsers.length > 0"
                            class="overflow-x-auto"
                        >
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead>
                                    <tr class="bg-gray-50/60">
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                        >
                                            Pelanggan
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                        >
                                            Email
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                        >
                                            Bergabung
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr
                                        v-for="u in recentUsers"
                                        :key="u.id"
                                        class="hover:bg-gray-50/50 transition-colors"
                                    >
                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="h-9 w-9 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                                                >
                                                    {{
                                                        u.name
                                                            .charAt(0)
                                                            .toUpperCase()
                                                    }}
                                                </div>
                                                <span
                                                    class="text-sm font-medium text-gray-900"
                                                    >{{ u.name }}</span
                                                >
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500"
                                        >
                                            {{ u.email }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-500"
                                        >
                                            {{ formatTimeAgo(u.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="p-12 text-center">
                            <p class="text-sm text-gray-500">
                                Belum ada pelanggan terdaftar.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ==================== USER/CUSTOMER DASHBOARD ==================== -->
                <div v-else>
                    <!-- Welcome Banner -->
                    <div
                        class="mb-8 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl sm:rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden"
                    >
                        <div
                            class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -translate-y-12 translate-x-12 blur-2xl"
                        ></div>
                        <div
                            class="absolute bottom-0 left-1/3 w-32 h-32 bg-indigo-400/20 rounded-full translate-y-10 blur-xl"
                        ></div>

                        <div
                            class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6"
                        >
                            <div>
                                <p
                                    class="text-indigo-200 text-sm font-medium mb-1"
                                >
                                    {{ greeting }}
                                </p>
                                <h1
                                    class="text-2xl sm:text-3xl font-extrabold leading-tight"
                                >
                                    {{ user.name.split(" ")[0] }}
                                </h1>
                                <p
                                    class="text-indigo-100 mt-2 max-w-md text-sm sm:text-base"
                                >
                                    Selamat datang kembali di LUMIERE. Temukan
                                    koleksi terbaru yang cocok untuk gaya Anda.
                                </p>
                            </div>
                            <div class="shrink-0">
                                <Link
                                    href="/"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 font-semibold rounded-xl text-indigo-700 bg-white hover:bg-indigo-50 transition-all duration-200 shadow-md hover:shadow-lg text-sm"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                        />
                                    </svg>
                                    Mulai Belanja
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-6">
                        <div
                            v-for="card in userOrderStatusCards"
                            :key="card.key"
                            class="rounded-2xl border p-5 shadow-sm"
                            :class="card.class"
                        >
                            <p class="text-sm font-medium opacity-80">
                                {{ card.label }}
                            </p>
                            <p class="mt-2 text-3xl font-bold tabular-nums">
                                {{ card.total }}
                            </p>
                            <p class="mt-1 text-xs opacity-80">
                                status pesanan
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="Number(orderStatusSummary.pending ?? 0) > 0"
                        class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <p class="text-sm font-semibold text-amber-800">
                                Ada {{ orderStatusSummary.pending }} pesanan yang masih menunggu diproses.
                            </p>
                            <p class="mt-1 text-sm text-amber-700">
                                Anda bisa memantau detailnya atau membatalkan pesanan yang belum diproses dari halaman riwayat.
                            </p>
                        </div>
                        <Link
                            :href="route('orders.index')"
                            class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-white px-4 py-2 text-sm font-semibold text-amber-700 transition-colors hover:bg-amber-100"
                        >
                            Cek Riwayat Pesanan
                        </Link>
                    </div>

                    <!-- Quick Stats -->
                    <div
                        class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-5 mb-8"
                    >
                        <div
                            class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-100 transition-all duration-300 flex items-center gap-4"
                        >
                            <div
                                class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:bg-blue-100 transition-colors flex-shrink-0"
                            >
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    Keranjang
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 tabular-nums"
                                >
                                    {{ cartTotal }}
                                    <span
                                        class="text-sm font-normal text-gray-400"
                                        >item</span
                                    >
                                </p>
                            </div>
                        </div>

                        <div
                            class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-100 transition-all duration-300 flex items-center gap-4"
                        >
                            <div
                                class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:bg-emerald-100 transition-colors flex-shrink-0"
                            >
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    Total Pesanan
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 tabular-nums"
                                >
                                    {{ userTotalOrders }}
                                    <span class="text-sm font-normal text-gray-400">pesanan</span>
                                </p>
                            </div>
                        </div>

                        <Link
                            :href="route('wishlist.index')"
                            class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-rose-100 transition-all duration-300 flex items-center gap-4"
                        >
                            <div
                                class="w-12 h-12 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center group-hover:bg-rose-100 transition-colors flex-shrink-0"
                            >
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    Wishlist
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 tabular-nums"
                                >
                                    {{ wishlistCount }}
                                    <span class="text-sm font-normal text-gray-400">produk</span>
                                </p>
                            </div>
                        </Link>

                        <Link
                            href="/orders"
                            class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-300 flex items-center gap-4"
                        >
                            <div
                                class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:bg-indigo-100 transition-colors flex-shrink-0"
                            >
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    Total Dibelanjakan
                                </p>
                                <p
                                    class="text-xl font-bold text-gray-900 tabular-nums"
                                >
                                    {{ formatPrice(totalSpent) }}
                                </p>
                            </div>
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
                        <div class="xl:col-span-1 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-6 py-5 border-b border-gray-100">
                                <h3 class="text-base font-semibold text-gray-900">
                                    Promo Aktif
                                </h3>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    Kode promo yang siap dipakai pada checkout.
                                </p>
                            </div>

                            <div v-if="promoNotifications.length > 0" class="divide-y divide-gray-100">
                                <div
                                    v-for="voucher in promoNotifications"
                                    :key="voucher.id"
                                    class="px-6 py-4"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ voucher.code }}
                                            </p>
                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ voucher.description || "Voucher promo untuk pembelian berikutnya." }}
                                            </p>
                                        </div>
                                        <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            {{ formatVoucherReward(voucher) }}
                                        </span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1">
                                            min. belanja {{ formatPrice(voucher.min_order) }}
                                        </span>
                                        <span
                                            v-if="voucher.expires_at"
                                            class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700"
                                        >
                                            berakhir {{ formatTimeAgo(voucher.expires_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="p-8 text-center text-sm text-gray-500">
                                Belum ada promo aktif saat ini.
                            </div>
                        </div>

                        <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">
                                        Notifikasi Wishlist & Stok
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        Produk favorit yang siap dibeli atau stoknya mulai terbatas.
                                    </p>
                                </div>
                                <Link
                                    :href="route('wishlist.index')"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                >
                                    Buka Wishlist
                                </Link>
                            </div>

                            <div v-if="wishlistAlerts.length > 0" class="divide-y divide-gray-100">
                                <Link
                                    v-for="item in wishlistAlerts"
                                    :key="item.id"
                                    :href="`/products/${item.product.id}`"
                                    class="flex flex-col gap-4 px-6 py-4 transition-colors hover:bg-gray-50 md:flex-row md:items-center"
                                >
                                    <div class="h-20 w-16 overflow-hidden rounded-xl bg-gray-100 flex-shrink-0">
                                        <img
                                            :src="getProductImageUrl(item.product.image)"
                                            :alt="item.product.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ item.product.name }}
                                            </p>
                                            <span
                                                v-if="item.product.is_featured"
                                                class="inline-flex rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700"
                                            >
                                                Promo Pilihan
                                            </span>
                                            <span
                                                v-if="item.product.stock_summary?.is_low"
                                                class="inline-flex rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-[11px] font-semibold text-red-700"
                                            >
                                                Stok Terbatas
                                            </span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ item.product.category }} · {{ formatPrice(item.product.price) }}
                                        </p>
                                        <p class="mt-2 text-xs text-gray-500">
                                            {{
                                                totalAvailableStock(item.product) > 0
                                                    ? `Total stok aktif ${totalAvailableStock(item.product)} item`
                                                    : "Produk tersedia untuk dipesan"
                                            }}
                                        </p>
                                    </div>

                                    <div class="flex-shrink-0 text-sm font-semibold text-indigo-600">
                                        Lihat Produk &rarr;
                                    </div>
                                </Link>
                            </div>

                            <div v-else class="p-8 text-center text-sm text-gray-500">
                                Simpan produk ke wishlist untuk mulai menerima notifikasi promo dan ketersediaan.
                            </div>
                        </div>
                    </div>

                    <!-- Cart Items & Profile Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Cart Items -->
                        <div class="lg:col-span-2">
                            <div
                                class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                            >
                                <div
                                    class="px-6 py-5 border-b border-gray-100 flex items-center justify-between"
                                >
                                    <div>
                                        <h3
                                            class="text-base font-semibold text-gray-900"
                                        >
                                            Keranjang Belanja
                                        </h3>
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            {{ cartTotal }} item di keranjang
                                            Anda
                                        </p>
                                    </div>
                                    <Link
                                        :href="route('cart.index')"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition-colors"
                                        >Lihat Semua</Link
                                    >
                                </div>

                                <div
                                    v-if="cartItems.length > 0"
                                    class="divide-y divide-gray-50"
                                >
                                    <div
                                        v-for="item in cartItems"
                                        :key="item.id"
                                        class="p-5 hover:bg-gray-50/50 transition-colors flex items-center gap-4"
                                    >
                                        <div
                                            class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0"
                                        >
                                            <img
                                                v-if="item.product"
                                                :src="
                                                    item.product.image.startsWith(
                                                        'http',
                                                    )
                                                        ? item.product.image
                                                        : '/storage/' +
                                                          item.product.image
                                                "
                                                :alt="item.product.name"
                                                class="w-full h-full object-cover"
                                            />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4
                                                class="text-sm font-semibold text-gray-900 truncate"
                                            >
                                                {{
                                                    item.product?.name ??
                                                    "Produk"
                                                }}
                                            </h4>
                                            <p
                                                class="text-sm text-gray-500 mt-0.5"
                                            >
                                                {{ item.quantity }}x
                                                {{
                                                    formatPrice(
                                                        item.product?.price ??
                                                            0,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <p
                                            class="text-sm font-bold text-gray-900 tabular-nums"
                                        >
                                            {{
                                                formatPrice(
                                                    (item.product?.price ?? 0) *
                                                        item.quantity,
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <!-- Cart Total -->
                                    <div
                                        class="px-5 py-4 bg-gray-50/60 flex items-center justify-between"
                                    >
                                        <span
                                            class="text-sm font-medium text-gray-600"
                                            >Subtotal</span
                                        >
                                        <span
                                            class="text-base font-bold text-gray-900 tabular-nums"
                                            >{{
                                                formatPrice(cartSubtotal)
                                            }}</span
                                        >
                                    </div>
                                </div>

                                <div v-else class="p-12 text-center">
                                    <div
                                        class="w-16 h-16 bg-gray-100 rounded-full mx-auto flex items-center justify-center mb-4"
                                    >
                                        <svg
                                            class="w-8 h-8 text-gray-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"
                                            />
                                        </svg>
                                    </div>
                                    <h3
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        Keranjang kosong
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Ayo mulai belanja dan temukan gaya
                                        favoritmu!
                                    </p>
                                    <Link
                                        href="/"
                                        class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                    >
                                        Jelajahi Produk
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Card -->
                        <div class="lg:col-span-1 space-y-6">
                            <div
                                class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                            >
                                <div class="px-6 py-5 border-b border-gray-100">
                                    <h3
                                        class="text-base font-semibold text-gray-900"
                                    >
                                        Informasi Akun
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <div class="flex items-center gap-4 mb-6">
                                        <div
                                            class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-full flex items-center justify-center text-xl font-bold shadow-md"
                                        >
                                            {{
                                                user.name
                                                    .charAt(0)
                                                    .toUpperCase()
                                            }}
                                        </div>
                                        <div>
                                            <h4
                                                class="text-base font-bold text-gray-900"
                                            >
                                                {{ user.name }}
                                            </h4>
                                            <p class="text-sm text-gray-500">
                                                {{ user.email }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div
                                            class="flex items-center justify-between py-3 border-t border-gray-100"
                                        >
                                            <span class="text-sm text-gray-500"
                                                >Status</span
                                            >
                                            <span
                                                class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700"
                                            >
                                                <span
                                                    class="w-2 h-2 bg-emerald-500 rounded-full"
                                                ></span>
                                                Aktif
                                            </span>
                                        </div>
                                        <div
                                            class="flex items-center justify-between py-3 border-t border-gray-100"
                                        >
                                            <span class="text-sm text-gray-500"
                                                >Tipe Akun</span
                                            >
                                            <span
                                                class="text-sm font-medium text-gray-900"
                                                >Pelanggan</span
                                            >
                                        </div>
                                    </div>

                                    <Link
                                        :href="route('profile.edit')"
                                        class="block w-full mt-6 text-center bg-gray-900 hover:bg-gray-800 text-white font-medium py-2.5 rounded-xl transition-colors text-sm"
                                    >
                                        Edit Profil
                                    </Link>
                                </div>
                            </div>

                            <!-- Quick Links -->
                            <div
                                class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                            >
                                <div class="px-6 py-5 border-b border-gray-100">
                                    <h3
                                        class="text-base font-semibold text-gray-900"
                                    >
                                        Jelajahi
                                    </h3>
                                </div>
                                <div class="p-3">
                                    <Link
                                        :href="route('pria')"
                                        class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-gray-50 transition-colors"
                                    >
                                        <div
                                            class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center flex-shrink-0"
                                        >
                                            <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                Koleksi Pria
                                            </p>
                                        </div>
                                        <svg
                                            class="w-4 h-4 text-gray-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('wanita')"
                                        class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-gray-50 transition-colors"
                                    >
                                        <div
                                            class="w-10 h-10 bg-rose-50 text-rose-600 rounded-lg flex items-center justify-center flex-shrink-0"
                                        >
                                            <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                Koleksi Wanita
                                            </p>
                                        </div>
                                        <svg
                                            class="w-4 h-4 text-gray-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('koleksi-baru')"
                                        class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-gray-50 transition-colors"
                                    >
                                        <div
                                            class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center flex-shrink-0"
                                        >
                                            <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
                                                />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                Koleksi Baru
                                            </p>
                                        </div>
                                        <svg
                                            class="w-4 h-4 text-gray-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pesanan Terbaru -->
                <div
                    class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                >
                    <div
                        class="px-6 py-5 border-b border-gray-100 flex items-center justify-between"
                    >
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">
                                Pesanan Terbaru
                            </h3>
                            <p class="text-sm text-gray-500 mt-0.5">
                                5 pesanan terakhir Anda
                            </p>
                        </div>
                        <Link
                            :href="route('orders.index')"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition-colors"
                            >Lihat Semua</Link
                        >
                    </div>

                    <div
                        v-if="userRecentOrders.length > 0"
                        class="divide-y divide-gray-50"
                    >
                        <Link
                            v-for="order in userRecentOrders"
                            :key="order.id"
                            :href="`/orders/${order.id}`"
                            class="flex items-center justify-between px-6 py-4 hover:bg-gray-50/50 transition-colors gap-4"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ order.order_number }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{
                                        new Date(
                                            order.created_at,
                                        ).toLocaleDateString("id-ID", {
                                            day: "numeric",
                                            month: "short",
                                            year: "numeric",
                                        })
                                    }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border"
                                    :class="{
                                        'bg-amber-50 text-amber-700 border-amber-200':
                                            order.status === 'pending',
                                        'bg-blue-50 text-blue-700 border-blue-200':
                                            order.status === 'processing',
                                        'bg-indigo-50 text-indigo-700 border-indigo-200':
                                            order.status === 'shipped',
                                        'bg-green-50 text-green-700 border-green-200':
                                            order.status === 'completed',
                                        'bg-red-50 text-red-700 border-red-200':
                                            order.status === 'cancelled',
                                    }"
                                >
                                    {{
                                        {
                                            pending: "Menunggu",
                                            processing: "Diproses",
                                            shipped: "Dikirim",
                                            completed: "Diterima",
                                            cancelled: "Batal",
                                        }[order.status] ?? order.status
                                    }}
                                </span>
                                <p
                                    class="text-sm font-bold text-gray-900 tabular-nums"
                                >
                                    {{ formatPrice(order.total_price) }}
                                </p>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="p-10 text-center">
                        <svg
                            class="mx-auto h-10 w-10 text-gray-300 mb-3"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            />
                        </svg>
                        <p class="text-sm text-gray-500">Belum ada pesanan.</p>
                        <Link
                            href="/products"
                            class="mt-3 inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-700"
                        >
                            Mulai Belanja &rarr;
                        </Link>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
