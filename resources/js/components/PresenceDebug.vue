<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { usePresence } from '@/composables/usePresence';

const { onlineUsers, connectionStatus, initializePresence } = usePresence();
const echoStatus = ref({
    available: false,
    connected: false,
    socketId: null as string | null,
    connectorState: null as number | null
});

const logs = ref<string[]>([]);

const addLog = (message: string) => {
    const timestamp = new Date().toLocaleTimeString();
    logs.value.unshift(`[${timestamp}] ${message}`);
    if (logs.value.length > 10) {
        logs.value = logs.value.slice(0, 10);
    }
};

const checkEchoStatus = () => {
    if (!window.Echo) {
        echoStatus.value.available = false;
        addLog('❌ Echo not available');
        return;
    }
    
    echoStatus.value.available = true;
    addLog('✅ Echo available');
    
    if (window.Echo.connector && window.Echo.connector.socket) {
        const readyState = window.Echo.connector.socket.readyState;
        echoStatus.value.connected = readyState === 1; // WebSocket.OPEN = 1
        echoStatus.value.socketId = window.Echo.socketId?.() || null;
        echoStatus.value.connectorState = readyState;
        
        const stateText = ['CONNECTING', 'OPEN', 'CLOSING', 'CLOSED'][readyState] || 'UNKNOWN';
        addLog(`🔍 Echo WebSocket state: ${readyState} (${stateText})`);
        
        if (readyState === 1) {
            addLog(`✅ Echo connected - Socket ID: ${echoStatus.value.socketId}`);
        } else if (readyState === 0) {
            addLog('🔄 Echo connecting...');
        } else {
            addLog(`❌ Echo not connected - State: ${stateText}`);
        }
    } else {
        echoStatus.value.connected = false;
        addLog('❌ Echo not connected - No connector/socket');
    }
};

const manuallyInitialize = () => {
    addLog('🔄 Manually initializing presence...');
    initializePresence();
};

const forceConnection = () => {
    if (!window.Echo) {
        addLog('❌ Cannot connect - Echo not available');
        return;
    }
    
    addLog('🔄 Forcing Echo connection...');
    
    try {
        // Try to manually trigger connection if using Pusher connector
        if (window.Echo.connector && typeof window.Echo.connector.connect === 'function') {
            window.Echo.connector.connect();
            addLog('🔌 Connection method called');
        }
        
        // Alternative: try to join a dummy channel to trigger connection
        window.Echo.private('test-channel-to-trigger-connection')
            .error((error: any) => {
                addLog(`⚠️ Test channel error (expected): ${error.message || error}`);
            });
            
        setTimeout(() => {
            window.Echo.leave('test-channel-to-trigger-connection');
            checkEchoStatus();
        }, 1000);
        
    } catch (error: any) {
        addLog(`❌ Force connection failed: ${error.message || error}`);
    }
};

const testPresenceChannel = () => {
    if (!window.Echo) {
        addLog('❌ Cannot test - Echo not available');
        return;
    }
    
    addLog('🧪 Testing presence channel directly...');
    
    try {
        const testChannel = window.Echo.join('presence')
            .here((users: any[]) => {
                addLog(`📋 Test channel - Users here: ${users.length}`);
                console.log('Test presence - users here:', users);
            })
            .joining((user: any) => {
                addLog(`👋 Test channel - User joining: ${user.name}`);
            })
            .leaving((user: any) => {
                addLog(`👋 Test channel - User leaving: ${user.name}`);
            })
            .error((error: any) => {
                addLog(`❌ Test channel error: ${error.message || error}`);
            });
        
        setTimeout(() => {
            if (testChannel) {
                window.Echo.leave('presence');
                addLog('🧪 Test channel left');
            }
        }, 5000);
        
    } catch (error: any) {
        addLog(`❌ Test failed: ${error.message || error}`);
    }
};

onMounted(() => {
    addLog('🚀 Debug component mounted');
    checkEchoStatus();
    
    // Check Echo status every 2 seconds
    const interval = setInterval(checkEchoStatus, 2000);
    
    onUnmounted(() => {
        clearInterval(interval);
    });
});
</script>

<template>
    <div class="p-4 bg-gray-100 rounded-lg border-2 border-gray-300 max-w-2xl">
        <h3 class="text-lg font-bold mb-4">🔍 Presence Debug Panel</h3>
        
        <!-- Echo Status -->
        <div class="mb-4 p-3 bg-white rounded border">
            <h4 class="font-semibold mb-2">Echo Status</h4>
            <div class="space-y-1 text-sm">
                <div>Available: <span :class="echoStatus.available ? 'text-green-600' : 'text-red-600'">{{ echoStatus.available ? '✅' : '❌' }}</span></div>
                <div>Connected: <span :class="echoStatus.connected ? 'text-green-600' : 'text-red-600'">{{ echoStatus.connected ? '✅' : '❌' }}</span></div>
                <div v-if="echoStatus.socketId">Socket ID: {{ echoStatus.socketId }}</div>
                <div v-if="echoStatus.connectorState">Connector State: {{ echoStatus.connectorState }}</div>
            </div>
        </div>
        
        <!-- Presence Status -->
        <div class="mb-4 p-3 bg-white rounded border">
            <h4 class="font-semibold mb-2">Presence Status</h4>
            <div class="space-y-1 text-sm">
                <div>Connected: <span :class="connectionStatus.isConnected ? 'text-green-600' : 'text-red-600'">{{ connectionStatus.isConnected ? '✅' : '❌' }}</span></div>
                <div>Initialized: <span :class="connectionStatus.isInitialized ? 'text-green-600' : 'text-red-600'">{{ connectionStatus.isInitialized ? '✅' : '❌' }}</span></div>
                <div>Online Users: {{ connectionStatus.userCount }}</div>
            </div>
        </div>
        
        <!-- Online Users List -->
        <div class="mb-4 p-3 bg-white rounded border">
            <h4 class="font-semibold mb-2">Online Users ({{ onlineUsers.length }})</h4>
            <div v-if="onlineUsers.length === 0" class="text-gray-500 text-sm">No users online</div>
            <div v-else class="space-y-1">
                <div v-for="user in onlineUsers" :key="user.id" class="text-sm">
                    👤 {{ user.name }} (ID: {{ user.id }})
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="mb-4 space-x-2">
            <button @click="checkEchoStatus" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                🔄 Check Echo
            </button>
            <button @click="forceConnection" class="px-3 py-1 bg-orange-500 text-white rounded text-sm hover:bg-orange-600">
                🔌 Force Connect
            </button>
            <button @click="manuallyInitialize" class="px-3 py-1 bg-green-500 text-white rounded text-sm hover:bg-green-600">
                🚀 Initialize Presence
            </button>
            <button @click="testPresenceChannel" class="px-3 py-1 bg-purple-500 text-white rounded text-sm hover:bg-purple-600">
                🧪 Test Channel
            </button>
        </div>
        
        <!-- Logs -->
        <div class="p-3 bg-white rounded border">
            <h4 class="font-semibold mb-2">Recent Logs</h4>
            <div class="space-y-1 text-xs font-mono bg-gray-50 p-2 rounded max-h-40 overflow-y-auto">
                <div v-if="logs.length === 0" class="text-gray-500">No logs yet...</div>
                <div v-for="log in logs" :key="log" class="text-gray-800">{{ log }}</div>
            </div>
        </div>
    </div>
</template> 