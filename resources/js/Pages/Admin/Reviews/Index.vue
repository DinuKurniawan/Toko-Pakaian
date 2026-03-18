<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminSidebar from "@/Components/AdminSidebar.vue";

const props = defineProps({ reviews: Object, filters: Object });
const isSidebarOpen = ref(false);
const copiedReviewId = ref(null);

function approve(review) {
    router.patch(route("admin.reviews.approve", review.id), {}, { preserveScroll: true });
}

function reject(review) {
    if (confirm("Tolak dan hapus ulasan ini?")) {
        router.delete(route("admin.reviews.reject", review.id), { preserveScroll: true });
    }
}

function filterByStatus(status) {
    router.get(route("admin.reviews.index"), { status }, { preserveScroll: true });
}

function getImageUrl(image) {
    if (!image) return "https://placehold.co/80x80?text=No+Image";
    if (image.startsWith("http")) return image;
    return `/storage/${image}`;
}

function merchantReplySuggestions(review) {
    const customerName = review.user?.name || "Kak";
    const productName = review.product?.name || "produk ini";

    if (review.rating >= 4) {
        return [
            `Terima kasih banyak, ${customerName}! Senang sekali mendengar ${productName} cocok untuk Anda. Kami tunggu order berikutnya di LUMIÈRE.`,
            `Terima kasih atas ulasan positifnya, ${customerName}. Feedback Anda membantu calon pembeli lain memilih ${productName} dengan lebih percaya diri.`,
        ];
    }

    if (review.rating === 3) {
        return [
            `Terima kasih sudah berbagi pengalaman, ${customerName}. Masukan Anda untuk ${productName} akan kami gunakan untuk meningkatkan kualitas layanan kami.`,
            `Terima kasih atas review-nya, ${customerName}. Jika ada detail tambahan tentang pengalaman Anda memakai ${productName}, tim kami akan sangat terbantu.`,
        ];
    }

    return [
        `Terima kasih sudah memberi tahu kami, ${customerName}. Mohon maaf pengalaman Anda dengan ${productName} belum sesuai harapan. Tim kami akan evaluasi ini lebih lanjut.`,
        `Mohon maaf atas pengalaman yang kurang nyaman, ${customerName}. Terima kasih atas masukannya untuk ${productName}; feedback ini penting agar kami bisa berbenah.`,
    ];
}

async function copyReplySuggestion(reviewId, suggestion) {
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(suggestion);
    } else {
        window.prompt("Salin balasan merchant berikut:", suggestion);
    }

    copiedReviewId.value = reviewId;

    window.setTimeout(() => {
        if (copiedReviewId.value === reviewId) {
            copiedReviewId.value = null;
        }
    }, 1800);
}
</script>

<template>
    <Head title="Moderasi Ulasan | LUMIÈRE" />
    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900">
        <AdminSidebar :is-sidebar-open="isSidebarOpen" active-page="reviews" @close-sidebar="isSidebarOpen = false" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-900">Moderasi Ulasan</h2>
                </div>
                <Link href="/" class="text-sm font-medium text-gray-500 hover:text-indigo-600">Kembali ke Toko &rarr;</Link>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium">{{ $page.props.flash.success }}</div>

                <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h1 class="text-3xl font-bold text-gray-900">Ulasan Produk</h1>
                    <div class="flex gap-2">
                        <button @click="filterByStatus('')" :class="!filters.status ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-300'" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors">Semua</button>
                        <button @click="filterByStatus('pending')" :class="filters.status === 'pending' ? 'bg-amber-500 text-white' : 'bg-white text-gray-700 border border-gray-300'" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors">Menunggu</button>
                        <button @click="filterByStatus('approved')" :class="filters.status === 'approved' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-300'" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors">Disetujui</button>
                    </div>
                </div>

                <div class="space-y-4">
                    <div v-for="review in reviews.data" :key="review.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-start gap-4">
                            <img :src="getImageUrl(review.product?.image)" :alt="review.product?.name" class="w-16 h-20 rounded-lg object-cover flex-shrink-0 bg-gray-100" />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-semibold text-gray-900 text-sm">{{ review.product?.name }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">oleh {{ review.user?.name }}</p>
                                    </div>
                                    <span :class="review.is_approved ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'" class="px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap">
                                        {{ review.is_approved ? 'Disetujui' : 'Menunggu' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 mt-2">
                                    <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <span class="text-sm text-gray-500 ml-1">{{ review.rating }}/5</span>
                                </div>
                                <p v-if="review.comment" class="mt-2 text-sm text-gray-700">{{ review.comment }}</p>
                                <p v-else class="mt-2 text-sm text-gray-400 italic">Tidak ada komentar</p>
                                <div class="flex items-center gap-3 mt-3">
                                    <button v-if="!review.is_approved" @click="approve(review)" class="px-4 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-lg hover:bg-green-700 transition-colors">✓ Setujui</button>
                                    <button @click="reject(review)" class="px-4 py-1.5 bg-red-50 text-red-600 text-xs font-semibold rounded-lg hover:bg-red-100 transition-colors">✕ Tolak & Hapus</button>
                                </div>

                                <div class="mt-4 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                    <div class="flex items-center justify-between gap-3 mb-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">
                                                Quick Reply Assistant
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Salin template balasan merchant yang sesuai dengan rating review.
                                            </p>
                                        </div>
                                        <span
                                            v-if="copiedReviewId === review.id"
                                            class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700"
                                        >
                                            Balasan disalin
                                        </span>
                                    </div>

                                    <div class="space-y-2">
                                        <button
                                            v-for="suggestion in merchantReplySuggestions(review)"
                                            :key="suggestion"
                                            type="button"
                                            @click="copyReplySuggestion(review.id, suggestion)"
                                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-left text-sm text-gray-700 transition-colors hover:border-indigo-200 hover:bg-indigo-50"
                                        >
                                            {{ suggestion }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="reviews.data.length === 0" class="text-center py-16 text-gray-500">
                        Tidak ada ulasan untuk dimoderasi.
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
