import { NextApiRequest, NextApiResponse } from 'next';

const BOT_TOKEN = process.env.BOT_TOKEN;

async function sendMessage(chatId: number, text: string) {
  console.log('Sending message to:', chatId, 'Text:', text);
  
  const response = await fetch(`https://api.telegram.org/bot${BOT_TOKEN}/sendMessage`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      chat_id: chatId,
      text: text,
      parse_mode: 'HTML'
    }),
  });
  
  const result = await response.json();
  console.log('Telegram API response:', result);
  return result;
}

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  console.log('Webhook received request:', {
    method: req.method,
    body: req.body,
    headers: req.headers
  });

  if (req.method !== 'POST') {
    console.log('Method not allowed:', req.method);
    return res.status(405).json({ message: 'Method not allowed' });
  }

  try {
    const { message } = req.body;
    console.log('Received message:', message);
    
    if (!message?.text) {
      console.log('No message text');
      return res.status(400).json({ message: 'No message text' });
    }

    const chatId = message.chat.id;
    const text = message.text;

    console.log('Processing command:', text);

    if (text === '/admin') {
      console.log('Handling /admin command');
      await sendMessage(chatId, `
<b>Адмін панель</b>

Доступні команди:
/create_raffle - Створити новий розіграш
/list_raffles - Список активних розіграшів
/end_raffle - Завершити розіграш

Статистика:
👥 Учасників: 0
🎲 Активних розіграшів: 0
      `);
    }

    res.status(200).json({ message: 'Success' });
  } catch (error) {
    console.error('Webhook error:', error);
    res.status(500).json({ message: 'Internal server error' });
  }
}
