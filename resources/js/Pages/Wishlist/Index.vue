<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Navbar from "@/Components/Navbar.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Footer from "@/Components/Footer.vue";

const props = defineProps({ wishlists: { type: Array, default: () => [] } });
const isSidebarOpen = ref(false);

function getImageUrl(path) {
    if (!path) return "https://placehold.co/600x400?text=No+Image";
    let p = String(path).replace(/^["'\[]+|["'\]]+$/g, "");
    if (p.startsWith("http://") || p.startsWith("https://")) return p;
    if (p.startsWith("/storage/")) return p;
    if (p.startsWith("storage/")) return `/${p}`;
    if (p.startsWith("/")) return `/storage${p}`;
    return `/storage/${p}`;
}

function formatPrice(value) {
    if (!value) return "Rp 0";
    return "Rp " + (value / 1).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function removeFromWishlist(productId) {
    router.post("/wishlist/toggle", { product_id: productId }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Wishlist Saya | LUMIÈRE" />

    <div class="min-h-screen bg-gray-50 font-sans text-gray-900 flex flex-col">
        <Navbar @openSidebar="isSidebarOpen = true" />
        <Sidebar :isOpen="isSidebarOpen" @closeSidebar="isSidebarOpen = false" />

        <main class="flex-grow pt-8 pb-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-baseline justify-between border-b border-gray-200 pb-6 mb-8 mt-4">
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Wishlist Saya</h1>
                    <div class="text-sm text-gray-500">{{ wishlists.length }} produk</div>
                </div>

                <div v-if="wishlists.length > 0" class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8">
                    <div v-for="item in wishlists" :key="item.id" class="group relative bg-white p-3 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="w-full aspect-[3/4] bg-gray-200 rounded-xl overflow-hidden relative">
                            <img :src="getImageUrl(item.product.image)" :alt="item.product.name" class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" />

                            <!-- Remove button -->
                            <button @click="removeFromWishlist(item.product.id)" class="absolute top-2 right-2 w-8 h-8 bg-white/90 rounded-full flex items-center justify-center shadow-sm hover:bg-red-50 transition-colors z-10" title="Hapus dari wishlist">
                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4 px-1">
                            <p class="text-xs text-indigo-600 font-semibold mb-1 uppercase tracking-wider">{{ item.product.category }}</p>
                            <h3 class="text-sm text-gray-900 font-medium line-clamp-1">
                                <Link :href="`/products/${item.product.id}`">
                                    <span aria-hidden="true" class="absolute inset-0 z-0"></span>
                                    {{ item.product.name }}
                                </Link>
                            </h3>
                            <p class="text-sm font-bold text-gray-900 mt-2">{{ formatPrice(item.product.price) }}</p>
                        </div>

                        <div class="mt-3 px-1">
                            <Link :href="`/products/${item.product.id}`" class="block w-full text-center py-2 bg-indigo-600 text-white text-xs font-semibold rounded-full hover:bg-indigo-700 transition-colors relative z-10">
                                Lihat Produk
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="py-20 flex flex-col items-center justify-center text-center">
                    <svg class="h-16 w-16 text-gray-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">Wishlist Kosong</h3>
                    <p class="mt-1 text-sm text-gray-500 max-w-sm">Belum ada produk di wishlist. Jelajahi koleksi dan tambahkan produk favorit Anda.</p>
                    <Link href="/products" class="mt-6 inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-full hover:bg-indigo-700 transition-colors">
                        Jelajahi Produk
                    </Link>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
