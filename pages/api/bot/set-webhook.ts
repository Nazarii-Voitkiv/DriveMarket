import { NextApiRequest, NextApiResponse } from 'next';

const BOT_TOKEN = process.env.BOT_TOKEN;
const WEBAPP_URL = process.env.WEBAPP_URL;

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method !== 'POST') {
    return res.status(405).json({ message: 'Method not allowed' });
  }

  try {
    const webhookUrl = `${WEBAPP_URL}/api/bot/webhook`;
    
    const response = await fetch(
      `https://api.telegram.org/bot${BOT_TOKEN}/setWebhook`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          url: webhookUrl,
        }),
      }
    );

    const data = await response.json();
    res.status(200).json(data);
  } catch (error) {
    console.error('Error setting webhook:', error);
    res.status(500).json({ message: 'Failed to set webhook' });
  }
}
