<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { usePresence } from '@/composables/usePresence';

const status = ref('Initializing...');
const onlineUsers = ref<any[]>([]);
const logs = ref<string[]>([]);

const addLog = (message: string) => {
    const timestamp = new Date().toLocaleTimeString();
    logs.value.unshift(`[${timestamp}] ${message}`);
    console.log(message);
};

const testPresence = async () => {
    addLog('🚀 Using global presence state...');
    status.value = 'Using Global Presence';
    
    // Instead of creating our own presence channel, use the global one
    const { onlineUsers: globalOnlineUsers, connectionStatus, initializePresence } = usePresence();
    
    // Trigger initialization if not already done
    initializePresence();
    
    // Watch the global state
    const updateFromGlobal = () => {
        onlineUsers.value = globalOnlineUsers.value;
        if (connectionStatus.value.isConnected) {
            status.value = `Connected - ${globalOnlineUsers.value.length} users online`;
            addLog(`📋 Global state: ${globalOnlineUsers.value.length} users online`);
        } else {
            status.value = 'Connecting...';
            addLog('⏳ Waiting for global presence connection...');
        }
    };
    
    // Initial update
    updateFromGlobal();
    
    // Update every second to monitor changes
    const interval = setInterval(updateFromGlobal, 1000);
    
    // Clear interval after 30 seconds
    setTimeout(() => {
        clearInterval(interval);
        addLog('🔄 Monitoring stopped');
    }, 30000);
};

onMounted(() => {
    addLog('🎯 Component mounted, waiting for Echo...');
    
    // Give Echo some time to initialize, then test
    setTimeout(testPresence, 3000);
});
</script>

<template>
    <div class="p-6 bg-blue-50 rounded-lg border-2 border-blue-200 max-w-xl">
        <h3 class="text-lg font-bold mb-4 text-blue-800">🧪 Simple Presence Test</h3>
        
        <div class="mb-4 p-3 bg-white rounded">
            <div class="font-semibold">Status: {{ status }}</div>
            <div class="text-sm text-gray-600 mt-1">Online Users: {{ onlineUsers.length }}</div>
        </div>
        
        <div v-if="onlineUsers.length > 0" class="mb-4 p-3 bg-white rounded">
            <h4 class="font-semibold mb-2">Online Users:</h4>
            <div v-for="user in onlineUsers" :key="user.id" class="text-sm">
                • {{ user.name || `User ${user.id}` }}
            </div>
        </div>
        
        <div class="p-3 bg-white rounded">
            <h4 class="font-semibold mb-2">Test Logs:</h4>
            <div class="space-y-1 text-xs font-mono bg-gray-50 p-2 rounded max-h-40 overflow-y-auto">
                <div v-if="logs.length === 0" class="text-gray-500">No logs yet...</div>
                <div v-for="log in logs.slice(0, 15)" :key="log" class="text-gray-800">{{ log }}</div>
            </div>
        </div>
        
        <button @click="testPresence" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            🔄 Retry Test
        </button>
    </div>
</template> 