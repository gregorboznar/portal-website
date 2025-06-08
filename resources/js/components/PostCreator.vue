<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { BarChart3, Smile, Upload, X } from 'lucide-vue-next';
import { ref } from 'vue';
import axios from 'axios';

interface Post {
    id: number;
    author: {
        name: string;
        company: string;
    };
    content: string;
    type: 'regular' | 'poll';
    poll_options?: string[];
    timestamp: string;
    likes: number;
    comments: number;
    views: number;
    isLiked: boolean;
}

interface UploadedImage {
    id: number;
    url: string;
    filename: string;
    original_filename: string;
    width: number;
    height: number;
    optimizations: Record<string, any>;
}

const emit = defineEmits<{
    'post-created': [post: Post]
}>();

const postText = ref('');
const isPosting = ref(false);
const postType = ref<'regular' | 'poll'>('regular');
const pollOptions = ref<string[]>(['', '']);
const isDialogOpen = ref(false);
const uploadedImages = ref<UploadedImage[]>([]);
const isUploading = ref(false);
const showEmojiPicker = ref(false);
const fileInputRef = ref<HTMLInputElement>();

const addPollOption = () => {
    if (pollOptions.value.length < 10) {
        pollOptions.value.push('');
    }
};

const removePollOption = (index: number) => {
    if (pollOptions.value.length > 2) {
        pollOptions.value.splice(index, 1);
    }
};

const togglePoll = () => {
    postType.value = postType.value === 'regular' ? 'poll' : 'regular';
    if (postType.value === 'poll' && pollOptions.value.length === 0) {
        pollOptions.value = ['', ''];
    }
};

const openDialog = () => {
    isDialogOpen.value = true;
};

const closeDialog = () => {
    isDialogOpen.value = false;
    postText.value = '';
    postType.value = 'regular';
    pollOptions.value = ['', ''];
    uploadedImages.value = [];
    showEmojiPicker.value = false;
};

const onFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    if (files && files.length > 0) {
        uploadImage(files[0]);
    }
};

