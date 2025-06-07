<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PostCreator from '@/components/PostCreator.vue';
import FeedPost from '@/components/FeedPost.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';

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

onMounted(() => {
    fetchPosts();
});
</script>

<template>
    <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
  <div class="flex h-full flex-1 gap-6 rounded-xl p-4 w-full">
    
    <div class="flex flex-col gap-6 flex-1">
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
          :type="post.type"
          :poll-options="post.poll_options"
        />
      </div>
    </div>

    
    <div class="w-72 flex-shrink-0 hidden xl:block">
      <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold mb-4">Upcoming Events</h3>
        <p class="text-gray-500 text-sm">No upcoming events</p>
      </div>
    </div>
  </div>
</AppLayout>

</template>
