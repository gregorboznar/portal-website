import { computed, reactive } from 'vue';

interface User {
    id: number;
    name: string;
    slug: string;
    avatar: string | null;
}

// Use reactive instead of ref for better global state management
const presenceState = reactive({
    users: [] as User[],
    isConnected: false,
    isInitialized: false,
    lastAttempt: 0
});

let presenceChannel: any = null;

export function usePresence() {
    const initializePresence = async () => {
        if (!window.Echo) {
            console.warn('usePresence: Echo not available');
            return;
        }

        // Prevent multiple rapid initializations
        const now = Date.now();
        if (presenceState.lastAttempt && (now - presenceState.lastAttempt) < 2000) {
            console.log('usePresence: Recent initialization attempt, skipping');
            return;
        }
        presenceState.lastAttempt = now;

        // If already connecting, wait for it to complete
        if (presenceState.isInitialized && !presenceState.isConnected) {
            console.log('usePresence: Already initializing, waiting...');
            return;
        }

        // If already connected, nothing to do
        if (presenceChannel && presenceState.isConnected) {
            console.log('usePresence: Presence already connected');
            return;
        }

        console.log('usePresence: Starting presence initialization...');
        presenceState.isInitialized = true;
        presenceState.isConnected = false;

        // Enhanced connection checking with better Echo state detection
        let retries = 0;
        const maxRetries = 15; // Increased for better reliability
        
        while (retries < maxRetries) {
            // Wait longer between attempts for better stability
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            try {
                console.log(`usePresence: Attempt ${retries + 1} - Checking Echo connection...`);
                
                // Better connection state checking
                const connector = window.Echo.connector;
                let isEchoReady = false;
                
                if (connector?.pusher?.connection?.state === 'connected') {
                    isEchoReady = true;
                    console.log('usePresence: Echo ready (Pusher connected)');
                } else if (connector?.socket?.readyState === WebSocket.OPEN) {
                    isEchoReady = true;
                    console.log('usePresence: Echo ready (WebSocket open)');
                } else {
                    console.log('usePresence: Echo not ready yet, connector state:', 
                        connector?.pusher?.connection?.state || connector?.socket?.readyState || 'unknown');
                }
                
                if (!isEchoReady) {
                    retries++;
                    continue;
                }
                
                console.log(`usePresence: Attempting to join presence channel...`);
                
                presenceChannel = window.Echo.join('presence')
                    .here((users: User[]) => {
                        console.log('usePresence: Users currently online:', users);
                        presenceState.users = users || [];
                        presenceState.isConnected = true;
                    })
                    .joining((user: User) => {
                        console.log('usePresence: User joining:', user);
                        if (user && !presenceState.users.some(u => u.id === user.id)) {
                            presenceState.users.push(user);
                        }
                    })
                    .leaving((user: User) => {
                        console.log('usePresence: User leaving:', user);
                        if (user) {
                            presenceState.users = presenceState.users.filter(u => u.id !== user.id);
                        }
                    })
                    .error((error: any) => {
                        console.error('usePresence: Presence channel error:', error);
                        presenceState.isConnected = false;
                        presenceState.isInitialized = false;
                        presenceChannel = null;
                        
                        // Reset and retry after a longer delay for stability
                        setTimeout(() => {
                            console.log('usePresence: Retrying after error...');
                            presenceState.lastAttempt = 0; // Reset throttle
                            initializePresence();
                        }, 5000);
                    });
                    
                console.log('usePresence: Presence channel join initiated successfully');
                
                // Wait longer to see if connection succeeds
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                if (presenceState.isConnected) {
                    console.log('usePresence: Successfully connected to presence channel');
                    break;
                } else {
                    console.log('usePresence: Connection attempt failed, retrying...');
                    retries++;
                    presenceState.isInitialized = false;
                    presenceChannel = null;
                }
                
            } catch (error) {
                console.error(`usePresence: Attempt ${retries + 1} failed:`, error);
                retries++;
                presenceState.isInitialized = false;
                presenceChannel = null;
                
                if (retries >= maxRetries) {
                    console.error('usePresence: Max retries reached, giving up');
                    break;
                }
            }
        }
        
        if (!presenceState.isConnected) {
            console.error('usePresence: Failed to establish presence connection after all retries');
            presenceState.isInitialized = false;
        }
    };

    const leavePresence = () => {
        if (window.Echo && presenceChannel) {
            try {
                console.log('usePresence: Leaving presence channel...');
                window.Echo.leave('presence');
                presenceChannel = null;
                presenceState.users = [];
                presenceState.isConnected = false;
                presenceState.isInitialized = false;
            } catch (error) {
                console.error('usePresence: Error leaving presence channel:', error);
            }
        }
    };

    const resetPresence = () => {
        console.log('usePresence: Resetting presence state...');
        if (presenceChannel) {
            try {
                window.Echo.leave('presence');
            } catch (error) {
                console.log('usePresence: Error leaving during reset:', error);
            }
        }
        presenceChannel = null;
        presenceState.users = [];
        presenceState.isConnected = false;
        presenceState.isInitialized = false;
        presenceState.lastAttempt = 0;
    };

    const isUserOnline = (userId: number) => {
        return presenceState.users.some(user => user.id === userId);
    };

    const onlineUsers = computed(() => presenceState.users);
    const isConnected = computed(() => presenceState.isConnected);
    const connectionStatus = computed(() => ({
        isConnected: presenceState.isConnected,
        isInitialized: presenceState.isInitialized,
        userCount: presenceState.users.length
    }));

    return {
        initializePresence,
        leavePresence,
        resetPresence,
        isUserOnline,
        onlineUsers,
        isConnected,
        connectionStatus
    };
} 