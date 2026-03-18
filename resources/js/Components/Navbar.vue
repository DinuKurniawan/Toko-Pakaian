<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { computed, ref, nextTick } from "vue";

// Mengambil data global dari Inertia (seperti status auth yang kita setting di HandleInertiaRequests)
const page = usePage();
const user = computed(() => page.props.auth.user);
const emit = defineEmits(["openSidebar"]);

// State untuk search modal
const isSearchOpen = ref(false);
const searchQuery = ref("");
const searchInputRef = ref(null);

// Buka modal search dan fokus ke input
const openSearch = () => {
    isSearchOpen.value = true;
    nextTick(() => {
        if (searchInputRef.value) searchInputRef.value.focus();
    });
};

const closeSearch = () => {
    isSearchOpen.value = false;
    searchQuery.value = "";
};

// Proses pencarian (redirect ke halaman pencarian, misal /products?search=...)
const submitSearch = () => {
    if (searchQuery.value.trim() !== "") {
        router.get(`/products?search=${encodeURIComponent(searchQuery.value)}`);
        closeSearch();
    }
};
</script>

<template>
    <div>
        <!-- Navbar Utama -->
        <nav
            class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-gray-100"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Kiri: Tombol Menu Mobile & Logo -->
                    <div class="flex items-center">
                        <button
                            @click="emit('openSidebar')"
                            class="md:hidden mr-4 text-gray-500 hover:text-indigo-600 focus:outline-none"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
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
                        <Link
                            href="/"
                            class="text-2xl font-bold tracking-tighter text-gray-900"
                        >
                            LUMIÈRE<span class="text-indigo-600">.</span>
                        </Link>
                    </div>

                    <!-- Tengah: Menu Desktop -->
                    <div class="hidden md:flex space-x-8 items-center">
                        <Link
                            href="/"
                            class="text-gray-600 hover:text-indigo-600 px-3 py-2 text-sm font-medium transition-colors"
                            >Beranda</Link
                        >
                        <Link
                            href="/pria"
                            class="text-gray-600 hover:text-indigo-600 px-3 py-2 text-sm font-medium transition-colors"
                            >Pria</Link
                        >
                        <Link
                            href="/wanita"
                            class="text-gray-600 hover:text-indigo-600 px-3 py-2 text-sm font-medium transition-colors"
                            >Wanita</Link
                        >
                        <Link
                            href="/koleksi-baru"
                            class="text-gray-600 hover:text-indigo-600 px-3 py-2 text-sm font-medium transition-colors"
                            >Koleksi Baru</Link
                        >

                        <!-- Menu Khusus Admin (Hanya muncul jika yang login adalah admin) -->
                        <Link
                            v-if="user && user.role === 'admin'"
                            href="/dashboard"
                            class="text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-full text-sm font-bold transition-colors border border-indigo-200"
                        >
                            ⚡ Panel Admin
                        </Link>
                    </div>

                    <!-- Kanan: Ikon & Auth -->
                    <div class="flex items-center space-x-4">
                        <!-- Tombol Search -->
                        <button
                            class="text-gray-500 hover:text-indigo-600 transition-colors"
                            @click="openSearch"
                            aria-label="Cari produk"
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
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </button>

                        <!-- TOMBOL WISHLIST (hanya jika login dan bukan admin) -->
                        <Link
                            v-if="user && user.role !== 'admin'"
                            href="/wishlist"
                            class="text-gray-500 hover:text-rose-500 transition-colors"
                            aria-label="Wishlist"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                />
                            </svg>
                        </Link>

                        <!-- TOMBOL KERANJANG -->
                        <Link
                            href="/cart"
                            class="text-gray-500 hover:text-indigo-600 transition-colors relative"
                            aria-label="Keranjang belanja"
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
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                />
                            </svg>
                            <!-- Badge Keranjang Dinamis (Hanya muncul jika cartCount > 0) -->
                            <span
                                v-if="page.props.cartCount > 0"
                                class="absolute -top-1 -right-1 bg-indigo-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                            >
                                {{ page.props.cartCount }}
                            </span>
                        </Link>

                        <div
                            class="hidden sm:flex items-center space-x-2 pl-4 border-l border-gray-200"
                        >
                            <!-- JIKA SUDAH LOGIN -->
                            <div
                                v-if="user"
                                class="flex items-center space-x-3"
                            >
                                <div class="text-sm font-medium text-gray-700">
                                    Halo, {{ user.name.split(" ")[0] }}
                                </div>
                                <Link
                                    v-if="user.role !== 'admin'"
                                    href="/dashboard"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-full transition-colors border border-indigo-200"
                                >
                                    Dashboard
                                </Link>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="text-sm font-medium text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-full transition-colors"
                                >
                                    Keluar
                                </Link>
                            </div>

                            <!-- JIKA BELUM LOGIN (GUEST) -->
                            <div v-else class="flex items-center space-x-2">
                                <Link
                                    :href="route('login')"
                                    class="text-sm font-medium text-gray-600 hover:text-indigo-600 px-2 py-2"
                                    >Masuk</Link
                                >
                                <Link
                                    :href="route('register')"
                                    class="text-sm font-medium text-white bg-gray-900 hover:bg-indigo-600 px-4 py-2 rounded-full transition-colors"
                                    >Daftar</Link
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <!-- Modal Search Dropdown -->
        <transition name="fade">
            <div
                v-if="isSearchOpen"
                class="fixed inset-0 z-50 flex items-start justify-center bg-black/40 backdrop-blur-sm"
                @click.self="closeSearch"
            >
                <div
                    class="mt-32 bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto p-6 relative animate-fadeInUp"
                >
                    <form @submit.prevent="submitSearch">
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari produk..."
                            class="w-full border border-gray-300 rounded-full px-5 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            @keydown.esc="closeSearch"
                        />
                        <button
                            type="submit"
                            class="absolute right-8 top-1/2 -translate-y-1/2 text-indigo-600 hover:text-indigo-800"
                            aria-label="Cari"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </button>
                    </form>
                    <button
                        @click="closeSearch"
                        class="absolute top-3 right-3 text-gray-400 hover:text-gray-700"
                        aria-label="Tutup"
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
                </div>
            </div>
        </transition>
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
    animation: fadeInUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
