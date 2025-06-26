<template>
  <AppLayout :breadcrumbs="breadcrumbs">
  <div class="min-h-screen bg-bg">
    <!-- Cover Photo Section -->
    <div class="relative h-80">
      <!-- Cover image if exists -->
      <div v-if="user.cover_image" class="absolute inset-0 group">
        <img :src="user.cover_image" alt="Cover" class="w-full h-full object-cover">
        
        <!-- Delete cover photo button - only for own profile -->
        <div v-if="isOwnProfile" class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
          <button 
            @click="handleCoverImageRemove"
            class="p-2 bg-red-500 rounded-full text-white hover:bg-red-600 transition-colors shadow-lg"
            title="Delete cover photo"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>
        </div>
      </div>
      
      <div v-else class="absolute inset-0 flex items-center justify-center bg-green rounded-t-lg">
        <img :src="TalmanLogo" alt="Talman Group Logo" class="w-[10rem]">
      </div>

    
      <div v-if="isOwnProfile" class="absolute bottom-4 right-4">
        <button 
          @click="showCoverUploadModal = true"
          class="bg-white rounded-sm px-3 py-2 flex items-center gap-3  border border-gray-200 text-sm font-semibold font-poppins text-green hover:opacity-90 transition-all duration-300"
        >
          <PictureIcon class="w-5 h-5" />
         
          Change cover photo
        </button>
      </div>
    </div>
    <div class="relative bg-white flex flex-col   pb-6 rounded-b-lg gap-8 | sm:flex-row sm:gap-0 sm:pt-6 ">
      <div>
          <div class="absolute mb-4 bottom-[1rem] left-[1rem]">
                 <div v-if="user.profile_image && !isOwnProfile" class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg">
                   <img 
                     :src="user.profile_image" 
                     :alt="user.firstname + ' ' + user.lastname"
                     class="w-full h-full object-cover"
                   >
                 </div>
                 
                 <div v-else-if="!user.profile_image && !isOwnProfile" class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg">
                   <div class="w-full h-full bg-gray-300 flex items-center justify-center">
                     <span class="text-2xl text-gray-600">{{ getInitials(user.firstname + ' ' + user.lastname) }}</span>
                   </div>
                 </div>
                 
                                <div v-if="isOwnProfile" class="">
                    <div v-if="user.profile_image" class="relative w-36 h-36 rounded-full overflow-hidden border-4 border-white shadow-lg group">
                     <img 
                       :src="user.profile_image" 
                       :alt="user.firstname + ' ' + user.lastname"
                       class="w-full h-full object-cover"
                     >
                     <!-- Overlay controls -->
                     <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                       <div class="flex gap-2 mt-4 w-full items-center justify-center">
                         <button 
                           @click="triggerProfileUpload"
                           class="p-2 bg-white rounded-full text-gray-700 hover:bg-gray-100 transition-colors"
                           title="Change photo"
                         >
                           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                           </svg>
                         </button>
                         <button 
                           @click="handleProfileImageRemove"
                           class="p-2 bg-red-500 rounded-full text-white hover:bg-red-600 transition-colors"
                           title="Delete photo"
                         >
                           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                           </svg>
                         </button>
                       </div>
                     </div>
                   </div>
                   
                   <file-pond
                     v-else
                     ref="profileFilePond"
                     name="profile_image"
                     label-idle="Drop image here or<br/>click to browse.<br/><span class='text-xs'>3MB max size</span>"
                     :allow-multiple="false"
                     accepted-file-types="image/jpeg, image/jpg, image/png"
                     max-file-size="3MB"
                     :server="profileServerConfig"
                     class="profile-filepond"
                     :image-preview-height="128"
                     :image-crop-aspect-ratio="1"
                     image-resize-target-width="400"
                     image-resize-target-height="400"
                     style-panel-layout="compact circle"
                     style-load-indicator-position="center bottom"
                     style-progress-indicator-position="right bottom"
                     style-button-remove-item-position="left bottom"
                     style-button-process-item-position="right bottom"
                   />
                   
                   <input 
                     ref="hiddenFileInput"
                     type="file"
                     accept="image/jpeg,image/jpg,image/png"
                     class="hidden"
                     @change="handleFileChange"
                   />
                 </div>
               </div>
      </div>
     
      <div class="ml-48 flex justify-between w-full mr-4">
        <div>
          <h1>{{ user.firstname + ' ' + user.lastname }}</h1>
          <p class="text-green">{{ user.company || 'Company not specified' }}</p>
        </div>
        <div>
          <Link 
            v-if="isOwnProfile"
            :href="route('edit-profile', user.slug)" 
            class="px-4 py-2 bg-white border border-green font-semibold rounded-sm text-sm text-green hover:bg-gray-50 transition-all duration-300 flex items-center gap-2 font-poppins"
          >
            <UserCardIcon class="w-5 h-5" />
            Edit profile
          </Link>
        </div>
      </div>
      
    </div>

    <!-- Profile Content -->
    <div class=" mx-auto  relative z-10">
      <div class="flex flex-col lg:flex-row gap-4">
        <!-- Left Sidebar -->
        <div class="lg:w-1/3 mt-4">
         

          <AboutSection 
            :company="user.company" 
            :description="user.about" 
            :member-since="user.created_at" 
          />
        </div>

        <!-- Main Content -->
        <div class="lg:w-2/3 mt-[3.7rem]">
          <!-- Post Creation -->
          <div v-if="isOwnProfile" class="mb-6 ">
            <PostCreator @post-created="handleNewPost" />
          </div>

          <!-- Posts Feed -->
          <div v-if="loading" class="space-y-4">
            <div class="animate-pulse bg-gray-200 h-32 rounded-lg"></div>
            <div class="animate-pulse bg-gray-200 h-32 rounded-lg"></div>
            <div class="animate-pulse bg-gray-200 h-32 rounded-lg"></div>
          </div>

          <div v-else class="space-y-4">
            <FeedPost
              v-for="post in userPosts"
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
      </div>
    </div>

    <UploadCoverDialog 
      v-model:open="showCoverUploadModal"
      @upload-complete="handleCoverUploadComplete"
    />
  </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AboutSection from '@/components/AboutSection.vue';
