<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const props = defineProps({
    orders: Array,
    totalRevenue: Number,
    totalOrders: Number,
    bestSellers: Array,
    dailyRevenue: Array,
    filters: Object,
});

const isSidebarOpen = ref(false);
const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

function applyFilter() {
    router.get(route("admin.reports.index"), { start_date: startDate.value, end_date: endDate.value });
}

function exportCsv() {
    const url = route("admin.reports.export") + `?start_date=${startDate.value}&end_date=${endDate.value}`;
    window.open(url, "_blank");
}

function formatPrice(v) {
    return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(v);
}

function formatDate(d) {
    return new Date(d).toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
}

const statusColors = {
    pending: "bg-yellow-100 text-yellow-800",
    processing: "bg-blue-100 text-blue-800",
    shipped: "bg-indigo-100 text-indigo-800",
    completed: "bg-green-100 text-green-800",
    cancelled: "bg-red-100 text-red-800",
};

function getImageUrl(image) {
    if (!image) return "https://placehold.co/50x60?text=?";
    if (image.startsWith("http")) return image;
    return `/storage/${image}`;
}
</script>

<template>
    <Head title="Laporan Penjualan | LUMIÈRE" />
    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900">
        <AdminSidebar :is-sidebar-open="isSidebarOpen" active-page="reports" @close-sidebar="isSidebarOpen = false" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-900">Laporan Penjualan</h2>
                </div>
                <Link href="/" class="text-sm font-medium text-gray-500 hover:text-indigo-600">Kembali ke Toko &rarr;</Link>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">Laporan Penjualan</h1>

                <!-- Date Filter -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                            <input v-model="startDate" type="date" class="px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                            <input v-model="endDate" type="date" class="px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <button @click="applyFilter" class="px-5 py-2 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-indigo-600 transition-colors">Filter</button>
                        <button @click="exportCsv" class="px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Export CSV
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <p class="text-sm text-gray-500 mb-1">Total Pendapatan</p>
                        <p class="text-3xl font-bold text-indigo-600">{{ formatPrice(totalRevenue) }}</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <p class="text-sm text-gray-500 mb-1">Total Pesanan</p>
                        <p class="text-3xl font-bold text-gray-900">{{ totalOrders }}</p>
                    </div>
                </div>

                <!-- Best Sellers -->
                <div v-if="bestSellers.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Produk Terlaris</h2>
                    <div class="space-y-3">
                        <div v-for="(item, idx) in bestSellers" :key="item.product_id" class="flex items-center gap-4">
                            <span class="text-2xl font-bold text-gray-300 w-8">{{ idx + 1 }}</span>
                            <img :src="getImageUrl(item.product?.image)" class="w-10 h-12 rounded-lg object-cover bg-gray-100" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ item.product?.name }}</p>
                                <p class="text-xs text-gray-500">{{ item.total_qty }} terjual · {{ formatPrice(item.total_revenue) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-900">Daftar Pesanan</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">{{ order.order_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ order.user?.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(order.created_at) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="statusColors[order.status]" class="px-2.5 py-1 rounded-full text-xs font-semibold capitalize">{{ order.status }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">{{ formatPrice(order.total_price) }}</td>
                                </tr>
                                <tr v-if="orders.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">Tidak ada pesanan dalam periode ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
