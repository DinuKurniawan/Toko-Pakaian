<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

defineProps({
    isSidebarOpen: {
        type: Boolean,
        default: false,
    },
    activePage: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["closeSidebar"]);

const page = usePage();
const user = computed(() => page.props.auth.user);

const navItems = [
    {
        label: "Dashboard",
        routeName: "dashboard",
        key: "dashboard",
        icon: "M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z",
    },
    {
        label: "Produk",
        routeName: "admin.products.index",
        key: "products",
        icon: "M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z",
    },
    {
        label: "Kategori",
        routeName: "admin.categories.index",
        key: "categories",
        icon: "M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z",
    },
    {
        label: "Banner",
        routeName: "admin.banners.index",
        key: "banners",
        icon: "M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z",
    },
    {
        label: "Pesanan",
        routeName: "admin.orders.index",
        key: "orders",
        icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01",
    },
    {
        label: "Users",
        routeName: "admin.users.index",
        key: "users",
        icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
    },
    {
        label: "Voucher",
        routeName: "admin.vouchers.index",
        key: "vouchers",
        icon: "M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z",
    },
    {
        label: "Ulasan",
        routeName: "admin.reviews.index",
        key: "reviews",
        icon: "M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z",
    },
    {
        label: "Laporan",
        routeName: "admin.reports.index",
        key: "reports",
        icon: "M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
    },
];
</script>

<template>
    <!-- Sidebar Mobile Overlay -->
    <div
        v-if="isSidebarOpen"
        @click="emit('closeSidebar')"
        class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden"
    ></div>

    <!-- Sidebar -->
    <aside
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
                Panel Admin
            </div>
            <Link
                v-for="item in navItems"
                :key="item.key"
                :href="route(item.routeName)"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-colors"
                :class="
                    activePage === item.key
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
                    <p class="text-xs text-gray-500 truncate">ADMIN</p>
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
</template>
