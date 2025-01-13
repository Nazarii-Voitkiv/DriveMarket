import { NextApiRequest, NextApiResponse } from 'next';
import { BOT_TOKEN, CHANNEL_ID } from '../../bot/config';

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method !== 'POST') {
    return res.status(405).json({ message: 'Method not allowed' });
  }

  const { userId } = req.body;

  if (!userId) {
    return res.status(400).json({ message: 'User ID is required' });
  }

  try {
    const response = await fetch(
      `https://api.telegram.org/bot${BOT_TOKEN}/getChatMember?chat_id=${CHANNEL_ID}&user_id=${userId}`
    );
    
    const data = await response.json();
    
    if (!data.ok) {
      return res.status(400).json({ isMember: false, error: data.description });
    }

    const status = data.result.status;
    const isMember = ['creator', 'administrator', 'member'].includes(status);

    return res.status(200).json({ isMember });
  } catch (error) {
    console.error('Error checking channel membership:', error);
    return res.status(500).json({ message: 'Internal server error' });
  }
}
