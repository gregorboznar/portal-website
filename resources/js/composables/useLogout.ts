import { router } from '@inertiajs/vue3';
import { usePresence } from './usePresence';

export function useLogout() {
    const logout = () => {
        const { leavePresence, resetPresence } = usePresence();
        
        // Leave presence channel and reset state
        console.log('Logout: Leaving presence channel...');
        leavePresence();
        resetPresence();
        
        // For SPA behavior, don't completely disconnect Echo
        // Instead, let the new session re-establish the connection
        if (window.Echo) {
            try {
                // Leave all channels but keep the connection alive
                // This is better for single-page app behavior
                console.log('Logout: Resetting Echo channels...');
                
                // Try to leave known channels gracefully
                try {
                    window.Echo.leaveAllChannels?.();
                                 } catch {
                     console.log('Logout: No leaveAllChannels method available');
                 }
                
                // Only disconnect if we're doing a full page redirect
                // For now, let's not disconnect completely to avoid connection issues
                console.log('Logout: Keeping Echo connection alive for smoother re-login');
                
            } catch (error) {
                console.error('Error during Echo cleanup:', error);
            }
        }
        
        // Clear Inertia cache
        router.flushAll();
    };

    return {
        logout
    };
} 