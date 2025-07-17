<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Input } from '@/components/ui/input';
import Icon from '@/components/Icon.vue';
import { getInitials } from '@/composables/useInitials';
import SearchIcon from '@/assets/icons/search.svg';
import { computed, ref } from 'vue';
import { usePresence } from '@/composables/usePresence';
import PencilIcon from '@/assets/icons/note-pencil.svg';
import StartChatComponent from '@/components/chat/StartChatComponent.vue';
import { type User } from '@/types';

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
    activeConversation?: Conversation | null;
    users: User[];
}

interface Emits {
    (e: 'selectConversation', conversation: Conversation): void;
    (e: 'startConversation', friend: User): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();
const openStartChatModal = ref(false);

const searchQuery = ref('');
const { onlineUsers } = usePresence();

const filteredConversations = computed(() => {
    if (!searchQuery.value) return props.conversations;
    return props.conversations.filter(conversation =>
        conversation.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const formatLastMessageTime = (timestamp: string) => {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
    });
};

const selectConversation = (conversation: Conversation) => {
    emit('selectConversation', conversation);
};

const startConversation = (friend: User) => {
    emit('startConversation', friend);
};




const onlineUsersList = computed(() => {
    return props.users.filter(user => {
        return onlineUsers.value.some((onlineUser: any) => onlineUser.id === user.id);
    });
});

const getUserFullName = (user: User) => {
    return `${user.firstname} ${user.lastname}`;
};

</script>

<template>
    <div class="h-full w-[340px] flex-shrink-0 bg-white p-4 dark:bg-gray-800 rounded-lg">
        <div class="flex flex-col gap-4">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4" v-if="onlineUsersList.length > 0">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Online Users  
                </h2>
                <div class="mt-4 flex items-center gap-4 overflow-x-auto">
                    <div 
                        v-for="user in onlineUsersList" 
                        :key="user.id" 
                        class="relative cursor-pointer text-center flex-shrink-0"
                        @click="startConversation(user)"
                    >
                        <Avatar class="relative mx-auto h-14 w-14">
                            <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="getUserFullName(user)" />
                            <AvatarFallback>{{ getInitials(getUserFullName(user)) }}</AvatarFallback>
                        </Avatar>
                        <div class="absolute bottom-7 right-3 h-4 w-4 rounded-full border-2 border-white bg-green-500" />
                        <span class="mt-2 block text-sm font-medium text-gray-700 dark:text-gray-300 truncate max-w-[80px]">{{ getUserFullName(user) }}</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-1 flex-col">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Messages
                    </h2>
                    <button @click="openStartChatModal = true" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" title="Start new chat">
                        <PencilIcon class="h-5 w-5" />
                    </button>
                </div>
                <div class="relative mb-4">
                    <SearchIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    <Input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search" 
                        class="pl-10" 
                    />
                </div>
                <div class="flex-1 space-y-2 overflow-y-auto">
                    <div
                        v-for="conversation in filteredConversations"
                        :key="conversation.id"
                        class="flex cursor-pointer items-center gap-3 rounded-lg p-3"
                        :class="{
                            'bg-gray-100 dark:bg-gray-700': activeConversation?.id === conversation.id,
                            'hover:bg-gray-50 dark:hover:bg-gray-700/50': activeConversation?.id !== conversation.id,
                        }"
                        @click="selectConversation(conversation)"
                    >
                    
                        <Avatar class="relative h-12 w-12">
                            
                            <AvatarImage 
                                v-if="Object.values(conversation.participants)[0]?.avatar" 
                                :src="Object.values(conversation.participants)[0].avatar!" 
                                :alt="conversation.name" 
                            />
                            <AvatarFallback>{{ getInitials(conversation.name) }}</AvatarFallback>
                            <Icon 
                                v-if="conversation.type === 'group'" 
                                name="group" 
                                class="absolute bottom-0 right-0 h-5 w-5 rounded-full border-2 border-white bg-gray-400 p-0.5 text-white" 
                            />
                        </Avatar>
                        <div class="flex-1 overflow-hidden">
                            <p class="truncate font-semibold text-gray-800 dark:text-gray-200">
                                {{ conversation.name }}
                            </p>
                            <p v-if="conversation.last_message" class="truncate text-sm text-gray-500 dark:text-gray-400">
                                {{ conversation.last_message.is_mine ? 'You: ' : '' }}{{ conversation.last_message.content }}
                            </p>
                            <p v-else class="truncate text-sm text-gray-500 dark:text-gray-400">
                                Start chatting here
                            </p>
                        </div>
                        <div v-if="conversation.last_message" class="text-xs text-gray-400">
                            {{ formatLastMessageTime(conversation.last_message.created_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
    <StartChatComponent
        v-model:open="openStartChatModal"
        :users="users"
    />
</template> 