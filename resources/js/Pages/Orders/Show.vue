<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Navbar from "@/Components/Navbar.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Footer from "@/Components/Footer.vue";

const props = defineProps({
    order: { type: Object, required: true },
    reviewedProductIds: { type: Array, default: () => [] },
});

const isSidebarOpen = ref(false);
const reviewingItem = ref(null);

const reviewForm = useForm({
    product_id: null,
    order_id: props.order.id,
    rating: 5,
    comment: "",
});

function startReview(item) {
    reviewingItem.value = item;
    reviewForm.product_id = item.product_id;
    reviewForm.rating = 5;
    reviewForm.comment = "";
}

function submitReview() {
    reviewForm.post("/reviews", {
        preserveScroll: true,
        onSuccess: () => {
            reviewingItem.value = null;
        },
    });
}

const canCancelOrder = computed(() => props.order.status === "pending");
const canReorderOrder = computed(
    () =>
        Array.isArray(props.order.items) &&
        props.order.items.length > 0 &&
        props.order.status !== "pending",
);

function cancelOrder() {
    if (
        !window.confirm(
            "Batalkan pesanan ini? Pesanan yang dibatalkan tidak akan diproses lebih lanjut.",
        )
    ) {
        return;
    }

    router.post(route("orders.cancel", props.order.id), {}, { preserveScroll: true });
}

function reorderOrder() {
    router.post(route("orders.reorder", props.order.id), {}, { preserveScroll: true });
}

const formatPrice = (value) => {
    if (!value) return "Rp 0";
    return "Rp " + (value / 1).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
        day: "numeric", month: "long", year: "numeric", hour: "2-digit", minute: "2-digit",
    });
};

