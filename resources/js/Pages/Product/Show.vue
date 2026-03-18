<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Navbar from "@/Components/Navbar.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Footer from "@/Components/Footer.vue";
import SeoHead from "@/Components/SeoHead.vue";

const props = defineProps({
    product: { type: Object, required: true },
    relatedProducts: { type: Array, default: () => [] },
    isWishlisted: { type: Boolean, default: false },
    reviews: { type: Array, default: () => [] },
    reviewsCount: { type: Number, default: 0 },
    filteredReviewsCount: { type: Number, default: 0 },
    avgRating: { type: Number, default: null },
    reviewFilters: {
        type: Object,
        default: () => ({ sort: "latest", rating: null }),
    },
    ratingBreakdown: { type: Object, default: () => ({}) },
    seo: { type: Object, default: () => ({}) },
});

const page = usePage();
const isSidebarOpen = ref(false);
const added = ref(false);
const selectedSize = ref("");
const sizeError = ref("");
const wishlisted = ref(props.isWishlisted);

const ALL_SIZES = ["S", "M", "L", "XL"];

const availableSizes = computed(() => {
    if (props.product.stock && Object.keys(props.product.stock).length > 0) {
        return ALL_SIZES.filter(s => (props.product.stock[s] ?? 0) > 0);
    }
    if (props.product.sizes && props.product.sizes.length > 0) {
        return props.product.sizes;
    }
    return ALL_SIZES;
});

const stockForSize = (size) => {
    if (!props.product.stock) return null;
    return props.product.stock[size] ?? 0;
};

