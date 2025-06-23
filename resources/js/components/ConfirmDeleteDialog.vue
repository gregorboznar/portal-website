<script setup lang="ts">
import { 
    AlertDialog, 
    AlertDialogAction, 
    AlertDialogCancel, 
    AlertDialogContent, 
    AlertDialogDescription, 
    AlertDialogFooter, 
    AlertDialogHeader, 
    AlertDialogTitle 
} from '@/components/ui/alert-dialog';

interface ConfirmDeleteDialogProps {
    open: boolean;
    title?: string;
    description?: string;
    confirmText?: string;
    isLoading?: boolean;
}

const props = withDefaults(defineProps<ConfirmDeleteDialogProps>(), {
    title: 'Are you absolutely sure?',
    description: 'This action cannot be undone. This will permanently delete this item and remove it from our servers.',
    confirmText: 'Delete',
    isLoading: false
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    'confirm': [];
}>();

const handleOpenChange = (value: boolean) => {
    emit('update:open', value);
};

const handleConfirm = () => {
    emit('confirm');
};
</script>

<template>
    <AlertDialog :open="props.open" @update:open="handleOpenChange">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel :disabled="isLoading">Cancel</AlertDialogCancel>
                <AlertDialogAction 
                    @click="handleConfirm" 
                    class="bg-red-600 hover:bg-red-700 text-white"
                    :disabled="isLoading"
                >
                    {{ isLoading ? 'Deleting...' : confirmText }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>