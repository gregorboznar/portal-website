<script setup lang="ts">
import { computed } from 'vue';
import { 
    Dialog, 
    DialogContent, 
    DialogDescription, 
    DialogFooter, 
    DialogHeader, 
    DialogTitle 
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

interface UploadCoverDialogProps {
    open: boolean;
}

const props = defineProps<UploadCoverDialogProps>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'upload-complete': [];
}>();

const handleOpenChange = (value: boolean) => {
    emit('update:open', value);
};

const handleCancel = () => {
    emit('update:open', false);
};

const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
};

const handleCoverUploadResponse = (response: string) => {
    const data = JSON.parse(response);
    if (data.success) {
        emit('upload-complete');
        emit('update:open', false);
    }
    return response;
};

const handleUploadError = (error: any) => {
    console.error('Upload error:', error);
};

const coverServerConfig = computed(() => ({
    process: {
        url: '/api/images/upload-cover',
        method: 'POST',
        onload: handleCoverUploadResponse,
        onerror: handleUploadError,
        headers: {
            'X-CSRF-TOKEN': getCsrfToken()
        }
    }
}));
</script>

<template>
    <Dialog :open="props.open" @update:open="handleOpenChange">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Upload Cover Photo</DialogTitle>
                <DialogDescription>
                    Choose an image to use as your cover photo. Maximum file size is 10MB.
                </DialogDescription>
            </DialogHeader>
            
            <div class="py-4">
                <file-pond
                    name="cover_image"
                    label-idle="Drop image here or click to browse"
                    :allow-multiple="false"
                    accepted-file-types="image/jpeg, image/jpg, image/png"
                    max-file-size="10MB"
                    :server="coverServerConfig"
                    class="cover-upload-filepond"
                    :image-preview-height="200"
                    image-resize-target-width="1200"
                    image-resize-target-height="400"
                />
            </div>
            
            <DialogFooter>
                <Button variant="outline" @click="handleCancel">
                    Cancel
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>