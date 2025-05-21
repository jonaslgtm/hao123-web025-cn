-- 创建数据库
CREATE DATABASE IF NOT EXISTS navpage_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE navpage_db;

-- 创建用户表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 创建分类表
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    identifier VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 创建网站表
CREATE TABLE IF NOT EXISTS websites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    img_url VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- 插入默认管理员账户
INSERT INTO users (username, password, email) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com');

-- 插入默认分类数据
INSERT INTO categories (name, identifier) VALUES 
('全部', 'all'),
('资讯', 'news'),
('科技', 'tech'),
('工具', 'tools'),
('社交', 'social'),
('购物', 'shopping');

-- 插入默认网站数据
INSERT INTO websites (name, url, category_id, img_url, display_order) VALUES
-- 科技类
('Apple', 'https://www.apple.com/cn/', (SELECT id FROM categories WHERE identifier = 'tech'), 'https://www.apple.com/ac/globalnav/7/zh_CN/images/be15095f-5a20-57d0-ad14-cf4c638e223a/globalnav_apple_image__b5er5ngrzxqq_large.svg', 1),
('iCloud', 'https://www.icloud.com/', (SELECT id FROM categories WHERE identifier = 'tech'), 'https://www.apple.com/v/icloud/b/images/overview/icloud_icon__er1ur1j3rys2_large_2x.png', 2),
('GitHub', 'https://github.com/', (SELECT id FROM categories WHERE identifier = 'tech'), 'https://github.githubassets.com/assets/GitHub-Mark-ea2971cee799.png', 3),
('DeepSeek', 'https://www.deepseek.com/', (SELECT id FROM categories WHERE identifier = 'tech'), 'https://www.deepseek.com/images/logo.svg', 4),

-- 社交类
('Instagram', 'https://www.instagram.com/', (SELECT id FROM categories WHERE identifier = 'social'), 'https://static.cdninstagram.com/rsrc.php/v3/yt/r/30PrGfR3xhB.png', 1),
('抖音', 'https://www.douyin.com/', (SELECT id FROM categories WHERE identifier = 'social'), 'https://lf1-cdn-tos.bytegoofy.com/goofy/ies/douyin_web/public/favicon.ico', 2),

-- 资讯类
('央视视频', 'https://tv.cctv.com/', (SELECT id FROM categories WHERE identifier = 'news'), 'https://p1.img.cctvpic.com/photoAlbum/templet/common/DEPA1452928658061849/cctv_logo_20161128.png', 1),
('YouTube', 'https://www.youtube.com/', (SELECT id FROM categories WHERE identifier = 'news'), 'https://www.youtube.com/s/desktop/e4d15d2c/img/favicon_144x144.png', 2),
('知乎', 'https://www.zhihu.com/', (SELECT id FROM categories WHERE identifier = 'news'), 'https://static.zhihu.com/heifetz/favicon.ico', 3),
('微博', 'https://weibo.com/', (SELECT id FROM categories WHERE identifier = 'news'), 'https://weibo.com/favicon.ico', 4),
('鸟类', 'https://www.niaolei.org.cn/', (SELECT id FROM categories WHERE identifier = 'news'), 'https://www.niaolei.org.cn/favicon.ico', 5),

-- 工具类
('微信公众平台', 'https://work.weixin.qq.com/', (SELECT id FROM categories WHERE identifier = 'tools'), 'https://res.wx.qq.com/a/wx_fed/assets/res/OTE0YTAw.png', 1),
('百度翻译', 'https://fanyi.baidu.com/', (SELECT id FROM categories WHERE identifier = 'tools'), 'https://fanyi-cdn.cdn.bcebos.com/static/translation/img/favicon/favicon-32x32_ca689c3.png', 2),
('腾讯翻译', 'https://fanyi.qq.com/', (SELECT id FROM categories WHERE identifier = 'tools'), 'https://fanyi.qq.com/favicon.ico', 3),
('石墨', 'https://www.shimo.im/', (SELECT id FROM categories WHERE identifier = 'tools'), 'https://assets.shimonote.com/favicon.ico', 4),
('天眼查', 'https://www.tianyancha.com/', (SELECT id FROM categories WHERE identifier = 'tools'), 'https://static.tianyancha.com/wap-static/images/favicon.ico', 5),

-- 购物类
('小红书', 'https://www.xiaohongshu.com/', (SELECT id FROM categories WHERE identifier = 'shopping'), 'https://ci.xiaohongshu.com/favicon.ico', 1),
('京东购物', 'https://www.jd.com/', (SELECT id FROM categories WHERE identifier = 'shopping'), 'https://www.jd.com/favicon.ico', 2);