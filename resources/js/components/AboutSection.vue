<template>
  <div class="flex items-center gap-2 mb-4 ml-3">
    <StarIcon class="w-5 h-5 text-gray-500 top-5" />
    <h1 class="text-xl font-semibold text-left">About me</h1>
  </div>
  
    <div class="bg-white rounded-lg shadow-lg p-6">
    
      <div class="space-y-3">
        <div>
          <h1 v-if="position" class="font-semibold text-left">{{ position }}</h1>
          <h1 v-else class="font-semibold text-left text-gray-500">No company information</h1>
          <p class="text-gray-600 text-sm mt-2">
            {{ description || 'No description available' }}
          </p>
        </div>
        
        <div class="pt-4 border-t border-gray-200">
          <p class="text-sm text-gray-500">Member since {{ formatDate(registered_at) }}</p>
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import StarIcon from '@/assets/icons/star.svg'
  
  interface Props {
    position?: string;
    description?: string;
    registered_at?: string;
  }
  
  defineProps<Props>();
  
  const formatDate = (dateString?: string) => {
    if (!dateString) return 'Unknown';
    
    try {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long'
      });
    } catch {
      return 'Unknown';
    }
  };
  </script>