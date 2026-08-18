<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import FlashMessages from '@/Components/FlashMessages.vue';

const user = computed(() => usePage().props.auth.user);

const routeTitles = {
    'admin.dashboard': 'Dashboard',
    'pos.index': 'POS Kasir',
    'kitchen.index': 'Panel Dapur',
    'admin.users.index': 'Manajemen User',
    'admin.users.create': 'Tambah User',
    'admin.users.edit': 'Edit User',
    'admin.menu-categories.index': 'Kategori Menu',
    'admin.menu-categories.create': 'Tambah Kategori',
    'admin.menu-categories.edit': 'Edit Kategori',
    'admin.menu-items.index': 'Menu Items',
    'admin.menu-items.create': 'Tambah Menu',
    'admin.menu-items.edit': 'Edit Menu',
    'admin.tables.index': 'Meja',
    'admin.tables.create': 'Tambah Meja',
    'admin.tables.edit': 'Edit Meja',
    'admin.ingredients.index': 'Bahan Baku',
    'admin.ingredients.create': 'Tambah Bahan',
    'admin.ingredients.edit': 'Edit Bahan',
    'admin.recipes.index': 'Resep Menu',
    'admin.stock-logs.index': 'Mutasi Stok',
    'admin.stock-logs.create': 'Mutasi Stok Baru',
    'admin.stock-logs.create-production': 'Produksi Menu',
    'admin.stock-opnames.index': 'Stok Opname',
    'admin.stock-opnames.create': 'Buat Stok Opname',
    'admin.stock-opnames.show': 'Detail Stok Opname',
    'reports.sales': 'Laporan Penjualan',
    'reports.sales.show': 'Detail Transaksi',
    'reports.popular-menu': 'Menu Terlaris',
    'reports.tables': 'Laporan Meja',
    'reports.cash-flow': 'Arus Kas',
    'reports.cash-flow.create': 'Entri Arus Kas',
    'reports.cash-flow.edit': 'Edit Entri Arus Kas',
    'reports.cash-flow.posting': 'Posting Transaksi',
};

const title = computed(() => routeTitles[route().current()] ?? 'RestoApp');

