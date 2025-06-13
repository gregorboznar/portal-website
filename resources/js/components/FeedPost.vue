<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Heart, MessageCircle, Eye, MoreHorizontal } from 'lucide-vue-next';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import PostDropdownContent from '@/components/PostDropdownContent.vue';

import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';

interface Comment {
    id: number;
    content: string;
    created_at: string;
    user?: {
        id: number;
        name: string;
        avatar?: string;
    };
}

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
    isPinned?: boolean;
    canManage?: boolean;
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
const showPostDialog = ref(false);
const currentLikes = ref(props.likes);
const currentIsLiked = ref(props.isLiked || false);
const isLiking = ref(false);
const galleryId = `gallery-${props.postId}`;
const dialogGalleryId = `dialog-gallery-${props.postId}`;
const postComments = ref<Comment[]>([]);
const isLoadingComments = ref(false);
const isSubmittingComment = ref(false);
const postElement = ref<HTMLElement>();
const hasBeenViewed = ref(false);
const currentViews = ref(props.views);
const currentIsPinned = ref(props.isPinned || false);

const emit = defineEmits<{
    'post-deleted': [postId: number];
    'post-pinned': [postId: number, isPinned: boolean];
}>();

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

const trackView = async () => {
    if (hasBeenViewed.value) return;
    
    try {
        hasBeenViewed.value = true;
        const response = await axios.post(`/api/posts/${props.postId}/view`);
        currentViews.value = response.data.views_count;
    } catch (error) {
        console.error('Error tracking view:', error);
        hasBeenViewed.value = false;
    }
};

const openPostDialog = async () => {
    showPostDialog.value = true;
    await loadComments();
    
    // Initialize PhotoSwipe for dialog images
    setTimeout(() => {
        if (props.images && props.images.length > 0) {
            initDialogPhotoSwipe();
        }
    }, 100);
};

const loadComments = async () => {
    if (isLoadingComments.value) return;
    
    try {
        isLoadingComments.value = true;
        const response = await axios.get(`/api/posts/${props.postId}/comments`);
        postComments.value = response.data.data || [];
    } catch (error) {
        console.error('Error loading comments:', error);
        postComments.value = [];
    } finally {
        isLoadingComments.value = false;
    }
};

const currentComments = ref(props.comments)

const submitComment = async () => {
    if (!commentText.value.trim() || isSubmittingComment.value) return;
    
    try {
        isSubmittingComment.value = true;
        const response = await axios.post(`/api/posts/${props.postId}/comments`, {
            content: commentText.value.trim()
        });
        
        postComments.value.push(response.data.data);
        commentText.value = '';

        currentComments.value++;
    } catch (error) {
        console.error('Error submitting comment:', error);
    } finally {
        isSubmittingComment.value = false;
    }
};

const handlePinToggled = (postId: number, isPinned: boolean) => {
    currentIsPinned.value = isPinned;
    emit('post-pinned', postId, isPinned);
};

const handlePostDeleted = (postId: number) => {
    emit('post-deleted', postId);
};

const initPhotoSwipe = () => {
    const lightbox = new PhotoSwipeLightbox({
        gallery: `#${galleryId}`,
        children: 'a',
        pswpModule: () => import('photoswipe')
    });
    lightbox.init();
};

const initDialogPhotoSwipe = () => {
    const lightbox = new PhotoSwipeLightbox({
        gallery: `#${dialogGalleryId}`,
        children: 'a',
        pswpModule: () => import('photoswipe')
    });
    lightbox.init();
};

let observer: IntersectionObserver | null = null;

onMounted(async () => {
    if (props.images && props.images.length > 0) {
        initPhotoSwipe();
    }
    
    // Wait for template to be fully rendered
    await nextTick();
    
    // Set up intersection observer for view tracking
    if (postElement.value) {
        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
                        trackView();
                    }
                });
            },
            {
                threshold: 0.5, // Trigger when 50% of the post is visible
                rootMargin: '0px'
            }
        );
        
        observer.observe(postElement.value);
    }
});

onUnmounted(() => {
    if (observer && postElement.value) {
        observer.unobserve(postElement.value);
        observer.disconnect();
    }
});
</script>

