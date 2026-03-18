<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const isSidebarOpen = ref(false);
const imagePreview = ref(null);

const form = useForm({ name: "", image: null });

function onImageChange(e) {
    const file = e.target.files[0];
    form.image = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = (ev) => { imagePreview.value = ev.target.result; };
        reader.readAsDataURL(file);
    }
}

function submit() {
    form.post(route("admin.categories.store"));
}
</script>

<template>
    <Head title="Tambah Kategori | LUMIÈRE" />

    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900">
        <AdminSidebar :is-sidebar-open="isSidebarOpen" active-page="categories" @close-sidebar="isSidebarOpen = false" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Kategori</h2>
                </div>
                <Link :href="route('admin.categories.index')" class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">&larr; Kembali</Link>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div class="max-w-xl">
                    <h1 class="text-3xl font-bold text-gray-900 mb-8">Tambah Kategori Baru</h1>

                    <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Kategori</label>
                            <input v-model="form.name" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Contoh: Jaket" />
                            <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Thumbnail (opsional)</label>
                            <div class="flex items-center gap-4">
                                <div v-if="imagePreview" class="w-20 h-20 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                                    <img :src="imagePreview" class="w-full h-full object-cover" />
                                </div>
                                <label class="flex-1 flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl py-5 px-4 cursor-pointer hover:border-indigo-400 transition-colors">
                                    <svg class="w-7 h-7 text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm text-gray-500">Upload gambar</span>
                                    <input type="file" accept="image/*" class="hidden" @change="onImageChange" />
                                </label>
                            </div>
                            <p v-if="form.errors.image" class="mt-1.5 text-sm text-red-600">{{ form.errors.image }}</p>
                        </div>

                        <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center px-6 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-indigo-600 transition-colors shadow-sm disabled:opacity-50">
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>Simpan Kategori</span>
                            </button>
                            <Link :href="route('admin.categories.index')" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</Link>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</template>
