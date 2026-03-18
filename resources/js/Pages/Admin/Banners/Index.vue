<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const props = defineProps({
    banners: Array,
});

const isSidebarOpen = ref(false);

const showDeleteModal = ref(false);
const bannerToDelete = ref(null);

function confirmDelete(banner) {
    bannerToDelete.value = banner;
    showDeleteModal.value = true;
}

function deleteBanner() {
    router.delete(route("admin.banners.destroy", bannerToDelete.value.id), {
        onFinish: () => {
            showDeleteModal.value = false;
            bannerToDelete.value = null;
        },
    });
}

function getImageUrl(image) {
    if (!image) return "";
    if (image.startsWith("http")) return image;
    return `/storage/${image}`;
}
</script>

<template>
    <Head title="Kelola Banner | LUMIÈRE" />

    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900">
        <AdminSidebar
            :is-sidebar-open="isSidebarOpen"
            active-page="banners"
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
                        Kelola Banner
                    </h2>
                </div>
                <Link
                    href="/"
                    class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors"
                >
                    Kembali ke Toko &rarr;
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

                <!-- Header -->
                <div
                    class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4"
                >
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Daftar Banner
                        </h1>
                        <p class="text-gray-500 mt-1">
                            Kelola banner carousel di halaman utama.
                        </p>
                    </div>
                    <Link
                        :href="route('admin.banners.create')"
                        class="inline-flex items-center bg-gray-900 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-600 transition-colors shadow-sm"
                    >
                        + Tambah Banner
                    </Link>
                </div>

                <!-- Banners Table -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Banner
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Subtitle
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        CTA
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="banner in banners"
                                    :key="banner.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-20 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0"
                                            >
                                                <img
                                                    :src="
                                                        getImageUrl(
                                                            banner.image,
                                                        )
                                                    "
                                                    :alt="banner.title"
                                                    class="w-full h-full object-cover"
                                                />
                                            </div>
                                            <div class="min-w-0">
                                                <p
                                                    class="text-sm font-semibold text-gray-900 truncate"
                                                >
                                                    {{ banner.title }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 max-w-xs truncate"
                                    >
                                        {{ banner.subtitle }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800"
                                        >
                                            {{ banner.cta_text }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm"
                                    >
                                        <div
                                            class="flex items-center justify-end gap-2"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'admin.banners.edit',
                                                        banner.id,
                                                    )
                                                "
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                @click="confirmDelete(banner)"
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="banners.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-6 py-12 text-center text-gray-500"
                                    >
                                        Belum ada banner. Klik "Tambah Banner"
                                        untuk memulai.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-[60] flex items-center justify-center"
        >
            <div
                class="fixed inset-0 bg-gray-900/50"
                @click="showDeleteModal = false"
            ></div>
            <div
                class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4"
            >
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    Hapus Banner
                </h3>
                <p class="text-sm text-gray-500 mb-6">
                    Apakah Anda yakin ingin menghapus banner
                    <strong>{{ bannerToDelete?.title }}</strong
                    >? Tindakan ini tidak bisa dibatalkan.
                </p>
                <div class="flex gap-3 justify-end">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        @click="deleteBanner"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
