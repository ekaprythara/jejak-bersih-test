<script setup lang="ts" generic="TData">
import {
    ChevronDown,
    ChevronsUpDown,
    ChevronUp,
    Search,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from '@lucide/vue';
import type { ColumnDef, SortingState } from '@tanstack/vue-table';
import {
    useVueTable,
    getCoreRowModel,
    getSortedRowModel,
    getPaginationRowModel,
    getFilteredRowModel,
    FlexRender,
} from '@tanstack/vue-table';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
    columns: ColumnDef<TData, any>[];
    data: TData[];
    searchable?: boolean;
    searchPlaceholder?: string;
}>();

const sorting = ref<SortingState>([]);
const globalFilter = ref('');

const table = useVueTable({
    get data() {
        return props.data;
    },
    columns: props.columns,
    initialState: {
        pagination: {
            pageSize: 10,
        },
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    state: {
        get sorting() {
            return sorting.value;
        },
        get globalFilter() {
            return globalFilter.value;
        },
    },
    onSortingChange: (updater) => {
        sorting.value =
            typeof updater === 'function' ? updater(sorting.value) : updater;
    },
    onGlobalFilterChange: (updater) => {
        globalFilter.value =
            typeof updater === 'function'
                ? updater(globalFilter.value)
                : updater;
    },
});
</script>

<template>
    <div class="space-y-4">
        <!-- Top Bar: Length Menu & Search Bar -->
        <div
            class="flex flex-col items-center justify-between gap-4 sm:flex-row"
        >
            <!-- Pilihan Jumlah Baris per Halaman (Reka UI Select) -->
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <span>Tampilkan</span>
                <Select
                    :model-value="String(table.getState().pagination.pageSize)"
                    @update:model-value="
                        (val) => table.setPageSize(Number(val))
                    "
                >
                    <SelectTrigger class="h-9 w-18.75">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem value="10">10</SelectItem>
                            <SelectItem value="25">25</SelectItem>
                            <SelectItem value="50">50</SelectItem>
                            <SelectItem value="100">100</SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
                <span>entri</span>
            </div>

            <!-- Fitur Search Bar -->
            <div v-if="searchable !== false" class="w-full sm:w-auto">
                <div class="relative w-full sm:w-72">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                    />
                    <Input
                        v-model="globalFilter"
                        type="text"
                        :placeholder="searchPlaceholder || ''"
                        class="h-9 rounded-lg pl-9 text-sm"
                    />
                </div>
            </div>
        </div>

        <!-- Tabel Container -->
        <div
            class="overflow-auto scroll-smooth rounded-xl border border-gray-200 bg-white shadow-sm"
        >
            <table class="w-full border-collapse text-left">
                <thead
                    class="bg-gray-100/80 text-xs font-semibold tracking-wider text-gray-700 uppercase"
                >
                    <tr
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <th
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            class="border-b border-gray-200 px-5 py-3.5"
                        >
                            <!-- Menggunakan Button Reka UI untuk kolom yang bisa di-sort -->
                            <Button
                                v-if="
                                    !header.isPlaceholder &&
                                    header.column.getCanSort()
                                "
                                variant="ghost"
                                size="sm"
                                @click="header.column.toggleSorting()"
                                class="group -ml-3 flex h-8 items-center gap-2 px-3 text-xs font-semibold text-gray-600 uppercase transition-colors select-none hover:text-gray-900"
                            >
                                <FlexRender
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                                <span
                                    class="text-gray-400 group-hover:text-gray-600"
                                >
                                    <component
                                        :is="
                                            header.column.getIsSorted() ===
                                            'asc'
                                                ? ChevronUp
                                                : header.column.getIsSorted() ===
                                                    'desc'
                                                  ? ChevronDown
                                                  : ChevronsUpDown
                                        "
                                        :size="14"
                                        :stroke-width="2.5"
                                    />
                                </span>
                            </Button>

                            <!-- Kolom tanpa sort -->
                            <span
                                v-else
                                class="flex h-8 items-center px-0 text-xs font-semibold tracking-wider text-gray-600 uppercase"
                            >
                                <FlexRender
                                    v-if="!header.isPlaceholder"
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                            </span>
                        </th>
                    </tr>
                </thead>

                <!-- BODY DENGAN ODD / EVEN STYLING -->
                <tbody class="divide-y divide-gray-200/60 text-sm">
                    <tr
                        v-for="row in table.getRowModel().rows"
                        :key="row.id"
                        class="transition-colors odd:bg-white even:bg-slate-50/70 hover:bg-blue-50/60"
                    >
                        <td
                            v-for="cell in row.getVisibleCells()"
                            :key="cell.id"
                            class="px-5 py-3 text-gray-700"
                        >
                            <FlexRender
                                :render="cell.column.columnDef.cell"
                                :props="cell.getContext()"
                            />
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="table.getRowModel().rows.length === 0">
                        <td
                            :colspan="columns.length"
                            class="py-12 text-center text-gray-400 italic"
                        >
                            Tidak ada data yang ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer / Pagination Controls -->
        <div
            class="flex flex-col items-center justify-between gap-4 px-2 pt-1 text-sm text-gray-600 sm:flex-row"
        >
            <!-- Informasi Baris -->
            <div class="flex flex-row gap-1">
                Menampilkan
                <span class="font-semibold text-gray-800">
                    {{
                        table.getFilteredRowModel().rows.length > 0
                            ? table.getState().pagination.pageIndex *
                                  table.getState().pagination.pageSize +
                              1
                            : 0
                    }}
                </span>
                sampai
                <span class="font-semibold text-gray-800">
                    {{
                        Math.min(
                            (table.getState().pagination.pageIndex + 1) *
                                table.getState().pagination.pageSize,
                            table.getFilteredRowModel().rows.length,
                        )
                    }}
                </span>
                dari
                <span class="font-semibold text-gray-800">
                    {{ table.getFilteredRowModel().rows.length }}
                </span>
                entri
                <span
                    v-if="
                        table.getFilteredRowModel().rows.length !==
                        props.data.length
                    "
                >
                    (disaring dari {{ props.data.length }} total entri)
                </span>
            </div>

            <!-- Tombol Navigasi Halaman -->
            <div class="flex items-center space-x-1">
                <Button
                    variant="outline"
                    size="icon"
                    class="h-8 w-8 rounded-lg"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.setPageIndex(0)"
                >
                    <ChevronsLeft class="h-4 w-4" />
                </Button>

                <Button
                    variant="outline"
                    size="icon"
                    class="h-8 w-8 rounded-lg"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.previousPage()"
                >
                    <ChevronLeft class="h-4 w-4" />
                </Button>

                <div class="px-3 text-xs font-medium text-gray-700">
                    {{ table.getState().pagination.pageIndex + 1 }} /
                    {{ table.getPageCount() || 1 }}
                </div>

                <Button
                    variant="outline"
                    size="icon"
                    class="h-8 w-8 rounded-lg"
                    :disabled="!table.getCanNextPage()"
                    @click="table.nextPage()"
                >
                    <ChevronRight class="h-4 w-4" />
                </Button>

                <Button
                    variant="outline"
                    size="icon"
                    class="h-8 w-8 rounded-lg"
                    :disabled="!table.getCanNextPage()"
                    @click="table.setPageIndex(table.getPageCount() - 1)"
                >
                    <ChevronsRight class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
