<template>
  <AppLayout :breadcrumbs="breadcrumbs">
  <div class="min-h-screen bg-bg">
    <!-- Cover Photo Section -->
    <div class="relative h-80 ">
      <!-- Cover Image -->
      <div v-if="user.cover_image" class="absolute inset-0">
        <img :src="user.cover_image" alt="Cover" class="w-full h-full object-cover">
      </div>
      
      <!-- Logo overlay -->
      <div class="absolute inset-0 flex items-center justify-center bg-green">
       
   
          <img :src="talmanLogo" alt="Talman Group Logo" class="w-[10rem]">
     
      </div>

             <!-- Change Cover Photo Button -->
       <div v-if="isOwnProfile" class="absolute top-4 right-4">
         <file-pond
           ref="coverFilePond"
           name="cover_image"
           label-idle="🖼️ Change cover photo"
           :allow-multiple="false"
           accepted-file-types="image/jpeg, image/jpg, image/png"
           max-file-size="10MB"
           :server="coverServerConfig"
           class="cover-filepond"
           :image-preview-height="60"
           image-resize-target-width="1200"
           image-resize-target-height="400"
           style-button-remove-item-position="center bottom"
           style="height: 40px; width: 200px;"
         />
       </div>
    </div>
    <div class="relative bg-white flex flex-col  pt-4 rounded-b-lg gap-8 | sm:flex-row sm:gap-0 sm:pt-6 ">
      <div>
          <div class="absolute mb-4 bottom-[2rem] left-[1rem]">
                 <div v-if="user.profile_image && !isOwnProfile" class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg">
                   <img 
                     :src="user.profile_image" 
                     :alt="user.name"
                     class="w-full h-full object-cover"
                   >
                 </div>
                 
                 <div v-else-if="!user.profile_image && !isOwnProfile" class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg">
                   <div class="w-full h-full bg-gray-300 flex items-center justify-center">
                     <span class="text-2xl text-gray-600">{{ getInitials(user.name) }}</span>
                   </div>
                 </div>
                 
                 <!-- Profile Photo Upload with FilePond for own profile -->
                 <div v-if="isOwnProfile" class="">
                   <!-- Show existing profile image with overlay controls if exists -->
                   <div v-if="user.profile_image" class="relative w-36 h-36 rounded-full overflow-hidden border-4 border-white shadow-lg group">
                     <img 
                       :src="user.profile_image" 
                       :alt="user.name"
                       class="w-full h-full object-cover"
                     >
                     <!-- Overlay controls -->
                     <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                       <div class="flex gap-2 mt-4 w-full">
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
                   
                   <!-- FilePond for upload when no image exists -->
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
                   
                   <!-- Hidden file input for changing existing photo -->
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
     
      <div class="ml-48 flex justify-between w-full">
         <div>
             <h2 class="text-xl font-bold text-gray-900 mb-1">{{ user.name }}</h2>
              <p class="text-gray-600 mb-2">{{ user.company || 'Company not specified' }}</p>
              <p class="text-sm text-gray-500 mb-4">AVAILABLE TICKETS: 0 / 0</p>
         </div> 
          <div>
            <button 
                v-if="isOwnProfile"
                @click="showEditModal = true"
                class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit profile
              </button>
      </div>
      </div>
      
    </div>

    <!-- Profile Content -->
    <div class="max-w-6xl mx-auto px-4 relative z-10">
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Left Sidebar -->
        <div class="lg:w-1/3">
         

          <AboutSection 
  :company="user.company" 
  :description="user.description" 
  :member-since="user.memberSince" 
