<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Input } from '@/components/ui/input';
import { getInitials } from '@/composables/useInitials';
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import axios from 'axios';
import SmileIcon from '@/assets/icons/icon-smile.svg';
import SendMessageIcon from '@/assets/icons/send-message.svg';
import MessageIcon from '@/assets/icons/message-shape-7.svg';
import MessageIconMirrored from '@/assets/icons/message-shape-7-mirrored.svg';

interface User {
    id: number;
    name: string;
    slug: string;
    avatar: string | null;
}

interface Message {
    id: number;
    uuid: string;
    content: string;
    type: 'text' | 'image' | 'file';
    user: User;
    is_mine: boolean;
    created_at: string;
}

interface Conversation {
    id: number;
    uuid: string;
    name: string;
    type: 'direct' | 'group';
    participants: User[];
    last_message?: any;
}

interface Props {
    conversation: Conversation;
}

const props = defineProps<Props>();

const messages = ref<Message[]>([]);
const newMessage = ref('');
const loading = ref(true);
const sending = ref(false);
const messagesContainer = ref<HTMLElement>();

const currentUser = computed(() => {
    return (window as any).auth?.user || { id: 0, name: 'Unknown' };
});

const formatMessageTime = (timestamp: string) => {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
    });
};

const loadMessages = async () => {
    try {
        loading.value = true;
        const response = await axios.get(`/api/conversations/${props.conversation.uuid}`);
        messages.value = response.data.messages;
        await nextTick();
        scrollToBottom();
    } catch (error) {
        console.error('Failed to load messages:', error);
    } finally {
        loading.value = false;
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || sending.value) return;

    const messageContent = newMessage.value.trim();
    newMessage.value = '';
    sending.value = true;

    try {
        const response = await axios.post(`/api/conversations/${props.conversation.uuid}/messages`, {
            content: messageContent,
            type: 'text'
        });


        const optimisticMessage: Message = {
            id: Date.now(), // temporary ID
            uuid: response.data.message.uuid,
            content: messageContent,
            type: 'text',
            user: {
                id: currentUser.value.id,
                name: currentUser.value.name,
                slug: currentUser.value.slug || '',
                avatar: currentUser.value.avatar || null,
            },
            is_mine: true,
            created_at: new Date().toISOString(),
        };

        if (!messages.value.find(m => m.uuid === optimisticMessage.uuid)) {
            messages.value.push(optimisticMessage);
        }

        await nextTick();
        scrollToBottom();
    } catch (error) {
        console.error('Failed to send message:', error);
        newMessage.value = messageContent; // Restore message on error
    } finally {
        sending.value = false;
    }
};

const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const handleKeyPress = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};

// WebSocket setup
let channel: any = null;

const setupWebSocket = () => {
    if (window.Echo && props.conversation.uuid) {
        channel = window.Echo.private(`conversation.${props.conversation.uuid}`)
            .listen('.message.sent', (data: any) => {
                if (!messages.value.find(m => m.uuid === data.uuid)) {
                    messages.value.push({
                        id: data.id,
                        uuid: data.uuid,
                        content: data.content,
                        type: data.type,
                        user: data.user,
                        is_mine: data.user.id === currentUser.value.id,
                        created_at: data.created_at,
                    });
                    
                    nextTick(() => {
                        scrollToBottom();
                    });
                }
            });
    }
};

const cleanupWebSocket = () => {
    if (channel) {
        window.Echo.leave(`conversation.${props.conversation.uuid}`);
        channel = null;
    }
};

watch(() => props.conversation.uuid, (newUuid, oldUuid) => {
    if (oldUuid !== newUuid) {
        cleanupWebSocket();
        loadMessages();
        setupWebSocket();
    }
});

onMounted(() => {
    loadMessages();
    setupWebSocket();
});

onUnmounted(() => {
    cleanupWebSocket();
});
</script>

