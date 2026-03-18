<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(["closeSidebar"]);
</script>

<template>
    <!-- Overlay Latar Belakang Gelap -->
    <div
        v-if="isOpen"
        @click="emit('closeSidebar')"
        class="fixed inset-0 bg-black/50 z-50 transition-opacity md:hidden"
    ></div>

    <!-- Panel Sidebar Menu -->
    <div
        class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl z-50 transform transition-transform duration-300 ease-in-out md:hidden flex flex-col"
        :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <div
            class="flex items-center justify-between px-4 py-5 border-b border-gray-100"
        >
            <span class="text-xl font-bold tracking-tighter text-gray-900">
                LUMIÈRE<span class="text-indigo-600">.</span>
            </span>
            <button
                @click="emit('closeSidebar')"
                class="text-gray-500 hover:text-indigo-600"
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
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto py-4">
            <nav class="px-4 space-y-2">
                <Link
                    href="/"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-50 hover:text-indigo-600"
                    >Beranda</Link
                >

                <!-- UBAH BAGIAN INI: Mengarahkan tag <a> menjadi <Link> -->
                <Link
                    href="/products?category=Pria"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-50 hover:text-indigo-600"
                    >Pria</Link
                >
                <Link
                    href="/products?category=Wanita"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-50 hover:text-indigo-600"
                    >Wanita</Link
                >
                <Link
                    href="/products?sort=new"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-50 hover:text-indigo-600"
                    >Koleksi Baru</Link
                >
            </nav>
        </div>

        <div class="p-4 border-t border-gray-100">
            <Link
                href="/login"
                class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 mb-3"
            >
                Masuk
            </Link>
            <Link
                href="/register"
                class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-gray-900 hover:bg-indigo-600"
            >
                Daftar
            </Link>
        </div>
    </div>
</template>
