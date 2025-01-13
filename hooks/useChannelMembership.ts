import { useEffect, useState } from 'react';

declare global {
  interface Window {
    Telegram: {
      WebApp: {
        initDataUnsafe: {
          user?: {
            id: number;
          };
        };
      };
    };
  }
}

export function useChannelMembership() {
  const [isMember, setIsMember] = useState<boolean | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const checkMembership = async () => {
      try {
        // Wait for Telegram WebApp to initialize
        if (typeof window === 'undefined') {
          console.log('Window is not defined yet');
          return;
        }

        if (!window.Telegram) {
          console.log('Telegram object is not available yet');
          return;
        }

        if (!window.Telegram.WebApp) {
          console.log('WebApp object is not available yet');
          return;
        }

        const userId = window.Telegram.WebApp.initDataUnsafe?.user?.id;
        console.log('User ID:', userId);
        console.log('WebApp data:', window.Telegram.WebApp.initDataUnsafe);

        if (!userId) {
          console.error('User ID not found in WebApp data');
          setError('User ID not found');
          setIsLoading(false);
          return;
        }

        const response = await fetch('/api/check-membership', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ userId }),
        });

        const data = await response.json();
        console.log('API response:', data);

        if (data.error) {
          console.error('API error:', data.error);
          setError(data.error);
          setIsMember(false);
        } else {
          setIsMember(data.isMember);
        }
      } catch (err) {
        console.error('Error checking membership:', err);
        setError(err instanceof Error ? err.message : 'Failed to check membership');
      } finally {
        setIsLoading(false);
      }
    };

    // Add a small delay to ensure Telegram WebApp is initialized
    const timer = setTimeout(checkMembership, 1000);
    
    // Cleanup
    return () => clearTimeout(timer);
  }, []);

  return { isMember, isLoading, error };
}
