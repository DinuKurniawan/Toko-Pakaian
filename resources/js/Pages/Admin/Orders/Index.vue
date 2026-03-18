<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const props = defineProps({
    orders: Object,
    filters: Object,
    stalePendingOrdersCount: { type: Number, default: 0 },
    stalePendingDays: { type: Number, default: 3 },
});

const isSidebarOpen = ref(false);

// Filters
const searchQuery = ref(props.filters?.search || "");
const statusFilter = ref(props.filters?.status || "");

function applyFilters() {
    router.get(
        route("admin.orders.index"),
        {
            search: searchQuery.value || undefined,
            status: statusFilter.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

// Debounced search
let searchTimeout;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch(statusFilter, () => {
    applyFilters();
});

function formatPrice(price) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

const statusLabels = {
    pending: "Menunggu",
    processing: "Diproses",
    shipped: "Dikirim",
    completed: "Selesai",
    cancelled: "Dibatalkan",
};

const statusColors = {
    pending: "bg-yellow-100 text-yellow-800",
    processing: "bg-blue-100 text-blue-800",
    shipped: "bg-indigo-100 text-indigo-800",
    completed: "bg-green-100 text-green-800",
    cancelled: "bg-red-100 text-red-800",
};

function isStalePending(order) {
    if (order.status !== "pending" || !order.created_at) {
        return false;
    }

    const createdAt = new Date(order.created_at).getTime();

    return Date.now() - createdAt >= props.stalePendingDays * 86400000;
}
</script>

<template>
    <Head title="Kelola Pesanan | LUMIÈRE" />

    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900">
        <AdminSidebar
            :is-sidebar-open="isSidebarOpen"
            active-page="orders"
            @close-sidebar="isSidebarOpen = false"
        />

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
                        Kelola Pesanan
                    </h2>
                </div>
                <Link
                    href="/"
                    class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors"
                >
                    Kembali ke Toko &rarr;
                </Link>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <!-- Flash Message -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium"
                >
                    {{ $page.props.flash.success }}
                </div>

                <div
                    v-if="stalePendingOrdersCount > 0"
                    class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4"
                >
                    <div
                        class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="flex items-start gap-3">
                            <svg
                                class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-red-800">
                                    {{ stalePendingOrdersCount }} pesanan sudah
                                    pending lebih dari
                                    {{ stalePendingDays }} hari
                                </p>
                                <p class="mt-1 text-sm text-red-700">
                                    Prioritaskan follow-up untuk menjaga
                                    pengalaman pelanggan dan cashflow tetap
                                    sehat.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="statusFilter = 'pending'"
                            class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition-colors hover:bg-red-100"
                        >
                            Fokus ke Pesanan Pending
                        </button>
                    </div>
                </div>

                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Daftar Pesanan
                    </h1>
                    <p class="text-gray-500 mt-1">
                        Kelola semua pesanan pelanggan.
                    </p>
                </div>

                <!-- Filters -->
                <div class="mb-6 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1 max-w-md">
                        <svg
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari no. pesanan atau nama pelanggan..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                    <select
                        v-model="statusFilter"
                        class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="processing">Diproses</option>
                        <option value="shipped">Dikirim</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>

                <!-- Orders Table -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        No. Pesanan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Pelanggan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Total
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Tanggal
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="order in orders.data"
                                    :key="order.id"
                                    :class="
                                        isStalePending(order)
                                            ? 'bg-red-50/50 hover:bg-red-50'
                                            : 'hover:bg-gray-50'
                                    "
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <p
                                            class="text-sm font-semibold text-gray-900"
                                        >
                                            {{ order.order_number }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ order.items?.length ?? 0 }} item
                                        </p>
                                        <p
                                            v-if="isStalePending(order)"
                                            class="mt-1 text-xs font-semibold text-red-700"
                                        >
                                            Perlu tindak lanjut cepat
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                                            >
                                                {{
                                                    order.user?.name?.charAt(
                                                        0,
                                                    ) ?? "?"
                                                }}
                                            </div>
                                            <div class="min-w-0">
                                                <p
                                                    class="text-sm font-medium text-gray-900 truncate"
                                                >
                                                    {{ order.user?.name }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-500 truncate"
                                                >
                                                    {{ order.user?.email }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"
                                    >
                                        {{ formatPrice(order.total_price) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="statusColors[order.status]"
                                        >
                                            {{ statusLabels[order.status] }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ formatDate(order.created_at) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'admin.orders.show',
                                                    order.id,
                                                )
                                            "
                                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors"
                                        >
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="orders.data.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-6 py-12 text-center text-gray-500"
                                    >
                                        Belum ada pesanan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="orders.last_page > 1"
                        class="px-6 py-4 border-t border-gray-100 flex items-center justify-between"
                    >
                        <p class="text-sm text-gray-500">
                            Menampilkan {{ orders.from }} - {{ orders.to }} dari
                            {{ orders.total }} pesanan
                        </p>
                        <div class="flex gap-1">
                            <template
                                v-for="link in orders.links"
                                :key="link.label"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                                    :class="
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'text-gray-600 hover:bg-gray-100'
                                    "
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="px-3 py-1.5 text-sm text-gray-300"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
