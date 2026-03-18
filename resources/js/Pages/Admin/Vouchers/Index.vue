<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const props = defineProps({ vouchers: Object });
const isSidebarOpen = ref(false);
const showDeleteModal = ref(false);
const toDelete = ref(null);

function confirmDelete(v) { toDelete.value = v; showDeleteModal.value = true; }
function doDelete() {
    router.delete(route("admin.vouchers.destroy", toDelete.value.id), {
        onFinish: () => { showDeleteModal.value = false; toDelete.value = null; },
    });
}

function formatPrice(v) {
    return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(v);
}

function formatDate(d) {
    if (!d) return "-";
    return new Date(d).toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
}
</script>

<template>
    <Head title="Kelola Voucher | LUMIÈRE" />
    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900">
        <AdminSidebar :is-sidebar-open="isSidebarOpen" active-page="vouchers" @close-sidebar="isSidebarOpen = false" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-900">Kelola Voucher</h2>
                </div>
                <Link href="/" class="text-sm font-medium text-gray-500 hover:text-indigo-600">Kembali ke Toko &rarr;</Link>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium">{{ $page.props.flash.success }}</div>

                <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Daftar Voucher</h1>
                        <p class="text-gray-500 mt-1">Buat dan kelola kode diskon untuk pelanggan.</p>
                    </div>
                    <Link :href="route('admin.vouchers.create')" class="inline-flex items-center bg-gray-900 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-600 transition-colors shadow-sm">
                        + Buat Voucher
                    </Link>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diskon</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min. Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penggunaan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kedaluwarsa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="v in vouchers.data" :key="v.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono font-bold text-sm text-indigo-700 bg-indigo-50 px-2 py-1 rounded">{{ v.code }}</span>
                                        <p v-if="v.description" class="text-xs text-gray-400 mt-0.5">{{ v.description }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ v.type === 'percent' ? v.value + '%' : formatPrice(v.value) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ v.min_order > 0 ? formatPrice(v.min_order) : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ v.used_count }} / {{ v.max_uses ?? '∞' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(v.expires_at) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="v.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" class="px-2.5 py-1 rounded-full text-xs font-semibold">
                                            {{ v.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('admin.vouchers.edit', v.id)" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">Edit</Link>
                                            <button @click="confirmDelete(v)" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="vouchers.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">Belum ada voucher.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>

        <div v-if="showDeleteModal" class="fixed inset-0 z-[60] flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-900/50" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Voucher</h3>
                <p class="text-sm text-gray-500 mb-6">Hapus voucher <strong>{{ toDelete?.code }}</strong>?</p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button @click="doDelete" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