<template>
    <div class="flex flex-1 flex-col max-h-screen">
        <!-- Chat Header -->
        <div class="shadow-sm rounded-lg">
            <div class="flex items-center gap-4 p-4 dark:border-sidebar-border/10 bg-white rounded-t-lg">
                <div class="flex -space-x-4">
                    <Avatar
                        v-for="p in conversation.participants"
                        :key="p.id"
                        class="h-10 w-10 border-2 border-white dark:border-gray-800"
                    >
                        <AvatarImage v-if="p.avatar" :src="p.avatar" :alt="p.name" />
                        <AvatarFallback>{{ getInitials(p.name) }}</AvatarFallback>
                    </Avatar>
                </div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ conversation.name }}
                </h2>
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4"></div>
            </div>

            <!-- Messages -->
            <div
                ref="messagesContainer"
                class="space-y-4 overflow-y-auto p-6 h-[24rem] bg-white rounded-b-lg"
            >
                <div v-if="loading" class="flex items-center justify-center h-full">
                    <p class="text-gray-500">Loading messages...</p>
                </div>

                <div
                    v-for="(msg, index) in messages"
                    :key="msg.uuid"
                    class="flex flex-col"
                    :class="{ 'items-end': msg.is_mine, 'items-start': !msg.is_mine }"
                >
                    <div class="flex items-normal gap-3" :class="{ 'flex-row-reverse': msg.is_mine }">
                        <Avatar
                            v-if="index === messages.length - 1 || messages[index + 1].user.id !== msg.user.id"
                            class="h-8 w-8 bottom-[0.4rem]"
                        >
                            <AvatarImage
                                v-if="msg.user.avatar"
                                :src="msg.user.avatar"
                                :alt="msg.user.name"
                            />
                            <AvatarFallback>{{ getInitials(msg.user.name) }}</AvatarFallback>
                        </Avatar>
                        <div v-else class="w-8" />
                        <div class="flex relative">
                            <MessageIcon
                                v-if="!msg.is_mine"
                                class="w-[2.5rem] absolute top-[-0.4rem] left-0"
                            />
                            <MessageIconMirrored
                                v-else
                                class="w-[2.5rem] absolute top-[-0.4rem] right-0"
                            />
                            <div
                                class="max-w-md rounded-2xl p-4 min-w-[4rem]"
                                :class="{
                                    'rounded-tl-none': !msg.is_mine,
                                    'rounded-tr-none text-white': msg.is_mine,
                                }"
                                :style="
                                    msg.is_mine
                                        ? { backgroundColor: 'var(--color-linen-light)' }
                                        : { backgroundColor: 'var(--color-pale-green)' }
                                "
                            >
                                <p>{{ msg.content }}</p>
                            </div>
                        </div>
                    </div>

                    <span
                        class="mt-1 text-xs text-gray-500"
                        :class="msg.is_mine ? 'pr-11' : 'pl-11'"
                    >
                        {{ formatMessageTime(msg.created_at) }}
                    </span>
                </div>
            </div>
        </div>
        <!-- Message Input -->
        <div class="p-4 bg-white rounded-lg mt-4 flex items-center relative gap-1 shadow-sm">
            <div class="relative rounded-lg bg-gray-100 dark:bg-gray-700 w-full">
                <Input
                    v-model="newMessage"
                    type="text"
                    placeholder="Type a message..."
                    class="w-full border-none bg-transparent py-4 pl-4 pr-16"
                    :disabled="sending"
                    @keypress="handleKeyPress"
                />
                <div class="absolute top-2 -right-1 flex items-center">
                    <button
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-2"
                        disabled
                    >
                        <SmileIcon class="h-6 w-6" />
                    </button>
                </div>
            </div>
            <button
                class="text-blue-500 hover:text-blue-600 disabled:opacity-50 relative"
                :disabled="!newMessage.trim() || sending"
                @click="sendMessage"
            >
                <SendMessageIcon class="h-7 w-7" />
            </button>
        </div>
    </div>
</template>