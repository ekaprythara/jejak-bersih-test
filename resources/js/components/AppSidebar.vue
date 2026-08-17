<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    ReceiptText,
    ShieldUser,
    ShoppingCart,
    Sparkles,
    Store,
    TrendingDown,
    UsersRound,
} from '@lucide/vue';
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
import { computed } from 'vue';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => {
    const isOwner = page.props.auth.isOwner;
    const isAdmin = page.props.auth.isAdmin;

    return [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        // Hanya tambahkan ke array jika `can.viewCashierPage` bernilai TRUE
        ...(isAdmin
            ? [
                  {
                      title: 'Kasir / POS',
                      href: cashier.index(),
                      icon: ShoppingCart,
                  },
              ]
            : []),
        {
            title: 'Transaksi',
            href: transactions.index(),
            icon: ReceiptText,
        },
        ...(isOwner
            ? [
                  {
                      title: 'Admin',
                      href: users.index(),
                      icon: ShieldUser,
                  },
              ]
            : []),
        ...(isOwner
            ? [
                  {
                      title: 'Cabang',
                      href: index(),
                      icon: Store,
                  },
              ]
            : []),

        {
            title: 'Pelanggan',
            href: customers.index(),
            icon: UsersRound,
        },
        ...(isOwner
            ? [
                  {
                      title: 'Layanan',
                      href: services.index(),
                      icon: Sparkles,
                  },
              ]
            : []),
        {
            title: 'Pengeluaran',
            href: expenses.index(),
            icon: TrendingDown,
        },
    ];
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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