const uploadImage = async (file: File) => {
    if (isUploading.value) return;
    
    try {
        isUploading.value = true;
        
        const formData = new FormData();
        formData.append('image', file);
        formData.append('type', 'posts');
        
        const response = await axios.post('/api/images/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        
        if (response.data.success) {
            uploadedImages.value.push(response.data.image);
        }
    } catch (error) {
        console.error('Error uploading image:', error);
    } finally {
        isUploading.value = false;
        if (fileInputRef.value) {
            fileInputRef.value.value = '';
        }
    }
};

const removeImage = (index: number) => {
    uploadedImages.value.splice(index, 1);
};

const triggerFileUpload = () => {
    fileInputRef.value?.click();
};

const insertEmoji = (emoji: string) => {
    postText.value += emoji;
    showEmojiPicker.value = false;
};

const submitPost = async () => {
    if (!postText.value.trim() && uploadedImages.value.length === 0) return;
    
    try {
        isPosting.value = true;
        
        const payload: any = {
            content: postText.value,
            type: postType.value,
            images: uploadedImages.value.map(img => img.id),
        };
        
        if (postType.value === 'poll') {
            payload.poll_options = pollOptions.value.filter(option => option.trim());
        }
        
        const response = await axios.post('/api/posts', payload);
        
        emit('post-created', response.data);
        closeDialog();
        
    } catch (error) {
        console.error('Error creating post:', error);
    } finally {
        isPosting.value = false;
    }
};

const commonEmojis = ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳'];
</script>

<template>
    <Dialog v-model:open="isDialogOpen">
        <DialogTrigger as-child>
            <Card class="w-full cursor-pointer hover:bg-gray-50 transition-colors" @click="openDialog">
                <CardContent class="p-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="w-full text-lg text-gray-500 py-3">
                                What are you thinking?
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </DialogTrigger>
        
        <DialogContent class="sm:max-w-[600px] max-h-[80vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Create Post</DialogTitle>
                <DialogDescription>
                    Share your thoughts, add images, create polls, and more.
                </DialogDescription>
            </DialogHeader>
            
            <div class="space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <textarea
                            v-model="postText"
                            placeholder="What are you thinking?"
                            class="w-full border-none outline-none resize-none text-lg placeholder-gray-500 bg-transparent min-h-[120px]"
                            rows="5"
                        ></textarea>
                    </div>
                </div>
                
                <!-- Uploaded Images -->
                <div v-if="uploadedImages.length > 0" class="grid grid-cols-2 gap-2">
                    <div v-for="(image, index) in uploadedImages" :key="image.id" class="relative group">
                        <img 
                            :src="image.optimizations?.medium?.url || image.url" 
                            :alt="image.original_filename"
                            class="w-full h-32 object-cover rounded-lg"
                        />
                        <Button
                            variant="destructive"
                            size="sm"
                            class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity w-6 h-6 p-0"
                            @click="removeImage(index)"
                        >
                            <X class="w-3 h-3" />
                        </Button>
                    </div>
                </div>
                
                <!-- Poll Options -->
                <div v-if="postType === 'poll'" class="space-y-2">
                    <div v-for="(option, index) in pollOptions" :key="index" class="flex items-center space-x-2">
                        <input
                            v-model="pollOptions[index]"
                            :placeholder="`Option ${index + 1}`"
                            class="flex-1 border border-gray-200 rounded px-3 py-2 text-sm"
                        />
                        <Button
                            v-if="pollOptions.length > 2"
                            variant="ghost"
                            size="sm"
                            @click="removePollOption(index)"
                            class="text-red-500 hover:text-red-700"
                        >
                            ×
                        </Button>
                    </div>
                    <Button
                        v-if="pollOptions.length < 10"
                        variant="ghost"
                        size="sm"
                        @click="addPollOption"
                        class="text-blue-500 hover:text-blue-700"
                    >
                        + Add option
                    </Button>
                </div>
                
                <!-- Emoji Picker -->
                <div v-if="showEmojiPicker" class="border rounded-lg p-3 bg-gray-50">
                    <div class="grid grid-cols-8 gap-2">
                        <button
                            v-for="emoji in commonEmojis"
                            :key="emoji"
                            @click="insertEmoji(emoji)"
                            class="text-2xl hover:bg-gray-200 rounded p-1 transition-colors"
                        >
                            {{ emoji }}
                        </button>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex space-x-2">
                        <Button variant="ghost" size="sm" @click="triggerFileUpload" :disabled="isUploading">
                            <Upload class="w-4 h-4 mr-1" />
                            {{ isUploading ? 'Uploading...' : 'Photo' }}
                        </Button>
                        
                        <Button variant="ghost" size="sm" @click="showEmojiPicker = !showEmojiPicker">
                            <Smile class="w-4 h-4 mr-1" />
                            Emoji
                        </Button>
                        
                        <Button 
                            variant="ghost" 
                            size="sm"
                            :class="{ 'bg-blue-50 text-blue-600': postType === 'poll' }"
                            @click="togglePoll"
                        >
                            <BarChart3 class="w-4 h-4 mr-1" />
                            {{ postType === 'poll' ? 'Remove Poll' : 'Poll' }}
                        </Button>
                    </div>
                </div>
            </div>
            
            <DialogFooter>
                <Button variant="outline" @click="closeDialog">
                    Cancel
                </Button>
                <Button 
                    @click="submitPost"
                    :disabled="(!postText.trim() && uploadedImages.length === 0) || isPosting"
                    class="bg-blue-600 hover:bg-blue-700 text-white"
                >
                    {{ isPosting ? 'Posting...' : 'Post' }}
                </Button>
            </DialogFooter>
        </DialogContent>
        
        <!-- Hidden file input -->
        <input
            ref="fileInputRef"
            type="file"
            accept="image/*"
            class="hidden"
            @change="onFileSelect"
        />
    </Dialog>
</template> 