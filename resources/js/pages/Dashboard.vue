<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PostCreator from '@/components/PostCreator.vue';
import FeedPost from '@/components/FeedPost.vue';
import Event from '@/components/Event.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import FeedIcon from '@/assets/icons/feed.svg'

interface PostAuthor {
    name: string;
    company: string;
}

interface Post {
    id: number;
    author: PostAuthor;
    content: string;
    type: 'regular' | 'poll';
    poll_options?: string[];
    timestamp: string;
    likes: number;
    comments: number;
    views: number;
    isLiked: boolean;
    isPinned?: boolean;
    canManage?: boolean;
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const posts = ref<Post[]>([]);
const loading = ref(true);

const fetchPosts = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/posts');
        posts.value = response.data;
    } catch (error) {
        console.error('Error fetching posts:', error);
    } finally {
        loading.value = false;
    }
};

const handleNewPost = (newPost: Post) => {
    posts.value.unshift(newPost);
};

const handlePostDeleted = (postId: number) => {
    const index = posts.value.findIndex(post => post.id === postId);
    if (index !== -1) {
        posts.value.splice(index, 1);
    }
};

const handlePostPinned = (postId: number, isPinned: boolean) => {
    const post = posts.value.find(post => post.id === postId);
    if (post) {
        post.isPinned = isPinned;
        // Re-sort posts to move pinned posts to top
        fetchPosts();
    }
};

onMounted(() => {
    fetchPosts();
});
</script>

<template>
    <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
  <div class="flex h-full flex-1 gap-4 w-full bg-bg">
    
    
    <div class="flex flex-col gap-4 flex-1">
    <div class="flex  gap-2 ml-3">
      <FeedIcon class="w-5 h-5 relative top-1" />

      <div class="flex items-left flex-col">
        <h1 class="text-xl font-semibold text-left">Social Feed</h1>
        <p class="p-s ">Stay Connected and Informed</p>
      </div>
    </div>
     
      <PostCreator @post-created="handleNewPost" />

      <div v-if="loading" class="space-y-6">
        <div class="animate-pulse bg-gray-200 h-32 rounded-lg"></div>
        <div class="animate-pulse bg-gray-200 h-32 rounded-lg"></div>
        <div class="animate-pulse bg-gray-200 h-32 rounded-lg"></div>
      </div>

      <div v-else class="space-y-6">
        <FeedPost
          v-for="post in posts"
          :key="post.id"
          :post-id="post.id"
          :author="post.author"
          :content="post.content"
          :timestamp="post.timestamp"
          :likes="post.likes"
          :comments="post.comments"
          :views="post.views"
          :is-liked="post.isLiked"
          :is-pinned="post.isPinned"
          :can-manage="post.canManage"
          :type="post.type"
          :poll-options="post.poll_options"
          :images="post.images"
          @post-deleted="handlePostDeleted"
          @post-pinned="handlePostPinned"
        />
      </div>
    </div>

    <div>
<Event />
    </div>
    
  </div>
</AppLayout>

</template>
