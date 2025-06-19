<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import UsersDataTable from '@/components/UsersDataTable.vue';
import UserIcon from '@/assets/icons/user-card.svg'

interface User {
    id: number
    uuid: string
    name: string
    firstname?: string
    lastname?: string
    email: string
    company?: string
    position?: string
    role: string
    total_tickets?: number
    remaining_tickets?: number
    created_at: string
    slug: string
    profile_image_url?: string
}

interface Props {
    users: User[]
    canManageUsers?: boolean
}

defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex gap-2 ml-3 mb-4">
            <UserIcon class="w-5 h-5 relative top-1" />
            <div class="flex flex-col">
                <h1 class="text-xl font-semibold">Users</h1>
                <p>Manage users</p>
            </div>
        </div>
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4 bg-white">
            <UsersDataTable 
                :users="users" 
                :can-manage-users="canManageUsers" 
            />
        </div>
    </AppLayout>
</template> 