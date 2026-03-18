<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const props = defineProps({ categories: Object });

const isSidebarOpen = ref(false);
const showDeleteModal = ref(false);
const categoryToDelete = ref(null);

function confirmDelete(cat) {
    categoryToDelete.value = cat;
    showDeleteModal.value = true;
}

function deleteCategory() {
    router.delete(route("admin.categories.destroy", categoryToDelete.value.id), {
        onFinish: () => {
            showDeleteModal.value = false;
            categoryToDelete.value = null;
        },
    });
}

function getImageUrl(image) {
    if (!image) return null;
    if (image.startsWith("http")) return image;
    return `/storage/${image}`;
}
</script>

<template>
    <Head title="Kelola Kategori | LUMIÈRE" />

    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900">
        <AdminSidebar
            :is-sidebar-open="isSidebarOpen"
            active-page="categories"
            @close-sidebar="isSidebarOpen = false"
        />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-900">Kelola Kategori</h2>
                </div>
                <Link href="/" class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
                    Kembali ke Toko &rarr;
                </Link>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium">
                    {{ $page.props.flash.success }}
                </div>

                <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Daftar Kategori</h1>
                        <p class="text-gray-500 mt-1">Kelola kategori produk toko Anda.</p>
                    </div>
                    <Link :href="route('admin.categories.create')" class="inline-flex items-center bg-gray-900 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-600 transition-colors shadow-sm">
                        + Tambah Kategori
                    </Link>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="cat in categories.data" :key="cat.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 flex items-center justify-center">
                                                <img v-if="getImageUrl(cat.image)" :src="getImageUrl(cat.image)" :alt="cat.name" class="w-full h-full object-cover" />
                                                <svg v-else class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <p class="text-sm font-semibold text-gray-900">{{ cat.name }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('admin.categories.edit', cat.id)" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                                                Edit
                                            </Link>
                                            <button @click="confirmDelete(cat)" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="categories.data.length === 0">
                                    <td colspan="2" class="px-6 py-12 text-center text-gray-500">
                                        Belum ada kategori. Klik "Tambah Kategori" untuk memulai.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-[60] flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-900/50" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Kategori</h3>
                <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus kategori <strong>{{ categoryToDelete?.name }}</strong>?</p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button @click="deleteCategory" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