/>
        </div>

        <!-- Main Content -->
        <div class="lg:w-2/3">
          <!-- Post Creation -->
          <div v-if="isOwnProfile" class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 rounded-full overflow-hidden">
                <img 
                  v-if="user.profile_image" 
                  :src="user.profile_image" 
                  :alt="user.name"
                  class="w-full h-full object-cover"
                >
                <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center">
                  <span class="text-sm text-gray-600">{{ getInitials(user.name) }}</span>
                </div>
              </div>
              <div class="flex-1">
                <textarea 
                  v-model="newPost" 
                  placeholder="What are you thinking?"
                  class="w-full border-none resize-none focus:outline-none text-gray-700"
                  rows="3"
                ></textarea>
                
                <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-200">
                  <div class="flex items-center gap-4">
                    <button class="flex items-center gap-2 text-gray-600 hover:text-green-600">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                      Add Photo
                    </button>
                    <button class="flex items-center gap-2 text-gray-600 hover:text-green-600">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                      Event
                    </button>
                    <button class="flex items-center gap-2 text-gray-600 hover:text-green-600">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                      </svg>
                      Add Poll
                    </button>
                  </div>
                  <button 
                    @click="submitPost"
                    :disabled="!newPost.trim()"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Post
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Sample Post -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-start gap-3 mb-4">
              <div class="w-10 h-10 rounded-full overflow-hidden">
                <img 
                  v-if="user.profile_image" 
                  :src="user.profile_image" 
                  :alt="user.name"
                  class="w-full h-full object-cover"
                >
                <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center">
                  <span class="text-sm text-gray-600">{{ getInitials(user.name) }}</span>
                </div>
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <h4 class="font-semibold text-gray-900">{{ user.name }}</h4>
                  <span class="text-sm text-gray-500">{{ user.company || 'Bplanet d.o.o.' }}</span>
                </div>
                <p class="text-sm text-gray-500 mb-3">10 June at 12:15 AM ✓</p>
                <p class="text-gray-700">slika</p>
              </div>
              <button class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Profile Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Edit Profile</h3>
        <form @submit.prevent="updateProfile" class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input 
              id="name" 
              type="text" 
              v-model="form.name" 
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            />
          </div>
          
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input 
              id="email" 
              type="email" 
              v-model="form.email" 
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              disabled
            />
          </div>

          <div>
            <label for="company" class="block text-sm font-medium text-gray-700">Company</label>
            <input 
              id="company" 
              type="text" 
              v-model="form.company" 
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            />
          </div>
          
          <div class="flex justify-end gap-3">
            <button 
              type="button"
              @click="showEditModal = false"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-700"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AboutSection from '@/components/AboutSection.vue';
import talmanLogo from '@/assets/images/talman-logo.webp';
import type { BreadcrumbItem } from '@/types';
interface Props {
  user: any;
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
const newPost = ref('');

const form = reactive({
  name: props.user?.name || '',
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

const coverFilePond = ref();
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

const handleCoverUploadResponse = (response: string) => {
  const data = JSON.parse(response);
  if (data.success) {
    router.reload({ only: ['user'] });
  }
  return response;
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

const uploadProfileImage = async (file: File) => {
  try {
    const formData = new FormData();
    formData.append('profile_image', file);
    
    const csrfToken = getCsrfToken();
    const headers: Record<string, string> = {};
    
    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken;
    }
    
    const response = await fetch('/api/images/upload-profile', {
      method: 'POST',
      headers,
      body: formData
    });
    
    const data = await response.json();
    if (data.success) {
      router.reload({ only: ['user'] });
    }
  } catch (error) {
    console.error('Error uploading profile image:', error);
  }
};

const handleProfileImageRemove = async () => {
  try {
    const csrfToken = getCsrfToken();
    const headers: Record<string, string> = {
      'Content-Type': 'application/json'
    };
    
    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken;
    }
    
    const response = await fetch('/api/images/delete-profile', {
      method: 'DELETE',
      headers
    });
    
    if (response.ok) {
      router.reload({ only: ['user'] });
    }
  } catch (error) {
    console.error('Error deleting profile image:', error);
  }
};

const getCsrfToken = () => {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
};

const profileServerConfig = computed(() => ({
  process: {
    url: '/api/images/upload-profile',
    method: 'POST',
    onload: handleProfileUploadResponse,
    onerror: handleUploadError,
    headers: {
      'X-CSRF-TOKEN': getCsrfToken()
    }
  }
}));

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

const submitPost = () => {
  if (newPost.value.trim()) {
    newPost.value = '';
  }
};
</script>

<style>
.filepond--credits {
  display: none !important;
}

/* Cover photo FilePond styling */
.cover-filepond .filepond--root {
  background: rgba(0, 0, 0, 0.6);
  border-radius: 8px;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.cover-filepond .filepond--drop-label {
  color: white;
  font-size: 14px;
  font-weight: 500;
}

.cover-filepond .filepond--panel-root {
  background: rgba(0, 0, 0, 0.6);
  border-radius: 8px;
}

/* Profile photo FilePond styling */
.profile-filepond .filepond--root {
  width: 128px !important;
  height: 128px !important;
  border-radius: 50% !important;
  border: 4px solid white !important;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
  background: #f3f4f6;
}

.profile-filepond .filepond--panel-root {
  border-radius: 50% !important;
  background: #f3f4f6;
  border: none;
}

.profile-filepond .filepond--drop-label {
  color: #6b7280;
  font-size: 12px;
  text-align: center;
  cursor: pointer;
  padding: 1rem;
}

.profile-filepond .filepond--drip {
  display: none;
}

.profile-filepond .filepond--item {
  border-radius: 50%;
}

.profile-filepond .filepond--item-panel {
  border-radius: 50% !important;
}

.profile-filepond .filepond--image-preview {
  border-radius: 50% !important;
}

.profile-filepond .filepond--image-preview-wrapper {
  border-radius: 50% !important;
}

.profile-filepond .filepond--image-preview-overlay {
  border-radius: 50% !important;
}
</style>