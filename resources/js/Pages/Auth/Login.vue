<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Masuk | LUMIÈRE" />

    <div class="min-h-screen flex bg-white font-sans text-gray-900">
        <!-- Bagian Kiri: Gambar (Hidden di mobile, muncul di desktop) -->
        <div
            class="hidden lg:flex lg:w-1/2 relative bg-gray-900 items-center justify-center overflow-hidden"
        >
            <img
                src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80"
                alt="Fashion Model"
                class="absolute inset-0 w-full h-full object-cover opacity-80 mix-blend-overlay"
            />
            <div class="relative z-10 px-12 text-center">
                <Link
                    href="/"
                    class="text-4xl font-bold tracking-tighter text-white mb-6 block"
                >
                    LUMIÈRE<span class="text-indigo-400">.</span>
                </Link>
                <p class="text-lg text-gray-300 max-w-md mx-auto">
                    Masuk ke akun Anda untuk pengalaman berbelanja yang lebih
                    personal, melacak pesanan, dan mendapatkan penawaran
                    eksklusif.
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

                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                    Selamat Datang Kembali
                </h2>
                <p class="text-gray-500 mb-8">
                    Silakan masukkan detail akun Anda untuk masuk.
                </p>

                <div
                    v-if="status"
                    class="mb-4 font-medium text-sm text-green-600"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Alamat Email</label
                        >
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all outline-none"
                            placeholder="nama@email.com"
                        />
                        <div
                            v-if="form.errors.email"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label
                                for="password"
                                class="block text-sm font-medium text-gray-700"
                                >Kata Sandi</label
                            >
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                            >
                                Lupa sandi?
                            </Link>
                        </div>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all outline-none"
                            placeholder="••••••••"
                        />
                        <div
                            v-if="form.errors.password"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            type="checkbox"
                            v-model="form.remember"
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-600"
                        />
                        <label
                            for="remember"
                            class="ml-2 block text-sm text-gray-700"
                            >Ingat saya</label
                        >
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gray-900 hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 transition-colors disabled:opacity-50"
                    >
                        Masuk
                    </button>
                </form>

                <!-- Divider -->
                <div class="mt-8 relative">
                    <div
                        class="absolute inset-0 flex items-center"
                        aria-hidden="true"
                    >
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500"
                            >Atau masuk dengan</span
                        >
                    </div>
                </div>

                <!-- Social Login (Mockup) -->
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <button
                        class="w-full flex items-center justify-center px-4 py-2.5 border border-gray-200 rounded-xl shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        <img
                            class="h-5 w-5 mr-2"
                            src="https://www.svgrepo.com/show/475656/google-color.svg"
                            alt="Google"
                        />
                        Google
                    </button>
                    <button
                        class="w-full flex items-center justify-center px-4 py-2.5 border border-gray-200 rounded-xl shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        <svg
                            class="h-5 w-5 mr-2 text-gray-900"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.43.916 3.96.948 1.637.026 2.62-1.454 3.63-2.923 1.156-1.673 1.634-3.298 1.652-3.385-.035-.015-3.17-1.22-3.196-4.82-.026-3.003 2.451-4.437 2.56-4.502-1.41-2.064-3.597-2.343-4.38-2.389-2.041-.122-4.045 1.288-4.522 1.288zm-1.542-4.145c.804-.98 1.345-2.344 1.198-3.706-1.162.047-2.607.773-3.435 1.74-.663.766-1.288 2.148-1.115 3.486 1.296.102 2.548-.54 3.352-1.52z"
                            />
                        </svg>
                        Apple
                    </button>
                </div>

                <p class="mt-8 text-center text-sm text-gray-600">
                    Belum punya akun?
                    <Link
                        :href="route('register')"
                        class="font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        Daftar sekarang
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
