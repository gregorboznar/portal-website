<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FriendCard from '@/components/FriendCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Icon from '@/components/Icon.vue';

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

const performSearch = () => {
    router.get('/member-directory', { search: searchQuery.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(searchQuery, (newValue) => {
    if (!newValue) {
        performSearch();
    }
});
</script>

<template>
    <Head title="Member Directory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Header -->
            <div class="space-y-4">
                <div class="flex items-center space-x-2">
                    <Icon name="member-directory" class="w-6 h-6 text-green-600" />
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Member directory
                    </h1>
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    The Talman Group members.
                </p>
            </div>

            <!-- Search -->
            <div class="flex items-center space-x-4 max-w-md">
                <div class="relative flex-1">
                    <Icon name="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search"
                        class="pl-10"
                        @keyup.enter="performSearch"
                    />
                </div>
                <Button 
                    v-if="searchQuery"
                    @click="performSearch"
                    class="shrink-0"
                >
                    Search
                </Button>
            </div>

            <!-- Members Grid -->
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