const navGroups = computed(() => {
    const u = user.value;
    const group = (label, items) => ({ label, items });

    const main = [];
    const management = [];
    const stock = [];
    const reports = [];
    const dapur = [];

    if (u?.is_admin) {
        main.push({ name: 'Dashboard', href: route('admin.dashboard'), icon: 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10', activeRoutes: ['admin.dashboard'] });
        management.push(
            { name: 'Users', href: route('admin.users.index'), icon: 'M16 11a4 4 0 10-8 0m8 0a4 4 0 01-4 4m4-4a4 4 0 11-8 0m8 0V7a4 4 0 00-8 0v4m6 4H6a2 2 0 00-2 2v2m14-8v10', activeRoutes: ['admin.users'] },
            { name: 'Kategori Menu', href: route('admin.menu-categories.index'), icon: 'M4 6h16M4 12h16M4 18h16M8 6v12M16 6v12', activeRoutes: ['admin.menu-categories'] },
            { name: 'Menu Items', href: route('admin.menu-items.index'), icon: 'M3 7l9-4 9 4-9 4-9-4zM3 7v6l9 4 9-4V7M3 13v6l9 4 9-4v-6', activeRoutes: ['admin.menu-items'] },
            { name: 'Meja', href: route('admin.tables.index'), icon: 'M4 6h16M4 12h16M4 18h16M8 6v12M16 6v12', activeRoutes: ['admin.tables'] },
        );
        stock.push(
            { name: 'Bahan Baku', href: route('admin.ingredients.index'), icon: 'M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16zM3.3 7.7L12 12.7l8.7-5M12 22V12', activeRoutes: ['admin.ingredients'] },
            { name: 'Resep', href: route('admin.recipes.index'), icon: 'M9.5 3h5M10.5 3v4.2L5 13.5V15a3 3 0 003 3h8a3 3 0 003-3v-1.5l-5.5-6.3V3m-3 16v3m6-3v3', activeRoutes: ['admin.recipes'] },
            { name: 'Mutasi Stok', href: route('admin.stock-logs.index'), icon: 'M4 4v6h6M20 20v-6h-6M4 10a8 8 0 0114-5.5M20 10a8 8 0 01-14 5.5', activeRoutes: ['admin.stock-logs'] },
            { name: 'Stok Opname', href: route('admin.stock-opnames.index'), icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', activeRoutes: ['admin.stock-opnames'] },
        );
        reports.push(
            { name: 'Penjualan', href: route('reports.sales'), icon: 'M3 3v18h18M18 17V9m-5 8V5m-5 12v-6', activeRoutes: ['reports.sales'] },
            { name: 'Menu Terlaris', href: route('reports.popular-menu'), icon: 'M13 2L3 14h7l-1 8 10-12h-7l1-8z', activeRoutes: ['reports.popular-menu'] },
            { name: 'Meja', href: route('reports.tables'), icon: 'M4 6h16M4 12h16M4 18h16M10 6v12m4-12v12', activeRoutes: ['reports.tables'] },
            { name: 'Arus Kas', href: route('reports.cash-flow'), icon: 'M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6', activeRoutes: ['reports.cash-flow'] },
        );
    }

    if (u?.is_kasir || u?.is_admin) {
        main.push({ name: 'POS Kasir', href: route('pos.index'), icon: 'M3 3h2l.4 2M7 13h10l4-8H6.5M7 13L5.6 5M7 13l-2 6H4m13-2l1.5-8M9 19h10', activeRoutes: ['pos.index'] });
        if (!u?.is_admin) {
            reports.push(
                { name: 'Penjualan', href: route('reports.sales'), icon: 'M3 3v18h18M18 17V9m-5 8V5m-5 12v-6', activeRoutes: ['reports.sales'] },
                { name: 'Menu Terlaris', href: route('reports.popular-menu'), icon: 'M13 2L3 14h7l-1 8 10-12h-7l1-8z', activeRoutes: ['reports.popular-menu'] },
                { name: 'Meja', href: route('reports.tables'), icon: 'M4 6h16M4 12h16M4 18h16M10 6v12m4-12v12', activeRoutes: ['reports.tables'] },
                { name: 'Arus Kas', href: route('reports.cash-flow'), icon: 'M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6', activeRoutes: ['reports.cash-flow'] },
            );
        }
    }

    if (u?.is_dapur || u?.is_admin) {
        main.push({ name: 'Panel Dapur', href: route('kitchen.index'), icon: 'M17.5 12a5.5 5.5 0 11-11 0c0-2.8 2-5.5 5.5-7 3.5 1.5 5.5 4.2 5.5 7zM12 20v-6', activeRoutes: ['kitchen.index'] });
    }

    const groups = [group('Utama', main)];
    if (management.length) groups.push(group('Manajemen', management));
    if (stock.length) groups.push(group('Stok', stock));
    if (reports.length) groups.push(group('Laporan', reports));

    return groups;
});

const isActive = (item) => item.activeRoutes.some((r) => route().current(r));

const userMenuOpen = ref(false);
const sidebarOpen = ref(false);

const logout = () => {
    axios.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-slate-100 lg:flex">
        <FlashMessages />

        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" />
        </Transition>

        <!-- Sidebar -->
        <Transition
            enter-active-class="transition-transform duration-200"
            enter-from-class="-translate-x-full"
            leave-active-class="transition-transform duration-200"
            leave-to-class="-translate-x-full"
        >
            <aside
                v-if="sidebarOpen || true"
                class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full transform bg-slate-900 transition-transform duration-200 lg:static lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : ''"
            >
                <div class="flex h-full flex-col">
                    <div class="flex h-16 items-center gap-3 border-b border-slate-800 px-6">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-lg shadow-lg shadow-indigo-900/40">
                            🍽️
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white">{{ $page.props.appName ?? 'RestoApp' }}</p>
                            <p class="text-[11px] text-slate-400">Point of Sale</p>
                        </div>
                    </div>

                    <nav class="flex-1 space-y-6 overflow-y-auto px-4 py-6">
                        <div v-for="group in navGroups" :key="group.label">
                            <p class="mb-2 px-3 text-[10px] font-bold tracking-[0.12em] text-slate-500 uppercase">{{ group.label }}</p>
                            <div class="space-y-1">
                                <Link
                                    v-for="item in group.items"
                                    :key="item.href"
                                    :href="item.href"
                                    class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all"
                                    :class="isActive(item) ? 'bg-gradient-to-r from-indigo-600 to-indigo-500 text-white shadow-lg shadow-indigo-900/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                                >
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                                    </svg>
                                    <span>{{ item.name }}</span>
                                </Link>
                            </div>
                        </div>
                    </nav>

                    <div class="border-t border-slate-800 p-4">
                        <p class="px-3 text-xs text-slate-500">RestoApp v1.0</p>
                    </div>
                </div>
            </aside>
        </Transition>

        <!-- Main -->
        <div class="flex min-h-screen min-w-0 flex-1 flex-col">
            <!-- Topbar -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 px-4 backdrop-blur-md sm:px-6">
                <div class="flex items-center gap-3">
                    <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = !sidebarOpen">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold text-slate-800">{{ title }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative">
                        <button
                            class="flex items-center gap-3 rounded-xl p-1.5 pr-3 transition hover:bg-slate-100"
                            @click="userMenuOpen = !userMenuOpen"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 text-xs font-bold text-white">
                                {{ user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                            </div>
                            <div class="hidden text-left sm:block">
                                <p class="text-sm font-semibold text-slate-800">{{ user?.name }}</p>
                                <p class="text-[11px] text-slate-500 capitalize">{{ user?.role_label }}</p>
                            </div>
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </button>

                        <Transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="opacity-0 scale-95"
                            leave-active-class="transition ease-in duration-75"
                            leave-to-class="opacity-0 scale-95"
                        >
                            <div v-if="userMenuOpen" class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-200" @click.self>
                                <div class="border-b border-slate-100 px-4 py-3">
                                    <p class="text-sm font-semibold text-slate-800">{{ user?.name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ user?.email }}</p>
                                </div>
                                <button class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50" @click="logout">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    Logout
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 p-4 sm:p-6">
                <slot />
            </main>

            <footer class="border-t border-slate-200 px-6 py-4 text-center text-xs text-slate-400">
                &copy; {{ new Date().getFullYear() }} {{ $page.props.appName ?? 'RestoApp' }} — All rights reserved.
            </footer>
        </div>
    </div>
</template>