<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div>
        <!-- Newsletter Section -->
        <div class="bg-indigo-700">
            <div
                class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16 lg:flex lg:items-center lg:justify-between"
            >
                <div class="lg:w-0 lg:flex-1">
                    <h2
                        class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
                    >
                        Dapatkan info promo terbaru.
                    </h2>
                    <p class="mt-3 max-w-3xl text-lg leading-6 text-indigo-200">
                        Daftarkan email Anda untuk mendapatkan diskon khusus
                        member dan update koleksi terbaru.
                    </p>
                </div>
                <div class="mt-8 lg:mt-0 lg:ml-8 lg:flex-1">
                    <form class="sm:flex">
                        <label for="email-address" class="sr-only"
                            >Alamat Email</label
                        >
                        <input
                            id="email-address"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="w-full px-5 py-3 border border-transparent placeholder-gray-500 focus:ring-2 focus:ring-offset-2 focus:ring-offset-indigo-700 focus:ring-white focus:border-white sm:max-w-xs rounded-full"
                            placeholder="Masukkan email Anda"
                        />
                        <div
                            class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3 sm:flex-shrink-0"
                        >
                            <button
                                type="submit"
                                class="w-full flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-full text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-indigo-700 focus:ring-white transition-colors"
                            >
                                Langganan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Footer -->
        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-1">
                        <span
                            class="text-2xl font-bold tracking-tighter text-gray-900"
                            >LUMIÈRE<span class="text-indigo-600">.</span></span
                        >
                        <p class="mt-4 text-sm text-gray-500">
                            Toko pakaian modern yang menyediakan berbagai
                            pilihan gaya untuk keseharian Anda. Tampil percaya
                            diri setiap saat.
                        </p>
                    </div>
                    <div>
                        <h3
                            class="text-sm font-semibold text-gray-900 tracking-wider uppercase"
                        >
                            Toko
                        </h3>
                        <ul class="mt-4 space-y-4">
                            <li>
                                <Link
                                    href="/pria"
                                    class="text-sm text-gray-500 hover:text-indigo-600"
                                    >Pria</Link
                                >
                            </li>
                            <li>
                                <Link
                                    href="/wanita"
                                    class="text-sm text-gray-500 hover:text-indigo-600"
                                    >Wanita</Link
                                >
                            </li>
                            <li>
                                <Link
                                    href="/products"
                                    class="text-sm text-gray-500 hover:text-indigo-600"
                                    >Semua Produk</Link
                                >
                            </li>
                            <li>
                                <Link
                                    href="/koleksi-baru"
                                    class="text-sm text-gray-500 hover:text-indigo-600"
                                    >Koleksi Terbaru</Link
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3
                            class="text-sm font-semibold text-gray-900 tracking-wider uppercase"
                        >
                            Bantuan
                        </h3>
                        <ul class="mt-4 space-y-4">
                            <li>
                                <a
                                    href="#"
                                    class="text-sm text-gray-500 hover:text-indigo-600"
                                    >Cara Pembayaran</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-sm text-gray-500 hover:text-indigo-600"
                                    >Pengiriman</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-sm text-gray-500 hover:text-indigo-600"
                                    >Pengembalian</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-sm text-gray-500 hover:text-indigo-600"
                                    >FAQ</a
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3
                            class="text-sm font-semibold text-gray-900 tracking-wider uppercase"
                        >
                            Hubungi Kami
                        </h3>
                        <ul class="mt-4 space-y-4">
                            <li class="text-sm text-gray-500">
                                Email: halo@lumiere.com
                            </li>
                            <li class="text-sm text-gray-500">
                                WhatsApp: +62 812 3456 7890
                            </li>
                            <li class="flex space-x-4 mt-4">
                                <a
                                    href="#"
                                    class="text-gray-400 hover:text-indigo-600"
                                >
                                    <span class="sr-only">Instagram</span>
                                    <svg
                                        class="h-6 w-6"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div
                    class="mt-8 border-t border-gray-200 pt-8 text-center sm:flex sm:justify-between sm:text-left"
                >
                    <p class="text-sm text-gray-400">
                        &copy; 2026 Lumière. Hak Cipta Dilindungi.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
