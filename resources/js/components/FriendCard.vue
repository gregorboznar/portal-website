<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { getInitials } from '@/composables/useInitials';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmFriendshipIcon from '@/assets/icons/confirm-friendship.svg'
import DeclineFriendshipIcon from '@/assets/icons/decline-friendship.svg'
import MessageIcon from '@/assets/icons/message-3.svg'
import MessageIcon2 from '@/assets/icons/message-4.svg'
import AddFriendIcon from '@/assets/icons/add-friend-icon.svg'

interface Friend {
    id: number;
    firstname: string;
    lastname: string;
    company?: string;
    slug: string;
    profile_image_url?: string | null;
    friendship_id?: number;
    friendship_status?: string | null;
}

interface Props {
    friend: Friend;
    type: 'request' | 'friend' | 'suggested' | 'member';
}

const props = defineProps<Props>();
const isLoading = ref(false);
const requestSent = ref(false);

const fullName = `${props.friend.firstname || ''} ${props.friend.lastname || ''}`.trim() || 'Unknown User';

const sendFriendRequest = async () => {
    isLoading.value = true;
    try {
        await router.post('/friendships', { friend_id: props.friend.id });
        requestSent.value = true;
    } finally {
        isLoading.value = false;
    }
};

const acceptFriendRequest = async () => {
    isLoading.value = true;
    try {
        await router.patch(`/friendships/${props.friend.friendship_id}/accept`);
    } finally {
        isLoading.value = false;
    }
};

const declineFriendRequest = async () => {
    isLoading.value = true;
    try {
        await router.delete(`/friendships/${props.friend.friendship_id}/decline`);
    } finally {
        isLoading.value = false;
    }
};

const removeFriend = async () => {
    isLoading.value = true;
    try {
        await router.delete(`/friendships/${props.friend.id}`);
    } finally {
        isLoading.value = false;
    }
};

const visitProfile = () => {
    router.visit(`/profile/${props.friend.slug}`);
};

const sendMessage = () => {
    // Placeholder for messaging functionality
    console.log('Send message to', fullName);
};
</script>

<template>
    <div class="flex flex-col items-center space-y-4 p-4  rounded-lg bg-white dark:bg-gray-800">
        <Avatar 
            class="w-24 h-24 cursor-pointer" 
            @click="visitProfile"
        >
            <AvatarImage 
                :src="friend.profile_image_url || ''" 
                :alt="fullName" 
            />
            <AvatarFallback class="text-lg font-medium">
                {{ getInitials(fullName) }}
            </AvatarFallback>
        </Avatar>

        <div class="text-center">
            <h3 
                class="font-semibold text-gray-900 dark:text-gray-100 cursor-pointer"
                @click="visitProfile"
            >
                {{ fullName }}
            </h3>
            <p class="green-text" v-if="friend.company">
                {{ friend.company }}
            </p>
        </div>

        <!-- Friend Request Actions -->
        <div v-if="type === 'request'" class="flex flex-col space-y-2 w-full">
            <Button 
                @click="acceptFriendRequest" 
                :disabled="isLoading"
                class="w-full bg-green text-white"
            >
                <ConfirmFriendshipIcon class="w-4 h-4 relative top-0.5" />
                Confirm friendship
            </Button>
            <Button 
                @click="declineFriendRequest" 
                :disabled="isLoading"
                variant="outline"
                class="w-full"
            >
                <DeclineFriendshipIcon class="w-4 h-4" />
                Decline friendship
            </Button>
            <Button 
                @click="sendMessage" 
                variant="outline"
                class="w-full"
            >
                <MessageIcon2 class="w-5 h-5 mr-1" />
                Message
            </Button>
        </div>

        <!-- Existing Friend Actions -->
        <div v-else-if="type === 'friend'" class="flex flex-col space-y-2 w-full">
            <Button @click="sendMessage" class="w-full bg-green text-white">
                <MessageIcon class="w-5 h-5 mr-1" />
                Message
            </Button>
            <Button 
                @click="removeFriend" 
                :disabled="isLoading"
                variant="outline"
                class="w-full"
            >
                <DeclineFriendshipIcon class="w-4 h-4" />
                Remove Friend
            </Button>
            
        </div>

        <!-- Suggested Friend Actions -->
        <div v-else-if="type === 'suggested'" class="flex flex-col space-y-2 w-full">
            <Button 
                v-if="!requestSent"
                @click="sendFriendRequest" 
                :disabled="isLoading"
                class="w-full bg-green text-white"
            >
                <AddFriendIcon class="w-5 h-5 mr-2" />
                Add friend
            </Button>
            <Button 
                v-else
                disabled
                variant="outline"
                class="w-full"
            >
                Request sent
            </Button>
            <Button 
                @click="sendMessage" 
                variant="outline"
                class="w-full"
            >
                <MessageIcon2 class="w-5 h-5 mr-1" />
                Message
            </Button>
        </div>

        <!-- Member Directory Actions -->
        <div v-else-if="type === 'member'" class="flex flex-col space-y-2 w-full">
            <Button 
                v-if="!friend.friendship_status && !requestSent"
                @click="sendFriendRequest" 
                :disabled="isLoading"
                class="w-full bg-green text-white"
            >
                <AddFriendIcon name="user-plus" class="w-5 h-5 " />
                Add friend
            </Button>
            <Button 
                v-else-if="friend.friendship_status === 'request_sent' || requestSent"
                disabled
                variant="outline"
                class="w-full"
            >
                Request sent
            </Button>
            <Button 
                v-else-if="friend.friendship_status === 'friends'"
                @click="removeFriend" 
                :disabled="isLoading"
                variant="outline"
                class="w-full "
            >
                <DeclineFriendshipIcon name="x" class="w-4 h-4 " />
                Remove Friend
            </Button>
            <Button 
                @click="sendMessage" 
                variant="outline"
                class="w-full"
            >
                <MessageIcon2 name="message-2" class="w-5 h-5 mr-1" />
                Message
            </Button>
        </div>
    </div>
</template> 