<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Store, User, Check, Footprints, Search, ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { StatusType, TransactionType } from '@/types/data-types';

const props = defineProps<{
    transaction: TransactionType;
    transactionStatus: StatusType[];
    shoeStatus: StatusType[];
}>();

const backgroundImg = '/images/tracking-background.png';

// Timeline Transaksi Utama
const transactionCurrentStep = computed(() => {
    return props.transaction?.status?.step ?? 1;
});

// Helper format tanggal Indonesia
const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head :title="`Tracking - ${transaction?.invoice_number}`" />

    <div
        class="flex min-h-screen flex-col items-center bg-cover bg-center bg-no-repeat p-6 text-slate-900 lg:justify-center lg:p-8"
        :style="{ backgroundImage: `url(${backgroundImg})` }"
    >
        <div class="flex w-full items-center justify-center lg:grow">
            <main class="flex w-full flex-col gap-6 lg:max-w-4xl">
                <!-- Card Info Transaksi Header (Liquid Glass) -->
                <div
                    class="relative overflow-hidden rounded-3xl border border-white/40 bg-white/20 p-5 shadow-[0_8px_32px_0_rgba(31,38,135,0.25)] backdrop-blur-xl"
                >
                    <div class="flex flex-row items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-xl font-extrabold text-slate-900">
                                {{ transaction?.invoice_number }}
                            </span>
                            <span class="text-xs font-medium text-slate-700">
                                Ditransaksikan pada
                                {{ formatDate(transaction?.created_at) }}
                            </span>
                        </div>
                        <Button class="rounded-xl shadow-md"
                            >Unduh Nota Transaksi</Button
                        >
                    </div>
                </div>

                <!-- Detail Pelanggan & Outlet (Liquid Glass) -->
                <div class="grid grid-cols-2 gap-3">
                    <div
                        class="relative overflow-hidden rounded-2xl border border-white/40 bg-white/20 p-4 shadow-[0_8px_32px_0_rgba(31,38,135,0.25)] backdrop-blur-xl"
                    >
                        <div
                            class="mb-2 flex items-center gap-1.5 text-xs font-bold text-slate-700"
                        >
                            <User class="h-4 w-4 text-slate-900" /> Pelanggan
                        </div>
                        <p class="font-bold text-slate-900">
                            {{ transaction?.customer?.name ?? '-' }}
                        </p>
                        <p class="text-xs font-medium text-slate-700">
                            {{ transaction?.customer?.phone_number ?? '-' }}
                        </p>
                    </div>

                    <div
                        class="relative overflow-hidden rounded-2xl border border-white/40 bg-white/20 p-4 shadow-[0_8px_32px_0_rgba(31,38,135,0.25)] backdrop-blur-xl"
                    >
                        <div
                            class="mb-2 flex items-center gap-1.5 text-xs font-bold text-slate-700"
                        >
                            <Store class="h-4 w-4 text-slate-900" /> Outlet
                        </div>
                        <p class="font-bold text-slate-900">
                            {{ transaction?.outlet?.name ?? '-' }}
                        </p>
                        <p class="truncate text-xs font-medium text-slate-700">
                            {{ transaction?.outlet?.phone_number ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- 1. TIMELINE PROGRESS UTAMA TRANSAKSI (Liquid Glass) -->
                <div
                    class="relative overflow-hidden rounded-3xl border border-white/40 bg-white/20 p-6 shadow-[0_8px_32px_0_rgba(31,38,135,0.25)] backdrop-blur-xl"
                >
                    <h3
                        class="mb-4 text-xs font-extrabold tracking-wider text-slate-800 uppercase"
                    >
                        Status Utama Transaksi
                    </h3>
                    <div class="flex w-full items-start justify-between">
                        <template
                            v-for="(status, index) in transactionStatus"
                            :key="status.id"
                        >
                            <div
                                class="z-10 flex min-w-16 flex-col items-center gap-2"
                            >
                                <div
                                    class="relative flex items-center justify-center"
                                >
                                    <!-- Animasi Ping (Indikator Aktif) -->
                                    <div
                                        v-if="
                                            status.step ===
                                            transactionCurrentStep
                                        "
                                        class="absolute inline-flex h-11 w-11 animate-ping rounded-full bg-blue-400/50"
                                    />

                                    <div
                                        class="relative flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold"
                                        :class="[
                                            status.step ===
                                            transactionCurrentStep
                                                ? 'bg-blue-600 text-white shadow-lg ring-4 shadow-blue-500/30 ring-white/60'
                                                : status.step <
                                                    transactionCurrentStep
                                                  ? 'bg-emerald-500 text-white'
                                                  : 'border border-white/50 bg-white/40 text-slate-500',
                                        ]"
                                    >
                                        <Check
                                            v-if="
                                                status.step <
                                                transactionCurrentStep
                                            "
                                            class="h-4 w-4 stroke-[2.5]"
                                        />
                                        <span v-else>{{ status.step }}</span>
                                    </div>
                                </div>

                                <!-- Animasi Pulse pada Teks Status Aktif -->
                                <span
                                    class="max-w-24 text-center text-[11px] leading-tight"
                                    :class="[
                                        status.step === transactionCurrentStep
                                            ? 'animate-pulse font-bold text-blue-700'
                                            : status.step <
                                                transactionCurrentStep
                                              ? 'font-semibold text-emerald-700'
                                              : 'text-slate-600',
                                    ]"
                                >
                                    {{ status.name }}
                                </span>
                            </div>

                            <div
                                v-if="index < transactionStatus.length - 1"
                                class="mt-4 h-0.5 min-w-5 flex-1"
                                :class="[
                                    status.step < transactionCurrentStep
                                        ? 'bg-emerald-500'
                                        : 'bg-white/40',
                                ]"
                            />
                        </template>
                    </div>
                </div>

                <!-- 2. DAFTAR SEMUA SEPATU DAN TIMELINE PROGRESS MASING-MASING -->
                <div
                    v-if="
                        transaction?.transaction_shoes &&
                        transaction.transaction_shoes.length > 0
                    "
                    class="flex flex-col gap-6"
                >
                    <div
                        class="flex items-center gap-2 text-lg font-extrabold text-slate-900"
                    >
                        <Footprints class="h-5 w-5 text-blue-600" />
                        <span
                            >Daftar Sepatu & Progress Pengerjaan ({{
                                transaction.transaction_shoes.length
                            }})</span
                        >
                    </div>

                    <div
                        v-for="(
                            shoe, shoeIndex
                        ) in transaction.transaction_shoes"
                        :key="shoe.id"
                        class="relative flex flex-col gap-4 overflow-hidden rounded-3xl border border-white/40 bg-white/20 p-5 shadow-[0_8px_32px_0_rgba(31,38,135,0.25)] backdrop-blur-xl"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-2 border-b border-white/30 pb-3"
                        >
                            <div>
                                <h4 class="text-base font-bold text-slate-900">
                                    {{ shoeIndex + 1 }}. {{ shoe.shoe_brand }}
                                </h4>
                                <p class="text-xs font-medium text-slate-700">
                                    Warna:
                                    <span class="font-bold text-slate-900">{{
                                        shoe.shoe_color
                                    }}</span>
                                    | Ukuran:
                                    <span class="font-bold text-slate-900">{{
                                        shoe.shoe_size
                                    }}</span>
                                </p>
                            </div>

                            <span
                                class="rounded-full border border-blue-200 bg-blue-500/20 px-3 py-1 text-xs font-bold text-blue-800 backdrop-blur-md"
                            >
                                Status:
                                {{ shoe.status?.name ?? 'Antrian Cuci' }}
                            </span>
                        </div>

                        <!-- Timeline Progress per Sepatu (Liquid Glass Inner) -->
                        <div
                            v-if="shoeStatus && shoeStatus.length > 0"
                            class="rounded-2xl border border-white/30 bg-white/20 p-5 backdrop-blur-md"
                        >
                            <div
                                class="flex w-full items-start justify-between"
                            >
                                <template
                                    v-for="(status, index) in shoeStatus"
                                    :key="status.id"
                                >
                                    <div
                                        class="z-10 flex min-w-16 flex-col items-center gap-2"
                                    >
                                        <div
                                            class="relative flex items-center justify-center"
                                        >
                                            <!-- Animasi Ping per Sepatu -->
                                            <div
                                                v-if="
                                                    status.step ===
                                                    (shoe.status?.step ?? 1)
                                                "
                                                class="absolute inline-flex h-11 w-11 animate-ping rounded-full bg-blue-400/50"
                                            />

                                            <div
                                                class="relative flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold"
                                                :class="[
                                                    status.step ===
                                                    (shoe.status?.step ?? 1)
                                                        ? 'bg-blue-600 text-white shadow-lg ring-4 shadow-blue-500/30 ring-white/60'
                                                        : status.step <
                                                            (shoe.status
                                                                ?.step ?? 1)
                                                          ? 'bg-emerald-500 text-white'
                                                          : 'border border-white/50 bg-white/40 text-slate-500',
                                                ]"
                                            >
                                                <Check
                                                    v-if="
                                                        status.step <
                                                        (shoe.status?.step ?? 1)
                                                    "
                                                    class="h-4 w-4 stroke-[2.5]"
                                                />
                                                <span v-else>{{
                                                    status.step
                                                }}</span>
                                            </div>
                                        </div>

                                        <!-- Animasi Pulse Teks per Sepatu -->
                                        <span
                                            class="max-w-24 text-center text-[11px] leading-tight"
                                            :class="[
                                                status.step ===
                                                (shoe.status?.step ?? 1)
                                                    ? 'animate-pulse font-bold text-blue-700'
                                                    : status.step <
                                                        (shoe.status?.step ?? 1)
                                                      ? 'font-semibold text-emerald-700'
                                                      : 'text-slate-600',
                                            ]"
                                        >
                                            {{ status.name }}
                                        </span>
                                    </div>

                                    <div
                                        v-if="index < shoeStatus.length - 1"
                                        class="mt-4 h-0.5 min-w-5 flex-1"
                                        :class="[
                                            status.step <
                                            (shoe.status?.step ?? 1)
                                                ? 'bg-emerald-500'
                                                : 'bg-white/40',
                                        ]"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
