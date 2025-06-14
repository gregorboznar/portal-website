<template>
  <div class="flex items-center gap-2 mb-4 ml-3">
    <StarIcon class="w-5 h-5 text-gray-500 top-5" />
    <h1 class="text-xl font-semibold text-left">About me</h1>
  </div>
  
    <div class="bg-white rounded-lg shadow-lg p-6">
      <div class="flex items-center gap-2 mb-4">
        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
       
      </div>
      
      <div class="space-y-3">
        <div>
          <h4 class="font-semibold text-gray-900">CEO at {{ company }}</h4>
          <p class="text-gray-600 text-sm mt-2">
            {{ description }}
          </p>
        </div>
        
        <div class="pt-4 border-t border-gray-200">
          <p class="text-sm text-gray-500">Member since {{ memberSince }}</p>
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import StarIcon from '@/assets/icons/star.svg'
  defineProps({
    company: {
      type: String,
      default: 'Bplanet d.o.o.'
    },
    description: {
      type: String,
      default: "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book."
    },
    memberSince: {
      type: String,
      default: 'January 1970'
    }
  })
  </script>