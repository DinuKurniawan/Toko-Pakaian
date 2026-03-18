<script setup>
import { Link, router } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";

// Import Komponen UI
import Navbar from "@/Components/Navbar.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Footer from "@/Components/Footer.vue";
import SeoHead from "@/Components/SeoHead.vue";

// 1. MENANGKAP DATA NYATA DARI DATABASE (Via Props Inertia)
const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    featuredProducts: {
        type: Array,
        default: () => [],
    },
    allProducts: {
        type: Array,
        default: () => [],
    },
    heroSlides: {
        type: Array,
        default: () => [],
    },
    seo: {
        type: Object,
        default: () => ({}),
    },
});

// State untuk Sidebar Mobile
const isSidebarOpen = ref(false);

// State & Logika untuk Banner Carousel (Slider)
const currentSlide = ref(0);
let slideInterval = null;

const nextSlide = () => {
    if (props.heroSlides && props.heroSlides.length > 0) {
        currentSlide.value = (currentSlide.value + 1) % props.heroSlides.length;
    }
};

const prevSlide = () => {
    if (props.heroSlides && props.heroSlides.length > 0) {
        currentSlide.value =
            (currentSlide.value - 1 + props.heroSlides.length) %
            props.heroSlides.length;
    }
};

const goToSlide = (index) => {
    currentSlide.value = index;
};

