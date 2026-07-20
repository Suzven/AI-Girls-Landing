-- ============================================================
-- CompanionVerse — схема базы данных
-- Импортируй этот файл в phpMyAdmin (вкладка "Импорт")
-- или выполни в консоли:  mysql -u USER -p DBNAME < schema.sql
-- ============================================================

SET NAMES utf8mb4;

-- ---------- Пользователи менеджмент-панели ----------
-- Пароль можно хранить ПРОСТЫМ ТЕКСТОМ (сайт это поймёт)
-- или как password_hash() — оба варианта работают.
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Демо-логин: admin / admin123  (смени после установки!)
INSERT INTO users (username, password) VALUES ('admin', 'admin123');

-- ---------- Инструменты (офферы) ----------
CREATE TABLE IF NOT EXISTS tools (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  tagline VARCHAR(255) DEFAULT '',
  description TEXT,
  logo VARCHAR(500) DEFAULT '',
  hero_image VARCHAR(500) DEFAULT '',
  website_url VARCHAR(500) DEFAULT '',
  affiliate_link VARCHAR(500) DEFAULT '',
  editor_score DECIMAL(3,1) DEFAULT NULL,
  free_trial TINYINT(1) NOT NULL DEFAULT 0,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  verified TINYINT(1) NOT NULL DEFAULT 0,
  is_new TINYINT(1) NOT NULL DEFAULT 0,
  is_popular TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Категории ----------
CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categories (name, slug) VALUES
('AI Girlfriend', 'ai-girlfriend'),
('AI Boyfriend', 'ai-boyfriend'),
('AI Companion', 'ai-companion'),
('Roleplay', 'roleplay'),
('Voice Chat', 'voice-chat'),
('Image Generation', 'image-generation'),
('Anime', 'anime'),
('Character Creation', 'character-creation');

CREATE TABLE IF NOT EXISTS tool_categories (
  tool_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (tool_id, category_id),
  FOREIGN KEY (tool_id) REFERENCES tools(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Быстрые бейджи карточки ----------
CREATE TABLE IF NOT EXISTS badges (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tool_id INT UNSIGNED NOT NULL,
  label VARCHAR(80) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (tool_id) REFERENCES tools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Key Features ----------
CREATE TABLE IF NOT EXISTS features (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tool_id INT UNSIGNED NOT NULL,
  feature VARCHAR(160) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (tool_id) REFERENCES tools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Тарифы (название / цена) ----------
CREATE TABLE IF NOT EXISTS pricing_plans (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tool_id INT UNSIGNED NOT NULL,
  plan_name VARCHAR(120) NOT NULL,
  price VARCHAR(80) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (tool_id) REFERENCES tools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Плюсы ----------
CREATE TABLE IF NOT EXISTS pros (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tool_id INT UNSIGNED NOT NULL,
  text VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (tool_id) REFERENCES tools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Минусы ----------
CREATE TABLE IF NOT EXISTS cons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tool_id INT UNSIGNED NOT NULL,
  text VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (tool_id) REFERENCES tools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Best For ----------
CREATE TABLE IF NOT EXISTS best_for (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tool_id INT UNSIGNED NOT NULL,
  text VARCHAR(160) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (tool_id) REFERENCES tools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- FAQ ----------
CREATE TABLE IF NOT EXISTS faqs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tool_id INT UNSIGNED NOT NULL,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (tool_id) REFERENCES tools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Альтернативы (связь инструмент → инструмент) ----------
CREATE TABLE IF NOT EXISTS alternatives (
  tool_id INT UNSIGNED NOT NULL,
  alternative_tool_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (tool_id, alternative_tool_id),
  FOREIGN KEY (tool_id) REFERENCES tools(id) ON DELETE CASCADE,
  FOREIGN KEY (alternative_tool_id) REFERENCES tools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Демо-оффер, чтобы сайт не был пустым ----------
INSERT INTO tools (name, slug, tagline, description, website_url, affiliate_link, editor_score, free_trial, featured, verified, is_popular)
VALUES (
  'Candy AI',
  'candy-ai',
  'AI companion for realistic chat, images and voice conversations.',
  'Candy AI lets you create customizable AI companions with realistic conversations, image generation and voice chat. It is designed for users looking for roleplay, companionship and long-term interactions. The platform remembers your preferences and adapts each character over time. You can start for free and upgrade when you need more messages and image generations.',
  'https://candy.ai',
  'https://candy.ai',
  9.2, 1, 1, 1, 1
);

SET @tid = LAST_INSERT_ID();

INSERT INTO tool_categories (tool_id, category_id)
SELECT @tid, id FROM categories WHERE slug IN ('ai-girlfriend','ai-companion','roleplay','voice-chat','image-generation');

INSERT INTO badges (tool_id, label, sort_order) VALUES
(@tid,'AI Girlfriend',1),(@tid,'AI Companion',2),(@tid,'Images',3),(@tid,'Voice',4),(@tid,'Roleplay',5),(@tid,'Mobile',6),(@tid,'Free Trial',7);

INSERT INTO features (tool_id, feature, sort_order) VALUES
(@tid,'AI Chat',1),(@tid,'Voice Calls',2),(@tid,'Image Generation',3),(@tid,'Character Creation',4),(@tid,'Memory',5),(@tid,'Roleplay',6),(@tid,'Mobile App',7);

INSERT INTO pricing_plans (tool_id, plan_name, price, sort_order) VALUES
(@tid,'Free Trial','$0',1),(@tid,'Premium Monthly','$12.99 / mo',2),(@tid,'Premium Yearly','$5.99 / mo',3);

INSERT INTO pros (tool_id, text, sort_order) VALUES
(@tid,'Excellent image quality',1),(@tid,'Natural conversations',2),(@tid,'Huge character library',3);

INSERT INTO cons (tool_id, text, sort_order) VALUES
(@tid,'Subscription required for full access',1),(@tid,'Limited free messages',2);

INSERT INTO best_for (tool_id, text, sort_order) VALUES
(@tid,'Romantic conversations',1),(@tid,'Roleplay',2),(@tid,'Image generation',3),(@tid,'Voice chat',4);

INSERT INTO faqs (tool_id, question, answer, sort_order) VALUES
(@tid,'Is Candy AI free?','Candy AI offers a free trial with limited messages. Full access to chat, images and voice requires a premium subscription.',1),
(@tid,'Is Candy AI safe?','Candy AI uses SSL encryption and discreet billing. Conversations are private to your account.',2),
(@tid,'Can I generate images?','Yes, image generation of your AI companion is available on premium plans.',3),
(@tid,'Does Candy AI support voice?','Yes, voice messages and voice calls are supported.',4);