const formatPrice = (value) => {
    if (!value) return "Rp 0";
    return "Rp " + (value / 1).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const getImageUrl = (path) => {
    if (!path) return "https://placehold.co/600x800?text=No+Image";
    let p = String(path).replace(/^["'\[]+|["'\]]+$/g, "");
    if (p.startsWith("http://") || p.startsWith("https://")) return p;
    if (p.startsWith("/storage/")) return p;
    if (p.startsWith("storage/")) return `/${p}`;
    if (p.startsWith("/")) return `/storage${p}`;
    return `/storage/${p}`;
};

const addToCart = (productId) => {
    if (!selectedSize.value) {
        sizeError.value = "Pilih ukuran terlebih dahulu";
        return;
    }
    sizeError.value = "";
    router.post(
        "/cart/add",
        { product_id: productId, size: selectedSize.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                added.value = true;
                setTimeout(() => (added.value = false), 2000);
            },
        },
    );
};

const toggleWishlist = () => {
    if (!page.props.auth?.user) {
        router.visit("/login");
        return;
    }
    wishlisted.value = !wishlisted.value;
    router.post("/wishlist/toggle", { product_id: props.product.id }, { preserveScroll: true });
};

const selectedReviewSort = computed(() => props.reviewFilters?.sort ?? "latest");
const selectedReviewRating = computed(() => props.reviewFilters?.rating ?? null);
const hasActiveReviewFilters = computed(
    () =>
        selectedReviewSort.value !== "latest" ||
        selectedReviewRating.value !== null,
);

const ratingFilterOptions = computed(() =>
    [5, 4, 3, 2, 1].map((rating) => ({
        rating,
        total: Number(props.ratingBreakdown?.[rating] ?? 0),
    })),
);

const reviewSortOptions = [
    { value: "latest", label: "Terbaru" },
    { value: "oldest", label: "Terlama" },
    { value: "highest_rating", label: "Rating Tertinggi" },
    { value: "lowest_rating", label: "Rating Terendah" },
];

function applyReviewFilters(filters = {}) {
    const nextSort = filters.sort ?? selectedReviewSort.value;
    const nextRating = Object.prototype.hasOwnProperty.call(filters, "rating")
        ? filters.rating
        : selectedReviewRating.value;

    const params = {};

    if (nextSort !== "latest") {
        params.review_sort = nextSort;
    }

    if (nextRating !== null) {
        params.rating = nextRating;
    }

    router.get(route("product.show", props.product.id), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function toggleRatingFilter(rating) {
    applyReviewFilters({
        rating: selectedReviewRating.value === rating ? null : rating,
    });
}
</script>

<template>
    <SeoHead :seo="seo" />

    <div class="min-h-screen bg-white font-sans text-gray-900 flex flex-col">
        <Navbar @openSidebar="isSidebarOpen = true" />
        <Sidebar
            :isOpen="isSidebarOpen"
            @closeSidebar="isSidebarOpen = false"
        />

        <main class="flex-1">
            <!-- Breadcrumb -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-2">
                <nav class="flex items-center gap-2 text-sm text-gray-400">
                    <Link
                        href="/"
                        class="hover:text-indigo-600 transition-colors"
                        >Beranda</Link
                    >
                    <span>/</span>
                    <Link
                        href="/products"
                        class="hover:text-indigo-600 transition-colors"
                        >Produk</Link
                    >
                    <span>/</span>
                    <span class="text-gray-600 truncate max-w-[200px]">{{
                        product.name
                    }}</span>
                </nav>
            </div>

            <!-- Detail Section -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                    <!-- Gambar Produk -->
                    <div
                        class="aspect-[3/4] bg-gray-100 rounded-2xl overflow-hidden shadow-sm"
                    >
                        <img
                            :src="getImageUrl(product.image)"
                            :alt="product.name"
                            loading="eager"
                            fetchpriority="high"
                            decoding="async"
                            class="w-full h-full object-cover object-center"
                        />
                    </div>

                    <!-- Info Produk -->
                    <div class="lg:sticky lg:top-24 space-y-6">
                        <!-- Badge Kategori + Wishlist -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-block bg-indigo-50 text-indigo-700 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">
                                    {{ product.category }}
                                </span>
                                <span v-if="product.is_featured" class="inline-block bg-amber-50 text-amber-700 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">
                                    Unggulan
                                </span>
                            </div>
                            <!-- Wishlist Button -->
                            <button @click="toggleWishlist" class="p-2 rounded-full border transition-all" :class="wishlisted ? 'bg-red-50 border-red-200 text-red-500' : 'bg-white border-gray-200 text-gray-400 hover:border-red-200 hover:text-red-400'" :title="wishlisted ? 'Hapus dari wishlist' : 'Tambah ke wishlist'">
                                <svg class="w-5 h-5" :fill="wishlisted ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </button>
                        </div>

                        <!-- Nama & Harga -->
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 leading-tight">{{ product.name }}</h1>
                            <div class="flex items-center gap-3 mt-2">
                                <p class="text-3xl font-extrabold text-indigo-600">{{ formatPrice(product.price) }}</p>
                                <!-- Rating summary -->
                                <div v-if="avgRating" class="flex items-center gap-1">
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-700">{{ avgRating }}</span>
                                    <span class="text-sm text-gray-400">({{ reviewsCount }} ulasan)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Pilih Ukuran -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Pilih Ukuran</label>
                            <div class="flex gap-2 flex-wrap">
                                <button
                                    v-for="size in ALL_SIZES"
                                    :key="size"
                                    type="button"
                                    @click="availableSizes.includes(size) ? selectedSize = size : null"
                                    :disabled="!availableSizes.includes(size)"
                                    :class="[
                                        'px-4 py-2 rounded-full border font-semibold transition text-sm',
                                        !availableSizes.includes(size)
                                            ? 'bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed line-through'
                                            : selectedSize === size
                                                ? 'bg-indigo-600 text-white border-indigo-600'
                                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100',
                                    ]"
                                >
                                    {{ size }}
                                    <span v-if="product.stock && product.stock[size] !== undefined && availableSizes.includes(size)" class="text-xs opacity-70 ml-1">({{ product.stock[size] }})</span>
                                </button>
                            </div>
                            <div v-if="sizeError" class="text-red-500 text-xs mt-1">{{ sizeError }}</div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-100"></div>

                        <!-- Deskripsi Produk -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Deskripsi Produk</label
                            >
                            <div
                                class="text-gray-700 text-base whitespace-pre-line"
                            >
                                {{ product.description || "-" }}
                            </div>
                        </div>

                        <!-- Info Pengiriman -->
                        <div class="space-y-3 text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <svg
                                    class="w-4 h-4 text-green-500 flex-shrink-0"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                Stok tersedia, siap kirim
                            </div>
                            <div class="flex items-center gap-2">
                                <svg
                                    class="w-4 h-4 text-indigo-500 flex-shrink-0"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                    />
                                </svg>
                                Pengiriman ke seluruh Indonesia
                            </div>
                            <div class="flex items-center gap-2">
                                <svg
                                    class="w-4 h-4 text-indigo-500 flex-shrink-0"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                    />
                                </svg>
                                Transaksi aman & terjamin
                            </div>
                        </div>

                        <!-- Tombol Tambah ke Keranjang -->
                        <button
                            @click="addToCart(product.id)"
                            class="w-full py-4 rounded-full font-bold text-base transition-all duration-300 flex items-center justify-center gap-2"
                            :class="
                                added
                                    ? 'bg-green-500 text-white'
                                    : 'bg-indigo-600 hover:bg-indigo-700 text-white'
                            "
                        >
                            <svg
                                v-if="!added"
                                class="w-5 h-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"
                                />
                            </svg>
                            <svg
                                v-else
                                class="w-5 h-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            <span>{{
                                added ? "Ditambahkan!" : "Tambah ke Keranjang"
                            }}</span>
                        </button>

                        <Link
                            href="/cart"
                            class="block w-full py-3 rounded-full font-semibold text-base text-center border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-50 transition-colors"
                        >
                            Lihat Keranjang
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Reviews Section -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 border-t border-gray-100">
                <div class="flex flex-col gap-5 mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">
                                Ulasan Pelanggan
                                <span v-if="reviewsCount > 0" class="text-sm font-normal text-gray-500 ml-2">({{ reviewsCount }} ulasan)</span>
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                Filter ulasan untuk melihat feedback yang paling relevan sebelum membeli.
                            </p>
                        </div>

                        <div class="w-full max-w-xs">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Urutkan ulasan
                            </label>
                            <select
                                :value="selectedReviewSort"
                                @change="applyReviewFilters({ sort: $event.target.value })"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                            >
                                <option
                                    v-for="option in reviewSortOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row lg:items-center gap-4 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <div class="min-w-[160px]">
                            <p class="text-sm font-medium text-gray-500">
                                Rating rata-rata
                            </p>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-3xl font-bold text-gray-900">
                                    {{ avgRating ?? "—" }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    / 5
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                {{
                                    hasActiveReviewFilters
                                        ? filteredReviewsCount
                                        : reviewsCount
                                }}
                                ulasan cocok
                            </p>
                        </div>

                        <div class="flex-1 flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="applyReviewFilters({ rating: null })"
                                class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm font-medium transition-colors"
                                :class="
                                    selectedReviewRating === null
                                        ? 'border-indigo-600 bg-indigo-600 text-white'
                                        : 'border-gray-200 bg-white text-gray-700 hover:border-indigo-200 hover:text-indigo-700'
                                "
                            >
                                Semua Rating
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs"
                                    :class="
                                        selectedReviewRating === null
                                            ? 'bg-white/20 text-white'
                                            : 'bg-gray-100 text-gray-500'
                                    "
                                >
                                    {{ reviewsCount }}
                                </span>
                            </button>

                            <button
                                v-for="option in ratingFilterOptions"
                                :key="option.rating"
                                type="button"
                                @click="toggleRatingFilter(option.rating)"
                                class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm font-medium transition-colors"
                                :class="
                                    selectedReviewRating === option.rating
                                        ? 'border-indigo-600 bg-indigo-600 text-white'
                                        : 'border-gray-200 bg-white text-gray-700 hover:border-indigo-200 hover:text-indigo-700'
                                "
                            >
                                {{ option.rating }} Bintang
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs"
                                    :class="
                                        selectedReviewRating === option.rating
                                            ? 'bg-white/20 text-white'
                                            : 'bg-gray-100 text-gray-500'
                                    "
                                >
                                    {{ option.total }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="reviews.length > 0" class="space-y-4">
                    <div v-for="review in reviews" :key="review.id" class="bg-gray-50 rounded-2xl p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    {{ review.user?.name?.charAt(0) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-semibold text-gray-900 text-sm">{{ review.user?.name }}</p>
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                            Pembeli terverifikasi
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-0.5 mt-0.5">
                                        <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5" :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">
                                        {{
                                            new Date(review.created_at).toLocaleDateString(
                                                "id-ID",
                                                {
                                                    day: "numeric",
                                                    month: "long",
                                                    year: "numeric",
                                                },
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <p v-if="review.comment" class="mt-3 text-sm text-gray-700">{{ review.comment }}</p>
                    </div>
                </div>

                <p
                    v-if="reviews.length > 0 && (filteredReviewsCount > reviews.length || hasActiveReviewFilters)"
                    class="mt-4 text-sm text-gray-500"
                >
                    Menampilkan {{ reviews.length }} dari {{ filteredReviewsCount || reviewsCount }} ulasan yang cocok.
                </p>

                <div v-else class="text-center py-10 text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="text-sm">
                        {{
                            hasActiveReviewFilters
                                ? "Belum ada ulasan yang cocok dengan filter ini."
                                : "Belum ada ulasan untuk produk ini."
                        }}
                    </p>
                    <button
                        v-if="hasActiveReviewFilters"
                        type="button"
                        @click="applyReviewFilters({ sort: 'latest', rating: null })"
                        class="mt-3 inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition-colors hover:bg-indigo-100"
                    >
                        Reset Filter Ulasan
                    </button>
                </div>
            </section>

            <!-- Produk Serupa -->
            <section
                v-if="relatedProducts.length > 0"
                class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 border-t border-gray-100"
            >
                <h2 class="text-xl font-bold text-gray-900 mb-6">
                    Produk Serupa
                </h2>
                <div
                    class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5"
                >
                    <Link
                        v-for="rel in relatedProducts"
                        :key="rel.id"
                        :href="`/products/${rel.id}`"
                        class="group block bg-white rounded-2xl shadow-sm border border-gray-100 p-3 hover:shadow-md transition-shadow"
                    >
                        <div
                            class="aspect-[3/4] bg-gray-100 rounded-xl overflow-hidden mb-3"
                        >
                            <img
                                :src="getImageUrl(rel.image)"
                                :alt="rel.name"
                                loading="lazy"
                                decoding="async"
                                class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
                            />
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 truncate">
                            {{ rel.name }}
                        </h3>
                        <p class="text-sm font-bold text-indigo-600 mt-1">
                            {{ formatPrice(rel.price) }}
                        </p>
                    </Link>
                </div>
            </section>
        </main>

        <Footer />
    </div>
</template>
