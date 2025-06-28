<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {  MoreHorizontal } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import FlashIcon from '@/assets/icons/flash.svg'
import FlashIcon2 from '@/assets/icons/flash-2.svg'
import CommentIcon from '@/assets/icons/comment.svg'
import EyeIcon from '@/assets/icons/eye.svg'
import PinIcon from '@/assets/icons/pin.svg'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import PostDropdownContent from '@/components/PostDropdownContent.vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import { getInitials } from '@/composables/useInitials';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';
import axios from 'axios';
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';
import FeedIcon from '@/assets/icons/feed.svg'
import PaperPlaneIcon from '@/assets/icons/paper-plane.svg'

interface Comment {
    id: number;
    content: string;
    created_at: string;
    user?: {
        id: number;
        firstname: string;
        lastname: string;
        avatar?: string;
        profile_image?: string;
    };
}

interface PollResult {
    option: string;
    votes: number;
    percentage: number;
}

interface FeedPostProps {
    postId: number;
    author: {
        firstname: string;
        lastname: string;
        company?: string;
        avatar?: string;
        profile_image?: string;
        slug: string;
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
    pollResults?: PollResult[];
    userVote?: number | null;
    hasVoted?: boolean;
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
const showDeleteDialog = ref(false);
const isDeletingPost = ref(false);
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
const currentPollResults = ref(props.pollResults || []);
const currentUserVote = ref(props.userVote);
const currentHasVoted = ref(props.hasVoted || false);
const isVoting = ref(false);

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

const handleDeleteRequested = () => {
    showDeleteDialog.value = true;
};

const handleDeleteConfirm = async () => {
    try {
        isDeletingPost.value = true;
        const response = await fetch(`/api/posts/${props.postId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
      
        if (response.ok) {
            emit('post-deleted', props.postId);
            window.location.reload();
        }
    } catch (error) {
        console.error('Error deleting post:', error);
    } finally {
        isDeletingPost.value = false;
        showDeleteDialog.value = false;
    }
};


watch(() => props.isPinned, (newValue) => {
    currentIsPinned.value = newValue || false;
});

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
    
    
    await nextTick();
    
 
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
                threshold: 0.5, 
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

const handlePollOptionClick = async (index: number) => {
    if (isVoting.value || props.type !== 'poll') return;
    
    try {
        isVoting.value = true;
        const response = await axios.post(`/api/posts/${props.postId}/vote`, {
            option_index: index
        });
        
        if (response.data.success) {
            currentPollResults.value = response.data.poll_results;
            currentUserVote.value = response.data.user_vote;
            currentHasVoted.value = response.data.has_voted;
        }
    } catch (error) {
        console.error('Error voting on poll:', error);
    } finally {
        isVoting.value = false;
    }
};
</script>

<template>
    <div ref="postElement">
        <Card class="w-full">
            <CardContent class="p-4">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-start space-x-3">
                      <Link :href="`/profile/${author.slug}`" class="flex items-center space-x-3">
                        <Avatar class="w-12 h-12 rounded-full overflow-hidden hover:brightness-110 transition duration-500">
                            <AvatarImage 
                                v-if="author.profile_image" 
                                :src="author.profile_image" 
                                :alt="author.firstname + ' ' + author.lastname" 
                            />
                            <AvatarFallback v-else class="bg-gray-300 text-gray-700 text-sm font-medium">
                                {{ getInitials(author?.firstname + ' ' + author?.lastname) }}
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold ">{{ author.firstname + ' ' + author.lastname }}</h3>
                                
                            </div>
                            <p class="green-text" v-if="author.company">{{ author.company }}</p>
                            <p class="min-text">{{ timestamp }}</p>
                        </div>
                        </Link>
                    </div>
                   <div class="flex items-center ">
                    <div class="flex items-center gap-2">
                       
                                <PinIcon v-if="currentIsPinned" class="w-4 h-4" />
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm" class="text-gray-400 hover:text-gray-600">
                                <MoreHorizontal class="w-5 h-5" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                             <PostDropdownContent 
                                :post-id="postId"
                                :is-pinned="currentIsPinned"
                                :can-manage="canManage"
                                @pin-toggled="handlePinToggled"
                                @post-deleted="handlePostDeleted"
                                @delete-requested="handleDeleteRequested"
                            />
                        </DropdownMenuContent>
                    </DropdownMenu>
                   </div>

                   
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
                                    class="w-full h-[25rem] object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                />
                            </a>
                        </div>
                    </div>

                    <div v-if="type === 'poll' && pollOptions" class="mt-4 space-y-2">
                        <div 
                            v-for="(option, index) in pollOptions" 
                            :key="index" 
                            @click="!currentHasVoted ? handlePollOptionClick(index) : null" 
                            :class="[
                                'border rounded-lg p-3 transition-all duration-200',
                                currentHasVoted ? 'cursor-default' : 'cursor-pointer hover:bg-gray-50',
                                currentUserVote === index ? 'border-green bg-green/5' : 'border-gray-200',
                                isVoting ? 'opacity-50 pointer-events-none' : ''
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">{{ option }}</span>
                                <span class="text-xs text-gray-500">
                                    {{ currentPollResults[index]?.percentage || 0 }}%
                                    {{ currentPollResults[index]?.votes ? `(${currentPollResults[index].votes})` : '' }}
                                </span>
                            </div>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="h-2 rounded-full transition-all duration-300"
                                    :style="{ 
                                        width: `${currentPollResults[index]?.percentage || 0}%`,
                                        backgroundColor: currentUserVote === index ? 'var(--color-green)' : 'var(--color-green-light-2)'
                                    }"
                                ></div>
                            </div>
                            <div v-if="currentUserVote === index" class="text-xs text-green mt-1 font-medium">
                                Your vote
                            </div>
                        </div>
                        <div v-if="!currentHasVoted" class="text-xs text-gray-500 text-center pt-2">
                            Click an option to vote
                        </div>
                        <div v-else class="text-xs text-gray-500 text-center pt-2">
                            You have voted in this poll
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
                        <FlashIcon v-if="currentIsLiked" class="w-6 h-6" />
                        <FlashIcon2 v-else class="w-6 h-6" />
                        <span class="p-min">{{ currentLikes }}</span>
                    </Button>
                    
                    <Button 
                        variant="ghost" 
                        size="sm" 
                        class="flex items-center space-x-2 text-gray-600"
                        @click="openPostDialog"
                    >
                        <CommentIcon class="w-6 h-6" />
                        <span class="p-min">{{ currentComments }}</span>
                    </Button>
                    
                    <Button 
                        variant="ghost" 
                        size="sm" 
                        class="flex items-center space-x-2 text-gray-600"
                    >
                        <EyeIcon class="w-6 h-6" />
                        <span class="p-min">{{ currentViews }}</span>
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
                                class="w-full border border-gray-200 rounded-lg p-3 text-sm placeholder-gray-500 resize-none focus:outline-none focus:ring-2 focus:ring-green focus:border-transparent"
                                rows="2"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>

  
    <Dialog v-model:open="showPostDialog">
        <DialogContent class="max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
            <DialogHeader>
                <DialogTitle class="flex items-center space-x-3">
              
                        <FeedIcon class="w-6 h-6 text-gray-600" />
                 
                    <div>
                        <h1 class="">Post by {{ author.firstname + ' ' + author.lastname }}</h1>
                    </div>
                </DialogTitle>
            </DialogHeader>
            
            <div class="flex-1 overflow-y-auto">
                <!-- Post Header -->
                <div class="flex items-start space-x-3 mb-4 border-t w-full pt-4 mt-1">
                    <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0">
                        <Avatar class="w-12 h-12 rounded-full overflow-hidden hover:brightness-110 transition duration-500">
                            <AvatarImage 
                                v-if="author.profile_image" 
                                :src="author.profile_image" 
                                :alt="author.firstname + ' ' + author.lastname" 
                            />
                            <AvatarFallback v-else class="bg-gray-300 text-gray-700 text-sm font-medium">
                                {{ getInitials(author?.firstname + ' ' + author?.lastname) }}
                            </AvatarFallback>
                        </Avatar>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 >{{ author.firstname + ' ' + author.lastname }}</h3>
                            
                        </div>
                        <p class="green-text" v-if="author.company">{{ author.company }}</p>
                        <p class="min-text">{{ timestamp }}</p>
                    </div>
                </div>

              
                <div class="mb-6">
                    <p class="text-gray-800 leading-relaxed mb-4">{{ content }}</p>
                   
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
                                    class="w-full h-[25rem] object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="border-t border-gray-200 pt-4">
                   
                    <div v-if="isLoadingComments" class="text-center py-4">
                        <div class="text-sm text-gray-500">Loading comments...</div>
                    </div>
                    
                    <!-- Comments List -->
                    <div v-else-if="postComments.length > 0" class="space-y-4 mb-6">
                        <div v-for="comment in postComments" :key="comment.id" class="flex items-start space-x-3">
                           <Avatar class="w-10 h-10 rounded-full overflow-hidden hover:brightness-110 transition duration-500">
                            <AvatarImage 
                                v-if="comment.user?.profile_image" 
                                :src="comment.user?.profile_image" 
                                :alt="comment.user?.firstname + ' ' + comment.user?.lastname" 
                            />
                            <AvatarFallback v-else class="bg-gray-300 text-gray-700 text-sm font-medium">
                                {{ getInitials(comment.user?.firstname + ' ' + comment.user?.lastname) }}
                            </AvatarFallback>
                        </Avatar>
                            <div class="flex-1">
                                <div class="bg-background rounded-lg p-3">
                                    <h3 class="">{{ comment.user?.firstname + ' ' + comment.user?.lastname || 'Anonymous' }}</h3>
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
                     
                        <div class="flex-1 flex space-x-2 relative">
                         
                            <input type="text" v-model="commentText" class="w-full bg-input rounded-full px-4 py-2 pr-28 text-sm outline-none border border-transparent focus:border-green focus:ring-0"
                                rows="1"
                                @keydown.enter.prevent="submitComment"
                            >
                             <div class="absolute right-5 top-1/2 -translate-y-1/2 flex gap-2">
                                
                                <button @click="submitComment" :disabled="!commentText.trim() || isSubmittingComment" class="hover:opacity-80">
                                    <PaperPlaneIcon class="w-4 h-4" alt="Send Comment" />
                                </button>
                            </div>
                          <!--   <Button 
                                @click="submitComment" 
                                :disabled="!commentText.trim() || isSubmittingComment"
                                size="sm"
                                class="self-end"
                            >
                                {{ isSubmittingComment ? 'Posting...' : 'Post' }}
                            </Button> -->
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
    <ConfirmDeleteDialog
        v-model:open="showDeleteDialog"
        title="Delete Post"
        description="This action cannot be undone. This will permanently delete your post and remove it from our servers."
        confirm-text="Delete Post"
        :is-loading="isDeletingPost"
        @confirm="handleDeleteConfirm"
    />
</template> 