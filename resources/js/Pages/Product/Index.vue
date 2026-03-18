<script setup>
import { Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Navbar from "@/Components/Navbar.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Footer from "@/Components/Footer.vue";
import SeoHead from "@/Components/SeoHead.vue";

const props = defineProps({
    products: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
});

const isSidebarOpen = ref(false);
const searchQuery = ref(props.filters.search ?? "");
const selectedCategory = ref(props.filters.category ?? "");
const selectedSort = ref(props.filters.sort ?? "");
const productItems = computed(() => props.products?.data ?? []);
const totalProducts = computed(
    () => props.products?.total ?? productItems.value.length,
);

const pageTitle = computed(() => {
    if (searchQuery.value) return `Hasil pencarian: "${searchQuery.value}"`;
    if (props.filters.category) return `Koleksi ${props.filters.category}`;
    if (props.filters.sort === "new") return "Koleksi Terbaru";
    return "Semua Produk";
});

const formatPrice = (value) => {
    if (!value) return "Rp 0";
    let val = (value / 1).toFixed(0).replace(".", ",");
    return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const getImageUrl = (path) => {
    if (!path) return "https://placehold.co/600x400?text=No+Image";
    let cleanPath = String(path).replace(/^["'\[]+|["'\]]+$/g, "");
    if (cleanPath.startsWith("http://") || cleanPath.startsWith("https://")) return cleanPath;
    if (cleanPath.startsWith("/storage/")) return cleanPath;
    if (cleanPath.startsWith("storage/")) return `/${cleanPath}`;
    if (cleanPath.startsWith("/")) return `/storage${cleanPath}`;
    return `/storage/${cleanPath}`;
};

const addToCart = (productId) => {
    router.post("/cart/add", { product_id: productId }, { preserveScroll: true });
};

function applyFilters() {
    const params = {};
    if (searchQuery.value) params.search = searchQuery.value;
    if (selectedCategory.value) params.category = selectedCategory.value;
    if (selectedSort.value) params.sort = selectedSort.value;
    router.get("/products", params, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function clearFilters() {
    searchQuery.value = "";
    selectedCategory.value = "";
    selectedSort.value = "";
    router.get("/products", {}, { preserveState: true, replace: true });
}
</script>

<template>
    <SeoHead :seo="seo" />

    <div class="min-h-screen bg-gray-50 font-sans text-gray-900 flex flex-col">
        <!-- Komponen Navbar -->
        <Navbar @openSidebar="isSidebarOpen = true" />
        <Sidebar
            :isOpen="isSidebarOpen"
            @closeSidebar="isSidebarOpen = false"
        />

        <main class="flex-grow pt-8 pb-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Search & Filter Bar -->
                <div class="mb-6 mt-4 bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <div class="flex flex-wrap items-end gap-3">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Cari Produk</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input v-model="searchQuery" type="text" placeholder="Cari nama atau deskripsi produk..." @keyup.enter="applyFilters" class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Kategori</label>
                            <select v-model="selectedCategory" class="px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                                <option value="">Semua</option>
                                <option value="Pria">Pria</option>
                                <option value="Wanita">Wanita</option>
                                <option value="Unisex">Unisex</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Urutan</label>
                            <select v-model="selectedSort" class="px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                                <option value="">A-Z</option>
                                <option value="new">Terbaru</option>
                                <option value="price_asc">Harga Terendah</option>
                                <option value="price_desc">Harga Tertinggi</option>
                            </select>
                        </div>
                        <button @click="applyFilters" class="px-5 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">Cari</button>
                        <button v-if="filters.search || filters.category || filters.sort" @click="clearFilters" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-xl hover:bg-gray-200 transition-colors">Reset</button>
                    </div>
                </div>

                <!-- Header Katalog -->
                <div class="flex items-baseline justify-between border-b border-gray-200 pb-6 mb-8">
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">{{ pageTitle }}</h1>
                    <div class="text-sm text-gray-500">Menampilkan {{ totalProducts }} produk</div>
                </div>

                <!-- Grid Produk -->
                <div
                    v-if="productItems.length > 0"
                    class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8"
                >
                    <div
                        v-for="product in productItems"
                        :key="product.id"
                        class="group relative bg-white p-3 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow"
                    >
                        <div
                            class="w-full aspect-[3/4] bg-gray-200 rounded-xl overflow-hidden relative"
                        >
                            <img
                                :src="getImageUrl(product.image)"
                                :alt="product.name"
                                loading="lazy"
                                decoding="async"
                                class="absolute inset-0 w-full h-full object-center object-cover group-hover:scale-105 transition-transform duration-500"
                            />

                            <!-- Label -->
                            <div
                                v-if="
                                    filters.sort === 'new' ||
                                    product.is_featured
                                "
                                class="absolute top-2 left-2 bg-white px-2 py-1 rounded text-[10px] font-bold text-gray-900 uppercase tracking-wider shadow-sm z-10"
                            >
                                Baru
                            </div>

                            <!-- Tombol Keranjang -->
                            <button
                                @click="addToCart(product.id)"
                                class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-white/90 backdrop-blur text-gray-900 font-semibold py-2 px-4 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-indigo-600 hover:text-white text-xs w-[85%] z-10"
                            >
                                Tambah ke Keranjang
                            </button>
                        </div>
                        <div class="mt-4 px-1 flex flex-col justify-between">
                            <div>
                                <p
                                    class="text-xs text-indigo-600 font-semibold mb-1 uppercase tracking-wider"
                                >
                                    {{ product.category }}
                                </p>
                                <h3
                                    class="text-sm text-gray-900 font-medium line-clamp-1"
                                >
                                    <Link :href="`/products/${product.id}`">
                                        <span
                                            aria-hidden="true"
                                            class="absolute inset-0 z-0"
                                        ></span>
                                        {{ product.name }}
                                    </Link>
                                </h3>
                            </div>
                            <p class="text-sm font-bold text-gray-900 mt-2">
                                {{ formatPrice(product.price) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="products.last_page > 1"
                    class="mt-10 flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm text-gray-500">
                        Menampilkan {{ products.from }} - {{ products.to }} dari {{ products.total }} produk
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <template v-for="link in products.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                                :class="link.active ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
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

                <!-- Tampilan Kosong (Jika Kategori Tidak Ada Produk) -->
                <div
                    v-else
                    class="py-20 flex flex-col items-center justify-center text-center"
                >
                    <svg
                        class="h-12 w-12 text-gray-300 mb-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                        />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">
                        Tidak ada produk
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 max-w-sm">
                        Maaf, kami belum memiliki koleksi pakaian untuk kategori
                        ini. Silakan cek kembali nanti.
                    </p>
                    <Link
                        href="/products"
                        class="mt-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
                    >
                        Lihat Semua Produk
                    </Link>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
