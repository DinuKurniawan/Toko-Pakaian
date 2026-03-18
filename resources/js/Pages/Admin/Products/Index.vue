<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const props = defineProps({
    products: Object,
});

// Sidebar mobile toggle
const isSidebarOpen = ref(false);

// Delete confirmation
const showDeleteModal = ref(false);
const productToDelete = ref(null);

function confirmDelete(product) {
    productToDelete.value = product;
    showDeleteModal.value = true;
}

function deleteProduct() {
    router.delete(route("admin.products.destroy", productToDelete.value.id), {
        onFinish: () => {
            showDeleteModal.value = false;
            productToDelete.value = null;
        },
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(price);
}

function getImageUrl(image) {
    if (!image) return "";
    if (image.startsWith("http")) return image;
    return `/storage/${image}`;
}
</script>

<template>
    <Head title="Kelola Produk | LUMIÈRE" />

    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900">
        <AdminSidebar
            :is-sidebar-open="isSidebarOpen"
            active-page="products"
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
                        Kelola Produk
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
                            Daftar Produk
                        </h1>
                        <p class="text-gray-500 mt-1">
                            Kelola semua produk toko Anda.
                        </p>
                    </div>
                    <Link
                        :href="route('admin.products.create')"
                        class="inline-flex items-center bg-gray-900 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-600 transition-colors shadow-sm"
                    >
                        + Tambah Produk
                    </Link>
                </div>

                <!-- Products Table -->
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
                                        Produk
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Harga
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Kategori
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Featured
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
                                    v-for="product in products.data"
                                    :key="product.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0"
                                            >
                                                <img
                                                    :src="
                                                        getImageUrl(
                                                            product.image,
                                                        )
                                                    "
                                                    :alt="product.name"
                                                    class="w-full h-full object-cover"
                                                />
                                            </div>
                                            <div class="min-w-0">
                                                <p
                                                    class="text-sm font-semibold text-gray-900 truncate"
                                                >
                                                    {{ product.name }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"
                                    >
                                        {{ formatPrice(product.price) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-blue-100 text-blue-800':
                                                    product.category === 'Pria',
                                                'bg-pink-100 text-pink-800':
                                                    product.category ===
                                                    'Wanita',
                                                'bg-purple-100 text-purple-800':
                                                    product.category ===
                                                    'Unisex',
                                            }"
                                        >
                                            {{ product.category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="product.is_featured"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-50 px-2.5 py-1 rounded-full"
                                        >
                                            <svg
                                                class="w-3.5 h-3.5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            Ya
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs text-gray-400"
                                            >Tidak</span
                                        >
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
                                                        'admin.products.edit',
                                                        product.id,
                                                    )
                                                "
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                @click="confirmDelete(product)"
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="products.data.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-12 text-center text-gray-500"
                                    >
                                        Belum ada produk. Klik "Tambah Produk"
                                        untuk memulai.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="products.last_page > 1"
                        class="px-6 py-4 border-t border-gray-100 flex items-center justify-between"
                    >
                        <p class="text-sm text-gray-500">
                            Menampilkan {{ products.from }} -
                            {{ products.to }} dari {{ products.total }} produk
                        </p>
                        <div class="flex gap-1">
                            <template
                                v-for="link in products.links"
                                :key="link.label"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                                    :class="
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'text-gray-600 hover:bg-gray-100'
                                    "
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
                    Hapus Produk
                </h3>
                <p class="text-sm text-gray-500 mb-6">
                    Apakah Anda yakin ingin menghapus produk
                    <strong>{{ productToDelete?.name }}</strong
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
                        @click="deleteProduct"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
