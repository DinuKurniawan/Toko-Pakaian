<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import Navbar from "@/Components/Navbar.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Footer from "@/Components/Footer.vue";

defineProps({
    orders: { type: Array, default: () => [] },
});

const isSidebarOpen = ref(false);

const formatPrice = (value) => {
    if (!value) return "Rp 0";
    return "Rp " + (value / 1).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
};

const statusConfig = {
    pending: {
        label: "Menunggu",
        class: "bg-amber-50 text-amber-700 border-amber-200",
    },
    processing: {
        label: "Diproses",
        class: "bg-blue-50 text-blue-700 border-blue-200",
    },
    shipped: {
        label: "Dikirim",
        class: "bg-indigo-50 text-indigo-700 border-indigo-200",
    },
    completed: {
        label: "Diterima",
        class: "bg-green-50 text-green-700 border-green-200",
    },
    cancelled: {
        label: "Dibatalkan",
        class: "bg-red-50 text-red-700 border-red-200",
    },
};

const getStatus = (status) =>
    statusConfig[status] ?? {
        label: status,
        class: "bg-gray-50 text-gray-600 border-gray-200",
    };
</script>

<template>
    <Head title="Riwayat Pesanan | LUMIÈRE" />

    <div class="min-h-screen bg-gray-50 font-sans text-gray-900 flex flex-col">
        <Navbar @openSidebar="isSidebarOpen = true" />
        <Sidebar
            :isOpen="isSidebarOpen"
            @closeSidebar="isSidebarOpen = false"
        />

        <main
            class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10"
        >
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Riwayat Pesanan
                    </h1>
                    <p class="text-gray-500 mt-1">
                        {{ orders.length }} pesanan ditemukan
                    </p>
                </div>
                <Link
                    href="/products"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-full text-sm transition-colors"
                >
                    Belanja Lagi
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.success"
                class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
            >
                {{ $page.props.flash.success }}
            </div>

            <div
                v-if="$page.props.flash?.error"
                class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"
            >
                {{ $page.props.flash.error }}
            </div>

            <!-- Kosong -->
            <div
                v-if="orders.length === 0"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center py-24"
            >
                <div
                    class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mb-5"
                >
                    <svg
                        class="w-10 h-10 text-indigo-400"
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
                </div>
                <h2 class="text-xl font-semibold text-gray-700 mb-2">
                    Belum ada pesanan
                </h2>
                <p class="text-gray-400 mb-6 text-center max-w-xs">
                    Mulai belanja dan pesanan kamu akan muncul di sini.
                </p>
                <Link
                    href="/products"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-full transition-colors"
                >
                    Mulai Belanja
                </Link>
            </div>

            <!-- Daftar Pesanan -->
            <div v-else class="space-y-4">
                <Link
                    v-for="order in orders"
                    :key="order.id"
                    :href="`/orders/${order.id}`"
                    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-indigo-100 transition-all duration-200 group"
                >
                    <div
                        class="flex items-start justify-between gap-4 flex-wrap"
                    >
                        <!-- Kiri: Info Pesanan -->
                        <div class="min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <p class="font-bold text-gray-900 text-sm">
                                    {{ order.order_number }}
                                </p>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                    :class="getStatus(order.status).class"
                                >
                                    {{ getStatus(order.status).label }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-400 mt-1">
                                {{ formatDate(order.created_at) }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Kurir:
                                <span class="font-medium text-gray-700">{{
                                    order.courier
                                }}</span>
                            </p>
                            <p
                                v-if="order.status === 'pending'"
                                class="text-xs font-medium text-amber-700 mt-2"
                            >
                                Bisa dibatalkan dari halaman detail pesanan.
                            </p>
                            <p
                                v-else-if="
                                    ['completed', 'cancelled'].includes(
                                        order.status,
                                    )
                                "
                                class="text-xs font-medium text-indigo-700 mt-2"
                            >
                                Bisa pesan lagi dari halaman detail pesanan.
                            </p>
                        </div>

                        <!-- Kanan: Total & Aksi -->
                        <div class="flex flex-col items-end gap-2">
                            <p class="text-lg font-bold text-indigo-600">
                                {{ formatPrice(order.total_price) }}
                            </p>
                            <span
                                class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 group-hover:text-indigo-800 transition-colors"
                            >
                                Lihat Detail
                                <svg
                                    class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </span>
                        </div>
                    </div>
                </Link>
            </div>
        </main>

        <Footer />
    </div>
</template>
