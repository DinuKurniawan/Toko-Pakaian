<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const props = defineProps({
    user: Object,
});

const isSidebarOpen = ref(false);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    password: "",
    password_confirmation: "",
});

function submit() {
    form.put(route("admin.users.update", props.user.id));
}
</script>

<template>
    <Head :title="`Edit User: ${user.name} | LUMIÈRE`" />

    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900">
        <AdminSidebar
            :is-sidebar-open="isSidebarOpen"
            active-page="users"
            @close-sidebar="isSidebarOpen = false"
        />

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
                        Edit User
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
                <!-- Back -->
                <Link
                    :href="route('admin.users.show', user.id)"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 mb-6 transition-colors"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                    Kembali ke Detail User
                </Link>

                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
                    <p class="text-gray-500 mt-1">
                        Perbarui informasi akun
                        <span class="font-medium text-gray-700">{{
                            user.name
                        }}</span
                        >.
                    </p>
                </div>

                <!-- Form -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 max-w-xl"
                >
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Name -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                                >Nama</label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-red-400': form.errors.name }"
                                placeholder="Nama lengkap"
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                                >Email</label
                            >
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-red-400': form.errors.email }"
                                placeholder="contoh@email.com"
                            />
                            <p
                                v-if="form.errors.email"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Role -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                                >Role</label
                            >
                            <select
                                v-model="form.role"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                :class="{ 'border-red-400': form.errors.role }"
                            >
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                            <p
                                v-if="form.errors.role"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.role }}
                            </p>
                        </div>

                        <!-- Password -->
                        <div class="pt-2 border-t border-gray-100">
                            <p class="text-xs text-gray-400 mb-3">
                                Kosongkan jika tidak ingin mengubah password.
                            </p>
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1.5"
                                        >Password Baru</label
                                    >
                                    <input
                                        v-model="form.password"
                                        type="password"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{
                                            'border-red-400':
                                                form.errors.password,
                                        }"
                                        placeholder="Min. 8 karakter"
                                        autocomplete="new-password"
                                    />
                                    <p
                                        v-if="form.errors.password"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ form.errors.password }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1.5"
                                        >Konfirmasi Password</label
                                    >
                                    <input
                                        v-model="form.password_confirmation"
                                        type="password"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Ulangi password baru"
                                        autocomplete="new-password"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-xl transition-colors"
                            >
                                {{
                                    form.processing
                                        ? "Menyimpan..."
                                        : "Simpan Perubahan"
                                }}
                            </button>
                            <Link
                                :href="route('admin.users.show', user.id)"
                                class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors"
                            >
                                Batal
                            </Link>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</template>
