<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Calendar, Camera, BarChart3 } from 'lucide-vue-next';
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

const emit = defineEmits<{
    'post-created': [post: Post]
}>();

const postText = ref('');
const isPosting = ref(false);
const postType = ref<'regular' | 'poll'>('regular');
const pollOptions = ref<string[]>(['', '']);

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

const submitPost = async () => {
    if (!postText.value.trim()) return;
    
    try {
        isPosting.value = true;
        
        const payload: any = {
            content: postText.value,
            type: postType.value,
        };
        
        if (postType.value === 'poll') {
            payload.poll_options = pollOptions.value.filter(option => option.trim());
        }
        
        const response = await axios.post('/api/posts', payload);
        
        emit('post-created', response.data);
        
        postText.value = '';
        postType.value = 'regular';
        pollOptions.value = ['', ''];
        
    } catch (error) {
        console.error('Error creating post:', error);
    } finally {
        isPosting.value = false;
    }
};
</script>

<template>
    <Card class="w-full">
        <CardContent class="p-4">
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
                        class="w-full border-none outline-none resize-none text-lg placeholder-gray-500 bg-transparent"
                        rows="3"
                    ></textarea>
                    
                    <div v-if="postType === 'poll'" class="mt-4 space-y-2">
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
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex space-x-4">
                        <Button variant="ghost" class="flex items-center space-x-2 text-gray-600 hover:bg-gray-50">
                            <Camera class="w-5 h-5 text-green-600" />
                            <span>Add Photo</span>
                        </Button>
                        
                        <Button variant="ghost" class="flex items-center space-x-2 text-gray-600 hover:bg-gray-50">
                            <Calendar class="w-5 h-5 text-blue-600" />
                            <span>Event</span>
                        </Button>
                        
                        <Button 
                            variant="ghost" 
                            class="flex items-center space-x-2 text-gray-600 hover:bg-gray-50"
                            :class="{ 'bg-blue-50 text-blue-600': postType === 'poll' }"
                            @click="togglePoll"
                        >
                            <BarChart3 class="w-5 h-5 text-green-600" />
                            <span>{{ postType === 'poll' ? 'Remove Poll' : 'Add Poll' }}</span>
                        </Button>
                    </div>
                    
                    <Button 
                        @click="submitPost"
                        :disabled="!postText.trim() || isPosting"
                        class="bg-blue-600 hover:bg-blue-700 text-white"
                    >
                        {{ isPosting ? 'Posting...' : 'Post' }}
                    </Button>
                </div>
            </div>
        </CardContent>
    </Card>
</template> 