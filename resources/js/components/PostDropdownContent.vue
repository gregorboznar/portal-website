<script setup lang="ts">
import { DropdownMenuGroup, DropdownMenuItem } from '@/components/ui/dropdown-menu';

import PinIcon from '@/assets/icons/pin.svg'
import DeleteIcon from '@/assets/icons/delete.svg'



interface PostDropdownProps {
    postId: number;
    isPinned?: boolean;
    canManage?: boolean;
}

const props = defineProps<PostDropdownProps>();

const emit = defineEmits<{
    'pin-toggled': [postId: number, isPinned: boolean];
    'post-deleted': [postId: number];
    'delete-requested': [postId: number];
}>();

const handlePin = async () => {
    try {
        const response = await fetch(`/api/posts/${props.postId}/pin`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        
        if (response.ok) {
            const data = await response.json();
            emit('pin-toggled', props.postId, data.isPinned);
            window.location.reload();
        }
    } catch (error) {
        console.error('Error toggling pin:', error);
    }
};

const handleDeleteClick = () => {
    emit('delete-requested', props.postId);
};
</script>

<template>
    <DropdownMenuGroup>
        <DropdownMenuItem @click="handlePin" class="cursor-pointer">
            <PinIcon v-if="!isPinned" class="mr-2 h-4 w-4" />
            <PinIcon v-else class="mr-2 h-4 w-4" />
            {{ isPinned ? 'Unpin Post' : 'Pin Post' }}
        </DropdownMenuItem>
        <DropdownMenuItem @click="handleDeleteClick" class="text-red-600 focus:text-red-600 cursor-pointer">
            <DeleteIcon class="mr-2 h-4 w-4" />
            Delete Post
        </DropdownMenuItem>
    </DropdownMenuGroup>
</template>
