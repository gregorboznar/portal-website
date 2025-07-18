<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getInitials } from '@/composables/useInitials';
import TextLink from '@/components/TextLink.vue';
import { computed } from 'vue';

interface User {
    id: number;
    firstname: string;
    lastname: string;
    slug: string;
    avatar?: string | null;
    about?: string | null;
}

interface Props {
    participant: User;
}

const props = defineProps<Props>();
const fullName = computed(() => `${props.participant.firstname} ${props.participant.lastname}`);
</script>

<template>
    <div class="w-[300px] flex-shrink-0 bg-white h-max p-4 dark:border-sidebar-border/10 dark:bg-gray-800/50 rounded-lg shadow-sm">
        <div class="text-center">
            <Avatar class="mx-auto h-36 w-36">
                <AvatarImage v-if="participant.avatar" :src="participant.avatar" :alt="fullName" />
                <AvatarFallback>{{ getInitials(fullName) }}</AvatarFallback>
            </Avatar>
            <h3 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">
                {{ fullName }}
            </h3>
            <p v-if="participant.about" class="mt-2 text-gray-600 dark:text-gray-400 text-left">
               {{ participant.about }} 
            </p>
            <p v-else class="mt-2 text-gray-500 dark:text-gray-500 italic">
               No description available
            </p>
            <TextLink :href="`/profile/${participant.slug}`" class="mt-4 inline-block">
                See profile
            </TextLink>
        </div>
    </div>
</template> 