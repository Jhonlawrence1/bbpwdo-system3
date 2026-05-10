require('dotenv').config();
const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');
const { Pool } = require('pg');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});

const PORT = process.env.PORT || 3000;
const JWT_SECRET = process.env.JWT_SECRET || 'pwd_secret_key_2024';

const pool = new Pool({
  connectionString: process.env.DATABASE_URL,
  ssl: process.env.NODE_ENV === 'production' ? { rejectUnauthorized: false } : false
});

async function initDatabase() {
  const client = await pool.connect();
  try {
    await client.query(`
      CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        role VARCHAR(50) DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

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
        user VARCHAR(255),
        text TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    await client.query(`
      CREATE TABLE IF NOT EXISTS registrations (
        id SERIAL PRIMARY KEY,
        reference VARCHAR(100),
        data JSONB,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    await client.query(`
      CREATE TABLE IF NOT EXISTS staff (
        id SERIAL PRIMARY KEY,
        data JSONB,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    console.log('Database initialized');
  } finally {
    client.release();
  }
}

app.use(cors());
app.use(express.json());
app.use(express.static(__dirname));

function logActivity(type, details) {
  pool.query('INSERT INTO activities (type, details) VALUES ($1, $2)', [type, JSON.stringify(details)])
    .then(() => io.emit('activity', { type, details, timestamp: new Date().toISOString() }))
    .catch(err => console.error('Error logging activity:', err));
}

app.post('/api/signup', async (req, res) => {
  const { username, password, email } = req.body;
  try {
    const existing = await pool.query('SELECT id FROM users WHERE username = $1', [username]);
    if (existing.rows.length > 0) {
      return res.json({ success: false, message: 'Username exists' });
    }
    const hashedPassword = await bcrypt.hash(password, 10);
    await pool.query(
      'INSERT INTO users (username, password, email, role) VALUES ($1, $2, $3, $4)',
      [username, hashedPassword, email, 'user']
    );
    logActivity('signup', { username, email });
    res.json({ success: true });
  } catch (e) {
    console.error('Signup error:', e);
    res.json({ success: false, message: 'Server error' });
  }
});

app.post('/api/login', async (req, res) => {
  const { username, password } = req.body;
  try {
    const result = await pool.query('SELECT * FROM users WHERE username = $1', [username]);
    const user = result.rows[0];
    if (!user || !(await bcrypt.compare(password, user.password))) {
      return res.json({ success: false, message: 'Invalid credentials' });
    }
    const token = jwt.sign({ id: user.id, username: user.username, role: user.role }, JWT_SECRET);
    logActivity('login', { username });
    res.json({ success: true, token, username: user.username, role: user.role });
  } catch (e) {
    console.error('Login error:', e);
    res.json({ success: false, message: 'Server error' });
  }
});

app.get('/api/activities', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false });
  try {
    const decoded = jwt.verify(auth.split(' ')[1], JWT_SECRET);
    if (decoded.role !== 'admin') return res.json({ success: false });
    const result = await pool.query('SELECT * FROM activities ORDER BY timestamp DESC LIMIT 100');
    res.json({ success: true, activities: result.rows });
  } catch (e) {
    res.json({ success: false });
  }
});

app.post('/api/submit', (req, res) => {
  const { name, email, message, type } = req.body;
  const submission = { id: Date.now(), name, email, message, type: type || 'contact', timestamp: new Date().toISOString() };
  pool.query('INSERT INTO activities (type, details) VALUES ($1, $2)', ['submission', JSON.stringify({ name, email, message, type: type || 'contact' })])
    .then(() => {
      io.emit('activity', { id: submission.id, type: 'submission', details: { name, email, message, type: type || 'contact' }, timestamp: submission.timestamp });
      res.json({ success: true, message: 'Submitted successfully' });
    })
    .catch(err => res.json({ success: false, message: 'Server error' }));
});

app.get('/api/messages', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false });
  try {
    jwt.verify(auth.split(' ')[1], JWT_SECRET);
    const result = await pool.query('SELECT * FROM messages ORDER BY timestamp DESC LIMIT 100');
    res.json({ success: true, messages: result.rows });
  } catch (e) {
    res.json({ success: false });
  }
});

app.post('/api/register', async (req, res) => {
  const reg = req.body;
  console.log('Registration data received:', JSON.stringify(reg, null, 2));
  const reference = 'PWD-' + Date.now();
  try {
    await pool.query(
      'INSERT INTO registrations (reference, data) VALUES ($1, $2)',
      [reference, JSON.stringify(reg)]
    );
    logActivity('registration', { reference, name: reg.firstName + ' ' + reg.lastName });
    io.emit('newRegistration', { reference, data: reg, timestamp: new Date().toISOString() });
    res.json({ success: true, reference });
  } catch (e) {
    console.error('Registration error:', e);
    res.json({ success: false, message: 'Server error' });
  }
});

app.get('/api/registrations', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false });
  try {
    jwt.verify(auth.split(' ')[1], JWT_SECRET);
    const result = await pool.query('SELECT * FROM registrations ORDER BY timestamp DESC');
    const registrations = result.rows.map(r => ({ id: r.id, reference: r.reference, ...r.data, timestamp: r.timestamp }));
    res.json({ success: true, registrations });
  } catch (e) {
    res.json({ success: false });
  }
});

app.put('/api/registrations/:id', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false });
  try {
    jwt.verify(auth.split(' ')[1], JWT_SECRET);
    const { id } = req.params;
    const existing = await pool.query('SELECT * FROM registrations WHERE id = $1', [id]);
    if (existing.rows.length === 0) return res.json({ success: false, message: 'Not found' });
    const updated = { ...existing.rows[0].data, ...req.body };
    await pool.query('UPDATE registrations SET data = $1 WHERE id = $2', [JSON.stringify(updated), id]);
    logActivity('update_registration', { reference: existing.rows[0].reference });
    res.json({ success: true, registration: { id, reference: existing.rows[0].reference, ...updated } });
  } catch (e) {
    res.json({ success: false });
  }
});

app.delete('/api/registrations/:id', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false });
  try {
    jwt.verify(auth.split(' ')[1], JWT_SECRET);
    const { id } = req.params;
    const existing = await pool.query('SELECT * FROM registrations WHERE id = $1', [id]);
    if (existing.rows.length === 0) return res.json({ success: false, message: 'Not found' });
    await pool.query('DELETE FROM registrations WHERE id = $1', [id]);
    logActivity('delete_registration', { reference: existing.rows[0].reference });
    res.json({ success: true });
  } catch (e) {
    res.json({ success: false });
  }
});

app.post('/api/reset', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false });
  try {
    const decoded = jwt.verify(auth.split(' ')[1], JWT_SECRET);
    if (decoded.role !== 'admin') return res.json({ success: false });
    await pool.query('DELETE FROM registrations');
    await pool.query('DELETE FROM activities');
    await pool.query('DELETE FROM messages');
    logActivity('reset', { username: decoded.username });
    res.json({ success: true, message: 'All data reset successfully' });
  } catch (e) {
    res.json({ success: false });
  }
});

app.get('/api/backup', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false });
  try {
    const decoded = jwt.verify(auth.split(' ')[1], JWT_SECRET);
    if (decoded.role !== 'admin') return res.json({ success: false });
    const users = (await pool.query('SELECT id, username, email, role FROM users')).rows;
    const activities = (await pool.query('SELECT * FROM activities')).rows;
    const messages = (await pool.query('SELECT * FROM messages')).rows;
    const registrations = (await pool.query('SELECT * FROM registrations')).rows;
    const staff = (await pool.query('SELECT * FROM staff')).rows;
    res.json({ success: true, data: { users, activities, messages, registrations, staff, exportDate: new Date().toISOString() } });
  } catch (e) {
    res.json({ success: false });
  }
});

app.post('/api/add-activity', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false });
  try {
    const decoded = jwt.verify(auth.split(' ')[1], JWT_SECRET);
    if (decoded.role !== 'admin') return res.json({ success: false });
    const { type, details } = req.body;
    const result = await pool.query(
      'INSERT INTO activities (type, details) VALUES ($1, $2) RETURNING *',
      [type || 'other', JSON.stringify(details)]
    );
    const activity = { ...result.rows[0], details };
    io.emit('activity', activity);
    res.json({ success: true, activity });
  } catch (e) {
    res.json({ success: false });
  }
});

app.get('/api/staff', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false });
  try {
    jwt.verify(auth.split(' ')[1], JWT_SECRET);
    const result = await pool.query('SELECT * FROM staff ORDER BY timestamp DESC');
    const staff = result.rows.map(s => ({ id: s.id, ...s.data, timestamp: s.timestamp }));
    res.json({ success: true, staff });
  } catch (e) {
    res.json({ success: false });
  }
});

app.post('/api/staff', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false, message: 'No auth' });
  try {
    const decoded = jwt.verify(auth.split(' ')[1], JWT_SECRET);
    if (decoded.role !== 'admin') return res.json({ success: false, message: 'Not admin' });
    const result = await pool.query(
      'INSERT INTO staff (data) VALUES ($1) RETURNING *',
      [JSON.stringify(req.body)]
    );
    const newStaff = { id: result.rows[0].id, ...req.body, timestamp: result.rows[0].timestamp };
    logActivity('add_staff', { name: req.body.name, role: req.body.role });
    res.json({ success: true, staff: newStaff });
  } catch (e) {
    res.json({ success: false, message: e.message });
  }
});

app.put('/api/staff/:id', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false });
  try {
    jwt.verify(auth.split(' ')[1], JWT_SECRET);
    const { id } = req.params;
    const existing = await pool.query('SELECT * FROM staff WHERE id = $1', [id]);
    if (existing.rows.length === 0) return res.json({ success: false, message: 'Not found' });
    const updated = { ...existing.rows[0].data, ...req.body };
    await pool.query('UPDATE staff SET data = $1 WHERE id = $2', [JSON.stringify(updated), id]);
    logActivity('update_staff', { name: updated.name, role: updated.role });
    res.json({ success: true, staff: { id, ...updated } });
  } catch (e) {
    res.json({ success: false });
  }
});

app.delete('/api/staff/:id', async (req, res) => {
  const auth = req.headers.authorization;
  if (!auth) return res.json({ success: false });
  try {
    jwt.verify(auth.split(' ')[1], JWT_SECRET);
    const { id } = req.params;
    const existing = await pool.query('SELECT * FROM staff WHERE id = $1', [id]);
    if (existing.rows.length === 0) return res.json({ success: false, message: 'Not found' });
    await pool.query('DELETE FROM staff WHERE id = $1', [id]);
    logActivity('delete_staff', { name: existing.rows[0].data.name });
    res.json({ success: true });
  } catch (e) {
    res.json({ success: false });
  }
});

io.on('connection', (socket) => {
  console.log('User connected:', socket.id);

  socket.on('chat', (data) => {
    const message = {
      id: Date.now(),
      user: data.user,
      text: data.text,
      timestamp: new Date().toISOString()
    };
    pool.query('INSERT INTO messages (user, text) VALUES ($1, $2)', [data.user, data.text])
      .then(() => {
        io.emit('chat', message);
        logActivity('chat', { user: data.user, message: data.text });
      })
      .catch(err => console.error('Error saving message:', err));
  });

  socket.on('disconnect', () => {
    console.log('User disconnected:', socket.id);
  });
});

initDatabase().then(() => {
  server.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
  });
}).catch(err => {
  console.error('Failed to initialize database:', err);
  process.exit(1);
});