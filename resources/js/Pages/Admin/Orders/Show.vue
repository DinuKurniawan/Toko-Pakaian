<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const props = defineProps({
    order: Object,
});

const isSidebarOpen = ref(false);

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
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function getImageUrl(image) {
    if (!image) return "";
    if (image.startsWith("http")) return image;
    return `/storage/${image}`;
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

const allStatuses = [
    "pending",
    "processing",
    "shipped",
    "completed",
    "cancelled",
];

function updateStatus(newStatus) {
    router.patch(route("admin.orders.update", props.order.id), {
        status: newStatus,
    });
}
</script>

<template>
    <Head :title="`Pesanan ${order.order_number} | LUMIÈRE`" />

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
                        Detail Pesanan
                    </h2>
                </div>
                <Link
                    :href="route('admin.orders.index')"
                    class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors"
                >
                    &larr; Kembali ke Daftar
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

                <!-- Order Header -->
                <div
                    class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ order.order_number }}
                        </h1>
                        <p class="text-gray-500 mt-1">
                            Dibuat pada {{ formatDate(order.created_at) }}
                        </p>
                    </div>
                    <span
                        class="px-4 py-2 text-sm font-semibold rounded-xl self-start"
                        :class="statusColors[order.status]"
                    >
                        {{ statusLabels[order.status] }}
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Order Items -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Items -->
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                        >
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Item Pesanan
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="px-6 py-4 flex items-center gap-4"
                                >
                                    <div
                                        class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0"
                                    >
                                        <img
                                            v-if="item.product?.image"
                                            :src="
                                                getImageUrl(item.product.image)
                                            "
                                            :alt="item.product?.name"
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="text-sm font-semibold text-gray-900"
                                        >
                                            {{
                                                item.product?.name ??
                                                "Produk dihapus"
                                            }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatPrice(item.price) }} x
                                            {{ item.quantity }}
                                        </p>
                                    </div>
                                    <p
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        {{
                                            formatPrice(
                                                item.price * item.quantity,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-between items-center"
                            >
                                <span
                                    class="text-sm font-semibold text-gray-900"
                                    >Total</span
                                >
                                <span class="text-lg font-bold text-gray-900">{{
                                    formatPrice(order.total_price)
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Info & Actions -->
                    <div class="space-y-6">
                        <!-- Customer Info -->
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
                        >
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-4"
                            >
                                Info Pelanggan
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <p
                                        class="text-xs font-medium text-gray-400 uppercase"
                                    >
                                        Nama
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        {{ order.user?.name }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-gray-400 uppercase"
                                    >
                                        Email
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        {{ order.user?.email }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-gray-400 uppercase"
                                    >
                                        Telepon
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        {{ order.phone }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium text-gray-400 uppercase"
                                    >
                                        Alamat Pengiriman
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        {{ order.shipping_address }}
                                    </p>
                                </div>
                                <div v-if="order.notes">
                                    <p
                                        class="text-xs font-medium text-gray-400 uppercase"
                                    >
                                        Catatan
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        {{ order.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Update Status -->
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
                        >
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-4"
                            >
                                Ubah Status
                            </h3>
                            <div class="space-y-2">
                                <button
                                    v-for="status in allStatuses"
                                    :key="status"
                                    @click="updateStatus(status)"
                                    :disabled="order.status === status"
                                    class="w-full px-4 py-2.5 text-sm font-medium rounded-xl transition-colors text-left"
                                    :class="
                                        order.status === status
                                            ? 'bg-indigo-600 text-white cursor-default'
                                            : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                                    "
                                >
                                    {{ statusLabels[status] }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
