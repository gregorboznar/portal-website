<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ChatSidebar from '@/components/chat/ChatSidebar.vue';
import ChatWindow from '@/components/chat/ChatWindow.vue';
import ChatParticipantInfo from '@/components/chat/ChatParticipantInfo.vue';
import { ref, provide } from 'vue';
import axios from 'axios';

interface User {
    id: number;
    name: string;
    slug: string;
    avatar: string | null;
    online?: boolean;
}

interface Conversation {
    id: number;
    uuid: string;
    name: string;
    type: 'direct' | 'group';
    participants: User[];
    last_message: {
        content: string;
        user_name: string;
        created_at: string;
        is_mine: boolean;
    } | null;
    last_message_at: string;
}

interface Users {
    id: number;
    firstname: string;
    lastname: string;
    slug: string;
    avatar: string | null;
}

interface Props {
    conversations: Conversation[];
    friends: User[];
    users: Users[];
}

const { conversations, friends, users } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chat',
        href: '/chat',
    },
];

const activeConversation = ref<Conversation | null>(null);
const selectedParticipant = ref<User | null>(null);

const selectConversation = (conversation: Conversation) => {
    activeConversation.value = conversation;
    selectedParticipant.value = conversation.participants[0] || null;
};

const startNewConversation = async (friend: User) => {
    try {
        const response = await axios.post('/api/conversations', {
            participants: [friend.id],
            type: 'direct'
        });

        const newConversation: Conversation = {
            id: Date.now(), 
            uuid: response.data.conversation.uuid,
            name: friend.name,
            type: 'direct',
            participants: [friend],
            last_message: null,
            last_message_at: new Date().toISOString(),
        };
        
        selectConversation(newConversation);
    } catch (error) {
        console.error('Failed to start conversation:', error);
    }
};

provide('activeConversation', activeConversation);
provide('selectedParticipant', selectedParticipant);
provide('selectConversation', selectConversation);
provide('startNewConversation', startNewConversation);
</script>

<template>
    <Head title="Chat" />

    <AppLayout :breadcrumbs="breadcrumbs" :show-background="false">
        <div class="flex h-full flex-1 space-x-4">
            <ChatSidebar 
                :conversations="conversations" 
                :friends="friends"
                :active-conversation="activeConversation"
                @select-conversation="selectConversation"
                @start-conversation="startNewConversation"
                :users="users"
            />
            <ChatWindow 
                v-if="activeConversation"
                :conversation="activeConversation"
            />
            <div v-else class="flex flex-1 items-center justify-center bg-white rounded-lg">
                <p class="text-gray-500">Select a conversation to start chatting</p>
            </div>
            <ChatParticipantInfo 
                v-if="selectedParticipant"
                :participant="selectedParticipant"
            />
        </div>
    </AppLayout>
</template> 