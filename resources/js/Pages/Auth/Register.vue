<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Daftar | LUMIÈRE" />

    <div
        class="min-h-screen flex flex-row-reverse bg-white font-sans text-gray-900"
    >
        <!-- Bagian Kanan: Gambar (Hidden di mobile) -->
        <div
            class="hidden lg:flex lg:w-1/2 relative bg-gray-900 items-center justify-center overflow-hidden"
        >
            <img
                src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80"
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
                    Bergabunglah dengan kami dan temukan gaya terbaik yang
                    mendefinisikan kepribadian Anda.
                </p>
            </div>
        </div>

        <!-- Bagian Kiri: Form -->
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
                    Buat Akun Baru
                </h2>
                <p class="text-gray-500 mb-8">
                    Lengkapi data di bawah ini untuk menjadi member Lumière.
                </p>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Nama Lengkap -->
                    <div>
                        <label
                            for="name"
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Nama Lengkap</label
                        >
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all outline-none"
                            placeholder="John Doe"
                        />
                        <div
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.name }}
                        </div>
                    </div>

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
                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Kata Sandi</label
                        >
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all outline-none"
                            placeholder="Minimal 8 karakter"
                        />
                        <div
                            v-if="form.errors.password"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label
                            for="password_confirmation"
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Konfirmasi Kata Sandi</label
                        >
                        <input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all outline-none"
                            placeholder="Ulangi kata sandi"
                        />
                        <div
                            v-if="form.errors.password_confirmation"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gray-900 hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 transition-colors disabled:opacity-50 mt-2"
                    >
                        Daftar Akun
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-gray-600">
                    Sudah punya akun?
                    <Link
                        :href="route('login')"
                        class="font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        Masuk di sini
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
