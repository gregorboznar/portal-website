<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div 
      v-if="successMessage" 
      class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"
    >
      {{ successMessage }}
    </div>
    <form @submit.prevent="handleSubmit" class="space-y-3" autocomplete="off">
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
              @click="handleDeleteClick"
              v-if="hasPermission"
              class="md:flex hidden w-full h-9 bg-white text-black py-2 rounded-md font-semibold border border-reddish-pink transition-colors duration-200 ease-in-out justify-center items-center text-sm sm:w-[10rem]"
            >
              <DeleteIcon class="w-[0.8rem] h-[0.8rem] mr-2" />
              Delete
            </button>
            <button 
              type="submit" 
              :disabled="form.processing"
              class="flex w-full h-9 bg-green text-white py-2 rounded-md font-semibold hover:bg-lightgreen transition-colors duration-200 ease-in-out justify-center items-center text-sm sm:w-[10rem] disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save changes' }}
            </button>
          </div>
        </div>
      </div>
      <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
        <div class="flex-[1.5]">
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
                    :class="{ 'opacity-0': profileImagePreview }"
                    :style-load-indicator-position="'center bottom'"
                    :style-progress-indicator-position="'center bottom'"
                  />
                  <img 
                    v-if="profileImagePreview"
                    :src="profileImagePreview" 
                    class="w-full h-full object-cover rounded-full"
                    alt="Profile"
                  >
                  <button 
                    v-if="profileImagePreview"
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
            <div class="flex items-center gap-3 mt-4 pl-3">
              <StarIcon class="w-5 h-5" />
              <h1>About me</h1>
            </div>
            <div class="bg-white rounded-lg p-6 mt-4">
              <div class="space-y-4">
                <div>
                  <label class="text-sm font-poppins leading-normal text-black mb-1">Position</label>
                  <Input 
                    v-model="form.position" 
                    type="text" 
                    class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
                  />
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
          </div>
        </div>
        <div class="bg-white rounded-lg p-8 flex-1">
          <div class="space-y-4">
            <div>
              <label class="text-sm font-poppins leading-normal text-black mb-1">First name</label>
              <Input 
                v-model="form.firstname" 
                type="text" 
                class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
              />
            </div>
            <div>
              <label class="text-sm font-poppins leading-normal text-black mb-1">Last name</label>
              <Input 
                v-model="form.lastname" 
                type="text" 
                class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
              />
            </div>
            <div>
              <label class="text-sm font-poppins leading-normal text-black mb-1">Email</label>
              <Input 
                v-model="form.email" 
                type="email" 
                :readonly="!hasPermission"
                :class="[
                  'w-full rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent h-[2.75rem]',
                  hasPermission 
                    ? 'bg-input focus:border-green focus:ring-0' 
                    : 'bg-gray-200 cursor-not-allowed'
                ]"
              />
            </div>
            <div>
              <label class="text-sm font-poppins leading-normal text-black mb-1">Company</label>
              <Input 
                v-model="form.company" 
                type="text" 
                class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
              />
            </div>
            <div class="flex gap-4">
              <div class="flex-1">
                <label class="text-sm font-poppins leading-normal text-black mb-1">LinkedIn</label>
                <Input 
                  v-model="form.linkedin" 
                  type="text" 
                  class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
                />
              </div>
              <div class="flex-1">
                <label class="text-sm font-poppins leading-normal text-black mb-1">Facebook</label>
                <Input 
                  v-model="form.facebook" 
                  type="text" 
                  class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
                />
              </div>
            </div>
             <div class="mt-4">
              <label class="text-sm font-poppins leading-normal text-black mb-1">Registered at</label>
              <Input 
                v-model="form.registered_at" 
                type="date"
                class="w-full bg-input rounded-xl px-4 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]"
              />
            </div>
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
                  <Input 
                    v-model="form.password" 
                    :type="showPassword ? 'text' : 'password'" 
                    class="w-full bg-input rounded-xl px-4 pr-12 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]" 
                    placeholder="Enter new password" 
                    autocomplete="new-password"
                  />
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
                  <Input 
                    v-model="form.password_confirmation" 
                    :type="showPasswordConfirm ? 'text' : 'password'" 
                    class="w-full bg-input rounded-xl px-4 pr-12 text-sm h-10 text-[#333] mt-2 outline-none border border-transparent focus:border-green focus:ring-0 h-[2.75rem]" 
                    placeholder="Confirm new password" 
                    autocomplete="new-password"
                  />
                  <button 
                    type="button" 
                    @click="showPasswordConfirm = !showPasswordConfirm"
                    class="absolute right-3 top-[40%] w-5 h-5"
                  >
                    <EyeIcon v-if="!showPasswordConfirm" class="w-5 h-5" />
                    <EyeIcon2 v-else class="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <button 
        type="button" 
          @click="handleDeleteClick"
        v-if="hasPermission "
        class="md:hidden flex w-full h-9 bg-white text-black py-2 rounded-md font-semibold border border-reddish-pink transition-colors duration-200 ease-in-out justify-center items-center text-sm"
      >
        <DeleteIcon class="w-[0.8rem] h-[0.8rem] mr-2" />
        Delete
      </button>
    </form>
  </AppLayout>

      <ConfirmDeleteDialog
        v-model:open="showDeleteDialog"
        title="Delete User"
        description="This action cannot be undone. This will permanently delete the user and remove it from our servers."
        confirm-text="Delete"
        :is-loading="isDeletingUser"
        @confirm="handleDeleteConfirm"
    />
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import vueFilePond from 'vue-filepond'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview'
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue'
import UserCardIcon from '@/assets/icons/user-card.svg';
import LeftArrowIcon from '@/assets/icons/left-arrow.svg';
import DeleteIcon from '@/assets/icons/delete.svg';
import StarIcon from '@/assets/icons/star.svg';
import LockIcon from '@/assets/icons/lock.svg';
import EyeIcon from '@/assets/icons/eye-icon.svg';
import EyeIcon2 from '@/assets/icons/eye-icon-2.svg';
import AppLayout from '@/layouts/AppLayout.vue'
import { Input } from '@/components/ui/input';

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
     
    }
    role?: string
    profile_image_url?: string
    slug?: string
    registered_at?: string
  }
  hasPermission?: boolean
  hasGodPermission?: boolean
  libraryUrl: string
}

