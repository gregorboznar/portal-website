<template>
  <AppLayout :breadcrumbs="breadcrumbs">
 
    <form @submit.prevent="handleSubmit" class="space-y-3" autocomplete="off">
      <!-- Header Section -->
      <div class="flex justify-between items-center mb-4">
        <div class="flex flex-col w-full justify-between gap-2 sm:items-center sm:flex-row sm:gap-0">
          <div class="pl-3">
            <div class="flex items-center gap-3">
              <UserCardIcon class="w-5 h-5" />
              <h1>Profile settings</h1>
            </div>
            <Link 
              :href="route('profile', user.slug)" 
              class="flex items-center gap-2 text-green hover:text-green/70 transition-colors duration-300 text-sm underline ml-2 cursor-pointer"
            >
              <LeftArrowIcon class="w-4 h-4" />
              Back to your profile
            </Link>
          </div>

          <div class="flex flex-col gap-2 w-full sm:gap-4 sm:flex-row sm:justify-end sm:w-auto">
            <button 
              type="button" 
              @click="showDeleteModal = true"
              v-if="hasPermission && user.role !== 'god'"
              class="md:flex hidden w-full h-9 bg-white text-black py-2 rounded-md font-semibold border border-reddish-pink transition-colors duration-200 ease-in-out justify-center items-center text-sm sm:w-[10rem]"
            >
              <DeleteIcon class="w-[0.8rem] h-[0.8rem] mr-2" />
              Delete
            </button>
            <button 
              type="submit" 
              :disabled="loading"
              class="flex w-full h-9 bg-green text-white py-2 rounded-md font-semibold hover:bg-lightgreen transition-colors duration-200 ease-in-out justify-center items-center text-sm sm:w-[10rem] disabled:opacity-50"
            >
              {{ loading ? 'Saving...' : 'Save changes' }}
            </button>
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
        <!-- Left Column -->
        <div class="flex-[1.5]">
          <!-- Profile Photo Section -->
          <div class="flex flex-col">
            <div class="bg-white p-8 rounded-lg">
              <div class="flex justify-between items-center w-full mb-4">
                <p class="text-sm font-poppins leading-normal text-black">Profile photo</p>
              </div>
              <div class="flex justify-center items-center">
                <div class="w-[13rem] h-[13rem] rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center relative overflow-hidden">
                  <FilePond
                    ref="pond"
                    name="profile_image"
                    label-idle='<div class="text-center"><svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg><p class="text-sm text-gray-500">Click to upload</p></div>'
                    accepted-file-types="image/jpeg, image/png, image/gif, image/webp"
                    max-file-size="3MB"
                    :allow-multiple="false"
                    :allow-revert="false"
                    :allow-remove="true"
                    @addfile="handleImageUpload"
                    @removefile="removeImage"
                    class="absolute inset-0 w-full h-full"
                    :class="{ 'opacity-0': form.profile_image_preview }"
                    :style-load-indicator-position="'center bottom'"
                    :style-progress-indicator-position="'center bottom'"
                  />
                  <img 
                    v-if="form.profile_image_preview"
                    :src="form.profile_image_preview" 
                    class="w-full h-full object-cover rounded-full"
                    alt="Profile"
                  >
                  <button 
                    v-if="form.profile_image_preview"
                    type="button"
                    @click="removeImage"
                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs z-10 hover:bg-red-600"
                  >
                    ×
                  </button>
                </div>
              </div>
              <p class="text-xs text-gray-500 mt-2">Max file size: 3MB</p>
            </div>

            <!-- About Me Section -->
            <div class="flex items-center gap-3 mt-4 pl-3">
              <StarIcon class="w-5 h-5" />
              <h1>About me</h1>
            </div>
            <div class="bg-white rounded-lg p-6 mt-4">
              <div class="space-y-4">
                <div>
                  <label class="text-sm font-poppins leading-normal text-black mb-1">Position</label>
                  <input 
                    v-model="form.position" 
                    type="text" 
                    class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
                  >
                </div>
                <div>
                  <label class="text-sm font-poppins leading-normal text-black mb-1">Short description about yourself</label>
                  <textarea 
                    v-model="form.about" 
                    placeholder="Enter description about yourself" 
                    rows="4" 
                    class="w-full px-3 py-2 text-sm rounded-xl bg-input mt-2 outline-none border border-transparent focus:border-green focus:ring-0"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Badges Section -->
            <div class="flex items-center gap-3 mt-4 pl-3">
              <StarIcon class="w-5 h-5" />
              <h1>Badges</h1>
            </div>
            <div class="bg-white rounded-lg p-6 mt-6">
              <div v-if="!badges.length" class="text-gray-600 px-3">
                You haven't unlocked any badges yet.
              </div>
              <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 px-3">
                <label 
                  v-for="badge in badges" 
                  :key="badge.id"
                  class="flex flex-col items-center cursor-pointer"
                >
                  <div 
                    :class="[
                      'border rounded-lg p-2 mb-1',
                      form.displayed_badges.includes(badge.id) 
                        ? 'border-green bg-green/10' 
                        : 'border-gray'
                    ]"
                  >
                    <img 
                      :src="badge.image_url" 
                      class="w-12 h-12 object-contain" 
                      :alt="badge.name"
                    >
                  </div>
                  <input 
                    type="checkbox" 
                    :value="badge.id"
                    v-model="form.displayed_badges"
                    @change="toggleBadge(badge.id)"
                    class="hidden"
                  >
                  <span class="text-xs text-center">{{ badge.name }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="bg-white rounded-lg p-8 flex-1">
          <div class="space-y-4">
            <div>
              <label class="text-sm font-poppins leading-normal text-black mb-1">First name</label>
              <input 
                v-model="form.firstname" 
                type="text" 
                class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
              >
            </div>
            <div>
              <label class="text-sm font-poppins leading-normal text-black mb-1">Last name</label>
              <input 
                v-model="form.lastname" 
                type="text" 
                class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
              >
            </div>
            <div>
              <label class="text-sm font-poppins leading-normal text-black mb-1">Email</label>
              <input 
                v-model="form.email" 
                type="email" 
                :readonly="!hasPermission"
                :class="[
                  'w-full rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent h-[2.75rem]',
                  hasPermission 
                    ? 'bg-input focus:border-green focus:ring-0' 
                    : 'bg-gray-200 cursor-not-allowed'
                ]"
              >
            </div>
            <div>
              <label class="text-sm font-poppins leading-normal text-black mb-1">Company</label>
              <input 
                v-model="form.company" 
                type="text" 
                class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
              >
            </div>
            <div>
              <label class="text-sm font-poppins leading-normal text-black mb-1">LinkedIn</label>
              <input 
                v-model="form.linkedin" 
                type="text" 
                class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
              >
            </div>
            <div>
              <label class="text-sm font-poppins leading-normal text-black mb-1">Facebook</label>
              <input 
                v-model="form.facebook" 
                type="text" 
                class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
              >
            </div>
            <div>
              <label class="text-sm font-poppins leading-normal text-black mb-1">Instagram</label>
              <input 
                v-model="form.instagram" 
                type="text" 
                class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
              >
            </div>

            <!-- Admin Only Fields -->
            <template v-if="hasPermission">
              <div>
                <label class="text-sm font-poppins leading-normal text-black mb-1">Total Tickets</label>
                <input 
                  v-model.number="form.total_tickets" 
                  type="number" 
                  min="0" 
                  class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
                >
              </div>
              <div>
                <label class="text-sm font-poppins leading-normal text-black mb-1">Remaining Tickets</label>
                <input 
                  v-model.number="form.remaining_tickets" 
                  type="number" 
                  min="0" 
                  class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
                >
              </div>
              <div class="flex flex-col">
                <label class="text-sm font-poppins">Role</label>
                <select 
                  v-model="form.role" 
                  class="w-full max-w-sm bg-input rounded-xl px-4 text-sm h-10 mt-2 outline-none border border-transparent focus:border-green focus:ring-0"
                >
                  <option value="user">User</option>
                  <option v-if="hasPermission" value="admin">Admin</option>
                  <option v-if="hasGodPermission" value="god">Super Admin</option>
                </select>
              </div>
            </template>

            <!-- Change Password Section -->
            <div class="mt-4">
              <a 
                href="#" 
                @click.prevent="showPasswordFields = !showPasswordFields"
                class="flex items-center gap-2 text-green hover:text-green/80"
              >
                <LockIcon class="w-5 h-5" />
                <p class="text-green hover:text-green/70 transition-colors duration-300 text-sm underline">
                  Change password
                </p>
              </a>
            </div>

            <div v-show="showPasswordFields" class="mt-2 space-y-4">
              <div>
                <label class="text-sm font-poppins leading-normal text-black mb-1">New Password</label>
                <div class="relative">
                  <input 
                    v-model="form.password" 
                    :type="showPassword ? 'text' : 'password'" 
                    class="w-full bg-input rounded-xl px-4 pr-12 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]" 
                    placeholder="Enter new password" 
                    autocomplete="new-password"
                  >
                  <button 
                    type="button" 
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-[40%] w-5 h-5"
                  >
                    <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                    <EyeIcon2 v-else class="w-5 h-5" />
                  </button>
                </div>
              </div>
              <div>
                <label class="text-sm font-poppins leading-normal text-black mb-1">Confirm New Password</label>
                <div class="relative">
                  <input 
                    v-model="form.password_confirmation" 
                    :type="showPasswordConfirm ? 'text' : 'password'" 
                    class="w-full bg-input rounded-xl px-4 pr-12 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]" 
                    placeholder="Confirm new password" 
                    autocomplete="new-password"
                  >
                  <button 
                    type="button" 
                    @click="showPasswordConfirm = !showPasswordConfirm"
                    class="absolute right-3 top-[40%] w-5 h-5"
                  >
                    <img 
                      :src="`${libraryUrl}/images/icons/eye-icon${showPasswordConfirm ? '-2' : ''}.svg`" 
                      class="w-5 h-5" 
                      alt="Toggle Password"
                    >
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile Delete Button -->
      <button 
        type="button" 
        @click="showDeleteModal = true"
        v-if="hasPermission && user.role !== 'god'"
        class="md:hidden flex w-full h-9 bg-white text-black py-2 rounded-md font-semibold border border-reddish-pink transition-colors duration-200 ease-in-out justify-center items-center text-sm"
      >
        <img :src="`${libraryUrl}/images/icons/delete.svg`" class="w-[0.8rem] h-[0.8rem] mr-2" alt="Delete Icon">
        Delete
      </button>
    </form>

    <!-- Delete Modal -->
    <div 
      v-if="showDeleteModal" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click="showDeleteModal = false"
    >
      <div 
        class="bg-white p-6 rounded-lg max-w-md w-full mx-4"
        @click.stop
      >
        <h3 class="text-lg font-semibold mb-4">Confirm Delete</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to delete this profile? This action cannot be undone.</p>
        <div class="flex gap-4">
          <button 
            @click="showDeleteModal = false"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button 
            @click="handleDelete"
            class="flex-1 px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
 
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3';
import vueFilePond from 'vue-filepond'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview'
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'
import UserCardIcon from '@/assets/icons/user-card.svg';
import LeftArrowIcon from '@/assets/icons/left-arrow.svg';
import DeleteIcon from '@/assets/icons/delete.svg';
import StarIcon from '@/assets/icons/star.svg';
import LockIcon from '@/assets/icons/lock.svg';
import EyeIcon from '@/assets/icons/eye-icon.svg';
import EyeIcon2 from '@/assets/icons/eye-icon-2.svg';

