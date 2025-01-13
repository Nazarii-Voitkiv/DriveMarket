"use client";

import { useChannelMembership } from '@/hooks/useChannelMembership';

export default function Home() {
  const { isMember, isLoading } = useChannelMembership();

  if (isLoading) {
    return <div className="flex items-center justify-center min-h-screen">Loading...</div>;
  }

  if (!isMember) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <p className="text-gray-700 mb-4">Для доступу до застосунку, будь ласка, підпишіться на канал</p>
          <a
            href="https://t.me/nemorgenshtern"
            target="_blank"
            rel="noopener noreferrer"
            className="inline-block px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
          >
            Підписатися на канал
          </a>
        </div>
      </div>
    );
  }

  return (
    <div className="flex items-center justify-center min-h-screen">
      <button className="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
        Відкрити застосунок
      </button>
    </div>
  );
}
