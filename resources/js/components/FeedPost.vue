<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Heart, MessageCircle, Eye, MoreHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';
import axios from 'axios';

interface FeedPostProps {
    postId: number;
    author: {
        name: string;
        company?: string;
        avatar?: string;
    };
    content: string;
    timestamp: string;
    likes: number;
    comments: number;
    views: number;
    isLiked?: boolean;
    type?: 'regular' | 'poll';
    pollOptions?: string[];
    images?: {
        id: number;
        url: string;
        filename: string;
        original_filename: string;
        width: number;
        height: number;
        optimizations?: Record<string, any>;
    }[];

    
}



const props = defineProps<FeedPostProps>();

const commentText = ref('');
const showComments = ref(false);
const currentLikes = ref(props.likes);
const currentIsLiked = ref(props.isLiked || false);
const isLiking = ref(false);

const toggleLike = async () => {
    if (isLiking.value) return;
    
    try {
        isLiking.value = true;
        const response = await axios.post(`/api/posts/${props.postId}/like`);
        
        currentLikes.value = response.data.likes;
        currentIsLiked.value = response.data.isLiked;
    } catch (error) {
        console.error('Error toggling like:', error);
    } finally {
        isLiking.value = false;
    }
};

const openImageModal = (image: any) => {
    window.open(image.url, '_blank');
};
</script>

<template>
    <Card class="w-full">
        <CardContent class="p-4">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-start space-x-3">
                    <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ author.name }}</h3>
                        <p class="text-sm text-gray-500" v-if="author.company">{{ author.company }}</p>
                        <p class="text-sm text-gray-500">{{ timestamp }}</p>
                    </div>
                </div>
                
                <Button variant="ghost" size="sm" class="text-gray-400 hover:text-gray-600">
                    <MoreHorizontal class="w-5 h-5" />
                </Button>
            </div>
            
            <div class="mb-4">
                <p class="text-gray-800 leading-relaxed">{{ content }}</p>
                
                <!-- Images -->
                <div v-if="images && images.length > 0" class="mt-4">
                    <div class="grid gap-2" :class="{
                        'grid-cols-1': images.length === 1,
                        'grid-cols-2': images.length === 2,
                        'grid-cols-2 md:grid-cols-3': images.length >= 3
                    }">
                        <div v-for="image in images" :key="image.id" class="relative group">
                            <img 
                                :src="image.optimizations?.medium?.url || image.url"
                                :alt="image.original_filename"
                                class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                @click="openImageModal(image)"
                            />
                        </div>
                    </div>
                </div>
                
                <div v-if="type === 'poll' && pollOptions" class="mt-4 space-y-2">
                    <div v-for="(option, index) in pollOptions" :key="index" class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-pointer">
                        <div class="flex items-center justify-between">
                            <span class="text-sm">{{ option }}</span>
                            <span class="text-xs text-gray-500">0%</span>
                        </div>
                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center space-x-6 pt-3 border-t border-gray-100">
                <Button 
                    variant="ghost" 
                    size="sm" 
                    class="flex items-center space-x-2 text-gray-600 hover:text-red-600"
                    @click="toggleLike"
                    :disabled="isLiking"
                >
                    <Heart class="w-5 h-5" :class="{ 'fill-red-500 text-red-500': currentIsLiked }" />
                    <span>{{ currentLikes }}</span>
                </Button>
                
                <Button 
                    variant="ghost" 
                    size="sm" 
                    class="flex items-center space-x-2 text-gray-600 hover:text-blue-600"
                    @click="showComments = !showComments"
                >
                    <MessageCircle class="w-5 h-5" />
                    <span>{{ comments }}</span>
                </Button>
                
                <Button variant="ghost" size="sm" class="flex items-center space-x-2 text-gray-600">
                    <Eye class="w-5 h-5" />
                    <span>{{ views }}</span>
                </Button>
            </div>
            
            <div v-if="showComments" class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <textarea
                            v-model="commentText"
                            placeholder="Write your comment..."
                            class="w-full border border-gray-200 rounded-lg p-3 text-sm placeholder-gray-500 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            rows="2"
                        ></textarea>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template> 