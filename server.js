const express = require('express');
const mysql = require('mysql2/promise');
const path = require('path');

function parseDatabaseUrl(url) {
  if (!url) return { host: 'localhost', port: 3306, user: 'root', password: '', database: 'bbpwdo' };
  const match = url.match(/mysql:\/\/([^:]+):([^@]+)@([^:]+):(\d+)\/(.+)/);
  if (match) {
    return { user: match[1], password: match[2], host: match[3], port: parseInt(match[4]), database: match[5] };
  }
  return { host: 'localhost', port: 3306, user: 'root', password: '', database: 'bbpwdo' };
}

const dbConfig = parseDatabaseUrl(process.env.DATABASE_URL);

const pool = mysql.createPool({
  host: dbConfig.host,
  port: dbConfig.port,
  user: dbConfig.user,
  password: dbConfig.password,
  database: dbConfig.database,
});

const app = express();
app.use(express.json());
app.use(express.static(path.join(__dirname, 'web/bbpwdo-system/public')));

async function initDB() {
  try {
    const conn = await pool.getConnection();
    await conn.query(`
      CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name TEXT,
        email VARCHAR(255) UNIQUE
      )
    `);
    conn.release();
    console.log('Database initialized');
  } catch (err) {
    console.error('DB init error:', err.message);
  }
}
initDB();

app.get('/api/users', async (req, res) => {
  try {
    const [rows] = await pool.query('SELECT * FROM users');
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/users', async (req, res) => {
  try {
    const { name, email } = req.body;
    const [result] = await pool.query(
      'INSERT INTO users (name, email) VALUES (?, ?)',
      [name, email]
    );
    res.json({ id: result.insertId, name, email });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.use((req, res) => {
  res.sendFile(path.join(__dirname, 'web/bbpwdo-system/public/index.html'));
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});