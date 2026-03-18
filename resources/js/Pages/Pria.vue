<script setup>
import { Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Navbar from "@/Components/Navbar.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Footer from "@/Components/Footer.vue";
import SeoHead from "@/Components/SeoHead.vue";

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    seo: {
        type: Object,
        default: () => ({}),
    },
});

const isSidebarOpen = ref(false);

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

const addToCart = (productId) => {
    router.post(
        "/cart/add",
        { product_id: productId },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <SeoHead :seo="seo" />

    <div class="min-h-screen bg-white font-sans text-gray-900 flex flex-col">
        <Navbar @openSidebar="isSidebarOpen = true" />
        <Sidebar
            :isOpen="isSidebarOpen"
            @closeSidebar="isSidebarOpen = false"
        />

        <main class="flex-grow">
            <!-- Hero Section -->
            <section
                class="relative bg-gray-900 h-[50vh] min-h-[350px] overflow-hidden flex items-center justify-center"
            >
                <div
                    class="absolute inset-0 bg-gradient-to-r from-indigo-900/80 to-gray-900/60"
                ></div>
                <div class="relative z-10 text-center px-4 max-w-3xl mx-auto">
                    <h1
                        class="text-4xl md:text-5xl font-extrabold text-white mb-4 drop-shadow-lg"
                    >
                        Koleksi Pria
                    </h1>
                    <p class="text-lg text-gray-200 mb-6 max-w-xl mx-auto">
                        Temukan gaya terbaik untuk pria modern, mulai dari
                        kasual hingga formal.
                    </p>
                    <Link
                        href="/products?category=Pria"
                        class="inline-flex items-center px-6 py-3 bg-white text-gray-900 font-semibold rounded-full hover:bg-gray-100 transition shadow-lg"
                    >
                        Lihat Semua Produk Pria
                    </Link>
                </div>
            </section>

            <!-- Produk Grid -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div
                    class="flex items-baseline justify-between border-b border-gray-200 pb-6 mb-8"
                >
                    <h2
                        class="text-2xl font-extrabold tracking-tight text-gray-900"
                    >
                        Produk Pria
                    </h2>
                    <div class="text-sm text-gray-500">
                        {{ products.length }} produk
                    </div>
                </div>

                <div
                    v-if="products.length > 0"
                    class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8"
                >
                    <div
                        v-for="product in products"
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
                            <div
                                v-if="product.is_featured"
                                class="absolute top-2 left-2 bg-white px-2 py-1 rounded text-[10px] font-bold text-gray-900 uppercase tracking-wider shadow-sm z-10"
                            >
                                Unggulan
                            </div>
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
                                    {{ product.name }}
                                </h3>
                            </div>
                            <p class="text-sm font-bold text-gray-900 mt-2">
                                {{ formatPrice(product.price) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tampilan Kosong -->
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
                        Belum ada produk pria
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 max-w-sm">
                        Koleksi pria sedang dipersiapkan. Silakan cek kembali
                        nanti.
                    </p>
                    <Link
                        href="/products"
                        class="mt-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
                    >
                        Lihat Semua Produk
                    </Link>
                </div>
            </section>
        </main>

        <Footer />
    </div>
</template>