<template>
    <div ref="postElement">
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
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-900">{{ author.name }}</h3>
                                <span v-if="currentIsPinned" class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">
                                    📌 Pinned
                                </span>
                            </div>
                            <p class="text-sm text-gray-500" v-if="author.company">{{ author.company }}</p>
                            <p class="text-sm text-gray-500">{{ timestamp }}</p>
                        </div>
                    </div>
                    
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm" class="text-gray-400 hover:text-gray-600">
                                <MoreHorizontal class="w-5 h-5" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent>
                             <PostDropdownContent 
                                :post-id="postId"
                                :is-pinned="currentIsPinned"
                                :can-manage="canManage"
                                @pin-toggled="handlePinToggled"
                                @post-deleted="handlePostDeleted"
                            />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                
                <div class="mb-4">
                    <p class="text-gray-800 leading-relaxed">{{ content }}</p>
                    
                    <!-- Images -->
                    <div v-if="images && images.length > 0" class="mt-4" :id="galleryId">
                        <div class="grid gap-2" :class="{
                            'grid-cols-1': images.length === 1,
                            'grid-cols-2': images.length === 2,
                            'grid-cols-2 md:grid-cols-3': images.length >= 3
                        }">
                            <a 
                                v-for="image in images" 
                                :key="image.id" 
                                :href="image.url"
                                :data-pswp-width="image.width"
                                :data-pswp-height="image.height"
                                class="relative group block"
                            >
                                <img 
                                    :src="image.optimizations?.medium?.url || image.url"
                                    :alt="image.original_filename"
                                    class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                />
                            </a>
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
                        @click="openPostDialog"
                    >
                        <MessageCircle class="w-5 h-5" />
                        <span>{{ currentComments }}</span>
                    </Button>
                    
                    <Button variant="ghost" size="sm" class="flex items-center space-x-2 text-gray-600">
                        <Eye class="w-5 h-5" />
                        <span>{{ currentViews }}</span>
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
    </div>

    <!-- Post Detail Dialog -->
    <Dialog v-model:open="showPostDialog">
        <DialogContent class="max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
            <DialogHeader>
                <DialogTitle class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">Post by {{ author.name }}</div>
                        <div class="text-sm text-gray-500" v-if="author.company">{{ author.company }}</div>
                    </div>
                </DialogTitle>
            </DialogHeader>
            
            <div class="flex-1 overflow-y-auto">
                <!-- Post Header -->
                <div class="flex items-start space-x-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-gray-900">{{ author.name }}</h3>
                            <span v-if="currentIsPinned" class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">
                                📌 Pinned
                            </span>
                        </div>
                        <p class="text-sm text-gray-500" v-if="author.company">{{ author.company }}</p>
                        <p class="text-sm text-gray-500">{{ timestamp }}</p>
                    </div>
                </div>

                <!-- Post Content -->
                <div class="mb-6">
                    <p class="text-gray-800 leading-relaxed mb-4">{{ content }}</p>
                    
                    <!-- Images in Dialog -->
                    <div v-if="images && images.length > 0" class="mb-4" :id="dialogGalleryId">
                        <div class="grid gap-2" :class="{
                            'grid-cols-1': images.length === 1,
                            'grid-cols-2': images.length === 2,
                            'grid-cols-2 md:grid-cols-3': images.length >= 3
                        }">
                            <a 
                                v-for="image in images" 
                                :key="image.id" 
                                :href="image.url"
                                :data-pswp-width="image.width"
                                :data-pswp-height="image.height"
                                class="relative group block"
                            >
                                <img 
                                    :src="image.optimizations?.medium?.url || image.url"
                                    :alt="image.original_filename"
                                    class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="border-t border-gray-200 pt-4">
                    <h4 class="font-semibold text-gray-900 mb-4">Comments</h4>
                    
                    <!-- Loading Comments -->
                    <div v-if="isLoadingComments" class="text-center py-4">
                        <div class="text-sm text-gray-500">Loading comments...</div>
                    </div>
                    
                    <!-- Comments List -->
                    <div v-else-if="postComments.length > 0" class="space-y-4 mb-6">
                        <div v-for="comment in postComments" :key="comment.id" class="flex items-start space-x-3">
                            <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="font-semibold text-sm text-gray-900">{{ comment.user?.name || 'Anonymous' }}</div>
                                    <div class="text-sm text-gray-800 mt-1">{{ comment.content }}</div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ comment.created_at }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- No Comments -->
                    <div v-else class="text-center py-4 mb-6">
                        <div class="text-sm text-gray-500">No comments yet. Be the first to comment!</div>
                    </div>
                    
                    <!-- Add Comment -->
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1 flex space-x-2">
                            <textarea
                                v-model="commentText"
                                placeholder="Write your comment..."
                                class="flex-1 border border-gray-200 rounded-lg p-3 text-sm placeholder-gray-500 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                rows="2"
                                @keydown.enter.prevent="submitComment"
                            ></textarea>
                            <Button 
                                @click="submitComment" 
                                :disabled="!commentText.trim() || isSubmittingComment"
                                size="sm"
                                class="self-end"
                            >
                                {{ isSubmittingComment ? 'Posting...' : 'Post' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template> 