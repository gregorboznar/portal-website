<template>
  <div class="flex items-center gap-2 mb-4 ml-3">
    <StarIcon class="w-5 h-5 text-gray-500 top-5" />
    <h1 class="text-xl font-semibold text-left">About me</h1>
  </div>
  
    <div class="bg-white rounded-lg shadow-lg p-6">
     
      <div class="space-y-3">
        <div>
          <h1 class="font-semibold text-left">CEO at {{ company }}</h1>
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