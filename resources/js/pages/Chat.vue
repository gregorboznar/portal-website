<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { Head } from '@inertiajs/vue3';
import ChatSidebar from '@/components/chat/ChatSidebar.vue';
import ChatWindow from '@/components/chat/ChatWindow.vue';
import ChatParticipantInfo from '@/components/chat/ChatParticipantInfo.vue';
import { ref, provide, onMounted } from 'vue';
import axios from 'axios';

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

interface Props {
    conversations: Conversation[];
    friends: User[];
    users: User[];
}

const { conversations, friends, users } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chat',
        href: '/chat',
    },
];

const activeConversation = ref<any>(null);
const selectedParticipant = ref<User | null>(null);

const selectConversation = (conversation: any) => {
    activeConversation.value = conversation;
    
    // Handle case where participants might be empty or undefined
    const participant = conversation.participants && conversation.participants.length > 0 
        ? conversation.participants[0] 
        : null;
        
    selectedParticipant.value = participant;
};

const startNewConversation = async (friend: User) => {
    try {
        const response = await axios.post('/api/conversations', {
            participants: [friend.id],
            type: 'direct'
        });

        // Create a conversation object that matches what ChatWindow expects
        const conversationForWindow = {
            id: Date.now(), 
            uuid: response.data.conversation.uuid,
            name: `${friend.firstname} ${friend.lastname}`,
            type: 'direct' as const,
            participants: [{
                id: friend.id,
                name: `${friend.firstname} ${friend.lastname}`,
                slug: friend.slug,
                avatar: friend.avatar || null,
            }],
            last_message: null,
            last_message_at: new Date().toISOString(),
        };
        
        // Set both the active conversation and selected participant
        activeConversation.value = conversationForWindow;
        selectedParticipant.value = friend; // Use the original friend object for ChatParticipantInfo
    } catch (error) {
        console.error('Failed to start conversation:', error);
    }
};


onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('user');
    
    if (userId) {
        const userToMessage = users.find(user => user.id === parseInt(userId));
        if (userToMessage) {
            startNewConversation(userToMessage);
           
            window.history.replaceState({}, '', '/chat');
        }
    }
});

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
            <div v-else class="flex flex-1 items-center justify-center bg-white rounded-lg h-max py-[25px] px-0 shadow-sm
">
                <p class="text-gray-500">Select a conversation to start chatting</p>
            </div>
            <ChatParticipantInfo 
                v-if="selectedParticipant"
                :participant="selectedParticipant"
            />
        </div>
    </AppLayout>
</template> 