import AppLayout from '@/layouts/AppLayout.vue'

// Import FilePond styles
import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css'

const FilePond = vueFilePond(
  FilePondPluginImagePreview,
  FilePondPluginFileValidateType
)

interface Props {
  user: {
    id: number
    uuid: string
    firstname?: string
    lastname?: string
    email: string
    company?: string
    position?: string
    about?: string
    social_media?: {
      linkedin?: string
      facebook?: string
      instagram?: string
    }
    total_tickets?: number
    remaining_tickets?: number
    role?: string
    profile_image_url?: string
    displayed_badges?: number[]
    slug?: string
  }
  badges?: Array<{
    id: number
    name: string
    image_url: string
  }>
  hasPermission?: boolean
  hasGodPermission?: boolean
  libraryUrl: string
}

const props = withDefaults(defineProps<Props>(), {
  badges: () => [],
  hasPermission: false,
  hasGodPermission: false
})

const loading = ref(false)
const showDeleteModal = ref(false)
const showPasswordFields = ref(false)
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const pond = ref<any>(null)

const breadcrumbs = [
  {
    title: 'Profile',
    href: `/profile/${props.user.slug || props.user.uuid}`,
  },
  {
    title: 'Edit Profile',
    href: `/edit-profile/${props.user.slug || props.user.uuid}`,
  }
]