import PostCreator from '@/components/PostCreator.vue';
import FeedPost from '@/components/FeedPost.vue';
import UploadCoverDialog from '@/components/UploadCoverDialog.vue';
import TalmanLogo from '@/assets/images/talman-logo.webp';
import PictureIcon from '@/assets/icons/picture.svg';
import UserCardIcon from '@/assets/icons/user-card.svg';
import type { BreadcrumbItem } from '@/types';
import { Link } from '@inertiajs/vue3';

interface Props {
  user: any;
  posts: any[];
  isOwnProfile: boolean;
}

const props = defineProps<Props>();

console.log('isOwnProfile value:', props.isOwnProfile);
console.log('user', props.user);

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Profile',
    href: `/profile/${props.user.id}`,
  },
];

const showEditModal = ref(false);
const showCoverUploadModal = ref(false);
const loading = ref(false);
const userPosts = ref(props.posts || []);

const form = reactive({
  firstname: props.user?.firstname || '',
  lastname: props.user?.lastname || '',
  email: props.user?.email || '',
  company: props.user?.company || '',
});

const getInitials = (name: string) => {
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2);
};

const updateProfile = () => {
  router.put(route('profile.update'), form, {
    onSuccess: () => {
      showEditModal.value = false;
    },
  });
};

const profileFilePond = ref();
const hiddenFileInput = ref();

const handleProfileUploadResponse = (response: string) => {
  console.log('Upload response:', response);
  const data = JSON.parse(response);
  if (data.success) {
    console.log('Upload successful, reloading...');
    router.reload({ only: ['user'] });
  }
  return response;
};

const handleCoverUploadComplete = () => {
  router.reload({ only: ['user'] });
};

const handleUploadError = (error: any) => {
  console.error('Upload error:', error);
};

const triggerProfileUpload = () => {
  hiddenFileInput.value?.click();
};

const handleFileChange = (event: Event) => {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  
  if (file) {
    uploadProfileImage(file);
  }
};

const getCsrfToken = () => {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
};

const profileServerConfig = computed(() => {
  const token = getCsrfToken();
  console.log('CSRF Token for FilePond:', token); // Debug log
  
  return {
    process: {
      url: '/api/images/upload-profile',
      method: 'POST',
      onload: handleProfileUploadResponse,
      onerror: handleUploadError,
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest'
      }
    }
  };
});

const uploadProfileImage = async (file: File) => {
  try {
    const formData = new FormData();
    formData.append('profile_image', file);
    
    const csrfToken = getCsrfToken();
    console.log('CSRF Token for manual upload:', csrfToken); // Debug log
    
    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }
    
    const headers: Record<string, string> = {
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest'
    };
    
    const response = await fetch('/api/images/upload-profile', {
      method: 'POST',
      headers,
      body: formData
    });
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('Upload error response:', errorText);
      throw new Error(`Upload failed: ${response.status} ${response.statusText}`);
    }
    
    const data = await response.json();
    if (data.success) {
      router.reload({ only: ['user'] });
    } else {
      throw new Error(data.message || 'Upload failed');
    }
  } catch (error) {
    console.error('Error uploading profile image:', error);
    alert('Failed to upload profile image. Please try again.');
  }
};

const handleProfileImageRemove = async () => {
  try {
    const csrfToken = getCsrfToken();
    console.log('CSRF Token for profile delete:', csrfToken); // Debug log
    
    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }
    
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest'
    };
    
    const response = await fetch('/api/images/delete-profile', {
      method: 'DELETE',
      headers
    });
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('Delete error response:', errorText);
      throw new Error(`Delete failed: ${response.status} ${response.statusText}`);
    }
    
    router.reload({ only: ['user'] });
  } catch (error) {
    console.error('Error deleting profile image:', error);
    alert('Failed to delete profile image. Please try again.');
  }
};

const handleCoverImageRemove = async () => {
  try {
    const csrfToken = getCsrfToken();
    console.log('CSRF Token for cover delete:', csrfToken); // Debug log
    
    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }
    
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest'
    };
    
    const response = await fetch('/api/images/delete-cover', {
      method: 'DELETE',
      headers
    });
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('Delete error response:', errorText);
      throw new Error(`Delete failed: ${response.status} ${response.statusText}`);
    }
    
    router.reload({ only: ['user'] });
  } catch (error) {
    console.error('Error deleting cover image:', error);
    alert('Failed to delete cover image. Please try again.');
  }
};

const handleNewPost = (post: any) => {
  userPosts.value.unshift(post);
};

const handlePostDeleted = (postId: number) => {
  userPosts.value = userPosts.value.filter((post: any) => post.id !== postId);
};

const handlePostPinned = (postId: number, isPinned: boolean) => {
  const post = userPosts.value.find((p: any) => p.id === postId);
  if (post) {
    post.isPinned = isPinned;
  }
};
</script>

