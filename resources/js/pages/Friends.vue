<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FriendCard from '@/components/FriendCard.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import CheckIcon from '@/assets/icons/checkmark-2.svg'
import FriendsIcon from '@/assets/icons/friends.svg'
import RocketIcon from '@/assets/icons/rocket.svg'

interface Friend {
    id: number;
    firstname: string;
    lastname: string;
    company?: string;
    slug: string;
    profile_image_url?: string;
    friendship_id?: number;
}

interface Props {
    friendRequests: Friend[];
    friends: Friend[];
    suggested: Friend[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Friends',
        href: '/friends',
    },
];

const showAllSuggested = computed(() => false);
const showAllFriends = computed(() => false);
</script>

<template>
    <Head title="Friends" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 ">
            <!-- Friend Requests Section -->
            <div v-if="friendRequests.length > 0" class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 ml-3">
                        <CheckIcon name="check" class="w-5 h-5 top-1 relative" />
                        <h1 class="">
                            Friend requests
                        </h1>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <FriendCard
                        v-for="request in friendRequests"
                        :key="request.id"
                        :friend="request"
                        type="request"
                    />
                </div>
            </div>

            <!-- Friends Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 ml-3">
                        <FriendsIcon class="w-5 h-5" />
                        <h1 class="">
                            Friends
                        </h1>
                    </div>
                    <Button 
                        v-if="friends.length > 10"
                        variant="ghost" 
                        class="text-blue-600 hover:text-blue-700"
                    >
                        Show all
                    </Button>
                </div>

                <div v-if="friends.length === 0" class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">
                        You don't have any friends yet. Start by adding friends from the suggestions below.
                    </p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <FriendCard
                        v-for="friend in (showAllFriends ? friends : friends.slice(0, 10))"
                        :key="friend.id"
                        :friend="friend"
                        type="friend"
                    />
                </div>
            </div>

            <!-- Suggested Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 ml-3">
                        <RocketIcon class="w-5 h-5" />
                        <h1 class="">
                            Suggested
                        </h1>
                    </div>
                    <Button 
                        v-if="suggested.length > 10"
                        variant="ghost" 
                        class="text-blue-600 hover:text-blue-700"
                    >
                        Show all
                    </Button>
                </div>

                <div v-if="suggested.length === 0" class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">
                        No suggestions available at the moment.
                    </p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <FriendCard
                        v-for="suggestion in (showAllSuggested ? suggested : suggested.slice(0, 10))"
                        :key="suggestion.id"
                        :friend="suggestion"
                        type="suggested"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template> 