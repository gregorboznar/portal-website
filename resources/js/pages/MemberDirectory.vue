<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FriendCard from '@/components/FriendCard.vue';
import { Input } from '@/components/ui/input';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import MemberDirectoryIcon from '@/assets/icons/member-directory.svg'
import SearchIcon from '@/assets/icons/search.svg'

interface Member {
    id: number;
    firstname: string;
    lastname: string;
    company?: string;
    slug: string;
    profile_image_url?: string;
    friendship_status?: string | null;
}

interface Props {
    members: Member[];
    search?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Member directory',
        href: '/member-directory',
    },
];

const searchQuery = ref('');
let searchTimeout: number | null = null;

const performSearch = () => {
    router.get('/member-directory', { search: searchQuery.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(() => {
        performSearch();
    }, 300);
};

watch(searchQuery, () => {
    debouncedSearch();
});
</script>

<template>
    <Head title="Member Directory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 ">
            <div class="flex justify-between">
                <div class="ml-3">
                    <div class="flex items-center space-x-2">
                        <MemberDirectoryIcon class="w-5 h-5" />
                        <h1 class="">
                          Member directory
                        </h1>
                    </div>
                    <p class="">
                        The Talman Group members.
                    </p>
                </div>
                <div class="flex items-center space-x-4 max-w-md">
                <div class="relative flex-1">
                    <SearchIcon name="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search"
                        class="pl-10 py-4 bg-white shadow-sm"
                    />
                </div>
            </div>
            </div>
            <div v-if="members.length === 0" class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">
                    No members found.
                </p>
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <FriendCard
                    v-for="member in members"
                    :key="member.id"
                    :friend="member"
                    type="member"
                />
            </div>
        </div>
    </AppLayout>
</template> 