const props = withDefaults(defineProps<Props>(), {
  hasPermission: false,
  hasGodPermission: false
})


const page = usePage()
const successMessage = (page.props as any).flash?.success

const showDeleteDialog = ref(false)
const showPasswordFields = ref(false)
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const pond = ref<any>(null)
const isDeletingUser = ref(false)

const profileImagePreview = ref(props.user.profile_image_url || null)

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

const form = useForm({
  firstname: props.user.firstname || '',
  lastname: props.user.lastname || '',
  email: props.user.email || '',
  company: props.user.company || '',
  position: props.user.position || '',
  about: props.user.about || '',
  linkedin: props.user.social_media?.linkedin || '',
  facebook: props.user.social_media?.facebook || '',
  registered_at: props.user.registered_at || '',
  role: props.user.role || 'user',
  password: '',
  password_confirmation: '',
  profile_image: null as File | null
})

const handleImageUpload = (error: any, file: any) => {
  if (error) {
    console.error('FilePond error:', error)
    alert('Error uploading image: ' + error.message)
    return
  }
  
  if (file && file.file) {
    const fileObj = file.file
    
 
    if (fileObj.size > 3 * 1024 * 1024) {
      alert('File size must be less than 3MB')
      if (pond.value) {
        pond.value.removeFiles()
      }
      return
    }
    

    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    if (!allowedTypes.includes(fileObj.type)) {
      alert('Please select a valid image file (JPEG, PNG, GIF, or WebP)')
      if (pond.value) {
        pond.value.removeFiles()
      }
      return
    }
    
    form.profile_image = fileObj
    
   
    const reader = new FileReader()
    reader.onload = (e: any) => {
      profileImagePreview.value = e.target.result
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
  profileImagePreview.value = props.user.profile_image_url || null
  if (pond.value) {
    pond.value.removeFiles()
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

const handleSubmit = () => {
  if (!validateForm()) return

  const endpoint = props.user.uuid ? `/api/update-profile/${props.user.uuid}` : '/api/update-profile'
  
  form.post(endpoint, {
    forceFormData: true, 
    onError: (errors) => {
      console.error('Form errors:', errors)
      const firstError = Object.values(errors)[0]
      alert('Error: ' + (firstError || 'Failed to update profile'))
    },
    onFinish: () => {
   
      form.password = ''
      form.password_confirmation = ''
    }
  })
}

const handleDeleteClick = () => {
    showDeleteDialog.value = true;
};

const handleDeleteConfirm = async () => {
  isDeletingUser.value = true;
  try {
    const response = await fetch(`/api/users/${props.user.uuid}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })

    const result = await response.json()
    
    if (result.success) {
      router.visit('/dashboard')
    } else {
      alert('Error: ' + (result.message || 'Failed to delete profile'))
    }
  } catch (error) {
    console.error('Delete error:', error)
    alert('Network error while deleting profile')
  } finally {
    showDeleteDialog.value = false
    isDeletingUser.value = false;
  }
}
</script>