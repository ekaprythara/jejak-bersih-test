<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <!-- Sesudah diubah (Floating Liquid Glass Navbar) -->
    <header class="w-full pb-0">
        <div
            class="flex h-14 w-full items-center justify-between rounded-2xl border border-white/40 bg-white/30 px-6 shadow-[0_8px_32px_0_rgba(31,38,135,0.15)] backdrop-blur-xl md:px-6"
        >
            <div class="flex items-center gap-3">
                <SidebarTrigger
                    class="-ml-1 rounded-xl border-none bg-transparent text-slate-800 hover:bg-white/40"
                />

                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs
                        :breadcrumbs="breadcrumbs"
                        class="font-semibold text-slate-800"
                    />
                </template>
                <template v-else>
                    <span class="text-base font-bold text-slate-800"
                        >Kasir / POS</span
                    >
                </template>
            </div>
        </div>
    </header>
</template>
