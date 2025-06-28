<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getInitials } from '@/composables/useInitials';
import TextLink from '@/components/TextLink.vue';

interface User {
    id: number;
    name: string;
    slug: string;
    avatar: string | null;
}

interface Props {
    participant: User;
}

defineProps<Props>();
</script>

<template>
    <div class="w-[340px] flex-shrink-0 bg-white p-4 dark:border-sidebar-border/10 dark:bg-gray-800/50 rounded-lg">
        <div class="text-center">
            <Avatar class="mx-auto h-24 w-24">
                <AvatarImage v-if="participant.avatar" :src="participant.avatar" :alt="participant.name" />
                <AvatarFallback>{{ getInitials(participant.name) }}</AvatarFallback>
            </Avatar>
            <h3 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">
                {{ participant.name }}
            </h3>
            <TextLink :href="`/profile/${participant.slug}`" class="mt-4 inline-block">
                See profile
            </TextLink>
        </div>
    </div>
</template> 