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
        <p className="text-center text-gray-700">Please subscribe to @nemorgenshtern to access the app</p>
      </div>
    );
  }

  return (
    <div className="flex items-center justify-center min-h-screen">
      <button className="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
        Open App
      </button>
    </div>
  );
}
