import { NextApiRequest, NextApiResponse } from 'next';
import { BOT_TOKEN, CHANNEL_ID } from '@/bot/config';

export default async function handler(
  req: NextApiRequest,
  res: NextApiResponse
) {
  if (req.method !== 'POST') {
    return res.status(405).json({ message: 'Method not allowed' });
  }

  const { userId } = req.body;

  if (!userId) {
    return res.status(400).json({ message: 'User ID is required' });
  }

  try {
    console.log('Checking membership for user:', userId);
    console.log('Channel ID:', CHANNEL_ID);
    console.log('Using bot token:', BOT_TOKEN ? 'Present' : 'Missing');

    const response = await fetch(
      `https://api.telegram.org/bot${BOT_TOKEN}/getChatMember?chat_id=${CHANNEL_ID}&user_id=${userId}`
    );

    const data = await response.json();
    console.log('Telegram API response:', data);

    if (!data.ok) {
      console.error('Telegram API error:', data.description);
      return res.status(400).json({ isMember: false, error: data.description });
    }

    const status = data.result.status;
    console.log('User status:', status);

    const isMember = ['creator', 'administrator', 'member'].includes(status);
    console.log('Is member:', isMember);

    return res.status(200).json({ isMember });
  } catch (error) {
    console.error('Error checking membership:', error);
    return res.status(500).json({ 
      message: 'Internal server error',
      error: error instanceof Error ? error.message : String(error)
    });
  }
}
