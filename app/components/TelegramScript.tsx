'use client';

import Script from 'next/script';

export default function TelegramScript() {
  return (
    <Script
      src="https://telegram.org/js/telegram-web-app.js"
      strategy="beforeInteractive"
      onLoad={() => {
        console.log('Telegram WebApp script loaded');
      }}
      onError={(e) => {
        console.error('Error loading Telegram WebApp script:', e);
      }}
    />
  );
}
