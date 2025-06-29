import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { usePresence } from './composables/usePresence';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';

import vueFilePond from 'vue-filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';


import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';

const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginImagePreview
);


window.Pusher = Pusher;

// Debug Echo configuration
const echoConfig = {
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8083,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8083,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
};

console.log('Echo configuration:', echoConfig);

try {
    window.Echo = new Echo({
        ...echoConfig,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        },
        authorizer: (channel: any) => {
            return {
                authorize: (socketId: string, callback: any) => {
                    console.log('Authorizing channel:', channel.name, 'with socket ID:', socketId);
                    axios.post('/broadcasting/auth', {
                        socket_id: socketId,
                        channel_name: channel.name,
                    }, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => {
                        console.log('Broadcasting auth successful for:', channel.name);
                        callback(null, response.data);
                    })
                    .catch(error => {
                        console.error('Broadcasting auth failed for:', channel.name, error);
                        if (error.response) {
                            console.error('Error response:', {
                                status: error.response.status,
                                data: error.response.data,
                                headers: error.response.headers
                            });
                        }
                        callback(error, null);
                    });
                }
            };
        },
    });
    
    console.log('✅ Echo instance created:', window.Echo);
    console.log('🔍 Echo connector:', window.Echo.connector);
    console.log('🔍 Echo options:', window.Echo.options);
    
} catch (error) {
    console.error('❌ Failed to create Echo instance:', error);
}

// Manually trigger connection for Reverb
if (window.Echo) {
    console.log('🔌 Attempting to connect Echo...');
    
    // Try to trigger connection by joining a test channel
    setTimeout(() => {
        try {
            console.log('🧪 Joining test channel to trigger connection...');
            window.Echo.private('test-connection-trigger')
                .error((error: any) => {
                    console.log('⚠️ Test channel error (this might be expected):', error);
                });
            
            // Leave the test channel after a moment
            setTimeout(() => {
                try {
                    window.Echo.leave('test-connection-trigger');
                    console.log('✅ Test channel left, connection should be established');
                } catch (e) {
                    console.log('🔍 Error leaving test channel:', e);
                }
            }, 1000);
            
        } catch (error) {
            console.error('❌ Failed to join test channel:', error);
        }
    }, 500);
}

// Add connection event listeners for debugging
setTimeout(() => {
    if (window.Echo && window.Echo.connector) {
        const connector = window.Echo.connector;
        console.log('🔍 Connector found after delay:', connector);
        
        // Listen for connection events
        if (connector.socket) {
            console.log('🔍 Socket found:', connector.socket);
            connector.socket.onopen = function() {
                console.log('✅ Echo WebSocket connected');
            };
            
            connector.socket.onclose = function(event: CloseEvent) {
                console.log('❌ Echo WebSocket closed:', event.code, event.reason);
            };
            
            connector.socket.onerror = function(error: Event) {
                console.error('❌ Echo WebSocket error:', error);
            };
        } else {
            console.log('❌ No socket found on connector');
        }
        
        // Alternative way to listen for Pusher events if using Pusher connector
        if (window.Pusher && connector.pusher) {
            console.log('🔍 Pusher connector found');
            connector.pusher.connection.bind('connected', function() {
                console.log('✅ Pusher connected');
            });
            
            connector.pusher.connection.bind('disconnected', function() {
                console.log('❌ Pusher disconnected');
            });
            
            connector.pusher.connection.bind('error', function(error: any) {
                console.error('❌ Pusher error:', error);
            });
        }
    } else {
        console.log('❌ No connector found after delay');
    }
}, 1000);





// Add global Echo types
declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: Echo<any>;
        auth?: {
            user?: {
                id: number;
                name: string;
                email: string;
                slug: string;
                [key: string]: any;
            };
        };
    }
}

// Add a function to wait for Echo connection to be ready
const waitForEchoConnection = async (maxWaitTime = 10000, checkInterval = 100): Promise<boolean> => {
    return new Promise((resolve) => {
        const startTime = Date.now();
        
        const checkConnection = () => {
            if (!window.Echo) {
                if (Date.now() - startTime > maxWaitTime) {
                    console.error('🚨 Echo connection timeout after', maxWaitTime, 'ms');
                    resolve(false);
                    return;
                }
                setTimeout(checkConnection, checkInterval);
                return;
            }

            // Check different connection indicators
            const connector = window.Echo.connector;
            
            // For Reverb/Pusher connections
            if (connector?.pusher?.connection?.state === 'connected') {
                console.log('✅ Echo connection ready (Pusher connected)');
                resolve(true);
                return;
            }
            
            // For WebSocket connections
            if (connector?.socket?.readyState === WebSocket.OPEN) {
                console.log('✅ Echo connection ready (WebSocket open)');
                resolve(true);
                return;
            }
            
            // Fallback: try to determine if we can join channels
            try {
                const testChannel = window.Echo.join('presence-connection-test');
                if (testChannel) {
                    // Clean up test channel
                    setTimeout(() => {
                        try {
                            window.Echo.leave('presence-connection-test');
                                                 } catch {
                             // Ignore cleanup errors
                         }
                    }, 100);
                    console.log('✅ Echo connection ready (can join channels)');
                    resolve(true);
                    return;
                }
                         } catch {
                 // Connection not ready yet
             }
            
            if (Date.now() - startTime > maxWaitTime) {
                console.error('🚨 Echo connection timeout after', maxWaitTime, 'ms');
                resolve(false);
                return;
            }
            
            setTimeout(checkConnection, checkInterval);
        };
        
        checkConnection();
    });
};

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        // Make auth data globally available
        window.auth = (props.initialPage?.props as any)?.auth;
        
        // Initialize presence for authenticated users
        if (window.auth?.user) {
            const { initializePresence } = usePresence();
            
            // Wait for Echo connection to be ready before initializing presence
            console.log('App: Waiting for Echo connection before initializing presence...');
            waitForEchoConnection().then((isConnected) => {
                if (isConnected) {
                    console.log('App: Echo is ready, initializing global presence...');
                    initializePresence();
                } else {
                    console.error('App: Echo connection failed, presence initialization skipped');
                }
            });
        }
        
        app.use(plugin)
           .use(ZiggyVue)
           .component('FilePond', FilePond as any)
           .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