// Fungsi memformat angka ke Rupiah
const formatPrice = (value) => {
    if (!value) return "Rp 0";
    let val = (value / 1).toFixed(0).replace(".", ",");
    return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

// FUNGSI PINTAR UNTUK MENGATASI GAMBAR TIDAK MUNCUL
const getImageUrl = (path) => {
    // Jika tidak ada gambar, tampilkan gambar abu-abu kosong
    if (!path) return "https://placehold.co/600x400?text=No+Image";

    // Bersihkan karakter aneh jika ada (seperti tanda kutip atau kurung siku yang tidak sengaja terbawa di DB)
    let cleanPath = String(path).replace(/^["'\[]+|["'\]]+$/g, "");

    // Jika string berisi format markdown link seperti [url](url), ambil url yang di dalam kurung
    if (cleanPath.startsWith("[http") && cleanPath.includes("](")) {
        cleanPath = cleanPath.substring(
            cleanPath.indexOf("](") + 2,
            cleanPath.length - 1,
        );
    }

    // 1. Jika gambar dari internet (seperti Unsplash), gunakan langsung
    if (cleanPath.startsWith("http://") || cleanPath.startsWith("https://")) {
        return cleanPath;
    }

    // 2. Jika path sudah memiliki format storage yang benar
    if (cleanPath.startsWith("/storage/")) {
        return cleanPath;
    }

    // 3. Jika path dari storage lokal tapi formatnya kurang tepat
    if (cleanPath.startsWith("storage/")) {
        return `/${cleanPath}`;
    }

    if (cleanPath.startsWith("/")) {
        return `/storage${cleanPath}`;
    }

    // 4. Default penanganan (misal tersimpan sebagai 'products/gambar.jpg')
    return `/storage/${cleanPath}`;
};

// Fungsi Tambah ke Keranjang
const addToCart = (productId) => {
    router.post("/cart/add", { product_id: productId }, { preserveScroll: true });
};

// Jalankan slider otomatis
onMounted(() => {
    if (props.heroSlides && props.heroSlides.length > 1) {
        slideInterval = setInterval(nextSlide, 5000);
    }
});

onUnmounted(() => {
    if (slideInterval) clearInterval(slideInterval);
});
</script>

<template>
    <SeoHead :seo="seo" />

    <div class="min-h-screen bg-white font-sans text-gray-900 flex flex-col">
        <!-- Komponen Navbar -->
        <Navbar @openSidebar="isSidebarOpen = true" />

        <!-- Komponen Sidebar (Drawer Mobile) -->
        <Sidebar
            :isOpen="isSidebarOpen"
            @closeSidebar="isSidebarOpen = false"
        />

        <main class="flex-grow">
            <!-- ========================================== -->
            <!-- 1. BANNER CAROUSEL -->
            <!-- ========================================== -->
            <div
                v-if="heroSlides && heroSlides.length > 0"
                class="relative bg-gray-900 h-[70vh] min-h-[500px] overflow-hidden group"
            >
                <div
                    v-for="(slide, index) in heroSlides"
                    :key="slide.id"
                    class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                    :class="
                        currentSlide === index
                            ? 'opacity-100 z-10'
                            : 'opacity-0 z-0'
                    "
                >
                    <!-- Menggunakan getImageUrl -->
                    <img
                        :src="getImageUrl(slide.image)"
                        :alt="slide.title"
                        :loading="currentSlide === index ? 'eager' : 'lazy'"
                        :fetchpriority="currentSlide === index ? 'high' : 'low'"
                        decoding="async"
                        class="absolute inset-0 w-full h-full object-cover opacity-50"
                    />

                    <div
                        class="absolute inset-0 flex items-center justify-center"
                    >
                        <div
                            class="text-center px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto transform transition-all duration-700"
                            :class="
                                currentSlide === index
                                    ? 'translate-y-0 opacity-100'
                                    : 'translate-y-10 opacity-0'
                            "
                        >
                            <h1
                                class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl mb-6 drop-shadow-lg"
                            >
                                {{ slide.title }}
                            </h1>
                            <p
                                class="mt-4 text-lg text-gray-200 sm:text-xl md:text-2xl max-w-2xl mx-auto mb-10 drop-shadow-md"
                            >
                                {{ slide.subtitle }}
                            </p>
                            <a
                                :href="slide.cta_link"
                                class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-gray-900 bg-white hover:bg-gray-100 md:py-4 md:text-lg transition-all duration-300 transform hover:scale-105 shadow-lg"
                            >
                                {{ slide.cta_text }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Navigasi Panah Banner -->
                <button
                    v-if="heroSlides.length > 1"
                    @click="prevSlide"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-20 p-2 bg-white/20 hover:bg-white/40 backdrop-blur-sm text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </button>
                <button
                    v-if="heroSlides.length > 1"
                    @click="nextSlide"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-20 p-2 bg-white/20 hover:bg-white/40 backdrop-blur-sm text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                >
                    <svg
                        class="h-6 w-6"
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
                </button>

                <!-- Indikator Dots Banner -->
                <div
                    v-if="heroSlides.length > 1"
                    class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex space-x-3"
                >
                    <button
                        v-for="(_, index) in heroSlides"
                        :key="'dot-' + index"
                        @click="goToSlide(index)"
                        class="w-3 h-3 rounded-full transition-all duration-300"
                        :class="
                            currentSlide === index
                                ? 'bg-white scale-125'
                                : 'bg-white/50 hover:bg-white/80'
                        "
                    ></button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 2. KATEGORI -->
            <!-- ========================================== -->
            <div
                v-if="categories && categories.length > 0"
                class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24"
            >
                <div class="sm:flex sm:items-baseline sm:justify-between mb-8">
                    <h2
                        class="text-2xl font-extrabold tracking-tight text-gray-900"
                    >
                        Jelajahi Kategori
                    </h2>
                </div>

                <div
                    class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6 lg:grid-cols-3 xl:gap-x-8"
                >
                    <Link
                        v-for="category in categories"
                        :key="category.id"
                        :href="`/products?category=${encodeURIComponent(category.name)}`"
                        class="group relative rounded-2xl overflow-hidden cursor-pointer aspect-[4/3] w-full block"
                    >
                        <!-- Menggunakan getImageUrl -->
                        <img
                            :src="getImageUrl(category.image)"
                            :alt="category.name"
                            loading="lazy"
                            decoding="async"
                            class="absolute inset-0 object-cover object-center w-full h-full group-hover:scale-105 transition-transform duration-500 ease-in-out"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent z-10"
                        ></div>
                        <div
                            class="absolute bottom-0 left-0 p-6 flex items-end z-20"
                        >
                            <h3 class="font-semibold text-xl text-white">
                                {{ category.name }}
                            </h3>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 3. PRODUK TERPOPULER -->
            <!-- ========================================== -->
            <div
                v-if="featuredProducts && featuredProducts.length > 0"
                class="bg-gray-50"
            >
                <div
                    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24"
                >
                    <div
                        class="md:flex md:items-center md:justify-between mb-8"
                    >
                        <h2
                            class="text-2xl font-extrabold tracking-tight text-gray-900"
                        >
                            Produk Terpopuler
                        </h2>
                        <Link
                            href="/products"
                            class="hidden text-sm font-medium text-indigo-600 hover:text-indigo-500 md:block"
                        >
                            Lihat semua produk
                            <span aria-hidden="true"> &rarr;</span>
                        </Link>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8"
                    >
                        <div
                            v-for="product in featuredProducts"
                            :key="product.id"
                            class="group relative"
                        >
                            <div
                                class="w-full aspect-[3/4] bg-gray-200 rounded-2xl overflow-hidden group-hover:shadow-lg transition-shadow duration-300 relative"
                            >
                                <!-- Menggunakan getImageUrl -->
                                <img
                                    :src="getImageUrl(product.image)"
                                    :alt="product.name"
                                    loading="lazy"
                                    decoding="async"
                                    class="absolute inset-0 w-full h-full object-center object-cover group-hover:scale-105 transition-transform duration-500"
                                />

                                <div
                                    v-if="product.is_featured"
                                    class="absolute top-3 left-3 bg-white px-2 py-1 rounded text-xs font-bold text-gray-900 shadow-sm z-10"
                                >
                                    Unggulan
                                </div>

                                <button
                                    @click="addToCart(product.id)"
                                    class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-white/90 backdrop-blur text-gray-900 font-semibold py-2 px-4 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-indigo-600 hover:text-white text-sm w-[80%] z-10"
                                >
                                    Tambah ke Keranjang
                                </button>
                            </div>
                            <div class="mt-4 flex justify-between">
                                <div>
                                    <h3
                                        class="text-sm text-gray-700 font-medium"
                                    >
                                        <Link :href="`/products/${product.id}`">
                                            <span
                                                aria-hidden="true"
                                                class="absolute inset-0 z-0"
                                            ></span>
                                            {{ product.name }}
                                        </Link>
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ product.category }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ formatPrice(product.price) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 4. SEMUA PRODUK -->
            <!-- ========================================== -->
            <div v-if="allProducts && allProducts.length > 0" class="bg-white">
                <div
                    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24"
                >
                    <div
                        class="md:flex md:items-center md:justify-between mb-8"
                    >
                        <h2
                            class="text-2xl font-extrabold tracking-tight text-gray-900"
                        >
                            Semua Produk
                        </h2>
                        <Link
                            href="/products"
                            class="hidden text-sm font-medium text-indigo-600 hover:text-indigo-500 md:block"
                        >
                            Lihat katalog lengkap
                            <span aria-hidden="true"> &rarr;</span>
                        </Link>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8"
                    >
                        <div
                            v-for="product in allProducts"
                            :key="'all-' + product.id"
                            class="group relative"
                        >
                            <div
                                class="w-full aspect-[3/4] bg-gray-200 rounded-2xl overflow-hidden group-hover:shadow-lg transition-shadow duration-300 relative"
                            >
                                <img
                                    :src="getImageUrl(product.image)"
                                    :alt="product.name"
                                    loading="lazy"
                                    decoding="async"
                                    class="absolute inset-0 w-full h-full object-center object-cover group-hover:scale-105 transition-transform duration-500"
                                />

                                <div
                                    v-if="product.is_featured"
                                    class="absolute top-3 left-3 bg-white px-2 py-1 rounded text-xs font-bold text-gray-900 shadow-sm z-10"
                                >
                                    Unggulan
                                </div>

                                <button
                                    @click="addToCart(product.id)"
                                    class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-white/90 backdrop-blur text-gray-900 font-semibold py-2 px-4 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-indigo-600 hover:text-white text-sm w-[80%] z-10"
                                >
                                    Tambah ke Keranjang
                                </button>
                            </div>
                            <div class="mt-4 flex justify-between">
                                <div>
                                    <h3
                                        class="text-sm text-gray-700 font-medium"
                                    >
                                        <Link :href="`/products/${product.id}`">
                                            <span
                                                aria-hidden="true"
                                                class="absolute inset-0 z-0"
                                            ></span>
                                            {{ product.name }}
                                        </Link>
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ product.category }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ formatPrice(product.price) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 5. TAMPILAN KOSONG -->
            <!-- ========================================== -->
            <div
                v-if="
                    (!heroSlides || heroSlides.length === 0) &&
                    (!categories || categories.length === 0) &&
                    (!featuredProducts || featuredProducts.length === 0) &&
                    (!allProducts || allProducts.length === 0)
                "
                class="py-32 flex flex-col items-center justify-center text-center px-4"
            >
                <div
                    class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6"
                >
                    <svg
                        class="h-10 w-10 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                        />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Toko Masih Kosong
                </h2>
                <p class="text-gray-500 max-w-md mx-auto mb-8">
                    Website Anda sudah siap, namun belum ada data produk,
                    kategori, atau banner yang ditambahkan di database.
                </p>
                <!-- Tombol ke Dashboard Admin (Jika user adalah admin) -->
                <Link
                    v-if="
                        $page.props.auth.user &&
                        $page.props.auth.user.role === 'admin'
                    "
                    href="/dashboard"
                    class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-full hover:bg-indigo-700 transition-colors"
                >
                    Pergi ke Panel Admin
                </Link>
            </div>
        </main>

        <Footer />
    </div>
</template>