const getImageUrl = (path) => {
    if (!path) return "https://placehold.co/200x200?text=No+Image";
    let p = String(path).replace(/^["'\[]+|["'\]]+$/g, "");
    if (p.startsWith("http://") || p.startsWith("https://")) return p;
    if (p.startsWith("/storage/")) return p;
    if (p.startsWith("storage/")) return `/${p}`;
    if (p.startsWith("/")) return `/storage${p}`;
    return `/storage/${p}`;
};

const statusConfig = {
    pending: { label: "Menunggu Pembayaran", class: "bg-amber-50 text-amber-700 border-amber-200" },
    processing: { label: "Sedang Diproses", class: "bg-blue-50 text-blue-700 border-blue-200" },
    shipped: { label: "Sedang Dikirim", class: "bg-indigo-50 text-indigo-700 border-indigo-200" },
    completed: { label: "Pesanan Selesai", class: "bg-green-50 text-green-700 border-green-200" },
    cancelled: { label: "Dibatalkan", class: "bg-red-50 text-red-700 border-red-200" },
};

const getStatus = (status) => statusConfig[status] ?? { label: status, class: "bg-gray-50 text-gray-600 border-gray-200" };

const statusSteps = ["pending", "processing", "shipped", "completed"];
const getStepIndex = (status) => statusSteps.indexOf(status);
</script>

<template>
    <Head :title="`${order.order_number} | LUMIÈRE`" />

    <div class="min-h-screen bg-gray-50 font-sans text-gray-900 flex flex-col">
        <Navbar @openSidebar="isSidebarOpen = true" />
        <Sidebar
            :isOpen="isSidebarOpen"
            @closeSidebar="isSidebarOpen = false"
        />

        <main
            class="flex-1 max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10"
        >
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

            <!-- Back -->
            <Link
                href="/orders"
                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-6"
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
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    />
                </svg>
                Kembali ke Riwayat Pesanan
            </Link>

            <!-- Header Card -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5"
            >
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div>
                        <p
                            class="text-xs text-gray-400 mb-1 uppercase tracking-wide font-medium"
                        >
                            Nomor Pesanan
                        </p>
                        <h1 class="text-xl font-bold text-gray-900">
                            {{ order.order_number }}
                        </h1>
                        <p class="text-sm text-gray-400 mt-1">
                            {{ formatDate(order.created_at) }}
                        </p>
                    </div>
                    <div class="flex flex-col items-start sm:items-end gap-3">
                        <span
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold border"
                            :class="getStatus(order.status).class"
                        >
                            {{ getStatus(order.status).label }}
                        </span>

                        <div class="flex flex-wrap gap-2">
                            <button
                                v-if="canCancelOrder"
                                type="button"
                                @click="cancelOrder"
                                class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition-colors hover:bg-red-100"
                            >
                                Batalkan Pesanan
                            </button>

                            <button
                                v-if="canReorderOrder"
                                type="button"
                                @click="reorderOrder"
                                class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition-colors hover:bg-indigo-100"
                            >
                                Pesan Lagi
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-if="canCancelOrder"
                    class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                >
                    Pesanan ini masih bisa dibatalkan sebelum masuk tahap
                    <span class="font-semibold">diproses</span>.
                </div>

                <!-- Progress Bar (hanya untuk status normal, bukan cancelled) -->
                <div v-if="order.status !== 'cancelled'" class="mt-6">
                    <div class="flex items-center justify-between relative">
                        <div
                            class="absolute left-0 right-0 top-4 h-0.5 bg-gray-200 -z-0"
                        >
                            <div
                                class="h-full bg-indigo-500 transition-all duration-500"
                                :style="{
                                    width: `${(getStepIndex(order.status) / (statusSteps.length - 1)) * 100}%`,
                                }"
                            ></div>
                        </div>
                        <div
                            v-for="(step, idx) in [
                                'Menunggu',
                                'Diproses',
                                'Dikirim',
                                'Diterima',
                            ]"
                            :key="step"
                            class="flex flex-col items-center z-10 gap-1"
                        >
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-colors"
                                :class="
                                    idx <= getStepIndex(order.status)
                                        ? 'bg-indigo-600 border-indigo-600 text-white'
                                        : 'bg-white border-gray-200 text-gray-400'
                                "
                            >
                                <svg
                                    v-if="idx < getStepIndex(order.status)"
                                    class="w-4 h-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="3"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                <span v-else>{{ idx + 1 }}</span>
                            </div>
                            <span
                                class="text-xs text-gray-500 text-center w-16 leading-tight hidden sm:block"
                                >{{ step }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Daftar Produk -->
                <div class="lg:col-span-2 space-y-5">
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                    >
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h2 class="font-semibold text-gray-900">
                                Item Pesanan
                            </h2>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="flex items-center gap-4 px-6 py-4"
                            >
                                <div
                                    class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0"
                                >
                                    <img
                                        :src="getImageUrl(item.product?.image)"
                                        :alt="item.product?.name"
                                        class="w-full h-full object-cover object-center"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">
                                        {{ item.product_name ?? item.product?.name ?? "-" }}
                                    </p>
                                    <p class="text-sm text-gray-400">
                                        {{ item.product?.category }}
                                        <span v-if="item.size" class="ml-1 font-semibold text-indigo-600">· Ukuran {{ item.size }}</span>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        {{ formatPrice(item.price) }} × {{ item.quantity }}
                                    </p>
                                    <!-- Review button for completed orders -->
                                    <div v-if="order.status === 'completed' && item.product_id" class="mt-2">
                                        <span v-if="reviewedProductIds.includes(item.product_id)" class="text-xs text-green-600 font-medium">✓ Sudah diulas</span>
                                        <button v-else @click="startReview(item)" class="text-xs text-indigo-600 font-medium hover:text-indigo-700 underline">Tulis Ulasan</button>
                                    </div>
                                </div>
                                <p
                                    class="font-semibold text-gray-900 text-sm flex-shrink-0"
                                >
                                    {{
                                        formatPrice(item.price * item.quantity)
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Pengiriman -->
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
                    >
                        <h2 class="font-semibold text-gray-900 mb-4">
                            Info Pengiriman
                        </h2>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-400 flex-shrink-0">
                                    Kurir
                                </dt>
                                <dd
                                    class="font-medium text-gray-800 text-right"
                                >
                                    {{ order.courier }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-400 flex-shrink-0">
                                    Alamat
                                </dt>
                                <dd
                                    class="font-medium text-gray-800 text-right"
                                >
                                    {{ order.shipping_address }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-400 flex-shrink-0">
                                    Telepon
                                </dt>
                                <dd class="font-medium text-gray-800">
                                    {{ order.phone }}
                                </dd>
                            </div>
                            <div
                                v-if="order.notes"
                                class="flex justify-between gap-4"
                            >
                                <dt class="text-gray-400 flex-shrink-0">
                                    Catatan
                                </dt>
                                <dd
                                    class="font-medium text-gray-800 text-right"
                                >
                                    {{ order.notes }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Ringkasan Pembayaran -->
                <div class="lg:col-span-1">
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24"
                    >
                        <h2 class="font-semibold text-gray-900 mb-4">
                            Ringkasan Pembayaran
                        </h2>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-400">Subtotal Produk</dt>
                                <dd class="font-medium text-gray-800">
                                    {{ formatPrice(order.total_price - order.shipping_cost + (order.discount_amount ?? 0)) }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-400">Ongkos Kirim</dt>
                                <dd class="font-medium text-gray-800">{{ formatPrice(order.shipping_cost) }}</dd>
                            </div>
                            <div v-if="order.discount_amount > 0" class="flex justify-between text-green-600">
                                <dt>Diskon ({{ order.voucher_code }})</dt>
                                <dd class="font-medium">-{{ formatPrice(order.discount_amount) }}</dd>
                            </div>
                            <div class="border-t border-gray-100 pt-3 flex justify-between text-base font-bold">
                                <dt class="text-gray-900">Total</dt>
                                <dd class="text-indigo-600">{{ formatPrice(order.total_price) }}</dd>
                            </div>
                        </dl>

                        <Link
                            href="/products"
                            class="mt-6 block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-full transition-colors text-sm"
                        >
                            Belanja Lagi
                        </Link>

                        <button
                            v-if="canReorderOrder"
                            type="button"
                            @click="reorderOrder"
                            class="mt-3 block w-full rounded-full border border-indigo-200 bg-indigo-50 py-3 text-center text-sm font-semibold text-indigo-700 transition-colors hover:bg-indigo-100"
                        >
                            Tambah Lagi ke Keranjang
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Review Modal -->
        <div v-if="reviewingItem" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/50" @click="reviewingItem = null"></div>
            <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Tulis Ulasan</h3>
                <p class="text-sm text-gray-500 mb-5">{{ reviewingItem.product_name ?? reviewingItem.product?.name }}</p>

                <div v-if="$page.props.flash?.error" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded-xl text-sm">{{ $page.props.flash.error }}</div>

                <form @submit.prevent="submitReview" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-1">
                            <button v-for="i in 5" :key="i" type="button" @click="reviewForm.rating = i" class="transition-transform hover:scale-110">
                                <svg class="w-8 h-8" :class="i <= reviewForm.rating ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Komentar (opsional)</label>
                        <textarea v-model="reviewForm.comment" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="Bagaimana pengalaman Anda dengan produk ini?"></textarea>
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="reviewingItem = null" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Batal</button>
                        <button type="submit" :disabled="reviewForm.processing" class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl disabled:opacity-50">
                            {{ reviewForm.processing ? 'Mengirim...' : 'Kirim Ulasan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <Footer />
    </div>
</template>
