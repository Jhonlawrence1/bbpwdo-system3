require('dotenv').config();
const express = require('express');
const { Pool } = require('pg');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const path = require('path');

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  if (req.method === 'OPTIONS') return res.sendStatus(200);
  next();
});
app.use(express.static(path.join(__dirname, 'web/bbpwdo-system/public')));

const PORT = process.env.PORT || 3000;
const JWT_SECRET = process.env.JWT_SECRET || 'bbpwdo_secret_key_2024';

const pool = new Pool({
  connectionString: process.env.DATABASE_URL,
  ssl: process.env.NODE_ENV === 'production' ? { rejectUnauthorized: false } : false,
});

async function initDB() {
  if (!process.env.DATABASE_URL) {
    console.log('DATABASE_URL not set, skipping database initialization');
    return;
  }
  const client = await pool.connect();
  try {
    await client.query(`
      CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        role VARCHAR(50) DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);
    await client.query(`
      CREATE TABLE IF NOT EXISTS registrations (
        id SERIAL PRIMARY KEY,
        reference VARCHAR(100),
        data JSONB,
        status VARCHAR(50) DEFAULT 'pending',
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);
    
    // Add status column if it doesn't exist (migration)
    try {
      await client.query(`ALTER TABLE registrations ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'pending'`);
    } catch(e) {}
    await client.query(`
      CREATE TABLE IF NOT EXISTS activities (
        id SERIAL PRIMARY KEY,
        type VARCHAR(100),
        details JSONB,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);
    await client.query(`
      CREATE TABLE IF NOT EXISTS messages (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(50),
        subject VARCHAR(100),
        message TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);
    
    // Add missing columns if they exist
    try { await client.query(`ALTER TABLE messages ADD COLUMN IF NOT EXISTS phone VARCHAR(50)`); } catch(e) {}
    try { await client.query(`ALTER TABLE messages ADD COLUMN IF NOT EXISTS subject VARCHAR(100)`); } catch(e) {}
    
    await client.query(`
      CREATE TABLE IF NOT EXISTS team_cards (
        id SERIAL PRIMARY KEY,
        position VARCHAR(100),
        name VARCHAR(255),
        title VARCHAR(255),
        bio TEXT,
        order_num INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);
    
    await client.query(`
      CREATE TABLE IF NOT EXISTS homepage_stats (
        id SERIAL PRIMARY KEY,
        stat_key VARCHAR(50) UNIQUE NOT NULL,
        stat_value INTEGER DEFAULT 0,
        stat_label VARCHAR(100),
        stat_icon VARCHAR(50),
        sort_order INTEGER DEFAULT 0
      )
    `);
    
    const teamExists = await client.query('SELECT COUNT(*) as count FROM team_cards');
    if (parseInt(teamExists.rows[0].count) === 0) {
      await client.query(`
        INSERT INTO team_cards (position, name, title, order_num) VALUES 
        ('Chairman', 'Ronie C. Cabalbun', 'PWD President', 1),
        ('Vice Chairman', 'Marlon B. Dayo', 'Vice President', 2),
        ('Secretary', 'Catherine P. Pabillaran', 'Secretary', 3),
        ('Treasurer', 'Reynan B. Pabillaran', 'Treasurer', 4)
      `);
    }
    
    const statsExists = await client.query('SELECT COUNT(*) as count FROM homepage_stats');
    if (parseInt(statsExists.rows[0].count) === 0) {
      await client.query(`
        INSERT INTO homepage_stats (stat_key, stat_value, stat_label, stat_icon, sort_order) VALUES 
        ('programs', 50, 'Programs This Year', 'fa-calendar-check', 2),
        ('partners', 25, 'Partner Organizations', 'fa-hand-holding-heart', 3),
        ('success_stories', 100, 'Success Stories', 'fa-award', 4)
      `);
    }

    const adminExists = await client.query("SELECT id FROM users WHERE role = 'admin'");
    if (adminExists.rows.length === 0) {
      const hashedPassword = await bcrypt.hash('admin123', 10);
      await client.query(
        'INSERT INTO users (username, password, email, role) VALUES ($1, $2, $3, $4)',
        ['admin', hashedPassword, 'admin@bbpwdo.com', 'admin']
      );
      console.log('Default admin created: admin / admin123');
    }
    console.log('Database initialized');
  } catch (err) {
    console.error('DB init error:', err.message);
  } finally {
    client.release();
  }
}

const authenticate = (req, res, next) => {
  const token = req.headers.authorization?.split(' ')[1];
  if (!token) return res.status(401).json({ error: 'Unauthorized' });
  try {
    req.user = jwt.verify(token, JWT_SECRET);
    next();
  } catch (e) {
    res.status(401).json({ error: 'Invalid token' });
  }
};

app.post('/api/login', async (req, res) => {
  const { email, password } = req.body;
  try {
    const result = await pool.query('SELECT * FROM users WHERE email = $1 AND role = $1', ['admin@bbpwdo.com']);
    const user = result.rows[0];
    if (!user || !(await bcrypt.compare(password, user.password))) {
      return res.json({ success: false, message: 'Invalid credentials' });
    }
    const token = jwt.sign({ id: user.id, username: user.username, role: user.role }, JWT_SECRET, { expiresIn: '24h' });
    res.json({ success: true, token, username: user.username, role: user.role });
  } catch (err) {
    res.json({ success: false, message: err.message });
  }
});

app.post('/api/admin/login', async (req, res) => {
  const { email, password } = req.body;
  try {
    const result = await pool.query('SELECT * FROM users WHERE email = $1', [email]);
    const user = result.rows[0];
    if (!user || !(await bcrypt.compare(password, user.password))) {
      return res.json({ success: false, message: 'Invalid credentials' });
    }
    const token = jwt.sign({ id: user.id, username: user.username, role: user.role }, JWT_SECRET, { expiresIn: '24h' });
    res.json({ success: true, token, username: user.username, role: user.role });
  } catch (err) {
    res.json({ success: false, message: err.message });
  }
});

app.get('/api/registrations', async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM registrations ORDER BY timestamp DESC');
    res.json(result.rows);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/admin/registrations', authenticate, async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM registrations ORDER BY timestamp DESC');
    res.json({ success: true, registrations: result.rows });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.put('/api/admin/registrations/:id', authenticate, async (req, res) => {
  const { id } = req.params;
  const { status } = req.body;
  try {
    await pool.query('UPDATE registrations SET status = $1 WHERE id = $2', [status, id]);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.delete('/api/admin/registrations/:id', authenticate, async (req, res) => {
  const { id } = req.params;
  try {
    await pool.query('DELETE FROM registrations WHERE id = $1', [id]);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/register', async (req, res) => {
  const reg = req.body;
  const reference = 'PWD-' + Date.now();
  try {
    await pool.query(
      'INSERT INTO registrations (reference, data) VALUES ($1, $2)',
      [reference, JSON.stringify(reg)]
    );
    res.json({ success: true, reference });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/messages', async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM messages ORDER BY timestamp DESC');
    res.json(result.rows);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/admin/messages', authenticate, async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM messages ORDER BY timestamp DESC');
    res.json({ success: true, messages: result.rows });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.delete('/api/admin/messages/:id', authenticate, async (req, res) => {
  const { id } = req.params;
  try {
    await pool.query('DELETE FROM messages WHERE id = $1', [id]);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/contact', async (req, res) => {
  const { name, email, phone, subject, message } = req.body;
  try {
    await pool.query(
      'INSERT INTO messages (name, email, phone, subject, message) VALUES ($1, $2, $3, $4, $5)',
      [name, email, phone || '', subject || '', message]
    );
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/stats', async (req, res) => {
  try {
    const total = (await pool.query('SELECT COUNT(*) as count FROM registrations')).rows[0].count;
    const pending = (await pool.query("SELECT COUNT(*) as count FROM registrations WHERE status = 'pending'")).rows[0].count;
    const approved = (await pool.query("SELECT COUNT(*) as count FROM registrations WHERE status = 'approved'")).rows[0].count;
    res.json({ total, pending, approved });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/admin/stats', authenticate, async (req, res) => {
  try {
    const total = (await pool.query('SELECT COUNT(*) as count FROM registrations')).rows[0].count;
    const pending = (await pool.query("SELECT COUNT(*) as count FROM registrations WHERE status = 'pending'")).rows[0].count;
    const approved = (await pool.query("SELECT COUNT(*) as count FROM registrations WHERE status = 'approved'")).rows[0].count;
    const messages = (await pool.query('SELECT COUNT(*) as count FROM messages')).rows[0].count;
    res.json({ success: true, stats: { total, pending, approved, messages } });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

app.get('/api/team', async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM team_cards ORDER BY order_num ASC');
    res.json(result.rows);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/stats/homepage', async (req, res) => {
  try {
    let stats = [];
    try {
      const result = await pool.query('SELECT * FROM homepage_stats ORDER BY sort_order ASC');
      stats = result.rows || [];
    } catch (e) {
      console.log('homepage_stats table not found, using defaults');
      stats = [
        { stat_key: 'programs', stat_value: 50, stat_label: 'Programs This Year', stat_icon: 'fa-calendar-check', sort_order: 2 },
        { stat_key: 'partners', stat_value: 25, stat_label: 'Partner Organizations', stat_icon: 'fa-hand-holding-heart', sort_order: 3 },
        { stat_key: 'success_stories', stat_value: 100, stat_label: 'Success Stories', stat_icon: 'fa-award', sort_order: 4 }
      ];
    }
    
    let registered = 0;
    try {
      const totalPWD = await pool.query("SELECT COUNT(*) as count FROM registrations WHERE status = 'approved'");
      registered = parseInt(totalPWD.rows[0]?.count) || 0;
    } catch (e) {
      console.log('Could not count registrations');
    }
    
    stats.unshift({
      id: 0,
      stat_key: 'registered',
      stat_value: registered,
      stat_label: 'Registered PWDs',
      stat_icon: 'fa-users',
      sort_order: 1,
      auto: true
    });
    
    res.json(stats);
  } catch (err) {
    console.error('Stats error:', err);
    res.json([
      { stat_key: 'registered', stat_value: 0, stat_label: 'Registered PWDs', stat_icon: 'fa-users', auto: true },
      { stat_key: 'programs', stat_value: 50, stat_label: 'Programs This Year', stat_icon: 'fa-calendar-check' },
      { stat_key: 'partners', stat_value: 25, stat_label: 'Partner Organizations', stat_icon: 'fa-hand-holding-heart' },
      { stat_key: 'success_stories', stat_value: 100, stat_label: 'Success Stories', stat_icon: 'fa-award' }
    ]);
  }
});

app.get('/api/admin/stats/homepage', authenticate, async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM homepage_stats ORDER BY sort_order ASC');
    res.json({ success: true, stats: result.rows });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.put('/api/admin/stats/homepage/:key', authenticate, async (req, res) => {
  const { key } = req.params;
  const { stat_value } = req.body;
  try {
    await pool.query(
      'UPDATE homepage_stats SET stat_value = $1 WHERE stat_key = $2',
      [stat_value, key]
    );
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/admin/team', authenticate, async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM team_cards ORDER BY order_num ASC');
    res.json({ success: true, team: result.rows });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/admin/team', authenticate, async (req, res) => {
  const { position, name, title, bio } = req.body;
  try {
    const maxOrder = await pool.query('SELECT MAX(order_num) as max FROM team_cards');
    const order = (maxOrder.rows[0].max || 0) + 1;
    const result = await pool.query(
      'INSERT INTO team_cards (position, name, title, bio, order_num) VALUES ($1, $2, $3, $4, $5) RETURNING *',
      [position, name, title, bio || '', order]
    );
    res.json({ success: true, member: result.rows[0] });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.put('/api/admin/team/:id', authenticate, async (req, res) => {
  const { id } = req.params;
  const { position, name, title, bio } = req.body;
  try {
    await pool.query(
      'UPDATE team_cards SET position = $1, name = $2, title = $3, bio = $4 WHERE id = $5',
      [position, name, title, bio || '', id]
    );
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.delete('/api/admin/team/:id', authenticate, async (req, res) => {
  const { id } = req.params;
  try {
    await pool.query('DELETE FROM team_cards WHERE id = $1', [id]);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.use((req, res, next) => {
  const filePath = path.join(__dirname, 'web/bbpwdo-system/public', req.path);
  const fs = require('fs');
  if (fs.existsSync(filePath) && fs.statSync(filePath).isFile()) {
    res.sendFile(filePath);
  } else {
    res.sendFile(path.join(__dirname, 'web/bbpwdo-system/public/index.html'));
  }
});

initDB().then(() => {
  app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server running on port ${PORT}`);
  });
}).catch(err => {
  console.error('Database initialization failed:', err.message);
  app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server running on port ${PORT} (without database)`);
  });
});