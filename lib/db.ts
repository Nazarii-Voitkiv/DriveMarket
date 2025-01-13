import Database from 'better-sqlite3';
import path from 'path';

// Створюємо з'єднання з базою даних
export const db = new Database(path.join(process.cwd(), 'participants.db'));

// Створюємо таблицю, якщо вона не існує
db.exec(`
  CREATE TABLE IF NOT EXISTS participants (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    telegram_id INTEGER NOT NULL,
    username TEXT,
    first_name TEXT,
    last_name TEXT,
    ip_address TEXT NOT NULL,
    participation_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(telegram_id)
  )
`);

export interface Participant {
  telegram_id: number;
  username?: string;
  first_name?: string;
  last_name?: string;
  ip_address: string;
  participation_time?: string;
}

export function addParticipant(participant: Participant): boolean {
  try {
    const stmt = db.prepare(`
      INSERT OR REPLACE INTO participants 
      (telegram_id, username, first_name, last_name, ip_address)
      VALUES (?, ?, ?, ?, ?)
    `);
    
    stmt.run(
      participant.telegram_id,
      participant.username || null,
      participant.first_name || null,
      participant.last_name || null,
      participant.ip_address
    );
    return true;
  } catch (error) {
    console.error('Error adding participant:', error);
    return false;
  }
}

export function getParticipant(telegramId: number): Participant | null {
  try {
    const stmt = db.prepare('SELECT * FROM participants WHERE telegram_id = ?');
    return stmt.get(telegramId) || null;
  } catch (error) {
    console.error('Error getting participant:', error);
    return null;
  }
}

export function getAllParticipants(): Participant[] {
  try {
    const stmt = db.prepare('SELECT * FROM participants ORDER BY participation_time DESC');
    return stmt.all();
  } catch (error) {
    console.error('Error getting all participants:', error);
    return [];
  }
}
