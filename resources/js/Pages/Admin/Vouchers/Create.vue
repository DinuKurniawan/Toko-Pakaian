<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const props = defineProps({ voucher: { type: Object, default: null } });
const isSidebarOpen = ref(false);

const isEdit = !!props.voucher;

const form = useForm({
    code: props.voucher?.code ?? "",
    description: props.voucher?.description ?? "",
    type: props.voucher?.type ?? "fixed",
    value: props.voucher?.value ?? "",
    min_order: props.voucher?.min_order ?? 0,
    max_uses: props.voucher?.max_uses ?? "",
    expires_at: props.voucher?.expires_at ? props.voucher.expires_at.substring(0, 10) : "",
    is_active: props.voucher?.is_active ?? true,
    _method: isEdit ? "PUT" : undefined,
});

function submit() {
    if (isEdit) {
        form.post(route("admin.vouchers.update", props.voucher.id));
    } else {
        form.post(route("admin.vouchers.store"));
    }
}
</script>

<template>
    <Head :title="(isEdit ? 'Edit' : 'Buat') + ' Voucher | LUMIÈRE'" />
    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900">
        <AdminSidebar :is-sidebar-open="isSidebarOpen" active-page="vouchers" @close-sidebar="isSidebarOpen = false" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-900">{{ isEdit ? 'Edit' : 'Buat' }} Voucher</h2>
                </div>
                <Link :href="route('admin.vouchers.index')" class="text-sm font-medium text-gray-500 hover:text-indigo-600">&larr; Kembali</Link>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div class="max-w-2xl">
                    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ isEdit ? 'Edit' : 'Buat' }} Voucher Diskon</h1>

                    <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Kode Voucher</label>
                                <input v-model="form.code" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm uppercase" placeholder="HEMAT20" />
                                <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi (opsional)</label>
                                <input v-model="form.description" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="Diskon untuk pelanggan baru" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe Diskon</label>
                                <select v-model="form.type" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                                    <option value="fixed">Nominal (Rp)</option>
                                    <option value="percent">Persentase (%)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nilai Diskon</label>
                                <input v-model="form.value" type="number" min="1" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm" :placeholder="form.type === 'percent' ? '20' : '50000'" />
                                <p v-if="form.errors.value" class="mt-1 text-sm text-red-600">{{ form.errors.value }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Minimum Order (Rp)</label>
                                <input v-model="form.min_order" type="number" min="0" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="0 = tanpa minimum" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Batas Penggunaan (kosong = unlimited)</label>
                                <input v-model="form.max_uses" type="number" min="1" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="100" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Kedaluwarsa (kosong = tidak ada)</label>
                            <input v-model="form.expires_at" type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm" />
                        </div>

                        <div class="flex items-center gap-3">
                            <input id="is_active" v-model="form.is_active" type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded" />
                            <label for="is_active" class="text-sm font-medium text-gray-700">Voucher aktif</label>
                        </div>

                        <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center px-6 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-indigo-600 transition-colors shadow-sm disabled:opacity-50">
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>{{ isEdit ? 'Simpan Perubahan' : 'Buat Voucher' }}</span>
                            </button>
                            <Link :href="route('admin.vouchers.index')" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</Link>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</template>
