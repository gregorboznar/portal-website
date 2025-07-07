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
import { usePresence } from '@/composables/usePresence';
import SimplePresenceTest from '@/components/SimplePresenceTest.vue';

interface PostAuthor {
    name: string;
    company: string;
}

interface PollResult {
    option: string;
    votes: number;
    percentage: number;
}

interface Event {
    id: number;
    title: string;
    short_description: string;
    description: string;
    date: string;
    end_date: string;
    location: string;
    status: string;
}

interface Post {
    id: number;
    author: PostAuthor;
    content: string;
    type: 'regular' | 'poll';
    poll_options?: string[];
    poll_results?: PollResult[];
    user_vote?: number | null;
    has_voted?: boolean;
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
const events = ref<Event[]>([]);
const { onlineUsers, connectionStatus, resetPresence, initializePresence } = usePresence();

const debugDashboard = () => {
    console.log('Dashboard: onlineUsers:', onlineUsers.value);
    console.log('Dashboard: connectionStatus:', connectionStatus.value);
};

const resetAndReinitialize = () => {
    console.log('Dashboard: Resetting and reinitializing presence...');
    resetPresence();
    setTimeout(() => {
        initializePresence();
    }, 1000);
};
const fetchPosts = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/posts');
        posts.value = response.data.posts;  
        events.value = response.data.events; 
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
    const postIndex = posts.value.findIndex(post => post.id === postId);
    if (postIndex !== -1) {
      
        const updatedPost = { ...posts.value[postIndex], isPinned };
        posts.value[postIndex] = updatedPost;

        posts.value.sort((a, b) => {
       
            if (a.isPinned && !b.isPinned) return -1;
            if (!a.isPinned && b.isPinned) return 1;
            
          
            return b.id - a.id;
        });
    }
};

onMounted(() => {
    fetchPosts();
});
</script>

<template>
    <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">

  <div class="mb-4">
    <SimplePresenceTest />
  </div>
  
  <div class="flex h-full flex-1 gap-4 w-full bg-bg">
    <div class="flex flex-col gap-4 flex-1">
    <div class="flex justify-between items-start ml-3">
      <div class="flex gap-2">
        <FeedIcon class="w-5 h-5 relative top-1" />
        <div class="flex items-left flex-col">
          <h1 class="text-xl font-semibold text-left">Social Feed</h1>
          <p class="p-s ">Stay Connected and Informed</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <div class="text-sm text-gray-600 px-3 py-1 rounded-full border" 
             :class="connectionStatus.isConnected ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
          <span class="inline-block w-2 h-2 rounded-full mr-2" 
                :class="connectionStatus.isConnected ? 'bg-green-500' : 'bg-red-500'"></span>
          {{ onlineUsers.length }} online 
          <span class="text-xs ml-1">
            ({{ connectionStatus.isConnected ? 'Connected' : 'Disconnected' }})
          </span>
        </div>
        <button @click="debugDashboard" class="px-2 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600">
          Debug
        </button>
        <button @click="resetAndReinitialize" class="px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">
          Reset
        </button>
      </div>
    </div>
     
      <PostCreator @post-created="handleNewPost" />

      <div v-if="loading" class="space-y-4">
        <div class="animate-pulse bg-gray-200 h-32 rounded-lg"></div>
        <div class="animate-pulse bg-gray-200 h-32 rounded-lg"></div>
        <div class="animate-pulse bg-gray-200 h-32 rounded-lg"></div>
      </div>

      <div v-else class="space-y-4">
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
          :poll-results="post.poll_results"
          :user-vote="post.user_vote"
          :has-voted="post.has_voted"
          :images="post.images"
          @post-deleted="handlePostDeleted"
          @post-pinned="handlePostPinned"
        />
      </div>
    </div>

   <div>
        <Event :events="events" />
    </div>
    
  </div>
</AppLayout>

</template>
