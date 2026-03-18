<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Navbar from "@/Components/Navbar.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Footer from "@/Components/Footer.vue";

const props = defineProps({
    cartItems: {
        type: Array,
        default: () => [],
    },
});

const isSidebarOpen = ref(false);
const showCheckoutForm = ref(false);

// ─── Ongkir State ────────────────────────────────────────────────
const provinces = ref([]);
const cities = ref([]);
const shippingServices = ref([]);
const selectedService = ref(null);
const isLoadingCities = ref(false);
const isLoadingOngkir = ref(false);
const ongkirError = ref("");

const shippingForm = ref({
    province_id: "",
    city_id: "",
    courier: "",
});

const COURIERS = [
    { value: "jne", label: "JNE" },
    { value: "tiki", label: "TIKI" },
    { value: "pos", label: "POS Indonesia" },
    { value: "jnt", label: "J&T Express" },
    { value: "sicepat", label: "SiCepat" },
    { value: "anteraja", label: "AnterAja" },
];

// ─── Checkout Form ───────────────────────────────────────────────
const checkoutForm = useForm({
    shipping_address: "",
    phone: "",
    notes: "",
    shipping_cost: 0,
    courier: "",
    voucher_code: "",
});

// ─── Voucher State ────────────────────────────────────────────────
const voucherInput = ref("");
const voucherStatus = ref(null); // { valid, message, discount }
const isCheckingVoucher = ref(false);
const appliedDiscount = ref(0);

const checkVoucher = async () => {
    if (!voucherInput.value) return;
    isCheckingVoucher.value = true;
    voucherStatus.value = null;
    try {
        const res = await window.axios.post("/voucher/check", {
            code: voucherInput.value,
            subtotal: subtotal.value,
        });
        voucherStatus.value = res.data;
        if (res.data.valid) {
            appliedDiscount.value = res.data.discount;
            checkoutForm.voucher_code = voucherInput.value;
        } else {
            appliedDiscount.value = 0;
            checkoutForm.voucher_code = "";
        }
    } catch {
        voucherStatus.value = { valid: false, message: "Gagal memeriksa voucher." };
        appliedDiscount.value = 0;
    } finally {
        isCheckingVoucher.value = false;
    }
};

const removeVoucher = () => {
    voucherInput.value = "";
    voucherStatus.value = null;
    appliedDiscount.value = 0;
    checkoutForm.voucher_code = "";
};