const form = reactive({
  firstname: props.user.firstname || '',
  lastname: props.user.lastname || '',
  email: props.user.email || '',
  company: props.user.company || '',
  position: props.user.position || '',
  about: props.user.about || '',
  linkedin: props.user.social_media?.linkedin || '',
  facebook: props.user.social_media?.facebook || '',
  instagram: props.user.social_media?.instagram || '',
  total_tickets: props.user.total_tickets || 0,
  remaining_tickets: props.user.remaining_tickets || 0,
  role: props.user.role || 'user',
  password: '',
  password_confirmation: '',
  profile_image: null as File | null,
  profile_image_preview: props.user.profile_image_url || null,
  displayed_badges: props.user.displayed_badges || []
})

const handleImageUpload = (error: any, file: any) => {
  if (error) {
    console.error('FilePond error:', error)
    alert('Error uploading image: ' + error.message)
    return
  }
  
  if (file && file.file) {
    const fileObj = file.file
    
    // Check file size (3MB limit)
    if (fileObj.size > 3 * 1024 * 1024) {
      alert('File size must be less than 3MB')
      if (pond.value) {
        pond.value.removeFiles()
      }
      return
    }
    
    // Check file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    if (!allowedTypes.includes(fileObj.type)) {
      alert('Please select a valid image file (JPEG, PNG, GIF, or WebP)')
      if (pond.value) {
        pond.value.removeFiles()
      }
      return
    }
    
    form.profile_image = fileObj
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e: any) => {
      form.profile_image_preview = e.target.result
    }
    reader.onerror = () => {
      alert('Error reading file')
      removeImage()
    }
    reader.readAsDataURL(fileObj)
  }
}

