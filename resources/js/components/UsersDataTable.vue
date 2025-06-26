<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef, ColumnFiltersState, SortingState, VisibilityState } from '@tanstack/vue-table'
import { h, ref } from 'vue'
import { ArrowUpDown, ChevronDown, MoreHorizontal, User } from 'lucide-vue-next'
import { FlexRender, getCoreRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useVueTable } from '@tanstack/vue-table'
import { valueUpdater } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Link, router } from '@inertiajs/vue3'

interface User {
    id: number
    uuid: string
    firstname?: string
    lastname?: string
    email: string
    company?: string
    position?: string
    role: string
    created_at: string
    slug: string
    profile_image_url?: string
}

const props = defineProps<{
    users: User[]
    canManageUsers?: boolean
}>()

const columns: ColumnDef<User>[] = [
    {
        accessorKey: 'name',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const user = row.original
            return h('div', { class: 'flex items-center space-x-3' }, [
                h(Avatar, { class: 'h-8 w-8' }, () => [
                    h(AvatarImage, { src: user.profile_image_url || '' }),
                    h(AvatarFallback, {}, () => [
                        h(User, { class: 'h-4 w-4' })
                    ])
                ]),
                h('div', {}, [
                    h('div', { class: 'font-medium' }, user.firstname + ' ' + user.lastname || 'N/A'),
                    user.position && h('div', { class: 'text-sm text-muted-foreground' }, user.position)
                ])
            ])
        },
    },
    {
        accessorKey: 'email',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Email', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => h('div', { class: 'lowercase' }, row.getValue('email')),
    },
    {
        accessorKey: 'company',
        header: 'Company',
        cell: ({ row }) => h('div', {}, row.getValue('company') || 'N/A'),
    },
    {
        accessorKey: 'role',
        header: 'Role',
        cell: ({ row }) => {
            const role = row.getValue('role') as string
                    const roleColors = {
            'god': 'bg-[var(--color-pale-green)] text-[var(--color-light-green)]',
            'admin': 'bg-[var(--color-pale-green)] text-[var(--color-light-green)]',
            'user': 'bg-[var(--color-linen-light)] text-[var(--color-yellow-gold)]'
        }
            return h(Badge, {
                class: roleColors[role as keyof typeof roleColors] || roleColors.user
            }, () => role?.charAt(0).toUpperCase() + role?.slice(1))
        },
    },

    {
        accessorKey: 'created_at',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Joined', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const date = new Date(row.getValue('created_at'))
            return h('div', { class: 'text-sm' }, date.toLocaleDateString())
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const user = row.original
            return h('div', { 
                class: 'relative',
                onClick: (e: Event) => e.stopPropagation()
            }, [
                h(DropdownMenu, {}, () => [
                    h(DropdownMenuTrigger, { asChild: true }, () => [
                        h(Button, { variant: 'ghost', class: 'w-8 h-8 p-0' }, () => [
                            h('span', { class: 'sr-only' }, 'Open menu'),
                            h(MoreHorizontal, { class: 'w-4 h-4' })
                        ])
                    ]),
                    h(DropdownMenuContent, { align: 'end' }, () => [
                        h(DropdownMenuLabel, {}, 'Actions'),
                        h(DropdownMenuItem, { onClick: () => navigator.clipboard.writeText(user.email) }, 'Copy email'),
                        h(DropdownMenuSeparator),
                        h(Link, {
                            href: `/profile/${user.slug}`,
                            class: 'block'
                        }, () => [
                            h(DropdownMenuItem, {}, 'View profile')
                        ]),
                        props.canManageUsers && h(Link, {
                            href: `/edit-profile/${user.slug}`,
                            class: 'block'
                        }, () => [
                            h(DropdownMenuItem, {}, 'Edit user')
                        ])
                    ])
                ])
            ])
        },
    },
]

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})

const table = useVueTable({
    get data() { return props.users },
    get columns() { return columns },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
    state: {
        get sorting() { return sorting.value },
        get columnFilters() { return columnFilters.value },
        get columnVisibility() { return columnVisibility.value },
    },
})

const navigateToProfile = (user: User) => {
    router.visit(`/profile/${user.slug}`)
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <Input
                    placeholder="Filter by name or email..."
                    :model-value="(table.getColumn('email')?.getFilterValue() as string) ?? ''"
                    @update:model-value="table.getColumn('email')?.setFilterValue"
                    class="max-w-sm"
                />
            </div>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="ml-auto">
                        Columns
                        <ChevronDown class="w-4 h-4 ml-2" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuCheckboxItem
                        v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
                        :key="column.id"
                        class="capitalize"
                        :model-value="column.getIsVisible()"
                        @update:model-value="(value) => column.toggleVisibility(!!value)"
                    >
                        {{ column.id }}
                    </DropdownMenuCheckboxItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            :data-state="row.getIsSelected() ? 'selected' : undefined"
                            class="cursor-pointer hover:bg-muted/50"
                            @click="navigateToProfile(row.original)"
                        >
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length" class="h-24 text-center">
                                No users found.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>
        <div class="flex items-center justify-between space-x-2 py-4">
            <div class="flex-1 text-sm text-muted-foreground">
                Showing {{ table.getRowModel().rows.length }} of {{ table.getFilteredRowModel().rows.length }} users
            </div>
            <div class="flex items-center space-x-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.previousPage()"
                >
                    Previous
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!table.getCanNextPage()"
                    @click="table.nextPage()"
                >
                    Next
                </Button>
            </div>
        </div>
    </div>
</template> 