// ─── Helpers ─────────────────────────────────────────────────────
const formatPrice = (value) => {
    if (!value) return "Rp 0";
    let val = (value / 1).toFixed(0).replace(".", ",");
    return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const getImageUrl = (path) => {
    if (!path) return "https://placehold.co/600x400?text=No+Image";
    let cleanPath = String(path).replace(/^["'\[]+|["'\]]+$/g, "");
    if (cleanPath.startsWith("[http") && cleanPath.includes("](")) {
        cleanPath = cleanPath.substring(
            cleanPath.indexOf("](") + 2,
            cleanPath.length - 1,
        );
    }
    if (cleanPath.startsWith("http://") || cleanPath.startsWith("https://"))
        return cleanPath;
    if (cleanPath.startsWith("/storage/")) return cleanPath;
    if (cleanPath.startsWith("storage/")) return `/${cleanPath}`;
    if (cleanPath.startsWith("/")) return `/storage${cleanPath}`;
    return `/storage/${cleanPath}`;
};

// ─── Computed ────────────────────────────────────────────────────
const subtotal = computed(() =>
    props.cartItems.reduce(
        (sum, item) => sum + item.product.price * item.quantity,
        0,
    ),
);

const shippingCost = computed(() =>
    selectedService.value ? selectedService.value.cost[0].value : null,
);

const total = computed(() => subtotal.value + (shippingCost.value ?? 0) - appliedDiscount.value);

const totalWeight = computed(() =>
    props.cartItems.reduce((sum, item) => sum + item.quantity * 500, 0),
);

// ─── Cart Actions ─────────────────────────────────────────────────
const updateQuantity = (item, newQty) => {
    if (newQty < 1) return;
    router.patch(
        `/cart/${item.id}`,
        { quantity: newQty },
        { preserveScroll: true },
    );
};

const removeItem = (item) => {
    router.delete(`/cart/${item.id}`, { preserveScroll: true });
};

// ─── JagoOngkir ──────────────────────────────────────────────────
const loadProvinces = async () => {
    try {
        const res = await window.axios.get("/ongkir/provinces");
        provinces.value = res.data?.rajaongkir?.results ?? [];
    } catch {
        ongkirError.value = "Gagal memuat daftar provinsi.";
    }
};

const onProvinceChange = async () => {
    shippingForm.value.city_id = "";
    cities.value = [];
    shippingServices.value = [];
    selectedService.value = null;
    ongkirError.value = "";
    if (!shippingForm.value.province_id) return;
    isLoadingCities.value = true;
    try {
        const res = await window.axios.get("/ongkir/cities", {
            params: { province_id: shippingForm.value.province_id },
        });
        cities.value = res.data?.rajaongkir?.results ?? [];
    } catch {
        ongkirError.value = "Gagal memuat daftar kota.";
    } finally {
        isLoadingCities.value = false;
    }
};

const onCityOrCourierChange = () => {
    shippingServices.value = [];
    selectedService.value = null;
    ongkirError.value = "";
};

const checkOngkir = async () => {
    ongkirError.value = "";
    shippingServices.value = [];
    selectedService.value = null;
    if (!shippingForm.value.city_id || !shippingForm.value.courier) {
        ongkirError.value = "Pilih kota tujuan dan kurir terlebih dahulu.";
        return;
    }
    isLoadingOngkir.value = true;
    try {
        const res = await window.axios.post("/ongkir/cost", {
            city_id: shippingForm.value.city_id,
            weight: totalWeight.value,
            courier: shippingForm.value.courier,
        });
        const results = res.data?.rajaongkir?.results?.[0]?.costs ?? [];
        shippingServices.value = results;
        if (results.length === 0) {
            ongkirError.value = "Tidak ada layanan tersedia untuk tujuan ini.";
        }
    } catch {
        ongkirError.value = "Gagal mengambil data ongkos kirim. Coba lagi.";
    } finally {
        isLoadingOngkir.value = false;
    }
};

const selectService = (service) => {
    selectedService.value = service;
    ongkirError.value = "";
};

// ─── Checkout ─────────────────────────────────────────────────────
const openCheckout = () => {
    if (provinces.value.length === 0) loadProvinces();
    showCheckoutForm.value = true;
};

const submitCheckout = () => {
    if (!selectedService.value) {
        ongkirError.value = "Pilih layanan pengiriman terlebih dahulu.";
        return;
    }
    checkoutForm.shipping_cost = selectedService.value.cost[0].value;
    checkoutForm.courier =
        shippingForm.value.courier.toUpperCase() +
        " - " +
        selectedService.value.service;

    checkoutForm.post("/checkout", {
        onSuccess: () => {
            showCheckoutForm.value = false;
            checkoutForm.reset();
            shippingForm.value = { province_id: "", city_id: "", courier: "" };
            shippingServices.value = [];
            selectedService.value = null;
        },
    });
};
</script>

<template>
    <Head title="Keranjang Belanja | LUMIÈRE" />

    <div class="min-h-screen bg-gray-50 font-sans text-gray-900 flex flex-col">
        <Navbar @openSidebar="isSidebarOpen = true" />
        <Sidebar
            :isOpen="isSidebarOpen"
            @closeSidebar="isSidebarOpen = false"
        />

        <main
            class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10"
        >
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Keranjang Belanja
                </h1>
                <p class="text-gray-500 mt-1">
                    {{ cartItems.length }} produk dalam keranjang Anda
                </p>
            </div>

            <!-- Keranjang Kosong -->
            <div
                v-if="cartItems.length === 0"
                class="flex flex-col items-center justify-center py-24 bg-white rounded-2xl shadow-sm border border-gray-100"
            >
                <div
                    class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mb-5"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-10 w-10 text-indigo-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                        />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-700 mb-2">
                    Keranjang masih kosong
                </h2>
                <p class="text-gray-400 mb-6 text-center max-w-xs">
                    Tambahkan produk favoritmu untuk mulai berbelanja.
                </p>
                <Link
                    href="/products"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-full transition-colors"
                >
                    Mulai Belanja
                </Link>
            </div>

            <!-- Isi Keranjang -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Daftar Item -->
                <div class="lg:col-span-2 space-y-4">
                    <div
                        v-for="item in cartItems"
                        :key="item.id"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex gap-5 items-start transition hover:shadow-md"
                    >
                        <div
                            class="w-24 h-24 flex-shrink-0 rounded-xl overflow-hidden bg-gray-100"
                        >
                            <img
                                :src="getImageUrl(item.product.image)"
                                :alt="item.product.name"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3
                                class="font-semibold text-gray-900 text-base truncate"
                            >
                                {{ item.product.name }}
                            </h3>
                            <p class="text-sm text-gray-400 mt-0.5">
                                {{ item.product.category }}
                            </p>
                            <p
                                v-if="item.size"
                                class="text-xs text-gray-500 mt-1"
                            >
                                Ukuran:
                                <span class="font-semibold">{{
                                    item.size
                                }}</span>
                            </p>
                            <p class="text-indigo-600 font-bold mt-1">
                                {{ formatPrice(item.product.price) }}
                            </p>

                            <div class="flex items-center justify-between mt-3">
                                <div
                                    class="flex items-center border border-gray-200 rounded-full overflow-hidden"
                                >
                                    <button
                                        @click="
                                            updateQuantity(
                                                item,
                                                item.quantity - 1,
                                            )
                                        "
                                        :disabled="item.quantity <= 1"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M20 12H4"
                                            />
                                        </svg>
                                    </button>
                                    <span
                                        class="w-10 text-center text-sm font-semibold text-gray-800"
                                        >{{ item.quantity }}</span
                                    >
                                    <button
                                        @click="
                                            updateQuantity(
                                                item,
                                                item.quantity + 1,
                                            )
                                        "
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 4v16m8-8H4"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <span
                                    class="text-sm font-semibold text-gray-700"
                                >
                                    {{
                                        formatPrice(
                                            item.product.price * item.quantity,
                                        )
                                    }}
                                </span>

                                <button
                                    @click="removeItem(item)"
                                    class="text-red-400 hover:text-red-600 transition-colors p-1 rounded-full hover:bg-red-50"
                                    title="Hapus item"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <Link
                            href="/products"
                            class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium text-sm transition-colors"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
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
                            Lanjut Belanja
                        </Link>
                    </div>
                </div>

                <!-- Ringkasan Pesanan -->
                <div class="lg:col-span-1">
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24"
                    >
                        <h2 class="text-lg font-bold text-gray-900 mb-5">
                            Ringkasan Pesanan
                        </h2>

                        <div class="space-y-3 text-sm text-gray-600">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span class="font-medium text-gray-800">{{
                                    formatPrice(subtotal)
                                }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Ongkos Kirim</span>
                                <span
                                    v-if="shippingCost !== null"
                                    class="font-medium text-gray-800"
                                    >{{ formatPrice(shippingCost) }}</span
                                >
                                <span
                                    v-else
                                    class="text-xs text-gray-400 italic"
                                    >Dihitung saat checkout</span
                                >
                            </div>
                            <div
                                class="border-t border-gray-100 pt-3 flex justify-between text-base font-bold text-gray-900"
                            >
                                <span>Total</span>
                                <span class="text-indigo-600">{{
                                    formatPrice(total)
                                }}</span>
                            </div>
                        </div>

                        <button
                            @click="openCheckout"
                            class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-full transition-colors text-sm"
                        >
                            Lanjut ke Pembayaran
                        </button>

                        <p
                            class="mt-4 text-center text-xs text-gray-400 flex items-center justify-center gap-1"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-3.5 w-3.5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                />
                            </svg>
                            Transaksi dijamin aman
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modal Form Checkout -->
        <transition name="fade">
            <div
                v-if="showCheckoutForm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
                @click.self="showCheckoutForm = false"
            >
                <div
                    class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-7 relative animate-fadeInUp max-h-[90vh] overflow-y-auto"
                >
                    <!-- Tombol Tutup -->
                    <button
                        @click="showCheckoutForm = false"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition-colors"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <h2 class="text-xl font-bold text-gray-900 mb-1">
                        Detail Pengiriman
                    </h2>
                    <p class="text-sm text-gray-400 mb-6">
                        Isi data di bawah untuk menyelesaikan pesanan Anda.
                    </p>

                    <!-- Ringkasan Mini -->
                    <div
                        class="bg-gray-50 rounded-xl px-4 py-3 mb-5 space-y-2 text-sm"
                    >
                        <div class="flex justify-between">
                            <span class="text-gray-500">Subtotal Produk</span>
                            <span class="text-gray-700">{{
                                formatPrice(subtotal)
                            }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Ongkos Kirim</span>
                            <span v-if="shippingCost !== null" class="text-gray-700">{{ formatPrice(shippingCost) }}</span>
                            <span v-else class="text-xs text-gray-400 italic">Pilih kurir di bawah</span>
                        </div>
                        <div v-if="appliedDiscount > 0" class="flex justify-between text-green-600">
                            <span>Diskon Voucher</span>
                            <span class="font-medium">-{{ formatPrice(appliedDiscount) }}</span>
                        </div>
                        <div
                            class="border-t border-gray-200 pt-2 flex justify-between font-bold"
                        >
                            <span class="text-gray-700">Total Pembayaran</span>
                            <span class="text-indigo-600">{{
                                formatPrice(total)
                            }}</span>
                        </div>
                    </div>

                    <form @submit.prevent="submitCheckout" class="space-y-4">
                        <!-- ── Cek Ongkos Kirim ───────────────────── -->
                        <div class="bg-indigo-50 rounded-xl p-4 space-y-3">
                            <p
                                class="text-xs font-semibold text-indigo-700 uppercase tracking-wide"
                            >
                                Cek Ongkos Kirim
                            </p>

                            <!-- Provinsi -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Provinsi Tujuan
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="shippingForm.province_id"
                                    @change="onProvinceChange"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                >
                                    <option value="">
                                        -- Pilih Provinsi --
                                    </option>
                                    <option
                                        v-for="prov in provinces"
                                        :key="prov.province_id"
                                        :value="prov.province_id"
                                    >
                                        {{ prov.province }}
                                    </option>
                                </select>
                            </div>

                            <!-- Kota -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Kota / Kabupaten Tujuan
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="shippingForm.city_id"
                                    @change="onCityOrCourierChange"
                                    :disabled="
                                        isLoadingCities || cities.length === 0
                                    "
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white disabled:opacity-50"
                                >
                                    <option value="">
                                        {{
                                            isLoadingCities
                                                ? "Memuat kota..."
                                                : "-- Pilih Kota --"
                                        }}
                                    </option>
                                    <option
                                        v-for="city in cities"
                                        :key="city.city_id"
                                        :value="city.city_id"
                                    >
                                        {{ city.type }} {{ city.city_name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Kurir -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Kurir <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="shippingForm.courier"
                                    @change="onCityOrCourierChange"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                >
                                    <option value="">-- Pilih Kurir --</option>
                                    <option
                                        v-for="c in COURIERS"
                                        :key="c.value"
                                        :value="c.value"
                                    >
                                        {{ c.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Tombol Cek Ongkir -->
                            <button
                                type="button"
                                @click="checkOngkir"
                                :disabled="
                                    isLoadingOngkir ||
                                    !shippingForm.city_id ||
                                    !shippingForm.courier
                                "
                                class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold py-2.5 rounded-xl transition-colors"
                            >
                                <span v-if="isLoadingOngkir">Mengecek...</span>
                                <span v-else>Cek Ongkos Kirim</span>
                            </button>

                            <!-- Error -->
                            <p v-if="ongkirError" class="text-red-500 text-xs">
                                {{ ongkirError }}
                            </p>

                            <!-- Hasil Layanan -->
                            <div
                                v-if="shippingServices.length > 0"
                                class="space-y-2 pt-1"
                            >
                                <p class="text-xs font-medium text-gray-500">
                                    Pilih Layanan:
                                </p>
                                <label
                                    v-for="svc in shippingServices"
                                    :key="svc.service"
                                    class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-colors"
                                    :class="
                                        selectedService?.service === svc.service
                                            ? 'border-indigo-500 bg-indigo-50'
                                            : 'border-gray-200 bg-white hover:border-indigo-300'
                                    "
                                >
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="radio"
                                            :value="svc"
                                            v-model="selectedService"
                                            @change="selectService(svc)"
                                            class="text-indigo-600"
                                        />
                                        <div>
                                            <p
                                                class="text-sm font-semibold text-gray-800"
                                            >
                                                {{
                                                    shippingForm.courier.toUpperCase()
                                                }}
                                                {{ svc.service }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                Estimasi
                                                {{ svc.cost[0].etd }} hari
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        class="text-sm font-bold text-indigo-600"
                                    >
                                        {{ formatPrice(svc.cost[0].value) }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- ── Data Penerima ────────────────────────── -->

                        <!-- Alamat Pengiriman -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Alamat Lengkap Pengiriman
                                <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="checkoutForm.shipping_address"
                                rows="3"
                                placeholder="Masukkan alamat lengkap pengiriman..."
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none transition"
                                required
                            ></textarea>
                            <p
                                v-if="checkoutForm.errors.shipping_address"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ checkoutForm.errors.shipping_address }}
                            </p>
                        </div>

                        <!-- Nomor Telepon -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Nomor Telepon
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="checkoutForm.phone"
                                type="tel"
                                placeholder="Contoh: 08123456789"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                required
                            />
                            <p
                                v-if="checkoutForm.errors.phone"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ checkoutForm.errors.phone }}
                            </p>
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <textarea
                                v-model="checkoutForm.notes"
                                rows="2"
                                placeholder="Instruksi khusus untuk kurir, dll."
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none transition"
                            ></textarea>
                        </div>

                        <!-- Kode Voucher -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Kode Voucher <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <div v-if="!voucherStatus?.valid" class="flex gap-2">
                                <input v-model="voucherInput" type="text" placeholder="Masukkan kode voucher" class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 uppercase" />
                                <button type="button" @click="checkVoucher" :disabled="isCheckingVoucher || !voucherInput" class="px-4 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-indigo-600 transition-colors disabled:opacity-50">
                                    {{ isCheckingVoucher ? '...' : 'Pakai' }}
                                </button>
                            </div>
                            <div v-if="voucherStatus" class="mt-2">
                                <div v-if="voucherStatus.valid" class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-2.5">
                                    <span class="text-sm font-medium text-green-700">✓ {{ voucherStatus.message }}</span>
                                    <button type="button" @click="removeVoucher" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                </div>
                                <p v-else class="text-xs text-red-500 mt-1">{{ voucherStatus.message }}</p>
                            </div>
                        </div>

                        <!-- Tombol Submit -->
                        <button
                            type="submit"
                            :disabled="
                                checkoutForm.processing || !selectedService
                            "
                            class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold py-3 rounded-full transition-colors text-sm mt-2"
                        >
                            <span v-if="checkoutForm.processing"
                                >Memproses...</span
                            >
                            <span v-else-if="!selectedService"
                                >Pilih Layanan Pengiriman Dulu</span
                            >
                            <span v-else>Buat Pesanan Sekarang</span>
                        </button>
                    </form>
                </div>
            </div>
        </transition>

        <Footer />
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.animate-fadeInUp {
    animation: fadeInUp 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
