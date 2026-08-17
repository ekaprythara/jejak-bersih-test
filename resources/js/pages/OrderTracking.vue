<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const backgroundImg = '/images/tracking-background.png';

const invoiceQuery = ref('');

// Redirect ke /tracking/{invoice}
const handleSearch = () => {
    if (!invoiceQuery.value.trim()) {
        return;
    }

    router.get(`/tracking/${encodeURIComponent(invoiceQuery.value.trim())}`);
};
</script>

<template>
    <Head title="Tracking Order" />

    <div
        class="flex min-h-screen flex-col items-center bg-cover bg-center bg-no-repeat p-6 text-slate-900 lg:justify-center lg:p-8"
        :style="{ backgroundImage: `url(${backgroundImg})` }"
    >
        <div class="flex w-full items-center justify-center lg:grow">
            <main class="flex w-full flex-col gap-8 lg:max-w-4xl">
                <!-- Header Title (Liquid Glass - Tanpa Transisi) -->
                <div
                    class="relative overflow-hidden rounded-3xl border border-white/40 bg-white/20 p-8 shadow-[0_8px_32px_0_rgba(31,38,135,0.25)] backdrop-blur-xl sm:p-12"
                >
                    <!-- Refleksi Kilau Cairan (Aksen Kaca) -->
                    <div
                        class="pointer-events-none absolute -top-12 -left-12 h-40 w-40 rounded-full bg-white/30 blur-2xl"
                    ></div>

                    <div class="relative z-10 flex flex-col gap-3 text-center">
                        <h2
                            class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Progress Tracking
                        </h2>
                        <p
                            class="text-sm leading-relaxed font-medium text-slate-700 sm:text-base"
                        >
                            Fitur progress tracking ini memudahkan Anda memantau
                            setiap tahapan pencucian sepatu secara real-time,
                            mulai dari penerimaan hingga siap diambil, agar
                            pengerjaan selalu tepat waktu.
                        </p>
                    </div>
                </div>

                <!-- Input Cek Resi / Invoice (Liquid Glass - Tanpa Transisi) -->
                <div
                    class="relative overflow-hidden rounded-3xl border border-white/40 bg-white/20 p-6 shadow-[0_8px_32px_0_rgba(31,38,135,0.25)] backdrop-blur-xl"
                >
                    <form
                        @submit.prevent="handleSearch"
                        class="relative z-10 flex flex-col gap-3 sm:flex-row"
                    >
                        <Input
                            v-model="invoiceQuery"
                            type="text"
                            placeholder="Ketik No. Invoice di sini (contoh: INV-20260804-1A73E)..."
                            class="flex-1 rounded-xl border-white/40 bg-white/40 placeholder:text-slate-500 focus-visible:ring-slate-400"
                        />
                        <Button
                            type="submit"
                            class="flex items-center justify-center gap-2 rounded-xl shadow-md"
                        >
                            <Search class="h-4 w-4" />
                            <span>Cek Status Pengerjaan</span>
                        </Button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</template>
