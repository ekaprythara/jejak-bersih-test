<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    FileChartColumn,
    LayoutGrid,
    ReceiptText,
    ShieldUser,
    ShoppingCart,
    Sparkles,
    Store,
    TrendingDown,
    TrendingUp,
    UsersRound,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import cashier from '@/routes/cashier';
import customers from '@/routes/customers';
import expenses from '@/routes/expenses';
import { index } from '@/routes/outlets';
import services from '@/routes/services';
import transactions from '@/routes/transactions';
import users from '@/routes/users';
import type { NavItem } from '@/types';

const page = usePage();
const isOwner = page.props.auth.isOwner;
const isAdmin = page.props.auth.isAdmin;

const mainNavItems = computed<NavItem[]>(() => {
    if (isOwner) {
        return [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: 'Admin',
                href: users.index(),
                icon: ShieldUser,
            },
            {
                title: 'Cabang',
                href: index(),
                icon: Store,
            },
            {
                title: 'Layanan',
                href: services.index(),
                icon: Sparkles,
            },
        ];
    } else if (isAdmin) {
        return [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: 'Kasir / POS',
                href: cashier.index(),
                icon: ShoppingCart,
            },
            {
                title: 'Transaksi',
                href: transactions.index(),
                icon: ReceiptText,
            },
            {
                title: 'Pelanggan',
                href: customers.index(),
                icon: UsersRound,
            },
            {
                title: 'Pengeluaran',
                href: expenses.index(),
                icon: TrendingDown,
            },
        ];
    } else {
        return [];
    }
});

const reportNavItems = computed<NavItem[]>(() => {
    if (isOwner) {
        return [
            {
                title: 'Transaksi',
                href: transactions.index(),
                icon: ReceiptText,
            },

            {
                title: 'Pemasukan',
                href: '#',
                icon: TrendingUp,
            },
            {
                title: 'Pengeluaran',
                href: expenses.index(),
                icon: TrendingDown,
            },
            {
                title: 'Laporan Keuangan',
                href: '#',
                icon: FileChartColumn,
            },
        ];
    } else {
        return [];
    }
});
</script>

<template>
    <Sidebar collapsible="icon" variant="floating">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" sidebar-group-label="Manajemen" />
            <NavMain
                v-if="isOwner"
                :items="reportNavItems"
                sidebar-group-label="Laporan"
            />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
