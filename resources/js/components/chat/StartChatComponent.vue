<script setup lang="ts">
import { ref, computed } from 'vue';
import { 
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogClose
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { type User } from '@/types';
import MessageIcon from '@/assets/icons/message-2-green.svg';
import SearchIcon from '@/assets/icons/search.svg';

interface StartChatDialogProps {
    open: boolean;
    users: User[];
}

const props = defineProps<StartChatDialogProps>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'startChat': [userId: number];
}>();

const searchQuery = ref('');
const selectedUserId = ref<number | null>(null);

const filteredUsers = computed(() => {
    if (!searchQuery.value) {
        return props.users;
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.users.filter(user => {
        const fullName = `${user.firstname} ${user.lastname}`.toLowerCase();
        return fullName.includes(query) || user.email.toLowerCase().includes(query);
    });
});

const handleOpenChange = (value: boolean) => {
    emit('update:open', value);
    if (!value) {
        searchQuery.value = '';
        selectedUserId.value = null;
    }
};

const handleConfirm = () => {
    if (selectedUserId.value) {
        emit('startChat', selectedUserId.value);
        handleOpenChange(false);
    }
};
</script>

<template>
    <Dialog :open="props.open" @update:open="handleOpenChange">
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="border-b border-gray-200 dark:border-gray-700 mb-3 pb-1">
                <div class="flex items-center gap-3">
                    <MessageIcon class="h-6 w-6" />
                    <DialogTitle class="text-xl font-semibold">Start chat</DialogTitle>
                </div>
                <DialogClose />
            </DialogHeader>
            
            <div class="space-y-4">
                <div class="relative">
                    <SearchIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search users..."
                        class="pl-10 rounded-lg border-gray-200 focus:border-green-500 focus:ring-green-500 "
                    />
                </div>
                
                <div class="max-h-80 overflow-y-auto space-y-2">
                    <div 
                        v-for="user in filteredUsers" 
                        :key="user.id"
                        :class="[
                            'flex items-center space-x-3 p-3 rounded-lg cursor-pointer transition-colors border',
                            selectedUserId === user.id 
                                ? 'bg-bg hover:bg-bg border-[var(--color-light-green)] shadow-[var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)]' 
                                : 'hover:bg-gray-50 border-gray'
                        ]"
                        @click="selectedUserId = user.id"
                    >
                        <div class="relative">
                            <input
                                type="radio"
                                :value="user.id"
                                v-model="selectedUserId"
                                class="w-4 h-4 text-green border-green focus:ring-green accent-green"
                            />
                        </div>
                        
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                                <img 
                                    v-if="user.avatar || user.profile_image_url" 
                                    :src="user.avatar || user.profile_image_url" 
                                    :alt="`${user.firstname} ${user.lastname}`" 
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-sm font-medium text-gray-600">
                                    {{ user.firstname.charAt(0) }}{{ user.lastname.charAt(0) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ user.firstname }} {{ user.lastname }}
                            </p>
                            <p v-if="user.company" class="text-xs text-gray-500 truncate">
                                {{ user.company }}
                            </p>
                        </div>
                    </div>
                    
                    <div v-if="filteredUsers.length === 0" class="text-center py-8 text-gray-500">
                        <p class="text-sm">No users found</p>
                    </div>
                </div>
                
                <div class="pt-4">
                    <Button 
                        @click="handleConfirm"
                        :disabled="!selectedUserId"
                        class="w-full bg-green hover:opacity-95 text-white rounded-lg py-3 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Confirm
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>