const removeImage = () => {
  form.profile_image = null
  form.profile_image_preview = props.user.profile_image_url || null
  if (pond.value) {
    pond.value.removeFiles()
  }
}

const toggleBadge = async (badgeId: number) => {
  try {
    const show = form.displayed_badges.includes(badgeId)
    const response = await fetch('/api/toggle-displayed-badge', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ 
        badge_id: badgeId, 
        show: show 
      })
    })

    const result = await response.json()
    if (!result.success) {
      // Revert the change if it failed
      if (show) {
        form.displayed_badges = form.displayed_badges.filter((id: number) => id !== badgeId)
      } else {
        form.displayed_badges.push(badgeId)
      }
      alert('Error: ' + (result.error || 'Unable to save badge selection'))
    }
  } catch (error) {
    console.error('Badge toggle error:', error)
    alert('Network error while saving badge selection')
  }
}

const validateForm = () => {
  if (form.password || form.password_confirmation) {
    if (form.password !== form.password_confirmation) {
      alert('Passwords do not match')
      return false
    }
    if (form.password.length < 6) {
      alert('Password must be at least 6 characters long')
      return false
    }
  }
  return true
}

const handleSubmit = async () => {
  if (!validateForm()) return

  loading.value = true
  
  try {
    const formData = new FormData()
    
    // Add all form fields
    Object.keys(form).forEach(key => {
      const value = (form as any)[key]
      if (key === 'profile_image' && value) {
        formData.append('profile_image', value)
      } else if (key === 'displayed_badges') {
        formData.append('displayed_badges', JSON.stringify(value))
      } else if (key !== 'profile_image_preview' && value !== '') {
        formData.append(key, value)
      }
    })

    const endpoint = props.user.uuid ? `/api/update-profile/${props.user.uuid}` : '/api/update-profile'
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: formData
    })

    const result = await response.json()
    
    if (result.success) {
      alert('Profile updated successfully!')
      // Refresh the page to show updated data
      router.reload()
    } else {
      alert('Error: ' + (result.message || 'Failed to update profile'))
    }
  } catch (error) {
    console.error('Submit error:', error)
    alert('Network error while updating profile')
  } finally {
    loading.value = false
  }
}

const handleDelete = async () => {
  try {
    const response = await fetch(`/api/users/${props.user.uuid}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })

    const result = await response.json()
    
    if (result.success) {
      alert('Profile deleted successfully')
      router.visit('/dashboard')
    } else {
      alert('Error: ' + (result.message || 'Failed to delete profile'))
    }
  } catch (error) {
    console.error('Delete error:', error)
    alert('Network error while deleting profile')
  } finally {
    showDeleteModal.value = false
  }
}
</script>