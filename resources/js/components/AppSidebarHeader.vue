<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import UserInfo from './UserInfo.vue';
import { computed } from 'vue';
import NavUser from './NavUser.vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const currentOutlet = computed(() => page.props.auth.currentOutlet);
</script>

<template>
    <!-- Sesudah diubah (Floating Liquid Glass Navbar) -->
    <header class="w-full pb-0">
        <div
            class="flex h-14 w-full items-center justify-between rounded-2xl border border-white/40 bg-white/30 px-6 shadow-[0_8px_32px_0_rgba(31,38,135,0.15)] backdrop-blur-xl md:px-6"
        >
            <div class="flex w-full justify-between">
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
                    <span
                        v-if="currentOutlet"
                        class="rounded-lg bg-blue-500/20 px-2 py-1 text-sm font-semibold text-blue-500"
                        >{{ `Cabang ${currentOutlet}` }}</span
                    >
                    <span
                        v-else
                        class="rounded-lg bg-blue-500/20 px-2 py-1 text-sm font-semibold text-blue-500"
                        >{{ `Semua Cabang` }}</span
                    >
                </div>

                <div class="flex items-center gap-2">
                    <NavUser />
                </div>
            </div>
        </div>
    </header>
</template>
