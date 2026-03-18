<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <Head title="Lupa Kata Sandi | LUMIÈRE" />

    <div class="min-h-screen flex bg-white font-sans text-gray-900">
        <!-- Bagian Kiri: Gambar (Hidden di mobile) -->
        <div
            class="hidden lg:flex lg:w-1/2 relative bg-gray-900 items-center justify-center overflow-hidden"
        >
            <img
                src="https://images.unsplash.com/photo-1550614000-4b95d466e06d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80"
                alt="Model Bersantai"
                class="absolute inset-0 w-full h-full object-cover opacity-70 mix-blend-overlay grayscale-[30%]"
            />
            <div class="relative z-10 px-12 text-center">
                <Link
                    href="/"
                    class="text-4xl font-bold tracking-tighter text-white mb-6 block"
                >
                    LUMIÈRE<span class="text-indigo-400">.</span>
                </Link>
                <p
                    class="text-lg text-gray-300 max-w-md mx-auto leading-relaxed"
                >
                    Jangan khawatir, ini bisa terjadi pada siapa saja. Masukkan
                    email Anda dan kami akan membantu Anda kembali ke akun Anda.
                </p>
            </div>
        </div>

        <!-- Bagian Kanan: Form -->
        <div
            class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12"
        >
            <div class="w-full max-w-md">
                <!-- Logo untuk Mobile -->
                <div class="lg:hidden text-center mb-10">
                    <Link
                        href="/"
                        class="text-3xl font-bold tracking-tighter text-gray-900"
                    >
                        LUMIÈRE<span class="text-indigo-600">.</span>
                    </Link>
                </div>

                <!-- Tombol Kembali -->
                <Link
                    :href="route('login')"
                    class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors mb-8 group"
                >
                    <svg
                        class="h-4 w-4 mr-2 transform group-hover:-translate-x-1 transition-transform"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                    Kembali ke halaman Masuk
                </Link>

                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                    Lupa Kata Sandi?
                </h2>

                <p class="text-gray-500 mb-8 leading-relaxed text-sm">
                    Tidak masalah. Cukup beri tahu kami alamat email Anda dan
                    kami akan mengirimkan tautan reset kata sandi melalui email
                    yang memungkinkan Anda memilih kata sandi yang baru.
                </p>

                <!-- Pesan Sukses (Status dari Laravel) -->
                <div
                    v-if="status"
                    class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-start"
                >
                    <svg
                        class="h-5 w-5 text-green-500 mt-0.5 mr-3 flex-shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <p class="font-medium text-sm text-green-800">
                        {{ status }}
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Input -->
                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Alamat Email Terdaftar</label
                        >
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                            >
                                <svg
                                    class="h-5 w-5 text-gray-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all outline-none"
                                placeholder="nama@email.com"
                            />
                        </div>
                        <div
                            v-if="form.errors.email"
                            class="mt-2 text-sm text-red-600 flex items-center"
                        >
                            <svg
                                class="h-4 w-4 mr-1.5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gray-900 hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed group"
                    >
                        <!-- Loading Spinner (muncul saat proses submit) -->
                        <svg
                            v-if="form.processing"
                            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>

                        <span v-if="!form.processing"
                            >Kirim Tautan Reset Sandi</span
                        >
                        <span v-else>Mengirim...</span>

                        <!-- Panah (muncul saat tidak loading) -->
                        <svg
                            v-if="!form.processing"
                            class="ml-2 -mr-1 w-4 h-4 transform group-hover:translate-x-1 transition-transform"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"
                            />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
